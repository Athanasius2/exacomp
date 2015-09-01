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
require_once dirname(__FILE__)."/lib/xmllib.php";

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$course_context = context_course::instance($courseid);

require_capability('block/exacomp:admin', context_system::instance());

$action = required_param('action', PARAM_ALPHANUMEXT);
$source = required_param('source', PARAM_INT);

$output = $PAGE->get_renderer('block_exacomp');

if ($action == 'delete_selected') {
    $descriptors = block_exacomp_clean_array(isset($_REQUEST['descriptors'])?$_REQUEST['descriptors']:array(), array(PARAM_INT=>PARAM_INT));
    
    die('todo');
} else if ($action == 'select') {
    
    /* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
    $page_identifier = 'tab_admin_import';
    
    /* PAGE URL - MUST BE CHANGED */
    $PAGE->set_url('/blocks/exacomp/source_delete.php', array('courseid' => $courseid));
    $PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
    $PAGE->set_title(get_string($page_identifier, 'block_exacomp'));
    
    // build breadcrumbs navigation
    $coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
    $blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
    $pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
    $pagenode->make_active();
    
    echo $output->header();
    echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($course_context,$courseid), $page_identifier);
    
    echo $output->print_descriptor_selection_source_delete($source);
    
    echo $output->footer();
} else {
    print_error("wrong action '$action'");
}
