<?php
/*
 * copyright exabis
 */

require __DIR__.'/inc.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);
$output = block_exacomp_get_renderer();

$id = optional_param('id', 0, PARAM_INT);
$item = $id ? \block_exacomp\subject::get($id) : null;

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/subject.php', array('courseid' => $courseid));
$PAGE->set_heading($item ? \block_exacomp\trans(['de:Kompetenzraster bearbeiten', 'en:Modify competence grid']) : \block_exacomp\trans(['de:Neuen Kompetenzraster anlegen', 'en:Create new competence grid']));
$PAGE->set_pagelayout('embedded');

// build tab navigation & print header

/* CONTENT REGION */

block_exacomp_require_teacher($context);
if ($item) {
	block_exacomp\require_item_capability(block_exacomp\CAP_MODIFY, $item);
}

// TODO: check permissions, check if item is \block_exacomp\DATA_SOURCE_CUSTOM

if ($item && optional_param('action', '', PARAM_TEXT) == 'delete') {
	block_exacomp\require_item_capability(block_exacomp\CAP_DELETE, $item);
	$item->delete();

	echo $output->popup_close_and_reload();
	exit;
}

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_local_item_form extends moodleform {

	function definition() {
		global $COURSE;

		$mform = & $this->_form;

		$mform->addElement('text', 'title', \block_exacomp\get_string('name'), 'maxlength="255" size="60"');
		$mform->setType('title', PARAM_TEXT);
		$mform->addRule('title', \block_exacomp\get_string("titlenotemtpy"), 'required', null, 'client');

		$courseid_schooltype = block_exacomp_is_skillsmanagement() ? $COURSE->id : 0;
		$schooltypes = block_exacomp_get_schooltypes_by_course($courseid_schooltype);

		$schooltypes = array_map(function($st) { return $st->title; }, $schooltypes);

		$mform->addElement('select', 'stid', \block_exacomp\get_string('tab_teacher_settings_selection_st'), $schooltypes);
		
		$this->add_action_buttons(false);
	}
}

$form = new block_exacomp_local_item_form($_SERVER['REQUEST_URI']);
if ($item) $form->set_data($item);

if($formdata = $form->get_data()) {
	
	$new = new stdClass();
	$new->title = $formdata->title;
	$new->stid = $formdata->stid;
	$new->titleshort = substr($formdata->title, 0, 1);
	
	if (!$item) {
		$new->source = \block_exacomp\DATA_SOURCE_CUSTOM;
		$new->sourceid = 0;
	
		$new->id = $DB->insert_record(\block_exacomp\DB_SUBJECTS, $new);
		
		// add one dummy topic
		$topicid = $DB->insert_record(\block_exacomp\DB_TOPICS, array(
			'title' => \block_exacomp\trans(['de:Neuer Raster', 'en:New competence grid']),
			'subjid' => $new->id,
			'numb' => 1,
			'source' => \block_exacomp\DATA_SOURCE_CUSTOM,
			'sourceid' => 0
		));
	
		// add dummy topic to course
		$DB->insert_record(\block_exacomp\DB_COURSETOPICS, array(
			'courseid' => $courseid,
			'topicid' => $topicid
		));
		$subjectid = $new->id;
	} else {
		$item->update($new);
		$subjectid = $item->id;
	}
	
	echo $output->header();
	echo $output->popup_close_and_forward($CFG->wwwroot."/blocks/exacomp/assign_competencies.php?courseid=".$courseid."&editmode=1&subjectid={$subjectid}");
	echo $output->footer();
	
	exit;
}

echo $output->header($context, $courseid, '', false);

if ($item) {
	// TODO: also check $item->can_delete
	echo '<div style="position: absolute; top: 40px; right: 20px;">';
	echo '<a href="'.$_SERVER['REQUEST_URI'].'&action=delete" onclick="return confirm(\''.\block_exacomp\trans('de:Wirklich löschen?').'\');">';
	echo \block_exacomp\get_string('delete');
	echo '</a></div>';
}

$form->display();

echo $output->footer();
