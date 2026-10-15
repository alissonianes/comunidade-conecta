<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Painel - Comunidade Conecta</title>
    <link rel="stylesheet" href="estilo.css">
</head>

<body>

    <h1>Comunidade Conecta</h1>

    <h2>Olá, <?php echo $_SESSION["usuario_nome"]; ?>!</h2>

    <p>Você entrou no sistema com sucesso.</p>

    <hr>

 
<h3>Menu principal</h3>

<div class="menu-cartoes">

    <a href="eventos.php" class="cartao-menu">
        <h3>Eventos</h3>
        <p>Cadastrar e gerenciar eventos</p>
    </a>

    <a href="participantes.php" class="cartao-menu">
        <h3>Participantes</h3>
        <p>Cadastrar e gerenciar participantes</p>
    </a>

    <a href="categorias.php" class="cartao-menu">
        <h3>Categorias</h3>
        <p>Consultar categorias de eventos</p>
    </a>

</div>

<p class="sair">
    <a href="logout.php">Sair do sistema</a>
</p>