# Simple Product Showcase

**Version:** 1.5.0  
**Author:** Teguh Rijanandi  
**License:** GPL v2 or later  
**Requires:** WordPress 5.0+  
**Tested up to:** WordPress 6.4  

Plugin WordPress ringan untuk menampilkan produk dengan integrasi WhatsApp tanpa fitur checkout, cart, atau pembayaran. Plugin ini fokus pada showcase produk dengan 3 tombol action yang dapat dikustomisasi penuh (Main Button dengan mode WhatsApp/Custom, Custom Button 1, Custom Button 2).

---

## 📋 Deskripsi

Simple Product Showcase adalah plugin WordPress yang memungkinkan Anda untuk:
- Menambahkan dan mengelola produk dengan Custom Post Type (`sps_product`)
- Menampilkan produk dalam grid responsif dengan berbagai layout options
- Mengintegrasikan **3 tombol custom** untuk setiap produk:
  - **Main Button** dengan 2 mode (WhatsApp Mode atau Custom Mode)
  - **Custom Button 1** dengan full customization
  - **Custom Button 2** dengan full customization
- Menggunakan shortcode `[sps_products]`, `[sps_products_with_filters]`, dan `[sps_detail_products]` untuk menampilkan produk
- Mengorganisir produk dengan kategori taksonomi (`sps_product_category`)
- Menambahkan hingga **5 gambar gallery** untuk setiap produk (+ 1 thumbnail = total 6 gambar)
- **AJAX Gallery Interaktif**: Perubahan gambar utama tanpa reload halaman dengan hash URL support
- Duplikasi produk dengan satu klik untuk mempercepat workflow
- **Data persistence**: Data produk tetap tersimpan meskipun plugin dinonaktifkan

---

## ✨ Fitur Utama

### 🛍️ Manajemen Produk
- **Custom Post Type** `sps_product` untuk produk dengan full WordPress admin UI
- **Fields**: Title, Content (description), Featured Image (thumbnail), Custom Meta Fields
- **Meta Box Harga**: Input field untuk harga produk (disimpan sebagai `_sps_product_price`)
- **Meta Box Gallery**: Upload hingga 5 gambar tambahan per produk (disimpan sebagai `_sps_gallery_1` sampai `_sps_gallery_5`)
- **Kolom Custom** di admin list: harga, kategori, featured image untuk quick overview
- **Duplicate Functionality**: Tombol "Duplicate" di quick actions untuk clone produk
- **Persistent Data**: Data tetap di database meskipun plugin dinonaktifkan (soft uninstall)

### 🎯 Sistem 3 Tombol Custom (Button Configuration)
**Lokasi**: `Products → Configuration` (menu admin baru di version 1.5.0)

#### Main Button (Mode Selector)
- **WhatsApp Mode** (Simplified):
  - Button Text: Teks tombol (default: "Tanya Produk")
  - WhatsApp Number: Nomor WhatsApp tujuan dengan kode negara
  - WhatsApp Message: Template pesan dengan placeholders `{product_name}` dan `{product_link}`
  - Background Color: Warna background tombol (color picker)
  - Text Color: Warna teks tombol (color picker)
  
- **Custom Mode** (Full Control):
  - Button Text: Teks tombol custom
  - Button Icon: Upload icon PNG/JPG/SVG dengan WordPress Media Library
  - Button URL: URL tujuan tombol (support external links)
  - Open in: Target window (_self atau _blank)
  - Background Color: Warna background tombol
  - Text Color: Warna teks tombol

#### Custom Button 1 & 2 (Full Customization)
- **Show this button**: Checkbox visibility (default: hidden)
- **Button Text**: Teks tombol bebas
- **Button Icon**: Upload icon dengan Media Library
- **Button URL**: URL tujuan custom
- **Open in**: Target window setting
- **Background Color**: Color picker untuk background
- **Text Color**: Color picker untuk teks

**Storage**: Semua settings disimpan di `wp_options` table dengan prefix `sps_`:
- Main: `sps_main_button_mode`, `sps_main_visible`, `sps_main_text`, `sps_main_bg_color`, `sps_main_text_color`, dll
- Custom 1: `sps_custom1_visible`, `sps_custom1_text`, `sps_custom1_icon`, `sps_custom1_url`, dll
- Custom 2: `sps_custom2_visible`, `sps_custom2_text`, `sps_custom2_icon`, `sps_custom2_url`, dll

### 📱 Detail Page Settings (Configuration)
- **Detail Page Mode**:
  - `default`: Gunakan template single product bawaan plugin (`single-sps_product.php`)
  - `custom`: Redirect ke halaman custom WordPress dengan shortcode support
- **Custom Detail Page**: Dropdown untuk memilih WordPress page tujuan
- **URL Generation**: Static method `SPS_Configuration::get_product_detail_url($product_id)` untuk generate URL detail produk

### 🎨 Tampilan Frontend
- **Template Hierarchy**: WordPress standard dengan override support di theme
  - `single-sps_product.php`: Detail produk (full layout dengan gallery, buttons, description)
  - `archive-sps_product.php`: List semua produk dengan pagination
  - `taxonomy-sps_product_category.php`: Produk filtered by category
- **Responsive Grid Layout**: CSS Grid dengan auto-fit minmax untuk berbagai screen sizes
- **Interactive Gallery**: AJAX-powered gallery dengan:
  - Hash URL support (`#thumbnail=1`, `#thumbnail=2`, dst)
  - Smooth image transition tanpa page reload
  - Visual feedback (border biru) pada active thumbnail
  - Horizontal slider di mobile/tablet, grid di desktop
- **Navigation**: Prev/Next product links di single product page

