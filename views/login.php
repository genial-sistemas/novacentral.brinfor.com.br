<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Nova Central do Cliente</title>
    <!-- vendor css -->
    <link href="views/js/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="views/js/vendor/ionicons/css/ionicons.min.css" rel="stylesheet">
    <!-- azia CSS -->
    <link rel="stylesheet" href="views/css/azia.css">
    <!-- Customizações -->
    <link rel="stylesheet" href="views/css/custom.css">
</head>
<body class="az-body">
    <div class="az-signin-wrapper">
        <div class="az-card-signin">
            <a href="#" class="az-logo">
                <img src="views/img/brInfor_logo_mini.png" class="img-fluid" alt="Br Info logo">
            </a>
            <div class="az-signin-header">
                <h2>Central do cliente!</h2>
                <h4>Por favor entre para continuar</h4>
                <?php if (isset($_GET['erro']) && $_GET['erro'] === '1'): ?>
                    <div class="alert alert-danger" role="alert">Usuário ou senha inválidos.</div>
                <?php endif; ?>
               <form action="autenticacao" method="post">
                    <div class="form-group">
                        <label>Usuário</label>
                        <input type="text" class="form-control" name="usuario" placeholder="Digite seu usuário">
                    </div><!-- form-group -->
                    <div class="form-group">
                        <label>Senha</label>
                        <input type="password" class="form-control" name="senha" placeholder="Digite sua senha">
                    </div><!-- form-group -->
                    <button class="btn btn-az-primary btn-block" name="acao" value="logar">Entrar</button>
                </form>
            </div><!-- az-signin-header -->
            <div class="az-signin-footer">
                <p class="text-center"><a href="">Esqueceu a senha?</a></p>
            </div>
        </div><!-- az-card-signin -->
    </div><!-- az-signin-wrapper -->
    <script src="views/js/vendor/jquery/jquery.min.js"></script>
    <script src="views/js/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="views/js/azia.js"></script>
</body>
</html>