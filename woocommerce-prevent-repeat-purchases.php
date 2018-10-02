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
	}

	/**
	 * Function to write the HTML/form fields for the product panel
	 */
	public function write_panel() {
		// Open Options Group
		echo '<div class="options_group order-proxies-checkbox-wrap">';

		// Write the checkbox for the product option
		woocommerce_wp_checkbox( [
			'id'            => 'order_proxy',
			'wrapper_class' => 'order-proxies-checkbox',
			'label'         => __( 'Ask for a proxy?', 'woocommerce-order-proxies' ),
			'description'   => __( 'If checked, we will show a field during checkout to ask for the name of a proxy.', 'woocommerce-order-proxies' ),
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
		update_post_meta( $post_id, 'order_proxy', empty( $_POST['order_proxy'] ) ? 'no' : 'yes' );
	}
}

// Fire it up!
add_action( 'plugins_loaded', function() {
	$WC_Order_Proxies = new WC_Order_Proxies();
} );
