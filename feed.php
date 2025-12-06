<?php
require 'config.php';


if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['usuario_id'];
$msg = "";


if (isset($_POST['novo_post'])) {
    $conteudo = trim($_POST['conteudo']);
    if (!empty($conteudo)) {
        $stmt = $conn->prepare("INSERT INTO posts (usuario_id, conteudo) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $conteudo);
        $stmt->execute();
        header("Location: feed.php");
        exit;
    } else {
        $msg = "O post não pode estar vazio.";
    }
}


if (isset($_GET['curtir'])) {
    $post_id = $_GET['curtir'];
    $stmt = $conn->prepare("UPDATE posts SET curtidas = curtidas + 1 WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    header("Location: feed.php");
    exit;
}


$sql_feed = "SELECT 
    p.id AS post_id,
    p.conteudo,
    p.curtidas,
    p.data_criacao,
    u.id AS usuario_id,
    u.nome,
    u.username,
    u.foto
FROM posts p
INNER JOIN usuarios u ON p.usuario_id = u.id
WHERE p.usuario_id IN (
    SELECT seguido_id FROM seguidores WHERE seguidor_id = ?
    UNION SELECT ? 
)
ORDER BY p.data_criacao DESC";

$stmt = $conn->prepare($sql_feed);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$posts = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Feed</title>

<style>
body {
    background: #0f1419;
    color: #e6e6e6;
    font-family: Arial, sans-serif;
    margin: 0;
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

h3 {
    color: #1da1f2;
}

textarea {
    width: 100%;
    height: 90px;
    padding: 12px;
    background: #192734;
    border: 1px solid #22303c;
    border-radius: 8px;
    color: white;
    resize: none;
}

button {
    background: #1da1f2;
    border: none;
    padding: 10px 18px;
    border-radius: 20px;
    margin-top: 8px;
    cursor: pointer;
    color: #fff;
    font-weight: bold;
}

button:hover {
    background: #188bd0;
}

.post {
    background: #15202b;
    padding: 18px;
    border-radius: 12px;
    margin-bottom: 20px;
    border: 1px solid #22303c;
}

.foto-perfil {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    background: #22303c;
}

.small-date {
    font-size: 12px;
    color: #8899a6;
}

</style>
</head>
<body>

<nav>
    Olá, <?php echo $_SESSION['nome']; ?> |
    <a href="feed.php">Feed</a>
    <a href="pesquisa.php">Pesquisar Usuários</a>
    <a href="perfil.php">Meu Perfil</a>
    <a href="logout.php" style="color:#ff5252;">Sair</a>
</nav>

<div class="container">

    <h3>Criar Novo Post</h3>

    <form method="POST">
        <textarea name="conteudo" placeholder="No que você está pensando?" required></textarea>
        <button type="submit" name="novo_post">Publicar</button>
    </form>

    <?php if($msg) echo "<p style='color:#ff5252'>$msg</p>"; ?>

    <hr style="border-color:#22303c; margin:25px 0;">

    <h3>Feed de Notícias</h3>

    <?php while($row = $posts->fetch_assoc()): ?>
    <div class="post">

        <div style="display:flex; align-items:center; gap:10px;">
            <?php 
            if ($row['foto']) {
                echo '<img src="data:image/jpeg;base64,'.base64_encode($row['foto']).'" class="foto-perfil"/>';
            } else {
                echo '<div class="foto-perfil" style="display:flex; align-items:center; justify-content:center; font-size:11px;">?</div>';
            }
            ?>
            <div>
                <strong><?php echo $row['nome']; ?></strong>
                (<?php echo '@'.$row['username']; ?>)<br>
                <span class="small-date"><?php echo date('d/m/Y H:i', strtotime($row['data_criacao'])); ?></span>
            </div>
        </div>

        <p style="margin-top:10px;"><?php echo nl2br(htmlspecialchars($row['conteudo'])); ?></p>

        <p>Likes: <?php echo $row['curtidas']; ?></p>

        <a href="feed.php?curtir=<?php echo $row['post_id']; ?>">
            <button>Curtir (+1)</button>
        </a>

    </div>
    <?php endwhile; ?>

</div>

</body>
</html>