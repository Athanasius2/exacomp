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

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_weekly_schedule';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/weekly_schedule.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));


block_exacomp_init_js_css();

block_exacomp_init_js_weekly_schedule();
// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

//TODO week von calendar
$week = optional_param('week', time(), PARAM_INT);
$week = block_exacomp_add_days($week, 1 - date('N', $week));

$isTeacher = block_exacomp_is_teacher($context);
$studentid = $isTeacher ? optional_param("studentid", 0, PARAM_INT) : $USER->id;

$selectedCourse = optional_param('pool_course', $courseid, PARAM_INT);

/* CONTENT REGION */
$output = $PAGE->get_renderer('block_exacomp');
echo $output->print_wrapperdivstart();

if($isTeacher){
	$coursestudents = block_exacomp_get_students_by_course($courseid);
	
	if($studentid == 0) {
		echo html_writer::tag("p", get_string("select_student_weekly_schedule","block_exacomp"));
		//print student selector
		echo get_string("choosestudent","block_exacomp");
		echo block_exacomp_studentselector($coursestudents,$studentid,$PAGE->url);
		echo $OUTPUT->footer();
		die;
	}else{
		//check permission for viewing students profile
		if(!array_key_exists($studentid, $coursestudents))
			print_error("nopermissions","","","Show student profile");
		
		//print student selector
		echo get_string("choosestudent","block_exacomp");
		echo block_exacomp_studentselector($coursestudents,$studentid,$PAGE->url);
	}
}

$student = $DB->get_record('user',array('id' => $studentid));

echo $output->print_course_dropdown($selectedCourse, $studentid);

echo $OUTPUT->box(get_string('weekly_schedule_link_to_grid','block_exacomp'));

echo $output->print_side_wrap_weekly_schedule();
/* END CONTENT REGION */

echo $output->print_wrapperdivend();
echo $OUTPUT->footer();

?>