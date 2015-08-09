<?php
/**
 * Plugin Name: Google noCAPTCHA reCAPTCHA for WordPress
 * Plugin URI: http://ashmatadeen.com
 * Description: Adds Google's reCAPTCHA to WP's login form
 * Author: Ash Matadeen
 * Author URI: http://ashmatadeen.com
 * Version: 1.2
 */

add_action( 'admin_menu', 'wr_no_captcha_menu' );
add_action( 'admin_init', 'wr_no_captcha_display_options' );
add_action( 'login_enqueue_scripts', 'wr_no_captcha_login_form_script' );
add_action( 'login_enqueue_scripts', 'wr_no_captcha_css' );
add_action( 'login_form', 'wr_no_captcha_render_login_captcha' );
add_filter( 'wp_authenticate_user', 'wr_no_captcha_verify_login_captcha', 10, 2 );

function wr_no_captcha_menu() {
	add_options_page( 'Google reCAPTCHA options', 'reCAPTCHA options', 'manage_options', 'recaptcha-options', 'wr_no_captcha_options_page' );
}

function wr_no_captcha_options_page() {
	?>
		<h2>Google noCAPTCHA reCAPTCHA for WordPress</h2>

		<div class="wrap">

			<div id="icon-options-general" class="icon32"></div>

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-2">

					<!-- main content -->
					<div id="post-body-content">

						<div class="meta-box-sortables ui-sortable">

							<div class="postbox">
								<div class="inside">
									<form method="post" action="options.php">
										<?php
											settings_fields( 'keys_section' );
											do_settings_sections( 'recaptcha-options' );
											submit_button();
										?>
									</form>
								</div>

							</div>
							<!-- .postbox -->

						</div>
						<!-- .meta-box-sortables .ui-sortable -->

					</div>
					<!-- post-body-content -->

					<!-- sidebar -->
					<div id="postbox-container-1" class="postbox-container">

						<div class="meta-box-sortables">

							<div class="postbox">

								<h3>Lorem ipsum</h3>

								<div class="inside">
									<p>Lorem ipsum dolor sit amet</p>
								</div>
								<!-- .inside -->

							</div>
							<!-- .postbox -->

						</div>
						<!-- .meta-box-sortables -->

					</div>
					<!-- #postbox-container-1 .postbox-container -->

				</div>
				<!-- #post-body .metabox-holder .columns-2 -->

				<br class="clear">
			</div>
			<!-- #poststuff -->

		</div> <!-- .wrap -->
	<?php
}

function wr_no_captcha_display_options() {
	add_settings_section( 'keys_section', 'API Credentials', 'wr_no_captcha_display_recaptcha_content', 'recaptcha-options' );

	add_settings_field( 'wr_no_captcha_site_key', 'Site key', 'wr_no_captcha_key_input', 'recaptcha-options', 'keys_section' );
	add_settings_field( 'wr_no_captcha_secret_key', 'Secret Key', 'wr_no_captcha_secret_key_input', 'recaptcha-options', 'keys_section' );

	register_setting( 'keys_section', 'wr_no_captcha_site_key' );
	register_setting( 'keys_section', 'wr_no_captcha_secret_key' );
}

function wr_no_captcha_display_recaptcha_content() {
	echo '<p>Please <a href="https://www.google.com/recaptcha/admin">register you domain</a> with Google to obtain the API keys and enter them below.</p>';
}

function wr_no_captcha_key_input() {
	echo '<input type="text" name="wr_no_captcha_site_key" id="captcha_site_key" value="'. get_option( 'wr_no_captcha_site_key' ) . '" />';
}

function wr_no_captcha_secret_key_input() {
	echo '<input type="text" name="wr_no_captcha_secret_key" id="captcha_secret_key" value="' . get_option( 'wr_no_captcha_secret_key' ) . '" />';
}

function wr_no_captcha_login_form_script() {
	wp_register_script( 'no_captcha_login', 'https://www.google.com/recaptcha/api.js' );
	wp_enqueue_script( 'no_captcha_login' );
}

function wr_no_captcha_render_login_captcha() {
	echo '<div class="g-recaptcha" data-sitekey="' . get_option( 'wr_no_captcha_site_key' ) . '"></div>';
	require_once( plugin_dir_path( __FILE__ ) . 'noscript/noscript.php');
}

function wr_no_captcha_verify_login_captcha($user, $password) {
	if ( isset( $_POST['g-recaptcha-response'] ) ) {
		$no_captcha_secret = get_option( 'wr_no_captcha_secret_key' );
		$response = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $no_captcha_secret . '&response=' . $_POST['g-recaptcha-response'] );
		$response = json_decode( $response['body'], true );
		if ( true === $response['success'] ) {
			return $user;
		} else {
			return new WP_Error( 'Captcha Invalid', __( '<strong>Robot test error</strong>: I suggest a new strategy, R2, let the Wookie win.' ) );
		}
	}
}

function wr_no_captcha_css() {
	$src = plugins_url( 'css/no-captcha.css', __FILE__ );
	wp_enqueue_style( 'no_captcha_css',  $src );
}
