<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Central do Cliente</title>
    <!-- vendor css -->
    <link href="views/lib/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="views/lib/ionicons/css/ionicons.min.css" rel="stylesheet">
    <link href="views/lib/morris.js/morris.css" rel="stylesheet">
    <link href="views/lib/flag-icon-css/css/flag-icon.min.css" rel="stylesheet">
    <link href="views/lib/jqvmap/jqvmap.min.css" rel="stylesheet">
    <link href="views/css/box_helpdesk.css" rel="stylesheet">
    <!-- azia CSS -->
    <link rel="stylesheet" href="views/css/azia.css">  
    <!-- Chart.js -->
    <link rel="stylesheet" href="views/css/chart.css">
    <!-- Customizações -->
    <link rel="stylesheet" href="views/css/custom.css">
    <link rel="stylesheet" href="views/css/icones.css">
    <?php require_once __DIR__ . '/icones.php'; ?>
    <!-- Jquery -->
    <script src="views/lib/jquery/jquery.min.js"></script>
    <script src="views/js/telefone.js"></script>
    <script src="views/js/troca-senha.js"></script>
    <script src="views/js/abrir_chamado.js"></script>
    
</head>
<body class="az-body az-body-sidebar az-light">
    <?php include 'sidebar.php' ?>
    <div class="az-content az-content-dashboard-five <?=($pagina=='Listar Fechados'?'chamados-fechados-page':'')?>">
        <div class="az-header">
            <div class="container-fluid">
                <div class="az-header-left">
                    <a href="" id="azSidebarToggle" class="az-header-menu-icon"><span></span></a>
                </div>
                <div class="az-header-center"></div>
                <div class="az-header-right">
                    <div class="dropdown az-profile-menu">
                        <a href="" class="az-img-user"><img src="/views/img/icon_user.png" alt=""></a>
                        <div class="dropdown-menu">
                            <div class="az-dropdown-header d-sm-none">
                                <a href="" class="az-header-arrow"><?=iconeInterface('voltar')?></a>
                            </div>
                            <div class="az-header-profile">
                                <div class="az-img-user">
                                    <img src="/views/img/icon_admin.png" alt="">
                                </div>
                                <h6><?=$_SESSION['nome_cliente']?></h6>
                                <span>Administrador</span>
                            </div>
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modalTrocarSenha"><?=iconeInterface('configuracoes')?> Trocar Senha</a>
                            <a href="logout" class="dropdown-item"><?=iconeInterface('sair')?> Sair</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Trocar Senha -->
        <div id="msgErro" class="text-danger mt-2" style="display:none;"></div>
        <div class="modal fade" id="modalTrocarSenha" tabindex="-1" role="dialog" aria-labelledby="modalTrocarSenhaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
               <div class="modal-content">
                   <div class="modal-header">
                        <h5 class="modal-title" id="modalTrocarSenhaLabel">Trocar Senha</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="/trocar_senha">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="senhaAtual">Senha Atual</label>
                                <input type="password" class="form-control" id="senhaAtual" name="senha_atual" required>
                            </div>
                            <div class="form-group">
                                <label for="novaSenha">Nova Senha</label>
                                <input type="password" class="form-control" id="novaSenha" name="nova_senha" required>
                            </div>
                            <div class="form-group">
                                <label for="confirmarSenha">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirmarSenha" name="confirmar_senha" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    