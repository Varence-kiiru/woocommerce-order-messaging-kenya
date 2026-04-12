/**
 * WhatsApp WooCommerce Frontend JavaScript
 */

(function($) {
	'use strict';

	// Initialize plugin
	$(document).ready(function() {
		initWhatsAppButton();
		initProductPageIntegration();
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

	/**
	 * Initialize product page integrations
	 */
	function initProductPageIntegration() {
		// Add WhatsApp button after add to cart button
		if ($('button[name="add-to-cart"]').length) {
			$('button[name="add-to-cart"]').after(function() {
				const productId = $(this).data('product_id') || $('input[name="product_id"]').val();
				return buildWhatsAppButton(productId);
			});
		}
	}

	/**
	 * Build WhatsApp button HTML
	 */
	function buildWhatsAppButton(productId) {
		if (!productId) return '';

		return '<a href="#" class="wwcc-whatsapp-button" data-product-id="' + productId + '">' +
			'<svg class="wwcc-whatsapp-button-icon" viewBox="0 0 24 24" fill="currentColor">' +
			'<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.272-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-5.031 1.378l-.36-.187L9.39 1.896l.461.18a9.879 9.879 0 018.354 8.703v.462L21.25 10.854a9.879 9.879 0 01-9.2 5.125z"/>' +
			'</svg> Order via WhatsApp</a>';
	}

	// Expose to global scope if needed
	window.wwccFrontend = {
		initWhatsAppButton: initWhatsAppButton,
		buildWhatsAppButton: buildWhatsAppButton
	};

})(jQuery);
