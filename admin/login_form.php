<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Ensure consistent box-sizing across all elements */
        *, *::before, *::after {
            box-sizing: border-box;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="login-container w-full max-w-sm mx-auto bg-white p-8 rounded-lg shadow-xl">
        <h2 class="text-center mb-6 text-2xl font-semibold text-gray-800">Login Admin</h2>
        <div id="error-message-container">
            <?php
            if (isset($_GET['error'])) {
                $error = htmlspecialchars($_GET['error']);
                echo '<div class="bg-red-100 text-red-700 p-3 rounded-md mb-4 text-center text-sm">' . $error . '</div>';
            }
            ?>
        </div>
        <form action="includes_admin/login.php" method="POST">
            <label for="username" class="block mb-2 font-medium text-gray-700">Username</label>
            <input type="text" id="username" name="username" required autofocus
                   class="w-full p-3 mb-4 border border-gray-300 rounded-md text-base focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-300">

            <label for="password" class="block mb-2 font-medium text-gray-700">Password</label>
            <input type="password" id="password" name="password" required
                   class="w-full p-3 mb-4 border border-gray-300 rounded-md text-base focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-300">

            <div class="flex gap-2.5 mt-6"> <a href="../index.php"
                   class="flex-1 p-3 bg-gray-500 text-white text-center rounded-md text-base no-underline hover:bg-gray-600 transition duration-300">
                    Kembali
                </a>
                <button type="submit"
                        class="flex-1 p-3 bg-blue-600 text-white rounded-md text-base cursor-pointer hover:bg-blue-700 transition duration-300">
                    Login
                </button>
            </div>
        </form>
    </div>
</body>
</html>