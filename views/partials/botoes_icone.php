<?php // Estilo dos botões de ícone das listas de chamados (abertos e fechados). ?>
<style>
.chamados-acoes { display: flex; gap: 6px; align-items: center; }
.chamados-acoes form { margin: 0; }
.btn-icone { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; padding: 0; border-radius: 6px; background: #fff; border: 1px solid #cdd4e0; }
.btn-icone svg { width: 18px; height: 18px; }
.btn-interagir { color: #fa9758; }
.btn-interagir:hover { background: #fa9758; border-color: #fa9758; color: #fff; }
.btn-encerrar { color: #3bb001; }
.btn-encerrar:hover { background: #3bb001; border-color: #3bb001; color: #fff; }
.btn-reabrir { color: #0168fa; }
.btn-reabrir:hover { background: #0168fa; border-color: #0168fa; color: #fff; }
.chamados-detalhes { color: #596882; font-size: 12px; }
/* Urgência: 1 Crítica, 2 Alta, 3 Baixa, 4 Padrão (tabela contrato_chamado_tipo_urgencia) */
.urgencia { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; white-space: nowrap; background: #eef0f4; color: #596882; }
.urgencia-1 { background: #fdecea; color: #c62828; }
.urgencia-2 { background: #fff4ec; color: #d9661f; }
</style>
