<?php

/*
Plugin Name: CSV to 301 Redirects
Description: Use a .csv file for bulk 301 redirects
Text Domain: csv-to-301-redirects
Domain Path: /languages
Author: Dave van Hoorn
Author URI: https://davevanhoorn.com/?utm_campaign=csv-to-301-redirects&utm_medium=referral&utm_content=author-uri
Version: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

 * Copyright (C) 2009-2016 davevanhoorn.com <info [at] freshcode [dot] nl>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the [GNU General Public License](http://wordpress.org/about/gpl/)
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * on an "AS IS", but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see [GNU General Public Licenses](http://www.gnu.org/licenses/),
 * or write to the Free Software Foundation, Inc., 51 Franklin Street,
 * Fifth Floor, Boston, MA 02110-1301, USA.
*/

/**
 * Hooks into template_redirect and redirects if there's a match in the redirects array
 *
 * @since 1.0.0
 */
function csv_to_301_redirects() {

	// Variables
	$uri		= $_SERVER['REQUEST_URI'];
	$file_path	= csv_to_301_redirects_get_file();
	$file 		= csv_to_301_redirects_check_file( $file_path );
	$redirects	= csv_to_301_redirects_get_redirects( $file );

	// Checks
	if ( false !== $file_path && false !== $file && false !== $redirects && true === isset( $redirects[ $uri ] ) ) {

		// Redirect
		wp_redirect( $redirects[ $uri ], 301 );

		// Exit
		exit;

	}

}
add_action( 'init', 'csv_to_301_redirects' );


/**
 * Returns the saved attachment ID's file path
 *
 * @since 1.0.0
 * @return array, bool	string with .csv file path
 */
function csv_to_301_redirects_get_file() {

	// Check
	if ( false !== get_option( 'csv_to_301_redirects_csv_file_path' ) ) {

		// Get attachment ID
		$attachment_id = (int) get_option( 'csv_to_301_redirects_csv_file_path' )['csv_to_301_redirects_csv_file_path'];

		// Get file path
		$file_path = get_attached_file( $attachment_id, false );

		// Return
		return $file_path;

	} else {

		// Return
		return false;

	}

}


/**
 * Checks the given attachment file type
 *
 * @since 1.0.0
 * @return string|bool	string with .csv file path or false on error
 */
function csv_to_301_redirects_check_file( $file_path = false ) {


	// Variables
	$mime_types = array(
		'application/csv',
		'application/excel',
		'application/ms-excel',
		'application/x-excel',
		'application/vnd.ms-excel',
		'application/vnd.msexcel',
		'application/octet-stream',
		'application/data',
		'application/x-csv',
		'application/txt',
		'plain/text',
		'text/anytext',
		'text/csv',
		'text/x-csv',
		'text/plain',
		'text/comma-separated-values'
	);

	if ( $file_path === false || ! is_readable( $file_path ) ) {
		return false;
	}

	// Check mime type
	if ( in_array( mime_content_type( $file_path ), $mime_types ) ) {
		return $file_path;
	} else {
		return false;
	}
}


/**
 * Loops over the csv file and save key/values in an array
 *
 * @since 1.0.0
 * @return array|bool
 */
function csv_to_301_redirects_get_redirects( $file = false ) {

	// Check
	if ( false !== $file ) {

		// Variables
		$redirects = [];

		// Open
		$handle = fopen( $file, 'r' );

		// Loop
		while ( false !== ( $data = fgetcsv( $handle ) ) ) {

			// Redirects
			$redirects[ $data[0] ] = $data[1];

		}

		// Close
		fclose( $handle );

		// Return
		return $redirects;

	} else {

		// Return
		return false;

	}

}


/**
 * Registers settings, adds settings section and setting field
 *
 * @since 1.0.0
 */
function csv_to_301_redirects_settings_init() {

	// Register setting
	register_setting( 'csv-to-301-redirects', 'csv_to_301_redirects_csv_file_path' );

	// Add section
	add_settings_section( 'csv_to_301_redirects_section_developers', null, null, 'csv-to-301-redirects' );

	// Register field
	add_settings_field(
		'csv_to_301_redirects_csv_file_path',
		__( 'Attachment ID:', 'csv-to-301-redirects' ),
		'csv_to_301_redirects_csv_file_path_callback',
		'csv-to-301-redirects',
		'csv_to_301_redirects_section_developers',
		[
			'label_for'		=> 'csv_to_301_redirects_csv_file_path',
			'class'      	=> 'csv_to_301_redirects_row',
			'csv_to_301_redirects_custom_data' => 'custom',
		]
	);

}
add_action( 'admin_init', 'csv_to_301_redirects_settings_init' );


/**
 * Shows attachment ID input field
 *
 * @since 1.0.0
 */
function csv_to_301_redirects_csv_file_path_callback( $args ) {

	// Output
	?>
	<input type="number"
		   id="<?php echo esc_attr( $args['label_for'] ); ?>"
		   data-custom="<?php echo esc_attr( $args['csv_to_301_redirects_custom_data'] ); ?>"
		   name="csv_to_301_redirects_csv_file_path[<?php echo esc_attr( $args['label_for'] ); ?>]"
		   value="<?php echo esc_attr( ( get_option( 'csv_to_301_redirects_csv_file_path' )['csv_to_301_redirects_csv_file_path'] ) ? get_option( 'csv_to_301_redirects_csv_file_path' )['csv_to_301_redirects_csv_file_path'] : ('') ) ; ?>" />
	<?php

}


