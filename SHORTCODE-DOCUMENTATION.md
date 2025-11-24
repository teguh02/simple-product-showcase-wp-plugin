# Simple Product Showcase - Shortcode Documentation

**Plugin Version:** 1.6.16
**Last Updated:** 2025-01-27

---

## 🎨 Button Configuration

The plugin now features a **new Configuration page** where you can customize up to **3 buttons** for your products:

### Button Types:
1. **Main Button (WhatsApp/Custom)** - Primary action button with 2 modes:
   - **WhatsApp Mode**: Simplified settings (text, phone number, message, colors)
   - **Custom Mode**: Full control (text, icon, URL, target, colors)
   
2. **Custom Button 1** - Second action button (full customization)
3. **Custom Button 2** - Third action button (full customization)

**Access Configuration:** WordPress Admin → Products → **Configuration**

---

## 🚀 Available Shortcodes

### 1. Product Grid/List Display
The main shortcode to display products:

```
[sps_products]
```

### 2. Random Products Grid
Display products in random order:

```
[sps_random_products]
```

**Features:**
- Always displays products in random order
- Displays **1 product per category** from different categories
- Supports `columns` and `limit` parameters
- Every page load shows different products
- Perfect for "Featured Products" or "Recommended Products" sections
- **Example**: If limit="4" and you have 8 categories, it will show 4 products (1 from each of 4 different categories)

### 3. Product Grid with Category Filters
Display products with interactive category filter tabs:

```
[sps_products_with_filters]
```

