<?php
    if (PHP_SAPI === 'cli-server') {
        $caminho_solicitado = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $arquivo_solicitado = realpath(__DIR__ . $caminho_solicitado);
        $arquivo_roteador = realpath(__FILE__);
        $raiz_projeto = realpath(__DIR__) . DIRECTORY_SEPARATOR;

        // Eu deixo o index.php processar suas rotas e sirvo os outros arquivos estáticos direto.
        if (
            $arquivo_solicitado !== false
            && is_file($arquivo_solicitado)
            && strpos($arquivo_solicitado, $raiz_projeto) === 0
            && $arquivo_solicitado !== $arquivo_roteador
            && strtolower(pathinfo($arquivo_solicitado, PATHINFO_EXTENSION)) !== 'php'
        ) {
            return false;
        }
    }

    include 'controlers/autenticacao.php';
    include_once 'controlers/funcoes.php';
    
    $pagina = 'login';

    if (isset($_GET['page'])) {
        $pagina = addslashes($_GET["page"]);
    } else {
        $requestPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        if ($requestPath !== '' && $requestPath !== 'index.php') {
            $pagina = addslashes($requestPath);
        }
    }

    switch($pagina) {
        case 'visualizar-chamado':
            include 'controlers/visitante/visualizar-chamado.php';
            break;
        case 'visitante-abrir-chamado':
            include 'controlers/visitante/chamados_abrir.php';
            break;
        case 'visitante-abrir-chamado-resultado':
            include 'views/visitante/chamados_abrir_resultado.php';
            break;
        case 'visitante-chamados-interacoes':
            include 'controlers/visitante/chamados_interacoes.php';
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
        case 'interagir-chamado':
        case 'visitante-interagir-chamado':
            include 'controlers/chamados_interagir.php';
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
        case 'relatorio_equipamento':
            include 'controlers/relatorio_equipamento.php';
            break;
        case 'documentacao_cliente':
            include 'controlers/documentacao_cliente.php';
            break;
        case 'equipamentos_listar':
            include 'controlers/equipamentos_listar.php';
            break;
        case 'equipamentos_listar_inativos':
            include 'controlers/equipamentos_listar_inativos.php';
            break;
        case 'equipamentos_editar':
            include 'controlers/equipamentos_editar.php';
            break;
        case 'equipamentos_status':
            include 'controlers/equipamentos_status.php';
            break;
        case 'equipamentos_locacao':
            include 'controlers/equipamentos_locacao.php';
            break;
        // Rotas do módulo de funcionários: formulário de cadastro/edição e listagem.
        case 'funcionarios_cadastrar':
            include 'controlers/funcionarios_cadastrar.php';
            break;
        case 'funcionarios_editar':
            include 'controlers/funcionarios_cadastrar.php';
            break;
        case 'funcionarios_listar':
            include 'controlers/funcionarios_listar.php';
            break;
        case 'logout':
            include 'controlers/logoff.php';
            break;
        case 'buscar-contrato':
            include 'controlers/buscar_contrato.php';
            break;
        default:
            include 'views/404.php';
    }
?>
