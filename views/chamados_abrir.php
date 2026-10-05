<?php
include 'header.php' 
?>
<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Abrir Chamado</h2>
    </div>
</div><!-- az-content-header -->
<div class="az-content-body">
    <form method="post" id="formChamado" class="validated">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tipo de Contrato</label>
                    <select class="form-control" name="id_tipo_contrato" id="id_tipo_contrato">
                        <option value="0">Selecione:</option>
                        <?php foreach($tipos_contrato_visitante as $id_tipo => $tipo_descricao): ?>
                            <?php if (in_array($id_tipo, $tipos_contrato_habilitados, true)): ?>
                                <option value="<?=$id_tipo?>" <?php if($id_tipo==$frm_tipo_contrato){ echo "selected"; } ?>><?=htmlspecialchars($tipo_descricao, ENT_QUOTES, 'UTF-8')?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div id="helpdesk" style="display: none;" class="col-md-6">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Código do Equipamento</label>
                            <select class="form-control" name="codigo_equipamento" id="codigo_equipamento">
                                <option value="0">Selecione:</option>
                                <?php foreach($contratos_outsourcing_equipamentos as $co): ?>
                                    <?php 
                                        $codigo = str_pad($co['codigo'], 4, "0", STR_PAD_LEFT); 
                                        $contato_info = obterNomeContato($co['contato']);  
                                        $contato_nome = $contato_info["nome"];
                                        $contato_email = $contato_info["email"];
                                        $contato_telefone = $contato_info["cel"];
                                        $numero_serie = trim((string)($co['sn'] ?? ''));
                                    ?>
                                    <option value="<?=htmlspecialchars((string)$co['id'], ENT_QUOTES, 'UTF-8')?>" data-codigo="<?=htmlspecialchars((string)$codigo, ENT_QUOTES, 'UTF-8')?>" data-nome="<?=htmlspecialchars((string)$contato_nome, ENT_QUOTES, 'UTF-8')?>" data-email="<?=htmlspecialchars((string)$contato_email, ENT_QUOTES, 'UTF-8')?>" data-telefone="<?=htmlspecialchars((string)$contato_telefone, ENT_QUOTES, 'UTF-8')?>"<?php if($co['id']==$frm_codigo_equipamento){ echo "selected"; } ?>><?=htmlspecialchars((string)$codigo, ENT_QUOTES, 'UTF-8')?> - <?=htmlspecialchars((string)$co['descricao'], ENT_QUOTES, 'UTF-8')?> - <?=htmlspecialchars((string)validaNomeContato($contato_nome), ENT_QUOTES, 'UTF-8')?><?php if ($numero_serie !== ''): ?> -- S/N: <?=htmlspecialchars($numero_serie, ENT_QUOTES, 'UTF-8')?><?php endif; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small id="aviso-contato-equipamento" class="form-text text-muted" role="status" hidden>Confira os dados de contato; preencha manualmente os campos que estiverem vazios.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="box-brinfor" id="contrato_1">
                                <div class="box-brinfor-esquerda">
                                    <div class="box-brinfor-esquerda-input">
                                        <input type="text" id="inputCodigoContrato" name="etiqueta_codigo_contrato" class="form-group input-brinfor-esquerda" value="<?=$etiqueta ?? NULL ?>" readonly="">
                                    </div>
                                    <div class="box-brinfor-esquerda-label">
                                        CONTRATO
                                    </div>
                                </div>
                                <div class="box-brinfor-direita">
                                    <div class="box-brinfor-direita-imagem">
                                        <img src="/views/img/brInfor_logo_mini.png" class="img-fluid imagem-brinfor-direita" alt="Br Info logo">
                                    </div>
                                    <div class="box-brinfor-direita-input">
                                        <input type="text" name="etiqueta_codigo_equipamento" id="etiqueta_codigo_equipamento" value="<?=$frm_etiqueta_codigo_equipamento ?>" class="form-group input-brinfor-direita" readonly="">
                                    </div>
                                    <div class="box-brinfor-direita-label">
                                        EQUIPAMENTO
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="contrato-referencia" style="display: none;" class="col-md-6">
                <div class="form-group">
                    <label for="contrato_id">Contrato ativo</label>
                    <select class="form-control" name="contrato_id" id="contrato_id" disabled>
                        <option value="0">Selecione:</option>
                        <?php foreach($contratosAtivos as $contrato): ?>
                            <?php if(array_key_exists((int)$contrato['id_tipo'], $tipos_contrato_visitante)): ?>
                                <option value="<?=$contrato['id']?>" data-tipo="<?=$contrato['id_tipo']?>" <?php if($contrato['id']==$frm_contrato_id){ echo 'selected'; } ?>><?=$contrato['id']?> - <?=htmlspecialchars($contrato['tipo_contrato'], ENT_QUOTES, 'UTF-8')?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite o seu nome" value="">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="Digite o e-mail" value="">
                </div>
            </div>     
            <div class="col-md-4">
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" placeholder="Digite o seu telefone" value="">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tipo de Solicitação</label>
                    <select class="form-control" name="tipo_solicitacao">
                        <option value="0">Selecione:</option>
                    <?php foreach($tipos_solicitacao as $ts): ?>
                        <option value="<?=$ts['id']?>" <?php if($ts['id']==$frm_tipo_solicitacao){ echo "selected"; } ?>><?=$ts['tipo']?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Urgência</label>
                    <select class="form-control" name="urgencia">
                         <option value="4">Padrão</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Descrição da sua solicitação:</label>
                    <textarea class="form-control" rows="6" name="descricao_solicitacao" placeholder="Descreva sua solicitação ..."><?=$frm_descricao_solicitacao ?></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Anexo:</label>
                    <input type="file" name="arquivo">
                </div>
            </div>
        </div>        
        <div class="row">
            <div class="col-md-12">
                <button type="submit" id="btn-submit" class="btn btn-az-primary pd-x-20" name="acao" value="abrir_chamado">Abrir Chamado</button>
            </div>
        </div>
    </form>
<?php if (isset($_POST['acao']) && $_POST['acao'] === 'abrir_chamado'): ?>
<?= $mensagem ?>
<?php endif; ?>
</div><!-- az-content-body -->
<script>
</script>
<?php
    include 'footer.php';
?>