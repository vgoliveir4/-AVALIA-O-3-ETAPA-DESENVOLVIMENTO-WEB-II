<?php

$host = 'sql110.infinityfree.com'; 
$user = 'if0_40078566';      
$pass = '6qQ5uBxZOg';         
$db   = 'if0_40078566_rede';     

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}


session_start();
?>