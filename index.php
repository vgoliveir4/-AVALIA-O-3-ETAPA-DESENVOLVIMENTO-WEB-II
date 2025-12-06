<?php
require 'config.php';

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

   
    $sql = "SELECT id, nome, email, senha FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verifica a senha hash
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nome'] = $user['nome'];
            header("Location: feed.php");
            exit;
        } else {
            $erro = "Senha incorreta.";
        }
    } else {
        $erro = "E-mail não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Rede Social</title>
   

    <style>
       
        body {
            background-color: #111;
            color: #eee;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            width: 380px;
            background: #1a1a1a;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.6);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #fff;
        }

        label {
            color: #ccc;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            background: #222;
            border: 1px solid #444;
            color: #fff;
            border-radius: 6px;
            margin-top: 6px;
            margin-bottom: 18px;
        }

        input:focus {
            border-color: #666;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4a90e2;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            background: #357ac9;
        }

        a {
            color: #4a90e2;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            background: #ff4d4d;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            color: white;
            text-align: center;
        }

        .register-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
    </style>

</head>
<body>

<div class="login-box">
    <h2>Login</h2>

    <?php if($erro) echo "<p class='error'>$erro</p>"; ?>

    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Senha:</label>
        <input type="password" name="senha" required>

        <button type="submit">Entrar</button>
    </form>

    <p class="register-link">
        Não tem conta? <a href="cadastro.php">Cadastre-se aqui</a>
    </p>
</div>

</body>
</html>