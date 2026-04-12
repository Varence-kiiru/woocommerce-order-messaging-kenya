<?php
/**
 * Message Logs Page Template
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Get logs.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin pagination parameter.
$wwcc_page = isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1;
$wwcc_limit = 50;
$wwcc_offset = ( $wwcc_page - 1 ) * $wwcc_limit;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom admin report query.
$wwcc_logs = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}wwcc_whatsapp_logs ORDER BY sent_at DESC LIMIT %d OFFSET %d",
		$wwcc_limit,
		$wwcc_offset
	)
);

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom admin report query.
$wwcc_total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wwcc_whatsapp_logs" );
$wwcc_pages = ceil( $wwcc_total / $wwcc_limit );
?>

<div class="wrap">
	<h1><?php esc_html_e( 'WhatsApp Message Logs', 'woocommerce-order-messaging-kenya' ); ?></h1>

	<?php if ( empty( $wwcc_logs ) ) : ?>
		<p><?php esc_html_e( 'No messages sent yet.', 'woocommerce-order-messaging-kenya' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Time', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php esc_html_e( 'Order', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php esc_html_e( 'Phone', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php esc_html_e( 'Type', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php esc_html_e( 'Message', 'woocommerce-order-messaging-kenya' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $wwcc_logs as $wwcc_log ) : ?>
					<tr>
						<td><?php echo esc_html( $wwcc_log->sent_at ); ?></td>
						<td>
							<?php if ( $wwcc_log->order_id ) : ?>
								<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $wwcc_log->order_id . '&action=edit' ) ); ?>">
									#<?php echo esc_html( $wwcc_log->order_id ); ?>
								</a>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $wwcc_log->phone_number ); ?></td>
						<td>
							<span class="badge" style="background: #0073aa; color: white; padding: 4px 8px; border-radius: 3px;">
								<?php echo esc_html( ucfirst( str_replace( '_', ' ', $wwcc_log->message_type ) ) ); ?>
							</span>
						</td>
						<td><?php echo esc_html( substr( $wwcc_log->message, 0, 100 ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $wwcc_pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
				<?php echo wp_kses_post( paginate_links( [
					'base'      => admin_url( 'admin.php?page=wwcc-logs&paged=%#%' ),
					'format'    => '%#%',
					'prev_text' => __( '&laquo; Previous', 'woocommerce-order-messaging-kenya' ),
					'next_text' => __( 'Next &raquo;', 'woocommerce-order-messaging-kenya' ),
					'total'     => $wwcc_pages,
					'current'   => $wwcc_page,
				] ) ); ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>

