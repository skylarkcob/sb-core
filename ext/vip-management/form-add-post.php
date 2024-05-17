<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$pay_later = HT_Options()->get_tab( 'pay_later', '', 'vip' );
$pay_later = absint( $pay_later );

$price     = HTE_VIP_Management()->get_vip_post_price( '$' );
$vip_price = $price;

if ( HT()->is_positive_number( $price ) ) {
	$price = '$' . $price . '/day';
}

$email   = '';
$phone   = '';
$name    = '';
$address = '';

$can_post = 1;

if ( is_user_logged_in() ) {
	$user = wp_get_current_user();

	$email   = $user->user_email;
	$phone   = get_user_meta( $user->ID, 'phone', true );
	$name    = $user->display_name;
	$address = get_user_meta( $user->ID, 'address', true );

	if ( 1 != $pay_later ) {
		$coin = get_user_meta( $user->ID, 'coin', true );
		$coin = floatval( $coin );
		$cost = HTE_VIP_Management()->get_vip_post_price();

		if ( HT()->is_positive_number( $cost ) && $coin < $cost ) {
			$can_post = 0;
		}
	}
}

$post_type = HT_Options()->get_tab( 'post_type', '', 'add_post_frontend' );

if ( is_array( $post_type ) && 1 == count( $post_type ) ) {
	$post_type = current( $post_type );
}

$first_post_type = ( is_array( $post_type ) ) ? current( $post_type ) : $post_type;

$tax_args = array();

if ( post_type_exists( $first_post_type ) ) {
	$tax_args['object_type'] = array( $first_post_type );
}

$taxonomies = HTE_Add_Post_Frontend()->get_taxonomies( $tax_args );

$tags = array();

HTE_Add_Post_Frontend()->filter_combined_disabled_taxonomies( $taxonomies, $tags );

