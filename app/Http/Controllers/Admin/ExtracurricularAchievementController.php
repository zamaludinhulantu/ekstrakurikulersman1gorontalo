<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAchievement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExtracurricularAchievementController extends Controller
{
    public function store(Request $request, Extracurricular $extracurricular): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $validated['extracurricular_id'] = $extracurricular->id;

        ExtracurricularAchievement::create($validated);

        return redirect()
            ->route('admin.extracurriculars.show', $extracurricular)
            ->with('success', 'Prestasi ekstrakurikuler berhasil ditambahkan.');
    }

    public function update(Request $request, Extracurricular $extracurricular, ExtracurricularAchievement $achievement): RedirectResponse
    {
        abort_unless($achievement->extracurricular_id === $extracurricular->id, 404);

        $achievement->update($this->validatePayload($request));

        return redirect()
            ->route('admin.extracurriculars.show', $extracurricular)
            ->with('success', 'Prestasi ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(Extracurricular $extracurricular, ExtracurricularAchievement $achievement): RedirectResponse
    {
        abort_unless($achievement->extracurricular_id === $extracurricular->id, 404);

        $achievement->delete();

        return redirect()
            ->route('admin.extracurriculars.show', $extracurricular)
            ->with('success', 'Prestasi ekstrakurikuler berhasil dihapus.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'achievement_date' => ['nullable', 'date'],
        ]);
    }
}
