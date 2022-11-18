<?php
/*
Plugin Name: All BD Mobile Payments Gateway
Plugin URI: https://profiles.wordpress.org/emrannet/#content-plugins
Description: All Bangladeshi Mobile Payment gateway for woo-commerce.
Version: 3.0
Author: Emran Hossen
Author URI: https://emran.net
License: GPLv2
*/





//Additional links on the plugin page
add_filter( 'plugin_row_meta', 'all_bd_mobile_payments_register_plugin_links', 10, 2 );
function all_bd_mobile_payments_register_plugin_links($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="https://emran.net" target="_blank">' . __( 'Emran Hossen', 'rsb' ) . '</a>';

	}
	return $links;
}



add_filter( 'woocommerce_currencies', 'all_bd_mobile_payments_add_bdt_currency' );
function all_bd_mobile_payments_add_bdt_currency( $currencies ) {
$currencies['BDT'] = __( 'Bangladeshi Taka', 'woocommerce' );
return $currencies;
}
 
add_filter('woocommerce_currency_symbol', 'all_bd_mobile_payments_add_bdt_currency_symbol', 10, 2);
function all_bd_mobile_payments_add_bdt_currency_symbol( $currency_symbol, $currency ) {
switch( $currency ) {
case 'BDT': $currency_symbol = '&#2547;&nbsp;'; break;
}
return $currency_symbol;
}

/**********************************All Bangladeshi Mobile Payment Gateways*******************/

add_action('plugins_loaded', 'wc_all_bd_mobile_payment_gateway', 0);
function wc_all_bd_mobile_payment_gateway(){
  if(!class_exists('WC_Payment_Gateway')) {
	  add_action( 'admin_notices', 'wc_all_bd_mobile_payments_gateway_fallback_notice' );
	  
	  /* WooCommerce fallback notice. */
	function wc_all_bd_mobile_payments_gateway_fallback_notice() {
   			 echo '<div class="error"><p>' . sprintf( __( 'All BD Mobile Payment Gateways depends on the last version of WooCommerce to work!', 'woocommerce' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';
		}

	  return;
  }
  
    function woocommerce_add_bkash_mCash_UCash_SureCash_Rocket_gateway($methods) {
        $methods[] = 'WC_Gateway_bKash';
		$methods[] = 'WC_Gateway_Rocket';
		$methods[] = 'WC_Gateway_Nagad';
		$methods[] = 'WC_Gateway_SureCash';
        $methods[] = 'WC_Gateway_UCash';
		$methods[] = 'WC_Gateway_mCash';
		$methods[] = 'WC_Gateway_MYCash';

        return $methods;
    }
 
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_bkash_mCash_UCash_SureCash_Rocket_gateway' );
	
	// Include the WC_bkash_mCash_UCash_SureCash_Rocket_Gateway class.
    require_once plugin_dir_path( __FILE__ ) . 'gateways/bKash.php';
	require_once plugin_dir_path( __FILE__ ) . 'gateways/Rocket.php';
    require_once plugin_dir_path( __FILE__ ) . 'gateways/Nagad.php';
	require_once plugin_dir_path( __FILE__ ) . 'gateways/SureCash.php';
	require_once plugin_dir_path( __FILE__ ) . 'gateways/UCash.php';
    require_once plugin_dir_path( __FILE__ ) . 'gateways/mCash.php';
	require_once plugin_dir_path( __FILE__ ) . 'gateways/MYCash.php';
	
}

/* Adds custom settings url in plugins page. */
function wc_all_bd_mobile_payments_gateway_action_links( $links ) {
    $settings = array(
		'settings' => sprintf(
		'<a href="%s">%s</a>',
		admin_url( 'admin.php?page=wc-settings&tab=checkout' ),
		__( 'Payment Settings', 'woocommerce' )
		)
    );

    return array_merge( $settings, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_all_bd_mobile_payments_gateway_action_links' );