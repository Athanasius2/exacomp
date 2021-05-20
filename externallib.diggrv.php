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

defined('MOODLE_INTERNAL') || die();

require __DIR__.'/inc.php';
require_once $CFG->libdir.'/externallib.php';
require_once __DIR__.'/externallib.php';

use block_exacomp\globals as g;

class block_exacomp_external_diggrv extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_v_edit_course_parameters() {
        return new external_function_parameters (array(
            'courseid' => new external_value (PARAM_INT, 'courseid of course that should be edited'),
            'fullname' => new external_value (PARAM_TEXT, 'new fullname of course'),
            // 'shortname' => new external_value (PARAM_TEXT, 'new shortname of course'),
        ));
    }

    /**
     * Create an example or update it
     * create example
     * @ws-type-write
     *
     * @return array
     */
    public static function diggrplus_v_edit_course($courseid, $fullname) {
        static::validate_parameters(static::diggrplus_v_edit_course_parameters(), array(
            'courseid' => $courseid,
            'fullname' => $fullname,
            // 'shortname' => $shortname
        ));
        global $DB;

        block_exacomp_require_diggrv_enabled();
        block_exacomp_require_teacher($courseid);


        $course = $DB->get_record('course', array('id' => $courseid));
        $course->fullname = $fullname;
        // $course->shortname = $shortname;
        $DB->update_record('course', $course);

        return array("success" => true);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_v_edit_course_returns() {
        return new external_single_structure (array(
            'success' => new external_value (PARAM_BOOL, 'status'),
        ));
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_v_create_or_update_student_parameters() {
        return new external_function_parameters (array(
            'courseid' => new external_value (PARAM_INT, 'courseid of course where the student should be added'),
            'userid' => new external_value (PARAM_INT, 'userid of student. 0 if new', VALUE_DEFAULT, 0),
            'firstname' => new external_value (PARAM_TEXT, 'firstname of student'),
            'lastname' => new external_value (PARAM_TEXT, 'lastname of student'),
            'ausserordentlich' => new external_value (PARAM_TEXT),
        ));
    }

    /**
     * Create an example or update it
     * create example
     * @ws-type-write
     *
     * @return array
     * @throws moodle_exception
     */
    public static function diggrplus_v_create_or_update_student($courseid, $userid = 0, $firstname, $lastname, $ausserordentlich) {
        static::validate_parameters(static::diggrplus_v_create_or_update_student_parameters(), array(
            'courseid' => $courseid,
            'userid' => $userid,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'ausserordentlich' => $ausserordentlich,
        ));
        global $CFG;
        require_once $CFG->dirroot.'/lib/enrollib.php';
        require_once $CFG->dirroot.'/user/lib.php';

        block_exacomp_require_diggrv_enabled();
        block_exacomp_require_teacher($courseid);

        if ($userid == 0) {
            // create the student
            $username = 'diggrv-'.round((microtime(true) - 1600000000) * 1000);
            $user = array(
                'username' => $username,
                'password' => generate_password(20),
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $username.'@diggr-plus.at',
                'description' => 'diggrv',
                'suspended' => 1,
                'mnethostid' => 1,
                'confirmed' => 1,
            );
            $userid = user_create_user($user);

            // enrol the student
            $enrol = enrol_get_plugin("manual"); //enrolment = manual
            $instances = enrol_get_instances($courseid, true);
            $manualinstance = null;
            foreach ($instances as $instance) {
                if ($instance->enrol == "manual") {
                    $manualinstance = $instance;
                    break;
                }
            }

            $enrol->enrol_user($manualinstance, $userid, 5); //The roleid of "student" is 5 in mdl_role table
        } else {
            $users = user_get_users_by_id([$userid]);
            $user = array_pop($users);

            if (!block_exacomp_is_diggrv_student($user)) {
                throw new moodle_exception('user is not a diggrv-student');
            }
            if (!block_exacomp_is_user_in_course($userid, $courseid)) {
                throw new moodle_exception('user is not enrolled in course');
            }

            $user->firstname = $firstname;
            $user->lastname = $lastname;
            user_update_user($user, false, false);
        }

        block_exacomp_set_custom_profile_field_value($userid, 'ausserordentlich', $ausserordentlich);

        return array("userid" => $userid);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_v_create_or_update_student_returns() {
        return new external_single_structure (array(
            'userid' => new external_value (PARAM_INT, 'userid of created or updated user'),
        ));
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_v_delete_student_parameters() {
        return new external_function_parameters (array(
            'courseid' => new external_value (PARAM_INT),
            'userid' => new external_value (PARAM_INT, 'userid of student. 0 if new'),
        ));
    }

    /**
     * Create an example or update it
     * create example
     * @ws-type-write
     */
    public static function diggrplus_v_delete_student($courseid, $userid) {
        static::validate_parameters(static::diggrplus_v_delete_student_parameters(), array(
            'courseid' => $courseid,
            'userid' => $userid,
        ));

        global $DB;

        block_exacomp_require_diggrv_enabled();
        block_exacomp_require_teacher($courseid);
        if (!block_exacomp_is_user_in_course($userid, $courseid)) {
            throw new moodle_exception('user is not enrolled in course');
        }

        $user = $DB->get_record('user', ['id' => $userid]);

        // unenroll from course
        role_unassign_all(array('userid' => $userid, 'contextid' => context_course::instance($courseid)->id));

        if (block_exacomp_is_diggrv_student($user)) {
            // only delete, if really is a diggrv user, else user just gets unenrolled
            $DB->update_record('user', ['id' => $userid, 'deleted' => 1]);
        }

        return array("success" => true);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_v_delete_student_returns() {
        return new external_single_structure (array(
            'success' => new external_value (PARAM_BOOL, 'status'),
        ));
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_v_get_student_by_id_parameters() {
        return new external_function_parameters (array(
            'courseid' => new external_value (PARAM_INT),
            'userid' => new external_value (PARAM_INT, 'userid of student. 0 if new'),
        ));
    }

    /**
     * Create an example or update it
     * create example
     * @ws-type-write
     */
    public static function diggrplus_v_get_student_by_id($courseid, $userid) {
        static::validate_parameters(static::diggrplus_v_get_student_by_id_parameters(), array(
            'courseid' => $courseid,
            'userid' => $userid,
        ));
        global $DB;

        block_exacomp_require_diggrv_enabled();
        block_exacomp_require_teacher($courseid);
        if (!block_exacomp_is_user_in_course($userid, $courseid)) {
            throw new moodle_exception('user is not enrolled in course');
        }

        $user = $DB->get_record('user', ['id' => $userid]);

        $user->ausserordentlich = block_exacomp_get_custom_profile_field_value($userid, 'ausserordentlich');

        return $user;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_v_get_student_by_id_returns() {
        return new external_single_structure (array(
            'id' => new external_value (PARAM_INT, 'id'),
            'username' => new external_value (PARAM_TEXT, 'username'),
            'firstname' => new external_value (PARAM_TEXT, 'firstname'),
            'lastname' => new external_value (PARAM_TEXT, 'lastname'),
            'email' => new external_value (PARAM_TEXT, 'email'),
            'suspended' => new external_value (PARAM_BOOL, 'suspended'),
            'ausserordentlich' => new external_value (PARAM_BOOL),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_v_get_student_grading_tree_parameters() {
        return new external_function_parameters (array(
            'userid' => new external_value (PARAM_INT),
            'courseid' => new external_value (PARAM_INT),
        ));
    }

    /**
     * Get competence statistic for profile
     *
     * @ws-type-read
     */
    public static function diggrplus_v_get_student_grading_tree($userid = 0, $courseid = 0) {
        global $USER, $DB;

        static::validate_parameters(static::diggrplus_v_get_student_grading_tree_parameters(), array(
            'userid' => $userid,
            'courseid' => $courseid,
        ));

        block_exacomp_external::require_can_access_course_user($courseid, $userid);
        $courses = enrol_get_users_courses($userid);
        $course = $courses[$courseid];

        $structure = array();

        //showallexamples filters out those, who have not creatorid => those who were imported
        $tree = block_exacomp_get_competence_tree($course->id, null, null, false, null, true, null, false, false, true, false, true);
        // $students = block_exacomp_get_students_by_course($course->id);
        // $student = $students[$userid];
        // block_exacomp_get_user_information_by_course($student, $course->id);
        foreach ($tree as $subject) {
            $subjstudconfig = $DB->get_record('block_exacompsubjstudconfig', ['studentid' => $userid, 'subjectid' => $subject->id]);
            $elem_sub = new stdClass ();
            $elem_sub->id = $subject->id;
            $elem_sub->title = static::custom_htmltrim($subject->title);
            $elem_sub->courseid = $course->id;
            $elem_sub->courseshortname = $course->shortname;
            $elem_sub->coursefullname = $course->fullname;
            // $elem_sub->teacherevaluation = $student->subjects->teacher[$subject->id];
            // $elem_sub->studentevaluation = $student->subjects->student[$subject->id];
            $elem_sub->assess_with_grades = !!$subjstudconfig->assess_with_grades;
            $elem_sub->spf = !!$subjstudconfig->spf; // this makes it false instead of null if nothing exists
            $elem_sub->personalisedtext = $subjstudconfig->personalisedtext;

            // TODO:
            // $elem_sub->mwd = 'M';
            //$grading = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_SUBJECT, $subject->id);
            // $grading is not used, because the personalisedtext is now stored in the subjstudconfig->personalisedtext, not in the compuser table
            // 2021_05_17 now it could be stored in both, but we use the one from  subjstudconfig for faster access without any joins (redundant data: bad)

            $elem_sub->is_religion = false;
            $elem_sub->is_pflichtgegenstand = false;
            $elem_sub->is_freigegenstand = false;

            $elem_sub->topics = array();
            foreach ($subject->topics as $topic) {
                $elem_topic = new stdClass ();
                $elem_topic->id = $topic->id;
                $elem_topic->title = static::custom_htmltrim($topic->title);
                $elem_topic->descriptors = array();
                // $elem_topic->teacherevaluation = $student->topics->teacher[$topic->id];
                // $elem_topic->studentevaluation = $student->topics->student[$topic->id];

                foreach ($topic->descriptors as $descriptor) {
                    if (!$descriptor->visible) {
                        continue;
                    }

                    $elem_desc = new stdClass ();
                    $elem_desc->id = $descriptor->id;
                    $elem_desc->title = static::custom_htmltrim($descriptor->title);
                    $elem_desc->niveauid = $descriptor->niveauid;
                    $elem_desc->niveau_title = static::custom_htmltrim($descriptor->niveau_title);
                    // $elem_desc->teacherevaluation = $student->competencies->teacher[$descriptor->id];

                    $grading = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);
                    $elem_desc->teacherevaluation = $grading->value;
                    $elem_desc->personalisedtext = $grading->personalisedtext;

                    $elem_topic->descriptors[] = $elem_desc;
                }
                $elem_sub->topics[] = $elem_topic;
            }
            if (!empty($elem_sub->topics)) {
                $structure[] = $elem_sub;
            }
        }

        $statistics_return = [
            'competencetree' => $structure,
        ];

        return $statistics_return;
    }

    public static function diggrplus_v_get_student_grading_tree_returns() {
        return new external_single_structure (array(
            'competencetree' => new external_multiple_structure (new external_single_structure (array(
                'id' => new external_value (PARAM_INT, 'id of subject'),
                'title' => new external_value (PARAM_TEXT, 'title of subject'),
                // 'mwd' => new external_value (PARAM_TEXT),
                'personalisedtext' => new external_value (PARAM_TEXT),
                'assess_with_grades' => new external_value (PARAM_BOOL),
                'spf' => new external_value (PARAM_BOOL),
                'is_religion' => new external_value (PARAM_BOOL),
                'is_pflichtgegenstand' => new external_value (PARAM_BOOL),
                'is_freigegenstand' => new external_value (PARAM_BOOL),
                'topics' => new external_multiple_structure (new external_single_structure (array(
                    'id' => new external_value (PARAM_INT, 'id of example'),
                    'title' => new external_value (PARAM_TEXT, 'title of topic'),
                    'descriptors' => new external_multiple_structure (new external_single_structure (array(
                        'id' => new external_value (PARAM_INT),
                        'title' => new external_value (PARAM_TEXT),
                        'niveauid' => new external_value (PARAM_INT),
                        'niveau_title' => new external_value (PARAM_TEXT),
                        'teacherevaluation' => new external_value (PARAM_INT, 'teacher evaluation of descriptor'),
                        'personalisedtext' => new external_value (PARAM_TEXT),
                    ))),
                ))),
            ))),
        ));
    }

    public static function diggrplus_v_save_student_grading_parameters() {
        return new external_function_parameters (array(
            'userid' => new external_value (PARAM_INT),
            'courseid' => new external_value (PARAM_INT),
            'subjects' => new external_multiple_structure(new external_single_structure (array(
                'id' => new external_value (PARAM_INT),
                'personalisedtext' => new external_value (PARAM_TEXT, '', VALUE_OPTIONAL),
                'assess_with_grades' => new external_value (PARAM_BOOL),
                'spf' => new external_value (PARAM_BOOL),
            )), '', VALUE_OPTIONAL),
            'descriptors' => new external_multiple_structure(new external_single_structure (array(
                'id' => new external_value (PARAM_INT),
                'teacherevaluation' => new external_value (PARAM_INT, '', VALUE_OPTIONAL),
                'personalisedtext' => new external_value (PARAM_TEXT, '', VALUE_OPTIONAL),
            )), ''),
        ));
    }

    /**
     * @ws-type-read
     */
    public static function diggrplus_v_save_student_grading($userid = 0, $courseid = 0, $subject_gradings = [], $descriptor_gradings = []) {
        global $USER, $DB;

        static::validate_parameters(static::diggrplus_v_save_student_grading_parameters(), array(
            'userid' => $userid,
            'courseid' => $courseid,
            'subjects' => $subject_gradings,
            'descriptors' => $descriptor_gradings,
        ));

        block_exacomp_external::require_can_access_course_user($courseid, $userid);
        $courses = enrol_get_users_courses($userid);
        $course = $courses[$courseid];

        $tree = block_exacomp_get_competence_tree($course->id, null, null, false, null, true, null, false, false, true, false, true);
        // $students = block_exacomp_get_students_by_course($course->id);
        // $student = $students[$userid];
        // block_exacomp_get_user_information_by_course($student, $course->id);

        $subjects = $tree;
        $allowedDescriptors = [];
        foreach ($subjects as $subject) {
            foreach ($subject->topics as $topic) {
                foreach ($topic->descriptors as $descriptor) {
                    if (!$descriptor->visible) {
                        continue;
                    }

                    $allowedDescriptors[$descriptor->id] = $descriptor;
                }
            }
        }

        foreach ($subject_gradings as $subject_grading) {
            if (!$subjects[$subject_grading['id']]) {
                throw new Exception('subject not allowed');
            }

            block_exacomp_set_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_SUBJECT, $subject_grading['id'], null);

             g::$DB->insert_or_update_record('block_exacompsubjstudconfig', [
                 'assess_with_grades' => $subject_grading['assess_with_grades'],
                 'spf' => $subject_grading['spf'],
                 'personalisedtext' => $subject_grading['personalisedtext'],
                 // TODO:
                 // $elem_sub->mwd = 'M';
                 // $elem_sub->personalisedtext = 'test test test test test test';
                 // $elem_sub->is_pflichtgegenstand = true;
                 // $elem_sub->is_freigegenstand = false;
             ], ['studentid' => $userid, 'subjectid' => $subject_grading['id']]);
        }

        foreach ($descriptor_gradings as $descriptor_grading) {
            if (!$allowedDescriptors[$descriptor_grading['id']]) {
                throw new Exception('descriptor not allowed');
            }

            block_exacomp_set_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor_grading['id'], [
                'value' => $descriptor_grading['teacherevaluation'],
                'personalisedtext' => $descriptor_grading['personalisedtext'],
            ]);
        }

        return array("success" => true);
    }

    public static function diggrplus_v_save_student_grading_returns() {
        return new external_single_structure (array(
            'success' => new external_value (PARAM_BOOL, 'status'),
        ));
    }

    public static function diggrplus_v_print_student_grading_report_parameters() {
        return new external_function_parameters (array(
            'userid' => new external_value (PARAM_INT),
            'courseid' => new external_value (PARAM_INT),
        ));
    }

    /**
     * @ws-type-read
     */
    public static function diggrplus_v_print_student_grading_report($userid, $courseid) {
        global $USER, $DB;

        static::validate_parameters(static::diggrplus_v_print_student_grading_report_parameters(), array(
            'userid' => $userid,
            'courseid' => $courseid,
        ));

        block_exacomp_external::require_can_access_course_user($courseid, $userid);
        $courses = enrol_get_users_courses($userid);
        $course = $courses[$courseid];
        $user = $DB->get_record('user', ['id' => $userid]);

        $structure = array();

        $tree = block_exacomp_get_competence_tree($course->id, null, null, false, null, true, null, false, false, true, false, true);

        $subjects_html = '';
        foreach ($tree as $subject) {
            if (empty($subject->topics)) {
                continue;
            }

            //     <td><b>M:</b>  unterschiedliche Rollen des familiären Zusammenlebens kennen und nennen;  sich an Spielen zur Verbesserung der Kommunikation aktiv beteiligen; unterschiedliche Pflanzen und Tiere benennen; Teile des menschlichen Körpers und deren Funktionen kennen und benennen; die Verwendung von Geräten und Werkzeugen aus der eigenen Umwelt beschreiben; die Wirkungsweise von Kräften beobachten und beschreiben<br/>
            //     <b>W:</b> verschiedene Wege zu unterschiedlichen Bezugspunkten beschreiben; einfache geografische Gegebenheiten der Umgebung beschreiben; über die verantwortungsvolle Nutzung der Dinge des täglichen Lebens Bescheid wissen; unterschiedliche Berufe und deren Aufgabenfelder beschreiben<br/>
            //     <b>D:</b> alte und neue Gegenstände beschreiben und mit den jeweiligen Lebensumständen in Zusammenhang bringen; über alle Zeitabläufe eines Jahres (Minuten, Stunden, Tage, Wochen, Monate, Jahreszeiten) Bescheid wissen und Auskunft geben<br/>
            //     Text des frei befüllbaren Texfeldes für Ergänzungen durch die Lehrperson, z.B.: zu Vereinbarungen aus dem KEL-Gespräche, Fördermaßnahmen etc.
            //     </td>
            // </tr>'.

            $subject_content_html = [];
            foreach ($subject->topics as $topic) {
                foreach ($topic->descriptors as $descriptor) {
                    if (!$descriptor->visible) {
                        continue;
                    }

                    $grading = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);
                    // TODO: activate
                    // if (!$grading->value) {
                    //     // skip not graded items
                    //     continue;
                    // }

                    if (!$subject_content_html[$descriptor->niveauid]) {
                        $subject_content_html[$descriptor->niveauid] = '<b>'.static::custom_htmltrim($descriptor->niveau_title).':</b> ';
                    }

                    $subject_content_html[$descriptor->niveauid] .= static::custom_htmltrim($descriptor->title).', ';
                }
            }

            $subject_content_html = array_map(function($item) {
                // remove trailing colon
                return trim($item, ', ');
            }, $subject_content_html);
            $subject_content_html = join('<br/>', $subject_content_html);

            $grading = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_SUBJECT, $subject->id);
            if ($personalisedtext = trim($grading->personalisedtext)) {
                if ($subject_content_html) {
                    $subject_content_html .= '<br/><br/><b>Zusätzliche Informationen:</b><br/>';
                }

                $subject_content_html .= $personalisedtext;
            }

            if (!$subject_content_html) {
                $subject_content_html .= '-';
            }

            $subjects_html .= '<tr nobr="true"><td>'.static::custom_htmltrim($subject->title).'</td>';
            $subjects_html .= '<td>';
            $subjects_html .= $subject_content_html;
            $subjects_html .= '</td></tr>';
        }

        $pdf = \block_exacomp\printer::getPdfPrinter('P');
        $pdf->SetFont('times', '', 9);
        $pdf->setHeaderFont(['times', '', 9]);
        $pdf->SetLeftMargin(20);
        $pdf->SetRightMargin(20);

        $pdf->setStyle('
			* {
				font-size: 10pt;
			}
			div {
				padding: 0;
				margin: 0;
			}

			table.header {
			    padding: 0;
            }
            table.content {
                padding: 3px 6pt;
            }
            table.content td {
                border: 0.2pt solid black;
            }
        ');

        $pdf->writeHTML('
            <br/>
            <br/>
            <br/>
            <br/>
            <div style="text-align: center;">Bezeichnung und Standort der Schule</div>
            <br/>
            <br/>
            <div style="text-align: center; font-size: 20pt;">Schriftliche Erläuterung zur Ziffernbeurteilung</div>
            <br/>
            <br/>
            <br/>
            <table class="header"><tr>
                <td>für '.fullname($user).'</td>
                <td style="text-align: right">Schuljahr 2021/2022</td>
            </tr></table>
            <br/>
            <br/>
            <br/>
            <table class="content">
                <tr nobr="true">
                    <td style="width: 30%"></td>
                    <td style="width: 70%"><b>Die Schülerin/der Schüler hat folgende Anforderungen*) erfüllt:</b></td>
                </tr>'.
            $subjects_html.
            // '<tr nobr="true">
            //     <td>Sachunterricht</td>
            //     <td><b>M:</b>  unterschiedliche Rollen des familiären Zusammenlebens kennen und nennen;  sich an Spielen zur Verbesserung der Kommunikation aktiv beteiligen; unterschiedliche Pflanzen und Tiere benennen; Teile des menschlichen Körpers und deren Funktionen kennen und benennen; die Verwendung von Geräten und Werkzeugen aus der eigenen Umwelt beschreiben; die Wirkungsweise von Kräften beobachten und beschreiben<br/>
            //     <b>W:</b> verschiedene Wege zu unterschiedlichen Bezugspunkten beschreiben; einfache geografische Gegebenheiten der Umgebung beschreiben; über die verantwortungsvolle Nutzung der Dinge des täglichen Lebens Bescheid wissen; unterschiedliche Berufe und deren Aufgabenfelder beschreiben<br/>
            //     <b>D:</b> alte und neue Gegenstände beschreiben und mit den jeweiligen Lebensumständen in Zusammenhang bringen; über alle Zeitabläufe eines Jahres (Minuten, Stunden, Tage, Wochen, Monate, Jahreszeiten) Bescheid wissen und Auskunft geben<br/>
            //     <br/>
            //     <b>Zusätzliche Informationen:</b><br/>
            //     Text des frei befüllbaren Texfeldes für Ergänzungen durch die Lehrperson, z.B.: zu Vereinbarungen aus dem KEL-Gespräche, Fördermaßnahmen etc.
            //     </td>
            // </tr>'.
            '</table>
            <br/><br/><br/>
            <br/><br/><br/>
            <div nobr="true">
                <table class="header"><tr>
                    <td style="width: 40%; text-align: center;">...................................................<br/>Schul-/Clusterleitung</td>
                    <td style="width: 20%; text-align: center;">Rund-<br/>siegel</td>
                    <td style="width: 40%; text-align: center;">...................................................<br/>Klassenlehrer/Klassenlehrerin</td>
                </tr></table>

                <br/><br/><br/><br/>
                *) Anforderungsniveaus: Mindestanforderungen (M), wesentliche Anforderungen (W), (weit) darüber hinausgehende Anforderungen (D)
            </div>
        ');
        $pdf->Output();
        exit;
    }

    public static function diggrplus_v_print_student_grading_report_returns() {
        return new external_single_structure (array(
            'pdf' => new external_value (PARAM_FILE),
        ));
    }

    protected function custom_htmltrim($string) {
        return block_exacomp_external::custom_htmltrim($string);
    }
}