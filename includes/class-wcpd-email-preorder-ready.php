<?php
/**
 * Sends the "your pre-order is ready for delivery" customer notification email.
 *
 * @package WC_Preorder_Deposit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notifies the customer by email once their pre-order is ready for delivery.
 */
class WCPD_Email_Preorder_Ready extends WC_Email {

	/**
	 * Sets up the email's ID, title, description, and templates.
	 */
	public function __construct() {
		$this->id             = 'wcpd_preorder_ready';
		$this->customer_email = true;
		$this->title          = __( 'Pre-Order: Ready for Delivery', 'wc-preorder-deposit' );
		$this->description    = __( 'Notification sent when the product is ready and the remainder must be paid.', 'wc-preorder-deposit' );
		$this->template_html  = 'emails/preorder-ready.php';
		$this->template_plain = 'emails/plain/preorder-ready.php';
		$this->placeholders   = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);

		parent::__construct();

		$this->template_base = WCPD_DIR . 'templates/';
	}

	/**
	 * Sends the notification for a given order, if the email is enabled.
	 *
	 * @param int $order_id The order that is now ready for delivery.
	 * @return void
	 */
	public function trigger( $order_id ) {
		$this->object = wc_get_order( $order_id );
		if ( ! $this->object ) {
			return;
		}

		$this->recipient                      = $this->object->get_billing_email();
		$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
		$this->placeholders['{order_number}'] = $this->object->get_order_number();

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
	}

	/**
	 * The default email subject, used when no custom subject is configured.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Your order #{order_number} is ready for delivery', 'wc-preorder-deposit' );
	}

	/**
	 * The default email heading, used when no custom heading is configured.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Your product is ready', 'wc-preorder-deposit' );
	}

	/**
	 * Renders the HTML version of the email body.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => false,
				'email'         => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Renders the plain-text version of the email body.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => true,
				'email'         => $this,
			),
			'',
			$this->template_base
		);
	}
}

add_action( 'wcpd_preorder_ready_notification', array( 'WCPD_Email_Preorder_Ready', 'trigger' ), 10, 1 );
