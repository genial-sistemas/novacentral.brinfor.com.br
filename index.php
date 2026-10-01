<?php
    include 'controlers/autenticacao.php';
    include_once 'controlers/funcoes.php';
    
    $pagina = 'login';

    if (isset($_GET['page'])) {
        $pagina = addslashes($_GET["page"]);
    }

    switch($pagina) {
        case 'visitante-abrir-chamado':
            include 'controlers/visitante/chamados_abrir.php';
            break;
        case 'login':
            include 'controlers/login.php';
            break;
        case 'autenticacao':
            include 'controlers/autenticacao.php';
            break;
        case 'dashboard':
            include 'controlers/dashboard.php';
            break;
        case 'chamados_abrir':
            include 'controlers/chamados_abrir.php';
            break;
        case 'chamados_abrir-resultado':
            include 'views/chamados_abrir_resultado.php';
            break;            
        case 'chamados_listar_abertos':
            include 'controlers/chamados_listar_abertos.php';
            break;
        case 'chamados_listar_fechados':
            include 'controlers/chamados_listar_fechados.php';
            break;
        case 'chamados_interagir':
            include 'controlers/chamados_interagir.php';
            break;
        case 'relatorio_periodo':
            include 'controlers/relatorio_periodo.php';
            break;
        case 'logout':
            include 'controlers/logoff.php';
            break;
        default:
            include 'views/404.php';
    }
?>
