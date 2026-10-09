<?php if (!empty($mensagemEquipamento)): ?>
    <div class="alert alert-<?=($mensagemEquipamento['tipo'] === 'success' ? 'success' : 'danger')?>" role="alert">
        <?=htmlspecialchars($mensagemEquipamento['texto'], ENT_QUOTES, 'UTF-8')?>
    </div>
<?php endif; ?>

<?php if (!$equipamentos): ?>
    <div class="alert alert-info" role="status">Nenhum equipamento encontrado.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table mg-b-0">
            <thead>
                <tr>
                    <?php if ($pagina !== 'Ativos'): ?>
                        <th>Contrato</th>
                    <?php endif; ?>
                    <th>Código</th>
                    <?php if ($pagina === 'Ativos'): ?>
                        <th>Número de série</th>
                    <?php endif; ?>
                    <th>Equipamento</th>
                    <th>Descrição</th>
                    <th>Modelo</th>
                    <th>Funcionário</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipamentos as $equipamento): ?>
                    <?php
                    $funcionario = trim((string)($equipamento['usuario'] ?? ''));
                    if ($funcionario === '') {
                        $funcionario = trim((string)($equipamento['nome_contato'] ?? ''));
                    }
                    ?>
                    <tr>
                        <?php if ($pagina !== 'Ativos'): ?>
                            <td><?=htmlspecialchars((string)$equipamento['id_contrato'], ENT_QUOTES, 'UTF-8')?></td>
                        <?php endif; ?>
                        <td><?=htmlspecialchars((string)$equipamento['codigo'], ENT_QUOTES, 'UTF-8')?></td>
                        <?php if ($pagina === 'Ativos'): ?>
                            <td><?=htmlspecialchars(trim((string)($equipamento['sn'] ?? '')) ?: '-', ENT_QUOTES, 'UTF-8')?></td>
                        <?php endif; ?>
                        <td><?=htmlspecialchars((string)($equipamento['nome_tipo'] ?? ''), ENT_QUOTES, 'UTF-8')?></td>
                        <td><?=htmlspecialchars((string)($equipamento['descricao'] ?? ''), ENT_QUOTES, 'UTF-8')?></td>
                        <td><?=htmlspecialchars((string)($equipamento['modelo'] ?? ''), ENT_QUOTES, 'UTF-8')?></td>
                        <td><?=htmlspecialchars($funcionario, ENT_QUOTES, 'UTF-8')?></td>
                        <?php if ($pagina === 'Ativos'): ?>
                            <td class="funcionario-acoes">
                                <div class="funcionario-acoes-grupo">
                                    <a
                                        href="/equipamentos_editar?id=<?=urlencode((string)$equipamento['id'])?>"
                                        class="funcionario-acao funcionario-acao-editar"
                                        aria-label="Editar equipamento <?=htmlspecialchars((string)$equipamento['codigo'], ENT_QUOTES, 'UTF-8')?>"
                                        title="Editar"
                                    ><?=iconeInterface('editar', 'acao')?></a>
                                    <?php if (!empty($equipamento['desativacao_pendente'])): ?>
                                        <span class="text-muted" title="Aguardando aprovação do gerente">Aguardando aprovação</span>
                                    <?php else: ?>
                                        <form action="/equipamentos_status" method="post" class="funcionario-acao-form" onsubmit="return confirm('Enviar solicitação de desativação ao gerente do contrato? O equipamento permanecerá ativo até a aprovação.');">
                                            <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8')?>">
                                            <input type="hidden" name="id" value="<?=htmlspecialchars((string)$equipamento['id'], ENT_QUOTES, 'UTF-8')?>">
                                            <input type="hidden" name="acao" value="solicitar_desativacao">
                                            <button
                                                type="submit"
                                                class="funcionario-acao funcionario-acao-desativar"
                                                aria-label="Solicitar desativação do equipamento <?=htmlspecialchars((string)$equipamento['codigo'], ENT_QUOTES, 'UTF-8')?>"
                                                title="Solicitar desativação"
                                            ><?=iconeInterface('desativar', 'acao')?></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php else: ?>
                            <td class="funcionario-acoes">
                                <form action="/equipamentos_status" method="post" class="funcionario-acao-form" onsubmit="return confirm('Deseja reativar este equipamento?');">
                                    <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8')?>">
                                    <input type="hidden" name="id" value="<?=htmlspecialchars((string)$equipamento['id'], ENT_QUOTES, 'UTF-8')?>">
                                    <input type="hidden" name="acao" value="reativar">
                                    <button type="submit" class="btn btn-sm btn-az-primary">Reativar</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
