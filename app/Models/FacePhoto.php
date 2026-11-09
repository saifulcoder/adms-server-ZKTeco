<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The face comparison photo (BIOPHOTO) of one employee as held by one
 * terminal — the enrolment image a visible-light terminal builds its face
 * template from.
 *
 * @author XMindware
 */
class FacePhoto extends Model
{
    use HasFactory;

    protected $table = 'face_photos';

    protected $fillable = [
        'device_id',
        'sn',
        'pin',
        'bio_type',
        'file_name',
        'size',
        'content',
        'content_hash',
        'idempresa',
        'idoficina',
        'captured_at',
    ];

    protected $casts = [
        'bio_type' => 'integer',
        'size' => 'integer',
        'captured_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function scopeForOffice($query, $idempresa, $idoficina)
    {
        return $query->where('idempresa', $idempresa)->where('idoficina', $idoficina);
    }
}
