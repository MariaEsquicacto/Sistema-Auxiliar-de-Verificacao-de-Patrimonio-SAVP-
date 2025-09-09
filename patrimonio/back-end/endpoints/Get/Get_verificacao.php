<?php

require_once "../config.php";
header("Content-Type: application/json");
date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = conn();

        // Consulta à view "verificacao"
        $stmt = $pdo->prepare("SELECT * FROM verificacao");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $result
        ]);
        exit();
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao acessar a view: ' . $e->getMessage()
        ]);
        exit();
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use GET.'
    ]);
    exit();
}
