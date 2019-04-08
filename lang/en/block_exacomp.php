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

// shown in admin plugin list
$string['pluginname'] = 'Exabis Competence Grid';
// shown in block title and all headers
$string['blocktitle'] = 'Competence Grid';
$string['exacomp:addinstance'] = 'Add a Exabis Competence Grid block';
$string['exacomp:myaddinstance'] = 'Add a Exabis Competence Grid block to my moodle';
$string['exacomp:teacher'] = 'overview of trainer actions in a course';
$string['exacomp:admin'] = 'overview of administrator actions in a course';
$string['exacomp:student'] = 'overview of student actions in a course';
$string['exacomp:use'] = 'use Exabis Competence Grid';
$string['exacomp:deleteexamples'] = 'delete examples';
$string['exacomp:assignstudents'] = 'Assign external trainers';

//Cache definition
$string['cachedef_visibility_cache'] = 'Cache to improve performance while checking visibilities';

// === Admin Tabs ===
$string['tab_admin_import'] = 'Import/Export';
$string['tab_admin_settings'] = 'Admin Settings';
$string['tab_admin_configuration'] = 'Standards pre-selection';
$string['admin_config_pending'] = 'Standards pre-selection needs to be performed by the Moodle administrator';
$string['tab_admin_taxonomies'] = 'Taxonomies';


// === Teacher Tabs ===
$string['tab_teacher_settings'] = 'Settings';
$string['tab_teacher_settings_configuration'] = 'Configuration';
$string['tab_teacher_settings_selection_st'] = 'Schooltype selection';
$string['tab_teacher_settings_selection'] = 'Subject selection';
$string['tab_teacher_settings_assignactivities'] = 'Assign Moodle activities';
$string['tab_teacher_settings_badges'] = 'Edit badges';
$string['tab_teacher_settings_new_subject'] = 'Create new subject';
$string['tab_teacher_settings_taxonomies'] = 'Taxonomies';
$string['tab_teacher_report_general'] = 'General report';
$string['tab_teacher_report_annex'] = 'Annex';
$string['tab_teacher_report_annex_title'] = 'Annex to the learning development report';
$string['create_html'] = 'generate HTML preview';
$string['create_docx'] = 'generate docx';
$string['create_pdf'] = 'generate pdf';
$string['tab_teacher_report_annex_template'] = 'template docx';
$string['tab_teacher_report_annex_delete_template'] = 'delete';

// === Student Tabs ===
$string['tab_student_all'] = 'All gained competencies';


// === Generic Tabs (used by Teacher and Students) ===
$string['tab_competence_grid'] = 'Reports';
$string['tab_competence_overview'] = 'Competence grid';
$string['tab_examples'] = 'Examples and tasks';
$string['tab_badges'] = 'My badges';
$string['tab_competence_profile'] = 'Competence profile';
$string['tab_competence_profile_profile'] = 'Profile';
$string['tab_competence_profile_settings'] = 'Settings';
$string['tab_help'] = 'Help';
$string['tab_profoundness'] = 'Basic & Extended Competencies';
$string['tab_cross_subjects'] = 'Interdisciplinary Subjects';
$string['tab_cross_subjects_overview'] = 'Overview';
$string['tab_cross_subjects_course'] = 'Course Interdisciplinary Subjects';
$string['tab_weekly_schedule'] = 'Weekly Schedule';
$string['tab_group_reports'] = 'Group Reports';
$string['assign_descriptor_to_crosssubject'] = 'Assign the competence "{$a}" to the following interdisciplinary subjects:';
$string['assign_descriptor_no_crosssubjects_available'] = 'No interdisciplinary subjects are available.';
$string['first_configuration_step'] = 'The first step of the configuration is to import some data to Exabis Competence Grid.';
$string['second_configuration_step'] = 'In this configuration step you have to pre-select standards.';
$string['next_step'] = 'This configuration step has been completed. Click here to continue configuration.';
$string['next_step_teacher'] = 'The configuration that has to be done by the administrator is now completed. To continue with the course specific configuration click here.';
$string['teacher_first_configuration_step'] = 'The first step of course configuration is to adjust general settings for your course.';
$string['teacher_second_configuration_step'] = 'In the second configuration step topics to work with in this course have to be selected.';
$string['teacher_third_configuration_step'] = 'The next step is to associate Moodle activities with competencies ';
$string['teacher_third_configuration_step_link'] = '(Optional: if you don\'t want to work with activities untick the setting "I want to work with Moodle activities" in the tab "Configuration")';
$string['completed_config'] = 'The configuration of Exabis Competence Grid is completed.';
$string['optional_step'] = 'There are no participants in your course yet. If you want to enrol some please use this link.';
$string['next_step_first_teacher_step'] = 'Click here to continue configuration.';


