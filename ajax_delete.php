<?php
session_start();
// Adicione sua verificação de admin aqui

if (isset($_POST['arquivo'])) {
    $arquivo = basename($_POST['arquivo']); // Segurança contra caminhos relativos
    $caminho = 'uploads/' . $arquivo;

    if (file_exists($caminho) && unlink($caminho)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Não foi possível excluir."]);
    }
    exit;
}
