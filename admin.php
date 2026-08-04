<?php
session_start();

// Configurações de pastas
$pathCursos = 'data/cursos.json';
$dirUploads = 'uploads/';

// Cria as pastas se não existirem
if (!is_dir('data')) mkdir('data', 0777, true);
if (!is_dir($dirUploads)) mkdir($dirUploads, 0777, true);

$cursos = file_exists($pathCursos) ? json_decode(file_get_contents($pathCursos), true) : [];

// --- LÓGICA DE SALVAMENTO ---
if (isset($_POST['salvar_curso'])) {
    $id = $_POST['curso_id'] !== "" ? (int)$_POST['curso_id'] : time();
    
    // Tratamento da Imagem (Upload)
    $imagemNome = $_POST['imagem_atual'] ?? ''; // Mantém a antiga se não subir nova
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $imagemNome = 'curso_' . $id . '.' . $extensao;
        move_uploaded_file($_FILES['foto']['tmp_name'], $dirUploads . $imagemNome);
    }

    // Processa os múltiplos kits
    $kitsProcessados = [];
    if (isset($_POST['kit_nome'])) {
        foreach ($_POST['kit_nome'] as $index => $nomeKit) {
            if (!empty($nomeKit)) {
                $precoRaw = $_POST['kit_preco'][$index];
                $kitsProcessados[] = [
                    "nome" => $nomeKit,
                    "preco" => "R$ " . number_format((float)str_replace(['.', ','], ['', '.'], $precoRaw), 2, ',', '.')
                ];
            }
        }
    }

    $novoCurso = [
        "id" => $id,
        "titulo" => $_POST['titulo'],
        "tag" => $_POST['tag'],
        "imagem" => $imagemNome, // Salva o nome do arquivo no JSON
        "kits" => $kitsProcessados
    ];

    if (!empty($_POST['curso_id'])) {
        foreach ($cursos as $key => $c) {
            if ($c['id'] == $id) $cursos[$key] = $novoCurso;
        }
    } else {
        $cursos[] = $novoCurso;
    }
    
    file_put_contents($pathCursos, json_encode(array_values($cursos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: admin.php");
    exit;
}

// --- EXCLUSÃO ---
if (isset($_GET['excluir_curso'])) {
    $cursos = array_filter($cursos, fn($c) => $c['id'] != $_GET['excluir_curso']);
    file_put_contents($pathCursos, json_encode(array_values($cursos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: admin.php");
    exit;
}

$cursoEdicao = null;
if (isset($_GET['editar_curso'])) {
    foreach ($cursos as $c) {
        if ($c['id'] == $_GET['editar_curso']) $cursoEdicao = $c;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin KitCurso - Upload de Fotos</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .form-row { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .kit-row { display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; background: #f9f9f9; padding: 10px; margin-bottom: 5px; border-radius: 4px; }
        input[type="text"], input[type="file"] { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #007bff; color: white; }
        .img-preview { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        .btn { padding: 8px 12px; border-radius: 4px; text-decoration: none; border: none; cursor: pointer; }
        .btn-save { background: #28a745; color: white; width: 100%; font-size: 16px; margin-top: 10px; }
        .btn-del { background: #dc3545; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h1>Gerenciar Cursos e Fotos</h1>

    <div class="card">
        <h3><?= $cursoEdicao ? "Editar Curso" : "Novo Curso" ?></h3>
        <!-- IMPORTANTE: enctype para permitir envio de arquivos -->
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="curso_id" value="<?= $cursoEdicao['id'] ?? '' ?>">
            <input type="hidden" name="imagem_atual" value="<?= $cursoEdicao['imagem'] ?? '' ?>">
            
            <div class="form-row">
                <div>
                    <label>Título</label>
                    <input type="text" name="titulo" value="<?= $cursoEdicao['titulo'] ?? '' ?>" required>
                </div>
                <div>
                    <label>Tag</label>
                    <input type="text" name="tag" value="<?= $cursoEdicao['tag'] ?? '' ?>" required>
                </div>
                <div>
                    <label>Foto do Curso</label>
                    <input type="file" name="foto" accept="image/*">
                </div>
            </div>

            <h4>Kits do Curso</h4>
            <div id="kits-container">
                <?php 
                $kitsIniciais = $cursoEdicao['kits'] ?? [['nome' => '', 'preco' => '']];
                foreach ($kitsIniciais as $kit): 
                    $precoFormatado = str_replace(['R$ ', '.', ','], ['', '', '.'], $kit['preco']);
                ?>
                <div class="kit-row">
                    <input type="text" name="kit_nome[]" placeholder="Nome do Kit" value="<?= $kit['nome'] ?>" required>
                    <input type="text" name="kit_preco[]" placeholder="Preço (ex: 95,00)" value="<?= $precoFormatado ?>" required>
                    <button type="button" class="btn btn-del" onclick="this.parentElement.remove()">X</button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" onclick="addKit()" style="margin-top:10px">+ Adicionar Kit</button>
            <button type="submit" name="salvar_curso" class="btn btn-save">Salvar Curso e Foto</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Título / Tag</th>
                    <th>Kits e Preços</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cursos as $c): ?>
                <tr>
                    <td>
                        <?php if(!empty($c['imagem'])): ?>
                            <img src="uploads/<?= $c['imagem'] ?>" class="img-preview">
                        <?php else: ?>
                            <div style="width:50px;height:50px;background:#eee;display:flex;align-items:center;justify-content:center;font-size:10px">Sem foto</div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= $c['titulo'] ?></strong><br><small><?= $c['tag'] ?></small></td>
                    <td>
                        <?php foreach($c['kits'] as $k): ?>
                            <small>• <?= $k['nome'] ?>: <?= $k['preco'] ?></small><br>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <a href="?editar_curso=<?= $c['id'] ?>" class="btn" style="background:#ffc107">Editar</a>
                        <a href="?excluir_curso=<?= $c['id'] ?>" class="btn btn-del" onclick="return confirm('Excluir?')">X</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function addKit() {
    const container = document.getElementById('kits-container');
    const newRow = document.createElement('div');
    newRow.className = 'kit-row';
    newRow.innerHTML = `
        <input type="text" name="kit_nome[]" placeholder="Nome do Kit" required>
        <input type="text" name="kit_preco[]" placeholder="Preço (ex: 95,00)" required>
        <button type="button" class="btn btn-del" onclick="this.parentElement.remove()">X</button>
    `;
    container.appendChild(newRow);
}
</script>

</body>
</html>
