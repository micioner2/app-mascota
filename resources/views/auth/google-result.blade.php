{{-- resources/views/auth/google-result.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de Autenticación con Google</title>
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
        <h1 class="mb-4">Resultado de Autenticación con Google</h1>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Respuesta de la API</h5>
            </div>
            <div class="card-body">
                <p><strong>Código de estado:</strong>
                    <span class="{{ $status >= 200 && $status < 300 ? 'text-success' : 'text-danger' }}">
                        {{ $status }}
                    </span>
                </p>

                <h6>Datos de respuesta:</h6>
                <pre>{{ json_encode($response, JSON_PRETTY_PRINT) }}</pre>

                @if (isset($response['token']))
                    <div class="alert alert-success">
                        <p><strong>¡Autenticación exitosa!</strong></p>
                        <p>El token ha sido guardado en la sesión y se usará automáticamente para las próximas pruebas.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('google.test') }}" class="btn btn-primary">Volver a la página de pruebas</a>
        </div>
    </div>
</body>

</html>
