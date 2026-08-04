<?php
$caminhoCursos = 'data/cursos.json';
$dirUploads = 'uploads/';

// Se o arquivo não existir, cria com dados iniciais (sem imagem por padrão)
if (!file_exists($caminhoCursos)) {
    $dadosIniciais = [
        ["id" => 1, "titulo" => "Introdução à Eletricidade", "tag" => "Gratuito", "imagem" => "", "kits" => [["nome" => "Kit Componentes Básicos", "preco" => "R$ 45,00"]]],
        ["id" => 2, "titulo" => "Introdução à Programação Arduino", "tag" => "Gratuito", "imagem" => "", "kits" => [["nome" => "Kit Arduino Uno R3", "preco" => "R$ 120,00"]]],
        ["id" => 3, "titulo" => "Introdução à Robótica", "tag" => "Premium", "imagem" => "", "kits" => [["nome" => "Kit Braço Robótico", "preco" => "R$ 350,00"]]],
        ["id" => 4, "titulo" => "Internet das Coisas (IOT)", "tag" => "Premium", "imagem" => "", "kits" => [["nome" => "Kit NodeMCU + Sensores", "preco" => "R$ 180,00"]]],
        ["id" => 5, "titulo" => "Telecomunicações", "tag" => "Premium", "imagem" => "", "kits" => [["nome" => "Kit Transmissor FM", "preco" => "R$ 95,00"]]]
    ];
    if (!file_exists('data')) { mkdir('data', 0777, true); }
    file_put_contents($caminhoCursos, json_encode($dadosIniciais, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$cursos = json_decode(file_get_contents($caminhoCursos), true);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal KitCurso</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
        header { text-align: center; margin-bottom: 40px; }
        
        .grid-cursos { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; max-width: 1200px; margin: 0 auto; }
        
        .card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.2s; display: flex; flex-direction: column; }
        .card:hover { transform: translateY(-5px); }
        
        /* Estilo da Imagem */
        .card-image { width: 100%; height: 180px; object-fit: cover; background: #ddd; }
        .no-image { width: 100%; height: 180px; background: #eee; display: flex; align-items: center; justify-content: center; color: #999; font-style: italic; }
        
        .card-content { padding: 20px; flex-grow: 1; }
        
        .tag { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        .gratuito { background: #d4edda; color: #155724; }
        .premium { background: #fff3cd; color: #856404; }
        
        h3 { margin: 0 0 10px 0; color: #333; font-size: 1.2rem; }
        
        .kit-box { background: #f8f9fa; border-left: 4px solid #007bff; padding: 12px; margin-top: 15px; font-size: 13px; border-radius: 0 4px 4px 0; }
        .kit-box strong { color: #555; }
        
        .btn-acesso { display: block; text-align: center; background: #007bff; color: white; padding: 12px; border-radius: 6px; text-decoration: none; margin-top: 20px; font-weight: bold; transition: background 0.3s; }
        .btn-acesso:hover { background: #0056b3; }
    </style>
</head>
<body>

<header>
    <h1>Portal KitCurso</h1>
    <p>Aprenda tecnologia com teoria e prática (Kits Físicos)</p>
</header>

<div class="grid-cursos">
    <?php foreach ($cursos as $curso): ?>
        <div class="card">
            <!-- EXIBIÇÃO DA FOTO -->
            <?php if (!empty($curso['imagem']) && file_exists($dirUploads . $curso['imagem'])): ?>
                <img src="<?php echo $dirUploads . $curso['imagem']; ?>" alt="<?php echo $curso['titulo']; ?>" class="card-image">
            <?php else: ?>
                <div class="no-image">Sem imagem disponível</div>
            <?php endif; ?>

            <div class="card-content">
                <span class="tag <?php echo strtolower($curso['tag']); ?>">
                    <?php echo $curso['tag']; ?>
                </span>
                
                <h3><?php echo $curso['titulo']; ?></h3>
                
                <div class="kit-box">
                    <strong>Kits Disponíveis:</strong><div style="margin-top:5px"></div>
                    <?php foreach ($curso['kits'] as $kit): ?>
                        <div style="margin-bottom: 3px;">• <?php echo $kit['nome']; ?> (<strong><?php echo $kit['preco']; ?></strong>)</div>
                    <?php endforeach; ?>
                </div>

                
                <!-- Procure por esta linha no final do seu loop foreach -->
                <a href="login.php?curso_id=<?php echo $curso['id']; ?>" class="btn-acesso">Acessar Curso</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
