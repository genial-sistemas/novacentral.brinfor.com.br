<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title></title>
</head>
<body style="display: none;">
    <script>
        let redirecionar = <?php echo($redirecionar ? 1 : 0) ?>;
        let uriRedirecionar = '<?php echo($uriRedirecionar ?? '') ?>';

        if (redirecionar == 1) {
            let id = setTimeout(function(uriRedirecionar) {
                window.location.replace('');
                window.location.assign(uriRedirecionar);
            }, 500, uriRedirecionar);
        }
    </script>
</body>
</html>
