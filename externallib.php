<?php
require_once("$CFG->libdir/externallib.php");
require_once $CFG->dirroot . '/mod/assign/locallib.php';
require_once $CFG->dirroot . '/mod/assign/submission/file/locallib.php';
require_once $CFG->dirroot . '/lib/filelib.php';
require_once dirname(__FILE__)."/inc.php";

// DB COMPETENCE TYPE CONSTANTS
define('TYPE_DESCRIPTOR', 0);
define('TYPE_TOPIC', 1);
class block_exacomp_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_courses_parameters() {
        return new external_function_parameters(
                array('userid' => new external_value(PARAM_INT, 'id of user'))
        );
    }

    /**
     * get courses
     * @return array of user courses
     */
    public static function get_courses($userid) {
        global $CFG,$DB, $USER;
        require_once("$CFG->dirroot/lib/enrollib.php");

        $params = self::validate_parameters(self::get_courses_parameters(), array('userid'=>$userid));

        if($userid == 0)
            $userid = $USER->id;

        $mycourses = enrol_get_users_courses($userid);
        //$mycourses = enrol_get_my_courses();
        $courses = array();

        foreach($mycourses as $mycourse) {
            $context = context_course::instance($mycourse->id);
            //$context = get_context_instance(CONTEXT_COURSE, $mycourse->id);
            if($DB->record_exists("block_instances", array("blockname" => "exacomp", "parentcontextid" => $context->id))) {
                $course = array("courseid" => $mycourse->id,"fullname"=>$mycourse->fullname,"shortname"=>$mycourse->shortname);
                $courses[] = $course;
            }
        }

        return $courses;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_courses_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'courseid' => new external_value(PARAM_INT, 'id of course'),
                                'fullname' => new external_value(PARAM_TEXT, 'fullname of course'),
                                'shortname' => new external_value(PARAM_RAW, 'shortname of course'),
                        )
                )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_subjects_parameters() {
        return new external_function_parameters(
                array('courseid' => new external_value(PARAM_INT, 'id of course'))
        );

    }

    /**
     * Get subjects
     * @param int courseid
     * @return array of course subjects
     */
    public static function get_subjects($courseid) {
        global $CFG,$DB;

        if (empty($courseid)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }
        $params = self::validate_parameters(self::get_subjects_parameters(), array('courseid'=>$courseid));

        $subjects = $DB->get_records_sql('
                SELECT s.id as subjectid, s.title
                FROM {block_exacompschooltypes} s
                JOIN {block_exacompmdltype_mm} m ON m.stid = s.id AND m.courseid = ?
                GROUP BY s.id
                ORDER BY s.title
                ', array($courseid));

        return $subjects;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_subjects_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'subjectid' => new external_value(PARAM_INT, 'id of subject'),
                                'title' => new external_value(PARAM_TEXT, 'title of subject')
                        )
                )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_topics_parameters() {
        return new external_function_parameters(
                array('subjectid' => new external_value(PARAM_INT, 'id of subject'),
                        'courseid' => new external_value(PARAM_INT, 'id of course'))
        );
    }

    /**
     * Get subjects
     * @param int courseid
     * @return array of course subjects
     */
    public static function get_topics($subjectid, $courseid) {
        global $CFG,$DB;

        if (empty($subjectid) || empty($courseid)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::get_topics_parameters(), array('subjectid'=>$subjectid,'courseid'=>$courseid));

        /*$returnval = array();
         $returnval[92] = new stdClass();
        $returnval[92]->title = "title";
        $returnval[92]->topicid=12;
        return $returnval;*/

        $array = $DB->get_records_sql('
                SELECT s.id as topicid, s.title
                FROM {block_exacompsubjects} s
                JOIN {block_exacomptopics} t ON t.subjid = s.id
                JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id AND ct.courseid = ?
                '.(block_exacomp_get_settings_by_course($courseid)->show_all_descriptors ? '' : '
                        -- only show active ones
                        JOIN {block_exacompdescrtopic_mm} topmm ON topmm.topicid=t.id
                        JOIN {block_exacompdescriptors} d ON topmm.descrid=d.id
                        JOIN {block_exacompcompactiv_mm} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=ca.compid AND ca.comptype = '.TYPE_TOPIC.')
                        JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
                        ').'
                WHERE s.stid = ?
                GROUP BY s.id
                ORDER BY s.title
                ', array($courseid,$subjectid));
        	
        return $array;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_topics_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'topicid' => new external_value(PARAM_INT, 'id of topic'),
                                'title' => new external_value(PARAM_TEXT, 'title of topic')
                        )
                )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_subtopics_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'id of course'),
                        'topicid' => new external_value(PARAM_INT, 'id of topic')
                )
        );
    }

    /**
     * Get subjects
     * @param int courseid
     * @param int topicid
     * @return array of course subjects
     */
    public static function get_subtopics($courseid, $topicid) {
        global $CFG,$DB,$USER;

        if (empty($topicid) || empty($courseid)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::get_subtopics_parameters(), array('courseid'=>$courseid,'topicid'=>$topicid));

        $cats = $DB->get_records_menu('block_exacompcategories', array("lvl" => 4),"id,title","id,title");

        $competencies = array(
                "studentcomps"=>$DB->get_records('block_exacompcompuser',array("role"=>0,"courseid"=>$courseid,"userid"=>$USER->id, "comptype"=>TYPE_TOPIC),"","compid,userid,reviewerid,value"),
                "teachercomps"=>$DB->get_records('block_exacompcompuser',array("role"=>1,"courseid"=>$courseid,"userid"=>$USER->id, "comptype"=>TYPE_TOPIC),"","compid,userid,reviewerid,value"));

        $subtopics = $DB->get_records('block_exacomptopics',array('subjid' => $topicid),'catid','id as subtopicid, title, catid');

        $subtopics = $DB->get_records_sql('
                SELECT t.id as subtopicid, t.title, t.catid
                FROM {block_exacomptopics} t
                JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id AND t.subjid = ? AND ct.courseid = ?
                '.(block_exacomp_get_settings_by_course($courseid)->show_all_descriptors ? '' : '
                        -- only show active ones
                        JOIN {block_exacompdescrtopic_mm} topmm ON topmm.topicid=t.id
                        JOIN {block_exacompdescriptors} d ON topmm.descrid=d.id
                        JOIN {block_exacompcompactiv_mm} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=ca.compid AND ca.comptype = '.TYPE_TOPIC.')
                        JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
                        ').'
                GROUP BY t.id
                ORDER BY t.catid, t.title
                ', array($topicid, $courseid));

        foreach($subtopics as $subtopic) {
            $subtopic->studentcomp = (array_key_exists($subtopic->subtopicid, $competencies['studentcomps'])) ? true : false;
            $subtopic->teachercomp = (array_key_exists($subtopic->subtopicid, $competencies['teachercomps'])) ? true : false;
            $subtopic->catid = $cats[$subtopic->catid];
        }


        return $subtopics;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_subtopics_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'subtopicid' => new external_value(PARAM_INT, 'id of subtopic'),
                                'title' => new external_value(PARAM_TEXT, 'title of subtopic'),
                                'catid' => new external_value(PARAM_TEXT, 'category of subtopic'),
                                'studentcomp' => new external_value(PARAM_BOOL, 'student self evaluation'),
                                'teachercomp' => new external_value(PARAM_BOOL, 'teacher evaluation'),

                        )
                )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function set_subtopic_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'id of course'),
                        'subtopicid' => new external_value(PARAM_INT, 'id of subtopic'),
                        'value' => new external_value(PARAM_INT, 'evaluation value')
                )
        );
    }

    /**
     * Set subtopic student evaluation
     * @param int courseid
     * @param int subtopicid
     * @return status
     */
    public static function set_subtopic($courseid, $subtopicid, $value) {
        global $DB,$USER;

        if (empty($courseid) || empty($subtopicid) || !isset($value)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::set_subtopic_parameters(), array('subtopicid'=>$subtopicid,'value'=>$value,'courseid'=>$courseid));

        $transaction = $DB->start_delegated_transaction(); //If an exception is thrown in the below code, all DB queries in this code will be rollback.

        $DB->delete_records('block_exacompcompuser',array("userid"=>$USER->id,"role"=>0,"compid"=>$subtopicid,"courseid"=>$courseid, 'comptype'=>TYPE_TOPIC));
        if ($value > 0) {
            $DB->insert_record('block_exacompcompuser',array("userid"=>$USER->id,"role"=>0,"compid"=>$subtopicid,"courseid"=>$courseid,'comptype'=>TYPE_TOPIC, "reviewerid"=>$USER->id,"value"=>$value));
        }

        $transaction->allow_commit();

        return array("success" => true);

    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function set_subtopic_returns() {
        return new external_single_structure(
                array(
                        'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
                )
        );
    }

    /* Returns description of method parameters
     * @return external_function_parameters
    */
    public static function get_competencies_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'id of course'),
                        'subtopicid' => new external_value(PARAM_INT, 'id of subtopic')
                )
        );
    }

    /**
     * Get competencies
     * @param int courseid
     * @param int subtopicid
     * @return external_multiple_structure
     */
    public static function get_competencies($courseid, $subtopicid) {
        global $DB,$USER;

        if (empty($courseid) || empty($subtopicid) ) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::get_competencies_parameters(), array('subtopicid'=>$subtopicid,'courseid'=>$courseid));

        $courseSettings = block_exacomp_get_settings_by_course($courseid);

        $descriptors = $DB->get_records_sql('
                SELECT d.id as descriptorid, d.title, desctopmm.topicid AS topicid
                FROM {block_exacompdescriptors} d
                JOIN {block_exacompdescrtopic_mm} desctopmm ON desctopmm.descrid=d.id AND desctopmm.topicid = ?
                '.(block_exacomp_get_settings_by_course($courseid)->show_all_descriptors ? '' : '
                        -- only show active ones
                        JOIN {block_exacompcompactiv_mm} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.')
                        JOIN {course_modules} a ON ca.activityid=a.id AND a.course=?
                        ').'
                GROUP BY descriptorid
                ORDER BY d.sorting
                ', array($subtopicid, $courseid));

        $studentEvaluation = $DB->get_records('block_exacompcompuser',array("courseid"=>$courseid,"userid"=>$USER->id,"role"=>0, "comptype"=>TYPE_DESCRIPTOR),'','compid');
        $teacherEvaluation = $DB->get_records('block_exacompcompuser',array("courseid"=>$courseid,"userid"=>$USER->id,"role"=>1, "comptype"=>TYPE_DESCRIPTOR),'','compid');

        foreach($descriptors as $descriptor) {
            $descriptor->studentcomp = (array_key_exists($descriptor->descriptorid, $studentEvaluation)) ? true : false;
            $descriptor->teachercomp = (array_key_exists($descriptor->descriptorid, $teacherEvaluation)) ? true : false;

            $descriptor->isexpandable = false;
            // if there are examples for a descriptor
            if($DB->record_exists('block_exacompdescrexamp_mm', array("descrid"=>$descriptor->descriptorid)))
                $descriptor->isexpandable = true;
            else {
                $activities = $DB->get_records('block_exacompcompactiv_mm', array("compid"=>$descriptor->descriptorid, "comptype"=>TYPE_DESCRIPTOR));

                foreach($activities as $activity) {
                    $module = $DB->get_record('course_modules', array('id'=>$activity->activityid));
                    //only assignments
                    if($courseSettings->uses_activities && $module && $module->module == 1) {
                        $descriptor->isexpandable = true;
                        continue;
                    } else if ($activity->eportfolioitem == 1 ){
                        $descriptor->isexpandable = true;
                    }
                }
            }
        }
        return $descriptors;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_competencies_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
                                'title' => new external_value(PARAM_TEXT, 'title of descriptor'),
                                'isexpandable' => new external_value(PARAM_BOOL, 'is expandable if there are associated examples or eportfolio items'),
                                'studentcomp' => new external_value(PARAM_BOOL, 'student self evaluation'),
                                'teachercomp' => new external_value(PARAM_BOOL, 'teacher evaluation')
                        )
                )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function set_competence_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'id of course'),
                        'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
                        'value' => new external_value(PARAM_INT, 'evaluation value')
                )
        );
    }

    /**
     * Set student evaluation
     * @param int courseid
     * @param int descriptorid
     * @param int value
     * @return status
     */
    public static function set_competence($courseid, $descriptorid, $value) {
        global $DB,$USER;

        if (empty($courseid) || empty($descriptorid) || !isset($value)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::set_competence_parameters(), array('courseid'=>$courseid,'descriptorid'=>$descriptorid,'value'=>$value));

        $transaction = $DB->start_delegated_transaction(); //If an exception is thrown in the below code, all DB queries in this code will be rollback.

        $DB->delete_records('block_exacompcompuser',array("userid"=>$USER->id,"role"=>0,"compid"=>$descriptorid,"courseid"=>$courseid, "comptype"=>TYPE_DESCRIPTOR));
        if ($value > 0) {
            $DB->insert_record('block_exacompcompuser',array("userid"=>$USER->id,"role"=>0,"compid"=>$descriptorid,"courseid"=>$courseid, "comptype"=>TYPE_DESCRIPTOR, "reviewerid"=>$USER->id,"value"=>$value));
        }

        $transaction->allow_commit();

        return array("success" => true);

    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function set_competence_returns() {
        return new external_single_structure(
                array(
                        'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
                )
        );
    }

    /* Returns description of method parameters
     * @return external_function_parameters
    */
    public static function get_associated_content_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'id of course'),
                        'descriptorid' => new external_value(PARAM_INT, 'id of descriptor')
                )
        );
    }

    /**
     * Get content
     * @param int courseid
     * @param int subtopicid
     * @return external_multiple_structure
     */
    public static function get_associated_content($courseid, $descriptorid) {
        global $DB,$USER;

        if (empty($courseid) || empty($descriptorid) ) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::get_associated_content_parameters(), array('descriptorid'=>$descriptorid,'courseid'=>$courseid));

        $courseSettings = block_exacomp_get_settings_by_course($courseid);
        $results = array();

        $examples = $DB->get_records_sql('
                SELECT e.id, e.title, e.task, e.externalurl
                FROM {block_exacompexamples} e
                JOIN {block_exacompdescrexamp_mm} mm ON mm.descrid=? AND mm.exampid = e.id
                ', array($descriptorid));

        foreach($examples as $example) {
            $result = new stdClass();
            $result->type = "example";
            $result->title = $example->title;
            $result->link = ($example->task) ? $example->task : $example->externalurl;
            $result->contentid = $example->id;

            $results[] = $result;
        }

        $activities = $DB->get_records('block_exacompcompactiv_mm', array("compid"=>$descriptorid, "comptype"=>TYPE_DESCRIPTOR));

        foreach($activities as $activity) {
            $module = $DB->get_record('course_modules', array('id'=>$activity->activityid));
            if($courseSettings->uses_activities && $module->module==1) {

                $instance = $DB->get_field('course_modules', 'instance', array("id"=>$activity->activityid,"course"=>$courseid));
                $assign = $DB->get_record('assign',array('id'=>$instance));

                if($assign) {
                    $result = new stdClass();
                    $result->type = "assign";
                    $result->title = $assign->name;
                    $result->link = "";
                    $result->contentid = $assign->id;

                    $results[] = $result;
                }
            } else if ($activity->eportfolioitem == 1 ){
                $result = new stdClass();
                $result->type = "exaport";
                $result->title = $DB->get_field('block_exaportitem', 'name', array('id'=>$activity->activityid));
                $result->link = "";
                $result->contentid = $activity->activityid;
                	
                $results[] = $result;
            }
        }
        return $results;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_associated_content_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'type' => new external_value(PARAM_TEXT, 'type of content (exaport, assign, example)'),
                                'title' => new external_value(PARAM_TEXT, 'title of content'),
                                'link' => new external_value(PARAM_URL, 'link to external example'),
                                'contentid' => new external_value(PARAM_INT, 'id of content')
                        )
                )
        );
    }

    /** Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_assign_information_parameters() {
        return new external_function_parameters(
                array(
                        'assignid' => new external_value(PARAM_INT, 'id of assign')
                )
        );
    }

    /**
     * Get assign information
     * @param int assignid
     * @return external_multiple_structure
     */
    public static function get_assign_information($assignid) {
        global $DB,$USER;

        if (empty($assignid)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::get_assign_information_parameters(), array('assignid'=>$assignid));

        $cm = get_coursemodule_from_instance('assign', $assignid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

        $context = context_module::instance($cm->id);

        $assign = new assign($context, $cm, $course);
        $instance = $assign->get_instance();

        $submission = $assign->get_user_submission($USER->id,true);
        $onlinetextenabled = false;
        $onlinetext = "";

        $conditions = $DB->sql_compare_text("plugin")." = 'onlinetext' AND ".$DB->sql_compare_text("name"). " = 'enabled' AND value=1 AND assignment =".$assignid;
        if($DB->get_record_select("assign_plugin_config",$conditions)) {
            $onlinetextenabled = true;
            $onlinetext = $DB->get_field("assignsubmission_onlinetext", "onlinetext", array("assignment"=>$assignid,"submission"=>$submission->id));
        }

        $fileenabled = false;
        $file = "";
        $filename = "";

        $conditions = $DB->sql_compare_text("plugin")." = 'file' AND ".$DB->sql_compare_text("name"). " = 'enabled' AND value=1 AND assignment =".$assignid;
        if($DB->get_record_select("assign_plugin_config",$conditions)) {
            $fileenabled = true;

            $filesubmission = new assign_submission_file($assign, "submission");
            $files = $filesubmission->get_files($submission, $USER);

            if($files) {
                $file = reset($files);
                $filename = $file->get_filename();
                $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename())->out();
                $url = str_replace("pluginfile.php", "webservice/pluginfile.php", $url);
            }
        }

        return array(
                "title" => $instance->name,
                "intro" => strip_tags($instance->intro),
                "submissionstatus" => $submission->status,
                "deadline" => $instance->duedate,
                "onlinetextenabled" => $onlinetextenabled,
                "onlinetext" => $onlinetext,
                "fileenabled" => $fileenabled,
                "file" => $url,
                "filename" => $filename,
                "submissionenabled" => $assign->submissions_open(),
                "grade" => $assign->get_user_grade()->grade);
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_assign_information_returns() {
        return new external_single_structure(
                array(
                        'title' => new external_value(PARAM_TEXT, 'title of assign'),
                        'intro' => new external_value(PARAM_RAW, 'introduction of assign'),
                        'submissionstatus' => new external_value(PARAM_TEXT, 'submissionstatus'),
                        'deadline' => new external_value(PARAM_INT, 'submission deadline'),
                        'onlinetextenabled' => new external_value(PARAM_BOOL, 'true if text submission enabled'),
                        'onlinetext' => new external_value(PARAM_TEXT, 'online text submission'),
                        'fileenabled' => new external_value(PARAM_BOOL, 'true if file submission enabled'),
                        'file' => new external_value(PARAM_URL, 'link to file'),
                        'filename' => new external_value(PARAM_TEXT, 'filename'),
                        'submissionenabled' => new external_value(PARAM_BOOL, 'tells if a submission is allowed'),
                        'grade' => new external_value(PARAM_FLOAT, 'grade')
                )
        );
    }

    /* Returns description of method parameters
     * @return external_function_parameters
    */
    public static function update_assign_submission_parameters() {
        return new external_function_parameters(
                array(
                        'assignid' => new external_value(PARAM_INT, 'assignid'),
                        'onlinetext' => new external_value(PARAM_TEXT, 'onlinetext submission'),
                        'filename' => new external_value(PARAM_TEXT, 'onlinetext submission')
                )
        );
    }

    /**
     * Update assign submission:
     * When this function is called with a filename, the file itself is already
     * stored in the private user file area. Then it has to be moved to the
     * user draft area, and then be submitted to the given assign.
     *
     * @param int assignid
     * @param string onlinetext
     * @param string filename
     * @return external_multiple_structure
     */
    public static function update_assign_submission($assignid,$onlinetext,$filename) {
        global $DB,$USER;

        if (empty($assignid) || (empty($onlinetext) && empty($filename)) ) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::update_assign_submission_parameters(), array('assignid'=>$assignid,'onlinetext'=>$onlinetext,'filename'=>$filename));

        $context = context_user::instance($USER->id);

        if($filename) {
            $fs = get_file_storage();
            try {
                $old = $fs->get_file($context->id, "user", "private", 0, "/", $filename);
                $draftid = file_get_unused_draft_itemid();

                $file_record = array('contextid'=>$context->id, 'component'=>'user', 'filearea'=>'draft',
                        'itemid'=>$draftid, 'filepath'=>'/', 'filename'=>$old->get_filename(),
                        'timecreated'=>time(), 'timemodified'=>time());
                $fs->create_file_from_storedfile($file_record, $old->get_id());
            } catch (Exception $e) {
            }

        }

        $cm = get_coursemodule_from_instance('assign', $assignid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

        $context = context_module::instance($cm->id);

        $assign = new assign($context, $cm, $course);

        $data = new stdClass();
        if($filename)
            $data->files_filemanager = $draftid;

        $conditions = $DB->sql_compare_text("plugin")." = 'onlinetext' AND ".$DB->sql_compare_text("name"). " = 'enabled' AND value=1 AND assignment =".$assignid;
        if($onlinetext || $DB->get_record_select("assign_plugin_config",$conditions)) {
            $onlinetexteditor = array();
            $onlinetexteditor['text'] = $onlinetext;
            $onlinetexteditor['format'] = 1;
            $data->onlinetext_editor = $onlinetexteditor;
        }
        $data->id = $cm->id;
        $data->action = "savesubmission";
        $notices = array();

        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/lib.php');

        return array("success" => $assign->save_submission($data, $notices));
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function update_assign_submission_returns() {
        return new external_single_structure(
                array(
                        'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
                )
        );
    }


    /** Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_competence_by_id_parameters() {
        return new external_function_parameters(
                array(
                        'competenceid' => new external_value(PARAM_INT, 'id of competence')
                )
        );
    }

    /**
     * Get competence information
     * @param int assignid
     * @return external_multiple_structure
     */
    public static function get_competence_by_id($competenceid) {
        global $DB,$USER;

        if (empty($competenceid)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::get_competence_by_id_parameters(), array('competenceid'=>$competenceid));

        return $DB->get_record("block_exacompdescriptors", array("id"=>$competenceid));
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_competence_by_id_returns() {
        return new external_single_structure(
                array(
                        'title' => new external_value(PARAM_TEXT, 'title of assign')
                )
        );
    }


    /** Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_topic_by_id_parameters() {
        return new external_function_parameters(
                array(
                        'topicid' => new external_value(PARAM_INT, 'id of competence')
                )
        );
    }

    /**
     * Get competence information
     * @param int assignid
     * @return external_multiple_structure
     */
    public static function get_topic_by_id($topicid) {
        global $DB,$USER;

        if (empty($topicid)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::get_topic_by_id_parameters(), array('topicid'=>$topicid));

        return $DB->get_record("block_exacomptopics", array("id"=>$topicid));
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_topic_by_id_returns() {
        return new external_single_structure(
                array(
                        'title' => new external_value(PARAM_TEXT, 'title of topic')
                )
        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_subtopics_by_topic_parameters() {
        return new external_function_parameters(
                array(
                        'topicid' => new external_value(PARAM_INT, 'id of topic'),
                        'userid' => new external_value(PARAM_INT, 'id of user'))
        );
    }
    /**
     * Get subtopics
     * @param int topicid
     * @return array of subtopics
     */
    public static function get_subtopics_by_topic($topicid, $userid) {
        global $CFG,$DB, $USER;

        if (empty($topicid)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::get_subtopics_by_topic_parameters(), array('topicid'=>$topicid, 'userid'=>$userid));

        if($userid == 0)
            $userid = $USER->id;

        $mycourses = enrol_get_users_courses($USER->id);
        //$mycourses = enrol_get_my_courses();
        $courses = array();

        foreach($mycourses as $mycourse) {
            $context = context_course::instance($mycourse->id);
            //$context = get_context_instance(CONTEXT_COURSE, $mycourse->id);
            if($DB->record_exists("block_instances", array("blockname" => "exacomp", "parentcontextid" => $context->id))) {
                $course = array("courseid" => $mycourse->id,"fullname"=>$mycourse->fullname,"shortname"=>$mycourse->shortname);
                $courses[] = $course;
            }
        }

        //courses in $courses
        $topics = array();
        foreach($courses as $course){
            $tree = block_exacomp_build_example_tree_desc($course["courseid"]);
            foreach($tree as $subject){
                if($subject->id == $topicid){
                    foreach($subject->subs as $topic){
                        if(!array_key_exists($topic->id, $topics)){
                            $topics[$topic->id] = new stdClass();
                            $topics[$topic->id]->subtopicid = $topic->id;
                            $topics[$topic->id]->title = $topic->title;
                        }
                    }
                }
            }
        }

        return $topics;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_subtopics_by_topic_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'subtopicid' => new external_value(PARAM_INT, 'id of topic'),
                                'title' => new external_value(PARAM_TEXT, 'title of topic')
                        )
                )
        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_examples_for_subject_parameters() {
        return new external_function_parameters(
                array(
                        'subjectid' => new external_value(PARAM_INT, 'id of subject'),
                        'courseid' => new external_value(PARAM_INT, 'id of course'),
                        'userid' => new external_value(PARAM_INT, 'id of user'))
        );
    }
    /**
     * Get examples
     * @param int subjectid
     * @return array of examples
     */
    public static function get_examples_for_subject($subjectid, $courseid, $userid) {
        global $CFG,$DB, $USER;

        if (empty($subjectid) || empty($courseid)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::get_examples_for_subject_parameters(), array('subjectid'=>$subjectid,'courseid'=>$courseid, 'userid'=>$userid));

        if($userid == 0)
            $userid = $USER->id;


        $structure = array();

        $topics = block_exacomp_get_topics_by_subject($courseid, $subjectid);
        foreach($topics as $topic){
            if(!array_key_exists($topic->id, $structure)){
                $structure[$topic->id] = new stdClass();
                $structure[$topic->id]->topicid = $topic->id;
                $structure[$topic->id]->title = $topic->title;
                $structure[$topic->id]->examples = array();
            }
            $descriptors = block_exacomp_get_descriptors_by_topic($courseid, $topic->id);

            foreach($descriptors as $descriptor){
                $examples = $DB->get_records_sql(
                        "SELECT de.id as deid, e.id, e.title, tax.title as tax, e.task, e.externalurl,
                        e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement, e.creatorid
                        FROM {" . DB_EXAMPLES . "} e
                        JOIN {" . DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?
                        LEFT JOIN {" . DB_TAXONOMIES . "} tax ON e.taxid=tax.id"
                        , array($descriptor->id));
                	
                foreach($examples as $example){
                    if(!array_key_exists($example->id, $structure[$topic->id]->examples)){
                        $structure[$topic->id]->examples[$example->id] = new stdClass();
                        $structure[$topic->id]->examples[$example->id]->exampleid = $example->id;
                        $structure[$topic->id]->examples[$example->id]->example_title = $example->title;
						$structure[$topic->id]->examples[$example->id]->example_creatorid = $example->creatorid;
                        $items = $DB->get_records('block_exacompitemexample', array('exampleid'=>$example->id));
                        if($items){
                            //check for current
                            $current_timestamp = 0;
                            foreach($items as $item){
                                if($item->timecreated > $current_timestamp){
                                    $structure[$topic->id]->examples[$example->id]->example_item = $item->itemid;
                                    $structure[$topic->id]->examples[$example->id]->example_status = $item->status;
                                }
                            }
                        }else{
                            $structure[$topic->id]->examples[$example->id]->example_item = -1;
                            $structure[$topic->id]->examples[$example->id]->example_status = -1;
                        }
                    }
                }
            }
        }

        return $structure;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_examples_for_subject_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'topicid' => new external_value(PARAM_INT, 'id of topic'),
                                'title' => new external_value(PARAM_TEXT, 'title of topic'),
                                'examples' => new external_multiple_structure(
                                        new external_single_structure(
                                                array(
                                                        'exampleid' => new external_value(PARAM_INT, 'id of example'),
                                                        'example_title' => new external_value(PARAM_TEXT, 'title of example'),
                                                        'example_item' => new external_value(PARAM_INT, 'current item id'),
                                                        'example_status' => new external_value(PARAM_INT, 'status of current item'),
														'example_creatorid' => new external_value(PARAM_INT, 'creator of example')
                                                )
                                        )
                                )
                        )
                )
        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_example_by_id_parameters() {
        return new external_function_parameters(
                array(
                        'exampleid' => new external_value(PARAM_INT, 'id of example')
                )
        );
    }
    /**
     * Get example
     * @param int exampleid
     * @return example
     */
    public static function get_example_by_id($exampleid) {
        global $CFG,$DB, $USER;

        if (empty($exampleid)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }

        $params = self::validate_parameters(self::get_example_by_id_parameters(), array('exampleid'=>$exampleid));

        $example = $DB->get_record(DB_EXAMPLES, array('id'=>$exampleid));

        return $example;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_example_by_id_returns() {
        return new external_single_structure(
                array(
                        'title' => new external_value(PARAM_TEXT, 'title of example'),
                        'description' => new external_value(PARAM_TEXT, 'description of example'),
                        'task' => new external_value(PARAM_TEXT, 'task(url/description) of example'),
						'externaltask' => new external_value(PARAM_TEXT, 'externaltask(url/description) of example')
                )

        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_descriptors_for_example_parameters() {
        return new external_function_parameters(
                array(
                        'exampleid' => new external_value(PARAM_INT, 'id of example'),
                        'courseid' => new external_value(PARAM_INT, 'id of course'),
                        'userid' => new external_value(PARAM_INT, 'id of user')
                )
        );
    }

    /**
     * Get descriptors for example
     * @param int exampleid
     * @return list of descriptors
     */
    public static function get_descriptors_for_example($exampleid, $courseid, $userid) {
        global $CFG,$DB, $USER;

        if($userid == 0)
            $userid = $USER->id;

        $params = self::validate_parameters(self::get_descriptors_for_example_parameters(), array('exampleid'=>$exampleid, 'courseid'=>$courseid, 'userid'=>$userid));

        $descriptors_exam_mm = $DB->get_records(DB_DESCEXAMP, array('exampid'=>$exampleid));

        $descriptors = array();
        foreach($descriptors_exam_mm as $descriptor_mm){
            $descriptors[$descriptor_mm->descrid] = $DB->get_record(DB_DESCRIPTORS, array('id'=>$descriptor_mm->descrid));
            	
            $eval = $DB->get_record(DB_COMPETENCIES, array('userid'=>$userid, 'compid'=>$descriptor_mm->descrid, 'courseid'=>$courseid, 'role'=>ROLE_TEACHER));
            if($eval){
                $descriptors[$descriptor_mm->descrid]->evaluation=$eval->value;
            }else{
                $descriptors[$descriptor_mm->descrid]->evaluation=0;
            }
            	
            $descriptors[$descriptor_mm->descrid]->descriptorid = $descriptor_mm->descrid;
        }
        return $descriptors;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_descriptors_for_example_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
                                'title' => new external_value(PARAM_TEXT, 'title of descriptor'),
                                'evaluation' => new external_value(PARAM_INT, 'evaluation of descriptor')
                        )
                )
        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_user_role_parameters() {
        return new external_function_parameters(
                array(
                )
        );
    }

    /**
     * return 1 for trainer
     * 		  2 for student
     * @param int userid
     * @return int
     */
    public static function get_user_role() {
        global $CFG,$DB, $USER;

        $params = self::validate_parameters(self::get_user_role_parameters(), array());
         
        $trainer = $DB->get_records('block_exaportexternaltrainer', array('trainerid'=>$USER->id));
        if($trainer)
            return  array("role" => 1);

        $student = $DB->get_records('block_exaportexternaltrainer', array('studentid'=>$USER->id));

        if($student)
            return array("role" => 2);

        //neither student nor trainer
        return array("role" => $USER->id);
    }

    /**
     * Returns description of method return values
     * @return external_multiple_structure
     */
    public static function get_user_role_returns() {
        return new external_function_parameters(
                array(
                        'role' => new external_value(PARAM_INT, '1=trainer, 2=student')
                )
        );
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_external_trainer_students_parameters() {
        return new external_function_parameters(
                array(
                )
        );
    }

    /**
     * Get all students for an external trainer
     * @return all items available
     */
    public static function get_external_trainer_students($trainerid) {
        global $CFG,$DB,$USER;

        $students = $DB->get_records('block_exacompexternaltrainer',array('trainerid'=>$USER->id));
        $returndata = array();

        foreach($students as $student) {
            $studentObject = $DB->get_record('user',array('id'=>$student->studentid));
            $returndataObject = new stdClass();
            $returndataObject->name = fullname($studentObject);
            $returndataObject->userid = $student->studentid;
            $returndata[] = $returndataObject;
        }
        return $returndata;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_external_trainer_students_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'userid' => new external_value(PARAM_INT, 'id of user'),
                                'name' => new external_value(PARAM_TEXT, 'name of user')

                        )
                )
        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_item_example_status_parameters() {
        return new external_function_parameters(
                array(
                        'exampleid' => new external_value(PARAM_INT, 'example id')
                )
        );
    }

    /**
     * Get status of example
     * @return status
     */
    public static function get_item_example_status($exampleid) {
        global $CFG,$DB,$USER;

        $params = self::validate_parameters(self::get_item_example_status_parameters(), array('exampleid'=>$exampleid));
        $entries = $DB->get_records('block_exacompitemexample', array('exampleid'=>$exampleid));

        $current_timestamp = 0;
        $status = 0;
        foreach($entries as $entry){
            if($current_timestamp < $entry->timecreated){
                $current_timestamp = $entry->timecreated;
                $status = $entry->status;
            }
        }
        return array("status"=>$status);;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_item_example_status_returns() {
        return new external_single_structure(
                array(
                        'status' => new external_value(PARAM_INT, 'status')
                )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_subjects_for_user_parameters() {
        return new external_function_parameters(
                array('userid' => new external_value(PARAM_INT, 'id of user'))
        );
    }

    /**
     * get subjects from one user for all his courses
     * @return array of user courses
     */
    public static function get_subjects_for_user($userid) {
        global $CFG,$DB, $USER;
        require_once("$CFG->dirroot/lib/enrollib.php");

        $params = self::validate_parameters(self::get_subjects_for_user_parameters(), array('userid'=>$userid));

        if($userid == 0)
            $userid = $USER->id;

        $courses = block_exacomp_external::get_courses($userid);

        $subjects_res = array();
        foreach($courses as $course){
			$subjects = block_exacomp_get_subjects_by_course($course["courseid"]);
           
            foreach($subjects as $subject){
				if(!array_key_exists($subject->id, $subjects_res)){
					$elem = new stdClass();
					$elem->subjectid = $subject->id;
					$elem->title= $subject->title;
					$elem->courseid = $course["courseid"];
					$subjects_res[] = $elem;
				}
			}
        }

        return $subjects_res;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_subjects_for_user_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'subjectid' => new external_value(PARAM_INT, 'id of subject'),
                                'title' => new external_value(PARAM_TEXT, 'title of subject'),
                                'courseid' => new external_value(PARAM_INT, 'id of course')
                        )
                )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_item_for_example_parameters() {
        return new external_function_parameters(
                array('userid' => new external_value(PARAM_INT, 'id of user'),
                        'itemid' => new external_value(PARAM_INT, 'id of item'))
        );
    }

    /**
     * get subjects from one user for all his courses
     * @return array of user courses
     */
    public static function get_item_for_example($userid,$itemid) {
        global $CFG,$DB, $USER;

        if($userid == 0)
            $userid = $USER->id;
        
        $params = self::validate_parameters(self::get_item_for_example_parameters(), array('userid'=>$userid,'itemid'=>$itemid));

        $conditions=array("id"=>$itemid,"userid"=>$userid);
        $item = $DB->get_record("block_exaportitem", $conditions, 'id,userid,type,name,intro,url',MUST_EXIST);
        $itemexample = $DB->get_record("block_exacompitemexample", array("itemid" => $itemid));

        $item->file = "";
        $item->isimage = false;
        $item->filename = "";
        $item->effort = strip_tags($item->intro);
        $item->teachervalue = isset($itemexample->teachervalue) ? $itemexample->teachervalue : 0;
        $item->studentvalue = isset($itemexample->studentvalue) ? $itemexample->studentvalue : 0;
        $item->status = isset($itemexample->status) ? $itemexample->status : 0;

        if ($item->type == 'file') {
            require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';

            $item->userid = $userid;
            if ($file = block_exaport_get_item_file($item)) {
                $item->file = ("{$CFG->wwwroot}/blocks/exaport/portfoliofile.php?access=portfolio/id/".$userid."&itemid=".$itemid);
                $item->isimage = $file->is_valid_image();
                $item->filename = $file->get_filename();
            }
        }
         
        $item->studentcomment = '';
        $item->teachercomment = '';
        $itemcomments = $DB->get_records('block_exaportitemcomm',array('itemid'=>$itemid),'timemodified ASC','entry',0,2);
        if($itemcomments) {
            $count = 0;
            foreach($itemcomments as $itemcomment) {
                if($count == 0) {
                    $item->studentcomment = $itemcomment->entry;
                    $count++;
                } else {
                    $item->teachercomment = $itemcomment->entry;
                }
            }
        } 
        
        return ($item);
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_item_for_example_returns() {
        return new external_single_structure(
                array(
                        'id' => new external_value(PARAM_INT, 'id of item'),
                        'name' => new external_value(PARAM_TEXT, 'title of item'),
                        'type' => new external_value(PARAM_TEXT, 'type of item (note,file,link)'),
                        'url' => new external_value(PARAM_TEXT, 'url'),
                        'effort' => new external_value(PARAM_RAW, 'description of the effort'),
                        'filename' => new external_value(PARAM_TEXT, 'title of item'),
                        'file' => new external_value(PARAM_URL, 'file url'),
                        'isimage' => new external_value(PARAM_BOOL,'true if file is image'),
                        'status' => new external_value(PARAM_INT,'status of the submission'),
                        'teachervalue' => new external_value(PARAM_INT,'teacher grading'),
                        'studentvalue' => new external_value(PARAM_INT,'student grading'),
                        'teachercomment' => new external_value(PARAM_TEXT,'teacher comment'),
                        'studentcomment' => new external_value(PARAM_TEXT,'student comment')
                )
        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_competencies_for_upload_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'id of user')
                )
        );
    }
    /**
     * Get all available competencies
     * @param int subjectid
     * @return array of examples
     */
    public static function get_competencies_for_upload($userid) {
        global $CFG,$DB, $USER;

        $params = self::validate_parameters(self::get_competencies_for_upload_parameters(), array('userid'=>$userid));
        if($userid == 0)
            $userid = $USER->id;

        $structure = array();

        $courses = block_exacomp_external::get_courses($userid);

        foreach($courses as $course){
            $tree = block_exacomp_get_competence_tree($course["courseid"]);
            	
            foreach($tree as $subject){
                $elem_sub = new stdClass();
                $elem_sub->subjectid = $subject->id;
                $elem_sub->subjecttitle = $subject->title;
                $elem_sub->topics = array();
                foreach($subject->subs as $topic){
                    $elem_topic = new stdClass();
                    $elem_topic->topicid = $topic->id;
                    $elem_topic->topictitle = $topic->title;
                    $elem_topic->descriptors = array();
                    foreach($topic->descriptors as $descriptor){
                        $elem_desc = new stdClass();
                        $elem_desc->descriptorid = $descriptor->id;
                        $elem_desc->descriptortitle = $descriptor->title;
                        $elem_topic->descriptors[] = $elem_desc;
                    }
                    $elem_sub->topics[] = $elem_topic;
                }
                $structure[] = $elem_sub;
            }
        }

        return $structure;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_competencies_for_upload_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'subjectid' => new external_value(PARAM_INT, 'id of topic'),
                                'subjecttitle' => new external_value(PARAM_TEXT, 'title of topic'),
                                'topics' => new external_multiple_structure(
                                        new external_single_structure(
                                                array(
                                                        'topicid' => new external_value(PARAM_INT, 'id of example'),
                                                        'topictitle' => new external_value(PARAM_TEXT, 'title of example'),
                                                        'descriptors' => new external_multiple_structure(
                                                                new external_single_structure(
                                                                        array(
                                                                                'descriptorid' => new external_value(PARAM_INT, 'id of example'),
                                                                                'descriptortitle' => new external_value(PARAM_TEXT, 'title of example')
                                                                        )
                                                                )
                                                        )
                                                )
                                        )
                                )
                        )
                )
        );
    }
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function submit_example_parameters() {
        return new external_function_parameters(
                array('exampleid' => new external_value(PARAM_INT, 'exampleid'),
                        'studentvalue' => new external_value(PARAM_INT, 'studentvalue'),
                        'url' => new external_value(PARAM_URL, 'url'),
                        'effort' => new external_value(PARAM_TEXT, 'effort'),
                        'filename' => new external_value(PARAM_TEXT, 'filename, used to look up file and create a new one in the exaport file area'),
                        'studentcomment' => new external_value(PARAM_TEXT, 'studentcomment'),
                        'title' => new external_value(PARAM_TEXT, 'title'),
                        'itemid' => new external_value(PARAM_INT, 'itemid'))
        );
    
    }
    
    /**
     * Add item
     * @param int itemid
     * @return array of course subjects
     */
    public static function submit_example($exampleid,$studentvalue,$url,$effort,$filename,$studentcomment,$title,$itemid=0) {
        global $CFG,$DB,$USER;
    
        $params = self::validate_parameters(self::submit_example_parameters(), array('title'=>$title,'exampleid'=>$exampleid,'url'=>$url,'effort'=>$effort,'filename'=>$filename,'studentcomment'=>$studentcomment,'studentvalue'=>$studentvalue,'itemid'=>$itemid));
    
        $type = ($filename) ? 'file' : 'url';
        
        //insert: if itemid == 0 OR status != 0
        $insert = true;
        if($itemid != 0) {
            $itemexample = $DB->get_record('block_exacompitemexample', array('itemid'=>$itemid));
            if ($itemexample->status == 0) 
                $insert = false;
        }
        if($insert) {
            $itemid = $DB->insert_record("block_exaportitem", array('userid'=>$USER->id,'name'=>$title,'url'=>$url,'intro'=>$effort,'type'=>$type,'timemodified'=>time()));
        } else {
            require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';
            $item = $DB->get_record('block_exaportitem',array('id'=>$itemid));
            $item->name = $title;
            $item->url = $url;
            $item->intro = $effort;
            $item->type = $type;
            $item->timemodified = time();
            
            block_exaport_file_remove($DB->get_record("block_exaportitem",array("id"=>$itemid)));
            $DB->update_record('block_exaportitem', $item);
        }
        
        //if a file is added we need to copy the file from the user/private filearea to block_exaport/item_file with the itemid from above
        if($type == "file") {
            $context = context_user::instance($USER->id);
            $fs = get_file_storage();
            try {
                $old = $fs->get_file($context->id, "user", "private", 0, "/", $filename);
    
                if($old) {
                    $file_record = array('contextid'=>$context->id, 'component'=>'block_exaport', 'filearea'=>'item_file',
                            'itemid'=>$itemid, 'filepath'=>'/', 'filename'=>$old->get_filename(),
                            'timecreated'=>time(), 'timemodified'=>time());
                    $fs->create_file_from_storedfile($file_record, $old->get_id());
                }
            } catch (Exception $e) {
                //some problem with the file occured
            }
        }
        
        if($insert) {
            $DB->insert_record('block_exacompitemexample',array('exampleid'=>$exampleid,'itemid'=>$itemid,'timecreated'=>time(),'status'=>0,'studentvalue'=>$studentvalue));
            $DB->insert_record('block_exaportitemcomm',array('itemid'=>$itemid,'userid'=>$USER->id,'entry'=>$studentcomment,'timemodified'=>time()));
        } else {
            $itemexample->timemodified = time();
            $itemexample->studentvalue = $studentvalue;
            $DB->update_record('block_exacompitemexample', $itemexample);

            $DB->delete_records('block_exaportitemcomm',array('itemid'=>$itemid,'userid'=>$USER->id));
            $DB->insert_record('block_exaportitemcomm',array('itemid'=>$itemid,'userid'=>$USER->id,'entry'=>$studentcomment,'timemodified'=>time()));
        }
        
        return array("success"=>true,"itemid"=>$itemid);
    }
    
    /**
     * Returns desription of method return values
     * @return external_single_structure
     */
    public static function submit_example_returns() {
        return new external_single_structure(
                array(
                        'success' => new external_value(PARAM_BOOL, 'status'),
                        'itemid' => new external_value(PARAM_INT, 'itemid')
                )
        );
    }
	/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
     public static function create_example_parameters() {
        return new external_function_parameters(
                array(
                        'name' => new external_value(PARAM_TEXT, 'title of example'),
                        'description' => new external_value(PARAM_TEXT, 'description of example'),
                        'task' => new external_value(PARAM_TEXT, 'task of example'),
                        'comps' => new external_value(PARAM_TEXT, 'list of competencies, seperated by comma'),
						'filename' => new external_value(PARAM_TEXT, 'filename, used to look up file and create a new one in the exaport file area')
                )
        );
    }
    /**
     * create example
     * @param 
     * @return 
     */
    public static function create_example($name, $description, $task, $comps, $filename) {
        global $CFG,$DB, $USER;

        if (empty($name)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }
        
        $params = self::validate_parameters(self::create_example_parameters(), array('name'=>$name, 'description'=>$description, 'task'=>$task, 'comps'=>$comps, 'filename'=>$filename));
        
		if($filename != null){
			$context = context_user::instance($USER->id);
			$fs = get_file_storage();
			
			if(!$fs->file_exists($context->id, 'user', 'private', 0, '/', $filename))
				$form->save_stored_file('file', $context->id, 'user', 'private', 0, '/', $filename, true);

			$pathnamehash = $fs->get_pathname_hash($context->id, 'user', 'private', 0, '/', $filename);
			$temp_task = new moodle_url($CFG->wwwroot.'/blocks/exacomp/example_upload.php',array("action"=>"serve","c"=>$context->id,"i"=>$pathnamehash,"courseid"=>1));
            $example_task = $temp_task->out(false);
		}
		
		//insert into examples and example_desc
		$example = new stdClass();
		$example->title = $name;
		$example->description = $description;
		$example->externaltask = $task;
		$example->task = $example_task;
		$example->creatorid = $USER->id;
		$example->timestamp = date();
		$example->source = CUSTOM_EXAMPLE_SOURCE;
		
		$id = $DB->insert_record(DB_EXAMPLES, $example);
		
		$descriptors = explode(',', $comps);
		foreach($descriptors as $descriptor){
			$insert = new stdClass();
			$insert->exampid = $id;
			$insert->descrid = $descriptor;
			$DB->insert_record(DB_DESCEXAMP, $insert);
		}
		
       return array("exampleid"=>$id);
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function create_example_returns() {
        return new external_single_structure(
            array(
                    'exampleid' => new external_value(PARAM_INT, 'id of created example')
            )   
        );
    }
	/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function grade_item_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'id of user'),
                        'value' => new external_value(PARAM_INT, 'value for grading'),
                        'status' => new external_value(PARAM_INT, 'status'),
                        'comment' => new external_value(PARAM_TEXT, 'comment of grading'),
                        'itemid' => new external_value(PARAM_INT, 'id of item'),
						'comps' => new external_value(PARAM_TEXT, 'comps for example - positive grading'),
						'courseid' => new external_value(PARAM_INT, 'if of course')
                )
        );
    }
    /**
     * grade an item
     * @param 
     * @return 
     */
    public static function grade_item($userid, $value, $status, $comment, $itemid, $comps, $courseid) {
        global $CFG,$DB, $USER;

        if (empty($userid) || empty($value) || empty($comment) || empty($itemid) || empty($courseid)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }
        
        $params = self::validate_parameters(self::grade_item_parameters(), array('userid'=>$userid, 'value'=>$value, 'status'=>$status, 'comment'=>$comment, 'itemid'=>$itemid, 'comps'=>$comps, 'courseid'=>$courseid));
        
		//insert into block_exacompitemexample
		$update = new stdClass();
		$update = $DB->get_record('block_exacompitemexample',array('itemid'=>$itemid));
		
		$exampleid = $update->exampleid;
		
		$update->itemid = $itemid;
		$update->datemodified = time();
		$update->teachervalue = $value;
		$update->status = $status;
		
		$DB->update_record('block_exacompitemexample', $update);
		
		$insert = new stdClass();
		$insert->itemid = $itemid;
		$insert->userid = $USER->id;
		$insert->entry = $comment;
		$insert->timemodified = time();
		
		$DB->delete_records('block_exaportitemcomm',array('itemid'=>$itemid,'userid'=>$USER->id));
		$DB->insert_record('block_exaportitemcomm', $insert);

		//get all available descriptors and unset them who are not received via web service
		$descriptors_exam_mm = $DB->get_records(DB_DESCEXAMP, array('exampid'=>$exampleid));
		
		$descriptors = explode(',', $comps);
			
		$unset_descriptors = array();
		foreach($descriptors_exam_mm as $descr_examp){
			if(!in_array($descr_examp->descrid, $descriptors)){
				$unset_descriptors[] = $descr_examp->descrid;
			}
		}
		
		//set positive graded competencies
		foreach($descriptors as $descriptor){
			if($descriptor != 0){
				$entry = $DB->get_record(DB_COMPETENCIES, array('userid'=>$userid, 'compid'=>$descriptor, 'courseid'=>$courseid, 'role'=>ROLE_TEACHER));
				
				if($entry){
					$entry->reviewerid = $USER->id;
					$entry->value = 1;
					$entry->timestamp = time();
					$DB->update_record(DB_COMPETENCIES, $entry);
				}else{
					$insert = new stdClass();
					$insert->userid = $userid;
					$insert->compid = $descriptor;
					$insert->reviewerid = $USER->id;
					$insert->role = ROLE_TEACHER;
					$insert->courseid = $courseid;
					$insert->value = 1;
					$insert->timestamp = time();
					
					$DB->insert_record(DB_COMPETENCIES, $insert);
				}
			}
		}
		
		//set negative graded competencies
		foreach($unset_descriptors as $descriptor){
			$entry = $DB->get_record(DB_COMPETENCIES, array('userid'=>$userid, 'compid'=>$descriptor, 'courseid'=>$courseid, 'role'=>ROLE_TEACHER));
			
			if($entry){
				$entry->reviewerid = $USER->id;
				$entry->value = 0;
				$entry->timestamp = time();
				$DB->update_record(DB_COMPETENCIES, $entry);
			}else{
				$insert = new stdClass();
				$insert->userid = $userid;
				$insert->compid = $descriptor;
				$insert->reviewerid = $USER->id;
				$insert->role = ROLE_TEACHER;
				$insert->courseid = $courseid;
				$insert->value = 0;
				$insert->timestamp = time();
				
				$DB->insert_record(DB_COMPETENCIES, $insert);
			}
		}
		
        return array("success"=>true);
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function grade_item_returns() {
        return new external_single_structure(
            array(
                    'success' => new external_value(PARAM_BOOL, 'true if grading was successful')
            )   
        );
    }
		/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_item_grading_parameters() {
        return new external_function_parameters(
                array(
                        'itemid' => new external_value(PARAM_INT, 'id of item'),
                        'userid' => new external_value(PARAM_INT, 'id of user')
                )
        );
    }
    /**
     * grade an item
     * @param 
     * @return 
     */
    public static function get_item_grading($itemid, $userid) {
        global $CFG,$DB, $USER;

        if (empty($itemid)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }
        
        $params = self::validate_parameters(self::get_item_grading_parameters(), array('itemid'=>$itemid, 'userid'=>$userid));
        
        if($userid == 0)
            $userid = $USER->id;
            
		$entry = $DB->get_record('block_exacompitemexample', array('itemid'=>$itemid));
	    $comments = $DB->get_records('block_exaportitemcomm', array('itemid'=>$itemid));
	    foreach($comments as $comment){
	        //two comments per item, one from student and one from trainer
	        if($comment->userid != $userid){
	            $entry->comment = $comment;
	        }
	    }
		
        return $entry;
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_item_grading_returns() {
        return new external_single_structure(
            array(
                    'teachervalue' => new external_value(PARAM_INT, 'grading of teacher'),
                    'comment' => new external_value(PARAM_TEXT, 'comment of teacher')
            )   
        );
    }
		/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_user_examples_parameters() {
        return new external_function_parameters(
                array()
        );
    }
    /**
     * grade an item
     * @param 
     * @return 
     */
    public static function get_user_examples() {
        global $CFG,$DB, $USER;

        $params = self::validate_parameters(self::get_user_examples_parameters(), array());
        
		$subjects = block_exacomp_external::get_subjects_for_user($USER->id);
		
		$examples = array();
		foreach($subjects as $subject){
			$topics = block_exacomp_external::get_examples_for_subject($subject->subjectid, $subject->courseid, 0);
			foreach($topics as $topic){
				foreach($topic->examples as $example){
					if($example->example_creatorid == $USER->id){
						$elem = new stdClass();
						$elem->exampleid = $example->exampleid;
						$elem->exampletitle = $example->example_title;
						$examples[] = $elem;
					}
				}
			}
		}
       
        return $examples;
		//return array();
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_user_examples_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'exampleid' => new external_value(PARAM_INT, 'id of example'),
                                'exampletitle' => new external_value(PARAM_TEXT, 'title of example')
							)
				)
		);
    }
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_user_profile_parameters() {
        return new external_function_parameters(
                array('userid' => new external_value(PARAM_INT, 'id of user'))
        );
    }
    
    /**
     * @return array of user courses
     */
    public static function get_user_profile($userid) {
        global $CFG,$DB, $USER;
        require_once("$CFG->dirroot/lib/enrollib.php");
        require_once $CFG->dirroot . '/blocks/exacomp/lib/lib.php';
    
        $params = self::validate_parameters(self::get_user_profile_parameters(), array('userid'=>$userid));
    
        if($userid == 0)
            $userid = $USER->id;
    
        $courses = block_exacomp_external::get_courses($userid);
    
        $total_total = 0;
        $total_reached = 0;
        $total_average = 0;
    
        $response = array();
    
        foreach($courses as $course) {
            $statistics = block_exacomp_get_course_competence_statistics($course->id, $DB->get_record('user',array('id'=>$userid)),
                    block_exacomp_get_grading_scheme($course->id));
    
            $total_total +=  $statistics[0];
            $total_reached += $statistics[1];
            $total_average += $statistics[2];
    
            $response_obj = new stdClass();
            $response_obj->courseid = $course->id;
            $response_obj->title = $course->fullname;
            $response_obj->totalcomps = $statistics[0];
            $response_obj->reachedcomps = $statistics[1];
            $response_obj->averagecomps = $statistics[2];
    
            $response[] = $response_obj;
        }
    
        return $response;
    }
    
    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function get_user_profile_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'courseid' => new external_value(PARAM_INT, 'id of course'),
                                'title' => new external_value(PARAM_TEXT, 'title of course'),
                                'totalcomps' => new external_value(PARAM_INT, 'amount of total competencies'),
                                'reachedcomps' => new external_value(PARAM_INT, 'amount of reached competencies'),
                                'averagecomps' => new external_value(PARAM_INT, 'amount of average reached competencies by all coure students')
                        )
                )
        );
    }
	/**
     * Returns description of method parameters
     * @return external_function_parameters
     */
     public static function update_example_parameters() {
        return new external_function_parameters(
                array(
						'exampleid' => new external_value(PARAM_INT, 'id of example'),
                        'name' => new external_value(PARAM_TEXT, 'title of example'),
                        'description' => new external_value(PARAM_TEXT, 'description of example'),
                        'task' => new external_value(PARAM_TEXT, 'task of example'),
                        'comps' => new external_value(PARAM_TEXT, 'list of competencies, seperated by comma'),
						'filename' => new external_value(PARAM_TEXT, 'filename, used to look up file and create a new one in the exaport file area')
                )
        );
    }
    /**
     * create example
     * @param 
     * @return 
     */
    public static function update_example($exampleid, $name, $description, $task, $comps, $filename) {
        global $CFG,$DB, $USER;

        if (empty($exampleid) || empty($name)) {
            throw new invalid_parameter_exception('Parameter can not be empty');
        }
        
        $params = self::validate_parameters(self::update_example_parameters(), array('exampleid'=>$exampleid, 'name'=>$name, 'description'=>$description, 'task'=>$task, 'comps'=>$comps, 'filename'=>$filename));
        
		if($filename != null){
			$context = context_user::instance($USER->id);
			$fs = get_file_storage();
			
			if(!$fs->file_exists($context->id, 'user', 'private', 0, '/', $filename))
				$form->save_stored_file('file', $context->id, 'user', 'private', 0, '/', $filename, true);

			$pathnamehash = $fs->get_pathname_hash($context->id, 'user', 'private', 0, '/', $filename);
			$temp_task = new moodle_url($CFG->wwwroot.'/blocks/exacomp/example_upload.php',array("action"=>"serve","c"=>$context->id,"i"=>$pathnamehash,"courseid"=>1));
            $example_task = $temp_task->out(false);
		}
		
		$example = $DB->get_record(DB_EXAMPLES, array('id'=>$exampleid));
		
		//insert into examples and example_desc
		$example->title = $name;
		$example->description = $description;
		$example->externaltask = $task;
		$example->task = $example_task;
		
		$id = $DB->update_record(DB_EXAMPLES, $example);
		
		if(!empty($comps)){
			$DB->delete_records(DB_DESCEXAMP, array('exampid'=>$exampleid));
			
			$descriptors = explode(',', $comps);
			foreach($descriptors as $descriptor){
				$insert = new stdClass();
				$insert->exampid = $id;
				$insert->descrid = $descriptor;
				$DB->insert_record(DB_DESCEXAMP, $insert);
			}
		}
		
       return array("success"=>true);
    }

    /**
     * Returns desription of method return values
     * @return external_multiple_structure
     */
    public static function update_example_returns() {
        return new external_single_structure(
            array(
                    'success' => new external_value(PARAM_BOOL, 'true if successful')
            )   
        );
    }
}