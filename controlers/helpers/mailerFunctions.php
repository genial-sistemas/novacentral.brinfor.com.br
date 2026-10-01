<?php
require_once(__DIR__ . '/../bibliotecas/phpmailer/class.phpmailer.php');
require_once(__DIR__ . '/../models/conexao.php');
require_once(__DIR__ . '/../models/misc.php');
require_once(__DIR__ . '/../models/pessoa.php');

require_once(__DIR__ . '/typesHelper.php');
require_once(__DIR__ . '/loggerFunctions.php');
require_once(__DIR__ . '/httpHelpers.php');
require_once __DIR__ . '/sessionDataHelpers.php';

require_once __DIR__ . '/../config/mailerConfig.php';

// function mailSender($to="", $subject="", $body="") {
//     try {
//         // p/ testes
//         //$to = 'dev01@genialsistemas.com.br';
//         $to = \trim($to);
//         $subject = \trim($subject);
//         //
//         $mail = new PHPMailer();

//         $mail->IsSMTP(); // evia por SMTP
//         $mail->Host = "mail.bhcloud.com.br"; // SMTP servers
//         $mail->SMTPAuth = true; // Caso o servidor SMTP precise de autentica��o
//         $mail->Username = "sistema@bhcloud.com.br"; // SMTP username
//         $mail->Password = "bhbm2915"; // SMTP password
//         $mail->CharSet = 'UTF-8';

//         $mail->From = "sistema@bhcloud.com.br"; // From. O padr�o est� vendasnet@bhinfor.com
//         $mail->FromName = "BRInfor Soluções em TI"; // Nome de quem envia o email. O padr�o est� BHInfor Computadores
//         $mail->AddAddress("$to",""); // Email e nome de quem receber�
//         //$mail->AddReplyTo("info@site.com","Information"); //Responder
//         //$mail->AddCC("info@site.com","Nome"); //Com c�pia
//         //$mail->AddBCC("info@site.com","Nome"); //Com c�pia oculta

//         $mail->WordWrap = 50; // Definir quebra de linha
//         //$mail->AddAttachment("olamundo.jpg"); // Anexo 1
//         //$mail->AddAttachment("olamundo.jpg", "new.jpg"); // Anexo 2 renomeado
//         $mail->IsHTML(true); // Enviar como HTML

//         $mail->Subject = "$subject"; // Assunto
//         $mail->Body = "$body"; //Corpo da mensagem caso seja HTML
//         //$mail->AltBody = "This is the text-only body"; //PlainText, para caso quem receber o email n�o aceite o corpo HTML

//         // Tenta enviar o email
//         if ( ! $mail->Send() ) {
//             $errorContextData = [
//                 'to' => $to, 
//                 'subject' => $subject, 
//                 'body' => $body
//             ];
//             logErrorOnSendMail($errorContextData);
//             return false;
//         } else {
//             return true;
//         }

//     } catch (\Throwable $th) {
//         //throw $th;
//         $errorContextData = [
//             'to' => $to, 
//             'subject' => $subject, 
//             'body' => $body,
//             'systemErrorMessage' => $th->getMessage()
//         ];
//         logErrorOnSendMail($errorContextData);
//         return false;
//     }
// }

