<?php
/*
Plugin Name: WooCommerce Multi-User Address Library
Plugin URI: http://www.source3media.com
Description: A WooCommerce plugin that allows every user in the site to utilize a list of all addresses as well as ship to multiple places(with the proper extensions).
Author: Source3Media
Author URI: http://www.source3media.com
Version: 1.0.0
Text Domain: wc-multiuser-address

	Copyright: © 2018 Source3Media (email : creative@source3media.com)
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!function_exists('multiuser_scripts')) {
    /**
     * Load theme's JavaScript sources.
     */
    function multiuser_scripts() {
        // Get the theme data.
        $the_theme = wp_get_theme();
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.2.1.min.js', array(), true);
        wp_enqueue_script( 'addressbook-scripts', get_template_directory_uri() . '/core.js', array(), '0.0.0.0.10', true );
        wp_localize_script( 'wc-address-book', 'wc_address_book', array(
    					'ajax_url' => admin_url( 'admin-ajax.php' ),
    				));
    }
}

add_action('wp_enqueue_scripts', 'multiuser_scripts');

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$woo_path = 'woocommerce/woocommerce.php';

if ( ! is_plugin_active( $woo_path ) && ! is_plugin_active_for_network( $woo_path ) ) {

	deactivate_plugins( plugin_basename( __FILE__ ) );

	/**
	 * Deactivate the plugin if WooCommerce is not active.
	 *
	 * @since    1.0.0
	 */
	function woocommerce_notice__error() {

    $class   = 'notice notice-error';
		$message = __( 'WooCommerce Address Book requires WooCommerce and has been deactivated.', 'wc-address-book' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_attr( $message ) );
	}
	add_action( 'admin_notices', 'woocommerce_notice__error' );
	add_action( 'network_admin_notices', 'woocommerce_notice__error' );

} else {
	//class Woo_Address_Book {
		// Ad 'addressbook' custom checkout select field
		add_filter( 'woocommerce_checkout_fields' , 'add_addressbook_checkout_field', 20, 1 );
		function add_addressbook_checkout_field( $fields ) {

			$options = array();

			// First option
			$options[0] = 'Please select something…';

		  // Get 'addressbook' posts
			$posts = array();
			$args = array('post_type'=>'addressbook', 'posts_per_page'=>-1,'order'=>'asc');
			$query = New WP_Query($args);
			if($query->have_posts()):while($query->have_posts()):$query->the_post();

				$temp = array();
				$temp['id'] = get_the_id();
				$temp['fname'] = get_field('fname');
				$temp['lname'] = get_field('lname');
				$temp['company'] = get_field('company');
				$temp['addr1'] = get_field('address_line_1');
				$temp['addr2'] = get_field('address_line_2');
				$temp['city'] = get_field('city');
				$temp['state'] = get_field('state');
				$temp['zip'] = get_field('zip');
				$posts = $temp;

				$id 			=	$posts['id'];
				$fname 		= $posts['fname'];
				$lname 		= $posts['lname'];
				$company 	= $posts['company'];
				$addr1 		= $posts['addr1'];
				$addr2 		= $posts['addr2'];
				$city 		= $posts['city'];
				$state 		= $posts['state'];
				$zip 			= $posts['zip'];
				// Loop through 'addressbook' posts (to set all other select options)
		    foreach( $posts as $post ){
		        // Set each complete name as an option (Where key is the post ID)
						$options[$id] = $company  . ', '. $fname  . ' '. $lname . ', ' . $addr1 . '....';
		    }

			endwhile;endif;wp_reset_postdata();
			$fields['shipping']['addressbook'] = array(
		      'type'          => 'select',
		      'label'         => __('Address Book', 'woocommerce'),
		      'placeholder'   => _x('Pick an address', 'placeholder', 'woocommerce'),
		      'options'       =>  $options, // Here we set the options
		      'required'      => false,
		      'priority'      => 1,
		      'class'         => array('form-row-wide'),
		      'clear'         => true
		  );

		  return $fields;

		}

		// process custom checkout field
		add_action('woocommerce_checkout_process', 'check_addressbook_checkout_field', 20 );
		function check_addressbook_checkout_field( $order, $data ) {
		  if ( isset($_POST['addressbook']) && empty($_POST['addressbook']) )
		      wc_add_notice( __("Please pick an address from the Address Book"), 'error' );
		}

		// Add custom meta data (or existing change data) to the order before saving it
		add_action('woocommerce_checkout_create_order', 'set_meta_data_in_checkout_create_order', 20, 2 );
		function set_meta_data_in_checkout_create_order( $order, $data ) {
		  if ( isset($_POST['addressbook']) ){
		              // Set the meta data in the order
		      if( ! empty($fname) )
		          $order->update_meta_data( 'ab_fname', esc_attr( $fname ) );
		      if( ! empty($lname) )
		          $order->update_meta_data( 'ab_lname', esc_attr( $lname ) );
		      if( ! empty($company) )
		          $order->update_meta_data( 'ab_company', esc_attr( $company ) );
		      if( ! empty($addr1) )
		          $order->update_meta_data( 'ab_addr1', esc_attr( $addr1 ) );
		      if( ! empty($addr2) )
		          $order->update_meta_data( 'ab_addr2', esc_attr( $addr2 ) );
		      if( ! empty($city) )
		          $order->update_meta_data( 'ab_city', esc_attr( $city ) );
		      if( ! empty($state) )
		          $order->update_meta_data( 'ab_state', esc_attr( $state ) );
		      if( ! empty($zip) )
		          $order->update_meta_data( 'ab_zip', esc_attr( $zip ) );
		  }
		}
?>
