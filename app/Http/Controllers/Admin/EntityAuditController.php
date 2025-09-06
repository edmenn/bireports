<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EntityAudit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class EntityAuditController extends Controller
{
    public function index(Request $request): View
    {
        $qAction = trim((string) $request->query('action', ''));
        $qModel  = trim((string) $request->query('model', ''));
        $qUser   = (int) $request->query('user', 0);

        $rows = EntityAudit::query()
            ->when($qAction !== '', fn($qr)=>$qr->where('action', $qAction))
            ->when($qModel !== '', function($qr) use ($qModel) {
                $like = '%' . str_replace('\\', '\\\\', $qModel) . '%';
                $qr->where('auditable_type', 'like', $like);
            })
            ->when($qUser > 0, fn($qr)=>$qr->where('user_id',$qUser))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audits.entities', [
            'rows' => $rows,
            'qAction' => $qAction,
            'qModel' => $qModel,
            'qUser' => $qUser,
        ]);
    }
}