// === Block Settings ===
$string['settings_xmlserverurl'] = 'Server-URL';
$string['settings_configxmlserverurl'] = 'Url to a xml file, which is used for keeping the database entries up to date';
$string['settings_autotest'] = 'Automatical gain of competence through Moodle-quizzes';
$string['settings_autotest_description'] = 'Competences that are associated with quizzes are gained automatically if needed percentage of quiz is reached';
$string['settings_testlimit'] = 'Quiz-percentage needed to gain competence';
$string['settings_testlimit_description'] = 'This percentage has to be reached to gain the competence';
$string['settings_usebadges'] = 'Connect to Moodle badges';
$string['settings_usebadges_description'] = 'Check to associate badges with competences';
$string['settings_interval'] = 'Unit duration';
$string['settings_interval_description'] = 'Duration of the units in the schedule';
$string['settings_scheduleunits'] = 'Amount of units';
$string['settings_scheduleunits_description'] = 'Amount of units in the schedule';
$string['settings_schedulebegin'] = 'Begin of schedule';
$string['settings_schedulebegin_description'] = 'Begin time for the first unit in the schedule. Format hh:mm';
$string['settings_admin_scheme'] = 'Predefined Konfiguration';
$string['settings_admin_scheme_description'] = 'Grading can be done on different difficulty levels.';
$string['settings_admin_scheme_none'] = 'no global difficulty levels';
$string['settings_additional_grading'] = 'Adapted grading';
$string['settings_additional_grading_description'] = 'Grading limited from "not gained(0)" - "completely gained(3)"';
$string['settings_periods'] = 'Timetable entries';
$string['settings_periods_description'] = 'Weekly schedule can be adapted to any timetable. Use one row in the text area for each time entry. You can use any format you like, e.g. "1st hour" or "07:30 - 09:00".';
$string['settings_heading_general'] = 'General';
$string['settings_heading_assessment'] = 'Assessment';
$string['settings_heading_visualisation'] = 'Visualisation';
$string['settings_heading_technical'] = 'Administrative';
$string['settings_heading_apps'] = 'Configuration for apps';
$string['settings_heading_scheme'] = 'Generic assessment scheme';
$string['settings_assessment_scheme_0'] = 'None';
$string['settings_assessment_scheme_1'] = 'Grade';
$string['settings_assessment_scheme_2'] = 'Verbose';
$string['settings_assessment_scheme_3'] = 'Points';
$string['settings_assessment_scheme_4'] = 'Yes/No';
$string['settings_assessment_diffLevel'] = 'Global assessment level';
$string['settings_assessment_SelfEval'] = 'Student assessment';
$string['settings_assessment_target_example'] = 'Material';
$string['settings_assessment_target_childcomp'] = 'Child competence';
$string['settings_assessment_target_comp'] = 'Competence';
$string['settings_assessment_target_topic'] = 'Topic';
$string['settings_assessment_target_subject'] = 'Subject';
$string['settings_assessment_target_theme'] = 'Theme (interdisciplinary)';
$string['settings_assessment_points_limit'] = 'Highest value for Points';
$string['settings_assessment_points_limit_description'] = 'assessment scheme points, limit for input';
$string['settings_assessment_grade_limit'] = 'Highest value for grade';
$string['settings_assessment_grade_limit_description'] = 'assessment scheme grade, limit for input';
$string['settings_assessment_diffLevel_options'] = 'Difficulty Level Options';
$string['settings_assessment_diffLevel_options_description'] = 'list of difficultiy Levels, i.e. G,M,E,Z';
$string['settings_assessment_diffLevel_options_default'] = 'G,M,E,Z';
$string['settings_assessment_verbose_options'] = 'verbose Options (EN)';
$string['settings_assessment_verbose_options_description'] = 'list of verbose Options, i.e. not gained, partly gained, mostly gained, completely gained';
$string['settings_assessment_verbose_options_default'] = 'not gained, partly gained, mostly gained, completely gained';
$string['settings_assessment_verbose_options_short'] = 'verbose Options (EN) short';
$string['settings_assessment_verbose_options_short_description'] = 'list of verbose Options, i.e. not gained, partly gained, mostly gained, completely gained';
$string['settings_assessment_verbose_options_short_default'] = 'ng, pg, mg, cg';
$string['settings_assessment_grade_verbose'] = 'verbalized grades (EN)';
$string['settings_assessment_grade_verbose_description'] = 'Verbalisierte Werte der Noten, kommagetrennt. Die Anzahl muß mit dem Wert "Höchste Note" oben übereinstimmen. z.B: Sehr gut, Gut, Befriedigend, Ausreichend, Mangelhaft, Ungenügend';
$string['settings_assessment_grade_verbose_default'] = 'Very good, Good, Satisfactory, Sufficient, Deficient, Insufficient';
$string['use_grade_verbose_competenceprofile'] = 'grades verbose competence profile ';
$string['use_grade_verbose_competenceprofile_descr'] = 'use grades verbose in competence profile';
$string['settings_sourceId'] = 'Source ID';
$string['settings_sourceId_description'] = 'Automatically generated ID of this Exacomp installation. This ID can not be changed';
$string['settings_admin_preconfiguration_none'] = 'No preconfiguration';
$string['settings_default_de_value'] = 'DE value: ';
$string['settings_assessment_SelfEval_verboses'] = 'Verboses for self evaluations';
$string['settings_assessment_SelfEval_verboses_long_columntitle'] = 'Long';
$string['settings_assessment_SelfEval_verboses_short_columntitle'] = 'Short';
$string['settings_assessment_SelfEval_verboses_edit'] = 'Edit verboses';
$string['settings_assessment_SelfEval_verboses_validate_error_long'] = 'Long titles: up to 4 entries, delimiter ";", maximum 20 characters per entry (4 for short form)';
$string['settings_addblock_to_newcourse'] = 'Add block to new courses';
$string['settings_addblock_to_newcourse_description'] = 'The block "Exabis competence Grid" will be added to every new course automatically. Position of inserted block depends on selected Moodle theme';
$string['settings_addblock_to_newcourse_option_no'] = 'No';
$string['settings_addblock_to_newcourse_option_yes'] = 'Yes';
$string['settings_addblock_to_newcourse_option_left'] = 'to the Left region';
$string['settings_addblock_to_newcourse_option_right'] = 'to the Right region';
$string['settings_example_autograding'] = 'automatic assessment of parent materials';
$string['settings_example_autograding_description'] = 'when all child examples have been graded, the parent material should be assessed automatically.';



