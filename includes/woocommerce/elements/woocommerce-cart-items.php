<?php
/**
 * Bricks Child: Cart Items element override – list layout instead of table.
 * Reusable across themes: copy this file + registration snippet to any Bricks child.
 *
 * @package Bricks_Child
 */

namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Same element name as parent so existing Cart templates keep using it.
 * Extends parent to keep all controls; only render() output is list-based.
 */
class Woocommerce_Cart_Items_List extends Woocommerce_Cart_Items {

	/**
	 * Override controls to update CSS selectors for list layout (no table/tbody/thead).
	 */
	public function set_controls() {
		// Call parent to get all controls
		parent::set_controls();

		// Update typography selectors: tbody .product-* → .cart-item .product-*
		$typography_fields = [ 'name', 'price', 'quantity', 'subtotal' ];
		foreach ( $typography_fields as $key ) {
			$control_key = "{$key}Typography";
			if ( isset( $this->controls[ $control_key ] ) ) {
				$this->controls[ $control_key ]['css'] = [
					[
						'property' => 'font',
						'selector' => ".cart-item .product-{$key}",
					],
				];
			}
		}

		// Update body background: tbody → .cart-items
		if ( isset( $this->controls['bodyBackground'] ) ) {
			$this->controls['bodyBackground']['css'] = [
				[
					'property' => 'background-color',
					'selector' => '.cart-items',
				],
			];
		}

		// Update body border: tbody tr → .cart-item
		if ( isset( $this->controls['bodyBorder'] ) ) {
			$this->controls['bodyBorder']['css'] = [
				[
					'property' => 'border',
					'selector' => '.cart-item',
				],
			];
		}

		// Remove table header controls (not applicable for list layout)
		unset(
			$this->controls['headSeparator'],
			$this->controls['headHide'],
			$this->controls['headBackground'],
			$this->controls['headBorder'],
			$this->controls['headTypography']
		);

		// Update remove position selector
		if ( isset( $this->controls['removePosition'] ) ) {
			$this->controls['removePosition']['css'] = [
				[
					'property' => '',
					'selector' => '.cart-item .product-remove',
				],
				[
					'property' => 'position',
					'selector' => '.cart-item .product-remove',
					'value'    => 'absolute',
				],
			];
		}
	}

	/**
	 * Enqueue this element's CSS.
	 */
	public function enqueue_scripts() {
		$dir = get_stylesheet_directory();
		$uri = get_stylesheet_directory_uri();

		$css = $dir . '/assets/css/elements/woocommerce-cart-items.css';
		if ( file_exists( $css ) ) {
			wp_enqueue_style(
				'bricks-child-woocommerce-cart-items',
				$uri . '/assets/css/elements/woocommerce-cart-items.css',
				[],
				filemtime( $css )
			);
		}
	}

	public function render() {
		$settings = $this->settings;

		Woocommerce_Helpers::maybe_init_cart_context();

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		Woocommerce_Helpers::maybe_populate_cart_contents();

		add_filter( 'woocommerce_cart_item_permalink', [ $this, 'woocommerce_cart_item_permalink' ], 10, 3 );
		add_filter( 'woocommerce_cart_item_thumbnail', [ $this, 'woocommerce_cart_item_thumbnail' ], 10, 3 );

		$this->set_attribute( '_root', 'class', 'woocommerce-cart-form' );
		$this->set_attribute( '_root', 'action', esc_url( wc_get_cart_url() ) );
		$this->set_attribute( '_root', 'method', 'post' );
		?>
		<form <?php echo $this->render_attributes( '_root' ); ?>>
			<?php do_action( 'woocommerce_before_cart_table' ); ?>

			<div class="woocommerce-cart-form__contents cart-items">
				<?php do_action( 'woocommerce_before_cart_contents' ); ?>

				<?php
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
					$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

					if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						continue;
					}

					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<div class="cart-item woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<div class="cart-item__thumbnail product-thumbnail">
							<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
							if ( ! $product_permalink ) {
								echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							} else {
								printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</div>

						<div class="cart-item__content">
							<div class="cart-item__details">
								<div class="cart-item__header">
									<div class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
										<?php
										if ( ! $product_permalink ) {
											echo wp_kses_post( $product_name . '&nbsp;' );
										} else {
											echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
										}
										do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
										?>
									</div>
									<div class="product-remove">
										<?php
										echo apply_filters(
											'woocommerce_cart_item_remove_link',
											sprintf(
												'<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
												esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
												esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
												esc_attr( $product_id ),
												esc_attr( $_product->get_sku() )
											),
											$cart_item_key
										);
										?>
									</div>
								</div>

								<?php if ( $_product->get_sku() ) { ?>
									<div class="cart-item__sku"><?php echo esc_html( __( 'SKU:', 'woocommerce' ) . ' ' . $_product->get_sku() ); ?></div>
								<?php } ?>

								<div class="cart-item__variation">
									<?php
									echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
										echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
									}
									?>
								</div>

								<?php
								$availability = $_product->get_availability();
								if ( ! empty( $availability['availability'] ) ) {
									?>
									<div class="cart-item__stock"><?php echo wp_kses_post( $availability['availability'] ); ?></div>
								<?php } ?>
							</div>

							<div class="cart-item__footer">
								<div class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
									<?php
									if ( $_product->is_sold_individually() ) {
										$min_quantity = 1;
										$max_quantity = 1;
									} else {
										$min_quantity = 0;
										$max_quantity = $_product->get_max_purchase_quantity();
									}
									$product_quantity = woocommerce_quantity_input(
										[
											'input_name'   => "cart[{$cart_item_key}][qty]",
											'input_value'  => $cart_item['quantity'],
											'max_value'    => $max_quantity,
											'min_value'    => $min_quantity,
											'product_name' => $product_name,
										],
										$_product,
										false
									);
									echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</div>
								<div class="cart-item__prices">
									<div class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
										<?php
										echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo ' &times; ' . esc_html( $cart_item['quantity'] );
										?>
									</div>
									<div class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
										<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</div>
								</div>
							</div>
						</div>

					</div>
					<?php
				}
				?>

				<?php do_action( 'woocommerce_cart_contents' ); ?>

				<div class="cart-items__actions actions">
					<?php if ( wc_coupons_enabled() && ! isset( $settings['hideCoupon'] ) ) { ?>
						<div class="coupon">
							<label for="coupon_code"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
							<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
							<button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
							<?php do_action( 'woocommerce_cart_coupon' ); ?>
						</div>
					<?php } ?>

					<button type="submit" class="button" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

					<?php do_action( 'woocommerce_cart_actions' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				</div>

				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
			</div>

			<?php do_action( 'woocommerce_after_cart_table' ); ?>
		</form>
		<?php
		remove_filter( 'woocommerce_cart_item_permalink', [ $this, 'woocommerce_cart_item_permalink' ] );
		remove_filter( 'woocommerce_cart_item_thumbnail', [ $this, 'woocommerce_cart_item_thumbnail' ] );
	}
}
