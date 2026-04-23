/**
 * Cart Recovery Tracking
 *
 * Tracks cart activity for abandoned cart recovery campaigns
 *
 * @package PesaFlow_Payments_For_WooCommerce
 */

(function() {
	'use strict';

	if (typeof jQuery === 'undefined') {
		return;
	}

	var $ = jQuery;
	var cacheKey = 'wwcc_cart_tracked';
	var cartCount = $('span.cart-contents-count').text() || 0;

	if (cartCount > 0) {
		// Cart has items - track them
		localStorage.setItem(cacheKey, JSON.stringify({
			cartCount: cartCount,
			timestamp: Date.now(),
			url: window.location.href
		}));
	}
})();
