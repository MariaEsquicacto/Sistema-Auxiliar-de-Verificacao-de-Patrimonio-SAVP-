<?php
require_once "../config.php";
header("Content-Type: application/json");
date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    try {
        $pdo = conn();

        // Recebe JSON enviado no body
        $data = json_decode(file_get_contents("php://input"), true);

        // O ID do registro é obrigatório para a atualização
        $registro_id = $data["id_verificacao"] ?? null;
        
        if (!$registro_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'O ID do reg é obrigatório para a atualização.'
            ]);
            exit();
        }
        
        // Mapeia os campos da requisição para a atualização
        $setClauses = [];
        $params = [];

        // Adiciona um timestamp de atualização
        $setClauses[] = "data_hora = NOW()";

        // Mapeia os campos que podem ser atualizados
        $columns = [
            'usuarios_id_usuario',
            'ambientes_id_ambientes',
            'patrimonios_num_patrimonio',
            'verificacao_del' // Permite atualizar o status, se necessário
        ];

        foreach ($columns as $column) {
            if (isset($data[$column])) {
                $setClauses[] = "$column = :$column";
                $params[$column] = $data[$column];
            }
        }
        
        // Se nenhum campo válido foi fornecido para atualização
        if (count($setClauses) <= 1) { // Conta o NOW()
            echo json_encode([
                'status' => 'error',
                'message' => 'Nenhum campo válido fornecido para atualização.'
            ]);
            exit();
        }

        // Constrói a query de forma dinâmica
        $sql = "UPDATE verificacao_ambiente SET " . implode(', ', $setClauses) . " WHERE id_verificacao = :id_verificacao";
        $params['id_verificacao'] = $registro_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Registro atualizado com sucesso!'
            ]);
            exit();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Registro não encontrado ou nenhum dado alterado.'
            ]);
            exit();
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao atualizar registro: ' . $e->getMessage()
        ]);
        exit();
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use PATCH.'
    ]);
    exit();
}