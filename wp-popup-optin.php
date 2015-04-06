<?php
/**
 * @package wpPopupOptin
 */
/*
Plugin Name: WP Popup Optin
Plugin URI: http://www.logicbaseinteractive.com/wp-popup-optin/
Description: WP Popup + Optin is a customizable Wordpress popup plugin with an optin form to help you build your list of subscribers! Using this plugin is the best way to build your list as popup in unblockable and it comes with a few great looking theme colors for you to choose from to save you a lot of time. 
Version: 1.1
Author: Logicbase Interactive
Author URI: http://logicbaseinteractive.com/
License: GPLv2 or later
Text Domain: wppopupoptin
*/	

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/	

class wpPopupOptin {

	private $wpo_name = 'wp-popup-optin';
	private $wpo_cookie_name = 'wpo_popup_status';
	private $wpo_cookie_val = '';
	private $wpo_for_js = '';

	public function __construct() {
		//add_filter( 'the_content', array( $this, 'the_content' ) );	

		add_action( 'wp_enqueue_scripts', array( $this, 'wpo_front_scripts_head' ) );
		add_action( 'init', array( $this, 'wpo_init' ) );
		add_action( 'init', array( $this, 'wpo_setcookie' ) );
		add_action( 'wp_head', array( $this, 'wpo_getcookie' ) );
		add_action( 'admin_menu', array( $this, 'wpo_admin_actions' ) );
		add_action( 'wp_head', array( $this, 'wpo_front_styles' ) );
		
		add_action( 'wp_footer', array( $this, 'wpo_front_scripts' ) );
		add_action( 'wp_footer', array( $this, 'wpo_show_content' ) );		
		add_action( 'admin_print_scripts', array( $this, 'wpo_admin_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'wpo_admin_styles' ) );
		add_action('admin_head', array( $this, 'wpo_custom_fonts' ) );

		add_action( 'wp_footer', array( $this,'wpo_formprocess_js' ) );
		add_action( 'wp_ajax_wpo_formprocess', array( $this,'wpo_formprocess_func' ) );

		//add_shortcode('show_cf_how_to_buy_form', 'chel_how_to_buy_form');  
		register_activation_hook(__FILE__,  array( $this, 'wpo_plugin_activate' ) );		

	}

	function wpo_custom_fonts() {
		
	}

	function wpo_plugin_activate() {
		global $wpdb;
		$db_prefix = $wpdb->prefix; 

	   $sql = "CREATE TABLE `".$db_prefix."wpo_email_list` (
								  `wpo_email_id` int(5) NOT NULL AUTO_INCREMENT,
								  `wpo_email` varchar(150) NOT NULL,
								  `wpo_optin_date` datetime NOT NULL,
								  PRIMARY KEY (`wpo_email_id`)
	                            ) ENGINE = MyISAM;";
	   $this->wpo_create_table($db_prefix.'wpo_email_list', $sql);		

	}

	function wpo_check_user_permission() {
		if(current_user_can('manage_options') || current_user_can('edit_posts'))
			return true;
		else 
			return false;
	}

	function wpo_create_table($table_name, $sql) {
		global $wpdb;
	    $db_prefix = $wpdb->prefix; 
		if($wpdb->get_var("show tables like '". $table_name . "'") != $table_name) {
			$wpdb->query($sql);
	   }
	}

	function wpo_admin() {
		global $wpdb;
		$db_prefix = $wpdb->prefix; 
		if(!current_user_can('manage_options') || !current_user_can('edit_posts')) {
			echo '<div id="message" class="error">'. __("You don't have permissions to use this plugin","wpo") .' </div>';
		} else {

			if(function_exists( 'wp_enqueue_media' )){
			    wp_enqueue_media();
			}else{
			    wp_enqueue_style('thickbox');
			    wp_enqueue_script('media-upload');
			    wp_enqueue_script('thickbox');
			}

	    	include('wpo_admin_page.php');        
		}
	}

	function wpo_email_list() {
		if(!current_user_can('manage_options') || !current_user_can('edit_posts')) {
			echo '<div id="message" class="error">'. __("You don't have permissions to use this plugin","wpo") .' </div>';
		} else {
	    	include('wpo_email_list.php');
		}		
	}

	function wpo_process_popup() {

	}

	function wpo_get_cookie_name() {
		$get_c_name = get_option('wpo_cookie_name','wpo_popup_status');
		return $get_c_name;
	}

	function wpo_get_cookie_duration() {
		$wpo_show_every = get_option('wpo_show_every','');
		if( $wpo_show_every == '' || !is_numeric($wpo_show_every) )
			$wpo_show_every = 200;
		return $wpo_show_every;
	}

	function wpo_RandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}	

	
	function wpo_getcookie() {
		$get_c_name = $this->wpo_get_cookie_name();
	   $cookie_val = isset( $_COOKIE[$get_c_name] ) ? $_COOKIE[$get_c_name] : '';
	   $this->wpo_cookie_val = $cookie_val;
	}	