### 🔧 Shortcode System
**Main Shortcode**: `[sps_products]`
- Display grid produk dengan 10+ parameters
- Support category filtering dari URL `?category=slug`
- Auto-responsive dengan CSS Grid

**Filter Shortcode**: `[sps_products_with_filters]`
- Display grid produk dengan filter kategori interaktif di bagian atas
- Tab-based category filtering dengan visual feedback
- Produk hanya muncul setelah kategori dipilih
- Support semua parameter `[sps_products]` (columns, limit, orderby, dll)
- URL parameter `?category=slug` untuk deep linking

**Detail Shortcode**: `[sps_detail_products section="..." style="..."]`
- Modular display untuk custom page layouts
- Sections: `title`, `image`, `description`, `gallery`, `button`
- Styles: Title (h1-h5), Gallery (grid/slider/carousel)

### 🔌 Architecture & Code Structure
**Main Plugin File**: `simple-product-showcase.php`
- Bootstrap file dengan class `Simple_Product_Showcase` (Singleton pattern)
- Registrasi activation/deactivation hooks
- Load dependencies dari `/includes/` folder

**Core Classes** (`/includes/`):
1. `SPS_Init`: Plugin initialization coordinator
2. `SPS_CPT`: Register Custom Post Type & Taxonomy
3. `SPS_Configuration`: Button configuration page (NEW in v1.5.0, replaces Settings)
4. `SPS_Settings`: Legacy settings class (commented out, backup only)
5. `SPS_Shortcodes`: Shortcode handler untuk `[sps_products]` dan `[sps_detail_products]`
6. `SPS_Frontend`: Template loader dan frontend rendering
7. `SPS_Metabox`: Gallery meta box dengan WordPress Media Library integration
8. `SPS_Duplicate`: Product duplication functionality
9. `SPS_Persistent`: Ensure data persistence saat plugin deactivated

**Assets** (`/assets/`):
- `css/`: Frontend styles, admin styles, gallery styles
- `js/`: AJAX gallery handler, admin scripts, color picker integration
- `img/`: Placeholder images dan icons

**Templates** (`/templates/`):
- Override-able di theme dengan folder `simple-product-showcase/`

---

## 🚀 Instalasi

1. Upload folder `simple-product-showcase` ke direktori `/wp-content/plugins/`
2. Aktifkan plugin melalui menu 'Plugins' di WordPress admin
3. Pergi ke **Simple Product Showcase → Settings** untuk konfigurasi awal
4. Set nomor WhatsApp Anda di halaman settings

## ⚙️ Konfigurasi

### Setup Awal (First Time)
1. Aktifkan plugin melalui **Plugins** menu di WordPress admin
2. Plugin akan otomatis:
   - Register Custom Post Type `sps_product`
   - Register Taxonomy `sps_product_category`
   - Flush rewrite rules untuk permalink
   - Set default options di `wp_options` table
3. Menu **Products** akan muncul di sidebar admin

### Konfigurasi 3 Tombol Custom (Button Configuration)
**Lokasi**: `Products → Configuration`

#### Setup Main Button (Pilih Mode)
1. **WhatsApp Mode** (Simplified untuk toko online):
   - Centang "Show this button" untuk visibility
   - Isi "Button Text" (contoh: "Tanya Produk", "Hubungi Kami")
   - Isi "WhatsApp Number" dengan kode negara (contoh: `+6281234567890`)
   - Kustomisasi "WhatsApp Message Template":
     ```
     Hai kak, saya mau tanya tentang {product_name}. Link: {product_link}
     ```
   - Pilih warna background (default: `#25D366` WhatsApp green)
   - Pilih warna text (default: `#FFFFFF` white)

2. **Custom Mode** (Full control untuk website profesional):
   - Toggle mode selector ke "Custom"
   - Isi "Button Text" bebas
   - Upload "Button Icon" (PNG/JPG/SVG) via Media Library
   - Isi "Button URL" (contoh: `/contact-us/` atau `https://external-site.com`)
   - Pilih "Open in" (_blank untuk tab baru, _self untuk tab sama)
   - Kustomisasi warna background dan text

#### Setup Custom Button 1 & 2
1. Centang "Show this button" untuk menampilkan
2. Isi semua field yang tersedia:
   - Button Text: Label tombol (contoh: "Catalog PDF", "Video Tutorial")
   - Button Icon: Upload icon (optional)
   - Button URL: Link tujuan (internal atau external)
   - Open in: Target window preference
   - Background Color: Sesuaikan branding
   - Text Color: Contrast untuk readability
3. Klik "Save Configuration"

**Database**: Settings disimpan di `wp_options` dengan prefix `sps_*`:
```sql
-- Contoh options yang disimpan:
sps_main_button_mode = 'whatsapp' atau 'custom'
sps_main_visible = '1' atau '0'
sps_main_text = 'Tanya Produk'
sps_main_bg_color = '#25D366'
sps_main_text_color = '#FFFFFF'
sps_custom1_visible = '0' -- Hidden by default
sps_custom2_visible = '0' -- Hidden by default
```

### Setup Detail Page Settings
1. Buka `Products → Configuration`
2. Scroll ke bagian "Detail Page Settings"
3. Pilih **Detail Page Mode**:
   - **Default**: Gunakan template bawaan plugin dengan full layout
   - **Custom**: Redirect ke page WordPress dengan shortcodes
4. Jika pilih Custom:
   - Pilih WordPress Page dari dropdown
   - Tambahkan shortcodes di page editor:
     ```
     [sps_detail_products section="title" style="h2"]
     [sps_detail_products section="image"]
     [sps_detail_products section="gallery" style="slider"]
     [sps_detail_products section="description"]
     [sps_detail_products section="button"]
     ```
