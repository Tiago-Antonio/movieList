<?php


include_once './usuario.php';
include_once './conexao.php';

if (isset($_POST['login'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];

    

    // Conectar ao banco de dados
    $conn = mysqli_connect($hostname, $usuario, $senha, $bancodedados);
    if (!$conn) {
        die("Conexão falhou: " . mysqli_connect_error());
    }

    $consulta = mysqli_query($conn, "SELECT id, nome, email, password FROM users WHERE nome = '" . $login . "'");
    $dados = mysqli_fetch_assoc($consulta);

    $user = null;

    if ($dados != null) {
        $user = new Usuario($dados["id"], $dados["nome"], $dados["email"], $dados["password"]);
        $_SESSION['user'] = $user; 
    }

    if ($user != null && $user->ValidaUsuarioSenha($login, $password)) {
        $_SESSION['nome'] = $user->nome; 
        header("Location: menu.php"); 
        exit;
    } else {
        $_SESSION['msg'] = "Usuário ou senha incorretos!";
        header("Location: index.php");
        exit;
    }
}

if (!isset($_SESSION['nome'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<?php include 'head.php'; ?>
<body class="bg-black">
    <section class="h-full text-white max-w-7xl mx-auto">

        <h1 class="pt-2 text-gray-200 font-bold text-lg">Bem vindo, <?php echo $_SESSION['nome']?></h1>

        <?php include 'header.php'; ?>

        <div>
            <?php 
            include "apikey.php";
            $filmes = [];
            $lista_generos = [];
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $query = isset($_GET['consulta']) ? $_GET['consulta'] : null;

            if ($query) {
                $url = "https://api.themoviedb.org/3/search/movie?query=" . urlencode($query) ."&api_key={$APIKEY}&language=pt-BR&page={$page}";
            } else {
                $url = "https://api.themoviedb.org/3/movie/popular?api_key={$APIKEY}&language=pt-BR&page={$page}";
            }

            $genero_url = "https://api.themoviedb.org/3/genre/movie/list?api_key={$APIKEY}&language=pt-BR";

            $ch = curl_init();

            // gêneros
            curl_setopt($ch, CURLOPT_URL, $genero_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resposta_generos = curl_exec($ch);

            $data_generos = json_decode($resposta_generos, true);
            if (!empty($data_generos['genres'])) {
                foreach ($data_generos['genres'] as $g) {
                    $lista_generos[$g['id']] = $g['name'];
                }
            }

            // filmes
            curl_setopt($ch, CURLOPT_URL, $url); 
            $response = curl_exec($ch);
            curl_close($ch); 

            $data = json_decode($response, true);
            if (!empty($data['results'])) {
                $filmes = $data['results'];
                $total_pages = $data['total_pages'];
            }
            ?>

            <?php if (!empty($filmes)): ?>
            <div class="grid md:grid-cols-6 gap-4 mt-12" >
                <?php foreach (array_slice($filmes, 0, 12) as $index => $filme): ?>
                    <button class=" grid gap-2 bg-black  p-1 rounded-sm cursor-pointer custom-tilt" onclick="mostrarDetalhes(<?php echo $index; ?>)">
                        <?php if (!empty($filme['poster_path'])): ?>  
                            <img src="https://image.tmdb.org/t/p/original<?php echo $filme['poster_path']; ?>" loading="lazy" class="w-full custom-tilt rounded-2xl shadow shadow-white hover:shadow-yellow-800 elemento" />
                        <?php endif; ?>
                        <div class="text-white text-center font-semibold text-md">
                            <h2 class="max-h-6"><?php echo $filme['title']; ?></h2>
                        </div>
                    </button>

                     <!-- Show Seção -->
                    <section id="detalhes-<?php echo $index; ?>" class="hidden  rounded-md bg-black  2xl:h-screen w-screen fixed left-0 z-20">
                        <div aria-label="Fechar" class=" max-w-7xl mx-auto bg-gray-800 p-8 rounded-2xl relative shadow shadow-white ">
                            <button class="absolute right-4 top-4 text-black hover:text-white bg-gray-300 hover:bg-red-600 elemento rounded-full w-8 h-8 flex items-center justify-center shadow-md cursor-pointer" onclick="mostrarDetalhes(<?php echo $index; ?>)">
                                <i class="fa-solid fa-times fa-xl"></i>
                            </button>
                            <div class="flex gap-20 md:h-[400px] 2xl:h-full">
                                <img src="https://image.tmdb.org/t/p/original<?php echo $filme['poster_path']; ?>" loading="lazy" class="w-1/3 rounded-xl custom-tilt cursor-pointer shadow shadow-white"  />
                                <ul class="">
                                    <p class="text-5xl"><?php echo $filme['title']; ?></p>
                                    <div class="flex items-center gap-4 mt-20">
                                        <!-- Favoritar Filme -->
                                        <form action="favoritar.php" method="post">
                                            <input type="hidden" name="filme_id" value="<?php echo $filme['id']; ?>">

                                            <input type="hidden" name="original_title" value="<?php echo $filme['original_title']; ?>">
                                            <input type="hidden" name="overview" value="<?php echo $filme['overview']; ?>">
                                            <input type="hidden" name="poster_path" value="<?php echo $filme['poster_path']; ?>">
                                            <input type="hidden" name="release_date" value="<?php echo $filme['release_date']; ?>">
                                            <input type="hidden" name="vote_average" value="<?php echo $filme['vote_average']; ?>">
                                            <button type="submit"><i class="fa-solid fa-heart fa-lg text-red-700 hover:text-red-400 cursor-pointer elemento"></i></button>
                                        </form>
                                        <!-- Seção Generos -->
                                        <?php foreach ($filme['genre_ids'] as $id): ?>
                                            <li class="bg-gray-600 px-2 py-1 rounded-xl"><?php echo $lista_generos[$id] ?? 'Desconhecido'; ?></li>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class=" mt-24"><?php echo $filme['overview'] ?: 'Sem descrição.'; ?></p>
                                </ul>
                            </div>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

            <!-- Paginação -->
            <div class="flex justify-center items-center gap-4 mt-12">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(['consulta' => $query, 'page' => $page - 1]); ?>" class="bg-gray-700 px-4 py-2 rounded">Anterior</a>
                <?php endif; ?>
                <span>Página <?php echo $page; ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(['consulta' => $query, 'page' => $page + 1]); ?>" class="bg-gray-700 px-4 py-2 rounded">Próxima</a>
                <?php endif; ?>
            </div>

            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <p>Nenhum resultado encontrado.</p>
            <?php endif; ?>
        </div>
    </section>

    <script>
        function mostrarDetalhes(index) {
            const div = document.getElementById('detalhes-' + index);
            div.classList.toggle('hidden');
        }

        VanillaTilt.init(document.querySelectorAll(".custom-tilt"), {
            max: 3,
            speed: 400
        });
    </script>
    <style>
        .elemento {
            transition: all 0.3s ease-in-out;
        }
    </style>
</body>
</html>
