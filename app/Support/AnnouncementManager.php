<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\NotificationPreference;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AnnouncementManager
{
    public const TITLE_MAX_LENGTH = 120;

    public const CONTENT_MAX_LENGTH = 5000;

    public function filters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'extracurricular_id' => ['nullable', 'integer', 'exists:extracurriculars,id'],
            'target' => ['nullable', Rule::in(['all', 'activity'])],
            'status' => ['nullable', Rule::in([
                Announcement::STATUS_DRAFT,
                Announcement::STATUS_SCHEDULED,
                Announcement::STATUS_PUBLISHED,
                Announcement::STATUS_INACTIVE,
                'expired',
            ])],
            'priority' => ['nullable', Rule::in([
                Announcement::PRIORITY_NORMAL,
                Announcement::PRIORITY_IMPORTANT,
                Announcement::PRIORITY_URGENT,
            ])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort' => ['nullable', Rule::in(['title', 'publication_status', 'created_at', 'updated_at', 'publish_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50])],
            'form' => ['nullable', Rule::in(['create'])],
        ]);

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'extracurricular_id' => isset($validated['extracurricular_id'])
                ? (int) $validated['extracurricular_id']
                : null,
            'target' => (string) ($validated['target'] ?? ''),
            'status' => (string) ($validated['status'] ?? ''),
            'priority' => (string) ($validated['priority'] ?? ''),
            'date_from' => (string) ($validated['date_from'] ?? ''),
            'date_to' => (string) ($validated['date_to'] ?? ''),
            'sort' => (string) ($validated['sort'] ?? 'updated_at'),
            'direction' => (string) ($validated['direction'] ?? 'desc'),
            'per_page' => (int) ($validated['per_page'] ?? 10),
            'form' => (string) ($validated['form'] ?? ''),
        ];
    }

    public function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($filters['target'] === 'all', fn (Builder $query) => $query->whereNull('extracurricular_id'))
            ->when($filters['target'] === 'activity', fn (Builder $query) => $query->whereNotNull('extracurricular_id'))
            ->when($filters['extracurricular_id'], fn (Builder $query, int $id) => $query->where('extracurricular_id', $id))
            ->when($filters['status'], function (Builder $query, string $status): void {
                if ($status === 'expired') {
                    $query->whereNotNull('ends_at')->where('ends_at', '<', now());

                    return;
                }

                if ($status === Announcement::STATUS_INACTIVE) {
                    $query->where(function (Builder $statusQuery): void {
                        $statusQuery->where('publication_status', Announcement::STATUS_INACTIVE)
                            ->orWhere(function (Builder $legacyInactive): void {
                                $legacyInactive->where('is_active', false)
                                    ->where('publication_status', '!=', Announcement::STATUS_DRAFT);
                            })
                            ->orWhere(fn (Builder $expired) => $expired
                                ->whereNotNull('ends_at')
                                ->where('ends_at', '<', now()));
                    });

                    return;
                }

                if ($status === Announcement::STATUS_PUBLISHED) {
                    $query->where('is_active', true)
                        ->where(function (Builder $published): void {
                            $published->where('publication_status', Announcement::STATUS_PUBLISHED)
                                ->orWhere(fn (Builder $due) => $due
                                    ->where('publication_status', Announcement::STATUS_SCHEDULED)
                                    ->where('publish_at', '<=', now()));
                        })
                        ->where(fn (Builder $active) => $active
                            ->whereNull('ends_at')
                            ->orWhere('ends_at', '>=', now()));

                    return;
                }

                if ($status === Announcement::STATUS_SCHEDULED) {
                    $query->where('publication_status', Announcement::STATUS_SCHEDULED)
                        ->where('publish_at', '>', now());

                    return;
                }

                $query->where('publication_status', $status);
            })
            ->when($filters['priority'], fn (Builder $query, string $priority) => $query->where('priority', $priority))
            ->when($filters['date_from'], fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'], fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->orderBy($filters['sort'], $filters['direction'])
            ->orderByDesc('id');
    }

    public function statistics(Builder $baseQuery): array
    {
        $now = now();

        return [
            'total' => (clone $baseQuery)->count(),
            'published' => (clone $baseQuery)->where('is_active', true)
                ->where(function (Builder $query) use ($now): void {
                    $query->where('publication_status', Announcement::STATUS_PUBLISHED)
                        ->orWhere(function (Builder $scheduled) use ($now): void {
                            $scheduled->where('publication_status', Announcement::STATUS_SCHEDULED)
                                ->where('publish_at', '<=', $now);
                        });
                })
                ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
                ->count(),
            'draft' => (clone $baseQuery)->where('publication_status', Announcement::STATUS_DRAFT)->count(),
            'scheduled' => (clone $baseQuery)->where('publication_status', Announcement::STATUS_SCHEDULED)
                ->where('publish_at', '>', $now)
                ->count(),
            'inactive' => (clone $baseQuery)->where(function (Builder $query) use ($now): void {
                $query->where(function (Builder $inactive): void {
                    $inactive->where('is_active', false)
                        ->where('publication_status', '!=', Announcement::STATUS_DRAFT);
                })
                    ->orWhere('publication_status', Announcement::STATUS_INACTIVE)
                    ->orWhere(fn (Builder $expired) => $expired->whereNotNull('ends_at')->where('ends_at', '<', $now));
            })->count(),
        ];
    }

    public function payload(
        Request $request,
        array $allowedExtracurricularIds,
        bool $canTargetAllStudents,
        ?Announcement $announcement = null
    ): array {
        $request->merge([
            'title' => trim((string) $request->input('title')),
            'content' => trim((string) $request->input('content')),
            'target_scope' => $request->input(
                'target_scope',
                $canTargetAllStudents && ! $request->filled('extracurricular_id') ? 'all_students' : 'single'
            ),
            'priority' => $request->input('priority', Announcement::PRIORITY_NORMAL),
            'publication_action' => $request->input(
                'publication_action',
                $request->boolean('is_active', true)
                    ? Announcement::STATUS_PUBLISHED
                    : Announcement::STATUS_DRAFT
            ),
        ]);

        $allowedIds = array_map('strval', $allowedExtracurricularIds);
        $targetScopes = $canTargetAllStudents ? ['all_students', 'single'] : ['single'];
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:'.self::TITLE_MAX_LENGTH],
            'content' => ['required', 'string', 'max:'.self::CONTENT_MAX_LENGTH],
            'target_scope' => ['required', Rule::in($targetScopes)],
            'extracurricular_id' => ['nullable', Rule::in($allowedIds)],
            'priority' => ['required', Rule::in([
                Announcement::PRIORITY_NORMAL,
                Announcement::PRIORITY_IMPORTANT,
                Announcement::PRIORITY_URGENT,
            ])],
            'publication_action' => ['required', Rule::in([
                Announcement::STATUS_DRAFT,
                Announcement::STATUS_PUBLISHED,
                Announcement::STATUS_SCHEDULED,
            ])],
            'publish_date' => ['nullable', 'date'],
            'publish_time' => ['nullable', 'date_format:H:i'],
            'ends_at_date' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        if ($validated['target_scope'] === 'single' && empty($validated['extracurricular_id'])) {
            throw ValidationException::withMessages([
                'extracurricular_id' => 'Pilih kegiatan tujuan pengumuman.',
            ]);
        }

        $publishAt = null;
        if ($validated['publication_action'] === Announcement::STATUS_PUBLISHED) {
            $publishAt = now();
        } elseif ($validated['publication_action'] === Announcement::STATUS_SCHEDULED) {
            if (empty($validated['publish_date']) || empty($validated['publish_time'])) {
                throw ValidationException::withMessages([
                    'publish_date' => 'Tanggal dan jam tayang wajib diisi.',
                ]);
            }

            $publishAt = Carbon::parse($validated['publish_date'].' '.$validated['publish_time']);
            if ($publishAt->isPast()) {
                throw ValidationException::withMessages([
                    'publish_date' => 'Jadwal publikasi harus setelah waktu sekarang.',
                ]);
            }
        }

        $endsAt = empty($validated['ends_at_date'])
            ? null
            : Carbon::parse($validated['ends_at_date'])->endOfDay();
        if ($endsAt && $publishAt && $endsAt->lt($publishAt)) {
            throw ValidationException::withMessages([
                'ends_at_date' => 'Tanggal berakhir tidak boleh sebelum waktu tayang.',
            ]);
        }

        $attachmentPath = $announcement?->attachment_path;
        $attachmentName = $announcement?->attachment_name;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('announcements', 'public');
            $attachmentName = $file->getClientOriginalName();
        }

        return [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'extracurricular_id' => $validated['target_scope'] === 'all_students'
                ? null
                : (int) $validated['extracurricular_id'],
            'published_by' => auth()->id(),
            'priority' => $validated['priority'],
            'publication_status' => $validated['publication_action'],
            'publish_at' => $publishAt,
            'ends_at' => $endsAt,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'is_active' => $validated['publication_action'] !== Announcement::STATUS_DRAFT,
        ];
    }

    public function isDeliverable(Announcement $announcement): bool
    {
        return $announcement->is_active
            && in_array($announcement->publication_status, [
                Announcement::STATUS_PUBLISHED,
                Announcement::STATUS_SCHEDULED,
            ], true)
            && (! $announcement->publish_at || $announcement->publish_at->isPast())
            && (! $announcement->ends_at || $announcement->ends_at->isFuture());
    }

    public function notifyAudience(Announcement $announcement): void
    {
        if (! $this->isDeliverable($announcement)) {
            return;
        }

        $users = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->where('is_active', true)
            ->when($announcement->extracurricular_id, function (Builder $query, int $extracurricularId): void {
                $query->whereHas('student.registrations', fn (Builder $registrations) => $registrations
                    ->where('status', Registration::STATUS_APPROVED)
                    ->where('extracurricular_id', $extracurricularId));
            })
            ->with(['notificationPreference', 'pushSubscriptions'])
            ->get();

        app(NotificationCenter::class)->notifyUsers($users, [
            'title' => 'Pengumuman baru tersedia',
            'message' => $announcement->title,
            'url' => route('public.announcements'),
            'category' => NotificationPreference::CATEGORY_ANNOUNCEMENTS,
            'icon' => 'bi-megaphone',
            'tag' => 'announcement-'.$announcement->id,
        ]);
    }

    public function deleteDraft(Announcement $announcement): void
    {
        if ($announcement->publication_status !== Announcement::STATUS_DRAFT) {
            throw ValidationException::withMessages([
                'announcement' => 'Pengumuman yang pernah ditayangkan tidak dapat dihapus. Nonaktifkan pengumuman untuk mempertahankan riwayat.',
            ]);
        }

        $attachmentPath = $announcement->attachment_path;
        $announcement->delete();

        if ($attachmentPath) {
            Storage::disk('public')->delete($attachmentPath);
        }
    }

    public function deleteReplacedAttachment(?string $oldPath, ?string $newPath): void
    {
        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}
