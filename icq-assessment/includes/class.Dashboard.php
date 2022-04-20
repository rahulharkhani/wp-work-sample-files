<?php
class icqDashboardInit{

    public function __construct(){
        add_action('template_redirect', array($this, 'dashboard_rewrite_templates'));
        add_filter('query_vars', array($this, 'register_dashboard_query_vars'));
        add_filter('rewrite_rules_array', array($this, 'dashboard_rewrite_rule'));
    }

    public function dashboard_rewrite_rule($aRules){
        $aNewRules = array('dashboard/([a-z]+)/([A-Za-z0-9-]+)/([a-z]+)/?$' => 'index.php?dashboard=true&display=$matches[1]&secondpath=$matches[2]&admin_action=$matches[3]');
        $aRules = $aNewRules + $aRules;
        $aNewRules = array('dashboard/([a-z]+)/([A-Za-z0-9-]+)/?$' => 'index.php?dashboard=true&display=$matches[1]&secondpath=$matches[2]');
        $aRules = $aNewRules + $aRules;
        $aNewRules = array('dashboard/([a-z]+)/?$' => 'index.php?dashboard=true&display=$matches[1]');
        $aRules = $aNewRules + $aRules;
        $aNewRules = array('dashboard$' => 'index.php?dashboard=true');
        $aRules = $aNewRules + $aRules;
        return $aRules;
    }

    public function register_dashboard_query_vars($vars){
        $vars[] = 'dashboard';
        $vars[] = 'display';
        $vars[] = 'secondpath';
        $vars[] = 'admin_action';
        return $vars;
    }

    public function dashboard_rewrite_templates(){
        $dasboard = get_query_var('dashboard');
        $dashboard_display = get_query_var('display');
        if ($dasboard) {
            add_filter('template_include', function() {
                return get_template_directory() . '/dashboard/dashboard-layout.php';    
            });
        }
    }

	public function upload_user_profile_pic($file, $user_id){
		if (!$user_id) $user_id = get_current_user_id();
		$url = $this->upload_attachment($file);
		return $this->set_user_profile_pic($url, $user_id);	
	}

	/**
	*	example: uplaod_attachment($_FILES['upload_image_field']);
	*   return: url of uploaded images
	***/
	public function upload_attachment($file){
		require_once( ABSPATH . 'wp-admin/includes/admin.php' );
		$file_upload = wp_handle_upload($file, array('test_form' => false));
		if ( ! ( isset($file_upload['error']) || isset($file_upload['upload_error_handler']) ) ) {
			$name_and_type = explode(".", $file['name']);
			$attachment = array(
					'post_mime_type' => $file_upload['type'],
					'post_title' => $name_and_type[0],
					'post_status' => $data['inherit'],
					'guid' => $file_upload['url']
			);
			$arr_upload_path = wp_upload_dir();
			$modify_path = str_replace($arr_upload_path["baseurl"]."/", "", $file_upload['url'] );
			$attachment_id = wp_insert_attachment( $attachment, $modify_path );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file_upload['file'] );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );
			
