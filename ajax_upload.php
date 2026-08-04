<?php
session_start();

// Configurações
$dirUploads = 'uploads/';
$permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ino', 'pdf'];

// Garante que a pasta existe
if (!is_dir($dirUploads)) {
    mkdir($dirUploads, 0777, true);
}

// --- LÓGICA DE EXCLUSÃO ---
if (isset($_GET['del']) && isset($_POST['arquivo'])) {
    $arquivoParaDeletar = basename($_POST['arquivo']); // Segurança contra caminhos relativos
    $caminhoCompleto = $dirUploads . $arquivoParaDeletar;

    if (file_exists($caminhoCompleto)) {
        if (unlink($caminhoCompleto)) {
            echo json_encode(["status" => "success", "message" => "Arquivo excluído."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erro ao excluir do servidor."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Arquivo não encontrado."]);
    }
    exit;
}

// --- LÓGICA DE UPLOAD ---
if (isset($_FILES['nova_midia'])) {
    $file = $_FILES['nova_midia'];
    $nomeOriginal = basename($file['name']);
    
    // 1. Limpeza do nome do arquivo (remove espaços e caracteres especiais)
    $nomeLimpo = preg_replace("/[^a-zA-Z0-9._-]/", "_", $nomeOriginal);
    $extensao = strtolower(pathinfo($nomeLimpo, PATHINFO_EXTENSION));

    // 2. Validação de Extensão
    if (!in_array($extensao, $permitidos)) {
        echo json_encode([
            "status" => "error", 
            "message" => "Extensão .$extensao não permitida. Use: " . implode(', ', $permitidos)
        ]);
        exit;
    }

    // 3. Caminho de Destino
    $caminhoFinal = $dirUploads . $nomeLimpo;

    // 4. Executa o Upload
    if (move_uploaded_file($file['tmp_name'], $caminhoFinal)) {
        echo json_encode([
            "status" => "success",
            "nome" => $nomeLimpo,
            "url" => $caminhoFinal,
            "tipo" => $extensao
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Erro interno ao mover o arquivo para a pasta uploads."
        ]);
    }
    exit;
}

// Se acessar o arquivo diretamente sem POST
echo json_encode(["status" => "error", "message" => "Nenhuma ação enviada."]);
