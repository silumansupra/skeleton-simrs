<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\BaseController;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends BaseController
{
    public function index(Request $request)
    {
        $filter = $request->only(['keyword', 'modul', 'aksi', 'user_id', 'tgl_dari', 'tgl_sampai']);

        $query = AuditLog::with('user')
            ->when($filter['keyword'] ?? null, fn($q, $kw) =>
                $q->where(fn($q) =>
                    $q->where('keterangan', 'like', "%{$kw}%")
                      ->orWhere('aksi', 'like', "%{$kw}%")
                      ->orWhere('username', 'like', "%{$kw}%")
                ))
            ->when($filter['modul'] ?? null, fn($q, $v) => $q->where('modul', $v))
            ->when($filter['aksi']  ?? null, fn($q, $v) => $q->where('aksi',  $v))
            ->when($filter['user_id'] ?? null, fn($q, $v) => $q->where('user_id', $v))
            ->when($filter['tgl_dari']   ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filter['tgl_sampai'] ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at');

        $logs   = $query->paginate(25)->withQueryString();
        $moduls = AuditLog::selectRaw('DISTINCT modul')->whereNotNull('modul')->pluck('modul')->sort()->values();
        $aksis  = AuditLog::selectRaw('DISTINCT aksi')->whereNotNull('aksi')->pluck('aksi')->sort()->values();

        return view('pengaturan.audit-log.index', $this->shareLayout([
            'title'      => 'Audit Log',
            'breadcrumb' => [
                ['label' => 'Pengaturan', 'url' => '#'],
                ['label' => 'Audit Log',  'url' => ''],
            ],
            'logs'   => $logs,
            'filter' => $filter,
            'moduls' => $moduls,
            'aksis'  => $aksis,
        ]));
    }

    public function show(int $id)
    {
        return $this->respondOk(data: ['log' => AuditLog::with('user')->findOrFail($id)]);
    }
}
