<?php
session_start();
if (!isset($_SESSION['usuario_nome'])) { header("Location: login.php"); exit; }

$usuario = $_SESSION['usuario_nome'];
$idCurso = $_GET['id'] ?? 1;
$aulaAtivaIdx = (int)($_GET['aula'] ?? 0);
$secaoAtivaIdx = isset($_GET['secao']) ? (int)$_GET['secao'] : -1; // -1 = Introdução

$caminhoProgresso = 'data/progresso.json';
$caminhoCursos = 'data/cursos.json';
$dirUploads = 'uploads/';

// --- FUNÇÕES DE APOIO ---
function carregarDados($path) { return file_exists($path) ? json_decode(file_get_contents($path), true) : []; }
function salvarDados($dados, $path) { file_put_contents($path, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); }
function romanic($n) {
    $r = ''; $map = ['L'=>50,'XL'=>40,'X'=>10,'IX'=>9,'V'=>5,'IV'=>4,'I'=>1];
    foreach($map as $rom=>$arb) { while($n >= $arb) { $r .= $rom; $n -= $arb; } } return $r;
}

$cursos = carregarDados($caminhoCursos);
$dadosProgresso = carregarDados($caminhoProgresso);

$cursoAtual = null;
foreach ($cursos as $c) { if ((int)$c['id'] == (int)$idCurso) { $cursoAtual = $c; break; } }
if (!$cursoAtual) die("Curso não encontrado.");

$progressoMax = $dadosProgresso[$usuario][$idCurso]['nivel'] ?? 0;
$secoesConcluidas = $dadosProgresso[$usuario][$idCurso]['secoes_concluidas'] ?? [];

// Cálculos de Progresso Geral para a Sidebar
$totalGeralSecoes = 0; $totalGeralConcluidas = 0;
foreach ($cursoAtual['modulos'] as $mIdx => $modulo) {
    if(isset($modulo['secoes'])){
        foreach ($modulo['secoes'] as $sIdx => $secao) {
            $totalGeralSecoes++;
            if (in_array("c{$idCurso}_a{$mIdx}_s{$sIdx}", $secoesConcluidas)) $totalGeralConcluidas++;
        }
    }
}
$porcentagemGeral = ($totalGeralSecoes > 0) ? round(($totalGeralConcluidas / $totalGeralSecoes) * 100) : 0;

$capituloExibido = $cursoAtual['modulos'][$aulaAtivaIdx];

// --- DEFINIÇÃO DO CONTEÚDO ---
if ($secaoAtivaIdx === -1) {
    $conteudosParaExibir = $capituloExibido['conteudos_intro'] ?? [];
    $tituloPagina = "Introdução: " . $capituloExibido['titulo'];
    $isConcluida = true; 
} else {
    $secaoExibida = $capituloExibido['secoes'][$secaoAtivaIdx];
    $conteudosParaExibir = $secaoExibida['conteudos'] ?? [];
    $tituloPagina = $secaoExibida['titulo_secao'];
    $idUnicoAtivo = "c{$idCurso}_a{$aulaAtivaIdx}_s{$secaoAtivaIdx}";
    $isConcluida = in_array($idUnicoAtivo, $secoesConcluidas);
}

