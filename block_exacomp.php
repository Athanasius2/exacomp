<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Newblock block caps.
 *
 * @package    block_exacomp
 * @copyright  Florian Jungwirth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once dirname(__FILE__) . '/lib/lib.php';

class block_exacomp extends block_list {

	function init() {
		$this->title = get_string('pluginname', 'block_exacomp');
	}

	function get_content() {
		global $CFG, $USER, $COURSE, $DB, $OUTPUT, $version, $skillmanagement, $usebadges;

		//does not work with global var, don't know why TODO
		$usebadges = get_config('exacomp', 'usebadges');
		$skillmanagement = get_config('exacomp', 'skillmanagement');
		$version = get_config('exacomp', 'alternativedatamodel');
		
		if ($this->content !== null) {
			return $this->content;
		}

		if (empty($this->instance)) {
			$this->content = '';
			return $this->content;
		}

		$this->content = new stdClass();
		$this->content->footer = '';
		$this->content->icons = array();
		$this->content->items = array();
		

		// user/index.php expect course context, so get one if page has module context.
		$currentcontext = $this->page->context->get_course_context(false);
		$globalcontext = context_system::instance();
		
		$this->content = '';
		if (empty($currentcontext)) {
			return $this->content;
		}

		$courseid = intval($COURSE->id);
		
		if($version || $skillmanagement)
			$checkConfig = block_exacomp_is_configured($courseid);
		else
			$checkConfig = block_exacomp_is_configured();
			
		$checkImport = $DB->get_records('block_exacompdescriptors');
		$crosssubs = block_exacomp_get_cross_subjects_by_course($courseid);
		
		$courseSettings = block_exacomp_get_settings_by_course($courseid);
		$usedetailpage = $courseSettings->usedetailpage;
		$useactivities = $courseSettings->uses_activities;
		
		$ready_for_use = block_exacomp_is_ready_for_use($courseid);
		
		$de = false;
		$lang = current_language();
		if(isset($lang) && substr( $lang, 0, 2) === 'de'){
			$de = true;
		}
		
		//if use skill management
		if($skillmanagement && has_capability('block/exacomp:teacher', $currentcontext)){
			$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/skillmanagement.php', array('courseid'=>$courseid)), get_string('tab_skillmanagement', 'block_exacomp'), array('title'=>get_string('tab_skillmanagement', 'block_exacomp')));
			$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/module_config.png'), 'alt'=>'', 'height'=>16, 'width'=>23));
		}
		
		//if checkImport && checkSubjects -> Modul wurde konfiguriert 
		//else nur admin sieht block und hat nur den link Modulkonfiguration
		if((has_capability('block/exacomp:admin', $globalcontext))){	//Admin sieht immer Modulkonfiguration
			//Modulkonfiguration
			if(!$version && !$skillmanagement){
				//Wenn Import schon erledigt, weiterleitung zu edit_config, ansonsten import.
				if($checkImport){
					$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/edit_config.php', array('courseid'=>$courseid)), get_string('tab_admin_configuration', 'block_exacomp'), array('title'=>get_string('tab_admin_configuration', 'block_exacomp')));
					$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/module_config.png'), 'alt'=>'', 'height'=>16, 'width'=>23));
				}else{
					$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid)), get_string('tab_admin_configuration', 'block_exacomp'), array('title'=>get_string('tab_admin_configuration', 'block_exacomp')));
					$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/module_config.png'), 'alt'=>'', 'height'=>16, 'width'=>23));
				}
				if(get_config('exacomp','external_trainer_assign') != false) {
    			    $this->content->items[]='<a title="' . get_string('block_exacomp_external_trainer_assign', 'block_exacomp') . '" href="' . $CFG->wwwroot . '/blocks/exacomp/externaltrainers.php?courseid=' . $COURSE->id . '">' . get_string('block_exacomp_external_trainer_assign', 'block_exacomp') . '</a>';
	    		    $this->content->icons[]='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/personal.png" height="16" width="23" alt="'.get_string("block_exacomp_external_trainer_assign", "block_exacomp").'" />';
				}
			}else{
				if(!$skillmanagement){
					$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid)), get_string('tab_admin_import', 'block_exacomp'), array('title'=>get_string('tab_admin_import', 'block_exacomp')));
					$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/module_config.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
				}			
			}
		}
		
		if($checkConfig && $checkImport || $version && $checkImport){	//Modul wurde konfiguriert
			if (has_capability('block/exacomp:teacher', $currentcontext) && $courseid != 1){
				//teacher LIS
				if($version){
					if(block_exacomp_is_activated($courseid)){
						//Kompetenzraster
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_grid.php', array('courseid'=>$courseid)), get_string('tab_competence_grid', 'block_exacomp'), array('title'=>get_string('tab_competence_grid', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/grid.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					}
					if($ready_for_use){
						//Kompetenzüberblick
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid'=>$courseid)), get_string('tab_competence_overview','block_exacomp'), array('title'=>get_string('tab_competence_overview','block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));
						
						//cross subjects
						/*if($crosssubs)
						    $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));
						else 
						    $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));
						
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));
						*/
						if($courseSettings->nostudents != 1) {
						//Kompetenz-Detailansicht nur wenn mit Aktivitäten gearbeitet wird
						if ($courseSettings->uses_activities && $usedetailpage){
							$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_detail.php', array('courseid'=>$courseid)), get_string('tab_competence_details', 'block_exacomp'), array('title'=>get_string('tab_competence_details', 'block_exacomp')));	
							$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/detailed_view_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						}
						
						//Kompetenzprofil
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_profile.php', array('courseid'=>$courseid)), get_string('tab_competence_profile', 'block_exacomp'), array('title'=>get_string('tab_competence_profile', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/area.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						
						//Beispiel-Aufgaben
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/view_examples.php', array('courseid'=>$courseid)), get_string('tab_examples', 'block_exacomp'), array('title'=>get_string('tab_examples', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/examples_and_tasks.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						
						//Lernagenda
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), get_string('tab_learning_agenda', 'block_exacomp'), array('title'=>get_string('tab_learning_agenda', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						}
						//Meine Auszeichnungen
						//if (block_exacomp_moodle_badges_enabled() && $usebadges) {
							//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), get_string('tab_badges', 'block_exacomp'), array('title'=>get_string('tab_badges', 'block_exacomp')));
							//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						//}
					}
					//Einstellungen
					$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), get_string('tab_teacher_settings', 'block_exacomp'), array('title'=>get_string('tab_teacher_settings', 'block_exacomp')));
					$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subjects_topics.gif'), 'alt'=>"", 'height'=>16, 'width'=>23));
				
					if($de && !$skillmanagement){
						//Hilfe
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/help.php', array('courseid'=>$courseid)), get_string('tab_help', 'block_exacomp'), array('title'=>get_string('tab_help', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/info.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					}
				}else{	//teacher !LIS
					if($ready_for_use){
						//Kompetenzüberblick
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid'=>$courseid)), get_string('tab_competence_overview','block_exacomp'), array('title'=>get_string('tab_competence_overview','block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));
					
						//cross subjects
						/*if($crosssubs)
						    $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));
						else 
						    $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));
						*/
						//Kompetenz-Detailansicht nur wenn mit Aktivitäten gearbeitet wird
						if ($courseSettings->uses_activities && $usedetailpage){
							$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_detail.php', array('courseid'=>$courseid)), get_string('tab_competence_details', 'block_exacomp'), array('title'=>get_string('tab_competence_details', 'block_exacomp')));	
							$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/detailed_view_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						}
					}
					if(block_exacomp_is_activated($courseid)){
						//Kompetenzraster
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_grid.php', array('courseid'=>$courseid)), get_string('tab_competence_grid', 'block_exacomp'), array('title'=>get_string('tab_competence_grid', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/grid.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					}
					if($ready_for_use && $courseSettings->nostudents != 1){
						//Kompetenzprofil
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_profile.php', array('courseid'=>$courseid)), get_string('tab_competence_profile', 'block_exacomp'), array('title'=>get_string('tab_competence_profile', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/area.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						
						//Beispiel-Aufgaben
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/view_examples.php', array('courseid'=>$courseid)), get_string('tab_examples', 'block_exacomp'), array('title'=>get_string('tab_examples', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/examples_and_tasks.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					
						//Lernagenda
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), get_string('tab_learning_agenda', 'block_exacomp'), array('title'=>get_string('tab_learning_agenda', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					
						if($courseSettings->profoundness == 1) {
							$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/profoundness.php', array('courseid'=>$courseid)), get_string('tab_profoundness', 'block_exacomp'), array('title'=>get_string('tab_profoundness', 'block_exacomp')));
							$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						}
						//Meine Auszeichnungen
						//if (block_exacomp_moodle_badges_enabled() && $usebadges) {
							//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), get_string('tab_badges', 'block_exacomp'), array('title'=>get_string('tab_badges', 'block_exacomp')));
							//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						//}
					} 

					$settings_string = get_string('tab_teacher_settings', 'block_exacomp');
					//if($skillmanagement)
						//$settings_string = get_string('tab_teacher_demo_settings', 'block_exacomp');
					//Einstellungen
					$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), $settings_string, array('title'=>get_string('tab_teacher_settings', 'block_exacomp')));
					$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subjects_topics.gif'), 'alt'=>"", 'height'=>16, 'width'=>23));
				
					if($de && !$skillmanagement){
						//Hilfe
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/help.php', array('courseid'=>$courseid)), get_string('tab_help', 'block_exacomp'), array('title'=>get_string('tab_help', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/info.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					}
				}
			}else if (has_capability('block/exacomp:student', $currentcontext) && $courseid != 1 && !has_capability('block/exacomp:admin', $currentcontext)){
				//student LIS
				if($version){
					if(block_exacomp_is_activated($courseid)){
						//Kompetenzraster
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_grid.php', array('courseid'=>$courseid)), get_string('tab_competence_grid', 'block_exacomp'), array('title'=>get_string('tab_competence_grid', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/grid.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					}
					if($ready_for_use){
						//Kompetenz�berblick
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid'=>$courseid)), get_string('tab_competence_overview','block_exacomp'), array('title'=>get_string('tab_competence_overview','block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));
					
						//Cross subjects
						/*$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));
						*/
						if($courseSettings->nostudents != 1) {
						
						//Kompetenz-Detailansicht
						if ($courseSettings->uses_activities && $usedetailpage){
							$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_detail.php', array('courseid'=>$courseid)), get_string('tab_competence_details', 'block_exacomp'), array('title'=>get_string('tab_competence_details', 'block_exacomp')));	
							$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/detailed_view_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						}
						
						//Lernagenda
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), get_string('tab_learning_agenda', 'block_exacomp'), array('title'=>get_string('tab_learning_agenda', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						
						//Kompetenzprofil
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_profile.php', array('courseid'=>$courseid)), get_string('tab_competence_profile', 'block_exacomp'), array('title'=>get_string('tab_competence_profile', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/area.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						}
						//Meine Auszeichnungen
						//if (block_exacomp_moodle_badges_enabled() && $usebadges) {
							//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), get_string('tab_badges', 'block_exacomp'), array('title'=>get_string('tab_badges', 'block_exacomp')));
							//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						//}
					}
					if($de && !$skillmanagement){
						//Hilfe
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/help.php', array('courseid'=>$courseid)), get_string('tab_help', 'block_exacomp'), array('title'=>get_string('tab_help', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/info.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					}
				}else{ //student !LIS
					if($ready_for_use){
						//Kompetenz�berblick
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid'=>$courseid)), get_string('tab_competence_overview','block_exacomp'), array('title'=>get_string('tab_competence_overview','block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));
						
						//Cross subjects
						/*$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));
						*/
						if($courseSettings->nostudents != 1) {
						//Kompetenz-Detailansicht
						if ($courseSettings->uses_activities && $usedetailpage){
							$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_detail.php', array('courseid'=>$courseid)), get_string('tab_competence_details', 'block_exacomp'), array('title'=>get_string('tab_competence_details', 'block_exacomp')));	
							$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/detailed_view_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						}
						
						//Kompetenzprofil
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_profile.php', array('courseid'=>$courseid)), get_string('tab_competence_profile', 'block_exacomp'), array('title'=>get_string('tab_competence_profile', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/area.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					
						}
					}
					if(block_exacomp_is_activated($courseid)){
						//Kompetenzraster
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_grid.php', array('courseid'=>$courseid)), get_string('tab_competence_grid', 'block_exacomp'), array('title'=>get_string('tab_competence_grid', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/grid.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					}
					if($ready_for_use && $courseSettings->nostudents != 1){	
						//Lernagenda
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), get_string('tab_learning_agenda', 'block_exacomp'), array('title'=>get_string('tab_learning_agenda', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						
						//Meine Auszeichnungen
						//if (block_exacomp_moodle_badges_enabled() && $usebadges) {
							//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), get_string('tab_badges', 'block_exacomp'), array('title'=>get_string('tab_badges', 'block_exacomp')));
							//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
						//}
					} 
					
					if($de && !$skillmanagement){
						//Hilfe
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/help.php', array('courseid'=>$courseid)), get_string('tab_help', 'block_exacomp'), array('title'=>get_string('tab_help', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/info.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					}
				}
			}
		}
		return $this->content;
	}

	public function instance_allow_multiple() {
		return false;
	}

	function has_config() {
		return true;
	}

	/**
	 * This function is executed by the Moodle cron job.
	 * It checks if an url for updating the data-xml file is specified and in this case
	 * it tries to get the content and update the local xml.
	 */
	public function cron() {
		global $COURSE, $DB, $xmlserverurl, $autotest, $testlimit;
		$xmlserverurl = get_config('exacomp', 'xmlserverurl');

		mtrace('Exabis Competencies: cron job is running.');
		
		//import xml with provided server url
		if($xmlserverurl) {
			$xml = file_get_contents($xmlserverurl);
			if($xml) {
				require_once dirname(__FILE__) . '/lib/xmllib.php';

				if(block_exacomp_xml_do_import($xml,1,1)) {
					mtrace("import done");
					block_exacomp_settstamp();
				}
				else mtrace("import failed");
			}
		}
		
		block_exacomp_perform_auto_test();
		
		return true;
	}
}