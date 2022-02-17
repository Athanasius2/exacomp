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


global $CFG;
require __DIR__.'/../inc.php';
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');


function moodle_backup($activityid, $user_doing_the_backup){

    if (!is_siteadmin()) {
        die('No Admin!');
    }

    global $CFG;
    $CFG->keeptempdirectoriesonbackup = true;

    $bc = new backup_controller(backup::TYPE_1ACTIVITY, $activityid, backup::FORMAT_MOODLE,
        backup::INTERACTIVE_NO, backup::MODE_GENERAL, $user_doing_the_backup);

    $backup_id = $bc->get_backupid();
    $bc->get_plan()->set_excluding_activities();
    $bc->execute_plan();
    return $backup_id;

}