// --- PROCESSAMENTO DE RESPOSTAS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['resp_micro']) && $secaoAtivaIdx !== -1) {
        if (strtolower(trim($_POST['resp_micro'])) == strtolower(trim($secaoExibida['resposta']))) {
            if (!$isConcluida) {
                $dadosProgresso[$usuario][$idCurso]['secoes_concluidas'][] = $idUnicoAtivo;
                salvarDados($dadosProgresso, $caminhoProgresso);
            }
            header("Location: curso.php?id=$idCurso&aula=$aulaAtivaIdx&secao=$secaoAtivaIdx&success=1"); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo $cursoAtual['titulo']; ?></title>
    <link href="https://cloudflare.com" rel="stylesheet" />
    <style>
        :root { --bg: #f0f2f5; --card: #fff; --text: #2c3e50; --primary: #2ecc71; --sidebar: #2c3e50; --border: #ddd; --arduino: #00878F; --pdf: #e74c3c; }
        body.dark-mode { --bg: #121212; --card: #1e1e1e; --text: #e0e0e0; --sidebar: #000; --border: #333; }
        body { font-family: 'Segoe UI', sans-serif; display: flex; margin: 0; background: var(--bg); color: var(--text); height: 100vh; overflow: hidden; transition: 0.3s; }
        
        /* Sidebar */
        .sidebar { width: 320px; background: var(--sidebar); color: #fff; padding: 20px; overflow-y: auto; flex-shrink: 0; border-right: 1px solid var(--border); }
        .user-info { background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid rgba(255,255,255,0.1); }
        .progresso-sidebar { background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px; margin-bottom: 20px; }
        
        .cap-header { background: #34495e; padding: 12px; cursor: pointer; border-radius: 5px; margin-top: 10px; color: var(--primary); font-weight: bold; font-size: 0.9em; }
        .cap-content { display: none; list-style: none; padding-left: 15px; margin: 0; }
        .cap-open .cap-content { display: block; }
        .tree-link { color: #bdc3c7; text-decoration: none; display: block; padding: 8px; font-size: 0.85em; border-radius: 4px; }
        .tree-link.active { background: var(--primary); color: #fff; font-weight: bold; }
        
        /* Conteúdo */
        .conteudo { flex: 1; padding: 40px; overflow-y: auto; scroll-behavior: smooth; }
        .card { background: var(--card); padding: 30px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .video-wrapper { position: relative; padding-bottom: 56.25%; height: 0; border-radius: 8px; overflow: hidden; background: #000; margin-bottom: 20px; }
        .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        
        /* Arduino e PDF */
        .arduino-box { border: 1px solid var(--arduino); border-radius: 8px; overflow: hidden; margin: 20px 0; }
        .arduino-header { background: var(--arduino); color: white; padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; }
        .pdf-box { border: 1px solid var(--pdf); border-radius: 8px; overflow: hidden; margin: 20px 0; }
        .pdf-header { background: var(--pdf); color: white; padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; }
        .btn-action { background: rgba(255,255,255,0.2); border: 1px solid #fff; color: white; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8em; font-weight: bold; text-decoration: none; }
        pre[class*="language-"] { margin: 0 !important; border-radius: 0 !important; max-height: 400px; }
        embed { width: 100%; height: 600px; border-radius: 0 0 8px 8px; background: #525659; }

        .btn { padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-green { background: var(--primary); color: #fff; }
        .lock { opacity: 0.4; pointer-events: none; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="user-info">
        <small style="color: #bdc3c7; display: block; margin-bottom: 5px;">Bem-vindo(a),</small>
        <strong><?php echo htmlspecialchars($usuario); ?></strong>
    </div>

    <div class="progresso-sidebar">
        <div style="display:flex; justify-content:space-between; font-size:0.75em; margin-bottom:5px; color:#bdc3c7;">
            <span>Evolução no Curso</span><span><?php echo $porcentagemGeral; ?>%</span>
        </div>
        <div style="background:#444; height:6px; border-radius:10px; overflow:hidden;">
            <div style="background:var(--primary); height:100%; width:<?php echo $porcentagemGeral; ?>%; transition: 0.5s;"></div>
        </div>
    </div>

    <a href="meus_cursos.php" style="color:#fff; text-decoration:none; display:block; padding:10px; background:rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius:5px; margin-bottom:15px; text-align:center; font-size: 0.85em;">⬅ Painel Principal</a>
    
    <nav>
        <?php foreach ($cursoAtual['modulos'] as $aIdx => $cap): 
            $lib = ($aIdx <= $progressoMax); $aberto = ($aIdx == $aulaAtivaIdx) ? 'cap-open' : '';
        ?>
            <div class="cap-item <?php echo $aberto; ?> <?php echo !$lib ? 'lock' : ''; ?>">
                <div class="cap-header" onclick="window.location.href='?id=<?php echo $idCurso; ?>&aula=<?php echo $aIdx; ?>&secao=-1'">
                    Cap. <?php echo romanic($aIdx+1); ?>: <?php echo $cap['titulo']; ?>
                </div>
                <ul class="cap-content">
                    <?php foreach ($cap['secoes'] ?? [] as $sIdx => $sec): 
                        $atv = ($aIdx == $aulaAtivaIdx && $sIdx == $secaoAtivaIdx) ? 'active' : '';
                        $ok = in_array("c{$idCurso}_a{$aIdx}_s{$sIdx}", $secoesConcluidas);
                    ?>
                        <li><a href="?id=<?php echo $idCurso; ?>&aula=<?php echo $aIdx; ?>&secao=<?php echo $sIdx; ?>" class="tree-link <?php echo $atv; ?>">
                            <?php echo ($aIdx+1).".".($sIdx+1); ?> <?php echo $ok ? '✅' : '📄'; ?> <?php echo $sec['titulo_secao']; ?>
                        </a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </nav>
</div>

<div class="conteudo">
    <div class="card">
        <small style="color:var(--primary); font-weight:bold;"><?php echo ($secaoAtivaIdx === -1) ? "INTRODUÇÃO" : "ITEM ".($aulaAtivaIdx+1).".".($secaoAtivaIdx+1); ?></small>
        <h1 style="margin-top:5px;"><?php echo $tituloPagina; ?></h1>
        <hr style="border:0; border-top:1px solid var(--border); margin:20px 0;">

        <?php foreach ($conteudosParaExibir as $bloco): 
            $val = $bloco['valor']; $ext = strtolower(pathinfo($val, PATHINFO_EXTENSION));
        ?>
            <div style="margin-bottom:30px;">
                <?php if($bloco['tipo'] == 'video'): ?>
                    <div class="video-wrapper">
                        <?php if (strpos($val, 'youtube.com') !== false || strpos($val, 'youtu.be') !== false): ?>
                            <iframe src="<?php echo str_replace(['watch?v=', 'youtu.be/'], ['embed/', '://youtube.com'], $val); ?>" frameborder="0" allowfullscreen></iframe>
                        <?php else: ?>
                            <video width="100%" height="100%" controls style="position:absolute; top:0; left:0;"><source src="uploads/<?php echo $val; ?>" type="video/mp4"></video>
                        <?php endif; ?>
                    </div>
                <?php elseif($bloco['tipo'] == 'imagem'): ?>
                    <img src="<?php echo (strpos($val, 'http') === 0) ? $val : $dirUploads . $val; ?>" style="max-width:100%; border-radius:8px; display:block; margin:0 auto; border: 1px solid var(--border);">
                <?php elseif($bloco['tipo'] == 'simulacao'): ?>
                    <iframe width="100%" height="500" src="<?php echo $val; ?>" frameborder="0"></iframe>
                <?php elseif($bloco['tipo'] == 'texto'): 
                    if ($ext == 'ino' && file_exists($dirUploads . $val)): 
                        $idCode = "code_" . md5($val); $codeContent = file_get_contents($dirUploads . $val); ?>
                        <div class="arduino-box">
                            <div class="arduino-header">
                                <span>💻 Código Arduino: <b><?php echo $val; ?></b></span>
                                <div>
                                    <button onclick="copiarCodigo('<?php echo $idCode; ?>', this)" class="btn-action">📋 COPIAR</button>
                                    <a href="uploads/<?php echo $val; ?>" download class="btn-action" style="background:#fff; color:var(--arduino);">💾 BAIXAR</a>
                                </div>
                            </div>
                            <pre><code id="<?php echo $idCode; ?>" class="language-cpp"><?php echo htmlspecialchars($codeContent); ?></code></pre>
                        </div>
                    <?php elseif ($ext == 'pdf' && file_exists($dirUploads . $val)): ?>
                        <div class="pdf-box">
                            <div class="pdf-header"><span>📄 Documento PDF: <b><?php echo $val; ?></b></span><a href="uploads/<?php echo $val; ?>" download class="btn-action">💾 BAIXAR</a></div>
                            <embed src="uploads/<?php echo $val; ?>#toolbar=0" type="application/pdf" />
                        </div>
                    <?php else: ?>
                        <div style="line-height:1.8; font-size:1.1em; text-align:justify;"><?php echo nl2br($val); ?></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php if ($secaoAtivaIdx === -1): ?>
            <div style="text-align:right;"><a href="?id=<?php echo $idCurso; ?>&aula=<?php echo $aulaAtivaIdx; ?>&secao=0" class="btn btn-green">COMEÇAR ESTUDOS ➡</a></div>
        <?php endif; ?>
    </div>

    <?php if ($secaoAtivaIdx !== -1): ?>
        <div class="card" style="border-left: 6px solid <?php echo $isConcluida ? 'var(--primary)' : '#e74c3c'; ?>;">
            <h3>📝 Desafio do Item</h3>
            <?php if($isConcluida): ?>
                <p style="color:var(--primary); font-weight:bold;">✅ Item validado!</p>
                <div style="display:flex; justify-content:space-between; margin-top:20px;">
                    <?php 
                        $pAula = $aulaAtivaIdx; $pSec = $secaoAtivaIdx - 1;
                        if($pSec < 0) { $pSec = -1; }
                    ?>
                    <a href="?id=<?php echo $idCurso; ?>&aula=<?php echo $pAula; ?>&secao=<?php echo $pSec; ?>" class="btn" style="background:#95a5a6; color:#fff;">⬅ Anterior</a>
                    <?php if(isset($capituloExibido['secoes'][$secaoAtivaIdx+1])): ?>
                        <a href="?id=<?php echo $idCurso; ?>&aula=<?php echo $aulaAtivaIdx; ?>&secao=<?php echo $secaoAtivaIdx+1; ?>" class="btn btn-green">Próximo Item ➡</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <form method="POST">
                    <p><b>Pergunta:</b> <?php echo $secaoExibida['pergunta']; ?></p>
                    <input type="text" name="resp_micro" required style="padding:10px; width:280px; border-radius:5px; border:1px solid #ccc;">
                    <button type="submit" class="btn btn-green">VALIDAR</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<button onclick="document.body.classList.toggle('dark-mode')" style="position:fixed; bottom:20px; right:20px; z-index:100; background:var(--sidebar); color:#fff; border:1px solid var(--primary); padding:10px; border-radius:50%; cursor:pointer;">🌙</button>

<script src="https://cloudflare.com"></script>
<script src="https://cloudflare.com"></script>
<script>
function copiarCodigo(id, btn) {
    const code = document.getElementById(id).innerText;
    navigator.clipboard.writeText(code).then(() => {
        const oldText = btn.innerText; btn.innerText = "✅ COPIADO!";
        setTimeout(() => { btn.innerText = oldText; }, 2000);
    });
}
</script>
</body>
</html>
