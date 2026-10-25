<?php
// FILE: actions/simpan_buku.php
require_once '../config/config.php';

// Wajib: Beritahu browser bahwa balasan kita berupa JSON
header('Content-Type: application/json');

// Cek keamanan sesi admin
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['book_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $price = $_POST['price'] ?? 0;
    $condition = $_POST['book_condition'] ?? '';
    $category = $_POST['category'] ?? '';
    $img_url = $_POST['img_url'] ?? '';
    $description = $_POST['description'] ?? '';

    try {
        if (empty($id)) {
            // Logika INSERT (Tambah Buku Baru)
            $stmt = $conn->prepare("INSERT INTO books (title, author, price, book_condition, category, img_url, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssissss", $title, $author, $price, $condition, $category, $img_url, $description);
        } else {
            // Logika UPDATE (Edit Buku)
            $stmt = $conn->prepare("UPDATE books SET title=?, author=?, price=?, book_condition=?, category=?, img_url=?, description=? WHERE id=?");
            $stmt->bind_param("ssissssi", $title, $author, $price, $condition, $category, $img_url, $description, $id);
        }

        if ($stmt->execute()) {
            // Jika berhasil, kirim pesan sukses JSON
            echo json_encode(['success' => true, 'message' => 'Data buku berhasil disimpan!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database.']);
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
}
exit;