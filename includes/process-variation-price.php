<?php

/*
*	process variation price
*/

if ( ! defined( 'ABSPATH' ) ) 
	
	exit; // Exit if accessed directly

function wk_mu_process_variation_price($vari_prod) {

	foreach ($vari_prod as $key => $value) {

		$postid = $value;
		
		$args = array(
			'post_parent' => $postid,
			'post_type'   => 'product_variation', 
			'numberposts' => -1,
			'post_status' => 'publish' 
		);
		
		$children = get_children( $args );

		foreach ($children as $k => $val) {
			
			$product_variation_ids[] = $k;

		}

		foreach ($product_variation_ids as $var_key => $var_value) {
			
			$var_regu_price[$var_value] = get_post_meta( $var_value, '_regular_price', true );
			
			$var_sale_price[$var_value] = get_post_meta( $var_value, '_sale_price', true );

		}

		$min_regu_price = min($var_regu_price);			

		foreach ($var_regu_price as $key => $value) {
			if($value == $min_regu_price)
				$min_regu_price_id = $key;
		}
		
		$max_regu_price = max($var_regu_price);

		foreach ($var_regu_price as $key => $value) {
			if($value == $max_regu_price)
				$max_regu_price_id = $key;
		}

		$min_sale_price = min($var_sale_price);

		foreach ($var_sale_price as $key => $value) {
			if( $min_sale_price && $value == $min_sale_price) {
				$min_sale_price_id = $key;
			}
		}

		$max_sale_price = max($var_sale_price);			

		foreach ($var_sale_price as $key => $value) {
			if( $max_sale_price && $value == $max_sale_price) {
				$max_sale_price_id = $key;
			}
		}

		update_post_meta( $postid, '_min_variation_price', $min_regu_price );

		update_post_meta( $postid, '_max_variation_price', $max_regu_price );

		update_post_meta( $postid, '_min_price_variation_id', $min_regu_price_id );

		update_post_meta( $postid, '_max_price_variation_id', $max_regu_price_id );

		update_post_meta( $postid, '_min_variation_regular_price', $min_regu_price );

		update_post_meta( $postid, '_max_variation_regular_price', $max_regu_price );

		update_post_meta( $postid, '_min_regular_price_variation_id', $min_regu_price_id );

		update_post_meta( $postid, '_max_regular_price_variation_id', $max_regu_price_id );

		if (!empty($min_sale_price_id)) {

			update_post_meta( $postid, '_min_variation_sale_price', $min_sale_price );

			update_post_meta( $postid, '_min_sale_price_variation_id', $min_sale_price_id );

		}

		else {

			update_post_meta( $postid, '_min_variation_sale_price', null );

			update_post_meta( $postid, '_min_sale_price_variation_id', null );

		}

		if (!empty($max_sale_price_id)) {
			
			update_post_meta( $postid, '_max_variation_sale_price', $max_sale_price );
			
			update_post_meta( $postid, '_max_sale_price_variation_id', $max_sale_price_id );

		}

		else {

			update_post_meta( $postid, '_max_variation_sale_price', null );
			
			update_post_meta( $postid, '_max_sale_price_variation_id', null );

		}


		delete_post_meta( $postid,'_price');
		
		add_post_meta( $postid, '_price', $min_regu_price );
		
		add_post_meta( $postid, '_price', $max_regu_price );

	}

}