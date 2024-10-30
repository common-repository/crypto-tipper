<?php
/*
Plugin Name: Crypto Tipper
Plugin URI: https://vegasgeek.com/crypto-tipper/
Description: Allow your authors to request tips via crypto currency on their posts
Author: John Hawkins
Version: 1.0
Author URI: https://vegasgeek.com/
Text Domain: vgs
*/

/**
 * Add Crypto fields to user profile
 */
function vgs_add_crypto_fields( $user ) {

	// See if the author has already saved a crypto currency
	if( get_the_author_meta( 'crypto_type', $user->ID ) ) {
		$users_crypto_type = esc_html( get_the_author_meta( 'crypto_type', $user->ID ) );
	}

	// See if the author has already saved crypto text
	if( get_the_author_meta( 'crypto_text', $user->ID ) ) {
		$users_crypto_text = esc_html( get_the_author_meta( 'crypto_text', $user->ID ) );
	} else {
		$users_crypto_text = __( 'Did you find this article helpful? If so, consider sending a tip via Bitcoin.', 'vgs' );
	}

	?>
	<h3><?php esc_html_e( 'Crypto Tipper', 'crf' ); ?></h3>
	<p><i><?php _e( 'Select the crypto currency to use to request a tips on posts you write on this website.', 'vgs' ); ?></i></p>

	<table class="form-table">
		<tr class="user-crypto-type-wrap">
			<th><label for="crypto_type"><?php esc_html_e( 'Select A Currency', 'crf' ); ?></label></th>
			<td>
				<select name="crypto_type">
					<option value=""><?php _e( 'Please Select', 'vgs' ); ?></option>
					<option value="btc" <?php if( isset( $users_crypto_type ) && $users_crypto_type === 'btc' ) { echo 'SELECTED'; } ?> ><?php _e( 'Bitcoin', 'vgs' ); ?></option>
					<option value="bch" <?php if( isset( $users_crypto_type ) && $users_crypto_type === 'bch' ) { echo 'SELECTED'; } ?> ><?php _e( 'Bitcoin Cash', 'vgs' ); ?></option>
					<option value="ada" <?php if( isset( $users_crypto_type ) && $users_crypto_type === 'ada' ) { echo 'SELECTED'; } ?> ><?php _e( 'Cardano', 'vgs' ); ?></option>
					<option value="eos" <?php if( isset( $users_crypto_type ) && $users_crypto_type === 'eos' ) { echo 'SELECTED'; } ?> ><?php _e( 'EOS', 'vgs' ); ?></option>
					<option value="eth" <?php if( isset( $users_crypto_type ) && $users_crypto_type === 'eth' ) { echo 'SELECTED'; } ?> ><?php _e( 'Ethereum', 'vgs' ); ?></option>
					<option value="ltc" <?php if( isset( $users_crypto_type ) && $users_crypto_type === 'ltc' ) { echo 'SELECTED'; } ?> ><?php _e( 'Litecoin', 'vgs' ); ?></option>
					<option value="xmr" <?php if( isset( $users_crypto_type ) && $users_crypto_type === 'xmr' ) { echo 'SELECTED'; } ?> ><?php _e( 'Monero', 'vgs' ); ?></option>
					<option value="neo" <?php if( isset( $users_crypto_type ) && $users_crypto_type === 'neo' ) { echo 'SELECTED'; } ?> ><?php _e( 'NEO', 'vgs' ); ?></option>
					<option value="xrp" <?php if( isset( $users_crypto_type ) && $users_crypto_type === 'xrp' ) { echo 'SELECTED'; } ?> ><?php _e( 'Ripple', 'vgs' ); ?></option>
					<option value="xlm" <?php if( isset( $users_crypto_type ) && $users_crypto_type === 'xlm' ) { echo 'SELECTED'; } ?> ><?php _e( 'Stellar', 'vgs' ); ?></option>
				</select>
			</td>
		</tr>
		<tr class="user-crypto-id-wrap">
			<th><label for "crypto_id"><?php esc_html_e( 'Your Currency Address', 'crf' ); ?></label></th>
			<td><input type="text" class="regular-text" name="crypto_id" value="<?php esc_html_e( get_the_author_meta( 'crypto_id', $user->ID ) ); ?>" placeholder="<?php _e( 'ex: 1KpxKG7J7dBguspq9W5nPk876U8VbUiiCp', 'vgs' ); ?>"></td>
		</tr>

		<tr class="user-crypto-text-wrap">
			<th><label for "crypto_text"><?php esc_html_e( 'Text to Display', 'crf' ); ?></label></th>
			<td><textarea name="crypto_text"><?php esc_html_e( $users_crypto_text ); ?></textarea>
			</td>
		</tr>
	</table>
	<?php
	// add nonce field to form
	wp_nonce_field( 'add-crypto-currency', 'crypto-nonce' );
}
add_action( 'show_user_profile', 'vgs_add_crypto_fields' );
add_action( 'edit_user_profile', 'vgs_add_crypto_fields' );

/**
 * Save Crypto fields to user profile
 */
function vgs_save_crypto_fields( $user_id ) {

	// Check nonce field
	if ( isset( $_POST[ 'crypto-nonce' ] ) && wp_verify_nonce( $_POST[ 'crypto-nonce' ], 'add-crypto-currency' ) ) {

		// make sure user has proper privileges 
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( ! empty( $_POST['crypto_type'] ) ) {
			update_user_meta( $user_id, 'crypto_type', sanitize_text_field( $_POST["crypto_type"] ) );
		}

		if ( ! empty( $_POST['crypto_id'] ) ) {
			update_user_meta( $user_id, 'crypto_id', sanitize_text_field( $_POST["crypto_id"] ) );
		}

		if ( ! empty( $_POST['crypto_text'] ) ) {
			update_user_meta( $user_id, 'crypto_text', sanitize_textarea_field( $_POST["crypto_text"] ) );
		}
	}
}
add_action( 'personal_options_update', 'vgs_save_crypto_fields' );
add_action( 'edit_user_profile_update', 'vgs_save_crypto_fields' );

/**
 * Display the author's crypto info at bottom of their posts
 */
function vgs_display_crypto_box( $content ) {

	// Only display on single blog post
	if( is_singular( 'post' ) ) {

		// confirm the post author has added crypto info.
		// if not, bail
		if( strlen( ! trim( get_the_author_meta( 'crypto_type', get_the_author_meta( 'ID' ) ) ) ) ) {
			return;
		}

		// if so, create our html block
		$html = '<div class="crypto-block">';
			$html .= '<img src="' . plugin_dir_url( __FILE__ ) . 'logos/' . esc_html( get_the_author_meta( 'crypto_type', get_the_author_meta( 'ID' ) ) ) . '.png' . '" class="crypto-icon">';
			$html .= '<p class="crypto-text">' . esc_html( get_the_author_meta( 'crypto_text', get_the_author_meta( 'ID' ) ) ) . '</p>';
			$html .= '<p class="crypto-id">' . esc_html( get_the_author_meta( 'crypto_id', get_the_author_meta( 'ID' ) ) ) . '</p>';
		$html .= '</div>';
	}

    // Display the content and our block 
    return $content . $html;

}

add_filter( 'the_content', 'vgs_display_crypto_box', 20 );

/**
 * Enqueue our styles
 */
function vgs_enqueue_styles() {
    wp_enqueue_style( 'crypto-tipper', plugin_dir_url( __FILE__ ) . 'crypto-tipper.css' );
}
add_action( 'wp_enqueue_scripts', 'vgs_enqueue_styles' );