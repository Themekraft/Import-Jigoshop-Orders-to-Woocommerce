<?php
/**
 * Plugin Name: Import Jigoshop Orders to Woocommerce
 * Plugin URI:  https://themekraft.com
 * Description: Themekraft Jigoshop orders import to woocommerce partitial
 * Author:      themekraft.com
 * Version:     1.0
 * Author URI:  http://themekraft.com/
 * Text Domain: themekraft
 * Domain Path: /languages/
 * Network:     true
 */

 /*
  * This Script imports parts of Jigoshop order values to WooCommerce. The script is not 
  * complete and was just fitted to our needs. If you want to extend, you're welcome to fork
  * on Github and do a pull request on this gist.
  * 
  * Please be sure you have switched off Jigoshop and switched on WooCommerce.
  * 
  * We take no warranty for this script! 
  * 
  */

include 'classes/jigoshop_order.class.php';
include 'classes/jigoshop_orders.class.php';
include 'classes/jigoshop_tax.class.php';
include 'classes/jigoshop_countries.class.php';
  
function tk_woo_import_jigo_orders(){
	global $wpdb;
	
	$sql = 'SELECT * FROM ' . $wpdb->users . '';
	$users = $wpdb->get_results( $sql );
	
	echo '<h3>Transfering Orders</h3>';
	
	//echo '<br /><br /><br /><br />';
	
	foreach ( $users as $user ):
		
		$jigo_orders = new jigoshop_orders();
		
		echo '<b>' . $user->user_nicename .'</b><br />';
			
		$jigo_orders->get_customer_orders( $user->ID );
		
		$orders = $jigo_orders->orders;
		
		if( !is_admin() && 11==22):
			echo '<pre>';
			print_r( $orders );
			echo '</pre>';
		endif;
		
		foreach( $orders AS $key => $order ):
			
			$items = $item_id = $jigo_orders->orders[$key]->_data['items'];
			
			$order_id = $jigo_orders->orders[$key]->_data['id'];
			$order_key = $jigo_orders->orders[$key]->_data['order_key'];
			$order_discount = $jigo_orders->orders[$key]->_data['order_discount'];
			
			$user_id = $jigo_orders->orders[$key]->_data['user_id'];
			
			$billing_first_name = $jigo_orders->orders[$key]->_data['billing_first_name'];
			$billing_last_name = $jigo_orders->orders[$key]->_data['billing_last_name'];
			$billing_company = $jigo_orders->orders[$key]->_data['billing_company'];
			$billing_address_1 = $jigo_orders->orders[$key]->_data['billing_address_1'];
			$billing_address_2 = $jigo_orders->orders[$key]->_data['billing_address_2'];
			$billing_city = $jigo_orders->orders[$key]->_data['billing_city'];
			$billing_postcode = $jigo_orders->orders[$key]->_data['billing_postcode'];
			$billing_country = $jigo_orders->orders[$key]->_data['billing_country'];
			$billing_state = $jigo_orders->orders[$key]->_data['billing_state'];
			// $billing_email = $jigo_orders->orders[$key]->_data['billing_email'];
			$billing_phone = $jigo_orders->orders[$key]->_data['billing_phone'];
			$payment_method = $jigo_orders->orders[$key]->_data['payment_method'];
			$payment_method_title = $jigo_orders->orders[$key]->_data['payment_method_title'];
			
			
			// echo 'Order ID: ' .  $order_id . '<br />';

			
			$order_items = array();
			
			$tax_total = 0;
			$order_total = 0;
			
			foreach ( $items as $item_key => $item ):
				$item_id = $item['id'];
				$item_name = $item['name'];
				$item_qty = $item['qty'];
				$item_cost = $item['cost'];
				$item_taxrate = $item['taxrate'];
				$item_tax = $item_cost / 100 * $item_taxrate;
				$item_total = $item_cost * $item_qty;
				
				echo $item_name .'<br />';
				
				$order_items[] = array(
			 		'id' 				=> $item_id,
			 		'name' 				=> $item_name,
			 		'qty' 				=> (int) $item_qty,
			 		'line_subtotal'		=> $item_cost,
			 		'line_subtotal_tax' => $item_tax,
			 		'line_total'		=> $item_total,
			 		'line_tax'			=> $item_tax * $item_qty
			 	);
				
				$tax_total += $item_tax;
				$order_total += $item_total;
				
			endforeach;
			
			update_post_meta( $order_id, '_order_tax', 				number_format( $tax_total, 2, '.', '' ) );
			update_post_meta( $order_id, '_order_total', 			number_format( $order_total, 2, '.', '' ) );
			update_post_meta( $order_id, '_order_key', 				$order_key );
			update_post_meta( $order_id, '_order_discount', 		$order_discount );
			
			
			update_post_meta( $order_id, '_order_items', 			$order_items );
			update_post_meta( $order_id, '_customer_user', 			(int) $user_id );
			
			update_post_meta( $order_id, '_billing_first_name', 	$billing_first_name );
			update_post_meta( $order_id, '_billing_last_name', 		$billing_last_name );
			update_post_meta( $order_id, '_billing_company', 		$billing_company );
			update_post_meta( $order_id, '_billing_address_1', 		$billing_address_1 );
			update_post_meta( $order_id, '_billing_address_2', 		$billing_address_2 );
			update_post_meta( $order_id, '_billing_city', 			$billing_city );
			update_post_meta( $order_id, '_billing_country', 		$billing_country );
			update_post_meta( $order_id, '_billing_state', 			$billing_state );
			update_post_meta( $order_id, '_billing_postcode', 		$billing_postcode );
			update_post_meta( $order_id, '_billing_phone', 			$billing_phone );
			update_post_meta( $order_id, '_billing_email', 			$user->user_email );
			
			update_post_meta( $order_id, '_payment_method', 		$payment_method );
			update_post_meta( $order_id, '_payment_method_title', 	$payment_method_title );
			
			woocommerce_downloadable_product_permissions( $order_id );
		
		endforeach;
		
	endforeach;
	
}
add_action( 'wp_head', 'tk_woo_import_jigo_orders', 21 );