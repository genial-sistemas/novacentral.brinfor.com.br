<?php

if (empty($_SESSION['id_pessoa']) || !is_array($_SESSION['contratos'] ?? null)) {
    header('Location: login');
    exit;
}

require_once __DIR__ . '/../bibliotecas/fpdf/fpdf_protecao.php';
require_once __DIR__ . '/../models/documentacao_cliente.php';

function documentacaoPdfTexto($texto)
{
    // FPDF espera ISO-8859-1; converter aqui evita utf8_decode(), descontinuado no PHP 8.2.
    return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
}

function enviarDocumentacaoPdf($conteudo, $download)
{
    header('Content-Type: application/pdf');
    header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="Documentacao de Rede.pdf"');
    header('Content-Length: ' . strlen($conteudo));
    header('Cache-Control: private, no-store');
    echo $conteudo;
    exit;
}

$cachePdf = $_SESSION['documentacao_pdf_cache'] ?? array();
$contratosSessao = array_map('intval', $_SESSION['contratos']);
$cacheValido = isset($cachePdf['versao'], $cachePdf['id_pessoa'], $cachePdf['id_contrato'], $cachePdf['gerado_em'], $cachePdf['conteudo'])
    && (int)$cachePdf['versao'] === 3
    && (int)$cachePdf['id_pessoa'] === (int)$_SESSION['id_pessoa']
    && (int)$cachePdf['id_contrato'] > 0
    && in_array((int)$cachePdf['id_contrato'], $contratosSessao, true)
    && (int)$cachePdf['gerado_em'] >= time() - 300
    && is_string($cachePdf['conteudo'])
    && $cachePdf['conteudo'] !== '';

if ($cacheValido && isset($_GET['pdf'])) {
    enviarDocumentacaoPdf($cachePdf['conteudo'], isset($_GET['download']));
}

if ($cacheValido) {
    $idContrato = (int)$cachePdf['id_contrato'];
    $dbh = null;
} else {
    $dbh = null; // dados via API do ERP (models/erp_api.php)
    $idContrato = obterContratoHelpdesk($_SESSION['contratos'], $dbh);

    if (!$idContrato) {
        header('Location: dashboard');
        exit;
    }
}

$pdfUrl = '/documentacao_cliente?pdf=1#toolbar=0';
$pdfDownloadUrl = '/documentacao_cliente?pdf=1&download=1';

if (!isset($_GET['pdf'])) {
    $menu = 'Documentação';
    $pagina = 'Documentação';
    include __DIR__ . '/../views/documentacao_cliente_visualizar.php';
    exit;
}

$empresa = obterDadosEmpresa($_SESSION['id_pessoa'], $dbh);
$intro = obterIntroducao($idContrato, $dbh);
$rede = obterRede($idContrato, $dbh);
$obterRede = obterRede_ipv4_exclusao($idContrato, $dbh);
$obterssid = obterRede_ssid($idContrato, $dbh);

$equipamentos = obterEquipamentosDocumentacao($idContrato, $dbh);
$porCategoria = static function ($categoria, $status) use ($equipamentos) {
    return $equipamentos[$status][$categoria] ?? array();
};

