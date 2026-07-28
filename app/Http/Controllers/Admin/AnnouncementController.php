<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Extracurricular;
use App\Models\NotificationPreference;
use App\Models\Registration;
use App\Support\NotificationCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::with(['publisher', 'extracurricular'])
                ->latest()
                ->paginate(10),
            'extracurriculars' => Extracurricular::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $announcement = Announcement::create([
            ...$validated,
            'published_by' => auth()->id(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $students = Registration::query()
            ->with('student.user')
            ->where('status', 'approved')
            ->when($announcement->extracurricular_id, fn ($query, $id) => $query->where('extracurricular_id', $id))
            ->get()
            ->pluck('student.user')
            ->filter();

        app(NotificationCenter::class)->notifyUsers($students, [
            'title' => 'Pengumuman baru tersedia',
            'message' => $announcement->title,
            'url' => route('public.announcements'),
            'category' => NotificationPreference::CATEGORY_ANNOUNCEMENTS,
            'icon' => 'bi-megaphone',
            'tag' => 'announcement-'.$announcement->id,
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
