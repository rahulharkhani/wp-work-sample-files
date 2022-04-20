<?php 

function site_setup() {
	load_theme_textdomain( 'icq', get_template_directory() . '/languages' );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// This theme supports a variety of post formats.
	add_theme_support( 'post-formats', array( 'aside', 'image', 'link', 'quote', 'status' ) );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu( 'primary', __( 'Primary Menu', 'ryoking' ) );

	/*
	 * This theme supports custom background color and image, and here
	 * we also set up the default background color.
	 */
	add_theme_support( 'custom-background', array(
		'default-color' => 'e6e6e6',
	) );

	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1920, 1080 ); // Unlimited height, soft crop
	
	add_image_size( 'display_profile', 602, 335 ); 
	add_image_size( 'project_preview', 320, 360 ); 
	
	add_theme_support('category-thumbnails');
}

add_action('wp_head', 'site_head', 1);
function site_head() {
	wp_enqueue_script( 'jquery' );
	//wp_enqueue_script( 'modernizr', get_bloginfo("stylesheet_directory") . '/js/modernizr.js', array(), '1.1.3', true );  
	wp_enqueue_script( 'jquery.scrollTo', get_bloginfo("stylesheet_directory"). '/js/jquery.scrollTo.min.js', array(), '1.4.2', true );
	wp_enqueue_script( 'jquery.localscroll', get_bloginfo("stylesheet_directory") . '/js/jquery.localscroll-1.2.7-min.js', array(), '1.2.7', true );
	wp_enqueue_script( 'jquery.icqFrontend', get_bloginfo("stylesheet_directory") . '/js/custom-icq-frontend.js', array(), '1.0', true );

	wp_enqueue_style( 'phase2-style', get_template_directory_uri() . '/assets/css/phase2-custom-style.css' );
	wp_enqueue_style( 'wpb-google-fonts', 'https://fonts.googleapis.com/css?family=Roboto:300,400,400i,500,700,900', false );
}


add_action( 'after_setup_theme', 'site_setup', 10 );

remove_action( 'after_setup_theme', 'twentythirteen_custom_header_setup', 11);
remove_action( 'after_setup_theme', 'twentythirteen_custom_header_setup', 100 );

add_filter('ngg_render_template', 'nggTemplateAwesomeness', 10, 2);
 
/**
* tell NextGEN about our custom template
* @param string $custom_template the path to the custom template file (or false if not known to us)
* @param string $template_name name of custom template sought
* @return string
*/
function nggTemplateAwesomeness($custom_template, $template_name) {
	 
    if ($template_name == 'gallery-ryo') {
        // see if theme has customised this template
		 
        $custom_template = locate_template("nggallery/$template_name.php");
        if (!$custom_template) {
            // no custom template so set to the default
            $custom_template = dirname(__FILE__) . "/$template_name.php";
        }
    }
 
    return $custom_template;
}


function ibu_admin_scripts() {
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	/*wp_register_script('ibe_upload', get_bloginfo('stylesheet_directory') . '/js/be.js', array('jquery','media-upload','thickbox'));*/
	wp_enqueue_script('ibe_upload');
}
function ibu_admin_styles() {
	wp_enqueue_style('thickbox');
}
add_action('admin_print_scripts', 'ibu_admin_scripts');
add_action('admin_print_styles', 'ibu_admin_styles');

add_filter('upload_mimes', 'pixert_upload_types');
function pixert_upload_types($existing_mimes=array()) {
	$existing_mimes['bpg'] = 'image/bpg';
	return $existing_mimes;
}


add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

/*
function wpa84258_admin_posts_sort_last_name( $query ){
    global $pagenow;
    if( is_admin()
        && 'edit.php' == $pagenow
        && !isset( $_GET['orderby'] )
        && !isset( $_GET['post_type'] ) ){
            $query->set( 'meta_key', '_edit_last' );
            $query->set( 'orderby', 'meta_value' );
            $query->set( 'order', 'ASC' );
    }
}
add_action( 'pre_get_posts', 'wpa84258_admin_posts_sort_last_name' );
*/

// Add javascript for Add Distributions generated value.
function my_enqueue($hook) {
	global $wpdb;
	
	$user_id = get_current_user_id();
	$user_info = get_userdata(get_current_user_id());
	
	// Find URL in Individual URLs Type
	$args = array(
		'author' 	=> $user_id,
		'post_type'	=> 'distributions'
	);
	
	// Get coupon credits data
	if ( !in_array( 'administrator', (array) $user_info->roles ) ) { // If he is not admin
		$pquery = new WP_Query( $args );
		$posts = $pquery->posts;
		$posts_counter = 0;
		
		foreach ($posts as $p) {
			$access_type = $wpdb->get_results("SELECT * FROM wp_postmeta WHERE post_id = '" . $p->ID . "' AND meta_key='access_type'");
			if ($access_type[0]->meta_value == 'limit') {
				$limit_user_num = $wpdb->get_results("SELECT * FROM wp_postmeta WHERE post_id = '" . $p->ID . "' AND meta_key='limit_user_num'");
				$posts_counter += $limit_user_num[0]->meta_value;
				
			}else if ($access_type[0]->meta_value == 'individual') {
				$ind_urls_num = $wpdb->get_results("SELECT * FROM wp_postmeta WHERE post_id = '" . $p->ID . "' AND meta_key='generated_individual_urls_num'");
				$posts_counter += $ind_urls_num[0]->meta_value;
			}
		}		
		$user_coupon_credits = get_the_author_meta('coupon_credits', $user_id);
		$user_coupon_credits_used = $posts_counter;
		$user_coupon_credits_left = $user_coupon_credits - $user_coupon_credits_used;
		
		$datatoBePassed  = array('coupon_credits' => $user_coupon_credits, 'coupon_credits_left' => $user_coupon_credits_left);
	
	}else { // Else he is admin, So he will get unlimited coupon.
		$datatoBePassed  = array('coupon_credits' => 999999999, 'coupon_credits_left' => 999999999);
	
	}
	
	// Get tasks and variable to .js file
	if ($hook == 'edit.php') { // Distribution list page
		if ( in_array( 'reseller', (array) $user_info->roles ) || in_array( 'customer', (array) $user_info->roles ) ) {
			wp_register_script( 'my_custom_script_2', get_template_directory_uri() . '/../icq/js/custom-icq-admin-distribution-show-credits.js?ver=1.0' );
			wp_localize_script( 'my_custom_script_2', 'object_name', $datatoBePassed );
			wp_enqueue_script( 'my_custom_script_2' );
		}
		
	}else if ($hook == 'post-new.php') { // Add New Distribution page
		wp_register_script( 'my_custom_script', get_template_directory_uri() . '/../icq/js/custom-icq-admin-distribution-add.js?ver=1.0' );	
		wp_localize_script( 'my_custom_script', 'object_name', $datatoBePassed );
		wp_enqueue_script( 'my_custom_script' );
		
		if ( !in_array( 'administrator', (array) $user_info->roles ) ) {
			wp_enqueue_script( 'my_custom_script_3', get_template_directory_uri() . '/../icq/js/custom-icq-admin-distribution-hide-restrict.js' );	
		}
	
	}else if ($hook == 'post.php') { // Edit Distribution page
		wp_register_script( 'my_custom_script', get_template_directory_uri() . '/../icq/js/custom-icq-admin-distribution-edit.js?ver=1.0' );	
		wp_localize_script( 'my_custom_script', 'object_name', $datatoBePassed );
		wp_enqueue_script( 'my_custom_script' );
		
		if ( !in_array( 'administrator', (array) $user_info->roles ) ) {
			wp_enqueue_script( 'my_custom_script_3', get_template_directory_uri() . '/../icq/js/custom-icq-admin-distribution-hide-restrict.js' );	
		}
	}
	
}
add_action( 'admin_enqueue_scripts', 'my_enqueue' );

