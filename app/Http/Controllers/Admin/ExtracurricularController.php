<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\Extracurricular;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExtracurricularController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $extracurriculars = Extracurricular::with(['coach.user', 'coaches.user'])
            ->when($search, function ($query, $searchValue) {
                $query->where('name', 'like', "%{$searchValue}%")
                    ->orWhereHas('coaches.user', function ($userQuery) use ($searchValue): void {
                        $userQuery->where('name', 'like', "%{$searchValue}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.extracurriculars.index', compact('extracurriculars', 'search'));
    }

    public function create(): View
    {
        return view('admin.extracurriculars.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['image_path'] = $this->storeImage($request);

        Extracurricular::create($validated);

        return redirect()->route('admin.extracurriculars.index')->with('success', 'Data ekstrakurikuler berhasil ditambahkan.');
    }

    public function show(Extracurricular $extracurricular): View
    {
        $extracurricular->load([
            'coach.user',
            'coaches.user',
            'schedules' => fn ($query) => $query->orderByDesc('activity_date'),
            'registrations.student.user',
            'achievements',
        ]);

        return view('admin.extracurriculars.show', compact('extracurricular'));
    }

    public function edit(Extracurricular $extracurricular): View
    {
        return view('admin.extracurriculars.edit', [
            'extracurricular' => $extracurricular,
        ]);
    }

    public function update(Request $request, Extracurricular $extracurricular): RedirectResponse
    {
        $validated = $this->validatePayload($request, $extracurricular);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['image_path'] = $this->resolveUpdatedImage($request, $extracurricular);

        $extracurricular->update($validated);

        return redirect()->route('admin.extracurriculars.index')->with('success', 'Data ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(Extracurricular $extracurricular): RedirectResponse
    {
        $this->deleteImage($extracurricular->image_path);
        $extracurricular->delete();

        return redirect()->route('admin.extracurriculars.index')->with('success', 'Data ekstrakurikuler berhasil dihapus.');
    }

    private function validatePayload(Request $request, ?Extracurricular $extracurricular = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('extracurriculars', 'name')->ignore($extracurricular?->id)],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'schedule_overview' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:3072', 'mimes:jpg,jpeg,png,webp'],
            'remove_image' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function resolveUpdatedImage(Request $request, Extracurricular $extracurricular): ?string
    {
        if ($request->boolean('remove_image')) {
            $this->deleteImage($extracurricular->image_path);

            return null;
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($extracurricular->image_path);

            return $this->storeImage($request);
        }

        return $extracurricular->image_path;
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $directory = public_path('uploads/extracurriculars');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/extracurriculars/'.$filename;
    }

    private function deleteImage(?string $imagePath): void
    {
        if (! $imagePath) {
            return;
        }

        $absolutePath = public_path($imagePath);
        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
    }
}
