<?php
    require_once __DIR__ . '/../../helpers/codecsHelpers.php';
    if (!isset($redirecionar)) { $redirecionar = null; }
    if (!isset($messages)) { $messages = []; }
?>

<script>
    // Process single alert message.
    const alertMessage = "<?php echo($mensagem ?? ''); ?>";
    const alertTipoMensagem = "<?php echo($tipo_mensagem ?? ''); ?>";
    //
    if (String(alertTipoMensagem).length > 0 && String(alertMessage).length > 0) {
        procAlertMessage(String(alertMessage).trim(), String(alertTipoMensagem).trim());
        // messages.forEach(function(msgData) {
        //     alertMessages(msgData);
        // });
    }

    // Process multiples message data
    const hasError = "<?php echo((\is_array($messages) && (\count($messages) > 0)) ? 1 : 0); ?>";
    if (hasError && hasError == '1') {

        const messages = JSON.parse(htmlDecode('<?php echo(htmlDataJsonEncode($messages)); ?>'));
        if (hasError && hasError == '1' && messages && alertMessages) {
            messages.forEach(function(msgData) {
                alertMessages(msgData);
            });
        }

    } else {
        messages = null;
    }

    // Process Redirect data
    const redirecionar = "<?php echo($redirecionar ? 1 : 0); ?>";
    const uriRedirecionar = "<?php echo($uriRedirecionar ?? ''); ?>";

    if (redirecionar == '1' && uriRedirecionar) {
        let id = setTimeout(function(uriRedirecionar) {
            if (uriRedirecionar === 'reload') {
                window.location.reload();
            } else if (uriRedirecionar === 'back') {
                window.history.back();
                window.history.back();
            } else {
                window.location.assign(uriRedirecionar);
            }
        }, 2000, uriRedirecionar);
    }
</script>
