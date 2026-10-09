<?php

if (empty($_SESSION['id_pessoa']) || !is_array($_SESSION['contratos'] ?? null)) {
    header('Location: login');
    exit;
}

require_once __DIR__ . '/../bibliotecas/fpdf/fpdf.php';
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
    && (int)$cachePdf['versao'] === 2
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
    $dbh = getConexao();
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

class myPDF extends FPDF
{
    function header(){
        $this->Image('views/img/brInfor_logo_mini.png',180,5,-400);
    }

    function footer()
    {

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
            $ver = obterBackup($r['id']);
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
            $ver = obterInventarioDesktop($r['id']);
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
            $ver = obterInventarioNotebook($r['id']);
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

$pdf = new myPDF();
$pdf->SetMargins(10, 20, 10);
$pdf->AliasNbPages();

$pdf->AddPage('P', 'A4', 0);
$pdf->Image('views/img/brInfor_logo_mini.png',70,100,80.5);
$pdf->SetTextColor(102,102,102);
$pdf->SetFont('Arial', 'B', 32);
$pdf->SetY(130);
$pdf->Cell(0,0, documentacaoPdfTexto('Documentação de Rede'),0,1,'C');
$pdf->Ln();
$pdf->Cell(0,30, documentacaoPdfTexto($empresa["nome_pessoa"] ?? ''),0,1,'C');
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0,-60, documentacaoPdfTexto('Atualizado em '.date("d/m/Y").''),0,1,'C');


// Sumário
$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('Sumário'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0,5,documentacaoPdfTexto('INTRODUÇÃO.....................................................................................................................................'),0,0,'L');
$pdf->Cell(0,5,'4',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,5,documentacaoPdfTexto('1 DESCRIÇÃO EQUIPAMENTOS......................................................................................................'),0,0,'L');
$pdf->Cell(0,5,'5',0,1,'R');
$pdf->Ln();
$pdf->SetTextColor(102,102,102);
$pdf->Cell(0,3,documentacaoPdfTexto('1.1 Desktops / Workstations.............................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'5',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('1.2 Notebooks....................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'6',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('1.3 Servidores Físicos.......................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'7',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('1.4 Servidores Virtuais......................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'7',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,mb_convert_encoding('1.5 Servidores Nuvem.......................................................................................................................', 'ISO-8859-1'),0,0,'L');
$pdf->Cell(0,3,'8',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,mb_convert_encoding('1.6 Storages .......................................................................................................................................', 'ISO-8859-1'),0,0,'L');
$pdf->Cell(0,3,'8',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,mb_convert_encoding('1.7 Outros Dispositivos ....................................................................................................................', 'ISO-8859-1'),0,0,'L');
$pdf->Cell(0,3,'9',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,mb_convert_encoding('1.8 Nobreaks ......................................................................................................................................', 'ISO-8859-1'),0,0,'L');
$pdf->Cell(0,3,'10',0,1,'R');
$pdf->Ln();

$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,5,documentacaoPdfTexto('2 DESCRIÇÃO DA REDE..................................................................................................................'),0,0,'L');
$pdf->Cell(0,5,'11',0,1,'R');
$pdf->Ln();
$pdf->SetTextColor(102,102,102);
$pdf->Cell(0,3,documentacaoPdfTexto('2.1 Descrição....................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'11',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('2.2 Topologia....................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'12',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('2.3 Roteamento.................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'13',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('2.4 Endereçamento IPv4....................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'14',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('2.5 Endereçamento IPv6....................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'14',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('2.6 Internet ........................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'15',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('2.7 VPN ..............................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'16',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('2.8 Wireless ......................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'17',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('2.9 Active Directory...........................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'18',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('2.10 Domínios ...................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'18',0,1,'R');
$pdf->Ln();

$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,5,mb_convert_encoding('3 BACKUPS........................................................................................................................................', 'ISO-8859-1'),0,0,'L');
$pdf->Cell(0,5,'19',0,1,'R');
$pdf->Ln();
$pdf->SetTextColor(102,102,102);
$pdf->Cell(0,3,documentacaoPdfTexto('3.1 Descrição....................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'19',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('3.2 Volumetria...................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'19',0,1,'R');
$pdf->Ln();

$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,5,documentacaoPdfTexto('4 SEGURANÇA.................................................................................................................................'),0,0,'L');
$pdf->Cell(0,5,'21',0,1,'R');
$pdf->Ln();
$pdf->SetTextColor(102,102,102);
$pdf->Cell(0,3,documentacaoPdfTexto('4.1 Conceitos Cibersegurança.....................................................................................................................'),0,0,'L');
$pdf->Cell(0,5,'21',0,1,'R');
$pdf->Ln();
$pdf->SetTextColor(102,102,102);
$pdf->Cell(0,3,documentacaoPdfTexto('4.2 Nível Cibersegurança..........................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'23',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('4.3 Regras do Firewall..................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'24',0,1,'R');
$pdf->Ln();

$pdf->AddPage('P', 'A4', 0);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,5,documentacaoPdfTexto('5 LICENÇAS......................................................................................................................................'),0,0,'L');
$pdf->Cell(0,5,'24',0,1,'R');
$pdf->Ln();
$pdf->SetTextColor(102,102,102);
$pdf->Cell(0,3,documentacaoPdfTexto('5.1 Sistema Operacional...................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'24',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('5.2 Anti-Vírus...................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'24',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('5.3 Aplicativos.....................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'25',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('5.4 Cloud...........................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'25',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('5.5 Outros..........................................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'26',0,1,'R');
$pdf->Ln();

$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,5,documentacaoPdfTexto('6 CONTRATOS..................................................................................................................................'),0,0,'L');
$pdf->Cell(0,5,'27',0,1,'R');
$pdf->Ln();
$pdf->SetTextColor(102,102,102);
$pdf->Cell(0,3,documentacaoPdfTexto('6.1 Help Desk e Service Desk..........................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'27',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,mb_convert_encoding('6.2 Sistemas.......................................................................................................................................', 'ISO-8859-1'),0,0,'L');
$pdf->Cell(0,3,'28',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,mb_convert_encoding('6.3 Internet..........................................................................................................................................', 'ISO-8859-1'),0,0,'L');
$pdf->Cell(0,3,'29',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,mb_convert_encoding('6.4 Outros..........................................................................................................................................', 'ISO-8859-1'),0,0,'L');
$pdf->Cell(0,3,'30',0,1,'R');
$pdf->Ln();
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,5,documentacaoPdfTexto('7 INVENTÁRIO DE HARDWARE......................................................................................................'),0,0,'L');
$pdf->Cell(0,5,'31',0,1,'R');
$pdf->Ln();
$pdf->SetTextColor(102,102,102);
$pdf->Cell(0,3,documentacaoPdfTexto('7.1 Desktops Ativos.........................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'31',0,1,'R');
$pdf->Ln();
$pdf->Cell(0,3,documentacaoPdfTexto('7.2 Notebooks Ativos.......................................................................................................................'),0,0,'L');
$pdf->Cell(0,3,'32',0,1,'R');
$pdf->Ln();


//Página 4 - Introdução

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('INTRODUÇÃO'),0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Ln();
$pdf->MultiCell(0,5, documentacaoPdfTexto($intro['introducao1']),0,"J",false);
$pdf->Ln();
$pdf->MultiCell(0,5, documentacaoPdfTexto($intro['introducao2']),0,"J",false);


//Página 5 - Desktops

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('1.1 Desktop / Workstations (Ativos)'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($desktop);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('Desktop / Workstations (Inativos)'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($desktop_inativos);



//Página 6 - Notebooks

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('1.2 Notebooks'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($notebook);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('Notebooks (Inativos)'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($notebook_inativos);

//Página 7 - Servidores e Servidores Virtuais

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('1.3 Servidores Físicos'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($servidor);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('Servidores Físicos (Inativos)'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($servidor_inativos);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0,20, documentacaoPdfTexto('1.4 Servidores Virtuais'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($servidorv);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('Servidores Virtuais (Inativos)'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($servidorv_inativos);

//Página 8 - Servidores em Nuvem / Storages

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0,20, mb_convert_encoding('1.5 Servidores Nuvem', 'ISO-8859-1'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($servidorn);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, mb_convert_encoding('Servidores Nuvem (Inativos)', 'ISO-8859-1'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($servidorn_inativos);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Ln();
$pdf->Cell(0,20, mb_convert_encoding('1.6 Storages', 'ISO-8859-1'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($storage);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('Storages (Inativos)'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($storages_inativos);

//Página 9 - Outros Equipamentos

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, mb_convert_encoding('1.7 Outros Dispositivos', 'ISO-8859-1'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($outros);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, mb_convert_encoding('Outros Dispositivos (Inativos)', 'ISO-8859-1'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($outros_inativos);

//Página 10 - Nobreak

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, mb_convert_encoding('1.8 Nobreaks', 'ISO-8859-1'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($nobreak);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('Nobreaks (Inativos)'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($nobreak_inativos);

//Página 11 - Redes

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('2.1 Descrição de Rede'),0,1,'C');
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Cabeamento:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto($rede['descricao_cabeamento']),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Switchs:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto($rede['descricao_switchs']),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("VLANs:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto($rede['descricao_vlans']),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Wireless LAN:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto($rede['descricao_wlan']),0,"J",false);
$pdf->Ln();


//Página 12 - Topologia

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('2.2 Topologia'),0,1,'C');
$pdf->SetFont('Arial', '', 12);
if (!empty($rede['topologia_img']) && is_file($rede['topologia_img'])) {
    $pdf->Image($rede['topologia_img'],05,45);
}
$pdf->Ln();

//Página 13 - Roteamento

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('2.3 Roteamento'),0,1,'C');
$pdf->SetFont('Arial', '', 12);
if (!empty($rede['roteamento_img']) && is_file($rede['roteamento_img'])) {
    $pdf->Image($rede['roteamento_img'],05,45);
}
$pdf->Ln();

//Página 14 - Endereçamento

$ip_rede = $rede['enderecamento_ipv4_inicial'].' à '.$rede['enderecamento_ipv4_final'].'';
$masc_ipv4 = $rede['mascara_ipv4'];

switch($masc_ipv4){
    case 1:
        $mascara = '128.0.0.0/1';
        break;
    case 2:
        $mascara = '192.0.0.0/2';
        break;
    case 3:
        $mascara = '224.0.0.0/3';
        break;
    case 4:
        $mascara = '240.0.0.0/4';
        break;
    case 5:
        $mascara = '248.0.0.0/5';
        break;
    case 6:
        $mascara = '252.0.0.0/6';
        break;
    case 7:
        $mascara = '254.0.0.0/7';
        break;
    case 8:
        $mascara = '255.0.0.0/8';
        break;
    case 9:
        $mascara = '255.128.0.0/9';
        break;
    case 10:
        $mascara = '255.192.0.0/10';
        break;
    case 11:
        $mascara = '255.224.0.0/11';
        break;
    case 12:
        $mascara = '255.240.0.0/12<';
        break;
    case 13:
        $mascara = '255.248.0.0/13';
        break;
    case 14:
        $mascara = '255.254.0.0/14';
        break;
    case 15:
        $mascara = '255.255.0.0/15';
        break;
    case 16:
        $mascara = '255.255.0.0/16';
        break;
    case 17:
        $mascara = '255.255.128.0/17';
        break;
    case 18:
        $mascara = '255.255.192.0/18';
        break;
    case 19:
        $mascara = '255.255.224.0/19';
        break;
    case 20:
        $mascara = '255.255.240.0/20';
        break;
    case 21:
        $mascara = '255.255.248.0/21';
        break;
    case 22:
        $mascara = '255.255.252.0/22';
        break;
    case 23:
        $mascara = '255.255.254.0/23';
        break;
    case 24:
        $mascara = '255.255.255.0/24';
        break;
    case 25:
        $mascara = '255.255.255.128/25';
        break;
    case 26:
        $mascara = '255.255.255.192/26';
        break;
    case 27:
        $mascara = '255.255.255.224/27';
        break;
    case 28:
        $mascara = '255.255.255.240/28';
        break;
    case 29:
        $mascara = '255.255.255.248/29';
        break;
    case 30:
        $mascara = '255.255.255.252/30';
        break;
    case 31:
        $mascara = '255.255.255.254/31';
        break;
    default:
        $mascara = '255.255.255.0/24';
        break;
}

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('2.4 Endereçamento IPv4'),0,1,'C');
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Escopo IPv4 Inicial-Final:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto($ip_rede),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Mascara IPv4/Comprimento:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto($mascara),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Exclusões de IPs DHCP:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
foreach($obterRede as $rede_exclusao){
    $ips_exclusao = $rede_exclusao["ip_inicial"].' à '.$rede_exclusao["ip_final"].' ';
    $pdf->MultiCell(0,5,documentacaoPdfTexto($ips_exclusao),0,"J",false);
}
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Gateway IPv4:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto($rede['gateway_ipv4']),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Sevidor DNS1:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto($rede['dns1_ipv4']),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Sevidor DNS2:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto($rede['dns2_ipv4']),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Zona DNS:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto($rede['zona_dns']),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Servidor NTP:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto($rede['servidor_ntp_ipv4']),0,"J",false);
$pdf->Ln();


//Página 17 - Wifi

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('2.8 Wireless'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelaequipamento();
$pdf->dadosequipamento($aps);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Redes Wifi SSID:"),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
foreach($obterssid as $redes){
    $ssids = 'Nome: '.$redes["nome"];
    $pdf->MultiCell(0,5,documentacaoPdfTexto($ssids),0,"J",false);
    $pdf->Ln();
    $pdf->MultiCell(0,5,'Senha: **********************',0,"J",false);
    $pdf->Ln();
}
$pdf->Ln();

//Página 19 - Backup

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('3.1 Backups'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Backups Configurados:"),0,"J",false);
$pdf->Ln();
$pdf->tabelabackup();
$pdf->dadosbackup($backups);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('3.2 Volumetria'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Espaço Utilizado:"),0,"J",false);
$pdf->Ln();
$pdf->tabelavolumetria();
$pdf->dadosvolumetria($volumetria);
$pdf->Ln();

// Página 21 - Segurança

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('4.1 Conceitos de Cibersegurança'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Ln();
$pdf->Cell(0,5, mb_convert_encoding('1 - Antivírus', 'ISO-8859-1'),0,1,'L');
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Um antivírus é um software de segurança essencial que detecta, bloqueia e remove programas maliciosos (malware) como vírus, worms, ransomware e spyware, protegendo computadores e dispositivos contra roubo de dados, danos e atividades indesejadas, funcionando através de varreduras em tempo real e análise de comportamento. É considerado item básico de segurança que todas as empresas deveriam ter."),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0,5, mb_convert_encoding('2 - EDR/XDR/MDR', 'ISO-8859-1'),0,1,'L');
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("É uma plataforma de segurança cibernética unificada que coleta e correlaciona dados de múltiplas camadas (endpoints, e-mail, nuvem, rede, identidade) para fornecer uma visão holística e centralizada das ameaças, usando IA e automação para detectar, investigar e responder a ataques avançados de forma mais eficiente do que as soluções tradicionais. Hoje em dia é uma ferramenta de segurança muito importante nas empresas que ajuda identificar comportamentos fora do padrão para alertar possíveis falhas de segurança, além de oferecer sistema de auditoria para identificar a fonte de um possível ataque."),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0,5, mb_convert_encoding('3 - Firewall', 'ISO-8859-1'),0,1,'L');
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Um firewall é um dispositivo ou software que protege uma rede contra acesso não autorizado, filtrando o tráfego de rede com base em regras predefinidas. Ele atua como uma barreira entre redes confiáveis e não confiáveis, bloqueando conexões maliciosas e permitindo apenas o tráfego seguro. É uma ferramenta fundamental para manter a integridade e a segurança das redes. Hoje é considerado um equipamento essencial em toda empresa."),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0,5, mb_convert_encoding('4 - Patch Management (Gerenciamento de Patches)', 'ISO-8859-1'),0,1,'L');
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Patch Management (Gerenciamento de Patches) é o processo sistemático de identificar, testar, implantar e gerenciar atualizações de software (patches) para corrigir vulnerabilidades, bugs e melhorar a segurança e funcionalidade de sistemas e aplicativos em uma organização, protegendo contra ataques cibernéticos e garantindo a estabilidade operacional e conformidade. BRInfor recomenda que todos os clientes tenham em seu ambiente uma solução para esse gerenciamento."),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0,5, mb_convert_encoding('5 - Sistema de Monitoramento 24x7', 'ISO-8859-1'),0,1,'L');
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Um sistema de monitoramento de TI é uma solução (geralmente software) que acompanha continuamente servidores, redes, aplicativos e dispositivos, coletando dados em tempo real para identificar proativamente problemas, gargalos e vulnerabilidades, garantindo a disponibilidade, desempenho e segurança da infraestrutura tecnológica, e permitindo ações corretivas antes que causem interrupções graves nos negócios. Apesar de não ser uma solução obrigatória, ajuda antecipar possíveis problemas e evitar paradas desnecessárias."),0,"J",false);
$pdf->Ln();

// Página 22 - Cibersegurança continuação

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0,5, mb_convert_encoding('6 - Pentest', 'ISO-8859-1'),0,1,'L');
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Pentest (Teste de Penetração) é uma simulação autorizada de ataque cibernético a sistemas, redes ou aplicações, realizada por especialistas (ethical hackers) para encontrar e explorar vulnerabilidades de segurança, assim como um criminoso faria, mas com o objetivo de corrigi-las antes que atacantes reais as usem. É um \"ataque controlado\" que ajuda a fortalecer a segurança, identificar fraquezas e garantir a conformidade com leis de proteção de dados, como a LGPD. O pentest deve ser realizado pelo menos uma vez por ano e, crucialmente, após quaisquer alterações significativas na infraestrutura de TI. A frequência ideal depende do nível de risco da empresa, dos requisitos regulatórios do setor e da complexidade dos sistemas."),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0,5, mb_convert_encoding('7 - O controle de acesso da Rede', 'ISO-8859-1'),0,1,'L');
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("O controle de acesso no Active Directory (Windows) ou Samba-AD (Linux) é a gestão centralizada de permissões para usuários e recursos (arquivos, pastas, impressoras) em uma rede, utilizando grupos, políticas (GPO) para autenticar e autorizar, garantindo que usuários tenham apenas o acesso necessário. Isso é feito através de listas de controle de acesso (ACLs), que aninha grupos para gerenciar permissões de forma eficiente e segura. Em resumo, o AD serve como a espinha dorsal da segurança e gerenciamento de identidade, proporcionando controle, eficiência e segurança para redes de qualquer tamanho."),0,"J",false);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0,5, mb_convert_encoding('8 - Backup Imutável e em Nuvem', 'ISO-8859-1'),0,1,'L');
$pdf->Ln();
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0,5, documentacaoPdfTexto("Backup imutável é uma cópia de dados que, após criada, não pode ser alterada, modificada ou excluída, nem mesmo por administradores, por um período definido, garantindo uma versão limpa e intocada para recuperação contra ransomware, erros humanos e desastres, com tecnologias como WORM (Write Once, Read Many). Ele se torna uma base confiável para restaurar sistemas quando outras proteções falham, sendo uma recomendação da CISA para resiliência cibernética. Além disso é ideal possuir um Backup em Nuvem garante acesso remoto aos dados de qualquer lugar e protege contra falhas locais (como incêndios ou roubos), oferecendo escalabilidade e custos mais previsíveis ao pagar pelo uso, sendo um pilar essencial para a continuidade dos negócios."),0,"J",false);$pdf->Ln();

// Página 23 - Nível de Cibersegurança

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('4.2 Nível de Cibersegurança'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Ln();
$imagemPiramide = 'views/img/piramide_seguranca.png';
if (is_file($imagemPiramide)) {
    $pdf->Image($imagemPiramide,50,50,110);
}
$pdf->SetY(130);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->nivelseguranca($nota_seguranca['total']);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,5, mb_convert_encoding('Tabela de Pontuação:', 'ISO-8859-1'),0,1,'L');
$pdf->Ln();
$pdf->tabelaseguranca();
$pdf->dadosinventarioseguranca($inventario_seguranca);


//Página 24 - Licenciamento

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('5.1 Licenciamento - Sistema Operacional'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelalicenciamento();
$pdf->dadoslicenciamento($licencas_so);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('5.2 Licenciamento - Anti-Vírus'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelalicenciamento();
$pdf->dadoslicenciamento($licencas_av);
$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('5.3 Licenciamento - Aplicativos'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelalicenciamento();
$pdf->dadoslicenciamento($licencas_app);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, mb_convert_encoding('5.4 Licenciamento - Cloud', 'ISO-8859-1'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelalicenciamento();
$pdf->dadoslicenciamento($licencas_cloud);
$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, mb_convert_encoding('5.5 Licenciamento - Outros', 'ISO-8859-1'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelalicenciamento();
$pdf->dadoslicenciamento($licencas_outros);

//Página 27 - Contrato Helpdesk

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, mb_convert_encoding('6.1 Contrato Help Desk e Service Desk', 'ISO-8859-1'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(10, mb_convert_encoding("Empresa: ", 'ISO-8859-1'));
$pdf->SetFont('Arial', '', 12);
$pdf->Write(10, mb_convert_encoding("BRInfor Soluções em TI LTDA", 'ISO-8859-1'));
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(10, mb_convert_encoding("Tipo Contrato: ", 'ISO-8859-1'));
$pdf->SetFont('Arial', '', 12);
$pdf->Write(10,  mb_convert_encoding($contrato_helpdesk['tipo'], 'ISO-8859-1'));
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(10, mb_convert_encoding("Etiqueta: ", 'ISO-8859-1'));
$pdf->SetFont('Arial', '', 12);
$pdf->Write(10,  mb_convert_encoding($contrato_helpdesk['etiqueta'], 'ISO-8859-1'));
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(10, mb_convert_encoding("Prexifo: ", 'ISO-8859-1'));
$pdf->SetFont('Arial', '', 12);
$pdf->Write(10,  mb_convert_encoding($contrato_helpdesk['prefixo'], 'ISO-8859-1'));
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(10, mb_convert_encoding("Telefone de Suporte: ", 'ISO-8859-1'));
$pdf->SetFont('Arial', '', 12);
$pdf->Write(10,  mb_convert_encoding("31-3324-2901", 'ISO-8859-1'));
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(10, mb_convert_encoding("Site abertura de chamandos: ", 'ISO-8859-1'));
$pdf->SetFont('Arial', '', 12);
$pdf->Write(10, "https://brinfor.com.br");
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(10, mb_convert_encoding("Cental do Cliente: ", 'ISO-8859-1'));
$pdf->SetFont('Arial', '', 12);
$pdf->Write(10, mb_convert_encoding("https://central.brinfor.com.br", 'ISO-8859-1'));
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(10, mb_convert_encoding("Valor Hora Adiconal: ", 'ISO-8859-1'));
$pdf->SetFont('Arial', '', 12);
$cvalor_hora = number_format((float)$contrato_helpdesk["valor_hora_adicional"], 2, ',', '.');
$pdf->Write(10, mb_convert_encoding('R$: '.$cvalor_hora.'', 'ISO-8859-1'));
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Write(10, mb_convert_encoding("Valor Mensal: ", 'ISO-8859-1'));
$pdf->SetFont('Arial', '', 12);
$pdf->Write(10, "R$ **,**");
$pdf->Ln();

//Página 28 - Contratos Sistemas

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, mb_convert_encoding('6.2 Contratos Sistemas', 'ISO-8859-1'),0,1,'C');

if (\count($contrato_sistemas) > 0) {
    for ($i=0; $i <= (\count($contrato_sistemas) - 1); $i++) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Contrato Nº: ",  'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10, mb_convert_encoding(sprintf('%02d', $i + 1), 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Empresa: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10, mb_convert_encoding($contrato_sistemas[$i]["nome_empresa"], 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Nome Sistema: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10, mb_convert_encoding($contrato_sistemas[$i]["nome_sistema"], 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Código Contrato: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10,  mb_convert_encoding($contrato_sistemas[$i]["codigo_contrato"], 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Contato Suporte: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10,  mb_convert_encoding($contrato_sistemas[$i]["contato_suporte"], 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Telefone Suporte: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10,  mb_convert_encoding($contrato_sistemas[$i]["telefone_suporte"], 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("E-mail Suporte: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10,  mb_convert_encoding($contrato_sistemas[$i]["email_suporte"], 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Valor Mensal: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10,  number_format((float)$contrato_sistemas[$i]["valor_mensal"], 2, ',', '.'));
        $pdf->Ln();
        $pdf->Ln();
    }
}

//Página 29 - Contratos Internet/Outros

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, mb_convert_encoding('6.3 Contratos de Internet', 'ISO-8859-1'),0,1,'C');

if (\count($contrato_internet) > 0) {
    for ($i=0; $i <= (\count($contrato_internet) - 1); $i++) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Contrato Nº: ",  'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10, mb_convert_encoding(sprintf('%02d', $i + 1), 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Empresa: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10, mb_convert_encoding($contrato_internet[$i]["nome_empresa"], 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Código Contrato: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10,  mb_convert_encoding($contrato_internet[$i]["codigo_contrato"], 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Telefone Suporte: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10,  mb_convert_encoding($contrato_internet[$i]["telefone_suporte"], 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Tipo Internet: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10,  mb_convert_encoding($contrato_internet[$i]["descricao"], 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Download/Upload: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $upload = $contrato_internet[$i]["upload"];
        $download = $contrato_internet[$i]["download"];
        $pdf->Write(10,  mb_convert_encoding("$download MB/$upload MB", 'ISO-8859-1'));
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Write(10, mb_convert_encoding("Valor Mensal: ", 'ISO-8859-1'));
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(10,  number_format((float)$contrato_internet[$i]["valor_mensal"], 2, ',', '.'));
        $pdf->Ln();
        $pdf->Ln();
    }
}

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, mb_convert_encoding('6.4 Contratos Outros', 'ISO-8859-1'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);

//Página 30 - Inventário de Desktops

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('7.1 Desktops Ativos'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelainventario();
$pdf->dadosinventariodesktop($inventario_desktop);
$pdf->Ln();

//Página 31 - Inventário de Notebooks

$pdf->AddPage('P', 'A4', 0);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,20, documentacaoPdfTexto('7.2 Notebooks Ativos'),0,1,'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->tabelainventario();
$pdf->dadosinventarionotebook($inventario_notebook);
$pdf->Ln();

// Gerar PDF
$conteudoPdf = $pdf->Output('S');
$_SESSION['documentacao_pdf_cache'] = array(
    'versao' => 2,
    'id_pessoa' => (int)$_SESSION['id_pessoa'],
    'id_contrato' => (int)$idContrato,
    'gerado_em' => time(),
    'conteudo' => $conteudoPdf
);
enviarDocumentacaoPdf($conteudoPdf, isset($_GET['download']));
