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
	
	if (isset($_POST['POSTID'])) $POSTID = $_POST['POSTID'];

	//if ( in_array('licensee', $user_roles) || in_array('trainer', $user_roles) ) {
	if ( in_array('licensee', $user_roles) ) {
		if (isset($_POST['cid'])) $compared_individual_url_id = $_POST['cid'];
		else exit;
	
		$compared_single_participant_added = $icq_dashboard->get_participant_added_by_id($compared_individual_url_id);
		$compared_single_participant_name = $compared_single_participant_added[0]->participant_name;
		$compared_name = $compared_single_participant_name;
		
	}else {
		$participant_added = $icq_dashboard->get_participant_added();
	}
	
	/* Participant 2, who was compare with another participant */
	if (isset($_POST['cid'])) $compared_individual_url_id = $_POST['cid'];
	else exit;
	
	if (!isset($compared_name) || $compared_name == '' || $compared_name == NULL) exit;
	
	$assessment_post_id = 70;
	$assessments_keys = get_field('assessments_keys', $assessment_post_id);
	$q_and_a = get_field('q_and_a', $assessment_post_id);
	$total_question = count($q_and_a);
	
	// Comparison
	$DISC_result = array();
	for ($c=1; $c<=2; $c++) {
		if ($c==1) {
			$_participant_added = $compared_single_participant_added; // A single participant added who was compared with another.
			$idx_name = 'compared_participant';
			$DISC_result[$idx_name] = $icq_report->getDISCResult($_participant_added, $assessment_post_id);
		}
		if ($c==2) {
			$idx_name = 'country';
			$DISC_result[$idx_name]['D_quadrant_value'] = get_post_meta( $POSTID, 'd_percent_weight', true );
		 	$DISC_result[$idx_name]['I_quadrant_value'] = get_post_meta( $POSTID, 'i_percent_weight', true );
		 	$DISC_result[$idx_name]['S_quadrant_value'] = get_post_meta( $POSTID, 's_percent_weight', true );
		 	$DISC_result[$idx_name]['C_quadrant_value'] = get_post_meta( $POSTID, 'c_percent_weight', true );
		 	$DISC_result[$idx_name]['the_text_id'] = 161;
		 	$DISC_result[$idx_name]['participant_counter'] = 1;
		}
	}
	
	$D_quadrant_value = $DISC_result['country']['D_quadrant_value'];
	$I_quadrant_value = $DISC_result['country']['I_quadrant_value'];
	$S_quadrant_value = $DISC_result['country']['S_quadrant_value'];
	$C_quadrant_value = $DISC_result['country']['C_quadrant_value'];
	$participant_counter_1 = $DISC_result['country']['participant_counter'];	
	
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
		
		$CountryName = toAscii(get_the_title($POSTID));
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
			$pdf->MultiCell(95, 5.65, transformStringQuote($CountryName), 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Regular','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(128.5,159.6);
			$pdf->MultiCell(75, 5.65, transformStringQuote($compared_name) , 0, 1, 'L');
			
			$pdf->SetFont('SourceSansPro-Regular','',16);
			$pdf->SetTextColor(41,40,57);
			$pdf->SetXY(128.5,167.4);
			$pdf->MultiCell(75, 5.65, date('d/m/Y'), 0, 1, 'L');

		}else if ($pageNo == 4) {
			
			$write_text = $CountryName . ' (' . $compared_name . ')';
			
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
			$pdf->MultiCell(132, 5.65, $CountryName, 0, 1, 'L');
			
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
			$sliding_scale_sit = get_post_meta( $POSTID, 'objective_subjective', true );
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
			$sliding_scale_sit = get_post_meta( $POSTID, 'assertive_reflective', true );
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
			$sliding_scale_sit = get_post_meta( $POSTID, 'accepting_challenging', true );
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
			$sliding_scale_sit = get_post_meta( $POSTID, 'result_oriented_process_oriented', true );
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
			$sliding_scale_sit = get_post_meta( $POSTID, 'open_guarded', true );
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
	$CountryName = str_replace(' ', '', $CountryName);
	$compared_name = str_replace(' ', '', $compared_name);
	
	// Clean string
	$CountryName = $icq_report->cleanStr($CountryName);
	$compared_name = $icq_report->cleanStr($compared_name);
	
	$generation_result_path = 'pdf/generation-results/Global-DISC-comparsion-' . $CountryName . '-' . $compared_name . '.pdf';
	$pdf->Output($generation_result_path,'F');
	
	echo get_template_directory_uri() . '/' . $generation_result_path;
?>