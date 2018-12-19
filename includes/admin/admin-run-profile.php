<?php

/*
*	run csv profile admin
*/

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $wpdb;

$sql =  "SELECT user_id from {$wpdb->prefix}mpsellerinfo where seller_value = 'seller'";

$result = $wpdb->get_results($sql);

$user_id = get_current_user_id();

$csv_meta = get_user_meta($user_id, 'csv_profile_path', true);

?>

<div class="wk_mu_batch_process_wrapper">
		<img class="wk-mu-loader-image" src="<?php echo WP_MASS_UPLOAD. '/assets/images/loader.gif'; ?>">
</div>

<div class="wk_mu_admin_wrapper">

	<form action="" method="post" id="wk-run-admin-csv" enctype="multipart/form-data">

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

					<label for="select_admin">Post as admin</label>

				</th>

				<td>

					<span class="error">* </span><input id="select_admin" data-id="<?php echo get_current_user_id(); ?>" type="checkbox" name="author_admin">

					<p class="description">Check if seller will be admin itself.</p>

				</td>

			</tr>

			<tr valign="top" class="seller-select-field">

				<th scope="row" class="titledesc">

					<label for="select_seller">Select Seller</label>

				</th>

				<td>

					<span class="error">* </span><select name="csv_product_author" id="select_seller" title="Old or New">

					<option value="">Select Seller</option>

					<?php

						foreach ($result as $ke) {

						?>

						<option value="<?php echo $ke->user_id; ?>"><?php echo get_user_meta($ke->user_id, 'first_name',true).' '.get_user_meta($ke->user_id, 'last_name',true); ?></option>

						<?php

						}

					?>

					</select>

					<p class="description">Select seller to which products will be assigned (if not post as admin).</p>

				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc">

					<label for="select_profile">Select Profile</label>

				</th>

				<td>

					<span class="error">* </span><select name="csv_profile" id="select_profile" title="Old or New">

					<option value="">Select Profile</option>

					<?php

						$i = 0;

            if ($csv_meta) :
    						foreach ($csv_meta as $key => $value) {
    							?>

    							<option value="<?php echo $value['csv']; ?>"><?php echo $value['csv']; ?></option>

    							<?php

    						}
            endif;
            
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

		<?php wp_nonce_field( 'admin_run_upload_action', 'admin_run_upload_nonce' ); ?>

	 	<p class="submit">

			<input name="run_admin_csv" class="button-primary" type="submit" value="Run Profile">

		</p>

	</form>

</div>

<script id="tmpl-wkmu_notice_template" type="text/html">
     <div class="wk-mu-notice wk-mu-notice-{{{data.noticeType}}}"><p>{{{data.notice}}}</p></div>
</script>
