<?php
session_start();
if (!isset($_SESSION['usuario_nome'])) { header("Location: login.php"); exit; }

$usuario = $_SESSION['usuario_nome'];
$caminhoCursos = 'data/cursos.json';
$caminhoProgresso = 'data/progresso.json';

$cursos = json_decode(file_get_contents($caminhoCursos), true) ?: [];
$progressoTotal = json_decode(file_get_contents($caminhoProgresso), true) ?: [];
$meuProgresso = $progressoTotal[$usuario] ?? [];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meus Cursos - KitCurso</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; margin: 0; padding: 40px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .grid-cursos { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        
        .curso-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
        .curso-titulo { font-size: 1.2em; font-weight: bold; color: #2c3e50; margin-bottom: 10px; height: 50px; }
        
        /* Barra de Progresso */
        .barra-bg { background: #eee; border-radius: 10px; height: 12px; width: 100%; margin: 15px 0 5px 0; overflow: hidden; }
        .barra-fill { background: #2ecc71; height: 100%; transition: width 0.5s ease; }
        .porcentagem { font-size: 0.85em; color: #7f8c8d; font-weight: bold; }

        .btn { border: none; border-radius: 6px; padding: 12px; cursor: pointer; text-decoration: none; text-align: center; font-weight: bold; margin-top: 15px; transition: 0.3s; }
        .btn-estudar { background: #3498db; color: white; }
        .btn-estudar:hover { background: #2980b9; }
        .btn-cert { background: #27ae60; color: white; }
        .btn-cert:hover { background: #219150; }
        
        .badge-concluido { background: #e8f5e9; color: #27ae60; padding: 5px 10px; border-radius: 20px; font-size: 0.8em; font-weight: bold; align-self: flex-start; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🎓 Meus Cursos</h1>
        <div>
            <span>Olá, <strong><?php echo $usuario; ?></strong>!</span> | 
            <a href="index.php" style="color: #3498db; text-decoration: none;">Ir para a Loja</a>
        </div>
    </div>

    <div class="grid-cursos">
        <?php foreach ($cursos as $curso): 
            $idC = $curso['id'];
            $nivelAtual = $meuProgresso[$idC]['nivel'] ?? 0;
            $totalCapitulos = count($curso['modulos'] ?? []);
            
            // Cálculo da porcentagem
            $percent = ($totalCapitulos > 0) ? round(($nivelAtual / $totalCapitulos) * 100) : 0;
            if ($percent > 100) $percent = 100;
            
            $concluido = ($percent >= 100);
        ?>
            <div class="curso-card">
                <?php if($concluido): ?>
                    <span class="badge-concluido">✔ CONCLUÍDO</span>
                <?php endif; ?>
                
                <div class="curso-titulo"><?php echo $curso['titulo']; ?></div>
                
                <div class="porcentagem">Progresso: <?php echo $percent; ?>%</div>
                <div class="barra-bg">
                    <div class="barra-fill" style="width: <?php echo $percent; ?>%;"></div>
                </div>

                <a href="curso.php?id=<?php echo $idC; ?>" class="btn btn-estudar">
                    <?php echo ($nivelAtual > 0) ? "CONTINUAR ESTUDOS" : "INICIAR CURSO"; ?>
                </a>

                <?php if($concluido): ?>
                    <a href="certificado.php?id=<?php echo $idC; ?>" target="_blank" class="btn btn-cert">
                        📜 VER CERTIFICADO
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
