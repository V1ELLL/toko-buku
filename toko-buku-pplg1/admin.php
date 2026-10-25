<?php
require_once 'config/config.php';

if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$is_ajax = isset($_GET['ajax']) && $_GET['ajax'] === 'true';

if (!$is_ajax) {
    require_once "includes/header.php";
}

$nama_lengkap = $_SESSION['nama_lengkap'];

//konfigurasi
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_clause = "";
$params = [];
$types = "";

if (!empty($search)) {
    $where_clause = " WHERE title LIKE ? OR author LIKE ?";
    $search_like = "%" . $search . "%";
    $types = "ss";
    $params = [$search_like, $search_like];
}

//hitung jumlah
$count_sql = "SELECT COUNT(*) AS total FROM books" . $where_clause;
$stm_count = $conn->prepare($count_sql);
if (!empty($params)) $stm_count->bind_param($types, ...$params);
$stm_count->execute();
$total_books = $stm_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_books / $limit);
$stm_count->close();

//ambil data
$sql = "SELECT * FROM books" . $where_clause . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$types_data = $types . "ii";
$params_data = $params;
array_push($params_data, $limit, $offset);
$stmt->bind_param($types_data, ...$params_data);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$qs_search = !empty($search) ? '&search=' . urlencode($search) : '';

if ($is_ajax) {
    ob_start();
} else {

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Halaman Admin</title>
    </head>

    <body>
        <section class="py-12 bg-slate-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white p-6 sm:p-8 rounded-lg shadow-lg">
                    <div class="flex flex-wrap justify-between items-center mb-6 border-b pb-4">
                        <h1 class="text-3xl font-bold text-slate-800">Kelola Inventaris Buku (<?= htmlspecialchars($nama_lengkap) ?>)</h1>
                        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-colors"><i class="fa-solid fa-right-from-bracket mr-2"></i>Logout</a>
                    </div>

                    <?php if (isset($_GET['status'])): ?>
                        <?php $status = $_GET['status']; ?>
                        <div class="mb-4 p-4 rounded-md 
        <?= $status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php if ($status === 'success'): ?>
                                <strong>Horee..!!</strong> Data buku berhasil disimpan.
                            <?php elseif ($status === 'deleted'): ?>
                                <strong>Berhasil!</strong> Data buku berhasil dihapus.
                            <?php else: ?>
                                <strong>Oops!</strong> Terjadi kesalahan.
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>
                    <form id="admin-form" method="POST" action="action/simpan_buku.php" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <input type="hidden" id="book-id" name="book_id">

                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1" for="book_title">Judul Buku</label>
                            <input type="text" id="book_title" name="title" class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1" for="author">Penerbit/Penulis</label>
                            <input type="text" id="author" name="author" class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1" for="price">Harga</label>
                            <input type="number" id="price" name="price" class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1" for="book-condition">Kondisi</label>
                            <select id="book-condition" name="condition" class="w-full border border-gray-300 rounded-md shadow-sm bg-white text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500" required>
                                <option value="" disabled selected>Pilih Kondisi</option>
                                <option value="Seperti Baru">Seperti Baru</option>
                                <option value="Baik">Baik</option>
                                <option value="Cukup">Cukup</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1" for="category">Mata Pelajaran</label>
                            <select
                                id="category"
                                name="category"
                                class="w-full border border-gray-300 rounded-md shadow-sm bg-white text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500" required>
                                <option value="" disabled selected>Pilih Kategori</option>
                                <option value="Matematika">Matematika</option>
                                <option value="Fisika">Fisika</option>
                                <option value="Kimia">Kimia</option>
                                <option value="Biologi">Biologi</option>
                                <option value="Sejarah">Sejarah</option>
                                <option value="Bahasa">Bahasa</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1" for="img-url">IMG URL</label>
                            <input type="text" id="img-url" name="img_url" class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-500 mb-1" for="description">Deskripsi</label>
                            <textarea id="description" name="description" rows="4" maxlength="500" class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Masukkan deskripsi buku..." required></textarea>
                            <div class="flex justify-between text-xs text-slate-400 mt-1">
                                <span>Maksimal 500 karakter</span>
                                <span id="char-count" class="text-slate-400">0 / 500</span>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" id="form-submit-btn" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                Simpan Buku
                            </button>
                            <button type="button" id="reset-btn"
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                Reset
                            </button>
                        </div>

                    </form>
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 border-t pt-6 gap-4">
                        <h2 class="text-2xl font-bold text-slate-800">Daftar Buku Saat Ini (<?= $total_books; ?>)</h2>
                        <div class="relative w-full sm:w-1/2 md:w-1/3">
                            <input type="text" placeholder="Cari judul atau penulis..." id="admin-search-input" class="w-full border border-slate-300 rounded-lg py-2.5 pl-10 pr-4 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 transition-all">
                            <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-slate-400"></i>
                        </div>
                    </div>
                    <div id="admin-data-container" class="transition-opacity duration-300">
                    <?php } ?>
                    <div id="admin-book-list" class="space-y-3">
                        <?php if ($total_books > 0): ?>
                            <?php foreach ($books as $b): ?>
                                <div class="flex flex-wrap items-center justify-between bg-slate-50 p-3 rounded-md border">
                                    <div class="flex-grow">
                                        <p class="font-semibold">
                                            <?= htmlspecialchars($b['title']); ?>
                                            <span class="text-normal text-slate-600">-<?= htmlspecialchars($b['author']); ?></span>
                                        </p>
                                        <p class="text-sm text-slate-500">
                                            Rp<?= number_format($b['price'], 0, ',', '.'); ?>
                                            |Kondisi: <?= htmlspecialchars($b['book_condition']); ?>
                                            |Kategori: <?= htmlspecialchars($b['category']); ?>
                                        </p>
                                    </div>
                                    <div class="space-x-2 flex-shrink-0 mt-2 sm:mt-0">

