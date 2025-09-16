<?php
require_once "../config.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    try {
        $pdo = conn();

        $data = json_decode(file_get_contents("php://input"), true);

        // O ID do usuário e o e-mail são obrigatórios para identificar o registro a ser atualizado
        $id_usuario = $data["id_usuario"] ?? null;
        $usuario_email = $data["usuario_email"] ?? null;

        if (!$id_usuario || !$usuario_email) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Campos obrigatórios faltando (id_usuario, usuario_email).'
            ]);
            exit();
        }

        // Prepara os campos para a atualização de forma dinâmica
        $setClauses = [];
        $params = [
            'id_usuario' => $id_usuario,
            'usuario_email' => $usuario_email
        ];
        
        // Mapeia os campos da requisição para as colunas do banco de dados
        $columns = [
            'usuario_nome',
            'usuario_nivel',
            'usuario_del'
        ];

        foreach ($columns as $column) {
            if (isset($data[$column])) {
                $setClauses[] = "$column = :$column";
                $params[$column] = $data[$column];
            }
        }
        
        // Retorna erro se nenhum campo válido for fornecido para atualização
        if (empty($setClauses)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Nenhum campo válido fornecido para atualização.'
            ]);
            exit();
        }
        
        // Constrói a query SQL de forma dinâmica
        $sql = "UPDATE usuarios SET " . implode(', ', $setClauses) . " WHERE id_usuario = :id_usuario AND usuario_email = :usuario_email";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Usuário atualizado com sucesso!'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Usuário não encontrado ou nenhum dado foi alterado.'
            ]);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use PATCH.'
    ]);
}