<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $table    = 'audit_log';
    protected $fillable = [
        'user_id', 'username', 'modul', 'aksi', 'tabel',
        'record_id', 'before', 'after', 'keterangan',
        'ip_address', 'user_agent',
    ];
    protected $casts = [
        'before'     => 'array',
        'after'      => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Catat audit log secara statis — pakai ini dari mana saja.
     */
    public static function catat(
        string  $aksi,
        ?string $modul      = null,
        ?string $tabel      = null,
        ?string $record_id  = null,
        mixed   $before     = null,
        mixed   $after      = null,
        ?string $keterangan = null,
    ): void {
        $user = auth()->user();
        static::create([
            'user_id'    => $user?->id,
            'username'   => $user?->username,
            'modul'      => $modul,
            'aksi'       => $aksi,
            'tabel'      => $tabel,
            'record_id'  => $record_id,
            'before'     => $before,
            'after'      => $after,
            'keterangan' => $keterangan,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
