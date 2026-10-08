<?php
require_once __DIR__ . '/../models/documentacao_cliente.php';
$temContratoHelpdesk = obterContratoHelpdesk($_SESSION['contratos'] ?? array()) !== null;
?>
<div class="az-sidebar">
    <div class="az-sidebar-header">
        <a href="/dashboard" class="az-logo">
            <img src="/views/img/brInfor_logo_mini.png" class="img-fluid" alt="BRInfor logo">
        </a>
    </div>
    <div class="az-sidebar-loggedin">
        <div class="az-img-user online"><img src="/views/img/icon_admin.png" alt=""></div>
        <div class="media-body">
            <h6><?=$_SESSION['nome_cliente']?></h6>
            <span>Adminstrador</span>
        </div>
    </div>
    <div class="az-sidebar-body">
        <ul class="nav">
            <li class="nav-label">Menu Principal</li>
            <li class="nav-item <?=($menu=='Dashboard'?'active show':'')?>">
                <a href="/dashboard" class="nav-link with-sub"><i class="typcn typcn-chart-bar-outline"></i>Dashboard</a>
                <nav class="nav-sub">
                    <a href="/dashboard" class="nav-sub-link <?=($pagina=='Início'?'active':'')?>">Início</a>
                </nav>
            </li>
            <li class="nav-item <?=($menu=='Chamados'?'active show':'')?>">
                <a href="/chamados_listar_abertos" class="nav-link with-sub"><i class="typcn typcn-clipboard"></i>Chamados</a>
                <nav class="nav-sub">
                    <a href="/chamados_abrir" class="nav-sub-link <?=($pagina=='Abrir'?'active':'')?>">Abrir</a>
                    <a href="/chamados_listar_abertos" class="nav-sub-link <?=($pagina=='Listar Abertos'?'active':'')?>">Listar Abertos</a>
                    <a href="/chamados_listar_fechados" class="nav-sub-link <?=($pagina=='Listar Fechados'?'active':'')?>">Listar Fechados</a>
                </nav>
            </li>
            <li class="nav-item <?=($menu=='Funcionários'?'active show':'')?>">
                <a href="/funcionarios_listar" class="nav-link with-sub"><i class="typcn typcn-group"></i>Funcionários</a>
                <nav class="nav-sub">
                    <!-- Os nomes das páginas correspondem aos valores definidos nos controladores. -->
                    <a href="/funcionarios_cadastrar" class="nav-sub-link <?=($pagina=='Cadastrar'?'active':'')?>">Cadastrar</a>
                    <a href="/funcionarios_listar" class="nav-sub-link <?=($pagina=='Listar'?'active':'')?>">Listar</a>
                </nav>
            </li>
            <li class="nav-item <?=($menu=='Relatórios'?'active show':'')?>">
                <a href="/relatorio_periodo" class="nav-link with-sub"><i class="typcn typcn-tabs-outline"></i>Relatórios</a>
                <nav class="nav-sub">
                    <a href="/relatorio_periodo" class="nav-sub-link <?=($pagina=='Por período'?'active':'')?>">Por período</a>
                    <a href="/relatorio_equipamento" class="nav-sub-link <?=($pagina=='Por equipamento'?'active':'')?>">Por equipamento</a>
                </nav>
            </li>
            <?php if ($temContratoHelpdesk): ?>
                <li class="nav-item">
                    <a href="/documentacao_cliente" class="nav-link"><i class="typcn typcn-document-text"></i>Documentação</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>