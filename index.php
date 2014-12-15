<?php
/**
 * Plugin Name: Dashboard Image Sizes
 * Plugin URI: http://figmints.com
 * Description: Adds a dashboard widget to display the image sizes registered on a site. 
 * Version: 1.0
 * Author: Ryan Kanner
 * Author URI: http://figmints.com
 * License: GPL2
 */

function fig_admin_enqueue() {
	wp_enqueue_script( 'jquery-ui-tooltip' );
	wp_enqueue_script( 'dash-js', plugin_dir_url( __FILE__ ) . '/js/main.js', array('jquery-ui-tooltip') );
	wp_enqueue_style( 'tooltip-styles', '//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
}

add_action( 'admin_enqueue_scripts', 'fig_admin_enqueue' );

function fig_dash() {
	wp_add_dashboard_widget(
		'image_sizes',
		'Image Sizes',
		'fig_dash_content'
	);
}

add_action( 'wp_dashboard_setup', 'fig_dash' );

function fig_dash_content() {

	$sizes = fig_get_image_sizes();
	$count = 1;
	if ( $sizes ): ?>

	<table class="widefat">
		<thead>
			<tr>
				<th><span>Name</span></th>
				<th><span>Width</span></th>
				<th><span>Height</span></th>
				<th><span>Hard Crop <a id="croptooltip" title="testing">?</a></span></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach( $sizes as $size ) {
				if ( $count % 2 == 0 ) {
					echo '<tr class="alternate">';
				} else {
					echo '<tr>';
				}
					echo '<th>' . $size['name'] . '</th>';
					echo '<th>' . $size['width'] . '</th>';
					echo '<th>' . $size['height'] . '</th>';
					if ( $size['crop'] ) {
						echo '<th>True</th>';
					} else {
						echo '<th>False</th>';
					}
				echo '</tr>';
				$count++;
			}
			?>
		</tbody>
	</table>
	
	<?php endif;
}

function fig_get_image_sizes( $size = '' ) {

	global $_wp_additional_image_sizes;

	$sizes = array();
	$get_intermediate_image_sizes = get_intermediate_image_sizes();

	// Create the full array with sizes and crop info
	foreach( $get_intermediate_image_sizes as $_size ) {

		if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

			$sizes[ $_size ]['name'] = ucwords(str_replace( '-', ' ', $_size ));
			$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
			$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
			$sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );

		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

			$sizes[ $_size ] = array( 
				'name' => ucwords(str_replace( '-', ' ', $_size )),
				'width' => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
			);

		}

	}

	// Get only 1 size if found
	if ( $size ) {

		if( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		} else {
			return false;
		}

	}

	return $sizes;
}