<?php include __DIR__ . '/header.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Equipamentos de Locação</h2>
        <p class="mg-b-0">Equipamentos vinculados aos seus contratos de Locação ativos.</p>
    </div>
</div>

<div class="az-content-body">
    <?php if (!$equipamentos): ?>
        <div class="alert alert-info" role="status">Nenhum equipamento locado encontrado.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table mg-b-0">
                <thead>
                    <tr>
                        <th>Contrato</th>
                        <th>Patrimônio</th>
                        <th>Equipamento</th>
                        <th>Status</th>
                        <th>Funcionário</th>
                        <th>Email</th>
                        <th>Telefone</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipamentos as $equipamento): ?>
                        <tr>
                            <td><?=htmlspecialchars((string)$equipamento['id_contrato'], ENT_QUOTES, 'UTF-8')?></td>
                            <td><?=htmlspecialchars((string)($equipamento['patrimonio'] ?? ''), ENT_QUOTES, 'UTF-8')?></td>
                            <td><?=htmlspecialchars((string)($equipamento['descricao'] ?? ''), ENT_QUOTES, 'UTF-8')?></td>
                            <td><?=((int)$equipamento['status'] === 1 ? 'Ativo' : 'Inativo')?></td>
                            <td><?=htmlspecialchars((string)($equipamento['nome_contato'] ?? ''), ENT_QUOTES, 'UTF-8')?></td>
                            <td><?=htmlspecialchars((string)($equipamento['email'] ?? ''), ENT_QUOTES, 'UTF-8')?></td>
                            <td><?=htmlspecialchars((string)($equipamento['telefone'] ?? ''), ENT_QUOTES, 'UTF-8')?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
