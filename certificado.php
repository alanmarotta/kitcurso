<?php
session_start();
if (!isset($_SESSION['usuario_nome'])) exit;

$idCurso = $_GET['id'] ?? 1;
$caminhoCursos = 'data/cursos.json';
$cursos = json_decode(file_get_contents($caminhoCursos), true);

$cursoNome = "Curso Desconhecido";
foreach ($cursos as $c) {
    if ($c['id'] == $idCurso) { $cursoNome = $c['titulo']; break; }
}

$dataEmissao = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Certificado - <?php echo $_SESSION['usuario_nome']; ?></title>
    <style>
        @page { size: landscape; margin: 0; }
        body { margin: 0; padding: 0; background-color: #525659; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        
        .certificado {
            width: 297mm; height: 210mm;
            background: white; border: 20px solid #2c3e50;
            box-sizing: border-box; position: relative;
            padding: 60px; text-align: center; font-family: 'Georgia', serif;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        
        .borda-interna { border: 5px solid #27ae60; height: 100%; padding: 40px; box-sizing: border-box; }
        
        h1 { font-size: 80px; color: #2c3e50; margin: 0; letter-spacing: 5px; }
        h2 { font-size: 30px; color: #7f8c8d; font-weight: normal; margin-top: 10px; }
        
        .aluno { font-size: 55px; color: #27ae60; margin: 30px 0; border-bottom: 2px solid #eee; display: inline-block; padding: 0 40px; }
        
        .texto { font-size: 24px; color: #34495e; line-height: 1.6; max-width: 80%; margin: 0 auto; }
        
        .rodape { margin-top: 60px; display: flex; justify-content: space-around; align-items: flex-end; }
        .assinatura { border-top: 2px solid #333; width: 250px; padding-top: 10px; font-size: 18px; }
        
        .selo { width: 120px; height: 120px; background: #f1c40f; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 5px double #fff; color: #b7950b; font-weight: bold; font-size: 14px; transform: rotate(-15deg); }

        @media print {
            body { background: none; }
            .certificado { box-shadow: none; }
        }
    </style>
</head>
<body onclick="window.print()">
    <div class="certificado">
        <div class="borda-interna">
            <h1>CERTIFICADO</h1>
            <h2>Concedido a:</h2>
            
            <div class="aluno"><?php echo strtoupper($_SESSION['usuario_nome']); ?></div>
            
            <p class="texto">
                Por ter concluído com aproveitamento excepcional o curso de <br>
                <strong><?php echo $cursoNome; ?></strong> <br>
                através da plataforma <strong>KitCurso</strong>, totalizando a carga horária <br>
                exigida e superando todos os desafios técnicos propostos.
            </p>

            <div class="rodape">
                <div class="assinatura">
                    <strong>KitCurso Brasil</strong><br>Direção Acadêmica
                </div>
                
                <div class="selo">QUALIDADE<br>GARANTIDA</div>
                
                <div class="assinatura">
                    Emitido em: <?php echo $dataEmissao; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
