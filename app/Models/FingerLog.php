<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FingerLog extends Model
{
    use HasFactory;

    protected $table = 'finger_log';

    protected $fillable = [
        'id',
        'data',
        'sn',
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
    
}
