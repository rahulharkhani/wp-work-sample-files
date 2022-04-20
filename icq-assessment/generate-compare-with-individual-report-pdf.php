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
		
	}else {
		$participant_added = $icq_dashboard->get_participant_added();
	}
	
	/* Participant, who was compared with another participant */
	if (isset($_POST['id'])) $individual_url_id = $_POST['id'];
	else exit;
	$single_participant_added = $icq_dashboard->get_participant_added_by_id($individual_url_id);
	$single_participant_name = $single_participant_added[0]->participant_name;
	
	/* Participant 2, who was compare with another participant */
	if (isset($_POST['cid'])) $compared_individual_url_id = $_POST['cid'];
	else exit;
	$compared_single_participant_added = $icq_dashboard->get_participant_added_by_id($compared_individual_url_id);
	$compared_single_participant_name = $compared_single_participant_added[0]->participant_name;
	$compared_name = $compared_single_participant_name;
	
	if (!isset($compared_name) || $compared_name == '' || $compared_name == NULL) exit;
	
	$assessment_post_id = 70;
	$assessments_keys = get_field('assessments_keys', $assessment_post_id);
	$q_and_a = get_field('q_and_a', $assessment_post_id);
	$total_question = count($q_and_a);
	
	// Comparison
	$DISC_result = array();
	for ($c=1; $c<=2; $c++) {
		if ($c==1) {
			$_participant_added = $single_participant_added; // A single participant added who was compared.
			$idx_name = 'participant';
			
		}else if ($c==2) {
			$_participant_added = $compared_single_participant_added; // A single participant added who was compared with another.
			$idx_name = 'compared_participant';
		}
		
		$DISC_result[$idx_name] = $icq_report->getDISCResult($_participant_added, $assessment_post_id);
	}
	
	$D_quadrant_value = $DISC_result['participant']['D_quadrant_value'];
	$I_quadrant_value = $DISC_result['participant']['I_quadrant_value'];
	$S_quadrant_value = $DISC_result['participant']['S_quadrant_value'];
	$C_quadrant_value = $DISC_result['participant']['C_quadrant_value'];
	$individual_sliding_scales_value = $DISC_result['participant']['sliding_scales_value'];
	$the_text_id = $DISC_result['participant']['the_text_id'];	
	
	$compared_D_quadrant_value = $DISC_result['compared_participant']['D_quadrant_value'];
	$compared_I_quadrant_value = $DISC_result['compared_participant']['I_quadrant_value'];
	$compared_S_quadrant_value = $DISC_result['compared_participant']['S_quadrant_value'];
	$compared_C_quadrant_value = $DISC_result['compared_participant']['C_quadrant_value'];
	$compared_individual_sliding_scales_value = $DISC_result['compared_participant']['sliding_scales_value'];
	
	// Start generate PDF
	$pdf = new PDF_Diag();
	
	$pageCount = $pdf->setSourceFile('pdf/DISC-comparison-report.pdf');	
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
		
		$single_participant_name = toAscii($single_participant_name);
		$compared_name = toAscii($compared_name);
			
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
			$pdf->MultiCell(95, 5.65, transformStringQuote($single_participant_name), 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Regular','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(128.5,159.6);
			$pdf->MultiCell(75, 5.65, transformStringQuote($compared_name) , 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Regular','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(128.5,167.4);
			$pdf->MultiCell(75, 5.65, date('d/m/Y'), 0, 1, 'L');

		}else if ($pageNo == 4) {
			
			$write_text = $single_participant_name . ' (' . $compared_name . ')';
			
			$pdf->SetFont('SourceSansPro-Semibold','',16);
			$pdf->SetTextColor(41,40,57);
			$w = $pdf->GetStringWidth($write_text);
			$pdf->SetXY((210-$w)/2, 139);
			$pdf->Write(0, $write_text);
			
			$pdf->SetFont('SourceSansPro-Semibold','',18);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(48.7, 182.5);
			$pdf->Write(0, round($D_quadrant_value) . '(' . round($compared_D_quadrant_value).')%');
			
			$pdf->SetXY(139.2, 182.5);
			$pdf->Write(0, round($I_quadrant_value) . '(' . round($compared_I_quadrant_value).')%');
			
			$pdf->SetXY(139.2, 213.5);
			$pdf->Write(0, round($S_quadrant_value) . '(' . round($compared_S_quadrant_value).')%');
			
			$pdf->SetXY(48.7, 213.5);
			$pdf->Write(0, round($C_quadrant_value) . '(' . round($compared_C_quadrant_value).')%');
						
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
			
			// Initiate Individual Participant 1 assets
			$sliding_scales_value = $individual_sliding_scales_value[0];
			$individual_icon_img = get_template_directory_uri() . '/images/person1-icon.png';
			
			// Initiate Compared Individual Participant assets
			$compared_sliding_scales_value = $compared_individual_sliding_scales_value[0];
			$compared_individual_icon_img = get_template_directory_uri() . '/images/person2-icon.png';
			
			$pdf->Cell(0, 0, $pdf->Image($individual_icon_img, 25.2, 52), 0, 1, 'C', 0);
			$pdf->SetFont('SourceSansPro-Semibold','',18);
			$pdf->SetXY(33, 53);
			$pdf->SetTextColor(0,158,213);
			$pdf->MultiCell(132, 5.65, $single_participant_name, 0, 1, 'L');
			
			$pdf->Cell(0, 0, $pdf->Image($compared_individual_icon_img, 25.2, 63), 0, 1, 'C', 0);
			$pdf->SetFont('SourceSansPro-Semibold','',18);
			$pdf->SetXY(33, 64);
			$pdf->SetTextColor(41,40,57);
			$pdf->MultiCell(132, 5.65, $compared_name, 0, 1, 'L');
			
			/* 
			//Example html code if we need
			$pdf->SetFillColor(17,85,170);
			$pdf->Rect(20,20,80,80,"F");
			$pdf->SetFont("arial", '', 10);
			$pdf->MultiCell(80, 80, utf8_encode("<div style=\"width: 100%; background-color: #ff0000;\">xx</div>"), 1, "L", false, 1, 70, 70, true, 0, true, true, 0, 'T', false);
			*/
			
			// Sliding Scales ******************************
			// Sliding Scale 1 : Objective & Subjective
			// Individual Participant 1
			if ($sliding_scales_value['objective-communication'] > $sliding_scales_value['subjective-communication']) {
				$sliding_scale_sit = $inverse_score_init - ceil($sliding_scales_value['objective-communication'] * ($maximum_score_lines/$total_question));
				$intercultural_values_1 = 'objective';
			}else if ($sliding_scales_value['objective-communication'] < $sliding_scales_value['subjective-communication']) {
				$sliding_scale_sit = ceil($sliding_scales_value['subjective-communication'] * ($maximum_score_lines/$total_question));
				$intercultural_values_1 = 'subjective';
			}
			$the_man_personal_standing_1 = ($sliding_scale_sit * $one_line_width) - $one_line_width - $line_gap;
			$the_man_personal_standing_1_position = $the_man_personal_standing_1 + $the_man_on_center;
			
			// Compared Individual Participant
			if ($compared_sliding_scales_value['objective-communication'] > $compared_sliding_scales_value['subjective-communication']) {
				$sliding_scale_sit = $inverse_score_init - ceil($compared_sliding_scales_value['objective-communication'] * ($maximum_score_lines/$total_question));
			}else if ($compared_sliding_scales_value['objective-communication'] < $compared_sliding_scales_value['subjective-communication']) {
				$sliding_scale_sit = ceil($compared_sliding_scales_value['subjective-communication'] * ($maximum_score_lines/$total_question));
			}
			$c_the_man_personal_standing_1 = ($sliding_scale_sit * $one_line_width) - $one_line_width - $line_gap;
			$c_the_man_personal_standing_1_position = $c_the_man_personal_standing_1 + $the_man_on_center;
			
			if ($the_man_personal_standing_1_position == $c_the_man_personal_standing_1_position) {
				$the_man_personal_standing_1_position -= 2;
				$c_the_man_personal_standing_1_position += 2;
			}
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($individual_icon_img, 25.2 + $the_man_personal_standing_1_position, 112.5), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($compared_individual_icon_img, 25.2 + $c_the_man_personal_standing_1_position, 112.5), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
			// Sliding Scale 2 : Assertive & Reflective
			// Individual
			if ($sliding_scales_value['assertive-communication'] > $sliding_scales_value['reflective-communication']) {
				$sliding_scale_sit = $inverse_score_init - ceil($sliding_scales_value['assertive-communication'] * ($maximum_score_lines/$total_question));
				$intercultural_values_2 = 'assertive';
			}else if ($sliding_scales_value['assertive-communication'] < $sliding_scales_value['reflective-communication']) {
				$sliding_scale_sit = ceil($sliding_scales_value['reflective-communication'] * ($maximum_score_lines/$total_question));
				$intercultural_values_2 = 'reflective';
			}
			$the_man_personal_standing_2 = ($sliding_scale_sit * $one_line_width) - $one_line_width - $line_gap;
			$the_man_personal_standing_2_position = $the_man_personal_standing_2 + $the_man_on_center;
			
			// Compared Individual Participant
			if ($compared_sliding_scales_value['assertive-communication'] > $compared_sliding_scales_value['reflective-communication']) {
				$sliding_scale_sit = $inverse_score_init - ceil($compared_sliding_scales_value['assertive-communication'] * ($maximum_score_lines/$total_question));
			}else if ($compared_sliding_scales_value['assertive-communication'] < $compared_sliding_scales_value['reflective-communication']) {
				$sliding_scale_sit = ceil($compared_sliding_scales_value['reflective-communication'] * ($maximum_score_lines/$total_question));
			}
			$c_the_man_personal_standing_2 = ($sliding_scale_sit * $one_line_width) - $one_line_width - $line_gap;
			$c_the_man_personal_standing_2_position = $c_the_man_personal_standing_2 + $the_man_on_center;
			
			if ($the_man_personal_standing_2_position == $c_the_man_personal_standing_2_position) {
				$the_man_personal_standing_2_position -= 2;
				$c_the_man_personal_standing_2_position += 2;
			}
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($individual_icon_img, 25.2 + $the_man_personal_standing_2_position, 161.7), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($compared_individual_icon_img, 25.2 + $c_the_man_personal_standing_2_position, 161.7), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
		}else if ($pageNo == 6) {
			
			// Sliding Scale 3 : Accepting & Challenging
			// Individual
			if ($sliding_scales_value['accepting-behaviour'] > $sliding_scales_value['challenging-behaviour']) {
				$sliding_scale_sit = $inverse_score_init - ceil($sliding_scales_value['accepting-behaviour'] * ($maximum_score_lines/$total_question));
				$intercultural_values_3 = 'accepting';
			}else if ($sliding_scales_value['accepting-behaviour'] < $sliding_scales_value['challenging-behaviour']) {
				$sliding_scale_sit = ceil($sliding_scales_value['challenging-behaviour'] * ($maximum_score_lines/$total_question));
				$intercultural_values_3 = 'challenging';
			}
			$the_man_personal_standing_3 = ($sliding_scale_sit * $one_line_width) - $one_line_width - $line_gap;
			$the_man_personal_standing_3_position = $the_man_personal_standing_3 + $the_man_on_center;
			
			// Compared Individual Participant
			if ($compared_sliding_scales_value['accepting-behaviour'] > $compared_sliding_scales_value['challenging-behaviour']) {
				$sliding_scale_sit = $inverse_score_init - ceil($compared_sliding_scales_value['accepting-behaviour'] * ($maximum_score_lines/$total_question));
			}else if ($compared_sliding_scales_value['accepting-behaviour'] < $compared_sliding_scales_value['challenging-behaviour']) {
				$sliding_scale_sit = ceil($compared_sliding_scales_value['challenging-behaviour'] * ($maximum_score_lines/$total_question));
			}
			$c_the_man_personal_standing_3 = ($sliding_scale_sit * $one_line_width) - $one_line_width - $line_gap;
			$c_the_man_personal_standing_3_position = $c_the_man_personal_standing_3 + $the_man_on_center;
			
			if ($the_man_personal_standing_3_position == $c_the_man_personal_standing_3_position) {
				$the_man_personal_standing_3_position -= 2;
				$c_the_man_personal_standing_3_position += 2;
			}
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($individual_icon_img, 25.2 + $the_man_personal_standing_3_position, 47.3), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($compared_individual_icon_img, 25.2 + $c_the_man_personal_standing_3_position, 47.3), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
			// Sliding Scale 4 : Result Oriented & Process Oriented
			// Individual
			if ($sliding_scales_value['result-oriented-behaviour'] > $sliding_scales_value['process-oriented-behaviour']) {
				$sliding_scale_sit = $inverse_score_init - ceil($sliding_scales_value['result-oriented-behaviour'] * ($maximum_score_lines/$total_question));
				$intercultural_values_4 = 'result-oriented';
			}else if ($sliding_scales_value['result-oriented-behaviour'] < $sliding_scales_value['process-oriented-behaviour']) {
				$sliding_scale_sit = ceil($sliding_scales_value['process-oriented-behaviour'] * ($maximum_score_lines/$total_question));
				$intercultural_values_4 = 'process-oriented';
			}
			$the_man_personal_standing_4 = ($sliding_scale_sit * $one_line_width) - $one_line_width - $line_gap;
			$the_man_personal_standing_4_position = $the_man_personal_standing_4 + $the_man_on_center;
			
			// Compared Individual Participant
			if ($compared_sliding_scales_value['result-oriented-behaviour'] > $compared_sliding_scales_value['process-oriented-behaviour']) {
				$sliding_scale_sit = $inverse_score_init - ceil($compared_sliding_scales_value['result-oriented-behaviour'] * ($maximum_score_lines/$total_question));
			}else if ($compared_sliding_scales_value['result-oriented-behaviour'] < $compared_sliding_scales_value['process-oriented-behaviour']) {
				$sliding_scale_sit = ceil($compared_sliding_scales_value['process-oriented-behaviour'] * ($maximum_score_lines/$total_question));
			}
			$c_the_man_personal_standing_4 = ($sliding_scale_sit * $one_line_width) - $one_line_width - $line_gap;
			$c_the_man_personal_standing_4_position = $c_the_man_personal_standing_4 + $the_man_on_center;
			
			if ($the_man_personal_standing_4_position == $c_the_man_personal_standing_4_position) {
				$the_man_personal_standing_4_position -= 2;
				$c_the_man_personal_standing_4_position += 2;
			}
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($individual_icon_img, 25.2 + $the_man_personal_standing_4_position, 96.5), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($compared_individual_icon_img, 25.2 + $c_the_man_personal_standing_4_position, 96.5), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
			// Sliding Scale 5 : Open & Guarded
			// Individual
			if ($sliding_scales_value['open-behaviour'] > $sliding_scales_value['guarded-behaviour']) {
				$sliding_scale_sit = $inverse_score_init - ceil($sliding_scales_value['open-behaviour'] * ($maximum_score_lines/$total_question));
				$intercultural_values_5 = 'open-behaviour';
			}else if ($sliding_scales_value['open-behaviour'] < $sliding_scales_value['guarded-behaviour']) {
				$sliding_scale_sit = ceil($sliding_scales_value['guarded-behaviour'] * ($maximum_score_lines/$total_question));
				$intercultural_values_5 = 'guarded-behaviour';
			}
			$the_man_personal_standing_5 = ($sliding_scale_sit * $one_line_width) - $one_line_width - $line_gap;
			$the_man_personal_standing_5_position = $the_man_personal_standing_5 + $the_man_on_center;
			
			// Compared Individual Participant
			if ($compared_sliding_scales_value['open-behaviour'] > $compared_sliding_scales_value['guarded-behaviour']) {
				$sliding_scale_sit = $inverse_score_init - ceil($compared_sliding_scales_value['open-behaviour'] * ($maximum_score_lines/$total_question));
			}else if ($compared_sliding_scales_value['open-behaviour'] < $compared_sliding_scales_value['guarded-behaviour']) {
				$sliding_scale_sit = ceil($compared_sliding_scales_value['guarded-behaviour'] * ($maximum_score_lines/$total_question));
			}
			$c_the_man_personal_standing_5 = ($sliding_scale_sit * $one_line_width) - $one_line_width - $line_gap;
			$c_the_man_personal_standing_5_position = $c_the_man_personal_standing_5 + $the_man_on_center;
			
			if ($the_man_personal_standing_5_position == $c_the_man_personal_standing_5_position) {
				$the_man_personal_standing_5_position -= 2;
				$c_the_man_personal_standing_5_position += 2;
			}
			$pdf->SetAlpha(1);
			$pdf->Cell(0, 0, $pdf->Image($individual_icon_img, 25.2 + $the_man_personal_standing_5_position, 145.7), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($compared_individual_icon_img, 25.2 + $c_the_man_personal_standing_5_position, 145.7), 0, 1, 'C', 0);
			// --------------------------------------------------------------------------------------------------------------------
			
		}else if ($pageNo == 7) {
			
			$pdf->SetTextColor(41,40,57); 
			
			// ******************* UNDERSTANDING THE UNDERLYING INTERCULTURAL VALUES AND DRIVERS *******************
			// Intercultural Values 1 : OBJECTIVE - SUBJECTIVE ----------
			$pdf->Cell(0, 0, $pdf->Image($individual_icon_img, 25.2 + $the_man_personal_standing_1_position, 64.6), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($compared_individual_icon_img, 25.2 + $c_the_man_personal_standing_1_position, 64.6), 0, 1, 'C', 0);
			
		}else if ($pageNo == 8) {
			
			// Intercultural Values 2 : ASSERTIVE - REFLECTIVE ----------
			$pdf->Cell(0, 0, $pdf->Image($individual_icon_img, 25.2 + $the_man_personal_standing_2_position, 19.8), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($compared_individual_icon_img, 25.2 + $c_the_man_personal_standing_2_position, 19.8), 0, 1, 'C', 0);
			
		}else if ($pageNo == 9) {
			
			// Intercultural Values 3 : ACCEPTING - CHALLENGING ----------
			$pdf->Cell(0, 0, $pdf->Image($individual_icon_img, 25.2 + $the_man_personal_standing_3_position, 19.8), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($compared_individual_icon_img, 25.2 + $c_the_man_personal_standing_3_position, 19.8), 0, 1, 'C', 0);
			
		}else if ($pageNo == 10) {
			
			// Intercultural Values 4 : RESULT-ORIENTED - PROCESS-ORIENTED ----------
			$pdf->Cell(0, 0, $pdf->Image($individual_icon_img, 25.2 + $the_man_personal_standing_4_position, 19.8), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($compared_individual_icon_img, 25.2 + $c_the_man_personal_standing_4_position, 19.8), 0, 1, 'C', 0);
			
		}else if ($pageNo == 11) {
			
			// Intercultural Values 5 : OPEN BEHAVIOUR - GUARDED BEHAVIOUR ----------
			$pdf->Cell(0, 0, $pdf->Image($individual_icon_img, 25.2 + $the_man_personal_standing_5_position, 19.8), 0, 1, 'C', 0);
			$pdf->Cell(0, 0, $pdf->Image($compared_individual_icon_img, 25.2 + $c_the_man_personal_standing_5_position, 19.8), 0, 1, 'C', 0);
			
		}
	}
	
	// Remove string space
	$single_participant_name = str_replace(' ', '', $single_participant_name);
	$compared_name = str_replace(' ', '', $compared_name);
	
	// Clean string
	$single_participant_name = $icq_report->cleanStr($single_participant_name);
	$compared_name = $icq_report->cleanStr($compared_name);
	
	$generation_result_path = 'pdf/generation-comparsion-results/Global-DISC-comparsion-' . $single_participant_name . '-' . $compared_name . '.pdf';
	$pdf->Output($generation_result_path,'F');
	
	echo get_template_directory_uri() . '/' . $generation_result_path;
?>