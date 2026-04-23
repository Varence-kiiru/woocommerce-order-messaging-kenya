<?php
/**
 * Conversations Page Template
 *
 * @package WhatsApp_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Get conversations.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin pagination parameter.
$wwcc_page = isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1;
$wwcc_limit = 50;
$wwcc_offset = ( $wwcc_page - 1 ) * $wwcc_limit;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom admin report query.
$wwcc_conversations = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}wwcc_conversations ORDER BY created_at DESC LIMIT %d OFFSET %d",
		$wwcc_limit,
		$wwcc_offset
	)
);

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom admin report query.
$wwcc_total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wwcc_conversations" );
$wwcc_pages = ceil( $wwcc_total / $wwcc_limit );
?>

<div class="wrap">
	<h1><?php esc_html_e( 'WhatsApp Conversations', 'pesaflow-payments-for-woocommerce' ); ?></h1>

	<?php if ( empty( $wwcc_conversations ) ) : ?>
		<p><?php esc_html_e( 'No conversations found yet.', 'pesaflow-payments-for-woocommerce' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Time', 'pesaflow-payments-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Phone', 'pesaflow-payments-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Order', 'pesaflow-payments-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Action', 'pesaflow-payments-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Message', 'pesaflow-payments-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $wwcc_conversations as $wwcc_conversation ) : ?>
					<tr>
						<td><?php echo esc_html( $wwcc_conversation->created_at ); ?></td>
						<td><?php echo esc_html( $wwcc_conversation->phone_number ); ?></td>
						<td>
							<?php if ( $wwcc_conversation->order_id ) : ?>
								<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $wwcc_conversation->order_id . '&action=edit' ) ); ?>">
									#<?php echo esc_html( $wwcc_conversation->order_id ); ?>
								</a>
							<?php endif; ?>
						</td>
						<td>
							<span class="badge" style="background: #17a2b8; color: white; padding: 4px 8px; border-radius: 3px;">
								<?php echo esc_html( ucfirst( str_replace( '_', ' ', $wwcc_conversation->action ) ) ); ?>
							</span>
						</td>
						<td><?php echo esc_html( substr( $wwcc_conversation->message, 0, 100 ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $wwcc_pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
				<?php echo wp_kses_post( paginate_links( [
					'base'      => admin_url( 'admin.php?page=wwcc-conversations&paged=%#%' ),
					'format'    => '%#%',
					'prev_text' => __( '&laquo; Previous', 'pesaflow-payments-for-woocommerce' ),
					'next_text' => __( 'Next &raquo;', 'pesaflow-payments-for-woocommerce' ),
					'total'     => $wwcc_pages,
					'current'   => $wwcc_page,
				] ) ); ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>