// === Unit Tests ===
$string['unittest_string'] = 'result_unittest_string';
$string['de:unittest_string2'] = 'result_unittest_string2';
$string['de:unittest_string3'] = 'result_unittest_string3';
$string['de:unittest_param {$a} unittest_param'] = 'result_unittest_param {$a} result_unittest_param';
$string['de:unittest_param2 {$a->val} unittest_param2'] = 'result_unittest_param2 {$a->val} result_unittest_param2';


// === Learning agenda ===
$string['LA_MON'] = 'MON';
$string['LA_TUE'] = 'TUE';
$string['LA_WED'] = 'WED';
$string['LA_THU'] = 'THU';
$string['LA_FRI'] = 'FRI';
$string['LA_todo'] = 'What do I do?';
$string['LA_learning'] = 'What can I learn?';
$string['LA_student'] = 'S';
$string['LA_teacher'] = 'T';
$string['LA_assessment'] = 'assessment';
$string['LA_plan'] = 'working plan';
$string['LA_no_learningagenda'] = 'There is no learning agenda available for this week.';
$string['LA_no_student_selected'] = '-- no student selected --';
$string['LA_select_student'] = 'Please select a student to view his learning agenda.';
$string['LA_no_example'] = 'no example available';
$string['LA_backtoview'] = 'back to original view';
$string['LA_from_n'] = ' from ';
$string['LA_from_m'] = ' from ';
$string['LA_to'] = ' to ';
$string['LA_enddate'] = 'end date';
$string['LA_startdate'] = 'start date';


// === Help ===
$string['help_content'] = '<h1>Introduction Video</h1>
<iframe width="640" height="360" src="//www.youtube.com/embed/EL4Vb3_17EM?feature=player_embedded" frameborder="0" allowfullscreen></iframe>
';


// === Import ===
$string['importinfo'] = 'Please create your outcomes/standards at <a href="http://www.edustandards.org">www.edustandards.org</a> or visit <a href="https://eeducation.at/index.php?id=155&L=0">https://eeducation.at/index.php?id=155&L=0</a> to download an available xml file.';
$string['importwebservice'] = 'It is possible to keep the data up to date via a <a href="{$a}">Server-URL</a>.';
$string['import_max_execution_time'] = 'Important: The current Serversettings limit the Import to run up to {$a} seconds. If the import takes longer no data will be imported and the browser may display "500 Internal Server Error".';
$string['importdone'] = 'data has already been imported from xml';
$string['importpending'] = 'no data has been imported yet!';
$string['doimport'] = 'Import outcomes/standards';
$string['doimport_again'] = 'Import additional outcomes/standards';
$string['doimport_own'] = 'Import individual outcomes/standards';
$string['scheduler_import_settings'] = 'Settings for scheduler importing';
$string['delete_own'] = 'Delete individual outcomes/standards';
$string['delete_success'] = 'Individual outcomes/standards have been deleted';
$string['delete_own_confirm'] = 'Are you sure to delete the individual outcomes/standards?';
$string['importsuccess'] = 'data was successfully imported!';
$string['importsuccess_own'] = 'individual data was imported successfully!';
$string['importfail'] = 'an error has occured during import';
$string['noxmlfile'] = 'There is no data available to import. Please visit <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a> to download the required outcomes to the blocks xml directory.';
$string['oldxmlfile'] = 'You are using an outdated xml-file. Please create new outcomes/standards at <a href="http://www.edustandards.org">www.edustandards.org</a> or visit <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> to download an available xml file to the blocks xml directory.';
$string['do_demo_import'] = 'import demo data to see how Exabis Competence Grid works.';
$string['schedulerimport'] = 'Scheduler import tasks';
$string['add_new_importtask'] = 'Add new import task';
$string['importtask_title'] = 'Title';
$string['importtask_link'] = 'Link to source';
$string['importtask_disabled'] = 'Disabled';
$string['importtask_all_subjects'] = 'All Subjects';
$string['dest_course'] = 'Destiantion of imported activities';
$string['import_activities'] = 'Import activities of a template course into your course';



// === Configuration ===
$string['explainconfig'] = 'Your outcomes have already been imported. In this configuration you have to make the selection of the main standards you would like to use in this Moodle installation.';
$string['save_selection'] = 'Save selection';
$string['save_success'] = 'changes were successful';


