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
                    <!-- Cada linha representa um funcionário com ações de edição e exclusão. -->
                    <?php
                        foreach($contatos as $ca): ?>
                            <tr>
                                <td style="vertical-align: middle;"><?=$ca['nome']?></td>
                                <td style="vertical-align: middle;"><?=$ca['descricao']?></td>
                                <td style="vertical-align: middle;"><?=$ca['tel'] . ($ca['ramal'] != '' && $ca['ramal'] != null ? ' - Ramal: ' . $ca['ramal'] : '')?></td>
                                <td style="vertical-align: middle;"><?=$ca['cel']?></td>
                                <td style="vertical-align: middle;"><?=$ca['email']?></td>
                                <td class="funcionario-acoes">
                                    <div class="funcionario-acoes-grupo">
                                        <a
                                            href="/funcionarios_editar?id=<?=$ca['id']?>"
                                            class="funcionario-acao funcionario-acao-editar"
                                            title="Editar funcionário"
                                            aria-label="Editar funcionário"
                                        >
                                            <?=iconeInterface('editar')?>
                                        </a>
                                        <a
                                            href="funcionarios_cadastrar?id=<?=$ca['id']?>&acao=apagar"
                                            class="funcionario-acao funcionario-acao-apagar"
                                            title="Apagar funcionário"
                                            aria-label="Apagar funcionário"
                                        >
                                            <?=iconeInterface('apagar')?>
                                        </a>
                                    </div>
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
