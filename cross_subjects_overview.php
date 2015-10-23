<?php

/* * *************************************************************
 *  Copyright notice
*
*  (c) 2014 exabis internet solutions <info@exabis.at>
*  All rights reserved
*
*  You can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  This module is based on the Collaborative Moodle Modules from
*  NCSA Education Division (http://www.ncsa.uiuc.edu)
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
* ************************************************************* */

require_once dirname(__FILE__)."/inc.php";

global $DB, $OUTPUT, $PAGE, $USER, $version;

$courseid = required_param('courseid', PARAM_INT);
$studentid = optional_param('studentid', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);
$isAdmin = (has_capability('block/exacomp:admin', $context))?true:false;

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_cross_subjects_overview';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/cross_subjects_overview.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

$PAGE->requires->js("/blocks/exacomp/javascript/CollapsibleLists.compressed.js");
$PAGE->requires->css("/blocks/exacomp/css/CollapsibleLists.css");

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = $PAGE->get_renderer('block_exacomp');

//SAVE DATA
if (($action = optional_param("action", "", PARAM_TEXT) ) == "save") {
 	if(isset($_POST['delete_crosssubs']) && isset($_POST['draft'])){
    	$drafts_to_delete = $_POST['draft'];
    	block_exacomp_delete_crosssubject_drafts($drafts_to_delete);
    }
	else if(isset($_POST['draft'])){
        $drafts_to_save = $_POST['draft'];
        //if more than one draft added redirect to first selected
        $current_id = block_exacomp_save_drafts_to_course($drafts_to_save, $courseid);
        redirect(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$current_id)));
    }
    else if(isset($_POST['new_crosssub'])){
    	$current_id = block_exacomp_create_new_crosssub($courseid);
    	redirect(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$current_id, 'new'=>1)));
    }
}

// build tab navigation & print header
echo $output->header($context, $courseid, 'tab_cross_subjects');

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);
if(!$isTeacher)
	$studentid = $USER->id;

block_exacomp_init_cross_subjects();

$subjectdrafts = block_exacomp_get_cross_subjects_drafts_sorted_by_subjects();
$course_crosssubs = block_exacomp_get_cross_subjects_by_course($courseid, $studentid);

//$right_content = html_writer::empty_tag('input', array('type'=>'button', 'id'=>'edit_crossubs', 'name'=> 'edit_crossubs', 'value' => get_string('show_course_crosssubs','block_exacomp'),
//		"onclick" => "document.location.href='".(new moodle_url('/blocks/exacomp/cross_subjects.php',array('courseid' => $COURSE->id)))->__toString()."'"));
//echo html_writer::div($right_content, 'edit_buttons_float_right');
$content = $output->print_cross_subjects_list($course_crosssubs, $courseid, $isTeacher);
$content .=  '<hr />';
if($isTeacher)
	$content .= $output->print_cross_subjects_drafts($subjectdrafts, $isAdmin);
echo html_writer::div($content, "", array('id'=>'exabis_save_button'));
        
/* END CONTENT REGION */
echo $output->footer();

?>