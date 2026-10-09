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
                    <th>Contrato</th>
                    <th>Código</th>
                    <th>Equipamento</th>
                    <th>Descrição</th>
                    <th>Modelo</th>
                    <th>Funcionário</th>
                    <?php if ($pagina === 'Ativos'): ?>
                        <th class="text-center">Ações</th>
                    <?php endif; ?>
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
                        <td><?=htmlspecialchars((string)$equipamento['id_contrato'], ENT_QUOTES, 'UTF-8')?></td>
                        <td><?=htmlspecialchars((string)$equipamento['codigo'], ENT_QUOTES, 'UTF-8')?></td>
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
                                ><?=iconeInterface('editar')?></a>
                                <form action="/equipamentos_status" method="post" class="funcionario-acao-form" onsubmit="return confirm('Deseja desativar este equipamento?');">
                                    <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8')?>">
                                    <input type="hidden" name="id" value="<?=htmlspecialchars((string)$equipamento['id'], ENT_QUOTES, 'UTF-8')?>">
                                    <button
                                        type="submit"
                                        class="funcionario-acao funcionario-acao-desativar"
                                        aria-label="Desativar equipamento <?=htmlspecialchars((string)$equipamento['codigo'], ENT_QUOTES, 'UTF-8')?>"
                                        title="Desativar"
                                    ><?=iconeInterface('desativar')?></button>
                                </form>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
