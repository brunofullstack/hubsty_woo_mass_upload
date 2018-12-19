<?php
/*
	csv upload admin
*/

if ( ! defined( 'ABSPATH' ) )

	exit; // Exit if accessed directly

function wk_mu_csv() {

if (isset($_POST['submit_admin_csv'])) {

	if ( $_FILES['product_info_csv']['name'] && $_FILES['product_image_zip']['name'] ) {

		if ( isset($_POST['admin_mass_upload_nonce']) && !empty( $_POST['admin_mass_upload_nonce'] ) ) {

	 		if( wp_verify_nonce( $_POST['admin_mass_upload_nonce'], 'admin_mass_upload_action' )){

				$target_dir = plugins_url()."/uploads/";

				$url = wp_upload_dir();

				$user_folder = wp_get_current_user()->user_login;

				if (!file_exists($url['basedir'].'/' .$user_folder)) {
						wp_mkdir_p( $url['basedir'].'/' .$user_folder );
				}

				$target_file_csv = $url['basedir'].'/' . $user_folder .'/'. basename($_FILES['product_info_csv']['name']);

				$target_file_zip = $url['basedir'].'/' . $user_folder .'/'. basename($_FILES['product_image_zip']['name']);

				$count = 0;

				$res = '';

				$uploadOk = 1;

				$csv_tmpName = $_FILES['product_info_csv']['tmp_name'];

				$csv_name = $_FILES['product_info_csv']['name'];

				$csv_type = mime_content_type( $_FILES['product_info_csv']['tmp_name'] );

				$zip_type = mime_content_type( $_FILES['product_image_zip']['tmp_name'] );

				$csv_size = $_FILES['product_info_csv']['size'];

				$zip_size = $_FILES['product_image_zip']['size'];

			    // Check file size
				if ($zip_size > 5000000) {
				    echo "<div class='csv-error'>Sorry, your zip file is too large.</div>";
				    $uploadOk = 0;
				}
				if ($csv_size > 2000000) {
				    echo "<div class='csv-error'>Sorry, your csv file is too large.</div>";
				    $uploadOk = 0;
				}
			  if (file_exists($target_file_zip)) {
				    echo "<div class='csv-error'>Sorry, zip file already exists.</div>";
				    $uploadOk = 0;
				}
				if (file_exists($target_file_csv)) {
				    echo "<div class='csv-error'>Sorry, csv file already exists.</div>";
				    $uploadOk = 0;
				}

				$allowed_csv_type = array( "text/csv", "application/octet-stream", "application/vnd.ms-excel", "application/excel", "application/csv", "text/plain" );

				// Allow certain file formats
				if( !in_array( $csv_type, $allowed_csv_type ) )
				{
				    echo "<div class='csv-error'>Sorry, only CSV files are allowed.</div>";
				    $uploadOk = 0;
				}

				$allowed_zip_type = array( "application/zip", "application/x-zip-compressed", "application/x-compressed" );

				if( !in_array( $zip_type, $allowed_zip_type ) )
				{
				    echo "<div class='csv-error'>Sorry, only ZIP files are allowed.</div>";
				    $uploadOk = 0;
				}

				// Check if $uploadOk is set to 0 by an error
				if ($uploadOk == 0) {
				    echo "<div class='csv-error'>Sorry, your file was not uploaded.</div>";
				// if everything is ok, try to upload file
				} else {

					$user_id = get_current_user_id();

					if (is_uploaded_file($_FILES['product_info_csv']['tmp_name'])) {

				    	$flag = move_uploaded_file($_FILES['product_info_csv']['tmp_name'], $target_file_csv);

				    	if (is_uploaded_file($_FILES['product_image_zip']['tmp_name'])) {

				    		$flag1 = move_uploaded_file($_FILES['product_image_zip']['tmp_name'], $target_file_zip);

					    	if ($flag && $flag1) {

					    	$zip = new ZipArchive();
								$x = $zip->open($target_file_zip);
								if ($x === true) {
									$zip_folder = explode('.', $_FILES['product_image_zip']['name'])[0];
									wp_mkdir_p( $url['basedir'].'/' .$user_folder. '/'.$zip_folder );
									$zip->extractTo($url['basedir'].'/'.$user_folder.'/'.$zip_folder.'/'); // change this to the correct site path
									$zip->close();
								}

					    		$meta = get_user_meta( $user_id, 'csv_profile_path');

								if (!empty($meta)) {

									$data = $meta[0];

								}

								else {

									$data = array();
								}

								$data[] = array(
									'csv' => basename($_FILES['product_info_csv']['name']),
									'zip' => basename($_FILES['product_image_zip']['name'])
								);

								update_user_meta($user_id, 'csv_profile_path', $data);

								echo "<div class='csv-success'>Your files was uploaded and unpacked.</div>";

					    	}

					    }

				    }

				    else {

				    	echo "<div class='csv-error'>Sorry, your file was not uploaded.</div>";

				    }

				}

			}

			else {

				echo '<div class="csv-error">Cheati'."'".'n huh !</div>';

			}

		}

		else {

			echo '<div class="csv-error">Cheati'."'".'n huh !</div>';

		}

	}

	else {

		if (!is_admin()) {

			echo '<div class="csv-error">Please select files.</div>';

		}

		else {

			echo '<div class="notice notice-error"><p>Please select files.</p></div>';
		}

	}

}

if (!is_admin()) {

	?>

    <div class="woocommerce-account">

        <?php apply_filters('mp_get_wc_account_menu', 'marketplace'); ?>

        <div class="woocommerce-MyAccount-content">

				<?php

	echo '<div class="seller-form">';

	echo '<a href="'.site_url().'/seller/run-profile" title="Run Uploaded Profile" class="button button-primary front-button">Run Profile</a>';

	echo '<p>Download Sample File for <a href="'.WP_MASS_UPLOAD.'dummy-csv/simple.csv">Simple</a>, <a href="'.WP_MASS_UPLOAD.'dummy-csv/variable.csv">Variable</a>, <a href="'.WP_MASS_UPLOAD.'dummy-csv/grouped.csv">Grouped</a>, <a href="'.WP_MASS_UPLOAD.'dummy-csv/downloadable.csv">Downloadable</a>, <a href="'.WP_MASS_UPLOAD.'dummy-csv/external.csv">External</a> product types.</p>';

	echo '<p>Download Sample File for images zip <a href="'.WP_MASS_UPLOAD.'dummy-csv/zip_img.zip">zip_img</a></p>';

	echo '<p>Upload csv with product information along with product images zip.</p>';

}

?>

<div class="wk_mu_admin_wrapper">

	<form action="" method="post" enctype="multipart/form-data">

		<table class="form-table">

			<thead>

				<tr>
					<th>Fields</th>
					<th>Options</th>
				</tr>

			</thead>

			<tbody>

			<tr valign="top">

				<th scope="row" class="titledesc">

					<label for="upload_csv">Upload product info</label>

				</th>

				<td>

					<span class="error">* </span><input id="upload_csv" type="file" name="product_info_csv" required ><input type="button" class="upload-csv-btn button-secondary" value="Upload csv" /><br><br><span class="error">* </span><input type="text" name="csv_filename" class="csv_filename" required>

					<p class="description">Upload CSV file, follow csv format.</p>

				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc">

					<label for="upload_zip">Upload image info</label>

				</th>

				<td>

					<span class="error">* </span><input id="upload_zip" type="file" name="product_image_zip" required><input type="button" class="upload-zip-btn button-secondary" value="Upload zip" /><br><br><span class="error">* </span><input type="text" name="zip_filename" class="zip_filename" required>

					<p class="description">Upload Zip file, images name should be same as mapped in csv.</p>

				</td>

			</tr>

		 	</tbody>

		 	<tfoot>

				<tr>
					<th>Fields</th>
					<th>Options</th>
				</tr>

			</tfoot>

		</table>

		<?php wp_nonce_field( 'admin_mass_upload_action', 'admin_mass_upload_nonce' ); ?>

	 	<p class="submit">

			<input name="submit_admin_csv" class="button-primary" type="submit" value="Upload">

		</p>

	</form>

</div>

<?php

if (! is_admin()) {
		echo '</div></div></div>';
}

}
