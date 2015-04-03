<div class="wrap dashboard">
	<div class="row">
		<div id="wpo_email_list_cont">

			<h2 style="margin-bottom: 20px">WPO Email List</h2>

			<?php
			if(isset($_GET['eid'])) {
				if($_GET['eid'] != '') {
					global $wpdb;
					$eid = sanitize_text_field($_GET['eid']);

					$delete_query = "DELETE FROM " . $wpdb->prefix . "wpo_email_list WHERE wpo_email_id=" . $eid;
					if($wpdb->query($delete_query)) {
						echo '<div class="updated"><p><strong>Success</strong>: Email deleted.</p></div>';
					}
				}
			}
			?>

			<table class="widefat fixed">
			<thead>
			<tr>
				<th><b>Email</b></th>
				<th><b>Actions</b></th>
			</tr>
			</thead>
			<?php
			global $wpdb;
			$email_list = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wpo_email_list");
			$x=1;
			
			foreach( $email_list as $email ):
				$alt = '';
				if($x%2==0){ $alt = 'alternate'; }

				echo '<tr class="'.$alt.'">';
				echo '<td>' . $email->wpo_email . '</td>';
				echo '<td><a href="?page=wpo_email_list&eid=' . $email->wpo_email_id . '" onclick="return confirm(\'Are you sure you want to delete this email?\');">delete</a></td>';
				echo '</tr>';
				$x++;
			endforeach; 
			?>
			<tfoot>
			<tr>
				<th><b>Email</b></th>
				<th><b>Actions</b></th>
			</tr>
			</tfoot>
			</table>

		</div>

	</div>
</div>