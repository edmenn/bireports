<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\EntityAudit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

final class EntityObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model, null, $model->attributesToArray());
    }

    public function updated(Model $model): void
    {
        $before = [];
        $after  = [];
        foreach ($model->getChanges() as $key => $val) {
            // saltar timestamps típicos si no interesan
            if (in_array($key, ['updated_at', 'created_at', 'email_verified_at', 'remember_token'], true)) continue;
            $after[$key] = $val;
            $before[$key] = $model->getOriginal($key);
        }
        if (!empty($before) || !empty($after)) {
            $this->log('updated', $model, $before, $after);
        }
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model, $model->attributesToArray(), null);
    }

    private function log(string $action, Model $model, ?array $before, ?array $after): void
    {
        try {
            $userId = optional(Auth::user())->getAuthIdentifier();
            $meta = [
                'ip' => Request::ip(),
                'route' => optional(Request::route())->getName(),
                'method' => Request::method(),
                'url' => Request::fullUrl(),
                'user_agent' => Request::userAgent(),
            ];
            EntityAudit::create([
                'user_id' => $userId,
                'action'  => $action,
                'auditable_type' => get_class($model),
                'auditable_id'   => (string) $model->getKey(),
                'changes' => ['before' => $before, 'after' => $after],
                'meta'    => $meta,
            ]);
        } catch (\Throwable) {
            // silencio para no romper flujo de negocio
        }
    }
}

