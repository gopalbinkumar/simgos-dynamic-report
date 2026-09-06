@php
    try {
        DB::connection()->getPdo();

        $database = DB::connection()->getDatabaseName();

        $connected = true;
    } catch (\Exception $e) {
        $connected = false;
        $errorMessage = $e->getMessage();
    }

@endphp

<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 500px;
        }

        .success {
            color: #15803d;
            background: #dcfce7;
            padding: 15px;
            border-radius: 8px;
        }

        .error {
            color: #b91c1c;
            background: #fee2e2;
            padding: 15px;
            border-radius: 8px;
        }

        .info {
            margin-top: 20px;
            padding: 15px;
            background: #f3f4f6;
            border-radius: 8px;
        }
    </style>

</head>

<body>

    <div class="card">

        <h1>Database Connection Test</h1>

        @if ($connected)
            <div class="success">
                <strong>✓ Database Terhubung</strong>
                <p>Laravel berhasil terhubung ke MySQL/MariaDB.</p>
            </div>

            <div class="info">
                <p>
                    <strong>Database:</strong>
                    {{ $database }}
                </p>

                <p>
                    <strong>Status:</strong>
                    Connected
                </p>
            </div>
        @else
            <div class="error">
                <strong>✗ Database Tidak Terhubung</strong>

                <p>{{ $errorMessage }}</p>
            </div>
        @endif

    </div>

</body>

</html>
