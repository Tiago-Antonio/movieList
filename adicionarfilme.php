<?php

include_once './conexao.php';

function salvarFilme($conn, $tmdb_id, $original_title, $overview, $poster_path, $release_date, $vote_average) {

    // Verifica se o filme já existe na tabela filmes
    $query = "SELECT id FROM filmes WHERE tmdb_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tmdb_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {

        // Inserir o novo filme
        $insert_query = "INSERT INTO filmes (tmdb_id, titulo, descricao, imagem_url, ano_lancamento, nota, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("issssd", $tmdb_id, $original_title, $overview, $poster_path, $release_date, $vote_average);

        if ($insert_stmt->execute()) {
            $filme_id = $insert_stmt->insert_id; 
        } else {
            die("<p class='text-red-500'>Erro ao inserir filme: " . $insert_stmt->error . "</p>");
        }

        $insert_stmt->close();
    } else {
        $row = $result->fetch_assoc();
        $filme_id = $row['id']; 
    }

    $stmt->close();

    return $filme_id; 
}
?>
