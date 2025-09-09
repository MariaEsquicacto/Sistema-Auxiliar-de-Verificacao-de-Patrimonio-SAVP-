<?php
require_once "../config.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    try {
        $pdo = conn(); // função conn() do config.php

        // Recebe dados JSON
        $data = json_decode(file_get_contents("php://input"), true);

        $id     = $data["id_ambientes"]   ?? null; // ID do ambiente a ser atualizado
        $nome   = $data["ambiente_nome"] ?? null;
        $categoria = $data["catgoria"] ?? null;
        $local   = $data["localizacao"] ?? null;
        $status = $data["ambiente_del"]  ?? null;

        if ($id) {
            // Monta query dinamicamente (só atualiza o que foi enviado)
            $fields = [];
            $params = [":id" => $id];

            if ($nome !== null) {
                $fields[] = "ambiente_nome = :nome";
                $params[":nome"] = $nome;
            }
            if ($status !== null) {
                $fields[] = "ambiente_del = :status";
                $params[":status"] = $status;
            }

            if (empty($fields)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Nenhum campo para atualizar.'
                ]);
                exit();
            }

            $sql = "UPDATE ambientes SET " . implode(", ", $fields) . " WHERE id_ambientes = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode([
                'status' => 'success',
                'message' => 'Ambiente atualizado com sucesso!',
                'data' => [
                    'ambientes_id'   => $id,
                    'ambiente_nome' => $nome,
                    'categoria' => $categoria,
                    'localizacao' => $local,
                    'ambiente_del'  => $status
                ]
            ]);
            exit();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Campo obrigatório faltando (id_ambientes).'
            ]);
            exit();
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao atualizar ambiente: ' . $e->getMessage()
        ]);
        exit();
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use PUT.'
    ]);
    exit();
}