function mailSender($to="", $subject="", $body="") {
    try {
        //
        $to = \trim($to);
        $subject = \trim($subject);
        //
        $mail = new PHPMailer();

        $mail->IsSMTP(); // evia por SMTP

        $mail->Host = MAIL_HOST_NAME; // SMTP servers
        $mail->Port = MAIL_HOST_PORT; // SMTP server port

        $mail->SMTPAuth = MAIL_SMTP_AUTH; // Caso o servidor SMTP precise de autentica��o
        $mail->Username = MAIL_SMTP_USERNAME; // SMTP username
        $mail->Password = MAIL_SMTP_PASSWORD; // SMTP password
		
		$mail->SMTPDebug = 1;
		$mail->Debugoutput = 'error_log';

        $mail->CharSet = 'UTF-8';

        $mail->From = "sistema@bhcloud.com.br"; // From. O padr�o est� vendasnet@bhinfor.com
        $mail->FromName = "BRInfor Soluções em TI"; // Nome de quem envia o email. O padr�o est� BHInfor Computadores

        $mail->AddAddress("$to",""); // Email e nome de quem receber�
        //$mail->AddReplyTo("info@site.com","Information"); //Responder
        //$mail->AddCC("info@site.com","Nome"); //Com c�pia
        //$mail->AddBCC("info@site.com","Nome"); //Com c�pia oculta

        $mail->WordWrap = 50; // Definir quebra de linha
        //$mail->AddAttachment("olamundo.jpg"); // Anexo 1
        //$mail->AddAttachment("olamundo.jpg", "new.jpg"); // Anexo 2 renomeado
        $mail->IsHTML(true); // Enviar como HTML

        $mail->Subject = "$subject"; // Assunto
        $mail->Body = "$body"; //Corpo da mensagem caso seja HTML
        //$mail->AltBody = "This is the text-only body"; //PlainText, para caso quem receber o email n�o aceite o corpo HTML

        // Tenta enviar o email
        if ( ! $mail->Send() ) {
            $errorContextData = [
                'to' => $to, 
                'subject' => $subject, 
                'body' => $body
            ];
            logErrorOnSendMail($errorContextData);
            return false;
        } else {
            return true;
        }

    } catch (\Throwable $th) {
        //throw $th;
        $errorContextData = [
            'to' => $to, 
            'subject' => $subject, 
            'body' => $body,
            'systemErrorMessage' => $th->getMessage()
        ];
        logErrorOnSendMail($errorContextData);
        return false;
    }
}


function renderHtmlEventosEmailChamado($eventosList) {
    
    //$eventosList = obterContratoChamadoEventosPorIdChamado($htmlData['id_chamado']);

    $htmTop = <<<EODTOP
<h1 style="font-size:24px;margin:20px 0 20px 0;font-family:Arial,sans-serif; text-align: center">Eventos</h1>
<table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
    <thead>
        <tr>
            <th style="vertical-align: bottom; border-bottom: 2px solid #dee2e6;color: #70737c; font-weight: 700; font-size: 11px; letter-spacing: .5px; text-transform: uppercase; border-bottom-width: 1px; border-top-width: 0; padding: 0 15px 5px;">Data-Hora</th>
            <th style="vertical-align: bottom; border-bottom: 2px solid #dee2e6;color: #70737c; font-weight: 700; font-size: 11px; letter-spacing: .5px; text-transform: uppercase; border-bottom-width: 1px; border-top-width: 0; padding: 0 15px 5px;">Responsável Evento</th>
            <th style="vertical-align: bottom; border-bottom: 2px solid #dee2e6;color: #70737c; font-weight: 700; font-size: 11px; letter-spacing: .5px; text-transform: uppercase; border-bottom-width: 1px; border-top-width: 0; padding: 0 15px 5px;">Descrição</th>
            <th style="vertical-align: bottom; border-bottom: 2px solid #dee2e6;color: #70737c; font-weight: 700; font-size: 11px; letter-spacing: .5px; text-transform: uppercase; border-bottom-width: 1px; border-top-width: 0; padding: 0 15px 5px;">Status</th>
        </tr>
    </thead>
    <tbody style="color: #343640;">
EODTOP;

    $htmlMiddle = '';

    foreach ($eventosList as $lh) {
        $pessoaEvento = null;
        $pessoaNome = '';

        $idPessoa = getSafeArrayKeyValue($lh, "id_pessoa");
        if (isValidInteger($idPessoa)) {
            $pessoaEvento = obterPessoaGenericaPorIdPessoa($idPessoa);
            $pessoaNome = getSafeArrayKeyValue($pessoaEvento, 'nome');
        }

        $idContato = getSafeArrayKeyValue($lh, "id_contato");
        $idEqpto = getSafeArrayKeyValue($lh, "id_equipamento");

        if (isValidInteger($idContato)) {
            if (isValidInteger($idEqpto)) {
                $pessoaEvento = obterPessoaJuridicaContatoPorId($idContato);
            } else {
                $pessoaEvento = obterPessoaGenericaPorIdPessoa($idContato);
            }
            $pessoaNome = getSafeArrayKeyValue($pessoaEvento, 'nome');
        }

        $descricao	= stripslashes($lh["descricao"]);
        $status		= $lh["status"];
        $dataHora   = data_brasil($lh["data"]) . '-' . $lh["hora"];
        $statusNome = ($status);

        $htmlOut = <<<EODMIDDLE
            <tr style="background-color: rgba(255, 255, 255, 0.5);">
                <td style="padding: 9px 15px; border-bottom: 1px solid #dee2e6; line-height: 1.462;">$dataHora</td>
                <td style="padding: 9px 15px; border-bottom: 1px solid #dee2e6; line-height: 1.462;">$pessoaNome</td>
                <td style="font-size: 0.7125rem; padding: 9px 15px; border-bottom: 1px solid #dee2e6; line-height: 1.462; word-break: break-all;">$descricao</td>
                <td style="padding: 9px 15px; border-bottom: 1px solid #dee2e6; line-height: 1.462;">$statusNome</td>
            </tr>
        EODMIDDLE;

        $htmlMiddle .= $htmlOut;
    }

    $htmlBottom = <<<EODBOTTOM
    </tbody>
    </table>
</td>
</tr>
</table>   
EODBOTTOM; 

    return $htmTop . $htmlMiddle . $htmlBottom;
}

