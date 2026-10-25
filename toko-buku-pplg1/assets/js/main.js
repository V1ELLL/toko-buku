// Struktur aplikasi utama untuk menghindari polusi global scope
const App = {
  // --- API SERVICE ---
  api: {
    async post(url, formData) {
      try {
        const response = await fetch(url, { method: "POST", body: formData });
        return await response.json();
      } catch (error) {
        console.error("API POST Error:", error);
        App.ui.showToast("Terjadi kesalahan koneksi.", false);
        return { success: false, message: "Koneksi Gagal" };
      }
    },
    async get(url) {
      try {
        const response = await fetch(url);
        return await response.json();
      } catch (error) {
        console.error("API GET Error:", error);
        App.ui.showToast("Terjadi kesalahan koneksi.", false);
        return null;
      }
    },
  },

  // --- UI MODULE ---
  ui: {
    init() {
      this.cacheDOM();
      this.bindEvents();
    },
    cacheDOM() {
      this.mobileMenuButton = document.getElementById("mobile-menu-button");
      this.mobileMenu = document.getElementById("mobile-menu");
      this.toast = document.getElementById("toast-notification");
    },
    bindEvents() {
      if (this.mobileMenuButton) {
        this.mobileMenuButton.addEventListener("click", () =>
          this.mobileMenu.classList.toggle("hidden"),
        );
      }
    },
    showToast(message, isSuccess = true) {
      this.toast.textContent = message;
      this.toast.className = this.toast.className.replace(
        /bg-\w+-500/,
        isSuccess ? "bg-green-500" : "bg-red-500",
      );
      this.toast.classList.remove("opacity-0", "translate-y-10");
      setTimeout(() => {
        this.toast.classList.add("opacity-0", "translate-y-10");
      }, 2500);
    },
  },

  // --- BOOK DETAIL MODULE ---
  bookDetail: {
    init() {
      this.cacheDOM();
      if (!this.bookListContainer) return;
      this.bindEvents();
    },
    cacheDOM() {
      this.modal = document.getElementById("book-detail-modal");
      this.modalBody = document.getElementById("detail-modal-body");
      this.closeBtn = document.getElementById("close-detail-modal-btn");
      this.bookListContainer = document.getElementById("book-list");
    },
    bindEvents() {
      this.bookListContainer.addEventListener("click", async (e) => {
        const trigger = e.target.closest(".open-detail-trigger");
        if (trigger) {
          const bookId = trigger.dataset.id;
          const book = await App.api.get(`action/get_book_detail.php?id=${bookId}`);
          if (book) {
            this.render(book);
            this.open();
          } else {
            App.ui.showToast("Gagal memuat detail buku.", false);
          }
        }
      });
      this.closeBtn.addEventListener("click", () => this.close());
      this.modal.addEventListener("click", (e) => {
        if (e.target === this.modal) this.close();
      });
    },
    open() {
      this.modal.classList.add("active");
    },
    close() {
      this.modal.classList.remove("active");
    },
    getConditionBadge(condition) {
      switch (condition) {
        case "Seperti Baru":
          return `<span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">${condition}</span>`;
        case "Baik":
          return `<span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">${condition}</span>`;
        case "Cukup":
          return `<span class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">${condition}</span>`;
        default:
          return `<span class="bg-slate-100 text-slate-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">${condition}</span>`;
      }
    },
    render(book) {
      this.modalBody.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <img src="${book.img_url}" alt="${book.title}" class="w-full h-auto object-cover rounded-lg shadow-lg" onerror="this.onerror=null;this.src='https://placehold.co/400x600/cccccc/ffffff?text=Gambar+Rusak';">
          </div>
          <div>
            <h3 class="text-2xl md:text-3xl font-bold">${book.title}</h3>
            <p class="text-lg text-slate-600 mt-1 mb-4">oleh ${book.author}</p>
            <div class="space-y-2 mb-4">
              <p><strong>Kondisi:</strong> ${this.getConditionBadge(book.book_condition)}</p>
              <p><strong>Kategori:</strong> <span class="font-medium text-indigo-600">${book.category}</span></p>
            </div>
            <p class="text-3xl font-bold text-indigo-600 mb-4">Rp ${parseInt(book.price).toLocaleString("id-ID")}</p>
            <h4 class="font-bold text-md mb-1">Deskripsi:</h4>
            <p class="text-slate-700 text-base whitespace-pre-wrap">${book.description || "Tidak ada deskripsi."}</p>
            <div class="mt-6">
              <button data-id="${book.id}" class="add-to-cart-btn w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300">
                <i class="fa-solid fa-cart-plus mr-2"></i>Tambah ke Keranjang
              </button>
            </div>
          </div>
        </div>
      `;
    },
  },

  // --- CART MODULE ---
  cart: {
    init() {
      this.cacheDOM();
      this.bindEvents();
    },
    cacheDOM() {
      this.cartButton = document.getElementById("cart-button");
      this.cartModal = document.getElementById("cart-modal");
      this.closeCartButton = document.getElementById("close-cart-button");
      this.cartCountSpan = document.getElementById("cart-count");
      this.cartItemsContainer = document.getElementById("cart-items");
      this.cartTotalSpan = document.getElementById("cart-total");
      this.checkoutItemCountSpan = document.getElementById("checkout-item-count");
      this.checkoutButton = document.getElementById("checkout-button");
      this.selectAllCheckbox = document.getElementById("select-all-cart-items");
      this.removeSelectedBtn = document.getElementById("remove-selected-btn");
    },
    bindEvents() {
      // Satu event listener utama di document.body untuk menangani semua aksi dinamis
      document.body.addEventListener("click", async (e) => {
        // Cek jika tombol 'Tambah ke Keranjang' yang diklik
        if (e.target.closest(".add-to-cart-btn")) {
          e.preventDefault();
          const bookId = e.target.closest(".add-to-cart-btn").dataset.id;
          await this.addItem(bookId);
          return;
        }

        // Cek jika ada aksi di dalam kontainer item keranjang
        if (e.target.closest("#cart-items")) {
          const bookId = e.target.dataset.id;
          if (!bookId) return;

          const formData = new FormData();
          formData.append("book_id", bookId);

          // Logika untuk tombol +/- kuantitas
          if (e.target.matches(".quantity-change-btn")) {
            const qtySpan = e.target.parentElement.querySelector("span");
            const change = e.target.dataset.action === "increase" ? 1 : -1;
            formData.append("action", "update_quantity");
            formData.append("quantity", parseInt(qtySpan.textContent) + change);
            await App.api.post("action/handle_cart.php", formData);
            await this.updateView();
          }

          // Logika untuk checkbox per item
          if (e.target.matches(".cart-item-checkbox")) {
            formData.append("action", "toggle_select");
            await App.api.post("action/handle_cart.php", formData);
            await this.updateView();
          }
        }
      });

      // Event listener untuk tombol statis
      this.cartButton.addEventListener("click", async () => {
        await this.updateView();
        this.cartModal.classList.add("active");
      });

      this.closeCartButton.addEventListener("click", () =>
        this.cartModal.classList.remove("active"),
      );

      this.selectAllCheckbox.addEventListener("change", async (e) => {
        const isChecked = e.target.checked;
        const formData = new FormData();
        formData.append("action", "select_all");
        formData.append("is_selected", isChecked ? 1 : 0); // Mengirim 1 atau 0
        await App.api.post("action/handle_cart.php", formData);
        await this.updateView();
      });

      this.removeSelectedBtn.addEventListener("click", async () => {
        if (
          confirm("Apakah Anda yakin ingin menghapus semua item yang dipilih?")
        ) {
          const formData = new FormData();
          formData.append("action", "remove_selected");
          await App.api.post("action/handle_cart.php", formData);
          await this.updateView();
        }
      });
    },

    async addItem(bookId) {
      const formData = new FormData();
      formData.append("action", "add");
      formData.append("book_id", bookId);
      const data = await App.api.post("action/handle_cart.php", formData);
      if (data.success) {
        this.cartCountSpan.textContent = data.cart_count;
        App.ui.showToast("Buku berhasil ditambahkan!");
      } else {
        App.ui.showToast(data.message || "Gagal", false);
      }
    },

    async updateView() {
      const cartData = await App.api.get("action/handle_cart.php?action=get_view");
      if (cartData) {
        // 1. Update konten HTML dan teks informasi keranjang
        this.cartItemsContainer.innerHTML = cartData.html;
        this.cartTotalSpan.textContent = cartData.total_price_formatted;
        this.checkoutItemCountSpan.textContent = cartData.selected_item_count;
        this.cartCountSpan.textContent = cartData.cart_count;

        // 2. Aktifkan/nonaktifkan tombol checkout berdasarkan item yang dipilih
        this.checkoutButton.disabled = cartData.selected_item_count === 0;

        // 3. Hitung jumlah checkbox secara real-time dari UI
        const totalCheckboxes = this.cartItemsContainer.querySelectorAll(
          ".cart-item-checkbox",
        ).length;

        // 4. Sinkronisasi status checkbox "Pilih Semua"
        if (
          totalCheckboxes > 0 &&
          cartData.selected_item_count === totalCheckboxes
        ) {
          this.selectAllCheckbox.checked = true;
        } else {
          this.selectAllCheckbox.checked = false;
        }
      }
    },
  },

  // --- CHECKOUT MODULE ---
  checkout: {
    init() {
      this.cacheDOM();
      if (!this.checkoutModal) return; // elemen tidak ada, skip
      this.bindEvents();
    },
    cacheDOM() {
      this.checkoutModal = document.getElementById("checkout-modal");
      this.checkoutButton = document.getElementById("checkout-button");
      this.closeCheckoutModalBtn = document.getElementById("close-checkout-modal-btn");
      this.summaryContainer = document.getElementById("checkout-summary");
      this.totalContainer = document.getElementById("checkout-total");
      this.whatsappBtn = document.getElementById("whatsapp-confirmation-btn");
      this.WHATSAPP_NUMBER = "6282121509600";
    },
    bindEvents() {
      // Tombol Tutup Modal
      this.closeCheckoutModalBtn.addEventListener("click", () => this.close());

      // Tombol Lanjutkan ke Pembayaran — pakai delegation karena tombol bisa disabled
      document.body.addEventListener("click", async (e) => {
        if (!e.target.closest("#checkout-button")) return;
        const btn = document.getElementById("checkout-button");
        if (btn && btn.disabled) return;

        try {
          const response = await fetch("action/handle_cart.php?action=get_checkout_data");
          const checkoutData = await response.json();

          if (checkoutData && checkoutData.items.length > 0) {
            this.populateModal(checkoutData);
            App.cart.cartModal.classList.remove("active");
            this.open();
          } else {
            App.ui.showToast("Pilih setidaknya satu buku untuk checkout.", false);
          }
        } catch (error) {
          console.error("Gagal mengambil data checkout:", error);
          App.ui.showToast("Gagal memproses checkout.", false);
        }
      });

      // Tombol Konfirmasi WA
      this.whatsappBtn.addEventListener("click", () => {
        setTimeout(async () => {
          let body = new FormData();
          body.append("action", "remove_selected");
          await fetch("action/handle_cart.php", { method: "POST", body });

          await App.cart.updateView();
          this.close();
          App.ui.showToast("Pesanan Anda sedang diproses!");
        }, 1000);
      });
    },
    open() {
      this.checkoutModal.classList.add("active");
    },
    close() {
      this.checkoutModal.classList.remove("active");
    },
    populateModal(data) {
      // 1. Bersihkan ringkasan lama
      this.summaryContainer.innerHTML = "";

      // 2. Inisialisasi teks awal untuk template WhatsApp
      let message = `Halo, saya mau konfirmasi pesanan buku dari Toko Buku Sekolah Bekas:\n\n`;

      // 3. Gabungkan looping untuk merender UI sekaligus menyusun pesan WA
      data.items.forEach((item) => {
        // Render item ke Modal Ringkasan HTML
        const itemElement = document.createElement("p");
        itemElement.className = "text-sm text-slate-600";
        itemElement.textContent = `- ${item.title} (${item.quantity}x)`;
        this.summaryContainer.appendChild(itemElement);

        // Tambahkan item ke baris teks WhatsApp
        message += `- ${item.title} (${item.quantity}x)\n`;
      });

      // 4. Update total bayar di UI
      this.totalContainer.textContent = data.total_formatted;

      // 5. Finalisasi string pesan WhatsApp
      message += `\nTotal Pembayaran: *${data.total_formatted}*\n\n`;
      message += `Saya akan segera melakukan pembayaran dan mengirimkan buktinya. Terima kasih!`;

      // 6. Encode pesan dan pasang ke tombol WA
      const encodedMessage = encodeURIComponent(message);
      this.whatsappBtn.href = `https://api.whatsapp.com/send?phone=${this.WHATSAPP_NUMBER}&text=${encodedMessage}`;
    },
  },

  // --- ADMIN MODULE ---
  admin: {
    init() {
      this.adminForm = document.getElementById("admin-form");
      if (!this.adminForm) return;

      this.formSubmitBtn = document.getElementById("form-submit-btn");
      this.bookIdInput = document.getElementById("book-id");
      this.searchInput = document.getElementById("admin-search-input");
      this.dataContainer = document.getElementById("admin-data-container");
      this.totalBooksSpan = document.getElementById("admin-total-books");

      this.bindEvents();
    },
    bindEvents() {
      if (this.searchInput && this.dataContainer) {
        let debounceTimer;
        this.searchInput.addEventListener("input", (e) => {
          clearTimeout(debounceTimer);
          this.dataContainer.style.opacity = "0.5";
          debounceTimer = setTimeout(async () => {
            const searchTerm = e.target.value;
            const response = await App.api.get(
              `admin.php?ajax=true&search=${encodeURIComponent(searchTerm)}`,
            );
            if (response && response.html !== undefined) {
              this.dataContainer.innerHTML = response.html;
              if (this.totalBooksSpan)
                this.totalBooksSpan.textContent = response.total;
            }
            this.dataContainer.style.opacity = "1";
          }, 350);
        });

        this.dataContainer.addEventListener("click", async (e) => {
          const pageLink = e.target.closest(".ajax-page-link");
          if (pageLink) {
            e.preventDefault();
            this.dataContainer.style.opacity = "0.5";
            const url = pageLink.getAttribute("href") + "&ajax=true";
            const response = await App.api.get(url);
            if (response && response.html !== undefined) {
              this.dataContainer.innerHTML = response.html;
              window.scrollTo({
                top: this.dataContainer.offsetTop - 120,
                behavior: "smooth",
              });
            }
            this.dataContainer.style.opacity = "1";
          }
        });
      }

      if (this.dataContainer) {
        this.dataContainer.addEventListener("click", (e) => {
          const editButton = e.target.closest(".edit-btn");
          if (editButton) this.populateForm(editButton);

          const deleteButton = e.target.closest(".delete-btn");
          if (deleteButton) this.handleDelete(deleteButton);
        });
      }

      this.adminForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        this.formSubmitBtn.textContent = "Menyimpan...";
        this.formSubmitBtn.disabled = true;

        const formData = new FormData(this.adminForm);
        const url = this.adminForm.action;
        const result = await App.api.post(url, formData);

        if (result.success) {
          App.ui.showToast("Data buku berhasil disimpan!");
          setTimeout(() => window.location.reload(), 1500);
        } else {
          App.ui.showToast(result.message || "Gagal menyimpan data.", false);
          this.formSubmitBtn.textContent = this.bookIdInput.value
            ? "Update Buku"
            : "Tambah Buku";
          this.formSubmitBtn.disabled = false;
        }
      });

      const cancelBtn = document.getElementById("reset-btn");
      if (cancelBtn) {
        cancelBtn.addEventListener("click", () => this.resetForm());
      }
    },
    async handleDelete(deleteButton) {
      const bookId = deleteButton.dataset.id;
      const bookTitle = deleteButton.dataset.title;
      if (confirm(`Apakah Anda yakin ingin menghapus buku "${bookTitle}"?`)) {
        const formData = new FormData();
        formData.append("id", bookId);
        const result = await App.api.post("action/delete_book.php", formData);
        if (result.success) {
          App.ui.showToast("Buku berhasil dihapus!");
          deleteButton.closest(".flex-wrap").remove();
          if (this.totalBooksSpan) {
            let currentTotal = parseInt(this.totalBooksSpan.textContent) || 0;
            if (currentTotal > 0) {
              this.totalBooksSpan.textContent = currentTotal - 1;
            }
          }
        } else {
          App.ui.showToast(result.message || "Gagal menghapus buku.", false);
        }
      }
    },
    populateForm(editButton) {
      const bookData = JSON.parse(editButton.dataset.book);
      this.bookIdInput.value = bookData.id;
      document.getElementById("book_title").value = bookData.title;
      document.getElementById("author").value = bookData.author;
      document.getElementById("price").value = bookData.price;
      document.getElementById("book-condition").value = bookData.book_condition;
      document.getElementById("category").value = bookData.category;
      document.getElementById("description").value = bookData.description;
      document.getElementById("img-url").value = bookData.img_url;
      this.formSubmitBtn.textContent = "Update Buku";
      window.scrollTo({
        top: this.adminForm.offsetTop - 20,
        behavior: "smooth",
      });
    },
    resetForm() {
      this.adminForm.reset();
      this.bookIdInput.value = "";
      this.formSubmitBtn.textContent = "Tambah Buku";
    },
  },

  // --- BOOK SEARCH MODULE ---
  bookSearchModule: {
    state: {
      searchTerm: "",
      category: "",
      page: 1,
    },
    init() {
      this.cacheDOM();
      if (!this.searchInput || !this.bookListContainer) return; // tidak ada di halaman ini
      this.bindEvents();
      this.fetchBooks();
    },
    cacheDOM() {
      this.searchInput = document.getElementById("search-input");
      this.categoryList = document.getElementById("category-list");
      this.bookListContainer = document.getElementById("book-list");
      this.paginationContainer = document.getElementById("pagination-controls");
      this.filterInfo = document.getElementById("filter-info");
    },
    bindEvents() {
      let searchTimeout;
      this.searchInput.addEventListener("keyup", (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          this.state.searchTerm = e.target.value;
          this.state.page = 1;
          this.fetchBooks();
        }, 300);
      });

      this.categoryList.addEventListener("click", (e) => {
        const categoryCard = e.target.closest(".category-card");
        if (categoryCard) {
          const category = categoryCard.dataset.category;
          this.state.category =
            this.state.category === category ? "" : category;
          this.state.page = 1;
          this.fetchBooks();
          this.categoryList
            .querySelectorAll(".category-card")
            .forEach((c) => c.classList.remove("active"));
          if (this.state.category) {
            categoryCard.classList.add("active");
          }
        }
      });

      this.paginationContainer.addEventListener("click", (e) => {
        e.preventDefault();
        const pageButton = e.target.closest(".page-link");
        if (
          pageButton &&
          !pageButton.parentElement.classList.contains("disabled") &&
          !pageButton.parentElement.classList.contains("active")
        ) {
          this.state.page = parseInt(pageButton.dataset.page);
          this.fetchBooks();
        }
      });
    },
    async fetchBooks() {
      const { searchTerm, category, page } = this.state;
      const url = `actions/api_search.php?search=${encodeURIComponent(
        searchTerm,
      )}&category=${encodeURIComponent(category)}&page=${page}`;
      const response = await App.api.get(url);
      if (response && response.success) {
        this.renderBooks(response.data);
        this.renderPagination(response.pagination);
        this.updateFilterInfo();
      }
    },
    renderBooks(books) {
      this.bookListContainer.innerHTML = "";
      if (books.length === 0) {
        this.bookListContainer.innerHTML = `<p class="col-span-full text-center text-slate-500">Oops! Buku yang Anda cari tidak ditemukan.</p>`;
        return;
      }
      books.forEach((book) => {
        const bookCard = `
          <div class="book-card bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
            <div class="open-detail-trigger" data-id="${book.id}">
              <img src="${book.img_url || "https://placehold.co/300x450/cccccc/ffffff?text=Buku"}" alt="${book.title}" class="w-full h-64 object-cover">
            </div>
            <div class="p-4 flex-grow">
              <h3 class="text-lg font-bold mb-1 truncate">${book.title}</h3>
              <p class="text-slate-600 mb-3 text-sm">${book.author}</p>
            </div>
            <div class="p-4 pt-0">
              <p class="text-xl font-bold text-indigo-600 mb-3">Rp ${parseInt(book.price).toLocaleString("id-ID")}</p>
              <button data-id="${book.id}" class="add-to-cart-btn w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg">
                <i class="fa-solid fa-cart-plus mr-2"></i>Keranjang
              </button>
            </div>
          </div>`;
        this.bookListContainer.innerHTML += bookCard;
      });
    },
    renderPagination({ currentPage, totalPages }) {
      this.paginationContainer.innerHTML = "";
      if (totalPages <= 1) return;
      let paginationHTML = "";
      paginationHTML += `<li class="${currentPage === 1 ? "disabled" : ""}"><a href="#" class="page-link" data-page="${currentPage - 1}">&laquo;</a></li>`;
      for (let i = 1; i <= totalPages; i++) {
        paginationHTML += `<li class="${i === currentPage ? "active" : ""}"><a href="#" class="page-link" data-page="${i}">${i}</a></li>`;
      }
      paginationHTML += `<li class="${currentPage === totalPages ? "disabled" : ""}"><a href="#" class="page-link" data-page="${currentPage + 1}">&raquo;</a></li>`;
      this.paginationContainer.innerHTML = `<ul class="flex space-x-1">${paginationHTML}</ul>`;
    },
    updateFilterInfo() {
      if (this.state.category) {
        this.filterInfo.innerHTML = `Menampilkan kategori: <strong>${this.state.category}</strong>`;
      } else {
        this.filterInfo.innerHTML = "";
      }
    },
  },

  // --- SMOOTH SCROLL ---
  smoothScroll: {
    init() {
      document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
          const href = this.getAttribute("href");
          if (href.length > 1 && document.querySelector(href)) {
            e.preventDefault();
            document.querySelector(href).scrollIntoView({
              behavior: "smooth",
            });
          }
        });
      });
    },
  },

  // --- INIT UTAMA ---
  init() {
    this.ui.init();
    this.cart.init();
    this.admin.init();
    this.bookDetail.init();
    this.bookSearchModule.init();
    this.checkout.init();
    this.smoothScroll.init();
  },
};

// Jalankan aplikasi setelah semua HTML dimuat
document.addEventListener("DOMContentLoaded", () => App.init());