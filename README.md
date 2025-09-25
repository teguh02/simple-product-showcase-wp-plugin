# Simple Product Showcase

Plugin WordPress ringan untuk menampilkan produk dengan integrasi WhatsApp tanpa fitur checkout, cart, atau pembayaran.

## 📋 Deskripsi

Simple Product Showcase adalah plugin WordPress yang memungkinkan Anda untuk:
- Menambahkan dan mengelola produk dengan mudah
- Menampilkan produk dalam grid yang responsif
- Mengintegrasikan tombol WhatsApp untuk setiap produk
- Menggunakan shortcode untuk menampilkan produk di halaman manapun
- Mengorganisir produk dengan kategori
- Menambahkan hingga 5 gambar gallery untuk setiap produk
- Duplikasi produk dengan mudah
- Data produk tetap tersimpan meskipun plugin dinonaktifkan

## ✨ Fitur Utama

### 🛍️ Manajemen Produk
- Custom Post Type untuk produk (`sps_product`)
- Field: Nama, Deskripsi, Harga, Gambar, Kategori
- Meta box untuk harga dan pesan WhatsApp custom
- **Gallery Images**: Hingga 5 gambar tambahan per produk
- Kolom custom di admin list produk
- **Duplicate Functionality**: Duplikasi produk dengan satu klik
- **Persistent Data**: Data tetap tersimpan meskipun plugin dinonaktifkan

### 📱 Integrasi WhatsApp
- Pengaturan nomor WhatsApp global
- Tombol WhatsApp di setiap produk
- Pesan default yang dapat dikustomisasi
- Placeholder untuk link produk dan nama produk
- Pesan custom per produk (opsional)

### 🎨 Tampilan Frontend
- Template single product yang responsif
- Halaman archive produk dengan filter
- Grid layout yang dapat dikustomisasi
- Search dan filter berdasarkan kategori
- Navigation antar produk

### 🔧 Shortcode
- `[sps_products]` - Menampilkan semua produk
- **10 Parameter Lengkap**: `columns`, `category`, `limit`, `orderby`, `order`, `show_price`, `show_description`, `show_whatsapp`, `show_gallery`, `gallery_style`
- **Gallery Support**: Tampilkan gallery images dengan berbagai style (grid, slider, carousel)
- **Responsive Design**: Otomatis menyesuaikan dengan tema WordPress

## 🚀 Instalasi

1. Upload folder `simple-product-showcase` ke direktori `/wp-content/plugins/`
2. Aktifkan plugin melalui menu 'Plugins' di WordPress admin
3. Pergi ke **Simple Product Showcase → Settings** untuk konfigurasi awal
4. Set nomor WhatsApp Anda di halaman settings

## ⚙️ Konfigurasi

### Pengaturan WhatsApp
1. Buka **Products → Settings**
2. Masukkan nomor WhatsApp dengan kode negara (contoh: +6281234567890)
3. Kustomisasi pesan default dengan placeholder:
   - `{product_link}` - URL produk
   - `{product_name}` - Nama produk
4. Simpan pengaturan

### Menambah Produk
1. Pergi ke **Products → Add New**
2. Isi nama produk (title)
3. Tambahkan deskripsi di editor
4. Set harga di meta box "Product Price"
5. Upload gambar produk (featured image)
6. **Tambah Gallery Images**: Upload hingga 5 gambar tambahan di meta box "Product Gallery"
7. Pilih kategori produk
8. Kustomisasi pesan WhatsApp (opsional)
9. Publish produk

## 📖 Penggunaan Shortcode

### Dasar
```
[sps_products]
```

### Dengan Atribut
```
[sps_products columns="3" category="shoes" limit="6"]
```

### Atribut yang Tersedia

| Atribut | Deskripsi | Default | Contoh |
|---------|-----------|---------|---------|
| `columns` | Jumlah kolom grid (1-6) | 3 | `columns="2"` |
| `category` | Filter berdasarkan kategori | - | `category="shoes"` |
| `limit` | Batas jumlah produk | -1 (semua) | `limit="10"` |
| `orderby` | Urutan berdasarkan: title, date, menu_order, price | date | `orderby="title"` |
| `order` | Arah urutan: ASC atau DESC | DESC | `order="ASC"` |
| `show_price` | Tampilkan harga | true | `show_price="false"` |
| `show_description` | Tampilkan deskripsi | true | `show_description="false"` |
| `show_whatsapp` | Tampilkan tombol WhatsApp | true | `show_whatsapp="false"` |
| `show_gallery` | Tampilkan gallery images | true | `show_gallery="false"` |
| `gallery_style` | Style gallery: grid, slider, carousel | grid | `gallery_style="slider"` |

### Contoh Penggunaan
```
<!-- Menampilkan 6 produk dalam 2 kolom -->
[sps_products columns="2" limit="6"]

<!-- Menampilkan produk kategori "shoes" -->
[sps_products category="shoes"]

<!-- Menampilkan produk tanpa harga -->
[sps_products show_price="false"]

<!-- Menampilkan produk dengan deskripsi -->
[sps_products show_description="true"]

<!-- Gallery slider tanpa WhatsApp -->
[sps_products gallery_style="slider" show_whatsapp="false"]

<!-- Tampilan minimal (hanya gambar + judul) -->
[sps_products show_price="false" show_description="false" show_whatsapp="false" show_gallery="false"]

<!-- Produk terbaru dengan urutan alfabetis -->
[sps_products orderby="title" order="ASC" limit="8"]

<!-- Featured products section -->
[sps_products limit="4" orderby="menu_order" columns="2"]
```

