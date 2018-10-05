<?php
/**
 * Plugin Name: WooCommerce Order Proxies
 * Plugin URI: https://github.com/Craftpeak/woocommerce-order-proxies
 * Description: A WooCommerce plugin to allow proxies as metadata on a per-order basis.
 * Version: 1.0.0
 * Author: Craftpeak
 * Author URI: https://craftpeak.com
 * Requires at least: 4.0
 * Tested up to: 4.9.8
 * Text Domain: woocommerce-order-proxies
 */

class WC_Order_Proxies {
	public function __construct() {
		// Write the Admin Panel
		add_action( 'woocommerce_product_options_general_product_data', [ &$this, 'write_panel' ] );
		// Process the Admin Panel Saving
		add_action( 'woocommerce_process_product_meta', [ &$this, 'write_panel_save' ] );

		// Add the custom order field
		add_action( 'woocommerce_after_order_notes', [ &$this, 'add_checkout_field' ] );

		// Validate & Save the order field
		add_action( 'woocommerce_after_checkout_validation', [ &$this, 'validate_checkout_field' ], 10, 2 );
		add_action ( 'woocommerce_checkout_update_order_meta', [ &$this, 'save_checkout_field' ] );

		// Display the proxy in the order backend
		add_action ( 'woocommerce_admin_order_data_after_billing_address', [ &$this, 'display_order_proxy_order_admin' ] );
	}

	/**
	 * Function to write the HTML/form fields for the product panel
	 */
	public function write_panel() {
		// Open Options Group
		echo '<div class="options_group order-proxies-checkbox-wrap">';

		// Write the checkbox for the product option
		woocommerce_wp_checkbox( [
			'id'            => 'require_order_proxy',
			'wrapper_class' => 'order-proxies-checkbox',
			'label'         => __( 'Ask for a proxy?', 'woocommerce-order-proxies' ),
			'description'   => __( 'If checked, the checkout page will show a required field to ask for a proxy.', 'woocommerce-order-proxies' ),
		] );

		// Close Options Group
		echo '</div>';
	}

	/**
	 * Function to save our custom write panel values
	 *
	 * @param $post_id
	 */
	public function write_panel_save( $post_id ) {
		// Toggle the checkbox
		update_post_meta( $post_id, 'require_order_proxy', empty( $_POST['require_order_proxy'] ) ? 'no' : 'yes' );
	}

	/**
	 * Adds a checkout field for a proxy if there is a
	 *
	 * @param $checkout
	 *
	 * @return mixed
	 */
	public function add_checkout_field( $checkout ) {
		// Set to not ask for a proxy by default
		$ask_for_proxy = false;

		// Get the global cart and run through items so that we can figure out if we need to add the checkout field
		foreach ( WC()->cart->get_cart() as $item ) {
			$product_id = $item['product_id'];
			$product_order_proxy = get_post_meta( $product_id, 'require_order_proxy', true );

			if ( $product_order_proxy === 'yes' ) {
				$ask_for_proxy = true;
				break;
			}
		}

		// If we should add a proxy,
		if ( $ask_for_proxy ) {
			echo '<div class="order-proxies-checkout-field-wrap">';

		    woocommerce_form_field( 'order_proxy', [
			    'type'          => 'text',
			    'class'         => ['order-proxy form-row-wide'],
			    'label'         => __( 'Order Proxy', 'woocommerce-order-proxies' ),
			    'placeholder'   => __( 'Please enter the name of a proxy for this order', 'woocommerce-order-proxies' ),
			    'required'      => true,
		    ], $checkout->get_value( 'order_proxy' ) );

			echo '</div>';
		}

		return false;
	}

	/**
	 * Verifies that the order proxy field is filled out, if not, return an error
	 *
	 * @param $data
	 * @param $errors
	 */
	public function validate_checkout_field( $data, $errors ) {
		if ( isset( $_POST['order_proxy'] ) && ! $_POST['order_proxy'] ) {
			$errors->add( 'validation', __( 'Items in your cart require the proxy field to be filled in. If you are picking up yourself add "Self" or "N/A".', 'woocommerce-order-proxies') );
		}
	}

	/**
	 * Saves the order proxy field entered on checkout
	 *
	 * @param $order_id
	 */
	public function save_checkout_field( $order_id ) {
		if ( ! empty( $_POST['order_proxy'] ) ) {
			update_post_meta( $order_id, 'order_proxy', sanitize_text_field( $_POST['order_proxy'] ) );
		}
	}

	/**
	 * Displays the order proxy in the order edit backend if it exists
	 *
	 * @param $order
	 */
	public function display_order_proxy_order_admin( $order ) {
		$order_proxy = get_post_meta( $order->id, 'order_proxy', true );

		if ( $order_proxy ) {
			?>
			<p>
				<strong><?php echo __( 'Order Proxy', 'woocommerce-order-proxies' ); ?>:</strong>
				<br>
				<?php echo esc_html( $order_proxy ); ?>
			</p>
			<?php
		}
	}
}

// Fire it up!
add_action( 'plugins_loaded', function() {
	$WC_Order_Proxies = new WC_Order_Proxies();
} );
