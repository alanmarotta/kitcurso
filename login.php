<?php
session_start();

// Simulação de login simples (ajuste conforme seu banco de dados/JSON futuramente)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    if (!empty($usuario)) {
        $_SESSION['usuario_nome'] = $usuario;
        header("Location: meus_cursos.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KitCurso</title>
    <style>
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --bg-dark: #2c3e50;
            --text-light: #ecf0f1;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: var(--bg-dark) url('https://transparenttextures.com');
            color: #333;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .logo-area {
            margin-bottom: 30px;
        }

        .logo-area h1 {
            color: var(--bg-dark);
            margin: 0;
            font-size: 2.5em;
        }

        .logo-area span {
            color: var(--primary);
        }

        .login-card p {
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn-login {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: var(--primary-dark);
        }

        .footer-links {
            margin-top: 25px;
            font-size: 0.9em;
            color: #95a5a6;
        }

        .footer-links a {
            color: var(--bg-dark);
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-area">
        <h1>Kit<span>Curso</span></h1>
    </div>
    <p>Bem-vindo à sua jornada tecnológica. Entre para continuar seus estudos.</p>

    <form method="POST">
        <div class="form-group">
            <label>Seu Nome ou Usuário</label>
            <input type="text" name="usuario" placeholder="Ex: João Silva" required>
        </div>
        
        <div class="form-group">
            <label>Sua Senha</label>
            <input type="password" name="senha" placeholder="••••••••">
        </div>

        <button type="submit" class="btn-login">ACESSAR PLATAFORMA</button>
    </form>

    <div class="footer-links">
        Ainda não tem acesso? <a href="#">Crie sua conta</a>
    </div>
</div>

</body>
</html>
