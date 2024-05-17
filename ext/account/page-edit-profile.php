<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! is_user_logged_in() ) {
	$url = wp_login_url( get_the_permalink() );
	?>
	<p class="alert alert-warning"><?php _e( 'Please login to use this function.', 'sb-core' ); ?></p>
	<script>
		window.location.href = "<?php echo $url; ?>";
	</script>
	<?php
	return;
}

$user    = HTE_Account()->user;
$user_id = $user->ID;

$contacts = wp_get_user_contact_methods( $user_id );
?>
<div id="updateProfile" class="update-profile">
	<?php
	if ( isset( $_POST['submit'] ) ) {
		$first_name   = isset( $_POST['first_name'] ) ? $_POST['first_name'] : '';
		$last_name    = isset( $_POST['last_name'] ) ? $_POST['last_name'] : '';
		$user_email   = isset( $_POST['user_email'] ) ? $_POST['user_email'] : '';
		$description  = isset( $_POST['description'] ) ? $_POST['description'] : '';
		$nickname     = isset( $_POST['nickname'] ) ? $_POST['nickname'] : $user->user_login;
		$display_name = isset( $_POST['display_name'] ) ? $_POST['display_name'] : $user->display_name;

		$user_pass = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : '';
		$pass2     = isset( $_POST['pass2'] ) ? $_POST['pass2'] : '';

		$error = false;

		if ( ! is_email( $user_email ) ) {
			?>
			<p class="alert alert-error alert-danger">
				<?php _e( 'Invalid email address.', 'sb-core' ); ?>
			</p>
			<?php
			$error = true;
		}

		if ( ! empty( $user_pass ) && $pass2 !== $user_pass ) {
			?>
			<p class="alert alert-error alert-danger">
				<?php _e( 'Invalid password or passwords do not match.', 'sb-core' ); ?>
			</p>
			<?php
			$error = true;
		}

		$current_pass = isset( $_POST['current_pass'] ) ? $_POST['current_pass'] : '';

		if ( ! empty( $user_pass ) && ! wp_check_password( $current_pass, $user->user_pass, $user->ID ) ) {
			?>
			<p class="alert alert-error alert-danger">
				<?php _e( 'Your current password does not match with our records.', 'sb-core' ); ?>
			</p>
			<?php
			$error = true;
		}

		if ( ! $error ) {
			$data = array(
				'ID'           => $user_id,
				'first_name'   => $first_name,
				'last_name'    => $last_name,
				'user_email'   => $user_email,
				'nickname'     => $nickname,
				'display_name' => $display_name
			);

			if ( ! empty( $user_pass ) ) {
				$data['user_pass'] = $user_pass;
			}

			$updated = wp_update_user( $data );

			if ( $updated ) {
				update_user_meta( $user_id, 'description', $description );

				if ( HT()->array_has_value( $contacts ) ) {
					foreach ( $contacts as $name => $label ) {
						$key   = 'contact_' . $name;
						$value = isset( $_POST[ $key ] ) ? $_POST[ $key ] : '';
						update_user_meta( $user_id, $name, $value );
					}
				}
			}

			if ( $updated ) {
				$user = new WP_User( $user_id );
				?>
				<p class="alert alert-success">
					<?php _e( 'Your profile has been updated successfully.', 'sb-core' ); ?>
				</p>
				<?php
			} else {
				?>
				<p class="alert alert-error alert-danger">
					<?php _e( 'There was an error occurred, please try again.', 'sb-core' ); ?>
				</p>
				<?php
			}
		}
	}
	?>
	<form class="update-profile-form clearfix" action="" method="post" enctype="multipart/form-data">
		<div class="col-xs-12 col-md-12">
			<div class="row row-content">
				<div class="col-md-3 sidebar-col">
					<div class="profile-sidebar row">
						<div class="user-info">
							<div class="image cover-area">
								<?php echo get_avatar( $user->user_email, 90, '', $user->display_name, array( 'class' => 'cover-image img-thumbnail bg-white' ) ); ?>
							</div>
							<p class="text-center">
								<a href="<?php echo get_author_posts_url( $user->ID ); ?>">
									<strong><?php echo $user->display_name; ?></strong>
								</a>
							</p>
							<hr>
							<p>
								<strong><?php _e( 'Roles:', 'sb-core' ); ?></strong>
								<span><?php echo implode( ', ', $user->roles ); ?></span>
							</p>

							<p>
								<strong><?php _e( 'Joined:', 'sb-core' ); ?></strong>
								<span><?php echo date( HOCWP_Theme()->get_date_format(), strtotime( $user->user_registered ) ); ?></span>
							</p>

							<p>
								<strong><?php _e( 'Nickname:', 'sb-core' ); ?></strong>
								<span><?php echo $user->nickname; ?></span>
							</p>
						</div>
						<div class="tabs">
							<ul class="nav nav-tabs">
								<li class="active">
									<a href="#profileGeneral"
									   data-toggle="tab"><?php _ex( 'General information', 'profile', 'sb-core' ); ?></a>
								</li>
								<li><a href="#profileSecurity"
								       data-toggle="tab"><?php _ex( 'Security setting', 'profile', 'sb-core' ); ?></a>
								</li>
								<li><a href="#profileContact"
								       data-toggle="tab"><?php _ex( 'Contact information', 'profile', 'sb-core' ); ?></a>
								</li>
								<?php do_action( 'hocwp_theme_extension_account_profile_sidebar_tab' ); ?>
							</ul>
						</div>
					</div>
				</div>
				<div class="col-md-9">
					<div class="profile-fields">
						<div class="tab-content mb-15">
							<div class="tab-pane active" id="profileGeneral">
								<div class="form-group row mb-15">
									<div class="col-md-3">
										<label for="username"><?php _e( 'Username', 'sb-core' ); ?></label>
									</div>
									<div class="col-md-9">
										<input class="textfield form-control" id="username" type="text" data-required=""
										       data-type="text"
										       name="username" placeholder="" value="<?php echo $user->user_login; ?>"
										       size="40"
										       readonly disabled>
									</div>
								</div>
								<div class="form-group row mb-15">
									<div class="col-md-3">
										<label for="first_name"><?php _e( 'First Name', 'sb-core' ); ?></label>
									</div>
									<div class="col-md-9">
										<input class="textfield form-control" id="first_name" type="text"
										       data-required=""
										       data-type="text"
										       name="first_name" placeholder="" value="<?php echo $user->first_name; ?>"
										       size="40">
									</div>
								</div>
								<div class="form-group row mb-15">
									<div class="col-md-3">
										<label for="last_name"><?php _e( 'Last Name', 'sb-core' ); ?></label>
									</div>
									<div class="col-md-9">
										<input class="textfield form-control" id="last_name" type="text"
										       data-required=""
										       data-type="text"
										       name="last_name" placeholder="" value="<?php echo $user->last_name; ?>"
										       size="40">
									</div>
								</div>
								<div class="form-group row mb-15">
									<div class="col-md-3">
										<label for="display_name"><?php _e( 'Display Name', 'sb-core' ); ?></label>
									</div>
									<div class="col-md-9">
										<select class="form-control" name="display_name" id="display_name">
											<?php
											$public_display = array();

											$public_display['display_nickname'] = $user->nickname;
											$public_display['display_username'] = $user->user_login;

											if ( ! empty( $user->first_name ) ) {
												$public_display['display_firstname'] = $user->first_name;
											}

											if ( ! empty( $user->last_name ) ) {
												$public_display['display_lastname'] = $user->last_name;
											}

											if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
												$public_display['display_firstlast'] = $user->first_name . ' ' . $user->last_name;
												$public_display['display_lastfirst'] = $user->last_name . ' ' . $user->first_name;
											}

											if ( ! in_array( $user->display_name, $public_display ) ) {
												$public_display = array( 'display_displayname' => $user->display_name ) + $public_display;
											}

											$public_display = array_map( 'trim', $public_display );
											$public_display = array_unique( $public_display );

											foreach ( $public_display as $id => $item ) {
												?>
												<option <?php selected( $user->display_name, $item ); ?>><?php echo $item; ?></option>
												<?php
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group row mb-15">
									<div class="col-md-3">
										<label for="nickname"><?php _e( 'Nickname', 'sb-core' ); ?></label>
									</div>
									<div class="col-md-9">
										<input class="textfield form-control" id="nickname" type="text"
										       data-required=""
										       data-type="text"
										       name="nickname" placeholder="" value="<?php echo $user->nickname; ?>"
										       size="40">
									</div>
								</div>
								<div class="form-group row mb-15">
									<div class="col-md-3">
										<label for="user_email"><?php _e( 'Email', 'sb-core' ); ?></label>
									</div>
									<div class="col-md-9">
										<input id="user_email" type="email" class="email form-control" data-required=""
										       data-type="text"
										       name="user_email" placeholder="" value="<?php echo $user->user_email; ?>"
										       size=""
										       required autocomplete="username">
									</div>
								</div>
								<div class="form-group row">
									<div class="col-md-3">
										<label for="description"><?php _e( 'Bio', 'sb-core' ); ?></label>
									</div>
									<div class="col-md-9">
										<textarea class="textareafield form-control" id="description" name="description"
										          data-required=""
										          data-type="textarea" placeholder="" rows="5"
										          cols="50"><?php echo get_user_meta( $user_id, 'description', true ); ?></textarea>
									</div>
								</div>
							</div>
							<div class="tab-pane" id="profileSecurity">
								<div class="form-group row mb-15">
									<div class="col-md-3">
										<label for="current_pass"><?php _e( 'Current Password', 'sb-core' ); ?></label>
									</div>
									<div class="col-md-9">
										<input id="current_pass" type="password" class="password textfield form-control"
										       data-required=""
										       data-type="text" name="user_pass" value=""
										       autocomplete="off">
									</div>
								</div>
								<div class="form-group row mb-15">
									<div class="col-md-3">
										<label for="user_pass"><?php _e( 'New Password', 'sb-core' ); ?></label>
									</div>
									<div class="col-md-9">
										<input id="user_pass" type="password" class="password textfield form-control"
										       data-required=""
										       data-type="text" name="user_pass" value=""
										       autocomplete="current-password">
									</div>
								</div>
								<div class="form-group row mb-15">
									<div class="col-md-3">
										<label for="pass2"><?php _e( 'Confirm Password', 'sb-core' ); ?></label>
									</div>
									<div class="col-md-9">
										<input id="pass2" type="password" class="password textfield form-control"
										       data-required=""
										       data-type="text" name="pass2" value="" autocomplete="current-password">
									</div>
								</div>
							</div>
							<div class="tab-pane" id="profileContact">
								<?php
								if ( HT()->array_has_value( $contacts ) ) {
									foreach ( $contacts as $name => $label ) {
										$value = get_user_meta( $user_id, $name, true );
										?>
										<div class="form-group row mb-15">
											<div class="col-md-3">
												<label for="contact<?php echo $name; ?>"><?php echo $label; ?></label>
											</div>
											<div class="col-md-9">
												<input id="contact<?php echo $name; ?>" type="text"
												       class="form-control <?php echo sanitize_html_class( $name ); ?>"
												       name="contact_<?php echo $name; ?>"
												       value="<?php echo esc_attr( maybe_serialize( $value ) ); ?>"
												       autocomplete="off">
											</div>
										</div>
										<?php
									}
								}
								?>
							</div>
							<?php do_action( 'hocwp_theme_extension_account_profile_tab_content' ); ?>
						</div>
						<div class="form-group row mb-15">
							<div class="col-md-9 col-md-offset-3 offset-md-3">
								<button type="submit" class="btn btn-danger"
								        name="submit"><?php _e( 'Save Changes', 'sb-core' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>