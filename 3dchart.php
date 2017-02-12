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
global $USER;

require __DIR__.'/inc.php';

$start = optional_param('start', 0, PARAM_INT);
$end = optional_param('end', 0, PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$topicid = required_param('topicid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if (!$topic = $DB->get_record(BLOCK_EXACOMP_DB_TOPICS, array('id' => $topicid))) {
	print_error('invalidtopic', 'block_exacomp', $topicid);
}

require_login($course);

$context = context_course::instance($courseid);

if ($userid != $USER->id) {
	block_exacomp_require_teacher($courseid);
}

if (!block_exacomp_use_eval_niveau()) {
	print_error('invalidevalniveau', 'block_exacomp');
}


//	$scheme_items = \block_exacomp\global_config::get_value_titles($courseid);
//	$evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus();
/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/3dchart.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$exacomp_graph = (object)[
	'data' => [],
	'options' => (object)[],
];

$graph_options =& $exacomp_graph->options;
$graph_data =& $exacomp_graph->data;

/*
$graph_options->xMin = 0;
$graph_options->xMax = 10;
$graph_options->yMin = 0;
$graph_options->yMax = 10;
$graph_options->zMin = 0;
$graph_options->zMax = 10;
*/

$evaluation = block_exacomp_get_descriptor_statistic_for_topic($courseid, $topicid, $userid, $start, $end)['descriptor_evaluation'];

$graph_options->xLabels = array_map(function($label) {
	// remove LFS at the beginning
	return preg_replace('!^'.preg_quote(block_exacomp_get_string('niveau_short'), '!').'!', '', $label);
}, array_keys(['' => ''] + $evaluation));
$graph_options->xLabel = block_exacomp_get_string('niveau_short');
$xlabels_long = array_keys(['' => ''] + $evaluation);

$evalniveau_titles = array_filter(\block_exacomp\global_config::get_evalniveaus(), function($k) {
	return $k > 0;
}, ARRAY_FILTER_USE_KEY);
$graph_options->yLabels = array_values([0 => ''] + $evalniveau_titles);
$y_id_to_index = array_combine(array_keys([0 => ''] + $evalniveau_titles), array_keys($graph_options->yLabels));
$ylabels_long = $graph_options->yLabels;

end($graph_options->yLabels);
$student_value_index = key($graph_options->yLabels) + 1;
$graph_options->yLabels[$student_value_index] = block_exacomp_get_string('selfevaluation_short');
$ylabels_long[$student_value_index] = block_exacomp_get_string('selfevaluation');

$value_titles = array_filter(\block_exacomp\global_config::get_value_titles($courseid, true), function($k) {
	return $k >= 0;
}, ARRAY_FILTER_USE_KEY);
$value_titles_long = array_filter(\block_exacomp\global_config::get_value_titles($courseid, false), function($k) {
	return $k >= 0;
}, ARRAY_FILTER_USE_KEY);
$value_titles_self_assessment = \block_exacomp\global_config::get_student_value_titles();

$graph_options->zLabels = array_fill(0, count($value_titles), '');

$graph_options->yColors = [
	$student_value_index => [
		'fill' => 'RGB(255,197,57)',
	],
];

$x = 1;
foreach ($evaluation as $e) {
	if ($e->studentvalue > 0) {
		$data_value = (object)[
			'x' => $x,
			'y' => $student_value_index,
			'z' => $e->studentvalue,
			'label' => $xlabels_long[$x].' / '.block_exacomp_get_string('selfevaluation').': <b>'.$value_titles_self_assessment[$e->studentvalue].'</b>',
		];
		$graph_data["{$data_value->x}-{$data_value->y}-{$data_value->z}"] = $data_value;
	}
	if ($e->teachervalue >= 0 && isset($y_id_to_index[$e->evalniveau])) {
		$data_value = (object)[
			'x' => $x,
			'y' => $y_id_to_index[$e->evalniveau],
			'z' => $e->teachervalue,
			'label' => $xlabels_long[$x].' / '.$ylabels_long[$y_id_to_index[$e->evalniveau]].': <b>'.$value_titles_long[$e->teachervalue].'</b>',
		];
		$graph_data["{$data_value->x}-{$data_value->y}-{$data_value->z}"] = $data_value;
	}
/*
			var title = evalniveau_titles_by_index[point.y] ? evalniveau_titles_by_index[point.y].title : '' || '';
			var value
			if (evalniveau_titles_by_index[point.y].id == student_value_id) {
				value = exacomp_data.value_titles_self_assessment[point.z];
			} else {
				value = exacomp_data.value_titles_long[Object.keys(exacomp_data.value_titles)[point.z]];
			}
			return title + ' <b>' + value + '</b>';
*/
	$x++;
}

echo '<script> exacomp_graph = '.json_encode($exacomp_graph).'; </script>';

$output = block_exacomp_get_renderer();
$output->requires()->js('/blocks/exacomp/javascript/vis.js', true);

// build tab navigation & print header
$PAGE->set_pagelayout('embedded');
echo $output->header_v2();

/* CONTENT REGION */
if (!$exacomp_graph->data) {
	echo block_exacomp_get_string('topic_3dchart_empty');
} else {
	echo html_writer::div(null, null, array('id' => 'mygraph'));
}

/* END CONTENT REGION */
echo $output->footer();
