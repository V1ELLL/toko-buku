<?php

require_once "../config/config.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_REQUEST['action'] ?? null;
$was_successful = false;

// =============================================
// AKSI POST (mengubah data keranjang)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = (int)($_POST['book_id'] ?? 0);

    // --- Tambah item ke keranjang ---
    if ($action === 'add' && $book_id > 0) {
        if (isset($_SESSION['cart'][$book_id])) {
            $_SESSION['cart'][$book_id]['quantity']++;
        } else {
            $stmt = $conn->prepare("SELECT id, title, price, img_url FROM books WHERE id = ?");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($book = $result->fetch_assoc()) {
                $_SESSION['cart'][$book_id] = [
                    'id'       => (int)$book['id'],
                    'title'    => $book['title'],
                    'price'    => (int)$book['price'],
                    'img_url'  => $book['img_url'],
                    'quantity' => 1,
                    'selected' => true
                ];
            }
            $stmt->close();
        }
        $was_successful = true;
    }

    // --- Update kuantitas item ---
    if ($action === 'update_quantity' && $book_id > 0) {
        $new_qty = (int)($_POST['quantity'] ?? 0);
        if ($new_qty <= 0) {
            unset($_SESSION['cart'][$book_id]);
        } else {
            if (isset($_SESSION['cart'][$book_id])) {
                $_SESSION['cart'][$book_id]['quantity'] = $new_qty;
            }
        }
        $was_successful = true;
    }

    // --- Toggle pilih/batal pilih satu item ---
    if ($action === 'toggle_select' && $book_id > 0) {
        if (isset($_SESSION['cart'][$book_id])) {
            $_SESSION['cart'][$book_id]['selected'] = !$_SESSION['cart'][$book_id]['selected'];
        }
        $was_successful = true;
    }

    // --- Hapus semua item yang dipilih ---
    if ($action === 'remove_selected') {
        foreach ($_SESSION['cart'] as $id => $item) {
            if (!empty($item['selected'])) {
                unset($_SESSION['cart'][$id]);
            }
        }
        $was_successful = true;
    }

    // --- Pilih semua / batal pilih semua ---
    if ($action === 'select_all') {
        $is_selected = isset($_POST['is_selected']) && ($_POST['is_selected'] == '1' || $_POST['is_selected'] == 'true');
        foreach ($_SESSION['cart'] as $id => $item) {
            $_SESSION['cart'][$id]['selected'] = $is_selected;
        }
        $was_successful = true;
    }
}

// =============================================
// AKSI GET (membaca data keranjang)
// =============================================

// --- Render tampilan HTML keranjang ---
if ($action === 'get_view') {
    $cart_count = 0;
    $total_price = 0;
    $selected_item_count = 0;
    $html = '';

    if (empty($_SESSION['cart'])) {
        $html = '<p class="text-slate-500 text-center">Keranjang Anda masih kosong.</p>';
    } else {
        foreach ($_SESSION['cart'] as $book_id => $item) {
            $cart_count      += $item['quantity'];
            $item_total       = $item['price'] * $item['quantity'];
            $is_checked       = !empty($item['selected']) ? 'checked' : '';
            $is_selected_cls  = !empty($item['selected']) ? 'bg-indigo-50' : '';

            if (!empty($item['selected'])) {
                $total_price        += $item_total;
                $selected_item_count += $item['quantity'];
            }

            $img_url      = htmlspecialchars($item['img_url'] ?? '');
            $title        = htmlspecialchars($item['title']);
            $price_fmt    = 'Rp ' . number_format($item['price'], 0, ',', '.');
            $subtotal_fmt = 'Rp ' . number_format($item_total, 0, ',', '.');
            $qty          = (int)$item['quantity'];

            $html .= "
            <div class=\"flex items-center gap-4 py-3 border-b last:border-b-0 {$is_selected_cls}\">
                <input type=\"checkbox\" class=\"cart-item-checkbox rounded border-slate-400 text-indigo-600 focus:ring-indigo-500 flex-shrink-0\" data-id=\"{$book_id}\" {$is_checked}>
                <img src=\"{$img_url}\" alt=\"{$title}\" class=\"w-16 h-20 object-cover rounded flex-shrink-0\" onerror=\"this.src='https://placehold.co/64x80/cccccc/ffffff?text=Buku'\">
                <div class=\"flex-grow\">
                    <p class=\"font-semibold text-sm text-slate-800\">{$title}</p>
                    <p class=\"text-indigo-600 font-bold text-sm\">{$price_fmt}</p>
                    <div class=\"flex items-center gap-2 mt-2\">
                        <button class=\"quantity-change-btn w-7 h-7 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold flex items-center justify-center\" data-id=\"{$book_id}\" data-action=\"decrease\">-</button>
                        <span class=\"text-sm font-medium w-6 text-center\">{$qty}</span>
                        <button class=\"quantity-change-btn w-7 h-7 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold flex items-center justify-center\" data-id=\"{$book_id}\" data-action=\"increase\">+</button>
                    </div>
                </div>
                <div class=\"text-right flex-shrink-0\">
                    <p class=\"text-sm font-bold text-slate-700\">{$subtotal_fmt}</p>
                </div>
            </div>";
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success'               => true,
        'html'                  => $html,
        'cart_count'            => $cart_count,
        'selected_item_count'   => $selected_item_count,
        'total_price_formatted' => 'Rp ' . number_format($total_price, 0, ',', '.'),
        'cart_items'            => $_SESSION['cart']
    ]);
    exit;
}

// --- Data untuk proses checkout ---
if ($action === 'get_checkout_data') {
    $items       = [];
    $total_price = 0;

    foreach ($_SESSION['cart'] as $book_id => $item) {
        if (!empty($item['selected'])) {
            $subtotal     = $item['price'] * $item['quantity'];
            $total_price += $subtotal;
            $items[]      = [
                'id'       => $book_id,
                'title'    => $item['title'],
                'price'    => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'items'           => $items,
        'total'           => $total_price,
        'total_formatted' => 'Rp ' . number_format($total_price, 0, ',', '.')
    ]);
    exit;
}

// =============================================
// Respons default untuk aksi POST (add, dll.)
// =============================================
$cart_count = array_reduce($_SESSION['cart'] ?? [], function ($sum, $item) {
    return $sum + $item['quantity'];
}, 0);

header('Content-Type: application/json');
echo json_encode([
    'success'    => $was_successful,
    'cart_count' => $cart_count
]);

$conn->close();
exit;