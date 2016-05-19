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

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_example_upload_form extends moodleform {

	function definition() {
		global $CFG, $USER, $DB, $PAGE;

		$output = block_exacomp_get_renderer();
		
		$mform = & $this->_form;

		$descrid = $this->_customdata['descrid'];
		
		$descrTitle = $DB->get_field('block_exacompdescriptors','title',array("id"=>$descrid));
		$mform->addElement('header', 'general', get_string("example_upload_header", "block_exacomp", $descrTitle));

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', 0);
		
		//add html tree -> different treated in example_upload -> mform does not support a tree structure
		$treetitle = html_writer::start_div('fitem');
		$treetitle .= html_writer::start_div('fitemtitle') . html_writer::label(get_string('descriptors','block_exacomp'), 'tree'). html_writer::end_div();
		$treetitle .= html_writer::start_div('felement ftext');
		$tree = $this->_customdata['tree'];
		$html_tree = $output->competence_based_list_tree($tree, true, 1, false);
		$mform->addElement('html', $treetitle);
		$mform->addElement('html', $html_tree);

		$treetitle = html_writer::end_div() . html_writer::end_div();
		$mform->addElement('html', $treetitle);
		
		$mform->addElement('hidden', 'action');
		$mform->setType('action', PARAM_ACTION);
		$mform->setDefault('action', 'add');

		$mform->addElement('text', 'title', get_string("name_example","block_exacomp"), 'maxlength="255" size="60"');
		$mform->setType('title', PARAM_TEXT);
		$mform->addRule('title', get_string("titlenotemtpy", "block_exacomp"), 'required', null, 'client');

		$mform->addElement('text', 'description', get_string("description_example","block_exacomp"), 'maxlength="255" size="60"');
		$mform->setType('description', PARAM_TEXT);

		if ($this->_customdata['taxonomies']) {
			$tselect = $mform->addElement('select', 'taxid', get_string('taxonomy', 'block_exacomp'),$this->_customdata['taxonomies']);
			$tselect->setMultiple(true);
			$tselect->setSelected(array_keys($DB->get_records(\block_exacomp\DB_EXAMPTAX,array("exampleid" => $this->_customdata['exampleid']),"","taxid")));
		}

		$mform->addElement('header', 'link', get_string('link','block_exacomp'));
		
		$mform->addElement('text', 'externalurl', get_string("link","block_exacomp"), 'maxlength="255" size="60"');
		$mform->setType('externalurl', PARAM_TEXT);
		
		$mform->addElement('header', 'files', get_string('files','block_exacomp'));
		
		$mform->addElement('filemanager', 'file', get_string('file'), null, array('subdirs' => false, 'maxfiles' => 1));
		$mform->addElement('filemanager', 'solution', get_string('solution','block_exacomp'), null, array('subdirs' => false, 'maxfiles' => 1));
		
		if( $this->_customdata['uses_activities'] ) {
		
			$mform->addElement('header', 'assignments', get_string('assignments','block_exacomp'));
			$mform->addElement('select', 'assignment', get_string('assignments','block_exacomp'), $this->_customdata['activities']);
		}
		/* if(block_exacomp_is_altversion()) {
			$mform->addElement('checkbox', 'lisfilename', get_string('lisfilename', 'block_exacomp'));
			$mform->setDefault('lisfilename', 1);
		} */
		
		$mform->addElement('hidden','topicid');
		$mform->setType('topicid', PARAM_INT);
		$mform->setDefault('topicid',$this->_customdata['topicid']);
		
		$mform->addElement('hidden','exampleid');
		$mform->setType('exampleid', PARAM_INT);
		$mform->setDefault('exampleid',$this->_customdata['exampleid']);
		
		$this->add_action_buttons(false);
	}

	function validation($data, $files) {
		$errors = parent::validation($data, $files);
	
		$errors= array();
	
		if (!empty($data['link']) && filter_var($data['link'], FILTER_VALIDATE_URL) === FALSE
				&& filter_var("http://" . $data['link'], FILTER_VALIDATE_URL) === FALSE) {
			$errors['link'] = get_string('linkerr','block_exacomp');
		}
	
		return $errors;
	}
	public function print_competence_based_list_tree_for_form($tree, $mform) {
		global $PAGE;
		
		$mform->addElement('html', '<ul>');
		foreach($tree as $skey => $subject) {
			$mform->addElement('html', '<li>');
			$mform->addElement('static', 'subjecttitle', $subject->title);
			
			if(!empty($subject->topics))
				$mform->addElement('html', '<ul>');
			
			foreach ( $subject->topics as $tkey => $topic ) {
					$mform->addElement('html', '<li>');
					$mform->addElement('static', 'subjecttitle', $subject->title);
			
					if(!empty($topic->descriptors))
						$mform->addElement('html', '<ul>');
					
					foreach ( $topic->descriptors as $dkey => $descriptor ) {
						$mform = $this->print_competence_for_list_tree_for_form($descriptor, $mform);
					}
					
					if(!empty($topic->descriptors))
						$mform->addElement('html', '</ul>');
				
			}
			if(!empty($subject->topics))
				$mform->addElement('html', '</ul>');
			
			$mform->addElement('html', '</li>');
			
		}
		$mform->addElement('html', '</ul>');
		return $mform;
	}
	
	private function print_competence_for_list_tree_for_form($descriptor, $mform) {
		$mform->addElement('html', '<li>');
		
		if(isset($descriptor->direct_associated))
			$mform->addElement('advcheckbox', 'descriptor[]', 'Kompetenzen', $descriptor->title, array('group'=>'descriptor'));
			//$mform->setDefault('d');
			/*$html_tree .= html_writer::div(html_writer::div(
				html_writer::checkbox("descriptor[]", $descriptor->id, ($descriptor->direct_associated==1)?true:false, $descriptor->title),
				"felement fcheckbox"), "fitem fitem_fcheckbox ", array('id'=>'fitem_id_descriptor'));
	*/	else 
			$mform->addElement('static', 'descriptortitle', $descriptor->title);
			
		if(!empty($descriptor->examples))
			$mform->addElement('html', '<ul>');
			
		foreach($descriptor->examples as $example) {
			$mform->addElement('html', '<li>');
			$mform->addElement('static', 'exampletitle', $example->title);
		}
			
		if(!empty($descriptor->examples))
			$mform->addElement('html', '</ul>');
			
		if(!empty($descriptor->children)) {
			$mform->addElement('html', '<ul>');
			
			foreach($descriptor->children as $child)
				$mform = $this->print_competence_for_list_tree_for_form($child, $mform);
			
			$mform->addElement('html', '</ul>');
		}
		$mform->addElement('html', '</li>');
		
		return $mform;
	}
}
