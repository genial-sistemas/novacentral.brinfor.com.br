<?php
$menu   = 'Chamados';
$pagina = 'Listar Abertos';
include 'header.php'
?>
<div class="az-content-body">
    <div class="row row-sm">
        <div class="col-md-12">
            <div class="card card-body card-dashboard-fifteen">
                <div class="row mg-b-10">
                    <div class="col-md-4">
                        <b>Chamado</b>: <?=$chamado['id']?><br />
                        <b>Técnico responsavel</b>: <?=$ultima_interacao_tecnico?>
                    </div>
                    <div class="col-md-4">
                        <b>Última interação</b>: <?=$ultima_interacao_data_hora?><br />
                        <b>Status</b>: <?=$chamado['situacao']?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-sm">
        <div class="col-md-12">
            <div id="container-chat">
                <div id="azChatBody" class="az-chat-body chat-chamados">
                    <div class="content-inner">
                    <?php 
                    $data_atual = "";
                    foreach($interacoes as $i):
                        if($data_atual!=$i['data']):?>
                            <label class="az-chat-time"><span><?=data_brasil($i['data'])?></span></label>
                        <?php 
                        $data_atual = $i['data'];
                        endif;?>
                            <div class="media<?=$i['id_pessoa']==0?' flex-row-reverse':''?>">
                                <div class="az-img-user online"><img src="https://via.placeholder.com/500x500" alt="">
                                <?= $i['id_pessoa']==0?'Você':explode(" ", $i['nome_pessoa'])[0];?>
                                </div>
                            
                                <div class="media-body">
                                    <div class="az-msg-wrapper">
                                        <?=stripslashes($i['descricao'])?>
                                    </div><!-- az-msg-wrapper -->
                                    <div><span><?=$i['hora']?></span> <a href=""><i class="icon ion-android-more-horizontal"></i></a></div>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        <?php
                    endforeach; ?>
                    </div><!-- content-inner -->
                    <?php if (isset($erro_interacao)): ?>
                        <div class="alert alert-danger" role="alert"><?=htmlspecialchars($erro_interacao, ENT_QUOTES, 'UTF-8')?></div>
                    <?php endif; ?>
                    <?php if ((int)$chamado['id_situacao'] !== 7): ?>
                        <form action="/chamados_interagir?id=<?=$chamado['id']?>" method="post" class="mt-3">
                            <input type="hidden" name="chamado_id" value="<?=$chamado['id']?>">
                            <input type="hidden" name="id_equipamento" value="<?=$ultima_interacao_equipamento?>">
                            <div class="form-group">
                                <label for="nova-msg">Nova interação</label>
                                <textarea class="form-control" id="nova-msg" name="nova-msg" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-az-primary">Enviar interação</button>
                        </form>
                    <?php endif; ?>
                </div><!-- az-chat-body -->
            </div>
        </div>
    </div>
</div><!-- az-content-body -->
<?php
include 'footer.php'; ?>