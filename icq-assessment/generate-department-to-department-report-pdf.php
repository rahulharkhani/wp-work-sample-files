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
	
	// error_reporting(E_ALL);
	// ini_set('display_errors', -1);
	
	global $wpdb;
	global $icq_dashboard;
	global $icq_report;
	
	$user = wp_get_current_user();
	$user_roles = $user->roles;

	if (isset($_POST['gid'])) $group_id = $_POST['gid'];
	if (isset($_POST['Department1'])) $Department1 = $_POST['Department1'];
	if (isset($_POST['Department2'])) $Department2 = $_POST['Department2'];
	
	if ( in_array('licensee', $user_roles) ) {
		if (isset($_POST['gid'])) $group_id = $_POST['gid'];
		else exit;
	
		$participant_added_1 = $icq_dashboard->get_participant_added_by_group_id($group_id, $Department1);
		$participant_added_2 = $icq_dashboard->get_participant_added_by_group_id($group_id, $Department2);

		$cohort_group_data = $icq_dashboard->get_cohort_group_data($group_id);
		$group_name = $cohort_group_data->group_name;
		$group_owner_name = $cohort_group_data->group_owner_name;

	}else {

		$participant_added_1 = $icq_dashboard->get_participant_added_with_department($Department1);
		$participant_added_2 = $icq_dashboard->get_participant_added_with_department($Department2);
		$user = wp_get_current_user();
		$group_name = $user->display_name . ' Group';
		$group_owner_name = $user->display_name;
	}

	if (!isset($group_name) || $group_name == '' || $group_name == NULL) exit;
	if (!isset($Department1) || $Department1 == '' || $Department1 == NULL) exit;
	if (!isset($Department2) || $Department2 == '' || $Department2 == NULL) exit;

	$assessment_post_id = 70;
	$assessments_keys = get_field('assessments_keys', $assessment_post_id);
	$q_and_a = get_field('q_and_a', $assessment_post_id);
	$total_question = count($q_and_a);

	// Comparison
	$DISC_result = array();

	for ($c = 1; $c <= 2; $c ++) {
		if ($c==1) {
			$_participant_added = $participant_added_1; // A single participant added who was compared.
			$idx_name = 'department1';
			
		} else if ($c==2) {
			$_participant_added = $participant_added_2; // Participants added in group.
			$idx_name = 'department2';
		}

		$flag_valid = false;

		foreach ($_participant_added as $p_added) {
			if ($p_added->used == 1) {
				$flag_valid = true;
				break;
			}
		}

		if ($flag_valid == false) {
			echo '#empty';
			exit;
		}
		$DISC_result[$idx_name] = $icq_report->getDISCResult($_participant_added, $assessment_post_id);
	}
	$D_quadrant_value = $DISC_result['department1']['D_quadrant_value'];
	$I_quadrant_value = $DISC_result['department1']['I_quadrant_value'];
	$S_quadrant_value = $DISC_result['department1']['S_quadrant_value'];
	$C_quadrant_value = $DISC_result['department1']['C_quadrant_value'];
	$team1_sliding_scales_value = $DISC_result['department1']['sliding_scales_value'];
	$participant_counter_1 = $DISC_result['department1']['participant_counter'];

	$group_D_quadrant_value = $DISC_result['department2']['D_quadrant_value'];
	$group_I_quadrant_value = $DISC_result['department2']['I_quadrant_value'];
	$group_S_quadrant_value = $DISC_result['department2']['S_quadrant_value'];
	$group_C_quadrant_value = $DISC_result['department2']['C_quadrant_value'];
	$team2_sliding_scales_value = $DISC_result['department2']['sliding_scales_value'];
	$participant_counter = $DISC_result['department2']['participant_counter'];

	// Start generate PDF
	$pdf = new PDF_Diag();
	
	$pageCount = $pdf->setSourceFile('pdf/DISC-comparison-report.pdf');	// pdf/Global-DISC-comparsion.pdf
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
		
		$Department1 = toAscii($Department1);
		$Department2 = toAscii($Department2);
		
		if ($pageNo == 1) {
			// Add Subject text for comparison
			$pdf->SetFont('SourceSansPro-Semibold','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(82,152);
			$pdf->MultiCell(95, 5.65, 'NAME', 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Semibold','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(82,159.6);
			$pdf->MultiCell(75, 5.65, 'COMPARED WITH', 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Semibold','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(82,167.4);
			$pdf->MultiCell(75, 5.65, 'DATE', 0, 1, 'L');
			//--------------------------------------------
			
			$pdf->SetFont('SourceSansPro-Regular','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(128.5,152);
			$pdf->MultiCell(95, 5.65, transformStringQuote($Department1), 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Regular','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(128.5,159.6);
			$pdf->MultiCell(75, 5.65, transformStringQuote($Department2) , 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Regular','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(128.5,167.4);
			$pdf->MultiCell(75, 5.65, date('d/m/Y'), 0, 1, 'L');
			
		}else if ($pageNo == 4) {
			
			$write_text = $Department1 . ' (' . $Department2 . ')';
			
			$pdf->SetFont('SourceSansPro-Semibold','',16);
			$pdf->SetTextColor(41,40,57);
			$w = $pdf->GetStringWidth($write_text);
			$pdf->SetXY((210-$w)/2, 139);
			$pdf->Write(0, $write_text);
			
			$pdf->SetFont('SourceSansPro-Semibold','',18);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(48.7, 182.5);
			$pdf->Write(0, round($D_quadrant_value) . '(' . round($group_D_quadrant_value).')%');
			
			$pdf->SetXY(139.2, 182.5);
			$pdf->Write(0, round($I_quadrant_value) . '(' . round($group_I_quadrant_value).')%');
			
			$pdf->SetXY(139.2, 213.5);
			$pdf->Write(0, round($S_quadrant_value) . '(' . round($group_S_quadrant_value).')%');
			
			$pdf->SetXY(48.7, 213.5);
			$pdf->Write(0, round($C_quadrant_value) . '(' . round($group_C_quadrant_value).')%');
			
		}else if ($pageNo == 5) {
			
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
			
			// Initiate Team assets
			$group2_average_icon_img = get_template_directory_uri() . '/images/team-average-icon.png';			
			
			// Initiate Individual assets
			$group1_average_icon_img = get_template_directory_uri() . '/images/person1-icon.png';
			
			$pdf->Cell(0, 0, $pdf->Image($group1_average_icon_img, 25.2, 52), 0, 1, 'C', 0);
			$pdf->SetFont('SourceSansPro-Semibold','',18);
			$pdf->SetXY(33, 53);
			$pdf->SetTextColor(0,158,213);
			$pdf->MultiCell(132, 5.65, $Department1, 0, 1, 'L');
			
			$pdf->Cell(0, 0, $pdf->Image($group2_average_icon_img, 25.2, 63), 0, 1, 'C', 0);
			$pdf->SetFont('SourceSansPro-Semibold','',18);
			$pdf->SetXY(33, 64);
			$pdf->SetTextColor(41,40,57);
			$pdf->MultiCell(132, 5.65, $Department2, 0, 1, 'L');
			
			// Set position of first group....
			$sliding_scale_sit = 0;

			foreach ($team1_sliding_scales_value as $_value) {
				if ($_value['objective-communication'] > $_value['subjective-communication']) {
					$sliding_scale_sit += $inverse_score_init - ceil($_value['objective-communication'] * ($maximum_score_lines/$total_question));
				}else if ($_value['objective-communication'] < $_value['subjective-communication']) {
					$sliding_scale_sit += ceil($_value['subjective-communication'] * ($maximum_score_lines/$total_question));
				}
			}
			$team_average_sliding_scale = $sliding_scale_sit/$participant_counter_1;
			$the_man_personal_standing_1 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_personal_standing_1_position = $the_man_personal_standing_1 + $the_man_on_center;
			
			// Set position of second group....
			$p_sliding_scale_sit = 0;
			foreach ($team2_sliding_scales_value as $_value) {				
				if ($_value['objective-communication'] > $_value['subjective-communication']) {
					$p_sliding_scale_sit += $inverse_score_init - ceil($_value['objective-communication'] * ($maximum_score_lines/$total_question));
				}else if ($_value['objective-communication'] < $_value['subjective-communication']) {
					$p_sliding_scale_sit += ceil($_value['subjective-communication'] * ($maximum_score_lines/$total_question));
				}
			}
			$team_average_sliding_scale = $p_sliding_scale_sit/$participant_counter;
			$the_man_standing_1 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_standing_1_position = $the_man_standing_1 + $the_man_on_center;
			
			if ($the_man_standing_1_position == $the_man_personal_standing_1_position) {
				$the_man_standing_1_position += 2;
				$the_man_personal_standing_1_position -= 2;
			}
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($group1_average_icon_img, 25.2 + $the_man_personal_standing_1_position, 112.5), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($group2_average_icon_img, 25.2 + $the_man_standing_1_position, 112.5), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
			// Sliding Scale 2 : Assertive & Reflective
			// First group...
			$sliding_scale_sit = 0;

			foreach ($team1_sliding_scales_value as $_value) {
				if ($_value['assertive-communication'] > $_value['reflective-communication']) {
					$sliding_scale_sit += $inverse_score_init - ceil($_value['assertive-communication'] * ($maximum_score_lines/$total_question));
				}else if ($_value['assertive-communication'] < $_value['reflective-communication']) {
					$sliding_scale_sit += ceil($_value['reflective-communication'] * ($maximum_score_lines/$total_question));
				}
			}
			$team_average_sliding_scale = $sliding_scale_sit/$participant_counter_1;
			$the_man_personal_standing_2 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_personal_standing_2_position = $the_man_personal_standing_2 + $the_man_on_center;
			
			// Team average
			// Second group....
			$p_sliding_scale_sit = 0;

			foreach ($team2_sliding_scales_value as $_value) {				
				if ($_value['assertive-communication'] > $_value['reflective-communication']) {
					$p_sliding_scale_sit += $inverse_score_init - ceil($_value['assertive-communication'] * ($maximum_score_lines/$total_question));
				}else if ($_value['assertive-communication'] < $_value['reflective-communication']) {
					$p_sliding_scale_sit += ceil($_value['reflective-communication'] * ($maximum_score_lines/$total_question));
				}
			}
			$team_average_sliding_scale = $p_sliding_scale_sit/$participant_counter;
			$the_man_standing_2 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_standing_2_position = $the_man_standing_2 + $the_man_on_center;
			
			if ($the_man_standing_2_position == $the_man_personal_standing_2_position) {
				$the_man_standing_2_position += 2;
				$the_man_personal_standing_2_position -= 2;
			}
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($group1_average_icon_img, 25.2 + $the_man_personal_standing_2_position, 161.7), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($group2_average_icon_img, 25.2 + $the_man_standing_2_position, 161.7), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
		}else if ($pageNo == 6){
			
			// Sliding Scale 3 : Accepting & Challenging
			// First group...

			$sliding_scale_sit = 0;

			foreach ($team1_sliding_scales_value as $_value) {
				if ($_value['accepting-behaviour'] > $_value['challenging-behaviour']) {
					$sliding_scale_sit += $inverse_score_init - ceil($_value['accepting-behaviour'] * ($maximum_score_lines/$total_question));
				}else if ($_value['accepting-behaviour'] < $_value['challenging-behaviour']) {
					$sliding_scale_sit += ceil($_value['challenging-behaviour'] * ($maximum_score_lines/$total_question));
				}
			}
			$team_average_sliding_scale = $sliding_scale_sit/$participant_counter_1;
			$the_man_personal_standing_3 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_personal_standing_3_position = $the_man_personal_standing_3 + $the_man_on_center;
			
			// Team average
			// Second group...
			$p_sliding_scale_sit = 0;

			foreach ($team2_sliding_scales_value as $_value) {				
				if ($_value['accepting-behaviour'] > $_value['challenging-behaviour']) {
					$p_sliding_scale_sit += $inverse_score_init - ceil($_value['accepting-behaviour'] * ($maximum_score_lines/$total_question));
				}else if ($_value['accepting-behaviour'] < $_value['challenging-behaviour']) {
					$p_sliding_scale_sit += ceil($_value['challenging-behaviour'] * ($maximum_score_lines/$total_question));
				}
			}
			$team_average_sliding_scale = $p_sliding_scale_sit/$participant_counter;
			$the_man_standing_3 = (round($team_average_sliding_scale) * $one_line_width)- $one_line_width - $line_gap;
			$the_man_standing_3_position = $the_man_standing_3 + $the_man_on_center;
			
			if ($the_man_standing_3_position == $the_man_personal_standing_3_position) {
				$the_man_standing_3_position += 2;
				$the_man_personal_standing_3_position -= 2;
			}
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($group1_average_icon_img, 25.2 + $the_man_personal_standing_3_position, 47.3), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($group2_average_icon_img, 25.2 + $the_man_standing_3_position, 47.3), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
			// Sliding Scale 4 : Result Oriented & Process Oriented
			// First group...

			$sliding_scale_sit = 0;

			foreach ($team1_sliding_scales_value as $_value) {
				if ($_value['result-oriented-behaviour'] > $_value['process-oriented-behaviour']) {
					$sliding_scale_sit += $inverse_score_init - ceil($_value['result-oriented-behaviour'] * ($maximum_score_lines/$total_question));
				}else if ($_value['result-oriented-behaviour'] < $_value['process-oriented-behaviour']) {
					$sliding_scale_sit += ceil($_value['process-oriented-behaviour'] * ($maximum_score_lines/$total_question));
				}
			}
			$team_average_sliding_scale = $sliding_scale_sit/$participant_counter_1;
			$the_man_personal_standing_4 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_personal_standing_4_position = $the_man_personal_standing_4 + $the_man_on_center;
			
			// Team average
			// So, We loop in each participant to compare them one by one
			$p_sliding_scale_sit = 0;
			foreach ($team2_sliding_scales_value as $_value) {				
				if ($_value['result-oriented-behaviour'] > $_value['process-oriented-behaviour']) {
					$p_sliding_scale_sit += $inverse_score_init - ceil($_value['result-oriented-behaviour'] * ($maximum_score_lines/$total_question));
				}else if ($_value['result-oriented-behaviour'] < $_value['process-oriented-behaviour']) {
					$p_sliding_scale_sit += ceil($_value['process-oriented-behaviour'] * ($maximum_score_lines/$total_question));
				}
			}
			$team_average_sliding_scale = $p_sliding_scale_sit/$participant_counter;			
			$the_man_standing_4 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_standing_4_position = $the_man_standing_4 + $the_man_on_center;
			
			if ($the_man_standing_4_position == $the_man_personal_standing_4_position) {
				$the_man_standing_4_position +=  2;
				$the_man_personal_standing_4_position -=  2;
			}
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($group1_average_icon_img, 25.2 + $the_man_personal_standing_4_position, 96.5), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($group2_average_icon_img, 25.2 + $the_man_standing_4_position, 96.5), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
			// Sliding Scale 5 : Open & Guarded
			// First group...

			$sliding_scale_sit = 0;

			foreach ($team1_sliding_scales_value as $_value) {
				if ($_value['open-behaviour'] > $_value['guarded-behaviour']) {
					$sliding_scale_sit += $inverse_score_init - ceil($_value['open-behaviour'] * ($maximum_score_lines/$total_question));
				}else if ($_value['open-behaviour'] < $_value['guarded-behaviour']) {
					$sliding_scale_sit += ceil($_value['guarded-behaviour'] * ($maximum_score_lines/$total_question));
				}
			}
			$team_average_sliding_scale = $sliding_scale_sit/$participant_counter_1;
			$the_man_personal_standing_5 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_personal_standing_5_position = $the_man_personal_standing_5 + $the_man_on_center;
			
			// Team average
			// So, We loop in each participant to compare them one by one
			$p_sliding_scale_sit = 0;
			foreach ($team2_sliding_scales_value as $_value) {				
				if ($_value['open-behaviour'] > $_value['guarded-behaviour']) {
					$p_sliding_scale_sit += $inverse_score_init - ceil($_value['open-behaviour'] * ($maximum_score_lines/$total_question));
				}else if ($_value['open-behaviour'] < $_value['guarded-behaviour']) {
					$p_sliding_scale_sit += ceil($_value['guarded-behaviour'] * ($maximum_score_lines/$total_question));
				}
			}
			$team_average_sliding_scale = $p_sliding_scale_sit/$participant_counter;			
			$the_man_standing_5 = (round($team_average_sliding_scale) * $one_line_width) - $one_line_width - $line_gap;
			$the_man_standing_5_position = $the_man_standing_5 + $the_man_on_center;
			
			if ($the_man_standing_5_position == $the_man_personal_standing_5_position) {
				$the_man_standing_5_position += 2;
				$the_man_personal_standing_5_position -= 2;
			}
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($group1_average_icon_img, 25.2 + $the_man_personal_standing_5_position, 145.7), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($group2_average_icon_img, 25.2 + $the_man_standing_5_position, 145.7), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
		}else if ($pageNo == 7) {
			
			$pdf->SetTextColor(41,40,57); 
			
			// ******************* UNDERSTANDING THE UNDERLYING INTERCULTURAL VALUES AND DRIVERS *******************
			// Intercultural Values 1 : OBJECTIVE - SUBJECTIVE ----------
			$pdf->Cell(0, 0, $pdf->Image($group1_average_icon_img, 25.2 + $the_man_personal_standing_1_position, 64.6), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($group2_average_icon_img, 25.2 + $the_man_standing_1_position, 64.6), 0, 1, 'C', 0);
			
		}else if ($pageNo == 8) {
			
			// Intercultural Values 2 : ASSERTIVE - REFLECTIVE ----------
			$pdf->Cell(0, 0, $pdf->Image($group1_average_icon_img, 25.2 + $the_man_personal_standing_2_position, 19.8), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($group2_average_icon_img, 25.2 + $the_man_standing_2_position, 19.8), 0, 1, 'C', 0);
			
		}else if ($pageNo == 9) {
			
			// Intercultural Values 3 : ACCEPTING - CHALLENGING ----------
			$pdf->Cell(0, 0, $pdf->Image($group1_average_icon_img, 25.2 + $the_man_personal_standing_3_position, 19.8), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($group2_average_icon_img, 25.2 + $the_man_standing_3_position, 19.8), 0, 1, 'C', 0);
			
		}else if ($pageNo == 10) {
			
			// Intercultural Values 4 : RESULT-ORIENTED - PROCESS-ORIENTED ----------
			$pdf->Cell(0, 0, $pdf->Image($group1_average_icon_img, 25.2 + $the_man_personal_standing_4_position, 19.8), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($group2_average_icon_img, 25.2 + $the_man_standing_4_position, 19.8), 0, 1, 'C', 0);
			
		}else if ($pageNo == 11) {
			
			// Intercultural Values 5 : OPEN BEHAVIOUR - GUARDED BEHAVIOUR ----------
			$pdf->Cell(0, 0, $pdf->Image($group1_average_icon_img, 25.2 + $the_man_personal_standing_5_position, 19.8), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($group2_average_icon_img, 25.2 + $the_man_standing_5_position, 19.8), 0, 1, 'C', 0);
			
		}
	}
	
	

	// Remove string space
	$Department2 = str_replace(' ', '', $Department2);
	$Department1 = str_replace(' ', '', $Department1);
	
	// Clean string
	$Department2 = $icq_report->cleanStr($Department2);
	$Department1 = $icq_report->cleanStr($Department1);
	
	$generation_result_path = 'pdf/Global-DISC-comparsion-'.$Department1.'-'.$Department2.'.pdf';
	$pdf->Output($generation_result_path,'F');
	
	echo get_template_directory_uri() . '/' . $generation_result_path;
?>