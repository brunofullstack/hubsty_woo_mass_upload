<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* 	template for admin menus and pages
*/

if (!class_exists('MP_MU_Admin_Menus')) {

	class MP_MU_Admin_Menus {

		function __construct() {

			add_action('admin_menu', array( $this, 'wk_mass_upload_menu' ) );

		}

		function wk_mass_upload_menu() {

			add_menu_page('Mass Upload', 'Mass Upload', 'manage_marketplace', 'mass-upload', array($this, 'mp_mass_upload_menu' ), 'dashicons-upload', '56' );

			add_submenu_page( 'mass-upload', 'General', 'General', 'manage_marketplace', 'mass-upload', array( $this, 'mp_mass_upload_menu' ) );

			add_submenu_page( 'mass-upload', 'Profile', 'Run Profile', 'manage_marketplace', 'run-profile', array( $this, 'mp_csv_profile_menu' ) );

			$hook = add_submenu_page( 'mass-upload', 'Manage Profile', 'Manage Profile', 'manage_marketplace', 'manage-mass-upload-profile', array( $this, 'mp_csv_profile_manage' ) );

			add_action( "load-".$hook, array($this, 'wkmu_add_page_option') );

			add_filter( 'set-screen-option', array($this, 'wkmu_set_options'), 10, 3 );
		}

		function wkmu_set_options($status, $option, $value) {
				return $value;
		}

		function wkmu_add_page_option()
		{
				$args = array(
						'label' => __( 'Profile per page' ),
						'default' => 10,
						'option' => 'profile_per_page'
				);
				add_screen_option( 'per_page', $args );
		}

		function mp_csv_profile_manage() {
				require_once(sprintf("%s/admin/admin-manage-profile.php", dirname(__FILE__)));
		}

		function mp_mass_upload_menu() {

			echo '<div class="wrap a">';

				echo '<h1 class="wp-heading-inline">Mass Upload</h1>';

				echo '<a href="admin.php?page=run-profile" class="page-title-action">Run Profile</a>';
				echo '<p>Download Sample File for <a href="'.WP_MASS_UPLOAD.'dummy-csv/simple.csv">Simple</a>, <a href="'.WP_MASS_UPLOAD.'dummy-csv/variable.csv">Variable</a>, <a href="'.WP_MASS_UPLOAD.'dummy-csv/grouped.csv">Grouped</a>, <a href="'.WP_MASS_UPLOAD.'dummy-csv/downloadable.csv">Downloadable</a>, <a href="'.WP_MASS_UPLOAD.'dummy-csv/external.csv">External</a> product types.</p>';
				echo '<p>Download Sample File for images zip <a href="'.WP_MASS_UPLOAD.'dummy-csv/zip_img.zip">zip_img</a></p>';
				echo '<p>Upload csv with product information along with product images zip.</p>';

				require_once(sprintf("%s/admin/admin-mass-upload.php", dirname(__FILE__)));

				wk_mu_csv();

			echo '</div>';

		}

		function mp_csv_profile_menu() {

			echo '<div class="wrap a">';

				echo '<h1 class="wp-heading-inline">Run Profile</h1>';

				echo '<a href="admin.php?page=mass-upload" class="page-title-action">Upload New Profile</a>';

				echo '<p>Run uploaded csv profile to upload products.</p>';

				require_once(sprintf("%s/admin/admin-run-profile.php", dirname(__FILE__)));

			echo '</div>';
		}

	}

	return new MP_MU_Admin_Menus();
}
