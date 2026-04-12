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

// Get conversations
$page = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
$limit = 50;
$offset = ( $page - 1 ) * $limit;

$conversations = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}wwcc_conversations ORDER BY created_at DESC LIMIT %d OFFSET %d",
		$limit,
		$offset
	)
);

$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wwcc_conversations" );
$pages = ceil( $total / $limit );
?>

<div class="wrap">
	<h1><?php _e( 'WhatsApp Conversations', 'woocommerce-order-messaging-kenya' ); ?></h1>

	<?php if ( empty( $conversations ) ) : ?>
		<p><?php _e( 'No conversations yet.', 'woocommerce-order-messaging-kenya' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat striped">
			<thead>
				<tr>
					<th><?php _e( 'Date', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php _e( 'Phone', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php _e( 'Order', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php _e( 'Action', 'woocommerce-order-messaging-kenya' ); ?></th>
					<th><?php _e( 'Message', 'woocommerce-order-messaging-kenya' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $conversations as $conv ) : ?>
					<tr>
						<td><?php echo esc_html( $conv->created_at ); ?></td>
						<td><?php echo esc_html( $conv->phone_number ); ?></td>
						<td>
							<?php if ( $conv->order_id ) : ?>
								<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $conv->order_id . '&action=edit' ) ); ?>">
									#<?php echo esc_html( $conv->order_id ); ?>
								</a>
							<?php endif; ?>
						</td>
						<td>
							<span class="badge" style="background: #17a2b8; color: white; padding: 4px 8px; border-radius: 3px;">
								<?php echo esc_html( ucfirst( str_replace( '_', ' ', $conv->action ) ) ); ?>
							</span>
						</td>
						<td><?php echo esc_html( substr( $conv->message, 0, 100 ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
				<?php echo wp_kses_post( paginate_links( [
					'base'      => admin_url( 'admin.php?page=wwcc-conversations&paged=%#%' ),
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
