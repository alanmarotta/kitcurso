<?php $idCurso = $_GET['curso_id'] ?? ''; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - KitCurso</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; justify-content: center; padding-top: 50px; }
        .form-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background: #218838; }
    </style>
</head>
<body>
  <div class="form-container">
    <h2>Criar Conta KitCurso</h2>
    <form action="processa_cadastro.php" method="POST">
        <input type="hidden" name="curso_id" value="<?php echo $idCurso; ?>">
        <input type="text" name="nome" placeholder="Nome Completo" required>
        <input type="email" name="email" placeholder="Seu E-mail" required>
        <input type="password" name="senha" placeholder="Crie uma Senha" required>
        <button type="submit">Cadastrar e Acessar</button>
    </form>
    <p style="text-align:center; font-size:14px;">
        Já tem uma conta? <a href="login.php?curso_id=<?php echo $idCurso; ?>">Faça Login</a>
    </p>
  </div>
</body>
</html>
