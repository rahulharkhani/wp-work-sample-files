<?php

class IcqReport{
	public function __construct(){
		
	}
	
	public function current_assessment(){
		return '70';
	}
	
	public function saveDistributions($order_id) {
		global $wpdb;
		
		$current_user = wp_get_current_user();
		$user_email = $current_user->user_email;
		
		$order = wc_get_order( $order_id );
		
		$c;
		foreach ( $order->get_items() as $item_id => $item_values ) {
			$product_id = $item_values->get_product_id();
			
			$product_post = get_post($product_id);
			$product_slug = $product_post->post_name;
			
			if (strpos($product_slug, 'team-report') !== false) { // Buy Team Report ******************************
			
				// Protected user refresh page
				$team_report_credit_order_id = get_user_meta($current_user->ID, 'team_report_credit_order_id', false);
				if (in_array($order_id, $team_report_credit_order_id)) return false;
				// -----------------------
				
				$user_id = $current_user->ID;
				
				$item_quantity = $item_values->get_quantity(); // Get the item quantity
				$product_amount = $item_quantity;
				
				$team_report_credit = get_user_meta($user_id, 'team_report_credit', true);
				
				if ($team_report_credit) {
					$current_team_report_credit = get_user_meta($user_id, 'team_report_credit', true);
					$update_team_report_credit =  $current_team_report_credit + $product_amount;
					update_user_meta($user_id, 'team_report_credit', $update_team_report_credit);
				
				}else {
					add_user_meta($user_id, 'team_report_credit', $product_amount, true);
					add_user_meta($user_id, 'team_report_credit_used', 0, true);
					add_user_meta($user_id, 'team_report_generated', 0, true);
					add_user_meta($user_id, 'team_report_generated_date', NULL, true);
				}
				
				add_user_meta($user_id, 'team_report_credit_order_id', $order_id);
				
			}else { // Buy Distributions ******************************
			
				// Protected user refresh page
				$user_unique_txt = 'user-' . $current_user->ID . '_' . $order_id . '-' . $product_id;
				$args = array('name'   => $user_unique_txt,
					'post_type'      => 'distributions',
					'post_status'    => 'publish',
					'posts_per_page' => 1
				);
				$_posts = get_posts($args);
				if (count($_posts) > 0) return false;
				// -----------------------
				
				
				// ------------- Add data to 'wp_posts' table -------------
				$post_arr = array(
					'post_title' => $user_email,
					'post_name' => $user_unique_txt,
					'post_status' => 'publish',
					'post_type' => 'distributions',
					'post_content' => ''
				);
				$post_id  = wp_insert_post($post_arr);
				// ---------------------------------------------------------
				
						
				// ------------- Add data to 'wp_individuals_url' table -------------
				$item_quantity = $item_values->get_quantity();
				$product_amount = $item_quantity;
				
				$assessment = get_field('assessment_name', $product_id);
				$tag_name = $user_unique_txt;
				
				$generated_rand_num = rand(1,1000000);  // It will be 'generated_id' field in 'wp_postmeta' table
				
				for ($j=0; $j<$product_amount; $j++) {				
					$random_num = generateRandomString(8); // It will be 'code' field in 'wp_individual_urls' table, It will be always changed to match with a single individual url.
					
					$pid = $wpdb->get_row("SELECT id FROM wp_individual_urls WHERE code = '" . $random_num . "'");
					while ($pid > 0) { // If random num is exists in database, So it will generate again and again...
						$random_num = generateRandomString(8);
						$pid = $wpdb->get_row("SELECT id FROM wp_individual_urls WHERE code = '" . $random_num . "'");
					}
					
					// Product URL Example : http://aicq.localhost/?tagname=localhost&assessment=70&type=individual&publish=3112018&code=3884044068
					
					$generated_url = get_home_url() . '?tagname=' . $tag_name . '&assessment=' . $assessment->ID . '&type=individual&publish=' . date('Ymd') . '&code=' . $generated_rand_num . '&indcode=' . $random_num;
					
					$wpdb->insert('wp_individual_urls', array(
						'post_id' => $post_id,
						'url' => $generated_url,
						'code' => $random_num,
						'date' => date('Y-m-d'),
						'used' => '0',
						'generated_counter' => $product_amount,
						'user_id' => $current_user->ID
					));	
					
				}
				// ---------------------------------------------------------
				
				
				// ------------- Add data to 'wp_post_meta' table -------------
				add_post_meta( $post_id, 'user_id', $current_user->ID, false );
				add_post_meta( $post_id, 'order_id', $order_id, false );
				add_post_meta( $post_id, 'generated_id', $generated_rand_num, false );
				add_post_meta( $post_id, 'tag_name', $tag_name, false );
				add_post_meta( $post_id, 'assessment', $assessment->ID, false ); // Will be removed in next version
				add_post_meta( $post_id, 'access_type', 'individual', false );
				add_post_meta( $post_id, 'generated_individual_urls_num', $product_amount, false );
				add_post_meta( $post_id, 'expire_date', '20301231', false );
				// ---------------------------------------------------------
				
				$c++;
			}
		}
		
		
		/*
		// Example : Iterating through each "line" items in the order
		foreach ($order->get_items() as $item_id => $item_data) {
			// Get an instance of corresponding the WC_Product object
			$product_id = $item_data->get_product_id();
			$product = $item_data->get_product();
			$product_name = $product->get_name(); // Get the product name
			$item_quantity = $item_data->get_quantity(); // Get the item quantity
			$item_total = $item_data->get_total(); // Get the item line total

			// Displaying this data (to check)
			//echo 'Product name: '.$product_name.' | Quantity: '.$item_quantity.' | Item total: '. number_format( $item_total, 2 );
		}
		*/
	}
	
