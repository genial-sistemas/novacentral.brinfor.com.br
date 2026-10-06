<?php
// O cabeçalho compartilha o layout principal, menu lateral e estilos da aplicação.
include 'header.php'; ?>

<!-- Título e listagem dos funcionários ativos retornados pelo controlador. -->
<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Funcionários</h2>
    </div>
</div><!-- az-content-header -->
<div class="az-content-body">
    <div class="row row-sm">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table mg-b-0">
                    <!-- Colunas correspondentes aos dados e às operações disponíveis na listagem. -->
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Cargo</th>
                            <th>Telefone</th>
                            <th>Celular</th>
                            <th>E-mail</th>
                            <th>Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                    <!-- Cada linha representa um funcionário; os links passam ID e ação ao formulário de cadastro. -->
                    <?php
                        foreach($contatos as $ca): ?>
                            <tr>
                                <td style="vertical-align: middle;"><?=$ca['nome']?></td>
                                <td style="vertical-align: middle;"><?=$ca['descricao']?></td>
                                <td style="vertical-align: middle;"><?=$ca['tel'] . ($ca['ramal'] != '' && $ca['ramal'] != null ? ' - Ramal: ' . $ca['ramal'] : '')?></td>
                                <td style="vertical-align: middle;"><?=$ca['cel']?></td>
                                <td style="vertical-align: middle;"><?=$ca['email']?></td>
                                <td style="vertical-align: middle;">
                                    <a href="funcionarios_cadastrar?id=<?=$ca['id']?>&acao=editar" style="font-size: 13px" class="btn btn-az-primary">
                                        Editar
                                    </a>
                                    <a href="funcionarios_cadastrar?id=<?=$ca['id']?>&acao=apagar" style="font-size: 13px; background-color: #dd3333" class="btn btn-az-primary">
                                        Apagar
                                    </a>
                                </td>
                            </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- az-content-body -->

<?php
// O rodapé fecha o layout compartilhado da aplicação.
include 'footer.php'; ?>
