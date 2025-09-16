<?php
require_once "../../config/database.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");


// Define a duração do timeout em segundos (e.g., 2 horas)
$timeout_duration = 2 * 60 * 60; // 2 horas

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['email'])) {
        $email = trim($data['email']);

        $pdo = conn();

        // Verifica se existe usuário com esse email
        $smt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario_email = ?');
        $smt->execute([$email]);
        $user = $smt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Gera token único com SHA512
            $random_string = random_bytes(32);
            $token = hash('sha512', $random_string);

            // Adiciona a lógica de limpeza de tokens antigos
            // Exclui todos os tokens expirados para o usuário
            $smt_delete = $pdo->prepare("DELETE FROM token WHERE usuarios_id_usuario = ? AND created_at < NOW() - INTERVAL ? SECOND");
            $smt_delete->execute([$user['id_usuario'], $timeout_duration]);


            // Salva o novo token na tabela
            $smt = $pdo->prepare("INSERT INTO token (usuarios_id_usuario, token) VALUES (?, ?)");
            $smt->execute([$user['id_usuario'], $token]);

            if ($smt->rowCount() > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login realizado via SSO',
                    'token'   => $token,
                    'user'    => [
                        'id'    => $user['id_usuario'],
                        'email' => $user['usuario_email']
                    ]
                ]);
                exit();
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erro ao salvar login.'
                ]);
                exit();
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Email não encontrado.'
            ]);
            exit();
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Campo email ausente.'
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