	public function adminSaveDistributions($product_amount, $user_id, $assessment_id) {
		global $wpdb;
		global $icq_dashboard;
		
		$user = get_userdata($user_id);
		$user_email = $user->user_email;
		
		$total_credits = $icq_dashboard->getAllCreditsForOneUser($user->ID);
		$product_amount = $product_amount - $total_credits;
		
		if ($product_amount < 0) {
			echo 'Some mistake with amount number of this user credits';
			exit;
		}
		
		$user_unique_txt = 'user-' . $user_id . '_admin-added';
		
		// ------------- Add data to 'wp_posts' table -------------
		$post_arr = array(
			'post_title' => $user_email,
			'post_name' => $user_unique_txt,
			'post_status' => 'publish',
			'post_type' => 'distributions',
			'post_content' => ''
		);
		$post_id  = wp_insert_post($post_arr);
		// ---------------------------------------------------------
		
		$tag_name = $user_unique_txt;
		$generated_rand_num = rand(1,1000000);  // It will be 'generated_id' field in 'wp_postmeta' table
		
		for ($j=0; $j<$product_amount; $j++) {				
			$random_num = generateRandomString(8); // It will be 'code' field in 'wp_individual_urls' table, It will be always changed to match with a single individual url.
			
			$pid = $wpdb->get_row("SELECT id FROM wp_individual_urls WHERE code = '" . $random_num . "'");
			while ($pid > 0) { // If random num is exists in database, So it will generate again and again...
				$random_num = generateRandomString(8);
				$pid = $wpdb->get_row("SELECT id FROM wp_individual_urls WHERE code = '" . $random_num . "'");
			}
			
			$generated_url = get_home_url() . '?tagname=' . $tag_name . '&assessment=' . $assessment_id . '&type=individual&publish=' . date('Ymd') . '&code=' . $generated_rand_num . '&indcode=' . $random_num;
			
			$wpdb->insert('wp_individual_urls', array(
				'post_id' => $post_id,
				'url' => $generated_url,
				'code' => $random_num,
				'date' => date('Y-m-d'),
				'used' => '0',
				'generated_counter' => $product_amount,
				'user_id' => $user_id
			));
		}
		// -----------------------------------------------------------
		
		
		// ------------ Add data to 'wp_post_meta' table -------------
		add_post_meta( $post_id, 'user_id', $current_user->ID, false );
		add_post_meta( $post_id, 'order_id', $order_id, false );
		add_post_meta( $post_id, 'generated_id', $generated_rand_num, false );
		add_post_meta( $post_id, 'tag_name', $tag_name, false );
		add_post_meta( $post_id, 'assessment', $assessment_id, false ); // Will be removed in next version
		add_post_meta( $post_id, 'access_type', 'individual', false );
		add_post_meta( $post_id, 'generated_individual_urls_num', $product_amount, false );
		add_post_meta( $post_id, 'expire_date', '20301231', false );
		// ---------------------------------------------------------
		
	}
	
	
	function getTeamSpreadComponent($team_spread_summary, $all_score_lines, $dimension_number) {
		$team_spread_ret = array();
		
		$team_spread_summary_percent = $team_spread_summary * (100/($all_score_lines * $dimension_number)); 
		$team_spread_ret['percentage'] = $team_spread_summary_percent; 

		if ($team_spread_summary_percent <= 40) {  // Red, Low Diversity
			$team_spread_ret['text'] = 'Low';
		
		}else if ($team_spread_summary_percent <= 70) { // Yellow, Medium Diversity
			$team_spread_ret['text'] = 'Medium';
			
		}else if ($team_spread_summary_percent <= 100) { // Green, High Diversity
			$team_spread_ret['text'] = 'High';
		}
		
		return $team_spread_ret;
	}

	function getSpreadRGBColorForDimensionLine($spread) {
		
		$spreadColorArr = array();
		
		if ($spread <= 4) {  // Red, Low Diversity
			$spreadColorArr['r'] = 237;
			$spreadColorArr['g'] = 117;
			$spreadColorArr['b'] = 95;
		
		}else if ($spread <= 7) { // Yellow, Medium Diversity
			$spreadColorArr['r'] = 243;
			$spreadColorArr['g'] = 236;
			$spreadColorArr['b'] = 117;
			
		}else if ($spread <= 10) { // Green, High Diversity
			$spreadColorArr['r'] = 137;
			$spreadColorArr['g'] = 193;
			$spreadColorArr['b'] = 126;
		}
		
		return $spreadColorArr;
	}
	
	public function diff($v1, $v2) {
		return ($v1-$v2) < 0 ? (-1)*($v1-$v2) : ($v1-$v2);
	}
	
