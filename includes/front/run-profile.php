<?php

/*
	run csv profile seller
*/

if ( ! defined( 'ABSPATH' ) )

	exit; // Exit if accessed directly

function wk_mu_front_profile() {

	$user_id = get_current_user_id();

	$csv_meta = get_user_meta( $user_id, 'csv_profile_path');

	?>

    <div class="woocommerce-account">

        <?php apply_filters('mp_get_wc_account_menu', 'marketplace'); ?>

        <div class="woocommerce-MyAccount-content">

				<?php

	echo '<div class="seller-form">';

	echo '<a href="'.site_url().'/seller/mass-upload" title="Upload Profile" class="button button-primary front-button">Upload new</a>';

	echo '<p>Run uploaded csv profile to upload products.</p>';

	?>

	<div class="wk_mu_admin_wrapper">

		<form action="../process-csv" method="post" enctype="multipart/form-data">

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

						<label for="select_profile">Select Profile</label>

					</th>

					<td>

						<span class="error">* </span><select name="csv_profile" id="select_profile" title="profile" required>

						<option value="">Select Profile</option>

						<?php

							$i = 0;

							foreach ($csv_meta as $key => $value) {

								for ($i=0; $i < count($value) ; $i++) {

									?>

									<option value="<?php echo $value[$i]['csv']; ?>"><?php echo $value[$i]['csv']; ?></option>

									<?php

								}

							}

						?>

						</select>

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

			<?php wp_nonce_field( 'front_run_upload_action', 'front_run_upload_nonce' ); ?>

		 	<p class="submit">

				<input name="run_admin_csv" class="button-primary" type="submit" value="Run Profile">

			</p>

		</form>

	</div>

<?php

echo '</div></div></div>';

}
