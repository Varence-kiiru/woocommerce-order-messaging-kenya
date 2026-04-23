/**
 * WhatsApp WooCommerce Frontend JavaScript
 */

(function($) {
	'use strict';

	// Initialize plugin
	$(document).ready(function() {
		initWhatsAppButton();
	});

	/**
	 * Initialize WhatsApp button functionality
	 */
	function initWhatsAppButton() {
		$(document).on('click', '.wwcc-whatsapp-button', function(e) {
			const $button = $(this);
			const productId = $button.data('product-id');

			if (!productId) {
				return true; // Allow default link behavior
			}

			e.preventDefault();

			// Send AJAX request to get WhatsApp URL
			$.ajax({
				url: wwcc_frontend.ajax_url,
				type: 'POST',
				data: {
					action: 'wwcc_send_whatsapp_order',
					product_id: productId,
					nonce: wwcc_frontend.nonce
				},
				success: function(response) {
					if (response.success) {
						// Open WhatsApp in new window
						window.open(response.data.whatsapp_url, '_blank');
					} else {
						alert('Error: ' + response.data);
					}
				},
				error: function() {
					alert('Failed to process request');
				}
			});
		});
	}

	// Expose to global scope if needed
	window.wwccFrontend = {
		initWhatsAppButton: initWhatsAppButton
	};

})(jQuery);
