<?php
include_once './usuario.php';
include_once './conexao.php';


if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$conn = mysqli_connect($hostname, $usuario, $senha, $bancodedados);
if (!$conn) {
    die("Conexão falhou: " . mysqli_connect_error());
}

?>


<?php
include "apikey.php";

$user_id = $_SESSION['user']->id;

//Favoritos
$sql = "SELECT filme_id FROM favoritos WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$favoritos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $favoritos[] = $row['filme_id'];
}




$filmes = [];

if (!empty($favoritos)) {
    $placeholders = implode(',', array_fill(0, count($favoritos), '?'));
    $sql_filmes = "SELECT * FROM filmes WHERE id IN ($placeholders)";
    $stmt_filmes = mysqli_prepare($conn, $sql_filmes);

    $types = str_repeat('i', count($favoritos));
    mysqli_stmt_bind_param($stmt_filmes, $types, ...$favoritos);
    mysqli_stmt_execute($stmt_filmes);
    $result_filmes = mysqli_stmt_get_result($stmt_filmes);

    while ($filme = mysqli_fetch_assoc($result_filmes)) {
        $filmes[] = $filme;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'head.php'; ?>

<body class="bg-black">
    <div class="max-w-7xl mx-auto relative px-4">
        <?php include 'header.php'; ?>

        <?php if (!empty($filmes)): ?>
            <div class="relative">
                <!-- Esquerda -->
                <button 
                    onclick="scrollCarrossel(-1)"
                    class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white/80 hover:bg-white rounded-full h-8 w-8 shadow-lg cursor-pointer">
                    &#8592;
                </button>

                <div>
                    <!-- Carrossel -->
                    <div id="carrossel" class="overflow-x-auto whitespace-nowrap scroll-smooth py-6">
                        <?php
                        $filmesInvertidos = array_reverse($filmes); 

                        foreach (array_slice($filmesInvertidos, 0, 12) as $index => $filme): ?>
                            <button 
                                class="inline-block align-top mr-4 p-2 rounded-sm cursor-pointer custom-tilt"
                                onclick="mostrarDetalhes(<?php echo $index; ?>)">
                                
                                <?php if (!empty($filme['imagem_url'])): ?>  
                                    <img src="https://image.tmdb.org/t/p/original<?php echo  $filme['imagem_url']; ?>" 
                                        loading="lazy" 
                                        class="w-[400px] object-cover rounded-3xl" />
                                <?php endif; ?>
                                
                                <div class="text-white text-center font-semibold text-md mt-2">
                                    <h2 class="max-h-6 truncate"><?php echo $filme['titulo']; ?></h2>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Detalhes dos Filmes -->
                    <?php foreach (array_slice($filmesInvertidos, 0, 12) as $index => $filme): ?>
                        <section id="detalhes-<?php echo $index; ?>" class="hidden rounded-md bg-black absolute top-0 py-4 left-0 z-20">
                            <div class="bg-gray-800 p-8 rounded-2xl relative shadow shadow-white">
                            <button aria-label="Fechar" class="absolute right-4 top-4 text-black hover:text-white bg-gray-300 hover:bg-red-600 elemento rounded-full w-8 h-8 flex items-center justify-center shadow-md cursor-pointer"
                                onclick="mostrarDetalhes(<?php echo $index; ?>)">
                                <i class="fa-solid fa-x"></i>
                            </button>

                            <?php
                                $sql_avaliacao = "
                                    SELECT a.comentario, a.user_id, u.nome 
                                    FROM avaliacoes a
                                    JOIN users u ON a.user_id = u.id
                                    WHERE a.filme_id = ?
                                ";
                                $stmt_avaliacao = mysqli_prepare($conn, $sql_avaliacao);
                                mysqli_stmt_bind_param($stmt_avaliacao, 'i', $filme['id']);
                                mysqli_stmt_execute($stmt_avaliacao);
                                $result_avaliacao = mysqli_stmt_get_result($stmt_avaliacao);

                                $avaliacao = [];
                                while ($row = mysqli_fetch_assoc($result_avaliacao)) {
                                    $avaliacao[] = [
                                        'comentario' => $row['comentario'],
                                        'user_id' => $row['user_id'],
                                        'nome' => $row['nome'],
                                    ];
                                }


                                $comentario_usuario = '';
                                if (isset($user_id)) {
                                    $sql_avaliacao_usuario = "
                                        SELECT comentario, nota_trilha, nota_efeito_especial, nota_roteiro, nota_geral 
                                        FROM avaliacoes 
                                        WHERE filme_id = ? AND user_id = ?
                                        LIMIT 1
                                    ";
                                    $stmt_usuario = mysqli_prepare($conn, $sql_avaliacao_usuario);
                                    mysqli_stmt_bind_param($stmt_usuario, 'ii', $filme['id'], $user_id);
                                    mysqli_stmt_execute($stmt_usuario);
                                    $result_usuario = mysqli_stmt_get_result($stmt_usuario);
                                    
                                    if ($row_usuario = mysqli_fetch_assoc($result_usuario)) {
                                        $comentario_usuario = $row_usuario['comentario'];
                                        $nota_trilha_usuario = $row_usuario['nota_trilha'];
                                        $nota_efeito_especial_usuario = $row_usuario['nota_efeito_especial'];
                                        $nota_roteiro_usuario = $row_usuario['nota_roteiro'];
                                        $nota_geral_usuario = $row_usuario['nota_geral'];
                                    }
                                }?>
                                <div class="flex gap-20">
                                    <img src="https://image.tmdb.org/t/p/original<?php echo $filme['imagem_url']; ?>" loading="lazy" class="w-1/3 rounded-xl custom-tilt cursor-pointer shadow shadow-white object-cover max-h-[600px]" />
                                    <div class="text-white w-2/3">
                                        <p class="text-5xl"><?php echo $filme['titulo']; ?></p>
                                        <p class="mt-20 text-base"><?php echo $filme['descricao']; ?></p>

                                        <div class="mt-8">
                                            <button class=" rounded-2xl text-gray-800 font-semibold cursor-pointer hover:scale-115 bg-white h-8 w-8 elemento" onclick="removerfavorito(<?php echo $filme['id']; ?>)">
                                                <i class="fa-solid fa-heart-crack text-red-800 "></i>
                                            </button>

                                            
                                            <button class="bg-white rounded-2xl text-gray-800 px-4 py-1 font-semibold cursor-pointer hover:bg-gray-800 border hover:text-white border-white elemento" onclick="mostrarFormularioComentario(<?php echo $index; ?>)">
                                            <?php if (!isset($comentario_usuario) || empty($comentario_usuario)): ?>
                                                <p>Adicionar avaliação</p>
                                            <?php else: ?> 
                                                <p>Atualizar avaliação</p>
                                            <?php endif; ?>
                                            </button>
                                        </div>

                                       

                                        <!-- Forms para os comentários-->
                                        <div class="mt-4  w-screen h-screen fixed left-0 top-0 z-20 hidden flex items-center" style="background-color: rgba(0,0,0,0.5);" id="form-comentario-<?php echo $index; ?>">
                                            <form action="avaliarfilme.php" method="POST" class="w-7xl mx-auto bg-white p-4 rounded-2xl relative ">
                                                <button aria-label="Fechar" class="absolute right-4 top-4 text-white bg-gray-800 hover:bg-red-600 elemento rounded-full w-8 h-8 flex items-center justify-center shadow-md cursor-pointer" onclick="fecharAvaliacao(<?php echo $index; ?>)">
                                                    <i class="fa-solid fa-times fa-xl"></i>
                                                </button>
                                                <h3 class="text-gray-800 text-center text-xl font-semibold">Avaliação</h3>
                                                <input type="hidden" name="filme_id" value="<?php echo $filme['id']; ?>">
                                                
                                                <div class="mb-4">
                                                    <label for="nota_roteiro" class="block text-sm font-medium text-gray-700">Nota para Roteiro (0 a 10)</label>
                                                    <input type="number" id="nota_roteiro" name="nota_roteiro" min="0" max="10" required placeholder="De 0 a 10" class="w-full p-2 rounded bg-gray-900 text-white" value="<?php echo htmlspecialchars($nota_roteiro_usuario ?? ''); ?>">
                                                </div>

                                                <div class="mb-4">
                                                    <label for="nota_trilha" class="block text-sm font-medium text-gray-700">Nota para Trilha (0 a 10)</label>
                                                    <input type="number" id="nota_trilha" name="nota_trilha" min="0" max="10" required placeholder="De 0 a 10" class="w-full p-2 rounded bg-gray-900 text-white" value="<?php echo htmlspecialchars($nota_trilha_usuario ?? ''); ?>">
                                                </div>

                                                <div class="mb-4">
                                                    <label for="nota_efeito_especial" class="block text-sm font-medium text-gray-700">Nota para Efeitos Especiais (0 a 10)</label>
                                                    <input type="number" id="nota_efeitos" name="nota_efeito_especial" min="0" max="10" required placeholder="De 0 a 10" class="w-full p-2 rounded bg-gray-900 text-white" value="<?php echo htmlspecialchars($nota_efeito_especial_usuario ?? ''); ?>">
                                                </div>

                                                <div class="mb-4">
                                                    <label for="nota_geral" class="block text-sm font-medium text-gray-700">Nota Geral (0 a 10)</label>
                                                    <input type="number" id="nota_geral" name="nota_geral" min="0" max="10" required placeholder="De 0 a 10" class="w-full p-2 rounded bg-gray-900 text-white" value="<?php echo htmlspecialchars($nota_geral_usuario ?? ''); ?>">
                                                </div>

                                                <div>
                                                    <label for="comentario" class="block text-sm font-medium text-gray-700">Comentário: </label>
                                                    <textarea name="comentario" placeholder="Comentário Geral:" class="w-full p-2 rounded bg-gray-900 text-white" required><?php echo htmlspecialchars($comentario_usuario); ?></textarea>
                                                </div>

                                                <button type="submit" class="mt-2 bg-blue-600 text-white px-4 py-1 rounded hover:bg-gray-800 cursor-pointer elemento">Enviar</button>
                                            </form>
                                        </div>

                                        <!-- Avaliações do Usuário -->
                                        <div class="mt-6 bg-gray-800 p-4 rounded-xl shadow-lg text-white max-w-md">
                                            <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">
                                                <i class="fa-solid fa-star text-yellow-400"></i>
                                                Avaliações
                                            </h3>

                                            <?php if (isset($nota_geral_usuario)): ?>
                                                <ul class="grid grid-cols-2 gap-2 text-sm">
                                                    <li class="font-bold">Nota Geral: <?= htmlspecialchars($nota_geral_usuario); ?></li>
                                                    <li class="font-bold">Trilha: <?= htmlspecialchars($nota_trilha_usuario); ?></li>
                                                    <li class="font-bold">Roteiro: <?= htmlspecialchars($nota_roteiro_usuario); ?></li>
                                                    <li class="font-bold">Efeitos: <?= htmlspecialchars($nota_efeito_especial_usuario); ?></li>
                                                </ul>
                                            <?php else: ?>
                                                <p>Filme ainda não Avaliado!</p>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Comentários -->
                                        <div class="mt-4 bg-gray-800 p-4 rounded-xl shadow-lg text-white max-w-md max-h-[150px] overflow-y-auto">
                                            <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">
                                                <i class="fa-solid fa-comment" style="color: #FFD43B;"></i>
                                                Comentários
                                            </h3>
                                            <?php foreach (array_slice($avaliacao, 0, 3) as $avaliacao): ?>
                                                <div class="mb-2 p-2 rounded bg-gray-700 ">
                                                    <p class="text-sm"><strong><?php echo htmlspecialchars($avaliacao['nome']); ?>:</strong> <?php echo htmlspecialchars($avaliacao['comentario']); ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    <?php endforeach; ?>


                <!-- Direita -->
                <button 
                    onclick="scrollCarrossel(1)"
                    class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white/80 hover:bg-white rounded-full h-8 w-8 cursor-pointer shadow-lg">
                    &#8594;
                </button>
            </div>
        <?php else: ?>
            <p class="text-white">Você não tem filmes favoritos.</p>
        <?php endif; ?>
    </div>
    <script>
        function mostrarFormularioComentario(index) {
            const form = document.getElementById(`form-comentario-${index}`);
            if (form) {
                form.classList.toggle('hidden');
            }
        }

        function mostrarDetalhes(index) {
            const div = document.getElementById('detalhes-' + index);
            div.classList.toggle('hidden');
        }

        function fecharAvaliacao(index) {
            event.preventDefault()
            const div = document.getElementById('form-comentario-' + index);
            div.classList.toggle('hidden');
        }

        VanillaTilt.init(document.querySelectorAll(".custom-tilt"), {
            max: 3,
            speed: 400
        });

        function scrollCarrossel(direction) {
            const container = document.getElementById('carrossel');
            const scrollAmount = 300 * direction;
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
        function removerfavorito(filmeId) {
            if (!confirm("Tem certeza que deseja remover este filme dos favoritos?")) return;

            fetch('removerfavorito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'filme_id=' + encodeURIComponent(filmeId)
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload(); 
            })
            .catch(error => console.error('Erro:', error));
        }
    </script>


    <style>
        
        .elemento {
            transition: all 0.3s ease-in-out;
        }
   
        #carrossel {
            scrollbar-width: thin; 
            scrollbar-color: black black; 
        }

    </style>
    
</body>

</html>

