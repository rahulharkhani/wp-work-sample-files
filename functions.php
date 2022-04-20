<?php
/*** Child Theme Function  ***/
function enqueue_parent_styles() {
	wp_enqueue_style( 'parent-style', get_stylesheet_directory_uri().'/style.css' );
	
	if(is_page('news') || is_page('reports') || is_page('affiliate-chapter-home')){
		wp_enqueue_style('psa-slick', get_stylesheet_directory_uri() . '/css/slick.css', array(), null);
		wp_enqueue_script('psa-slick-min', get_stylesheet_directory_uri() . '/js/slick.min.js', array(), '1.0.0', true);
	}
}
add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );


/* Register new menu Location (i.e Primary menu, Footer Menu, Header menu, etc.) */
function register_header_menu(){
	register_nav_menu('header-menu', __('Header'));
}
add_action('init', 'register_header_menu');

/**
 * Custom Post type Stories
 */
function cpt_stories() {
	$labels = array(
		'name'               => _x( 'Stories', 'post type general name' ),
		'singular_name'      => _x( 'Story', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'story' ),
		'add_new_item'       => __( 'Add New Story' ),
		'edit_item'          => __( 'Edit Story' ),
		'new_item'           => __( 'New Story' ),
		'all_items'          => __( 'All Stories' ),
		'view_item'          => __( 'View Story' ),
		'search_items'       => __( 'Search Stories' ),
		'not_found'          => __( 'No stories found' ),
		'not_found_in_trash' => __( 'No stories found in the Trash' ),
		'parent_item_colon'  => ’,
		'menu_name'          => 'Stories'
	);
	$args = array(
		'labels'        => $labels,
		'description'   => 'Holds our stories and story specific data',
		'public'        => true,
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   => true,
		'show_in_rest'  => true,
	);
	register_post_type( 'story', $args ); 
	}
add_action( 'init', 'cpt_stories' );

/**
 * Custom Post type Stats
 */
