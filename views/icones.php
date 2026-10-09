<?php

function iconeInterface($nome)
{
    $caminhos = array(
        'dashboard' => '<path d="M4 19V5m0 14h16M7 15l3-4 3 2 5-6"/><path d="M15 7h3v3"/>',
        'chamados' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2h6v2M8 9h8M8 13h8M8 17h5"/>',
        'funcionarios' => '<circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6m2 3a5 5 0 0 1 3 4v2"/>',
        'relatorios' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 16v-4m4 4V8m4 8v-6"/>',
        'equipamentos' => '<rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8m-4-4v4"/>',
        'documentacao' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v5h4M9 12h6m-6 4h6"/>',
        'editar' => '<path d="m4 16-.8 4.8L8 20l11-11-4-4z"/><path d="m13.5 6.5 4 4M4 20l4-4"/>',
        'apagar' => '<path d="M4 7h16M10 11v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3"/>',
        'desativar' => '<path d="M12 2v10"/><path d="M6.2 5.8a8 8 0 1 0 11.6 0"/>',
        'usuario' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'detalhes' => '<path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8m-8 4h5"/>',
        'reabrir' => '<path d="M3 7l3-3 3 3"/><path d="M6 4v7a7 7 0 0 0 7 7h5"/><path d="m17 15 3 3-3 3"/>',
        'mais' => '<circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/>',
        'baixar' => '<path d="M12 3v12m-5-5 5 5 5-5"/><path d="M5 17v4h14v-4"/>',
        'configuracoes' => '<path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.7a8 8 0 0 1-1.6.9l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.6-.9l-1.7.7-1.4-2.4 1.4-1.1a7 7 0 0 1 0-1.9l-1.4-1.1 1.4-2.4 1.7.7a8 8 0 0 1 1.6-.9l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.6.9l1.7-.7 1.4 2.4-1.4 1.1a7 7 0 0 1-.1 1.8ZM4 12h1"/>',
        'sair' => '<path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>',
        'voltar' => '<path d="m15 18-6-6 6-6M9 12h12"/>'
    );

    if (!isset($caminhos[$nome])) {
        throw new InvalidArgumentException('Ícone de interface não reconhecido: ' . $nome);
    }

    return '<svg class="icone-interface" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
        . $caminhos[$nome]
        . '</svg>';
}
