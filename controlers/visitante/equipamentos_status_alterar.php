<?php
require_once __DIR__ . "/../../helpers/typeHelpers.php";
require_once __DIR__ . "/../../helpers/arrayHelpers.php";
require_once __DIR__ . "/../../helpers/httpHelpers.php";
require_once __DIR__ . "/../../helpers/cryptoHelpers.php";

require_once __DIR__ . "/../../models/contrato.php";
require_once __DIR__ . "/../../models/chamado.php";
require_once __DIR__ . "/../../models/contato.php";
require_once __DIR__ . "/../../models/equipamentos.php";

require_once __DIR__ . "/../../services/eqptoStatusService.php";

$dbh = getConexao();

$showForm = false;
$showPageMessage = false;
$pageMessage = null;

$codigoSeguranca = null;
$acao = null;

$idEqpto = null;
$idEqptoDesativacao = null;
$idChamado = null;

// Busca e composicao de dados p/ alimentar logica e formulario
if (isGET()) {

    $acao = safeHttpGetVar('acao');
    $idEqpto = safeHttpGetVar('id');
    $codigoSeguranca = safeHttpGetVar('seguranca');

    if (
        !isValidString($acao) 
        || !isValidInteger($idEqpto)
        || !isValidString($codigoSeguranca)
    ) {
        $messages[] = [
            'type' => 'Error',
            'text' => 'Erro na validação parametros da requisição da pagina Web!'
        ];

        $pageMessage = <<<EOT
            Erro na validação parametros da requisição da pagina Web! <br />
            Favor entrar em contacto com o suporte.
        EOT;

        $showPageMessage = true;
        include __DIR__ . '/../../views/visitante/equipamentos_status_alterar.php';
        exit();
    }
    
    $_SESSION['idEqpto'] = $idEqpto;
    $_SESSION['codigoSeguranca'] = $codigoSeguranca;

    $eqptoDesativacao = obterEqptoDesativacaoByToken($codigoSeguranca, ['*'], $dbh);
    if (
        !isValidArray($eqptoDesativacao) 
        || !arrayHasKeys($eqptoDesativacao, [
            'id',
            'id_contrato',
            'id_chamado',
            'id_equipamento',
            'id_gerente_contas'
        ])
        || !isValidInteger($eqptoDesativacao['id_chamado'])
        || !isValidInteger($eqptoDesativacao['id_equipamento'])
    ) {
        $messages[] = [
            'type' => 'Error',
            'text' => 'Codigo de segurança inválido ou operação já concluida!'
        ];

        $pageMessage = <<<EOT
            Codigo de segurança inválido ou operação já concluida! <br />
            Favor entrar em contacto com o suporte.
        EOT;

        $showPageMessage = true;
        include __DIR__ . '/../../views/visitante/equipamentos_status_alterar.php';
        exit();
    }

    $idEqptoDesativacao = $eqptoDesativacao['id'];
    $idChamado = $eqptoDesativacao['id_chamado'];
    $idEqpto = $eqptoDesativacao['id_equipamento'];
    $idGerenteContas = $eqptoDesativacao['id_gerente_contas'];

    $_SESSION['idGerenteContas'] = $idGerenteContas;
    $_SESSION['idEqptoDesativacao'] = $idEqptoDesativacao;
    $_SESSION['idChamado'] = $idChamado;
    
    $aprovado = ($acao == 'desativar');
    $respAvaliacao = atualizarAvaliacaoEqptoDesativacao($codigoSeguranca, $aprovado, $dbh);
    if (!$respAvaliacao) {
        $messages[] = [
            'type' => 'Error',
            'text' => 'Erro no sistema ao tentar finalizar solicitação desativar equipamento!'
        ];

        $pageMessage = <<<EOT
            Erro no sistema ao tentar finalizar solicitação desativar equipamento! <br />
            Favor entrar em contacto com o suporte.
        EOT;

        $showPageMessage = true;
        include __DIR__ . '/../../views/visitante/equipamentos_status_alterar.php';
        exit();
    }
   
    $status = $aprovado ? CONTRATO_OUTSOURCING_EQUIPAMENTO_INATIVO :  CONTRATO_OUTSOURCING_EQUIPAMENTO_ATIVO;
    $respEqptoStatus = atualizarStatusEqpto($idEqpto, $status, $dbh);

    if (!$respEqptoStatus) {
        $messages[] = [
            'type' => 'Error',
            'text' => 'Erro no sistema ao tentar atualizar status do equipamento!'
        ];

        $pageMessage = <<<EOT
            Erro no sistema ao tentar atualizar status do equipamento! <br />
            Favor entrar em contacto com o suporte.
        EOT;

        $showPageMessage = true;
        include __DIR__ . '/../../views/visitante/equipamentos_status_alterar.php';
        exit();
    } 

    if ($acao == 'manter_ativado') {

        $showPageMessage = false;
        $showForm = true;
        include __DIR__ . '/../../views/visitante/equipamentos_status_alterar.php';
        exit();
    }


    if ($aprovado) {

        $descricaoEvento = 'Equipamento foi desativado com sucesso!';
        $resp = finalizarChamadoDesativacao($idChamado, $idEqpto, $idGerenteContas, $descricaoEvento);

        if ($resp) {
            removerTokenAvaliacaoEqptoDesativacao($codigoSeguranca);
        } else {
            $messages[] = [
                'type' => 'Error',
                'text' => 'Erro no sistema ao tentar finalizar chamado desativação de equipamento!'
            ];
    
            $pageMessage = <<<EOT
                Erro no sistema ao tentar finalizar chamado desativação de equipamento! <br />
                Favor entrar em contacto com o suporte.
            EOT;
    
            $showPageMessage = true;
            include __DIR__ . '/../../views/visitante/equipamentos_status_alterar.php';
            exit();
        }
    }
    
    $messages[] = [
        'type' => 'Success',
        'text' => 'O equipamento foi desativado com sucesso!'
    ];

    $pageMessage = <<<EOT
        O equipamento foi desativado com sucesso!
    EOT;

    $showPageMessage = true;
    include __DIR__ . '/../../views/visitante/equipamentos_status_alterar.php';
    exit();
}

