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
	<h1><?php _e( 'WhatsApp Conversations', 'whatsapp-woocommerce' ); ?></h1>

	<?php if ( empty( $conversations ) ) : ?>
		<p><?php _e( 'No conversations yet.', 'whatsapp-woocommerce' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat striped">
			<thead>
				<tr>
					<th><?php _e( 'Date', 'whatsapp-woocommerce' ); ?></th>
					<th><?php _e( 'Phone', 'whatsapp-woocommerce' ); ?></th>
					<th><?php _e( 'Order', 'whatsapp-woocommerce' ); ?></th>
					<th><?php _e( 'Action', 'whatsapp-woocommerce' ); ?></th>
					<th><?php _e( 'Message', 'whatsapp-woocommerce' ); ?></th>
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
					<?php echo paginate_links( [
						'base'      => admin_url( 'admin.php?page=wwcc-conversations&paged=%#%' ),
						'format'    => '%#%',
						'prev_text' => __( '&laquo; Previous' ),
						'next_text' => __( 'Next &raquo;' ),
						'total'     => $pages,
						'current'   => $page,
					] ); ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
