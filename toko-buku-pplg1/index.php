<?php
require_once "config/config.php";
require_once "includes/header.php";


$search_terms = $_GET['search'] ??'';
$category_filter = $_GET['category'] ??'';

$sql = "SELECT * FROM books";
$conditions = [];
$params = [];
$types = '';

if (!empty($search_terms)) {
    $conditions[] = "(title LIKE ? OR author LIKE ?)";
    $search_like = "%". $search_terms ."%";
    $types .= 'ss';
    array_push($params, $search_like, $search_like);
    
    
}

if (!empty($category_filter)) { 
    $conditions[] = "category = ?";
    $types .= 's';
    $params[] = $category_filter;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}


$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);  
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$books =$result->fetch_all(MYSQLI_ASSOC);
$stmt->close(); 

function getConditionBadge($condition) {
    switch ($condition) {
        case "Seperti Baru":
            return '<span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">Seperti Baru</span>';
    }
    switch ($condition) {
        case "Baik":
            return '<span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">Baik</span>';
    }
    switch ($condition) {
        case "Cukup Baik":
            return '<span class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">Cukup Baik</span>';
    }
}


$category = ["Matematika","Fisika","Kimia","Biologi","Sejarah","Bahasa"];
$category_icons = [
    "Matematika"    => "fa-calculator text-indigo-500",
    "Fisika"    => "fa-atom text-indigo-500",
    "Kimia"     => "fa-flask-vial text-amber-500",
    "Biologi"   => "fa-dna text-sky-500",
    "Sejarah"   => "fa-scroll text-rose-500",
    "Bahasa"    => "fa-language text-purple-500",
]
?>

<section class="hero-section text-white py-20 sm:py-32">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-4">Cari Buku Sekolah Bekas di Sini!</h1>
        <p class="text-lg md:text-xl max-w-3x1 mx-auto mb-8">Dapatkan Buku Pelajaran bekas layak pakai dengan harga lebih terjangkau. Hemat dan tetap berprestasi.</p>
        <a href="http://" class="bg-indigo-600 hover:bg-indigo-700 text white font-bold py-3 px-8 rounded-full text-lg transition duration-300">Lihat Koleksi Buku</a>
    </div>
</section>

<section class="py-12 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <form action="index.php#featured-books" method="get"  class="relative">
                <input type="text" name="search" value="<?= htmlspecialchars($search_terms); ?>" class="w-full py-3 pl-4 pr-12 text-slate-700 border border-slate-300 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Cari Buku Pelajaran misal: Buku Matematika">
                <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-4">
                    <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                </button>
            </form>
        </div>
    </div>
</section>

<section id="category" class="py-16 bg-slate-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-10">Jelajahi Berdasarkan Mata Pelajaran</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 text-center">
            <?php foreach ($category as $cat): ?>
            <a href="index.php?category=<?= urlencode($cat); ?>#featured-books" class="category-card bg-white p-6 rounded-lg shadow-sm hover:shadow-lg transition-all">
                <i class="fa-solid <?= $category_icons[$cat]; ?> text-4xl mb-3"></i>
                <h3 class="font-semibold"><?= $cat; ?></h3>
            </a>
            <?php endforeach ?>
        </div>
    </div>
</section>

<section id="featured-books" class="py-16">
    <div class="container mx-auto px-4 sm:px6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold mb-2">Koleksi Buku Bekas Sekolah</h2>
            <div class="h-8 mb-8" id="filter-info">
                <?php if (!empty($category_filter) || !empty($search_terms)): ?>
                <span class="text-slate-600">
                    menampilkan hasil untuk 
                    <?php if (!empty($search_terms));
                        echo " pencarian <strong>" . 
                        htmlspecialchars($search_terms) . 
                        "<strong>"; 
                    ?>
                    <?php if (!empty($category_filter));
                        echo " kategori <strong>" . 
                        htmlspecialchars($category_filter) . 
                        "<strong>"; 
                    ?>
                </span>
                <a href="" class="ml-2 text-indigo-600 hover:underline text-sm font-semibold">Tampilkan Semua</a>
                <?php endif ?>
            </div>
        </div>
        <div id="book-list" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
    <?php if (count($books) > 0): ?>
        <?php foreach ($books as $b): ?>
        <div class="book-card bg-white rounded-lg shadow-md overflow-hidden flex flex-col border border-slate-100">
            
            <div class="relative cursor-pointer open-detail-trigger" data-id="<?= $b['id']; ?>">
                <img src="<?= htmlspecialchars($b['img_url']); ?>" alt="<?= htmlspecialchars($b['title']); ?>" class="w-full h-auto object-cover">
                <div class="absolute top-2 left-2">
                    <?= getConditionBadge($b['book_condition']); ?>
                </div>
            </div>
            
            <div class="p-4 flex flex-col flex-grow">
                <h3 class="text-lg font-bold mb-1 truncate cursor-pointer open-detail-trigger" 
                    data-id="<?= $b['id']; ?>" 
                    title="<?= htmlspecialchars($b['title']); ?>">
                    <?= htmlspecialchars($b['title']); ?>
                </h3>
                
                <p class="text-slate-600 text-sm mb-4">
                    <?= htmlspecialchars($b['author']); ?>
                </p>
                
                <div class="mt-auto">
                    <p class="text-xl font-bold text-indigo-600 mb-3">
                        Rp<?= number_format($b['price'], 0, ',', '.'); ?>
                    </p>
                    <button data-id="<?php echo $b['id']; ?>" class="add-to-cart-btn w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                        <i class="fa-solid fa-cart-shopping mr-2"></i>Tambah Ke Keranjang
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center text-slate-500 mt-8">
                    <p class="text-xl">OPPS! BUKU YANG ANDA CARI TIDAK ADA!!!!.</p>
                    <p>Coba Hapus Filter Atau Gunakan Kata Kunci Lain</p>
                </div>
            <?php endif ?>
        </div>
    </div>
</section>

<?php
require_once "includes/footer.php";
?>