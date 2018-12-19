$wk_mu = jQuery.noConflict();

(function($wk_mu) {

    $wk_mu(document).ready(function() {

      $wk_mu('.wkmu-confirm-delete-profile').on('click', function() {
          return confirm('Warning: Delete profile will also deletes the files(csv/images) associated to it!!');
      });

      $wk_mu("#doaction").on("click", function(evt) {
          if (window.location.search.split('=')[1] != undefined && window.location.search.split('=')[1] == 'manage-mass-upload-profile') {
              if ($wk_mu("#bulk-action-selector-top").val() != -1 ) {
                return confirm('Warning: Delete profile will also deletes the files(csv/images) associated to it!!');
              }
          }
      });

    	$wk_mu('.upload-csv-btn').on('click', function() {

    		$wk_mu("#upload_csv").trigger("click");

    	});

    	$wk_mu('.upload-zip-btn').on('click', function() {

    		$wk_mu("#upload_zip").trigger("click");

    	});

    	$wk_mu('#upload_csv').change(function() {

	        var fullpath = $wk_mu('#upload_csv').val();

	        var filename = fullpath.split('\\').pop();

	        $wk_mu('.csv_filename').val(filename);

	    });

	    $wk_mu('#upload_zip').change(function() {

	        var fullpath = $wk_mu('#upload_zip').val();

	        var filename = fullpath.split('\\').pop();

	        $wk_mu('.zip_filename').val(filename);

	    });

	    $wk_mu('#select_admin').on('change', function() {

  		 	if ($wk_mu(this).is(":checked")) {
  		 		   $wk_mu('.seller-select-field').hide();
  		 	} else {
  		 		   $wk_mu('.seller-select-field').show();
  		 	}

  		});

      $wk_mu('#wk-run-admin-csv').on('submit', function (evt) {
          if (($wk_mu('#select_seller').val() || $wk_mu('#select_admin').is(':checked')) && $wk_mu('#select_profile').val()) {
              $wk_mu('input[name=run_admin_csv]').prop('disabled', true);
              var fileName = $wk_mu('#select_profile').val();
              if ($wk_mu('#select_admin').is(':checked')) {
                var sellerID = $wk_mu('#select_admin').data('id');
              } else {
                var sellerID = $wk_mu('#select_seller').val();
              }

              var noticeTemplate = wp.template('wkmu_notice_template');
              var noticeData = {};

              $wk_mu.ajax({
                  type: 'post',
                  url: mass_upload_object.ajaxUrl,
                  data: {
                    'action': 'wkmu_get_csv_data',
                    'csv_profile': fileName,
                    'admin_run_upload_nonce': mass_upload_object.uploadNonce,
                    'seller_id': sellerID
                  },
                  beforeSend: function() {
                      $wk_mu('.wk_mu_batch_process_wrapper').show();
                      noticeData.noticeType = 'info';
                      noticeData.notice = 'Fetching data from file!';
                      $wk_mu('.wk_mu_batch_process_wrapper').append(noticeTemplate(noticeData))
                  },
                  success: function (response) {
                    if (! response.error) {
                        if (! $wk_mu.isEmptyObject(response.productData)) {
                            var index = 0;
                            noticeData.noticeType = 'success';
                            noticeData.notice = 'Fetching process completed!';
                            $wk_mu('.wk_mu_batch_process_wrapper').append(noticeTemplate(noticeData));

                            var productData = response.productData;

                            var imgFolder = response.imgFolder;
                            var authorID = response.authorID;
                            var userFolder = response.userFolder;

                            if (productData.length > 50) {
                                var batchSize = 50;
                            } else {
                                var batchSize = productData.length;
                            }

                            noticeData.noticeType = 'info';
                            noticeData.notice = 'Total ' + productData.length + ' product(s) to create. Process will process in batch of '+batchSize+'.';
                            $wk_mu('.wk_mu_batch_process_wrapper').append(noticeTemplate(noticeData));

                            function recursiveUpload(index) {
                                var product = productData.splice(0, 50)
                                var raw_data = JSON.stringify(product);

                                $wk_mu.ajax({
                                    type: 'post'                                  ,
                                    url: mass_upload_object.ajaxUrl,
                                    data: {
                                      'action': 'wk_mu_process_admin_csv_batch',
                                      'productData': raw_data,
                                      'authorID': authorID,
                                      'imgFolder': imgFolder,
                                      'userFolder': userFolder
                                    },
                                    success: function (lastResponse) {
                                        console.log(lastResponse);
                                        noticeData.noticeType = 'warning';
                                        noticeData.notice = lastResponse.skipped + ' product(s) skipped due to duplicate sku or may be already synced.!';
                                        $wk_mu('.wk_mu_batch_process_wrapper').append(noticeTemplate(noticeData));

                                        noticeData.noticeType = 'success';
                                        noticeData.notice = lastResponse.message;
                                        $wk_mu('.wk_mu_batch_process_wrapper').append(noticeTemplate(noticeData));

                                        if (productData.length > 0) {
                                            recursiveUpload(index);
                                        } else {
                                            $wk_mu('.wk-mu-loader-image').hide();
                                            noticeData.noticeType = 'info';
                                            noticeData.notice = 'Process Completed.';
                                            $wk_mu('.wk_mu_batch_process_wrapper').append(noticeTemplate(noticeData));
                                            setTimeout(function(){
                															location.reload();
                														}, 1500);
                                        }
                                    }
                                });
                            }

                            recursiveUpload(index);
                        }
                    }
                  }
              })
          }
          evt.preventDefault();
      });

  });

})($wk_mu);
