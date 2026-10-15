<?php

session_start();

require_once "config/conexao.php";

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $sql = "SELECT * FROM usuario WHERE email = ? AND senha = ?";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $usuario = $resultado->fetch_assoc();

        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome"];

        header("Location: painel.php");
        exit;

    } else {

        $mensagem = "E-mail ou senha incorretos!";

    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <title>Login - Comunidade Conecta</title>

</head>

<body>

    <h1>Comunidade Conecta</h1>

    <h2>Login</h2>

    <?php

    if ($mensagem != "") {
        echo "<p>$mensagem</p>";
    }

    ?>

    <form action="login.php" method="POST">

        <label>E-mail:</label>

        <input type="email" name="email" required>

        <br><br>

        <label>Senha:</label>

        <input type="password" name="senha" required>

        <br><br>

        <button type="submit">Entrar</button>

    </form>

</body>

</html>