<?php
include(__DIR__ . '/../config/database.php');
require_once __DIR__ . '/../../vendor/autoload.php';
// carregando composer autoload para JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
date_default_timezone_set('America/Sao_Paulo');

// Segredos para assinar tokens
$accessTokenSecret = 'seu_segredo_do_access_token';
$refreshTokenSecret = 'seu_segredo_de_refresh_aqui';

$dados = json_decode(file_get_contents('php://input'), true);

if (isset($dados['nome'], $dados['senha'])) {
    $nome = trim($dados['nome']);
    $senha = $dados['senha'];

    $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE nome_usuario = ?");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($senha, $usuario['senha_usuario'])) {
            $usuarioId = $usuario['id_usuario'];

            $issuedAt = time();
            $accessExp = $issuedAt + 3600; // 1 hora
            $refreshExp = $issuedAt + (7 * 24 * 60 * 60); // 7 dias

            // Payload do Access Token
            $accessTokenPayload = [
                'iat' => $issuedAt,
                'exp' => $accessExp,
                'id_usuario' => $usuarioId,
                'nome' => $usuario['nome_usuario'],
                'nivel' => $usuario['nivel_usuario']
            ];

            // Payload do Refresh Token
            // Nota: O payload do refresh token é geralmente mais simples, mas pode ser como o do access token.
            $refreshTokenPayload = [
                'iat' => $issuedAt,
                'exp' => $refreshExp, // A expiração do refresh token deve ser mais longa
                'id_usuario' => $usuarioId,
                'nome' => $usuario['nome_usuario'],
                'nivel' => $usuario['nivel_usuario']
            ];

            $accessToken = JWT::encode($accessTokenPayload, $accessTokenSecret, 'HS256');
            $refreshToken = JWT::encode($refreshTokenPayload, $refreshTokenSecret, 'HS256');

            // Salva o refresh token no banco
            $expira_em = date('Y-m-d H:i:s', $refreshExp);
            $stmtRefresh = $mysqli->prepare(
                "INSERT INTO refresh_tokens (id_usuario, token, expiracao) VALUES (?, ?, ?)"
            );
            if ($stmtRefresh) { // Adicionado verificação para o prepare
                $stmtRefresh->bind_param("iss", $usuarioId, $refreshToken, $expira_em);
                $stmtRefresh->execute();
                $stmtRefresh->close();
            } else {
                // Log de erro se o prepare falhar, mas não interrompe o login
                error_log("Erro ao preparar a query para salvar refresh token: " . $mysqli->error);
            }

            // --- AQUI É ONDE OS TOKENS SÃO SALVOS NA SESSÃO PHP ---
            session_start(); // Garante que a sessão está iniciada (já está no topo do arquivo principal que inclui este endpoint)
            $_SESSION['id_usuario'] = $usuarioId;
            $_SESSION['nome_usuario'] = $usuario['nome_usuario'];
            $_SESSION['access_token'] = $accessToken;
            $_SESSION['refresh_token'] = $refreshToken;
            // --- FIM DA ADIÇÃO ---

            echo json_encode([
                'mensagem' => 'Login realizado com sucesso!',
                'accessToken' => $accessToken,
                'refreshToken' => $refreshToken,
                'expira_em' => date('Y-m-d H:i:s', $accessExp), // Expiração do Access Token
                'id_usuario' => $usuarioId, // Retornar o ID do usuário para o frontend também pode ser útil
                'nome' => $usuario['nome_usuario'] // Retornar o nome do usuário
            ]);
        } else {
            echo json_encode(['erro' => 'Senha incorreta']);
        }
    } else {
        echo json_encode(['erro' => 'Usuário não encontrado']);
    }

    $stmt->close();
} else {
    echo json_encode(['erro' => 'Dados incompletos']);
}

// Fechar a conexão com o banco de dados se ela foi aberta e não houve erro
if (isset($mysqli) && $mysqli && !$mysqli->connect_error) { // Adicionado isset($mysqli)
    $mysqli->close();
}
