(function($) {
	'use strict';

	function initCouponToggle() {
		$('.order-summary-coupon-toggle').off('click keydown').on('click keydown', function(e) {
			if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) {
				return;
			}
			e.preventDefault();

			var $toggle = $(this);
			var $content = $toggle.next('.order-summary-coupon-content');
			var isExpanded = $toggle.attr('aria-expanded') === 'true';

			$toggle.attr('aria-expanded', !isExpanded);
			$content.slideToggle(200);
		});
	}

	$(document).ready(function() {
		initCouponToggle();
	});

	// Re-init after cart update (AJAX)
	$(document.body).on('updated_cart_totals', function() {
		initCouponToggle();
	});

})(jQuery);