	public function getTeamReportData($participant_added) { // For Team Report HTML only. Not Safe, if we use this function with Team Report PDF.
		global $wpdb;
		global $icq_dashboard;
		
		$assessment_post_id = 70;
		$assessments_keys = get_field('assessments_keys', $assessment_post_id);
		$q_and_a = get_field('q_and_a', $assessment_post_id);
		$total_question = count($q_and_a);
		
		// Specially, For Team Report -------------------------------------------------------------
		// Relation between wp_individual_urls and wp_users_submissions table are post_id and email
		$args = array('post_type' => 'the_sliding_scales', 'numberposts' => 100);
		$sliding_scales_posts = get_posts($args);
				
		$D_summary_arr = array();
		$I_summary_arr = array();
		$S_summary_arr = array();
		$C_summary_arr = array();
		$participant_counter = 0;
		$sliding_scales_value = array();
		
		foreach ($participant_added as $idx => $p_added) {
			if ($p_added->used == 1) {
				$submission = $icq_dashboard->get_submission_info($p_added->code);
				$result_arr_serialize = $submission->result;
				
				$_result_array = array();
				$_result_array = unserialize($result_arr_serialize);
				
				$D_summary += $_result_array['D'];
				$I_summary += $_result_array['I'];
				$S_summary += $_result_array['S'];
				$C_summary += $_result_array['C'];
				
				// Step 3 : The Sliding Scales - SPREAD remarks : It was moved here because we want to calculate sliding scales in each participant.
				$D_single_summary = $_result_array['D'];
				$I_single_summary = $_result_array['I'];
				$S_single_summary = $_result_array['S'];
				$C_single_summary = $_result_array['C'];
				
				foreach ($sliding_scales_posts as $sliding_scale) {
					$sliding_scales_keys = get_field('the_sliding_scales_key', $sliding_scale->ID);
					$sliding_single_value = 0;
					foreach ($sliding_scales_keys as $key) {
						$sliding_single_value += ${ $key['key'] . '_single_summary' };
					}
					$sliding_scales_value[$participant_counter][$sliding_scale->post_name] = $sliding_single_value;
				}
				$participant_counter++;
			}
		}
		
		// Find SPREAD
		// Now We have $sliding_scales in each participant, So we do loop from participants counter.
		$sliding_max_scales_value = array();
		foreach ($sliding_scales_posts as $sliding_scale) {
			$compare_arr = array();
			for($p=0; $p < $participant_counter; $p++) {
				$compare_arr[] = $sliding_scales_value[$p][$sliding_scale->post_name];
			}
			$sliding_max_scales_value[$sliding_scale->post_name] = max($compare_arr);
		}	
		// --------------------------------------------------------------------------------------
		
		// Step 1 : The Quadrant
		$D_quadrant_value = (100/($total_question * $participant_counter)) * $D_summary;
		$I_quadrant_value = (100/($total_question * $participant_counter)) * $I_summary;
		
		if (((int)round($D_quadrant_value) + (int)round($I_quadrant_value) + (int)round($S_quadrant_value)) > 100) {
			$S_quadrant_value = 100 - ((int)round($D_quadrant_value) + (int)round($I_quadrant_value));
			$C_quadrant_value = 0;
		}else {
			$S_quadrant_value = (100/($total_question * $participant_counter)) * $S_summary;
			$C_quadrant_value = 100 - ((int)round($D_quadrant_value) + (int)round($I_quadrant_value) + (int)round($S_quadrant_value));
		}
		
		// Step 2 : Setup D,I,S,C Average values
		$D_summary = (int)round($D_summary/$participant_counter);
		$I_summary = (int)round($I_summary/$participant_counter);
		$S_summary = (int)round($S_summary/$participant_counter);
		$C_summary = (int)round($C_summary/$participant_counter);
		
		// Step 4 : The Text
		// So, we need $D_summary, $I_summary, $S_summary, $C_summary data to initiate this formula ****************************
		$result_arr = array();
		foreach ($assessments_keys as $ak) {
			$result_arr[$ak['the_key']] = ${ $ak['the_key'] . '_summary' };
		}	

		$result_arr_no_sort = $result_arr;
		arsort($result_arr);
		
		$result_arr_keys = array_keys($result_arr);
		
		$highest_value = $result_arr[$result_arr_keys[0]];
		$second_highest_value = $result_arr[$result_arr_keys[1]];

		$different = $highest_value - $second_highest_value;
		
		/* Rules */
		$D_cultural_value = array();
		$I_cultural_value = array();
		$S_cultural_value = array();
		$C_cultural_value = array();
		
		$D_cultural_value['type'] = 'active';
		$D_cultural_value['oriented'] = 'task';
		
		$I_cultural_value['type'] = 'active';
		$I_cultural_value['oriented'] = 'people';
		
		$S_cultural_value['type'] = 'passive';
		$S_cultural_value['oriented'] = 'people';
		
		$C_cultural_value['type'] = 'passive';
		$C_cultural_value['oriented'] = 'task';		
		/* ---------- */
					
		
		$config_number_value = 5;
			
		// 1. Difference is 5 or more: Single result, i.e.: D
		if ($different > $config_number_value) {	
			$single = $result_arr_keys[0];
			$the_text_result = $single;
			
		}else if ($different < $config_number_value) {
			$counts = array_count_values($result_arr_no_sort);
			$filtered = array_filter($result_arr_no_sort, function ($value) use ($counts) { // Filtered for same value
				return $counts[$value] > 1;
			});
			
			// 4. Difference is 5 or less and you have 3 equal results then triple result
			if (count($filtered) == 3) { // Duplicate 3, Example : 4 4 4 3
			
				// 5. There is one highest one and 3 equal results, then it is based on this, there are no more possibilities for this type of result.
				if ($highest_value == 6 && current($filtered) == 3) {
					switch ($result_arr_keys[0]) {
						case 'D' :
							$the_text_result = 'DC';
							break;
						case 'I' :
							$the_text_result = 'IS';
							break;
						case 'S' :
							$the_text_result = 'SI';
							break;
						case 'C' :
							$the_text_result = 'CD';
							break;
					} 
				}else {	
					$tripple = $filtered;
					foreach ($tripple as $key => $tripple_val) {
						$the_text_result .= $key;
					}
				}
						
			// 3. Difference is less than 5 with 1 highest value and 2 equal, secondary ones, then double result matching the primary one (active – passive, or task- people oriented)
			}else if (count($filtered) == 2 && in_array($result_arr[$result_arr_keys[1]], $filtered)) { // Duplicate 2 with 1 highest value, Example : 7 4 4 0 
			
				if (current($filtered) == $second_highest_value && $different > 0) {
					$max_val = array_keys($result_arr, max($result_arr));
					$primary_key = $max_val[0];
					
					$primary_cultural_value = ${ $primary_key . '_cultural_value' };
					
					foreach ($filtered as $key => $f) {
						$filtered_cultural_value = ${ $key . '_cultural_value' };
						
						if (!$oriented_matching) {
							if ($filtered_cultural_value['oriented'] == $primary_cultural_value['oriented']) { // Oriented matching
								$second_key = $key;
								$oriented_matching = true;
							}else if ($filtered_cultural_value['type'] == $primary_cultural_value['type']) { // Type matching
								$second_key = $key;
								$type_matching = true;
							}
						}
					}
					
					$the_text_result = $primary_key.$second_key;
					
				}else {
								
					if (${ $result_arr_keys[0] . '_cultural_value' }['type']  ==  ${ $result_arr_keys[1] . '_cultural_value' }['type']) { // Type matching
						
						$map_with_this = ${ $result_arr_keys[2] . '_cultural_value'}['oriented'];
						if (${ $result_arr_keys[0] . '_cultural_value' }['oriented'] == $map_with_this) $double = $result_arr_keys[0].$result_arr_keys[1]; // Example : DI (5 5 2 3)
						else  $double = $result_arr_keys[1].$result_arr_keys[0];  // Example : ID (5 5 3 2)
						
					}else if (${ $result_arr_keys[0] . '_cultural_value' }['oriented']  ==  ${ $result_arr_keys[1] . '_cultural_value' }['oriented']) { // Oriented matching
						$map_with_this = ${ $result_arr_keys[2] . '_cultural_value'}['type'];
						if (${ $result_arr_keys[0] . '_cultural_value' }['type'] == $map_with_this) $double = $result_arr_keys[0].$result_arr_keys[1]; // Example : IS (3 5 5 2)
						else $double = $result_arr_keys[1].$result_arr_keys[0];  // Example : SI (2 5 5 3)
						
					}else {
						$double = $result_arr_keys[0].$result_arr_keys[1];
					}
					
					$the_text_result = $double;				
				}
				
			// 2. Difference is less than 5: double result, i.e. DI (the highest values)
			}else {		

				$double = $result_arr_keys[0].$result_arr_keys[1];
				$the_text_result = $double;

			}
			
		}else if ($different == $config_number_value) {		
			$counts = array_count_values($result_arr_no_sort);
			$filtered = array_filter($result_arr_no_sort, function ($value) use ($counts) { // Filtered for same value
				return $counts[$value] > 1;
			});
			
			// 4. Difference is 5 or less and you have 3 equal results then triple result
			if (count($filtered) == 3) {
				$tripple = $filtered;
				foreach ($tripple as $key => $tripple_val) {
					$the_text_result .= $key;
				}
			}else {
				$single = $result_arr_keys[0];
				$the_text_result = $single;
			}
		}
		// ***************************************
		
		$the_text_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $the_text_result . "' AND post_type = 'the_text'" );
	
