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

require __DIR__.'/inc.php';
require_once __DIR__.'/example_upload_form.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

require_login($course);
block_exacomp_require_teacher($courseid);
/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/example_upload.php', array('courseid' => $courseid));
$PAGE->set_title(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');
$context = context_course::instance($courseid);
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

$context = context_course::instance($courseid);

echo html_writer::tag("textarea", "", array("id" => "message", "style" => "width:280px;height:180px"));
echo html_writer::tag("br", "");
echo html_writer::tag("input", "", array("type" => "submit", "value" => block_exacomp_get_string("messagetocourse"), "exa-type" => "send-message-to-course"));
