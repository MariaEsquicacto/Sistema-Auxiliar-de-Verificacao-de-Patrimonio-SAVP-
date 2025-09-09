<?php
require_once "../../config/database.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = conn(); // função conn() vinda do config.php

        // Recebe JSON ou formulário
        $data = json_decode(file_get_contents("php://input"), true);

        $nome   = $data["usuario_nome"]   ?? null;
        $nivel  = $data["usuario_nivel"]  ?? null;
        $email  = $data["usuario_email"]  ?? null;
        $status = $data["usuario_del"]    ?? "ativo"; // padrão ativo

        if ($nome && $nivel && $email) {
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (usuario_nome, usuario_nivel, usuario_email, usuario_del) 
                VALUES (:nome, :nivel, :email, :status)
            ");

            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':nivel', $nivel);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':status', $status);

            $stmt->execute();

            echo json_encode([
                'status' => 'success',
                'message' => 'Usuário criado com sucesso!',
                'data' => [
                    'usuario_nome'  => $nome,
                    'usuario_nivel' => $nivel,
                    'usuario_email' => $email,
                    'usuario_del'   => $status
                ]
            ]);
            exit();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Campos obrigatórios faltando (usuario_nome, usuario_nivel, usuario_email).'
            ]);
            exit();
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao inserir usuário: ' . $e->getMessage()
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