function load_admin_css() {
	wp_enqueue_style( 'admin_stylesheet', get_template_directory_uri() . '/admin-style.css' );
}
add_action( 'admin_enqueue_scripts', 'load_admin_css' );

// Add 'Distributions' table column
function distributions_view_columns_head($defaults) {
    $defaults['view'] = 'Submissions';
	$defaults['view2'] = 'Individual URLs';
    return $defaults;
}
function distributions_view_columns_content($column_name, $post_ID) {
    if ($column_name == 'view') {
        echo '<a href="' . get_home_url() . '/wp-admin/admin.php?page=users_submissions_list&post=' . $post_ID . '">View</a>';
    
	}else if ($column_name == 'view2') {
		global $wpdb;
		$sql = 'SELECT post_id FROM '.$wpdb->postmeta.' WHERE meta_key = "access_type" AND meta_value = "individual" AND post_id = "' . $post_ID . '" ORDER BY 1';
		$field = $wpdb->get_row($sql);
		
		if ($field->post_id > 0) echo '<a href="' . get_home_url() . '/wp-admin/admin.php?page=individual_url_list&post=' . $post_ID . '">View</a>';
	}
}
add_filter('manage_distributions_posts_columns', 'distributions_view_columns_head', 10);
add_action('manage_distributions_posts_custom_column', 'distributions_view_columns_content', 10, 2);


/**
 * Extend WordPress filter to include custom fields 'tag_name', and 'assessments'
 *
 * http://adambalee.com
 */
/*
function ba_admin_posts_filter( $query )
{
	global $wpdb;
    global $pagenow;
    if ( is_admin() && $pagenow=='edit.php' && isset($_GET['ADMIN_FILTER_FIELD_NAME']) && $_GET['ADMIN_FILTER_FIELD_NAME'] != '') {
        $query->query_vars['meta_key'] = $_GET['ADMIN_FILTER_FIELD_NAME'];
    if (isset($_GET['ADMIN_FILTER_FIELD_VALUE']) && $_GET['ADMIN_FILTER_FIELD_VALUE'] != '')
        
		if ($_GET['ADMIN_FILTER_FIELD_NAME']=='assessment') {
			$sql = "SELECT ID FROM wp_posts WHERE post_title = '" . $_GET['ADMIN_FILTER_FIELD_VALUE'] . "' AND post_status='publish' AND post_type='post'";
			$fields = $wpdb->get_results($sql, ARRAY_N);
			$query->query_vars['meta_value'] = 70;
			
		}else {
			$query->query_vars['meta_value'] = $_GET['ADMIN_FILTER_FIELD_VALUE'];
		}
    }
}

function ba_admin_posts_filter_restrict_manage_posts()
{
	if ($_GET['post_type'] == 'distributions') {
		global $wpdb;
		$sql = 'SELECT DISTINCT meta_key FROM '.$wpdb->postmeta.' WHERE meta_key = "tag_name" OR meta_key = "assessment" ORDER BY 1';
		$fields = $wpdb->get_results($sql, ARRAY_N); ?>
		<select name="ADMIN_FILTER_FIELD_NAME">
			<option value=""><?php _e('Filter By Custom Fields', 'baapf'); ?></option>
			<?php
				$current = isset($_GET['ADMIN_FILTER_FIELD_NAME'])? $_GET['ADMIN_FILTER_FIELD_NAME']:'';
				$current_v = isset($_GET['ADMIN_FILTER_FIELD_VALUE'])? $_GET['ADMIN_FILTER_FIELD_VALUE']:'';
				foreach ($fields as $field) {
					if (substr($field[0],0,1) != "_"){
						(
							'<option value="%s"%s>%s</option>',
							$field[0],
							$field[0] == $current? ' selected="selected"':'',
							$field[0]
						);
					}
				}
			?>
			</select> <?php //_e('Value:', 'baapf'); ?><input type="TEXT" style="float:left; margin-right:5px; width:135px" name="ADMIN_FILTER_FIELD_VALUE" value="<?php echo $current_v; ?>" />
	<?php
	}
}
add_filter( 'parse_query', 'ba_admin_posts_filter' );
add_action( 'restrict_manage_posts', 'ba_admin_posts_filter_restrict_manage_posts' );
*/


/**
 * Extend WordPress search to include custom fields
 *
 * http://adambalee.com
 */

/**
 * Join posts and postmeta tables
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
function cf_search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {    
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }
    
    return $join;
}
add_filter('posts_join', 'cf_search_join' );

/**
 * Modify the search query with posts_where
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
 */
 
