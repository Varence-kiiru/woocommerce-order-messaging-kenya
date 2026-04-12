<?php
/**
 * Settings Page Template
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wwcc_settings = WWCC_Settings::get_all();
?>

<div class="wrap">
	<h1><?php esc_html_e( 'WhatsApp WooCommerce Settings', 'woocommerce-order-messaging-kenya' ); ?></h1>

	<form method="post" action="options.php">
		<?php settings_fields( 'wwcc_settings_group' ); ?>

		<div class="wwcc-settings-container">

			<!-- WhatsApp Configuration Tab -->
			<div class="wwcc-tab-content">
				<h2><?php esc_html_e( 'WhatsApp Configuration', 'woocommerce-order-messaging-kenya' ); ?></h2>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="whatsapp_provider"><?php esc_html_e( 'WhatsApp Provider', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<select name="wwcc_settings[whatsapp_provider]" id="whatsapp_provider" onchange="toggleWhatsAppProvider()">
								<option value="twilio" <?php selected( $wwcc_settings['whatsapp_provider'] ?? '', 'twilio' ); ?>>
									<?php esc_html_e( 'Twilio', 'woocommerce-order-messaging-kenya' ); ?>
								</option>
								<option value="meta" <?php selected( $wwcc_settings['whatsapp_provider'] ?? '', 'meta' ); ?>>
									<?php esc_html_e( 'Meta (WhatsApp Business)', 'woocommerce-order-messaging-kenya' ); ?>
								</option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Choose WhatsApp API provider. Twilio is easier to set up, Meta is more scalable.', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="business_phone"><?php esc_html_e( 'Business WhatsApp Number', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<input type="text" name="wwcc_settings[business_phone]" id="business_phone" 
								value="<?php echo esc_attr( $wwcc_settings['business_phone'] ?? '' ); ?>" 
								placeholder="+2547XXXXXXXX" />
							<p class="description">
								<?php esc_html_e( 'Your business WhatsApp number with country code (e.g., +2547XXXXXXXX)', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<!-- Twilio Settings -->
				<div id="twilio-settings" class="provider-settings">
					<h3><?php esc_html_e( 'Twilio Configuration', 'woocommerce-order-messaging-kenya' ); ?></h3>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="twilio_sid"><?php esc_html_e( 'Twilio Account SID', 'woocommerce-order-messaging-kenya' ); ?></label>
							</th>
							<td>
								<input type="password" name="wwcc_settings[twilio_sid]" id="twilio_sid" 
									value="<?php echo esc_attr( $wwcc_settings['twilio_sid'] ?? '' ); ?>" />
								<p class="description">
									<?php esc_html_e( 'Get from Twilio Console', 'woocommerce-order-messaging-kenya' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="twilio_token"><?php esc_html_e( 'Twilio Auth Token', 'woocommerce-order-messaging-kenya' ); ?></label>
							</th>
							<td>
								<input type="password" name="wwcc_settings[twilio_token]" id="twilio_token" 
									value="<?php echo esc_attr( $wwcc_settings['twilio_token'] ?? '' ); ?>" />
								<p class="description">
									<?php esc_html_e( 'Get from Twilio Console', 'woocommerce-order-messaging-kenya' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="twilio_phone"><?php esc_html_e( 'Twilio WhatsApp Number', 'woocommerce-order-messaging-kenya' ); ?></label>
							</th>
							<td>
								<input type="text" name="wwcc_settings[twilio_phone]" id="twilio_phone" 
									value="<?php echo esc_attr( $wwcc_settings['twilio_phone'] ?? '' ); ?>" 
									placeholder="+1234567890" />
								<p class="description">
									<?php esc_html_e( 'Your Twilio WhatsApp sandbox number', 'woocommerce-order-messaging-kenya' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<td colspan="2">
								<button type="button" class="button button-secondary" onclick="testWhatsAppConnection()">
									<?php esc_html_e( 'Test Connection', 'woocommerce-order-messaging-kenya' ); ?>
								</button>
							</td>
						</tr>
					</table>
				</div>

				<!-- Meta Settings -->
				<div id="meta-settings" class="provider-settings" style="display:none;">
					<h3><?php esc_html_e( 'Meta (WhatsApp Business) Configuration', 'woocommerce-order-messaging-kenya' ); ?></h3>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="meta_phone_number_id"><?php esc_html_e( 'Phone Number ID', 'woocommerce-order-messaging-kenya' ); ?></label>
							</th>
							<td>
								<input type="text" name="wwcc_settings[meta_phone_number_id]" id="meta_phone_number_id" 
									value="<?php echo esc_attr( $wwcc_settings['meta_phone_number_id'] ?? '' ); ?>" />
								<p class="description">
									<?php esc_html_e( 'From Meta Business Manager', 'woocommerce-order-messaging-kenya' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="meta_access_token"><?php esc_html_e( 'Access Token', 'woocommerce-order-messaging-kenya' ); ?></label>
							</th>
							<td>
								<textarea name="wwcc_settings[meta_access_token]" id="meta_access_token"><?php echo esc_textarea( $wwcc_settings['meta_access_token'] ?? '' ); ?></textarea>
								<p class="description">
									<?php esc_html_e( 'From Meta Business Manager', 'woocommerce-order-messaging-kenya' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<td colspan="2">
								<button type="button" class="button button-secondary" onclick="testWhatsAppConnection()">
									<?php esc_html_e( 'Test Connection', 'woocommerce-order-messaging-kenya' ); ?>
								</button>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<!-- M-Pesa Configuration -->
			<div class="wwcc-tab-content">
				<h2><?php esc_html_e( 'M-Pesa Configuration (Daraja)', 'woocommerce-order-messaging-kenya' ); ?></h2>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="daraja_consumer_key"><?php esc_html_e( 'Consumer Key', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<input type="password" name="wwcc_settings[daraja_consumer_key]" id="daraja_consumer_key" 
								value="<?php echo esc_attr( $wwcc_settings['daraja_consumer_key'] ?? '' ); ?>" />
							<p class="description">
								<?php esc_html_e( 'Get from Safaricom Daraja', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="daraja_consumer_secret"><?php esc_html_e( 'Consumer Secret', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<input type="password" name="wwcc_settings[daraja_consumer_secret]" id="daraja_consumer_secret" 
								value="<?php echo esc_attr( $wwcc_settings['daraja_consumer_secret'] ?? '' ); ?>" />
							<p class="description">
								<?php esc_html_e( 'Get from Safaricom Daraja', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="mpesa_till_number"><?php esc_html_e( 'Till Number (Shortcode)', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<input type="text" name="wwcc_settings[mpesa_till_number]" id="mpesa_till_number" 
								value="<?php echo esc_attr( $wwcc_settings['mpesa_till_number'] ?? '' ); ?>" 
								placeholder="600XXX" />
							<p class="description">
								<?php esc_html_e( 'Your M-Pesa business till number', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="mpesa_passkey"><?php esc_html_e( 'Online Passkey', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<input type="password" name="wwcc_settings[mpesa_passkey]" id="mpesa_passkey" 
								value="<?php echo esc_attr( $wwcc_settings['mpesa_passkey'] ?? '' ); ?>" />
							<p class="description">
								<?php esc_html_e( 'M-Pesa Online Passkey (obtained from Safaricom)', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<td colspan="2">
							<button type="button" class="button button-secondary" onclick="testMPesaConnection()">
								<?php esc_html_e( 'Test Connection', 'woocommerce-order-messaging-kenya' ); ?>
							</button>
						</td>
					</tr>
				</table>
			</div>

			<!-- Feature Settings -->
			<div class="wwcc-tab-content">
				<h2><?php esc_html_e( 'Feature Settings', 'woocommerce-order-messaging-kenya' ); ?></h2>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="enable_notifications"><?php esc_html_e( 'Enable Order Notifications', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wwcc_settings[enable_notifications]" id="enable_notifications" 
								value="1" <?php checked( $wwcc_settings['enable_notifications'] ?? 1 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Send WhatsApp notifications for new orders, payments, and shipping updates', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="enable_order_creation"><?php esc_html_e( 'Enable Order Creation from WhatsApp', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wwcc_settings[enable_order_creation]" id="enable_order_creation" 
								value="1" <?php checked( $wwcc_settings['enable_order_creation'] ?? 1 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Allow customers to create orders by messaging WhatsApp', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="enable_cart_recovery"><?php esc_html_e( 'Enable Cart Recovery', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wwcc_settings[enable_cart_recovery]" id="enable_cart_recovery" 
								value="1" <?php checked( $wwcc_settings['enable_cart_recovery'] ?? 1 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Send WhatsApp reminders for abandoned carts', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="cart_recovery_hours"><?php esc_html_e( 'Cart Recovery After (Hours)', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<input type="number" name="wwcc_settings[cart_recovery_hours]" id="cart_recovery_hours" 
								value="<?php echo esc_attr( $wwcc_settings['cart_recovery_hours'] ?? 2 ); ?>" 
								min="0" max="72" />
							<p class="description">
								<?php esc_html_e( 'Hours to wait before sending cart recovery message', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="enable_mpesa_stk"><?php esc_html_e( 'Enable M-Pesa STK Push', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wwcc_settings[enable_mpesa_stk]" id="enable_mpesa_stk" 
								value="1" <?php checked( $wwcc_settings['enable_mpesa_stk'] ?? 1 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Send payment prompts to customer phones automatically', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="enable_debug"><?php esc_html_e( 'Enable Debug Mode', 'woocommerce-order-messaging-kenya' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wwcc_settings[enable_debug]" id="enable_debug" 
								value="1" <?php checked( $wwcc_settings['enable_debug'] ?? 0 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Log debug information (disable in production)', 'woocommerce-order-messaging-kenya' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

		</div>

		<?php submit_button(); ?>
	</form>
</div>

<style>
.wwcc-settings-container {
	background: #fff;
	padding: 20px;
	border-radius: 4px;
}

.wwcc-tab-content {
	margin-bottom: 30px;
	padding-bottom: 20px;
	border-bottom: 1px solid #ddd;
}

.wwcc-tab-content:last-child {
	border-bottom: none;
}

.wwcc-tab-content h2 {
	margin-top: 30px;
	margin-bottom: 20px;
}

.provider-settings {
	background: #f9f9f9;
	padding: 15px;
	border-radius: 4px;
	margin-top: 15px;
}

.description {
	color: #666;
	font-size: 12px;
	margin-top: 5px;
}

input[type="text"],
input[type="password"],
input[type="number"],
textarea,
select {
	width: 100%;
	max-width: 500px;
}

textarea {
	min-height: 100px;
	font-family: monospace;
}

#meta-settings {
	display: none;
}
</style>

<script>
function toggleWhatsAppProvider() {
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

function testMPesaConnection() {
	if (confirm('<?php esc_html_e( 'Test M-Pesa connection?', 'woocommerce-order-messaging-kenya' ); ?>')) {
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
			}
		});
	}
}

function testWhatsAppConnection() {
	const phone = prompt('<?php esc_html_e( 'Enter your phone number (with country code):', 'woocommerce-order-messaging-kenya' ); ?>');
	if (!phone) return;

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
		}
	});
}

document.addEventListener('DOMContentLoaded', function() {
	toggleWhatsAppProvider();
});
</script>

