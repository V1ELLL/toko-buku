<?php

require_once __DIR__ . '/../config/config.php';

$cart_item_count = 0;
if(!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_item_count += $item['quantity'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Buku bekas sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet"  href="assets/css/style.css">
    <style>
        html{
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="">
    <header class="bg-white shadow-md sticky top-0 z-40">
        <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex-shrink-0">
                    <a href="index.php" class="text-2xl font-bold text-slate-800">
                        <i class="fa-solid fa-book text-indigo-600"></i>BukuSekolahBekas
                    </a>
                </div>
                <div class="hidden md:flex md:items-center md:space-x-8">
                    <a href="index.php" class="text-slate-600 hover:text-indigo-600 font-medium">Beranda</a>
                    <a href="index.php#featured-books" class="text-slate-600 hover:text-indigo-600 font-medium">Koleksi Buku</a>
                    <a href="index.php#category" class="text-slate-600 hover:text-indigo-600 font-medium">Mata Pelajaran</a>
                    <?php if (isset($_SESSION['is_admin_logged_in']) && $_SESSION['is_admin_logged_in'] === true): ?>
                        <a href="admin.php" class="text-indigo-600 hover:text-indigo-600 font-medium">Panel Admin</a>
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="cart-button" class="relative text-slate-600 hover:text-indigo-600">
                        <i class="fa-solid fa-cart-shopping text-xl"></i>
                        <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_item_count; ?></span>
                    </button>
                    <button id="mobile-menu-button" class="md:hidden text-slate-600 hover:text-indigo-600">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>
    <main>