function cf_search_where( $where ) {
    global $pagenow, $wpdb;
   
    if ( is_search() ) {
		
		if ($_GET['post_type']=='distributions') {
			$sql = "SELECT ID FROM wp_posts WHERE post_title = '".$_GET['s']."' AND post_status='publish' AND post_type='post'";
			$fields = $wpdb->get_results($sql, ARRAY_N);
			
			$id_arr = array();
			foreach ($fields as $field) { 
				$id_arr[] = $field[0];
			}
			$ids = join("','",$id_arr); 
		}
		
		$where = preg_replace(
			"/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			"(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1) OR (".$wpdb->postmeta.".meta_value IN ('".$ids."'))", $where );
	
    }

    return $where;
}
add_filter( 'posts_where', 'cf_search_where' );

/**
 * Prevent duplicates
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
 */
function cf_search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', 'cf_search_distinct' );

/**
 * HTML Email
 *
 *
 */
function wpse27856_set_content_type(){
    return "text/html";
}
add_filter( 'wp_mail_content_type','wpse27856_set_content_type' );

/**
 * Add additional custom field
*/
function my_show_extra_profile_fields($user) { 
	global $wpdb;
	global $icq_dashboard;
	
	if( current_user_can( 'delete_plugins' ) ) { // So he is admin ?>
		<h3>Extra profile information</h3>
		<table class="form-table">
			<tr>
				<?php /*
				<th><label for="coupon_credits">Coupon Credits</label></th>
				<td>
					<input type="text" name="coupon_credits" id="coupon_credits" value="<?php echo esc_attr( get_the_author_meta( 'coupon_credits', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your coupon credits number.</span>
				</td> 
				*/ ?>
				
				<?php $total_credits = $icq_dashboard->getAllCreditsForOneUser($user->ID); ?>
				<th><label for="coupon_credits">User Credits</label></th>
				<td>
					<input type="text" name="licensee_credits" id="coupon_credits" value="<?php echo $total_credits; ?>" class="regular-text" /><br />
				</td>
			</tr>
		</table>
<?php }
}
add_action ( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action ( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_save_extra_profile_fields($user_id) {
    global $icq_report;
	
	if ( !current_user_can( 'edit_user', $user_id ) )
        return false;
   
	//update_usermeta( $user_id, 'coupon_credits', $_POST['coupon_credits'] );
	$icq_report->adminSaveDistributions($_POST['licensee_credits'], $user_id, 70);
}
add_action ( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action ( 'edit_user_profile_update', 'my_save_extra_profile_fields' );


function posts_for_reseller() {
    global $user_ID;

    /*if current user is an 'administrator' do nothing*/
    //if ( current_user_can( 'add_users' ) ) return;

    /*if current user is an 'administrator' or 'editor' do nothing*/
    if ( current_user_can( 'edit_others_pages' ) ) return;

    if ( ! isset( $_GET['author'] ) ) {
        wp_redirect( add_query_arg( 'author', $user_ID ) );
        exit;
    }
}
add_action( 'load-edit.php', 'posts_for_reseller' );

function posts_for_create_distribution($query) {
	global $pagenow;
	if( 'edit.php' != $pagenow || !$query->is_admin )
	    return $query;

	return $query;
}
add_filter('pre_get_posts', 'posts_for_create_distribution');

function icq_change_password_mail_message($pass_change_mail, $user, $userdata) {
	$user_login_ = $user['user_login'];
	$user_pass_ = $user['user_pass'];
	$user_nicename_ = $user['user_nicename'];
	$user_email_ = $user['user_email'];
	$display_name_ = $user['display_name'];
	
	$msg_text = __( 'Hi ' . $user_login_ . ', This notice confirms that your password was changed on ICQ Consulting. If you did not change your password, please contact the Site Administrator at raul_bird@hotmail.com, visit the following address '.get_site_url().'/wp-login.php' );
	$pass_change_mail[ 'message' ] = $msg_text;
	return $pass_change_mail;
}
add_filter('password_change_email', 'icq_change_password_mail_message', 10, 3);

function custom_retrieve_password_message($content, $key) {
	global $wpdb;
	if ( empty( $_POST['user_login'] ) ) {
		 wp_die('<strong>ERROR</strong>: Enter a username or e-mail address.');
	 } else if ( strpos( $_POST['user_login'], '@' ) ) {
		 $user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
	 }else if(!empty( $_POST['user_login'] )){
		 $user_data = get_user_by('login', trim( $_POST['user_login']));
	 }elseif ( empty( $user_data ) ){
		 wp_die('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
	 }
	$user_login_name=$user_data->user_login;
	ob_start();
	$email_subject = 'Your password has been changed';
	include('email_header.php');
	
	wp_set_password( 'MpO29w5iTe', $user_data->ID );
	
	?>
	<span style="color:black !important">Hi <?php echo $user_data->user_login; ?>, This notice confirms that your password was changed on ICQ Consulting. <br/>To reset your password, Use below account to visit this following address: <a href="<?php echo get_site_url(); ?>/wp-login.php"><?php echo get_site_url(); ?>/wp-login.php</a><br /><br /> Username: <?php echo $user_data->user_email; ?><br /> Password: MpO29w5iTe</span>

	<?php
	include('email_footer.php');
	$message = ob_get_contents();
	ob_end_clean();
	return $message;
}
add_filter ('retrieve_password_message', 'custom_retrieve_password_message', 10, 2);

// Redefine user notification function
if ( !function_exists('wp_new_user_notification') ) :
	function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {
        if ( $deprecated !== null ) {
			_deprecated_argument( __FUNCTION__, '4.3.1' );
		}
	 
		global $wpdb, $wp_hasher;
		$user = get_userdata( $user_id );
	 
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	 
		if ( 'user' !== $notify ) {
			$switched_locale = switch_to_locale( get_locale() );
	 
			/* translators: %s: site title */
			$message  = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
			/* translators: %s: user login */
			$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
			/* translators: %s: user email address */
			$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";
	 
			$wp_new_user_notification_email_admin = array(
				'to'      => get_option( 'admin_email' ),
				/* translators: Password change notification email subject. %s: Site title */
				'subject' => __( '[%s] New User Registration' ),
				'message' => $message,
				'headers' => '',
			);
	 
			/**
			 * Filters the contents of the new user notification email sent to the site admin.
			 *
			 * @since 4.9.0
			 *
			 * @param array   $wp_new_user_notification_email {
			 *     Used to build wp_mail().
			 *
			 *     @type string $to      The intended recipient - site admin email address.
			 *     @type string $subject The subject of the email.
			 *     @type string $message The body of the email.
			 *     @type string $headers The headers of the email.
			 * }
			 * @param WP_User $user     User object for new user.
			 * @param string  $blogname The site title.
			 */
			$wp_new_user_notification_email_admin = apply_filters( 'wp_new_user_notification_email_admin', $wp_new_user_notification_email_admin, $user, $blogname );
	 
			@wp_mail(
				$wp_new_user_notification_email_admin['to'],
				wp_specialchars_decode( sprintf( $wp_new_user_notification_email_admin['subject'], $blogname ) ),
				$wp_new_user_notification_email_admin['message'],
				$wp_new_user_notification_email_admin['headers']
			);
	 
			if ( $switched_locale ) {
				restore_previous_locale();
			}
		}
	 
		// `$deprecated was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notification.
		if ( 'admin' === $notify || ( empty( $deprecated ) && empty( $notify ) ) ) {
			return;
		}
	 
		// Generate something random for a password reset key.
		$key = wp_generate_password( 20, false );
	 
		/** This action is documented in wp-login.php */
		do_action( 'retrieve_password_key', $user->user_login, $key );
	 
		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );
	 
		$switched_locale = switch_to_locale( get_user_locale( $user ) );
	 
		/* translators: %s: user login */
		/*
		$message = sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
		$message .= __('To set your password, visit the following address:') . "\r\n\r\n";
		$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . ">\r\n\r\n";
	 
		$message .= wp_login_url() . "\r\n";
		*/
		
		$message = __('Hi there,') . "\r\n\r\n Your account is here : <br />";
		$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n<br />";
		$message .= sprintf(__('Password: %s'), 'MpO29w5iTe') . "\r\n\r\n<br /><br />";
		$message .= " Please change your password at first time you login, visit the following address with your account: ";
		//$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . ">\r\n\r\n";
		$message .= wp_login_url() . "\r\n";
		$message .= "<br /><br />Thank You<br />ICQ Consulting Team";
		
		$wp_new_user_notification_email = array(
			'to'      => $user->user_email,
			/* translators: Password change notification email subject. %s: Site title */
			'subject' => __( '[%s] Your username and password info' ),
			'message' => $message,
			'headers' => '',
		);
	 
		/**
		 * Filters the contents of the new user notification email sent to the new user.
		 *
		 * @since 4.9.0
		 *
		 * @param array   $wp_new_user_notification_email {
		 *     Used to build wp_mail().
		 *
		 *     @type string $to      The intended recipient - New user email address.
		 *     @type string $subject The subject of the email.
		 *     @type string $message The body of the email.
		 *     @type string $headers The headers of the email.
		 * }
		 * @param WP_User $user     User object for new user.
		 * @param string  $blogname The site title.
		 */
		$wp_new_user_notification_email = apply_filters( 'wp_new_user_notification_email', $wp_new_user_notification_email, $user, $blogname );
	 
		wp_mail(
			$wp_new_user_notification_email['to'],
			wp_specialchars_decode( sprintf( $wp_new_user_notification_email['subject'], $blogname ) ),
			$wp_new_user_notification_email['message'],
			$wp_new_user_notification_email['headers']
		);
	 
		if ( $switched_locale ) {
			restore_previous_locale();
		}

	}
endif;


function transformStringQuote($str) {
	$str = str_replace('&#039;', "'", $str); // quote
	$str = str_replace('&#096;', "'", $str); // grave accent
	$str = str_replace('&#180;', "'", $str); // acute accent
	$str = str_replace('&#8217;', "'", $str); // apostrophe
	$str = str_replace('&lsquo;', "'", $str); // open quote
	$str = str_replace('&rsquo;', "'", $str); // close quote
	
	$str = str_replace('&quot;', '"', $str);
	
	$str = trim($str);
	return $str;
}

// Reference : http://stackoverflow.com/questions/13614622/transliterate-any-convertible-utf8-char-into-ascii-equivalent
// Reference : http://stackoverflow.com/questions/4783802/converting-string-into-web-safe-uri
function toAscii($str, $replace=array(), $delimiter = ' ') {
	setlocale(LC_ALL, 'en_US.UTF8');
	if( !empty($replace) ) {
		$str = str_replace((array)$replace, ' ', $str);
	}
	
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	//$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
	
	return $clean;
}

// Next Feature : https://acko.net/blog/ufpdf-unicode-utf-8-extension-for-fpdf/

function icq_woocommerce_order_status_completed($order_id) {
    global $icq_report;
	$distribution_pid = $icq_report->saveDistributions($order_id);
}
add_action( 'woocommerce_thankyou', 'icq_woocommerce_order_status_completed', 10, 1 );

function generateRandomString_($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function showTextWithQuote($str) {
	$ret_str = str_replace('\\', '', $str);
	return $ret_str;
}

remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
show_admin_bar(false);

/*Remove my-account menu*/
add_filter ( 'woocommerce_account_menu_items', 'remove_my_account_links' );
function remove_my_account_links( $menu_links ){
    unset( $menu_links['edit-address'] ); // Addresses 
    unset( $menu_links['dashboard'] ); // Dashboard
    unset( $menu_links['payment-methods'] ); // Payment Methods
    unset( $menu_links['orders'] ); // Orders
    unset( $menu_links['downloads'] ); // Downloads
    unset( $menu_links['edit-account'] ); // Account details
    unset( $menu_links['customer-logout'] ); // Logout
 
    return $menu_links;
}

// User login & Register
function login($username, $password) {
   $creds = array();
   $creds['user_login'] = stripslashes($username);
   $creds['user_password'] = stripslashes($password);
   $user = wp_signon($creds, false);
   $creds['remember'] = true;	
   
   if (!is_wp_error($user)) {
		wp_set_auth_cookie($user->ID, 0, 0);
	   $message = '';
	   wp_redirect( '/dashboard' );
	   exit;
   }
   // return true;
}

function register() {
	global $icq_dashboard;
	global $wpdb;
	
	if (isset($_POST['password']) && $_POST['password'] == $_POST['confirmPassword'] ) {
		if (isset($_POST['email']) && isset($_POST['password'])) {
			$address 			= $_POST['address'];
			$user_email			= trim($_POST['email']);
			$user_pass			= trim($_POST['password']);
			$confirmPassword	= trim($_POST['confirmPassword']);
			$first_name			= trim($_POST['firstname']);
			$last_name			= trim($_POST['lastname']);
			$role				= trim($_POST['actionRole']);
			$img_profile 		= $_FILES['my_image_upload'];

			$userdata = array(
				'user_login' 	=> $user_email,
				'user_email'   	=> $user_email,
				'user_pass'  	=> $user_pass,
				'first_name' 	=> $first_name,
				'last_name' 	=> $last_name,
				'role' 			=> $role,
				'locale' 		=> $address
			);

			$user_id = wp_insert_user( $userdata ) ;

			if($img_profile['size'])
				$icq_dashboard->upload_user_profile_pic($img_profile,$user_id);

			$map_value = array(
				"companyName" 	=> "company",
				"phone" 		=> "phone",
				"sizeBusiness" 	=> "sizeBusiness",
				"industry" 		=> "industry",
				"country" 		=> "country",
				"postcode" 		=> "postcode",
				"townandcity" 	=> "townandcity"
			);

			foreach($_POST as $key => $val ){
				update_user_meta( $user_id, $map_value[$key], $val );	
			}
			
			//$icq_dashboard->addActivityLog('user_register', $user_id); // Add Activity Log
			
			if ( ! is_wp_error( $user_id ) ) {
				login($user_email , $user_pass);
				$return_register = array('name' => 'regist', 'msg' => 'success');
				
			}else {
				$return_register = array('name' => 'regist', 'msg' => 'This email address has already been registered.');
			}
			
			return $return_register;
		}
	}
}

function add_relation_user( $user_id ) {
	global $wpdb;
	$licenseeID	= trim($_POST['licenseeID']);
	$isMTvalue	= trim($_POST['isMTvalue']);
	$actionRole = trim($_POST['actionRole']);
	if($isMTvalue == "0")
	{
		if ( !empty($licenseeID) ) {
			$wpdb->insert('wp_licensee_trainer_relations', array(
						'licensee_id' 	=> $licenseeID,
						'trainer_id' 	=> $user_id
					)
			);
		}
	}
	else
	{
		if ($actionRole == "trainer")
		{
			$wpdb->insert('wp_mt_trainer_relations', array(
					'mt_id' 	=> $licenseeID,
					'trainer_id' 	=> $user_id,
					'licensee_id' 	=> '0',
				)
			);
		}
		else
		{
			$wpdb->insert('wp_mt_trainer_relations', array(
					'mt_id' 	=> $licenseeID,
					'trainer_id' 	=> '0',
					'licensee_id' 	=> $user_id,
				)
			);
		}
	}
    
}
add_action( 'user_register', 'add_relation_user', 10, 1 );

function add_user_role() {
	add_role(
        'trainer',
        'trainer',
        [
            'read'         => true,
            'edit_posts'   => false,
            'upload_files' => false,
        ]
    );
    
    add_role(
        'licensee',
        'licensee',
        [
            'read'         => true,
            'edit_posts'   => false,
            'upload_files' => false,
        ]
    );
	
	add_role(
        'mastertrainer',
        'mastertrainer',
        [
            'read'         => true,
            'edit_posts'   => false,
            'upload_files' => false,
        ]
    );
}

add_action('init', 'add_user_role');


function add_theme_caps() {
    $role = get_role('trainer','licensee');
    $role->add_cap( 'edit_others_posts' );
}
add_action( 'init', 'add_theme_caps');


########################## Dashboard ##########################

function do_dashboard_actions() {
	if (wp_verify_nonce($_REQUEST['nonce_add_participants'], 'add_participants')) {
		do_add_participants();
	
	}else if (wp_verify_nonce($_REQUEST['nonce_add_trainer_users'], 'add_trainer_users')) {
		do_add_trainer_users();
	
	}else if (wp_verify_nonce($_REQUEST['nonce_edit_trainer_users'], 'edit_trainer_users')) {
		do_edit_trainer_users();
	
	}else if (wp_verify_nonce($_REQUEST['nonce_add_groups'], 'add_groups')) {
		do_add_groups();
	
	}else if (wp_verify_nonce($_REQUEST['nonce_edit_groups'], 'edit_groups')) {
		do_edit_groups();
	}else if (wp_verify_nonce($_REQUEST['nonce_add_departments'], 'add_departments')) {
		
		do_add_departments();
		
	}else if (wp_verify_nonce($_REQUEST['nonce_edit_departments'], 'edit_departments')) {
		
		do_edit_departments();
		
	}else if (wp_verify_nonce($_REQUEST['nonce_add_mttusers'], 'add_mttusers')) {
		do_add_mttusers();
	
	}else if (wp_verify_nonce($_REQUEST['nonce_edit_mttusers'], 'edit_mttusers')) {
		
		do_edit_mttusers();
		
	}	
	
}

function do_dashboard_role_redirect($action, $secondpath) {
	global $icq_dashboard;
	$user = wp_get_current_user();
	$user_roles = $user->roles; //array of roles the user is part of.
	
	if ($action == 'users') {		
		// dashboard/users
		if ( !in_array("licensee", $user_roles) && !in_array("administrator", $user_roles) ) { 
			wp_redirect( get_home_url() . '/dashboard');
		}		
		// dashboard/users/edit?taid=param
		if( $secondpath == 'edit' && empty($icq_dashboard->get_trainer_added_by_id($_GET['taid'])) ) {
			wp_redirect( get_home_url() . '/dashboard/users');
		}
	}
	
	if ($action == 'groups') {
		// dashboard/groups
		if ( !in_array("licensee", $user_roles) && !in_array("administrator", $user_roles) && !in_array("trainer", $user_roles) ) { 
			wp_redirect( get_home_url() . '/dashboard');
		}
	}
	
	if ($action == 'participants') {
		// dashboard/participants
		if( (in_array("licensee", $user_roles) || in_array("administrator", $user_roles) || in_array("trainer", $user_roles)) && !isset($_GET['gid']) ) {
			wp_redirect( get_home_url() . '/dashboard');
		}
	}	
}

function get_total_credit_amount() {
	global $icq_dashboard;
	$user = wp_get_current_user();
	$user_roles = $user->roles;
	if (in_array('licensee', $user_roles)) $total_credit_amount = $icq_dashboard->get_total_credit_urls_amount_for_licensee();
	else if (in_array('trainer', $user_roles)) $total_credit_amount = $icq_dashboard->get_total_credit_urls_amount_for_trainer();		
	else $total_credit_amount = $icq_dashboard->get_total_credit_urls_amount();
	
	return $total_credit_amount;
}


function get_credit_used_amount() {
	global $icq_dashboard;
	$user = wp_get_current_user();
	$user_roles = $user->roles;
	if (in_array('licensee', $user_roles)) {
		$participant_added = $icq_dashboard->get_participant_added_for_licensee();
		$credit_used = $icq_dashboard->get_credit_used($participant_added);
	
	}else if (in_array('trainer', $user_roles)) {
		$licensee_id = $icq_dashboard->get_licensee_id_of_trainer();
		$participant_added = $icq_dashboard->get_participant_added_for_licensee($licensee_id);
		$credit_used = $icq_dashboard->get_credit_used($participant_added);
	
	}else {
		$participant_added = $icq_dashboard->get_participant_added();
		$credit_used = count($participant_added);
	}
	
	return $credit_used;
}


function get_participant_added() {
	global $icq_dashboard;
	$user = wp_get_current_user();
	$user_roles = $user->roles;
	if (in_array('licensee', $user_roles)) $participant_added = $icq_dashboard->get_participant_added_for_licensee();
	else if (in_array('trainer', $user_roles)) $participant_added = $icq_dashboard->get_participant_added_for_trainer();
	else $participant_added = $icq_dashboard->get_participant_added();
	
	return $participant_added;
}
	
function do_add_participants() {
	global $icq_dashboard;
	
	// All $_POST is array
	$participant_name = $_POST['participant_name'];
	$participant_email = $_POST['participant_email'];
	$participant_department = $_POST['participant_department'];
	
	$user = wp_get_current_user();
	$user_roles = $user->roles;
	if ( ( in_array('licensee', $user_roles) || in_array('trainer', $user_roles) ) && isset($_POST['group_id'])) $participants_added_result = $icq_dashboard->add_participants_in_cohort_group($participant_name, $participant_email, $participant_department, $_POST['group_id'] );
	else $participants_added_result = $icq_dashboard->add_participants($participant_name, $participant_email, $participant_department);
	
	if ($participants_added_result == 'complete') wp_redirect(get_home_url() . '/dashboard/participants?gid=' . $_POST['group_id'] . '&status=added_successfully');	
	else wp_redirect(get_home_url() . '/dashboard/participants?gid=' . $_POST['group_id'] . '&error=' . $participants_added_result);
}

function get_cohorts_groups() {
	global $icq_dashboard;
	
	$user = get_userdata(get_current_user_id());
	$user_roles = $user->roles;
	
	if ( in_array('licensee', $user_roles) || in_array('administrator', $user_roles) ) {
		if (isset($_GET['taid'])) {			
			$trainer_user_data = get_trainer_user_data($_GET['taid']);
			if (!empty($trainer_user_data)) {
				$trainer_id = $trainer_user_data->ID;
				$cohorts_groups = $icq_dashboard->get_cohorts_groups_by_user_id($trainer_id); // Trainer groups
			}
			
		}else {
			$cohorts_groups = $icq_dashboard->get_licensee_cohorts_groups(get_current_user_id()); // Licensee groups
		}

	}else if (in_array('trainer', $user_roles)) {
		$cohorts_groups = $icq_dashboard->get_cohorts_groups_by_user_id(get_current_user_id()); // Trainer groups
	}

	return $cohorts_groups;
}

function get_cohorts_departments() {
	global $icq_dashboard,$wpdb;
	
	$DepartmentResults = $wpdb->get_results("SELECT id, department_name FROM wp_departments WHERE deleted = 0 ORDER BY id DESC");

	return $DepartmentResults;
}

function get_mt_licensee_trainer_added_users($UserId) {
	global $icq_dashboard,$wpdb;
	
	$MTTrainerResults = $wpdb->get_results("SELECT id, user_id, name, email, added_date, reference_code, deleted, userrole FROM wp_mt_trainer_added_users WHERE deleted = 0 AND user_id = ".$UserId." ORDER BY id DESC");

	return $MTTrainerResults;
}

function get_cohorts_mastertrainers() {
	global $icq_dashboard,$wpdb;
	
	$MasterTrainersResults = $wpdb->get_results("SELECT id, trainer_name, trainer_email, trainer_url FROM wp_master_trainer WHERE deleted = 0 ORDER BY id DESC");

	return $MasterTrainersResults;
}

function get_trainer_user_data($taid) {
	global $icq_dashboard;
	
	$trainer_added_user = $icq_dashboard->get_trainer_added_by_id($taid);
	$trainer_user_data = get_user_by('email', $trainer_added_user->email);
	
	return $trainer_user_data;
}


function do_add_groups() {
	global $icq_dashboard;

	// All $_POST is array
	$group_name = $_POST['group_name'];
	$group_owner_name = $_POST['group_owner_name'];
	$group_owner_email = $_POST['group_owner_email'];
	$user_id = get_current_user_id();
	$post_id = $_POST['post_id'];
	$GroupLevelType = $_POST['GroupLevelType'];
	
	$add_cohorts_groups = $icq_dashboard->add_cohorts_groups($group_name, $group_owner_name, $group_owner_email, $user_id, $post_id, $GroupLevelType);
	if ($add_cohorts_groups) wp_redirect(get_home_url() . '/dashboard/groups?status=added_successfully');
}


function do_edit_groups() {
	global $icq_dashboard;

	$group_name = $_POST['group_name'];
	$group_owner_name = $_POST['group_owner_name'];
	$group_owner_email = $_POST['group_owner_email'];
	$group_id = $_POST['group_id'];
	$GroupLevelType = $_POST['GroupLevelType'];
	
	$edit_cohorts_groups = $icq_dashboard->edit_cohorts_groups($group_id, $group_name, $group_owner_name, $group_owner_email, $GroupLevelType);
	if ($edit_cohorts_groups) wp_redirect(get_home_url() . '/dashboard/groups?status=changed_successfully');
}

function do_add_departments() {
	global $icq_dashboard, $wpdb;

	// All $_POST is array
	$department_name = $_POST['department_name'];
	$user_id = get_current_user_id();
	$post_id = $_POST['post_id'];
	
	$add_cohorts_department = $wpdb->insert( 'wp_departments', array( 'id' => 0, 'department_name' => $department_name ), array( '%d', '%s' ) );
	
	//$icq_dashboard->add_cohorts_departments($department_name);
	wp_redirect(get_home_url() . '/dashboard/department?status=added_successfully');
}
function do_edit_departments() {
	global $icq_dashboard, $wpdb;

	$department_name = $_POST['department_name'];
	$department_id = $_POST['department_id'];
	
	$edit_cohorts_department = $wpdb->update( 'wp_departments', array( 'department_name' => $department_name ), array( 'id' => $department_id ), array( '%s' ), array( '%d' ) );
	//$edit_cohorts_groups = $icq_dashboard->edit_cohorts_groups($group_id, $group_name, $group_owner_name, $group_owner_email);
	wp_redirect(get_home_url() . '/dashboard/department?status=changed_successfully');
	//if ($edit_cohorts_department) wp_redirect(get_home_url() . '/dashboard/department?status=changed_successfully');
}

function do_add_trainer_users() {
	global $icq_dashboard;

	$trainer_name = $_POST['username'];
	$trainer_email = $_POST['useremail'];
	$user_id = get_current_user_id();
	
	$trainer_added_result = $icq_dashboard->add_trainer_users($trainer_name, $trainer_email, $user_id);
	
	if ($trainer_added_result == 'complete') wp_redirect(get_home_url() . '/dashboard/users?status=added_successfully');
	else wp_redirect(get_home_url() . '/dashboard/users?error=' . $trainer_added_result);
}

function do_edit_trainer_users() {
	global $icq_dashboard;

	$trainer_added_id = $_POST['taid'];
	$trainer_name = $_POST['username'];
		
	$trainer_added = $icq_dashboard->edit_trainer_users($trainer_added_id, $trainer_name);
	if ($trainer_added) wp_redirect(get_home_url() . '/dashboard/users?status=changed_successfully');
}


function do_add_mttusers() {
	global $icq_dashboard, $wpdb, $icq_register;

	$trainer_name = $_POST['mttname'];
	$trainer_email = $_POST['mttemail'];
	$userrole =$_POST['userrole'];
	$user_id = get_current_user_id();
	
	$reference_code = generateRandomString(10);
	$wpdb->insert('wp_mt_trainer_added_users', array(
			'user_id' => $user_id,
			'name' => $trainer_name,
			'email' => $trainer_email,
			'reference_code' => $reference_code,
			'added_date' => date('Y-m-d h:i:s'),
			'deleted' => 0,
			'userrole' => $userrole
		)
	);
	$last_insert_id = $wpdb->insert_id;

	if($userrole == "trainer")
	{
		$return_url = get_home_url() . '/trainer-register';
	}
	else
	{
		$return_url = get_home_url() . '/licensee-register';
	}
	
	//$icq_dashboard->send_email_to_trainer($trainer_email, $trainer_name, $return_url, $last_insert_id, $reference_code);
	
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	$headers[] = 'From: ICQ Global <help@icq.global>' . "\r\n";
	$subject = 'Well done!';

	$encrypted = $icq_register->secret_key_encode($last_insert_id);			
	$message = 'Dear ' . $trainer_name . ',<br /><br />'.
				'Congratulation on becoming a Global DISC<sup>TM</sup> trainer and coach.<br />'.
				'You can set up your online portal here :<br/><a href="' . $return_url . '?code=' . $encrypted . '&ref=' . $reference_code . '">' . $return_url . '?code=' . $encrypted . '&ref=' . $reference_code . '</a><br /><br />'.
				'Regards,<br />'.
				'ICQ Global';

	wp_mail( $trainer_email, $subject, $message, '' );
	
	wp_redirect(site_url() . '/dashboard/mtusers/?status=added_successfully');
	
	//$trainer_added_result = $icq_dashboard->add_mt_trainer_users($trainer_name, $trainer_email, $user_id);
	//if ($trainer_added_result == 'complete') wp_redirect(get_home_url() . '/dashboard/mtusers?status=added_successfully');
	//else wp_redirect(get_home_url() . '/dashboard/mtusers?error=' . $trainer_added_result);
}

function do_edit_mttusers() {
	global $icq_dashboard, $wpdb;

	$trainer_name = $_POST['mttname'];
	$trainer_email = $_POST['mttemail'];
	$mtid = $_POST['mtid'];
	
	$edit_cohorts_department = $wpdb->update( 'wp_mt_trainer_added_users', array( 'name' => $trainer_name, 'email' => $trainer_email ), array( 'id' => $mtid ), array( '%s', '%s' ), array( '%d' ) );
	
	wp_redirect(get_home_url() . '/dashboard/mtusers?status=changed_successfully');
}

function get_customer_orders($meta_value){
	global $woocommerce;

	$customer_orders = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
			'numberposts' => $order_count,
			'meta_key'    => '_customer_user',
			'meta_value'  => $meta_value,
			'post_type'   => wc_get_order_types( 'view-orders' ),
			'post_status' => array_keys( wc_get_order_statuses() ),
		) ) );

	return $customer_orders;
}


function order_status_validate($order_status) {
	$order_status_validate = array(	
							// 'pending payment', 
							// 'failed', 
							'processing', 
							'completed', 
							// 'on-hold', 
							// 'cancelled', 
							// 'refunded'
						);

	$status_disable = false;
	foreach($order_status_validate as $key => $val){
		if( $val == $order_status)
			$status_disable = true;
	}

	return $status_disable;
}

remove_action( 'woocommerce_account_view-order_endpoint', 'woocommerce_account_view_order' );

add_action( 'woocommerce_account_view-order_endpoint', 'view_order');
function view_order($order_id) {
	global $icq_dashboard;
	
	$order   = wc_get_order($order_id);
	$user_id = get_current_user_id();
	$user_relations = $icq_dashboard->get_user_relations($user_id);
	$customer_id = $order->user_id;

	$current_user = wp_get_current_user();
	$current_user_roles = $current_user->roles;

	if ($user_id == $customer_id || (in_array('licensee', $current_user_roles) || in_array('administrator', $current_user_roles))) {
		$status       = new stdClass();
		$status->name = wc_get_order_status_name( $order->get_status() );

		wc_get_template( 'myaccount/view-order.php', array(
			'status'    => $status, // @deprecated 2.2
			'order'     => wc_get_order( $order_id ),
			'order_id'  => $order_id,
		) );
	}else {
		$html = '<p class="text-center cfDisable font-16 Bold">No Order Display.</p>';
		$html .= '<div class="col-xs-12 text-center mt-15"><a class="button btnPrimary" href="'.get_home_url().'/dashboard/history">Go to Dashboard</a>';
		echo $html;
	}
}

add_action('delete_user', 'remove_licensee_and_trainer_added');
function remove_licensee_and_trainer_added($user_id) {
    global $wpdb;
	
	$results = $wpdb->get_results("DELETE FROM wp_licensee_trainer_relations WHERE licensee_id = '" . $user_id . "' OR trainer_id = '". $user_id . "'");
	
	/*
	$user_obj = get_userdata($user_id);
    $email = $user_obj->user_email;
	$results = $wpdb->get_results("DELETE FROM wp_trainer_added_users WHERE email = '" . $email . "'");
	*/
}

add_filter('woocommerce_new_customer_data', 'woocommerce_icq_assign_custom_role', 10, 1);
function woocommerce_icq_assign_custom_role($args) {
	$args['role'] = 'reseller';
	return $args;
}


function remove_backslash($str) {
	return str_replace('\\', '', $str);
	
}

function _custom_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Language Switcher', '_tk' ),
		'id'            => 'lan-1',
        'description'   => __( 'Appears in the section of the site.', '_tk' ),
		'before_widget' => '<div class="lan-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '',
		'after_title'   => '',
	) );
}
add_action( 'widgets_init', '_custom_widgets_init' );

