<?php
/**
 * B2B King integration: enqueue child theme styles.
 * Load only on frontend (not in Bricks builder). Styles load after plugin styles via dependencies.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function() {
	if ( bricks_is_builder_main() ) {
		return;
	}

	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$int = $dir . '/assets/css/integrations/b2bking';

	// Conversations, Subaccounts, Offers: font override. Load when B2B King enqueued (My Account or marketplace dashboard).
	if ( function_exists( 'b2bking' ) && ( is_account_page() || ( method_exists( b2bking(), 'is_marketplace_dashboard' ) && b2bking()->is_marketplace_dashboard() ) ) ) {
		$conversations = $int . '/conversations.css';
		if ( file_exists( $conversations ) ) {
			wp_enqueue_style(
				'bricks-child-b2bking-conversations',
				$uri . '/assets/css/integrations/b2bking/conversations.css',
				[ 'b2bking_sub_offers_conv' ],
				filemtime( $conversations )
			);
		}
	}

	// Bulk Order: font override. Load after b2bking_bulkorder when present (shortcode or My Account).
	$bulkorder = $int . '/bulkorder.css';
	if ( file_exists( $bulkorder ) ) {
		wp_enqueue_style(
			'bricks-child-b2bking-bulkorder',
			$uri . '/assets/css/integrations/b2bking/bulkorder.css',
			[ 'b2bking_bulkorder' ],
			filemtime( $bulkorder )
		);
	}
}, 20 );
