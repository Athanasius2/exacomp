<?php
// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

use block_exacomp\data_importer;

require __DIR__.'/inc.php';
// temporary disabled (there are errors)
if (11 == 22) {
    require_once __DIR__.'/backup/test_backup.php';
    require_once __DIR__.'/backup/test_restore.php';
}
require_once __DIR__.'/classes/data.php';

global $DB, $OUTPUT, $PAGE, $CFG, $COURSE, $USER;

// TODO: was macht das? wieso brauchen wir das?
if (strcmp("mysql",$CFG->dbtype)==0) {
	$sql5 = "SET @@group_concat_max_len = 10125012";

	$DB->execute($sql5);
}

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param("action", "", PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$slicemodulelist = false;
$columngroupnumber = null;
if (get_config('exacomp', 'disable_js_edit_activities')) {
    $columngroupnumber = optional_param('colgroupid', 0, PARAM_INT);
    if ($columngroupnumber > -1) { // -1 - show all!
        $slicemodulelist = true;
        $slicestartposition = $columngroupnumber * BLOCK_EXACOMP_MODULES_PER_COLUMN;
    }
}

$context = context_course::instance($courseid);
$output = block_exacomp_get_renderer();

block_exacomp_require_teacher($context);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_activitiestodescriptors';

$page_params =  array('courseid' => $courseid);
if ($columngroupnumber !== null) {
    $page_params['colgroupid'] = $columngroupnumber;
}

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/activities_to_descriptors.php', $page_params);
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$headertext = "";
$img = new moodle_url('/blocks/exacomp/pix/three.png');
	 
if ($action == "save") {
	// RELATED DATA
    block_exacomp_update_example_activity_relations(isset($_POST['data']) ? $_POST['data'] : array(), isset($_POST['topicdata']) ? $_POST['topicdata'] : array(), $courseid);

	if (!isset($_POST['data']) && !isset($_POST['topicdata'])) {
        $headertext = block_exacomp_get_string('tick_some');
    } else {
	    $headertext = $output->notification(block_exacomp_get_string("save_success"), 'info');
		$headertext .= html_writer::empty_tag('img', array('src' => $img, 'alt' => '', 'width' => '60px', 'height' => '60px'))
			.block_exacomp_get_string('completed_config');
	
		$students = block_exacomp_get_students_by_course($courseid);
		if (empty($students)) {
            $headertext .= html_writer::empty_tag('br')
                    .html_writer::link(new moodle_url('/enrol/users.php', array('id' => $courseid)),
                            block_exacomp_get_string('optional_step'));
        }
	}
} else {
	$headertext = html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))
		.block_exacomp_get_string('teacher_third_configuration_step')
		.html_writer::link(new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), block_exacomp_get_string('teacher_third_configuration_step_link'));
}

if ($action == "import") {
    $headertext = $output->notification(block_exacomp_get_string("importsuccess"), 'info').$headertext;
    $template = required_param('template', PARAM_INT);
    $relatedDescriptors = array();
    // at first - backup+restore activities:
    $backuprecords = $DB->get_records_sql('
            SELECT DISTINCT e.activityid
            FROM {'.BLOCK_EXACOMP_DB_EXAMPLES.'} e
                JOIN {course_modules} m ON m.id = e.activityid
			WHERE m.course = '.$template.' AND m.deletioninprogress = 0
    ');
    foreach ($backuprecords as $record){
        $backupid = moodle_backup($record->activityid, $USER->id);
        moodle_restore($backupid, $COURSE->id, $USER->id);
        $relatedExample = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('activityid' => $record->activityid, 'courseid' => $template), '*', IGNORE_MULTIPLE);
        $descrs = block_exacomp_get_descriptors_by_example($relatedExample->id);
        if ($descrs) {
            $relatedDescriptors[$relatedExample->id] = array_map(function($d) {return $d->id;}, $descrs);
        } else {
            $relatedDescriptors[$relatedExample->id] = array(); // no sence for this case?
        }

    }
    // at second - relate example (create new) to new activity
    $records = $DB->get_records_sql('
            SELECT e.id, e.activityid, m.module
            FROM {'.BLOCK_EXACOMP_DB_EXAMPLES.'} e
                JOIN {course_modules} m ON m.id = e.activityid
			WHERE m.course = '.$template.' AND m.deletioninprogress = 0
    ');
    foreach ($records as $record) {
        $sourceModule = get_coursemodule_from_id(null, $record->activityid);
        // get ids immediatelly after moodle_restore
        $newActivityid = data_importer::get_new_activity_id($sourceModule->name, $record->module, $COURSE->id);
        // relate to new activity with using of old list of descriptors
        block_exacomp_relate_example_to_activity($COURSE->id, $newActivityid, $relatedDescriptors[$record->id]);
    }

}

// build tab navigation & print header
echo $output->header($context,$courseid, 'tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);

$selected_niveaus = array();
$selected_modules = array();
/* CONTENT REGION */
if ($action == "filter") {
	if (isset($_POST['niveau_filter'])) {
        $selected_niveaus = $_POST['niveau_filter'];
    }
		
	if (isset($_POST['module_filter'])) {
        $selected_modules = $_POST['module_filter'];
    }
}

$subjects = block_exacomp_get_competence_tree($courseid, null, null, true, null, false, array(), false, true);

$modules = $allModules = block_exacomp_get_allowed_course_modules_for_course($COURSE->id);

$visible_modules = [];
$modules_to_filter = [];

$colselector = $output->students_column_selector(count($allModules), 'activities_to_descriptors');

if ($modules) {
    if ($slicemodulelist) {
        if (count($modules) < ($columngroupnumber * BLOCK_EXACOMP_MODULES_PER_COLUMN)) {
            $slicestartposition = 0;
        }
        $modules = array_slice($modules, $slicestartposition, BLOCK_EXACOMP_MODULES_PER_COLUMN);
    }

	foreach ($modules as $module) {
        $module->descriptors = array();
        $module->topics = array();

		$relatedexample = $DB->get_record('block_exacompexamples', array('activityid'=>$module->id, 'courseid' => $courseid), '*', IGNORE_MULTIPLE);
		if ($relatedexample) {
            $descriptors = block_exacomp_get_descriptors_by_example($relatedexample->id);
            foreach ($descriptors as $descriptor) {
                $module->descriptors[$descriptor->id] = $descriptor->id;
                $topics = block_exacomp_get_topics_by_descriptor($descriptor->id);
                if ($topics) {
                    foreach ($topics as $topic) {
                        $module->topics[$topic->id] = $topic->id;
                    }
                }
            }
        }

		if (empty($selected_modules) || in_array(0, $selected_modules) || in_array($module->id, $selected_modules)) {
            $visible_modules[] = $module;
        }
		
		$modules_to_filter[] = $module;
	}
	
	$niveaus = block_exacomp_extract_niveaus($subjects);
	block_exacomp_filter_niveaus($subjects, $selected_niveaus);
	

	$topics_set = block_exacomp_get_topics_by_subject($courseid, null, true);

	if (!$topics_set) {
		echo $output->activity_legend($headertext);
		echo $output->transfer_activities();
		echo $output->no_topics_warning();
	} else if(count($visible_modules)==0) {
		echo $output->activity_legend($headertext);
		echo $output->transfer_activities();
		echo $output->no_course_activities_warning();
	} else {
		echo $output->activity_legend($headertext);
		echo $output->transfer_activities();
        echo $colselector;
		echo $output->activity_content($subjects, $visible_modules);
	}
} else {
    echo $output->transfer_activities();
}

/* END CONTENT REGION */
echo $output->footer();