<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\AnnouncementManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(private readonly AnnouncementManager $manager)
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

        $baseQuery = Announcement::query()->where('published_by', auth()->id());
        $announcements = $this->manager
            ->applyFilters((clone $baseQuery)->with(['publisher:id,name,role', 'extracurricular:id,name']), $filters)
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('coach.announcements.index', [
            'announcements' => $announcements,
            'extracurriculars' => $extracurriculars,
            'statistics' => $this->manager->statistics($baseQuery),
            'filters' => $filters,
            'canTargetAllStudents' => false,
            'routePrefix' => 'coach.announcements',
            'roleLabel' => 'Pembina',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $announcement = Announcement::create($this->manager->payload(
            $request,
            $coach->extracurriculars()->pluck('extracurriculars.id')->all(),
            false
        ));
        $this->manager->notifyAudience($announcement);

        return redirect()->route('coach.announcements.index')
            ->with('success', 'Pengumuman berhasil disimpan.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('manage', $announcement);
        $coach = auth()->user()->coach;

        return view('coach.announcements.edit', [
            'announcement' => $announcement,
            'extracurriculars' => $coach->extracurriculars()->orderBy('name')->get(['extracurriculars.id', 'name']),
            'canTargetAllStudents' => false,
            'routePrefix' => 'coach.announcements',
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('manage', $announcement);
        $coach = auth()->user()->coach;
        $wasDeliverable = $this->manager->isDeliverable($announcement);
        $oldAttachmentPath = $announcement->attachment_path;
        $announcement->update($this->manager->payload(
            $request,
            $coach->extracurriculars()->pluck('extracurriculars.id')->all(),
            false,
            $announcement
        ));
        $this->manager->deleteReplacedAttachment($oldAttachmentPath, $announcement->attachment_path);

        if (! $wasDeliverable) {
            $this->manager->notifyAudience($announcement);
        }

        return redirect()->route('coach.announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function publish(Announcement $announcement): RedirectResponse
    {
        $this->authorize('manage', $announcement);
        $wasDeliverable = $this->manager->isDeliverable($announcement);
        $announcement->update([
            'is_active' => true,
            'publication_status' => Announcement::STATUS_PUBLISHED,
            'publish_at' => now(),
        ]);

        if (! $wasDeliverable) {
            $this->manager->notifyAudience($announcement);
        }

        return back()->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function deactivate(Announcement $announcement): RedirectResponse
    {
        $this->authorize('manage', $announcement);
        $announcement->update([
            'is_active' => false,
            'publication_status' => Announcement::STATUS_INACTIVE,
        ]);

        return back()->with('success', 'Pengumuman berhasil dinonaktifkan.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('manage', $announcement);
        $this->manager->deleteDraft($announcement);

        return redirect()->route('coach.announcements.index')
            ->with('success', 'Draft pengumuman berhasil dihapus.');
    }
}
