<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        return view('coach.announcements.index', [
            'announcements' => Announcement::with(['publisher', 'extracurricular.coaches.user'])
                ->where('published_by', auth()->id())
                ->latest()
                ->paginate(10),
            'extracurriculars' => $coach->extracurriculars()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['extracurricular_id']) && ! $coach->extracurriculars()->whereKey($validated['extracurricular_id'])->exists()) {
            return back()->with('error', 'Anda hanya dapat membuat pengumuman untuk ekstrakurikuler yang dibina.');
        }

        Announcement::create([
            ...$validated,
            'published_by' => auth()->id(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('coach.announcements.index')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('manageByCoach', $announcement);
        $announcement->delete();

        return redirect()->route('coach.announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
