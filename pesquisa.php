<?php
require 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['usuario_id'];
$resultados = null;

// Seguir / Deixar de Seguir
if (isset($_GET['acao']) && isset($_GET['id_alvo'])) {
    $alvo = $_GET['id_alvo'];

    if ($_GET['acao'] == 'seguir') {
        $stmt = $conn->prepare("INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $alvo);
        $stmt->execute();
    } elseif ($_GET['acao'] == 'nao_seguir') {
        $stmt = $conn->prepare("DELETE FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
        $stmt->bind_param("ii", $user_id, $alvo);
        $stmt->execute();
    }
    header("Location: pesquisa.php?busca=" . (isset($_GET['busca']) ? $_GET['busca'] : ''));
    exit;
}

// Lógica de Busca
if (isset($_GET['busca'])) {
    $busca = $_GET['busca'];
    $sql = "SELECT id, nome, username, foto FROM usuarios WHERE (nome LIKE CONCAT('%', ?, '%') OR username LIKE CONCAT('%', ?, '%')) AND id != ? ORDER BY nome";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $busca, $busca, $user_id);
    $stmt->execute();
    $resultados = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Pesquisar Usuários</title>

<style>
/* ==========================
      ESTILO MODERNO
   ========================== */

body {
    background: #0f1419;
    color: #e6e6e6;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0 15px;
}

nav {
    background: #15202b;
    padding: 15px;
    text-align: center;
    border-bottom: 1px solid #22303c;
}

nav a {
    color: #1da1f2;
    text-decoration: none;
    margin: 0 12px;
    font-weight: bold;
}

nav a:hover {
    opacity: 0.7;
}

.container {
    width: 100%;
    max-width: 650px;
    margin: 25px auto;
}

h2, h3 {
    text-align: center;
    color: #1da1f2;
    margin-bottom: 20px;
}

form {
    display: flex;
    gap: 10px;
    max-width: 100%;
    margin: 0 auto 25px auto;
}

input[type="text"] {
    flex: 1;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #22303c;
    background: #192734;
    color: #e6e6e6;
}

button {
    background: #1da1f2;
    border: none;
    padding: 10px 18px;
    border-radius: 20px;
    cursor: pointer;
    color: #fff;
    font-weight: bold;
    transition: 0.2s;
}

button:hover {
    background: #188bd0;
}

.user-card {
    background: #15202b;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #22303c;
}

.user-card div:first-child {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-card img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    background: #22303c;
}

.user-card strong {
    color: #fff;
    font-size: 16px;
}

.user-card span {
    color: #8899a6;
    font-size: 14px;
}

a.back-link {
    display: inline-block;
    margin-bottom: 20px;
    color: #1da1f2;
    text-decoration: none;
}

a.back-link:hover {
    opacity: 0.7;
}
</style>
</head>
<body>

<nav>
    Olá, <?php echo $_SESSION['nome']; ?> |
    <a href="feed.php">Feed</a>
    <a href="perfil.php">Meu Perfil</a>
    <a href="logout.php" style="color:#ff5252;">Sair</a>
</nav>

<div class="container">

    <a class="back-link" href="feed.php">← Voltar ao Feed</a>

    <h2>Pesquisar Usuários</h2>

    <form method="GET">
        <input type="text" name="busca" placeholder="Nome ou username" 
        value="<?php echo isset($_GET['busca']) ? $_GET['busca'] : ''; ?>">
        <button type="submit">Buscar</button>
    </form>

    <?php if ($resultados && $resultados->num_rows > 0): ?>
        <h3>Resultados</h3>

        <?php while($user = $resultados->fetch_assoc()): ?>
            <div class="user-card">

                <div>
                    <?php 
                    if ($user['foto']) {
                        echo '<img src="data:image/jpeg;base64,'.base64_encode($user['foto']).'">';
                    } else {
                        echo '<div class="foto-perfil" style="width:50px;height:50px;border-radius:50%;background:#22303c;display:flex;align-items:center;justify-content:center;color:#8899a6;font-size:16px;">?</div>';
                    }
                    ?>
                    <div>
                        <strong><?php echo $user['nome']; ?></strong><br>
                        <span>@<?php echo $user['username']; ?></span>
                    </div>
                </div>

                <div>
                    <?php
                    $check = $conn->prepare("SELECT id FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
                    $check->bind_param("ii", $user_id, $user['id']);
                    $check->execute();
                    $check->store_result();
                    $ja_segue = $check->num_rows > 0;
                    ?>

                    <?php if ($ja_segue): ?>
                        <a href="pesquisa.php?acao=nao_seguir&id_alvo=<?= $user['id'] ?>&busca=<?= $_GET['busca'] ?>">
                            <button>Deixar de Seguir</button>
                        </a>
                    <?php else: ?>
                        <a href="pesquisa.php?acao=seguir&id_alvo=<?= $user['id'] ?>&busca=<?= $_GET['busca'] ?>">
                            <button>Seguir</button>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        <?php endwhile; ?>
    <?php elseif (isset($_GET['busca'])): ?>
        <p style="text-align:center; color:#ff5252;">Nenhum usuário encontrado.</p>
    <?php endif; ?>

</div>

</body>
</html>