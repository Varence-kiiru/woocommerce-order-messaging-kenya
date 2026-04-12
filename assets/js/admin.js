/**
 * WhatsApp WooCommerce Admin JavaScript
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		initAdminHandlers();
		initOrderMetaBox();
	});

	/**
	 * Initialize admin event handlers
	 */
	function initAdminHandlers() {
		// Charge order button
		$(document).on('click', '.wwcc-charge-order-btn', function(e) {
			e.preventDefault();
			const orderId = $(this).data('order-id');
			chargeOrder(orderId);
		});

		// Delete log entry
		$(document).on('click', '.wwcc-delete-log', function(e) {
			e.preventDefault();
			if (confirm(wwccAdmin.strings.confirm_delete)) {
				const logId = $(this).data('log-id');
				deleteLog(logId);
			}
		});
	}

	/**
	 * Initialize order meta box on edit order page
	 */
	function initOrderMetaBox() {
		// Add WhatsApp integration to order page
		if ($('#woocommerce-order-data').length) {
			addWhatsAppOrderMetaBox();
		}
	}

	/**
	 * Add WhatsApp meta box to order page
	 */
	function addWhatsAppOrderMetaBox() {
		const orderId = $('input[name="post_ID"]').val();
		if (!orderId) return;

		const metaBox = $('<div class="postbox" style="margin-top: 20px;">' +
			'<button type="button" class="handlediv button-link" aria-expanded="true"></button>' +
			'<h2 class="hndle"><span>🔔 WhatsApp Actions</span></h2>' +
			'<div class="inside">' +
			'<p>Manage WhatsApp communications for this order</p>' +
			'<div class="wwcc-order-actions">' +
			'<button type="button" class="button button-primary wwcc-charge-order-btn" data-order-id="' + orderId + '">' +
			'📱 Send M-Pesa Payment Prompt' +
			'</button> ' +
			'<button type="button" class="button wwcc-send-message-btn" data-order-id="' + orderId + '">' +
			'✉️ Send Custom Message' +
			'</button>' +
			'</div>' +
			'</div>' +
			'</div>');

		metaBox.insertAfter('#woocommerce-order-data');
	}

	/**
	 * Charge order via M-Pesa
	 */
	function chargeOrder(orderId) {
		const $button = $('[data-order-id="' + orderId + '"].wwcc-charge-order-btn');
		const originalText = $button.text();

		$button.prop('disabled', true).text(wwccAdmin.strings.testing);

		$.ajax({
			url: wwccAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wwcc_charge_order',
				order_id: orderId,
				nonce: wwccAdmin.nonce
			},
			success: function(response) {
				if (response.success) {
					alert(wwccAdmin.strings.message_sent);
					console.log(response.data);
				} else {
					alert(wwccAdmin.strings.error + ': ' + response.data);
				}
			},
			error: function() {
				alert(wwccAdmin.strings.error);
			},
			complete: function() {
				$button.prop('disabled', false).text(originalText);
			}
		});
	}

	/**
	 * Delete log entry
	 */
	function deleteLog(logId) {
		$.ajax({
			url: wwccAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wwcc_delete_log',
				log_id: logId,
				nonce: wwccAdmin.nonce
			},
			success: function(response) {
				if (response.success) {
					$('[data-log-id="' + logId + '"]').closest('tr').fadeOut(300, function() {
						$(this).remove();
					});
				}
			}
		});
	}

	// Expose to global
	window.wwccAdmin = window.wwccAdmin || {};
	window.wwccAdmin.chargeOrder = chargeOrder;
	window.wwccAdmin.deleteLog = deleteLog;

})(jQuery);
