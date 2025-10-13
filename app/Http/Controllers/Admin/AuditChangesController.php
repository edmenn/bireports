<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigAudit;
use App\Models\EntityAudit;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class AuditChangesController extends Controller
{
    public function index(Request $request): View
    {
        $qAction = trim((string) $request->query('action', ''));
        $qModel  = trim((string) $request->query('model', ''));
        $qUser   = (int) $request->query('user', 0);
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = 25;

        // Fetch recent from both sources with basic filters
        $entities = EntityAudit::query()
            ->when($qAction !== '', fn($qr)=>$qr->where('action', $qAction))
            ->when($qModel !== '', function($qr) use ($qModel) {
                $like = '%' . str_replace('\\', '\\\\', $qModel) . '%';
                $qr->where('auditable_type', 'like', $like);
            })
            ->when($qUser > 0, fn($qr)=>$qr->where('user_id',$qUser))
            ->orderByDesc('id')
            ->limit(400)
            ->get();

        $configs = ConfigAudit::query()
            ->when($qUser > 0, fn($qr)=>$qr->where('user_id',$qUser))
            ->orderByDesc('id')
            ->limit(400)
            ->get();

        $rows = new Collection();

        foreach ($entities as $e) {
            $rows->push([
                'id' => 'E'.$e->id,
                'created_at' => $e->created_at,
                'user_id' => $e->user_id,
                'source' => 'system',
                'action' => $e->action,
                'model'  => $e->auditable_type,
                'entity' => $e->auditable_id,
                'before' => $e->changes['before'] ?? null,
                'after'  => $e->changes['after']  ?? null,
                'meta'   => $e->meta,
            ]);
        }

        foreach ($configs as $c) {
            // Build before/after maps including only the fields that changed
            [$beforeMap, $afterMap] = $this->buildBeforeAfterFromChanges($c->changes);
            $rows->push([
                'id' => 'C'.$c->id,
                'created_at' => $c->created_at,
                'user_id' => $c->user_id,
                'source' => 'configuration',
                'action' => 'updated',
                'model'  => 'App\\Models\\Configuration',
                'entity' => 'config:1',
                'before' => $beforeMap,
                'after'  => $afterMap,
                'meta'   => ['ip' => $c->ip],
            ]);
        }

        // Client-side filter for model when applied to configs too
        if ($qModel !== '') {
            $rows = $rows->filter(function($r) use ($qModel) {
                return stripos((string)$r['model'], $qModel) !== false;
            });
        }

        // Sort and paginate
        $rows = $rows->sortByDesc('created_at')->values();
        $total = $rows->count();
        $items = $rows->forPage($page, $perPage)->values();

        // Resolve user names
        $userIds = $rows->pluck('user_id')->filter()->unique()->values();
        $map = User::query()->whereIn('id', $userIds)->pluck('name','id');
        $items = $items->map(function($r) use ($map) {
            $r['user_name'] = $r['user_id'] ? ($map[$r['user_id']] ?? (string)$r['user_id']) : '-';
            return $r;
        });

        $paginator = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('admin.audits.changes', [
            'rows' => $paginator,
            'qAction' => $qAction,
            'qModel' => $qModel,
            'qUser' => $qUser,
        ]);
    }

    private function extractBefore($changes): ?array
    {
        if (!is_array($changes)) return null;
        $before = [];
        foreach ($changes as $ch) {
            if (!isset($ch['field'])) continue;
            $before[$ch['field']] = $ch['old'] ?? null;
        }
        return $before ?: null;
    }

    private function extractAfter($changes): ?array
    {
        if (!is_array($changes)) return null;
        $after = [];
        foreach ($changes as $ch) {
            if (!isset($ch['field'])) continue;
            $after[$ch['field']] = $ch['new'] ?? null;
        }
        return $after ?: null;
    }

    /**
     * Convert a flat changes array [{field,old,new}] into before/after maps,
     * performing deep diffs for JSON (e.g. theme_config) and keeping only changed keys.
     */
    private function buildBeforeAfterFromChanges($changes): array
    {
        if (!is_array($changes)) return [null, null];
        $before = [];
        $after  = [];
        foreach ($changes as $ch) {
            $field = $ch['field'] ?? null;
            if (!$field) continue;
            $old = $ch['old'] ?? null;
            $new = $ch['new'] ?? null;

            // Attempt JSON decoding for deep diffs
            $oldJ = $this->jsonOrNull($old);
            $newJ = $this->jsonOrNull($new);
            if (is_array($oldJ) || is_array($newJ)) {
                $oldA = is_array($oldJ) ? $oldJ : [];
                $newA = is_array($newJ) ? $newJ : [];
                // Track which keys actually changed
                $keys = array_unique(array_merge(array_keys($oldA), array_keys($newA)));
                $changed = [];
                foreach ($keys as $k) {
                    $ov = $oldA[$k] ?? null;
                    $nv = $newA[$k] ?? null;
                    if ($ov !== $nv) $changed[] = $k;
                }
                if (!empty($changed)) {
                    $before[$field] = array_intersect_key($oldA, array_flip($changed));
                    $after[$field]  = array_intersect_key($newA, array_flip($changed));
                }
                continue;
            }

            // Scalar values: include them as-is
            $before[$field] = $old;
            $after[$field]  = $new;
        }
        return [$before ?: null, $after ?: null];
    }

    private function jsonOrNull($value): ?array
    {
        if (!is_string($value)) return null;
        $trim = trim($value);
        if ($trim === '' || ($trim[0] !== '{' && $trim[0] !== '[')) return null;
        $dec = json_decode($value, true);
        return is_array($dec) ? $dec : null;
    }
}
