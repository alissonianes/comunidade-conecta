
<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/conexao.php";

$mensagem = "";

// Cadastrar categoria
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["cadastrar"])) {
    $nome = trim($_POST["nome"]);

    if ($nome != "") {
        $stmt = $conexao->prepare(
            "INSERT INTO categoria (nome) VALUES (?)"
        );
        $stmt->bind_param("s", $nome);

        if ($stmt->execute()) {
            header("Location: categorias.php?mensagem=cadastrada");
            exit;
        } else {
            $mensagem = "Não foi possível cadastrar a categoria.";
        }

        $stmt->close();
    } else {
        $mensagem = "Digite o nome da categoria.";
    }
}

// Excluir categoria
if (isset($_GET["excluir"])) {
    $id = (int) $_GET["excluir"];

    if ($id > 0) {
        $stmt = $conexao->prepare(
            "DELETE FROM categoria WHERE id = ?"
        );
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $stmt->close();
            header("Location: categorias.php?mensagem=excluida");
            exit;
        } else {
            $mensagem = "Não foi possível excluir. A categoria pode estar sendo usada por um evento.";
        }

        $stmt->close();
    }
}

// Mensagens
if (isset($_GET["mensagem"])) {
    if ($_GET["mensagem"] == "cadastrada") {
        $mensagem = "Categoria cadastrada com sucesso!";
    } elseif ($_GET["mensagem"] == "excluida") {
        $mensagem = "Categoria excluída com sucesso!";
    }
}

// Buscar categorias
$resultado = $conexao->query(
    "SELECT * FROM categoria ORDER BY nome"
);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Categorias - Comunidade Conecta</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

    <h1>Gerenciamento de Categorias</h1>

    <p><a href="painel.php">Voltar ao painel</a></p>

    <?php if ($mensagem != ""): ?>
        <p class="sucesso">
            <?php echo htmlspecialchars($mensagem); ?>
        </p>
    <?php endif; ?>

    <h2>Cadastrar nova categoria</h2>

    <form method="POST" action="categorias.php">
        <label for="nome">Nome da categoria:</label>
        <input type="text" id="nome" name="nome" required>

        <button type="submit" name="cadastrar">
            Cadastrar categoria
        </button>
    </form>

    <h2>Categorias cadastradas</h2>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <?php while ($categoria = $resultado->fetch_assoc()): ?>
            <hr>

            <h3>
                <?php echo htmlspecialchars($categoria["nome"]); ?>
            </h3>

            <a
                href="categorias.php?excluir=<?php echo (int) $categoria["id"]; ?>"
                onclick="return confirm('Tem certeza que deseja excluir esta categoria?');"
            >Excluir</a>

        <?php endwhile; ?>
    <?php else: ?>
        <p>Nenhuma categoria cadastrada.</p>
    <?php endif; ?>

</body>
</html>