		// SPREAD
		// Initiate Sliding Scale Value
		$all_score_lines = 10;
		$maximum_score_lines = $all_score_lines;
		$inverse_score_init = $maximum_score_lines + 1; // 6th is a start line for another side
		$team_average_icon_img = get_template_directory_uri() . '/images/team-average-icon.png';
		
		
		// Get Team Spread -----------------------------------
		$team_spread = array();
		
		// Sliding Scale 1 : Objective & Subjective -------------------------------------------------------------
		$sliding_scale_sit_begin = $inverse_score_init - ceil($sliding_max_scales_value['objective-communication'] * ($maximum_score_lines/$total_question));
		$sliding_scale_sit_end = ceil($sliding_max_scales_value['subjective-communication'] * ($maximum_score_lines/$total_question));
		$team_spread['spread_1'] = $this->diff($sliding_scale_sit_end, $sliding_scale_sit_begin) + 1; // +1 Because we have to add the beginning line to be the first
		$team_spread['sit_begin_1'] = $sliding_scale_sit_begin;		
		if ($sliding_scale_sit_begin > $sliding_scale_sit_end) {  // Foul detect
			$team_spread['spread_1'] = 1;
			if ($sliding_max_scales_value['objective-communication'] < $sliding_max_scales_value['subjective-communication'])
				$team_spread['sit_begin_1'] = $sliding_scale_sit_end;
			else
				$team_spread['sit_begin_1'] = $sliding_scale_sit_begin;
		}
		
		// Find the Team average
		// So, We loop in each participant to compare them one by one
		$p_sliding_scale_sit = 0;
		foreach ($sliding_scales_value as $_value) {				
			if ($_value['objective-communication'] > $_value['subjective-communication']) {
				$p_sliding_scale_sit += $inverse_score_init - ceil($_value['objective-communication'] * ($maximum_score_lines/$total_question));
			}else if ($_value['objective-communication'] < $_value['subjective-communication']) {
				$p_sliding_scale_sit += ceil($_value['subjective-communication'] * ($maximum_score_lines/$total_question));
			}
		}
		$team_average_sliding_scale = $p_sliding_scale_sit/$participant_counter;
		if ($team_average_sliding_scale <= 5) $intercultural_values_1 = 'objective';
		else $intercultural_values_1 = 'subjective';
		
		$team_average_sliding_scale_1 = $team_average_sliding_scale;
		// ----------------------------------------------------------------------------------

