<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExtracurricularCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.extracurricular-categories.index', [
            'categories' => ExtracurricularCategory::query()->ordered()->get(),
        ]);
    }

    public function edit(ExtracurricularCategory $extracurricularCategory): View
    {
        return view('admin.extracurricular-categories.edit', [
            'category' => $extracurricularCategory,
            'tones' => $this->tones(),
        ]);
    }

    public function update(Request $request, ExtracurricularCategory $extracurricularCategory): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:120', Rule::unique('extracurricular_categories', 'slug')->ignore($extracurricularCategory->id)],
            'label' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string'],
            'catalog_title' => ['required', 'string', 'max:255'],
            'catalog_subtitle' => ['required', 'string'],
            'icon' => ['required', 'string', 'max:80'],
            'tone' => ['required', Rule::in(array_keys($this->tones()))],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:3072', 'mimes:jpg,jpeg,png,webp'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $validated['image_path'] = $this->resolveUpdatedImage($request, $extracurricularCategory);
        $validated['is_active'] = $request->boolean('is_active');

        $extracurricularCategory->update($validated);
        Extracurricular::forgetCatalogCaches();
        Cache::forget('extracurricular.category-definitions.records.v1');

        return redirect()->route('admin.extracurricular-categories.index')
            ->with('success', 'Kategori ekskul berhasil diperbarui.');
    }

    private function tones(): array
    {
        return [
            'is-extracurricular' => 'Biru umum',
            'is-osn' => 'Biru sains',
            'is-o2sn' => 'Emas olahraga',
        ];
    }

    private function resolveUpdatedImage(Request $request, ExtracurricularCategory $category): ?string
    {
        if ($request->boolean('remove_image')) {
            $this->deleteImage($category->image_path);

            return null;
        }

        if (! $request->hasFile('image')) {
            return $category->image_path;
        }

        $this->deleteImage($category->image_path);

        return $this->storeImage($request);
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $directory = public_path('uploads/extracurricular-categories');
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $file = $request->file('image');
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/extracurricular-categories/'.$filename;
    }

    private function deleteImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        $absolutePath = public_path($path);
        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
    }
}