add_action( 'wp_ajax_nopriv_bulk_upload_participant', 'bulk_upload_participant' );
add_action( 'wp_ajax_bulk_upload_participant', 'bulk_upload_participant' );
function bulk_upload_participant() {
	$group_id = $_POST['group_id'];
	if(isset($_FILES['file']['name'][0]))
	{
		// Upload File Coding For CSV Files Items ----------------------
		$FilesItems = $_FILES['file']['name'][0];
		$FilesItems1 = array();
		
		if($_FILES['file']['name'][0] == "" || $_FILES['file']['name'][0] == NULL)
		{
			$FilesItems1 = "";
		}
		else
		{
			$FilesName = array();
			$fileCount = count($_FILES['file']['name']);
			for ($i = 0; $i < $fileCount; $i++)
			{
				$p = strrpos($_FILES['file']["name"][$i],'.');
				$extn = substr($_FILES['file']["name"][$i],$p);
				
				$path = __DIR__.'/bulk-csv/';
				$imageid = date("mdHis");
				$imagename = str_replace(" ","",$imageid."_".$_FILES['file']["name"][$i]);
				$fullpath=$path."/".$imagename;
				$dbpath=$imagename;
				move_uploaded_file($_FILES['file']['tmp_name'][$i], $fullpath);
				array_push($FilesName,$imagename);
				
				$FilePath = site_url()."/wp-content/themes/icq/bulk-csv/".$imagename;
				$file = fopen($FilePath, "r");
				$cnt = 1;
				$status = "";
				if($extn == ".csv")
				{
					while (($column = fgetcsv($file, 10000, ",")) !== FALSE)
					{
						global $wpdb;
						if($cnt != 1)
						{
							$date = date('Y-m-d');
							$newusername = $column[0];
							$newuseremailaddress = $column[1];
							$newuserdepartment = $column[2];
							$CurrentUserID = get_current_user_id();
							?>
							<div class="input_particapants_data_row row adding">
								<div class="input_particapants_data_field col-xs-11">
									<div class="row">
										<div class="col-xs-4">
											<input type="text" class="form-control" placeholder="name" value="<?php echo $newusername; ?>" name="participant_name[]">
										</div>
										<div class="col-xs-4">
											<input type="email" class="form-control" placeholder="Email Address" name="participant_email[]" value="<?php echo $newuseremailaddress; ?>">
										</div>
										<div class="col-xs-4">
											<div class="form-group">
											<select class="form-control js-select-cover" name="participant_department[]">
												<?php echo depertment_dd($newuserdepartment); ?>
											</select>
										  </div>
										</div>
									</div>
								</div>
								<div class="col-xs-1 text-center">
									<a href="#" class="font-36 Semibold cDefault delete nonunderline">×</a>
								</div>
							</div>
							<?php
						}
						$cnt++;
					}
				}
				elseif($extn == ".xlsx")
				{
					$Fpath = __DIR__.'/bulk-csv/'.$imagename;
					require_once __DIR__.'/SimpleXLSX.php';
					if ( $xlsx = SimpleXLSX::parse($Fpath)) {
						$data = $xlsx->rows();
						$n = count($data);
						for($i=0;$i<$n;$i++)
						{
							if($i != 0)
							{
								$newusername = $data[$i][0];
								$newuseremailaddress = $data[$i][1];
								$newuserdepartment = $data[$i][2];
								?>
								<div class="input_particapants_data_row row adding">
									<div class="input_particapants_data_field col-xs-11">
										<div class="row">
											<div class="col-xs-4">
												<input type="text" class="form-control" placeholder="name" value="<?php echo $newusername; ?>" name="participant_name[]">
											</div>
											<div class="col-xs-4">
												<input type="email" class="form-control" placeholder="Email Address" name="participant_email[]" value="<?php echo $newuseremailaddress; ?>">
											</div>
											<div class="col-xs-4">
												<div class="form-group">
												<select class="form-control js-select-cover" name="participant_department[]">
													<?php echo depertment_dd($newuserdepartment); ?>
												</select>
											  </div>
											</div>
										</div>
									</div>
									<div class="col-xs-1 text-center">
										<a href="#" class="font-36 Semibold cDefault delete nonunderline">×</a>
									</div>
								</div>
								<?php
							}
						}
					}
				}
				elseif($extn == ".xls")
				{
					$Fpath = __DIR__.'/bulk-csv/'.$imagename;
					require_once __DIR__.'/SimpleXLS.php';
					if ( $xls = SimpleXLS::parse($Fpath)) {
						$data = $xls->rows();
						$ndata = count($data);
						for($j=0;$j<$ndata;$j++)
						{
							if($j != 0)
							{
								if(!empty($data[$j][0]) || !empty($data[$j][1]) || !empty($data[$j][2]))
								{
									$newusername = $data[$j][0];
									$newuseremailaddress = $data[$j][1];
									$newuserdepartment = $data[$j][2];
									?>
									<div class="input_particapants_data_row row adding">
										<div class="input_particapants_data_field col-xs-11">
											<div class="row">
												<div class="col-xs-4">
													<input type="text" class="form-control" placeholder="name" value="<?php echo $newusername; ?>" name="participant_name[]">
												</div>
												<div class="col-xs-4">
													<input type="email" class="form-control" placeholder="Email Address" name="participant_email[]" value="<?php echo $newuseremailaddress; ?>">
												</div>
												<div class="col-xs-4">
													<div class="form-group">
													<select class="form-control js-select-cover" name="participant_department[]">
														<?php echo depertment_dd($newuserdepartment); ?>
													</select>
												  </div>
												</div>
											</div>
										</div>
										<div class="col-xs-1 text-center">
											<a href="#" class="font-36 Semibold cDefault delete nonunderline">×</a>
										</div>
									</div>
									<?php
								}
							}
						}
					}
				}
			}
		}
	}
	die();
}