		// Sliding Scale 2 : Assertive & Reflective ----------------------------------------------------------
		$sliding_scale_sit_begin = $inverse_score_init - ceil($sliding_max_scales_value['assertive-communication'] * ($maximum_score_lines/$total_question));
		$sliding_scale_sit_end = ceil($sliding_max_scales_value['reflective-communication'] * ($maximum_score_lines/$total_question));		
		$team_spread['spread_2'] = $this->diff($sliding_scale_sit_end, $sliding_scale_sit_begin) + 1; // +1 Because we have to add the beginning line to be the first
		$team_spread['sit_begin_2'] = $sliding_scale_sit_begin;
		if ($sliding_scale_sit_begin > $sliding_scale_sit_end) {  // Foul detect
			$team_spread['spread_2'] = 1;
			if ($sliding_max_scales_value['assertive-communication'] < $sliding_max_scales_value['reflective-communication'])
				$team_spread['sit_begin_2'] = $sliding_scale_sit_end;
			else
				$team_spread['sit_begin_2'] = $sliding_scale_sit_begin;
		}
		
		// Find the Team average
		// So, We loop in each participant to compare them one by one
		$p_sliding_scale_sit = 0;
		foreach ($sliding_scales_value as $_value) {				
			if ($_value['assertive-communication'] > $_value['reflective-communication']) {
				$p_sliding_scale_sit += $inverse_score_init - ceil($_value['assertive-communication'] * ($maximum_score_lines/$total_question));
			}else if ($_value['assertive-communication'] < $_value['reflective-communication']) {
				$p_sliding_scale_sit += ceil($_value['reflective-communication'] * ($maximum_score_lines/$total_question));
			}
		}
		$team_average_sliding_scale = $p_sliding_scale_sit/$participant_counter;
		if ($team_average_sliding_scale <= 5) $intercultural_values_2 = 'assertive';
		else $intercultural_values_2 = 'reflective';
		
		$team_average_sliding_scale_2 = $team_average_sliding_scale;
		// ----------------------------------------------------------------------------------
		
		
		// Sliding Scale 3 : Accepting & Challenging ----------------------------------------------------------
		$sliding_scale_sit_begin = $inverse_score_init - ceil($sliding_max_scales_value['accepting-behaviour'] * ($maximum_score_lines/$total_question));
		$sliding_scale_sit_end = ceil($sliding_max_scales_value['challenging-behaviour'] * ($maximum_score_lines/$total_question));
		$team_spread['spread_3'] = $this->diff($sliding_scale_sit_end, $sliding_scale_sit_begin) + 1;
		$team_spread['sit_begin_3'] = $sliding_scale_sit_begin;
		if ($sliding_scale_sit_begin > $sliding_scale_sit_end) {  // Foul detect
			$team_spread['spread_3'] = 1;
			if ($sliding_max_scales_value['accepting-behaviour'] < $sliding_max_scales_value['challenging-behaviour'])
				$team_spread['sit_begin_3'] = $sliding_scale_sit_end;
			else 
				$team_spread['sit_begin_3'] = $sliding_scale_sit_begin;
		}
		
		// Find the Team average
		// So, We loop in each participant to compare them one by one
		$p_sliding_scale_sit = 0;
		foreach ($sliding_scales_value as $_value) {				
			if ($_value['accepting-behaviour'] > $_value['challenging-behaviour']) {
				$p_sliding_scale_sit += $inverse_score_init - ceil($_value['accepting-behaviour'] * ($maximum_score_lines/$total_question));
			}else if ($_value['accepting-behaviour'] < $_value['challenging-behaviour']) {
				$p_sliding_scale_sit += ceil($_value['challenging-behaviour'] * ($maximum_score_lines/$total_question));
			}
		}
		$team_average_sliding_scale = $p_sliding_scale_sit/$participant_counter;			
		if ($team_average_sliding_scale <= 5) $intercultural_values_3 = 'accepting';
		else $intercultural_values_3 = 'challenging';
		
		$team_average_sliding_scale_3 = $team_average_sliding_scale;
		// ----------------------------------------------------------------------------------
		
		// Sliding Scale 4 : Result Oriented & Process Oriented -----------------------------------------------
		$sliding_scale_sit_begin = $inverse_score_init - ceil($sliding_max_scales_value['result-oriented-behaviour'] * ($maximum_score_lines/$total_question));
		$sliding_scale_sit_end = ceil($sliding_max_scales_value['process-oriented-behaviour'] * ($maximum_score_lines/$total_question));
		$team_spread['spread_4'] = $this->diff($sliding_scale_sit_end, $sliding_scale_sit_begin) + 1;
		$team_spread['sit_begin_4'] = $sliding_scale_sit_begin;
		if ($sliding_scale_sit_begin > $sliding_scale_sit_end) {  // Foul detect
			$team_spread['spread_4'] = 1;
			if ($sliding_max_scales_value['result-oriented-behaviour'] < $sliding_max_scales_value['process-oriented-behaviour']) 
				$team_spread['sit_begin_4'] = $sliding_scale_sit_end;
			else
				$team_spread['sit_begin_4'] = $sliding_scale_sit_begin;
		}
		
		// Find the Team average
		// So, We loop in each participant to compare them one by one
		$p_sliding_scale_sit = 0;
		foreach ($sliding_scales_value as $_value) {				
			if ($_value['result-oriented-behaviour'] > $_value['process-oriented-behaviour']) {
				$p_sliding_scale_sit += $inverse_score_init - ceil($_value['result-oriented-behaviour'] * ($maximum_score_lines/$total_question));
			}else if ($_value['result-oriented-behaviour'] < $_value['process-oriented-behaviour']) {
				$p_sliding_scale_sit += ceil($_value['process-oriented-behaviour'] * ($maximum_score_lines/$total_question));
			}
		}
		$team_average_sliding_scale = $p_sliding_scale_sit/$participant_counter;			
		if ($team_average_sliding_scale <= 5) $intercultural_values_4 = 'result-oriented';
		else $intercultural_values_4 = 'process-oriented';
		