## 🎨 Kustomisasi

### Template Override
Plugin menggunakan template hierarchy WordPress. Anda dapat mengoverride template dengan menambahkan file berikut ke theme Anda:

- `simple-product-showcase/single-sps_product.php`
- `simple-product-showcase/archive-sps_product.php`
- `simple-product-showcase/taxonomy-sps_product_category.php`

### CSS Kustomisasi
Semua class CSS dimulai dengan prefix `sps-` untuk menghindari konflik:

```css
/* Kustomisasi grid produk */
.sps-products-grid {
    gap: 30px;
}

/* Kustomisasi tombol WhatsApp */
.sps-whatsapp-button {
    background: #your-color;
}
```

### JavaScript Events
Plugin menyediakan custom events untuk tracking:

```javascript
// Track klik WhatsApp
$(document).on('sps:whatsapp:click', function(e, data) {
    console.log('WhatsApp clicked for:', data.product);
});

// Track view produk
$(document).on('sps:product:view', function(e, data) {
    console.log('Product viewed:', data.product);
});
```

## 🔧 Hooks dan Filters

### Actions
```php
// Tambahkan konten setelah deskripsi produk
add_action('sps_single_product_content', 'my_custom_content');

// Tambahkan konten di archive produk
add_action('sps_archive_product_content', 'my_archive_content');
```

### Filters
```php
// Kustomisasi query produk
add_filter('sps_products_query_args', function($args) {
    $args['meta_key'] = '_sps_product_price';
    $args['orderby'] = 'meta_value_num';
    return $args;
});

// Kustomisasi pesan WhatsApp
add_filter('sps_whatsapp_message', function($message, $product_id) {
    return $message . ' - Pesan tambahan';
}, 10, 2);
```

## 📁 Struktur File

```
simple-product-showcase/
├── simple-product-showcase.php          # File utama plugin
├── includes/                            # Class-class utama
│   ├── class-sps-init.php              # Inisialisasi plugin
│   ├── class-sps-cpt.php               # Custom Post Type
│   ├── class-sps-settings.php          # Halaman settings
│   ├── class-sps-shortcodes.php        # Shortcode handler
│   ├── class-sps-frontend.php          # Frontend template
│   ├── class-sps-metabox.php           # Gallery meta box
│   ├── class-sps-duplicate.php         # Duplicate functionality
│   └── class-sps-persistent.php        # Data persistence
├── assets/                             # Assets (CSS & JS)
│   ├── css/
│   │   ├── style.css                   # CSS frontend
│   │   ├── admin-style.css             # CSS admin
│   │   ├── duplicate-style.css         # CSS duplicate button
│   │   └── gallery-metabox.css         # CSS gallery meta box
│   ├── js/
│   │   ├── script.js                   # JavaScript frontend
│   │   ├── admin-script.js             # JavaScript admin
│   │   ├── gallery-metabox.js          # JavaScript gallery
│   │   └── gallery-admin.js            # JavaScript gallery admin
│   └── images/
│       └── placeholder.png             # Placeholder image
├── templates/                          # Template files
│   ├── single-sps_product.php          # Template single product
│   ├── archive-sps_product.php         # Template archive
│   └── taxonomy-sps_product_category.php # Template kategori
├── SHORTCODE-DOCUMENTATION.md          # Dokumentasi shortcode lengkap
├── uninstall.php                       # Cleanup script
└── README.md                           # Dokumentasi ini
```

## 🐛 Troubleshooting

### WhatsApp Button Tidak Muncul
1. Pastikan nomor WhatsApp sudah di-set di Settings
2. Cek format nomor (harus dengan kode negara, contoh: +6281234567890)
3. Pastikan produk sudah di-publish

### Template Tidak Berubah
1. Pastikan file template ada di theme dengan path yang benar
2. Clear cache jika menggunakan plugin cache
3. Cek permission file template

### Shortcode Tidak Berfungsi
1. Pastikan shortcode ditulis dengan benar
2. Cek apakah ada produk yang di-publish
3. Cek konflik dengan plugin lain

## 🔄 Changelog

### Version 1.0.0
- Initial release
- Custom Post Type untuk produk
- Integrasi WhatsApp
- Shortcode [sps_products]
- Template responsif
- Admin settings page
- **Gallery Images**: Hingga 5 gambar tambahan per produk
- **Duplicate Functionality**: Duplikasi produk dengan satu klik
- **Data Persistence**: Data tetap tersimpan meskipun plugin dinonaktifkan
- **Enhanced Shortcode**: 10 parameter lengkap dengan gallery support
- **Improved Admin**: Menu terintegrasi dan meta box yang lebih baik
- **Comprehensive Documentation**: Dokumentasi shortcode lengkap

## 📞 Support

Jika Anda mengalami masalah atau memiliki pertanyaan:

1. Cek dokumentasi ini terlebih dahulu
2. Cek FAQ di halaman settings plugin
3. Pastikan WordPress dan theme Anda up-to-date
4. Deactivate plugin lain untuk cek konflik

## 📄 License

Plugin ini dilisensikan di bawah GPL v2 atau yang lebih baru.

## 🤝 Contributing

Kontribusi sangat diterima! Silakan:

1. Fork repository ini
2. Buat branch fitur baru
3. Commit perubahan Anda
4. Push ke branch
5. Buat Pull Request

## 🙏 Credits

Plugin ini dibuat dengan ❤️ untuk komunitas WordPress Indonesia.

---

**Simple Product Showcase** - Plugin WordPress untuk showcase produk dengan integrasi WhatsApp yang mudah dan powerful.