function depertment_dd ($newuserdepartment){
	$html = '';
	global $wpdb;
	$DepartmentResults = $wpdb->get_results("SELECT id, department_name FROM wp_departments WHERE deleted = 0 ORDER BY id DESC");
	foreach ( $DepartmentResults as $Department ) 
	{
		if($newuserdepartment == $Department->department_name)
		{
			$selected = 'Selected';
		}
		else
		{
			$selected = '';
		}
		$html .= '<option value="'.$Department->department_name.'" '.$selected.'>'.$Department->department_name.'</option>';

	}
	echo $html;
}

add_action( 'wp_ajax_nopriv_participant_transfer_to_group', 'participant_transfer_to_group' );
add_action( 'wp_ajax_participant_transfer_to_group', 'participant_transfer_to_group' );
function participant_transfer_to_group() {
	global $wpdb;
	$transferid = $_POST['transferid'];
	$groupid = $_POST['groupid'];
	$user_id = get_current_user_id();
	$user = get_userdata( $user_id );
	$user_roles = $user->roles;
	if (in_array('trainer', $user_roles, true) || in_array('licensee', $user_roles, true)) {
		$wpdb->update('wp_individual_urls', array('cohort_group_id' => $groupid), array('id' => $transferid), array('%d'), array('%d'));
		echo '1';
	}
	else
	{
		echo '0';
	}
	die();
}