function renderHtmlEmailChamado($htmlData) {

    $resp_nome = $htmlData['nome'];
    $eventosList = obterContratoChamadoEventosPorIdChamado($htmlData['id_chamado']);

    $html_email = '<!DOCTYPE html>
<html lang="en" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title></title>
    <style>
        table, td, div, h1, p {font-family: Arial, sans-serif;}
    </style>
</head>
<body style="margin:0;padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
    <tr>
        <td align="center" style="padding:0;">
            <table role="presentation" style="width:800px;border-collapse:collapse;border:1px solid #fa9758;border-spacing:0;text-align:left;">
                <tr>
                    <td align="center" style="padding:40px 0 30px 0;background:#fa9758;"></td>
                </tr>
                <tr>
                    <td align="center" style="padding:40px 0 30px 0; border-bottom: 1px solid #fa9758;">
                        <img src="https://central.brinfor.com.br/views/img/brInfor_logo_mini.png" alt="" width="300" style="height:auto;display:block;" />
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 30px 42px 30px;">
                        <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
                            ';

    if ($htmlData['status'] == 7) {
        $html_email .= '<tr>
                <td>
                    <div style="width: 100%; text-align: right">
                        <a style="text-decoration: none; border-top: #268820 10px solid; border-right: #268820 20px solid; background: #268820; border-bottom: #268820 10px solid; font-weight: bold; color: white; border-left: #268820 20px solid; display: inline-block;" href="https://central.brinfor.com.br/avaliar-chamado?id=' . $htmlData['id_chamado'] . '&seguranca=' . $_SESSION['seguranca'] . '">Avaliar Chamado</a>
                    </div>
                </td>
            </tr>';
    }

                                $html_email .= '
<tr>
<td style="color:#153643;text-align: left">
                                    <h1 style="font-size:24px;margin:0 0 20px 0;font-family:Arial,sans-serif;width: 100%; text-align: center">Chamado: '.$htmlData['id_chamado'].'</h1>
                                    <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                        <b>Contrato:</b> '.$htmlData['contrato_nome'].'
</p>
                                    <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                        <b>Responsável Abertura:</b> '.($resp_nome === null || empty($resp_nome) ? 'Usuário' : $resp_nome).'
</p>
                                    <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                        <b>Email Abertura:</b> '.($htmlData['email_usuario_chamado'] === null || empty($htmlData['email_usuario_chamado']) ? 'Usuário' : $htmlData['email_usuario_chamado']).'
</p>
                                    <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                        <b>Telefone Abertura:</b> '.($htmlData['obs'] === null || empty($htmlData['obs']) ? 'Usuário' : $htmlData['obs']).'
</p>
                                    <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                        <b>Tipo Chamado:</b> '.$htmlData['tipo_nome'].'
                                    </p>
                                    <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                        <b>Tipo Abertura:</b> '.$htmlData['abertura_tipo'].'
                                    </p>
                                    <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                        <b>Tipo Atendimento:</b> '.$htmlData['atendimento_nome'].'
                                    </p>
                                    <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                        <b>Urgência:</b> '.$htmlData['urgencia_nome'].'
</p>
                                    <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                        <b>Data e Hora Abertura:</b> '.date('d/m/Y').' - '.date('H:i:s').'
</p>
                                    <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                        <b>E-mail do Usuário:</b> '.$htmlData['email_usuario'].'
                                    </p>
								';
    
    if ($htmlData['tipo_contrato'] == 1) {

        $fet_equipa = obterContratoOutsourcingEquipamentoPorIdEquipamento($htmlData['id_equipamento']);
        $usuario	= $fet_equipa["usuario"];
        //$email		= $fet_equipa["email"];
        $etiqueta	= $fet_equipa["codigo"];
        $contato	= $fet_equipa["contato"];

        $html_email.=' <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                            <b>SLA Atendimento:</b>	'.$htmlData['sla_atendimento'].'
                        </p>
                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                        <b>Equipamento:</b>	'.$etiqueta.' - '.$usuario.'
                                    </p>
                      ';

        if ($contato == 0 or $contato == NULL) {
            $email_usuario = $fet_equipa["email"];
        } else {
            $email_usuario = $htmlData['email_usuario'];
        }
    }

    $html_email.='</td>
            </tr>';

    if ($htmlData['status'] != 7 ) {
        $html_email .= '<tr>
                <td>
                    <div style="width: 100%; text-align: right">
                        <a style="text-decoration: none; border-top: #dd9933 10px solid; border-right: #dd9933 20px solid; background: #dd9933; border-bottom: #dd9933 10px solid; font-weight: bold; color: white; border-left: #dd9933 20px solid; display: inline-block;" href="https://novacentral.brinfor.com.br/index.php?page=visitante-interagir-chamado&id='. $htmlData['id_chamado'] . '&seguranca='. $_SESSION['seguranca'] . '">Nova Interação</a>
                    </div>
                </td>
            </tr>';
    }
    $htmlEventos = renderHtmlEventosEmailChamado($eventosList);

    $html_email .='
            <tr>
                <td>'. $htmlEventos .'
                </td>
            </tr>

                <tr>
                    <td style="padding:30px;background:#fa9758;">
                        <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">
                            <tr>
                                <td style="padding:0;width:100%;" align="center">
                                    <p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">
                                        &copy; 2002 - ' . date('Y') . ' Genial Sistemas Ltda.<br/>
                                    </p>
                                </td>
                                <td style="padding:0;width:50%;" align="right">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>';

    return $html_email;
}

