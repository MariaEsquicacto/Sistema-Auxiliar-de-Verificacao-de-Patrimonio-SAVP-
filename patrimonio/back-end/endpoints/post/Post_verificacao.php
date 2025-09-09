<?php
require_once "../../config/database.php";
header("Content-Type: application/json");
date_default_timezone_set('America/Sao_Paulo'); // Define fuso horário de SP/Brasília


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = conn();

        // Recebe JSON enviado no body
        $data = json_decode(file_get_contents("php://input"), true);

        $usuario_id  = $data["usuarios_id_usuario"] ?? null;
        $ambiente_id = $data["ambientes_id_ambientes"] ?? null;
        
        // Validação
        if (!$usuario_id || !$ambiente_id) {
            http_response_code(400); // Bad Request
            echo json_encode([
                'status' => 'error',
                'message' => 'Campos obrigatórios faltando (usuarios_id_usuario, ambientes_id_ambientes).'
            ]);
            exit();
        }

        // CORREÇÃO: Removida a vírgula extra após 'ambientes_id_ambientes'
        $stmt = $pdo->prepare("
            INSERT INTO verificacao_ambiente (
                data_hora, verificacao_del, usuarios_id_usuario, ambientes_id_ambientes
            ) VALUES (NOW(), 'ativo', ?, ?)
        ");

        $stmt->execute([
            $usuario_id,
            $ambiente_id
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Registro inserido com sucesso!',
            'id_inserido' => $pdo->lastInsertId()
        ]);
        exit();
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao inserir: ' . $e->getMessage()
        ]);
        exit();
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use POST.'
    ]);
    exit();
}
