<?php
session_start();
if (!isset($_SESSION['usuario_nome'])) {
    header("Location: login.php");
    exit;
}

// Captura os dados do kit selecionado (vindo da página do curso)
$kitNome = $_GET['kit'] ?? 'Kit Básico';
$kitPreco = $_GET['preco'] ?? '0,00';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Checkout - KitCurso</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .checkout-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .resumo-pedido { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-finalizar { background: #28a745; color: white; padding: 15px; width: 100%; border: none; border-radius: 5px; font-size: 18px; cursor: pointer; }
        .btn-finalizar:hover { background: #218838; }
    </style>
</head>
<body>

<div class="checkout-container">
    <h2>Finalizar Adquisição do Kit</h2>
    
    <div class="resumo-pedido">
        <strong>Produto:</strong> <?php echo htmlspecialchars($kitNome); ?><br>
        <strong>Valor:</strong> <?php echo htmlspecialchars($kitPreco); ?><br>
        <strong>Comprador:</strong> <?php echo $_SESSION['usuario_nome']; ?>
    </div>

    <form action="processa_pedido.php" method="POST">
        <input type="hidden" name="kit_nome" value="<?php echo $kitNome; ?>">
        <input type="hidden" name="kit_preco" value="<?php echo $kitPreco; ?>">

        <h3>Endereço de Entrega</h3>
        <input type="text" name="cep" placeholder="CEP" required>
        <input type="text" name="logradouro" placeholder="Rua/Avenida" required>
        <input type="text" name="numero" placeholder="Número" required>
        <input type="text" name="bairro" placeholder="Bairro" required>
        <input type="text" name="cidade" placeholder="Cidade" required>
        
        <h3>Pagamento (Simulação)</h3>
        <select name="metodo_pagamento">
            <option value="pix">PIX (Aprovação imediata)</option>
            <option value="cartao">Cartão de Crédito</option>
            <option value="boleto">Boleto Bancário</option>
        </select>

        <button type="submit" class="btn-finalizar">Confirmar e Pagar</button>
    </form>
</div>

</body>
</html>