// === Course-Configuration ===
$string['grading_scheme'] = 'grading scheme';
$string['points_limit_forcourse'] = 'Highest value for Points';
$string['uses_activities'] = 'I work with Moodle activites';
$string['show_all_descriptors'] = 'Show all outcomes in overview';
$string['useprofoundness'] = 'Use basic and extended competencies';
$string['assessment_SelfEval_useVerbose'] = 'verbose feedback options for students';
$string['selfEvalVerbose.defaultValue_long'] = 'does not apply; rather not true; rather applies; true';
$string['selfEvalVerbose.defaultValue_short'] = 'na; rnt; ra; t';
$string['selfEvalVerboseExample.defaultValue_long'] = 'unsolved; solved with help; solved independently';
$string['selfEvalVerboseExample.defaultValue_short'] = 'un; sh; si';
$string['selfEvalVerbose.1'] = 'does not apply';
$string['selfEvalVerbose.2'] = 'rather not true';
$string['selfEvalVerbose.3'] = 'rather applies';
$string['selfEvalVerbose.4'] = 'true';
$string['selfEvalVerboseExample.1'] = 'unsolved';
$string['selfEvalVerboseExample.2'] = 'solved with help';
$string['selfEvalVerboseExample.3'] = 'solved independently';
$string['usetopicgrading'] = 'Enable topic gradings';
$string['usesubjectgrading'] = 'Enable subject gradings';
$string['usenumbering'] = 'Enable automatic numbering in the competence grid';
$string['usenostudents'] = 'Use without students';
$string['profoundness_0'] = 'not reached';
$string['profoundness_1'] = 'Partially gained';
$string['profoundness_2'] = 'Fully gained';
$string['filteredtaxonomies'] = 'Examples are filtered accordingly to the following taxonomies:';
$string['show_all_taxonomies'] = 'All taxonomies';
$string['warning_use_activities'] = 'Warning: you are now working with Moodle-activites that are associated with competences. Please verify that the same outcomes are used as before.';
$string['delete_unconnected_examples'] = 'If you are deselecting topics which are associated with examples used in the weekly schedule, these examples will be removed.';


// === Badges ===
$string['mybadges'] = 'My badges';
$string['pendingbadges'] = 'Pending badges';
$string['no_badges_yet'] = 'no badges available';
$string['description_edit_badge_comps'] = 'Here you can associate the selected badge with outcomes.';
$string['to_award'] = 'To award this badge in exacomp you have to configure competencies';
$string['to_award_role'] = 'To award this badge in exacomp you have to add the "Manual issue by role" criteria';
$string['ready_to_activate'] = 'This badge is ready to be activated: ';
$string['conf_badges'] = 'configure badges';
$string['conf_comps'] = 'configure competences';


// === Examples ===
$string['example'] = 'Example';
$string['sorting'] = 'select sorting: ';
$string['subject'] = 'subject';
$string['topic'] = 'Topic';
$string['taxonomies'] = 'taxonomies';
$string['show_all_course_examples'] = 'Show examples from all courses';
$string['name_example'] = 'Name';
$string['example_add_taxonomy'] = 'Add new taxonomy';
$string['comp_based'] = 'sort by competencies';
$string['examp_based'] = 'sort by examples';
$string['cross_based'] = 'For Interdisciplinary Subjects';

// === Icons ===
$string['assigned_example'] = 'Assigned Example';
$string['task_example'] = 'Tasks';
$string['extern_task'] = 'External Task';
$string['total_example'] = 'Complete Example';


// === Example Upload ===
$string['example_upload_header'] = 'Upload my own task/example';
$string['taxonomy'] = 'Taxonomy';
$string['descriptors'] = 'Competencies';
$string['filerequired'] = 'A file must be selected.';
$string['titlenotemtpy'] = 'A name is required.';
$string['solution'] = 'Solution';
$string['hide_solution'] = 'Hide solution';
$string['show_solution'] = 'Show solution';
$string['hide_solution_disabled'] = 'The solution is already hidden for all students';
$string['submission'] = 'Submission';
$string['assignments'] = 'Assignments';
$string['files'] = 'Files';
$string['link'] = 'Link';
$string['links'] = 'Links';
$string['dataerr'] = 'At least a link or a file are required!';
$string['linkerr'] = 'The given link is not valid!';
$string['isgraded'] = 'The example is already graded and therefore a submission is not possible anymore';
$string['allow_resubmission'] = 'Allow a new submission for this example';
$string['allow_resubmission_info'] = 'The example is now allowed to be resubmited.';


