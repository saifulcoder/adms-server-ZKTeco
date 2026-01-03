<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesando Fix</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .container {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        @if(isset($success))
            @if($success)
                <div class="success">
                    <h2>✓ Éxito</h2>
                    <p>{{ $message }}</p>
                </div>
            @else
                <div class="error">
                    <h2>✗ Error</h2>
                    <p>{{ $message }}</p>
                </div>
            @endif
        @else
            <div>
                <div class="spinner"></div>
                <h2>Procesando...</h2>
                <p>Por favor espera un momento</p>
            </div>
        @endif
    </div>

    @if(isset($success))
    <script>
        // Close window after 2 seconds
        setTimeout(function() {
            window.close();
            
            // Fallback: if the window doesn't close, redirect to parent
            setTimeout(function() {
                if (!document.hidden) {
                    window.location.href = '{{ route("devices.attendance") }}';
                }
            }, 1000);
        }, 2000);
    </script>
    @endif
</body>
</html>

