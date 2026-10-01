<?php
require_once(__DIR__ . '/../models/contrato.php');

include_once __DIR__ . '/../models/contratoConstantes.php';

/**
 * isContratoTipoOutsourcing function
 *
 * @param int $contratoIdTipo
 * @return boolean
 */
function isContratoTipoOutsourcing(?int $contratoIdTipo): bool
{
    try {
        return isset($contratoIdTipo) && in_array($contratoIdTipo, CONTRATOS_OUTSOURCING_ID_LIST);
    } catch (\Throwable $th) {
        //throw $th;
        return false;
    }
}

/**
 * temContratoOutsourcing function
 *
 * @return int|bool
 */
function temContratoOutsourcing() {

    // if (!is_array($contratosIds)) {
    //     if (
    //         !is_array($_SESSION) ||
    //         !$_SESSION['id_pessoa'] ||
    //         !$_SESSION['contratos']
    //     ) { return false; }
    //     $contratosIds = $_SESSION['contratos'];
    // }
    // //
    // $contratosAtivos = obterContratosAtivos($contratosIds);
    // if (!is_array($contratosAtivos) || \count($contratosAtivos) <= 0) { return false; }
    // //
    // $temOutsourcing = false;
    // foreach ($contratosAtivos as $contrato) {
    //     if (
    //         is_array($contrato) &&
    //         array_key_exists('id_tipo', $contrato) &&
    //         in_array($contrato['id_tipo'], CONTRATOS_OUTSOURCING_ID_LIST)
    //     ) {
    //         $temOutsourcing = true;
    //     }
    // }
    //
    $ctOutsourcingId = $_SESSION['contrato_outsourcing_id'] ?? null;
    return (isset($ctOutsourcingId) && is_numeric($ctOutsourcingId))
        ? intval($ctOutsourcingId)
        : false;
}