// === Assign competencies ===
$string['header_edit_mode'] = 'Editing mode is turned on';
$string['comp_-1'] = 'no information';
$string['comp_0'] = 'not gained';
$string['comp_1'] = 'partly';
$string['comp_2'] = 'mostly';
$string['comp_3'] = 'completely';
$string['comp_-1_short'] = 'ni';
$string['comp_0_short'] = 'ng';
$string['comp_1_short'] = 'pg';
$string['comp_2_short'] = 'mg';
$string['comp_3_short'] = 'cg';
$string['delete_confirmation'] = 'Do you really want to delete "{$a}"?';
$string['legend_activities'] = 'Moodle activities';
$string['legend_eportfolio'] = 'ePortfolio';
$string['legend_notask'] = 'No Moodle activities/quizzes have been submitted for this outcome';
$string['legend_upload'] = 'Upload your own task/example';
$string['allniveaus'] = 'All difficulty levels';
$string['choosesubject'] = 'Choose subject: ';
$string['choosetopic'] = 'Choose topic';
$string['choosestudent'] = 'Choose student: ';
$string['choose_student'] = 'Choose students: ';
$string['choosedaterange'] = 'Pick a date range: ';
$string['cleardaterange'] = 'Clear range';
$string['seperatordaterange'] = 'to';
$string['own_additions'] = 'Curricular additions: ';
$string['delete_confirmation_descr'] = 'Do you really want to delete the competence "{$a}"?';
$string['import_source'] = 'Imported from: {$a}';
$string['local'] = 'Local';
$string['unknown_src'] = 'Unknown source';
$string['override_notice1'] = 'This entry was editied by ';
$string['override_notice2'] = ' before. Continue?';
$string['dismiss_gradingisold'] = 'Do you want to dismiss this warning?';
$string['unload_notice'] = 'Are you sure? Unsaved changes will be lost.';
$string['example_sorting_notice'] = 'Please save the changes first.';
$string['newsubmission'] = 'New Submission';
$string['value_too_large'] = 'Error: Values above {limit} are not allowed';
$string['value_too_low'] = 'Error: Values below 1.0 are not allowed';
$string['value_not_allowed'] = 'Error: Values need to be numbers between 1.0 and 6.0';
$string['competence_locked'] = 'Evaluation exists or learning material is used';
$string['save_changes_competence_evaluation'] = 'Changes were saved';
// === Example Submission ===
$string['example_submission_header'] = 'Edit example {$a}';
$string['example_submission_info'] = 'You are about to edit the example "{$a}". Your submission will be saved in Exabis ePortfolio and Teachers can view it there.';
$string['example_submission_subject'] = 'New submission';
$string['example_submission_message'] = 'Student {$a->student} handed in a new submission in {$a->course}.';
$string['submissionmissing'] = 'Es müssen zumindest ein Link oder eine Datei abgegeben werden';
$string['associated_activities'] = 'Associated Moodle Activities:';
$string['usernosubmission'] = '{$a} has not yet submitted any Moodle activities or quizzes associated with this outcome';
$string['grading'] = 'Grading';
$string['teacher_tipp'] = 'tip';
$string['teacher_tipp_1'] = 'This competence has been associated with ';
$string['teacher_tipp_2'] = ' Moodle activities and has been reached with ';
$string['teacher_tipp_3'] = ' outcomes.';
$string['print'] = 'Print';
$string['eportitems'] = 'Submitted ePortfolio artifacts:';
$string['eportitem_shared'] = ' (shared)';
$string['eportitem_notshared'] = ' (not shared)';
$string['teachershortcut'] = 'T';
$string['studentshortcut'] = 'S';
$string['overview'] = 'This is an overview of all students and the course competencies.';
$string['showevaluation'] = 'To show self-assessment click <a href="{$a}">here</a>';
$string['hideevaluation'] = 'To hide self-assessment click <a href="{$a}">here</a>';
$string['showevaluation_student'] = 'To show trainer-assessment click <a href="{$a}">here</a>';
$string['hideevaluation_student'] = 'To hide trainer-assessment click <a href="{$a}">here</a>';
$string['columnselect'] = 'Column selection';
$string['allstudents'] = 'All students';
$string['nostudents'] = 'No students';
$string['statistic'] = 'Overview';
$string['niveau'] = 'Difficulty Level';
$string['niveau_short'] = 'Level';
$string['competence_grid_niveau'] = 'difficulty Level';
$string['competence_grid_additionalinfo'] = 'grade';
$string['descriptor'] = 'competence';
$string['descriptor_child'] = 'child competence';
$string['assigndone'] = 'task done: ';
$string['descriptor_categories'] = 'Categories: ';
$string['descriptor_add_category'] = 'Add new category ';

// === metadata ===
$string['subject_singular'] = 'Field of competence';
$string['comp_field_idea'] = 'Skill';
$string['comp'] = 'Topic';
$string['progress'] = 'Progress';
$string['instruction'] = 'Instruction';
$string['instruction_content'] = 'This is an overview for learning resources that are associated with 
				standards and ticking off competencies for students. Students can
				assess their competencies. Moodle activities that were turned in by
				students are displayed with a red icon. ePortfolio-artifacts of students 
				are displayed in blue icons.';


// === Activities ===
$string['explaineditactivities_subjects'] = '';
$string['niveau_filter'] = 'Filter difficulty levels';
$string['module_filter'] = 'filter activities';
$string['apply_filter'] = 'apply filter';
$string['no_topics_selected'] = 'configuration of Exabis Competence Grid is not completed yet. please chose a topic that you would like to associate Moodle activities with';
$string['no_activities_selected'] = 'please associate Moodle activities with competences';
$string['no_activities_selected_student'] = 'There is no data available yet';
$string['no_course_activities'] = 'No Moodle activities found in this course - click here to create some.';
$string['all_modules'] = 'all activities';
$string['tick_some'] = 'Please make a selection!';


