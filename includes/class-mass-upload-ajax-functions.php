<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MP_MU_Ajax_Functions' ) ) {
    /**
     *
     */
    class MP_MU_Ajax_Functions {
        function __construct() {
            add_action( 'wp_ajax_nopriv_wkmu_get_csv_data', array( $this, 'wkmu_get_csv_data' ) );
            add_action('wp_ajax_wkmu_get_csv_data', array($this, 'wkmu_get_csv_data'));

            add_action('wp_ajax_nopriv_wk_mu_process_admin_csv_batch', array($this, 'wk_mu_process_admin_csv_batch'));
            add_action('wp_ajax_wk_mu_process_admin_csv_batch', array($this, 'wk_mu_process_admin_csv_batch'));
        }

        function wkmu_get_csv_data()
        {
            if (isset($_POST['admin_run_upload_nonce']) && !empty($_POST['admin_run_upload_nonce'])) {
                if (wp_verify_nonce($_POST['admin_run_upload_nonce'], 'admin_run_upload_action')) {
                    $author_id = 1;

                    $url = wp_upload_dir();

                    $user_folder = wp_get_current_user()->user_login;

                    $target_file = $url['basedir'].'/'.$user_folder.'/' . $_POST['csv_profile'];

                    $row = 0;

                    $csv_data = get_user_meta($author_id, 'csv_profile_path', true);

                    foreach ($csv_data as $key => $value) {
                        if ($value['csv'] == $_POST['csv_profile']) {
                            $img_folder = explode('.', $value['zip'])[0];
                        }
                    }

                    if (($handle = fopen($target_file, "r")) !== false) {
                        while ($data = fgetcsv($handle, 10000, ",")) {
                            foreach ($data as $final_key => $value) {
                                $new_arr[$row][] = $value;
                            }
                            $row++;
                        }

                        fclose($handle);

                        $base_array = $new_arr[0];

                        for ($i=1; $i < count($new_arr); $i++) {
                            for ($j=0; $j < count($base_array); $j++) {
                                $result[$i][$base_array[$j]] = $new_arr[$i][$j];
                            }
                        }

                        if (!empty($result)) {
                            $user_folder = get_userdata($author_id)->user_login;

                            $author_id = ( isset($_POST['seller_id']) ) ? $_POST['seller_id'] : 1;

                            $product_count = 0;

                            $response = array(
                                'error'       => false,
                                'productData' => array_values($result),
                                'authorID'    => $author_id,
                                'imgFolder'   => $img_folder,
                                'userFolder'  => $user_folder
                            );
                        }
                    }
                } else {
                    $response = array(
                        'error'   => true,
                        'message' => __('Security check failed!', 'mass_upload')
                    );
                }
            } else {
                $response = array(
                    'error'   => true,
                    'message' => __('Security check failed!', 'mass_upload')
                );
            }

            wp_send_json($response);
            wp_die();
        }

        function wk_mu_process_admin_csv_batch()
        {
            session_start();
            $current = $product_count = $skipped = 0;

            $result = json_decode(stripslashes($_POST['productData']), true);
            $author_id = $_POST['authorID'];
            $img_folder = $_POST['imgFolder'];
            $user_folder = $_POST['userFolder'];

            foreach ($result as $val) {
                $current++;
                $pr_id = $this->wk_mu_process_csv_data($val, $author_id, $current, $img_folder, $user_folder, $skipped);

                $skipped = $pr_id['skipped'];

                if (isset($pr_id['post_id']) && $pr_id['post_id'] && $val['type'] == 'product') {
                    $product_count++;
                }

                if (isset($pr_id['post_id']) && $pr_id['post_id'] && $val['product_type'] == 'variable') {
                    $vari_prod[] = $pr_id['post_id'];
                }
            }

            if (isset($vari_prod) && !empty($vari_prod)) {
                require_once(sprintf("%s/process-variation-price.php", dirname(__FILE__)));
                wk_mu_process_variation_price($vari_prod);
            }

            if (isset($_SESSION['var_pid'])) {
                unset($_SESSION['var_pid']);
            }

            if (isset($_SESSION['grp_pid'])) {
                unset($_SESSION['grp_pid']);
            }

            $response = array(
                'skipped' => $skipped,
                'message' => "{$product_count} product(s) imported."
            );

            wp_send_json($response);
            wp_die;
        }

        function wk_mu_process_csv_data( $val, $author_id, $current, $img_folder, $user_folder, $skipped ) {
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
            $attr_name = ($val['attribute_name']) ? $val['attribute_name'] : '';

            $attr_data = ($val['attribute_data']) ? $val['attribute_data'] : '';
            $attribute_single = ($val['attribute_single']) ? $val['attribute_single'] : '';

            $images_folder = $img_folder;
            $virtual = $product_type == 'virtual' ? 'yes' : 'no';

            $simple = $product_type == 'simple' ? 'yes' : 'no';
            $product_categories = explode('|', $product_cat);

            $attribute_name = explode( '|', $attr_name );        
            $attribute_data = explode( '|', $attr_data );

            global $wpdb;

            if (empty($product_sku)) {
                ++$skipped;
                return array('skipped'  => $skipped);
            }

            if (strlen($product_sku) < 3) {
                ++$skipped;
                return array('skipped'  => $skipped);
            }

            $sku_data = $wpdb->get_results("select meta_value from $wpdb->postmeta where meta_key='_sku'");

            foreach ($sku_data as $d) {
                $sku[] = $d->meta_value;
            }

            if (! empty($sku)) {
                if (in_array($product_sku, $sku)) {
                    ++$skipped;
                    return array('skipped'  => $skipped);
                }
            }

            if (isset($_SESSION['var_pid'])) {
                foreach ($_SESSION['var_pid'] as $key => $value) {
                    foreach ($value as $k => $val) {
                        if ($post_parent == $k) {
                            $post_parent = $val;
                        } else {
                            ++$skipped;
                            return array('skipped'  => $skipped);
                        }
                    }
                }
            }

            if ($post_type == 'product_variation') {
                $product_title = 'Product #'.$post_parent.' Variation';
                $post_name = 'product-'.$post_parent.'-variation';
            }

            if (! empty($attribute_name)) {
                foreach ( $attribute_name as $key => $value ) {
                    $product_attributes_arr[] = array(
                        'name' => $value,
                        'value' => $attribute_data[$key],
                        'position' => 1,
                        'is_visible' => 1,
                        'is_variation' => 1,
                        'is_taxonomy' => 0,
                    );
                }
            } else {
                $product_attributes_arr = array();
            }

            $product_attributes = array();

            if (! empty($product_attributes_arr)) {
                foreach ($product_attributes_arr as $attribute) {
                    if (empty($attribute['name']) || empty($attribute['value'])) {
                        continue;
                    }

                    $rep_str = $attribute['value'];
                    $rep_str = preg_replace('/\s+/', ' ', $rep_str);
                    $attribute['name'] = str_replace(' ', '-', $attribute['name']);
                    $attribute['value'] = str_replace("|", "|", $rep_str);

                    if (isset($attribute['is_visible'])) {
                        $attribute['is_visible']=(int)$attribute['is_visible'];
                    } else {
                        $attribute['is_visible'] = 0;
                    }

                    if (isset($attribute['is_variation'])) {
                        $attribute['is_variation'] = (int)$attribute['is_variation'];
                    } else {
                        $attribute['is_variation'] = 0;
                    }

                    $attribute['is_taxonomy'] = (int)$attribute['is_taxonomy'];

                    $product_attributes[str_replace(' ', '-', $attribute['name'])] = $attribute;
                }
            }

            $product_data = array(
                'post_author' => $author_id,
                'post_date'   => '',
                'post_date_gmt' => '',
                'post_content'  => $product_desc,
                'post_content_filtered' => $short_desc,
                'post_title'  => $product_title,
                'post_excerpt'  => $short_desc,
                'post_status' => $product_status,
                'post_type' => $post_type,
                'comment_status'  => $comment_status,
                'ping_status' => 'open',
                'post_password' => '',
                'post_name' => wp_strip_all_tags($product_title),
                'to_ping' => '',
                'pinged'  => '',
                'post_modified' => '',
                'post_modified_gmt' => '',
                'post_parent' => '',
                'menu_order'  => '',
                'guid'  => ''
            );

            $postid = wp_insert_post($product_data);

            $meta_keys = array();

            if ($post_type == 'product_variation') {
                $product_title = 'Variation #'.$postid.' of '.get_the_title($post_parent);
                $data = array(
                    'ID'  => $postid,
                    'post_title'  => $product_title,
                    'post_parent' => $post_parent,
                );
                wp_update_post($data);
            }

            if ($post_type == 'product_variation') {
                $meta_keys['attribute_'.$attribute_name] = $attribute_single;
            }

            if ($product_type == 'variable') {
                if (isset($_SESSION['var_pid'])) {
                    $var_session_data = $_SESSION['var_pid'];
                } else {
                    $var_session_data = array();
                }

                if (! empty($var_session_data)) {
                    $data = $var_session_data;
                } else {
                    $data = array();
                }

                $data[] = array(
                    $p_id => $postid
                );

                $_SESSION['var_pid'] = $data;
            }

            if ($product_type == 'grouped') {
                if (isset($_SESSION['grp_pid'])) {
                    $var_session_data = $_SESSION['grp_pid'];
                } else {
                    $var_session_data = array();
                }

                if (! empty($var_session_data)) {
                    $data = $var_session_data;
                } else {
                    $data = array();
                }

                $data[] = array(
                    $p_id => $postid
                );

                $_SESSION['grp_pid'] = $data;
            }

            if (isset($_SESSION['grp_pid'])) {
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

            if (! empty($filename)) {
                if (! empty($images_folder)) {
                    $filetype = wp_check_filetype(basename($filename), null);

                    $wp_upload_dir = wp_upload_dir();

                    $attachment = array(
                        'post_author' => $author_id,
                        'guid'           => $wp_upload_dir['basedir'].'/'.$user_folder.'/'.$images_folder . '/' . basename( $filename ),
                        'post_mime_type' => $filetype['type'],
                        'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
                        'post_content'   => '',
                        'post_status'    => $product_status
                    );
                    $attach_id = wp_insert_attachment($attachment, $user_folder.'/'.$images_folder . '/' .$filename);

                    require_once(ABSPATH . 'wp-admin/includes/image.php');

                    $attach_data = wp_generate_attachment_metadata($attach_id, $wp_upload_dir['basedir'].'/'.$user_folder.'/'.$images_folder . '/' .$filename);

                    wp_update_attachment_metadata($attach_id, $attach_data);
                }
            }

            if (! empty($gallery)) {
                $gallery_imgs = explode('|', $gallery);

                foreach ($gallery_imgs as $key => $value) {
                    $filename = $value;
                    $filetype = wp_check_filetype(basename($filename), null);
                    $wp_upload_dir = wp_upload_dir();

                    $attachment = array(
                        'post_author'  => $author_id,
                        'guid'           => $wp_upload_dir['basedir'].'/'.$user_folder.'/'.$images_folder . '/' . basename($filename),
                        'post_mime_type' => $filetype['type'],
                        'post_title'  => preg_replace('/\.[^.]+$/', '', basename($filename)),
                        'post_content'   => '',
                        'post_status'    => $product_status
                    );

                    $g_attach_id = wp_insert_attachment($attachment, $user_folder.'/'.$images_folder . '/' .$filename);

                    require_once(ABSPATH . 'wp-admin/includes/image.php');

                    $g_attach_data = wp_generate_attachment_metadata($g_attach_id, $wp_upload_dir['basedir'].'/'.$user_folder.'/'.$images_folder . '/' .$filename);

                    wp_update_attachment_metadata($g_attach_id, $g_attach_data);

                    $gallery_ids[] = $g_attach_id;
                }

                $gallery_img_ids = implode(',', array_unique($gallery_ids));

                $meta_keys['_product_image_gallery'] = $gallery_img_ids;
            }

            $meta_keys['_visibility'] = $visibility;

            $meta_keys['_featured'] = $featured;

            $meta_keys['_tax_status'] = $tax_status;

            $tax_class = strtolower( str_replace( ' ', '-', $tax_class ) );

            $meta_keys['_tax_class'] = $tax_class;

            $meta_keys['_sku'] = $product_sku;

            $meta_keys['_regular_price'] = $regu_price;

            if (isset($sale_price) && ! empty($sale_price)) {
                $meta_keys['_sale_price'] = $sale_price;

                $meta_keys['_price'] = $sale_price;
            } else {
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

            if ($virtual) {
                $meta_keys['_weight'] = '';

                $meta_keys['_length'] = '';

                $meta_keys['_width'] = '';

                $meta_keys['_height'] = '';
            } else {
                if (isset($weight)) {
                    $meta_keys['_weight'] = ( '' === $weight ) ? '' : wc_format_decimal( $weight );
                }

                if (isset($length)) {
                    $meta_keys['_length'] = ( '' === $length ) ? '' : wc_format_decimal( $length );
                }

                if (isset($width)) {
                    $meta_keys['_width'] = ( '' === $width ) ? '' : wc_format_decimal( $width );
                }

                if (isset($height)) {
                    $meta_keys['_height'] = ( '' === $height ) ? '' : wc_format_decimal( $height );
                }
            }

            // external product
            if ($product_type == 'external') {
                if (isset($product_url)) {
                    $btn_txt = 'Get Now';

                    $meta_keys['_product_url'] = $product_url;

                    $meta_keys['_button_text'] = $btn_txt;
                    delete_post_meta($postid, '_simple');
                }
            }

            if ($is_downloadable == 'yes') {
                $meta_keys['_downloadable'] = $is_downloadable;
                $meta_keys['_virtual'] = 'yes';
                $dwnload_url = wc_clean($product_url);

                $upload_file_url[ md5( $dwnload_url ) ] = array(
                    'name'  => $download_file,
                    'file'  => $dwnload_url
                );
                $meta_keys['_downloadable_files'] = maybe_serialize( $upload_file_url );
            } else {
                $meta_keys['_downloadable_files'] = '';
            }

            $meta_keys['_thumbnail_id'] = $attach_id;

            if (! empty($product_attributes)) {
                $meta_keys['_product_attributes'] = maybe_serialize($product_attributes);
            } else {
                $meta_keys['_product_attributes'] = '';
            }

            $custom_fields = array();
            $place_holders = array();

            $query_string = "INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value) VALUES ";

            foreach ($meta_keys as $key => $value) {
                array_push($custom_fields, $postid, $key, $value);
                $place_holders[] = "('%d', '%s', '%s')";
            }

            $query_string .= implode(', ', $place_holders);

            $wpdb->query($wpdb->prepare("$query_string ", $custom_fields));

            if (! empty($product_categories)) {
                foreach ($product_categories as $key => $value) {
                    if ($value) {
                        $idObj = get_term_by('name', $value, 'product_cat');

                        if ($idObj) {
                            $cat_id = $idObj->term_id;
                            $pro_cat_id = array( 'object_id' => $postid, 'term_taxonomy_id' => $cat_id);

                            $wpdb->insert("$wpdb->term_relationships", $pro_cat_id);
                            wp_set_object_terms( $postid, $cat_id, 'product_cat' );
                        }
                    }
                }
            }

            wp_set_object_terms($postid, $product_type, 'product_type', false);

            return array(
                'post_id' => $postid,
                'skipped' => $skipped
            );
        }
    }

    new MP_MU_Ajax_Functions();
}
