<?php
// O cabeçalho compartilha o layout principal, menu lateral e estilos da aplicação.
include 'header.php';
$h = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }; ?>

<!-- Título e listagem dos funcionários ativos retornados pelo controlador. -->
<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Funcionários</h2>
    </div>
</div><!-- az-content-header -->
<div class="az-content-body">
    <?php if (isset($_GET['editado'])): ?>
        <div class="alert alert-success" role="alert">Funcionário editado com sucesso.</div>
    <?php elseif (isset($_GET['erro_editar'])): ?>
        <div class="alert alert-danger" role="alert">Não foi possível editar o funcionário. Confira os dados e tente novamente.</div>
    <?php endif; ?>
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
                                <td style="vertical-align: middle;"><?=htmlspecialchars(formata_telefone($ca['tel']) . (trim((string)$ca['ramal']) !== '' ? ' - Ramal: ' . trim((string)$ca['ramal']) : ''), ENT_QUOTES, 'UTF-8')?></td>
                                <td style="vertical-align: middle;"><?=htmlspecialchars(formata_telefone($ca['cel']), ENT_QUOTES, 'UTF-8')?></td>
                                <td style="vertical-align: middle;"><?=$ca['email']?></td>
                                <td class="funcionario-acoes">
                                    <div class="funcionario-acoes-grupo">
                                        <a
                                            href="/funcionarios_editar?id=<?=$ca['id']?>"
                                            data-editar-funcionario
                                            data-id="<?=(int)$ca['id']?>"
                                            data-nome="<?=$h($ca['nome'])?>"
                                            data-cargo="<?=(int)$ca['cargo']?>"
                                            data-email="<?=$h($ca['email'])?>"
                                            data-telefone="<?=$h(formata_telefone($ca['tel']))?>"
                                            data-ramal="<?=$h($ca['ramal'])?>"
                                            data-celular="<?=$h(formata_telefone($ca['cel']))?>"
                                            class="funcionario-acao funcionario-acao-editar"
                                            title="Editar funcionário"
                                            aria-label="Editar funcionário"
                                        >
                                            <?=iconeInterface('editar', 'acao')?>
                                        </a>
                                        <a
                                            href="funcionarios_cadastrar?id=<?=$ca['id']?>&acao=apagar"
                                            class="funcionario-acao funcionario-acao-apagar"
                                            title="Apagar funcionário"
                                            aria-label="Apagar funcionário"
                                        >
                                            <?=iconeInterface('apagar', 'acao')?>
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
    <!-- Edição do funcionário em modal; sem JavaScript o link abre a página de edição. -->
    <div class="modal fade" id="modalEditarFuncionario" tabindex="-1" role="dialog" aria-labelledby="modalEditarFuncionarioTitulo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <form class="modal-content" id="formEditarFuncionario" method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarFuncionarioTitulo">Editar funcionário</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="retorno" value="modal">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="editar_nome"><span style="color:#a40000">*</span> Nome</label>
                            <input type="text" class="form-control" maxlength="200" id="editar_nome" name="nome" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="editar_cargo"><span style="color:#a40000">*</span> Cargo</label>
                            <select class="form-control" id="editar_cargo" name="cargo" required>
                                <option value="" disabled>Selecione o cargo</option>
                                <?php foreach ($cargos as $cargo): ?>
                                    <option value="<?=(int)$cargo['id']?>"><?=$h($cargo['descricao'])?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="editar_email"><span style="color:#a40000">*</span> E-mail</label>
                            <input type="email" class="form-control" maxlength="250" id="editar_email" name="email" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="editar_celular"><span style="color:#a40000">*</span> Celular</label>
                            <input type="tel" class="form-control" maxlength="15" data-telefone-br id="editar_celular" name="celular" placeholder="(00) 00000-0000" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="editar_telefone">Telefone</label>
                            <input type="tel" class="form-control" maxlength="15" data-telefone-br id="editar_telefone" name="telefone" placeholder="(00) 0000-0000">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="editar_ramal">Ramal</label>
                            <input type="text" class="form-control" maxlength="10" id="editar_ramal" name="ramal">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-az-primary" name="acao" value="funcionario_editar">Salvar</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.querySelectorAll('[data-editar-funcionario]').forEach(function (link) {
        link.addEventListener('click', function (evento) {
            if (typeof $ === 'undefined' || !$.fn.modal) return; // sem Bootstrap, segue o link
            evento.preventDefault();
            var d = link.dataset;
            document.getElementById('formEditarFuncionario').action = '/funcionarios_editar?id=' + encodeURIComponent(d.id);
            document.getElementById('editar_nome').value = d.nome || '';
            document.getElementById('editar_cargo').value = d.cargo || '';
            document.getElementById('editar_email').value = d.email || '';
            document.getElementById('editar_celular').value = d.celular || '';
            document.getElementById('editar_telefone').value = d.telefone || '';
            document.getElementById('editar_ramal').value = d.ramal || '';
            $('#modalEditarFuncionario').modal('show');
        });
    });
    </script>
</div><!-- az-content-body -->

<?php
// O rodapé fecha o layout compartilhado da aplicação.
include 'footer.php'; ?>