add_action( 'wp_ajax_nopriv_participant_pagination_list', 'participant_pagination_list' );
add_action( 'wp_ajax_participant_pagination_list', 'participant_pagination_list' );
function participant_pagination_list() {
	global $wpdb, $icq_dashboard;
	
	$group_id = $_POST['gid'];
	$limit = $_POST['pageLimit'];  
	if (isset($_POST["page"])) { $page  = $_POST["page"]; } else { $page=1; };  
	$start_from = ($page-1) * $limit;  

	$participant_added = $icq_dashboard->get_participant_added_by_group_id_with_page($group_id, $start_from, $limit);
	$used_counter = 0;
	foreach ($participant_added as $idx => $p_added) {
		if($p_added->used == 1) {								
			$submission = $icq_dashboard->get_submission_info($p_added->code);
			$used_counter++;
		} 
		?>
		<div class="table_row_data row participant-row-<?php echo $p_added->id; ?> <?php if($p_added->soft_delete == 1) echo 'row-disabled'; ?>">
			<div class="border_table_row">
				<div class="col-xs-3 table_data">
					<?php
						$user_participant = get_user_by( 'email', $p_added->participant_email );
						if ($user_participant->ID == NULL) $user_participant_id = 'unregister';
						else $user_participant_id = $user_participant->ID;
					?>
					<span class="participant-name"><?php echo $p_added->participant_name; ?></span>
				</div>
				<div class="col-xs-4 table_data text-left"><span class="participant-email"><?php echo $p_added->participant_email; ?></span></div>
				<div class="col-xs-2 table_data text-left"><?php echo $p_added->participant_department; ?></div>
				<div class="col-xs-2 table_data text-center result">
					<?php
						if($p_added->used == 1) { 
							echo $submission->personal_output_result;
						}else { ?>
							<button class="bntInfo Regular font-14" onclick="disabledBtn(this); send_email_to_participant('<?php echo $p_added->id; ?>', '<?php echo $p_added->participant_name; ?>', '<?php echo $p_added->participant_email; ?>', '<?php echo $p_added->participant_department; ?>', '<?php echo $p_added->url; ?>', '<?php echo plugins_url(); ?>')">Send reminder</button>
					<?php 
						} 
					?>
				</div>
				<div class="col-xs-1 table_data text-right participants-row-menu">
					<?php if($p_added->used == 1) { ?>
							<input type="radio" id="participant-chbk-<?php echo $p_added->id; ?>" name="participant_compared_with" class="compared-participant" style="display:none" value="<?php echo $p_added->id; ?>" />
							<a href="javascript:void(0);" class="table_data_action" data-id="<?php echo $p_added->id; ?>" data-name="<?php echo $p_added->participant_name; ?>" data-email="<?php echo $p_added->participant_email; ?>" data-department="<?php echo $p_added->participant_department; ?>" data-url="<?php echo $p_added->url; ?>" data-report-url="<?php echo $submission->pdf_path; ?>" data-plugins-url="<?php echo plugins_url(); ?>" data-gid="<?php echo $group_id; ?>"><i class="fa fa-ellipsis-h"></i></a>
					<?php }else { ?>
							<a href="javascript:void(0);" class="table_data_action_no_submission" data-id="<?php echo $p_added->id; ?>" data-name="<?php echo $p_added->participant_name; ?>" data-email="<?php echo $p_added->participant_email; ?>" data-department="<?php echo $p_added->participant_department; ?>" data-url="<?php echo $p_added->url; ?>" data-report-url="<?php echo $submission->pdf_path; ?>" data-plugins-url="<?php echo plugins_url(); ?>"><i class="fa fa-ellipsis-h"></i></a>
					<?php } ?>

				</div>
			</div>
		</div>
<?php }
	
	die();
}