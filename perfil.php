<?php
require 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$msg = "";
$user_id = $_SESSION['usuario_id'];


$stmt = $conn->prepare("SELECT nome, username, foto FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$meu_perfil = $stmt->get_result()->fetch_assoc();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_nome = $_POST['nome'];
    $novo_user = $_POST['username'];
    $nova_foto = null;

    if (!empty($_FILES['foto']['tmp_name'])) {
        $nova_foto = addslashes(file_get_contents($_FILES['foto']['tmp_name']));
        $sql = "UPDATE usuarios SET nome=?, username=?, foto=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $novo_nome, $novo_user, $nova_foto, $user_id);
    } else {
        $sql = "UPDATE usuarios SET nome=?, username=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $novo_nome, $novo_user, $user_id);
    }

    if ($stmt->execute()) {
        $msg = "Perfil atualizado com sucesso!";
        header("Refresh:1");
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil</title>

<style>


body {
    background: #0d1117;
    color: #e6e6e6;
    font-family: Arial, sans-serif;
    margin: 0;
}


nav {
    background: #0f172a;
    padding: 15px;
    display: flex;
    justify-content: center;
    gap: 25px;
    border-bottom: 2px solid #1e293b;
}

nav a {
    color: #93c5fd;
    text-decoration: none;
    font-weight: bold;
    padding: 6px 10px;
    border-radius: 6px;
    transition: 0.2s;
}

nav a:hover {
    background: #1e40af;
    color: white;
}


h2 {
    text-align: center;
    color: #60a5fa;
    margin-top: 30px;
}


.card {
    background: #131a26;
    max-width: 450px;
    margin: 30px auto;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 0 10px #0005;
}


.foto-perfil {
    text-align: center;
    margin-bottom: 20px;
}

.foto {
    width: 150px;
    height: 150px;
    border-radius: 12px;
    object-fit: cover;
    border: 3px solid #1d4ed8;
}

.foto-placeholder {
    width: 150px;
    height: 150px;
    background: #1e293b;
    border-radius: 12px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #94a3b8;
    border: 3px solid #1d4ed8;
}


input[type="text"],
input[type="file"] {
    width: 100%;
    padding: 12px;
    margin: 8px 0 18px;
    border-radius: 8px;
    background: #0f172a;
    border: 1px solid #334155;
    color: white;
}

input:focus {
    border-color: #60a5fa;
}

button {
    width: 100%;
    padding: 12px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s;
}

button:hover {
    background: #1d4ed8;
}


.success {
    background: #052e16;
    border-left: 4px solid #22c55e;
    padding: 12px;
    color: #bbf7d0;
    border-radius: 6px;
    text-align: center;
    margin-bottom: 20px;
}

</style>
</head>
<body>

<nav>
    <a href="feed.php">Feed</a>
    <a href="pesquisa.php">Pesquisar</a>
    <a href="perfil.php" style="color:#fff;">Meu Perfil</a>
    <a href="logout.php" style="color:#f87171;">Sair</a>
</nav>

<h2>Meu Perfil</h2>

<div class="card">

    <?php if($msg): ?>
        <p class="success"><?= $msg ?></p>
    <?php endif; ?>

    <div class="foto-perfil">
        <?php if ($meu_perfil['foto']): ?>
            <img src="data:image/jpeg;base64,<?= base64_encode($meu_perfil['foto']) ?>" class="foto">
        <?php else: ?>
            <div class="foto foto-placeholder">Sem Foto</div>
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data">

        <label>Nome:</label>
        <input type="text" name="nome" value="<?= $meu_perfil['nome'] ?>" required>

        <label>Username:</label>
        <input type="text" name="username" value="<?= $meu_perfil['username'] ?>" required>

        <label>Alterar Foto:</label>
        <input type="file" name="foto" accept="image/*">

        <button type="submit">Salvar Alterações</button>
    </form>
</div>

</body>
</html>