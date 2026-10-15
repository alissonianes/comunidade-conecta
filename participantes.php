
<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/conexao.php";

$mensagem = "";

// Cadastrar participante
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["cadastrar"])) {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $telefone = trim($_POST["telefone"]);

    $sql = "INSERT INTO participante (nome, email, telefone)
            VALUES (?, ?, ?)";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sss", $nome, $email, $telefone);

    if ($stmt->execute()) {
        header("Location: participantes.php?mensagem=cadastrado");
        exit;
    } else {
        $mensagem = "Erro ao cadastrar participante. Verifique se o e-mail já está cadastrado.";
    }

    $stmt->close();
}

// Editar participante
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["editar_id"])) {
    $id = (int) $_POST["editar_id"];
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $telefone = trim($_POST["telefone"]);

    $sql = "UPDATE participante
            SET nome = ?, email = ?, telefone = ?
            WHERE id = ?";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssi", $nome, $email, $telefone, $id);

    if ($stmt->execute()) {
        header("Location: participantes.php?mensagem=editado");
        exit;
    } else {
        $mensagem = "Erro ao atualizar participante.";
    }

    $stmt->close();
}


 // Excluir participante
if (isset($_GET["excluir"])) {
    $id = (int) $_GET["excluir"];

    if ($id > 0 && isset($_GET["confirmar"]) && $_GET["confirmar"] == "sim") {
        $stmt = $conexao->prepare(
            "DELETE FROM participante WHERE id = ?"
        );
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: participantes.php?mensagem=excluido");
            exit;
        } else {
            $mensagem = "Não foi possível excluir o participante. Ele pode possuir inscrições relacionadas.";
        }

        $stmt->close();
    }
}

// Mensagens
if (isset($_GET["mensagem"])) {
    if ($_GET["mensagem"] == "cadastrado") {
        $mensagem = "Participante cadastrado com sucesso!";
    } elseif ($_GET["mensagem"] == "editado") {
        $mensagem = "Participante atualizado com sucesso!";
    } elseif ($_GET["mensagem"] == "excluido") {
        $mensagem = "Participante excluído com sucesso!";
    }
}

// Buscar participante para edição
$participante_edicao = null;

if (isset($_GET["editar"])) {
    $id = (int) $_GET["editar"];

    $stmt = $conexao->prepare("SELECT * FROM participante WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $participante_edicao = $resultado->fetch_assoc();

    $stmt->close();
}

// Listar participantes
$participantes = $conexao->query(
    "SELECT * FROM participante ORDER BY nome ASC"
);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Participantes - Comunidade Conecta</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<h1>Gerenciamento de Participantes</h1>

<p><a href="painel.php">Voltar ao painel</a></p>

<?php if ($mensagem != ""): ?>
    <p><strong><?php echo htmlspecialchars($mensagem); ?></strong></p>
<?php endif; ?>

<?php if ($participante_edicao): ?>

    <h2>Editar participante</h2>

    <form method="POST" action="participantes.php">
        <input type="hidden" name="editar_id"
               value="<?php echo $participante_edicao["id"]; ?>">

        <label>Nome:</label><br>
        <input type="text" name="nome" maxlength="100" required
               value="<?php echo htmlspecialchars($participante_edicao["nome"]); ?>">
        <br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email" maxlength="100" required
               value="<?php echo htmlspecialchars($participante_edicao["email"]); ?>">
        <br><br>

        <label>Telefone:</label><br>
        <input type="text" name="telefone" maxlength="20"
               value="<?php echo htmlspecialchars($participante_edicao["telefone"] ?? ""); ?>">
        <br><br>

        <button type="submit">Salvar alterações</button>
        <a href="participantes.php">Cancelar</a>
    </form>

<?php else: ?>

    <h2>Cadastrar novo participante</h2>

    <form method="POST" action="participantes.php">
        <input type="hidden" name="cadastrar" value="1">

        <label>Nome:</label><br>
        <input type="text" name="nome" maxlength="100" required>
        <br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email" maxlength="100" required>
        <br><br>

        <label>Telefone:</label><br>
        <input type="text" name="telefone" maxlength="20">
        <br><br>

        <button type="submit">Cadastrar participante</button>
    </form>

<?php endif; ?>

<hr>

<h2>Participantes cadastrados</h2>

<?php if ($participantes && $participantes->num_rows > 0): ?>

    <?php while ($participante = $participantes->fetch_assoc()): ?>

        <h3><?php echo htmlspecialchars($participante["nome"]); ?></h3>

        <p>
            <strong>E-mail:</strong>
            <?php echo htmlspecialchars($participante["email"]); ?>
        </p>

        <p>
            <strong>Telefone:</strong>
            <?php echo htmlspecialchars($participante["telefone"] ?? ""); ?>
        </p>

        <p>
            <a href="participantes.php?editar=<?php echo $participante["id"]; ?>">
                Editar
            </a>
            |
          
<a href="participantes.php?excluir=<?php echo $participante["id"]; ?>&confirmar=sim"
   onclick="return confirm('Tem certeza que deseja excluir este participante?');">
    Excluir
</a>
        </p>

        <hr>

    <?php endwhile; ?>

<?php else: ?>

    <p>Nenhum participante cadastrado ainda.</p>

<?php endif; ?>

</body>
</html>