function enviarEmailChamado(
    $id_chamado, 
    $id_contrato, 
    $vendedor, 
    $id_equipamento, 
    $tipo_contrato, 
    $urgencia, 
    $tipo_atendimento, 
    $tipo_abertura, 
    $obs, 
    $nome = 'Usuário', 
    $email_usuario_chamado = null
) {
    
    
    // Email Gerente
    $email_gerente = obterEmailGerentePorIdContrato($id_contrato);

    //$email_usuario = null;
    $email_usuario = $email_usuario_chamado;

    // Id Situacao Contrato Chamado
    $status = obterContratoIdSituacaoPorIdChamado($id_chamado);

    // Nome Tipo Contrato
    $tipo_nome = obterContratoTipoDescricaoPorIdTipoContrato($tipo_contrato);

    // Tipo Urgencia
    $urgencia_nome = obterContratoChamadoTipoUrgenciaPorIdTipoUrgencia($urgencia);

    // Tipo Atendimento
    $atendimento_nome = obterContratoChamadoTipoAtendimentoPorIdTipoAtendimento($tipo_atendimento);

    // Tipo Abertura
    $abertura_tipo = obterContratoChamadoTipoAberturaPorIdTipoAbertura($tipo_abertura);
    
    $label = $status == 7 ? 'Avaliação' : 'Abertura';

    $sla_atendimento = '';

    // Se tipo contrato = 'outsourcing'
    if ($tipo_contrato == 1) {

        // Email Usuario do Equipamento
        $email_usuario = obterContratoOutsourcingEqpEmailContato($id_equipamento);

        // SLA Atendimento
        $dados = obterContratoOutsourcingNomeESlaPorIdContrato($id_contrato);
        $contrato_nome = $dados["nome"];
        $sla_atendimento = (empty($dados["sla_atendimento"]) || $dados["sla_atendimento"] == null  || $dados["sla_atendimento"] == 0) 
            ? 'Não cadastrado' 
            : $dados["sla_atendimento"] . ' horas';

    } else {
        // Nome Contrato/Pessoa
        $contrato_nome = obterContratoNomePessoaPorIdContrato($id_contrato);
    }

    $htmlData = [
      'id_chamado' => $id_chamado, 
      'id_equipamento' => $id_equipamento, 
      'tipo_contrato' => $tipo_contrato, 
      'urgencia_nome' => $urgencia_nome, 
      'atendimento_nome' => $atendimento_nome, 
      'contrato_nome' => $contrato_nome,
      'tipo_nome' => $tipo_nome,
      'abertura_tipo' => $abertura_tipo, 
      'obs' => $obs, 
      'nome' => $nome, 
      'email_usuario_chamado' => $email_usuario_chamado,
      'sla_atendimento' => $sla_atendimento,
      'status' => $status,
      'email_usuario' => $email_usuario
    ];

    $htmlEmailBody = renderHtmlEmailChamado($htmlData);

    // Send mail to Suporte BRInfo
    $to = "suporte@brinfor.com.br";
    $subject = "$label de Chamado Site - $id_chamado - $tipo_nome - $contrato_nome";
    $okMailSuporte = mailSender($to, $subject, $htmlEmailBody);

    $okMailUsuarioChamado = false;
    $okMailVendedor = false;
    $okMailUsuarioEmpresa = false;
    $okMailGerente = false;

    // Try send mail to Usuario do Chamado
    if ($email_usuario_chamado != null) {
        $to = $email_usuario_chamado;
        $subject = "$label de Chamado Site - $id_chamado - $tipo_nome - $contrato_nome";
        $okMailUsuarioChamado = mailSender($to, $subject, $htmlEmailBody);
    }

    // Try send mail to Vendedor relacionado
    if (
        $status != 7 
        && $vendedor != 0
    ) {
        $to = obterFuncionarioEmailPorVendedor($vendedor);
        $subject = "$label de Chamado Site - $id_chamado - $tipo_nome - $contrato_nome";
        $okMailVendedor = mailSender($to, $subject, $htmlEmailBody);
    }

    // Try send mail to Usuario Empresa
    if (
        $email_usuario != "" 
        && $email_usuario_chamado != $email_usuario
    ) {
        $to = $email_usuario;
        $subject = "$label de Chamado Site BRInfor - $id_chamado - $tipo_nome - $contrato_nome";
        $okMailUsuarioEmpresa = mailSender($to, $subject, $htmlEmailBody);
    }

    // Try send mail to Gerente Empresa
    if (
        $status != 7 
        && $email_gerente != "" 
        && $email_gerente != $email_usuario 
        && $email_usuario_chamado != $email_gerente
    ) {
        $to = $email_gerente;
        $subject = "$label de Chamado Site BRInfor  - $id_chamado - $tipo_nome - $contrato_nome";
        $okMailGerente = mailSender($to, $subject, $htmlEmailBody);
    }

    return $okMailSuporte && ($okMailUsuarioChamado || $okMailUsuarioEmpresa);
}

