<?php

namespace App\Support;

use App\Models\Article;
use App\Models\NotificationPreference;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ArticleManager
{
    public function filters(Request $request): array
    {
        $validated = $request->validate([
            'tab' => ['nullable', Rule::in(['list', 'write'])],
            'search' => ['nullable', 'string', 'max:120'],
            'content_category' => ['nullable', Rule::in(array_keys(Article::contentCategories()))],
            'status' => ['nullable', Rule::in([...array_keys(Article::publicationStatuses()), 'expired'])],
            'extracurricular_id' => ['nullable', 'integer', 'exists:extracurriculars,id'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'published_from' => ['nullable', 'date'],
            'published_until' => ['nullable', 'date', 'after_or_equal:published_from'],
            'image' => ['nullable', Rule::in(['with', 'without'])],
            'sort' => ['nullable', Rule::in(['title', 'author', 'publication_status', 'publish_at', 'updated_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
        ]);

        return [
            'tab' => (string) ($validated['tab'] ?? 'list'),
            'search' => trim((string) ($validated['search'] ?? '')),
            'content_category' => (string) ($validated['content_category'] ?? ''),
            'status' => (string) ($validated['status'] ?? ''),
            'extracurricular_id' => isset($validated['extracurricular_id']) ? (int) $validated['extracurricular_id'] : null,
            'author_id' => isset($validated['author_id']) ? (int) $validated['author_id'] : null,
            'published_from' => (string) ($validated['published_from'] ?? ''),
            'published_until' => (string) ($validated['published_until'] ?? ''),
            'image' => (string) ($validated['image'] ?? ''),
            'sort' => (string) ($validated['sort'] ?? 'updated_at'),
            'direction' => (string) ($validated['direction'] ?? 'desc'),
            'per_page' => (int) ($validated['per_page'] ?? 10),
        ];
    }

    public function applyFilters(Builder $query, array $filters): Builder
    {
        $query
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($filters['content_category'], fn (Builder $query, string $category) => $query->where('content_category', $category))
            ->when($filters['status'], function (Builder $query, string $status): void {
                if ($status === 'expired') {
                    $query->where('publication_status', Article::STATUS_PUBLISHED)
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<=', now());

                    return;
                }

                $query->where('publication_status', $status);
            })
            ->when($filters['extracurricular_id'], fn (Builder $query, int $id) => $query->where('extracurricular_id', $id))
            ->when($filters['author_id'], fn (Builder $query, int $id) => $query->where('published_by', $id))
            ->when($filters['published_from'], fn (Builder $query, string $date) => $query->whereDate('publish_at', '>=', $date))
            ->when($filters['published_until'], fn (Builder $query, string $date) => $query->whereDate('publish_at', '<=', $date))
            ->when($filters['image'] === 'with', fn (Builder $query) => $query->whereNotNull('image_path'))
            ->when($filters['image'] === 'without', fn (Builder $query) => $query->whereNull('image_path'));

        if ($filters['sort'] === 'author') {
            $query->orderBy(
                User::query()->select('name')->whereColumn('users.id', 'articles.published_by'),
                $filters['direction']
            );
        } else {
            $query->orderBy($filters['sort'], $filters['direction']);
        }

        return $query->orderByDesc('id');
    }

    public function statistics(Builder $baseQuery): array
    {
        $rows = (clone $baseQuery)
            ->selectRaw('publication_status, COUNT(*) as aggregate')
            ->groupBy('publication_status')
            ->pluck('aggregate', 'publication_status');
        $expired = (clone $baseQuery)
            ->where('publication_status', Article::STATUS_PUBLISHED)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->count();

        return [
            'total' => (int) $rows->sum(),
            'draft' => (int) $rows->get(Article::STATUS_DRAFT, 0),
            'scheduled' => (int) $rows->get(Article::STATUS_SCHEDULED, 0),
            'published' => max(0, (int) $rows->get(Article::STATUS_PUBLISHED, 0) - $expired),
            'archived' => (int) $rows->get(Article::STATUS_ARCHIVED, 0),
            'inactive' => (int) $rows->get(Article::STATUS_INACTIVE, 0),
            'expired' => $expired,
        ];
    }

    public function payload(
        Request $request,
        array $allowedExtracurricularIds,
        bool $allowsGeneralContent,
        ?Article $article = null
    ): array {
        $request->merge([
            'title' => trim((string) $request->input('title')),
            'excerpt' => trim((string) $request->input('excerpt')),
            'slug' => trim((string) $request->input('slug')),
        ]);

        $submitAction = (string) $request->input('submit_action', 'draft');
        $status = (string) $request->input('publication_status', Article::STATUS_DRAFT);
        $requiresCompleteContent = in_array($submitAction, ['publish', 'preview'], true)
            || in_array($status, [Article::STATUS_PUBLISHED, Article::STATUS_SCHEDULED], true);
        $activityRule = $allowsGeneralContent
            ? ['nullable', 'integer', Rule::in($allowedExtracurricularIds)]
            : ['required', 'integer', Rule::in($allowedExtracurricularIds)];

        $validated = $request->validate([
            '_active_tab' => ['nullable', 'string'],
            'submit_action' => ['nullable', Rule::in(['draft', 'preview', 'publish'])],
            'title' => ['required', 'string', 'min:8', 'max:255'],
            'slug' => ['nullable', 'string', 'min:4', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('articles', 'slug')->ignore($article?->id)],
            'content_category' => ['required', Rule::in(array_keys(Article::contentCategories()))],
            'excerpt' => ['nullable', 'string', 'max:320'],
            'content' => [$requiresCompleteContent ? 'required' : 'nullable', 'string'],
            'extracurricular_id' => $activityRule,
            'publication_status' => ['required', Rule::in(array_keys(Article::publicationStatuses()))],
            'publish_date' => ['nullable', 'date'],
            'publish_time' => ['nullable', 'date_format:H:i'],
            'expires_date' => ['nullable', 'date'],
            'expires_time' => ['nullable', 'date_format:H:i'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'image_alt_text' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=5000,max_height=5000'],
        ], [
            'title.min' => 'Judul minimal 8 karakter.',
            'excerpt.required' => 'Ringkasan wajib diisi sebelum konten dipublikasikan.',
            'content.required' => 'Isi konten wajib diisi sebelum dipublikasikan.',
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
            'extracurricular_id.in' => 'Kegiatan tersebut tidak berada dalam cakupan akses Anda.',
        ]);

        $content = Article::sanitizeHtml($validated['content'] ?? '');
        if ($requiresCompleteContent && blank(trim(strip_tags($content)))) {
            throw ValidationException::withMessages(['content' => 'Isi konten wajib diisi sebelum dipublikasikan.']);
        }

        $publishAt = $this->resolvePublishAt($validated, $status);
        $expiresAt = $this->resolveExpiresAt($validated);
        if ($status === Article::STATUS_SCHEDULED) {
            if (! $publishAt) {
                throw ValidationException::withMessages(['publish_date' => 'Tanggal dan jam tayang wajib diisi.']);
            }
            if ($publishAt->isPast()) {
                throw ValidationException::withMessages(['publish_date' => 'Jadwal tayang harus setelah waktu sekarang.']);
            }
        }
        if ($status === Article::STATUS_PUBLISHED && $publishAt?->isFuture()) {
            throw ValidationException::withMessages([
                'publication_status' => 'Gunakan status dijadwalkan untuk tanggal tayang yang akan datang.',
            ]);
        }
        if ($expiresAt && $publishAt && $expiresAt->lte($publishAt)) {
            throw ValidationException::withMessages(['expires_date' => 'Tanggal berakhir harus setelah tanggal tayang.']);
        }

        $imagePath = $article?->image_path;
        $imageName = $article?->image_name;
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imagePath = app(UploadedImageOptimizer::class)->store(
                $imageFile,
                storage_path('app/public/articles'),
                'articles',
                Str::slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME))
            );
            $imageName = $imageFile->getClientOriginalName();
        }

        $slug = filled($validated['slug'] ?? null)
            ? Str::slug($validated['slug'])
            : ($article?->slug ?: Article::uniqueSlugFromTitle($validated['title'], $article?->id));

        return [
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => filled($validated['excerpt'] ?? null)
                ? $validated['excerpt']
                : Str::limit(trim(strip_tags($content)), 220),
            'content' => $content,
            'content_category' => $validated['content_category'],
            'extracurricular_id' => isset($validated['extracurricular_id']) ? (int) $validated['extracurricular_id'] : null,
            'published_by' => auth()->id(),
            'image_path' => $imagePath,
            'image_name' => $imageName,
            'image_alt_text' => trim($validated['image_alt_text'] ?? '') ?: $validated['title'],
            'meta_description' => trim($validated['meta_description'] ?? '')
                ?: Str::limit(trim(strip_tags($validated['excerpt'] ?? $content)), 160),
            'publication_status' => $status,
            'publish_at' => $publishAt,
            'expires_at' => $expiresAt,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'is_active' => $status !== Article::STATUS_INACTIVE,
        ];
    }

    public function duplicate(Article $article): Article
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

        return $duplicate;
    }

    public function publish(Article $article): void
    {
        if (blank($article->excerpt) || blank(trim(strip_tags($article->content)))) {
            throw ValidationException::withMessages([
                'publication_status' => 'Konten belum dapat dipublikasikan karena ringkasan atau isi belum lengkap.',
            ]);
        }

        $wasVisible = $article->is_publicly_visible;
        $article->update([
            'publication_status' => Article::STATUS_PUBLISHED,
            'publish_at' => $article->publish_at && $article->publish_at->isPast() ? $article->publish_at : now(),
            'is_active' => true,
        ]);

        if (! $wasVisible) {
            $this->notifyAudience($article);
        }
    }

    public function notifyAudience(Article $article): void
    {
        if (! $article->is_publicly_visible) {
            return;
        }

        $users = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->where('is_active', true)
            ->when($article->extracurricular_id, function (Builder $query, int $extracurricularId): void {
                $query->whereHas('student.registrations', fn (Builder $registrations) => $registrations
                    ->where('status', Registration::STATUS_APPROVED)
                    ->where('extracurricular_id', $extracurricularId));
            })
            ->with(['notificationPreference', 'pushSubscriptions'])
            ->get();

        app(NotificationCenter::class)->notifyUsers($users, [
            'title' => 'Berita sekolah baru',
            'message' => $article->title,
            'url' => route('public.articles.show', $article->slug),
            'category' => NotificationPreference::CATEGORY_SCHOOL_NEWS,
            'icon' => 'bi-newspaper',
            'tag' => 'article-'.$article->id,
        ]);
    }

    public function delete(Article $article): void
    {
        if (! in_array($article->publication_status, [Article::STATUS_DRAFT, Article::STATUS_ARCHIVED], true)) {
            throw ValidationException::withMessages([
                'article' => 'Konten aktif tidak dapat dihapus permanen. Arsipkan terlebih dahulu untuk menjaga riwayat publikasi.',
            ]);
        }

        $imagePath = $article->image_path;
        DB::transaction(fn () => $article->delete());
        $this->deleteImageIfUnused($imagePath);
    }

    public function deleteReplacedImage(?string $oldPath, ?string $newPath): void
    {
        if ($oldPath && $oldPath !== $newPath) {
            $this->deleteImageIfUnused($oldPath);
        }
    }

    public static function managementStatuses(): array
    {
        return [
            Article::STATUS_DRAFT => 'Draft',
            Article::STATUS_SCHEDULED => 'Dijadwalkan',
            Article::STATUS_PUBLISHED => 'Dipublikasikan',
            Article::STATUS_ARCHIVED => 'Diarsipkan',
            Article::STATUS_INACTIVE => 'Dinonaktifkan',
            'expired' => 'Berakhir',
        ];
    }

    private function deleteImageIfUnused(?string $path): void
    {
        if ($path && ! Article::query()->where('image_path', $path)->exists()) {
            Storage::disk('public')->delete($path);
        }
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
        if (empty($validated['expires_date'])) {
            return null;
        }

        $expiresTime = filled($validated['expires_time'] ?? null) ? $validated['expires_time'] : '23:59';

        return Carbon::parse($validated['expires_date'].' '.$expiresTime);
    }
}
