<?php
require_once "../config.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    try {
        $pdo = conn(); // função conn() do config.php

        // Recebe JSON
        $data = json_decode(file_get_contents("php://input"), true);

        // EXTRAI O ID DO USUÁRIO DO JSON
        $id_usuario     = $data["id_usuario"] ?? null;

        $id             = $data["patrimonio"]["num_patrimonio"] ?? null; // chave primária
        $nome           = $data["patrimonio"]["patrimonio_nome"] ?? null;
        $atividade      = $data["patrimonio"]["patrimonio_del"] ?? null;
        $status         = $data["patrimonio"]["status"] ?? null;
        $img            = $data["patrimonio"]["patrimonio_img"] ?? null;
        $denominacao    = $data["patrimonio"]["denominacao"] ?? null;
        $origem         = $data["patrimonio"]["ambientes_id_ambientes"] ?? null;

        if ($id && $id_usuario) { // Verifica se o ID do patrimônio e do usuário existem
            // DEFINE A VARIÁVEL DE SESSÃO DO MYSQL
            $pdo->exec("SET @id_usuario_logado = " . (int)$id_usuario);
            
            $stmt = $pdo->prepare("
                UPDATE patrimonios SET 
                    patrimonio_nome = COALESCE(:nome, patrimonio_nome),
                    patrimonio_del = COALESCE(:atividade, patrimonio_del),
                    status = COALESCE(:status, status),
                    patrimonio_img = COALESCE(:img, patrimonio_img),
                    denominacao = COALESCE(:denominacao, denominacao),
                    ambientes_id_ambientes = COALESCE(:origem, ambientes_id_ambientes)
                WHERE num_patrimonio = :id
            ");

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':atividade', $atividade);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':img', $img);
            $stmt->bindParam(':denominacao', $denominacao);
            $stmt->bindParam(':origem', $origem);
            
            $stmt->execute();

            echo json_encode([
                'status'  => 'success',
                'message' => 'Patrimônio atualizado com sucesso!',
                'data'    => [
                    'id_patrimonios' => $id,
                    'patrimonio_nome' => $nome,
                    'status' => $status
                ]
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'ID do patrimônio e ID do usuário são obrigatórios para atualizar.'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao atualizar patrimônio: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use PUT.'
    ]);
}