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

require_once __DIR__."/../../lib/lib.php";

class restore_exacomp_block_structure_step extends restore_structure_step {

	protected function define_structure() {

		$paths = array();

		$paths[] = new restore_path_element('block', '/block', true);
		$paths[] = new restore_path_element('settings', '/block/exacomp/settings');
		$paths[] = new restore_path_element('mdltype', '/block/exacomp/mdltypes/mdltype');
		$paths[] = new restore_path_element('topic', '/block/exacomp/topics/topic');
		$paths[] = new restore_path_element('compactiv_mm', '/block/exacomp/activities/compactiv_mm');
		$paths[] = new restore_path_element('taxonomy', '/block/exacomp/taxonomies/taxonomy');

		// Return the paths wrapped into standard activity structure
		return $paths;
	}
	
	protected function get_db_record($table, $data, $prefix = "") {
		global $DB;
		
		\block_exacomp\data::prepare();
		if (!$where = block_exacomp\data_course_backup::parse_sourceid($data, $prefix)) {
			return null;
		}

		return $DB->get_record($table, $where);
	}

	public function process_block($data) {
		global $DB;

		$data = (object)$data;

		$taxonomies = array();
		if (isset($data->exacomp['taxonomies']['taxonomy'])) {
			foreach($data->exacomp['taxonomies']['taxonomy'] as $taxonomy) {
				$taxonomy = (object)$taxonomy;
				
				if (!$dbTaxonomy = $this->get_db_record(\block_exacomp\DB_TAXONOMIES, $taxonomy)) {
					continue;
				}
				
				$taxonomies[] = $dbTaxonomy->id;
			}
			
		}
		
		if (isset($data->exacomp['settings'])) {
			$settings = $data->exacomp['settings'];
			$settings = (object)reset($settings);
			unset($settings->courseid);
			unset($settings->id);
			
			$settings->filteredtaxonomies = $taxonomies ? json_encode($taxonomies) : array(SHOW_ALL_TAXONOMIES);
			
			block_exacomp\globals::$DB->insert_or_update_record(\block_exacomp\DB_SETTINGS, $settings, array('courseid'=>$this->get_courseid()));
		}

		if (isset($data->exacomp['mdltypes']['mdltype'])) {
			foreach ($data->exacomp['mdltypes']['mdltype'] as $mdltype) {
				$mdltype = (object)$mdltype;
				if (!$schooltype = $this->get_db_record(\block_exacomp\DB_SCHOOLTYPES, $mdltype)) {
					continue;
				}

				$mdltype->stid = $schooltype->id;
				$mdltype->courseid = $this->get_courseid();

				$DB->insert_record('block_exacompmdltype_mm', $mdltype);
			}
		}
		
		if (isset($data->exacomp['topics']['topic'])) {

			foreach($data->exacomp['topics']['topic'] as $topic) {
				$topic = (object)$topic;
				
				if (!$dbTopic = $this->get_db_record(\block_exacomp\DB_TOPICS, $topic)) {
					continue;
				}
				
				block_exacomp\globals::$DB->insert_or_update_record(\block_exacomp\DB_COURSETOPICS, array('topicid' => $dbTopic->id, 'courseid' => $this->get_courseid()));
			}
		}
		
		// hack
		$GLOBALS['block_exacomp_imported_activities'] = array();
		if (isset($data->exacomp['activities']['compactiv_mm'])) {
			$course = $DB->get_record("course",array("id"=>$this->get_courseid()));

			$DB->execute("
					DELETE FROM {block_exacompcompactiv_mm} WHERE activityid IN
					(SELECT id FROM {course_modules} WHERE course = ?)
				", array($this->get_courseid()));
			
			foreach ($data->exacomp['activities']['compactiv_mm'] as $descractiv_mm) {
				$descractiv_mm = (object)$descractiv_mm;
				
				if ($descractiv_mm->comptype == \block_exacomp\TYPE_DESCRIPTOR) {
					$table = \block_exacomp\DB_DESCRIPTORS;
				} else if ($descractiv_mm->comptype == \block_exacomp\TYPE_TOPIC) {
					$table = \block_exacomp\DB_TOPICS;
				} else {
					print_error("unknown comptype {$descractiv_mm->comptype}");
				}
				
				if (!$source_desc = $this->get_db_record($table, $descractiv_mm, 'comp')) {
					continue;
				}
				
				$descractiv_mm->compid = $source_desc->id;
				// temporary activityid, will be overwritten in restore_exacomp_block_task.class.php::after_restore()
				$descractiv_mm->oldactivityid = $descractiv_mm->activityid;
				$descractiv_mm->activityid = -12345;
				$descractiv_mm->coursetitle = $course->shortname;
					
				$descractiv_mm->id = $DB->insert_record('block_exacompcompactiv_mm', $descractiv_mm);
				
				$GLOBALS['block_exacomp_imported_activities'][$descractiv_mm->id] = $descractiv_mm;
			}
		}
   }
}
