<?php

namespace App\Support;

use App\Models\Article;
use Illuminate\Support\Facades\DB;

class ScheduledArticlePublisher
{
    public function __construct(private readonly ArticleManager $manager)
    {
    }

    public function publishDue(): int
    {
        $published = 0;

        Article::query()
            ->where('is_active', true)
            ->where('publication_status', Article::STATUS_SCHEDULED)
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->orderBy('id')
            ->chunkById(50, function ($articles) use (&$published): void {
                foreach ($articles as $article) {
                    $claimed = DB::transaction(fn (): bool => Article::query()
                        ->whereKey($article->id)
                        ->where('publication_status', Article::STATUS_SCHEDULED)
                        ->update(['publication_status' => Article::STATUS_PUBLISHED]) === 1);

                    if (! $claimed) {
                        continue;
                    }

                    $article->refresh();
                    $this->manager->notifyAudience($article);
                    $published++;
                }
            });

        return $published;
    }
}