		$team_average_sliding_scale_4 = $team_average_sliding_scale;
		// ----------------------------------------------------------------------------------
		
		// Sliding Scale 5 : Open & Guarded -------------------------------------------------------------------
		$sliding_scale_sit_begin = $inverse_score_init - ceil($sliding_max_scales_value['open-behaviour'] * ($maximum_score_lines/$total_question));
		$sliding_scale_sit_end = ceil($sliding_max_scales_value['guarded-behaviour'] * ($maximum_score_lines/$total_question));
		$team_spread['spread_5'] = $this->diff($sliding_scale_sit_end, $sliding_scale_sit_begin) + 1;
		$team_spread['sit_begin_5'] = $sliding_scale_sit_begin;
		if ($sliding_scale_sit_begin > $sliding_scale_sit_end) {  // Foul detect
			$team_spread['spread_5'] = 1;
			if ($sliding_max_scales_value['open-behaviour'] < $sliding_max_scales_value['guarded-behaviour']) 
				$team_spread['sit_begin_5'] = $sliding_scale_sit_end;
			else
				$team_spread['sit_begin_5'] = $sliding_scale_sit_begin;
		}
		
		// Find the Team average
		// So, We loop in each participant to compare them one by one
		$p_sliding_scale_sit = 0;
		foreach ($sliding_scales_value as $_value) {
			if ($_value['open-behaviour'] > $_value['guarded-behaviour']) {
				$p_sliding_scale_sit += $inverse_score_init - ceil($_value['open-behaviour'] * ($maximum_score_lines/$total_question));
			}else if ($_value['open-behaviour'] < $_value['guarded-behaviour']) {
				$p_sliding_scale_sit += ceil($_value['guarded-behaviour'] * ($maximum_score_lines/$total_question));
			}
		}
		$team_average_sliding_scale = $p_sliding_scale_sit/$participant_counter;			
		if ($team_average_sliding_scale <= 5) $intercultural_values_5 = 'open-behaviour';
		else $intercultural_values_5 = 'guarded-behaviour';
		
		$team_average_sliding_scale_5 = $team_average_sliding_scale;
		// ----------------------------------------------------------------------------------
					
		$team_spread_summary = $team_spread['spread_1'] + $team_spread['spread_2'] + $team_spread['spread_3'] + $team_spread['spread_4'] + $team_spread['spread_5'];
			
		$args = array('post_type' => 'intercultural_values', 'name' => $intercultural_values_1, 'numberposts' => 1);
		$intercultural_values_post = get_posts($args);
		$intercultural_intro_text_1 = get_field('intro_text', $intercultural_values_post[0]->ID);
		$intercultural_drivers_arr_1 = get_field('drivers', $intercultural_values_post[0]->ID);
		
		$args = array('post_type' => 'intercultural_values', 'name' => $intercultural_values_2, 'numberposts' => 1);
		$intercultural_values_post = get_posts($args);
		$intercultural_intro_text_2 = get_field('intro_text', $intercultural_values_post[0]->ID);
		$intercultural_drivers_arr_2 = get_field('drivers', $intercultural_values_post[0]->ID);
		
		$args = array('post_type' => 'intercultural_values', 'name' => $intercultural_values_3, 'numberposts' => 1);
		$intercultural_values_post = get_posts($args);
		$intercultural_intro_text_3 = get_field('intro_text', $intercultural_values_post[0]->ID);
		$intercultural_drivers_arr_3 = get_field('drivers', $intercultural_values_post[0]->ID);
		
		$args = array('post_type' => 'intercultural_values', 'name' => $intercultural_values_4, 'numberposts' => 1);
		$intercultural_values_post = get_posts($args);
		$intercultural_intro_text_4 = get_field('intro_text', $intercultural_values_post[0]->ID);
		$intercultural_drivers_arr_4 = get_field('drivers', $intercultural_values_post[0]->ID);
		
		$args = array('post_type' => 'intercultural_values', 'name' => $intercultural_values_5, 'numberposts' => 1);
		$intercultural_values_post = get_posts($args);
		$intercultural_intro_text_5 = get_field('intro_text', $intercultural_values_post[0]->ID);
		$intercultural_drivers_arr_5 = get_field('drivers', $intercultural_values_post[0]->ID);
		
			
		// Prepare to return ---------------------------------------------------
		$report_data = array();
		
		$report_data['D_quadrant_value'] = $D_quadrant_value;
		$report_data['I_quadrant_value'] = $I_quadrant_value;
		$report_data['S_quadrant_value'] = $S_quadrant_value;
		$report_data['C_quadrant_value'] = $C_quadrant_value;
		
		$report_data['team_spread_1'] = $team_spread['spread_1'];
		$report_data['team_spread_2'] = $team_spread['spread_2'];
		$report_data['team_spread_3'] = $team_spread['spread_3'];
		$report_data['team_spread_4'] = $team_spread['spread_4'];
		$report_data['team_spread_5'] = $team_spread['spread_5'];
		$report_data['team_sit_begin_1'] = $team_spread['sit_begin_1'];
		$report_data['team_sit_begin_2'] = $team_spread['sit_begin_2'];
		$report_data['team_sit_begin_3'] = $team_spread['sit_begin_3'];
		$report_data['team_sit_begin_4'] = $team_spread['sit_begin_4'];
		$report_data['team_sit_begin_5'] = $team_spread['sit_begin_5'];
		
