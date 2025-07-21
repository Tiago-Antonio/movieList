<?php
include_once './usuario.php';
include_once './conexao.php';
include_once './adicionarfilme.php';

//Autenticação
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}


$user_id = $_SESSION['user']->id;
$filme_id = $_POST['filme_id'] ?? null;
$comentario = $_POST['comentario'] ?? '';
$nota_geral = $_POST['nota_geral'] ?? '';
$nota_trilha = $_POST['nota_trilha'] ?? '';
$nota_roteiro = $_POST['nota_roteiro'] ?? '';
$nota_efeito_especial = $_POST['nota_efeito_especial'] ?? '';

if (!$filme_id) {
    echo "ID do filme inválido.";
    exit;
}

// Conexao
$conn = mysqli_connect($hostname, $usuario, $senha, $bancodedados);
if (!$conn) {
    die("Conexão falhou: " . mysqli_connect_error());
}

// Verificar
$sql_check = "SELECT id FROM avaliacoes WHERE user_id = ? AND filme_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $filme_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    // Atualizar/Editar
    $sql_update = "UPDATE avaliacoes SET comentario = ?, nota_geral = ?, nota_trilha = ?, nota_roteiro = ?, nota_efeito_especial = ?, updated_at = NOW() WHERE user_id = ? AND filme_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("siiiiii", $comentario, $nota_geral, $nota_trilha, $nota_roteiro, $nota_efeito_especial, $user_id, $filme_id);

    if ($stmt_update->execute()) {
        echo "<script>alert('Avaliação atualizada com sucesso!'); window.location.href = 'listafavoritos.php';</script>";
    } else {
        echo "<p class='text-red-500'>Erro ao atualizar Avaliação: " . $stmt_update->error . "</p>";
    }

    $stmt_update->close();
} else {
    // Inserir comentário
    $insert_query = "INSERT INTO avaliacoes (user_id, nota_geral, nota_trilha, nota_roteiro, nota_efeito_especial, filme_id, comentario, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt_insert = $conn->prepare($insert_query);
    $stmt_insert->bind_param("iiiiiss", $user_id, $nota_geral, $nota_trilha, $nota_roteiro, $nota_efeito_especial, $filme_id, $comentario);

    if ($stmt_insert->execute()) {
        echo "<script> alert('Avaliação Inserida com sucesso!'); window.location.href = 'listafavoritos.php'; </script>";
    } else {
        echo "<p class='text-red-500'>Erro ao inserir Avaliação: " . $stmt_insert->error . "</p>";
    }

    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();
?>
