<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportAccessService
{
    /**
     * Resolve all folder identifiers linked to the report.
     *
     * @return array<int, int>
     */
    protected function folderIdsForReport(Report $report): array
    {
        return $report->folders()->pluck('folders.id')->map(fn ($id) => (int) $id)->unique()->all();
    }

    /**
     * Return all folder identifiers the user can access via direct or group membership.
     *
     * @return array<int, int>
     */
    protected function folderIdsForUser(User $user): array
    {
        $direct = $user->folders()->pluck('folders.id')->map(fn ($id) => (int) $id);

        $viaGroups = DB::table('folder_group')
            ->join('group_user', 'group_user.group_id', '=', 'folder_group.group_id')
            ->where('group_user.user_id', $user->id)
            ->pluck('folder_group.folder_id')
            ->map(fn ($id) => (int) $id);

        return $direct->concat($viaGroups)->unique()->all();
    }

    /**
     * Determine if the user can access the given report based on folder assignments or groups.
     */
    public function userCanAccessReport(User $user, Report $report): bool
    {
        $folderIds = $this->folderIdsForReport($report);
        if (empty($folderIds)) {
            return false;
        }

        return $user->folders()
            ->whereIn('folders.id', $folderIds)
            ->exists()
            || $user->groups()
                ->whereHas('folders', fn ($q) => $q->whereIn('folders.id', $folderIds))
                ->exists();
    }

    /**
     * Groups that have access to the report via folder assignments.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Group>
     */
    public function groupsWithAccess(Report $report): Collection
    {
        $folderIds = $this->folderIdsForReport($report);
        if (empty($folderIds)) {
            return collect();
        }

        return Group::query()
            ->whereHas('folders', fn ($q) => $q->whereIn('folders.id', $folderIds))
            ->orderBy('name')
            ->get();
    }

    /**
     * Users that can access the report either directly or through groups.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\User>
     */
    public function usersWithAccess(Report $report): Collection
    {
        $folderIds = $this->folderIdsForReport($report);
        if (empty($folderIds)) {
            return collect();
        }

        return User::query()
            ->where(function ($query) use ($folderIds) {
                $query->whereHas('folders', fn ($q) => $q->whereIn('folders.id', $folderIds))
                    ->orWhereHas('groups.folders', fn ($q) => $q->whereIn('folders.id', $folderIds));
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Reports accessible by the given user.
     *
     * @return array<int, string>
     */
    public function reportIdsAccessibleByUser(User $user): array
    {
        $folderIds = $this->folderIdsForUser($user);
        if (empty($folderIds)) {
            return [];
        }

        return DB::table('folder_report')
            ->whereIn('folder_id', $folderIds)
            ->pluck('report_id')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->all();
    }
}
