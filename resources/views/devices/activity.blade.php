@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <form method="GET" action="{{ route('devices.activity', ['id' => $id ]) }}">
        <label for="range">Select Time Range:</label>
        <select name="range" id="range" onchange="this.form.submit()">
            <option value="1h" {{ $range === '1h' ? 'selected' : '' }}>Last 1 Hour</option>
            <option value="6h" {{ $range === '6h' ? 'selected' : '' }}>Last 6 Hours</option>
            <option value="1d" {{ $range === '1d' ? 'selected' : '' }}>Last 24 Hours</option>
            <option value="7d" {{ $range === '7d' ? 'selected' : '' }}>Last 7 Days</option>
            <option value="30d" {{ $range === '30d' ? 'selected' : '' }}>Last 30 Days</option>
            <option value="90d" {{ $range === '90d' ? 'selected' : '' }}>Last 90 Days</option>
            <option value="all" {{ $range === 'all' ? 'selected' : '' }}>All</option>
        </select>
    </form>

    <canvas id="deviceChart" width="400" height="140"></canvas>

    <script>
    const ctx = document.getElementById('deviceChart').getContext('2d');

    const labels = {!! json_encode(array_keys($data)) !!};
    const counts = {!! json_encode(array_values($data)) !!};

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Reports per {{ $range === "1h" || $range === "6h" || $range === "1d" ? "Minute" : ($range === "7d" ? "Hour" : "Day") }}',
                data: counts,
                borderWidth: 2,
                fill: false,
                borderColor: 'blue',
                tension: 0.3,
                pointRadius: 2,
            }]
        },
        options: {
            scales: {
                x: {
                    title: { display: true, text: 'Time' },
                    ticks: {
                        maxRotation: 90,
                        minRotation: 45
                    }
                },
                y: {
                    title: { display: true, text: 'Number of Reports' },
                    beginAtZero: true
                }
            }
        }
    });
    </script>
@endsection