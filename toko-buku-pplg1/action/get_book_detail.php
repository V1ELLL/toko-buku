<?php
require_once '../config/config.php';
header('Content-type: application/json');

$book_id = $_GET['id'] ?? null;


if(!$book_id || !filter_var($book_id, FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID buku tidak valid.']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();

    
    if($book) {
        echo json_encode($book);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Buku tidak ditemukan']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Terjadi kesalahan pada server: ' . $e->getMessage()]);
}

$conn->close();
exit;
?>