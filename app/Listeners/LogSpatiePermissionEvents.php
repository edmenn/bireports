<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\EntityAudit;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Log;

final class LogSpatiePermissionEvents
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(\Spatie\Permission\Events\RoleAttached::class, [$this, 'onRoleAttached']);
        $events->listen(\Spatie\Permission\Events\RoleDetached::class, [$this, 'onRoleDetached']);
        $events->listen(\Spatie\Permission\Events\PermissionAttached::class, [$this, 'onPermissionAttached']);
        $events->listen(\Spatie\Permission\Events\PermissionDetached::class, [$this, 'onPermissionDetached']);
    }

    public function onRoleAttached($event): void
    {
        $this->log('attached', $event->model, ['role' => $event->role->name]);
    }

    public function onRoleDetached($event): void
    {
        $this->log('detached', $event->model, ['role' => $event->role->name]);
    }

    public function onPermissionAttached($event): void
    {
        $this->log('attached', $event->model ?? $event->role ?? null, ['permission' => $event->permission->name]);
    }

    public function onPermissionDetached($event): void
    {
        $this->log('detached', $event->model ?? $event->role ?? null, ['permission' => $event->permission->name]);
    }

    private function log(string $action, $target, array $meta): void
    {
        if (!$target) return;
        try {
            EntityAudit::create([
                'user_id' => optional(auth()->user())->id,
                'action'  => $action,
                'auditable_type' => is_object($target) ? get_class($target) : (string) $target,
                'auditable_id'   => is_object($target) ? (string) ($target->getKey() ?? '') : '',
                'changes' => null,
                'meta'    => $meta,
            ]);
        } catch (\Throwable $e) {
            Log::error('Permission change audit failed', ['error' => $e->getMessage()]);
        }
    }
}