$desktop = $porCategoria(EQP_CATEGORIA_DESKTOP_WORKSTATION, EQP_STATUS_ATIVO);
$desktop_inativos = $porCategoria(EQP_CATEGORIA_DESKTOP_WORKSTATION, EQP_STATUS_INATIVO);
$notebook = $porCategoria(EQP_CATEGORIA_NOTEBOOK, EQP_STATUS_ATIVO);
$notebook_inativos = $porCategoria(EQP_CATEGORIA_NOTEBOOK, EQP_STATUS_INATIVO);
$servidor = $porCategoria(EQP_CATEGORIA_SERVIDOR_FISICO, EQP_STATUS_ATIVO);
$servidor_inativos = $porCategoria(EQP_CATEGORIA_SERVIDOR_FISICO, EQP_STATUS_INATIVO);
$servidorv = $porCategoria(EQP_CATEGORIA_SERVIDOR_VIRTUAL, EQP_STATUS_ATIVO);
$servidorv_inativos = $porCategoria(EQP_CATEGORIA_SERVIDOR_VIRTUAL, EQP_STATUS_INATIVO);
$servidorn = $porCategoria(EQP_CATEGORIA_SERVIDOR_NUVEM, EQP_STATUS_ATIVO);
$servidorn_inativos = $porCategoria(EQP_CATEGORIA_SERVIDOR_NUVEM, EQP_STATUS_INATIVO);
$storage = $porCategoria(EQP_CATEGORIA_STORAGE, EQP_STATUS_ATIVO);
$storages_inativos = $porCategoria(EQP_CATEGORIA_STORAGE, EQP_STATUS_INATIVO);
$nobreak = $porCategoria(EQP_CATEGORIA_NOBREAK, EQP_STATUS_ATIVO);
$nobreak_inativos = $porCategoria(EQP_CATEGORIA_NOBREAK, EQP_STATUS_INATIVO);
$outros = $porCategoria(EQP_CATEGORIA_OUTROS, EQP_STATUS_ATIVO);
$outros_inativos = $porCategoria(EQP_CATEGORIA_OUTROS, EQP_STATUS_INATIVO);
$aps = $porCategoria(EQP_CATEGORIA_APS, EQP_STATUS_ATIVO);

$backups = obterBackup($idContrato, $dbh);
$volumetria = obterVolumetria($idContrato, $dbh);
$licencas = obterLicencasPorCategorias($idContrato, array(1, 2, 3, 4, 5), $dbh);
$licencas_so = $licencas[1] ?? array();
$licencas_av = $licencas[2] ?? array();
$licencas_app = $licencas[3] ?? array();
$licencas_cloud = $licencas[4] ?? array();
$licencas_outros = $licencas[5] ?? array();
$contrato_helpdesk = obterContratoOutsourcingPorIdContrato($idContrato, $dbh);
$contrato_internet = obterContratosInternet($idContrato, $dbh);
$contrato_sistemas = obterContratosSistemas($idContrato, $dbh);
$inventario_desktop = obterInventarioDesktop($idContrato, $dbh);
$inventario_notebook = obterInventarioNotebook($idContrato, $dbh);
$inventario_seguranca = obterInventarioSeguranca($idContrato, $dbh);
$nota_seguranca = obterNotaSeguranca($idContrato, $dbh);

class myPDF extends FPDF_Protecao
{
    /** Linha de identificação no rodapé (a quem o documento foi entregue e quando). */
    public $identificacao = '';

    /** Página em que cada seção começou (chave do sumário => número da página). */
    public $marcas = array();

    function marcar(...$chaves)
    {
        foreach ($chaves as $chave) {
            $this->marcas[$chave] = $this->PageNo();
        }
    }
    function header(){
        $this->Image('views/img/brInfor_logo_mini.png',180,5,-400);
    }

