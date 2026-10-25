<?php
// FILE: login.php
require_once 'config/config.php';

// Cegah akses ke halaman login jika sudah login
if (isset($_SESSION['is_admin_logged_in']) && $_SESSION['is_admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Ambil data user dari database
    $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Cek user & password
    if ($user && password_verify($password, $user['password'])) {

        // Cek role admin
        if ($user['role'] === 'admin') {

            // Simpan ke session
            $_SESSION['is_admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap']; 

            header("Location: admin.php");
            exit;

        } else {
            $error_message = 'Anda tidak memiliki akses admin.';
        }

    } else {
        $error_message = 'Username atau kata sandi salah.';
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Toko Buku Bekas</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-sm m-4">
        <div class="p-6 sm:p-8">

            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-slate-600">
                    <i class="fa-solid fa-lock text-indigo-600"></i>
                    Akses Panel Admin
                </h1>
                <p class="text-slate-500 mt-2">Silahkan masukan kata sandi untuk melanjutkan.</p>
            </div>

            <form method="POST" action="">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" name="username" id="username"
                        class="w-full border border-slate-300 rounded-md shadow-sm p-2"
                        required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Kata Sandi</label>
                    <input type="password" name="password" id="password"
                        class="w-full border border-slate-300 rounded-md shadow-sm p-2"
                        required>
                </div>

                <?php if (!empty($error_message)): ?>
                    <p class="text-red-500 text-sm mt-2">
                        <?= htmlspecialchars($error_message) ?>
                    </p>
                <?php endif; ?>

                <div class="mt-6">
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300">
                        Login
                    </button>
                </div>

                <div class="text-center mt-4">
                    <a href="index.php" class="text-sm text-indigo-600 hover:underline">
                        &larr; Kembali ke Toko
                    </a>
                </div>
            </form>

        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</body>
</html>