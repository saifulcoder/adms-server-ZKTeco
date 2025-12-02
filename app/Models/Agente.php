<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agente extends Model
{
    use HasFactory;

    protected $table = 'agentes';

    protected $fillable = [
        'idempresa',
        'idoficina',
        'idagente',
        'shortname',
        'fullname',
        'fingerprint_data'
    ];

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'idoficina', 'idoficina');
    }

}
