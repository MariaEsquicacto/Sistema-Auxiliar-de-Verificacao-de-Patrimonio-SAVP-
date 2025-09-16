<?php
require_once __DIR__ . "/../config.php"; 
header("Content-Type: application/json");
date_default_timezone_set('America/Sao_Paulo'); // Define fuso horário

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    try {
        $pdo = conn();

        // Recebe JSON enviado no body
        $data = json_decode(file_get_contents("php://input"), true);

        $id_mov = $data["id_movimentacao"] ?? null;
        $updates = [];

        if (!$id_mov) {
            echo json_encode([
                'status' => 'error',
                'message' => 'É necessário informar o ID da movimentação (id_movimentacao).'
            ]);
            exit();
        }

        // Checa campos que vieram no body
        if (isset($data["origem"])) $updates["origem"] = $data["origem"];
        if (isset($data["destino"])) $updates["destino"] = $data["destino"];
        if (isset($data["usuarios_id_usuario"])) $updates["usuarios_id_usuario"] = $data["usuarios_id_usuario"];
        if (isset($data["patrimonios_num_patrimonio"])) $updates["patrimonios_num_patrimonio"] = $data["patrimonios_num_patrimonio"];
        if (isset($data["movimentacao_del"])) $updates["movimentacao_del"] = $data["movimentacao_del"]; // ativo ou inativo

        if (empty($updates)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Nenhum campo para atualizar foi enviado.'
            ]);
            exit();
        }

        // Monta query dinamicamente
        $setParts = [];
        foreach ($updates as $col => $val) {
            $setParts[] = "$col = :$col";
        }
        $sql = "UPDATE movimentacao_item SET " . implode(", ", $setParts) . ", data_hora = NOW() WHERE id_movimentacao = :id_mov";

        $stmt = $pdo->prepare($sql);

        // Bind params
        foreach ($updates as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }
        $stmt->bindValue(":id_mov", $id_mov, PDO::PARAM_INT);

        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Movimentação atualizada com sucesso!',
            'id_movimentacao' => $id_mov,
            'alteracoes' => $updates
        ]);
        exit();

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao atualizar: ' . $e->getMessage()
        ]);
        exit();
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use PATCH.'
    ]);
    exit();
}
