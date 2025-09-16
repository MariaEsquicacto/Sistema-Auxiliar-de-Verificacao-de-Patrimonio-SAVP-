<?php
require_once "../../config/database.php";
header("Content-Type: application/json");
date_default_timezone_set('America/Sao_Paulo');

// --- Valida o método da requisição ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Método inválido. Use POST.']);
    exit();
}

try {
    // --- Validações iniciais do arquivo ---
    if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400); // Bad Request
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'O arquivo excede o limite definido em upload_max_filesize no php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'O arquivo excede o limite definido no formulário HTML.',
            UPLOAD_ERR_PARTIAL    => 'O upload do arquivo foi feito parcialmente.',
            UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente.',
            UPLOAD_ERR_CANT_WRITE => 'Falha em escrever o arquivo em disco.',
            UPLOAD_ERR_EXTENSION  => 'Uma extensão do PHP interrompeu o upload do arquivo.',
        ];
        $errorCode = $_FILES['arquivo']['error'] ?? UPLOAD_ERR_NO_FILE;
        $message = $uploadErrors[$errorCode] ?? 'Erro desconhecido no upload.';
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit();
    }

    // --- Valida o ID do usuário (enviado como campo de formulário) ---
    $usuario_id = $_POST['usuarios_id_usuario'] ?? null;
    if (empty($usuario_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'O ID do usuário é obrigatório.']);
        exit();
    }

    $arquivo = $_FILES['arquivo'];

    // --- Valida a extensão do arquivo ---
    $nomeArquivo = $arquivo['name'];
    $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
    $extensoesPermitidas = ['csv', 'xls', 'xlsx'];

    if (!in_array($extensao, $extensoesPermitidas)) {
        http_response_code(415); // Unsupported Media Type
        echo json_encode(['status' => 'error', 'message' => 'Tipo de arquivo não permitido. Apenas CSV, XLS e XLSX são aceitos.']);
        exit();
    }

    // --- Salva o arquivo no servidor ---
    $diretorioUploads = __DIR__ . '/uploads/';
    if (!is_dir($diretorioUploads)) {
        mkdir($diretorioUploads, 0777, true);
    }
    // Cria um nome único para evitar sobrescrever arquivos
    $nomeArquivoUnico = uniqid('', true) . '.' . $extensao;
    $caminhoCompleto = $diretorioUploads . $nomeArquivoUnico;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        throw new Exception("Não foi possível mover o arquivo para o diretório de uploads.");
    }

    // --- Insere o registro no banco de dados ---
    $pdo = conn();
    $stmt = $pdo->prepare("
        INSERT INTO arquivo_importacao 
            (data_importacao, resultado, arquivo, arquivo_del, usuarios_id_usuario) 
        VALUES 
            (NOW(), :resultado, :caminho_arquivo, :arquivo_del, :id_usuario)
    ");

    $resultado = 'sucesso'; // O resultado inicial é 'sucesso' (upload bem-sucedido)
    $arquivo_del = 'ativo';   // Valor padrão

    $stmt->bindParam(':resultado', $resultado);
    $stmt->bindParam(':caminho_arquivo', $caminhoCompleto);
    $stmt->bindParam(':arquivo_del', $arquivo_del);
    $stmt->bindParam(':id_usuario', $usuario_id);
    
    $stmt->execute();

    // --- Resposta de sucesso ---
    http_response_code(201); // Created
    echo json_encode([
        'status' => 'success',
        'message' => 'Arquivo enviado e registro criado com sucesso!',
        'id_registro' => $pdo->lastInsertId(),
        'caminho_arquivo' => $caminhoCompleto
    ]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
// depois fazer os documentos preencherem os campos respctivos no banco de dados