function cpt_stats() {
	$labels = array(
		'name'               => _x( 'Stats', 'post type general name' ),
		'singular_name'      => _x( 'Stat', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'stat' ),
		'add_new_item'       => __( 'Add New Stat' ),
		'edit_item'          => __( 'Edit Stat' ),
		'new_item'           => __( 'New Stat' ),
		'all_items'          => __( 'All Stats' ),
		'view_item'          => __( 'View Stat' ),
		'search_items'       => __( 'Search Stats' ),
		'not_found'          => __( 'No stats found' ),
		'not_found_in_trash' => __( 'No stats found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'Stats'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds our stats and stat specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   		=> true,
		// 'taxonomies' 			=> array('stats_category'),
	);
	register_post_type( 'stats', $args ); 
	}
add_action( 'init', 'cpt_stats' );

/* Register custom taxonomies for Stats post type */
function stats_taxonomies() {
	$labels = array(
		'name'              => _x( 'Stats Categories', 'taxonomy general name' ),
		'singular_name'     => _x( 'Stats Category', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Stats Categories' ),
		'all_items'         => __( 'All Stats Categories' ),
		'parent_item'       => __( 'Parent Stats Category' ),
		'parent_item_colon' => __( 'Parent Stats Category:' ),
		'edit_item'         => __( 'Edit Stats Category' ), 
		'update_item'       => __( 'Update Stats Category' ),
		'add_new_item'      => __( 'Add New Stats Category' ),
		'new_item_name'     => __( 'New Stats Category' ),
		'menu_name'         => __( 'Stats Categories' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
	);
	register_taxonomy( 'stats_type', 'stats', $args );
}
add_action( 'init', 'stats_taxonomies', 0 );

/**
 * Custom Post type Candles
 */
function cpt_candle() {
	$labels = array(
		'name'               => _x( 'Candle', 'post type general name' ),
		'singular_name'      => _x( 'Candle', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'Candle' ),
		'add_new_item'       => __( 'Add New Candle' ),
		'edit_item'          => __( 'Edit Candle' ),
		'new_item'           => __( 'New Candle' ),
		'all_items'          => __( 'All Candles' ),
		'view_item'          => __( 'View Candle' ),
		'search_items'       => __( 'Search Candle' ),
		'not_found'          => __( 'No candle found' ),
		'not_found_in_trash' => __( 'No candle found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'Candle'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds our candle specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   		=> true,
		'hierarchical' 		=> true,
		// 'taxonomies' 			=> array('candle_category'),
	);
	register_post_type( 'candle', $args ); 
	}
add_action( 'init', 'cpt_candle' );

if(is_single('candle')){
	flush_rewrite_rules( false );
}

/**
 * Custom Post type History
 */
function cpt_history() {
	$labels = array(
		'name'               => _x( 'History', 'post type general name' ),
		'singular_name'      => _x( 'History', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'history' ),
		'add_new_item'       => __( 'Add New History' ),
		'edit_item'          => __( 'Edit History' ),
		'new_item'           => __( 'New History' ),
		'all_items'          => __( 'All History' ),
		'view_item'          => __( 'View History' ),
		'search_items'       => __( 'Search History' ),
		'not_found'          => __( 'No history found' ),
		'not_found_in_trash' => __( 'No history found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'History'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds our history specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'supports'      		=> array( 'title', 'editor', 'thumbnail'),
		'has_archive'   		=> true,
		'show_in_rest'  		=> true,
		// 'taxonomies' 			=> array('history_category'),
	);
	register_post_type( 'history', $args ); 
	}
add_action( 'init', 'cpt_history' );

/**
 * Custom Post type Leadership
 */
function cpt_leaders() {
	$labels = array(
		'name'               => _x( 'Team', 'post type general name' ),
		'singular_name'      => _x( 'Team', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'member' ),
		'add_new_item'       => __( 'Add New Member' ),
		'edit_item'          => __( 'Edit Member' ),
		'new_item'           => __( 'New Member' ),
		'all_items'          => __( 'All Members' ),
		'view_item'          => __( 'View Member' ),
		'search_items'       => __( 'Search Members' ),
		'not_found'          => __( 'No leaders found' ),
		'not_found_in_trash' => __( 'No leaders found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'Team'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds Team members specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   		=> false,
	);
	register_post_type( 'leader', $args ); 
	}
add_action( 'init', 'cpt_leaders' );

/* Register custom taxonomies for leaders/team post type */
function leaders_taxonomies() {
	$labels = array(
		'name'              => _x( 'Team Categories', 'taxonomy general name' ),
		'singular_name'     => _x( 'Team Category', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Team Categories' ),
		'all_items'         => __( 'All Team Categories' ),
		'parent_item'       => __( 'Parent Team Category' ),
		'parent_item_colon' => __( 'Parent Team Category:' ),
		'edit_item'         => __( 'Edit Team Category' ), 
		'update_item'       => __( 'Update Team Category' ),
		'add_new_item'      => __( 'Add New Team Category' ),
		'new_item_name'     => __( 'New Team Category' ),
		'menu_name'         => __( 'Team Categories' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'show_admin_column' => true,
	);
	register_taxonomy( 'leaders_type', 'leader', $args );
}
add_action( 'init', 'leaders_taxonomies', 0 );


/**
 * Custom Post type Office
 */
function cpt_offices() {
	$labels = array(
		'name'               => _x( 'Local Offices', 'post type general name' ),
		'singular_name'      => _x( 'Local Offices', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'office' ),
		'add_new_item'       => __( 'Add New Office' ),
		'edit_item'          => __( 'Edit Office' ),
		'new_item'           => __( 'New Office' ),
		'all_items'          => __( 'All Local Offices' ),
		'view_item'          => __( 'View Local Office' ),
		'search_items'       => __( 'Search Local Office' ),
		'not_found'          => __( 'No stats found' ),
		'not_found_in_trash' => __( 'No stats found in the Trash' ),
		'menu_name'          => 'Local Offices'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds Local Offices specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'supports'      		=> array( 'title', 'thumbnail'),
		'has_archive'   		=> false,
	);
	register_post_type( 'office', $args ); 
	}
add_action( 'init', 'cpt_offices' );


/**
 * Custom Post type press-release
 */
function cpt_press() {
	$labels = array(
		'name'               => _x( 'Press Releases', 'post type general name' ),
		'singular_name'      => _x( 'Press Release', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'press' ),
		'add_new_item'       => __( 'Add New' ),
		'edit_item'          => __( 'Edit Press' ),
		'new_item'           => __( 'New Press' ),
		'all_items'          => __( 'All Press Releases' ),
		'view_item'          => __( 'View Press' ),
		'search_items'       => __( 'Search Press' ),
		'not_found'          => __( 'No press found' ),
		'not_found_in_trash' => __( 'No press found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'Press Releases'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds Press releases specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'menu_icon'         => 'dashicons-welcome-write-blog',
		'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   		=> true,
	);
	register_post_type( 'press-release', $args ); 
}
add_action( 'init', 'cpt_press' );

/* Register custom taxonomies for Press Release post type */
function press_taxonomies() {
	$labels = array(
		'name'              => _x( 'Issues', 'taxonomy general name' ),
		'singular_name'     => _x( 'Issues', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Issues' ),
		'all_items'         => __( 'All Issues' ),
		'parent_item'       => __( 'Parent Issue' ),
		'parent_item_colon' => __( 'Parent Issue:' ),
		'edit_item'         => __( 'Edit Issue' ), 
		'update_item'       => __( 'Update Issue' ),
		'add_new_item'      => __( 'Add New Issue' ),
		'new_item_name'     => __( 'New Issue' ),
		'menu_name'         => __( 'Issues' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'show_admin_column' => true,
	);
	register_taxonomy( 'issues_type', 'press-release', $args );
}
add_action( 'init', 'press_taxonomies', 0 );

/**
 * Custom Post type PSA
 */
function cpt_psa() {
	$labels = array(
		'name'               => _x( 'PSA', 'post type general name' ),
		'singular_name'      => _x( 'PSA', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'psa' ),
		'add_new_item'       => __( 'Add New' ),
		'edit_item'          => __( 'Edit PSA' ),
		'new_item'           => __( 'New PSA' ),
		'all_items'          => __( 'All PSAs' ),
		'view_item'          => __( 'View PSA' ),
		'search_items'       => __( 'Search PSA' ),
		'not_found'          => __( 'No psa found' ),
		'not_found_in_trash' => __( 'No psa found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'PSA'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds PSAs specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'menu_icon'         => 'dashicons-media-interactive',
		'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   		=> false,
	);
	register_post_type( 'psa', $args ); 
}
add_action( 'init', 'cpt_psa' );

/**
 * Custom Post type Position statement
 */
function cpt_position() {
	$labels = array(
		'name'               => _x( 'Position statement', 'post type general name' ),
		'singular_name'      => _x( 'Position statement', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'psa' ),
		'add_new_item'       => __( 'Add New' ),
		'edit_item'          => __( 'Edit Position statement' ),
		'new_item'           => __( 'New Position statement' ),
		'all_items'          => __( 'All Position statements' ),
		'view_item'          => __( 'View Position statement' ),
		'search_items'       => __( 'Search Position statement' ),
		'not_found'          => __( 'No position statement found' ),
		'not_found_in_trash' => __( 'No position statement found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'Position statement'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds Position statement specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'menu_icon'         => 'dashicons-media-document',
		'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   		=> true,
	);
	register_post_type( 'position-statement', $args ); 
}
add_action( 'init', 'cpt_position' );


/**
 * Custom Post type Careers
 */
function cpt_careers() {
	$labels = array(
		'name'               => _x( 'Careers', 'post type general name' ),
		'singular_name'      => _x( 'Career', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'career' ),
		'add_new_item'       => __( 'Add New Job' ),
		'edit_item'          => __( 'Edit Job' ),
		'new_item'           => __( 'New Job' ),
		'all_items'          => __( 'All Jobs' ),
		'view_item'          => __( 'View Job' ),
		'search_items'       => __( 'Search Jobs' ),
		'not_found'          => __( 'No jobs found' ),
		'not_found_in_trash' => __( 'No jobs found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'Careers'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds our careers specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   		=> true,
		// 'taxonomies' 			=> array('stats_category'),
	);
	register_post_type( 'careers', $args ); 
	}
add_action( 'init', 'cpt_careers' );

/* Register custom taxonomies for careers post type */
function careers_taxonomies() {
	$labels = array(
		'name'              => _x( 'States', 'taxonomy general name' ),
		'singular_name'     => _x( 'States', 'taxonomy singular name' ),
		'search_items'      => __( 'Search States' ),
		'all_items'         => __( 'All States' ),
		'parent_item'       => __( 'Parent States' ),
		'parent_item_colon' => __( 'Parent States:' ),
		'edit_item'         => __( 'Edit States' ), 
		'update_item'       => __( 'Update States' ),
		'add_new_item'      => __( 'Add New State' ),
		'new_item_name'     => __( 'New States' ),
		'menu_name'         => __( 'States' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'show_admin_column' => true,
	);
	register_taxonomy( 'careers_type', 'careers', $args );
}
add_action( 'init', 'careers_taxonomies', 0 );


function careers_role() {
	$labels = array(
		'name'              => _x( 'Roles', 'taxonomy general name' ),
		'singular_name'     => _x( 'Roless', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Roles' ),
		'all_items'         => __( 'All Roles' ),
		'parent_item'       => __( 'Parent Roles' ),
		'parent_item_colon' => __( 'Parent Roles:' ),
		'edit_item'         => __( 'Edit Roles' ), 
		'update_item'       => __( 'Update Roles' ),
		'add_new_item'      => __( 'Add New Role' ),
		'new_item_name'     => __( 'New Roles' ),
		'menu_name'         => __( 'Roles' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'show_admin_column' => true,
	);
	register_taxonomy( 'role_type', 'careers', $args );
}
add_action( 'init', 'careers_role', 0 );


/**
 * Custom Post type Partners
 */
function cpt_partners() {
	$labels = array(
		'name'               => _x( 'Partners', 'post type general name' ),
		'singular_name'      => _x( 'Partner', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'career' ),
		'add_new_item'       => __( 'Add New Partner' ),
		'edit_item'          => __( 'Edit Partner' ),
		'new_item'           => __( 'New Partner' ),
		'all_items'          => __( 'All Partnerss' ),
		'view_item'          => __( 'View Partner' ),
		'search_items'       => __( 'Search Partners' ),
		'not_found'          => __( 'No partners found' ),
		'not_found_in_trash' => __( 'No partners found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'Partners'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds our partners specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'supports'      		=> array( 'title', 'editor', 'thumbnail'),
		'has_archive'   		=> true,
		'rewrite'   				=> array( 'slug' => 'partner' ),
	);
	register_post_type( 'partners', $args ); 
	}
add_action( 'init', 'cpt_partners' );


/* Register custom taxonomies for partners post type */
function partner_taxonomies() {
	$labels = array(
		'name'              => _x( 'Partner Categories', 'taxonomy general name' ),
		'singular_name'     => _x( 'Partner Category', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Category' ),
		'all_items'         => __( 'All Categories' ),
		'parent_item'       => __( 'Parent Category' ),
		'parent_item_colon' => __( 'Parent Categories:' ),
		'edit_item'         => __( 'Edit Category' ), 
		'update_item'       => __( 'Update Category' ),
		'add_new_item'      => __( 'Add New Category' ),
		'new_item_name'     => __( 'New Category' ),
		'menu_name'         => __( 'Partner Category' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'show_admin_column' => true,
	);
	register_taxonomy( 'partner_type', 'partners', $args );
}
add_action( 'init', 'partner_taxonomies', 0 );

/**
 * Custom Post type Leadership
 */
function cpt_reports() {
	$labels = array(
		'name'               => _x( 'Report', 'post type general name' ),
		'singular_name'      => _x( 'Report', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'Report' ),
		'add_new_item'       => __( 'Add New Report' ),
		'edit_item'          => __( 'Edit Report' ),
		'new_item'           => __( 'New Report' ),
		'all_items'          => __( 'All Reports' ),
		'view_item'          => __( 'View Report' ),
		'search_items'       => __( 'Search Reports' ),
		'not_found'          => __( 'No Reports found' ),
		'not_found_in_trash' => __( 'No Reports found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'Report'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds Reports specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   		=> false,
	);
	register_post_type( 'report', $args ); 
	}
add_action( 'init', 'cpt_reports' );

/* Register custom taxonomies for leaders/team post type */
function reports_taxonomies() {
	$labels = array(
		'name'              => _x( 'Report Categories', 'taxonomy general name' ),
		'singular_name'     => _x( 'Report Category', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Report Categories' ),
		'all_items'         => __( 'All Report Categories' ),
		'parent_item'       => __( 'Parent Report Category' ),
		'parent_item_colon' => __( 'Parent Report Category:' ),
		'edit_item'         => __( 'Edit Report Category' ), 
		'update_item'       => __( 'Update Report Category' ),
		'add_new_item'      => __( 'Add New Report Category' ),
		'new_item_name'     => __( 'New Report Category' ),
		'menu_name'         => __( 'Report Categories' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'show_admin_column' => true,
	);
	register_taxonomy( 'reports_type', 'report', $args );
}
add_action( 'init', 'reports_taxonomies', 0 );

/**
 * Custom Post type Events
 */
function cpt_events() {
	$labels = array(
		'name'               => _x( 'Events', 'post type general name' ),
		'singular_name'      => _x( 'Event', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'event' ),
		'add_new_item'       => __( 'Add New event' ),
		'edit_item'          => __( 'Edit Event' ),
		'new_item'           => __( 'New Event' ),
		'all_items'          => __( 'All Events' ),
		'view_item'          => __( 'View Events' ),
		'search_items'       => __( 'Search Events' ),
		'not_found'          => __( 'No Events found' ),
		'not_found_in_trash' => __( 'No Events found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'Events'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds our events specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   		=> true,
		'hierarchical' 		=> true,
		// 'taxonomies' 			=> array('events_category'),
	);
	register_post_type( 'events', $args ); 
	}
add_action( 'init', 'cpt_events' );

/* Register custom taxonomies for Events post type */
function events_taxonomies() {
	$labels = array(
		'name'              => _x( 'Events Categories', 'taxonomy general name' ),
		'singular_name'     => _x( 'Events Category', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Category' ),
		'all_items'         => __( 'All Categories' ),
		'parent_item'       => __( 'Parent Category' ),
		'parent_item_colon' => __( 'Parent Categories:' ),
		'edit_item'         => __( 'Edit Category' ), 
		'update_item'       => __( 'Update Category' ),
		'add_new_item'      => __( 'Add New Category' ),
		'new_item_name'     => __( 'New Category' ),
		'menu_name'         => __( 'Events Category' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'show_admin_column' => true,
	);
	register_taxonomy( 'events_type', 'events', $args );

	// Tags
	$labels = array(
		'name'              => _x( 'Event Tags', 'taxonomy general name' ),
		'singular_name'     => _x( 'Event Tag', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Tags' ),
		'all_items'         => __( 'All Categories' ),
		'parent_item'       => null,
		'parent_item_colon' => null,
		'edit_item'         => __( 'Edit Tags' ), 
		'update_item'       => __( 'Update Tags' ),
		'add_new_item'      => __( 'Add New Tags' ),
		'new_item_name'     => __( 'New Tags' ),
		'menu_name'         => __( 'Event Tags' ),
	);
	$args_tag = array(
		'labels' => $labels,
		'hierarchical' => false,
		'show_admin_column' => true,
	);
	register_taxonomy( 'events_tag', 'events', $args_tag );
}
add_action( 'init', 'events_taxonomies', 0 );

/**
 * Custom Post type Attorneys
 */
function cpt_attorneys() {
	$labels = array(
		'name'               => _x( 'Attorney', 'post type general name' ),
		'singular_name'      => _x( 'Attorney', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'Attorney' ),
		'add_new_item'       => __( 'Add New Attorney' ),
		'edit_item'          => __( 'Edit Attorney' ),
		'new_item'           => __( 'New Attorney' ),
		'all_items'          => __( 'All Attorneys' ),
		'view_item'          => __( 'View Attorney' ),
		'search_items'       => __( 'Search Attorneys' ),
		'not_found'          => __( 'No Attorneys found' ),
		'not_found_in_trash' => __( 'No Attorneys found in the Trash' ),
		// 'parent_item_colon'  => ’,
		'menu_name'          => 'Attorney'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds Attorneys specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   		=> false,
	);
	register_post_type( 'attorney', $args ); 
}
add_action( 'init', 'cpt_attorneys' );

// Google Maps API key for ACF
function acf_google_map_api( $api ){
	$api['key'] = 'YOUR_GOOGLE_API_KEY';
	return $api;
}
add_filter('acf/fields/google_map/api', 'acf_google_map_api');

/**
 * Custom Post type Blog
 */
function cpt_blog() {
	$labels = array(
		'name'               => _x( 'Blog', 'post type general name' ),
		'singular_name'      => _x( 'Blog', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'blog' ),
		'add_new_item'       => __( 'Add New Blog' ),
		'edit_item'          => __( 'Edit Blog' ),
		'new_item'           => __( 'New Blog' ),
		'all_items'          => __( 'All Blog' ),
		'view_item'          => __( 'View Blog' ),
		'search_items'       => __( 'Search Blog' ),
		'not_found'          => __( 'No Blogs found' ),
		'not_found_in_trash' => __( 'No Blogs found in the Trash' ),
		'menu_name'          => 'Blog'
	);
	$args = array(
		'labels'        		=> $labels,
		'description'   		=> 'Holds our Blog specific data',
		'public'        		=> true,
		'menu_position' 		=> 5,
		'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt'),
		'has_archive'   		=> true,
		'hierarchical' 			=> true,
		'rewrite' 				=> array('slug' => 'blog'),
	);
	register_post_type( 'blog', $args ); 
}
add_action( 'init', 'cpt_blog' );

/* Register custom taxonomies for Blog post type */
function blog_taxonomies() {
	$labels = array(
		'name'              => _x( 'Blogs Categories', 'taxonomy general name' ),
		'singular_name'     => _x( 'Blogs Category', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Category' ),
		'all_items'         => __( 'All Categories' ),
		'parent_item'       => __( 'Parent Category' ),
		'parent_item_colon' => __( 'Parent Categories:' ),
		'edit_item'         => __( 'Edit Category' ), 
		'update_item'       => __( 'Update Category' ),
		'add_new_item'      => __( 'Add New Category' ),
		'new_item_name'     => __( 'New Category' ),
		'menu_name'         => __( 'Blogs Category' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'show_admin_column' => true,
	);
	register_taxonomy( 'blog_type', 'blog', $args );

}
add_action( 'init', 'blog_taxonomies', 0 );


/**
 * Shortcode for Candles
*/
function caldle_with_search(){

	if ( get_query_var( 'paged' ) ) { 
		$paged = get_query_var( 'paged' ); 
	} elseif ( get_query_var( 'page' ) ) { 
		$paged = get_query_var( 'page' ); 
	} else { 
		$paged = 1; 
	}

	$args = array(
		'post_type' => 'candle',
		'post_status' => 'publish',
		'posts_per_page' => 16,
		'orderby' => 'title',
		'order' => 'ASC',
		'paged' => $paged,
	);

	$query = new WP_Query($args);

	?>
	<div class="candle-wrapper">
		
		<div class="candle-search-container">
			<!-- <form method="post"> -->
				<input type="text" name="candle_search" id="candle-search" placeholder="Search a Name" />
				<i aria-hidden="true" class="fas fa-search"></i>
			<!-- </form> -->
		</div>

		<div class="candles-list" id="candles-list">
			
			<?php
			while($query->have_posts()):
				$query->the_post();
			?>

			<div class="candle">
				<img src="<?php echo site_url().'/wp-content/uploads/2021/07/candle.png'; ?>" alt="Story of <?php echo the_title();?>">
				<p class="candle-intro text-red">My Name is...</p>
				<h3 class="candle-name"><?php echo the_title();?></h3>
				<div class="elementor-element elementor-element-bf51314 animate-btn-fill elementor-align-center elementor-widget elementor-widget-button" data-id="bf51314" data-element_type="widget" data-widget_type="button.default">
					<div class="elementor-widget-container">
						<div class="elementor-button-wrapper">
							<a href="<?php echo get_post_permalink();?>" class="elementor-button-link elementor-button elementor-size-sm candle-btn" role="button">
								<span class="elementor-button-content-wrapper">
									<span class="elementor-button-text">Read Story</span>
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>
			
			<?php
			endwhile;
			wp_reset_postdata();
			?>

		</div> <!-- End #candles-list -->

	</div>
	<?php
}
add_shortcode('caldle_with_search', 'caldle_with_search');

/**
 * ajax search Candles
*/
function search_candles(){
	$search_term = $_POST['search_term'];

	if ( get_query_var( 'paged' ) ) { 
		$paged = get_query_var( 'paged' ); 
	} elseif ( get_query_var( 'page' ) ) { 
		$paged = get_query_var( 'page' ); 
	} else { 
		$paged = 1; 
	}

	$args = array(
		'post_type' => 'candle',
		's' => $search_term,
		'paged' => $paged,
		'posts_per_page' => 16,
	);

	$query = new WP_Query($args);

	if($query->have_posts()):
		ob_start();
		while($query->have_posts()):
			$query->the_post();
			?>
			<div class="candle">
				<img src="<?php echo site_url();?>/wp-content/uploads/2021/07/candle.png" alt="Story of <?php echo the_title();?>">
				<p class="candle-intro text-red">My Name is...</p>
				<h3 class="candle-name"><?php echo the_title();?></h3>
				<div class="elementor-element elementor-element-bf51314 animate-btn-fill elementor-align-center elementor-widget elementor-widget-button" data-id="bf51314" data-element_type="widget" data-widget_type="button.default">
					<div class="elementor-widget-container">
						<div class="elementor-button-wrapper">
							<a href="<?php echo get_post_permalink();?>" class="elementor-button-link elementor-button elementor-size-sm candle-btn" role="button">
								<span class="elementor-button-content-wrapper">
									<span class="elementor-button-text">Read Story</span>
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php
		endwhile;
		$data = ob_get_clean();
	endif;

	// echo json_encode($data);
	echo $data;

	wp_die();
}
add_action('wp_ajax_search_candles', 'search_candles');
add_action('wp_ajax_nopriv_search_candles', 'search_candles');

/**
 * ajax load more Candles
*/
function load_more_candles(){
	$search_term = $_POST['search_term'];
	$page_no = $_POST['page_no'];

	$args = array(
		'post_type' => 'candle',
		's' => $search_term,
		'paged' => $page_no,
		'posts_per_page' => 16,
	);

	$query = new WP_Query($args);

	if($query->have_posts()):
		ob_start();
		while($query->have_posts()):
			$query->the_post();
			?>
			<div class="candle">
				<img src="<?php echo site_url();?>/wp-content/uploads/2021/07/candle.png" alt="Story of <?php echo the_title();?>">
				<p class="candle-intro text-red">My Name is...</p>
				<h3 class="candle-name"><?php echo the_title();?></h3>
				<div class="elementor-element elementor-element-bf51314 animate-btn-fill elementor-align-center elementor-widget elementor-widget-button" data-id="bf51314" data-element_type="widget" data-widget_type="button.default">
					<div class="elementor-widget-container">
						<div class="elementor-button-wrapper">
							<a href="<?php echo get_post_permalink();?>" class="elementor-button-link elementor-button elementor-size-sm candle-btn" role="button">
								<span class="elementor-button-content-wrapper">
									<span class="elementor-button-text">Read Story</span>
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php
		endwhile;
		$data = ob_get_clean();
	endif;

	// echo json_encode($data);
	echo $data;

	wp_die();
}
add_action('wp_ajax_load_more_candles', 'load_more_candles');
add_action('wp_ajax_nopriv_load_more_candles', 'load_more_candles');


/* History */
function show_timeline(){
	include '/assets/timeline/timeline.php';
}
add_shortcode('show_timeline', 'show_timeline');

// delete this function
function history_content(){
	include 'timeline/timeline.php';
}
add_shortcode('history_content', 'history_content');

/* Shortcode to dispaly PSAs slider */
function psa_content() {
	global $post;
	$psa_study = new WP_Query( array( 'post_type' => 'psa', 'post_status' => 'publish', 'orderby' => 'date', 'posts_per_page' => -1 ) );

	$output .= '<div class = "psa-posts">';
	if ( $psa_study->have_posts() ) : while ( $psa_study->have_posts() ) : $psa_study-> the_post();
		$study_id = get_the_ID();
		$study_title = get_the_title( $study_id );
		$study_link = get_the_permalink();
		$study_img = wp_get_attachment_url( get_post_thumbnail_id( $study_id ) );
		$study_desc = post_content_excerpt(24);
		$psa_file = get_field( 'psa_file', $study_id );

		$output .= '<div class="psa-item">';
		if ( !empty($study_img) ) {
				$output .= '<img src ="' . $study_img . '" alt ="' . get_the_title() . '" />';
		}
		$output .= '<div class="psa-studies">';
		$output .= '<h3 class ="psa-title"><a href='.$study_link.'> '.$study_title.' </a></h3>';
		$output .= '<p>' . $study_desc . '</p>';
		$output .= '<div class="download-psa">';
		$output .= '<a class="pcp-readmore-link elementor-button elementor-size-sm" target="_self" href="'.$psa_file.'" download="" role="button">
				<span class="elementor-button-content-wrapper">
					<span class="elementor-button-text">
						Download PSA 
					</span>
				</span>
			</a>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';
		 
	endwhile; endif; wp_reset_postdata(); wp_reset_query();
	
	$output .= '</div>';

	return $output;
}
add_shortcode( 'psa-tab', 'psa_content' );


/* Blog detail page month archive list */
add_shortcode( 'archive-page', 'compacter_archives' );
function get_posts_by_month( $year = null ) {

	if ( $year == null ) {
		$year = date('Y');
	}

	$months = range(1,12);
	$posts = array();

	foreach ( $months as $month ) {
		$posts_for_month = get_posts(array(
			'year' => $year,
			'monthnum' => $month ));
		$posts[$month] = $posts_for_month;
	}

	return $posts;
}

function compacter_archives() {

	$current_year = get_the_date( 'Y' );
	$monthly_posts = get_posts_by_month($current_year);

	ob_start();
	echo '<div class="archive-month">';
		echo '<ul class="archives-month-list">';
			echo '<li class="date-archive">';
				$post_date = get_the_date( 'F j,' ); 
				echo $post_date;
				echo '<span>&nbsp;' .$current_year. '</span>';
			echo '</li>';
		foreach ( $monthly_posts as $month => $posts ) {    
			$time = mktime(0, 0, 0, $month);
			$month_name = strftime("%b", $time);

			if ($posts) {
				echo '<li><a href="' . get_month_link( $current_year, $month ) . '">' . $month_name . '</a></li>';
			} else {
				echo '<li>' . $month_name . '</li>'; 
			}
		}
		echo '</ul>';
	echo '</div>';
	return ob_get_clean();
}


/* Blog detail page archive year */
add_shortcode( 'archive-year', 'archive_year' );

function archive_year() {
$current_year = get_the_date( 'Y' );

$query1 = new WP_Query( array ( 'year'=>$current_year ) );
if ( $query1->have_posts() ) {  
	echo '<div class="elementor-button-wrapper">';
		echo '<a href="' . get_year_link($current_year) . '" class="elementor-button-link elementor-button elementor-size-sm" role="button">';
			echo '<span class="elementor-button-content-wrapper">';
				echo '<span class="elementor-button-text">All articles from ' . $current_year . '</span>';
			echo '</span>';
		echo '</a>';
	echo '</div>';
} 
wp_reset_query();
}


/* Post excerpt */

function post_content_excerpt( $atts, $more = null ) {
	global $post;
	extract(shortcode_atts(array(
		'limit' => '24',
	), $atts));

	if( has_excerpt() ){
		$content = the_excerpt();
	} 
	else {
		$content = explode( ' ', get_the_content(), $limit );
			if ( count($content) >= $limit ) {
				array_pop( $content );
				$content = implode( " ", $content ).$more;
				$content = wp_strip_all_tags( $content, true );
			} 
			else {
				$content = implode( " ", $content );
			}
			
		$content = preg_replace( '/\[.+\]/','', $content );
		$content = preg_replace('/<img[^>]+\>/i','', $content); 
		$content = apply_filters( 'the_content', $content ); 
		$content = str_replace( ']]>', ']]&gt;', $content );
	}

	return $content;
}
add_shortcode( 'output_post_excerpt', 'post_content_excerpt' );

/* Filter post by careers on frontend */
function career_filter_taxonomy() {?>
<form action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST" id="filter">
		<?php
				if( $terms = get_terms( array( 'taxonomy' => 'careers_type', 'orderby' => 'name' ) ) ) : 
		
						echo '<select id="state_filter" name="statefilter"><option value="*">All States</option>';
						foreach ( $terms as $term ) :
								echo '<option value="' . $term->term_id . '">' . $term->name . '</option>'; // ID of the category as the value of an option
						endforeach;
						echo '</select>';
				endif;

				if( $terms_2 = get_terms( array( 'taxonomy' => 'role_type', 'orderby' => 'name' ) ) ) : 
		
						echo '<select id="role_filter" name="rolefilter"><option value="*">All Roles</option>';
						foreach ( $terms_2 as $term ) :
								echo '<option value="' . $term->term_id . '">' . $term->name . '</option>'; // ID of the category as the value of an option
						endforeach;
						echo '</select>';
				endif;
		?>
		<!-- <button>Apply filter</button>
		<input type="hidden" name="action" value="myfilter"> -->
</form>
<div id="response"></div>
<?php }
add_shortcode('filter_taxonomy', 'career_filter_taxonomy');

/* Shortcode to display Careers */

function career_content() {
	global $post;
	$career_role = new WP_Query( array( 'post_type' => 'careers', 'post_status' => 'publish', 'orderby' => 'date', 'posts_per_page' => 12 ) );

	$output .= '<div class = "career-posts">';
	//$output .= '<div class = "row justify-content-center">';

	if ( $career_role->have_posts() ) : while ( $career_role->have_posts() ) : $career_role-> the_post();
		$career_id = get_the_ID();
		$career_title = get_the_title( $career_id );
		$career_link = get_the_permalink();
		//$career_desc = post_content_excerpt(100);
		$career_state = get_field( 'state', $career_id );
		$career_work = get_field( 'work', $career_id );
		$career_type = get_field( 'type', $career_id );
		$career_salary = get_field( 'salary', $career_id );
		$career_travel = get_field( 'travel', $career_id );

		$output .= '<div class="career-item">';
		$output .= '<div class="career-content">';
		$output .= '<h2 class ="role-title">'.$career_title.'</h2>';
		if ( !empty($career_state) ) {
			$output .= '<p>' .$career_state. '</p>';
		}
		if ( !empty($career_work) ) {
			$output .= '<p>' .$career_work. '</p>';
		}
		$output .= '<div class="career-info">';
		if ( !empty($career_type) ) {
			$output .= '<p>Type:&nbsp' .$career_type. '</p>';
		}
		if ( !empty($career_salary) ) {
			$output .= '<p>Salary:&nbsp' .$career_salary. '</p>';
		}
		if ( !empty($career_travel) ) {
			$output .= '<p>Travel:&nbsp' .$career_travel. '</p>';
		}
		$output .= '</div>';
		$output .= '<div class="career-excpert">';
		$output .= '<p>' . do_shortcode('[output_post_excerpt  limit="46"].[/output_post_excerpt]') . '</p>';
		$output .= '</div>';
		$output .= '<div class="explore-role">';
		$output .= '<a class="exploremore-link elementor-button elementor-size-sm" target="_self" href="'.$career_link.'" role="button">
				<span class="elementor-button-text">
					Explore Role
				</span>
			</a>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';
		 
	endwhile; endif; wp_reset_postdata(); wp_reset_query();
	
	$output .= '</div>';

	return $output;
}
add_shortcode( 'carrer-tab', 'career_content' );


/* Ajax search for career filter */

function career_search() {
	$args = array(
		'post_type' => 'careers',
		'tax_query' => array(
				'relation' => 'AND',
				array(
						'taxonomy' => 'careers_type',
						'field'    => 'term_id',
						'terms'    => $_POST['state'],
				),
				array(
						'taxonomy' => 'role_type',
						'field'    => 'term_id',
						'terms'    => $_POST['role'],
						//'operator' => 'NOT IN',
				),
		),
	);

	$query = new WP_Query( $args );
	$output .= '<div class = "career-posts">';
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query-> the_post();
			$career_id = get_the_ID();
			$career_title = get_the_title( $career_id );
			$career_link = get_the_permalink();
			$career_state = get_field( 'state', $career_id );
			$career_work = get_field( 'work', $career_id );
			$career_type = get_field( 'type', $career_id );
			$career_salary = get_field( 'salary', $career_id );
			$career_travel = get_field( 'travel', $career_id );

			$output .= '<div class="career-item">';
			$output .= '<div class="career-content">';
			$output .= '<h2 class ="role-title"> '.$career_title.' </h2>';
			if ( !empty($career_state) ) {
				$output .= '<p>' .$career_state. '</p>';
			}
			if ( !empty($career_work) ) {
				$output .= '<p>' .$career_work. '</p>';
			}
			$output .= '<div class="career-info">';
			if ( !empty($career_type) ) {
				$output .= '<p>Type:&nbsp' .$career_type. '</p>';
			}
			if ( !empty($career_salary) ) {
				$output .= '<p>Salary:&nbsp' .$career_salary. '</p>';
			}
			if ( !empty($career_travel) ) {
				$output .= '<p>Travel:&nbsp' .$career_travel. '</p>';
			}
			$output .= '</div>';
			$output .= '<div class="career-excpert">';
			$output .= '<p>' . do_shortcode('[output_post_excerpt  limit="46"].[/output_post_excerpt]') . '</p>';
			$output .= '</div>';
			$output .= '<div class="explore-role">';
			$output .= '<a class="exploremore-link elementor-button elementor-size-sm" target="_self" href="'.$career_link.'" role="button">
					<span class="elementor-button-text">
						Explore Role
					</span>
				</a>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '</div>';
		}
	}
	else {
		echo "<p>No jobs found</p>";
	}
	//echo "</pre>";
	echo $output;
	wp_die();
}
add_action('wp_ajax_career_search', 'career_search');
add_action('wp_ajax_nopriv_career_search', 'career_search');


/* Geo WP plugin submit button text change to search */
function gmw_form_submit_button( $output, $gmw ) {

	return '<input type="submit" id="'.esc_attr( $gmw['ID'] ).'" class="gmw-submit gmw-submit-button" value="Search" />';
}
add_filter( 'gmw_form_submit_button', 'gmw_form_submit_button', 50, 2 );

/* Geo WP plugin stop update */
function stop_plugin_updates( $value ) {
	unset( $value->response['geo-my-wp/geo-my-wp.php'] );
	return $value;
}
add_filter( 'site_transient_update_plugins', 'stop_plugin_updates' ); 

/* Custom js */

add_action('wp_enqueue_scripts', 'custom_page_script');
function custom_page_script(){

	$custom_js_path = get_stylesheet_directory_uri() . '/js/custom.js';
	$script_data = get_file_data( $custom_js_path, array(
		'Version' => 'Version'
	));
	$version = $script_data['Version'];
	$version = empty($version) ? filemtime($custom_js_path) : $version;

	echo '<script>';
	echo 'var ajaxUrl = "'.admin_url('admin-ajax.php').'"';
	echo '</script>';
	
	wp_enqueue_script('js-custom', $custom_js_path, array('jquery'), $version, true);

	wp_enqueue_script('doy-mapcluster-callback', 'https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_API_KEY&callback=initMap', array('js-custom'), '', true);	// Google Map Marker cluster callback


}

/*-- Animate CSS --*/
wp_enqueue_style('animate-css', get_stylesheet_directory_uri() . '/css/animate.min.css', array(), null);
wp_enqueue_script('wow-js', get_stylesheet_directory_uri() . '/js/wow.min.js', array(), '1.0.0', false);

add_action('get_header', function() {

	if(is_page('timeline_1') || is_page('our-history')){

		/* CSS */
		wp_enqueue_style('timeline-bootstrap', get_stylesheet_directory_uri() . '/assets/timeline/css/bootstrap.min.css', array(), null);
		wp_enqueue_style('timeline-custom', get_stylesheet_directory_uri() . '/assets/timeline/css/custom.css', array(), null);
		wp_enqueue_style('timeline-slick', get_stylesheet_directory_uri() . '/assets/timeline/css/slick.css', array(), null);

		/* JS */
		echo "<script>var site_url = '".site_url()."'</script>";
		wp_enqueue_script('timeline-isMobile-min', get_stylesheet_directory_uri() . '/assets/timeline/js/isMobile.min.js', array(), '1.0.0', true);
		wp_enqueue_script('timeline-main', get_stylesheet_directory_uri() . '/assets/timeline/js/main.js', array(), '1.0.0', true);
		wp_enqueue_script('timeline-slick-min', get_stylesheet_directory_uri() . '/assets/timeline/js/slick.min.js', array(), '1.0.0', true);
		wp_enqueue_script('timeline-gsap-min', get_stylesheet_directory_uri() . '/assets/timeline/js/gsap.min.js', array(), '1.0.0', true);
		wp_enqueue_script('timeline-mobile_menu_anim', get_stylesheet_directory_uri() . '/assets/timeline/js/mobile_menu_anim.js', array(), '1.0.0', true);
		wp_enqueue_script('timeline-ScrollToPlugin-min', get_stylesheet_directory_uri() . '/assets/timeline/js/ScrollToPlugin.min.js', array(), '1.0.0', true);
		wp_enqueue_script('timeline-ScrollTrigger-min', get_stylesheet_directory_uri() . '/assets/timeline/js/ScrollTrigger.min.js', array(), '1.0.0', true);
		wp_enqueue_script('timeline-Draggable-min', get_stylesheet_directory_uri() . '/assets/timeline/js/Draggable.min.js', array(), '1.0.0', true);
		wp_enqueue_script('timeline-SplitText-min', get_stylesheet_directory_uri() . '/assets/timeline/js/SplitText.min.js', array(), '1.0.0', true);
	}
});


// function that runs when shortcode is called
function reports_shortcode($atts) {
	global $wpdb;
	$atts = shortcode_atts(
				array('category' => 'all'), 
		$atts
	);
	$category = $atts['category'];
	if ($category == 'all') {
		$args = array(
			'post_type' => 'report',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			//'orderby' => 'ID',
			//'order' => 'DESC',
		);
	} else {
		$args = array(
			'post_type' => 'report',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			//'orderby' => 'ID',
			//'order' => 'DESC',
			'tax_query' => array(
					array(
						'taxonomy' => 'reports_type',
						'field'    => 'slug',
						'terms'    => $category,
					)
			),
		);
	}
	$html = '<div class="ctm-slider responsive reports-'.$category.'">';
	$query = new WP_Query($args);
	if ($query->have_posts() ) {
		while ( $query->have_posts() ) : $query->the_post();
			$postID = get_the_ID();
			$redirectLink = get_field('custom_links', $postID);
			$sliderImage = "";
			if (has_post_thumbnail( $post->ID ) ):
				$featuredImage = wp_get_attachment_image_src( get_post_thumbnail_id( $postID ), 'medium' );
				$sliderImage = $featuredImage[0];
			endif;
			$html .= '<div>
						<a href="'.$redirectLink.'" target="_blank">
							<img src="'.$sliderImage.'" />
						</a>
				</div>';
		endwhile;
		wp_reset_postdata();
	} else {
		$html .= $category." reports not found";	
	}
	$html .= '</div>';

	$jQuerySelector = ".reports-".$category;
	$html .= '<script>
		jQuery(document).ready(function ($) {
			$("'.$jQuerySelector.'").slick({
				dots: false,
				infinite: true,
				speed: 3000,
				slidesToShow: 3,
				slidesToScroll: 1,
				autoplay: true,
				autoplaySpeed: 3000,
				responsive: [
					{
						breakpoint: 1024,
						settings: {
							slidesToShow: 3,
							slidesToScroll: 1,
							infinite: true,
							dots: false
						}
					},
					{
						breakpoint: 600,
						settings: {
							slidesToShow: 2,
							slidesToScroll: 1,
							infinite: true,
							dots: false
						}
					},
					{
						breakpoint: 480,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1,
							infinite: true,
							dots: false
						}
					}
				]
			});
		});
	</script>';
	return $html;
}
add_shortcode('reports', 'reports_shortcode');


// function that runs when shortcode is called
function events_shortcode($atts) {
	global $wpdb;
	$args = array(
		'post_type' => 'events',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	);
	$html = '<div class="ctm-slider responsive event-shortcode">';
	$query = new WP_Query($args);
	if ($query->have_posts() ) {
		while ( $query->have_posts() ) : $query->the_post();
			$postID = get_the_ID();
			$postTitle = get_the_title($postID);
			$event_date = get_field( "event_date", $postID );
			$business_name = get_field( "business_name", $postID );
			$business_address = get_field( "business_address", $postID );
			$sliderImage = "";
			if (has_post_thumbnail( $post->ID ) ):
				$featuredImage = wp_get_attachment_image_src( get_post_thumbnail_id( $postID ), 'medium' );
				$sliderImage = $featuredImage[0];
			endif;
			$html .= '<div>
							<img src="'.$sliderImage.'" />
							<h4>'.$postTitle.'</h4>
							<p><b>'.$event_date.'</b></p>
							<p>'.$business_name.'</p>
							<p>'.$business_address.'</p>
							<p><a href="'.get_the_permalink($postID).'">LEARN MORE</a><a href="#">REGISTER</a></p>
				</div>';
		endwhile;
		wp_reset_postdata();
	} else {
		$html .= "events not found";	
	}
	$html .= '</div>';

	$jQuerySelector = ".event-shortcode";
	$html .= '<script>
		jQuery(document).ready(function ($) {
			$("'.$jQuerySelector.'").slick({
				dots: true,
				infinite: true,
				speed: 3000,
				slidesToShow: 2,
				slidesToScroll: 1,
				autoplay: true,
				autoplaySpeed: 3000,
				responsive: [
					{
						breakpoint: 1024,
						settings: {
							slidesToShow: 2,
							slidesToScroll: 1,
							infinite: true,
							dots: true
						}
					},
					{
						breakpoint: 600,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1,
							infinite: true,
							dots: true
						}
					},
					{
						breakpoint: 480,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1,
							infinite: true,
							dots: true
						}
					}
				]
			});
			setTimeout(function() {
				jQuery("'.$jQuerySelector.'")[0].slick.refresh();
			}, 2000);
		});
	</script>';
	return $html;
}
add_shortcode('events', 'events_shortcode');


function blog_content_shortcode( $atts = array(), $content = null ) {
	// do something to $content
	$atts = shortcode_atts(
		array('width' => '100'), 
		$atts
	);

	$html = '<div class="para-block">'.$content.'</div>';
	// always return
	return $html;
}
add_shortcode( 'blogcontent', 'blog_content_shortcode' );


// function that runs when shortcode is called
function attorney_shortcode($atts) {
	global $wpdb;
	$atts = shortcode_atts(
		array('id' => '0', 'loop' => '0'), 
		$atts
	);
	$html = '<div class="attorney-detail">';
	if ($atts['id'] != '0') {
		$postID = $atts['id'];
		$postTitle = get_the_title($postID);
		$website = get_field( "website", $postID );
		$phone = get_field( "phone", $postID );
		$fax = get_field( "fax", $postID );
		$email = get_field( "email", $postID );
		$address = get_field( "address", $postID );
		$available = get_field( "available", $postID );
		
		if(!empty($website)) {
			$html .= '<p>Website:
				<a href="'.$website.'">
					<strong class="text-red">'.$website.'</strong>
				</a>
			</p>';	
		}

		if(!empty($phone)) {
			$html .= '<p>Phone:
				<strong class="text-red">'.$phone.'</strong>
			</p>';
		}
		if(!empty($fax)) {
			$html .= '<p>Fax:
				<strong>'.$fax.'</strong>
			</p>';
		}
		if(!empty($email)) {
			$html .= '<p>Email:
				<a href="mailto:'.$email.'">
					<strong class="text-red">'.$email.'</strong>
				</a>
			</p>';
		}
		if(!empty($address)) {
			$html .= '<p>Address:
				<strong class="text-red">'.$address.'</strong>
			</p>';
		}
		if(!empty($available)) {
			$html .= '<p>Available:
				<strong class="text-red">'.$available.'</strong>
			</p>';
		}
	} elseif ($atts['id'] == '0' && $atts['loop'] == '1') {
		global $post;
		$postID = $post->ID;
		$postTitle = get_the_title($postID);
		$website = get_field( "website", $postID );
		$phone = get_field( "phone", $postID );
		$fax = get_field( "fax", $postID );
		$email = get_field( "email", $postID );
		$address = get_field( "address", $postID );
		$available = get_field( "available", $postID );
		$location = get_field('googlemap', $postID);


		if(!empty($website)) {
			$html .= '<p>Website:
				<a href="'.$website.'">
					<strong class="text-red">'.$website.'</strong>
				</a>
			</p>';	
		}

		if(!empty($phone)) {
			$html .= '<p>Phone:
				<strong class="text-red">'.$phone.'</strong>
			</p>';
		}
		if(!empty($fax)) {
			$html .= '<p>Fax:
				<strong>'.$fax.'</strong>
			</p>';
		}
		if(!empty($email)) {
			$html .= '<p>Email:
				<a href="mailto:'.$email.'">
					<strong class="text-red">'.$email.'</strong>
				</a>
			</p>';
		}
		if(!empty($address)) {
			$html .= '<p>Address:
				<strong class="text-red">'.$address.'</strong>
			</p>';
		}
		if(!empty($available)) {
			$html .= '<p>Available:
				<strong class="text-red">'.$available.'</strong>
			</p>';
		}
		if(!empty($location)) {
			$html .= '<span class="business-lat-long doy-hide" data-lat="'.esc_attr($location['lat']).'" data-lng="'.esc_attr($location['lng']).'" data-title="'.esc_attr($postTitle).'" data-website="'.esc_attr($website).'" data-phone="'.esc_attr($phone).'" data-fax="'.esc_attr($fax).'" data-email="'.esc_attr($email).'"></span>';
		}
	} else {
		$html .= "attorney not found";
	}
	
	$html .= '</div>';
	return $html;
}
add_shortcode('attorney', 'attorney_shortcode');

// function that runs when shortcode is called
function acf_googlemap_shortcode($atts) {
	global $wpdb;
	$atts = shortcode_atts(
		array('id' => '0'), 
		$atts
	);
	if(is_singular('attorney')) {
		$html = '<div class="acf-map" data-zoom="12">';
		if ($atts['id'] != '0') {
			$postID = $atts['id'];
			$location = get_field('googlemap', $postID);
			if(!empty($location)) {
				$html .= '<div class="marker" data-lat="'.esc_attr($location['lat']).'" data-lng="'.esc_attr($location['lng']).'"></div>';
			}
		} else {
			$html .= "google map not found";	
		}
		$html .= '</div>';
	} else {
		$html .= '<div class="ctm-error">this google map shortcode only support attorney post type</div>';
	}
	return $html;
}
add_shortcode('acfgooglemap', 'acf_googlemap_shortcode');