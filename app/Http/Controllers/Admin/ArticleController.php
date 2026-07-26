<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Extracurricular;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'tab' => ['nullable', Rule::in(['list', 'write'])],
            'search' => ['nullable', 'string', 'max:255'],
            'content_category' => ['nullable', Rule::in(array_keys(Article::contentCategories()))],
            'status' => ['nullable', Rule::in(array_keys(Article::publicationStatuses()))],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'published_from' => ['nullable', 'date'],
            'published_until' => ['nullable', 'date', 'after_or_equal:published_from'],
        ]);

        $tab = $filters['tab'] ?? 'list';
        $search = trim((string) ($filters['search'] ?? ''));
        $contentCategory = (string) ($filters['content_category'] ?? '');
        $status = (string) ($filters['status'] ?? '');
        $extracurricularId = $filters['extracurricular_id'] ?? '';
        $publishedFrom = $filters['published_from'] ?? '';
        $publishedUntil = $filters['published_until'] ?? '';

        $articlesQuery = Article::query()
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'content',
                'content_category',
                'extracurricular_id',
                'published_by',
                'image_path',
                'image_alt_text',
                'publication_status',
                'publish_at',
                'expires_at',
                'is_featured',
                'is_active',
                'created_at',
            ])
            ->with(['publisher:id,name', 'extracurricular:id,name'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($contentCategory !== '', fn ($query) => $query->where('content_category', $contentCategory))
            ->when($status !== '', fn ($query) => $query->where('publication_status', $status))
            ->when($extracurricularId !== '', fn ($query) => $query->where('extracurricular_id', $extracurricularId))
            ->when($publishedFrom !== '', fn ($query) => $query->whereDate('publish_at', '>=', $publishedFrom))
            ->when($publishedUntil !== '', fn ($query) => $query->whereDate('publish_at', '<=', $publishedUntil))
            ->orderByDesc('is_featured')
            ->orderByRaw("case publication_status when 'published' then 0 when 'scheduled' then 1 when 'draft' then 2 when 'archived' then 3 else 4 end")
            ->latest('publish_at')
            ->latest('id');

        $articles = $articlesQuery->paginate(10)->withQueryString();

        $statistics = $this->buildStatistics(Article::query());
        $listUrl = route('admin.articles.index', $request->except(['page', 'tab']) + ['tab' => 'list']);
        $writeUrl = route('admin.articles.index', $request->except(['page', 'tab']) + ['tab' => 'write']);

        return view('admin.articles.index', [
            'articles' => $articles,
            'article' => null,
            'tab' => old('_active_tab', $tab),
            'statistics' => $statistics,
            'extracurriculars' => Extracurricular::query()->select(['id', 'name'])->orderBy('name')->get(),
            'search' => $search,
            'contentCategory' => $contentCategory,
            'status' => $status,
            'extracurricularId' => $extracurricularId,
            'publishedFrom' => $publishedFrom,
            'publishedUntil' => $publishedUntil,
            'contentCategories' => Article::contentCategories(),
            'publicationStatuses' => Article::publicationStatuses(),
            'listUrl' => $listUrl,
            'writeUrl' => $writeUrl,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedPayload($request);
        $article = Article::query()->create($payload);

        return $this->redirectAfterSave($request, $article, true);
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.edit', [
            'article' => $article,
            'extracurriculars' => Extracurricular::orderBy('name')->get(),
            'contentCategories' => Article::contentCategories(),
            'publicationStatuses' => Article::publicationStatuses(),
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $article->update($this->validatedPayload($request, $article));

        return $this->redirectAfterSave($request, $article, false);
    }

    public function preview(Article $article): View
    {
        return view('public.article-detail', [
            'article' => $article->load(['publisher', 'extracurricular']),
            'relatedArticles' => collect(),
            'previewMode' => true,
        ]);
    }

    public function duplicate(Article $article): RedirectResponse
    {
        $duplicate = $article->replicate([
            'slug',
            'publication_status',
            'publish_at',
            'expires_at',
            'is_featured',
            'created_at',
            'updated_at',
        ]);

        $duplicate->title = $article->title.' (Salinan)';
        $duplicate->slug = Article::uniqueSlugFromTitle($duplicate->title);
        $duplicate->publication_status = Article::STATUS_DRAFT;
        $duplicate->publish_at = null;
        $duplicate->expires_at = null;
        $duplicate->is_active = true;
        $duplicate->is_featured = false;
        $duplicate->published_by = auth()->id();
        $duplicate->save();

        return redirect()->route('admin.articles.edit', $duplicate)
            ->with('success', 'Artikel berhasil diduplikasi sebagai draft baru.');
    }

    public function publish(Article $article): RedirectResponse
    {
        if (blank($article->excerpt) || blank(trim(strip_tags($article->content)))) {
            throw ValidationException::withMessages([
                'publication_status' => 'Artikel belum bisa dipublikasikan karena ringkasan atau isi masih belum lengkap.',
            ]);
        }

        $article->update([
            'publication_status' => Article::STATUS_PUBLISHED,
            'publish_at' => $article->publish_at ?: now(),
            'is_active' => true,
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil dipublikasikan.');
    }

    public function unpublish(Article $article): RedirectResponse
    {
        $article->update([
            'publication_status' => Article::STATUS_DRAFT,
            'publish_at' => null,
            'expires_at' => null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Publikasi artikel berhasil ditarik ke draft.');
    }

    public function archive(Article $article): RedirectResponse
    {
        $article->update([
            'publication_status' => Article::STATUS_ARCHIVED,
            'is_active' => true,
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil diarsipkan.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        if ($article->image_path) {
            Storage::disk('public')->delete($article->image_path);
        }

        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Berita / artikel berhasil dihapus.');
    }

    private function redirectAfterSave(Request $request, Article $article, bool $created): RedirectResponse
    {
        $submitAction = $request->input('submit_action', 'draft');
        $message = $created ? 'Berita / artikel berhasil disimpan.' : 'Berita / artikel berhasil diperbarui.';

        if ($submitAction === 'preview') {
            return redirect()->route('admin.articles.preview', $article)->with('success', $message);
        }

        if ($submitAction === 'publish') {
            $article->update([
                'publication_status' => Article::STATUS_PUBLISHED,
                'publish_at' => $article->publish_at ?: now(),
                'is_active' => true,
            ]);

            return redirect()
                ->route('admin.articles.index', ['tab' => 'list'])
                ->with('success', 'Artikel berhasil dipublikasikan.');
        }

        return redirect()
            ->route('admin.articles.index', ['tab' => 'list'])
            ->with('success', $message);
    }

    private function validatedPayload(Request $request, ?Article $article = null): array
    {
        $submitAction = $request->input('submit_action', 'draft');
        $status = $request->input('publication_status', Article::STATUS_DRAFT);
        $requiresPublicationCompleteness = in_array($submitAction, ['publish', 'preview'], true)
            || in_array($status, [Article::STATUS_PUBLISHED, Article::STATUS_SCHEDULED], true);

        $validated = $request->validate([
            '_active_tab' => ['nullable', 'string'],
            'submit_action' => ['nullable', Rule::in(['draft', 'preview', 'publish'])],
            'title' => ['required', 'string', 'min:8', 'max:255'],
            'slug' => ['nullable', 'string', 'min:4', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('articles', 'slug')->ignore($article?->id)],
            'content_category' => ['required', Rule::in(array_keys(Article::contentCategories()))],
            'excerpt' => [$requiresPublicationCompleteness ? 'required' : 'nullable', 'string', 'min:24', 'max:320'],
            'content' => [$requiresPublicationCompleteness ? 'required' : 'nullable', 'string'],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'publication_status' => ['required', Rule::in(array_keys(Article::publicationStatuses()))],
            'publish_date' => ['nullable', 'date'],
            'publish_time' => ['nullable', 'date_format:H:i'],
            'expires_date' => ['nullable', 'date'],
            'expires_time' => ['nullable', 'date_format:H:i'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'image_alt_text' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'title.min' => 'Judul minimal 8 karakter.',
            'excerpt.required' => 'Ringkasan singkat wajib diisi sebelum artikel dipublikasikan.',
            'content.required' => 'Isi artikel wajib diisi sebelum artikel dipublikasikan.',
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);

        $sanitizedContent = Article::sanitizeHtml($validated['content'] ?? '');

        if ($requiresPublicationCompleteness && blank(trim(strip_tags($sanitizedContent)))) {
            throw ValidationException::withMessages([
                'content' => 'Isi artikel wajib diisi sebelum artikel dipublikasikan.',
            ]);
        }

        $publishAt = $this->resolvePublishAt($validated, $status);
        $expiresAt = $this->resolveExpiresAt($validated);

        if ($status === Article::STATUS_SCHEDULED && ! $publishAt) {
            throw ValidationException::withMessages([
                'publish_date' => 'Tanggal dan jam tayang wajib diisi untuk artikel terjadwal.',
            ]);
        }

        if ($expiresAt && $publishAt && $expiresAt->lte($publishAt)) {
            throw ValidationException::withMessages([
                'expires_date' => 'Tanggal berakhir tidak boleh sebelum atau sama dengan tanggal tayang.',
            ]);
        }

        $imagePath = $article?->image_path;
        $imageName = $article?->image_name;
        if ($request->hasFile('image')) {
            if ($article?->image_path) {
                Storage::disk('public')->delete($article->image_path);
            }

            $imageFile = $request->file('image');
            $safeFileName = Str::slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
            $imagePath = $imageFile->storeAs(
                'articles',
                $safeFileName.'-'.Str::uuid().'.'.$imageFile->getClientOriginalExtension(),
                'public'
            );
            $imageName = $imageFile->getClientOriginalName();
        }

        $slug = filled($validated['slug'] ?? null)
            ? Str::slug($validated['slug'])
            : Article::uniqueSlugFromTitle($validated['title'], $article?->id);

        return [
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => filled($validated['excerpt'] ?? null)
                ? trim($validated['excerpt'])
                : Str::limit(trim(strip_tags($sanitizedContent)), 220),
            'content' => $sanitizedContent,
            'content_category' => $validated['content_category'],
            'extracurricular_id' => $validated['extracurricular_id'] ?? null,
            'published_by' => auth()->id(),
            'image_path' => $imagePath,
            'image_name' => $imageName,
            'image_alt_text' => trim($validated['image_alt_text'] ?? '') ?: $validated['title'],
            'meta_description' => trim($validated['meta_description'] ?? '') ?: Str::limit(trim(strip_tags($validated['excerpt'] ?? $sanitizedContent)), 160),
            'publication_status' => $status,
            'publish_at' => $publishAt,
            'expires_at' => $expiresAt,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'is_active' => $status !== Article::STATUS_INACTIVE,
        ];
    }

    private function resolvePublishAt(array $validated, string $status): ?Carbon
    {
        return match ($status) {
            Article::STATUS_DRAFT => null,
            Article::STATUS_SCHEDULED => isset($validated['publish_date'], $validated['publish_time'])
                ? Carbon::parse($validated['publish_date'].' '.$validated['publish_time'])
                : null,
            Article::STATUS_PUBLISHED => isset($validated['publish_date'], $validated['publish_time'])
                ? Carbon::parse($validated['publish_date'].' '.$validated['publish_time'])
                : now(),
            default => null,
        };
    }

    private function resolveExpiresAt(array $validated): ?Carbon
    {
        if (! isset($validated['expires_date']) || blank($validated['expires_date'])) {
            return null;
        }

        $time = filled($validated['expires_time'] ?? null) ? $validated['expires_time'] : '23:59';

        return Carbon::parse($validated['expires_date'].' '.$time);
    }

    private function buildStatistics($query): array
    {
        $rows = $query
            ->selectRaw('publication_status, COUNT(*) as aggregate')
            ->groupBy('publication_status')
            ->pluck('aggregate', 'publication_status');

        return [
            'total' => (int) $rows->sum(),
            'draft' => (int) $rows->get(Article::STATUS_DRAFT, 0),
            'scheduled' => (int) $rows->get(Article::STATUS_SCHEDULED, 0),
            'published' => (int) $rows->get(Article::STATUS_PUBLISHED, 0),
        ];
    }
}
