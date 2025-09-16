<?php

include(__DIR__ . '/../config/database.php');
header('Content-Type: application/json');

$dados = json_decode(file_get_contents('php://input'), true);

if (
    isset($dados['nome'], $dados['senha'], $dados['confirmacao']) &&
    !empty(trim($dados['nome'])) &&
    !empty($dados['senha']) &&
    !empty($dados['confirmacao'])
    ) {
    $nome = trim($dados['nome']);
    $senha = $dados['senha'];
    $confirmacao = $dados['confirmacao'];

    if ($senha === $confirmacao) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("INSERT INTO usuarios (nome_usuario, senha_usuario, nivel_usuario, ativo_usuario) VALUES (?, ?, 1, 1)");
        if ($stmt) {
            $stmt->bind_param("ss", $nome, $senhaHash);
            $executado = $stmt->execute();

            if ($executado && $stmt->affected_rows > 0) {
                echo json_encode(['mensagem' => 'Usuário cadastrado com sucesso']);
            } else {
                echo json_encode(['erro' => 'Usuário ou senha incorretos']);
            }

            $stmt->close();
        } else {
            echo json_encode(['erro' => 'Erro ao preparar a query']);
        }
    } else {
        echo json_encode(['erro' => 'Usuário ou senha incorretos']);
    }
} else {
    echo json_encode(['erro' => 'Dados incompletos']);
}
