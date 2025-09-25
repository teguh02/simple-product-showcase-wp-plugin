# 📚 Simple Product Showcase - Shortcode Documentation

**Plugin Version:** 1.0.0  
**Last Updated:** 2025-09-25

## 🚀 Basic Usage

The main shortcode to display products:

```
[sps_products]
```

This will display all products in a responsive 3-column grid layout.

## 📋 All Available Parameters

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

## 💡 Usage Examples

### Basic Grid (3 columns)
```
[sps_products]
```

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
- **Control:** Can be disabled per shortcode with `show_whatsapp="false"`

### Default Message Template
```
Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}
```

Available placeholders:
- `{product_link}` - Will be replaced with the actual product URL
- `{product_name}` - Will be replaced with the product title

## 🌐 Frontend Display

### How to Use the Shortcode

1. **Go to any page or post editor**
2. **Add the shortcode:** `[sps_products]`
3. **Customize with parameters as needed**
4. **Preview or publish to see the product grid**

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
- Homepage: `[sps_products limit="6" columns="3"]`
- Category page: `[sps_products category="electronics" columns="2"]`
- Featured section: `[sps_products limit="4" orderby="menu_order"]`

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

---

**Plugin:** Simple Product Showcase  
**Version:** 1.0.0  
**Last Updated:** 2025-09-25
