<?php

namespace App\Support;

use App\Models\Announcement;
use Illuminate\Support\Facades\DB;

class ScheduledAnnouncementPublisher
{
    public function __construct(private readonly AnnouncementManager $manager)
    {
    }

    public function publishDue(): int
    {
        $published = 0;

        Announcement::query()
            ->where('is_active', true)
            ->where('publication_status', Announcement::STATUS_SCHEDULED)
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->orderBy('id')
            ->chunkById(50, function ($announcements) use (&$published): void {
                foreach ($announcements as $announcement) {
                    $claimed = DB::transaction(function () use ($announcement): bool {
                        return Announcement::query()
                            ->whereKey($announcement->id)
                            ->where('publication_status', Announcement::STATUS_SCHEDULED)
                            ->update(['publication_status' => Announcement::STATUS_PUBLISHED]) === 1;
                    });

                    if (! $claimed) {
                        continue;
                    }

                    $announcement->refresh();
                    $this->manager->notifyAudience($announcement);
                    $published++;
                }
            });

        return $published;
    }
}
