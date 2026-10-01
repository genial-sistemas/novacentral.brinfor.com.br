$(document).ready(function() {
$("#modalTrocarSenha form").on("submit", function(e) {
    const novaSenha = $("#novaSenha").val();
    const confirmarSenha = $("#confirmarSenha").val();

    if (novaSenha !== confirmarSenha) {
    e.preventDefault(); // impede envio
    alert("As senhas não coincidem. Por favor, verifique.");
    return false;
    }

    if (novaSenha.length < 6) {
    e.preventDefault();
    alert("A nova senha deve ter pelo menos 6 caracteres.");
    return false;
    }
});
});