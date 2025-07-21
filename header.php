
<header class="my-4">
    <nav class="bg-gray-800 px-4 py-2 rounded-xl w-full">
        <div class="flex justify-between items-center">
            <ul class="flex gap-8 h-full items-center">
                <li class="cursor-pointer">
                    <a href="menu.php"><img src="img/popcorn.png" alt="Cinema" class="h-12"></a>
                </li>
                <li class="text-gray-400"><a href="menu.php">Home</a></li>
                <li class="text-gray-400"><a href="listafavoritos.php">Favoritos</a></li>
            </ul>
            <div class="flex gap-4 items-center">
                <form action="" method="GET">
                    <input type="text" placeholder="Pesquisar" name="consulta" class="min-h-8 px-2 text-gray-200 font-semibold text-md border rounded-2xl focus:bg-white focus:text-gray-800 elemento">
                </form>
                <a href="logout.php" class="text-sm text-gray-400">
                    Log out <i class="fa-solid fa-right-from-bracket text-white"></i>
                </a>
            </div>
        </div>
    </nav>
    <style>
        .elemento {
            transition: all 0.3s ease-in-out;
        }
    </style>
</header>
