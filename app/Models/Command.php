<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
    use HasFactory;

    protected $table = 'device_commands';

    protected $fillable = [
        'device_id',
        'command',
        'data',
        'response',
        'executed_at'
    ];

    public $timestamps = [
        'completed_at',
        'failed_at',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }


    public function scopePending($query)
    {
        return $query->whereNull('executed_at');
    }

    public function scopeExecuted($query)
    {
        return $query->whereNotNull('executed_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeFailed($query)
    {
        return $query->whereNotNull('failed_at');
    }

    public function scopeByDevice($query, $device)
    {
        return $query->where('device_id', $device->id);
    }
    
}