// === Competence Grid ===
$string['infolink'] = 'Additional information: ';
$string['textalign'] = 'Switch text align';
$string['selfevaluation'] = 'self assessment';
$string['selfevaluation_short'] = 'SA';
$string['teacherevaluation'] = 'trainer assessment';
$string['competencegrid_nodata'] = 'In case the competence grid is empty the outcomes for the chosen subject were not assigned to a level in the datafile. This can be fixed by associating outcomes with levels at www.edustandards.org and re-importing the xml-file.';
$string['statistic_type_descriptor'] = 'Change to descriptor statistics';
$string['statistic_type_example'] = 'Change to example statistics';
$string['reports'] = 'Type of report';
$string['newer_grading_tooltip'] = 'Grading may not be up to date. </br> A childdescriptor has been graded.';
$string['create_new_topic'] = 'New topic';
$string['really_delete'] = 'Really to delete?';
$string['add_niveau'] = 'Add niveau';
$string['delete_niveau'] = 'Delete niveau';
$string['add_new_taxonomie'] = 'Add a new taxonomie';
$string['taxonomy_was_deleted'] = 'Taxonomy was deleted';
$string['move_up'] = 'Move up';
$string['move_down'] = 'Move down';
$string['also_taxonomies_from_import'] = 'There are also taxonomies from import';

// === Competence Profile ===
$string['name'] = 'Name';
$string['city'] = 'City';
$string['total'] = 'total';
$string['select_student'] = 'Please select a student first';
$string['my_comps'] = 'My Competencies';
$string['my_badges'] = 'My Badges';
$string['innersection1'] = 'Grid view';
$string['innersection2'] = 'Statistics';
$string['innersection3'] = 'Comparison: Teacher-Student';
$string['childcompetencies_compProfile'] = 'Child competencies';
$string['materials_compProfile'] = 'Materials';

// === Competence Profile Settings ===
$string['profile_settings_choose_courses'] = 'Using Exabis Competence Grid trainers assess your competencies in various subjects. You can select which course to include in the competence profile.';
$string['specificcontent'] = 'site-specific topics';
$string['topic_3dchart'] = '3D Chart';
$string['topic_3dchart_empty'] = 'No gradings available';
// === Profoundness ===
$string['profoundness_description'] = 'Description';
$string['profoundness_basic'] = 'Basic competence';
$string['profoundness_extended'] = 'Extended competence';
$string['profoundness_mainly'] = 'Mainly achieved';
$string['profoundness_entirely'] = 'Entirely achieved';


// === External trainer & eLove ===
$string['block_exacomp_external_trainer_assign_head'] = 'Allow assigning of external trainers for students.';
$string['block_exacomp_external_trainer_assign_body'] = 'This is required for using the elove app.';
$string['block_exacomp_elove_student_self_assessment_head'] = 'Allow self-assessment for students in the elove app';
$string['block_exacomp_elove_student_self_assessment_body'] = '';
$string['block_exacomp_external_trainer_assign'] = 'Assign external trainers';
$string['block_exacomp_external_trainer'] = 'Trainer';
$string['block_exacomp_external_trainer_student'] = 'Student';
$string['block_exacomp_external_trainer_allstudents'] = 'All Students';


// === Crosssubjects ===
$string['add_drafts_to_course'] = 'Add drafts to course';
$string['crosssubject'] = 'Interdisciplinary Subject';
$string['help_crosssubject'] = 'The compilation of a subject is done for the whole Moodle installation (school) using the tab learning path. Here you can selectively deactivate course-specific competences, sub-competences and materials. Individual learning material can also be added. This is then automatically added to the learning paths.';
$string['description'] = 'Description';
$string['numb'] = 'Number';
$string['no_student'] = '-- no participant selected --';
$string['no_student_edit'] = 'edit mode - no participant';
$string['save_as_draft'] = 'Save interdisciplinary subject as draft';
$string['comps_and_material'] = 'outcomes and exercises';
$string['no_crosssubjs'] = 'No interdisciplinary subjects available.';
$string['delete_drafts'] = 'Delete selected drafts';
$string['share_crosssub'] = 'Share interdisciplinary subject with participants';
$string['share_crosssub_with_students'] = 'Share interdisciplinary subject "{$a}" with the following participants: ';
$string['share_crosssub_with_all'] = 'Share interdisciplinary subject "{$a}" with all participants: ';
$string['new_crosssub'] = 'Create new interdisciplinary subject';
$string['add_crosssub'] = 'Create interdisciplinary subject';
$string['nocrosssubsub'] = 'General Interdisciplinary Subjects';
$string['delete_crosssub'] = 'Delete interdisciplinary subject';
$string['confirm_delete'] = 'Do you really want to delete this interdisciplinary subject?';
$string['no_students_crosssub'] = 'No students are assigend to this interdisciplinary subject.';
$string['use_available_crosssub'] = 'Use draft for creating new interdisciplinary subject:';
$string['save_crosssub'] = 'Save changes';
$string['add_content_to_crosssub'] = 'The interdisciplinary subject is still empty.';
$string['add_descriptors_to_crosssub'] = 'Add competence to interdisciplinary subject';
$string['manage_crosssubs'] = 'Back to overview';
$string['show_course_crosssubs'] = 'Show used interdisciplinary subjects';
$string['existing_crosssub'] = 'existing cross subjects in this course';
$string['create_new_crosssub'] = 'Create new interdisciplinary subject';
$string['share_crosssub_for_further_use'] = 'Share the interdisciplinary subject with students.';
$string['available_crosssubjects'] = 'Available Interdisciplinary Subjects';
$string['crosssubject_drafts'] = 'Interdisciplinary Subject Drafts';
$string['de:Freigegebene Kursthemen'] = 'Published Interdisciplinary Subjects';
$string['de:Freigabe bearbeiten'] = 'Change Sharing';
$string['de:Kopie als Vorlage speichern'] = 'Create Copy as Draft';
$string['de:Vorlage verwenden'] = 'Use Draft';
$string['crosssubject_files'] = 'crosssubject files';



