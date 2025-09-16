<?php
require_once "../../config/database.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = conn(); // função conn() vinda do config.php

        // Recebe JSON ou formulário
        $data = json_decode(file_get_contents("php://input"), true);

        $local    = $data["localizacao"]   ?? null;
        $categoria= $data["categoria"]   ?? null;
        $nome     = $data["ambiente_nome"]   ?? null;
        $status   = $data["ambiente_del"]    ?? "ativo"; // padrão ativo

        if ($nome) {
            // CORREÇÃO: Adicionada a vírgula entre 'localizacao' e 'ambiente_del'
            $stmt = $pdo->prepare("
                INSERT INTO ambientes (ambiente_nome, categoria, localizacao, ambiente_del) 
                VALUES (:nome, :categoria, :local, :status)
            ");

            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':categoria', $categoria);
            $stmt->bindParam(':local', $local);
            $stmt->bindParam(':status', $status);

            $stmt->execute();

            echo json_encode([
                'status' => 'success',
                'message' => 'Ambiente criado com sucesso!',
                'data' => [
                    'ambiente_nome'  => $nome,
                    'categoria'  => $categoria,
                    'localizacao'  => $local,
                    'ambiente_del'   => $status
                ]
            ]);
            exit();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Campos obrigatórios faltando (ambiente_nome).'
            ]);
            exit();
        }
    } catch (Exception $e) {
        // Retorna um código de status HTTP 500 para erros de servidor
        http_response_code(500); 
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao inserir ambiente: ' . $e->getMessage()
        ]);
        exit();
    }
} else {
    // Retorna um código de status HTTP 405 para método não permitido
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use POST.'
    ]);
    exit();
}
