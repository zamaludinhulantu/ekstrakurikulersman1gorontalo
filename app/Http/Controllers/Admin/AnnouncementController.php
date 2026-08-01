<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Extracurricular;
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
        $filters = $this->manager->filters($request);
        $baseQuery = Announcement::query();
        $announcements = $this->manager
            ->applyFilters((clone $baseQuery)->with(['publisher:id,name,role', 'extracurricular:id,name']), $filters)
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('admin.announcements.index', [
            'announcements' => $announcements,
            'extracurriculars' => Extracurricular::query()->orderBy('name')->get(['id', 'name']),
            'statistics' => $this->manager->statistics($baseQuery),
            'filters' => $filters,
            'canTargetAllStudents' => true,
            'routePrefix' => 'admin.announcements',
            'roleLabel' => 'Admin/Kesiswaan',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $announcement = Announcement::create($this->manager->payload(
            $request,
            Extracurricular::query()->pluck('id')->all(),
            true
        ));
        $this->manager->notifyAudience($announcement);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil disimpan.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('manage', $announcement);

        return view('admin.announcements.edit', [
            'announcement' => $announcement,
            'extracurriculars' => Extracurricular::query()->orderBy('name')->get(['id', 'name']),
            'canTargetAllStudents' => true,
            'routePrefix' => 'admin.announcements',
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('manage', $announcement);
        $wasDeliverable = $this->manager->isDeliverable($announcement);
        $oldAttachmentPath = $announcement->attachment_path;
        $announcement->update($this->manager->payload(
            $request,
            Extracurricular::query()->pluck('id')->all(),
            true,
            $announcement
        ));
        $this->manager->deleteReplacedAttachment($oldAttachmentPath, $announcement->attachment_path);

        if (! $wasDeliverable) {
            $this->manager->notifyAudience($announcement);
        }

        return redirect()->route('admin.announcements.index')
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

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Draft pengumuman berhasil dihapus.');
    }
}
