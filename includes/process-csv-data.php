<?php
/*
	csv process
*/

if ( ! defined( 'ABSPATH' ) )

	exit; // Exit if accessed directly

function wk_mu_process_csv() {

	if (isset($_POST['run_admin_csv'])) {

		if ( !empty($_POST['csv_profile']) ) {

			if ( isset($_POST['front_run_upload_nonce']) && !empty( $_POST['front_run_upload_nonce'] ) ) {

		 		if( wp_verify_nonce( $_POST['front_run_upload_nonce'], 'front_run_upload_action' ) ) {

		 			echo '<div class="csv-success">Starting Execution...</div>';

		 			$author_id = get_current_user_id();

		 			$url = wp_upload_dir();

					$csv_data = get_user_meta( $author_id, 'csv_profile_path', true);

					foreach ($csv_data as $key => $value) {
							if ($value['csv'] == $_POST['csv_profile']) {
									$img_folder = explode('.', $value['zip'])[0];
							}
					}

					$user_folder = wp_get_current_user()->user_login;

					$target_file = $url['basedir'].'/'.$user_folder.'/' . $_POST['csv_profile'];

					$row = 0;

					if (($handle = fopen($target_file, "r")) !== FALSE) {

					    while($data = fgetcsv ($handle, 10000, ",")) {

					        foreach ($data as $final_key => $value) {

					        	$new_arr[$row][] = $value;

					        }

					        $row++;
					    }

					    fclose($handle);

					    $base_array = $new_arr[0];

					    for ($i=1; $i < count($new_arr); $i++) {

					    	for ($j=0; $j < count($base_array) ; $j++) {

					    		$result[$i][$base_array[$j]] = $new_arr[$i][$j];

					    	}

					    }

					    echo '<div class="csv-success">Please don'."'".'t close or refresh the window while importing products.</div>';

					   	$product_count = 0;

					    foreach ($result as $val) {
					    	if ($val['type'] == 'product') {
					    		$product_count++;
					    	}
					    }

					    echo '<div class="csv-success">Total '.$product_count.' Product(s) to import.</div>';

					   	session_start();

					   	$current = $product_count = 0;

					    foreach ($result as $val) {

					    	$current++;

					    	$pr_id = wk_mu_process_csv_data($val, $author_id, $current, $img_folder, $user_folder);

					    	if ($pr_id && $val['type'] == 'product') {

					    		$product_count++;

					    	}

					    	if ($pr_id && $val['product_type'] == 'variable') {

					    		$vari_prod[] = $pr_id;

					    	}

					    }

					    if ( isset($vari_prod) && !empty($vari_prod) ) {

						    require_once(sprintf("%s/process-variation-price.php", dirname(__FILE__)));

						    wk_mu_process_variation_price($vari_prod);

					    }

					    if (isset($_SESSION['var_pid'])) {

					    	unset($_SESSION['var_pid']);

					    }

					    if (isset($_SESSION['grp_pid'])) {

					    	unset($_SESSION['grp_pid']);

					    }

					    $current = 0;

					    foreach ($result as $val) {

					    	$current++;

					    	wk_mu_outputProgress($current, count($result));

					    }

					    echo '<div class="csv-success" style="margin-top:60px;">Total '.$product_count.' product(s) imported.</div>';

					    echo '<div class="csv-success">Finished Execution.</div>';

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

			echo '<div class="csv-error">Select all fields. <a href="'.site_url().'/seller/run-profile">Go Back</a></div>';

		}

	}

}

function wk_mu_process_admin_csv($result, $author_id, $img_folder, $user_folder) {
		session_start();

   	$current = $product_count = 0;

    foreach ($result as $val) {

    	$current++;

    	$pr_id = wk_mu_process_csv_data($val, $author_id, $current, $img_folder, $user_folder);

    	if ($pr_id && $val['type'] == 'product') {

    		$product_count++;

    	}

    	if ($pr_id && $val['product_type'] == 'variable') {

    		$vari_prod[] = $pr_id;

    	}

    }

    if ( isset($vari_prod) && !empty($vari_prod) ) {

	    require_once(sprintf("%s/process-variation-price.php", dirname(__FILE__)));

	    wk_mu_process_variation_price($vari_prod);

    }

    if (isset($_SESSION['var_pid'])) {

    	unset($_SESSION['var_pid']);

    }

    if (isset($_SESSION['grp_pid'])) {

    	unset($_SESSION['grp_pid']);

    }

    echo '<div class="csv-success" style="margin-top:60px;">Total '.$product_count.' product(s) imported.</div>';

    echo '<div class="csv-success">Finished Execution.</div>';

}

function wk_mu_process_csv_data( $val, $author_id, $current, $img_folder, $user_folder ) {
	$p_id = ($val['ID']) ? $val['ID'] : '';

	$product_title = ($val['product_name']) ? $val['product_name'] : '';

	$product_sku = ($val['sku']) ? $val['sku'] : '';

	$short_desc = ($val['short_desc']) ? $val['short_desc'] : '';

	$product_desc = ($val['description']) ? $val['description'] : '';

	$product_status = ($val['product_status']) ? $val['product_status'] : '';

	$post_parent = ($val['product_parent']) ? $val['product_parent'] : 0;

	$post_type = ($val['type']) ? $val['type'] : '';

	$comment_status = ($val['comment_status']) ? $val['comment_status'] : '';

	$is_downloadable = ($val['downloadable']) ? $val['downloadable'] : '';

	$is_virtual = ($val['virtual']) ? $val['virtual'] : '';

	$visibility = ($val['visibility']) ? $val['visibility'] : '';

	$stock = ($val['stock']) ? $val['stock'] : '';

	$stock_status = ($val['stock_status']) ? $val['stock_status'] : '';

	$backorders = ($val['backorders']) ? $val['backorders'] : '';

	$manage_stock = ($val['manage_stock']) ? $val['manage_stock'] : '';

	$regu_price = ($val['regular_price']) ? $val['regular_price'] : '';

	$sale_price = ($val['sale_price']) ? $val['sale_price'] : '';

	$weight = ($val['weight']) ? $val['weight'] : '';

	$length = ($val['length']) ? $val['length'] : '';

	$width = ($val['width']) ? $val['width'] : '';

	$height = ($val['height']) ? $val['height'] : '';

	$tax_status = ($val['tax_status']) ? $val['tax_status'] : '';

	$tax_class = ($val['tax_class']) ? $val['tax_class'] : '';

	$featured = ($val['featured']) ? $val['featured'] : '';

	$sale_price_dates_from = ($val['sale_price_dates_from']) ? $val['sale_price_dates_from'] : '';

	$sale_price_dates_to = ($val['sale_price_dates_to']) ? $val['sale_price_dates_to'] : '';

	$download_limit = ($val['download_limit']) ? $val['download_limit'] : '';

	$download_expiry = ($val['download_expiry']) ? $val['download_expiry'] : '';

	$download_file = ($val['download_file']) ? $val['download_file'] : '';

	$product_url = ($val['product_url']) ? $val['product_url'] : '';

	$product_image = ($val['product_image']) ? $val['product_image'] : '';

	$gallery = ($val['gallery']) ? $val['gallery'] : '';

	$product_type = ($val['product_type']) ? $val['product_type'] : '';

	$product_cat = ($val['product_cat']) ? $val['product_cat'] : '';

	$attribute_name = ($val['attribute_name']) ? $val['attribute_name'] : '';

	$attribute_data = ($val['attribute_data']) ? $val['attribute_data'] : '';

	$attribute_single = ($val['attribute_single']) ? $val['attribute_single'] : '';

	// HUBSTY variables

	$color = ($val['color']) ? $val['color'] : '';
	$brand = ($val['brand']) ? $val['brand'] : '';
	$depth = ($val['depth']) ? $val['depth'] : '';
	$volume = ($val['volume']) ? $val['volume'] : '';
	$material = ($val['material']) ? $val['material'] : '';
	$customisable = ($val['customisable']) ? $val['customisable'] : '';
	$pack_height_cm = ($val['pack_height_cm']) ? $val['pack_height_cm'] : '';
	$pack_lenght_cm = ($val['pack_lenght_cm']) ? $val['pack_lenght_cm'] : '';
	$pack_width_cm = ($val['pack_lenght_cm']) ? $val['pack_width_cm'] : '';
	$pack_weight = ($val['pack_weight']) ? $val['pack_weight'] : '';
	$eco_friendly = ($val['eco_friendly']) ? $val['breco_friendlyand'] : '';

	$images_folder = $img_folder;

	$virtual = 'virtual' === $product_type ? 'yes' : 'no';

	$simple = 'simple' === $product_type ? 'yes' : 'no';

	$product_categories = explode( '|', $product_cat );

	global $wpdb;

	if ( empty( $product_sku ) ) {
		echo '<div style="color:red">Row ' . $current . ' skipped. SKU should contain atleast 3 letters</div>';
		return;
	}

	if ( strlen( $product_sku ) < 3 ) {
		echo '<div style="color:red">Row ' . $current . ' skipped. SKU should contain atleast 3 letters</div>';
		return;
	}

	$sku_data = $wpdb->get_results( "select meta_value from $wpdb->postmeta where meta_key='_sku'" );

	foreach ( $sku_data as $d ) {
		$sku[] = $d->meta_value;
	}

	if ( ! empty( $sku ) ) {
		if ( in_array( $product_sku, $sku ) ) {
			echo '<div style="color:red">Row ' . $current . ' skipped. SKU already exists.</div>';
			return;
		}
	}

	if ( isset( $_SESSION['var_pid'] ) ) {
		foreach ( $_SESSION['var_pid'] as $key => $value ) {
			foreach ( $value as $k => $val ) {
				if ( $post_parent == $k ) {
					$post_parent = $val;
				} else {
					echo '<div style="color:red">Row ' . $current . ' skipped. Parent Product not exists.</div>';
					return;
				}
			}
		}
	}

	if ( 'product_variation' === $post_type ) {
		$product_title = 'Product #' . $post_parent . ' Variation';

		$post_name = 'product-' . $post_parent . '-variation';
	}

	if ( ! empty( $attribute_name ) ) {
		$product_attributes_arr[] = array(
			'name' => $attribute_name,
			'value' => $attribute_data,
			'position' => 1,
			'is_visible' => 1,
			'is_variation' => 1,
			'is_taxonomy' => 0,
		);
	} else {
		$product_attributes_arr = array();
	}

	$product_attributes = array();

	if ( ! empty( $product_attributes_arr ) ) {
		foreach ( $product_attributes_arr as $attribute ) {
			if ( empty( $attribute['name'] ) || empty( $attribute['value'] ) ) {
				continue;
			}

			$rep_str = $attribute['value'];

			$rep_str = preg_replace( '/\s+/', ' ', $rep_str );

			$attribute['name'] = str_replace( ' ', '-', $attribute['name'] );

			$attribute['value'] = str_replace( '|', '|', $rep_str );

			if ( isset( $attribute['is_visible'] ) ) {
				$attribute['is_visible'] = (int) $attribute['is_visible'];
			} else {
				$attribute['is_visible'] = 0;
			}

			if ( isset( $attribute['is_variation'] ) ) {
				$attribute['is_variation'] = (int) $attribute['is_variation'];
			} else {
				$attribute['is_variation'] = 0;
			}

			$attribute['is_taxonomy'] = (int) $attribute['is_taxonomy'];

			$product_attributes[ str_replace( ' ', '-', $attribute['name'] ) ] = $attribute;
		}
	}

	$product_data = array(
		'post_author'   => $author_id,
		'post_date'     => '',
		'post_date_gmt' => '',
		'post_content'  => $product_desc,
		'post_content_filtered' => $short_desc,
		'post_title'   => $product_title,
		'post_excerpt' => $short_desc,
		'post_status'  => $product_status,
		'post_type'    => $post_type,
		'comment_status' => $comment_status,
		'ping_status'    => 'open',
		'post_password'  => '',
		'post_name'      => wp_strip_all_tags( $product_title ),
		'to_ping'        => '',
		'pinged'         => '',
		'post_modified'  => '',
		'post_modified_gmt' => '',
		'post_parent'    => '',
		'menu_order'     => '',
		'guid'           => '',
	);

	$postid = wp_insert_post( $product_data );

	$meta_keys = array();

	if ( 'product_variation' === $post_type ) {
		$product_title = 'Variation #' . $postid . ' of ' . get_the_title( $post_parent );

		$data = array(
			'ID' => $postid,
			'post_title' => $product_title,
			'post_parent' => $post_parent,
		);

		wp_update_post( $data );
	}

	if ( 'product_variation' === $post_type ) {
		$meta_keys[ 'attribute_' . $attribute_name ] = $attribute_single;
	}

	if ( 'variable' === $product_type ) {
		if ( isset( $_SESSION['var_pid'] ) ) {
			$var_session_data = $_SESSION['var_pid'];
		} else {
			$var_session_data = array();
		}

		if ( ! empty( $var_session_data ) ) {
			$data = $var_session_data;
		} else {
			$data = array();
		}

		$data[] = array(
			$p_id => $postid,
		);

		$_SESSION['var_pid'] = $data;
	}

	if ( 'grouped' === $product_type ) {
		if ( isset( $_SESSION['grp_pid'] ) ) {
			$var_session_data = $_SESSION['grp_pid'];
		} else {
			$var_session_data = array();
		}

		if ( ! empty( $var_session_data ) ) {
			$data = $var_session_data;
		} else {
			$data = array();
		}

		$data[] = array(
			$p_id => $postid,
		);

		$_SESSION['grp_pid'] = $data;
	}

	if ( isset( $_SESSION['grp_pid'] ) ) {
		foreach ( $_SESSION['grp_pid'] as $key => $value ) {
			foreach ( $value as $k => $val ) {
				if ( $post_parent == $k ) {
					if ( ! empty( $val ) ) {
						$childrens = get_post_meta( $val, '_children', true );

						$childrens = is_array( $childrens ) ? $childrens : array();

						array_push( $childrens, $postid );

						update_post_meta( $val, '_children', $childrens );
					}
				}
			}
		}
	}

	$filename = $product_image;

	if ( ! empty( $filename ) ) {
		if ( empty( $images_folder ) ) {
			echo '<div style="color:red">Row ' . $current . ' skipped. Image folder name empty.</div>';
		} else {
				$filetype = wp_check_filetype( basename( $filename ), null );

				$wp_upload_dir = wp_upload_dir();

				$attachment = array(
					'post_author'    => $author_id,
					'guid'           => $wp_upload_dir['basedir'] . '/' . $user_folder . '/' . $images_folder . '/' . basename( $filename ),
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
					'post_content'   => '',
					'post_status'    => $product_status,
				);

				$attach_id = wp_insert_attachment( $attachment, $user_folder . '/' . $images_folder . '/' . $filename );

				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				$attach_data = wp_generate_attachment_metadata( $attach_id, $wp_upload_dir['basedir'] . '/' . $user_folder . '/' . $images_folder . '/' . $filename );

				wp_update_attachment_metadata( $attach_id, $attach_data );
		}
	}

	if ( ! empty( $gallery ) ) {
		$gallery_imgs = explode( '|', $gallery );

		foreach ( $gallery_imgs as $key => $value ) {
			$filename = $value;

			$filetype = wp_check_filetype( basename( $filename ), null );

			$wp_upload_dir = wp_upload_dir();

			$user_folder = get_userdata( $author_id )->user_login;

			$attachment = array(
				'post_author' => $author_id,
				'guid'        => $wp_upload_dir['basedir'] . '/' . $user_folder . '/' . $images_folder . '/' . basename( $filename ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => $product_status,
			);

			$g_attach_id = wp_insert_attachment( $attachment, $user_folder . '/' . $images_folder . '/' . $filename );

			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			$g_attach_data = wp_generate_attachment_metadata( $g_attach_id, $wp_upload_dir['basedir'] . '/' . $user_folder . '/' . $images_folder . '/' . $filename );

			wp_update_attachment_metadata( $g_attach_id, $g_attach_data );

			$gallery_ids[] = $g_attach_id;
		}

		$gallery_img_ids = implode( ',', array_unique( $gallery_ids ) );

		$meta_keys['_product_image_gallery'] = $gallery_img_ids;
	}

	$meta_keys['_visibility'] = $visibility;

	$meta_keys['_featured'] = $featured;

	$meta_keys['_tax_status'] = $tax_status;

	$tax_class = strtolower( str_replace( ' ', '-', $tax_class ) );

	$meta_keys['_tax_class'] = $tax_class;

	$meta_keys['_sku'] = $product_sku;

	$meta_keys['_regular_price'] = $regu_price;

	if ( isset( $sale_price ) && ! empty( $sale_price ) ) {
		$meta_keys['_sale_price'] = $sale_price;

		$meta_keys['_price'] = $sale_price;
	}
	else {
		$meta_keys['_sale_price'] = '';

		$meta_keys['_price'] = $regu_price;
	}

	$meta_keys['_sale_price_dates_from'] = $sale_price_dates_from;

	$meta_keys['_sale_price_dates_to'] = $sale_price_dates_to;

	$meta_keys['_backorders'] = $backorders;

	$meta_keys['_stock_status'] = $stock_status;

	$meta_keys['_stock'] = $stock;

	$meta_keys['_manage_stock'] = $manage_stock;

	$meta_keys['_virtual'] = $virtual;

	$meta_keys['_simple'] = $simple;

	$meta_keys['_download_limit'] = $download_limit;

	$meta_keys['_download_expiry'] = $download_expiry;

	// HUBSTY variables
	$meta_keys['_color'] = $color;
	$meta_keys['_brand'] = $brand;
	$meta_keys['_customisable'] = $customisable;
	$meta_keys['_pack_height_cm'] = $pack_height_cm;
	$meta_keys['_pack_height_cm'] = $pack_height_cm;
	$meta_keys['_pack_lenght_cm'] = $pack_lenght_cm;
	$meta_keys['_pack_lenght_cm'] = $pack_lenght_cm;
	$meta_keys['_pack_weight'] = $pack_weight;
	$meta_keys['_eco_friendly'] = $eco_friendly;

	if ( $virtual ) {
		$meta_keys['_weight'] = '';

		$meta_keys['_length'] = '';

		$meta_keys['_width'] = '';

		$meta_keys['_height'] = '';
	} else {

		if ( isset( $weight ) ) {
			$meta_keys['_weight'] = ( '' === $weight ) ? '' : wc_format_decimal( $weight );
		}

		if ( isset( $length ) ) {
				$meta_keys['_length'] = ( '' === $length ) ? '' : wc_format_decimal( $length );
		}

		if ( isset( $width ) ) {
				$meta_keys['_width'] = ( '' === $width ) ? '' : wc_format_decimal( $width );
		}

		if ( isset( $height ) ) {
				$meta_keys['_height'] = ( '' === $height ) ? '' : wc_format_decimal( $height );
		}
	}

	if ( $product_type == 'external' ) {
		if ( isset( $product_url ) ) {
			$btn_txt = 'Get Now';

			$meta_keys['_product_url'] = $product_url;

			$meta_keys['_button_text'] = $btn_txt;

			delete_post_meta( $postid, '_simple' );
		}
	}

	if ( $is_downloadable == 'yes' ) {
		$meta_keys['_downloadable'] = $is_downloadable;

		$meta_keys['_virtual'] = 'yes';

		$dwnload_url = wc_clean( $product_url );

		$upload_file_url[ md5( $dwnload_url ) ] = array(
			'name' => $download_file,
			'file' => $dwnload_url,
		);

		$meta_keys['_downloadable_files'] = maybe_serialize( $upload_file_url );
	} else {
		$meta_keys['_downloadable_files'] = '';
	}

	$meta_keys['_thumbnail_id'] = $attach_id;

	if ( ! empty( $product_attributes ) ) {
		$meta_keys['_product_attributes'] = maybe_serialize( $product_attributes );
	} else {
		$meta_keys['_product_attributes'] = '';
	}

	$custom_fields = array();
	$place_holders = array();

	$query_string = "INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value) VALUES ";

	foreach ( $meta_keys as $key => $value ) {
			array_push( $custom_fields, $postid, $key, $value );
			$place_holders[] = "('%d', '%s', '%s')";
	}

	$query_string .= implode( ', ', $place_holders );

	file_put_contents('../../hello24.php', var_export($custom_fields , true));

	$wpdb->query( $wpdb->prepare( "$query_string ", $custom_fields ) );

	if ( ! empty( $product_categories ) ) {
		foreach ( $product_categories as $key => $value ) {
			if ( $value ) {
				$idObj = get_term_by( 'slug', $value, 'product_cat' );

				if ( $idObj ) {
					$cat_id = $idObj->term_id;

					$pro_cat_id = array( 'object_id' => $postid, 'term_taxonomy_id' => $cat_id );

					$wpdb->insert( "$wpdb->term_relationships", $pro_cat_id );
				}
			}
		}
	}

	wp_set_object_terms( $postid, $product_type, 'product_type', false );

	return $postid;
}

function wk_mu_outputProgress($current, $total) {

	?>

	<div id="csv_progress">
	  	<div id="csv_progress_bar" style="width:<?php echo round($current / $total * 100) . "%"; ?>"></div>
	</div>

	<?php

    wk_mu_myFlush();

    sleep(1);

}

function wk_mu_myFlush() {

    echo(str_repeat(' ', 256));

    if (@ob_get_contents()) {

        @ob_end_flush();

    }

    flush();

}
