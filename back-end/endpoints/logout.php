<?php
include(__DIR__ . '/../config/database.php');
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$refreshTokenSecret = 'seu_segredo_de_refresh_aqui';

$dados = json_decode(file_get_contents('php://input'), true);

if (!isset($dados['refreshToken'])) {
    echo json_encode(['erro' => 'Refresh token é obrigatório']);
    exit;
}

$refreshToken = $dados['refreshToken'];

// Opcional: verificar se o token é válido (não obrigatório, só se quiser)
try {
    $decoded = JWT::decode($refreshToken, new Key($refreshTokenSecret, 'HS256'));
} catch (Exception $e) {
    echo json_encode(['erro' => 'Refresh token inválido']);
    exit;
}

// Apagar o refresh token do banco (logout)
$stmt = $mysqli->prepare("DELETE FROM refresh_tokens WHERE token = ?");
$stmt->bind_param("s", $refreshToken);
$executado = $stmt->execute();
$stmt->close();

if ($executado) {
    echo json_encode(['mensagem' => 'Logout realizado com sucesso']);
} else {
    echo json_encode(['erro' => 'Erro ao realizar logout']);
}
