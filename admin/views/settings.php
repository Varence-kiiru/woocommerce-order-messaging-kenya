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
	<h1><?php esc_html_e( 'Order Messaging Kenya Settings', 'pesaflow-payments-for-woocommerce' ); ?></h1>

	<form method="post" action="options.php">
		<?php settings_fields( 'wwcc_settings_group' ); ?>

		<div class="wwcc-settings-container">

			<!-- WhatsApp Configuration Tab -->
			<div class="wwcc-tab-content">
				<h2><?php esc_html_e( 'WhatsApp Configuration', 'pesaflow-payments-for-woocommerce' ); ?></h2>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="whatsapp_provider"><?php esc_html_e( 'WhatsApp Provider', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<select name="wwcc_settings[whatsapp_provider]" id="whatsapp_provider" onchange="toggleWhatsAppProvider()">
								<option value="twilio" <?php selected( $wwcc_settings['whatsapp_provider'] ?? '', 'twilio' ); ?>>
									<?php esc_html_e( 'Twilio', 'pesaflow-payments-for-woocommerce' ); ?>
								</option>
								<option value="meta" <?php selected( $wwcc_settings['whatsapp_provider'] ?? '', 'meta' ); ?>>
									<?php esc_html_e( 'Meta (WhatsApp Business)', 'pesaflow-payments-for-woocommerce' ); ?>
								</option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Choose WhatsApp API provider. Twilio is easier to set up, Meta is more scalable.', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="business_phone"><?php esc_html_e( 'Business WhatsApp Number', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="text" name="wwcc_settings[business_phone]" id="business_phone" 
								value="<?php echo esc_attr( $wwcc_settings['business_phone'] ?? '' ); ?>" 
								placeholder="+2547XXXXXXXX" />
							<p class="description">
								<?php esc_html_e( 'Your business WhatsApp number with country code (e.g., +2547XXXXXXXX)', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<!-- Twilio Settings -->
				<div id="twilio-settings" class="provider-settings">
					<h3><?php esc_html_e( 'Twilio Configuration', 'pesaflow-payments-for-woocommerce' ); ?></h3>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="twilio_sid"><?php esc_html_e( 'Twilio Account SID', 'pesaflow-payments-for-woocommerce' ); ?></label>
							</th>
							<td>
								<input type="password" name="wwcc_settings[twilio_sid]" id="twilio_sid" 
									value="<?php echo esc_attr( $wwcc_settings['twilio_sid'] ?? '' ); ?>" />
								<p class="description">
									<?php esc_html_e( 'Get from Twilio Console', 'pesaflow-payments-for-woocommerce' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="twilio_token"><?php esc_html_e( 'Twilio Auth Token', 'pesaflow-payments-for-woocommerce' ); ?></label>
							</th>
							<td>
								<input type="password" name="wwcc_settings[twilio_token]" id="twilio_token" 
									value="<?php echo esc_attr( $wwcc_settings['twilio_token'] ?? '' ); ?>" />
								<p class="description">
									<?php esc_html_e( 'Get from Twilio Console', 'pesaflow-payments-for-woocommerce' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="twilio_phone"><?php esc_html_e( 'Twilio WhatsApp Number', 'pesaflow-payments-for-woocommerce' ); ?></label>
							</th>
							<td>
								<input type="text" name="wwcc_settings[twilio_phone]" id="twilio_phone" 
									value="<?php echo esc_attr( $wwcc_settings['twilio_phone'] ?? '' ); ?>" 
									placeholder="+1234567890" />
								<p class="description">
									<?php esc_html_e( 'Your Twilio WhatsApp sandbox number', 'pesaflow-payments-for-woocommerce' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<td colspan="2">
								<button type="button" class="button button-secondary" onclick="testWhatsAppConnection()">
									<?php esc_html_e( 'Test Connection', 'pesaflow-payments-for-woocommerce' ); ?>
								</button>
							</td>
						</tr>
					</table>
				</div>

				<!-- Meta Settings -->
				<div id="meta-settings" class="provider-settings" style="display:none;">
					<h3><?php esc_html_e( 'Meta (WhatsApp Business) Configuration', 'pesaflow-payments-for-woocommerce' ); ?></h3>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="meta_phone_number_id"><?php esc_html_e( 'Phone Number ID', 'pesaflow-payments-for-woocommerce' ); ?></label>
							</th>
							<td>
								<input type="text" name="wwcc_settings[meta_phone_number_id]" id="meta_phone_number_id" 
									value="<?php echo esc_attr( $wwcc_settings['meta_phone_number_id'] ?? '' ); ?>" />
								<p class="description">
									<?php esc_html_e( 'From Meta Business Manager', 'pesaflow-payments-for-woocommerce' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="meta_access_token"><?php esc_html_e( 'Access Token', 'pesaflow-payments-for-woocommerce' ); ?></label>
							</th>
							<td>
								<textarea name="wwcc_settings[meta_access_token]" id="meta_access_token"><?php echo esc_textarea( $wwcc_settings['meta_access_token'] ?? '' ); ?></textarea>
								<p class="description">
									<?php esc_html_e( 'From Meta Business Manager', 'pesaflow-payments-for-woocommerce' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="webhook_verify_token"><?php esc_html_e( 'Webhook Verify Token', 'pesaflow-payments-for-woocommerce' ); ?></label>
							</th>
							<td>
								<input type="text" name="wwcc_settings[webhook_verify_token]" id="webhook_verify_token"
									value="<?php echo esc_attr( $wwcc_settings['webhook_verify_token'] ?? '' ); ?>" />
								<p class="description">
									<?php esc_html_e( 'Use the same token in your Meta webhook verification setup.', 'pesaflow-payments-for-woocommerce' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<td colspan="2">
								<button type="button" class="button button-secondary" onclick="testWhatsAppConnection()">
									<?php esc_html_e( 'Test Connection', 'pesaflow-payments-for-woocommerce' ); ?>
								</button>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<!-- M-Pesa Configuration -->
			<div class="wwcc-tab-content">
				<h2><?php esc_html_e( 'M-Pesa Configuration (Daraja)', 'pesaflow-payments-for-woocommerce' ); ?></h2>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="daraja_consumer_key"><?php esc_html_e( 'Consumer Key', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="password" name="wwcc_settings[daraja_consumer_key]" id="daraja_consumer_key" 
								value="<?php echo esc_attr( $wwcc_settings['daraja_consumer_key'] ?? '' ); ?>" />
							<p class="description">
								<?php esc_html_e( 'Get from Safaricom Daraja', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="daraja_consumer_secret"><?php esc_html_e( 'Consumer Secret', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="password" name="wwcc_settings[daraja_consumer_secret]" id="daraja_consumer_secret" 
								value="<?php echo esc_attr( $wwcc_settings['daraja_consumer_secret'] ?? '' ); ?>" />
							<p class="description">
								<?php esc_html_e( 'Get from Safaricom Daraja', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="mpesa_till_number"><?php esc_html_e( 'Till Number (Shortcode)', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="text" name="wwcc_settings[mpesa_till_number]" id="mpesa_till_number" 
								value="<?php echo esc_attr( $wwcc_settings['mpesa_till_number'] ?? '' ); ?>" 
								placeholder="600XXX" />
							<p class="description">
								<?php esc_html_e( 'Your M-Pesa business till number', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="mpesa_passkey"><?php esc_html_e( 'Online Passkey', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="password" name="wwcc_settings[mpesa_passkey]" id="mpesa_passkey" 
								value="<?php echo esc_attr( $wwcc_settings['mpesa_passkey'] ?? '' ); ?>" />
							<p class="description">
								<?php esc_html_e( 'M-Pesa Online Passkey (obtained from Safaricom)', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<td colspan="2">
							<button type="button" class="button button-secondary" onclick="testMPesaConnection()">
								<?php esc_html_e( 'Test Connection', 'pesaflow-payments-for-woocommerce' ); ?>
							</button>
						</td>
					</tr>
				</table>
			</div>

			<!-- Feature Settings -->
			<div class="wwcc-tab-content">
				<h2><?php esc_html_e( 'Feature Settings', 'pesaflow-payments-for-woocommerce' ); ?></h2>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="enable_notifications"><?php esc_html_e( 'Enable Order Notifications', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wwcc_settings[enable_notifications]" id="enable_notifications" 
								value="1" <?php checked( $wwcc_settings['enable_notifications'] ?? 1 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Send WhatsApp notifications for new orders, payments, and shipping updates', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="enable_order_creation"><?php esc_html_e( 'Enable Order Creation from WhatsApp', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wwcc_settings[enable_order_creation]" id="enable_order_creation" 
								value="1" <?php checked( $wwcc_settings['enable_order_creation'] ?? 1 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Allow customers to create orders by messaging WhatsApp', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="enable_cart_recovery"><?php esc_html_e( 'Enable Cart Recovery', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wwcc_settings[enable_cart_recovery]" id="enable_cart_recovery" 
								value="1" <?php checked( $wwcc_settings['enable_cart_recovery'] ?? 1 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Send WhatsApp reminders for abandoned carts', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="cart_recovery_hours"><?php esc_html_e( 'Cart Recovery After (Hours)', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="number" name="wwcc_settings[cart_recovery_hours]" id="cart_recovery_hours" 
								value="<?php echo esc_attr( $wwcc_settings['cart_recovery_hours'] ?? 2 ); ?>" 
								min="0" max="72" />
							<p class="description">
								<?php esc_html_e( 'Hours to wait before sending cart recovery message', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="enable_mpesa_stk"><?php esc_html_e( 'Enable M-Pesa STK Push', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wwcc_settings[enable_mpesa_stk]" id="enable_mpesa_stk" 
								value="1" <?php checked( $wwcc_settings['enable_mpesa_stk'] ?? 1 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Send payment prompts to customer phones automatically', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="enable_debug"><?php esc_html_e( 'Enable Debug Mode', 'pesaflow-payments-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wwcc_settings[enable_debug]" id="enable_debug" 
								value="1" <?php checked( $wwcc_settings['enable_debug'] ?? 0 ); ?> />
							<p class="description">
								<?php esc_html_e( 'Log debug information (disable in production)', 'pesaflow-payments-for-woocommerce' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

		</div>

		<?php submit_button(); ?>
	</form>
</div>
