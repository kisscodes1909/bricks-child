<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Override Bricks Cart Collaterals element with list-based layout.
 * 
 * Sections:
 * 1. Subtotal, Shipping, Tax
 * 2. Total
 * 3. Discount/Coupon (collapsible)
 * 4. Checkout Button
 */
class Woocommerce_Cart_Collaterals_List extends Woocommerce_Cart_Collaterals {

	/**
	 * Enqueue element-specific styles and scripts.
	 */
	public function enqueue_scripts() {
		$css_file = get_stylesheet_directory() . '/assets/css/elements/woocommerce-cart-collaterals.css';
		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				'bricks-child-cart-collaterals',
				get_stylesheet_directory_uri() . '/assets/css/elements/woocommerce-cart-collaterals.css',
				[],
				filemtime( $css_file )
			);
		}

		$js_file = get_stylesheet_directory() . '/assets/js/elements/woocommerce-cart-collaterals.js';
		if ( file_exists( $js_file ) ) {
			wp_enqueue_script(
				'bricks-child-cart-collaterals',
				get_stylesheet_directory_uri() . '/assets/js/elements/woocommerce-cart-collaterals.js',
				[ 'jquery' ],
				filemtime( $js_file ),
				true
			);
		}
	}

	/**
	 * Override controls: update CSS selectors for list layout.
	 */
	public function set_controls() {
		parent::set_controls();

		// Update subtotal typography selector
		if ( isset( $this->controls['subtotalTypography'] ) ) {
			$this->controls['subtotalTypography']['css'] = [
				[
					'property' => 'font',
					'selector' => '.order-summary-row.cart-subtotal',
				],
			];
		}

		// Update total typography selector
		if ( isset( $this->controls['totalTypography'] ) ) {
			$this->controls['totalTypography']['css'] = [
				[
					'property' => 'font',
					'selector' => '.order-summary-row.order-total',
				],
			];
		}

		// Remove table-specific controls
		unset( $this->controls['tableSeparator'] );
		unset( $this->controls['tableMargin'] );
		unset( $this->controls['tablePadding'] );
		unset( $this->controls['tableBorder'] );

		// Add section controls
		$this->controls['sectionSeparator'] = [
			'type'  => 'separator',
			'label' => esc_html__( 'Sections', 'bricks' ),
			'tab'   => 'content',
		];

		$this->controls['sectionBorder'] = [
			'tab'   => 'content',
			'type'  => 'border',
			'label' => esc_html__( 'Section border', 'bricks' ),
			'css'   => [
				[
					'property' => 'border-bottom',
					'selector' => '.order-summary-section',
				],
			],
		];

		$this->controls['sectionPadding'] = [
			'tab'   => 'content',
			'type'  => 'spacing',
			'label' => esc_html__( 'Section padding', 'bricks' ),
			'css'   => [
				[
					'property' => 'padding',
					'selector' => '.order-summary-section',
				],
			],
			'placeholder' => [
				'top'    => '1.5rem',
				'right'  => '0',
				'bottom' => '1.5rem',
				'left'   => '0',
			],
		];

		$this->controls['sectionGap'] = [
			'tab'   => 'content',
			'type'  => 'number',
			'units' => true,
			'label' => esc_html__( 'Row gap', 'bricks' ),
			'css'   => [
				[
					'property' => 'gap',
					'selector' => '.order-summary-section',
				],
			],
			'placeholder' => '1.25rem',
		];

		// Coupon section
		$this->controls['couponSeparator'] = [
			'type'  => 'separator',
			'label' => esc_html__( 'Coupon', 'bricks' ),
			'tab'   => 'content',
		];

		$this->controls['couponToggleTypography'] = [
			'tab'   => 'content',
			'type'  => 'typography',
			'label' => esc_html__( 'Toggle text', 'bricks' ),
			'css'   => [
				[
					'property' => 'font',
					'selector' => '.coupon-toggle-text',
				],
			],
		];

		$this->controls['couponIconColor'] = [
			'tab'   => 'content',
			'type'  => 'color',
			'label' => esc_html__( 'Icon color', 'bricks' ),
			'css'   => [
				[
					'property' => 'color',
					'selector' => '.coupon-toggle-icon svg',
				],
				[
					'property' => 'color',
					'selector' => '.coupon-toggle-arrow svg',
				],
			],
		];
	}

	/**
	 * Render the element with list-based layout.
	 */
	public function render() {
		$settings = $this->settings;

		Woocommerce_Helpers::maybe_init_cart_context();

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		add_filter( 'bricks/woocommerce/cart_proceed_label', [ $this, 'proceed_to_checkout_button' ], 10, 1 );

		do_action( 'woocommerce_before_cart_collaterals' );

		if ( isset( $settings['disableCrossSells'] ) ) {
			remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
		}

		$this->set_attribute( '_root', 'class', [ 'cart-collaterals', 'order-summary-list' ] );

		?>
		<div <?php echo $this->render_attributes( '_root' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php
			// Cross sells (if not disabled)
			if ( ! isset( $settings['disableCrossSells'] ) ) {
				woocommerce_cross_sell_display();
			}
			?>

			<div class="cart_totals">
				<?php if ( ! isset( $settings['hideTitle'] ) ) : ?>
					<h2><?php esc_html_e( 'Cart totals', 'woocommerce' ); ?></h2>
				<?php endif; ?>

				<div class="order-summary-rows">

					<?php // Section 1: Subtotal, Shipping, Tax ?>
					<div class="order-summary-section order-summary-section-totals">
						<?php // Subtotal ?>
						<div class="order-summary-row cart-subtotal">
							<span class="order-summary-label"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
							<span class="order-summary-value"><?php wc_cart_totals_subtotal_html(); ?></span>
						</div>

						<?php // Coupons applied ?>
						<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
							<div class="order-summary-row cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
								<span class="order-summary-label"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
								<span class="order-summary-value"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
							</div>
						<?php endforeach; ?>

						<?php // Shipping ?>
						<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
							<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
							<?php $this->render_shipping_row(); ?>
							<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
						<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
							<div class="order-summary-row shipping">
								<span class="order-summary-label"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></span>
								<span class="order-summary-value"><?php woocommerce_shipping_calculator(); ?></span>
							</div>
						<?php endif; ?>

						<?php // Fees ?>
						<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
							<div class="order-summary-row fee">
								<span class="order-summary-label"><?php echo esc_html( $fee->name ); ?></span>
								<span class="order-summary-value"><?php wc_cart_totals_fee_html( $fee ); ?></span>
							</div>
						<?php endforeach; ?>

						<?php // Tax (if itemized) ?>
						<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
							<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
								<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
									<div class="order-summary-row tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
										<span class="order-summary-label"><?php echo esc_html( $tax->label ); ?></span>
										<span class="order-summary-value"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="order-summary-row tax-total">
									<span class="order-summary-label"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
									<span class="order-summary-value"><?php wc_cart_totals_taxes_total_html(); ?></span>
								</div>
							<?php endif; ?>
						<?php endif; ?>

						<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>
					</div>

					<?php // Section 2: Total ?>
					<div class="order-summary-section order-summary-section-total">
						<div class="order-summary-row order-total">
							<span class="order-summary-label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
							<span class="order-summary-value"><?php wc_cart_totals_order_total_html(); ?></span>
						</div>
						<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
					</div>

					<?php // Section 3: Discount/Coupon (collapsible) ?>
					<?php if ( wc_coupons_enabled() ) : ?>
					<div class="order-summary-section order-summary-section-coupon">
						<div class="order-summary-coupon">
							<div class="order-summary-coupon-toggle" role="button" tabindex="0" aria-expanded="false" aria-controls="coupon-content">
								<span class="coupon-toggle-icon">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
										<line x1="7" y1="7" x2="7.01" y2="7"></line>
									</svg>
								</span>
								<span class="coupon-toggle-text"><?php esc_html_e( 'Have a Discount Code?', 'woocommerce' ); ?></span>
								<span class="coupon-toggle-arrow">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<polyline points="9 18 15 12 9 6"></polyline>
									</svg>
								</span>
							</div>
							<div class="order-summary-coupon-content" id="coupon-content" style="display: none;">
								<div class="coupon">
									<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
									<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
									<button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply', 'woocommerce' ); ?></button>
									<?php do_action( 'woocommerce_cart_coupon' ); ?>
								</div>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php // Section 4: Checkout Button ?>
					<div class="order-summary-section order-summary-section-checkout">
						<div class="wc-proceed-to-checkout">
							<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
						</div>
					</div>

				</div>
			</div>
		</div>
		<?php

		if ( isset( $settings['disableCrossSells'] ) ) {
			add_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
		}

		remove_filter( 'bricks/woocommerce/cart_proceed_label', [ $this, 'proceed_to_checkout_button' ], 10, 1 );
	}

	/**
	 * Render shipping row with WooCommerce shipping packages.
	 */
	private function render_shipping_row() {
		$packages = WC()->shipping()->get_packages();
		$first    = true;

		foreach ( $packages as $i => $package ) {
			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
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
			<div class="order-summary-row shipping <?php echo esc_attr( $first ? 'shipping-first' : '' ); ?>">
				<span class="order-summary-label"><?php echo esc_html( $package_name ); ?></span>
				<span class="order-summary-value" data-title="<?php echo esc_attr( $package_name ); ?>">
					<?php if ( $available_methods ) : ?>
						<?php if ( count( $available_methods ) > 1 ) : ?>
							<ul id="shipping_method_<?php echo esc_attr( $i ); ?>" class="woocommerce-shipping-methods">
								<?php foreach ( $available_methods as $method ) : ?>
									<li>
										<?php
										if ( 1 < count( $available_methods ) ) {
											printf( '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', $i, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), checked( $method->id, $chosen_method, false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										} else {
											printf( '<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />', $i, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										}
										printf( '<label for="shipping_method_%1$s_%2$s">%3$s</label>', $i, esc_attr( sanitize_title( $method->id ) ), wc_cart_totals_shipping_method_label( $method ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										do_action( 'woocommerce_after_shipping_rate', $method, $i );
										?>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<?php
							$method = current( $available_methods );
							printf( '%1$s', wc_cart_totals_shipping_method_label( $method ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							printf( '<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />', $i, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						<?php endif; ?>

						<?php if ( is_cart() ) : ?>
							<p class="woocommerce-shipping-destination">
								<?php
								if ( $package['destination']['country'] ) {
									$shipping_estimate_label = apply_filters(
										'woocommerce_shipping_estimate_html',
										sprintf(
											' &mdash; ' . esc_html__( 'Shipping to %s.', 'woocommerce' ),
											WC()->countries->shipping_to_prefix() . ' ' . WC()->countries->countries[ $package['destination']['country'] ]
										)
									);
									echo wp_kses_post( $shipping_estimate_label );
								}
								?>
							</p>
						<?php endif; ?>
					<?php else : ?>
						<?php
						if ( ! is_cart() ) {
							echo wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce' ) ) );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_no_shipping_available_html', sprintf( esc_html__( 'No shipping options were found for %s.', 'woocommerce' ) . ' ', '<strong>' . WC()->countries->countries[ WC()->customer->get_shipping_country() ] . '</strong>' ) ) );
							$calculator_text = esc_html__( 'Enter a different address', 'woocommerce' );
						}
						?>
					<?php endif; ?>

					<?php if ( $package['destination']['postcode'] && is_cart() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
						<?php woocommerce_shipping_calculator( isset( $calculator_text ) ? $calculator_text : '' ); ?>
					<?php endif; ?>
				</span>
			</div>
			<?php
			$first = false;
		}
	}
}
