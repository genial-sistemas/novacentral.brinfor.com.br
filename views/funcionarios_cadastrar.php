<?php
// O cabeçalho compartilha o layout principal, menu lateral e estilos da aplicação.
include 'header.php' ?>

<!-- O rótulo muda entre os modos de cadastro, edição e confirmação de exclusão. -->
<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8"><?=$acaoLabel?> Funcionário</h2>
    </div>
</div><!-- az-content-header -->
<div class="az-content-body">
<!-- Sem uma ação processada, apresenta o formulário para cadastrar ou editar. -->
<?php if(!isset($_POST['acao'])): ?>
    <form action="" method="post">
        <div class="row">
            <!-- Dados básicos e obrigatórios do funcionário. -->
            <div class="col-md-4">
                <div class="form-group">
                    <label for="nome"><label style="color: #a40000; margin-bottom: 0px;">*</label> Nome</label>
                    <input
                        type="text"
                        class="form-control"
                        max="200"
                        value="<?=($funcionario !== null ? $funcionario['nome'] : '')?>"
                        id="nome"
                        name="nome"
                        placeholder="Digite o seu nome"
                    required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="celular"><label style="color: #a40000; margin-bottom: 0px;">*</label> Celular</label>
                    <input type="text" class="form-control" max="12" value="<?=($funcionario !== null ? $funcionario['cel'] : '')?>" id="celular" name="celular" placeholder="Digite o seu celular" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="cargo"><label style="color: #a40000; margin-bottom: 0px;">*</label> Cargo</label>
                    <select class="form-control" name="cargo" id="cargo" required>
                        <option value="" disabled>Selecione o cargo</option>
                        <!-- As opções vêm dos cargos ativos carregados pelo controlador. -->
                        <?php foreach ($cargos as $cargo) : ?>
                            <option value="<?=$cargo['id']?>" <?=($funcionario !== null && $funcionario['cargo'] == (string) $cargo['id'] ? 'selected=selected' : '')?>><?=$cargo['descricao']?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <!-- Telefone fixo e ramal são opcionais. -->
            <div class="col-md-4">
                <div class="form-group">
                    <label for="email"><label style="color: #a40000; margin-bottom: 0px;">*</label> E-mail</label>
                    <input type="email" class="form-control" max="250" value="<?=($funcionario !== null ? $funcionario['email'] : '')?>" id="email" name="email" placeholder="Digite o seu e-mail" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" class="form-control" max="12" value="<?=($funcionario !== null ? $funcionario['tel'] : '')?>" id="telefone" name="telefone" placeholder="Digite o seu telefone">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="ramal">Ramal</label>
                    <input type="text" class="form-control" max="10" value="<?=($funcionario !== null ? $funcionario['ramal'] : '')?>" id="ramal" name="ramal" placeholder="Digite o seu ramal">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <!-- O valor enviado identifica no controlador a ação a executar. -->
                <button class="btn btn-az-primary pd-x-20" name="acao" value="<?=$acao?>"><?=$acaoLabel?></button>
            </div>
        </div>
    </form>
    <!-- Após uma ação bem-sucedida, mostra a confirmação e o destino do botão de retorno. -->
    <?php elseif(isset($_POST['acao']) && ($_POST['acao']=="funcionario_cadastrar" || $_POST['acao']=="funcionario_editar" || $_POST['acao']=="funcionario_apagar")): ?>
    <div class="row">
        <div class="col-md-12">
            <p>Funcionário <?=strtolower($acaoLabelSucesso)?> com sucesso!!</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <button class="btn btn-az-primary pd-x-20" onclick="window.location.href = '<?=$acaoRedirecionar?>'">Ok</button>
        </div>
    </div>
<?php endif; ?>
</div><!-- az-content-body -->
<?php
// O rodapé fecha o layout compartilhado da aplicação.
include 'footer.php'; ?>
