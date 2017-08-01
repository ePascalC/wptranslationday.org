<?php
/**
 * Add metaboxes to new local event CPT 
 */
add_action( 'add_meta_boxes_local-event', 'gwtdle_metaboxes' );
function gwtdle_metaboxes() {
   global $wp_meta_boxes;
   add_meta_box('mbox_le_city', __('Fields'), 'gwtdle_metaboxes_metaboxes_html', 'local-event', 'normal', 'high');
}

/**
 * Add metaboxes to new local event CPT 
 */
function gwtdle_metaboxes_metaboxes_html() {
    global $post;
    $custom = get_post_custom($post->ID);
	$arr = array(
		'city' => __('City', 'gwtdle'),
		'country' => __('Country', 'gwtdle'),
		'continent' => __('Continent', 'gwtdle'),
		'organizer_name' => __('Organizer Name', 'gwtdle'),
		'organizer_w_org' => __('Organizer w.org username', 'gwtdle'),
		'organizer_slack' => __('Organizer slack username', 'gwtdle'),
		'coorganizers' => __('Co-organizers', 'gwtdle'),
		'utc_start' => __('UTC start time', 'gwtdle'),
		'announcement_url' => __('Announcement URL', 'gwtdle')
	);

	?>
	<table id="le-custom-fields">
	<?php	
	foreach ($arr as $key=>$item) {
		$value = isset($custom[$key][0])?$custom[$key][0]:'';

		?>
		<tr><td><?php echo $item . ':'; ?></td><td><input name="<?php echo $key; ?>" value="<?php echo $value; ?>"></td></tr>
		<?php
	}
	?>
 	</table>
	<?php	
}

/**
 * Save meta data for local-event CPT
 */
add_action( 'save_post_local-event', 'gwtdle_save_post' ); 
function gwtdle_save_post()
{
    if(empty($_POST)) return; //why is gwtdle_save_post triggered by add new? 
    global $post;
	$arr = array(
		'city' => __('City', 'gwtdle'),
		'country' => __('Country', 'gwtdle'),
		'continent' => __('Continent', 'gwtdle'),
		'organizer_name' => __('Organizer Name', 'gwtdle'),
		'organizer_w_org' => __('Organizer w.org username', 'gwtdle'),
		'organizer_slack' => __('Organizer slack username', 'gwtdle'),
		'coorganizers' => __('Co-organizers', 'gwtdle'),
		'utc_start' => __('UTC start time', 'gwtdle'),
		'announcement_url' => __('Announcement URL', 'gwtdle')
	);
	foreach ($arr as $key=>$item) {
		update_post_meta($post->ID, $key, $_POST[$key]);
	}
}   

/**
 * Add columns in overview
 */
add_filter('manage_local-event_posts_columns' , 'gwtdle_add_columns');
function gwtdle_add_columns($columns) {
    unset($columns['date']);
    return array_merge($columns, 
				array(
					'Place' => __('Place', 'gwtdle'),
					'Organizer' =>__( 'Organizer', 'gwtdle'),
					'UTC Start' =>__( 'UTC Start', 'gwtdle'),
					'URL' =>__( 'URL', 'gwtdle'),
				)
		);
}
/**
 * Render columns in overview
 */
add_filter( 'manage_local-event_posts_custom_column', 'gwtdle_render_columns', 10, 2 );
function gwtdle_render_columns( $column, $post_id ) {
	switch ( $column ) {
        case 'Place' :
			$str = get_post_meta( $post_id , 'continent' , true ) . '/' . get_post_meta( $post_id , 'country' , true ) . '/' . get_post_meta( $post_id , 'city' , true );
            echo $str; 
            break;
        case 'Organizer' :
			$w_org = get_post_meta( $post_id , 'organizer_w_org' , true );
            echo 'WP: <a href="https://profiles.wordpress.org/' . $w_org . '">' . $w_org . '</a><br>Slack: @' . get_post_meta( $post_id , 'organizer_slack' , true ); 
            break;
		case 'UTC Start' :
			echo get_post_meta( $post_id , 'utc_start' , true );
			break;
		case 'URL' :
			echo '<a href="' . get_post_meta( $post_id , 'announcement_url' , true ) . '">Link</a>';
			break;
	}
}

/**
 * Create the local-event CPT 
 */
add_action( 'init', 'gwtdle_init' );
function gwtdle_init() {
	$labels = array(
		'name'               => _x( 'Local Events', 'post type general name', 'gwtdle' ),
		'singular_name'      => _x( 'Local Event', 'post type singular name', 'gwtdle' ),
		'menu_name'          => _x( 'Local Events', 'admin menu', 'gwtdle' ),
		'name_admin_bar'     => _x( 'Local Event', 'add new on admin bar', 'gwtdle' ),
		'add_new'            => _x( 'Add New', 'local-event', 'gwtdle' ),
		'add_new_item'       => __( 'Add New Local Event', 'gwtdle' ),
		'new_item'           => __( 'New Local Event', 'gwtdle' ),
		'edit_item'          => __( 'Edit Local Event', 'gwtdle' ),
		'view_item'          => __( 'View Local Event', 'gwtdle' ),
		'all_items'          => __( 'All Local Events', 'gwtdle' ),
		'search_items'       => __( 'Search Local Events', 'gwtdle' ),
		'parent_item_colon'  => __( 'Parent Local Events:', 'gwtdle' ),
		'not_found'          => __( 'No Local Events found.', 'gwtdle' ),
		'not_found_in_trash' => __( 'No Local Events found in Trash.', 'gwtdle' )
	);

	$args = array(
		'labels'             => $labels,
                'description'        => __( 'Description.', 'gwtdle' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'local-event' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'thumbnail' )
	);

	register_post_type( 'local-event', $args );
}

/**
 * Flush rewrite rules on plugin activation 
 */
register_activation_hook( __FILE__, 'gwtdle_rewrite_flush' );
function gwtdle_rewrite_flush() {
    // First, we "add" the custom post type via the above written function.
    // Note: "add" is written with quotes, as CPTs don't get added to the DB,
    // They are only referenced in the post_type column with a post entry, 
    // when you add a post of this CPT.
    gwtdle_init();

    // ATTENTION: This is *only* done during plugin activation hook in this example!
    // You should *NEVER EVER* do this on every page load!!
    flush_rewrite_rules();
}