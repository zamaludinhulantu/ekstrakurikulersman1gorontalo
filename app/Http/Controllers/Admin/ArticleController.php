<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Extracurricular;
use App\Models\User;
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
        $filters = $this->manager->filters($request);
        $baseQuery = Article::query();
        $articles = $this->manager->applyFilters(
            (clone $baseQuery)
                ->select($this->listColumns())
                ->with(['publisher:id,name,role', 'extracurricular:id,name']),
            $filters
        )->paginate($filters['per_page'])->withQueryString();

        return view('admin.articles.index', $this->viewData(
            $request,
            $articles,
            $filters,
            $this->manager->statistics($baseQuery),
            Extracurricular::query()->orderBy('name')->get(['id', 'name'])
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $article = Article::query()->create($this->manager->payload(
            $request,
            Extracurricular::query()->pluck('id')->all(),
            true
        ));
        $this->completePublication($request, $article, false);

        return $this->redirectAfterSave($request, $article, true);
    }

    public function edit(Article $article): View
    {
        $this->authorize('manage', $article);

        return view('admin.articles.edit', [
            'article' => $article,
            'extracurriculars' => Extracurricular::query()->orderBy('name')->get(['id', 'name']),
            'contentCategories' => Article::contentCategories(),
            'publicationStatuses' => Article::publicationStatuses(),
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('manage', $article);
        $wasVisible = $article->is_publicly_visible;
        $oldImagePath = $article->image_path;
        $article->update($this->manager->payload(
            $request,
            Extracurricular::query()->pluck('id')->all(),
            true,
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

        return redirect()->route('admin.articles.edit', $duplicate)
            ->with('success', 'Konten berhasil diduplikasi sebagai draft.');
    }

    public function publish(Article $article): RedirectResponse
    {
        $this->authorize('manage', $article);
        $this->manager->publish($article);

        return redirect()->route('admin.articles.index')->with('success', 'Konten berhasil dipublikasikan.');
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

        return redirect()->route('admin.articles.index')->with('success', 'Publikasi ditarik kembali ke draft.');
    }

    public function archive(Article $article): RedirectResponse
    {
        $this->authorize('manage', $article);
        $article->update(['publication_status' => Article::STATUS_ARCHIVED, 'is_active' => true]);

        return redirect()->route('admin.articles.index')->with('success', 'Konten berhasil diarsipkan.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->authorize('manage', $article);
        $this->manager->delete($article);

        return redirect()->route('admin.articles.index')->with('success', 'Konten berhasil dihapus.');
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
            return redirect()->route('admin.articles.preview', $article)->with('success', $message);
        }

        return redirect()->route('admin.articles.index', ['tab' => 'list'])
            ->with('success', $request->input('submit_action') === 'publish' ? 'Konten berhasil dipublikasikan.' : $message);
    }

    private function viewData(Request $request, $articles, array $filters, array $statistics, $extracurriculars): array
    {
        return [
            'articles' => $articles,
            'article' => null,
            'tab' => old('_active_tab', $filters['tab']),
            'statistics' => $statistics,
            'extracurriculars' => $extracurriculars,
            'authors' => User::query()->whereHas('articles')->orderBy('name')->get(['id', 'name', 'role']),
            'filters' => $filters,
            'contentCategories' => Article::contentCategories(),
            'publicationStatuses' => ArticleManager::managementStatuses(),
            'routePrefix' => 'admin.articles',
            'allowsGeneralContent' => true,
            'listUrl' => route('admin.articles.index', $request->except(['page', 'tab']) + ['tab' => 'list']),
            'writeUrl' => route('admin.articles.index', $request->except(['page', 'tab']) + ['tab' => 'write']),
        ];
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
