<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $caminhoArquivo = 'data/usuarios.json';

    // 1. Captura os dados do formulário
    $novoUsuario = [
        "id" => uniqid(),
        "nome" => $_POST['nome'],
        "email" => $_POST['email'],
        "senha" => password_hash($_POST['senha'], PASSWORD_DEFAULT),
        "data_cadastro" => date('Y-m-d H:i:s')
    ];

    // 2. Verifica se a pasta e o arquivo existem, se não, cria
    if (!file_exists('data')) { mkdir('data', 0777, true); }
    if (!file_exists($caminhoArquivo)) { file_put_contents($caminhoArquivo, json_encode([])); }

    // 3. Lê os usuários atuais
    $usuariosAtuais = json_decode(file_get_contents($caminhoArquivo), true);

    // 4. Verifica se o e-mail já existe
    foreach ($usuariosAtuais as $user) {
        if ($user['email'] === $novoUsuario['email']) {
            die("Erro: Este e-mail já está cadastrado. <a href='cadastro.php'>Voltar</a>");
        }
    }

    // 5. Adiciona e salva
    $usuariosAtuais[] = $novoUsuario;
    file_put_contents($caminhoArquivo, json_encode($usuariosAtuais, JSON_PRETTY_PRINT));

    echo "Sucesso! Agora você já pode acessar o KitCurso. <a href='index.php'>Ir para o Portal</a>";
}
?>
