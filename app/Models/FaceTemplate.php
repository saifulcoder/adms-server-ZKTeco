<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A face template for one employee as held by one terminal.
 *
 * @author XMindware
 */
class FaceTemplate extends Model
{
    use HasFactory;

    protected $table = 'face_templates';

    protected $fillable = [
        'device_id',
        'sn',
        'pin',
        'fid',
        'size',
        'valid',
        'template',
        'template_hash',
        'idempresa',
        'idoficina',
        'format',
        'version',
        'captured_at',
    ];

    protected $casts = [
        'fid' => 'integer',
        'size' => 'integer',
        'valid' => 'integer',
        'captured_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function scopeValid($query)
    {
        return $query->where('valid', '>', 0);
    }

    public function scopeForOffice($query, $idempresa, $idoficina)
    {
        return $query->where('idempresa', $idempresa)->where('idoficina', $idoficina);
    }
}
