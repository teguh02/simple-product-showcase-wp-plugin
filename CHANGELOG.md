# Changelog

Semua perubahan penting pada plugin ini akan didokumentasikan dalam file ini.

Format berdasarkan [Keep a Changelog](https://keepachangelog.com/id/1.0.0/),
dan plugin ini mengikuti [Semantic Versioning](https://semver.org/lang/id/).



## [1.6.35] - 2025-12-28

### Fixed
- **CRITICAL FIX**: Memperbaiki penyimpanan data yang tidak bekerja di Gutenberg Block Editor
  - Menambahkan multiple filters untuk memastikan Block Editor benar-benar dinonaktifkan untuk `sps_product`:
    - `use_block_editor_for_post_type` dengan priority 100
    - `use_block_editor_for_post` dengan priority 100
    - `gutenberg_can_edit_post_type` dengan priority 100
    - `gutenberg_can_edit_post` dengan priority 100
  - Menambahkan fungsi `disable_block_editor_for_post()` untuk menangani filter berbasis post object
  - Menambahkan dukungan REST API untuk meta fields sebagai backup jika Gutenberg masih aktif
  - Mendaftarkan meta fields (`_sps_product_price_numeric`, `_sps_product_price_discount`, `_sps_product_weight`, `_sps_product_price`) via `register_post_meta()` dengan `show_in_rest => true`
  - Menambahkan hook `save_post_sps_product` untuk menyimpan data ke kolom custom wp_posts dari REST API
  - Sekarang data tersimpan dengan benar baik di Classic Editor maupun Gutenberg

## [1.6.34] - 2025-12-28

### Fixed
- Memperbaiki fungsi penyimpanan data di meta box "Product Price"
  - Menambahkan cek post type di awal fungsi `save_product_meta()` untuk memastikan hanya post type `sps_product` yang diproses
  - Menghapus logic yang menghapus meta jika field kosong, sekarang hanya menyimpan jika field ada di form
  - Menambahkan cek untuk mencegah duplikasi registrasi meta box di fallback function
  - Memperjelas priority pada hook `save_post` untuk memastikan fungsi save terpanggil dengan benar
  - Data sekarang tersimpan dengan benar setelah menekan tombol Save

## [1.6.33] - 2025-12-28

### Removed
- Menghapus meta box "WhatsApp Settings" dari halaman edit/tambah produk
  - Pengaturan WhatsApp sekarang hanya tersedia di halaman Button Configuration (Products → Configuration)
  - Semua pengaturan WhatsApp (nomor, pesan, mode button) hanya bisa diatur di halaman konfigurasi
  - Menyederhanakan antarmuka admin dengan memusatkan semua pengaturan button di satu tempat
  - Hanya meta box "Product Price" yang tersisa di halaman edit produk dengan 3 inputan: Harga Normal, Harga Diskon Coret, dan Berat Produk

## [1.6.32] - 2025-12-28

### Fixed
- Menambahkan fallback registrasi meta box "Product Price" di file utama plugin untuk memastikan inputan selalu muncul
  - Fungsi `register_fallback_meta_boxes()` ditambahkan sebagai cadangan jika registrasi dari class SPS_CPT gagal
  - Fungsi `fallback_product_price_meta_box()` dan `fallback_product_whatsapp_meta_box()` ditambahkan untuk menampilkan inputan
  - Memastikan meta box "Product Price" dengan 3 inputan (Harga Normal, Harga Diskon Coret, Berat Produk) selalu muncul di sidebar kanan halaman edit/tambah produk
  - Fallback akan menggunakan method dari class SPS_CPT jika tersedia, jika tidak akan menggunakan implementasi fallback

## [1.6.31] - 2025-12-28

### Changed
- Memperbaiki label inputan di meta box "Product Price" menjadi lebih jelas dalam bahasa Indonesia
  - "Harga Normal": Input untuk harga normal produk (disimpan ke kolom `price` di tabel `wp_posts`)
  - "Harga Diskon Coret": Input untuk harga diskon (jika diisi, harga normal akan dicoret di tampilan website)
  - "Berat Produk (dalam gram)": Input untuk berat produk (disimpan ke kolom `weight` di tabel `wp_posts`)
  - Semua inputan tetap tersimpan ke kolom database yang sudah ada sebelumnya
  - Field "Price (Display)" tetap ada untuk kompatibilitas namun disembunyikan dari tampilan

## [1.6.30] - 2025-12-27

### Changed
- Cookie info produk sekarang hanya satu (sps_product_info), selalu replace dengan produk terakhir yang dilihat, tidak ada lagi cookie per produk ID.

## [1.6.28] - 2025-12-27

### Changed
- Cookie info produk sekarang disimpan sebagai base64 JSON agar proses decode cukup sekali

## [1.6.27] - 2025-12-27

### Changed
- Section `weight` pada shortcode `[sps_detail_products]` kini default ke tag `p` saat `style` tidak diisi atau bernilai `grid`, sehingga ukuran font tidak terlalu besar

## [1.6.26] - 2025-12-27

### Changed
- Penyimpanan cookie info produk dipindahkan ke PHP (server-side) untuk memastikan cookie tersedia tanpa bergantung pada JavaScript
- Fallback handler `[sps_detail_products]` kini mendukung `section="category"` agar tidak muncul error "Invalid section"

## [1.6.25] - 2025-12-27

### Added
- Section baru `category` pada shortcode `[sps_detail_products section="category"]` untuk menampilkan kategori dan sub kategori produk (format: Kategori > Subkategori jika ada)
- Setiap section yang ditampilkan oleh `[sps_detail_products]` kini otomatis disimpan ke cookie (localStorage) per produk, sehingga plugin/fitur lain dapat membaca data produk yang sedang dilihat user

### Changed
- Tidak ada perubahan pada fungsionalitas lama, hanya penambahan fitur di atas

## [1.6.24] - 2025-12-24

### Added
- Menambahkan field harga diskon (Price Discount) untuk produk
  - Field "Price Discount (Numeric) - Rp:" muncul di meta box "Product Price" pada halaman tambah dan edit produk
  - Input tipe number dengan validasi untuk nilai positif (min="0" step="1")
  - Data price discount disimpan di post_meta dengan key `_sps_product_price_discount`
  - Default value: Harga asli 100.000 dan harga diskon 75.000 untuk produk yang belum memiliki harga

### Changed
- Section `price` pada shortcode `[sps_detail_products]` sekarang menampilkan harga asli dan harga diskon
  - Jika ada harga diskon: Harga asli ditampilkan dengan strikethrough (garis coret) dan harga diskon ditampilkan di sampingnya
  - Harga asli menggunakan class `sps-product-price-original` dengan data attribute `data-price`
  - Harga diskon menggunakan class `sps-product-price-discount` dengan data attribute `data-price`
  - Warna harga diskon: orange (#f56c2d) dengan font-weight bold
  - Jika tidak ada diskon: Hanya menampilkan harga asli saja
  - Default value otomatis disimpan ke database saat produk diakses jika belum ada
  - Format tampilan: "Rp 100.000" (coret) "Rp 75.000" (orange, bold)

## [1.6.23] - 2025-12-24

### Changed
- Section `weight` pada shortcode `[sps_detail_products]` sekarang memiliki default value
  - Jika berat produk belum diatur di database, secara otomatis akan diset ke **20 kg (20.000 gram)**
  - Nilai default akan otomatis disimpan ke post_meta (`_sps_product_weight`) dan kolom database (`weight` di tabel `wp_posts` jika ada)
  - Ketika produk tanpa berat diakses, nilai default akan langsung disimpan sehingga tidak perlu input manual
  - Format tampilan tetap: "20.000 gram"

## [1.6.22] - 2025-12-24

### Added
- Menambahkan section `price` dan `weight` pada shortcode `[sps_detail_products]`
  - Section `price`: Menampilkan harga produk dengan heading tag (h1, h2, h3, h4, h5), default h3
    - Class selector: `sps-product-detail-price` untuk scraping
    - Data attribute: `data-price` berisi nilai numeric untuk scraping
    - Mendukung style parameter seperti section title
  - Section `weight`: Menampilkan berat produk dengan heading tag (h1, h2, h3, h4, h5), default h3
    - Class selector: `sps-product-detail-weight` untuk scraping
    - Data attribute: `data-weight` berisi nilai dalam gram untuk scraping
    - Format tampilan: "X.XXX gram" (contoh: "500 gram" atau "1.500 gram")
  - Usage: `[sps_detail_products section="price" style="h3"]` atau `[sps_detail_products section="weight" style="h3"]`

### Changed
- Update dokumentasi untuk menambahkan section `price` dan `weight` di daftar available sections
- Update contoh penggunaan complete product detail page layout untuk menyertakan section price dan weight

## [1.6.21] - 2025-12-24

### Added
- Menambahkan field harga produk numeric untuk perhitungan total harga
  - Field "Price (Numeric) - Rp:" muncul di meta box "Product Price" pada halaman tambah dan edit produk
  - Input tipe number dengan validasi untuk nilai positif (min="0" step="1")
  - Data price numeric disimpan di post_meta dengan key `_sps_product_price_numeric`
  - Jika kolom `price` ada di tabel `wp_posts`, data juga disimpan ke kolom tersebut
  - Field "Price (Display)" tetap ada untuk menampilkan harga dengan format teks seperti "Rp 100,000"

### Changed
- Menambahkan mekanisme pengecekan dan pembuatan kolom `price` otomatis di tabel `wp_posts`
  - Fungsi `check_and_create_price_column()` mengecek apakah kolom sudah ada
  - Jika belum ada, kolom akan dibuat otomatis dengan tipe `DECIMAL(15,2) UNSIGNED DEFAULT 0.00`
  - Menggunakan cache option (`sps_price_column_exists`) untuk menghindari query berulang
  - Dipanggil via hook `admin_init` untuk memastikan database sudah siap

## [1.6.20] - 2025-12-24

### Added
- Menambahkan field berat produk (Weight) dalam satuan gram
  - Field "Weight (gram)" muncul di meta box "Product Price" pada halaman tambah dan edit produk
  - Input tipe number dengan validasi untuk nilai positif
  - Data weight disimpan di post_meta dengan key `_sps_product_weight`
  - Jika kolom `weight` ada di tabel `wp_posts`, data juga disimpan ke kolom tersebut

### Changed
- Menambahkan mekanisme pengecekan dan pembuatan kolom `weight` otomatis di tabel `wp_posts`
  - Fungsi `check_and_create_weight_column()` mengecek apakah kolom sudah ada
  - Jika belum ada, kolom akan dibuat otomatis dengan tipe `INT(11) UNSIGNED DEFAULT 0`
  - Menggunakan cache option untuk menghindari query berulang

## [1.6.19] - 2025-12-01

### Fixed
- Pencarian sekarang hanya mencari di judul/nama produk saja, tidak di deskripsi/konten
- Hasil pencarian lebih akurat dan tepat sasaran
- Mengubah semua logika pencarian di `products_shortcode()`, `products_sub_category_shortcode()`, dan `ajax_search_products()` untuk hanya mencari di `post_title`

## [1.6.18] - 2025-12-01

### Fixed
- Perbaikan akurasi pencarian produk
- Pencarian sekarang hanya mencari di title dan content produk, tidak di taxonomy terms
- Mengubah logika pencarian dari WordPress native search (`'s'` parameter) ke filter manual
- Hasil pencarian tidak lagi menampilkan produk dari kategori yang namanya mengandung kata kunci

## [1.6.17] - 2025-11-24

### Fixed
- Increment versi plugin

## [1.6.16] - 2025-11-24

### Fixed
- Menghapus duplicate PHP tag yang menyebabkan parse error di line 1431

## [1.6.15] - 2025-11-24

### Fixed
- Menambahkan CSS lengkap untuk product card di `products_sub_category_shortcode`
- Menghapus inline styles dan menggunakan CSS class untuk product grid
- Menyalin CSS dan HTML structure yang sama persis dari `sps_products` ke `sps_products_sub_category`
- Memastikan tampilan search results menggunakan card design yang konsisten
- Menghapus duplicate columns validation

## [1.6.14] - 2025-11-24

### Changed
- Menggunakan template product card yang sama untuk category dan query search
- Menambahkan CSS untuk product card template di `products_sub_category` shortcode

## [1.6.13] - 2025-11-24

### Fixed
- Update search results untuk menggunakan proper product card layout dengan Detail button

## [1.6.12] - 2025-11-24

### Added
- Menambahkan query search functionality ke `sps_products_sub_category` shortcode
- Search bar sekarang dapat mencari produk bahkan ketika tidak ada kategori yang dipilih

## [1.6.11] - 2025-11-24

### Changed
- Mengubah search bar dari div dengan button menjadi HTML form dengan GET method
- Mengganti search icon dengan tombol "Cari" di sebelah kanan search bar

### Fixed
- Menambahkan responsive CSS untuk search button
- Memperbaiki URL parameter handling untuk search button
- Memastikan form submission bekerja dengan benar

## [1.6.10] - 2025-11-24

### Added
- Search bar sekarang selalu terlihat, bahkan sebelum memilih kategori apapun
- Ketika tidak ada kategori yang dipilih, search akan mencari di semua produk

## [1.6.9] - 2025-11-23

### Fixed
- Memperbaiki autocomplete search bar
- Memperbaiki fungsi Enter key untuk menambahkan parameter `?query=` ke URL
- Menambahkan kemampuan klik icon kaca pembesar sama seperti tombol Enter

## [1.6.8] - 2025-11-23

### Added
- Menambahkan search bar dengan autocomplete untuk category filtering
- Search bar muncul ketika parameter `?category=` ada di URL
- Autocomplete menampilkan produk dengan gambar, judul, dan kategori
- Menambahkan parameter `?query=` ke URL ketika Enter ditekan atau icon diklik
- AJAX endpoint `sps_search_products` untuk autocomplete

## [1.6.7] - 2025-11-05

### Fixed
- Mencegah produk duplikat di `sps_random_products` shortcode
- Mengimplementasikan tracking `$selected_product_ids` untuk memastikan semua produk unik
- Menambahkan `post__not_in` di `WP_Query` untuk mengecualikan produk yang sudah dipilih

## [1.6.6] - 2025-11-05

### Fixed
- Parameter `limit` sekarang mengontrol total jumlah produk yang ditampilkan
- Parameter `columns` hanya mengontrol layout grid, bukan jumlah produk
- Memperbaiki logika untuk menampilkan produk sesuai dengan `limit` yang diatur

## [1.6.5] - 2025-11-04

### Fixed
- Mengimplementasikan array-based random products dengan column index assignment
- Memperbaiki logika untuk menampilkan 1 produk per kategori sesuai dengan jumlah columns

## [1.6.4] - 2025-11-02

### Added
- Enhance `sps_random_products` untuk menampilkan 1 produk per kategori untuk showcase yang beragam
- Parameter `columns` mengontrol layout grid
- Parameter `limit` mengontrol total jumlah produk

## [1.6.3] - 2025-11-02

### Added
- Menambahkan shortcode `[sps_random_products]` untuk menampilkan produk secara acak
- Menambahkan direct shortcode handler di main plugin file

## [1.6.2] - 2025-10-30

### Fixed
- Menampilkan produk kategori parent segera tanpa menunggu sub-category dipilih

## [1.6.1] - 2025-10-30

### Fixed
- Menggunakan IN operator dengan array of term IDs instead of include_children

## [1.6.0] - 2025-10-30

### Fixed
- Menggunakan WordPress native include_children untuk hierarchical category queries

## [1.5.9] - 2025-10-30

### Added
- Menambahkan parameter `include_children` untuk otomatis menampilkan produk dari parent dan child categories

## [1.5.8] - 2025-10-30

### Fixed
- Meningkatkan category search dengan normalisasi dan multi-method lookup
- Memperbaiki handling untuk space/dash di URL

## [1.5.7] - 2025-10-30

### Fixed
- Menangani category search berdasarkan name dan slug untuk URL-encoded parameters

## [1.5.6] - 2025-10-30

### Fixed
- Revert `sps_products_with_filters` dan update `sps_products_sub_category` untuk menampilkan produk segera

## [1.5.5] - 2025-10-30

### Changed
- Version bump

## [1.5.4] - 2025-10-30

### Added
- Enhance `sps_products_with_filters` dengan 2-level hierarchical filtering
- Menampilkan produk segera ketika kategori dipilih

## [1.5.3] - 2025-10-27

### Fixed
- Menghapus yellow background dan border dari sub-category message

## [1.5.2] - 2025-10-27

### Added
- Menambahkan shortcode `[sps_products_sub_category]` dengan 2-level hierarchical filtering

## [1.5.1] - 2025-10-26

### Changed
- Menghapus gray background dari filter tabs, membuatnya transparan
- Menambahkan direct fallback registration untuk `sps_products_with_filters` shortcode

## [1.5.0] - 2025-10-25

### Added
- Halaman Configuration baru dengan sistem 3-button
- Mode selector untuk memilih mode button
- Menu cleanup dan reorganisasi

### Changed
- Enhanced documentation untuk AI comprehension
- Menambahkan icon size setting untuk semua button (Main, Custom 1, Custom 2) - dapat dikonfigurasi 10-100px

### Fixed
- Menerapkan icon size variable ke Main Button (WhatsApp mode) frontend rendering
- Memperbaiki icon size: mengubah max-width/max-height ke width/height 100% dengan object-fit contain
- Memperbaiki deprecated float to int warnings

### Removed
- Menghapus preview button dari configuration page

## [1.4.0] - 2025-10-15

### Changed
- Enhanced Gallery Logic
- Memperbaiki WhatsApp Button CSS

## [1.3.9] - 2025-10-15

### Added
- Smart Gallery Display - Menyembunyikan Gallery untuk produk dengan single image

## [1.3.8] - 2025-10-10

### Changed
- Optimasi Tablet Responsiveness untuk Product Grid

## [1.3.7] - 2025-10-09

### Changed
- Optimasi Gallery Image Size untuk Mobile & Tablet

## [1.3.6] - 2025-10-09

### Added
- Complete AJAX Gallery System dengan Enhanced Documentation
- Memperbaiki AJAX image update functionality dengan srcset conflict resolution

## [1.3.5] - 2025-10-09

### Changed
- Major gallery improvements dan responsive enhancements
- Memperbaiki responsive image display untuk main product image

## [1.3.4] - 2025-10-05

### Added
- Menambahkan active border indicator untuk gallery images

## [1.3.3] - 2025-10-05

### Added
- Menambahkan gallery image click functionality
- Menambahkan thumbnail parameter support

## [1.3.2] - 2025-10-05

### Added
- Menambahkan thumbnail sebagai image pertama di product gallery

## [1.3.1] - 2025-09-29

### Changed
- Increment plugin version

## [1.3.0] - 2025-09-29

### Added
- Implementasi reliable URL parameters

### Fixed
- Memperbaiki WhatsApp button text field yang tidak muncul di admin settings

## [1.2.0] - 2025-09-29

### Added
- Implementasi SEO friendly URLs dengan slug parameter

## [1.1.0] - 2025-09-28

### Added
- Menambahkan category filtering feature

## [1.0.0] - 2025-09-26

### Added
- Initial release
- Shortcode `[sps_products]` untuk menampilkan daftar produk
- Shortcode `[sps_detail_products]` untuk menampilkan detail produk
- Product gallery dengan multiple images
- WhatsApp button dengan custom text dan icon
- Automatic WhatsApp number format detection dan normalization
- Flexible detail page settings system
- Product grid design yang match dengan Golden Coil Nails layout
- Horizontal layout untuk product title dan detail button
- Oval/pill-shaped detail button
- Padding dan card styling untuk product items
- Style options untuk title section
- Placeholder `{product_name}` di WhatsApp message template
- Cache busting dan timestamp untuk settings documentation
- Enhanced plugin settings dengan comprehensive shortcode documentation

### Changed
- Update plugin author dan URLs ke Teguh Rijanandi
- Update WhatsApp button styling dengan proper green background dan white elements
- Update documentation untuk reflect product_id routing instead of slug
- Update WhatsApp button dengan custom text dan icon
- Increase WhatsApp icon size pada detail button

### Fixed
- Memperbaiki shortcode registration dengan direct registration approach
- Memperbaiki WhatsApp button styling - smaller icon dengan proper alignment
- Revert redirect behavior kembali ke showing "No product found" message
- Revert slug-based URL changes dan cleanup debug files

### Removed
- Menghapus price section dari `sps_detail_products` shortcode
- Menghapus Plugin Information dan membuat dedicated Documentation submenu

---

## Kategori Perubahan

- **Added** untuk fitur baru
- **Changed** untuk perubahan pada fitur yang sudah ada
- **Deprecated** untuk fitur yang akan dihapus di versi mendatang
- **Removed** untuk fitur yang sudah dihapus
- **Fixed** untuk perbaikan bug
- **Security** untuk perbaikan keamanan

