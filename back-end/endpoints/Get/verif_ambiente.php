<?php
require_once "../config.php";
header("Content-Type: application/json");
date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = conn();

        // Query para selecionar todos os registros
        $stmt = $pdo->prepare("SELECT * FROM verificacao_ambiente");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Todos os registros foram carregados com sucesso!',
                'data' => $results
            ]);
            exit();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Nenhum registro encontrado na tabela.'
            ]);
            exit();
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao buscar dados: ' . $e->getMessage()
        ]);
        exit();
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use GET.'
    ]);
    exit();
}