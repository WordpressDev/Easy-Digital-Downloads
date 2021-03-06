<?php
/**
 * Scripts
 *
 * @package     Easy Digital Downloads
 * @subpackage  Scripts
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_load_scripts() {

	global $edd_options, $post;

	wp_enqueue_script( 'jquery' );

	// Get position in cart of current download
	if( isset( $post->ID ) ) {
	    $position = edd_get_item_position_in_cart( $post->ID );
	}

	// Load AJAX scripts, if enabled
	if( edd_is_ajax_enabled() ) {
		wp_enqueue_script( 'edd-ajax', EDD_PLUGIN_URL . 'assets/js/edd-ajax.js', array( 'jquery' ), EDD_VERSION );
		wp_localize_script( 'edd-ajax', 'edd_scripts', array(
				'ajaxurl' 					=> edd_get_ajax_url(),
				'ajax_nonce' 				=> wp_create_nonce( 'edd_ajax_nonce' ),
				'position_in_cart' 			=> isset( $position ) ? $position : -1,
				'already_in_cart_message' 	=> __('You have already added this item to your cart', 'edd'), // Item already in the cart message
				'empty_cart_message' 		=> __('Your cart is empty', 'edd'), // Item already in the cart message
				'loading' 					=> __('Loading', 'edd') , // General loading message
				'ajax_loader' 				=> EDD_PLUGIN_URL . 'assets/images/loading.gif', // Ajax loading image
				'redirect_to_checkout'      => edd_straight_to_checkout() ? '1' : '0',
				'checkout_page' 			=> edd_get_checkout_uri(),
				'permalinks' 				=> get_option( 'permalink_structure' ) ? '1' : '0'
			)
		);
	}

	// Load jQuery validation
	if( isset( $edd_options['jquery_validation'] ) && edd_is_checkout() ) {
		wp_enqueue_script( 'jquery-validation', EDD_PLUGIN_URL . 'assets/js/jquery.validate.min.js' );
		wp_enqueue_script( 'edd-validation', EDD_PLUGIN_URL . 'assets/js/form-validation.js', array( 'jquery', 'jquery-validation' ), EDD_VERSION );
		$required = array( 'firstname' => true, 'lastname' => true );
		wp_localize_script( 'edd-validation', 'edd_scripts_validation', apply_filters( 'edd_scripts_validation', $required ) );
	}
	if( edd_is_checkout() ) {

		if( edd_is_cc_verify_enabled() ) {
			wp_enqueue_script( 'creditCardValidator', EDD_PLUGIN_URL . 'assets/js/jquery.creditCardValidator.js', array( 'jquery' ), EDD_VERSION );
		}
		wp_enqueue_script( 'edd-checkout-global', EDD_PLUGIN_URL . 'assets/js/edd-checkout-global.js', array( 'jquery' ), EDD_VERSION );
		wp_localize_script( 'edd-checkout-global', 'edd_global_vars', array(
			'ajaxurl'           => edd_get_ajax_url(),
			'checkout_nonce'    => wp_create_nonce( 'edd_checkout_nonce' ),
	        'currency_sign'		=> edd_currency_filter(''),
	        'currency_pos'		=> isset( $edd_options['currency_position'] ) ? $edd_options['currency_position'] : 'before',
	        'no_gateway'		=> __( 'Please select a payment method', 'edd' ),
	        'no_discount'       => __('Please enter a discount code', 'edd'), // Blank discount code message
			'discount_applied'  => __('Discount Applied', 'edd'), // Discount verified message
			'no_email'          => __('Please enter an email address before applying a discount code', 'edd'),
			'no_username'       => __('Please enter a username before applying a discount code', 'edd'),
	    ));
	}
}
add_action( 'wp_enqueue_scripts', 'edd_load_scripts' );


/**
 * Register Styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_register_styles() {
	global $edd_options;

	if( isset( $edd_options['disable_styles'] ) )
		return;

	$file = 'edd.css';

	if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'edd_templates/' . $file ) ) {
		$url = trailingslashit( get_stylesheet_directory_uri() ) . 'edd_templates/' . $file;
	} elseif ( file_exists( trailingslashit( get_template_directory() ) . 'edd_templates/' . $file ) ) {
		$url = trailingslashit( get_template_directory_uri() ) . 'edd_templates/' . $file;
	} elseif ( file_exists( trailingslashit( edd_get_templates_dir() ) . $file ) ) {
		$url = trailingslashit( edd_get_templates_url() ) . $file;
	}

	wp_enqueue_style( 'edd-styles', $url, EDD_VERSION );
}
add_action( 'wp_enqueue_scripts', 'edd_register_styles' );


/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_load_admin_scripts( $hook ) {
	global $post, $pagenow, $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_system_info_page, $edd_add_ons_page, $edd_options, $edd_upgrades_screen;

	$edd_pages = array( $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_system_info_page, $edd_add_ons_page, $edd_upgrades_screen, 'index.php' );
	$edd_cpt   = apply_filters( 'edd_load_scripts_for_these_types', array( 'download', 'edd_payment' ) );

	if ( !in_array( $hook, $edd_pages ) && !is_object( $post ) )
	    return;

	if ( is_object( $post ) && !in_array( $post->post_type, $edd_cpt ) )
	    return;

	if( $hook == 'download_page_edd-reports' ) {
		wp_enqueue_script( 'jquery-flot', EDD_PLUGIN_URL . 'assets/js/jquery.flot.js' );
	}
	if( $hook == 'download_page_edd-discounts' ) {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
		wp_enqueue_style( 'jquery-ui-css', EDD_PLUGIN_URL . 'assets/css/jquery-ui-' . $ui_style . '.css' );
	}
	if( $hook == $edd_settings_page ) {
		wp_enqueue_style( 'colorbox', EDD_PLUGIN_URL . 'assets/css/colorbox.css', array(  ), '1.3.20' );
		wp_enqueue_script( 'colorbox', EDD_PLUGIN_URL . 'assets/js/jquery.colorbox-min.js', array( 'jquery' ), '1.3.20');
	}
	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_script( 'edd-admin-scripts', EDD_PLUGIN_URL . 'assets/js/admin-scripts.js', array( 'jquery' ), EDD_VERSION, false );
	wp_localize_script( 'edd-admin-scripts', 'edd_vars', array(
        'post_id' 			=> isset( $post->ID ) ? $post->ID : null,
        'add_new_download' 	=> __( 'Add New Download', 'edd' ), 									// Thickbox title
        'use_this_file' 	=> __( 'Use This File','edd' ), 										// "use this file" button
        'quick_edit_warning'=> __( 'Sorry, not available for variable priced products.', 'edd' ),
        'delete_payment' 	=> __( 'Are you sure you wish to delete this payment?', 'edd' ),
        'one_price_min' 	=> __( 'You must have at least one price', 'edd' ),
        'one_file_min' 		=> __( 'You must have at least one file', 'edd' ),
        'one_field_min'		=> __( 'You must have at least one field', 'edd' ),
        'currency_sign'		=> edd_currency_filter(''),
        'currency_pos'		=> isset( $edd_options['currency_position'] ) ? $edd_options['currency_position'] : 'before'
    ));
	wp_enqueue_style( 'thickbox' );

	wp_enqueue_style( 'edd-admin', EDD_PLUGIN_URL . 'assets/css/edd-admin.css', EDD_VERSION );
}
add_action( 'admin_enqueue_scripts', 'edd_load_admin_scripts', 100 );


/**
 * Admin Downloads Icon
 *
 * Echoes the CSS for the downloads post type icon.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_admin_downloads_icon() {
    global $post_type;
	$icon_url = EDD_PLUGIN_URL . 'assets/images/edd-icon.png';
	?>
	<style type="text/css" media="screen">
		body #adminmenu #menu-posts-download div.wp-menu-image { background:transparent url( "<?php echo $icon_url; ?>" ) no-repeat 7px -32px; }
		body #adminmenu #menu-posts-download:hover div.wp-menu-image,
		body #adminmenu #menu-posts-download.wp-has-current-submenu div.wp-menu-image { background:transparent url( "<?php echo $icon_url; ?>" ) no-repeat 7px 0; }
		<?php if ( ( isset( $_GET['post_type'] ) ) && ( $_GET['post_type'] == 'download' ) || ( $post_type == 'download' ) ) : ?>
        #icon-edit { background:transparent url("<?php echo EDD_PLUGIN_URL .'assets/images/edd-cpt.png'; ?>") no-repeat; }
        <?php endif; ?>
        @media
        only screen and (-webkit-min-device-pixel-ratio: 1.5),
        only screen and (   min--moz-device-pixel-ratio: 1.5),
        only screen and (     -o-min-device-pixel-ratio: 3/2),
        only screen and (        min-device-pixel-ratio: 1.5),
        only screen and (        		 min-resolution: 1.5dppx) {
        	/* Admin Menu - 16px @2x */
        	body #adminmenu #menu-posts-download div.wp-menu-image {
        		background: transparent url('<?php echo EDD_PLUGIN_URL; ?>assets/images/edd-icon-2x.png') no-repeat 7px -20px!important;
        		background-size: 16px 48px !important;
        	}

        	body #adminmenu #menu-posts-download:hover div.wp-menu-image,
        	body #adminmenu #menu-posts-download.wp-menu-open div.wp-menu-image {
        		background-position: 7px 4px!important;
        	}

        	/* Post Screen - 32px @2x */
        	.icon32-posts-download {
        		background: url('<?php echo EDD_PLUGIN_URL; ?>assets/images/edd-cpt-2x.png') no-repeat 0 0!important;
        		background-size: 32px 32px !important;
        	}
        }
        #edd-media-button { -webkit-background-size: 16px; -moz-background-size: 16px; background-size: 16px; background-image: url(<?php echo EDD_PLUGIN_URL; ?>assets/images/edd-cpt-2x.png); margin-top: -1px; }
	</style>
    <?php
}
add_action( 'admin_head','edd_admin_downloads_icon' );

/**
 * EDD Version in Header
 *
 * Adds Version to header
 *
 * @access      private
 * @since       1.4.2
 * @return      void
*/

function edd_version_in_header(){
	// Newline on both sides to avoid being in a blob
	echo '<meta name="generator" content="Easy Digital Downloads v'.EDD_VERSION.'" />'."\n";
}
add_action('wp_head', 'edd_version_in_header');