/**
 * Creates options page
 *
 * @since 1.0.0
 */
function csv_to_301_redirects_options_page() {

	// Add option pages
	add_options_page(
		'CSV to 301 Redirects',
		'CSV to 301',
		'manage_options',
		'csv-to-301-redirects-options',
		'csv_to_301_redirects_options_page_html'
	);

	// Remove update nag
	remove_action( 'admin_notices', 'update_nag', 3 );

}
add_action( 'admin_menu', 'csv_to_301_redirects_options_page' );


/**
 * Outputs HTML on the options page
 *
 * @since 1.0.0
 */
function csv_to_301_redirects_options_page_html() {

	// Check capabilities
	if ( !current_user_can( 'manage_options' ) ) {
		return;
	}

	// Variables
	$file_path = csv_to_301_redirects_get_file();
	$file      = csv_to_301_redirects_check_file( $file_path );
	$redirects = csv_to_301_redirects_get_redirects( $file );

	// Check file_path
	if ( false === $file_path ) {

		// Show file error message
		add_settings_error( 'csv_to_301_redirects_messages', 'csv_to_301_redirects_message', __( 'Upload a .csv file in the <a href="' . esc_url( get_admin_url() . 'upload.php' ) . '" target="_blank" title="Open the WordPress Media Library">media library</a> and enter the attachment ID below. Make sure your .csv is formatted correctly (see the example .csv files in the plugin directory).', 'csv-to-301-redirects' ), 'notice-info' );

	}

	// Check file
	if ( false === $file && false !== $file_path ) {

		if ( is_readable( $file_path ) ) {

			// Show MIME error message
			add_settings_error( 'csv_to_301_redirects_messages', 'csv_to_301_redirects_message', __( 'Attachment has an invalid mime type. Create a message in the plugin support forum if you\'re 100% sure you\'ve uploaded a genuine .csv file', 'csv-to-301-redirects' ), 'notice-error' );

		} else {

			// Show file unreadable error message
			add_settings_error( 'csv_to_301_redirects_messages', 'csv_to_301_redirects_message', __( 'Cannot read uploaded CSV file. Check your upload settings and try reuploading the file.', 'csv-to-301-redirects' ), 'notice-error' );

		}

	}

	// Nofity of redirects
	if ( false !== $file_path && false !== $file && false !== $redirects ) {

		// Show redirecting message
		add_settings_error( 'csv_to_301_redirects_messages', 'csv_to_301_redirects_message', __( 'Redirects active. Please confirm by clicking some of the sources below. Clearing the attachment ID will stop all redirects from working.', 'csv-to-301-redirects' ), 'notice-success' );

	}

	// Show message
	settings_errors( 'csv_to_301_redirects_messages' );

	// Output
	?>

	<div class="wrap">
		<h1><?= esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php

			// Output security fields
			settings_fields( 'csv-to-301-redirects' );

			// Output sections and fields
			do_settings_sections( 'csv-to-301-redirects' );

			// Check file
			if ( false !== $file_path && false === $file && true !== $redirects || false !== $file_path && false !== $file && false !== $redirects ) {

				// Get attachment ID
				$attachment_id = (int) get_option( 'csv_to_301_redirects_csv_file_path' )['csv_to_301_redirects_csv_file_path'];

				// Show attachment info
				echo '<table class="form-table"><tbody>';
				echo '<tr>';
				echo '<th>Attachment title: </th>';
				echo '<td>' . esc_html( get_the_title( $attachment_id ) ) . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th>Attachment file: </th>';
				echo '<td>' . esc_html( basename( get_attached_file( $attachment_id ) ) ) . ' </td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th>Attachment mime: </th>';
				if ( false !== $file ) {
					echo '<td>' . esc_html( mime_content_type( $file_path ) ) . '</td>';
				} else {
					echo '<td>' . esc_html__( 'Cannot read file!', 'csv-to-301-redirects' ) . '</td>';
				}
				echo '</tr>';
				echo '</tbody></table>';

			}

			// Output submit button
			submit_button( 'Save' );

			?>
		</form>
	</div>

	<?php

	// Check
	if ( false !== $file_path && false !== $file && false !== $redirects ) {

		// Variables
		$counter = 0;

	?>

		<div class="wrap">
			<table>
				<thead>
					<tr>
						<td><strong>Source</strong></td>
						<td style="padding-left: 20px;"><strong>Target</strong></td>
					</tr>
				</thead>
				<tbody>
					<?php

						// Loop
						foreach( $redirects as $source => $target ) {

							// Up
							$counter++;

							// Stop at 250
							if ( $counter < 250 ) {

								?>

								<tr>
									<td><a href="<?php echo esc_url( home_url() . $source ); ?>" target="blank"><?php echo $source; ?></a></td>
									<td style="padding-left: 20px;"><?php echo esc_html( $target ); ?></td>
								</tr>

								<?php

							} else {

								?>
								<tr><td>-</td></tr>
								<tr><td><strong>Showing only the first 250 results of the .csv file.</strong></td></tr>
								<tr><td>-</td></tr>
								<?php

								// Stop
								break;

							}

						}

					?>
				</tbody>
			</table>
		</div>

	<?php

	}

}

?>
