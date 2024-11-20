<!DOCTYPE html>
<html>
<head>
    <title>Redefinição de Senha</title>
</head>
<body>
    <p>Olá,</p>
    <p>Você solicitou a redefinição de sua senha da sua conta IntegraVagas. Clique no link abaixo para redefini-la:</p>
    <p>
        <a href="{{ url('http://localhost:3000/trocar-senha?token=' . $token) }}">Redefinir senha</a>
    </p>
    <p>Se você não solicitou esta ação, ignore este e-mail.</p>
</body>
</html>