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

$courseid = required_param('courseid', PARAM_INT);
$badgeid = optional_param('badgeid', 0, PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHA);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_badges';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/edit_badges.php', array('courseid' => $courseid));
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

if (!block_exacomp_moodle_badges_enabled()) {
	error("Badges library not found, please upgrade your Moodle to 2.5");
	exit;
}

/* CONTENT REGION */
/*NOT CONTINUED BECAUSE OF PRINT LEVELS, SHOULD BE THE SAME, EVERYTIME*/

/*
 if ($badgeid && $badge = $DB->get_record('badge', array('id' => $badgeid))) {

	if ($action == 'save') {
		$DB->delete_records('block_exacompdescbadge_mm', array("badgeid" => $badgeid));
		
		if (!empty($_POST['descriptors'])){
			foreach ($_POST['descriptors'] as $value=>$tmp) {
				$DB->insert_record('block_exacompdescbadge_mm', array("badgeid" => $badgeid, "descid" => intval($value)));
			}
		}
	}

	?>
	<div class='exabis_competencies_lis'>
	<form id="edit-activities" action="edit_badges.php?action=save&amp;courseid=<?php echo $courseid ?>&badgeid=<?=$badgeid?>" method="post">

	<div class="grade-report-grader">
	<table id="comps" class="exabis_comp_comp">
	<tr class="heading r0">
	<td class="category catlevel1" scope="col"><h2><?php echo $COURSE->fullname; ?></h2></td>
	<td class="category catlevel1 bottom" scope="col"></td>
	</tr>
	<tr><td></td>
	<?php
	echo '<td class="ec_tableheadwidth"><a href="' . '">' . $badge->name . '</a></td>';
	?>
	</tr>
	<?php
		
	function block_exacomp_print_levels($level, $subs, &$data, $rowgroup_class = '') {
		if (empty($subs)) return;

		extract((array)$data);
		
		if ($level == 0) {
			foreach ($subs as $group) {
				?>
				<tr class="ec_heading">
				<td colspan="2"><h4><?php echo $group->title; ?></h4></td>
				</tr>
				<?php

				block_exacomp_print_levels($level+1, $group->subs, $data);
			}
			
			return;
		}
		
		foreach ($subs as $item) {
			$hasSubs = !empty($item->subs) || !empty($item->descriptors);

			if ($hasSubs) {
				$data->rowgroup++;
				$this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$data->rowgroup.' '.$rowgroup_class;
				$subs_rowgroup_class = 'rowgroup-content rowgroup-content-'.$data->rowgroup.' '.$rowgroup_class;
			} else {
				$this_rowgroup_class = $rowgroup_class;
			}
			
			?>
			<tr class="ec_heading <?php echo $this_rowgroup_class; ?>">
				<td class="rowgroup-arrow" style="padding-left: <?php echo ($level-1)*20+12; ?>px" colspan="2"><div><?php echo $item->title; ?></div></td></tr>
			</tr>
			<?php

			if (isset($item->subs))
				block_exacomp_print_levels($level+1, $item->subs, $data, $subs_rowgroup_class);

			if (!empty($item->descriptors)) {
				foreach ($item->descriptors as $descriptor) {

					echo '<tr class="r2 '.$subs_rowgroup_class.'">';
					echo '<td class="competencetitle" style="padding-left: '.(($level-1)*20+12).'px">' . $descriptor->title . '</td>';
					echo '<td>'.
						'<input type="checkbox" name="descriptors[' . $descriptor->id . ']" '.(in_array($descriptor->id, $selectedDescriptors)?' checked="checked"':'').'" />'.
						'</td>';
					echo '</tr>';
				}
			}
		}
	}
	
	$levels = block_exacomp_get_competence_tree_for_activity_selection($courseid);
	$data = (object)array(
		'rowgroup' => 0,
		'courseid' => $courseid,
		'badge' => $badge,
		'selectedDescriptors' => $DB->get_records_menu('block_exacompdescbadge_mm',array('badgeid'=>$badgeid),null,'id, descid')
	);
	
	block_exacomp_print_levels(0, $levels, $data);
	
	echo '</table>';
	echo '<input type="submit" value="' . get_string('save', 'admin') . '" />';
	echo '</div>';
	echo '</form>';
	echo '</div>';

	echo $OUTPUT->footer();
	return;
}



$badges = badges_get_badges(BADGE_TYPE_COURSE, $courseid); // , $sort = '', $dir = '', $page = 0, $perpage = BADGE_PERPAGE, $user = 0) {


if (!$badges) {
	echo $OUTPUT->box(text_to_html(get_string("no_badges_yet", "block_exacomp")));
	echo $OUTPUT->footer();
	return;
}



block_exacomp_award_badges($courseid);


foreach ($badges as $badge) {
	$descriptors = $DB->get_records_sql('
		SELECT d.*
		FROM {block_exacompdescriptors} d
		JOIN {block_exacompdescbadge_mm} db ON d.id=db.descid AND db.badgeid=?
	', array($badge->id));

	echo '<div style="padding: 10px;">';
	$context = context_course::instance($badge->courseid);
	$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
	echo html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
	echo '<div style="font-weight: bold;">';
	echo $badge->name;
	echo "</div>";
	if ($badge->is_locked()) {
		echo get_string('statusmessage_'.$badge->status, 'badges');
	} elseif ($badge->status == BADGE_STATUS_ACTIVE) {
		echo get_string('statusmessage_'.$badge->status, 'badges');
		echo '<div>';
		?>
			<form method="post" action="<?php echo $CFG->wwwroot.'/badges/action.php'; ?>">
				<input type="hidden" name="id" value="<?php echo $badge->id; ?>" />
				<input type="hidden" name="lock" value="1" />
				<input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>" />
				<input type="hidden" name="return" value="<?php echo '/blocks/exacomp/edit_badges.php?courseid=' . $courseid; ?>" />
				<input type="submit" value="<?php echo get_string('deactivate', 'badges'); ?>" />
			</form>
		<?php
		echo '</div>';
	} elseif (!$badge->has_manual_award_criteria()) {
		echo '<div>';
		echo '<a href="'.$CFG->wwwroot.'/badges/edit.php?id='.$badge->id.'&action=details">To award this badge in exacomp you have to add the "Manual issue by role" criteria</a>';
		echo '</div>';
	} else {
		if (empty($descriptors)) {
			echo '<div>';
			echo '<a href="'.$CFG->wwwroot.'/blocks/exacomp/edit_badges.php?courseid=' . $courseid.'&badgeid='.$badge->id.'">To award this badge in exacomp you have to configure competencies</a>';
			echo '</div>';
		} else {
			echo '<div style="padding-bottom: 20px;">';
			
			?>
			<form method="post" action="<?php echo $CFG->wwwroot.'/badges/action.php'; ?>">
				<input type="hidden" name="id" value="<?php echo $badge->id; ?>" />
				<input type="hidden" name="activate" value="1" />
				<input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>" />
				<input type="hidden" name="return" value="<?php echo '/blocks/exacomp/edit_badges.php?courseid=' . $courseid; ?>" />

				This badge is ready to be activated: 
				<input type="submit" value="<?php echo get_string('activate', 'badges'); ?>" />
			</form>
			<?php
			
			echo '</div>';
			echo '<div>';
			echo '<a href="'.$CFG->wwwroot.'/badges/edit.php?id='.$badge->id.'&action=details">configure badge</a>';
			echo ' / <a href="'.$CFG->wwwroot.'/blocks/exacomp/edit_badges.php?courseid=' . $courseid.'&badgeid='.$badge->id.'">configure competencies</a>';
			echo '</div>';
		}
	}
	if ($descriptors) {
		echo '<ul>';
		foreach ($descriptors as $descriptor) {
			echo '<li>'.$descriptor->title.'</li>';
		}
		echo '</ul>';
	}
	echo "</div>";
}

 */

echo "CONTENT";

/* END CONTENT REGION */

echo $OUTPUT->footer();

?>