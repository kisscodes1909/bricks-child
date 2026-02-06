<?php 
/**
 * Register/enqueue custom scripts and styles
 */
add_action( 'wp_enqueue_scripts', function() {
	// Enqueue your files on the canvas & frontend, not the builder panel. Otherwise custom CSS might affect builder)
	if ( ! bricks_is_builder_main() ) {
		wp_enqueue_style( 'bricks-child', get_stylesheet_uri(), ['bricks-frontend'], filemtime( get_stylesheet_directory() . '/style.css' ) );

		// Enqueue base CSS (global classes)
		$base_css = get_stylesheet_directory() . '/assets/css/base.css';
		if ( file_exists( $base_css ) ) {
			wp_enqueue_style(
				'bricks-child-base',
				get_stylesheet_directory_uri() . '/assets/css/base.css',
				[ 'bricks-child' ],
				filemtime( $base_css )
			);
		}

		// B2B King: font override (Conversations, Subaccounts, Offers); separate file for plugin support.
		// Load only when B2B King has enqueued Subaccounts/Offers/Conversations style (My Account or marketplace dashboard).
		// Oswald font already available on site; do not enqueue again.
		if ( function_exists( 'b2bking' ) && ( is_account_page() || ( method_exists( b2bking(), 'is_marketplace_dashboard' ) && b2bking()->is_marketplace_dashboard() ) ) ) {
			$b2bking_css = get_stylesheet_directory() . '/assets/css/b2bking-conversations.css';
			if ( file_exists( $b2bking_css ) ) {
				wp_enqueue_style(
					'bricks-child-b2bking-conversations',
					get_stylesheet_directory_uri() . '/assets/css/b2bking-conversations.css',
					[ 'b2bking_sub_offers_conv' ],
					filemtime( $b2bking_css )
				);
			}
		}
	}
}, 20 );

/**
 * Register custom elements
 */
add_action( 'init', function() {
	$element_files = [
		__DIR__ . '/elements/title.php',
	];

	foreach ( $element_files as $file ) {
		\Bricks\Elements::register_element( $file );
	}
}, 11 );

/**
 * Register custom WooCommerce Bricks elements (override existing)
 */
add_action( 'init', function() {
	// Cart Items List element
	$cart_items_file = get_stylesheet_directory() . '/includes/woocommerce/elements/woocommerce-cart-items.php';
	if ( is_readable( $cart_items_file ) ) {
		require_once $cart_items_file;
		\Bricks\Elements::register_element( $cart_items_file, 'woocommerce-cart-items', 'Bricks\\Woocommerce_Cart_Items_List' );
	}

	// Cart Collaterals (Order Summary) List element
	$cart_collaterals_file = get_stylesheet_directory() . '/includes/woocommerce/elements/woocommerce-cart-collaterals.php';
	if ( is_readable( $cart_collaterals_file ) ) {
		require_once $cart_collaterals_file;
		\Bricks\Elements::register_element( $cart_collaterals_file, 'woocommerce-cart-collaterals', 'Bricks\\Woocommerce_Cart_Collaterals_List' );
	}
}, 11 );

/**
 * Add text strings to builder
 */
add_filter( 'bricks/builder/i18n', function( $i18n ) {
	// For element category 'custom'
	$i18n['custom'] = esc_html__( 'Custom', 'bricks' );

	return $i18n;
} );
