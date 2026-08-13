<?php
/**
 * Renders the order-screen pre-order UI: meta box, badges, and list column.
 *
 * @package WC_Preorder_Deposit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the pre-order meta box, badges, and order list column to wp-admin.
 */
class WCPD_Admin {

	/**
	 * Registers all admin-side hooks for pre-order orders.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'col_add' ), 20 );
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'col_show' ), 20, 2 );
		add_filter( 'woocommerce_admin_order_data_after_order_details', array( __CLASS__, 'order_detail_badge' ) );
	}

	/**
	 * Enqueues the admin CSS/JS on the order list and order edit screens.
	 *
	 * @param string $hook The current admin page hook suffix.
	 * @return void
	 */
	public static function assets( $hook ) {
		global $post_type, $post;
		if ( 'edit.php' === $hook && 'shop_order' === $post_type ) {
			wp_enqueue_style( 'wcpd-admin', WCPD_URL . 'assets/admin.css', array(), WCPD_VERSION );
		}

		if ( 'post.php' === $hook && isset( $post ) && 'shop_order' === $post->post_type ) {
			wp_enqueue_script( 'wcpd-admin', WCPD_URL . 'assets/admin.js', array( 'jquery' ), WCPD_VERSION, true );
			wp_localize_script(
				'wcpd-admin',
				'wcpd_admin',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'wcpd_admin_action' ),
					'strings'  => array(
						'confirm_ready'  => __( 'Mark as ready for delivery and notify customer?', 'wc-preorder-deposit' ),
						'confirm_charge' => __( 'Attempt automatic charging of the remaining amount?', 'wc-preorder-deposit' ),
					),
				)
			);
		}
	}

	/**
	 * Prints the pre-order status badge on the order edit screen.
	 *
	 * @param WC_Order $order The order being edited.
	 * @return void
	 */
	public static function order_detail_badge( $order ) {
		if ( ! WCPD_Order::is_preorder( $order ) ) {
			return;
		}
		echo '<p class="wcpd-admin-badge">';
		echo '<span class="wcpd-pill wcpd-pill-info">PRE-ORDER</span>';
		echo '<span class="wcpd-pill wcpd-pill-amount">' . sprintf( __( 'Remaining: %s', 'wc-preorder-deposit' ), wc_price( (float) $order->get_meta( '_wcpd_remaining_total' ) ) ) . '</span>';
		echo '</p>';
	}

	/**
	 * Registers the "Pre-Order Deposit Manager" order meta box.
	 *
	 * @return void
	 */
	public static function meta_boxes() {
		add_meta_box(
			'wcpd_box',
			__( 'Pre-Order Deposit Manager', 'wc-preorder-deposit' ),
			array( __CLASS__, 'render_box' ),
			'shop_order',
			'side',
			'high'
		);
	}

	/**
	 * Renders the pre-order payment breakdown and action buttons meta box.
	 *
	 * @param WP_Post $post The order post being edited.
	 * @return void
	 */
	public static function render_box( $post ) {
		$order = wc_get_order( $post->ID );

		if ( ! WCPD_Order::is_preorder( $order ) ) {
			echo '<div class="wcpd-meta-empty">';
			echo '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg>';
			echo '<p>' . __( 'This order is not a pre-order.', 'wc-preorder-deposit' ) . '</p>';
			echo '</div>';
			return;
		}

		$full      = (float) $order->get_meta( '_wcpd_full_total' );
		$deposit   = (float) $order->get_meta( '_wcpd_deposit_total' );
		$remaining = (float) $order->get_meta( '_wcpd_remaining_total' );
		$paid_rem  = (float) $order->get_meta( '_wcpd_remaining_paid' );
		$status    = $order->get_status();
		?>
		<div class="wcpd-meta-box">
			<div class="wcpd-meta-header">
				<div class="wcpd-meta-icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
				</div>
				<div class="wcpd-meta-title"><?php _e( 'Payment Breakdown', 'wc-preorder-deposit' ); ?></div>
			</div>

			<div class="wcpd-meta-grid">
				<div class="wcpd-meta-card">
					<div class="wcpd-meta-label"><?php _e( 'Total Value', 'wc-preorder-deposit' ); ?></div>
					<div class="wcpd-meta-value"><?php echo wc_price( $full ); ?></div>
				</div>
				<div class="wcpd-meta-card">
					<div class="wcpd-meta-label"><?php _e( 'Deposit Paid', 'wc-preorder-deposit' ); ?></div>
					<div class="wcpd-meta-value wcpd-meta-accent"><?php echo wc_price( $deposit ); ?></div>
				</div>
				<div class="wcpd-meta-card">
					<div class="wcpd-meta-label"><?php _e( 'Remaining', 'wc-preorder-deposit' ); ?></div>
					<div class="wcpd-meta-value wcpd-meta-warn"><?php echo wc_price( $remaining ); ?></div>
				</div>
				<?php if ( $paid_rem > 0 ) : ?>
				<div class="wcpd-meta-card wcpd-meta-card-full">
					<div class="wcpd-meta-label"><?php _e( 'Remaining Paid', 'wc-preorder-deposit' ); ?></div>
					<div class="wcpd-meta-value wcpd-meta-success"><?php echo wc_price( $paid_rem ); ?></div>
				</div>
				<?php endif; ?>
			</div>

			<div class="wcpd-meta-actions">
				<?php if ( 'preorder-deposit' === $status ) : ?>
					<button type="button" class="wcpd-btn wcpd-btn-primary wcpd-btn-ready" data-order="<?php echo esc_attr( $order->get_id() ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
						<?php _e( 'Mark Ready & Notify', 'wc-preorder-deposit' ); ?>
					</button>
					<button type="button" class="wcpd-btn wcpd-btn-secondary wcpd-btn-charge" data-order="<?php echo esc_attr( $order->get_id() ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
						<?php _e( 'Auto-Charge Remainder', 'wc-preorder-deposit' ); ?>
					</button>
					<div class="wcpd-meta-hint">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
						<?php _e( 'Auto-charge works only with saved cards via supported gateways (Stripe, etc.).', 'wc-preorder-deposit' ); ?>
					</div>
				<?php elseif ( 'preorder-ready' === $status ) : ?>
					<div class="wcpd-status-msg">
						<span class="wcpd-status-dot wcpd-status-pending"></span>
						<?php _e( 'Awaiting final payment from customer.', 'wc-preorder-deposit' ); ?>
					</div>
				<?php elseif ( 'preorder-completed' === $status ) : ?>
					<div class="wcpd-status-msg wcpd-status-msg-success">
						<span class="wcpd-status-dot wcpd-status-success"></span>
						<?php _e( 'Pre-order fully completed.', 'wc-preorder-deposit' ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Inserts the "Pre-Order" column into the order list table.
	 *
	 * @param array $columns Existing order list columns, keyed by column ID.
	 * @return array Order list columns including the pre-order column.
	 */
	public static function col_add( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'order_status' === $key ) {
				$new['wcpd_preorder'] = __( 'Pre-Order', 'wc-preorder-deposit' );
			}
		}
		return $new;
	}

	/**
	 * Renders the "Pre-Order" column content for a single order row.
	 *
	 * @param string $column  The column being rendered.
	 * @param int    $post_id The order post ID for this row.
	 * @return void
	 */
	public static function col_show( $column, $post_id ) {
		if ( 'wcpd_preorder' !== $column ) {
			return;
		}

		$order = wc_get_order( $post_id );
		if ( WCPD_Order::is_preorder( $order ) ) {
			$rem = (float) $order->get_meta( '_wcpd_remaining_total' );
			echo '<div class="wcpd-list-badge">';
			echo '<span class="wcpd-pill">PRE-ORDER</span>';
			echo '<small>' . sprintf( __( 'Remaining: %s', 'wc-preorder-deposit' ), wc_price( $rem ) ) . '</small>';
			echo '</div>';
		} else {
			echo '<span class="wcpd-na">—</span>';
		}
	}
}
