<?php
include_once './usuario.php';
include_once './menu.php';
include_once './conexao.php';
include_once './adicionarfilme.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user']->id;
$filme_tmdb_id = $_POST['filme_id'];


$original_title = $_POST['original_title'];
$overview = $_POST['overview'];
$poster_path = $_POST['poster_path'];
$release_date = $_POST['release_date'];
$vote_average = $_POST['vote_average'];

if (!$filme_tmdb_id) {
    echo "ID do filme inválido.";
    exit;
}

$conn = mysqli_connect($hostname, $usuario, $senha, $bancodedados);
if (!$conn) {
    die("Conexão falhou: " . mysqli_connect_error());
}

// Salva o filme e recupera o ID real da tabela filmes
$filme_id = salvarFilme($conn, $filme_tmdb_id, $original_title, $overview, $poster_path, $release_date, $vote_average);

// Verifica se o filme já está na tabela de favoritos
$check_favorite_query = "SELECT * FROM favoritos WHERE user_id = ? AND filme_id = ?";
$check_stmt = $conn->prepare($check_favorite_query);
$check_stmt->bind_param("ii", $user_id, $filme_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // O filme já foi adicionado aos favoritos
    echo "<script>alert('Este filme já está na sua lista de favoritos!');</script>";
    
    exit;
} else {
    // O filme ainda não foi adicionado, então insere nos favoritos
    $insert_query = "INSERT INTO favoritos (user_id, filme_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ii", $user_id, $filme_id);

    if ($stmt->execute()) {
        echo "<script>alert('Filme favoritado com sucesso!');</script>";
        
        exit;
    } else {
        echo "<p class='text-red-500'>Erro ao favoritar filme: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

$check_stmt->close();
$conn->close();
?>
