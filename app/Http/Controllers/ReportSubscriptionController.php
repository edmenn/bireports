<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportSubscriptionRequest;
use App\Http\Requests\UpdateReportSubscriptionRequest;
use App\Jobs\ProcessReportSubscriptionRun;
use App\Models\Group;
use App\Models\Report;
use App\Models\ReportSubscription;
use App\Models\ReportSubscriptionNotification;
use App\Models\ReportSubscriptionRun;
use App\Models\User;
use App\Services\PowerBiService;
use App\Services\ReportSubscriptionScheduler;
use App\Services\ReportAccessService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ReportSubscriptionController extends Controller
{
    public function __construct(
        private readonly ReportAccessService $access,
        private readonly PowerBiService $powerBi,
        private readonly ReportSubscriptionScheduler $scheduler
    ) {
        $this->middleware(['auth', \App\Http\Middleware\EnsureUserIsNotBlocked::class, 'permission:report_subscriptions.manage']);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $query = ReportSubscription::query()
            ->with([
                'report.workspace',
                'recipientUsers',
                'recipientGroups',
            ])
            ->latest();

        $reportsQuery = Report::query()->orderBy('name');

        if (! $user->hasRole('admin')) {
            $reportIds = $this->access->reportIdsAccessibleByUser($user);
            if (empty($reportIds)) {
                $subscriptions = new LengthAwarePaginator([], 0, 20, 1, [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]);

                return view('subscriptions.index', [
                    'subscriptions' => $subscriptions,
                    'reports' => collect(),
                ]);
            }

            $query->whereIn('report_id', $reportIds);
            $reportsQuery->whereIn('id', $reportIds);
        }

        $subscriptions = $query->paginate(20)->withQueryString();
        $reports = $reportsQuery->get(['id', 'name', 'workspace_id']);

        return view('subscriptions.index', [
            'subscriptions' => $subscriptions,
            'reports' => $reports,
            'subscriptionSnippetOptions' => $this->snippetOptions($user),
        ]);
    }

    public function show(Request $request, ReportSubscription $reportSubscription): View
    {
        $this->authorize('view', $reportSubscription);

        $reportSubscription->loadMissing([
            'report.workspace',
            'recipientGroups',
            'recipientUsers',
            'pages' => fn ($q) => $q->orderBy('page_name'),
            'runs' => fn ($q) => $q->latest()->limit(50),
            'notificationLinks.user',
            'notificationLinks.group',
        ]);

        $user = $request->user();
        $reportsQuery = Report::query()->orderBy('name');

        if ($user && ! $user->hasRole('admin')) {
            $reportIds = $this->access->reportIdsAccessibleByUser($user);
            $reportIds[] = $reportSubscription->report_id;
            $reportIds = array_values(array_unique(array_filter($reportIds)));
            if (! empty($reportIds)) {
                $reportsQuery->whereIn('id', $reportIds);
            } else {
                $reportsQuery->whereRaw('1 = 0');
            }
        }

        return view('subscriptions.show', [
            'subscription' => $reportSubscription,
            'runs' => $reportSubscription->runs()->latest()->paginate(20)->withQueryString(),
            'reports' => $reportsQuery->get(['id', 'name', 'workspace_id']),
            'subscriptionSnippetOptions' => $this->snippetOptions($user),
        ]);
    }

    public function store(StoreReportSubscriptionRequest $request): JsonResponse|RedirectResponse
    {
        $attributes = $request->subscriptionAttributes();
        $user = $request->user();

        $subscription = DB::transaction(function () use ($attributes, $request, $user) {
            $attributes['created_by'] = $user?->id;
            $attributes['updated_by'] = $user?->id;

            /** @var \App\Models\ReportSubscription $subscription */
            $subscription = ReportSubscription::query()->create($attributes);

            $this->syncPages($subscription, $request->selectedPages());
            $this->syncGroups($subscription, $request->input('groups', []));
            $this->syncUsers($subscription, $request->input('users', []));
            $this->syncNotifications(
                $subscription,
                $request->input('notification_users', []),
                $request->input('notification_groups', [])
            );

            return $subscription;
        });

        $this->scheduler->reschedule($subscription->fresh());

        if ($request->expectsJson()) {
            return response()->json([
                'subscription' => $subscription->load('report'),
                'message' => __('subscriptions.flash.created'),
                'redirect' => route('subscriptions.show', $subscription),
            ], 201);
        }

        return redirect()
            ->route('subscriptions.show', $subscription)
            ->with('status', __('subscriptions.flash.created'));
    }

    public function edit(ReportSubscription $reportSubscription): JsonResponse
    {
        $this->authorize('view', $reportSubscription);

        $reportSubscription->loadMissing([
            'report',
            'pages' => fn ($q) => $q->orderBy('page_name'),
            'recipientGroups:id,name',
            'recipientUsers:id,name,email',
            'notificationLinks.user',
            'notificationLinks.group',
        ]);

        return response()->json([
            'subscription' => $reportSubscription,
        ]);
    }

    public function update(UpdateReportSubscriptionRequest $request, ReportSubscription $reportSubscription): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $reportSubscription);

        $attributes = $request->subscriptionAttributes();
        $user = $request->user();

        DB::transaction(function () use ($reportSubscription, $attributes, $request, $user) {
            $attributes['updated_by'] = $user?->id;
            $reportSubscription->fill($attributes);
            $reportSubscription->save();

            $this->syncPages($reportSubscription, $request->selectedPages());
            $this->syncGroups($reportSubscription, $request->input('groups', []));
            $this->syncUsers($reportSubscription, $request->input('users', []));
            $this->syncNotifications(
                $reportSubscription,
                $request->input('notification_users', []),
                $request->input('notification_groups', [])
            );
        });

        $reportSubscription->refresh();
        $this->scheduler->reschedule($reportSubscription);

        if ($request->expectsJson()) {
            return response()->json([
                'subscription' => $reportSubscription->fresh('report'),
                'message' => __('subscriptions.flash.updated'),
                'redirect' => route('subscriptions.show', $reportSubscription),
            ]);
        }

        return redirect()
            ->route('subscriptions.show', $reportSubscription)
            ->with('status', __('subscriptions.flash.updated'));
    }

    public function destroy(Request $request, ReportSubscription $reportSubscription): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $reportSubscription);

        $this->scheduler->cancelPendingRuns($reportSubscription, CarbonImmutable::now(config('app.timezone')));

        $reportSubscription->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('subscriptions.flash.deleted'),
            ]);
        }

        return redirect()
            ->route('subscriptions.index')
            ->with('status', __('subscriptions.flash.deleted'));
    }

    public function sendNow(Request $request, ReportSubscription $reportSubscription): RedirectResponse|JsonResponse
    {
        $this->authorize('run', $reportSubscription);

        $run = $reportSubscription->runs()->create([
            'scheduled_time' => now(),
            'status' => ReportSubscriptionRun::STATUS_PENDING,
        ]);

        ProcessReportSubscriptionRun::dispatch($run->id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('subscriptions.flash.dispatched_now'),
            ]);
        }

        return back()->with('status', __('subscriptions.flash.dispatched_now'));
    }

    public function metadata(Report $report, Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || (! $user->hasRole('admin') && ! $this->access->userCanAccessReport($user, $report))) {
            abort(403);
        }

        $pages = $this->powerBi->listReportPages($report->workspace_id, $report->id);
        $groups = Group::query()->orderBy('name')->get(['id', 'name']);
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return response()->json([
            'report' => [
                'id' => $report->id,
                'name' => $report->name,
            ],
            'pages' => $pages,
            'groups' => $groups->map(fn ($group) => [
                'id' => $group->id,
                'name' => $group->name,
            ])->values(),
            'users' => $users->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])->values(),
        ]);
    }

    protected function syncPages(ReportSubscription $subscription, array $pages): void
    {
        /** @var EloquentCollection<int, \App\Models\ReportSubscriptionPage> $existing */
        $existing = $subscription->pages()->withTrashed()->get()->keyBy('page_name');
        $kept = [];

        foreach ($pages as $page) {
            $name = (string) Arr::get($page, 'name');
            if ($name === '') {
                continue;
            }
            $display = Arr::get($page, 'display_name');

            $model = $existing->get($name);
            if ($model) {
                $model->display_name = $display;
                if ($model->trashed()) {
                    $model->restore();
                } else {
                    $model->save();
                }
                $kept[] = $model->id;
            } else {
                $created = $subscription->pages()->create([
                    'page_name' => $name,
                    'display_name' => $display,
                ]);
                $kept[] = $created->id;
            }
        }

        foreach ($existing as $model) {
            if (! in_array($model->id, $kept, true)) {
                $model->delete();
            }
        }
    }

    protected function syncGroups(ReportSubscription $subscription, array $groupIds): void
    {
        $groupIds = array_unique(array_map('intval', $groupIds));
        $existing = $subscription->groupLinks()->withTrashed()->get()->keyBy('group_id');
        $kept = [];

        foreach ($groupIds as $groupId) {
            $link = $existing->get($groupId);
            if ($link) {
                if ($link->trashed()) {
                    $link->restore();
                }
                $kept[] = $link->id;
            } else {
                $newLink = $subscription->groupLinks()->create(['group_id' => $groupId]);
                $kept[] = $newLink->id;
            }
        }

        foreach ($existing as $link) {
            if (! in_array($link->id, $kept, true)) {
                $link->delete();
            }
        }
    }

    protected function syncUsers(ReportSubscription $subscription, array $userIds): void
    {
        $userIds = array_unique(array_map('intval', $userIds));
        $existing = $subscription->userLinks()->withTrashed()->get()->keyBy('user_id');
        $kept = [];

        foreach ($userIds as $userId) {
            $link = $existing->get($userId);
            if ($link) {
                if ($link->trashed()) {
                    $link->restore();
                }
                $kept[] = $link->id;
            } else {
                $newLink = $subscription->userLinks()->create(['user_id' => $userId]);
                $kept[] = $newLink->id;
            }
        }

        foreach ($existing as $link) {
            if (! in_array($link->id, $kept, true)) {
                $link->delete();
            }
        }
    }

    protected function syncNotifications(ReportSubscription $subscription, array $userIds, array $groupIds): void
    {
        $userIds = array_unique(array_map('intval', $userIds));
        $groupIds = array_unique(array_map('intval', $groupIds));

        $existing = $subscription->notificationLinks()->withTrashed()->get();
        $kept = [];

        foreach ($userIds as $userId) {
            $link = $existing
                ->first(fn ($item) => $item->notifiable_type === ReportSubscriptionNotification::TYPE_USER && (int) $item->notifiable_id === $userId);

            if ($link) {
                if ($link->trashed()) {
                    $link->restore();
                }
                $kept[] = $link->id;
            } else {
                $newLink = $subscription->notificationLinks()->create([
                    'channel' => 'mail',
                    'notifiable_type' => ReportSubscriptionNotification::TYPE_USER,
                    'notifiable_id' => $userId,
                ]);
                $kept[] = $newLink->id;
            }
        }

        foreach ($groupIds as $groupId) {
            $link = $existing
                ->first(fn ($item) => $item->notifiable_type === ReportSubscriptionNotification::TYPE_GROUP && (int) $item->notifiable_id === $groupId);

            if ($link) {
                if ($link->trashed()) {
                    $link->restore();
                }
                $kept[] = $link->id;
            } else {
                $newLink = $subscription->notificationLinks()->create([
                    'channel' => 'mail',
                    'notifiable_type' => ReportSubscriptionNotification::TYPE_GROUP,
                    'notifiable_id' => $groupId,
                ]);
                $kept[] = $newLink->id;
            }
        }

        foreach ($existing as $link) {
            if (! in_array($link->id, $kept, true)) {
                $link->delete();
            }
        }
    }

    protected function snippetOptions(?User $user): array
    {
        $options = [
            ['value' => '{{report_name}}', 'label' => __('subscriptions.snippets.report_name')],
            ['value' => '{{date}}', 'label' => __('subscriptions.snippets.date')],
            ['value' => '{{datetime}}', 'label' => __('subscriptions.snippets.datetime')],
            ['value' => '{{subscription_name}}', 'label' => __('subscriptions.snippets.subscription_name')],
        ];

        if ($user && $user->can('reports.view') && ! collect($options)->contains(fn ($option) => $option['value'] === '{{report_link}}')) {
            $options[] = ['value' => '{{report_link}}', 'label' => __('subscriptions.snippets.report_link')];
        }

        return $options;
    }
}
