<?php
require_once __DIR__ . '/erp_api.php';

function obterNome($id){
    return erp_api('pessoa.nome', ['id' => (int)$id]);
}