			return $file_upload['url'];
		}
	}

	public function set_user_profile_pic($image_url, $user_id){
		if (!$user_id) $user_id = get_current_user_id();
		return update_user_meta($user_id, 'profile_thumbnail' , $image_url);
	}

	public function get_user_profile_pic($user_id = null){
		if (!$user_id) $user_id = get_current_user_id();
        if( get_user_meta( $user_id, "profile_thumbnail", true )){
			
			$profile_thumbnail_url = get_user_meta($user_id, 'profile_thumbnail', 1);
			
			if(isset($_SERVER['HTTPS']))
				$profile_thumbnail_url = str_replace('http:', 'https:', $profile_thumbnail_url);
			
            return $profile_thumbnail_url; 
			
        }else {
            return bloginfo('template_directory')."/assets/images/avatar.png";
        }
	}
	
	public function get_total_credit_urls_amount_() {
		global $wpdb;
		global $icq_report;
		
		$pmeta = $wpdb->get_results("SELECT * FROM " . $wpdb->postmeta . " WHERE meta_key='user_id' AND meta_value='" . get_current_user_id() . "'");
		$submission_num_total = 0;
		
		if (isset($pmeta)) {
			foreach($pmeta as $p) {
				$pmeta_assessment = get_post_meta($p->post_id, 'assessment', false);
				if ($pmeta_assessment[0] == $icq_report->current_assessment()) {
					$pmeta_generated_individual_urls_num = get_post_meta($p->post_id, 'generated_individual_urls_num', false);
					$generated_individual_urls_num_total += $pmeta_generated_individual_urls_num[0];
				}
			}
			
		}else {
			return false;
		}
		
		return $generated_individual_urls_num_total;
	}
	
	
	// Independence total credits and their participant added.
	public function get_total_credit_urls_amount() {
		global $wpdb;
		$credit_total = $wpdb->get_results("SELECT COUNT(id) AS total_credits FROM wp_individual_urls WHERE user_id = '" . get_current_user_id() . "'");
		
		return $credit_total[0]->total_credits;
	}
	
	public function get_participant_added() {
		global $wpdb;
		$participant_added_urls = $wpdb->get_results("SELECT * FROM wp_individual_urls WHERE user_id = '" . get_current_user_id() . "' AND participant_name <> '' AND participant_email <> '' AND participant_department <> '' AND soft_delete = '0'");
		
		return $participant_added_urls;
	}
	
	public function get_participant_added_with_department($department = 'All') {
		global $wpdb;
		
		if ($department == 'All')
			$participant_added_urls = $this->get_participant_added();
		else 
			$participant_added_urls = $wpdb->get_results("SELECT * FROM wp_individual_urls WHERE user_id = '" . get_current_user_id() . "' AND participant_name <> '' AND participant_email <> '' AND participant_department = '" . $department . "' AND soft_delete = '0'");
		
		return $participant_added_urls;
	}
	// -----------------------------------------------
	
	
	// Licensee total credits and their participant added.
	public function get_total_credit_urls_amount_for_licensee() {
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM wp_licensee_trainer_relations WHERE licensee_id = '" . get_current_user_id() . "'");
		if (count($results) > 0) {
			$results = $wpdb->get_results("SELECT * FROM wp_licensee_trainer_relations AS licensee_trainer_relations INNER JOIN wp_individual_urls AS individual_urls ON individual_urls.user_id = licensee_trainer_relations.licensee_id OR individual_urls.user_id = licensee_trainer_relations.trainer_id  WHERE licensee_trainer_relations.licensee_id = '" . get_current_user_id() . "' GROUP BY individual_urls.post_id");
			$total_credits = 0;
			foreach ($results as $r) {
				$total_credits += $r->generated_counter;
			}	
		
		}else {
			$results = $wpdb->get_results("SELECT * FROM wp_individual_urls WHERE user_id = '" . get_current_user_id() . "'");
			$total_credits = count($results);
		}
		
		return $total_credits;
	}
	
	public function get_participant_added_for_licensee($licensee_id = NULL) {
		global $wpdb;
		
		if ($licensee_id == NULL) $licensee_id = get_current_user_id();
		
		// Check if this licensee has relations with trainers
		$licensee_relations = $wpdb->get_results("SELECT * FROM wp_licensee_trainer_relations WHERE licensee_id = '" . $licensee_id . "'");
		if (!empty($licensee_relations)) { // So, This licensee has relations with trainers 
			$participant_added_urls = $wpdb->get_results("SELECT * FROM wp_licensee_trainer_relations INNER JOIN wp_individual_urls ON user_id = trainer_id OR user_id = '" . $licensee_id . "' WHERE licensee_id = '" . $licensee_id . "' GROUP BY wp_individual_urls.id");
		
		}else { // No relations for this licensee and trainers 
			$participant_added_urls = $wpdb->get_results("SELECT * FROM wp_individual_urls WHERE user_id = '" . $licensee_id . "'");
		}
		
		return $participant_added_urls;
	}
	
	// -----------------------------------------------

	
	// Trainer total credits and their participant added
	public function get_total_credit_urls_amount_for_trainer() {
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM wp_licensee_trainer_relations AS licensee_trainer_relations INNER JOIN wp_individual_urls AS individual_urls ON individual_urls.user_id = licensee_trainer_relations.licensee_id OR individual_urls.user_id = licensee_trainer_relations.trainer_id  WHERE licensee_trainer_relations.licensee_id IN (SELECT licensee_id FROM wp_licensee_trainer_relations WHERE trainer_id = '" . get_current_user_id() . "') GROUP BY individual_urls.post_id");
		
		$total_credits = 0;
		foreach ($results as $r) {
			$total_credits += $r->generated_counter;
		}
		return $total_credits;
	}
	
	public function get_licensee_id_of_trainer() {
		global $wpdb;
		$licensee_trainer_relation = $wpdb->get_row("SELECT * FROM wp_licensee_trainer_relations WHERE trainer_id = '" . get_current_user_id() . "'");
		$licensee_id = $licensee_trainer_relation->licensee_id;
		
		return $licensee_id;
	}
	
	public function get_participant_added_for_trainer() {
		global $wpdb;		
		$licensee_id = $this->get_licensee_id_of_trainer();
		$participant_added_urls = $wpdb->get_results("SELECT * FROM wp_licensee_trainer_relations INNER JOIN wp_individual_urls ON user_id = trainer_id OR user_id = '" . $licensee_id . "' WHERE licensee_id = '" . $licensee_id . "' GROUP BY wp_individual_urls.id");
		
		return $participant_added_urls;
	}
	// -----------------------------------------------
	
	public function get_credit_used($participant_added_urls) {
		$counter = 0;
		foreach ($participant_added_urls as $p_url) {
			if ($p_url->participant_email != '') {
				$counter++;
			}
		}
		return $counter;
	}	
	
	// Independence users use this function.
	public function add_participants($participant_name_arr, $participant_email_arr, $participant_department_arr) {
		global $wpdb;		
		$results = $wpdb->get_results("SELECT * FROM wp_individual_urls WHERE user_id = '" . get_current_user_id() . "' AND participant_name = '' AND participant_email = '' AND participant_department = '' ORDER BY id ASC");
		if (count($results) > 0) {
			foreach ($results as $i => $r) {
				if ($participant_email_arr[$i] != '') {
					$participant_email_exists = $wpdb->get_row("SELECT * FROM wp_individual_urls WHERE participant_email = '" . $participant_email_arr[$i] . "' AND user_id = '" . get_current_user_id() . "'");
					if (empty($participant_email_exists)) {
						$wpdb->get_results( $wpdb->prepare( "UPDATE wp_individual_urls SET participant_name = %s, participant_email = %s, participant_department = %s, soft_delete = 0 WHERE id = %d", $participant_name_arr[$i], $participant_email_arr[$i], $participant_department_arr[$i], $r->id ) );
						$this->send_email_to_participant($r->id, $participant_email_arr[$i], $participant_name_arr[$i], $r->url);
						
					}else {
						return 'existing-email';
					}
				}
			}			
		}else {
			return 'not-found';
		}
		
		return 'complete';		
	}
	
	// Individual total URLs from user_id - Only use in Admin user-edit.php page.
	public function getAllCreditsForOneUser($user_id) {
		global $wpdb;		
		$results = $wpdb->get_results("SELECT * FROM wp_individual_urls WHERE user_id = '" . $user_id . "'");
		$total_credits = count($results);
		
		return $total_credits;
	}
	
	
	public function get_submission_info($indcode) {
		global $wpdb;
		$user_submission = $wpdb->get_row("SELECT * FROM wp_users_submissions WHERE individual_url_code = '" . $indcode . "'");
		
		return $user_submission;
	}

	public function send_email_to_participant($id, $email, $name, $url) {
		global $wpdb;
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: ICQ Global <help@icq.global>' . "\r\n";
		$subject = 'Global DISC™ assessment';
				
		$message = 'Dear ' . $name . ',<br /><br />'.
					'You are invited to take the Global DISC<sup>TM</sup> assessment.<br />Don’t worry, it takes only 3 minutes and you are going to find out why you and others think and behave differently, indeed, how you can turn those differences into synergy instead of painful liability.<br />'.
					'You can access the survey by clicking here :<br/><a href="' . $url . '">' . $url . '</a><br /><br />'.
					'Regards,<br />'.
					'ICQ Global';
		
		$send = wp_mail( $email, $subject, $message, $headers );
		if ($send) $wpdb->get_results( $wpdb->prepare( "UPDATE wp_individual_urls SET email_sent = 1 WHERE id = %d", $id ) );
	}

    public function chk_current_password($id,$pass){
        
        $user_data = get_user_by('ID',$id);        

		$password_hashed = $user_data->user_pass;
		$plain_password = $pass;

		if(wp_check_password($plain_password, $password_hashed , $id)) {
		    echo true;
		} else {
		    echo false;
		}
    }
	
	public function add_cohorts_groups($group_name, $group_owner_name, $group_owner_email, $user_id, $post_id, $GroupLevelType) {
		global $wpdb;
		$wpdb->insert('wp_cohorts_groups', array(
				'group_name' => $group_name,
				'group_owner_name' => $group_owner_name,
				'group_owner_email' => $group_owner_email,
				'user_id' => $user_id,
				'post_id' => $post_id,
				'grouplevel' => $GroupLevelType
			)
		);
		
		$this->addActivityLog('add_cohort_group', get_current_user_id(), $group_name); // Add Activity Log
		return true;
	}
	
	public function check_email_in_users_list($group_owner_email, $user_id) {
		global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM wp_trainer_added_users WHERE email = '" . $group_owner_email . "' AND user_id = '" . $user_id . "'");
		
		return $result;
	}
	
	public function edit_cohorts_groups($id, $group_name, $group_owner_name, $group_owner_email, $GroupLevelType) {
		global $wpdb;
		
		$wpdb->update('wp_cohorts_groups', array(
				'group_name' => $group_name,
				'group_owner_name' => $group_owner_name,
				'group_owner_email' => $group_owner_email,
				'grouplevel' => $GroupLevelType),
				array('id' => $id));
		
		$this->addActivityLog('edit_cohort_group', get_current_user_id(), $group_name); // Add Activity Log
		
		return true;
	}
	
	public function add_mt_trainer_users($name, $email, $user_id) {
		global $wpdb;
		
		$added_user = get_user_by('email', $email);
		$added_user_roles = $added_user->roles;
		
		$reference_code = generateRandomString(10);
		
		//if (empty($trainer_email_exists)) {
			$wpdb->insert('wp_mt_trainer_added_users', array(
					'user_id' => $user_id,
					'name' => $name,
					'email' => $email,
					'reference_code' => $reference_code,
					'added_date' => date('Y-m-d h:i:s'),
					'deleted' => 0
				)
			);
			
			$last_insert_id = $wpdb->insert_id;
			
			$user_exists = get_user_by('email', $email);
			
			if (!empty($user_exists)) {
				$wpdb->insert('wp_mt_trainer_relations', array(
					'mt_id' => $user_id,
					'trainer_id' => $user_exists->ID
					)
				);
				
				update_user_meta($user_exists->ID, 'wp_capabilities', 'a:1:{s:8:\"trainer\";b:1;}');
				
				$return_url = get_home_url() . '/register';
				$this->send_email_to_existing_trainer($email, $name, $return_url, $last_insert_id, $reference_code);
				
				
			}else {
				$return_url = get_home_url() . '/trainer-register';
				$this->send_email_to_trainer($email, $name, $return_url, $last_insert_id, $reference_code);
			}
		
		//}else {
			//return 'existing-email';
		//}
		
		$this->addActivityLog('add_trainer_user', get_current_user_id(), $name); // Add Activity Log
		return 'complete';
	}
	
	public function add_trainer_users($name, $email, $user_id) {
		global $wpdb;
		
		$added_user = get_user_by('email', $email);
		$added_user_roles = $added_user->roles;
		
		// if this email has been already in the database, it means this email was registered with the relations between licensee & trainer was created, So we return this email was used.
		if (in_array('licensee', $added_user_roles)) {
			return 'already-licensee-email';
		}
		
		if (in_array('reseller', $added_user_roles)) {
			return 'already-independence-email';
		}
		
		//$trainer_email_exists = $wpdb->get_row("SELECT * FROM wp_trainer_added_users WHERE email = '" . $email . "'");
		$reference_code = generateRandomString(10);
		
		//if (empty($trainer_email_exists)) {
			$wpdb->insert('wp_trainer_added_users', array(
					'user_id' => $user_id,
					'name' => $name,
					'email' => $email,
					'reference_code' => $reference_code,
					'added_date' => date('Y-m-d h:i:s')
				)
			);
			
			$last_insert_id = $wpdb->insert_id;
			
			$user_exists = get_user_by('email', $email);
			
			if (!empty($user_exists)) {
				$wpdb->insert('wp_licensee_trainer_relations', array(
					'licensee_id' => $user_id,
					'trainer_id' => $user_exists->ID
					)
				);
								
				update_user_meta($user_exists->ID, 'wp_capabilities', 'a:2:{s:7:\"trainer\";b:1;s:8:\"reseller\";b:1;}');
				
				$return_url = get_home_url() . '/register';
				$this->send_email_to_existing_trainer($email, $name, $return_url, $last_insert_id, $reference_code);
				
				
			}else {
				$return_url = get_home_url() . '/trainer-register';
				$this->send_email_to_trainer($email, $name, $return_url, $last_insert_id, $reference_code);
			}
		
		//}else {
			//return 'existing-email';
		//}
		
		$this->addActivityLog('add_trainer_user', get_current_user_id(), $name); // Add Activity Log
		
		return 'complete';
	}
	
	public function edit_trainer_users($id, $name) {
		global $wpdb;
		$wpdb->update('wp_trainer_added_users', array('name' => $name), array('id' => $id));
		
		$this->addActivityLog('edit_trainer_user', get_current_user_id(), $name); // Add Activity Log
		
		return true;
	}
	
	public function get_trainer_added_users() {
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM wp_trainer_added_users WHERE user_id = '" . get_current_user_id() . "' AND deleted = 0 ORDER BY id DESC");
		
		return $results;
	}
	
	public function get_trainer_added_by_id($trainer_added_id) {
		global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM wp_trainer_added_users WHERE id = '" . $trainer_added_id . "' AND user_id = '" . get_current_user_id() . "'");
		
		return $result;
	}
	
	public function send_email_to_trainer($email, $name, $return_url, $taid, $reference_code) {
		global $wpdb;
		global $icq_register;
		
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: ICQ Global <help@icq.global>' . "\r\n";
		$subject = 'Well done!';
				
		$encrypted = $icq_register->secret_key_encode($taid);			
		$message = 'Dear ' . $name . ',<br /><br />'.
					'Congratulation on becoming a Global DISC<sup>TM</sup> trainer and coach.<br />'.
					'You can set up your online portal here :<br/><a href="' . $return_url . '?code=' . $encrypted . '&ref=' . $reference_code . '">' . $return_url . '?code=' . $encrypted . '&ref=' . $reference_code . '</a><br /><br />'.
					'Regards,<br />'.
					'ICQ Global';
		
		$send = wp_mail( $email, $subject, $message, '' );
	}
	
	public function send_email_to_existing_trainer($email, $name, $return_url, $taid, $reference_code) {
		global $wpdb;
		global $icq_register;
		
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: ICQ Global <help@icq.global>' . "\r\n";
		$subject = 'Well done!';
				
		$encrypted = $icq_register->secret_key_encode($taid);			
		$message = 'Dear ' . $name . ',<br /><br />'.
					'Congratulation on becoming Global DISC<sup>TM</sup> trainer and coach.<br />'.
					'You can set up your online portal here :<br/><a href="' . $return_url . '?code=' . $encrypted . '&ref=' . $reference_code . '">' . $return_url . '?code=' . $encrypted . '&ref=' . $reference_code . '</a><br /><br />'.
					'Regards,<br />'.
					'ICQ Global';
		
		$send = wp_mail( $email, $subject, $message, '' );
	}
	
	public function get_user_relations($licensee_id) {
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM wp_licensee_trainer_relations WHERE licensee_id = '" . $licensee_id . "' ORDER BY id DESC");
		
		return $results;
	}
	
	public function get_licensee_cohorts_groups($user_id) {
		global $wpdb;
		$sql_command = "SELECT *, cohorts_groups.id AS cohort_group_id FROM wp_cohorts_groups AS cohorts_groups LEFT OUTER JOIN wp_licensee_trainer_relations AS licensee_trainer_relations ON cohorts_groups.user_id = licensee_trainer_relations.licensee_id OR cohorts_groups.user_id =  licensee_trainer_relations.trainer_id WHERE ((licensee_trainer_relations.trainer_id = cohorts_groups.user_id AND licensee_trainer_relations.licensee_id = '" . $user_id . "') OR cohorts_groups.user_id = '" . $user_id . "') AND deleted = 0 GROUP BY cohorts_groups.id ";
		
		$key = $_REQUEST['sortby'];
		$orderby = $_REQUEST['orderby'];
		
		if (isset($key)) $sql_command .= " ORDER BY ".$key." ".$orderby;
		else $sql_command .= " ORDER BY cohort_group_id DESC";
		
		$results = $wpdb->get_results($sql_command);
		
		return $results;
	}
	
	public function get_cohorts_groups_by_user_id($user_id) {
		global $wpdb;
		$sql_command = "SELECT *, id AS cohort_group_id FROM wp_cohorts_groups WHERE user_id = '" . $user_id . "' AND deleted = 0 ";
		
		$key = $_REQUEST['sortby'];
		$orderby = $_REQUEST['orderby'];
		
		if (isset($key)) $sql_command .= " ORDER BY ".$key." ".$order;
		else $sql_command .= " ORDER BY cohort_group_id DESC";
		
		$results = $wpdb->get_results($sql_command);
		
		return $results;
	}
	
	public function get_cohort_group_data($group_id) {
		global $wpdb;		
		$user = wp_get_current_user();
		$user_roles = $user->roles;
		
		if (in_array('administrator', $user_roles)) {
			$result = $wpdb->get_row("SELECT * FROM wp_cohorts_groups WHERE id = '" . $group_id . "'");
		
		}else if (in_array('licensee', $user_roles)) {
			$result = $wpdb->get_row("SELECT * FROM wp_cohorts_groups WHERE id = '" . $group_id . "' AND (user_id = '" . get_current_user_id()  . "' OR user_id IN (SELECT trainer_id FROM wp_licensee_trainer_relations WHERE licensee_id = '" . get_current_user_id() . "') ) GROUP BY id");
		
		}else if (in_array('trainer', $user_roles)) {
			$result = $wpdb->get_row("SELECT * FROM wp_cohorts_groups WHERE id = '" . $group_id . "' AND user_id = '" . get_current_user_id()  . "'");
		
		} 
		
		return $result;
	}
	
	public function get_cohort_group_by_id($group_id) {
		global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM wp_cohorts_groups WHERE id = '" . $group_id . "'");
		
		return $result;
	}
	
	public function get_cohort_group_by_name($group_name) {
		global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM wp_cohorts_groups WHERE group_name = '" . $group_name . "'");
		
		return $result;
	}
	
	public function add_participants_in_cohort_group($participant_name_arr, $participant_email_arr, $participant_department_arr, $group_id) {
		global $wpdb;
		$_licensee_id;
		$user = wp_get_current_user();
		$user_roles = $user->roles;
		
		// Check credit left
		if (in_array('licensee', $user_roles)) {
			$total_credit = $this->get_total_credit_urls_amount_for_licensee();
			$participant_added = $this->get_participant_added_for_licensee();
			$credit_used = $this->get_credit_used($participant_added);
			$credit_left = $total_credit - $credit_used;
			
			$_licensee_id = get_current_user_id();
			$results = $wpdb->get_results("SELECT *, wp_individual_urls.id AS individual_url_id FROM wp_licensee_trainer_relations INNER JOIN wp_individual_urls ON user_id = trainer_id OR user_id = '" . $_licensee_id . "' WHERE licensee_id = '" . $_licensee_id . "' AND participant_name = '' AND participant_email = '' AND participant_department = '' GROUP BY wp_individual_urls.id ORDER BY wp_individual_urls.id ASC");
			if (count($results) == 0) {
				$results = $wpdb->get_results("SELECT *, id AS individual_url_id FROM wp_individual_urls WHERE user_id = '" . $_licensee_id . "' AND participant_name = '' AND participant_email = '' AND participant_department = '' ORDER BY id ASC");
			}
			
		}else if (in_array('trainer', $user_roles)) {
			$total_credit = $this->get_total_credit_urls_amount_for_trainer();
			$licensee_id = $this->get_licensee_id_of_trainer();
			$participant_added = $this->get_participant_added_for_licensee($licensee_id);
			$credit_used = $this->get_credit_used($participant_added);
			$credit_left = $total_credit - $credit_used;
			
			$results = $wpdb->get_results("SELECT *, wp_individual_urls.id AS individual_url_id FROM wp_licensee_trainer_relations INNER JOIN wp_individual_urls ON user_id = trainer_id OR user_id = '" . $licensee_id . "' WHERE licensee_id = '" . $licensee_id . "' AND participant_name = '' AND participant_email = '' AND participant_department = '' GROUP BY wp_individual_urls.id ORDER BY wp_individual_urls.id ASC");
		}
		
		if ($credit_left > 0) {
			foreach ($results as $i => $r) {
				if ($credit_left > 0) {
					if ($participant_email_arr[$i] != '') {
						$participant_email_exists = $wpdb->get_row("SELECT * FROM wp_individual_urls WHERE cohort_group_id = '" . $group_id . "' AND participant_email = '" . $participant_email_arr[$i] . "' ORDER BY id ASC");
						if (empty($participant_email_exists)) {						
							$wpdb->get_results( $wpdb->prepare( "UPDATE wp_individual_urls SET participant_name = %s, participant_email = %s, participant_department = %s, added_by_user_id = %s, cohort_group_id = %d, soft_delete = 0 WHERE id = %d", $participant_name_arr[$i], $participant_email_arr[$i], $participant_department_arr[$i], get_current_user_id(), $group_id, $r->individual_url_id ) );
							$this->send_email_to_participant($r->individual_url_id, $participant_email_arr[$i], $participant_name_arr[$i], $r->url);
							
							$credit_left--;
							
						}else {
							return 'existing-email';
						}
					}
					
				}else {
					return 'credit-full';
				}
			}
			
		}else {
			return 'credit-empty';
		}
		
		$participant_counter = 0;
		foreach ($participant_email_arr as $e) {
			if ($e != NULL) $participant_counter++;
		}
		
		$this->addActivityLog('add_participants', get_current_user_id(), $group_id . '-' . $participant_counter); // Add Activity Log
		
		return 'complete';
	}
	
	public function get_participant_added_by_group_id($gid, $department="All") {
		global $wpdb;
		
		if ($department == 'All') {
			$participant_added = $wpdb->get_results("SELECT id, post_id, url, code, date, used, generated_counter, user_id, participant_name, participant_email, participant_department, email_sent, soft_delete, cohort_group_id, added_by_user_id, (select group_name from wp_cohorts_groups where id = cohort_group_id) AS GroupName FROM wp_individual_urls AS individual_urls WHERE individual_urls.participant_name <> '' AND individual_urls.participant_email <> '' AND individual_urls.participant_department <> '' AND soft_delete = '0' AND cohort_group_id = " . $gid);
		}else {
			$participant_added = $wpdb->get_results("SELECT id, post_id, url, code, date, used, generated_counter, user_id, participant_name, participant_email, participant_department, email_sent, soft_delete, cohort_group_id, added_by_user_id, (select group_name from wp_cohorts_groups where id = cohort_group_id) AS GroupName FROM wp_individual_urls AS individual_urls WHERE individual_urls.participant_name <> '' AND individual_urls.participant_email <> '' AND individual_urls.participant_department = '" . $department . "' AND soft_delete = '0' AND cohort_group_id = " . $gid);
		}
		
		return $participant_added;
	}
	
	public function get_participant_added_by_group_id_paginate($gid, $department="All") {
		global $wpdb;
		
		if ($department == 'All') {
			$participant_added = $wpdb->get_results("SELECT id, post_id, url, code, date, used, generated_counter, user_id, participant_name, participant_email, participant_department, email_sent, soft_delete, cohort_group_id, added_by_user_id, (select group_name from wp_cohorts_groups where id = cohort_group_id) AS GroupName FROM wp_individual_urls AS individual_urls WHERE individual_urls.participant_name <> '' AND individual_urls.participant_email <> '' AND individual_urls.participant_department <> '' AND soft_delete = '0' AND cohort_group_id = ".$gid." LIMIT 0, 15");
		}else {
			$participant_added = $wpdb->get_results("SELECT id, post_id, url, code, date, used, generated_counter, user_id, participant_name, participant_email, participant_department, email_sent, soft_delete, cohort_group_id, added_by_user_id, (select group_name from wp_cohorts_groups where id = cohort_group_id) AS GroupName FROM wp_individual_urls AS individual_urls WHERE individual_urls.participant_name <> '' AND individual_urls.participant_email <> '' AND individual_urls.participant_department = '" . $department . "' AND soft_delete = '0' AND cohort_group_id = ".$gid." LIMIT 0, 15");
		}
		
		return $participant_added;
	}
	public function get_participant_added_by_group_id_with_page($gid, $start_from, $limit, $department="All") {
		global $wpdb;
		
		if ($department == 'All') {
			$participant_added = $wpdb->get_results("SELECT id, post_id, url, code, date, used, generated_counter, user_id, participant_name, participant_email, participant_department, email_sent, soft_delete, cohort_group_id, added_by_user_id, (select group_name from wp_cohorts_groups where id = cohort_group_id) AS GroupName FROM wp_individual_urls AS individual_urls WHERE individual_urls.participant_name <> '' AND individual_urls.participant_email <> '' AND individual_urls.participant_department <> '' AND soft_delete = '0' AND cohort_group_id = " . $gid ." LIMIT ".$start_from.", ".$limit);
		}else {
			$participant_added = $wpdb->get_results("SELECT id, post_id, url, code, date, used, generated_counter, user_id, participant_name, participant_email, participant_department, email_sent, soft_delete, cohort_group_id, added_by_user_id, (select group_name from wp_cohorts_groups where id = cohort_group_id) AS GroupName FROM wp_individual_urls AS individual_urls WHERE individual_urls.participant_name <> '' AND individual_urls.participant_email <> '' AND individual_urls.participant_department = '" . $department . "' AND soft_delete = '0' AND cohort_group_id = ".$gid." LIMIT ".$start_from.", ".$limit);
		}
		
		return $participant_added;
	}
	
	
	public function get_participant_added_by_id($id) {
		global $wpdb;
		
		$participant_added = $wpdb->get_results("SELECT * FROM wp_individual_urls WHERE id = " . $id);
		
		return $participant_added;
	}
	
	public function addActivityLog($activity_type, $user_id, $last_key_before_save = NULL) {
		global $wpdb;
		
		$wpdb->insert('wp_dashboard_activities', array(
				'user_id' => $user_id,
				'activity_type' => $activity_type,
				'last_key_before_save' => $last_key_before_save,
				'date_time_entered' => date('Y-m-d h:i:s')
			)
		);
	}
	
	public function getActivitiesLog() {
		global $wpdb;
		$user = wp_get_current_user();
		$user_roles = $user->roles;
		
		if (in_array('licensee', $user_roles)) {
			
			$user_id_arr = array();
			
			$user_id_arr[] = get_current_user_id();
			$results = $wpdb->get_results("SELECT * FROM wp_licensee_trainer_relations WHERE licensee_id = '" . get_current_user_id() . "'");
			if (count($results) > 0) {				
				foreach ($results as $r) {
					$user_id_arr[] = $r->trainer_id;
				}
				$user_id_arr_str = "'". implode("', '", $user_id_arr) ."'";
			
			}else {
				$user_id_arr_str = get_current_user_id();
			}
			
			$results = $wpdb->get_results("SELECT * FROM wp_dashboard_activities WHERE user_id IN (" . $user_id_arr_str . ") ORDER BY id DESC LIMIT 10");
			
			return $results;
			
		}else if (in_array('trainer', $user_roles)) {			
			$results = $wpdb->get_results("SELECT * FROM wp_dashboard_activities WHERE user_id IN (" . get_current_user_id() . ") ORDER BY id DESC LIMIT 10");
			
			return $results;
			
		}else if (in_array('administrator', $user_roles)) {
			$results = $wpdb->get_results("SELECT * FROM wp_dashboard_activities ORDER BY id DESC LIMIT 25");
			
			return $results;
		}
		
	}
	
}

$icq_dashboard = new icqDashboardInit();
