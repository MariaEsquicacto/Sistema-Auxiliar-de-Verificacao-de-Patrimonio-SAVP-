<?php
require_once "../../config/database.php";
header("Content-Type: application/json");
date_default_timezone_set('America/Sao_Paulo'); // Define fuso horário de SP/Brasília

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = conn();

        // Recebe JSON enviado no body
        $data = json_decode(file_get_contents("php://input"), true);

        $usuario_id     = $data["usuarios_id_usuario"] ?? null;
        $patrimonio_id  = $data["patrimonios_num_patrimonio"] ?? null;
        $origem         = $data["origem"] ?? null;
        $destino        = $data["destino"] ?? null;

        // Validação
        if (!$usuario_id || !$patrimonio_id || !$origem || !$destino) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Campos obrigatórios faltando (usuario, patrimonio, origem, destino).'
            ]);
            exit();
        }

        // Insert (movimentacao_del sempre 'ativo')
        $stmt = $pdo->prepare("
            INSERT INTO movimentacao_item (
                data_hora, movimentacao_del, patrimonios_num_patrimonio, origem, destino, usuarios_id_usuario
            ) VALUES (NOW(), 'ativo', ?, ?, ?, ?)
        ");

        $stmt->execute([
            $patrimonio_id,
            $origem,
            $destino,
            $usuario_id
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Movimentação registrada com sucesso!',
            'id_inserido' => $pdo->lastInsertId()
        ]);
        exit();
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao inserir: ' . $e->getMessage()
        ]);
        exit();
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use POST.'
    ]);
    exit();
}