// === Associations ===
$string['competence_associations'] = 'Associations';
$string['competence_associations_explaination'] = 'The material {$a} is associated wih the following standards:';


// === Weeky schedule ===
$string['weekly_schedule'] = 'Weekly schedule';
$string['weekly_schedule_added'] = 'Example added to the weekly schedule';
$string['weekly_schedule_already_exists'] = 'Example is already in the weekly schedule';
$string['select_student_weekly_schedule'] = 'Please select a student to view his/her weekly schedule.';
$string['example_pool'] = 'Example pool';
$string['example_trash'] = 'Trash bin';
$string['choosecourse'] = 'Select course: ';
$string['choosecoursetemplate'] = 'Select template course: ';
$string['weekly_schedule_added_all'] = 'Example added to the weekly schedule of all students.';
$string['weekly_schedule_already_existing_for_one'] = 'Example has already been added to at least one student\'s weekly schedule.';
$string['weekly_schedule_link_to_grid'] = 'For adding examples to the schedule, please use the overview';
$string['pre_planning_storage'] = 'Pre-planning storage';
$string['pre_planning_storage_added'] = 'Example added to the pre-planning storage.';
$string['pre_planning_storage_already_contains'] = 'Example is already in pre-planning storage.';
$string['save_pre_planning_selection'] = 'Add selected examples to weekly schedule of selected students';
$string['empty_pre_planning_storage'] = 'Empty pre-planning storage';
$string['noschedules_pre_planning_storage'] = 'Pre-planning storage has been emptied, use the competence grid to put new examples in the pre-planning storage.';
$string['empty_trash'] = 'Empty trash bin';
$string['empty_pre_planning_confirm'] = 'Examples added from all teachers are deleted, are you sure you want to do this?';
$string['to_weekly_schedule'] = 'To weekly schedule';
$string['blocking_event'] = 'Create blocking event';
$string['blocking_event_title'] = 'title';
$string['blocking_event_create'] = 'Add to pre-planning storage';
$string['weekly_schedule_disabled'] = 'Hidden example can not be added to weekly schedule';
$string['pre_planning_storage_disabled'] = 'Hidden example can not be added to pre-planning storage.';
$string['add_example_for_all_students_to_schedule'] = 'Attention: Here you can add examples to the schedules of all students. This requires extra confirmation.';
$string['add_example_for_group_to_schedule'] = 'Attention: Here you can add examples to the schedules of all students of the selected group. This requires extra confirmation.';
$string['add_example_for_all_students_to_schedule_confirmation'] = 'You are about to add the examples to the schedules of all students, do you want to continue?';
$string['add_example_for_group_to_schedule_confirmation'] = 'You are about to add the examples to the schedules of all students of this group, do you want to continue?';
$string['participating_student'] = 'student';
$string['n1.unit'] = '1. unit';
$string['n2.unit'] = '2. unit';
$string['n3.unit'] = '3. unit';
$string['n4.unit'] = '4. unit';
$string['n5.unit'] = '5. unit';
$string['n6.unit'] = '6. unit';
$string['n7.unit'] = '7. unit';
$string['n8.unit'] = '8. unit';
$string['n9.unit'] = '9. unit';
$string['n10.unit'] = '10. unit';

// === Notifications ===
$string['notification_submission_subject'] = '{$a->site}: {$a->student} submitted a solution for {$a->example}';
$string['notification_submission_body'] = 'Dear Mr./Ms. {$a->receiver}, </br></br> {$a->student} submitted {$a->example} on {$a->date} at {$a->time}. The submission can be seen in ePortfolio: <a href="{$viewurl}">{$a->example}</a> </br></br> This message has been generated form moodle site {$a->site}.';
$string['notification_submission_context'] = 'Submission';
$string['notification_grading_subject'] = '{$a->site}: New grading in course {$a->course}';
$string['notification_grading_body'] = 'Dear {$a->receiver}, </br></br>You have got new gradings in {$a->course} from {$a->teacher}.</br></br> This message has been generated form moodle site {$a->site}.';
$string['notification_grading_context'] = 'Grading';
$string['notification_self_assessment_subject'] = '{$a->site}: New self assessments in {$a->course}';
$string['notification_self_assessment_body'] = 'Dear Mr./Ms. {$a->receiver}, </br></br> {$a->student} has new self assessments in {$a->course}.</br></br> This message has been generated form moodle site {$a->site}.';
$string['notification_self_assessment_context'] = 'self assessment';
$string['notification_example_comment_subject'] = '{$a->site}: New comment for example {$a->example}';
$string['notification_example_comment_body'] = 'Dear {$a->receiver}, </br></br>{$a->teacher} commented in {$a->course} the example {$a->example}.</br></br> This message has been generated form moodle site {$a->site}.';
$string['notification_example_comment_context'] = 'Comment';
$string['notification_weekly_schedule_subject'] = '{$a->site}: New example on the schedule';
$string['notification_weekly_schedule_body'] = 'Dear {$a->receiver}, </br></br>{$a->teacher} added an example in {$a->course} to your weekly schedule.</br></br> This message has been generated form moodle site {$a->site}.';
$string['notification_weekly_schedule_context'] = 'Weekly schedule';
$string['inwork'] = '{$a->inWork}/{$a->total} in work';
$string['block_exacomp_notifications_head'] = 'Notifications and Messages';
$string['block_exacomp_notifications_body'] = 'Users will get notified after relevant actions.';


