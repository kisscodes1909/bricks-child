<?php
/**
 * Bricks Child: Cart Collaterals (Order Summary) element override – list layout instead of table.
 * Keeps WooCommerce output exactly the same, just converts table to div structure.
 *
 * @package Bricks_Child
 */

namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woocommerce_Cart_Collaterals_List extends Woocommerce_Cart_Collaterals {

	/**
	 * Override controls to update CSS selectors for list layout.
	 */
	public function set_controls() {
		parent::set_controls();

		// Remove table-specific controls (not applicable for list)
		unset(
			$this->controls['tableSeparator'],
			$this->controls['tableMargin'],
			$this->controls['tablePadding'],
			$this->controls['tableBorder']
		);

		// Update subtotal/total selectors for list layout
		if ( isset( $this->controls['subtotalTypography'] ) ) {
			$this->controls['subtotalTypography']['css'] = [
				[
					'property' => 'font',
					'selector' => '.order-summary-row.cart-subtotal',
				],
			];
		}

		if ( isset( $this->controls['totalTypography'] ) ) {
			$this->controls['totalTypography']['css'] = [
				[
					'property' => 'font',
					'selector' => '.order-summary-row.order-total',
				],
			];
		}

		// Add section border control
		$this->controls['sectionSeparator'] = [
			'type'  => 'separator',
			'label' => esc_html__( 'Section Dividers', 'flavor-flavor-flavor' ),
		];

		$this->controls['sectionBorder'] = [
			'tab'   => 'content',
			'group' => 'settings',
			'label' => esc_html__( 'Section border', 'flavor-flavor-flavor' ),
			'type'  => 'border',
			'css'   => [
				[
					'property' => 'border-bottom',
					'selector' => '.order-summary-section',
				],
			],
		];

		$this->controls['sectionPadding'] = [
			'tab'   => 'content',
			'group' => 'settings',
			'label' => esc_html__( 'Section padding', 'flavor-flavor-flavor' ),
			'type'  => 'spacing',
			'css'   => [
				[
					'property' => 'padding',
					'selector' => '.order-summary-section',
				],
			],
		];
	}

	/**
	 * Enqueue this element's CSS and JS.
	 */
	public function enqueue_scripts() {
		$dir = get_stylesheet_directory();
		$uri = get_stylesheet_directory_uri();

		$css = $dir . '/assets/css/elements/woocommerce-cart-collaterals.css';
		if ( file_exists( $css ) ) {
			wp_enqueue_style(
				'bricks-child-woocommerce-cart-collaterals',
				$uri . '/assets/css/elements/woocommerce-cart-collaterals.css',
				[],
				filemtime( $css )
			);
		}

		$js = $dir . '/assets/js/elements/woocommerce-cart-collaterals.js';
		if ( file_exists( $js ) ) {
			wp_enqueue_script(
				'bricks-child-woocommerce-cart-collaterals',
				$uri . '/assets/js/elements/woocommerce-cart-collaterals.js',
				[ 'jquery' ],
				filemtime( $js ),
				true
			);
		}
	}

	public function render() {
		$settings = $this->settings;

		Woocommerce_Helpers::maybe_init_cart_context();

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		$cart = WC()->cart;

		add_filter( 'bricks/woocommerce/cart_proceed_label', [ $this, 'proceed_to_checkout_button' ], 10, 1 );

		do_action( 'woocommerce_before_cart_collaterals' );

		if ( isset( $settings['disableCrossSells'] ) ) {
			remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
		}

		$this->set_attribute( '_root', 'class', [ 'cart-collaterals', 'order-summary-list' ] );
		?>
		<div <?php echo $this->render_attributes( '_root' ); ?>>
			<div class="cart_totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">

				<?php do_action( 'woocommerce_before_cart_totals' ); ?>

				<?php if ( ! isset( $settings['hideTitle'] ) ) : ?>
					<h2><?php esc_html_e( 'Cart totals', 'woocommerce' ); ?></h2>
				<?php endif; ?>

				<div class="shop_table shop_table_responsive order-summary-rows">

					<!-- Section 1: Subtotal, Shipping, Tax -->
					<div class="order-summary-section order-summary-section-subtotals">
						<div class="order-summary-row cart-subtotal">
							<div class="order-summary-label"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></div>
							<div class="order-summary-value" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>"><?php wc_cart_totals_subtotal_html(); ?></div>
						</div>

						<?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
							<div class="order-summary-row cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
								<div class="order-summary-label"><?php wc_cart_totals_coupon_label( $coupon ); ?></div>
								<div class="order-summary-value" data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></div>
							</div>
						<?php endforeach; ?>

						<?php if ( $cart->needs_shipping() && $cart->show_shipping() ) : ?>

							<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>

							<?php $this->render_shipping_rows(); ?>

							<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>

						<?php elseif ( $cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>

							<div class="order-summary-row shipping">
								<div class="order-summary-label"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></div>
								<div class="order-summary-value" data-title="<?php esc_attr_e( 'Shipping', 'woocommerce' ); ?>"><?php woocommerce_shipping_calculator(); ?></div>
							</div>

						<?php endif; ?>

						<?php foreach ( $cart->get_fees() as $fee ) : ?>
							<div class="order-summary-row fee">
								<div class="order-summary-label"><?php echo esc_html( $fee->name ); ?></div>
								<div class="order-summary-value" data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></div>
							</div>
						<?php endforeach; ?>

						<?php
						if ( wc_tax_enabled() && ! $cart->display_prices_including_tax() ) {
							$taxable_address = WC()->customer->get_taxable_address();
							$estimated_text  = '';

							if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
								$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
							}

							if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
								foreach ( $cart->get_tax_totals() as $code => $tax ) {
									?>
									<div class="order-summary-row tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
										<div class="order-summary-label"><?php echo esc_html( $tax->label ) . $estimated_text; ?></div>
										<div class="order-summary-value" data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></div>
									</div>
									<?php
								}
							} else {
								?>
								<div class="order-summary-row tax-total">
									<div class="order-summary-label"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; ?></div>
									<div class="order-summary-value" data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></div>
								</div>
								<?php
							}
						}
						?>
					</div>

					<!-- Section 2: Total -->
					<div class="order-summary-section order-summary-section-total">
						<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

						<div class="order-summary-row order-total">
							<div class="order-summary-label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></div>
							<div class="order-summary-value" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>"><?php wc_cart_totals_order_total_html(); ?></div>
						</div>

						<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
					</div>

					<!-- Section 3: Coupon -->
					<?php if ( wc_coupons_enabled() ) : ?>
						<div class="order-summary-section order-summary-section-coupon">
							<div class="order-summary-coupon">
								<div class="order-summary-coupon-toggle" role="button" tabindex="0" aria-expanded="false" aria-controls="order-summary-coupon-content">
									<svg class="coupon-toggle-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
										<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
										<line x1="7" y1="7" x2="7.01" y2="7"></line>
									</svg>
									<span class="coupon-toggle-text"><?php esc_html_e( 'Have a Discount Code?', 'woocommerce' ); ?></span>
									<span class="coupon-toggle-arrow">&#10095;</span>
								</div>
								<div id="order-summary-coupon-content" class="order-summary-coupon-content" style="display: none;">
									<div class="coupon">
										<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
										<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
										<button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
										<?php do_action( 'woocommerce_cart_coupon' ); ?>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<!-- Section 4: Checkout Button -->
					<div class="order-summary-section order-summary-section-checkout">
						<div class="wc-proceed-to-checkout">
							<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
						</div>
					</div>

				</div>

			<?php do_action( 'woocommerce_after_cart_totals' ); ?>

			</div>
		</div>
		<?php

		if ( isset( $settings['disableCrossSells'] ) ) {
			add_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
		}

		remove_filter( 'bricks/woocommerce/cart_proceed_label', [ $this, 'proceed_to_checkout_button' ], 10, 1 );
	}

	/**
	 * Render shipping rows as list items (same as WooCommerce cart-shipping.php but with divs).
	 */
	private function render_shipping_rows() {
		$packages = WC()->shipping()->get_packages();

		foreach ( $packages as $i => $package ) {
			$chosen_method    = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
			$formatted_destination = WC()->countries->get_formatted_address( $package['destination'], ', ' );
			$has_calculated_shipping = WC()->customer->has_calculated_shipping();
			$show_shipping_calculator = apply_filters( 'woocommerce_shipping_show_shipping_calculator', is_cart(), $i, $package );
			$calculator_text = '';

			$product_names = array();

			if ( count( $packages ) > 1 ) {
				foreach ( $package['contents'] as $item_id => $values ) {
					$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
				}
				$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
			}

			$available_methods = $package['rates'];
			$package_name      = apply_filters( 'woocommerce_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Shipping %d', 'shipping packages', 'woocommerce' ), ( $i + 1 ) ) : _x( 'Shipping', 'shipping packages', 'woocommerce' ), $i, $package );
			?>
			<div class="order-summary-row shipping woocommerce-shipping-totals shipping-<?php echo esc_attr( $i ); ?>">
				<div class="order-summary-label"><?php echo wp_kses_post( $package_name ); ?></div>
				<div class="order-summary-value" data-title="<?php echo esc_attr( $package_name ); ?>">
					<?php if ( count( $available_methods ) > 0 ) : ?>
						<ul id="shipping_method_<?php echo esc_attr( $i ); ?>" class="woocommerce-shipping-methods">
							<?php foreach ( $available_methods as $method ) : ?>
								<li>
									<?php
									if ( 1 < count( $available_methods ) ) {
										printf( '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', $i, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), checked( $method->id, $chosen_method, false ) );
									} else {
										printf( '<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />', $i, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ) );
									}
									printf( '<label for="shipping_method_%1$s_%2$s">%3$s</label>', $i, esc_attr( sanitize_title( $method->id ) ), wc_cart_totals_shipping_method_label( $method ) );
									do_action( 'woocommerce_after_shipping_rate', $method, $i );
									?>
								</li>
							<?php endforeach; ?>
						</ul>
						<?php if ( is_cart() ) : ?>
							<p class="woocommerce-shipping-destination">
								<?php
								if ( $formatted_destination ) {
									printf( esc_html__( 'Shipping to %s.', 'woocommerce' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' );
									$calculator_text = esc_html__( 'Change address', 'woocommerce' );
								} else {
									echo wp_kses_post( apply_filters( 'woocommerce_shipping_estimate_html', __( 'Shipping options will be updated during checkout.', 'woocommerce' ) ) );
								}
								?>
							</p>
						<?php endif; ?>
					<?php elseif ( ! $has_calculated_shipping || ! $formatted_destination ) : ?>
						<?php
						if ( is_cart() && 'no' === get_option( 'woocommerce_enable_shipping_calc' ) ) {
							echo wp_kses_post( apply_filters( 'woocommerce_shipping_not_enabled_on_cart_html', __( 'Shipping costs are calculated during checkout.', 'woocommerce' ) ) );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_shipping_may_be_available_html', __( 'Enter your address to view shipping options.', 'woocommerce' ) ) );
						}
						?>
					<?php elseif ( ! is_cart() ) : ?>
						<?php echo wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce' ) ) ); ?>
					<?php else : ?>
						<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_no_shipping_available_html', sprintf( esc_html__( 'No shipping options were found for %s.', 'woocommerce' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' ) ) ); ?>
						<?php $calculator_text = esc_html__( 'Enter a different address', 'woocommerce' ); ?>
					<?php endif; ?>

					<?php if ( $show_shipping_calculator ) : ?>
						<?php woocommerce_shipping_calculator( $calculator_text ); ?>
					<?php endif; ?>

					<?php if ( count( $packages ) > 1 ) : ?>
						<p class="woocommerce-shipping-contents"><small><?php echo esc_html( implode( ', ', $product_names ) ); ?></small></p>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
	}
}
