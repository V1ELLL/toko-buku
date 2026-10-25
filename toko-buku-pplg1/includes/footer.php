</main>
    <footer class="bg-slate-800 text-white pt-8">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-t border-slate-700 mt-4 pt-6 pb-6 text-center text-slate-400 flex flex-col gap-4">
                
                <p>JANGAN BERKEMBANG BIAK SEBELUM BERKEMBANG BAIK!!!</p>
                
                <hr class="border-slate-700 w-full" />
                
                <p>
                    &copy; 2025 BukuSekolahBekas Dibuat Dengan 
                    <i class="fa-solid fa-robot text-blue-500"></i> Oleh Repana Pirdaus   
                </p>
                
            </div>
        </div>
    </footer>

    <!-- Modal Detail Buku -->
    <div id="book-detail-modal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 invisible opacity-0">
        <div class="modal-content bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-1/2 max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-xl font-semibold">Detail Produk</h3>
                <button id="close-detail-modal-btn" class="text-slate-500 hover:text-slate-800 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto" id="detail-modal-body"></div>
        </div>
    </div>

    <!-- Modal Keranjang Belanja -->
    <div id="cart-modal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 invisible opacity-0">
        <div id="cart-modal-content" class="modal-content bg-white rounded-lg shadow-xl w-11/12 md:w-2/3 lg:w-1/2 max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-xl font-semibold">Keranjang Belanja Anda</h3>
                <button id="close-cart-button" class="text-slate-500 hover:text-slate-800">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>
            <div class="flex items-center justify-between p-4 border-b bg-slate-50">
                <label for="select-all-cart-items" class="flex items-center space-x-2 text-sm font-medium">
                    <input type="checkbox" id="select-all-cart-items" class="rounded border-slate-400 text-indigo-600 focus:ring-indigo-500">
                    <span>Pilih Semua</span>
                </label>
                <button id="remove-selected-btn" class="text-red-500 hover:text-red-700 text-sm font-semibold">
                    <i class="fa-solid fa-trash-can mr-1"></i>Hapus yang Dipilih
                </button>
            </div>
            <div id="cart-items" class="p-6 overflow-y-auto flex-grow">
                <p id="cart-empty-message" class="text-slate-500 text-center">Keranjang Anda masih kosong.</p>
            </div>
            <div class="p-4 border-t bg-slate-50 mt-auto">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-semibold">Total Dipilih:</span>
                    <span id="cart-total" class="text-xl font-bold text-indigo-600">Rp 0</span>
                </div>
                <button id="checkout-button" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition duration-300 disabled:bg-slate-400 disabled:cursor-not-allowed">
                    Lanjutkan ke Pembayaran (<span id="checkout-item-count">0</span> item)
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Checkout -->
    <div id="checkout-modal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 invisible opacity-0">
        <div class="modal-content bg-white rounded-lg shadow-xl w-11/12 md:w-1/2 lg:w-1/3 max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-xl font-semibold">Konfirmasi Pesanan</h3>
                <button id="close-checkout-modal-btn" class="text-slate-500 hover:text-slate-800">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto">
                <h4 class="font-bold text-lg mb-2">Ringkasan Pesanan</h4>
                <div id="checkout-summary" class="space-y-2 border-b pb-4 mb-4"></div>
                <div class="flex justify-between items-center font-bold text-lg mb-4">
                    <span>Total Pembayaran:</span>
                    <span id="checkout-total" class="text-indigo-600">Rp 0</span>
                </div>
                <h4 class="font-bold text-lg mb-2">Instruksi Pembayaran</h4>
                <div class="bg-slate-100 p-4 rounded-lg text-sm space-y-3">
                    <p>1. Lakukan pembayaran ke salah satu rekening berikut:</p>
                    <div class="pl-4">
                        <p><strong>BCA:</strong> 1234567890 (a/n Toko Buku Bekas)</p>
                        <p><strong>GoPay/OVO/DANA:</strong> isi dengan nomor wa masing-masing</p>
                    </div>
                    <p>2. Atau scan kode QRIS di bawah ini:</p>
                    <img src="assets/images/qris.png" alt="QRIS Code" class="mx-auto rounded-md">
                    <p>3. Klik tombol di bawah untuk konfirmasi pesanan Anda melalui WhatsApp dan kirimkan bukti transfer.</p>
                </div>
            </div>
            <div class="p-4 border-t bg-slate-50">
                <a id="whatsapp-confirmation-btn" href="#" target="_blank" class="block w-full text-center bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-lg transition duration-300">
                    <i class="fab fa-whatsapp mr-2"></i>Konfirmasi Pesanan via WhatsApp
                </a>
            </div>
        </div>
    </div>

    <!-- Toast Notifikasi -->
    <div id="toast-notification" class="fixed bottom-5 right-5 bg-green-500 text-white py-2 px-4 rounded-lg shadow-lg opacity-0 transform translate-y-10 transition-all duration-300">
        Pesan Notifikasi.
    </div>
    
    <script src="assets/js/main.js" defer></script>
</body>
</html>