$disabled_taxonomies = HTE_Add_Post_Frontend()->get_disabled_taxonomies();
$combined_taxonomies = HTE_Add_Post_Frontend()->get_combined_taxonomies();
?>
<div id="wizardAddPost" data-pay-later="<?php echo $pay_later; ?>" data-can-post="<?php echo $can_post; ?>"
     class="right-label add-post-wizard" data-type="normal">
    <input type="hidden" name="vip_price" value="<?php echo $vip_price; ?>">
    <input id="TotalCost" type="hidden" name="total_cost" value="">

    <div class="navbar">
        <div class="navbar-inner">
            <ul class="clickable nav nav-pills">
                <li class="active">
                    <a href="#tab1" data-toggle="tab" aria-expanded="true">
						<span
                                class="stt-circle">1</span><?php _ex( 'Scheduling postings', 'form add post', 'sb-core' ); ?>
                    </a>
                </li>
                <li>
                    <a href="#tab2" data-toggle="tab">
                        <span class="stt-circle">2</span><?php _ex( 'Post information', 'form add post', 'sb-core' ); ?>
                    </a>
                </li>
                <li>
                    <a href="#tab3" data-toggle="tab"><span
                                class="stt-circle">3</span><?php _ex( 'Contact information', 'form add post', 'sb-core' ); ?>
                    </a>
                </li>
                <li>
                    <a href="#tab4" data-toggle="tab"><span
                                class="stt-circle">4</span><?php _ex( 'Preview post', 'form add post', 'sb-core' ); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div id="bar" class="progress">
        <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="0"
             aria-valuemin="0" aria-valuemax="100" style="width: 25%;"></div>
    </div>
    <div class="tab-content">
        <div class="tab-pane active" id="tab1">
            <h2 class="wizard-title">
                <span><?php _ex( 'Scheduling postings', 'form add post', 'sb-core' ); ?></span>
            </h2>

            <div class="col-md-3">
                <div class="form-group">
                    <div class="row">
                        <label for="typeOfPost"
                               class="col-md-12"><?php _ex( 'Type of post', 'form add post', 'sb-core' ); ?></label>

                        <div class="col-md-12">
                            <select class="form-control" id="typeOfPost" name="type_of_post" required="">
                                <option
                                        value="normal"
                                        data-price="<?php echo esc_attr( __( 'Free', 'sb-core' ) ); ?>"><?php _ex( 'Normal post', 'form add post', 'sb-core' ); ?></option>
                                <option
                                        value="vip"
                                        data-price="<?php echo $price; ?>"
                                        data-cost="<?php echo $vip_price; ?>"><?php _ex( 'Special VIP post', 'form add post', 'sb-core' ); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <div class="row">
                        <label for=""
                               class="col-md-12"><?php _ex( 'Start date', 'form add post', 'sb-core' ); ?></label>

                        <div class="col-md-12">
                            <div class="input-group">
                                <span class="input-group-btn">
                                    <button class="btn btn-default btn-opendatepicker" type="button"><i
                                                class="fa fa-calendar"></i></button>
                                </span>
                                <input data-date-format="dd/mm/yy" class="form-control datepicker" id="StartDate"
                                       name="StartDate"
                                       placeholder="dd/MM/yyyy" type="text"
                                       value="<?php echo current_time( 'd/m/Y' ); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <div class="row">
                        <label for="" class="col-md-12"><?php _ex( 'To date', 'form add post', 'sb-core' ); ?></label>

                        <div class="col-md-12">
                            <div class="input-group">
                                <span class="input-group-btn">
                                    <button class="btn btn-default btn-opendatepicker" type="button"><i
                                                class="fa fa-calendar"></i></button>
                                </span>
                                <input data-date-format="dd/mm/yy" class="form-control datepicker" id="EndDate"
                                       name="EndDate"
                                       placeholder="dd/MM/yyyy" type="text"
                                       value="<?php echo date( 'd/m/Y', strtotime( '+1 month', current_time( 'timestamp' ) ) ); ?>">
                            </div>
                            <!-- /input-group -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <div class="row">
                        <label for=""
                               class="col-md-12"><?php _ex( 'Posting fee', 'form add post', 'sb-core' ); ?></label>

                        <div class="col-md-12" style="padding-top:5px;">
                            <div class="price-fee">
                                <span class="label label-primary"><?php _e( 'Free', 'sb-core' ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
			$vip_post_description = HT_Options()->get_tab( 'vip_post_description', '', 'vip' );

			if ( ! empty( $vip_post_description ) ) {
				?>
                <div id="vipDesc" class="col-md-12 vip-desc">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
								<?php echo wpautop( $vip_post_description ); ?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}

			$normal_post_description = HT_Options()->get_tab( 'normal_post_description', '', 'vip' );

			if ( ! empty( $normal_post_description ) ) {
				?>
                <div id="normalDesc" class="col-md-12 vip-desc">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
								<?php echo wpautop( $normal_post_description ); ?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			?>
        </div>
        <div class="tab-pane" id="tab2">
            <h2 class="wizard-title">
                <span><?php _ex( 'Post information', 'form add post', 'sb-core' ); ?></span>
            </h2>

            <div class="col-md-12 form-horizontal">
				<?php
				if ( is_array( $post_type ) ) {
					?>
                    <div class="form-group">
                        <label for="add-post-type" class="control-label col-md-3"><?php _e( 'Post type', 'sb-core' ); ?>
                            :</label>

                        <div class="col-md-9">
							<?php HTE_Add_Post_Frontend()->post_type_form_control( $post_type ); ?>
                        </div>
                    </div>
					<?php
				} else {
					HTE_Add_Post_Frontend()->post_type_form_control( $post_type );
				}

				$post_title = isset( $_POST['add_post_title'] ) ? $_POST['add_post_title'] : '';
				?>
                <div class="form-group">
                    <label for="Title" class="control-label col-md-3"><?php _e( 'Post title', 'sb-core' ); ?> (<span
                                class="red">*</span>) :
                    </label>

                    <div class="col-md-9">
                        <input class="form-control" data-error="<?php _e( 'Please enter a title.', 'sb-core' ); ?>"
                               id="Title" name="add_post_title"
                               required="required" type="text" value="<?php echo $post_title; ?>">

                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3"><?php _e( 'Post content:', 'sb-core' ); ?></label>

                    <div class="col-md-9">
						<?php
						$post_content = isset( $_POST['add_post_content'] ) ? $_POST['add_post_content'] : '';

						HTE_Add_Post_Frontend()->content_editor( $post_content );
						?>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="hierarchical-taxs">
					<?php
					HTE_Add_Post_Frontend()->add_combined_taxonomies_to_list( $taxonomies, $first_post_type, array( 'right_label' => true ) );

					HTE_Add_Post_Frontend()->taxonomy_form_group_html( $taxonomies, array( 'right_label' => true ) );
					?>
                </div>
                <div class="custom-fields">
					<?php do_action( 'hte_add_post_frontend_form_middle' ); ?>
                </div>
                <div class="none-hierarchical-taxs">
					<?php HTE_Add_Post_Frontend()->taxonomy_form_group_html( $tags, array( 'right_label' => true ) ); ?>
                </div>
				<?php
				if ( HTE_Add_Post_Frontend()->can_upload_thumbnail() ) {
					?>
                    <div class="form-group">
                        <label class="control-label col-md-3"><?php _e( 'Thumbnail:', 'sb-core' ); ?></label>

                        <div class="col-md-9">
							<?php HTE_Add_Post_Frontend()->form_control_thumbnail(); ?>
                        </div>
                    </div>
					<?php
				}

				if ( HTE_VIP_Management()->can_upload_gallery() ) {
					?>
                    <div class="form-group">
                        <label class="control-label col-md-3"><?php _e( 'Gallery:', 'sb-core' ); ?></label>

                        <div class="col-md-9">
                            <label class="btn btn-success image-button">
								<span><i
                                            class="fa fa-cloud-upload"></i> <?php _e( 'Upload image', 'sb-core' ); ?></span>
                                <input accept="image/jpeg, image/png" type="file" id="post_gallery"
                                       name="post_gallery[]"
                                       style="display: none;"
                                       multiple="">
                            </label>

                            <div class="wrap-gallery wrap-image">
                            </div>
                            <div class="wrap-loader">
                                <div class="loader loader-success"></div>
                            </div>
                            <input type="hidden" name="ImageLib" id="ImageLib">
                        </div>
                    </div>
					<?php
				}
				?>
            </div>
        </div>
        <div class="tab-pane poster-info" id="tab3">
            <h2 class="wizard-title">
                <span><?php _ex( 'Contact information', 'form add post', 'sb-core' ); ?></span>
            </h2>

            <div class="col-md-12 form-horizontal">
				<?php
				$fields = HTE_VIP_Management()->author_contact_fields();

				$contacts = array(
					'NameContact'    => $name,
					'AddressContact' => $address,
					'PhoneContact'   => $phone,
					'EmailContact'   => $email
				);

				if ( HT()->array_has_value( $fields ) ) {
					foreach ( $fields as $key => $field ) {
						$type     = isset( $field['type'] ) ? $field['type'] : 'text';
						$required = isset( $field['required'] ) ? $field['required'] : false;
						$label    = isset( $field['label'] ) ? $field['label'] : '';

						if ( $required ) {
							$label .= ' (<span class="red">*</span>)';
						}

						$label = trim( $label );

						if ( ! empty( $label ) ) {
							$label .= ':';
						}

						$value = HTE_VIP_Management()->get_author_contact_default_value( $key, $contacts );
						?>
                        <div class="form-group <?php echo sanitize_html_class( $key ); ?>">
                            <label for="<?php echo $key; ?>"
                                   class="control-label col-md-3"><?php echo $label; ?></label>

                            <div class="col-md-9">
                                <input class="form-control" data-error="" id="<?php echo $key; ?>"
                                       name="<?php echo $key; ?>" type="text"
                                       value="<?php echo $value; ?>"<?php HT()->checked_selected_helper( $required, true, true, 'required' ); ?>>

                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
						<?php
					}
				}
				?>
            </div>
        </div>
        <div class="tab-pane post-preview" id="tab4">
            <h2 class="wizard-title">
                <span><?php _ex( 'Preview post', 'form add post', 'sb-core' ); ?></span>
            </h2>

            <div class="col-md-12 form-horizontal confirm-info">
				<?php
				$confirm_post_notice = HT_Options()->get_tab( 'confirm_post_notice', '', 'vip' );

				$cat_tax = get_taxonomy( 'category' );

				if ( ! empty( $confirm_post_notice ) ) {
					?>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <div class="alert alert-success">
								<?php echo $confirm_post_notice; ?>
                            </div>
                        </div>
                    </div>
					<?php
				}
				?>
                <div class="form-group">
                    <label class="control-label col-md-3 col-xs-3"><?php _e( 'Post title:', 'sb-core' ); ?></label>

                    <div class="col-md-9 col-xs-9">
                        <strong id="temp_Title"></strong>
                    </div>
                </div>
                <div class="form-group">
                    <label
                            class="control-label col-xs-3 col-md-3"><?php _ex( 'Start date:', 'form add post', 'sb-core' ); ?></label>

                    <div class="col-md-3 col-xs-9">
                        <span id="temp_StartDate"><?php echo current_time( 'd/m/Y' ); ?></span>
                    </div>
                    <label
                            class="control-label col-md-3 col-xs-3"><?php _ex( 'To date:', 'form add post', 'sb-core' ); ?></label>

                    <div class="col-md-3 col-xs-9">
						<span
                                id="temp_EndDate"><?php echo date( 'd/m/Y', strtotime( '+1 month', current_time( 'timestamp' ) ) ); ?></span>
                    </div>
                </div>
                <div class="preview-taxs">
					<?php HTE_Add_Post_Frontend()->generate_preview_taxs( $tax_args ); ?>
                </div>
                <div class="form-group separator clearfix">
                    <label
                            class="control-label col-md-3 col-xs-3"><?php _ex( 'Type of post:', 'form add post', 'sb-core' ); ?></label>

                    <div class="col-md-3 col-xs-9">
                        <span id="temp_typeOfPost"><?php _ex( 'Normal post', 'form add post', 'sb-core' ); ?></span>
                    </div>
                    <label
                            class="control-label col-md-3 col-xs-3"><?php _ex( 'Posting fee', 'form add post', 'sb-core' ); ?></label>

                    <div class="col-md-3 price-fee col-xs-9">
						<span id="temp_Fee"><span
                                    class="label label-primary"><?php _e( 'Free', 'sb-core' ); ?></span></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-xs-12"><?php _e( 'Post content:', 'sb-core' ); ?></label>

                    <div class="col-md-9 col-xs-12" id="temp_post_content"></div>
                </div>
				<?php
				if ( HTE_Add_Post_Frontend()->can_upload_thumbnail() ) {
					?>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-xs-12"><?php _e( 'Thumbnail:', 'sb-core' ); ?></label>

                        <div class="col-md-9 col-xs-12">
                            <div class="picture-wrap wrap-image" id="temp_post_thumbnail">
                            </div>
                        </div>
                    </div>
					<?php
				}

				if ( HTE_VIP_Management()->can_upload_gallery() ) {
					?>
                    <div class="form-group">
                        <label
                                class="control-label col-md-3 col-sm-3 col-xs-12"><?php _e( 'Gallery:', 'sb-core' ); ?></label>

                        <div class="col-md-9 picture-wrap wrap-image col-sm-9 col-xs-12" id="temp_post_gallery">
                        </div>
                    </div>
					<?php
				}

				if ( HT()->array_has_value( $fields ) ) {
					$parts = array_chunk( $fields, 2, true );

					if ( ! HT()->array_has_value( $parts ) ) {
						$parts = array( $fields );
					}

					$last_parts = array_pop( $parts );

					if ( 2 == count( $last_parts ) ) {
						$parts[] = $last_parts;
					}

					$count = 0;

					if ( HT()->array_has_value( $parts ) ) {
						foreach ( $parts as $fields ) {
							$div_class = 'form-group';

							if ( 0 == $count ) {
								$div_class .= ' separator clearfix top';
							}
							?>
                            <div class="<?php echo $div_class; ?>">
								<?php
								foreach ( $fields as $key => $field ) {
									$label = isset( $field['label'] ) ? $field['label'] : '';

									$label = trim( $label );

									if ( ! empty( $label ) ) {
										$label .= ':';
									}

									$value = HTE_VIP_Management()->get_author_contact_default_value( $key, $contacts );
									?>
                                    <label
                                            class="control-label col-md-3 col-xs-3"><?php echo $label; ?></label>

                                    <div class="col-md-3 col-xs-9">
                                        <strong id="temp_<?php echo $key; ?>"><?php echo $value; ?></strong>
                                    </div>
									<?php
								}
								?>
                            </div>
							<?php
							$count ++;
						}
					}

					if ( 1 == count( $last_parts ) ) {
						$div_class = 'form-group';

						if ( 0 == $count ) {
							$div_class .= ' separator clearfix top';
						}
						?>
                        <div class="<?php echo $div_class; ?>">
							<?php
							foreach ( $last_parts as $key => $field ) {
								$label = isset( $field['label'] ) ? $field['label'] : '';

								$label = trim( $label );

								if ( ! empty( $label ) ) {
									$label .= ':';
								}

								$value = '';

								switch ( $key ) {
									case 'NameContact':
										$value = $name;
										break;
									case 'AddressContact':
										$value = $address;
										break;
									case 'PhoneContact':
										$value = $phone;
										break;
									case 'EmailContact':
										$value = $email;
										break;
								}
								?>
                                <label
                                        class="control-label col-md-3 col-xs-3"><?php echo $label; ?></label>

                                <div class="col-md-3 col-xs-9">
                                    <strong id="temp_<?php echo $key; ?>"><?php echo $value; ?></strong>
                                </div>
								<?php
							}
							?>
                        </div>
						<?php
					}
				}
				?>
            </div>
            <div class="col-md-12 text-center">
				<?php
				if ( HTE_Add_Post_Frontend()->use_captcha() && HT_CAPTCHA()->check_config_valid() ) {
					?>
                    <div class="captcha-box">
						<?php HT_CAPTCHA()->display_html(); ?>
                    </div>
					<?php
				}
				?>
                <button type="submit" class="btn btn-primary disabled"><i
                            class="fa fa-save mr-5"></i> <?php _ex( 'Add post', 'add post frontend', 'sb-core' ); ?>
                </button>
            </div>
        </div>
        <ul class="pager wizard">
            <li class="previous disabled">
                <a href="#"><i class="fa fa-backward"></i> <?php _e( 'Previous', 'sb-core' ); ?></a>
            </li>
            <li class="next">
                <a href="#"><?php _e( 'Next', 'sb-core' ); ?> <i class="fa fa-forward"></i></a>
            </li>
        </ul>
    </div>

</div>
