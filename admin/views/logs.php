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

// Get logs
$page = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
$limit = 50;
$offset = ( $page - 1 ) * $limit;

$logs = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}wwcc_whatsapp_logs ORDER BY sent_at DESC LIMIT %d OFFSET %d",
		$limit,
		$offset
	)
);

$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wwcc_whatsapp_logs" );
$pages = ceil( $total / $limit );
?>

<div class="wrap">
	<h1><?php _e( 'WhatsApp Message Logs', 'woocommerce-order-messaging-kenya' ); ?></h1>

	<?php if ( empty( $logs ) ) : ?>
		<p><?php _e( 'No messages sent yet.', 'woocommerce-order-messaging-kenya' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat striped">
			<thead>
				<tr>
					<th><?php _e( 'Time', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php _e( 'Order', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php _e( 'Phone', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php _e( 'Type', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php _e( 'Message', 'woocommerce-order-messaging-kenya' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $logs as $log ) : ?>
					<tr>
						<td><?php echo esc_html( $log->sent_at ); ?></td>
						<td>
							<?php if ( $log->order_id ) : ?>
								<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $log->order_id . '&action=edit' ) ); ?>">
									#<?php echo esc_html( $log->order_id ); ?>
								</a>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $log->phone_number ); ?></td>
						<td>
							<span class="badge" style="background: #0073aa; color: white; padding: 4px 8px; border-radius: 3px;">
								<?php echo esc_html( ucfirst( str_replace( '_', ' ', $log->message_type ) ) ); ?>
							</span>
						</td>
						<td><?php echo esc_html( substr( $log->message, 0, 100 ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
				<?php echo wp_kses_post( paginate_links( [
					'base'      => admin_url( 'admin.php?page=wwcc-logs&paged=%#%' ),
					'format'    => '%#%',
					'prev_text' => __( '&laquo; Previous', 'woocommerce-order-messaging-kenya' ),
					'next_text' => __( 'Next &raquo;', 'woocommerce-order-messaging-kenya' ),
					'total'     => $pages,
					'current'   => $page,
				] ) ); ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
