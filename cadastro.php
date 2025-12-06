<?php
require 'config.php';

$erro = "";
$sucesso = "";


$nome = $username = $email = $data_nasc = $genero = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $conf_senha = $_POST['conf_senha'];
    $data_nasc = $_POST['data_nascimento'];
    $genero = $_POST['genero'];

    
    if (empty($nome) || empty($username) || empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Formato de e-mail inválido.";
    } elseif (strlen($senha) < 6 || !preg_match("/[A-Z]/", $senha) || !preg_match("/[0-9]/", $senha)) {
        $erro = "A senha deve ter no mínimo 6 caracteres, 1 maiúscula e 1 número.";
    } elseif ($senha !== $conf_senha) {
        $erro = "As senhas não coincidem.";
    } else {
       
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ? OR username = ?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $erro = "E-mail ou Username já cadastrados.";
        } else {
           
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios (nome, username, email, senha, data_nascimento, genero) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $nome, $username, $email, $senha_hash, $data_nasc, $genero);

            if ($stmt->execute()) {
               
                header("Location: index.php");
                exit;
            } else {
                $erro = "Erro ao cadastrar: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="style.css">

    <style>
        
        body {
            background-color: #111;
            color: #eee;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 420px;
            margin: 50px auto;
            background: #1a1a1a;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px #000;
        }

        h2 {
            text-align: center;
            color: #fff;
        }

        label {
            font-size: 14px;
            color: #ccc;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px 0;
            background: #222;
            border: 1px solid #444;
            color: #fff;
            border-radius: 5px;
        }

        input:focus, select:focus {
            border-color: #777;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4a90e2;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #357ac9;
        }

        a {
            color: #4a90e2;
            text-decoration: none;
            display: block;
            margin-top: 15px;
            text-align: center;
        }

        .error {
            background: #ff4d4d;
            padding: 10px;
            color: white;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>

</head>
<body>

<div class="container">
    <h2>Cadastro de Usuário</h2>

    <?php if($erro) echo "<p class='error'>$erro</p>"; ?>

    <form method="POST">

        <label>Nome Completo:</label>
        <input type="text" name="nome" value="<?php echo $nome; ?>" required>

        <label>Nome de Usuário (Username):</label>
        <input type="text" name="username" value="<?php echo $username; ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $email; ?>" required>

        <label>Senha (Mín 6, 1 Maiúscula, 1 Número):</label>
        <input type="password" name="senha" required>

        <label>Confirmar Senha:</label>
        <input type="password" name="conf_senha" required>

        <label>Data de Nascimento:</label>
        <input type="date" name="data_nascimento" value="<?php echo $data_nasc; ?>" required>

        <label>Gênero:</label>
        <select name="genero" required>
            <option value="">Selecione</option>
            <option value="feminino" <?php if($genero=='feminino') echo 'selected'; ?>>Feminino</option>
            <option value="masculino" <?php if($genero=='masculino') echo 'selected'; ?>>Masculino</option>
            <option value="outro" <?php if($genero=='outro') echo 'selected'; ?>>Outro</option>
        </select>

        <button type="submit">Cadastrar</button>
    </form>

    <a href="index.php">Voltar para Login</a>
</div>

</body>
</html>