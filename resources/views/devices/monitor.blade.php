@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-traffic-light"></i> Device Status Monitor</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <a href="{{ route('devices.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Devices
            </a>
        </div>
    </div>

    <!-- Status Legend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Status Legend</h6>
                    <div class="d-flex gap-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="traffic-light online"></div>
                            <span>Online (Last 5 min)</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="traffic-light warning"></div>
                            <span>Warning (5-15 min)</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="traffic-light offline"></div>
                            <span>Offline (>15 min)</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="traffic-light unknown"></div>
                            <span>Unknown</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Devices Grid -->
    <div class="row">
        @foreach($devices as $device)
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="device-card">
                <div class="traffic-light-container">
                    @php
                        $status = 'unknown';
                        if ($device->online) {
                            $diffInMinutes = $device->online->diffInMinutes(now());
                            if ($diffInMinutes < 5) {
                                $status = 'online';
                            } elseif ($diffInMinutes <= 15) {
                                $status = 'warning';
                            } else {
                                $status = 'offline';
                            }
                        }
                    @endphp
                    <div class="traffic-light {{ $status }}"></div>
                </div>
                
                <div class="device-info">
                    <h6 class="device-name">{{ $device->name ?? 'Device ' . $device->idreloj }}</h6>
                    <p class="device-location">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $device->oficina ? $device->oficina->ubicacion : 'Unknown Location' }}
                    </p>
                    <div class="last-checkin">
                        <span class="label">Last Check-in:</span>
                        <span class="time">{{ $device->last_attendance_human }}</span>
                    </div>
                    <div class="office-time">
                        <span class="label">Office Time:</span>
                        <span class="time">{{ $device->office_time_display }}</span>
                        @if($device->office_timezone)
                            <small class="timezone">({{ $device->office_timezone }})</small>
                        @endif
                    </div>
                    @if($device->discrepancy_count > 0)
                    <div class="discrepancy-alert">
                        <span class="label">⚠️ Time Discrepancies Today:</span>
                        <span class="count">{{ $device->discrepancy_count }}</span>
                    </div>
                    @endif
                    <div class="device-details">
                        <small class="text-muted">
                            ID: {{ $device->idreloj }} | 
                            Model: {{ $device->modelo ?? 'N/A' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Auto-refresh indicator -->
    <div class="text-center mt-4">
        <small class="text-muted">
            <i class="fas fa-clock"></i> Auto-refreshing every 30 seconds
        </small>
    </div>
</div>

<style>
.device-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.device-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.traffic-light-container {
    text-align: center;
    margin-bottom: 15px;
}

.traffic-light {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin: 0 auto;
    position: relative;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    animation: pulse 2s infinite;
}

.traffic-light.online {
    background: linear-gradient(135deg, #28a745, #20c997);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
}

.traffic-light.warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
}

.traffic-light.offline {
    background: linear-gradient(135deg, #dc3545, #c82333);
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
}

.traffic-light.unknown {
    background: linear-gradient(135deg, #6c757d, #495057);
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
}

.traffic-light::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    background: rgba(255,255,255,0.3);
    border-radius: 50%;
}

.device-info {
    text-align: center;
}

.device-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.device-location {
    color: #6c757d;
    margin-bottom: 12px;
    font-size: 0.9rem;
}

.device-location i {
    margin-right: 5px;
    color: #e74c3c;
}

.last-checkin {
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.last-checkin .label {
    font-size: 0.8rem;
    color: #6c757d;
    display: block;
    margin-bottom: 2px;
}

.last-checkin .time {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}

.office-time {
    background: #e3f2fd;
    padding: 8px 12px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.office-time .label {
    font-size: 0.8rem;
    color: #1976d2;
    display: block;
    margin-bottom: 2px;
}

.office-time .time {
    font-weight: 600;
    color: #1976d2;
    font-size: 1.1rem;
}

.office-time .timezone {
    color: #64b5f6;
    font-size: 0.75rem;
    margin-left: 5px;
}

.discrepancy-alert {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    padding: 6px 10px;
    border-radius: 6px;
    margin-bottom: 10px;
    text-align: center;
}

.discrepancy-alert .label {
    font-size: 0.75rem;
    color: #856404;
    display: block;
    margin-bottom: 2px;
}

.discrepancy-alert .count {
    font-weight: 600;
    color: #856404;
    font-size: 1rem;
}

.device-details {
    margin-top: 10px;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .col-sm-12 {
        margin-bottom: 20px;
    }
    
    .device-card {
        padding: 15px;
    }
    
    .traffic-light {
        width: 50px;
        height: 50px;
    }
}

/* Status legend styling */
.traffic-light.online.legend {
    width: 20px;
    height: 20px;
    display: inline-block;
    margin-right: 8px;
}

.traffic-light.warning.legend {
    width: 20px;
    height: 20px;
    display: inline-block;
    margin-right: 8px;
}

.traffic-light.offline.legend {
    width: 20px;
    height: 20px;
    display: inline-block;
    margin-right: 8px;
}

.traffic-light.unknown.legend {
    width: 20px;
    height: 20px;
    display: inline-block;
    margin-right: 8px;
}
</style>

@endsection

@section('scripts')
<script>
function refreshData() {
    window.location.reload();
}

// Auto-refresh every 30 seconds
setInterval(function() {
    refreshData();
}, 30000);

// Add some interactivity
document.addEventListener('DOMContentLoaded', function() {
    const deviceCards = document.querySelectorAll('.device-card');
    
    deviceCards.forEach(card => {
        card.addEventListener('click', function() {
            // Add a subtle click effect
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'translateY(-5px)';
            }, 150);
        });
    });
});
</script>
@endsection