	function wpo_setcookie() {
		$get_c_name = $this->wpo_get_cookie_name();
		$get_duration = $this->wpo_get_cookie_duration();
		$wpo_popup_status = get_option('wpo_popup_status');
		if ( !is_admin() && !isset($_COOKIE[$get_c_name]) ) {

			$this->wpo_for_js = "
			<script>
			jQuery(document).ready(function($) {

				setTimeout(
				  function() 
				  {
				    $('.wpo_popup_link').trigger('click');
				  }, 3000);

				
			});		
			</script>	
			";

			if($wpo_popup_status == '1')
	   			setcookie( $get_c_name, '1', time()+ ( $get_duration * 86400 ), COOKIEPATH, COOKIE_DOMAIN );
		} else {
			if(isset($_POST['wpo_clear_cookie'])) {
				if($_POST['wpo_clear_cookie'] == 'yes') {
					setcookie( $get_c_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
					$rand_text = 'wpo_' . $this->wpo_RandomString();
					update_option('wpo_cookie_name',$rand_text);
				}
			}
		}
	}	

	function wpo_show_content() { 
			$wpo_title = stripslashes(stripslashes(get_option('wpo_title','')));
			$wpo_image_url = get_option('wpo_image_url','');
			$wpo_text = stripslashes(stripslashes(get_option('wpo_text','')));
			$wpo_show_every = get_option('wpo_show_every','');
			if( $wpo_show_every == '' || !is_numeric($wpo_show_every) )
				$wpo_show_every = 200;			
			$wpo_content_type = get_option('wpo_content_type');
			$wpo_theme_color = get_option('wpo_theme_color');
			$wpo_popup_status = get_option('wpo_popup_status');
			$wpo_popup_layout = get_option('wpo_popup_layout');
			$wpo_custom_html = get_option('wpo_custom_html');
			$wpo_submit_text = get_option('wpo_submit_text');		


		?>
			<div style="display: none;">
				<div id="wpo_popup" <?php if($wpo_content_type != 'custom_html') { echo 'class="cleanslate"'; } ?>>
					<div class="<?php echo 'wpo_' . $wpo_theme_color; ?> <?php echo 'wpo_' . $wpo_popup_layout; ?>">
						<?php if($wpo_content_type == 'custom_html') { ?>
							<div class="wpo_popup_custom"><?php echo do_shortcode(stripslashes(wpautop($wpo_custom_html))); ?></div>
						<?php } else { ?>
							<div class="wpo_popup_top">
								<div class="wpo_popup_img"><img src="<?php echo $wpo_image_url; ?>" alt=" " /></div>
								<div class="wpo_popup_content">
									<h3><?php echo $wpo_title; ?></h3>
									<p><?php echo $wpo_text; ?></p>
								</div>
								<div class="wpo_popup_clear"></div>
							</div> <!-- //wpo_popup_top -->
							<div class="wpo_popup_bottom">
								<div class="wpo_popup_form">
									<div class="wpo_popup_success_msg">Thank you for signing up!</div>
									<div class="wpo_popup_error_msg"></div>
									<div class="wpo_popup_clear"></div>
									<form method="POST">
									<div class="wpo_input_cont"><input type="text" name="wpo_email" placeholder="Enter your email address" id="wpo_input_email_id" /></div>
									<div class="wpo_input_submit"><input type="submit" id="wpo_submit_button" value="<?php echo $wpo_submit_text; ?>" /></div>
									</form>
									<div class="wpo_popup_clear"></div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<a href="#wpo_popup" class="wp_popup_me wpo_popup_link"></a>
	<?php if($wpo_popup_status == '1') { echo $this->wpo_for_js; }
	}

	function wpo_formprocess_js() { ?>
		<script type="text/javascript" >
		jQuery(document).ready(function($) {

			$('#wpo_submit_button').click(function(e) {
				e.preventDefault();
//				if(!$(this).hasClass('is_clicked')) {
					$(this).addClass('is_clicked');
					var data = {
						'action': 'wpo_formprocess',
						'wpo_email': $('#wpo_input_email_id').val()
					};

					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
					$.post(ajaxurl, data, function(response) {
						//alert('Got this from the server: ' + response);

						$(this).removeClass('is_clicked');

						if(response == 'true') {
							$('.wpo_popup_form form').addClass('wpo_popup_hide');
							$('.wpo_popup_success_msg').addClass('wpo_popup_success_msg_show');
							$('.wpo_popup_error_msg').html('');
							$('#wpo_input_email_id').val('');
						} else if(response == 'invalid email') {
							$('.wpo_popup_error_msg').html('<p>Sorry, invalid email')
						} else if(response == 'email exists') {
							$('.wpo_popup_error_msg').html('<p>Sorry, email is already in our list')
						}
					});				
//				}
			});

		});
		</script><?php
	}	

	function wpo_formprocess_func() {
		global $wpdb;

		$get_email = trim(sanitize_text_field($_POST['wpo_email']));
		$today_date = date('Y-m-d H:i:s');

		if($get_email != '') {
			$check_exist = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wpo_email_list WHERE wpo_email='" . $get_email . "'");

			if (!filter_var($get_email, FILTER_VALIDATE_EMAIL)) {
				echo 'invalid email';
			} elseif(count($check_exist) > 0) {
				echo 'email exists';
			} else {
				$insert_query = "INSERT INTO " . $wpdb->prefix . "wpo_email_list (wpo_email, wpo_optin_date) VALUES ('" . $get_email . "','" . $today_date . "')";
				if($wpdb->query($insert_query)) {
					echo 'true';
				} else {
					echo 'false';
				}
			}
		} else {
			echo 'false';
		}

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	function wpo_admin_actions() {
		if($this->wpo_check_user_permission()) {
			add_menu_page( 'WP Popup+Optin', 'WP Popup+Optin', 'manage_options', 'WPPopupOptin', array( $this, 'wpo_admin' ) );
			add_submenu_page( 'WPPopupOptin', 'Settings', 'Settings', 'manage_options', 'wpo_manage_settings', array( $this, 'wpo_admin' ) );
			add_submenu_page( 'WPPopupOptin', 'Email List', 'Email List', 'manage_options', 'wpo_email_list', array( $this, 'wpo_email_list' ) );
		}
	}


	function wpo_init() {
	}

	function wpo_front_scripts_head() {
		wp_enqueue_script("wpo-fancybox", plugins_url( "fancybox/jquery.fancybox-1.3.4.js" , __FILE__ ), array('jquery'), '1.0.0', false );
	}

	function wpo_front_scripts() {
		
		wp_enqueue_script("wpo-scripts", plugins_url( "js/wpo-scripts.js" , __FILE__ ), array('jquery') );
	}

	function wpo_front_styles() {
		wp_enqueue_style("wpo-fonts-ss", "http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700,400italic,700italic");
		wp_enqueue_style("wpo-font-oswald", "http://fonts.googleapis.com/css?family=Oswald:400,700");
		wp_enqueue_style("wpo-fancybox-css", plugins_url( "fancybox/jquery.fancybox-1.3.4.css" , __FILE__ ) );
		wp_enqueue_style("wpo-cleanslate", plugins_url( "css/cleanslate.css" , __FILE__ ) );
		wp_enqueue_style("wpo-front-popup", plugins_url( "css/wpo-front.css" , __FILE__ ) );
	}

	function wpo_admin_scripts() {

	}

	function wpo_admin_styles() {
		$c_page = $_REQUEST['page'];
		if($c_page == "wpo_email_list" || $c_page == "WPPopupOptin" || $c_page == "wpo_manage_settings") {
			wp_enqueue_style("wpo-admin-css", plugins_url( "css/wpo-admin.css" , __FILE__ ) );
		}
	}


}

$wpPopOpt = new wpPopupOptin();
?>