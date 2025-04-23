{{-- resources/views/auth/google-test.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pruebas de Autenticación con Google</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .card {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <h1 class="mb-4">Pruebas de Autenticación con Google</h1>

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Paso 1: Obtener un ID Token de Google</h5>
                    </div>
                    <div class="card-body">
                        <p>Para obtener un ID Token de Google, puedes usar el <a
                                href="https://developers.google.com/oauthplayground" target="_blank">OAuth 2.0
                                Playground de Google</a>.</p>
                        <ol>
                            <li>Selecciona "Google OAuth2 API v2"</li>
                            <li>Marca "userinfo.profile" y "userinfo.email"</li>
                            <li>Haz clic en "Authorize APIs"</li>
                            <li>Inicia sesión con tu cuenta de Google</li>
                            <li>En el paso 2, haz clic en "Exchange authorization code for tokens"</li>
                            <li>Copia el valor de "id_token"</li>
                        </ol>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Paso 2: Probar la Autenticación</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('google.test.process') }}" method="post">
                            @csrf
                            <div class="mb-3">
                                <label for="id_token" class="form-label">ID Token de Google</label>
                                <textarea name="id_token" id="id_token" rows="5" class="form-control" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Probar Autenticación</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Paso 3: Probar API de Usuario</h5>
                    </div>
                    <div class="card-body">
                        <p>Después de autenticarte correctamente, puedes probar la API para obtener los datos del
                            usuario:</p>
                        <a href="{{ route('google.test.user') }}" class="btn btn-info">Obtener Datos de Usuario</a>
                        <p class="mt-3">Esto realizará una solicitud a <code>GET /api/user</code> con el token de la
                            sesión actual.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Paso 4: Probar Cierre de Sesión</h5>
                    </div>
                    <div class="card-body">
                        <p>Para probar el cierre de sesión:</p>
                        <a href="{{ route('google.test.logout') }}" class="btn btn-danger">Cerrar Sesión</a>
                        <p class="mt-3">Esto realizará una solicitud a <code>POST /api/auth/logout</code> con el token
                            de la sesión actual.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Estado Actual</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Token en sesión:</strong>
                            @if (session('auth_token'))
                                <span class="text-success">Disponible</span>
                            @else
                                <span class="text-danger">No disponible</span>
                            @endif
                        </p>
                        @if (session('auth_token'))
                            <p><small>Token: {{ substr(session('auth_token'), 0, 20) }}...</small></p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