		$report_data['team_average_sliding_scale_1'] = $team_average_sliding_scale_1;
		$report_data['team_average_sliding_scale_2'] = $team_average_sliding_scale_2;
		$report_data['team_average_sliding_scale_3'] = $team_average_sliding_scale_3;
		$report_data['team_average_sliding_scale_4'] = $team_average_sliding_scale_4;
		$report_data['team_average_sliding_scale_5'] = $team_average_sliding_scale_5;
		
		$report_data['intercultural_values_intro_text_1'] = $intercultural_intro_text_1;
		$report_data['intercultural_values_intro_text_2'] = $intercultural_intro_text_2;
		$report_data['intercultural_values_intro_text_3'] = $intercultural_intro_text_3;
		$report_data['intercultural_values_intro_text_4'] = $intercultural_intro_text_4;
		$report_data['intercultural_values_intro_text_5'] = $intercultural_intro_text_5;
		$report_data['intercultural_values_drivers_arr_1'] = $intercultural_drivers_arr_1;
		$report_data['intercultural_values_drivers_arr_2'] = $intercultural_drivers_arr_2;
		$report_data['intercultural_values_drivers_arr_3'] = $intercultural_drivers_arr_3;
		$report_data['intercultural_values_drivers_arr_4'] = $intercultural_drivers_arr_4;
		$report_data['intercultural_values_drivers_arr_5'] = $intercultural_drivers_arr_5;
		
		$report_data['team_spread_summary'] = $team_spread_summary;
		
