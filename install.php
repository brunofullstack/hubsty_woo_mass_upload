<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if (!class_exists('Mass_Upload_Install')) {
	
	/**
	* 
	*/
	class Mass_Upload_Install {
		
		function wk_mu_install() {

			if ( !is_plugin_active( 'wk-woocommerce-marketplace/functions.php' ) ) {
  	
				wp_die('Sorry, but this plugin requires the <a href="https://codecanyon.net/item/wordpress-woocommerce-marketplace-plugin/19214408" target="_blank">Marketplace Plugin</a> to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
		  	
			}
			else {

			}

		}

	}

}