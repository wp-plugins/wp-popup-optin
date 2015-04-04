<div class="wrap dashboard">
	<div class="row">
		<div id="wpo_admin_form">

			<h2>WP Popup Optin</h2>

			<?php

			if(isset($_POST['wpo_clear_cookie'])) {
				if($_POST['wpo_clear_cookie'] == 'yes') {
					echo '<div class="updated"><p><strong>Success</strong>: Cookies cleared.</p></div>';
				}
			}

			if(isset($_POST['wpo_action'])) {
				if($_POST['wpo_action'] == 'update') {
					$fields_arr = array('wpo_title', 'wpo_image_url', 'wpo_text', 'wpo_popup_status', 'wpo_show_every', 'wpo_content_type','wpo_popup_layout','wpo_custom_html', 'wpo_theme_color', 'wpo_submit_text');

					for($x=0;$x<count($fields_arr);$x++) {
						$field_name = $fields_arr[$x];

						update_option($field_name, sanitize_text_field($_POST[$field_name]));
					}

					echo '<div class="updated"><p><strong>Success</strong>: Settings Saved.</p></div>';

				}
			}

			$layout_arr = array( 
				'left_image' => 'left-image.png',
				'right_image' => 'right-image.png',
				'top_image' => 'top-image.png',
				'no_image' => 'no-image.png' );

			$colors_arr = array(
				'blue' => 'Blue',
				'red' => 'Red',
				'green_gray' => 'Green/Gray');

			$wpo_title = htmlentities(stripslashes(stripslashes(get_option('wpo_title',''))));
			$wpo_image_url = get_option('wpo_image_url','');
			$wpo_text = htmlentities(stripslashes(stripslashes(get_option('wpo_text',''))));
			$wpo_show_every = get_option('wpo_show_every','');
			if( $wpo_show_every == '' || !is_numeric($wpo_show_every) )
				$wpo_show_every = 200;
			$wpo_content_type = get_option('wpo_content_type','optin_form');
			$wpo_popup_layout = get_option('wpo_popup_layout', 'left_image');
			$wpo_theme_color = get_option('wpo_theme_color');
			$wpo_popup_status = get_option('wpo_popup_status');
			$wpo_custom_html = stripslashes(stripslashes(get_option('wpo_custom_html')));	
			$wpo_submit_text = htmlentities(stripslashes(stripslashes(get_option('wpo_submit_text'))));		
			?>

			<form method="post" id="settings">
			<p><b>Popup Content:</b><br />
				<select name="wpo_content_type" id="popup_content">
					<option value="optin_form" <?php if($wpo_content_type == 'optin_form') { echo 'selected="selected"'; } ?>>Optin Form</option>
					<option value="custom_html" <?php if($wpo_content_type == 'custom_html') { echo 'selected="selected"'; } ?>>Custom HTML</option>
				</select>
			</p>
			<div class="part_left">
			<div class="optin_form<?php if($wpo_content_type == 'optin_form') { echo ' active'; } ?>" >

			<p><b>Title</b><br />
			<input type="text" name="wpo_title" value="<?php echo $wpo_title; ?>" /></p>

			<p><b>Image URL:</b><br />
			                <img class="wpo_image" src="<?php echo $wpo_image_url; ?>" style="max-height: 100px; width: auto;" alt="no image" /><br />
			                <input class="wpo_image_url" type="text" name="wpo_image_url" value="<?php echo $wpo_image_url; ?>">
			                <a href="#" class="wpo_image_upload button">Upload</a>

			</p>    


			<script>
			    jQuery(document).ready(function($) {
			        $('.wpo_image_upload').click(function(e) {
			            e.preventDefault();

			            var custom_uploader = wp.media({
			                title: 'Custom Image',
			                button: {
			                    text: 'Upload Image'
			                },
			                multiple: false  // Set this to true to allow multiple files to be selected
			            })
			            .on('select', function() {
			                var attachment = custom_uploader.state().get('selection').first().toJSON();
			                $('.wpo_image').attr('src', attachment.url);
			                $('.wpo_image_url').val(attachment.url);

			            })
			            .open();
			        });
			    });
			</script>			

			<p><b>Popup Layout:</b><br />
				<input id="wpo_popup_layout_id" name="wpo_popup_layout" type="hidden" value="<?php if(!empty($wpo_popup_layout)) { echo $wpo_popup_layout; } else { echo 'left_image'; } ?>" />

				<div class="wpo_layouts_cont">
					<?php 
					$x = 0;
					foreach( $layout_arr as $key => $value ) {
						$is_selected = '';
						if($wpo_popup_layout == $key) {
							$is_selected = 'class="selected"';						
						} ?>
						<span <?php echo $is_selected; ?> data="<?php echo $key; ?>"><img src="<?php echo plugins_url( 'images/' . $value, __FILE__ ) ?>" ></span>
					<?php
						if($x == 2) { echo '<div class="clear"></div>'; }
						$x++;
					} ?>
					<div class="clear"></div>
				</div>
			</p>

			<p><b>Theme Color:</b><br />
				<input id="theme_color" type="hidden" name="wpo_theme_color" value="<?php if(!empty($wpo_theme_color)) { echo $wpo_theme_color; } else {echo 'blue'; } ?>">
				<div class="themes">
					<span <?php if ($wpo_theme_color=='blue') echo 'class="selected"'; ?> data="blue"><img src="<?php echo plugins_url( 'images/screen1.jpg', __FILE__ ) ?>" ></span>
					<span <?php if ($wpo_theme_color=='red') echo 'class="selected"'; ?> data="red"><img src="<?php echo plugins_url( 'images/screen2.jpg', __FILE__ ) ?>" ></span>
					<span <?php if ($wpo_theme_color=='green_gray') echo 'class="selected"'; ?> data="green_gray"><img src="<?php echo plugins_url( 'images/screen3.png', __FILE__ ) ?>" ></span>
					<div class="clear"></div>
				</div>
			</p>

			<p>
				<b>Text</b><br />
				<textarea name="wpo_text"><?php echo $wpo_text; ?></textarea>
			</p>

			<p><b>Button Text:</b><br />
				<input type="text" name="wpo_submit_text" value="<?php echo $wpo_submit_text; ?>" />
			</p>

			</div>

			<div class="custom_html<?php if($wpo_content_type == 'custom_html') { echo ' active'; } ?>">
			<p><b>Custom HTML:</b><br />
				<div>
				<?php
				$settings = array( 'textarea_rows' => 8 );
				$content = wpautop($wpo_custom_html);
				$editor_id = 'wpo_custom_html';

				wp_editor( $content, $editor_id, $settings );
				?>
				</div>
			</p>
			</div>

			<input type="hidden" name="wpo_action" value="update" />
			<p><input type="submit" value="Update" class="button button-primary button-large"/> <input type="submit" class="button button-primary"  id="reset-cookies" value="Update & Reset Cookies"></p>

			</div>
			<div class="area_rght">

				<p><b>Status</b><br />
					<select name="wpo_popup_status">
						<option value="">Disabled</option>
						<option value="1" <?php if($wpo_popup_status == '1') { echo 'selected="selected"'; } ?>>Enabled</option>
					</select>
				</p>

				<p><b>Show Every:</b><br />
					<input type="radio" name="wpo_show_every" id="wpo_show_once_id" value="200" <?php if($wpo_show_every == '200') { echo 'checked="checked"'; } ?> /> <label for="wpo_show_once_id">Only Once</label><br />				
					<input type="radio" name="wpo_show_every" id="wpo_show_daily_id" value="1" <?php if($wpo_show_every == '1') { echo 'checked="checked"'; } ?>/> <label for="wpo_show_daily_id">Once a day</label><br />
					<input type="radio" name="wpo_show_every" id="wpo_show_weekly_id" value="7" <?php if($wpo_show_every == '7') { echo 'checked="checked"'; } ?> /> <label for="wpo_show_weekly_id">Once a week</label><br />
					<input type="radio" name="wpo_show_every" id="wpo_show_monthly_id" value="30" <?php if($wpo_show_every == '30') { echo 'checked="checked"'; } ?>/> <label for="wpo_show_monthly_id">Once a month</label>
				</p>
			

				<input type="hidden" name="wpo_action" value="update" />
			</div>
			</form>

			<div class="area_rght">
			
			</div>
			<div class="clear"></div>
		</div>
		<form method="post" id="clear-cookies">
				<input type="hidden" name="wpo_clear_cookie" value="yes" id="wpo_clear_cookie"/>
				<input type="submit" value="Reset Cookies" class="button button-large" />
			</form>

	</div>
</div>
<script type="text/javascript">
	
	$j = jQuery.noConflict();

	$j(document).ready(function() {
		$j( "#popup_content" ).change(function() {
			$j('.custom_html, .optin_form').fadeToggle(300,"linear");
		});

		// for update and reset cookies		
		$j("#reset-cookies").click(function(e){
			e.preventDefault();
			var data = {
						'action': 'wpo_clearcookies',
						'wpo_clear_cookie': $j('#wpo_clear_cookie').val()
					};

					var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
					$j.post(ajaxurl, data, function(response) {
						$j('#settings').submit();
					});	
			
		});

		// for theme selection
		$j(".themes span").click(function(){
			$j(".themes span").removeClass('selected');
			$j(this).addClass('selected');
			$j('#theme_color').val($j(this).attr('data'));
		});

		// for layout selection
		$j(".wpo_layouts_cont span").click(function(){
			$j(".wpo_layouts_cont span").removeClass('selected');
			$j(this).addClass('selected');
			$j('#wpo_popup_layout_id').val($j(this).attr('data'));
		});

	});

</script>