// === Logging ===
$string['block_exacomp_logging_head'] = 'Activate logging';
$string['block_exacomp_logging_body'] = 'Relevant actions will get logged.';
$string['eventscompetenceassigned'] = 'Competence assigned';
$string['eventsexamplesubmitted'] = 'Example submitted';
$string['eventsexamplegraded'] = 'Example graded';
$string['eventsexamplecommented'] = 'Example commented';
$string['eventsexampleadded'] = 'Example added to weekly schedule';
$string['eventsimportcompleted'] = 'Import completed';
$string['eventscrosssubjectadded'] = 'Interdisciplinary subject added';

// === Message ===
$string['messagetocourse'] = 'Send message to all students';
$string['messageprovider:submission'] = 'Notify teacher that a student has submitted an item';
$string['messageprovider:grading'] = 'Notify Student that a teacher graded competencies';
$string['messageprovider:self_assessment'] = 'Student assessed some own competencies';
$string['messageprovider:weekly_schedule'] = 'Teacher adds new example to weekly schedule';
$string['messageprovider:comment'] = 'Teacher comments an example';
$string['description_example'] = 'Description';
$string['submit_example'] = 'Submit';
// === Webservice Status ===
$string['enable_rest'] = 'REST Protocol not enabled';
$string['access_roles'] = 'Roles with webservice access';
$string['no_permission'] = 'Permissions not set';
$string['description_createtoken'] = 'Grant additional permission to the role "authenticated user" at: Site administration/Users/Permissions/Define roles
4.1 Select Authenticated User
4.2 Click on "Edit"
4.3 Filter for createtoken
4.4 Allow moodle/webservice:createtoken';
$string['exacomp_not_found'] = 'Exacompservice not found';
$string['exaport_not_found'] = 'Exaportservice not found';
$string['no_external_trainer'] = 'No external trainers assigned';
$string['periodselect'] = 'Select Period';
$string['teacher'] = 'Teacher';
$string['student'] = 'Student';
$string['timeline_available'] = 'Available';
// === Group Reports ===
$string['result'] = 'result';
$string['evaluationdate'] = 'evaluation Date';
$string['output_current_assessments'] = 'output of current assessments';
$string['student_assessment'] = 'students\' assessment';
$string['teacher_assessment'] = 'teachers\' assessment';
$string['exa_evaluation'] = 'learning material';
$string['difficulty_group_report'] = 'difficulty level';
$string['no_entries_found'] = 'no entries found';
$string['assessment_date'] = 'assessment date';
$string['number_of_found_students'] = 'number of found students';
$string['display_settings'] = 'display settings';
$string['create_report'] = 'generate report';
$string['students_competences'] = 'students\' competences';
$string['number_of_students'] = 'number of students';
$string['no_specification'] = 'not specified';
$string['period'] = 'time interval';
$string['from'] = 'from';
$string['to'] = 'to';
$string['report_type'] = 'type of report';
$string['report_subject'] = 'educational standard';
$string['report_learniningmaterial'] = 'learning material';
$string['report_competencefield'] = 'competence field';
$string['all_students'] = 'all students';
$string['export_all_standards'] = 'Export all competence grids of this Moodle installation';
$string['export_selective'] = 'Select competence grids for export';
$string['select_all'] = 'select all';
$string['deselect_all'] = 'deselect all';
$string['new'] = 'new';
$string['import_used_preselected_from_previous'] = 'is used preselected values from your previous importing from this source';
$string['import_from_related_komet'] = 'Import/update grids from related KOMET';
$string['import_activate_scheduled_tasks'] = 'Activate these tasks';

// === API ====
$string['yes_no_No'] = 'No';
$string['yes_no_Yes'] = 'Yes';
$string['grade_Verygood'] = 'Very good';
$string['grade_good'] = 'Good,';
$string['grade_Satisfactory'] = 'Satisfactory';
$string['grade_Sufficient'] = 'Sufficient';
$string['grade_Deficient'] = 'Deficient';
$string['grade_Insufficient'] = 'Insufficient';
$string['import_category_selectgrids_needed'] = 'Select subjects for importing:';
$string['import_category_mapping_needed'] = 'Grading scheme from XML is different with exacomp scheme. Please configure right correlations and try to import again:';
$string['import_category_mapping_column_xml'] = 'XML title';
$string['import_category_mapping_column_exacomp'] = 'Exacomp difflevel title';
$string['import_category_mapping_column_level'] = 'Level';
$string['import_category_mapping_column_level_descriptor'] = 'Competence / Child competence';
$string['import_category_mapping_column_level_example'] = 'Material';
$string['save'] = 'Save';
$string['add_competence_insert_learning_progress'] = 'To insert a new competence, you must first select or add a difficulty level!';
$string['delete_level_from_another_source'] = 'Content from another source. These can only be deleted by the admin.';
$string['delete_level_has_children_from_another_source'] = 'Has children from another source!';
$string['module_used_availabilitycondition_competences'] = 'Grant related exabis competencies when condition is met';
