<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $caminhoPedidos = 'data/pedidos.json';

    $novoPedido = [
        "pedido_id" => "#" . strtoupper(uniqid()),
        "usuario" => $_SESSION['usuario_nome'],
        "kit" => $_POST['kit_nome'],
        "valor" => $_POST['kit_preco'],
        "endereco" => [
            "cep" => $_POST['cep'],
            "cidade" => $_POST['cidade']
        ],
        "status" => "Aguardando Pagamento",
        "data" => date('d/m/Y H:i')
    ];

    if (!file_exists('data')) { mkdir('data', 0777, true); }
    
    $pedidosAtuais = file_exists($caminhoPedidos) ? json_decode(file_get_contents($caminhoPedidos), true) : [];
    $pedidosAtuais[] = $novoPedido;
    
    file_put_contents($caminhoPedidos, json_encode($pedidosAtuais, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "<h1>Pedido Realizado!</h1>";
    echo "<p>Obrigado, seu pedido <strong>{$novoPedido['pedido_id']}</strong> foi registrado.</p>";
    echo "<a href='index.php'>Voltar ao Portal</a>";
}
?>
