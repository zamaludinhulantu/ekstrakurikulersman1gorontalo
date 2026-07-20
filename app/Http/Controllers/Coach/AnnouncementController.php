<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $allowedExtracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id');
        $search = $request->string('search')->toString();
        $extracurricularId = $request->string('extracurricular_id')->toString();
        $status = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();
        $period = $request->string('period')->toString();
        $activeTab = $request->string('tab')->toString() ?: old('active_tab', 'list');
        $hasFilters = filled($search) || filled($extracurricularId) || filled($status) || filled($priority) || filled($period) || $request->has('page');

        if (! in_array($activeTab, ['list', 'create'], true)) {
            $activeTab = 'list';
        }

        $supportsEnhancedSchema = Announcement::supportsEnhancedSchema();

        $announcements = Announcement::with(['publisher', 'extracurricular'])
            ->where('published_by', auth()->id())
            ->when($search, fn ($query, $value) => $query->where('title', 'like', "%{$value}%"))
            ->when($extracurricularId === 'all_managed', fn ($query) => $query->whereNull('extracurricular_id'))
            ->when($extracurricularId && $extracurricularId !== 'all_managed', fn ($query, $value) => $query->where('extracurricular_id', $value))
            ->when($supportsEnhancedSchema && $status, function ($query, $value): void {
                if ($value === 'expired') {
                    $query->whereNotNull('ends_at')->where('ends_at', '<', now());
                    return;
                }

                if ($value === Announcement::STATUS_INACTIVE) {
                    $query->where(function ($subQuery): void {
                        $subQuery->where('publication_status', Announcement::STATUS_INACTIVE)
                            ->orWhere('is_active', false);
                    });
                    return;
                }

                $query->where('publication_status', $value);
            })
            ->when($supportsEnhancedSchema && $priority, fn ($query, $value) => $query->where('priority', $value))
            ->when($supportsEnhancedSchema && $period === 'today', fn ($query) => $query->whereDate('publish_at', Carbon::today()))
            ->when($supportsEnhancedSchema && $period === 'week', fn ($query) => $query->whereDate('publish_at', '>=', Carbon::today()->subDays(7)))
            ->when($supportsEnhancedSchema, fn ($query) => $query->latest('publish_at'), fn ($query) => $query->latest())
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('coach.announcements.index', [
            'announcements' => $announcements,
            'extracurriculars' => $coach->extracurriculars()->orderBy('name')->get(),
            'activeTab' => $activeTab,
            'hasFilters' => $hasFilters,
            'search' => $search,
            'extracurricularId' => $extracurricularId,
            'status' => $status,
            'priority' => $priority,
            'period' => $period,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $announcement = Announcement::create($this->validatedPayload($request, $coach->extracurriculars()->pluck('extracurriculars.id')->all()));

        return redirect()->route('coach.announcements.index')->with('success', 'Pengumuman berhasil disimpan.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('manageByCoach', $announcement);
        $coach = auth()->user()->coach;

        return view('coach.announcements.edit', [
            'announcement' => $announcement,
            'extracurriculars' => $coach->extracurriculars()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('manageByCoach', $announcement);
        $coach = auth()->user()->coach;

        $payload = $this->validatedPayload($request, $coach->extracurriculars()->pluck('extracurriculars.id')->all(), $announcement);
        $announcement->update($payload);

        return redirect()->route('coach.announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function publish(Announcement $announcement): RedirectResponse
    {
        $this->authorize('manageByCoach', $announcement);

        $payload = ['is_active' => true];
        if (Announcement::supportsEnhancedSchema()) {
            $payload['publication_status'] = Announcement::STATUS_PUBLISHED;
            $payload['publish_at'] = now();
        }

        $announcement->update($payload);

        return redirect()->route('coach.announcements.index')->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function deactivate(Announcement $announcement): RedirectResponse
    {
        $this->authorize('manageByCoach', $announcement);

        $payload = ['is_active' => false];
        if (Announcement::supportsEnhancedSchema()) {
            $payload['publication_status'] = Announcement::STATUS_INACTIVE;
        }

        $announcement->update($payload);

        return redirect()->route('coach.announcements.index')->with('success', 'Pengumuman berhasil dinonaktifkan.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('manageByCoach', $announcement);

        if (Announcement::supportsEnhancedSchema() && $announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }

        $announcement->delete();

        return redirect()->route('coach.announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function validatedPayload(Request $request, array $allowedExtracurricularIds, ?Announcement $announcement = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'extracurricular_id' => ['nullable', Rule::in(array_map('strval', $allowedExtracurricularIds))],
            'target_scope' => ['nullable', Rule::in(['single', 'all_managed'])],
            'priority' => ['nullable', Rule::in([Announcement::PRIORITY_NORMAL, Announcement::PRIORITY_IMPORTANT, Announcement::PRIORITY_URGENT])],
            'publication_action' => ['nullable', Rule::in([Announcement::STATUS_DRAFT, Announcement::STATUS_PUBLISHED, Announcement::STATUS_SCHEDULED])],
            'publish_date' => ['nullable', 'date'],
            'publish_time' => ['nullable', 'date_format:H:i'],
            'ends_at_date' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'confirm_all_managed' => ['nullable', 'boolean'],
        ]);

        $rawExtracurricularId = $request->input('extracurricular_id');
        $validated['target_scope'] = $validated['target_scope']
            ?? (($rawExtracurricularId !== null && $rawExtracurricularId !== '') ? 'single' : 'all_managed');
        $validated['priority'] = $validated['priority'] ?? Announcement::PRIORITY_NORMAL;
        $validated['publication_action'] = $validated['publication_action']
            ?? ($request->boolean('is_active', true) ? Announcement::STATUS_PUBLISHED : Announcement::STATUS_DRAFT);
        $supportsEnhancedSchema = Announcement::supportsEnhancedSchema();

        if ($validated['target_scope'] === 'all_managed') {
            $validated['extracurricular_id'] = null;
        }

        if ($validated['target_scope'] === 'single' && empty($validated['extracurricular_id'])) {
            throw ValidationException::withMessages([
                'extracurricular_id' => 'Pilih ekstrakurikuler tujuan.',
            ]);
        }

        $publishAt = now();
        if ($supportsEnhancedSchema) {
            if ($validated['publication_action'] === Announcement::STATUS_DRAFT) {
                $publishAt = null;
            } elseif ($validated['publication_action'] === Announcement::STATUS_SCHEDULED) {
                $publishDate = $validated['publish_date'] ?? null;
                $publishTime = $validated['publish_time'] ?? null;
                if (! $publishDate || ! $publishTime) {
                    throw ValidationException::withMessages([
                        'publish_date' => 'Tanggal dan jam tayang wajib diisi untuk jadwal publikasi.',
                    ]);
                }

                $publishAt = Carbon::parse($publishDate.' '.$publishTime);
            }
        }

        $endsAt = null;
        if ($supportsEnhancedSchema && ! empty($validated['ends_at_date'])) {
            $endsAt = Carbon::parse($validated['ends_at_date'])->endOfDay();
        }

        $attachmentPath = $announcement?->attachment_path;
        $attachmentName = $announcement?->attachment_name;
        if ($supportsEnhancedSchema && $request->hasFile('attachment')) {
            if ($announcement?->attachment_path) {
                Storage::disk('public')->delete($announcement->attachment_path);
            }

            $storedFile = $request->file('attachment');
            $attachmentPath = $storedFile->store('announcements', 'public');
            $attachmentName = $storedFile->getClientOriginalName();
        }

        $payload = [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'extracurricular_id' => $validated['extracurricular_id'] ?? null,
            'published_by' => auth()->id(),
            'is_active' => $validated['publication_action'] !== Announcement::STATUS_DRAFT,
        ];

        if ($supportsEnhancedSchema) {
            $payload['priority'] = $validated['priority'];
            $payload['publication_status'] = $validated['publication_action'];
            $payload['publish_at'] = $publishAt;
            $payload['ends_at'] = $endsAt;
            $payload['attachment_path'] = $attachmentPath;
            $payload['attachment_name'] = $attachmentName;
        }

        return $payload;
    }
}
