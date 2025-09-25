<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationSectionRequest;
use App\Models\Application;
use App\Models\ApplicationSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ApplicationSectionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:application_sections.manage');
    }

    public function store(ApplicationSectionRequest $request, Application $application): RedirectResponse
    {
        $data = $request->validated();
        $parentId = $this->validatedParent($application, $data['parent_id'] ?? null);
        $slug = $this->resolveSlug($application, $data['slug'] ?? null, $data['name']);

        $application->sections()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => $parentId,
            'display_order' => $data['display_order'] ?? null,
        ]);

        return back()->with('success', __('applications.sections.flash.created'));
    }

    public function update(ApplicationSectionRequest $request, Application $application, ApplicationSection $section): RedirectResponse
    {
        $this->ensureSectionOwner($application, $section);

        $data = $request->validated();
        $parentId = $this->validatedParent($application, $data['parent_id'] ?? null, $section);
        $slug = $this->resolveSlug($application, $data['slug'] ?? null, $data['name'], $section);

        $section->update([
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => $parentId,
            'display_order' => $data['display_order'] ?? null,
        ]);

        return back()->with('success', __('applications.sections.flash.updated'));
    }

    public function destroy(Application $application, ApplicationSection $section): RedirectResponse
    {
        $this->ensureSectionOwner($application, $section);

        $reportIds = $section->reports()->pluck('reports.id');
        foreach ($reportIds as $reportId) {
            $application->reports()->updateExistingPivot((string) $reportId, ['section_id' => null]);
        }

        $section->delete();

        return back()->with('success', __('applications.sections.flash.deleted'));
    }

    private function resolveSlug(Application $application, ?string $slug, string $name, ?ApplicationSection $current = null): string
    {
        $base = Str::slug($slug ?: $name);
        $base = $base !== '' ? $base : Str::random(6);

        $candidate = $base;
        $suffix = 1;
        while ($application->sections()
            ->when($current, fn ($q) => $q->whereKeyNot($current->getKey()))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function validatedParent(Application $application, ?int $parentId, ?ApplicationSection $current = null): ?int
    {
        if (!$parentId) {
            return null;
        }

        if ($current && $current->id === $parentId) {
            return null;
        }

        $belongs = $application->sections()->whereKey($parentId)->exists();

        return $belongs ? $parentId : null;
    }

    private function ensureSectionOwner(Application $application, ApplicationSection $section): void
    {
        abort_unless($section->application_id === $application->id, 404);
    }
}