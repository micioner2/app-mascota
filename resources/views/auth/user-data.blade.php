{{-- resources/views/auth/user-data.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow: auto;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <h1 class="mb-4">Datos de Usuario</h1>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Respuesta de la API</h5>
            </div>
            <div class="card-body">
                @if (isset($error))
                    <div class="alert alert-danger">
                        <h6>Error:</h6>
                        <p>{{ $error }}</p>
                        <p><strong>Código:</strong> {{ $status }}</p>
                    </div>
                @else
                    <p><strong>Código de estado:</strong>
                        <span class="{{ $status >= 200 && $status < 300 ? 'text-success' : 'text-danger' }}">
                            {{ $status }}
                        </span>
                    </p>

                    <h6>Datos de usuario:</h6>
                    <pre>{{ json_encode($userData, JSON_PRETTY_PRINT) }}</pre>
                @endif
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('google.test') }}" class="btn btn-primary">Volver a la página de pruebas</a>
        </div>
    </div>
</body>

</html>
