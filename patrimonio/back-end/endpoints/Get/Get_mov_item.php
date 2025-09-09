<?php
require_once "../config.php";
header("Content-Type: application/json");
date_default_timezone_set('America/Sao_Paulo'); // Define fuso horário de SP/Brasília

try {
    $pdo = conn();

    // Permitir filtro opcional por patrimonio ou usuario
    $where = [];
    $params = [];

    if (!empty($_GET['patrimonio'])) {
        $where[] = "patrimonios_num_patrimonio = ?";
        $params[] = $_GET['patrimonio'];
    }

    if (!empty($_GET['usuario'])) {
        $where[] = "usuarios_id_usuario = ?";
        $params[] = $_GET['usuario'];
    }

    $sql = "SELECT * FROM movimentacao_item";
    if (count($where) > 0) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY data_hora DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'total' => count($dados),
        'movimentacoes' => $dados
    ]);
    exit();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao buscar movimentações: ' . $e->getMessage()
    ]);
    exit();
}
