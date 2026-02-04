# Bricks Child Theme - Clean WooCommerce

A clean, lightweight WooCommerce theme built on top of Bricks Builder. This theme extends Bricks' native WooCommerce elements instead of rebuilding from scratch.

## Philosophy

- **Reuse, don't rebuild** - Extend existing Bricks elements via class inheritance
- **Clean & lightweight** - No unnecessary dependencies, minimal CSS/JS
- **WooCommerce native** - Use WooCommerce templates, functions, and hooks
- **Mobile first** - Responsive layouts that work on all devices

## What's Customized

### Cart Items Element (`woocommerce-cart-items`)

Converts the default table-based cart layout to a modern list-based layout:

- **Two-column layout**: Thumbnail (left) + Content (right)
- **Content structure**:
  - Header: Product name + Remove button
  - Details: SKU, Variation, Stock status
  - Footer: Quantity input + Prices (unit price × qty, subtotal)
- **BEM naming**: `.cart-item`, `.cart-item__thumbnail`, `.cart-item__content`, etc.

### Cart Collaterals Element (`woocommerce-cart-collaterals`)

Converts the order summary from table to organized sections:

- **Section 1**: Subtotal, Shipping, Tax
- **Section 2**: Order Total
- **Section 3**: Discount Code (collapsible)
- **Section 4**: Checkout Button
- **Bricks controls**: Section border & padding customizable via builder

## File Structure

```
bricks-child/
├── assets/
│   ├── css/elements/           # CSS per element (BEM, layout only)
│   │   ├── woocommerce-cart-items.css
│   │   └── woocommerce-cart-collaterals.css
│   └── js/elements/            # JS per element (jQuery, WC compatible)
│       └── woocommerce-cart-collaterals.js
├── elements/                   # Custom Bricks elements
│   └── title.php
├── includes/woocommerce/
│   └── elements/               # WooCommerce element overrides
│       ├── woocommerce-cart-items.php
│       └── woocommerce-cart-collaterals.php
├── functions.php               # Element registration
├── style.css                   # Theme metadata
└── README.md
```

## How Element Override Works

Instead of creating new elements, we extend Bricks' existing classes:

```php
// Extend parent class to inherit all controls
class Woocommerce_Cart_Items_List extends Woocommerce_Cart_Items {
    public function set_controls() {
        parent::set_controls();  // Keep original controls
        // Update CSS selectors for new HTML structure
    }
    
    public function render() {
        // Custom HTML output (list instead of table)
    }
}

// Register with SAME element name to override
\Bricks\Elements::register_element(
    $file, 
    'woocommerce-cart-items',  // Original element name
    'Bricks\\Woocommerce_Cart_Items_List'  // New class
);
```

## CSS Guidelines

- **Layout only** - No colors, fonts, or decorative styles
- **BEM naming** - `.block__element--modifier`
- **Mobile first** - Base styles for mobile, `min-width` media queries for desktop
- **Semantic classes** - Describe purpose, not appearance

## JavaScript Guidelines

- **jQuery** - Required for WooCommerce compatibility
- **WC Events** - Listen to `updated_cart_totals`, `wc_update_cart`
- **Re-init after AJAX** - WooCommerce replaces DOM on cart update

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- Bricks Builder 1.9+

## Development

See `.cursor/rules/woocommerce-bricks.mdc` for coding standards and guidelines.