5. Save settings

**URL Generation**: Plugin akan otomatis generate URL detail:
- Default mode: `/product/nama-produk/` (WordPress permalink)
- Custom mode: `/your-page/?product_id=123` (URL parameter)

### Pengaturan WhatsApp (Legacy - untuk backward compatibility)
1. Buka **Products → Settings**
2. Masukkan nomor WhatsApp dengan kode negara (contoh: +6281234567890)
3. Kustomisasi pesan default dengan placeholder:
   - `{product_link}` - URL produk
   - `{product_name}` - Nama produk
4. **Kustomisasi teks tombol WhatsApp** (contoh: "Tanya Produk Ini", "Hubungi Kami", "Chat WhatsApp")
5. **Konfigurasi Tombol Custom**: Atur 3 tombol (Main Button + Custom 1 + Custom 2) dengan:
   - Visibility (tampil/sembunyi)
   - Text customization
   - Icon upload (PNG/JPG/SVG)
   - URL configuration
   - Target setting (_self/_blank)
   - Color picker (background & text color)
   - Live preview
6. Simpan pengaturan

### Menambah Produk Baru
1. Buka **Products → Add New**
2. **Title**: Nama produk (contoh: "Laptop ASUS ROG Strix G15")
3. **Content Editor**: Deskripsi lengkap produk dengan formatting
4. **Featured Image**: Upload gambar utama (thumbnail) - klik "Set featured image"
5. **Product Price** (Meta Box):
   - Isi harga tanpa simbol currency (contoh: `15000000`)
   - Atau dengan pemisah ribuan (contoh: `15.000.000`)
   - Currency symbol akan otomatis ditambahkan di frontend
6. **Product Gallery** (Meta Box):
   - Klik "Upload Image" untuk gambar 1-5
   - Maksimal 5 gambar tambahan (+ 1 thumbnail = 6 total)
   - Drag & drop untuk reorder
   - Klik "Remove" untuk delete gambar
7. **Categories**: Pilih atau buat kategori produk
8. **Publish**: Klik tombol "Publish" untuk publikasi

**Data Storage**:
```sql
-- wp_posts table
post_type = 'sps_product'
post_status = 'publish'
post_title = 'Nama Produk'
post_content = 'Deskripsi'

-- wp_postmeta table
meta_key = '_sps_product_price', meta_value = '15000000'
meta_key = '_sps_gallery_1', meta_value = '123' (attachment ID)
meta_key = '_sps_gallery_2', meta_value = '124'
... dst sampai _sps_gallery_5

-- wp_term_relationships table
Relationship antara product dan category taxonomy
```

### Duplikasi Produk (Quick Clone)
1. Hover produk di list **Products → All Products**
2. Klik link **"Duplicate"** di quick actions
3. Plugin akan:
   - Clone semua post data (title, content, excerpt)
   - Copy semua meta fields (price, gallery images)
   - Copy taxonomy terms (categories)
   - Set status sebagai 'draft' untuk review
   - Append "(Copy)" di title untuk identifikasi
4. Edit duplikat produk sesuai kebutuhan
5. Publish saat siap

**Technical**: Class `SPS_Duplicate` menggunakan:
```php
// Hook registration
add_filter('post_row_actions', array($this, 'add_duplicate_link'))
add_action('admin_action_sps_duplicate_product', array($this, 'duplicate_product'))

// Duplication logic
wp_insert_post() // Create new post
copy_post_metadata() // Clone all meta
wp_set_post_terms() // Assign categories
```

---
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

### Shortcode Grid Produk
```
[sps_products]
```

