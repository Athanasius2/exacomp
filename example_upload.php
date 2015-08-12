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
require_once dirname(__FILE__) . '/example_upload_form.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$exampleid = optional_param('exampleid', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if ($exampleid > 0 && (!$example = $DB->get_record('block_exacompexamples', array('id' => $exampleid)))
        && $example->creatorid != $USER->id) {
    print_error('invalidexample', 'block_exacomp', $exampleid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/example_upload.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();
$PAGE->requires->js("/blocks/exacomp/javascript/CollapsibleLists.compressed.js");
$PAGE->requires->css("/blocks/exacomp/css/CollapsibleLists.css");

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$blocknode->make_active();

$action = optional_param('action', 'add', PARAM_TEXT);

if($action == 'serve') {

    $contextid = required_param('c', PARAM_INT);
    $itempathnamehash = required_param('i', PARAM_TEXT);
    $fs = get_file_storage();
    send_stored_file($fs->get_file_by_hash($itempathnamehash));
    die;
}
// build tab navigation & print header
echo $PAGE->get_renderer('block_exacomp')->header();
/* CONTENT REGION */

block_exacomp_require_teacher($context);
$descrid = required_param('descrid', PARAM_INT);
$topicid = required_param('topicid', PARAM_INT);

$taxonomies = $DB->get_records_menu("block_exacomptaxonomies",null,"","id, title");
$taxonomies = array_merge(array("0" => ""),$taxonomies);
$topicsub = $DB->get_record("block_exacomptopics", array("id"=>$topicid));
$topics = $DB->get_records("block_exacomptopics", array("subjid"=>$topicsub->subjid), null, 'title,id');

$example_descriptors = array();
if($exampleid>0)
	$example_descriptors = $DB->get_records(DB_DESCEXAMP,array('exampid'=>$exampleid),'','descrid');

$tree = block_exacomp_build_example_association_tree($courseid, $example_descriptors, $exampleid, $descrid);

$form = new block_exacomp_example_upload_form($_SERVER['REQUEST_URI'], array("descrid" => $descrid,"taxonomies"=>$taxonomies,"tree"=>$tree,"topicid"=>$topicid, "exampleid"=>$exampleid, "task"=>isset($example->task) ? $example->task : null,
        "solution"=>isset($example->solution) ? $example->solution : null) );

if($formdata = $form->get_data()) {
	
    $newExample = new stdClass();
    $newExample->title = $formdata->title;
    $newExample->description = $formdata->description;
    $newExample->creatorid = $USER->id;
    $newExample->externalurl = $formdata->externalurl;
    $newExample->source = EXAMPLE_SOURCE_TEACHER;
    if($form->get_new_filename('file') || $form->get_new_filename('solution')) {
        // save file
        $context = context_user::instance($USER->id);
        $fs = get_file_storage();

        if($formdata->lisfilename == 1 && $form->get_new_filename('file')) {
        	$descr = reset($_POST['descriptor']);
        	$descr = $DB->get_record(DB_DESCRIPTORS,array('id' => $descr));
			$descr->topicid = $topicid;
        	$newfilename = block_exacomp_get_descriptor_numbering($descr).' ';
            
        	$temp_filename = $newfilename;
            $newfilename .= $formdata->title . "." . pathinfo($form->get_new_filename('file'), PATHINFO_EXTENSION);
            $newsolutionname = $temp_filename . $formdata->name . "_SOLUTION." . pathinfo($form->get_new_filename('solution'), PATHINFO_EXTENSION);
            $newExample->title = $newfilename;
        }
        else {
        	$newfilename = "";
        	if($formdata->lisfilename==1){
	        	$descr = reset($_POST['descriptor']);
	        	$descr = $DB->get_record(DB_DESCRIPTORS,array('id' => $descr));
				$descr->topicid = $topicid;
	        	$newfilename = block_exacomp_get_descriptor_numbering($descr).' ';
        	}
            $newfilename = $newfilename.$form->get_new_filename('file');
            $newsolutionname =  $newfilename.$form->get_new_filename('solution');
        }

        if(!$fs->file_exists($context->id, 'user', 'private', 0, '/', $newfilename))
            $form->save_stored_file('file', $context->id, 'user', 'private', 0, '/', $newfilename, true);

        $pathnamehash = $fs->get_pathname_hash($context->id, 'user', 'private', 0, '/', $newfilename);

        if(!$fs->file_exists($context->id, 'user', 'private', 0, '/', $newsolutionname))
            $form->save_stored_file('solution', $context->id, 'user', 'private', 0, '/', $newsolutionname, true);
        $solutionpathnamehash = $fs->get_pathname_hash($context->id, 'user', 'private', 0, '/', $newsolutionname);

        // insert example
        if($form->get_new_filename('file')) {
            $task = new moodle_url($CFG->wwwroot.'/blocks/exacomp/example_upload.php',array("action"=>"serve","c"=>$context->id,"i"=>$pathnamehash,"courseid"=>$courseid));
            $newExample->task = $task->out(false);
        }
        if($form->get_new_filename('solution')) {
            $solution = new moodle_url($CFG->wwwroot.'/blocks/exacomp/example_upload.php',array("action"=>"serve","c"=>$context->id,"i"=>$solutionpathnamehash,"courseid"=>$courseid));
            $newExample->solution = $solution->out(false);
        }
    }
    if($formdata->exampleid == 0)
        $newExample->id = $DB->insert_record('block_exacompexamples', $newExample);
    else {
        //update example
        $newExample->id = $formdata->exampleid;
        $DB->update_record('block_exacompexamples', $newExample);
        $DB->delete_records('block_exacompdescrexamp_mm',array('exampid' => $newExample->id));
    }

    //TODO mehrfachauswahl
    //insert taxid in exampletax_mm
    $new_mm = new stdClass();
    $new_mm->exampleid = $newExample->id;
    $new_mm->taxid = $formdata->taxid;
    $DB->insert_record(DB_EXAMPTAX, $new_mm);
    
    //add descriptor association
    if(isset($_POST['descriptor'])){
    	foreach($_POST['descriptor'] as $descriptorid){
    		$record = $DB->get_record(DB_DESCEXAMP, array('descrid'=>$descriptorid, 'exampid'=>$newExample->id));
			if(!$record)
    			$DB->insert_record(DB_DESCEXAMP, array('descrid'=>$descriptorid, 'exampid'=> $newExample->id));
    	}
    }
        
    block_exacomp_settstamp();
    ?>
<script type="text/javascript">
		window.opener.block_exacomp.newExampleAdded();
		window.close();
	</script>
<?php 
	exit;
}

if($exampleid > 0) {
    $example->descriptors = $DB->get_fieldset_select('block_exacompdescrexamp_mm', 'descrid', 'exampid = ?',array($exampleid));
    $form->set_data($example);
}

$form->display();

/* END CONTENT REGION */
echo $PAGE->get_renderer('block_exacomp')->footer();
