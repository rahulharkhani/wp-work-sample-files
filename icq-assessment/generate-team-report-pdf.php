<?php
	/**
	 * Template Name: Thank you
	 *
	 * @package WordPress
	 */
	
	//get_header();
	require_once __DIR__ . '/../../../wp-load.php';
	require_once('fpdf/fpdf.php');
	require_once('fpdi/fpdi.php');
	require_once('team-report-pdf/diag.php');	
	
	global $wpdb;
	global $icq_dashboard;
	global $icq_report;
	
	$user = wp_get_current_user();
	$user_roles = $user->roles;
	
	if ( in_array('licensee', $user_roles) || in_array('administrator', $user_roles) || in_array('trainer', $user_roles) ) {
		if (isset($_POST['gid'])) $group_id = $_POST['gid'];
		else exit;
		
		$participant_added = $icq_dashboard->get_participant_added_by_group_id($group_id);
		$cohort_group_data = $icq_dashboard->get_cohort_group_data($group_id);
		$group_name = $cohort_group_data->group_name;
		
		$wpdb->update('wp_cohorts_groups', array('team_report_generated' => 1, 'team_report_generated_date' => date('Y-m-d')),	array('id' => $group_id));
		
		$refered_id = $group_id;
		
	}else {
		$participant_added = $icq_dashboard->get_participant_added();
		$group_name = $user->display_name . ' Group';
		
		$user_id = get_current_user_id();
		$team_report_credit = get_user_meta($user_id, 'team_report_credit', true);
		$team_report_credit_used = get_user_meta($user_id, 'team_report_credit_used', true);
		$left_team_report_credit_amount = $team_report_credit - $team_report_credit_used;
		
		if ($left_team_report_credit_amount > 0) {
			$team_report_credit_used =  $team_report_credit_used + 1;
			update_user_meta($user_id, 'team_report_credit_used', $team_report_credit_used);
			
			update_user_meta($user_id, 'team_report_generated', 1);
			update_user_meta($user_id, 'team_report_generated_date', date('Y-m-d'));
			
			$refered_id = $user_id;
		
		}else {
			return 'credit-empty'; // Credit empty, Exit immediately
		}
	}
	
	if (!isset($group_name) || $group_name == '' || $group_name == NULL) exit;
	
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
	
	// Start generate PDF
	$pdf = new PDF_Diag();
	
	$pageCount = $pdf->setSourceFile('pdf/DISC-team-report.pdf');	
	for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
		
		$tplIdx = $pdf->importPage($pageNo);
	
		$pdf->addPage();
		$pdf->useTemplate($tplIdx, 0, 0, 0, 0, true);
		$pdf->SetFillColor(255,255,255);
		$pdf->SetTextColor(41,40,57);  // Set Text Color default
		
		$pdf->AddFont('GT-Walsheim-Pro-Trial-Regular','','GT-Walsheim-Pro-Trial-Regular.php');
		$pdf->AddFont('GT-Walsheim-Pro-Trial-Black','','GT-Walsheim-Pro-Trial-Black.php');
		$pdf->AddFont('GT-Walsheim-Pro-Trial-Light','','GT-Walsheim-Pro-Trial-Light.php');
		$pdf->AddFont('SourceSansPro-Regular','','SourceSansPro-Regular.php');
		$pdf->AddFont('SourceSansPro-Black','','SourceSansPro-Black.php');
		$pdf->AddFont('SourceSansPro-Semibold','','SourceSansPro-Semibold.php');
		$pdf->AddFont('SourceSansPro-Light','','SourceSansPro-Light.php');
		
		$x_fluctuation_multicell = '0.25333';
		$y_fluctuation_multicell = '0.25657';
		
		$group_name = toAscii($group_name);
			
		if ($pageNo == 1) {
			$pdf->SetFont('SourceSansPro-Regular','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(120.5,152);
			$pdf->MultiCell(95, 5.65, transformStringQuote($group_name), 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Regular','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(120.5,159.8);
			$pdf->MultiCell(75, 5.65, $participant_counter , 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Regular','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(120.5,167.4);
			$pdf->MultiCell(75, 5.65, date('d/m/Y'), 0, 1, 'L');

		}else if ($pageNo == 4) {
				
			$pdf->SetFont('SourceSansPro-Semibold','',18);
			$pdf->SetTextColor(41,40,57);
			$w = $pdf->GetStringWidth($group_name);
			$pdf->SetXY((210-$w)/2, 139);
			$pdf->Write(0, $group_name);
			
			$pdf->SetFont('SourceSansPro-Semibold','',18);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(56.7, 182.5);
			$pdf->Write(0, round($D_quadrant_value).'%');
			
			$pdf->SetXY(139.2, 182.5);
			$pdf->Write(0, round($I_quadrant_value).'%');
			
			$pdf->SetXY(139.2, 213.5);
			$pdf->Write(0, round($S_quadrant_value).'%');
			
			$pdf->SetXY(56.7, 213.5);
			$pdf->Write(0, round($C_quadrant_value).'%');
						
			// ******************* BEHAVIOURAL AND COMMUNICATION STYLE *******************
			$behavioural_and_communication_style_text_above_visual = get_field('behavioural_and_communication_style_text_above_visual', $the_text_id);
			$behavioural_and_communication_style_text_above_visual_arr = explode('<br />', $behavioural_and_communication_style_text_above_visual);
			foreach ($behavioural_and_communication_style_text_above_visual_arr as $k => $bc) {
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				if ($k==0) $pdf->SetXY(19, 59.35);
				else $pdf->SetXY(19, $pdf->GetY()+1.5);
				$bc = transformStringQuote($bc);
				$pdf->MultiCell(132, 5.65, $bc, 0, 1, 'L');
			}
		
		}else if ($pageNo == 5) {
		
			// ******************* CULTURAL ORIENTATION *******************
			$cultural_orientation_select = get_field('cultural_orientation_select', $the_text_id);
			
			$cultural_orientation_text_above_visual = get_field('cultural_orientation_text_above_visual', $cultural_orientation_select->ID);
			$pdf->SetFont('SourceSansPro-Regular','',11.8);
			$pdf->SetXY(19, 58.5);
			$cultural_orientation_text_above_visual = transformStringQuote($cultural_orientation_text_above_visual);
			$pdf->MultiCell(132, 5.65, $cultural_orientation_text_above_visual, 0, 1, 'L');
			
			$img_visual_path = get_field('visual_image', $cultural_orientation_select->ID);
			if ($img_visual_path == NULL) $img_visual_path = get_template_directory_uri() .'/images/active-task-oriented-visual.png';
			
			$pdf->SetXY(17, $pdf->GetY()+5);
			$pdf->Cell(210,3,$pdf->Image($img_visual_path,$pdf->GetX(),$pdf->GetY()),0,1,'C',0);
			
			$cultural_orientation_text_below_visual = get_field('cultural_orientation_text_below_visual-part_1', $cultural_orientation_select->ID);
			$cultural_orientation_text_below_visual_arr = explode('<br />', $cultural_orientation_text_below_visual);
			foreach ($cultural_orientation_text_below_visual_arr as $k => $co) {
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				if ($k==0) $pdf->SetXY(19, 211);
				else $pdf->SetXY(19, $pdf->GetY()+1.5);
				$co = transformStringQuote($co);
				$pdf->MultiCell(132, 5.65, $co, 0, 1, 'L');
			}
		
		}else if ($pageNo == 6) {
		
			$cultural_orientation_text_below_visual = get_field('cultural_orientation_text_below_visual-part_2', $cultural_orientation_select->ID);
			$cultural_orientation_text_below_visual_arr = explode('<br />', $cultural_orientation_text_below_visual);
			foreach ($cultural_orientation_text_below_visual_arr as $k => $co) {
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				if ($k==0) $pdf->SetXY(19, 19);
				else $pdf->SetXY(19, $pdf->GetY()+1.5);
				$co = transformStringQuote($co);
				$pdf->MultiCell(132, 5.65, $co, 0, 1, 'L');
			}
			
			$text_summarize_under_dimension_bar_chart = get_field('text_summarize_under_dimension_bar_chart', $cultural_orientation_select->ID);
			$text_summarize_under_dimension_bar_chart_arr = explode('<br />', $text_summarize_under_dimension_bar_chart);
			foreach ($text_summarize_under_dimension_bar_chart_arr as $k => $ts) {
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				if ($k==0) $pdf->SetXY(19, $pdf->GetY()+13);
				else $pdf->SetXY(19, $pdf->GetY()+1.5);
				$ts = transformStringQuote($ts);
				$pdf->MultiCell(132, 5.65, $ts, 0, 1, 'L');
			}
			
			$cultural_orientation_dimension = get_field('dimension', $cultural_orientation_select->ID);
			
			// Cultural Orientation dimension [index : 0]
			$dimension = $cultural_orientation_dimension[0];
			$pdf->SetFont('SourceSansPro-Semibold','',14);
			$pdf->SetXY(19, $pdf->GetY() + 13);
			$dimension_title = transformStringQuote($dimension['title']);
			$pdf->MultiCell(132, 7.4, $dimension_title, 0, 1, 'L');
			foreach ($dimension['clause'] as $clause) {
				$img_bullet = get_template_directory_uri() . '/images/bullet.png';
				$pdf->SetXY(20, $pdf->GetY() + 4);
				$pdf->Cell(0,0,$pdf->Image($img_bullet,$pdf->GetX(),$pdf->GetY()),0,1,'C',0);
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				$pdf->SetXY(21, $pdf->GetY() - 2.4);
				$clause_text = transformStringQuote($clause['text']);
				$pdf->MultiCell(132, 5.9, $clause_text, 0, 1, 'L');
			}
			
			// Cultural Orientation dimension [index : 1]
			$dimension = $cultural_orientation_dimension[1];
			$pdf->SetFont('SourceSansPro-Semibold','',14);
			$pdf->SetXY(19, $pdf->GetY() + 7.4);
			$dimension_title = transformStringQuote($dimension['title']);
			$pdf->MultiCell(132, 7.4, $dimension_title, 0, 1, 'L');
			foreach ($dimension['clause'] as $clause) {
				$img_bullet = get_template_directory_uri() . '/images/bullet.png';
				$pdf->SetXY(20, $pdf->GetY() + 4);
				$pdf->Cell(0,0,$pdf->Image($img_bullet,$pdf->GetX(),$pdf->GetY()),0,1,'C',0);
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				$pdf->SetXY(21, $pdf->GetY() - 2.4);
				$clause_text = transformStringQuote($clause['text']);
				$pdf->MultiCell(132, 5.9, $clause_text, 0, 1, 'L');
			}
			
		}else if ($pageNo == 7) {
			
			// Cultural Orientation dimension [index : 2] to [index : last index]
			for ($k=2; $k < count($cultural_orientation_dimension); $k++) { // Start at index 2 
				$dimension = $cultural_orientation_dimension[$k];
				$pdf->SetFont('SourceSansPro-Semibold','',14);
				if ($k==0) $pdf->SetXY(19, 19);
				else $pdf->SetXY(19, $pdf->GetY() + 7.4);
				$dimension_title = transformStringQuote($dimension['title']);
				$pdf->MultiCell(132, 7.4, $dimension_title, 0, 1, 'L');
				foreach ($dimension['clause'] as $clause) {
					$img_bullet = get_template_directory_uri() . '/images/bullet.png';
					$pdf->SetXY(20, $pdf->GetY() + 4);
					$pdf->Cell(0,0,$pdf->Image($img_bullet,$pdf->GetX(),$pdf->GetY()),0,1,'C',0);
					$pdf->SetFont('SourceSansPro-Regular','',11.8);
					$pdf->SetXY(21, $pdf->GetY() - 2.4);
					$clause_text = transformStringQuote($clause['text']);
					$pdf->MultiCell(132, 5.9, $clause_text, 0, 1, 'L');
				}
			}
			
		}else if ($pageNo == 8) {
			
			// Initiate Sliding Scale Value
			// Set up for PDF scale only
			$one_line_width = 15.96;
			$fist_line_start = 25.2;
			$the_man_on_center = ($one_line_width/2) - 1.3; // The man position from a beginning of a line
			$line_gap = 0.23; // A tiny space between line
			
			// Initiate Sliding Scale Value
			$all_score_lines = 10;
			$maximum_score_lines = $all_score_lines;
			$inverse_score_init = $maximum_score_lines + 1; // 6th is a start line for another side
			$team_average_icon_img = get_template_directory_uri() . '/images/team-average-icon.png';
			
			
			// Get Team Spread -----------------------------------
			$team_spread = array();
			
			// Objective & Subjective
			$sliding_scale_sit_begin = $inverse_score_init - ceil($sliding_max_scales_value['objective-communication'] * ($maximum_score_lines/$total_question));
			$sliding_scale_sit_end = ceil($sliding_max_scales_value['subjective-communication'] * ($maximum_score_lines/$total_question));
			$team_spread['spread_1'] = $sliding_scale_sit_end - $sliding_scale_sit_begin + 1; // +1 Because we have to add the beginning line to be the first
			$team_spread['sit_begin_1'] = $sliding_scale_sit_begin;
			if ($sliding_scale_sit_begin > $sliding_scale_sit_end) {  // Foul detect
				$team_spread['spread_1'] = 1;
				if ($sliding_max_scales_value['objective-communication'] < $sliding_max_scales_value['subjective-communication'])
					$team_spread['sit_begin_1'] = $sliding_scale_sit_end;
				else
					$team_spread['sit_begin_1'] = $sliding_scale_sit_begin;
			}
			
			// Assertive & Reflective
			$sliding_scale_sit_begin = $inverse_score_init - ceil($sliding_max_scales_value['assertive-communication'] * ($maximum_score_lines/$total_question));
			$sliding_scale_sit_end = ceil($sliding_max_scales_value['reflective-communication'] * ($maximum_score_lines/$total_question));		
			$team_spread['spread_2'] = $sliding_scale_sit_end - $sliding_scale_sit_begin + 1; // +1 Because we have to add the beginning line to be the first
			$team_spread['sit_begin_2'] = $sliding_scale_sit_begin;
			if ($sliding_scale_sit_begin > $sliding_scale_sit_end) {  // Foul detect
				$team_spread['spread_2'] = 1;
				if ($sliding_max_scales_value['assertive-communication'] < $sliding_max_scales_value['reflective-communication'])
					$team_spread['sit_begin_2'] = $sliding_scale_sit_end;
				else
					$team_spread['sit_begin_2'] = $sliding_scale_sit_begin;
			}
			
			// Accepting & Challenging
			$sliding_scale_sit_begin = $inverse_score_init - ceil($sliding_max_scales_value['accepting-behaviour'] * ($maximum_score_lines/$total_question));
			$sliding_scale_sit_end = ceil($sliding_max_scales_value['challenging-behaviour'] * ($maximum_score_lines/$total_question));
			$team_spread['spread_3'] = $sliding_scale_sit_end - $sliding_scale_sit_begin + 1;
			$team_spread['sit_begin_3'] = $sliding_scale_sit_begin;
			if ($sliding_scale_sit_begin > $sliding_scale_sit_end) {  // Foul detect
				$team_spread['spread_3'] = 1;
				if ($sliding_max_scales_value['accepting-behaviour'] < $sliding_max_scales_value['challenging-behaviour'])
					$team_spread['sit_begin_3'] = $sliding_scale_sit_end;
				else
					$team_spread['sit_begin_3'] = $sliding_scale_sit_begin;
			}
			
			// Result Oriented & Process Oriented
			$sliding_scale_sit_begin = $inverse_score_init - ceil($sliding_max_scales_value['result-oriented-behaviour'] * ($maximum_score_lines/$total_question));
			$sliding_scale_sit_end = ceil($sliding_max_scales_value['process-oriented-behaviour'] * ($maximum_score_lines/$total_question));
			$team_spread['spread_4'] = $sliding_scale_sit_end - $sliding_scale_sit_begin + 1;
			$team_spread['sit_begin_4'] = $sliding_scale_sit_begin;
			if ($sliding_scale_sit_begin > $sliding_scale_sit_end) {  // Foul detect
				$team_spread['spread_4'] = 1;
				if ($sliding_max_scales_value['result-oriented-behaviour'] < $sliding_max_scales_value['process-oriented-behaviour'])
					$team_spread['sit_begin_4'] = $sliding_scale_sit_end;
				else
					$team_spread['sit_begin_4'] = $sliding_scale_sit_begin;
			}
			
			// Open & Guarded
			$sliding_scale_sit_begin = $inverse_score_init - ceil($sliding_max_scales_value['open-behaviour'] * ($maximum_score_lines/$total_question));
			$sliding_scale_sit_end = ceil($sliding_max_scales_value['guarded-behaviour'] * ($maximum_score_lines/$total_question));
			$team_spread['spread_5'] = $sliding_scale_sit_end - $sliding_scale_sit_begin + 1;
			$team_spread['sit_begin_5'] = $sliding_scale_sit_begin;
			if ($sliding_scale_sit_begin > $sliding_scale_sit_end) {  // Foul detect
				$team_spread['spread_5'] = 1;
				if ($sliding_max_scales_value['open-behaviour'] < $sliding_max_scales_value['guarded-behaviour'])
					$team_spread['sit_begin_5'] = $sliding_scale_sit_end;
				else
					$team_spread['sit_begin_5'] = $sliding_scale_sit_begin;
			}
			
			$team_spread_summary = $team_spread['spread_1'] + $team_spread['spread_2'] + $team_spread['spread_3'] + $team_spread['spread_4'] + $team_spread['spread_5'];
			
			$dimension_number = 5;
			$team_spread_for_write = $icq_report->getTeamSpreadComponent($team_spread_summary, $all_score_lines, $dimension_number);
			
			$pdf->SetFont('SourceSansPro-Semibold','',21);
			$pdf->SetTextColor(0,174,239);
			$pdf->SetXY(47,58.5);
			$pdf->Write(0, strtoupper($team_spread_for_write['text']));
			$pdf->SetXY(109.5,$pdf->GetY()+9.5);
			$pdf->Write(0, $team_spread_for_write['percentage'] . ' %');
			// -----------------------------------------------------
			
			
			// Sliding Scales ******************************
			// Sliding Scale 1 : Objective & Subjective
			$spread = $team_spread['spread_1'];
			$spreadColor = $icq_report->getSpreadRGBColorForDimensionLine($spread);
			$rect_start = 25.2 + ($one_line_width * $team_spread['sit_begin_1']) - $one_line_width;
			$rect_long = $one_line_width * $spread;
			$pdf->SetFillColor($spreadColor['r'], $spreadColor['g'], $spreadColor['b']);
			$pdf->SetDrawColor($spreadColor['r'], $spreadColor['g'], $spreadColor['b']);
			$pdf->SetAlpha(0.7);
			$pdf->Rect($rect_start, 115.5, $rect_long, 2.6, 'DF');
			
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
			if (round($team_average_sliding_scale) <= 5) $intercultural_values_1 = 'objective';
			else $intercultural_values_1 = 'subjective';
			$the_man_standing_1 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_standing_1_position = $the_man_standing_1 + $the_man_on_center;
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($team_average_icon_img, 25.2 + $the_man_standing_1_position, 112.5), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
			// Sliding Scale 2 : Assertive & Reflective
			$spread = $team_spread['spread_2'];
			$spreadColor = $icq_report->getSpreadRGBColorForDimensionLine($spread);
			$rect_start = 25.2 + ($one_line_width * $team_spread['sit_begin_2']) - $one_line_width;
			$rect_long = $one_line_width * $spread;
			$pdf->SetFillColor($spreadColor['r'], $spreadColor['g'], $spreadColor['b']);
			$pdf->SetDrawColor($spreadColor['r'], $spreadColor['g'], $spreadColor['b']);
			$pdf->SetAlpha(0.7);
			$pdf->Rect($rect_start, 164.7, $rect_long, 2.6, 'DF');
			
			// Team average
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
			if (round($team_average_sliding_scale) <= 5) $intercultural_values_2 = 'assertive';
			else $intercultural_values_2 = 'reflective';
			$the_man_standing_2 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_standing_2_position = $the_man_standing_2 + $the_man_on_center;
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($team_average_icon_img, 25.2 + $the_man_standing_2_position, 161.7), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
		}else if ($pageNo == 9) {
			
			// Sliding Scale 3 : Accepting & Challenging
			$spread = $team_spread['spread_3'];
			$spreadColor = $icq_report->getSpreadRGBColorForDimensionLine($spread);			
			$rect_start = 25.2 + ($one_line_width * $team_spread['sit_begin_3']) - $one_line_width;
			$rect_long = $one_line_width * $spread;			
			$pdf->SetXY(25.2, 72.9);
			$pdf->SetFillColor($spreadColor['r'], $spreadColor['g'], $spreadColor['b']);
			$pdf->SetDrawColor($spreadColor['r'], $spreadColor['g'], $spreadColor['b']);
			$pdf->SetAlpha(0.7);
			$pdf->Rect($rect_start, 50.3, $rect_long, 2.6, 'DF');
			
			// Team average
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
			if (round($team_average_sliding_scale) <= 5) $intercultural_values_3 = 'accepting';
			else $intercultural_values_3 = 'challenging';
			$the_man_standing_3 = (round($team_average_sliding_scale) * $one_line_width)- $one_line_width - $line_gap;
			$the_man_standing_3_position = $the_man_standing_3 + $the_man_on_center;
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($team_average_icon_img, 25.2 + $the_man_standing_3_position, 47.3), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
			// Sliding Scale 4 : Result Oriented & Process Oriented
			$spread = $team_spread['spread_4'];
			$spreadColor = $icq_report->getSpreadRGBColorForDimensionLine($spread);			
			$rect_start = 25.2 + ($one_line_width * $team_spread['sit_begin_4']) - $one_line_width;
			$rect_long = $one_line_width * $spread;			
			$pdf->SetXY(25.2, 72.9);
			$pdf->SetFillColor($spreadColor['r'], $spreadColor['g'], $spreadColor['b']);
			$pdf->SetDrawColor($spreadColor['r'], $spreadColor['g'], $spreadColor['b']);
			$pdf->SetAlpha(0.7);
			$pdf->Rect($rect_start, 99.5, $rect_long, 2.6, 'DF');
			
			// Team average
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
			if (round($team_average_sliding_scale) <= 5) $intercultural_values_4 = 'result-oriented';
			else $intercultural_values_4 = 'process-oriented';
			$the_man_standing_4 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_standing_4_position = $the_man_standing_4 + $the_man_on_center;
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($team_average_icon_img, 25.2 + $the_man_standing_4_position, 96.5), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
			// Sliding Scale 5 : Open & Guarded
			$spread = $team_spread['spread_5'];
			$spreadColor = $icq_report->getSpreadRGBColorForDimensionLine($spread);			
			$rect_start = 25.2 + ($one_line_width * $team_spread['sit_begin_5']) - $one_line_width;
			$rect_long = $one_line_width * $spread;		
			$pdf->SetXY(25.2, 72.9);
			$pdf->SetFillColor($spreadColor['r'], $spreadColor['g'], $spreadColor['b']);
			$pdf->SetDrawColor($spreadColor['r'], $spreadColor['g'], $spreadColor['b']);
			$pdf->SetAlpha(0.7);
			$pdf->Rect($rect_start, 148.7, $rect_long, 2.6, 'DF');
			
			// Team average
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
			if (round($team_average_sliding_scale) <= 5) $intercultural_values_5 = 'open-behaviour';
			else $intercultural_values_5 = 'guarded-behaviour';
			$the_man_standing_5 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_standing_5_position = $the_man_standing_5 + $the_man_on_center;
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($team_average_icon_img, 25.2 + $the_man_standing_5_position, 145.7), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
		}else if ($pageNo == 10) {
			
			$circle_inverse = 100 - $team_spread_for_write['percentage'];
			$data = array('Diversity' => $team_spread_for_write['percentage'], 'Blind spot' => $circle_inverse);
			
			//Pie chart
			$pdf->SetFont('SourceSansPro-Semibold','',20);
			$pdf->SetTextColor(0,0,0); 
			$pdf->SetXY(52, 78);
			$col1 = array(0,174,239);
			$col2 = array(13,34,67);
			$pdf->SetDrawColor(255, 255, 255);
			$pdf->PieChart(158, 158, $data, '%l (%p)', array($col1,$col2));
			
			$pdf->SetFont('SourceSansPro-Semibold','',14);
			$pdf->SetTextColor(16,34,72);
			$pdf->SetXY(112.5,199.5);
			$pdf->Write(0, '-');
			$pdf->SetXY($pdf->GetX()+2.8,199.5);
			$pdf->Write(0, $circle_inverse . ' %');
			
			$pdf->SetFont('SourceSansPro-Semibold','',11);
			$pdf->SetTextColor(0,174,239); 
			$pdf->SetXY(93.7,207.5);
			$pdf->Write(0, $team_spread_for_write['text'] . ' Diversity');
			
			$pdf->SetFont('SourceSansPro-Semibold','',14);
			$pdf->SetTextColor(0,174,239); 
			$pdf->SetXY($pdf->GetX()+2.8,207.3);
			$pdf->Write(0, '-');
			$pdf->SetXY($pdf->GetX()+2.8,207.3);
			$pdf->Write(0, $team_spread_for_write['percentage'] . ' %');
			
		}else if ($pageNo == 11) {
			
			$pdf->SetTextColor(41,40,57); 
			
			// ******************* UNDERSTANDING THE UNDERLYING INTERCULTURAL VALUES AND DRIVERS *******************
			// Intercultural Values 1 : OBJECTIVE - SUBJECTIVE ----------
			$pdf->Cell(0, 0, $pdf->Image($team_average_icon_img, 25.2 + $the_man_standing_1_position, 65.3), 0, 1, 'C', 0);
						
			$args = array('post_type' => 'intercultural_values', 'name' => $intercultural_values_1, 'numberposts' => 1);
			$intercultural_values_post = get_posts($args);
			$intercultural_intro_text = get_field('intro_text', $intercultural_values_post[0]->ID);
			$pdf->SetFont('SourceSansPro-Regular','',11.8);
			$pdf->SetXY(19, 96.2);
			$intercultural_intro_text = transformStringQuote($intercultural_intro_text);
			$pdf->MultiCell(132, 5.65, $intercultural_intro_text, 0, 1, 'L');
			
			$intercultural_drivers = get_field('drivers', $intercultural_values_post[0]->ID);
			foreach ($intercultural_drivers as $driver) {
				$pdf->SetFont('SourceSansPro-Semibold','',11.8);
				$pdf->SetXY(19, $pdf->GetY()+4.5);
				$intercultural_drivers_title = transformStringQuote($driver['title']);
				$pdf->MultiCell(132, 6, $intercultural_drivers_title, 0, 1, 'L');
				
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				$pdf->SetXY(19, $pdf->GetY()+3.3);
				$intercultural_drivers_content = transformStringQuote($driver['content']);
				$pdf->MultiCell(132, 5.65, $intercultural_drivers_content, 0, 1, 'L');
			}
			// -----------------------		
		
		}else if ($pageNo == 12) {
			
			// Intercultural Values 2 : ASSERTIVE - REFLECTIVE ----------
			$pdf->Cell(0, 0, $pdf->Image($team_average_icon_img, 25.2 + $the_man_standing_2_position, 18.7), 0, 1, 'C', 0);
			
			$args = array('post_type' => 'intercultural_values', 'name' => $intercultural_values_2, 'numberposts' => 1);
			$intercultural_values_post = get_posts($args);
			$intercultural_intro_text = get_field('intro_text', $intercultural_values_post[0]->ID);
			$pdf->SetFont('SourceSansPro-Regular','',11.8);
			$pdf->SetXY(19, 45.2);
			$intercultural_intro_text = transformStringQuote($intercultural_intro_text);
			$pdf->MultiCell(132, 5.65, $intercultural_intro_text, 0, 1, 'L');
			
			$intercultural_drivers = get_field('drivers', $intercultural_values_post[0]->ID);
			foreach ($intercultural_drivers as $driver) {
				$pdf->SetFont('SourceSansPro-Semibold','',11.8);
				$pdf->SetXY(19, $pdf->GetY()+4.5);
				$intercultural_drivers_title = transformStringQuote($driver['title']);
				$pdf->MultiCell(132, 6, $intercultural_drivers_title, 0, 1, 'L');
				
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				$pdf->SetXY(19, $pdf->GetY()+3.3);
				$intercultural_drivers_content = transformStringQuote($driver['content']);
				$pdf->MultiCell(132, 5.65, $intercultural_drivers_content, 0, 1, 'L');
			
			}
			// -----------------------
			
		}else if ($pageNo == 13) {
			
			// Intercultural Values 3 : ACCEPTING - CHALLENGING ----------
			$pdf->Cell(0, 0, $pdf->Image($team_average_icon_img, 25.2 + $the_man_standing_3_position, 18.7), 0, 1, 'C', 0);
			
			$args = array('post_type' => 'intercultural_values', 'name' => $intercultural_values_3, 'numberposts' => 1);
			$intercultural_values_post = get_posts($args);
			$intercultural_intro_text = get_field('intro_text', $intercultural_values_post[0]->ID);
			$pdf->SetFont('SourceSansPro-Regular','',11.8);
			$pdf->SetXY(19, 45.2);
			$intercultural_intro_text = transformStringQuote($intercultural_intro_text);
			$pdf->MultiCell(132, 5.65, $intercultural_intro_text, 0, 1, 'L');
			
			$intercultural_drivers = get_field('drivers', $intercultural_values_post[0]->ID);
			foreach ($intercultural_drivers as $driver) {
				$pdf->SetFont('SourceSansPro-Semibold','',11.8);
				$pdf->SetXY(19, $pdf->GetY()+4.5);
				$driver_title = transformStringQuote($driver['title']);
				$pdf->MultiCell(132, 6, $driver_title, 0, 1, 'L');
				
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				$pdf->SetXY(19, $pdf->GetY()+3.3);
				$driver_content = transformStringQuote($driver['content']);
				$pdf->MultiCell(132, 5.65, $driver_content, 0, 1, 'L');
			}
			// -----------------------
			
		}else if ($pageNo == 14) {
			
			// Intercultural Values 4 : RESULT-ORIENTED - PROCESS-ORIENTED ----------
			$pdf->Cell(0, 0, $pdf->Image($team_average_icon_img, 25.2 + $the_man_standing_4_position, 18.7), 0, 1, 'C', 0);
		
			$args = array('post_type' => 'intercultural_values', 'name' => $intercultural_values_4, 'numberposts' => 1);
			$intercultural_values_post = get_posts($args);
			$intercultural_intro_text = get_field('intro_text', $intercultural_values_post[0]->ID);
			$pdf->SetFont('SourceSansPro-Regular','',11.8);
			$pdf->SetXY(19, 45.2);
			$intercultural_intro_text = transformStringQuote($intercultural_intro_text);
			$pdf->MultiCell(132, 5.65, $intercultural_intro_text, 0, 1, 'L');
			
			$intercultural_drivers = get_field('drivers', $intercultural_values_post[0]->ID);
			foreach ($intercultural_drivers as $driver) {
				$pdf->SetFont('SourceSansPro-Semibold','',11.8);
				$pdf->SetXY(19, $pdf->GetY()+4.5);
				$driver_title = transformStringQuote($driver['title']);
				$pdf->MultiCell(132, 6, $driver_title, 0, 1, 'L');
				
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				$pdf->SetXY(19, $pdf->GetY()+3.3);
				$driver_content = transformStringQuote($driver['content']);
				$pdf->MultiCell(132, 5.65, $driver_content, 0, 1, 'L');
			}
			// -----------------------
			
		}else if ($pageNo == 15) {
			
			// Intercultural Values 5 : OPEN BEHAVIOUR - GUARDED BEHAVIOUR ----------
			$pdf->Cell(0, 0, $pdf->Image($team_average_icon_img, 25.2 + $the_man_standing_5_position, 18.7), 0, 1, 'C', 0);
			
			$args = array('post_type' => 'intercultural_values', 'name' => $intercultural_values_5, 'numberposts' => 1);
			$intercultural_values_post = get_posts($args);
			$intercultural_intro_text = get_field('intro_text', $intercultural_values_post[0]->ID);
			$pdf->SetFont('SourceSansPro-Regular','',11.8);
			$pdf->SetXY(19, 45.2);
			$intercultural_intro_text = transformStringQuote($intercultural_intro_text);
			$pdf->MultiCell(132, 5.65, $intercultural_intro_text, 0, 1, 'L');
			
			$intercultural_drivers = get_field('drivers', $intercultural_values_post[0]->ID);
			
			// We can't use for loop, Because it was seperated  by template. So, we specify index of array of '$intercultural_drivers'
			// array index - 0
			$pdf->SetFont('SourceSansPro-Semibold','',11.8);
			$pdf->SetXY(19, $pdf->GetY()+4.5);
			$intercultural_drivers_title = transformStringQuote($intercultural_drivers[0]['title']);
			$pdf->MultiCell(132, 6, $intercultural_drivers_title, 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Regular','',11.8);
			$pdf->SetXY(19, $pdf->GetY()+3.3);
			$intercultural_drivers_content = transformStringQuote($intercultural_drivers[0]['content']);
			$pdf->MultiCell(132, 5.65, $intercultural_drivers_content, 0, 1, 'L');
			
			// array index - 1
			$pdf->SetFont('SourceSansPro-Semibold','',11.8);
			$pdf->SetXY(19, $pdf->GetY()+4.5);
			$intercultural_drivers_title = transformStringQuote($intercultural_drivers[1]['title']);
			$pdf->MultiCell(132, 6, $intercultural_drivers_title, 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Regular','',11.8);
			$pdf->SetXY(19, $pdf->GetY()+3.3);
			$intercultural_drivers_content = transformStringQuote($intercultural_drivers[1]['content']);
			$pdf->MultiCell(132, 5.65, $intercultural_drivers_content, 0, 1, 'L');
			
			// array index - 2
			if (!is_null($intercultural_drivers[2]['title'])) {
				$pdf->SetFont('SourceSansPro-Semibold','',11.8);
				$pdf->SetXY(19, $pdf->GetY()+4.5);
				$intercultural_drivers_title = transformStringQuote($intercultural_drivers[2]['title']);
				$pdf->MultiCell(132, 6, $intercultural_drivers_title, 0, 1, 'L');
				
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				$pdf->SetXY(19, $pdf->GetY()+3.3);
				$intercultural_drivers_content = transformStringQuote($intercultural_drivers[2]['content']);
				$pdf->MultiCell(132, 5.65, $intercultural_drivers_content, 0, 1, 'L');
			}
			
		}else if ($pageNo == 16) {
			
			// array index - 3
			if (!is_null($intercultural_drivers[3]['title'])) {
				$pdf->SetFont('SourceSansPro-Semibold','',11.8);
				$pdf->SetXY(19, 19);
				$intercultural_drivers_title = transformStringQuote($intercultural_drivers[3]['title']);
				$pdf->MultiCell(132, 6, $intercultural_drivers_title, 0, 1, 'L');
				
				$pdf->SetFont('SourceSansPro-Regular','',11.8);
				$pdf->SetXY(19, $pdf->GetY()+3.3);
				$intercultural_drivers_content = transformStringQuote($intercultural_drivers[3]['content']);
				$pdf->MultiCell(132, 5.65, $intercultural_drivers_content, 0, 1, 'L');
			}
			
		}
		
	}
	
	// Remove string space
	$group_name = str_replace(' ', '', $group_name);
	
	// Clean string
	$group_name = $icq_report->cleanStr($group_name);
	
	$generation_result_path = 'pdf/generation-team-results/Global-DISC-'. $group_name . '-' . $refered_id . '.pdf';
	$pdf->Output($generation_result_path,'F');
	
	if ( in_array('licensee', $user_roles) || in_array('administrator', $user_roles) || in_array('trainer', $user_roles) ) {
		$wpdb->update('wp_cohorts_groups', array('last_team_report_pdf_path' => $generation_result_path), array('id' => $refered_id));
	
	}else {

	}
	
	echo $generation_result_path;
?>