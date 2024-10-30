<?php

/*
Plugin Name: ChillThemes Team Members
Plugin URI: http://wordpress.org/plugins/chillthemes-team-members
Description: Enables a post type to display team members for use in any of our Chill Themes.
Version: 1.2
Author: ChillThemes
Author URI: http://chillthemes.net
Author Email: itismattadams@gmail.com
License:

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/* Setup the plugin. */
add_action( 'plugins_loaded', 'chillthemes_team_setup' );

/* Register plugin activation hook. */
register_activation_hook( __FILE__, 'chillthemes_team_activation' );
	
/* Register plugin activation hook. */
register_deactivation_hook( __FILE__, 'chillthemes_team_deactivation' );

/* Plugin setup function. */
function chillthemes_team_setup() {

	/* Define the plugin version. */
	define( 'CHILLTHEMES_TEAM_VER', '1.2' );

	/* Get the plugin directory URI. */
	define( 'CHILLTHEMES_TEAM_URI', plugin_dir_url( __FILE__ ) );

	/* Load translations on the backend. */
	if ( is_admin() )
		load_plugin_textdomain( 'chillthemes-team', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/* Register the custom post type. */
	add_action( 'init', 'chillthemes_register_team' );

	/* Filter the post type columns. */
	add_filter( 'manage_edit-team_columns', 'chillthemes_team_columns' );

	/* Add the post type column. */
	add_action( 'manage_posts_custom_column', 'chillthemes_team_column' );

	/* Add meta box. */
	add_action( 'add_meta_boxes', 'chillthemes_team_create_meta_boxes' );
	
	/* Save meta box. */
	add_action( 'save_post', 'chillthemes_team_save_member_details', 10, 2 );

	/* Load social icons webfont stylesheet. */
	wp_enqueue_style( 'chillthemes-team-foundation-icons', plugins_url( 'fonts/foundation-icons.css', __FILE__ ), false, null );

}

/* Do things on plugin activation. */
function chillthemes_team_activation() {
	flush_rewrite_rules();
}

/* Do things on plugin deactivation. */
function chillthemes_team_deactivation() {
	flush_rewrite_rules();
}

/* Register the post type. */
function chillthemes_register_team() {

	/* Set the post type labels. */
	$team_labels = array(
		'name'					=> __( 'Team Members', 'ChillThemes' ),
		'singular_name'			=> __( 'Team Member', 'ChillThemes' ),
		'all_items'				=> __( 'Team Members', 'ChillThemes' ),
		'add_new_item'			=> __( 'Add New Team Member', 'ChillThemes' ),
		'edit_item'				=> __( 'Edit Team Member', 'ChillThemes' ),
		'new_item'				=> __( 'New Team Member', 'ChillThemes' ),
		'view_item'				=> __( 'View Team Member', 'ChillThemes' ),
		'search_items'			=> __( 'Search Team Members', 'ChillThemes' ),
		'not_found'				=> __( 'No team members found', 'ChillThemes' ),
		'not_found_in_trash'	=> __( 'No team members in trash', 'ChillThemes' )
	);

	/* Define the post type arguments. */
	$team_args = array(
		'can_export'		=> true,
		'capability_type'	=> 'post',
		'has_archive'		=> true,
		'labels'			=> $team_labels,
		'menu_icon'			=> CHILLTHEMES_TEAM_URI . '/images/menu-icon.png',
		'public'			=> true,
		'query_var'			=> 'member',
		'rewrite'			=> array( 'slug' => 'team', 'with_front' => false ),
		'supports'			=> array( 'editor', 'thumbnail', 'title' )
	);

	/* Register the post type. */
	register_post_type( apply_filters( 'chillthemes_team', 'team' ), apply_filters( 'chillthemes_team_args', $team_args ) );

}

/* Filter the columns on the custom post type admin screen. */
function chillthemes_team_columns( $columns ) {
	$columns = array(
		'cb'							=> '<input type="checkbox" />',
		'title'							=> __( 'Member Title', 'ChillThemes' ),
		'chillthemes-member-image'		=> __( 'Member Image', 'ChillThemes' )
	);
	return $columns;
}

/* Filter the data on the custom post type admin screen. */
function chillthemes_team_column( $column ) {
	switch( $column ) {

		/* If displaying the 'Image' column. */
		case 'chillthemes-member-image' :
			$return = '<img src="' . the_post_thumbnail( array( 40, 40 ) ) . '" alt="' . get_the_title() . '" />';
		break;

		break;

		/* Just break out of the switch statement for everything else. */
		default : break;
	}
}

/* Sort the order of the posts using AJAX. */
function chillthemes_team_sorting_page() {
	$chillthemes_team_sort = add_submenu_page( 'edit.php?post_type=team', __( 'Sort Team Members', 'ChillThemes' ), __( 'Sort', 'ChillThemes' ), 'edit_posts', basename( __FILE__ ), 'chillthemes_team_post_sorting_interface' );

	add_action( 'admin_print_scripts-' . $chillthemes_team_sort, 'chillthemes_team_scripts' );
	add_action( 'admin_print_styles-' . $chillthemes_team_sort, 'chillthemes_team_styles' );
}
add_action( 'admin_menu', 'chillthemes_team_sorting_page' );

/* Create the AJAX sorting interface. */
function chillthemes_team_post_sorting_interface() {
   $team_members = new WP_Query(
    	array(
    		'orderby' => 'menu_order',
    		'order' => 'ASC',
    		'posts_per_page' => -1,
    		'post_type' => 'team'
    	)
    );
?>

	<div class="wrap">

		<?php screen_icon( 'tools' ); ?>

		<h2><?php _e( 'Sort Team Members', 'ChillThemes' ); ?></h2>

		<p><?php _e( 'Drag and drop the items into the order in which you want them to display.', 'ChillThemes' ); ?></p>			

		<ul id="chillthemes-team-list">

			<?php while ( $team_members->have_posts() ) : $team_members->the_post(); if ( get_post_status() == 'publish' ) : ?>

				<li id="<?php the_id(); ?>" class="menu-item">

					<dl class="menu-item-bar">

						<dt class="menu-item-handle">
							<span class="menu-item-title"><?php the_title(); ?></span>
						</dt><!-- .menu-item-handle -->

					</dl><!-- .menu-item-bar -->

					<ul class="menu-item-transport"></ul>

				</li><!-- .menu-item -->

			<?php endif; endwhile; wp_reset_postdata(); ?>

		</ul><!-- #chillthemes-team-list -->

	</div><!-- .wrap -->

<?php }

/* Save the order of the items when it is modified. */
function chillthemes_team_save_sorted_order() {
	global $wpdb;

	$order = explode( ',', $_POST['order'] );
	$counter = 0;

	foreach( $order as $team_id ) {
		$wpdb->update( $wpdb->posts, array( 'menu_order' => $counter ), array( 'ID' => $team_id ) );
		$counter++;
	}

	die(1);
}
add_action( 'wp_ajax_team_sort', 'chillthemes_team_save_sorted_order' );

/* Load the scripts required for the AJAX sorting. */
function chillthemes_team_scripts() {
	wp_enqueue_script( 'jquery-ui-sortable' );
 	wp_enqueue_script( 'chillthemes-team-sorting', CHILLTHEMES_TEAM_URI . '/js/sort.js' );
}

/* Load the styles required for the AJAX sorting. */
function chillthemes_team_styles() {
	wp_enqueue_style( 'nav-menu' );
}

/* Create custom meta boxes. */
function chillthemes_team_create_meta_boxes() {

	/* Add custom meta box for member details fields */
	add_meta_box(
		'chillthemes-team-member-details',
		__( 'Team Member&#39;s Details', 'ChillThemes' ),
		'chillthemes_team_display_member_details',
		'team',
		'normal',
		'high'
	);

}

/* Display custom meta boxes. */
function chillthemes_team_display_member_details() {
	global $post;	

	wp_nonce_field( basename( __FILE__ ), 'chillthemes_team_member_details_nonce' );

	/* Retrieve the Position if it already exists. */
	$position = get_post_meta( $post->ID, 'chillthemes_team_member_position', true );

	/* Retrieve the Social Profile URLs if it already exists. */
	$dribbble = get_post_meta( $post->ID, 'chillthemes_team_member_dribbble', true );
	$email = get_post_meta( $post->ID, 'chillthemes_team_member_email', true );
	$facebook = get_post_meta( $post->ID, 'chillthemes_team_member_facebook', true );
	$flickr = get_post_meta( $post->ID, 'chillthemes_team_member_flickr', true );
	$forrst = get_post_meta( $post->ID, 'chillthemes_team_member_forrst', true );
	$googleplus = get_post_meta( $post->ID, 'chillthemes_team_member_googleplus', true );
	$instagram = get_post_meta( $post->ID, 'chillthemes_team_member_instagram', true );
	$pinterest = get_post_meta( $post->ID, 'chillthemes_team_member_pinterest', true );
	$twitter = get_post_meta( $post->ID, 'chillthemes_team_member_twitter', true );
	$youtube = get_post_meta( $post->ID, 'chillthemes_team_member_youtube', true );

?>

	<table class="form-table">

		<tbody>

			<tr>

				<th>
					<?php echo _e( 'Member&#39;s Position', 'ChillThemes' ); ?>
				</th>

				<td>
					<input class="widefat" id="chillthemes-team-member-position" name="chillthemes-team-member-position" type="text" value="<?php echo wp_filter_nohtml_kses( $position ); ?>" />
				</td>

			</tr>

			<tr>

				<th>
					<?php echo _e( 'Dribbble URL', 'ChillThemes' ); ?>
				</th>

				<td>
					<input class="widefat" id="chillthemes-team-member-dribbble" name="chillthemes-team-member-dribbble" type="text" value="<?php echo esc_url( $dribbble ); ?>" />
				</td>

			</tr>

			<tr>

				<th>
					<?php echo _e( 'Email URL', 'ChillThemes' ); ?>
				</th>

				<td>
					<input class="widefat" id="chillthemes-team-member-email" name="chillthemes-team-member-email" type="text" value="<?php echo esc_url( $email ); ?>" />
				</td>

			</tr>

			<tr>

				<th>
					<?php echo _e( 'Facebook URL', 'ChillThemes' ); ?>
				</th>

				<td>
					<input class="widefat" id="chillthemes-team-member-facebook" name="chillthemes-team-member-facebook" type="text" value="<?php echo esc_url( $facebook ); ?>" />
				</td>

			</tr>

			<tr>

				<th>
					<?php echo _e( 'Flickr URL', 'ChillThemes' ); ?>
				</th>

				<td>
					<input class="widefat" id="chillthemes-team-member-flickr" name="chillthemes-team-member-flickr" type="text" value="<?php echo esc_url( $flickr ); ?>" />
				</td>

			</tr>

			<tr>

				<th>
					<?php echo _e( 'Forrst URL', 'ChillThemes' ); ?>
				</th>

				<td>
					<input class="widefat" id="chillthemes-team-member-forrst" name="chillthemes-team-member-forrst" type="text" value="<?php echo esc_url( $forrst ); ?>" />
				</td>

			</tr>

			<tr>

				<th>
					<?php echo _e( 'Google&plus; URL', 'ChillThemes' ); ?>
				</th>

				<td>
					<input class="widefat" id="chillthemes-team-member-googleplus" name="chillthemes-team-member-googleplus" type="text" value="<?php echo esc_url( $googleplus ); ?>" />
				</td>

			</tr>

			<tr>

				<th>
					<?php echo _e( 'Instagram URL', 'ChillThemes' ); ?>
				</th>

				<td>
					<input class="widefat" id="chillthemes-team-member-instagram" name="chillthemes-team-member-instagram" type="text" value="<?php echo esc_url( $instagram ); ?>" />
				</td>

			</tr>

			<tr>

				<th>
					<?php echo _e( 'Pinterest URL', 'ChillThemes' ); ?>
				</th>

				<td>
					<input class="widefat" id="chillthemes-team-member-pinterest" name="chillthemes-team-member-pinterest" type="text" value="<?php echo esc_url( $pinterest ); ?>" />
				</td>

			</tr>

			<tr>

				<th>
					<?php echo _e( 'Twitter URL', 'ChillThemes' ); ?>
				</th>

				<td>
					<input class="widefat" id="chillthemes-team-member-twitter" name="chillthemes-team-member-twitter" type="text" value="<?php echo esc_url( $twitter ); ?>" />
				</td>

			</tr>

			<tr>

				<th>
					<?php echo _e( 'YouTube URL', 'ChillThemes' ); ?>
				</th>

				<td>
					<input class="widefat" id="chillthemes-team-member-youtube" name="chillthemes-team-member-youtube" type="text" value="<?php echo esc_url( $youtube ); ?>" />
				</td>

			</tr>

		</tbody>

	</table><!-- .form-table -->

<?php }

/* Save custom meta box info. */
function chillthemes_team_save_member_details( $post_id, $post ) {

	if ( !isset( $_POST['chillthemes_team_member_details_nonce'] ) || !wp_verify_nonce( $_POST['chillthemes_team_member_details_nonce'], basename( __FILE__ ) ) )
		return $post_id;

	$post_type = get_post_type_object( $post->post_type );

	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;
		
	$meta = array(
		'chillthemes_team_member_position' => wp_filter_nohtml_kses( strip_tags( $_POST['chillthemes-team-member-position'] ) ),
		'chillthemes_team_member_dribbble' => esc_url( $_POST['chillthemes-team-member-dribbble'] ),
		'chillthemes_team_member_email' => esc_url( $_POST['chillthemes-team-member-email'] ),
		'chillthemes_team_member_facebook' => esc_url( $_POST['chillthemes-team-member-facebook'] ),
		'chillthemes_team_member_flickr' => esc_url( $_POST['chillthemes-team-member-flickr'] ),
		'chillthemes_team_member_forrst' => esc_url( $_POST['chillthemes-team-member-forrst'] ),
		'chillthemes_team_member_googleplus' => esc_url( $_POST['chillthemes-team-member-googleplus'] ),
		'chillthemes_team_member_instagram' => esc_url( $_POST['chillthemes-team-member-instagram'] ),
		'chillthemes_team_member_pinterest' => esc_url( $_POST['chillthemes-team-member-pinterest'] ),
		'chillthemes_team_member_twitter' => esc_url( $_POST['chillthemes-team-member-twitter'] ),
		'chillthemes_team_member_youtube' => esc_url( $_POST['chillthemes-team-member-youtube'] )
	);

	foreach ( $meta as $meta_key => $new_meta_value ) {

		/* Get the meta value of the custom field key. */
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		/* If a new meta value was added and there was no previous value, add it. */
		if ( $new_meta_value && '' == $meta_value )
			add_post_meta( $post_id, $meta_key, $new_meta_value, true );

		/* If the new meta value does not match the old value, update it. */
		elseif ( $new_meta_value && $new_meta_value != $meta_value )
			update_post_meta( $post_id, $meta_key, $new_meta_value );

		/* If there is no new meta value but an old value exists, delete it. */
		elseif ( '' == $new_meta_value && $meta_value )
			delete_post_meta( $post_id, $meta_key, $meta_value );
	}

}

?>