<?php
include_once './usuario.php';
include_once './conexao.php';

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo "Usuário não autenticado.";
    exit;
}

$user_id = $_SESSION['user']->id;
$filme_id = $_POST['filme_id'] ?? null;

if (!$filme_id) {
    http_response_code(400);
    echo "ID do filme não fornecido.";
    exit;
}

$conn = mysqli_connect($hostname, $usuario, $senha, $bancodedados);
if (!$conn) {
    http_response_code(500);
    echo "Erro de conexão.";
    exit;
}

$sql = "DELETE FROM favoritos WHERE user_id = ? AND filme_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $filme_id);

if ($stmt->execute()) {
    echo "Filme removido dos favoritos!";
} else {
    http_response_code(500);
    echo "Erro ao remover filme.";
}

$stmt->close();
$conn->close();
?>