**Features:**
- Horizontal category filter tabs at the top
- Products only appear after selecting a category
- Active filter highlighted with yellow color (#FDB913)
- URL parameter `?category=slug` added when filter is clicked
- Fully responsive design
- Displays "TERDAPAT FILTER DISINI" message when no category selected

### 4. Product Grid with Sub Category Filters (NEW v1.5.1)
Display products with 2-level category filtering (parent → sub category):

```
[sps_products_sub_category]
```

**Features:**
- **Step 1**: No parameters → Shows message "Silakan pilih kategori utama terlebih dahulu"
- **Step 2**: With `?category=slug` → Shows sub category filter tabs, no products yet
- **Step 3**: With `?category=slug&sub_category=sub-slug` → Shows products from selected sub category
- Horizontal sub category filter tabs with yellow active state (#FDB913)
- URL parameter `?category=slug&sub_category=sub-slug` added when filter is clicked
- Fully responsive design
- Supports hierarchical WordPress taxonomy (parent-child categories)

**How it works:**
1. User visits product page → No category/sub category selected → Shows "please select main category" message
2. User clicks main category (from custom image links) → URL gets `?category=Gun%20Nailer` → Shows sub category filters (e.g., Paslode, Makita, etc.) but NO products yet
3. User clicks sub category → URL gets `?category=Gun%20Nailer&sub_category=Paslode` → Shows products from that sub category
4. Final URL example: `https://yoursite.com/produk/?category=Gun%20Nailer&sub_category=Paslode`

**Important:** This shortcode requires WordPress hierarchical taxonomy. Sub categories must be created as child terms of parent categories in Products → Categories.

### 5. Product Detail Display
New shortcode for displaying individual product details:

```
[sps_detail_products section="title"]
```

This will display all products in a responsive 3-column grid layout.

## 📋 Product Grid Shortcode Parameters

**Applies to:** `[sps_products]`, `[sps_random_products]`, and `[sps_products_with_filters]`

| Parameter | Description | Default | Example |
|-----------|-------------|---------|---------|
| `columns` | Number of columns in the grid (1-6) | 3 | `columns="4"` |
| `category` | Filter by product category slug | - | `category="electronics"` |
| `limit` | Maximum number of products to display | -1 (all) | `limit="6"` |
| `orderby` | Sort products by: title, date, menu_order, price | date | `orderby="title"` |
| `order` | Sort order: ASC or DESC | DESC | `order="ASC"` |
| `show_price` | Show product price: true or false | true | `show_price="false"` |
| `show_description` | Show product description: true or false | true | `show_description="false"` |
| `show_whatsapp` | Show WhatsApp contact button: true or false | true | `show_whatsapp="false"` |
| `show_gallery` | Show product gallery images: true or false | true | `show_gallery="false"` |
| `gallery_style` | Gallery display style: grid, slider, or carousel | grid | `gallery_style="slider"` |

**Note:** For `[sps_products_with_filters]`, the `category` parameter is automatically set based on the selected filter tab.

**Note:** For `[sps_random_products]`, products are always displayed in random order. Only `columns` and `limit` parameters are available.

## 📋 Product Detail Shortcode Parameters

The `[sps_detail_products]` shortcode automatically detects the current product from the URL and displays specific sections.

| Parameter | Description | Options | Example |
|-----------|-------------|---------|---------|
| `section` | Which part of the product to display | `title`, `image`, `description`, `gallery`, `button` | `section="title"` |
| `style` | Display style based on section:<br>• Title: h1, h2, h3, h4, h5<br>• Gallery: grid, slider, carousel | Title: `h1`, `h2`, `h3`, `h4`, `h5`<br>Gallery: `grid`, `slider`, `carousel` | `style="h2"` or `style="slider"` |

### Available Sections:

- **`title`** - Display product title as heading (supports h1, h2, h3, h4, h5 styles)
- **`image`** - Display main product image (featured image)
- **`description`** - Display full product description/content
- **`gallery`** - Display product image gallery (supports grid, slider, carousel styles)
- **`button`** - Display configured action buttons (up to 3 buttons: Main, Custom 1, Custom 2)
- **`gallery`** - Display up to 6 images (1 thumbnail + up to 5 gallery images) (supports grid, slider, carousel styles)
- **`whatsapp`** - Display WhatsApp contact button
- **`button`** - Display all configured buttons (WhatsApp + Custom 1 + Custom 2)

### Title Styles:

- **`h1`** - Display as H1 heading (default)
- **`h2`** - Display as H2 heading
- **`h3`** - Display as H3 heading
- **`h4`** - Display as H4 heading
- **`h5`** - Display as H5 heading

### Gallery Styles:

- **`grid`** - Display up to 6 images in a responsive grid (1 thumbnail + 5 gallery images) (default)
- **`slider`** - Display up to 6 images in a slideshow with navigation (1 thumbnail + 5 gallery images)
- **`carousel`** - Display up to 6 images in a horizontal scrolling carousel (1 thumbnail + 5 gallery images)

## 💡 Usage Examples

### Product Grid Examples

### Basic Grid (3 columns)
```
[sps_products]
```

### Random Products (4 columns, 4 products)
```
[sps_random_products columns="4" limit="4"]
```
Always shows random products, different every page load. Perfect for "Featured Products" sections.

### Product Grid with Category Filters
```
[sps_products_with_filters]
```
Shows horizontal filter tabs with all categories. Products appear only after selecting a category.

### Product Grid with Filters - Custom Layout
```
[sps_products_with_filters columns="4" limit="12"]
```
4-column grid, maximum 12 products per category, with category filter tabs.

### Product Grid with Sub Category - Basic Usage (NEW)
```
[sps_products_sub_category]
```
Default 3-column grid with 2-level category filtering (parent → sub category).

### Product Grid with Sub Category - 4 Columns (NEW)
```
[sps_products_sub_category columns="4"]
```
4-column grid with hierarchical category filtering.

### Product Grid with Sub Category - Custom Settings (NEW)
```
[sps_products_sub_category columns="3" limit="12" orderby="title" order="ASC"]
```
3-column grid, maximum 12 products, alphabetically sorted, with 2-level filtering.

### 4-Column Grid with 8 Products
```
[sps_products columns="4" limit="8"]
```

### Electronics Category Only
```
[sps_products category="electronics" columns="2"]
```

### Alphabetical Order, No Price
```
[sps_products orderby="title" order="ASC" show_price="false"]
```

### Gallery Slider, No WhatsApp
```
[sps_products gallery_style="slider" show_whatsapp="false"]
```

### Minimal Display (Image + Title Only)
```
[sps_products show_price="false" show_description="false" show_whatsapp="false" show_gallery="false"]
```

### Random Products (Alternative)
```
[sps_random_products columns="3" limit="6"]
```
Shows 6 random products in 3 columns, different every page load. Displays **1 product per category** from different categories.

### Featured Products Section
```
[sps_products limit="4" orderby="menu_order" columns="2"]
```

### Latest Products
```
[sps_products limit="6" orderby="date" order="DESC" columns="3"]
```

### Category-Specific with Custom Layout
```
[sps_products category="clothing" columns="2" show_price="true" show_description="false"]
```

## 🖼️ Gallery Images

Each product can have up to 5 additional gallery images:

- **Access:** Products → Edit Product → Product Gallery meta box
- **Storage:** Images are stored as attachment IDs in post meta
- **Display:** Automatically shown on product pages and in shortcodes
- **Styles:** Supports different display styles: grid, slider, carousel

### Gallery Meta Keys
- `_sps_gallery_1` - First gallery image
- `_sps_gallery_2` - Second gallery image
- `_sps_gallery_3` - Third gallery image
- `_sps_gallery_4` - Fourth gallery image
- `_sps_gallery_5` - Fifth gallery image

## 📱 WhatsApp Integration

Automatic WhatsApp contact buttons on all products:

- **Number:** Uses the WhatsApp number configured in plugin settings
- **Message:** Pre-filled message with product link
- **Customization:** Customizable message per product
- **Button Text:** Customizable button text in plugin settings
- **Control:** Can be disabled per shortcode with `show_whatsapp="false"`

### WhatsApp Settings Configuration

In **Products → Settings**, you can configure:

1. **WhatsApp Number:** Your WhatsApp number with country code
2. **Default WhatsApp Message:** Template for the message sent
3. **WhatsApp Button Text:** Customize the button text (default: "Tanya Produk Ini")

### Default Message Template
```
Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}
```

Available placeholders:
- `{product_link}` - Will be replaced with the actual product URL
- `{product_name}` - Will be replaced with the product title

### Button Text Customization Examples
- Default: "Tanya Produk Ini"
- Custom: "Hubungi Kami"
- Custom: "Chat WhatsApp"
- Custom: "Beli Sekarang"
- Custom: "Info Produk"

## 🌐 Frontend Display

### How to Use the Shortcode

1. **Go to any page or post editor**
2. **Add the shortcode:** `[sps_products]`, `[sps_products_with_filters]`, or `[sps_products_sub_category]`
3. **Customize with parameters as needed**
4. **Preview or publish to see the product grid**

### Sub Category Filter Behavior (NEW)

When using `[sps_products_sub_category]`:
- **Initial Load (No URL Parameters):** Shows message "Silakan pilih kategori utama terlebih dahulu" (no filters, no products)
- **With ?category=slug:** Shows sub category filter tabs, displays "Silakan pilih sub kategori untuk melihat produk" message (no products yet)
- **With ?category=slug&sub_category=sub-slug:** Shows products filtered by selected sub category
- **Active Filter:** Highlighted with yellow background (#FDB913) and bold text
- **Deep Linking:** Users can bookmark/share URL with `?category=slug&sub_category=sub-slug` to directly show filtered products
- **Responsive:** Filter tabs stack vertically on mobile, horizontal on desktop
- **Hierarchical Support:** Automatically detects parent-child relationship in WordPress taxonomy

**Setup Required:**
1. Create parent categories (e.g., "Gun Nailer", "Spare Part", "Paku Tembak")
2. Create sub categories as child terms (e.g., "Paslode", "Makita" as children of "Gun Nailer")
3. Create custom images/links on your main product page that link to: `/produk/?category=Gun%20Nailer`
4. Add shortcode `[sps_products_sub_category]` to your product page
5. Users click main category image → See sub category filters → Click sub category → See products

### Category Filter Behavior

When using `[sps_products_with_filters]`:
- **Initial Load:** Shows filter tabs with "TERDAPAT FILTER DISINI" message (no products)
- **Click Filter:** URL changes to `?category=category-slug` and products for that category appear
- **Active Filter:** Highlighted with yellow background (#FDB913) and bold text
- **Deep Linking:** Users can bookmark/share URL with `?category=slug` to directly show filtered products
- **Responsive:** Filter tabs stack vertically on mobile, horizontal on desktop

### Product Pages

Individual product pages are automatically available at:
```
http://yoursite.com/product/product-name/
```

Each product has its own dedicated page with:
- Full product details
- Gallery images
- WhatsApp contact button
- SEO-friendly URLs

## 🎯 Pro Tips

### 1. **Variety is Key**
Use different shortcodes on different pages for variety:
- Homepage: `[sps_random_products limit="6" columns="3"]`
- Products page with filters: `[sps_products_with_filters columns="4"]`
- Category page: `[sps_products category="electronics" columns="2"]`
- Featured section: `[sps_random_products limit="4"]`

### 2. **Category Filtering Options**
Choose the right approach for your needs:
- **Static Filter:** `[sps_products category="shoes"]` - Always shows shoes category
- **URL Filter:** `[sps_products]` - Filters based on `?category=slug` in URL
- **Interactive Filter:** `[sps_products_with_filters]` - User clicks tabs to filter

### 3. **Custom CSS Classes**
The filter shortcode provides these CSS classes for styling:
- `.sps-filter-container` - Main wrapper
- `.sps-filter-tabs` - Filter tabs container
- `.sps-filter-tab` - Individual filter button
- `.sps-filter-tab.active` - Active/selected filter
- `.sps-no-category-message` - Message when no category selected

### 2. **Category Filtering**
Combine with categories to create filtered product sections:
```
[sps_products category="electronics" columns="2" show_price="true"]
[sps_products category="clothing" columns="3" show_description="false"]
```

### 3. **Responsive Design**
Test different column layouts for your theme:
- Mobile: `columns="1"` or `columns="2"`
- Tablet: `columns="2"` or `columns="3"`
- Desktop: `columns="3"` or `columns="4"`

### 4. **Featured Products**
Use limit parameter to create "Featured Products" sections:
```
[sps_products limit="4" orderby="menu_order" columns="2"]
```

### 5. **Performance Optimization**
- Use `limit` to control the number of products displayed
- Combine with `orderby="menu_order"` for custom ordering
- Use `show_description="false"` if you don't need descriptions

## 📋 Product Detail Shortcode Examples

### Basic Usage Examples

**Display Product Title (H1):**
```
[sps_detail_products section="title"]
```

**Display Product Title (H2):**
```
[sps_detail_products section="title" style="h2"]
```

**Display Product Title (H3):**
```
[sps_detail_products section="title" style="h3"]
```

**Display Main Product Image:**
```
[sps_detail_products section="image"]
```

**Display Product Description:**
```
[sps_detail_products section="description"]
```

**Display Gallery in Grid Layout:**
```
[sps_detail_products section="gallery" style="grid"]
```

**Display Gallery as Slider:**
```
[sps_detail_products section="gallery" style="slider"]
```

**Display Gallery as Carousel:**
```
[sps_detail_products section="gallery" style="carousel"]
```

**Display WhatsApp Contact Button:**
```
[sps_detail_products section="whatsapp"]
```

**Display All Buttons (WhatsApp + Custom 1 + Custom 2):**
```
[sps_detail_products section="button"]
```

### Complete Product Detail Page Layout

For a complete product detail page, use multiple shortcodes:

```
[sps_detail_products section="title" style="h2"]
[sps_detail_products section="image"]
[sps_detail_products section="description"]
[sps_detail_products section="gallery" style="slider"]
[sps_detail_products section="whatsapp"]
[sps_detail_products section="button"]
```

**Alternative Layout with H3 Title and Carousel:**
```
[sps_detail_products section="title" style="h3"]
[sps_detail_products section="image"]
[sps_detail_products section="gallery" style="carousel"]
[sps_detail_products section="description"]
[sps_detail_products section="whatsapp"]
```

### How Product Detection Works

The `[sps_detail_products]` shortcode automatically detects the current product using clean URL parameters:

- **SEO Friendly Detection** (`/show-product/?product=product-slug`) - Detects by product slug parameter (primary)
- **Product ID Detection** (`/show-product/?product_id=123`) - Fallback detection by product ID parameter
- **WordPress Permalinks** - Works with any permalink structure (Post name, Numeric, etc.)
- **Automatic Fallback** - If no product is found, displays "No product found" message

**Example URLs:**

**Primary Method (SEO Friendly):**
- `/show-product/?product=paku-tembak-polos-ukuran-50mm-x-2-1mm` - Will display product by slug
- `/show-product/?product=contoh-produk-1` - Will display product by slug
- `/product-detail/?product=smartphone-terbaru-2024` - Will display product by slug

**Fallback Method (Backward Compatibility):**
- `/show-product/?product_id=28` - Will display product with ID 28
- `/product-detail/?product_id=15` - Will display product with ID 15
- `/show-product/?product_id=123` - Will display product with ID 123

**URL Structure Benefits:**
- **SEO Friendly**: Clean URLs with readable product slugs
- **Reliable**: Standard parameter format that works consistently
- **Backward Compatible**: Still supports legacy product_id parameter
- **WordPress Compatible**: Works with any permalink structure

This approach ensures reliable product detection regardless of WordPress permalink settings.

### 6. **Custom Styling**
The shortcode generates HTML with CSS classes that you can customize:
- `.sps-products-grid` - Main container
- `.sps-product-item` - Individual product
- `.sps-product-title` - Product title
- `.sps-product-price` - Product price
- `.sps-product-description` - Product description
- `.sps-whatsapp-button` - WhatsApp button

## 🔧 Advanced Usage

### Conditional Display
You can use the shortcode in different contexts:

```php
// In theme files
echo do_shortcode('[sps_products columns="3" limit="6"]');

// With PHP variables
$category = 'electronics';
$columns = 2;
echo do_shortcode("[sps_products category='{$category}' columns='{$columns}']");
```

### Custom CSS
Add custom CSS to your theme to style the product grid:

```css
.sps-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.sps-product-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}

.sps-product-title {
    font-size: 18px;
    font-weight: bold;
    margin: 10px 0;
}

.sps-product-price {
    color: #0073aa;
    font-size: 16px;
    font-weight: bold;
}

.sps-whatsapp-button {
    background-color: #25D366;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    display: inline-block;
    margin-top: 10px;
}
```

## 🐛 Troubleshooting

### Common Issues

1. **Products not showing**
   - Check if you have products created
   - Verify the product status is "Published"
   - Check if the shortcode is correctly written

2. **Images not displaying**
   - Ensure products have featured images
   - Check if gallery images are properly uploaded
   - Verify image permissions

3. **WhatsApp button not working**
   - Check if WhatsApp number is configured in settings
   - Verify the phone number format (+country code)
   - Test the button on different devices

4. **Styling issues**
   - Check for theme conflicts
   - Add custom CSS to override styles
   - Test on different screen sizes

5. **Product not found with URL parameter**
   - Ensure the product slug exists: `/show-product/?product=correct-slug`
   - Check if the product is published and not in draft
   - Verify the slug matches exactly (case-sensitive)
   - Try fallback with product_id: `/show-product/?product_id=123`
   - Check if custom page is properly configured in settings

### Debug Mode
Enable WordPress debug mode to see any errors:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📞 Support

If you need help with the shortcode:

1. **Check this documentation first**
2. **Test with basic shortcode:** `[sps_products]`
3. **Add parameters one by one** to isolate issues
4. **Check WordPress error logs**
5. **Contact plugin support** with specific error messages

## 🔄 Changelog

### Version 1.6.16 (NEW)
- **Critical Fix**: Match exact CSS and HTML structure from `sps_products` to `sps_products_sub_category`
  - Copy semua CSS class dan media queries dari `sps_products`
  - Validate columns di awal fungsi seperti `sps_products`
  - Match HTML structure dengan `while...endwhile` dan indentasi yang sama
  - Desain card produk sekarang SAMA PERSIS dengan `sps_products`

### Version 1.6.15
- **Refactor**: Use same product card template for category and query search
  - Menggunakan template card produk yang sama untuk kategori dan query search
  - Template konsisten dengan sps-products-grid, sps-product-item, sps-detail-button
  - CSS untuk product card ditambahkan di shortcode products_sub_category
  - Hanya berbeda di cara mendapatkan produk (filter), template sama

### Version 1.6.14
- **Fix**: Update search results to use proper product card layout with Detail button
  - Hasil pencarian sekarang menggunakan card layout yang sama seperti saat kategori dipilih
  - Menambahkan tombol "Detail" dengan styling yang konsisten
  - Menggunakan struktur HTML yang sama dengan shortcode `sps_products`

### Version 1.6.13
- **Fix**: Add query search functionality to `sps_products_sub_category` shortcode
  - Shortcode sekarang menampilkan produk berdasarkan query parameter
  - Jika tidak ada kategori tapi ada query: tampilkan semua produk yang sesuai dengan query
  - Jika ada kategori dan ada query: tampilkan produk dari kategori yang sesuai dengan query
  - Responsive grid layout untuk hasil pencarian

### Version 1.6.12
- **Refactor**: Convert search bar to HTML form with GET method
  - Changed from div to form HTML with method="get"
  - Button changed from type="button" to type="submit"
  - Added name="query" attribute to input field
  - Native form submission with proper URL parameter handling

### Version 1.6.11
- **UI Improvement**: Ganti icon kaca pembesar dengan tombol "Cari" di sebelah kanan search bar
  - Tombol "Cari" dengan background kuning (#FDB913)
  - Hover effect dengan transform dan shadow
  - Responsive design untuk mobile
  - Click handler yang sama seperti Enter key

### Version 1.6.10
- **Feature**: Search Bar Always Visible
  - Search bar sekarang muncul selalu, bahkan sebelum kategori dipilih
  - Autocomplete bekerja untuk semua produk jika kategori belum dipilih
  - Autocomplete bekerja untuk kategori tertentu jika kategori sudah dipilih
  - URL: `https://pakutembak.id/produk` (tanpa parameter category) tetap menampilkan search bar

### Version 1.6.9
- **Bug Fix**: Perbaikan autocomplete search dan Enter key handling
  - Autocomplete sekarang bekerja dengan baik
  - Enter key dan icon search click menambahkan query parameter ke URL
  - Error handling dan debugging improvements
  - CSS improvements untuk icon search

### Version 1.6.8
- **Feature**: Search Bar dengan Autocomplete untuk `[sps_products_sub_category]`
  - Bar pencarian muncul minimal ketika parameter `?category=` sudah ada di URL
  - Autocomplete menampilkan produk dari kategori aktif saja (AJAX-based)
  - Menampilkan gambar thumbnail, judul, dan kategori produk
  - Keyboard navigation (Arrow Up/Down, Enter, Escape)
  - Enter key menambahkan parameter `?query=` ke URL
  - Filter produk berdasarkan query parameter di URL
  - URL format: `?category=Spare%20Part&sub_category=cn-55&query=Seal`
  - Technical: Added AJAX handler `sps_search_products`, JavaScript autocomplete, modified query filter

### Version 1.6.7
- **Bug Fix**: `[sps_random_products]` sekarang mencegah produk duplikat yang muncul
  - Problem: Produk yang sama muncul beberapa kali karena satu produk bisa masuk ke beberapa kategori sekaligus
  - Solution: Track Product IDs yang sudah dipilih dan exclude dari query berikutnya menggunakan `post__not_in`
  - Now: Setiap produk yang ditampilkan adalah unik, tidak ada duplikat
  - Technical: Added `$selected_product_ids` array tracking, double-check dengan `in_array()`, loop protection dengan `max_attempts`
  - Examples: `columns="4" limit="8"` → 8 produk unik tanpa duplikat

### Version 1.6.6
- **Bug Fix**: `[sps_random_products]` sekarang menggunakan `limit` untuk menentukan jumlah total produk
  - Problem: `columns="4" limit="8"` hanya menampilkan 4 produk
  - Solution: `limit` mengontrol jumlah produk, `columns` hanya untuk CSS grid layout
  - Now: `columns="4" limit="8"` = 8 produk dalam grid 4 kolom (2 baris x 4 kolom)
- **Correct Behavior**: 
  - `limit` = jumlah total produk yang ingin ditampilkan
  - `columns` = jumlah kolom per baris (untuk layout grid)
  - Grid CSS otomatis membuat baris baru sesuai jumlah produk
- **Examples**:
  - `columns="4" limit="8"` → 8 produk dalam 4 kolom = 2 baris
  - `columns="3" limit="9"` → 9 produk dalam 3 kolom = 3 baris
  - `columns="2" limit="6"` → 6 produk dalam 2 kolom = 3 baris

### Version 1.6.5
- **Array-Based Random Products**: `[sps_random_products]` sekarang menggunakan array dengan index sesuai `columns` parameter
- **Index Assignment**: Setiap index array diisi dengan 1 produk random dari kategori berbeda
  - `columns="4"` → Array index[0] = kategori 1, index[1] = kategori 2, index[2] = kategori 3, index[3] = kategori 4
  - `columns="5"` → Array dengan 5 index, masing-masing dari kategori berbeda
- **Perfect Distribution**: Memastikan distribusi kategori yang merata sesuai jumlah columns
- **Backward Compatible**: Tetap support `limit` parameter untuk membatasi total produk yang ditampilkan

### Version 1.6.4
- **Smart Random Products**: `[sps_random_products]` sekarang menampilkan **1 produk per kategori** dari kategori yang berbeda
- **Category Distribution**: Ketika limit="4" dan ada 8 kategori, akan menampilkan 4 produk (masing-masing 1 dari 4 kategori berbeda)
- **Perfect for Showcase**: Ideal untuk menampilkan diversity produk dari berbagai kategori
- **Backward Compatible**: Tetap support `columns` dan `limit` parameters seperti sebelumnya

### Version 1.6.3
- **Random Products Shortcode**: New shortcode `[sps_random_products]` untuk menampilkan produk dalam urutan random
- **Dynamic Display**: Setiap page refresh menampilkan produk berbeda
- **Perfect for Featured Section**: Ideal untuk "Featured Products" atau "Recommended Products" sections
- **Parameters Support**: Mendukung `columns` dan `limit` parameters
- **Documentation Update**: Updated documentation dengan informasi shortcode baru

### Version 1.3.2
- **Enhanced Gallery**: Thumbnail automatically added as first image in product gallery
- **Total 6 Images**: Gallery now displays 1 thumbnail + 5 additional images = 6 images total
- **Improved UX**: Consistent display with thumbnail as first image
- **Documentation Update**: Updated documentation to explain enhanced gallery feature
- **Better Visual Experience**: Enhanced visual experience with integrated thumbnail

### Version 1.3.1
- **WhatsApp Button Text Field Fix**: Fixed WhatsApp Button Text field not appearing in admin settings
- **Settings Reliability**: Improved settings initialization and field registration reliability
- **User Experience**: Field now available for customizing WhatsApp button text

### Version 1.3.0
- **Reliable URL Parameters**: Changed from `?=` to `?product=` parameter for better reliability
- **Enhanced Product Detection**: More robust product detection with standard parameter format
- **Improved Documentation**: Comprehensive documentation for URL parameters and troubleshooting
- **Better Compatibility**: Enhanced WordPress compatibility with standard parameter handling
- **Admin Integration**: Updated admin "Lihat" links to use reliable parameter format
- **WhatsApp Integration**: All WhatsApp links now use reliable parameter format

### Version 1.2.0
- **SEO Friendly URLs**: Changed from `product_id` parameter to clean `?product=` parameter
- **Enhanced Detail Links**: All detail links use clean URLs for better SEO
- **WhatsApp URL Update**: WhatsApp messages use clean URLs
- **Customizable WhatsApp Button Text**: New setting to customize button text
- **Admin View Integration**: Admin "Lihat" links use clean URLs
- **Backward Compatibility**: Still supports `product_id` parameter

### Version 1.1.0
- **Category Filtering**: Automatic filtering based on URL parameter `?category=category_slug`
- **Enhanced URL Detection**: Shortcode `[sps_products]` detects categories from URL
- **Improved Flexibility**: Shortcode attribute overrides URL parameter
- **Updated Documentation**: Comprehensive documentation for category filtering

### Version 1.0.0
- Initial release with basic functionality
- Custom Post Type for products
- WhatsApp integration
- Basic shortcode `[sps_products]`
- Template system

---

**Plugin:** Simple Product Showcase  
**Version:** 1.6.3  
**Last Updated:** 2025-01-27
