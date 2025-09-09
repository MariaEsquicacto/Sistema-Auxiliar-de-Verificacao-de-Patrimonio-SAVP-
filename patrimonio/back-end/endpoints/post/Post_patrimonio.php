<?php
require_once "../../config/database.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = conn(); // função conn() vinda do config.php

        // Recebe JSON 
        $data = json_decode(file_get_contents("php://input"), true);

        $num_patrimonio = $data["num_patrimonio"] ?? null;
        $nome           = $data["patrimonio_nome"] ?? null;
        $atividade      = $data["patrimonio_del"] ?? "ativo";
        $status         = "pendente"; // Sempre inicia como pendente
        $img            = $data["patrimonio_img"] ?? null;
        $denominacao    = $data["denominacao"] ?? null;
        $origem         = $data["ambientes_id_ambientes"] ?? null;

        // Validação mais robusta, verificando se não são vazios
        if (!empty($num_patrimonio) && !empty($nome) && !empty($origem)) {
            
            // O INSERT está correto, pois omite a coluna 'verificacao_ambiente_id_verificacao'.
            // Após a alteração na tabela, o banco de dados irá inserir NULL automaticamente nesta coluna.
            $stmt = $pdo->prepare("
                INSERT INTO patrimonios 
                    (num_patrimonio, patrimonio_nome, patrimonio_del, status, patrimonio_img, denominacao, ambientes_id_ambientes) 
                VALUES 
                    (:num_patrimonio, :nome, :atividade, :status, :img, :denominacao, :origem)
            ");

            $stmt->bindParam(':num_patrimonio', $num_patrimonio);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':atividade', $atividade);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':img', $img);
            $stmt->bindParam(':denominacao', $denominacao);
            $stmt->bindParam(':origem', $origem);

            $stmt->execute();

            http_response_code(201); // 201 Created
            echo json_encode([
                'status'  => 'success',
                'message' => 'Patrimônio cadastrado com sucesso!',
                'data'    => [
                    'num_patrimonio' => $num_patrimonio,
                    'patrimonio_nome' => $nome,
                    'status' => $status
                ]
            ]);
            exit();
        } else {
            http_response_code(400); // Bad Request
            echo json_encode([
                'status' => 'error',
                'message' => 'Campos obrigatórios faltando (num_patrimonio, patrimonio_nome, ambientes_id_ambientes).'
            ]);
            exit();
        }
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        // Verifica se é erro de chave duplicada (patrimônio já existe)
        if ($e->getCode() == '23000') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro: O número de patrimônio ' . $num_patrimonio . ' já está cadastrado.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao inserir patrimônio: ' . $e->getMessage()
            ]);
        }
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
