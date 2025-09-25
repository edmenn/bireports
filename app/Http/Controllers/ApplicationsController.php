<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationRequest;
use App\Models\Application;
use App\Models\ApplicationSection;
use App\Models\Group;
use App\Models\Report;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ApplicationsController extends Controller
{
    public function __construct(private readonly DatabaseManager $db)
    {
        $this->middleware('permission:applications.view')->only(['index', 'create', 'edit']);
        $this->middleware('permission:applications.create')->only(['create', 'store']);
        $this->middleware('permission:applications.edit')->only(['edit', 'update']);
        $this->middleware('permission:applications.delete')->only(['destroy']);
    }

    public function index(): View
    {
        $applications = Application::query()
            ->with(['sections' => fn ($q) => $q->orderBy('name')])
            ->withCount(['reports', 'users', 'groups'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('applications.index', compact('applications'));
    }

    public function create(): View
    {
        return view('applications.create', $this->formData());
    }

    public function store(ApplicationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $application = $this->db->transaction(function () use ($data) {
            $payload = $this->applicationPayload($data);
            $application = Application::create($payload);

            $this->syncSections($application, $data);
            $this->syncAssignments($application, $data);

            return $application;
        });

        return redirect()->route('applications.edit', $application)
            ->with('success', __('applications.flash.created'));
    }

    public function edit(Application $application): View
    {
        $application->load([
            'sections' => fn ($q) => $q->orderBy('name'),
            'users:id',
            'groups:id',
            'reports' => fn ($q) => $q->orderBy('name'),
        ]);

        return view('applications.edit', $this->formData($application));
    }

    public function update(ApplicationRequest $request, Application $application): RedirectResponse
    {
        $data = $request->validated();

        $this->db->transaction(function () use ($application, $data) {
            $payload = $this->applicationPayload($data, $application);
            $application->update($payload);

            $this->syncSections($application, $data, true);
            $this->syncAssignments($application, $data);
        });

        return redirect()->route('applications.edit', $application)
            ->with('success', __('applications.flash.updated'));
    }

    public function destroy(Application $application): RedirectResponse
    {
        $this->db->transaction(function () use ($application) {
            $application->reports()->detach();
            $application->users()->detach();
            $application->groups()->detach();
            $application->sections()->delete();
            $application->delete();
        });

        return redirect()->route('applications.index')
            ->with('success', __('applications.flash.deleted'));
    }

    private function formData(?Application $application = null): array
    {
        return [
            'application' => $application,
            'users' => User::orderBy('name')->get(),
            'groups' => Group::orderBy('name')->get(),
            'reports' => Report::orderBy('name')->get(),
        ];
    }

    private function applicationPayload(array $data, ?Application $application = null): array
    {
        $payload = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'display_order' => $data['display_order'] ?? null,
        ];

        $desiredSlug = $data['slug'] ?? null;
        $payload['slug'] = $this->resolveApplicationSlug($desiredSlug, $payload['name'], $application);

        return $payload;
    }

    private function resolveApplicationSlug(?string $slug, string $name, ?Application $current = null): string
    {
        $base = Str::slug($slug ?: $name);
        $base = $base !== '' ? $base : Str::random(8);

        $candidate = $base;
        $suffix = 1;
        while (Application::query()
            ->when($current, fn ($q) => $q->whereKeyNot($current->getKey()))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function resolveSectionSlug(Application $application, ?string $slug, string $name, ?ApplicationSection $current = null): string
    {
        $base = Str::slug($slug ?: $name);
        $base = $base !== '' ? $base : Str::random(6);

        $candidate = $base;
        $suffix = 1;
        while ($application->sections()
            ->when($current, fn ($q) => $q->whereKeyNot($current->getKey()))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function syncAssignments(Application $application, array $data): void
    {
        $application->users()->sync($data['users'] ?? []);
        $application->groups()->sync($data['groups'] ?? []);

        $reports = $data['reports'] ?? [];

        $reportSections = [];
        if (isset($data['report_sections']) && is_array($data['report_sections'])) {
            $validSectionIds = $application->sections()->pluck('id')->all();
            $validLookup = array_flip($validSectionIds);

            foreach ($data['report_sections'] as $reportId => $sectionId) {
                $sectionId = is_numeric($sectionId) ? (int) $sectionId : null;
                if ($sectionId && isset($validLookup[$sectionId])) {
                    $reportSections[(string) $reportId] = $sectionId;
                }
            }
        }

        if (is_array($reports)) {
            $pivot = [];
            foreach ($reports as $reportId) {
                $key = (string) $reportId;
                $pivot[$key] = ['section_id' => $reportSections[$key] ?? null];
            }

            $application->reports()->sync($pivot);
        }
    }

    private function syncSections(Application $application, array $data, bool $updating = false): void
    {
        $newSections = array_filter(array_map('trim', $data['sections'] ?? []), fn ($name) => $name !== '');

        foreach ($newSections as $name) {
            $application->sections()->create([
                'name' => $name,
                'slug' => $this->resolveSectionSlug($application, null, $name),
            ]);
        }

        if (! $updating) {
            return;
        }

        $updates = $data['sections_existing'] ?? [];
        foreach ($updates as $sectionId => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $section = $application->sections()->whereKey((int) $sectionId)->first();
            if (! $section) {
                continue;
            }

            $section->name = $name;
            $section->slug = $this->resolveSectionSlug($application, $section->slug, $name, $section);
            $section->save();
        }

        $remove = array_filter($data['sections_remove'] ?? [], fn ($value) => filled($value));
        if (empty($remove)) {
            return;
        }

        $ids = array_map('intval', $remove);
        ApplicationSection::query()
            ->where('application_id', $application->id)
            ->whereIn('id', $ids)
            ->each(function (ApplicationSection $section) use ($application) {
                $section->reports()->each(function (Report $report) use ($application) {
                    $application->reports()->updateExistingPivot((string) $report->id, ['section_id' => null]);
                });
                $section->delete();
            });
    }
}