    function footer()
    {

        $this->SetY(-18);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(150,150,150);
        $this->Cell(0, 4, documentacaoPdfTexto($this->identificacao), 0, 0, 'C');
        $this->SetY(-15);
        $this->SetFont('Arial', '', '8');
        $this->SetTextColor(0,0,0);
        $this->Cell(0, 10, documentacaoPdfTexto('Copyright© 2002-'.date('Y').' - BRInfor Soluções em TI Ltda. É proibida a reprodução total ou parcial deste documento.'), 0, 0, 'L');
        $this->Cell(0, 10, documentacaoPdfTexto('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'R');
    }

    function tabelaequipamento()
    {
        $this->SetFont('Times', 'B', 10);
        $this->Cell(15, 5, documentacaoPdfTexto('Codigo'), 1, 0, 'C');
        $this->Cell(40, 5, documentacaoPdfTexto('Equipamento'), 1, 0, 'C');
        $this->Cell(30, 5, documentacaoPdfTexto('Descrição'), 1, 0, 'C');
        $this->Cell(40, 5, documentacaoPdfTexto('Modelo'), 1, 0, 'C');
        $this->Cell(65, 5, documentacaoPdfTexto('Usuário'), 1, 0, 'L');
        $this->Ln();
    }

    function dadosequipamento($res)
    {
        if (!is_array($res) || \count($res) === 0) { return; }
        $this->SetFont('Times', '', 10);

        foreach ($res as $r) {
            $this->Cell(15, 5, documentacaoPdfTexto($r['codigo']), 1, 0, 'C');
            $this->Cell(40, 5, documentacaoPdfTexto($r['equipamento']), 1, 0, 'C');
            $this->Cell(30, 5, documentacaoPdfTexto($r['descricao']), 1, 0, 'C');
            $this->Cell(40, 5, documentacaoPdfTexto($r['modelo']), 1, 0, 'C');
            if($r['nome']=="") $this->Cell(65, 5, documentacaoPdfTexto('Sem Contato Vinculado'), 1, 0, 'L');
            else $this->Cell(65, 5, documentacaoPdfTexto(mb_strimwidth($r['nome'],0,40)), 1, 0, 'L');
            $this->Ln();
        }
    }


    function tabelalicenciamento()
    {
        $this->SetFont('Times', 'B', 10);
        $this->Cell(15, 5, documentacaoPdfTexto('Codigo'), 1, 0, 'C');
        $this->Cell(70, 5, documentacaoPdfTexto('Descrição'), 1, 0, 'L');
        $this->Cell(30, 5, documentacaoPdfTexto('Tipo'), 1, 0, 'C');
        $this->Cell(20, 5, documentacaoPdfTexto('Quant.'), 1, 0, 'C');
        $this->Cell(20, 5, documentacaoPdfTexto('Em uso'), 1, 0, 'C');
        $this->Cell(30, 5, documentacaoPdfTexto('Vencimento'), 1, 0, 'C');
        $this->Ln();
    }

    function dadoslicenciamento($res)
    {
        if (!is_array($res) || \count($res) === 0) { return; }
        $this->SetFont('Times', '', 10);


        foreach($res as $r) {
            $this->Cell(15, 5, documentacaoPdfTexto($r['id']), 1, 0, 'C');
            $this->Cell(70, 5, documentacaoPdfTexto($r['nome']), 1, 0, 'L');
            $this->Cell(30, 5, documentacaoPdfTexto($r['tipo']), 1, 0, 'C');
            $this->Cell(20, 5, documentacaoPdfTexto($r['quantidade']), 1, 0, 'C');
            $this->Cell(20, 5, documentacaoPdfTexto($r['soma']), 1, 0, 'C');
            $this->Cell(30, 5, date('d/m/Y', strtotime($r['vencimento'])), 1, 0, 'C');
            $this->Ln();
        }

    }

    function tabelabackup()
    {
        $this->SetFont('Times', 'B', 10);
        $this->Cell(15, 5, documentacaoPdfTexto('Codigo'), 1, 0, 'C');
        $this->Cell(55, 5, documentacaoPdfTexto('Titulo'), 1, 0, 'L');
        $this->Cell(20, 5, documentacaoPdfTexto('Local'), 1, 0, 'C');
        $this->Cell(20, 5, documentacaoPdfTexto('Software'), 1, 0, 'C');
        $this->Cell(20, 5, documentacaoPdfTexto('Recorrência'), 1, 0, 'C');
        $this->Cell(20, 5, documentacaoPdfTexto('Horario'), 1, 0, 'C');
        $this->Cell(30, 5, documentacaoPdfTexto('Destino'), 1, 0, 'C');
        $this->Cell(15, 5, documentacaoPdfTexto('Retenção'), 1, 0, 'C');
        $this->Ln();
    }

    function dadosbackup($res)
    {
        if (!is_array($res) || \count($res) === 0) { return; }
        $this->SetFont('Times', '', 10);


        foreach($res as $r) {
            $this->Cell(15, 5, documentacaoPdfTexto($r['id']), 1, 0, 'C');
            $this->Cell(55, 5, documentacaoPdfTexto($r['titulo']), 1, 0, 'L');
            $this->Cell(20, 5, documentacaoPdfTexto($r['local']), 1, 0, 'C');
            $this->Cell(20, 5, documentacaoPdfTexto($r['software']), 1, 0, 'C');
            $this->Cell(20, 5, documentacaoPdfTexto($r['recorrencia']), 1, 0, 'C');
            $this->Cell(20, 5, documentacaoPdfTexto($r['horario']), 1, 0, 'C');
            $this->Cell(30, 5, documentacaoPdfTexto($r['destino']), 1, 0, 'C');
            $this->Cell(15, 5, documentacaoPdfTexto($r['retencao']), 1, 0, 'C');
            $this->Ln();
            $this->SetFont('Times', 'B', 10);
            $this->Cell(20, 5, documentacaoPdfTexto('Descrição:'), 1, 0, 'R');
            $this->SetFont('Times', '', 10);
            $this->Cell(175, 5, documentacaoPdfTexto($r['descricao']), 1, 0, 'L');
            $this->Ln();
            $this->Cell(195, 5, '', 1, 0, 'L');
            $this->Ln();
        }

    }

    function tabelavolumetria()
    {
        $this->SetFont('Times', 'B', 10);
        $this->Cell(15, 5, documentacaoPdfTexto('Codigo'), 1, 0, 'C');
        $this->Cell(60, 5, documentacaoPdfTexto('Titulo'), 1, 0, 'L');
        $this->Cell(40, 5, documentacaoPdfTexto('Volumetria Existente'), 1, 0, 'C');
        $this->Cell(40, 5, documentacaoPdfTexto('Volumetria Usada'), 1, 0, 'C');
        $this->Cell(40, 5, documentacaoPdfTexto('Volumetria Disponível'), 1, 0, 'C');
        $this->Ln();
    }

    function dadosvolumetria($res)
    {
        if (!is_array($res) || \count($res) === 0) { return; }
        $this->SetFont('Times', '', 10);


        foreach($res as $r) {
            $this->Cell(15, 5, documentacaoPdfTexto($r['id']), 1, 0, 'C');
            $this->Cell(60, 5, documentacaoPdfTexto($r['titulo']), 1, 0, 'L');
            $this->Cell(40, 5, $r['volumetria_existente'].' GB', 1, 0, 'C');
            $this->Cell(40, 5, $r['volumetria_usada'].' GB', 1, 0, 'C');
            $this->Cell(40, 5, $r['volumetria_disponivel'].' GB', 1, 0, 'C');
            $this->Ln();
        }

    }

    function tabelainventario()
    {
        $this->SetFont('Times', 'B', 10);
        $this->Cell(15, 5, documentacaoPdfTexto('Codigo'), 1, 0, 'C');
        $this->Cell(20, 5, documentacaoPdfTexto('Descrição'), 1, 0, 'C');
        $this->Cell(80, 5, documentacaoPdfTexto('Processador'), 1, 0, 'L');
        $this->Cell(25, 5, documentacaoPdfTexto('Memória'), 1, 0, 'C');
        $this->Cell(50, 5, documentacaoPdfTexto('HD'), 1, 0, 'C');
        $this->Ln();
    }

    function dadosinventariodesktop($res)
    {
        if (!is_array($res) || \count($res) === 0) { return; }
        $this->SetFont('Times', '', 10);


        foreach($res as $r) {
            $this->Cell(15, 5, documentacaoPdfTexto($r['id']), 1, 0, 'C');
            $this->Cell(20, 5, documentacaoPdfTexto($r['descricao']), 1, 0, 'C');
            $this->Cell(80, 5, documentacaoPdfTexto($r['cpu']), 1, 0, 'L');
            $this->Cell(25, 5, documentacaoPdfTexto($r['memoria']), 1, 0, 'C');
            $this->Cell(50, 5, documentacaoPdfTexto($r['hd']), 1, 0, 'C');
            $this->Ln();
        }

    }

    function dadosinventarionotebook($res)
    {
        if (!is_array($res) || \count($res) === 0) { return; }
        $this->SetFont('Times', '', 10);


        foreach($res as $r) {
            $this->Cell(15, 5, documentacaoPdfTexto($r['id']), 1, 0, 'C');
            $this->Cell(20, 5, documentacaoPdfTexto($r['descricao']), 1, 0, 'C');
            $this->Cell(80, 5, documentacaoPdfTexto($r['cpu']), 1, 0, 'L');
            $this->Cell(25, 5, documentacaoPdfTexto($r['memoria']), 1, 0, 'C');
            $this->Cell(50, 5, documentacaoPdfTexto($r['hd']), 1, 0, 'C');
            $this->Ln();
        }

    }

    function tabelaseguranca()
    {
        $this->SetFont('Times', 'B', 10);
        $this->Cell(10, 5, documentacaoPdfTexto('Item'), 1, 0, 'C');
        $this->Cell(110, 5, documentacaoPdfTexto('Solução Segurança'), 1, 0, 'L');
        $this->Cell(50, 5, documentacaoPdfTexto('Categoria'), 1, 0, 'C');
        $this->Cell(20, 5, documentacaoPdfTexto('Peso'), 1, 0, 'C');
        $this->Ln();
    }

    function dadosinventarioseguranca($res)
    {
        if (!is_array($res) || \count($res) === 0) { return; }
        $this->SetFont('Times', '', 10);

        foreach($res as $r) {
            $this->Cell(10, 5, documentacaoPdfTexto($r['item']), 1, 0, 'C');
            $this->Cell(110, 5, documentacaoPdfTexto($r['descricao']), 1, 0, 'L');
            $this->Cell(50, 5, documentacaoPdfTexto($r['categoria']), 1, 0, 'C');
            $this->Cell(20, 5, documentacaoPdfTexto($r['valor']), 1, 0, 'C');
            $this->Ln();
        }
    }

    function nivelseguranca($nivel)
    {
        if ($nivel >= 0 && $nivel <= 40) {
            return $this->Cell(0,20, documentacaoPdfTexto('Seu Nível Atual é N1 ('.$nivel.') pontos'),0,1,'C');
        } elseif ($nivel >= 41 && $nivel <= 80) {
            return $this->Cell(0,20, documentacaoPdfTexto('Seu Nível Atual é N2 ('.$nivel.') pontos'),0,1,'C');
        } elseif ($nivel >= 81 && $nivel <= 120) {
            return $this->Cell(0,20, documentacaoPdfTexto('Seu Nível Atual é N3 ('.$nivel.') pontos'),0,1,'C');
        } elseif ($nivel >= 121 && $nivel <= 150) {
            return $this->Cell(0,20, documentacaoPdfTexto('Seu Nível Atual é N4 ('.$nivel.') pontos'),0,1,'C');
        } else {
            return $this->Cell(0,20, documentacaoPdfTexto('Seu Nível Atual é N5 ('.$nivel.') pontos'),0,1,'C');
        }
    }

}

/**
 * Sumário com as páginas reais. $paginas vem da 1ª geração do PDF (chave => página);
 * na 1ª passagem fica vazio e só serve para ocupar o mesmo espaço.
 * Cada linha é um link para a página da seção.
 */
function desenharSumarioDocumentacao($pdf, array $paginas)
{
    $itens = array(
        array(0, 'intro', 'INTRODUÇÃO'),
        array(0, '1', '1 DESCRIÇÃO DOS EQUIPAMENTOS'),
        array(1, '1.1', '1.1 Desktops / Workstations'),
        array(1, '1.2', '1.2 Notebooks'),
        array(1, '1.3', '1.3 Servidores Físicos'),
        array(1, '1.4', '1.4 Servidores Virtuais'),
        array(1, '1.5', '1.5 Servidores em Nuvem'),
        array(1, '1.6', '1.6 Storages'),
        array(1, '1.7', '1.7 Outros Dispositivos'),
        array(1, '1.8', '1.8 Nobreaks'),
        array(0, '2', '2 DESCRIÇÃO DA REDE'),
        array(1, '2.1', '2.1 Descrição'),
        array(1, '2.2', '2.2 Topologia'),
        array(1, '2.3', '2.3 Roteamento'),
        array(1, '2.4', '2.4 Endereçamento IPv4'),
        array(1, '2.5', '2.5 Wireless'),
        array(0, '3', '3 BACKUPS'),
        array(1, '3.1', '3.1 Descrição'),
        array(1, '3.2', '3.2 Volumetria'),
        array(0, '4', '4 SEGURANÇA'),
        array(1, '4.1', '4.1 Conceitos de Cibersegurança'),
        array(1, '4.2', '4.2 Nível de Cibersegurança'),
        array(0, '5', '5 LICENÇAS'),
        array(1, '5.1', '5.1 Sistema Operacional'),
        array(1, '5.2', '5.2 Anti-Vírus'),
        array(1, '5.3', '5.3 Aplicativos'),
        array(1, '5.4', '5.4 Cloud'),
        array(1, '5.5', '5.5 Outros'),
        array(0, '6', '6 CONTRATOS'),
        array(1, '6.1', '6.1 Help Desk e Service Desk'),
        array(1, '6.2', '6.2 Sistemas'),
        array(1, '6.3', '6.3 Internet'),
        array(1, '6.4', '6.4 Outros'),
        array(0, '7', '7 INVENTÁRIO DE HARDWARE'),
        array(1, '7.1', '7.1 Desktops Ativos'),
        array(1, '7.2', '7.2 Notebooks Ativos'),
    );

    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 20, documentacaoPdfTexto('Sumário'), 0, 1, 'C');

    $largura = 190; // A4 (210) menos as margens de 10 mm
    foreach ($itens as $item) {
        list($nivel, $chave, $texto) = $item;
        if ($nivel === 0) {
            $pdf->Ln(2);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(0, 0, 0);
            $altura = 7;
            $recuo = 0;
        } else {
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetTextColor(102, 102, 102);
            $altura = 6;
            $recuo = 6;
        }
        $pagina = isset($paginas[$chave]) ? (int)$paginas[$chave] : 0;
        $link = '';
        if ($pagina > 0) {
            $link = $pdf->AddLink();
            $pdf->SetLink($link, 0, $pagina);
        }
        $textoPdf = documentacaoPdfTexto($texto);
        $larguraTexto = $pdf->GetStringWidth($textoPdf) + 2;
        $larguraPagina = 12;
        $larguraPontos = max(0, $largura - $recuo - $larguraTexto - $larguraPagina);
        $pontos = str_repeat('.', (int)floor($larguraPontos / max(0.1, $pdf->GetStringWidth('.'))));

        if ($recuo) {
            $pdf->Cell($recuo, $altura, '');
        }
        $pdf->Cell($larguraTexto, $altura, $textoPdf, 0, 0, 'L', false, $link);
        $pdf->Cell($larguraPontos, $altura, $pontos, 0, 0, 'R', false, $link);
        $pdf->Cell($larguraPagina, $altura, $pagina > 0 ? (string)$pagina : '', 0, 1, 'R', false, $link);
    }
}


// Duas passagens: a 1ª descobre em que página cada seção caiu (tabelas grandes ocupam mais de uma página);
// a 2ª gera o PDF final com o sumário apontando para essas páginas.
$paginasSumario = array();
include __DIR__ . '/documentacao_cliente_pdf.php';
$paginasSumario = $pdf->marcas;
include __DIR__ . '/documentacao_cliente_pdf.php';

// Gerar PDF
$conteudoPdf = $pdf->Output('S');
$_SESSION['documentacao_pdf_cache'] = array(
    'versao' => 3,
    'id_pessoa' => (int)$_SESSION['id_pessoa'],
    'id_contrato' => (int)$idContrato,
    'gerado_em' => time(),
    'conteudo' => $conteudoPdf
);
enviarDocumentacaoPdf($conteudoPdf, isset($_GET['download']));
