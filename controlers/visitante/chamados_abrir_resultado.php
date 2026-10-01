<?php
require_once __DIR__ . "/../../helpers/typeHelpers.php";
require_once __DIR__ . "/../../helpers/arrayHelpers.php";
require_once __DIR__ . "/../../helpers/httpHelpers.php";
require_once __DIR__ . "/../../helpers/cryptoHelpers.php";

require_once __DIR__ . "/../../models/contrato.php";
require_once __DIR__ . "/../../models/chamado.php";
require_once __DIR__ . "/../../models/contato.php";
require_once __DIR__ . "/../../models/equipamentos.php";

require_once __DIR__ . "/../../services/chamadoAbrirService.php";

$mensagem = null;
$tipo_mensagem = null;

$idChamado = safeHttpGetVar('id') ?? $_SESSION['idChamado'];
$strSeguranca = safeHttpGetVar('seguranca') ?? $_SESSION['seguranca'];

$urlInteragir = sprintf('/interagir-chamado?id=%s&seguranca=%s', $idChamado, $strSeguranca);

header('Cache-Control: max-age=3600, must-revalidate');
header('Expires: Fri, 30 Oct 1998 14:19:41 GMT');
header('Last-Modified: Mon, 29 Jun 1998 02:28:12 GMT');

include 'views/visitante/chamados_abrir_resultado.php';
