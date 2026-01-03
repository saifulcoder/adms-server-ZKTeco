<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
    use HasFactory;

    protected $table = 'device_log';

    protected $fillable = [
        'id',
        'data',
        'tgl',
        'sn',
        'option',
        'url'
    ];

    public function getCreatedAtAttribute($value)
    {
        $timezone = config('app.timezone');
        return Carbon::parse($value)->setTimezone($timezone)->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        $timezone = config('app.timezone');
        return Carbon::parse($value)->setTimezone($timezone)->toDateTimeString();
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'idreloj', 'id');
    }

}
