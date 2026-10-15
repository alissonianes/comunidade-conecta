
<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once "config/conexao.php";

$mensagem = "";

// Excluir evento
if (isset($_GET["excluir"])) {
    $id = (int) $_GET["excluir"];

    if (isset($_GET["confirmar"]) && $_GET["confirmar"] == "sim") {
        $stmt = $conexao->prepare("DELETE FROM evento WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: eventos.php?mensagem=excluido");
            exit;
        } else {
            $mensagem = "Não foi possível excluir o evento.";
        }

        $stmt->close();
    }
}

// Salvar edição
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["editar_id"])) {
    $id = (int) $_POST["editar_id"];
    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $data_evento = $_POST["data_evento"];
    $local = trim($_POST["local"]);
    $categoria_id = (int) $_POST["categoria_id"];

    $sql = "UPDATE evento
            SET titulo = ?, descricao = ?, data_evento = ?,
                local = ?, categoria_id = ?
            WHERE id = ?";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param(
        "ssssii",
        $titulo,
        $descricao,
        $data_evento,
        $local,
        $categoria_id,
        $id
    );

    if ($stmt->execute()) {
        header("Location: eventos.php?mensagem=editado");
        exit;
    } else {
        $mensagem = "Erro ao editar o evento.";
    }

    $stmt->close();
}

// Cadastrar evento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["cadastrar"])) {
    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $data_evento = $_POST["data_evento"];
    $local = trim($_POST["local"]);
    $usuario_id = (int) $_SESSION["usuario_id"];
    $categoria_id = (int) $_POST["categoria_id"];

    $sql = "INSERT INTO evento
            (titulo, descricao, data_evento, local, usuario_id, categoria_id)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param(
        "ssssii",
        $titulo,
        $descricao,
        $data_evento,
        $local,
        $usuario_id,
        $categoria_id
    );

    if ($stmt->execute()) {
        header("Location: eventos.php?mensagem=cadastrado");
        exit;
    } else {
        $mensagem = "Erro ao cadastrar o evento.";
    }

    $stmt->close();
}

// Mensagens
if (isset($_GET["mensagem"])) {
    if ($_GET["mensagem"] == "cadastrado") {
        $mensagem = "Evento cadastrado com sucesso!";
    } elseif ($_GET["mensagem"] == "editado") {
        $mensagem = "Evento atualizado com sucesso!";
    } elseif ($_GET["mensagem"] == "excluido") {
        $mensagem = "Evento excluído com sucesso!";
    }
}

// Buscar categorias
$categorias = $conexao->query("SELECT * FROM categoria");

// Buscar evento que será editado
$evento_edicao = null;

if (isset($_GET["editar"])) {
    $id_edicao = (int) $_GET["editar"];
    $stmt = $conexao->prepare("SELECT * FROM evento WHERE id = ?");
    $stmt->bind_param("i", $id_edicao);
    $stmt->execute();
    $resultado_edicao = $stmt->get_result();
    $evento_edicao = $resultado_edicao->fetch_assoc();
    $stmt->close();
}

// Buscar eventos
$sql_eventos = "SELECT evento.*, categoria.nome AS nome_categoria
                FROM evento
                INNER JOIN categoria ON evento.categoria_id = categoria.id
                ORDER BY evento.data_evento ASC";

$eventos = $conexao->query($sql_eventos);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Eventos - Comunidade Conecta</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<h1>Gerenciamento de Eventos</h1>

<p><a href="painel.php">Voltar ao painel</a></p>

<?php if ($mensagem != ""): ?>
    <p><strong><?php echo htmlspecialchars($mensagem); ?></strong></p>
<?php endif; ?>

<?php if ($evento_edicao): ?>

    <h2>Editar evento</h2>

    <form method="POST" action="eventos.php">
        <input type="hidden" name="editar_id"
               value="<?php echo $evento_edicao["id"]; ?>">

        <label>Título:</label><br>
        <input type="text" name="titulo" maxlength="100" required
               value="<?php echo htmlspecialchars($evento_edicao["titulo"]); ?>">
        <br><br>

        <label>Descrição:</label><br>
        <textarea name="descricao" required><?php
            echo htmlspecialchars($evento_edicao["descricao"]);
        ?></textarea>
        <br><br>

        <label>Data:</label><br>
        <input type="date" name="data_evento" required
               value="<?php echo $evento_edicao["data_evento"]; ?>">
        <br><br>

        <label>Local:</label><br>
        <input type="text" name="local" maxlength="100" required
               value="<?php echo htmlspecialchars($evento_edicao["local"]); ?>">
        <br><br>

        <label>Categoria:</label><br>
        <select name="categoria_id" required>
            <?php while ($categoria = $categorias->fetch_assoc()): ?>
                <option value="<?php echo $categoria["id"]; ?>"
                    <?php
                    if ($categoria["id"] == $evento_edicao["categoria_id"]) {
                        echo "selected";
                    }
                    ?>>
                    <?php echo htmlspecialchars($categoria["nome"]); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>

        <button type="submit">Salvar alterações</button>
        <a href="eventos.php">Cancelar</a>
    </form>

<?php else: ?>

    <h2>Cadastrar novo evento</h2>

    <form method="POST" action="eventos.php">
        <input type="hidden" name="cadastrar" value="1">

        <label>Título:</label><br>
        <input type="text" name="titulo" maxlength="100" required>
        <br><br>

        <label>Descrição:</label><br>
        <textarea name="descricao" required></textarea>
        <br><br>

        <label>Data do evento:</label><br>
        <input type="date" name="data_evento" required>
        <br><br>

        <label>Local:</label><br>
        <input type="text" name="local" maxlength="100" required>
        <br><br>

        <label>Categoria:</label><br>
        <select name="categoria_id" required>
            <option value="">Selecione uma categoria</option>
            <?php while ($categoria = $categorias->fetch_assoc()): ?>
                <option value="<?php echo $categoria["id"]; ?>">
                    <?php echo htmlspecialchars($categoria["nome"]); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>

        <button type="submit">Cadastrar evento</button>
    </form>

<?php endif; ?>

<hr>

<h2>Eventos cadastrados</h2>

<?php if ($eventos && $eventos->num_rows > 0): ?>

    <?php while ($evento = $eventos->fetch_assoc()): ?>

        <h3><?php echo htmlspecialchars($evento["titulo"]); ?></h3>

        <p><strong>Descrição:</strong>
            <?php echo htmlspecialchars($evento["descricao"]); ?>
        </p>

        <p><strong>Data:</strong>
            <?php echo date("d/m/Y", strtotime($evento["data_evento"])); ?>
        </p>

        <p><strong>Local:</strong>
            <?php echo htmlspecialchars($evento["local"]); ?>
        </p>

        <p><strong>Categoria:</strong>
            <?php echo htmlspecialchars($evento["nome_categoria"]); ?>
        </p>

        <p>
            <a href="eventos.php?editar=<?php echo $evento["id"]; ?>">
                Editar
            </a>
            |
            
<a href="eventos.php?excluir=<?php echo $evento["id"]; ?>&confirmar=sim"
   onclick="return confirm('Tem certeza que deseja excluir este evento?');">
    Excluir
</a>
        </p>

        <hr>

    <?php endwhile; ?>

<?php else: ?>

    <p>Nenhum evento cadastrado ainda.</p>

<?php endif; ?>

</body>
</html>