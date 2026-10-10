    <?php
        require_once __DIR__ . '/../../controlers/funcoes.php';
        if (!isset($redirecionar)) { $redirecionar = null; }
        if (!isset($messages)) { $messages = []; }
    ?>
        <div class="az-footer">
            <div class="container-fluid" style="justify-content:center;">
                <span>Copyright &copy; 2002-<?php echo date("Y");?> <a href="https://genialsistemas.com.br/" target="_blank">Genial Sistemas Ltda</a>.
                    Todos os direitos reservados.</span>
            </div><!-- container -->
        </div><!-- az-footer -->
    </div> <!-- az-content -->

    <script src="/views/js/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="/views/js/azia.js"></script>
    <script src="/views/js/iziToast.min.js"></script>
    
    <script>
        let hasError = <?php echo($hasError ? 1 : 0) ?>;
        let redirecionar = <?php echo($redirecionar ? 1 : 0) ?>;
        let uriRedirecionar = '<?php echo($uriRedirecionar ?? '') ?>';

        <?php if (is_array($messages) && (\count($messages) > 0)) : ?>
            let messages = JSON.parse(htmlDecode('<?php echo(htmlDataJsonEncode($messages)) ?>'));
            messages.forEach(function(msgData) {
                alertMessages(msgData);
            });
        <?php endif; ?>

        if (redirecionar == 1) {
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
</body>
</html>
