<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigAudit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ConfigAuditController extends Controller
{
    public function index(Request $request): View
    {
        $q    = trim((string) $request->query('q', ''));
        $user = (int) $request->query('user', 0);

        $audits = ConfigAudit::query()
            ->when($user > 0, fn($qr)=>$qr->where('user_id',$user))
            ->when($q !== '', function($qr) use ($q){
                $like = '%' . mb_strtolower($q) . '%';
                $qr->whereRaw('LOWER(CAST(changes as TEXT)) LIKE ?', [$like]);
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('configuraciones.audit', compact('audits','q','user'));
    }
}