// Finalizando processo desativacao com fechamento do Chamado e envio de emails
if (isPOST()) {

    $codigoSegurancaSession = $_SESSION['codigoSeguranca'];
    $codigoSeguranca = safeHttpPostVar('seguranca');
    $idChamado = safeHttpPostVar('id_chamado');
    $idEqpto = safeHttpPostVar('id_equipamento');
    $idGerenteContas = safeHttpPostVar('id_gerente_contas');
    $motivoManterAprovado = safeHttpPostVar('motivo_manter_ativao');

    $acao = safeHttpPostVar('acao');

    if (
        ($codigoSeguranca == $codigoSegurancaSession)
        && isValidString($acao) 
        && ($acao == 'finalizar_chamado_desativacao')
        && isValidInteger($idChamado)
        && isValidInteger($idEqpto)
        && isValidString($motivoManterAprovado)
    ) {
        $showForm = false;
        $showPageMessage = true;

        $descricaoEvento = $motivoManterAprovado;
        $resp = finalizarChamadoDesativacao($idChamado, $idEqpto, $idGerenteContas, $descricaoEvento);
        if ($resp) {
            removerTokenAvaliacaoEqptoDesativacao($codigoSeguranca);
            
            $messages[] = [
                'type' => 'Success',
                'text' => 'O Chamado foi encerrado e o Equipamento continua ativado conforme sua determinação!'
            ];
 
            $pageMessage = <<<EOT
               O Chamado foi finalizado e o equipamento continua ativado <br />
               conforme sua determinação!
            EOT;
 
        } else {
 
            $messages[] = [
                'type' => 'Error',
                'text' => 'Erro no sistema ao tentar finalizar chamado desativação de equipamento!'
            ];
    
            $pageMessage = <<<EOT
                Erro no sistema ao tentar finalizar chamado desativação de equipamento! <br />
                Favor entrar em contacto com o suporte.
            EOT;
        }

    }

    include __DIR__ . '/../../views/visitante/equipamentos_status_alterar.php';
    exit();
}