<?php

function conn() {
    $host = 'localhost';         // ou o IP do servidor do banco
    $dbname = 'patrimonio';   // substitua pelo nome do seu banco
    $user = 'root';           // seu usuário do banco
    $pass = '';             // sua senha do banco
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // lança exceções em erros
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // retorna arrays associativos
            PDO::ATTR_EMULATE_PREPARES => false, // usa prepared statements reais
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Em produção, é melhor não exibir mensagens sensíveis
        die(json_encode([
            'status' => 'error',
            'message' => 'Erro na conexão com o banco de dados: ' . $e->getMessage()
        ]));
    }
}
