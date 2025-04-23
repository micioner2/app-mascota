<!DOCTYPE html>
<html>

<head>
    <title>Prueba de Autenticación Google</title>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
        }

        button {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <h1>Obtener Token de Google para Pruebas</h1>

    <div id="g_id_onload" data-client_id="{{ $clientId }}" data-callback="handleCredentialResponse" data-auto_prompt="false">
    </div>

    <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline" data-text="sign_in_with" data-shape="rectangular" data-logo_alignment="left">
    </div>

    <div>
        <h3>ID Token:</h3>
        <pre id="token-display">{{ $tokenMessage }}</pre>
        <button id="copy-button" onclick="copyToken()" {{ $buttonDisabled }}>Copiar Token</button>
    </div>

    <script>
        function handleCredentialResponse(response) {
            const token = response.credential;
            document.getElementById('token-display').textContent = token;
            document.getElementById('copy-button').disabled = false;
            console.log("ID Token:", token);

            // Opcionalmente enviar el token al servidor
            fetch('{{ route("gmail.saveToken") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ token: token })
            })
            .then(response => response.json())
            .then(data => console.log('Token guardado:', data))
            .catch(error => console.error('Error al guardar token:', error));
        }

        function copyToken() {
            const tokenText = document.getElementById('token-display').textContent;
            navigator.clipboard.writeText(tokenText)
                .then(() => alert('Token copiado al portapapeles'))
                .catch(err => console.error('Error al copiar:', err));
        }
    </script>
</body>

</html>
