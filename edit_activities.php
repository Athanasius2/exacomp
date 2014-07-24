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

if(strcmp("mysql",$CFG->dbtype)==0){
	$sql5="SET @@group_concat_max_len = 5012";

	$DB->execute($sql5);
}

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHA);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

require_capability('block/exacomp:teacher', $context);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_assignactivities';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/edit_activities.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

/* CONTENT REGION */
if (($action = optional_param("action", "", PARAM_TEXT) )== "save") {
	block_exacomp_delete_competencies_activities();
	// DESCRIPTOR DATA
	block_exacomp_save_competencies_activities(isset($_POST['data']) ? $_POST['data'] : array(), $courseid, 0);
	// TOPIC DATA
	block_exacomp_save_competencies_activities(isset($_POST['topicdata']) ? $_POST['topicdata'] : array(), $courseid, 1);
}
/*
 * save 
 * 	
        $modsetting_arr=array();
        if (!empty($_POST['block_exacomp_activitysetting'])){
	        foreach ($_POST['block_exacomp_activitysetting'] as $ks=>$vs){
	            $modsetting_arr["activities"][]=clean_param($vs,PARAM_SEQUENCE);
	        };
	      }
	      
            $modsetting="";
						if ($modsetting = $DB->get_record("block_exacompsettings", array("course"=>$courseid))){
							$modsetting->activities=serialize($modsetting_arr);
							$DB->update_record('block_exacompsettings', $modsetting);
						}else{
							$curtime=time();
							$modsettingi=array("course" => $courseid,"grading"=>"1","activities"=>serialize($modsetting_arr),"tstamp"=>$curtime);
							$DB->insert_record('block_exacompsettings',$modsettingi);
				}   
        echo $OUTPUT->box(text_to_html(get_string("activitysuccess", "block_exacomp")));
    }
 */

/* 
 * niveau filter
 * if (!empty($_POST['block_exacomp_niveaufilter'])){
    	$niveau_arr=array();
    	$niveau_arr["niveau"]=array();
    	
    	foreach ($_POST['block_exacomp_niveaufilter'] as $ks=>$vs){
    		if($vs > 0)
    			$niveau_arr["niveau"][]=clean_param($vs,PARAM_SEQUENCE);
    	};
    }
 */

$subjects = block_exacomp_get_competence_tree($courseid, null, true);
$modinfo = get_fast_modinfo($COURSE->id);
$modules = $modinfo->get_cms();
	
$output = $PAGE->get_renderer('block_exacomp');
echo $output->print_activity_legend();

$visible_modules = array();
if($modules){
	$colspan = (count($modules) + 1);
	foreach($modules as $mod){
		if(!$mod->visible){
			$colspan = ($colspan-1);
			continue;
		}
		
		$module = block_exacomp_get_coursemodule($mod);
		
		//Skip Nachrichtenforum
        if($module->name == get_string('namenews','mod_forum')){
        	$colspan=($colspan-1);
        	continue;
        }
        
		if ($module->modname == 'file') {
			$hasFileModule = true;
		}
		
		$compsactiv = $DB->get_records('block_exacompcompactiv_mm', array('activityid'=>$module->id, 'eportfolioitem'=>0));
		
		$module->descriptors = array();
		$module->topics = array();
		
		foreach($compsactiv as $comp){
			if($comp->comptype == 0)
				$module->descriptors[$comp->compid] = $comp->compid;
			else 	
				$module->topics[$comp->compid] = $comp->compid;
		}
		
		$visible_modules[] = $module;
	}
	echo $output->print_activity_content($subjects, $visible_modules, $courseid, $colspan);
}

/* END CONTENT REGION */

echo $OUTPUT->footer();

?>