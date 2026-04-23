/**
 * Admin Settings Page Scripts
 *
 * Handles settings page functionality and provider switching
 *
 * @package Order_Messaging_For_WooCommerce_Kenya
 */

/**
 * Toggle WhatsApp provider settings visibility
 */
function toggleWhatsAppProvider() {
	'use strict';
	const provider = document.getElementById('whatsapp_provider').value;
	const twilioSettings = document.getElementById('twilio-settings');
	const metaSettings = document.getElementById('meta-settings');

	if (provider === 'twilio') {
		twilioSettings.style.display = 'block';
		metaSettings.style.display = 'none';
	} else {
		twilioSettings.style.display = 'none';
		metaSettings.style.display = 'block';
	}
}

/**
 * Test M-Pesa connection
 */
function testMPesaConnection() {
	'use strict';
	if (confirm(wwccAdmin.strings.test_mpesa)) {
		jQuery.ajax({
			url: wwccAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wwcc_test_mpesa_connection',
				nonce: wwccAdmin.nonce
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message);
				} else {
					alert('Error: ' + response.data);
				}
			},
			error: function() {
				alert(wwccAdmin.strings.error);
			}
		});
	}
}

/**
 * Test WhatsApp connection
 */
function testWhatsAppConnection() {
	'use strict';
	const phone = prompt(wwccAdmin.strings.enter_phone);
	if (!phone) {
		return;
	}

	jQuery.ajax({
		url: wwccAdmin.ajaxUrl,
		type: 'POST',
		data: {
			action: 'wwcc_test_whatsapp_connection',
			phone: phone,
			nonce: wwccAdmin.nonce
		},
		success: function(response) {
			if (response.success) {
				alert(response.data.message);
			} else {
				alert('Error: ' + response.data);
			}
		},
		error: function() {
			alert(wwccAdmin.strings.error);
		}
	});
}

/**
 * Initialize settings page
 */
document.addEventListener('DOMContentLoaded', function() {
	'use strict';
	toggleWhatsAppProvider();
});
