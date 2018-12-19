<?php
/**
 * Plugin Name: Marketplace Product Mass Upload
 * Plugin URI: https://store.webkul.com/Marketplace-Mass-Upload-WordPress-WooCommerce.html
 * Description: WordPress WooCommerce Marketplace Product Mass Upload add-on is useful for bulk upload products. Using this Marketplace add-on Seller can upload the products in bulk using CSV.
 * Version: 3.0.0
 * Author: Webkul
 * Author URI: http://webkul.com
 * Domain Path: plugins/wp-marketplace-mass-upload
 * License: GNU/GPL for more info see license.txt included with plugin
 * License URI: https://www.gnu.org/licenses/gpl-2.0.en.html
 * WC requires at least: 3.0.0
 * WC tested up to: 3.4.0
 * Text Domain: mass_upload
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'WP_MASS_UPLOAD', plugin_dir_url(__FILE__));

if (!class_exists('MP_MASS_UPLOAD')) {

set_time_limit(0);
	/**
	*
	*/
	class MP_MASS_UPLOAD {

		protected $page_title_display = 1;

		function __construct() {
				ob_start();

				register_activation_hook(__FILE__, array( $this, 'wk_mu_check_marketplace_is_installed' ) );

				require_once(sprintf("%s/includes/class-admin-menus.php", dirname(__FILE__)));

				require_once(sprintf("%s/includes/class-mass-upload-ajax-functions.php", dirname(__FILE__)));

				add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'wk_mu_plugin_settings_link' ) );

				add_action( 'admin_enqueue_scripts', array( $this, 'wk_mu_admin_scripts' ) );

				add_action( 'wp_enqueue_scripts', array( $this, 'wk_mu_front_scripts' ) );

				add_action('marketplace_list_seller_option', array($this,'add_wk_mu_tab_list'), 10, 1 );

				add_action('wp_head', array( $this, 'wk_mu_calling_pages') );

				add_filter('mp_woocommerce_account_menu_options', array($this, 'wk_mu_links_in_menu'));

				add_filter( 'the_title', array( $this, 'wk_mu_hide_page_title' ) );
		}

		function wk_mu_hide_page_title($title) {
				global $wpdb, $wp_query;
				$page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option( 'wkmp_seller_page_title' ) . "'");
				if ( in_the_loop() && is_page( $page_name ) && $this->page_title_display == 1 ) {
						$this->page_title_display = 0;

						if ( null !== get_query_var( 'main_page' ) ) {
								$main_page = get_query_var( 'main_page' );
								switch ( $main_page ) {
										case 'mass-upload':
												return __( 'Mass Upload', 'mass_upload' );
												break;

										case 'run-profile':
												return __( 'Run Profile', 'mass_upload' );
												break;
								}
						}
				}
				return $title;
		}

		function wk_mu_links_in_menu($items) {
        global $wpdb;

        $user_id = get_current_user_id();

        $new_items = array();

        $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='".get_option('wkmp_seller_page_title')."'");

        $new_items['../' . $page_name . '/mass-upload'] = __( 'Mass Upload', 'mass_upload' );

        $items += $new_items;

        return $items;
    }

		function wk_mu_plugin_settings_link($links) {

			$url = 'https://wordpressdemo.webkul.com';

			$settings_link = '<a href="'.$url.'" target="_blank" style="color:green;">' . __( 'More Add-ons', 'mass_upload' ) . '</a>';

			$links[] = $settings_link;

			return $links;

		}

		function wk_mu_check_marketplace_is_installed() {

			require_once(sprintf("%s/install.php", dirname(__FILE__)));

			$install_obj = new Mass_Upload_Install();

			$install_obj->wk_mu_install();

		}

		function wk_mu_admin_scripts() {

			wp_enqueue_script( 'wk_mass_admin_js', WP_MASS_UPLOAD. 'assets/js/plugin-admin.js', array( 'jquery' ) );

			wp_localize_script('wk_mass_admin_js', 'mass_upload_object', array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'uploadNonce' => wp_create_nonce('admin_run_upload_action'),
				)
			);

			wp_enqueue_style( 'wk_mass_admin_css', WP_MASS_UPLOAD.'assets/css/style.css');

		}

		function wk_mu_front_scripts() {

			wp_enqueue_script( 'wk_mass_front_js', WP_MASS_UPLOAD. 'assets/js/plugin-admin.js', array( 'jquery' ) );

			wp_enqueue_style( 'wk_mass_front_css', WP_MASS_UPLOAD.'assets/css/style.css');

		}

		function add_wk_mu_tab_list($page_name) {

			echo '<li class="wkmp-selleritem"><a href="'.home_url("/".$page_name."/mass-upload").'">Mass Upload</a></li>';

		}

		function wk_mu_calling_pages() {

			global $current_user,$wpdb;

	        $current_user = wp_get_current_user();

	        $seller_info = $wpdb->get_var("SELECT user_id FROM ".$wpdb->prefix."mpsellerinfo WHERE user_id = '".$current_user->ID ."' and seller_value='seller'");

	        $pagename = get_query_var('pagename');

	        $main_page = get_query_var('main_page');

	        if( !empty($pagename) ){

	            if( $main_page == "mass-upload" && ( $current_user->ID || $seller_info > 0 ) ) {

	                require_once 'includes/admin/admin-mass-upload.php';

	                add_shortcode( 'marketplace', 'wk_mu_csv' );

	            }

	            if( $main_page == "run-profile" && ( $current_user->ID || $seller_info > 0 ) ) {

	                require_once 'includes/front/run-profile.php';

	                add_shortcode( 'marketplace', 'wk_mu_front_profile' );

	            }

	            if( $main_page == "process-csv" && ( $current_user->ID || $seller_info > 0 ) ) {

	                require_once 'includes/process-csv-data.php';

	                add_shortcode( 'marketplace', 'wk_mu_process_csv' );

	            }

	        }

		}

	}

	new MP_MASS_UPLOAD();

}
