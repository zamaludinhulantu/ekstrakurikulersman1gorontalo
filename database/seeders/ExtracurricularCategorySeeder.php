<?php

namespace Database\Seeders;

use App\Models\Extracurricular;
use App\Models\ExtracurricularCategory;
use Illuminate\Database\Seeder;

class ExtracurricularCategorySeeder extends Seeder
{
    public function run(): void
    {
        $sort = 1;

        foreach (Extracurricular::defaultCategoryDefinitions() as $definition) {
            ExtracurricularCategory::updateOrCreate(
                ['key' => $definition['key']],
                [
                    'slug' => $definition['slug'],
                    'label' => $definition['label'],
                    'description' => $definition['description'],
                    'catalog_title' => $definition['catalog_title'],
                    'catalog_subtitle' => $definition['catalog_subtitle'],
                    'icon' => $definition['icon'],
                    'tone' => $definition['tone'],
                    'image_path' => $this->toRelativeImagePath($definition['image']),
                    'sort_order' => $sort++,
                    'is_active' => true,
                ]
            );
        }
    }

    private function toRelativeImagePath(string $asset): ?string
    {
        $parts = parse_url($asset);
        $path = $parts['path'] ?? null;

        return $path ? ltrim($path, '/') : null;
    }
}
