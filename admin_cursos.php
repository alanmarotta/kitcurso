<?php
session_start();
$caminhoCursos = 'data/cursos.json';
$dirUploads = 'uploads/';
if (!is_dir($dirUploads)) mkdir($dirUploads, 0777, true);

$cursos = json_decode(file_get_contents($caminhoCursos), true) ?: [];
$cursoID = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : null;
$cursoIdx = -1;

if ($cursoID !== null) {
    foreach ($cursos as $idx => $c) {
        if ((int)$c['id'] === $cursoID) { $cursoIdx = $idx; break; }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['curso_idx'])) {
    $cIdx = (int)$_POST['curso_idx'];
    $mIdx = isset($_POST['modulo_idx']) ? (int)$_POST['modulo_idx'] : -1;

    if (isset($_POST['btn_excluir_cap'])) {
        array_splice($cursos[$cIdx]['modulos'], $mIdx, 1);
    } else {
        // 1. Processa Introdução (Seção 0)
        $capIntro = [];
        if (isset($_POST['cap_intro_tipo'])) {
            foreach ($_POST['cap_intro_tipo'] as $i => $tipo) {
                if (!empty($_POST['cap_intro_valor'][$i])) {
                    $capIntro[] = ["tipo" => $tipo, "valor" => $_POST['cap_intro_valor'][$i]];
                }
            }
        }
        // 2. Processa Seções
        $secoes = [];
        if (isset($_POST['sec_titulo'])) {
            foreach ($_POST['sec_titulo'] as $i => $titulo) {
                $listaCont = [];
                $prefixo = "sec_{$i}";
                if (isset($_POST[$prefixo . "_tipo"])) {
                    foreach ($_POST[$prefixo . "_tipo"] as $j => $tipo) {
                        if (!empty($_POST[$prefixo . "_valor"][$j])) {
                            $listaCont[] = ["tipo" => $tipo, "valor" => $_POST[$prefixo . "_valor"][$j]];
                        }
                    }
                }
                $secoes[] = [
                    "titulo_secao" => $titulo,
                    "conteudos" => $listaCont,
                    "pergunta" => $_POST['sec_perg'][$i] ?? "",
                    "resposta" => $_POST['sec_resp'][$i] ?? ""
                ];
            }
        }
        $dadosModulo = [
            "id" => ($mIdx !== -1) ? $cursos[$cIdx]['modulos'][$mIdx]['id'] : time(),
            "titulo" => $_POST['titulo_capitulo'],
            "conteudos_intro" => $capIntro,
            "secoes" => $secoes,
            "pergunta" => $_POST['pergunta_final'] ?? "",
            "resposta_correta" => $_POST['resposta_final'] ?? ""
        ];
        if ($mIdx !== -1) $cursos[$cIdx]['modulos'][$mIdx] = $dadosModulo;
        else $cursos[$cIdx]['modulos'][] = $dadosModulo;
    }
    file_put_contents($caminhoCursos, json_encode($cursos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: admin_cursos.php?curso_id=" . $cursos[$cIdx]['id']);
    exit;
}

$editM = ($cursoIdx !== -1 && isset($_GET['edit_m'])) ? $cursos[$cursoIdx]['modulos'][$_GET['edit_m']] : null;
$arquivosExistentes = array_diff(scandir($dirUploads), array('.', '..'));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin KitCurso - Full</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; display: flex; margin: 0; background: #f4f7f6; height: 100vh; }
        .sidebar { width: 260px; background: #2c3e50; color: white; padding: 20px; overflow-y: auto; }
        .main { flex: 1; padding: 30px; overflow-y: auto; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; border: 1px solid #ddd; }
        .secao-item { border-left: 5px solid #3498db; background: #fcfcfc; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee; position: relative; }
        .linha-conteudo { display: flex; gap: 15px; background: #fff; padding: 10px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 10px; align-items: center; }
        .preview-container { width: 70px; height: 70px; background: #f0f0f0; border-radius: 5px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd; flex-shrink: 0; }
        .preview-container img { width: 100%; height: 100%; object-fit: cover; }
        input, select, textarea { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; color: white; transition: 0.2s; }
        .btn-add { background: #2ecc71; }
        .btn-del { background: #e74c3c; font-size: 0.8em; }
        .btn-save { background: #3498db; width: 100%; font-size: 1.2em; padding: 20px; }
        #modalGaleria { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); display: none; justify-content: center; align-items: center; z-index: 10000; }
        .galeria-content { background: white; padding: 25px; border-radius: 12px; width: 85%; max-height: 85vh; overflow-y: auto; }
        .grid-galeria { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 15px; margin-top: 20px; }
        .img-item { border: 1px solid #ddd; padding: 8px; text-align: center; cursor: pointer; border-radius: 8px; position: relative; background: #fff; }
        .img-item img { width: 100%; height: 90px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>KitCurso Admin</h2>
    <?php foreach ($cursos as $c): ?>
        <a href="?curso_id=<?php echo $c['id']; ?>" style="color:<?php echo ($cursoID == $c['id'])?'#2ecc71':'#bdc3c7'; ?>; display:block; padding:10px; text-decoration:none;"><?php echo $c['titulo']; ?></a>
    <?php endforeach; ?>
</div>

<div class="main">
    <?php if ($cursoIdx !== -1): ?>
        <div class="card">
            <h3>Capítulos Gravados em: <?php echo $cursos[$cursoIdx]['titulo']; ?></h3>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <?php foreach ($cursos[$cursoIdx]['modulos'] ?? [] as $mIdx => $m): ?>
                    <a href="?curso_id=<?php echo $cursoID; ?>&edit_m=<?php echo $mIdx; ?>" style="background:#eee; padding:10px; text-decoration:none; color:#333; border-radius:5px; border:1px solid #ccc;"><?php echo ($mIdx+1); ?>. <?php echo $m['titulo']; ?></a>
                <?php endforeach; ?>
                <a href="?curso_id=<?php echo $cursoID; ?>" class="btn btn-add" style="text-decoration:none;">+ Novo Capítulo</a>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="curso_idx" value="<?php echo $cursoIdx; ?>">
            <?php if ($editM): ?> <input type="hidden" name="modulo_idx" value="<?php echo $_GET['edit_m']; ?>"> <?php endif; ?>

            <div class="card" style="border-top: 5px solid #2ecc71;">
                <h2>Título e Introdução do Capítulo (Seção 0)</h2>
                <input type="text" name="titulo_capitulo" value="<?php echo htmlspecialchars($editM['titulo'] ?? ''); ?>" placeholder="Ex: Fundamentos de Eletricidade" required>
                
                <div id="cap-intro-conts" style="margin-top:20px;">
                    <?php if ($editM && isset($editM['conteudos_intro'])): foreach ($editM['conteudos_intro'] as $j => $cont): $inputID = "cap_intro_{$j}"; ?>
                        <div class="linha-conteudo">
                            <div class="preview-container" id="prev_<?php echo $inputID; ?>">---</div>
                            <div style="flex:1">
                                <select name="cap_intro_tipo[]" onchange="atualizarPreview(document.getElementById('<?php echo $inputID; ?>'))">
                                    <option value="texto" <?php if($cont['tipo']=='texto') echo 'selected'; ?>>Texto / Sketch</option>
                                    <option value="video" <?php if($cont['tipo']=='video') echo 'selected'; ?>>Vídeo</option>
                                    <option value="imagem" <?php if($cont['tipo']=='imagem') echo 'selected'; ?>>Figura</option>
                                    <option value="simulacao" <?php if($cont['tipo']=='simulacao') echo 'selected'; ?>>Simulação</option>
                                </select>
                                <div style="display:flex; gap:5px;">
                                    <input type="text" name="cap_intro_valor[]" id="<?php echo $inputID; ?>" value="<?php echo htmlspecialchars($cont['valor']); ?>" oninput="atualizarPreview(this)">
                                    <button type="button" onclick="abrirGaleria('<?php echo $inputID; ?>')" class="btn" style="background:#444">🖼️</button>
                                    <button type="button" class="btn btn-del" onclick="this.closest('.linha-conteudo').remove()">🗑️</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
                <button type="button" class="btn btn-add" onclick="addConteudoIntro()">+ Adicionar Conteúdo à Intro</button>
            </div>

            <div id="secoes-container">
                <?php if ($editM && isset($editM['secoes'])): foreach ($editM['secoes'] as $i => $s): ?>
                    <div class="secao-item">
                        <button type="button" class="btn btn-del" style="float:right;" onclick="this.parentElement.remove()">Excluir Seção</button>
                        <strong>Sub-item (Seção):</strong>
                        <input type="text" name="sec_titulo[]" value="<?php echo htmlspecialchars($s['titulo_secao']); ?>">
                        
                        <div id="sec-<?php echo $i; ?>-conts">
                            <?php foreach ($s['conteudos'] ?? [] as $j => $cont): $inputID = "in_{$i}_{$j}"; ?>
                                <div class="linha-conteudo">
                                    <div class="preview-container" id="prev_<?php echo $inputID; ?>">---</div>
                                    <div style="flex:1">
                                        <select name="sec_<?php echo $i; ?>_tipo[]" onchange="atualizarPreview(document.getElementById('<?php echo $inputID; ?>'))">
                                            <option value="texto" <?php if($cont['tipo']=='texto') echo 'selected'; ?>>Texto / Sketch</option>
                                            <option value="video" <?php if($cont['tipo']=='video') echo 'selected'; ?>>Vídeo</option>
                                            <option value="imagem" <?php if($cont['tipo']=='imagem') echo 'selected'; ?>>Figura</option>
                                            <option value="simulacao" <?php if($cont['tipo']=='simulacao') echo 'selected'; ?>>Simulação</option>
                                        </select>
                                        <div style="display:flex; gap:5px;">
                                            <input type="text" name="sec_<?php echo $i; ?>_valor[]" id="<?php echo $inputID; ?>" value="<?php echo htmlspecialchars($cont['valor']); ?>" oninput="atualizarPreview(this)">
                                            <button type="button" onclick="abrirGaleria('<?php echo $inputID; ?>')" class="btn" style="background:#444">🖼️</button>
                                            <button type="button" class="btn btn-del" onclick="this.closest('.linha-conteudo').remove()">🗑️</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-add" style="background:#3498db; font-size:0.8em;" onclick="addConteudoSec(<?php echo $i; ?>)">+ Conteúdo na Seção</button>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:10px;">
                            <input type="text" name="sec_perg[]" value="<?php echo htmlspecialchars($s['pergunta']); ?>" placeholder="Pergunta da Seção">
                            <input type="text" name="sec_resp[]" value="<?php echo htmlspecialchars($s['resposta']); ?>" placeholder="Resposta Correta">
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
            <button type="button" class="btn btn-add" onclick="addNovaSecao()">+ Adicionar Nova Seção</button>

            <div class="card" style="background:#fff3cd; border: 1px solid #ffeeba;">
                <h3>🏁 Desafio Final para Concluir o Capítulo</h3>
                <input type="text" name="pergunta_final" value="<?php echo htmlspecialchars($editM['pergunta'] ?? ''); ?>" placeholder="Pergunta que encerra o capítulo">
                <input type="text" name="resposta_final" value="<?php echo htmlspecialchars($editM['resposta_correta'] ?? ''); ?>" placeholder="Resposta correta">
            </div>

            <button type="submit" class="btn btn-save">SALVAR TODAS AS ALTERAÇÕES</button>
            <?php if ($editM): ?>
                <button type="submit" name="btn_excluir_cap" class="btn btn-del" style="width:100%; margin-top:10px;" onclick="return confirm('Excluir este capítulo?')">EXCLUIR CAPÍTULO COMPLETO</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<!-- Modal Galeria -->
<div id="modalGaleria">
    <div class="galeria-content">
        <h3>Galeria de Mídia <button type="button" class="btn btn-del" onclick="fecharGaleria()" style="float:right;">Fechar X</button></h3>
        <div style="background:#f8f9fa; padding:15px; border-radius:8px; margin:15px 0; border: 1px dashed #ccc;">
            <input type="file" id="ajax_file" style="width:auto;">
            <button type="button" class="btn btn-add" id="btnUpload" onclick="fazerUploadAjax()">Subir Arquivo</button>
        </div>
        <div class="grid-galeria" id="gridImagens">
            <?php foreach ($arquivosExistentes as $arq): ?>
                <div class="img-item" id="file-<?php echo md5($arq); ?>" onclick="selecionarDaGaleria('<?php echo $arq; ?>')">
                    <button type="button" class="btn-del" style="position:absolute; top:0; right:0;" onclick="excluirArquivo('<?php echo $arq; ?>','<?php echo md5($arq); ?>', event)">X</button>
                    <?php $ext = strtolower(pathinfo($arq, PATHINFO_EXTENSION));
                    if(in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                        <img src="uploads/<?php echo $arq; ?>">
                    <?php else: ?>
                        <div style="height:90px; display:flex; align-items:center; justify-content:center; background:#eee;"><?php echo strtoupper($ext); ?></div>
                    <?php endif; ?>
                    <span style="font-size:10px; display:block; margin-top:5px;"><?php echo $arq; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
let campoAlvo = null;
let secaoCount = <?php echo isset($editM['secoes']) ? count($editM['secoes']) : 0; ?>;

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('input[name*="_valor[]"]').forEach(input => atualizarPreview(input));
});

function abrirGaleria(id) { campoAlvo = document.getElementById(id); document.getElementById('modalGaleria').style.display='flex'; }
function fecharGaleria() { document.getElementById('modalGaleria').style.display='none'; }
function selecionarDaGaleria(nome) { if(campoAlvo) { campoAlvo.value = nome; atualizarPreview(campoAlvo); fecharGaleria(); } }

function atualizarPreview(el) {
    const prev = document.getElementById('prev_' + el.id);
    if (!prev) return;
    const tipo = el.closest('.linha-conteudo').querySelector('select').value;
    const valor = el.value.trim();
    if (valor === "") { prev.innerHTML = "---"; return; }

    switch (tipo) {
        case 'imagem':
            const ext = valor.split('.').pop().toLowerCase();
            if(['jpg','jpeg','png','gif','webp'].includes(ext)) {
                prev.innerHTML = `<img src="uploads/${valor}" style="width:100%; height:100%; object-fit:cover;">`;
            } else { prev.innerHTML = "<span>DOC</span>"; }
            break;
        case 'video': prev.innerHTML = "<span>🎬</span>"; break;
        case 'simulacao': prev.innerHTML = "<span>⚙️</span>"; break;
        case 'texto': prev.innerHTML = valor.toLowerCase().endsWith('.ino') ? "<span style='color:#00878F'>∞</span>" : "<span>📄</span>"; break;
        default: prev.innerHTML = "---";
    }
}

function fazerUploadAjax() {
    const fileInput = document.getElementById('ajax_file');
    if(fileInput.files.length === 0) return alert("Selecione um arquivo");
    const fd = new FormData(); fd.append('nova_midia', fileInput.files[0]);
    fetch('ajax_upload.php', { method:'POST', body:fd }).then(r => r.json()).then(data => {
        if(data.status==='success') {
            location.reload(); // Para atualizar a galeria com o novo arquivo
        } else { alert(data.message); }
    });
}

function excluirArquivo(nome, id, e) {
    e.stopPropagation();
    if(!confirm("Excluir permanentemente?")) return;
    const fd = new FormData(); fd.append('arquivo', nome);
    fetch('ajax_upload.php?del=1', { method:'POST', body:fd }).then(() => document.getElementById('file-'+id).remove());
}

function addConteudoIntro() {
    const id = Date.now();
    const html = `<div class="linha-conteudo"><div class="preview-container" id="prev_cap_intro_${id}">---</div><div style="flex:1"><select name="cap_intro_tipo[]" onchange="atualizarPreview(document.getElementById('cap_intro_${id}'))"><option value="texto">Texto / Sketch</option><option value="video">Vídeo</option><option value="imagem">Figura</option><option value="simulacao">Simulação</option></select><div style="display:flex; gap:5px;"><input type="text" name="cap_intro_valor[]" id="cap_intro_${id}" oninput="atualizarPreview(this)"><button type="button" onclick="abrirGaleria('cap_intro_${id}')" class="btn" style="background:#444">🖼️</button><button type="button" class="btn btn-del" onclick="this.closest('.linha-conteudo').remove()">🗑️</button></div></div></div>`;
    document.getElementById('cap-intro-conts').insertAdjacentHTML('beforeend', html);
}

function addConteudoSec(idx) {
    const id = Date.now();
    const inputID = `in_${idx}_${id}`;
    const html = `<div class="linha-conteudo"><div class="preview-container" id="prev_${inputID}">---</div><div style="flex:1"><select name="sec_${idx}_tipo[]" onchange="atualizarPreview(document.getElementById('${inputID}'))"><option value="texto">Texto / Sketch</option><option value="video">Vídeo</option><option value="imagem">Figura</option><option value="simulacao">Simulação</option></select><div style="display:flex; gap:5px;"><input type="text" name="sec_${idx}_valor[]" id="${inputID}" oninput="atualizarPreview(this)"><button type="button" onclick="abrirGaleria('${inputID}')" class="btn" style="background:#444">🖼️</button><button type="button" class="btn btn-del" onclick="this.closest('.linha-conteudo').remove()">🗑️</button></div></div></div>`;
    document.getElementById(`sec-${idx}-conts`).insertAdjacentHTML('beforeend', html);
}

function addNovaSecao() {
    const html = `<div class="secao-item"><button type="button" class="btn btn-del" style="float:right;" onclick="this.parentElement.remove()">Excluir Seção</button><strong>Título do Sub-item:</strong><input type="text" name="sec_titulo[]" placeholder="Ex: Nova Seção"><div id="sec-${secaoCount}-conts"></div><button type="button" class="btn btn-add" style="background:#3498db; font-size:0.8em;" onclick="addConteudoSec(${secaoCount})">+ Adicionar Conteúdo</button><div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:15px;"><input type="text" name="sec_perg[]" placeholder="Pergunta"><input type="text" name="sec_resp[]" placeholder="Resposta"></div></div>`;
    document.getElementById('secoes-container').insertAdjacentHTML('beforeend', html);
    secaoCount++;
}
</script>
</body>
</html>