		return $report_data;
	}
	
	public function getDISCResult($participant_added, $assessment_post_id) {
		global $wpdb;
		global $icq_dashboard;
		
		$assessments_keys = get_field('assessments_keys', $assessment_post_id);
		$q_and_a = get_field('q_and_a', $assessment_post_id);
		$total_question = count($q_and_a);
	
		$args = array('post_type' => 'the_sliding_scales', 'numberposts' => 100);
		$sliding_scales_posts = get_posts($args);
				
		$D_summary_arr = array();
		$I_summary_arr = array();
		$S_summary_arr = array();
		$C_summary_arr = array();
		$participant_counter = 0;
		$sliding_scales_value = array();
	
		foreach ($participant_added as $idx => $p_added) {
			if ($p_added->used == 1) {
				$submission = $icq_dashboard->get_submission_info($p_added->code);
				$result_arr_serialize = $submission->result;
				
				$_result_array = array();
				$_result_array = unserialize($result_arr_serialize);
				
				$D_summary += $_result_array['D'];
				$I_summary += $_result_array['I'];
				$S_summary += $_result_array['S'];
				$C_summary += $_result_array['C'];
				
				// Step 3 : The Sliding Scales - SPREAD remarks : It was moved here because we want to calculate sliding scales in each participant.
				$D_single_summary = $_result_array['D'];
				$I_single_summary = $_result_array['I'];
				$S_single_summary = $_result_array['S'];
				$C_single_summary = $_result_array['C'];
				
				foreach ($sliding_scales_posts as $sliding_scale) {
					$sliding_scales_keys = get_field('the_sliding_scales_key', $sliding_scale->ID);
					$sliding_single_value = 0;
					foreach ($sliding_scales_keys as $key) {
						$sliding_single_value += ${ $key['key'] . '_single_summary' };
					}
					$sliding_scales_value[$participant_counter][$sliding_scale->post_name] = $sliding_single_value;
				}
				$participant_counter++;
			}
		}
		
		// Step 1 : The Quadrant
		$D_quadrant_value = (100/($total_question * $participant_counter)) * $D_summary;
		$I_quadrant_value = (100/($total_question * $participant_counter)) * $I_summary;
		
		if (((int)round($D_quadrant_value) + (int)round($I_quadrant_value) + (int)round($S_quadrant_value)) > 100) {
			$S_quadrant_value = 100 - ((int)round($D_quadrant_value) + (int)round($I_quadrant_value));
			$C_quadrant_value = 0;
		}else {
			$S_quadrant_value = (100/($total_question * $participant_counter)) * $S_summary;
			$C_quadrant_value = 100 - ((int)round($D_quadrant_value) + (int)round($I_quadrant_value) + (int)round($S_quadrant_value));
		}
		
		// Step 2 : Setup D,I,S,C Average values
		$D_summary = (int)round($D_summary/$participant_counter);
		$I_summary = (int)round($I_summary/$participant_counter);
		$S_summary = (int)round($S_summary/$participant_counter);
		$C_summary = (int)round($C_summary/$participant_counter);
		
		// Step 4 : The Text
		// So, we need $D_summary, $I_summary, $S_summary, $C_summary data to initiate this formula ****************************
		$result_arr = array();
		foreach ($assessments_keys as $ak) {
			$result_arr[$ak['the_key']] = ${ $ak['the_key'] . '_summary' };
		}	

		$result_arr_no_sort = $result_arr;
		arsort($result_arr);
		
		$result_arr_keys = array_keys($result_arr);
		
		$highest_value = $result_arr[$result_arr_keys[0]];
		$second_highest_value = $result_arr[$result_arr_keys[1]];

		$different = $highest_value - $second_highest_value;
		
		/* Rules */
		$D_cultural_value = array();
		$I_cultural_value = array();
		$S_cultural_value = array();
		$C_cultural_value = array();
		
		$D_cultural_value['type'] = 'active';
		$D_cultural_value['oriented'] = 'task';
		
		$I_cultural_value['type'] = 'active';
		$I_cultural_value['oriented'] = 'people';
		
		$S_cultural_value['type'] = 'passive';
		$S_cultural_value['oriented'] = 'people';
		
		$C_cultural_value['type'] = 'passive';
		$C_cultural_value['oriented'] = 'task';		
		/* ---------- */
					
		
		$config_number_value = 5;
			
		// 1. Difference is 5 or more: Single result, i.e.: D
		if ($different > $config_number_value) {	
			$single = $result_arr_keys[0];
			$the_text_result = $single;
			
		}else if ($different < $config_number_value) {
			$counts = array_count_values($result_arr_no_sort);
			$filtered = array_filter($result_arr_no_sort, function ($value) use ($counts) { // Filtered for same value
				return $counts[$value] > 1;
			});
			
			// 4. Difference is 5 or less and you have 3 equal results then triple result
			if (count($filtered) == 3) { // Duplicate 3, Example : 4 4 4 3
			
				// 5. There is one highest one and 3 equal results, then it is based on this, there are no more possibilities for this type of result.
				if ($highest_value == 6 && current($filtered) == 3) {
					switch ($result_arr_keys[0]) {
						case 'D' :
							$the_text_result = 'DC';
							break;
						case 'I' :
							$the_text_result = 'IS';
							break;
						case 'S' :
							$the_text_result = 'SI';
							break;
						case 'C' :
							$the_text_result = 'CD';
							break;
					} 
				}else {	
					$tripple = $filtered;
					foreach ($tripple as $key => $tripple_val) {
						$the_text_result .= $key;
					}
				}
						
			// 3. Difference is less than 5 with 1 highest value and 2 equal, secondary ones, then double result matching the primary one (active – passive, or task- people oriented)
			}else if (count($filtered) == 2 && in_array($result_arr[$result_arr_keys[1]], $filtered)) { // Duplicate 2 with 1 highest value, Example : 7 4 4 0 
			
				if (current($filtered) == $second_highest_value && $different > 0) {
					$max_val = array_keys($result_arr, max($result_arr));
					$primary_key = $max_val[0];
					
					$primary_cultural_value = ${ $primary_key . '_cultural_value' };
					
					foreach ($filtered as $key => $f) {
						$filtered_cultural_value = ${ $key . '_cultural_value' };
						
						if (!$oriented_matching) {
							if ($filtered_cultural_value['oriented'] == $primary_cultural_value['oriented']) { // Oriented matching
								$second_key = $key;
								$oriented_matching = true;
							}else if ($filtered_cultural_value['type'] == $primary_cultural_value['type']) { // Type matching
								$second_key = $key;
								$type_matching = true;
							}
						}
					}
					
					$the_text_result = $primary_key.$second_key;
					
				}else {
								
					if (${ $result_arr_keys[0] . '_cultural_value' }['type']  ==  ${ $result_arr_keys[1] . '_cultural_value' }['type']) { // Type matching
						
						$map_with_this = ${ $result_arr_keys[2] . '_cultural_value'}['oriented'];
						if (${ $result_arr_keys[0] . '_cultural_value' }['oriented'] == $map_with_this) $double = $result_arr_keys[0].$result_arr_keys[1]; // Example : DI (5 5 2 3)
						else  $double = $result_arr_keys[1].$result_arr_keys[0];  // Example : ID (5 5 3 2)
						
					}else if (${ $result_arr_keys[0] . '_cultural_value' }['oriented']  ==  ${ $result_arr_keys[1] . '_cultural_value' }['oriented']) { // Oriented matching
						$map_with_this = ${ $result_arr_keys[2] . '_cultural_value'}['type'];
						if (${ $result_arr_keys[0] . '_cultural_value' }['type'] == $map_with_this) $double = $result_arr_keys[0].$result_arr_keys[1]; // Example : IS (3 5 5 2)
						else $double = $result_arr_keys[1].$result_arr_keys[0];  // Example : SI (2 5 5 3)
						
					}else {
						$double = $result_arr_keys[0].$result_arr_keys[1];
					}
					
					$the_text_result = $double;				
				}
				
			// 2. Difference is less than 5: double result, i.e. DI (the highest values)
			}else {		

				$double = $result_arr_keys[0].$result_arr_keys[1];
				$the_text_result = $double;

			}
			
		}else if ($different == $config_number_value) {		
			$counts = array_count_values($result_arr_no_sort);
			$filtered = array_filter($result_arr_no_sort, function ($value) use ($counts) { // Filtered for same value
				return $counts[$value] > 1;
			});
			
			// 4. Difference is 5 or less and you have 3 equal results then triple result
			if (count($filtered) == 3) {
				$tripple = $filtered;
				foreach ($tripple as $key => $tripple_val) {
					$the_text_result .= $key;
				}
			}else {
				$single = $result_arr_keys[0];
				$the_text_result = $single;
			}
		}
		// ***************************************

		$the_text_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $the_text_result . "' AND post_type = 'the_text'" );
		
		$result_data = array();
		$result_data['D_quadrant_value'] = $D_quadrant_value;
		$result_data['I_quadrant_value'] = $I_quadrant_value;
		$result_data['S_quadrant_value'] = $S_quadrant_value;
		$result_data['C_quadrant_value'] = $C_quadrant_value;
		$result_data['sliding_scales_value'] = $sliding_scales_value;
		$result_data['the_text_id'] = $the_text_id;
		$result_data['participant_counter'] = $participant_counter;
		
		return $result_data;
	}
	
	public function cleanStr($string) {
	   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

	   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	}
}

$icq_report = new IcqReport();