### Shortcode Grid Produk dengan Filter Kategori
```
[sps_products_with_filters]
```
**Fitur**:
- Tampilan filter kategori di bagian atas (horizontal tabs)
- Produk hanya muncul setelah kategori dipilih
- URL parameter `?category=slug` ditambahkan saat filter diklik
- Visual feedback untuk filter aktif (warna kuning #FDB913)
- Responsive di mobile dan desktop
- Message "TERDAPAT FILTER DISINI" saat belum pilih kategori

### Dengan Atribut
```
[sps_products columns="3" category="shoes" limit="6"]
[sps_products_with_filters columns="4" limit="12"]
```

### Shortcode Detail Produk
```
[sps_detail_products section="title" style="h2"]
[sps_detail_products section="image"]
[sps_detail_products section="gallery" style="slider"]
[sps_detail_products section="whatsapp"]
[sps_detail_products section="button"]

<!-- Contoh dengan title h3 dan gallery carousel -->
[sps_detail_products section="title" style="h3"]
[sps_detail_products section="gallery" style="carousel"]
```

### AJAX Gallery Implementation
```
<!-- AJAX Gallery bekerja otomatis dengan shortcode gallery -->
[sps_detail_products section="gallery" style="grid"]
<!-- atau -->
[sps_detail_products section="gallery" style="slider"]
<!-- atau -->
[sps_detail_products section="gallery" style="carousel"]

<!-- Fitur yang didapat: -->
<!-- 1. Klik gambar gallery → gambar utama berubah tanpa reload -->
<!-- 2. URL berubah ke #thumbnail=X -->
<!-- 3. Border biru pada gambar aktif -->
<!-- 4. Responsive di semua device -->
<!-- 5. URL dapat di-share dan dibuka langsung -->
```

### Category Filtering dari URL
```
<!-- URL: http://yoursite.com/produk/?category=shoes -->
<!-- Shortcode akan otomatis filter produk kategori "shoes" -->
[sps_products]

<!-- URL: http://yoursite.com/produk/?category=electronics -->
<!-- Shortcode akan otomatis filter produk kategori "electronics" -->
[sps_products columns="4"]
```

### Product Detail URLs
```
<!-- URL dengan parameter product untuk SEO friendly -->
<!-- http://yoursite.com/show-product/?product=paku-tembak-polos-ukuran-50mm-x-2-1mm -->
<!-- Shortcode akan otomatis detect produk berdasarkan slug -->

<!-- URL dengan parameter product_id untuk backward compatibility -->
<!-- http://yoursite.com/show-product/?product_id=28 -->
<!-- Shortcode akan otomatis detect produk berdasarkan ID -->
```

### AJAX Gallery dengan Hash URL Support
```
<!-- URL dengan hash thumbnail untuk direct access ke gambar tertentu -->
<!-- http://yoursite.com/show-product/?product=contoh-produk-1#thumbnail=1 -->
<!-- Gambar utama akan menampilkan thumbnail (gambar pertama) -->

<!-- http://yoursite.com/show-product/?product=contoh-produk-1#thumbnail=3 -->
<!-- Gambar utama akan menampilkan gambar gallery ke-3 -->

<!-- http://yoursite.com/show-product/?product=contoh-produk-1#thumbnail=6 -->
<!-- Gambar utama akan menampilkan gambar gallery ke-6 (gambar terakhir) -->

<!-- Fitur AJAX Gallery: -->
<!-- 1. Klik gambar di gallery → URL berubah ke #thumbnail=X -->
<!-- 2. Gambar utama berubah tanpa reload halaman -->
<!-- 3. Border biru muncul pada gambar aktif di gallery -->
<!-- 4. URL dapat di-share dan dibuka langsung -->
```

### Atribut yang Tersedia

#### Untuk `[sps_products]` (Grid Produk):
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

#### Untuk `[sps_detail_products]` (Detail Produk):
| Atribut | Deskripsi | Default | Contoh |
|---------|-----------|---------|---------|
| `section` | Bagian produk yang ditampilkan: title, image, description, gallery, whatsapp | title | `section="gallery"` |
| `style` | Style berdasarkan section:<br>• Title: h1, h2, h3, h4, h5<br>• Gallery: grid, slider, carousel | h1 (title)<br>grid (gallery) | `style="h2"` atau `style="slider"` |

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

## 🔗 Cara Kerja Product Detection

Shortcode `[sps_detail_products]` mendeteksi produk berdasarkan parameter `product_id` di URL:

- **Deteksi Product ID** (`/show-product/?product_id=123`) - Mendeteksi berdasarkan parameter product_id
- **WordPress Permalinks** - Bekerja dengan semua struktur permalink (Post name, Numeric, dll)
- **Fallback Otomatis** - Jika tidak ada product_id, menampilkan pesan "No product found"

**Contoh URL:**
- `/show-product/?product_id=28` - Akan menampilkan produk dengan ID 28
- `/product-detail/?product_id=15` - Akan menampilkan produk dengan ID 15

Pendekatan ini memastikan deteksi produk yang andal terlepas dari pengaturan permalink WordPress.

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

## 📁 Struktur File & Arsitektur Plugin

```
simple-product-showcase/
├── simple-product-showcase.php          # 🔷 MAIN PLUGIN FILE (Bootstrap)
│   │   - Plugin header metadata (Name, Version, Author, etc)
│   │   - Class Simple_Product_Showcase (Singleton pattern)
│   │   - Activation/Deactivation hooks (flush rewrite, set defaults)
│   │   - Load all dependencies dari /includes/
│   │   - Fallback methods untuk CPT, shortcodes, admin menu
│   │   - Direct shortcode registration untuk reliability
│   │
├── includes/                             # 🔷 CORE CLASS FILES
│   ├── class-sps-init.php               # Plugin Initialization Coordinator
│   │   │   - Singleton class untuk init semua components
│   │   │   - Load semua class files (CPT, Configuration, Frontend, dll)
│   │   │   - Enqueue CSS/JS untuk frontend dan admin
│   │   │   - Priority: Initialization → Enqueue → Component Load
│   │   │
│   ├── class-sps-cpt.php                # Custom Post Type & Taxonomy Registration
│   │   │   - Register 'sps_product' post type dengan full labels
│   │   │   - Register 'sps_product_category' taxonomy (hierarchical)
│   │   │   - Admin columns customization (thumbnail, price, category)
│   │   │   - Custom admin styles untuk better UX
│   │   │
│   ├── class-sps-configuration.php      # ⭐ Button Configuration Page (NEW v1.5.0)
│   │   │   - Replaces old Settings class dengan improved UI
│   │   │   - Admin menu: "Products → Configuration"
│   │   │   - 3 Button Settings: Main (WhatsApp/Custom mode), Custom1, Custom2
│   │   │   - Detail Page Settings (default/custom mode)
│   │   │   - WordPress Settings API untuk form handling
│   │   │   - Color Picker & Media Library integration
│   │   │   - Static method: get_product_detail_url($product_id)
│   │   │   - Save logic: validate, sanitize, update_option()
│   │   │
│   ├── class-sps-settings.php           # ⚠️ DEPRECATED (Backup only, not loaded)
│   │   │   - Old settings class (commented out di main file line 121)
│   │   │   - Kept for emergency restore jika Configuration error
│   │   │   - Hooks commented: add_action('admin_menu') & register_settings
│   │   │
│   ├── class-sps-shortcodes.php         # Shortcode Handler & Rendering
│   │   │   - [sps_products]: Grid display dengan 10+ attributes
│   │   │   - [sps_detail_products]: Modular detail sections
│   │   │   - Product detection: URL params (?product=, ?product_id=)
│   │   │   - Category filtering dari URL (?category=slug)
│   │   │   - WP_Query generation dengan tax_query support
│   │   │   - Responsive CSS generation (inline styles)
│   │   │   - AJAX gallery integration untuk interactive images
│   │   │
│   ├── class-sps-frontend.php           # Frontend Template Loader
│   │   │   - Template hierarchy: theme override → plugin templates
│   │   │   - Load templates: single, archive, taxonomy
│   │   │   - Body class injection untuk styling
│   │   │   - Enqueue frontend CSS/JS berdasarkan page type
│   │   │
│   ├── class-sps-metabox.php            # Gallery Meta Box & Price Field
│   │   │   - Meta box "Product Price" dengan currency formatting
│   │   │   - Meta box "Product Gallery" dengan 5 upload slots
│   │   │   - WordPress Media Library integration
│   │   │   - AJAX upload & remove functionality
│   │   │   - Save logic: sanitize, validate, update_post_meta()
│   │   │   - Gallery data: _sps_gallery_1 s/d _sps_gallery_5
│   │   │
│   ├── class-sps-duplicate.php          # Product Duplication Feature
│   │   │   - Add "Duplicate" link di post row actions
│   │   │   - admin_action hook: 'sps_duplicate_product'
│   │   │   - Clone logic: wp_insert_post() + copy metadata
│   │   │   - Taxonomy terms copy dengan wp_set_post_terms()
│   │   │   - Security: nonce verification, capability check
│   │   │
│   └── class-sps-persistent.php         # Data Persistence Manager
│       │   - Ensure data tetap di database saat plugin deactivated
│       │   - No cleanup pada deactivation (only on uninstall)
│       │   - Custom deactivation logic untuk preserve products
│       │
├── assets/                               # 🔷 FRONTEND & ADMIN ASSETS
│   ├── css/
│   │   ├── style.css                    # Frontend product grid & detail styles
│   │   │   - Grid layout dengan CSS Grid (responsive)
│   │   │   - Product card styling dengan shadow & hover effects
│   │   │   - Button styles untuk WhatsApp & custom buttons
│   │   │   - Mobile breakpoints (@media queries)
│   │   │
│   │   ├── product-detail.css           # Single product detail page styles
│   │   │   - Gallery grid/slider/carousel layouts
│   │   │   - Image zoom & lightbox effects
│   │   │   - Button group positioning
│   │   │   - Responsive image handling
│   │   │
│   │   ├── admin-style.css              # Admin area custom styles
│   │   │   - Configuration page styling
│   │   │   - Meta box custom layouts
│   │   │   - Color picker integration
│   │   │
│   │   ├── gallery-admin.css            # Gallery meta box specific styles
│   │   │   - Upload slot styling dengan drag & drop
│   │   │   - Preview thumbnail dengan remove button
│   │   │   - Grid layout untuk 5 slots
│   │   │
│   │   ├── gallery-metabox.css          # Gallery admin UI enhancements
│   │   │   - WordPress Media Library modal styling
│   │   │   - Image preview cards
│   │   │
│   │   └── duplicate-style.css          # Duplicate link styling
│   │       - "Duplicate" link colors & hover states
│   │
│   └── js/
│       ├── script.js                    # ⭐ Frontend AJAX Gallery Handler
│       │   │   - AJAX gallery click handler untuk image switching
│       │   │   - Hash URL support (#thumbnail=1, #thumbnail=2, etc)
│       │   │   - Active thumbnail visual feedback (border biru)
│       │   │   - DOM manipulation untuk main image replacement
│       │   │   - srcset conflict removal untuk smooth transition
│       │   │   - Console logging untuk debugging
│       │   │   - Event listeners: DOMContentLoaded, hashchange, click
│       │   │
│       ├── admin-script.js              # Admin area JavaScript
│       │   │   - Color picker initialization (wpColorPicker)
│       │   │   - Form validation untuk Configuration page
│       │   │   - Media Library modal handling
│       │   │
│       ├── gallery-metabox.js           # Gallery Meta Box JavaScript
│       │   │   - WordPress Media Library integration
│       │   │   - Upload button click handlers
│       │   │   - Remove button functionality
│       │   │   - Image preview generation
│       │   │   - AJAX save untuk instant feedback
│       │   │
│       └── gallery-admin.js             # Gallery Admin Enhancements
│           │   - Drag & drop reordering
│           │   - Bulk upload support
│           │   - Progress bar untuk upload
│           │
├── templates/                            # 🔷 FRONTEND TEMPLATE FILES
│   ├── single-sps_product.php           # Single Product Detail Template
│   │   │   - Full product layout: image, gallery, description, buttons
│   │   │   - AJAX gallery integration dengan hash URL
│   │   │   - Prev/Next navigation links
│   │   │   - Breadcrumb navigation
│   │   │   - Responsive layout untuk mobile/desktop
│   │   │   - Override-able di theme folder
│   │   │
│   ├── archive-sps_product.php          # All Products Archive Template
│   │   │   - Grid layout dengan pagination
│   │   │   - Filter sidebar (optional)
│   │   │   - Sort options (title, date, price)
│   │   │   - No products found message
│   │   │
│   └── taxonomy-sps_product_category.php # Category Archive Template
│       │   - Category-specific product grid
│       │   - Category description display
│       │   - Category filter breadcrumb
│       │
├── SHORTCODE-DOCUMENTATION.md           # 📘 Complete Shortcode Documentation
│   │   - Comprehensive guide untuk [sps_products]
│   │   - Parameter reference dengan contoh
│   │   - [sps_detail_products] usage guide
│   │   - URL parameter documentation
│   │   - AJAX gallery implementation guide
│   │
├── uninstall.php                        # 🗑️ Cleanup Script (Hard Delete)
│   │   - Triggered only saat plugin di-uninstall (bukan deactivate)
│   │   - Delete all products: wp_posts WHERE post_type='sps_product'
│   │   - Delete all metadata: wp_postmeta
│   │   - Delete all taxonomy terms: wp_terms, wp_term_taxonomy
│   │   - Delete all options: wp_options WHERE option_name LIKE 'sps_%'
│   │   - Flush rewrite rules untuk cleanup
│   │
├── index.php                            # Directory Index Protection
└── README.md                            # 📖 This comprehensive documentation
```

### 🔄 Request Flow Diagram

#### Frontend Product Grid Display
```
User Request → WordPress → Shortcode Parser
                               ↓
                    [sps_products columns="3"]
                               ↓
              SPS_Shortcodes::products_shortcode()
                               ↓
              WP_Query (post_type='sps_product')
                               ↓
              Loop products → Render HTML Grid
                               ↓
              Enqueue style.css → Browser
```

#### AJAX Gallery Image Switch
```
User Click Gallery Thumbnail → script.js (click event)
                                      ↓
                              AJAX Request (POST)
                              wp-admin/admin-ajax.php
                              action=get_gallery_image
                              image_id=123
                                      ↓
              Simple_Product_Showcase::ajax_get_gallery_image()
                                      ↓
              wp_get_attachment_image_src(image_id, 'large')
                                      ↓
              JSON Response {success:true, image_url:'...'}
                                      ↓
              script.js updates #sps-main-image-container
              + Update URL hash #thumbnail=X
              + Add active class border
```

#### Configuration Save Flow
```
User Submit Form → POST /wp-admin/edit.php?...page=sps-configuration
                              ↓
      SPS_Configuration::save_configuration()
                              ↓
      WordPress nonce verification (security)
                              ↓
      Sanitize all inputs (sanitize_text_field, esc_url_raw, etc)
                              ↓
      update_option('sps_main_text', $value)
      update_option('sps_main_bg_color', $value)
      ... untuk semua 24+ settings
                              ↓
      Redirect dengan success message
```

### 🎯 Key Technical Decisions

1. **Configuration menggantikan Settings (v1.5.0)**:
   - Old: `class-sps-settings.php` → tidak di-load (line 121 commented)
   - New: `class-sps-configuration.php` → loaded dan aktif
   - Reason: Better UI/UX, mode selector, cleaner code structure

2. **Fallback Methods di Main File**:
   - `add_fallback_admin_menu()`: Jika class tidak load
   - `register_fallback_cpt()`: Jika CPT class gagal
   - `direct_products_shortcode()`: Direct registration tanpa class
   - Reason: Maximum reliability, plugin tetap berfungsi minimal

3. **AJAX Gallery tanpa Plugin Dependencies**:
   - Pure JavaScript (no jQuery dependency untuk gallery)
   - WordPress AJAX API (`admin-ajax.php`)
   - Hash URL untuk shareable links
   - Reason: Performance, compatibility, SEO-friendly

4. **Data Persistence Strategy**:
   - Deactivate: Keep ALL data (products, meta, taxonomy)
   - Uninstall: DELETE ALL (`uninstall.php`)
   - Reason: User safety, data recovery possibility

5. **Static Method untuk URL Generation**:
   - `SPS_Configuration::get_product_detail_url($id)`
   - Accessible dari anywhere tanpa instance
   - Reason: Flexibility, backward compatibility

---

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

### Produk Tidak Muncul dengan URL Parameter
1. Pastikan slug produk benar: `/show-product/?product=slug-yang-benar`
2. Cek apakah produk sudah di-publish (bukan draft)
3. Pastikan slug sesuai dengan nama produk (case-sensitive)
4. Coba fallback dengan product_id: `/show-product/?product_id=123`
5. Pastikan halaman custom sudah dikonfigurasi di Settings

### AJAX Gallery Tidak Berfungsi
1. **Buka Developer Tools (F12)** dan cek tab Console untuk error messages
2. **Cek AJAX Response**: Lihat apakah AJAX request berhasil (status 200)
3. **Verify Image IDs**: Pastikan `data-image-id` pada gallery links valid
4. **Check Nonce**: Pastikan nonce security tidak expired (refresh halaman)
5. **Browser Compatibility**: Test di browser yang berbeda (Chrome, Firefox, Safari)
6. **JavaScript Conflicts**: Disable plugin lain untuk cek konflik JavaScript
7. **Cache Issues**: Clear browser cache dan WordPress cache
8. **Console Debugging**: Lihat console logs untuk tracking AJAX requests

### Hash URL (#thumbnail=X) Tidak Berfungsi
1. **URL Format**: Pastikan format URL benar: `#thumbnail=1`, `#thumbnail=2`, dst.
2. **Image Range**: Pastikan angka dalam range yang valid (1-6 untuk 6 gambar)
3. **Page Load**: Pastikan JavaScript sudah loaded saat page load
4. **Hash Detection**: Cek console untuk log "checkHashParameter" function
5. **Gallery Links**: Pastikan gallery memiliki `data-thumbnail` dan `data-image-id`

## 🔄 Changelog

### Version 1.5.0 (Latest - October 2025)
**Major Update: Button Configuration System**
- **🎯 NEW: Configuration Page**: Replaced old Settings with new Configuration page (`class-sps-configuration.php`)
  - Location: `Products → Configuration` (new admin menu)
  - Better UI/UX dengan organized sections
  - Real-time form validation

- **🎨 3-Button System**:
  - **Main Button** dengan mode selector:
    - WhatsApp Mode: Simplified fields (text, number, message, colors)
    - Custom Mode: Full control (text, icon, URL, target, colors)
  - **Custom Button 1**: Full customization (hidden by default)
  - **Custom Button 2**: Full customization (hidden by default)
  
- **🎛️ Configuration Features**:
  - Show/Hide toggle untuk setiap button
  - WordPress Color Picker integration
  - Media Library untuk icon upload
  - Target window selection (_self/_blank)
  - Detail Page Mode selector (default/custom)
  - Custom page dropdown untuk detail redirect

- **💾 Data Management**:
  - 24+ new options di `wp_options` table (prefix: `sps_*`)
  - Default values: Main Button visible dengan "Tanya Produk" text
  - Custom 1 & 2 hidden by default untuk clean layout

- **🔧 Code Architecture Changes**:
  - `class-sps-settings.php`: Commented out (backup only, line 121)
  - `class-sps-configuration.php`: New active class
  - `class-sps-init.php`: Updated to load Configuration instead of Settings
  - Static method: `SPS_Configuration::get_product_detail_url($product_id)`
  - All references updated: `SPS_Settings` → `SPS_Configuration`

- **🗑️ Removed**:
  - Preview button dari configuration forms (cleaner UI)
  - Debug Settings menu (no longer needed)
  - Documentation menu (refer to SHORTCODE-DOCUMENTATION.md)
  - Success alert dari Configuration page save

- **📝 Documentation**:
  - README.md completely rewritten dengan technical details
  - Comprehensive architecture documentation
  - Request flow diagrams untuk better understanding
  - Code structure explanation untuk AI comprehension

### Version 1.4.0
- **Enhanced Gallery Logic**: Updated gallery display logic to hide gallery when ≤2 images (thumbnail + 1 gallery) and show when ≥3 images
- **WhatsApp Button CSS Fix**: Fixed WhatsApp button CSS breaking when gallery shortcode is missing or no gallery images exist
- **Improved Gallery Threshold**: Gallery now hides for 1-2 images and shows for 3+ images for better user experience
- **Independent WhatsApp CSS**: WhatsApp button CSS now loads independently from gallery, preventing styling issues
- **Consistent Button Styling**: WhatsApp buttons maintain proper styling regardless of gallery presence
- **Better Visual Hierarchy**: Cleaner product layout with smarter gallery display logic
- **Enhanced User Experience**: No more broken WhatsApp buttons when gallery is not present
- **Smart Gallery Detection**: Automatic detection of image count with appropriate gallery display
- **Production Ready**: Robust CSS loading system for consistent button appearance

### Version 1.3.9
- **Smart Gallery Display**: Gallery HTML element disembunyikan otomatis ketika hanya ada 1 gambar (thumbnail saja)
- **Enhanced User Experience**: Tidak lagi menampilkan box gallery kosong untuk produk dengan 1 gambar
- **Clean Product Layout**: Layout produk lebih bersih dan professional untuk kasus single image
- **Conditional Gallery Rendering**: Gallery hanya ditampilkan ketika ada multiple images (2+ gambar)
- **Improved Visual Design**: Menghilangkan elemen visual yang tidak perlu dan mengganggu
- **Backward Compatibility**: Semua fitur gallery AJAX dan multiple images tetap berfungsi normal
- **Consistent Behavior**: Logic yang sama diterapkan di main shortcode class dan fallback methods
- **Smart Image Detection**: Otomatis mendeteksi jumlah gambar dan menyesuaikan tampilan gallery
- **Production Ready**: Optimasi UI/UX untuk berbagai skenario penggunaan produk

### Version 1.3.8
- **Tablet Responsiveness Optimization**: Optimasi responsivitas untuk perangkat tablet dengan breakpoint yang lebih granular dan spacing yang optimal
- **Enhanced Tablet Breakpoints**: Breakpoint khusus untuk tablet besar (1024px) dan tablet kecil (992px) untuk layout yang lebih proporsional
- **Improved Product Grid Layout**: Grid layout yang lebih baik untuk tablet dengan 3 kolom (tablet besar) dan 2 kolom (tablet kecil)
- **Fixed Product Title Spacing**: Perbaikan spacing antara judul produk dan tombol "Detail" yang sebelumnya berdempetan di tablet
- **Optimized Product Card Dimensions**: Min-height dan padding yang disesuaikan untuk setiap breakpoint tablet
- **Better Typography Scaling**: Font size dan line height yang optimal untuk readability di berbagai ukuran tablet
- **Enhanced Touch-Friendly Interface**: Tombol dan spacing yang lebih mudah di-tap di perangkat tablet
- **Maintained Mobile & Desktop Compatibility**: Tidak mengubah layout yang sudah optimal di mobile dan desktop
- **Responsive Design Perfected**: Transisi yang smooth antara breakpoint untuk user experience yang konsisten

### Version 1.3.7
- **Gallery Image Size Optimization**: Optimasi ukuran gambar gallery untuk mobile dan tablet dengan ukuran yang lebih proporsional
- **Mobile Gallery Enhancement**: Gallery images di mobile (80px) dan tablet (100px) dengan ukuran yang ideal dan mudah di-tap
- **CSS Override Enhancement**: Menambahkan `!important` pada CSS gallery untuk memastikan ukuran yang konsisten
- **Responsive Gallery Perfected**: Gallery horizontal slider dengan ukuran yang sesuai untuk setiap device
- **Visual Balance Improved**: Ukuran gallery yang tidak terlalu kecil maupun terlalu besar, perfect untuk user experience
- **Touch-Friendly Navigation**: Gallery images dengan ukuran optimal untuk navigasi touch di mobile dan tablet
- **Cross-Device Consistency**: Ukuran gallery yang konsisten dan proporsional di semua device
- **User Experience Enhanced**: Gallery navigation yang smooth dan responsive dengan visual feedback yang jelas

### Version 1.3.6
- **AJAX Gallery System Complete**: Implementasi lengkap gallery interaktif dengan AJAX untuk perubahan gambar utama tanpa reload halaman
- **Hash URL Support Finalized**: URL dengan parameter `#thumbnail=X` untuk direct access ke gambar tertentu dengan reliability tinggi
- **Interactive Gallery Optimized**: Klik gambar di gallery → URL berubah ke `#thumbnail=X` → gambar utama berubah secara instant dengan smooth transition
- **Visual Feedback Enhanced**: Border biru pada gambar aktif di gallery untuk indikator visual yang jelas dan konsisten
- **Responsive Gallery Perfected**: Gallery horizontal slider di mobile/tablet, grid layout di desktop dengan touch-friendly navigation
- **SEO Friendly URLs Finalized**: Hash parameters tidak mempengaruhi SEO dan dapat di-share/bookmark dengan reliability tinggi
- **Enhanced User Experience**: Perubahan gambar yang smooth dan responsif di semua device dengan performance optimal
- **Technical Improvements**: DOM manipulation yang robust dengan penghapusan srcset conflicts dan enhanced error handling
- **Debugging Features Complete**: Console logging untuk troubleshooting dan development dengan comprehensive error reporting
- **Documentation Updated**: README dan changelog diperbarui dengan comprehensive documentation untuk AI dan developer understanding
- **Backward Compatibility Maintained**: Tetap kompatibel dengan semua fitur sebelumnya tanpa breaking changes

### Version 1.3.5
- **AJAX Gallery System**: Implementasi gallery interaktif dengan AJAX untuk perubahan gambar utama tanpa reload halaman
- **Hash URL Support**: URL dengan parameter `#thumbnail=X` untuk direct access ke gambar tertentu (contoh: `#thumbnail=4`)
- **Interactive Gallery**: Klik gambar di gallery → URL berubah ke `#thumbnail=X` → gambar utama berubah secara instant
- **Visual Feedback**: Border biru pada gambar aktif di gallery untuk indikator visual yang jelas
- **Responsive Gallery**: Gallery horizontal slider di mobile/tablet, grid layout di desktop
- **SEO Friendly URLs**: Hash parameters tidak mempengaruhi SEO dan dapat di-share/bookmark
- **Enhanced User Experience**: Perubahan gambar yang smooth dan responsif di semua device
- **Technical Improvements**: DOM manipulation yang robust dengan penghapusan srcset conflicts
- **Debugging Features**: Console logging untuk troubleshooting dan development
- **Backward Compatibility**: Tetap kompatibel dengan semua fitur sebelumnya

### Version 1.3.4
- **Gallery Navigation Removal**: Menghapus tombol panah kiri-kanan dari gallery slider
- **Clean Slider Design**: Gallery horizontal slider yang bersih dengan scrollbar biru sebagai indikator
- **Mobile Optimization**: Gallery slider yang touch-friendly tanpa overlay buttons
- **Responsive Design**: Slider horizontal di mobile/tablet, grid di desktop
- **Visual Enhancement**: Scrollbar biru untuk visual feedback pada mobile gallery

### Version 1.3.2
- **Enhanced Gallery**: Thumbnail otomatis ditambahkan sebagai gambar pertama dalam galeri produk
- **Total 6 Images**: Sekarang galeri menampilkan 1 thumbnail + 5 gambar tambahan = 6 gambar total
- **Improved UX**: Konsistensi tampilan dengan thumbnail sebagai gambar pertama
- **Documentation Update**: Update dokumentasi untuk menjelaskan fitur galeri yang ditingkatkan
- **Better Visual Experience**: Pengalaman visual yang lebih baik dengan thumbnail terintegrasi

### Version 1.3.1
- **WhatsApp Button Text Field Fix**: Perbaikan field "WhatsApp Button Text" yang tidak muncul di admin settings
- **Settings Reliability**: Peningkatan keandalan inisialisasi settings dan field registration
- **User Experience**: Field sekarang dapat digunakan untuk mengatur teks tombol WhatsApp

### Version 1.3.0
- **Reliable URL Parameters**: Changed from `?=` to `?product=` parameter for better reliability
- **Enhanced Product Detection**: More robust product detection with standard parameter format
- **Improved Documentation**: Comprehensive documentation for URL parameters and troubleshooting
- **Better Compatibility**: Enhanced WordPress compatibility with standard parameter handling
- **Admin Integration**: Updated admin "Lihat" links to use reliable parameter format
- **WhatsApp Integration**: All WhatsApp links now use reliable parameter format

### Version 1.2.0
- **SEO Friendly URLs**: Mengubah semua URL dari `product_id` parameter ke `?product=` parameter
- **Enhanced Detail Links**: Tombol "Detail" dan link "Lihat" admin menggunakan clean URLs
- **WhatsApp URL Update**: Pesan WhatsApp menggunakan clean URLs untuk SEO
- **Admin View Integration**: Link "Lihat" di admin menggunakan custom page dengan clean parameter
- **Customizable WhatsApp Button Text**: Pengaturan teks tombol WhatsApp yang dapat dikustomisasi
- **Backward Compatibility**: Masih support `product_id` parameter untuk kompatibilitas

### Version 1.1.0
- **Category Filtering**: Filter otomatis produk berdasarkan URL parameter `?category=category_slug`
- **Enhanced URL Detection**: Shortcode `[sps_products]` otomatis mendeteksi kategori dari URL
- **Improved Flexibility**: Prioritas shortcode attribute over URL parameter untuk fleksibilitas maksimal
- **Updated Documentation**: Dokumentasi lengkap untuk fitur category filtering

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
