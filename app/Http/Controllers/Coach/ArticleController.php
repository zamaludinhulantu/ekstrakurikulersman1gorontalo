<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Support\ArticleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(private readonly ArticleManager $manager)
    {
    }

    public function index(Request $request): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurriculars = $coach->extracurriculars()->orderBy('name')->get(['extracurriculars.id', 'name']);
        $allowedIds = $extracurriculars->pluck('id');
        $filters = $this->manager->filters($request);
        if ($filters['extracurricular_id'] && ! $allowedIds->contains($filters['extracurricular_id'])) {
            abort(403, 'Kegiatan tersebut bukan binaan Anda.');
        }
        if ($filters['author_id'] && $filters['author_id'] !== auth()->id()) {
            abort(403);
        }

        $baseQuery = Article::query()->where('published_by', auth()->id());
        $articles = $this->manager->applyFilters(
            (clone $baseQuery)
                ->select($this->listColumns())
                ->with(['publisher:id,name,role', 'extracurricular:id,name']),
            $filters
        )->paginate($filters['per_page'])->withQueryString();

        return view('coach.articles.index', [
            'articles' => $articles,
            'article' => null,
            'tab' => old('_active_tab', $filters['tab']),
            'statistics' => $this->manager->statistics($baseQuery),
            'extracurriculars' => $extracurriculars,
            'authors' => collect([auth()->user()]),
            'filters' => $filters,
            'contentCategories' => Article::contentCategories(),
            'publicationStatuses' => ArticleManager::managementStatuses(),
            'routePrefix' => 'coach.articles',
            'allowsGeneralContent' => false,
            'listUrl' => route('coach.articles.index', $request->except(['page', 'tab']) + ['tab' => 'list']),
            'writeUrl' => route('coach.articles.index', $request->except(['page', 'tab']) + ['tab' => 'write']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');
        $article = Article::query()->create($this->manager->payload(
            $request,
            $coach->extracurriculars()->pluck('extracurriculars.id')->all(),
            false
        ));
        $this->completePublication($request, $article, false);

        return $this->redirectAfterSave($request, $article, true);
    }

    public function edit(Article $article): View
    {
        $this->authorize('manage', $article);
        $coach = auth()->user()->coach;

        return view('coach.articles.edit', [
            'article' => $article,
            'extracurriculars' => $coach->extracurriculars()->orderBy('name')->get(['extracurriculars.id', 'name']),
            'contentCategories' => Article::contentCategories(),
            'publicationStatuses' => Article::publicationStatuses(),
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('manage', $article);
        $coach = auth()->user()->coach;
        $wasVisible = $article->is_publicly_visible;
        $oldImagePath = $article->image_path;
        $article->update($this->manager->payload(
            $request,
            $coach->extracurriculars()->pluck('extracurriculars.id')->all(),
            false,
            $article
        ));
        $this->manager->deleteReplacedImage($oldImagePath, $article->image_path);
        $this->completePublication($request, $article, $wasVisible);

        return $this->redirectAfterSave($request, $article, false);
    }

    public function preview(Article $article): View
    {
        $this->authorize('manage', $article);

        return view('public.article-detail', [
            'article' => $article->load(['publisher', 'extracurricular']),
            'relatedArticles' => collect(),
            'previewMode' => true,
        ]);
    }

    public function duplicate(Article $article): RedirectResponse
    {
        $this->authorize('manage', $article);
        $duplicate = $this->manager->duplicate($article);

        return redirect()->route('coach.articles.edit', $duplicate)
            ->with('success', 'Konten berhasil diduplikasi sebagai draft.');
    }

    public function publish(Article $article): RedirectResponse
    {
        $this->authorize('manage', $article);
        $this->manager->publish($article);

        return redirect()->route('coach.articles.index')->with('success', 'Konten berhasil dipublikasikan.');
    }

    public function unpublish(Article $article): RedirectResponse
    {
        $this->authorize('manage', $article);
        $article->update([
            'publication_status' => Article::STATUS_DRAFT,
            'publish_at' => null,
            'expires_at' => null,
            'is_active' => true,
        ]);

        return redirect()->route('coach.articles.index')->with('success', 'Publikasi ditarik kembali ke draft.');
    }

    public function archive(Article $article): RedirectResponse
    {
        $this->authorize('manage', $article);
        $article->update(['publication_status' => Article::STATUS_ARCHIVED, 'is_active' => true]);

        return redirect()->route('coach.articles.index')->with('success', 'Konten berhasil diarsipkan.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->authorize('manage', $article);
        $this->manager->delete($article);

        return redirect()->route('coach.articles.index')->with('success', 'Konten berhasil dihapus.');
    }

    private function completePublication(Request $request, Article $article, bool $wasVisible): void
    {
        if ($request->input('submit_action') === 'publish') {
            if (! $wasVisible && $article->is_publicly_visible) {
                $this->manager->notifyAudience($article);
            } else {
                $this->manager->publish($article);
            }

            return;
        }
        if (! $wasVisible && $article->is_publicly_visible) {
            $this->manager->notifyAudience($article);
        }
    }

    private function redirectAfterSave(Request $request, Article $article, bool $created): RedirectResponse
    {
        $message = $created ? 'Konten berhasil disimpan.' : 'Konten berhasil diperbarui.';
        if ($request->input('submit_action') === 'preview') {
            return redirect()->route('coach.articles.preview', $article)->with('success', $message);
        }

        return redirect()->route('coach.articles.index', ['tab' => 'list'])
            ->with('success', $request->input('submit_action') === 'publish' ? 'Konten berhasil dipublikasikan.' : $message);
    }

    private function listColumns(): array
    {
        return [
            'id', 'title', 'slug', 'excerpt', 'content_category', 'extracurricular_id', 'published_by',
            'image_path', 'image_alt_text', 'publication_status', 'publish_at', 'expires_at',
            'is_featured', 'is_active', 'created_at', 'updated_at',
        ];
    }
}
