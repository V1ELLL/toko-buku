<?php
// FILE: actions/delete_book.php

require_once '../config/config.php';

header('Content-Type: application/json');

// Cek keamanan sesi admin
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Buku berhasil dihapus.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus buku.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid.']);
}
exit;