<button 
    data-id="<?= $b['id']; ?>"
    data-book='<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>'
    class="edit-btn text-blue-500 hover:text-blue-700 px-2 py-1"
>
    <i class="fa-regular fa-pen-to-square"></i> Edit
</button>
                                    </div>
                                    <button data-id = "<?= $b['id']; ?>" data-title = "<?= htmlspecialchars($b ['title']); ?>" class="delete-btn text-red-500 hover:text-red-700 px-2 py-1"><i class="fa-solid fa-trash"></i> Hapus</button>
                                </div>
                            <?php endforeach ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($total_pages > 1) : ?>
                        <div class="flex justify-center mt-8">
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-md" aria-label="Pagination">
                                <a href="admin.php?page=<?= max(1, $page - 1) . $qs_search; ?>" class="ajax-page-link relative inline-flex items-center rounded-l-md px-3 py-2 text-slate-400 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus:z-20 focus:outline-offset-0 <?= $page <= 1 ? 'pointer-events-none opacity-50 bg-slate-100' : ''; ?>">
                                    <span class="sr-only">Previous</span>
                                    <i class="fa-solid fa-chevron-left text-sm"></i>
                                </a>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="admin.php?page=<?= $i . $qs_search; ?>" class="ajax-page-link relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $page === $i ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600' : 'text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus:z-20 focus:outline-offset-0'; ?>"><?= $i;  ?></a>
                                <?php endfor; ?>
                                <a href="admin.php?page=<?= min($total_pages, $page + 1) . $qs_search; ?>" class="ajax-page-link relative inline-flex items-center rounded-r-md px-3 py-2 text-slate-400 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus:z-20 focus:outline-offset-0 <?= $page >= $total_pages ? 'pointer-events-none opacity-50 bg-slate-100' : ''; ?>">
                                    <span class="sr-only">Next</span>
                                    <i class="fa-solid fa-chevron-right text-sm"></i>
                                </a>
                            </nav>
                        </div>
                    <?php endif ?>

                    <?php if ($is_ajax) {
                        $html = ob_get_clean();
                        header('Content-Type: application/json');
                        echo json_encode(['html' => $html, 'total' => $total_books]);
                        exit;
                    } else { ?>
                    </div>
                </div>
            </div>
        </section>
    <?php require_once "includes/footer.php";
                    }
    ?>
    <script>
        const textarea = document.getElementById('description');
        const counter = document.getElementById('char-count');
        const maxLength = parseInt(textarea.getAttribute('maxlength'));

        textarea.addEventListener('input', function() {
            const currentLength = textarea.value.length;

            // update angka
            counter.textContent = currentLength + " / " + maxLength;

            // reset warna
            counter.classList.remove('text-red-500', 'text-yellow-500', 'text-slate-400');

            // kondisi warna
            if (currentLength >= maxLength) {
                counter.classList.add('text-red-500'); // FULL → merah
            } else if (currentLength >= maxLength * 0.8) {
                counter.classList.add('text-yellow-500'); // hampir penuh
            } else {
                counter.classList.add('text-slate-400'); // normal
            }
        });
    </script>
    </body>

    </html>