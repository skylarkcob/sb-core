<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( is_user_logged_in() ) {
	$url = get_edit_profile_url();
	?>
	<p class="alert alert-warning"><?php _e( 'You are logged in.', 'sb-core' ); ?></p>
	<script>
		window.location.href = "<?php echo $url; ?>";
	</script>
	<?php
	return;
}

$permalink = get_the_permalink();
?>
<div id="loginFormContainer" class="account-login">
	<form id="loginForm" class="login-form maxw-600 mx-auto clearfix border rounded pd-20" action="" method="post">
		<?php
		if ( isset( $_POST['action'] ) && isset( $_POST['error'] ) ) {
			$error = $_POST['error'];

			if ( is_array( $error ) ) {
				foreach ( $error as $err ) {
					?>
					<p class="alert alert-error alert-danger"><?php echo $err; ?></p>
					<?php
				}
			} else {
				?>
				<p class="alert alert-error alert-danger"><?php echo $error; ?></p>
				<?php
			}
		}

		$class     = 'col-md-5';
		$sub_class = 'col-md-7';
		?>
		<div class="form-group row mb-15">
			<div class="<?php echo $class; ?>">
				<label for="user_login"><?php _e( 'Username or Email', 'sb-core' ); ?></label>
			</div>
			<div class="<?php echo $sub_class; ?>">
				<input name="user_login" id="user_login" class="required input form-control" type="text"
				       autocomplete="username">
			</div>
		</div>
		<div class="form-group row mb-15">
			<div class="<?php echo $class; ?>">
				<label for="user_pass"><?php _e( 'Password', 'sb-core' ); ?></label>
			</div>
			<div class="<?php echo $sub_class; ?>">
				<input name="user_pass" id="user_pass" class="password required input form-control" type="password"
				       autocomplete="current-password">
			</div>
		</div>
		<?php $class = 'col-md-7 col-md-offset-5 offset-md-5'; ?>
		<div class="form-group row">
			<div class="<?php echo $class; ?>">
				<label><input name="rememberme" type="checkbox" id="rememberme"
				              value="forever"> <?php _e( 'Remember Me', 'sb-core' ); ?></label>
			</div>
		</div>
		<div class="form-group row">
			<div class="<?php echo $class; ?>">
				<div class="mb-20">
					<?php do_action( 'login_form' ); ?>
				</div>
				<?php $redirect_to = $_REQUEST['redirect_to'] ?? ''; ?>
				<input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>">
				<?php wp_nonce_field(); ?>
				<input type="hidden" name="action" value="user_login">
				<button id="login_submit" type="submit"
				        class="submit btn btn-success btn-block d-block w-full"><?php _e( 'Log In', 'sb-core' ); ?></button>
			</div>
		</div>
		<div class="form-group row mt-15">
			<div class="<?php echo $class; ?>">
				<a href="<?php echo wp_lostpassword_url(); ?>"><?php _e( 'Lost Password?', 'sb-core' ); ?></a>
				<?php
				if ( 1 == get_option( 'users_can_register' ) ) {
					?>
					<span><?php _e( 'Or', 'sb-core' ); ?></span>
					<a href="<?php echo wp_registration_url(); ?>"><?php _e( 'Register', 'sb-core' ); ?></a>
					<?php
				}
				?>
			</div>
		</div>
		<?php unset( $url, $permalink, $err, $error, $class, $sub_class, $redirect_to ); ?>
	</form>
</div>