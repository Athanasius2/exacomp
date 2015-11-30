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
 * dukreminder block.
 *
 * @package	block_exacomp
 * @copyright  gtn gmbh <office@gtn-solutions.com>
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exacomp\event;
defined('MOODLE_INTERNAL') || die();

/**
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  gtn gmbh <office@gtn-solutions.com>
 */
class competence_assigned extends base {

	/**
	 * Init
	 * @return nothing
	 */
	protected function init() {
		$this->data['crud'] = 'u';
		$this->data['edulevel'] = self::LEVEL_TEACHING;
		$this->data['objecttable'] = 'block_exacompdescriptors';
	}

	/**
	 * Return localised event name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return get_string('eventscompetenceassigned', 'block_exacomp');
	}

	/**
	 * Get description
	 * @return string
	 */
	public function get_description() {
		return "User {$this->userid} assigned competence {$this->objectid} for user {$this->relateduserid} in course {$this->courseid}";
	}

	/**
	 * Get URL related to the action
	 *
	 * @return \moodle_url
	 */
	public function get_url() {
		return new \moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $this->courseid));
	}

}