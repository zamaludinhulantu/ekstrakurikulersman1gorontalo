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
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(['all', ...array_keys($this->types())])],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])],
            'coach_id' => ['nullable', 'integer', 'exists:coaches,id'],
            'sort' => ['nullable', Rule::in(['latest', 'name', 'oldest'])],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $allowedCategories = array_keys(Extracurricular::categoryDefinitions());
        $requestedCategory = (string) ($filters['category'] ?? 'all');
        $category = in_array($requestedCategory, $allowedCategories, true) ? $requestedCategory : 'all';
        $type = (string) ($filters['type'] ?? 'all');
        $status = (string) ($filters['status'] ?? 'all');
        $coachId = (int) ($filters['coach_id'] ?? 0);
        $sort = (string) ($filters['sort'] ?? 'latest');

        $extracurriculars = Extracurricular::with(['coach.user', 'coaches.user'])
            ->when($search, function ($query, $searchValue) {
                $query->where(function ($searchQuery) use ($searchValue): void {
                    $searchQuery->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('description', 'like', "%{$searchValue}%")
                        ->orWhereHas('coaches.user', function ($userQuery) use ($searchValue): void {
                            $userQuery->where('name', 'like', "%{$searchValue}%");
                        });
                });
            })
            ->when($type !== 'all', fn ($query) => $query->where('type', $type))
            ->when($status !== 'all', fn ($query) => $query->where('is_active', $status === 'active'))
            ->when($coachId > 0, function ($query) use ($coachId): void {
                $query->where(function ($coachQuery) use ($coachId): void {
                    $coachQuery->where('coach_id', $coachId)
                        ->orWhereHas('coaches', fn ($assignedCoachQuery) => $assignedCoachQuery->whereKey($coachId));
                });
            })
            ->when($category !== 'all', function ($query) use ($category): void {
                $ids = Extracurricular::idsForCategory($category);

                if ($ids === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('id', $ids);
            })
            ->when($sort === 'name', fn ($query) => $query->orderBy('name'))
            ->when($sort === 'oldest', fn ($query) => $query->oldest())
            ->when($sort === 'latest', fn ($query) => $query->latest())
            ->paginate(10)
            ->withQueryString();

        return view('admin.extracurriculars.index', [
            'extracurriculars' => $extracurriculars,
            'search' => $search,
            'category' => $category,
            'type' => $type,
            'status' => $status,
            'coachId' => $coachId > 0 ? $coachId : null,
            'sort' => $sort,
            'categories' => collect(Extracurricular::categoryDefinitions())
                ->map(fn (array $definition) => ['key' => $definition['key'], 'label' => $definition['label']])
                ->values(),
            'types' => $this->types(),
            'coaches' => Coach::with('user')
                ->orderByRaw('coalesce(nip, "")')
                ->get(),
            'statusOptions' => [
                ['value' => 'all', 'label' => 'Semua status'],
                ['value' => 'active', 'label' => 'Aktif'],
                ['value' => 'inactive', 'label' => 'Tidak aktif'],
            ],
            'sortOptions' => [
                ['value' => 'latest', 'label' => 'Terbaru'],
                ['value' => 'name', 'label' => 'Nama A-Z'],
                ['value' => 'oldest', 'label' => 'Terlama'],
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.extracurriculars.create', [
            'types' => $this->types(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $validated['type'] = $validated['type'] ?? Extracurricular::TYPE_EXTRACURRICULAR;
        $validated['branch_options'] = $this->normalizeBranchOptions($request->input('branch_options'));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['image_path'] = $this->storeImage($request);

        Extracurricular::create($validated);
        Extracurricular::forgetCatalogCaches();

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
            'types' => $this->types(),
        ]);
    }

    public function update(Request $request, Extracurricular $extracurricular): RedirectResponse
    {
        $validated = $this->validatePayload($request, $extracurricular);
        $validated['type'] = $validated['type'] ?? Extracurricular::TYPE_EXTRACURRICULAR;
        $validated['branch_options'] = $this->normalizeBranchOptions($request->input('branch_options'));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['image_path'] = $this->resolveUpdatedImage($request, $extracurricular);

        $extracurricular->update($validated);
        Extracurricular::forgetCatalogCaches();

        return redirect()->route('admin.extracurriculars.index')->with('success', 'Data ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(Extracurricular $extracurricular): RedirectResponse
    {
        $this->deleteImage($extracurricular->image_path);
        $extracurricular->delete();
        Extracurricular::forgetCatalogCaches();

        return redirect()->route('admin.extracurriculars.index')->with('success', 'Data ekstrakurikuler berhasil dihapus.');
    }

    private function validatePayload(Request $request, ?Extracurricular $extracurricular = null): array
    {
        return $request->validate([
            'type' => ['nullable', Rule::in(array_keys($this->types()))],
            'name' => ['required', 'string', 'max:255', Rule::unique('extracurriculars', 'name')->ignore($extracurricular?->id)],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'schedule_overview' => ['nullable', 'string'],
            'branch_options' => ['nullable', 'string'],
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

    private function types(): array
    {
        return [
            Extracurricular::TYPE_EXTRACURRICULAR => 'Ekstrakurikuler',
            Extracurricular::TYPE_OLYMPIAD => 'Olimpiade',
        ];
    }

    private function normalizeBranchOptions(?string $branchOptions): ?array
    {
        $items = collect(preg_split('/\r\n|\r|\n/', (string) $branchOptions))
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $items !== [] ? $items : null;
    }
}
