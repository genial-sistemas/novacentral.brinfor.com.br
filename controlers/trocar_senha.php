<?php
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senhaAtual = $_POST['senha_atual'];
    $novaSenha = $_POST['nova_senha'];
    $confirmarSenha = $_POST['confirmar_senha'];
    $idCliente = $_SESSION['id_cliente'];

    if ($novaSenha !== $confirmarSenha) {
        die("As senhas não coincidem.");
    }

    // Verifica senha atual
    $sql = "SELECT senha FROM contrato_login  WHERE id_pessoa = $idCliente";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idCliente);
    $stmt->execute();
    $stmt->fetch();
    $stmt->close();

    if ($senhaAtual!=$senha) {
        die("Senha atual incorreta.");
    }

    // Atualiza nova senha
    $sql = "UPDATE contrato_login SET senha = $novasenha WHERE id_pessoa = $idCliente";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $novaSenhaHash, $idCliente);
    $stmt->execute();
    $stmt->close();

    echo "Senha alterada com sucesso!";
}else{
    echo "Error!";
}
*/
?>