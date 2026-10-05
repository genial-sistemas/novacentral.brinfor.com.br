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
                        <b>Chamado</b>: <?=htmlspecialchars((string)$chamado['id'], ENT_QUOTES, 'UTF-8')?> (<?=htmlspecialchars((string)$chamado['tipo_contrato'], ENT_QUOTES, 'UTF-8')?>)<br />
                        <b>Técnico responsável</b>: <span id="tecnico-responsavel"><?=htmlspecialchars((string)$ultima_interacao_tecnico, ENT_QUOTES, 'UTF-8')?></span>
                    </div>
                    <div class="col-md-4">
                        <b>Última interação</b>: <span id="ultima-interacao-topo"><?=htmlspecialchars($ultima_interacao_data_hora, ENT_QUOTES, 'UTF-8')?></span><br />
                        <b>Status</b>: <span id="status-chamado-topo"><?=htmlspecialchars((string)$chamado['situacao'], ENT_QUOTES, 'UTF-8')?></span>
                    </div>
                    <div class="col-md-4">
                        <b>Data abertura</b>: <?=htmlspecialchars($data_abertura, ENT_QUOTES, 'UTF-8')?><br />
                        <b>Equipamento</b>: <span id="equipamento-chamado-topo"><?=htmlspecialchars($equipamento_rotulo, ENT_QUOTES, 'UTF-8')?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-sm">
        <div class="col-md-12">
            <div id="container-chat">
                <div id="azChatBody" class="az-chat-body chat-chamados">
                    <div class="content-inner" id="lista-interacoes">
                    <?php 
                    $data_atual = "";
                    foreach($interacoes as $i):
                        if($data_atual!=$i['data']):?>
                            <label class="az-chat-time" data-data="<?=htmlspecialchars((string)$i['data'], ENT_QUOTES, 'UTF-8')?>"><span><?=data_brasil($i['data'])?></span></label>
                        <?php 
                        $data_atual = $i['data'];
                        endif;
                        $evento_cliente = (int)$i['id_pessoa'] === 0;
                        $nome_interacao = $evento_cliente ? 'Você' : (explode(' ', trim((string)($i['nome_pessoa'] ?? '')))[0] ?: 'Técnico');
                        ?>
                            <div class="media <?=$evento_cliente ? 'flex-row-reverse message-client' : 'message-technician'?>" data-id-evento="<?=htmlspecialchars((string)$i['id_evento'], ENT_QUOTES, 'UTF-8')?>">
                                <div class="az-img-user online"><img src="/views/img/icon_user.png" alt="Usuário">
                                <?=htmlspecialchars($nome_interacao, ENT_QUOTES, 'UTF-8')?>
                                </div>
                            
                                <div class="media-body">
                                    <div class="az-msg-wrapper">
                                        <?=htmlspecialchars(stripslashes($i['descricao']), ENT_QUOTES, 'UTF-8')?>
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
                        <form id="form-nova-interacao" action="/chamados_interagir?id=<?=$chamado['id']?>" method="post" class="mt-3">
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
<script>
(() => {
    const lista = document.getElementById('lista-interacoes');
    const campoMensagem = document.getElementById('nova-msg');
    const container = document.getElementById('container-chat');
    const endpoint = new URL(window.location.href);
    endpoint.searchParams.set('atualizar', '1');
    let atualizando = false;

    const formatarData = (data) => {
        const partes = String(data || '').slice(0, 10).split('-');
        return partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : '';
    };

    const adicionarEvento = (evento, datasExistentes) => {
        const data = String(evento.data || '').slice(0, 10);
        if (data && !datasExistentes.has(data)) {
            const separador = document.createElement('label');
            separador.className = 'az-chat-time';
            separador.dataset.data = data;
            const textoData = document.createElement('span');
            textoData.textContent = formatarData(data);
            separador.appendChild(textoData);
            lista.appendChild(separador);
            datasExistentes.add(data);
        }

        const cliente = Number(evento.id_pessoa) === 0;
        const nomeCompleto = cliente ? 'Você' : (evento.nome_pessoa || 'Técnico');
        const mensagem = document.createElement('div');
        mensagem.className = `media ${cliente ? 'flex-row-reverse message-client' : 'message-technician'}`;
        mensagem.dataset.idEvento = String(evento.id_evento);

        const avatar = document.createElement('div');
        avatar.className = 'az-img-user online';
        const imagem = document.createElement('img');
        imagem.src = '/views/img/icon_user.png';
        imagem.alt = 'Usuário';
        avatar.appendChild(imagem);
        avatar.appendChild(document.createTextNode(cliente ? 'Você' : nomeCompleto.trim().split(/\s+/)[0]));

        const corpo = document.createElement('div');
        corpo.className = 'media-body';
        const balao = document.createElement('div');
        balao.className = 'az-msg-wrapper';
        balao.textContent = evento.descricao || '';
        const meta = document.createElement('div');
        const hora = document.createElement('span');
        hora.textContent = evento.hora || '';
        meta.appendChild(hora);
        corpo.append(balao, meta);
        mensagem.append(avatar, corpo);
        lista.appendChild(mensagem);
    };

    const atualizarConversa = async () => {
        if (atualizando || document.hidden || (campoMensagem && (document.activeElement === campoMensagem || campoMensagem.value.trim() !== ''))) {
            return;
        }

        atualizando = true;
        try {
            const resposta = await fetch(endpoint, {cache: 'no-store', headers: {'Accept': 'application/json'}});
            if (!resposta.ok) return;
            const dados = await resposta.json();

            document.getElementById('tecnico-responsavel').textContent = dados.tecnico || 'Não informado';
            document.getElementById('ultima-interacao-topo').textContent = dados.ultima_interacao || '';
            document.getElementById('status-chamado-topo').textContent = dados.situacao || '';
            document.getElementById('equipamento-chamado-topo').textContent = dados.equipamento || 'Não informado';

            const idsExistentes = new Set([...lista.querySelectorAll('[data-id-evento]')].map((evento) => evento.dataset.idEvento));
            const datasExistentes = new Set([...lista.querySelectorAll('.az-chat-time')].map((separador) => separador.dataset.data));
            let adicionouEvento = false;
            for (const evento of dados.eventos || []) {
                const idEvento = String(evento.id_evento);
                if (idsExistentes.has(idEvento)) continue;
                adicionarEvento(evento, datasExistentes);
                idsExistentes.add(idEvento);
                adicionouEvento = true;
            }

            const formulario = document.getElementById('form-nova-interacao');
            if (formulario && Number(dados.id_situacao) === 7) formulario.hidden = true;
            if (adicionouEvento) container.scrollTop = container.scrollHeight;
        } catch (erro) {
            // Eu tento novamente no próximo ciclo se a consulta temporária falhar.
        } finally {
            atualizando = false;
        }
    };

    window.setInterval(atualizarConversa, 8000);
})();
</script>
<?php
include 'footer.php'; ?>