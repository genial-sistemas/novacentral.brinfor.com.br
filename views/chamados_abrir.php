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
                        <?php foreach($contratosAtivos as $c): ?>
                            <option value="<?=$c['id_tipo']?>"  <?php if($c['id_tipo']==$frm_tipo_contrato){ echo "selected"; } ?>><?=$c['tipo_contrato'] ?></option>
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
                                    ?>
                                    <option value="<?=$co['id']?>" data-codigo="<?=$codigo?>" data-nome="<?=$contato_nome?>" data-email="<?=$contato_email?>" data-telefone="<?=$contato_telefone?>"<?php if($co['id']==$frm_codigo_equipamento){ echo "selected"; } ?>><?=$codigo?> - <?=$co['descricao']?> - <?php echo validaNomeContato($contato_nome); ?></option>
                                <?php endforeach; ?>
                            </select>
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
            <div id="hospedagem" style="display: none;">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Domínio</label>
                            <select class="form-control" name="dominio">
                                <option value="0">Selecione:</option>
                            <?php foreach($contratos_hospedagem as $ch): ?>
                                <option value="<?=$ch['id']?>"><?=$ch['dominio']?></option>
                            <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div id="backup" style="display: none;">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Plano</label>
                            <select class="form-control" name="backup_plano">
                                <option value="0">Selecione:</option>
                            <?php foreach($contratos_backup as $cb): ?>
                                <option value="<?=$cb['id']?>"><?=$cb['plano']?></option>
                            <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div id="bhclouderp" style="display: none;">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Plano</label>
                            <select class="form-control" name="bhclouderp_plano">
                                <option value="0">Selecione:</option>
                            </select>
                        </div>
                    </div>
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