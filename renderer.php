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
 * exacomp block rendrer
 *
 * @package    block_exacomp
 * @copyright  2013 gtn gmbh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

define('STUDENTS_PER_COLUMN', 5);

require_once dirname(__FILE__)."/lib/xmllib.php";

class block_exacomp_renderer extends plugin_renderer_base {
    public function header() {
        block_exacomp_init_js_css();

        return
            parent::header().
            $this->print_wrapperdivstart();
    }
    
    public function footer() {
        return
            $this->print_wrapperdivend().
            parent::footer();
    }
    
    public function form_week_learningagenda($selectstudent,$action,$studentid, $view, $date = ''){
        global $COURSE, $CFG;

        if($view == 0){
            $content = html_writer::start_div('');
            $content .= html_writer::start_tag('div',array('style'=>'width:400px;'));
            $content .= $selectstudent;
            $content .= html_writer::start_tag('form', array('id'=>"calendar", 'method'=>"POST", 'action'=>new moodle_url('/blocks/exacomp/learningagenda.php?courseid='.$COURSE->id.'&studentid='.$studentid)));
            $content .= html_writer::start_tag('a', array('href' => new moodle_url('/blocks/exacomp/learningagenda.php?courseid='.$COURSE->id.'&studentid='.$studentid.'&action='.($action-1))));
            $content .= html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/bwd_16x16.png', 'alt' => 'bwd', 'height' => '16', 'width'=>'16'));
            $content .= ' ';
            $content .= html_writer::end_tag('a');
            $content .= html_writer::start_tag('input', array('id'=>"calendarinput", 'value' => $date, 'class'=>"datepicker", 'type'=>"text", 'name'=>"calendarinput",
                    'onchange'=>"this.form.submit();", 'readonly'));
            $content .= html_writer::end_tag('input');
            $content .= ' ';
            $content .= html_writer::start_tag('a', array('href' => new moodle_url('/blocks/exacomp/learningagenda.php?courseid='.$COURSE->id.'&studentid='.$studentid.'&action='.($action+1))));
            $content .= html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/fwd_16x16.png', 'alt' => 'fwd', 'height' => '16', 'width'=>'16'));
            $content .= html_writer::end_tag('a');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('form');

            $print_content = html_writer::link('javascript:window.print()', 
            html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/view_print.png'), 'alt'=>'print')), array('class'=>'print'));
            $content .= html_writer::div(html_writer::tag('form', $print_content), 'competence_profile_printbox');
    
            /*$content .= html_writer::start_tag('div', array('align'=>"right"));
            $content .= html_writer::start_tag('a', array('href' => new moodle_url('/blocks/exacomp/learningagenda.php?courseid='.$COURSE->id.'&studentid='.$studentid.'&print=1&action='.$action)));
            $content .= html_writer::empty_tag('img', array('src'=>$CFG->wwwroot . '/blocks/exacomp/pix/view_print.png', 'alt'=>'print'));
            $content .= html_writer::end_tag('a');
            $content .= html_writer::end_tag('div');*/
            $content .= html_writer::end_div();
        } else {
            $content = html_writer::start_tag('div', array('id'=>'linkback', 'align'=>"right"));
            $content .= html_writer::start_tag('a', array('href' => new moodle_url('/blocks/exacomp/learningagenda.php?courseid='.$COURSE->id.'&studentid='.$studentid.'&print=0&action='.$action)));
            $content .= html_writer::tag('p',get_string('LA_backtoview', 'block_exacomp'));
            $content .= html_writer::end_tag('a');
            $content .= html_writer::end_tag('div');
        }
        return $content;
    }
    public function render_learning_agenda($data, $wochentage){
        global $CFG, $COURSE;


        //header
        $table = new html_table();
        $table->attributes['class'] = 'lernagenda';
        $table->border = 3;
        $head = array();

        $cellhead1 = new html_table_cell();
        $cellhead1->text = html_writer::tag("p", get_string('LA_plan', 'block_exacomp'));
        //$cellhead1->colspan = 4;
        //without column "Was kann ich lernen"
        $cellhead1->colspan = 4;
        $head[] = $cellhead1;

        $cellhead2 = new html_table_cell();
        $cellhead2->text = html_writer::tag("p", get_string('LA_assessment', 'block_exacomp'));
        $cellhead2->colspan = 2;
        $head[] = $cellhead2;

        $table->head = $head;

        $rows = array();

        //erste Reihe->Überschriften
        $row = new html_table_row();
        $cell = new html_table_cell();
        $cell->text = "";
        $cell->colspan = 2;
        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text = html_writer::tag("p", get_string('LA_todo', 'block_exacomp'));
        $row->cells[] = $cell;

        //$cell = new html_table_cell();
        //$cell->text = html_writer::tag("p", get_string('learning', 'block_exacomp'));
        //$row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text = html_writer::tag("p", get_string('LA_enddate', 'block_exacomp'));
        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text = html_writer::tag("p", get_string('LA_student', 'block_exacomp'));
        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text = html_writer::tag("p", get_string('LA_teacher', 'block_exacomp'));
        $row->cells[] = $cell;

        $rows[] = $row;

        foreach($data as $day=>$daydata){
            $row = new html_table_row();
            $cell = new html_table_cell();
            $cell->text = html_writer::tag("p", $day.": ".$daydata['date']);

            $cell->rowspan = count($daydata, COUNT_RECURSIVE)-count($daydata);
            $row->cells[] = $cell;

            foreach($daydata as $subject=>$subjectdata){
                if(strcmp($subject,'date')!=0){
                    if(strcmp($subject, 'no example available')!=0){
                        $cell = new html_table_cell();
                        $cell->text = html_writer::tag("p",$subject);
                        $cell->rowspan = count($subjectdata);

                        $row->cells[] = $cell;
                        foreach($subjectdata as $example){
                            $cell = new html_table_cell();
                            if($task = block_exacomp_get_file_url($example, 'example_task'))
								$cell->text = html_writer::tag("p", html_writer::tag("b", $example->desc.": ").(($example->numb > 0) ? $example->schooltype.$example->numb : "")." "
										.html_writer::tag("a", $example->title, array("href"=>$task, "target"=>"_blank")).(($example->cat) ? " (".$example->cat.")" : ""));
							elseif(isset($example->externalurl))
                            $cell->text = html_writer::tag("p", html_writer::tag("b", $example->desc.": ").(($example->numb > 0) ? $example->schooltype.$example->numb : "")." "
                                    .html_writer::tag("a", $example->title, array("href"=>$example->externalurl, "target"=>"_blank")).(($example->cat) ? " (".$example->cat.")" : ""));
                            else
                                $cell->text = html_writer::tag("p", html_writer::tag("b", $example->desc.": ").(($example->numb > 0) ? $example->schooltype.$example->numb : "")." "
                                        .$example->title.(($example->cat) ? " (".$example->cat.")" : ""));

                            $row->cells[] = $cell;

                            $cell = new html_table_cell();
                            $cell->text = date("d.m.y", $example->enddate);
                            $row->cells[] = $cell;
                            $cell = new html_table_cell();
                            $grading = block_exacomp_get_grading_scheme($COURSE->id);
                            if($grading == 1){
                                if($example->evaluate == 1){
                                    $cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/ok_16x16.png', 'alt' => '1', 'height' => '16', 'width'=>'16'));
                                }
                                else{
                                    $cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
                                }
                            }else{
                                if($example->evaluate > 0)
                                    $cell->text =    $example->evaluate;
                                else
                                    $cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
                            }
                            $row->cells[] = $cell;

                            $cell = new html_table_cell();
                            if($example->tevaluate == 1){
                                $cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/ok_16x16.png', 'alt' => '1', 'height' => '16', 'width'=>'16'));
                            }
                            else{
                                $cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
                            }
                            $row->cells[] = $cell;

                            $rows[] = $row;
                            $row = new html_table_row();
                        }
                    }else{
                        $cell = new html_table_cell();
                        $cell->text = html_writer::tag("p",get_string('LA_no_example', 'block_exacomp'));
                        $cell->colspan = 5;
                        $row->cells[] = $cell;
                        $rows[] = $row;
                        $row = new html_table_row();
                    }
                }
            }
        }

        $table->data = $rows;

        return html_writer::tag("div", html_writer::table($table), array("id"=>"exabis_competences_block"));
    }

    public function print_view_learning_agenda($data, $studentname){
        global $CFG, $COURSE;

        //header
        $table = new html_table();
        $table->attributes['class'] = 'lernagenda';
        $table->attributes['border'] = 1;
        $table->attributes['style'] = 'padding:5px; table-layout:inherit';

        $head = array();

        $cellhead1 = new html_table_cell();
        $cellhead1->text = html_writer::tag("p", get_string('LA_plan', 'block_exacomp').
                get_string('LA_from_n', 'block_exacomp').$studentname.get_string('LA_from_m', 'block_exacomp').
                $data[get_string('LA_MON', 'block_exacomp')]['date'].get_string('LA_to', 'block_exacomp').$data[get_string('LA_FRI', 'block_exacomp')]['date']);
        //$cellhead1->colspan = 4;
        //without column "Was kann ich lernen"
        $cellhead1->colspan = 4;
        $head[] = $cellhead1;

        $cellhead2 = new html_table_cell();
        $cellhead2->text = html_writer::tag("p", get_string('LA_assessment', 'block_exacomp'));
        $cellhead2->colspan = 2;
        $head[] = $cellhead2;

        $table->head = $head;

        $rows = array();

        //erste Reihe->Überschriften
        $row = new html_table_row();
        $cell = new html_table_cell();
        $cell->text = "";
        $cell->colspan = 2;
        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text = html_writer::tag("p", get_string('LA_todo', 'block_exacomp'));
        $row->cells[] = $cell;

        //$cell = new html_table_cell();
        //$cell->text = html_writer::tag("p", get_string('learning', 'block_exacomp'));
        //$row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = html_writer::tag("p", get_string('LA_enddate', 'block_exacomp'));
        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text = html_writer::tag("p", get_string('LA_student', 'block_exacomp'));
        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text = html_writer::tag("p", get_string('LA_teacher', 'block_exacomp'));
        $row->cells[] = $cell;

        $rows[] = $row;

        foreach($data as $day=>$daydata){
            $row = new html_table_row();
            $cell = new html_table_cell();
            $cell->text = html_writer::tag("p", $day.": ".$daydata['date']);

            $cell->rowspan = count($daydata, COUNT_RECURSIVE)-count($daydata);
            $row->cells[] = $cell;

            foreach($daydata as $subject=>$subjectdata){
                if(strcmp($subject,'date')!=0){
                    if(strcmp($subject, 'no example available')!=0){
                        $cell = new html_table_cell();
                        $cell->text = html_writer::tag("p",$subject);
                        $cell->rowspan = count($subjectdata);

                        $row->cells[] = $cell;
                        foreach($subjectdata as $example){
                            $cell = new html_table_cell();
                            $cell->text = html_writer::tag("p", html_writer::tag("b", $example->desc.":")." ".(($example->numb > 0) ? $example->schooltype.$example->numb : "")." ".$example->title. (($example->cat) ? " (".$example->cat.")" : ""));
                            $row->cells[] = $cell;

                            $cell = new html_table_cell();
                            $cell->text = date("d.m.y", $example->enddate);
                            $row->cells[] = $cell;

                            $cell = new html_table_cell();
                            $grading=block_exacomp_get_grading_scheme($COURSE->id);
                            if($grading == 1){
                                if($example->evaluate == 1){
                                    $cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/ok_16x16.png', 'alt' => '1', 'height' => '16', 'width'=>'16'));
                                }
                                else{
                                    $cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
                                }
                            }else{
                                if($example->evaluate > 0)
                                    $cell->text = $example->evaluate;
                                else
                                    $cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
                            }
                            $row->cells[] = $cell;

                            $cell = new html_table_cell();
                            if($example->tevaluate == 1){
                                $cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/ok_16x16.png', 'alt' => '1', 'height' => '16', 'width'=>'16'));
                            }
                            else{
                                $cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
                            }
                            $row->cells[] = $cell;

                            $rows[] = $row;
                            $row = new html_table_row();
                        }
                    }else{
                        $cell = new html_table_cell();
                        $cell->text = html_writer::tag("p",get_string('LA_no_example', 'block_exacomp'));
                        $cell->colspan = 5;
                        $row->cells[] = $cell;
                        $rows[] = $row;
                        $row = new html_table_row();
                    }
                }
            }
        }

        $table->data = $rows;

        $content = html_writer::tag("div", html_writer::table($table), array("id"=>"exabis_competences_block"));
        return $content;
    }
    public function print_subject_dropdown($schooltypetree, $selectedSubject, $studentid = 0) {
        global $PAGE, $version;
        $content = get_string("choosesubject", "block_exacomp");
        $array = array();
        $options = array();
        
        foreach($schooltypetree as $schooltype){
            $options[$schooltype->title] = array();
            foreach($schooltype->subjects as $subject)
                $options[$schooltype->title][$subject->id] = $subject->title;
            
            $array[] = $options;
            $options = array();
        }
        
        $content .= html_writer::select($array, "lis_subjects",$selectedSubject, false,
                array("onchange" => "document.location.href='".$PAGE->url."&studentid=".$studentid."&subjectid='+this.value;"));
        
        return $content;
    }
    /**
     * Prints 2 select inputs for subjects and topics
     */
    public function print_overview_dropdowns($schooltypetree, $topics, $selectedSubject, $selectedTopic, $students, $selectedStudent = 0, $isTeacher = false) {
        global $PAGE, $COURSE, $USER;

        $content = "";
        /*
        $content = $this->print_subject_dropdown($schooltypetree, $selectedSubject, $selectedStudent);
        $content .= html_writer::empty_tag("br");

        $content .= get_string("choosetopic", "block_exacomp").': ';
        $options = array();
        foreach($topics as $topic){
            $options[$topic->id] = (isset($topic->cattitle)?$topic->cattitle.": " :" ")  . $topic->title;
        }
        $content .= html_writer::select($options, "lis_topics", $selectedTopic, false,
                array("onchange" => "document.location.href='".$PAGE->url."&studentid=".$selectedStudent."&subjectid=".$selectedSubject."&topicid='+this.value;"));
		*/
        
        if($isTeacher){
            $content .= html_writer::empty_tag("br");
            $content .= get_string("choosestudent", "block_exacomp");
            /*
            $options = array();
            $options[0] = get_string('no_student_edit', 'block_exacomp');
            
            foreach($students as $student)
                $options[$student->id] = $student->firstname." ".$student->lastname;
            $options[BLOCK_EXACOMP_SHOW_ALL_STUDENTS] = get_string('allstudents', 'block_exacomp');
            $options[BLOCK_EXACOMP_SHOW_STATISTIC] = get_string('statistic', 'block_exacomp');
            
            $content .= html_writer::select($options, "lis_crosssubs_students", $selectedStudent, false,
                    array("onchange" => "document.location.href='".$PAGE->url."&subjectid=".$selectedSubject."&topicid=".$selectedTopic."&studentid='+this.value;"));
            */
            $content .= block_exacomp_studentselector($students,$selectedStudent,$PAGE->url."&subjectid=".$selectedSubject."&topicid=".$selectedTopic,  BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN);

            $content .= $this->print_edit_mode_button("&studentid=".$selectedStudent."&subjectid=".$selectedSubject."&topicid=".$selectedTopic);
			$url = new moodle_url('/blocks/exacomp/pre_planning_storage.php', array('courseid'=>$COURSE->id, 'creatorid'=>$USER->id));
    		$content .= html_writer::empty_tag('input', array('type'=>'submit', 'id'=>'pre_planning_storage_submit', 'name'=> 'pre_planning_storage_submit', 'value'=>get_string('pre_planning_storage','block_exacomp'), ((block_exacomp_has_items_pre_planning_storage($USER->id, $COURSE->id))?"enabled":"disabled")=>"", 
    			"onclick" => "window.open('".$url->out(false)."','_blank','width=880,height=660, scrollbars=yes'); return false;"));
        
		}    
        
        return $content;
    }
    public function print_edit_mode_button($url) {
    	global $PAGE;
    	
    	$edit = optional_param('editmode', 0, PARAM_BOOL);
    	
    	
    	return html_writer::empty_tag('input', array('type'=>'submit', 'id'=>'edit_mode_submit', 'name'=> 'edit_mode_submit', 'value'=>get_string(($edit) ? 'editmode_off' : 'editmode_on','block_exacomp'),
    			 "onclick" => "document.location.href='".$PAGE->url."&editmode=" . (!$edit).$url."'"));
    }
    public function print_subjects_menu($types,$selectedSubject) {
    	global $PAGE;
    	
    	$edit = optional_param('editmode', 0, PARAM_BOOL);
    	$studentid = optional_param('studentid', BLOCK_EXACOMP_SHOW_ALL_STUDENTS,PARAM_INT);
    	
    	$content = html_writer::start_div('subjects_menu');
    	$content .= html_writer::start_tag('ul');
    	
    	foreach($types as $type) {
    		$content .= html_writer::tag('li',
    				html_writer::link("#",
    						$type->title, array('class' => 'type'))
    		);
    		
    		foreach($type->subjects as $subject)
    			$content .= html_writer::tag('li',
    				html_writer::link($PAGE->url . "&studentid=" . $studentid . "&editmode=" . $edit . "&subjectid=" . $subject->id,
    						$subject->title, array('class' => ($subject->id == $selectedSubject->id) ? 'current' : ''))
    				);
    	}
    	
    	$content .= html_writer::end_tag('ul');
    	$content .= html_writer::end_tag('div');    	
    	return $content;
    }
    public function print_topics_menu($topics,$selectedTopic,$selectedSubject) {
    	   	global $PAGE;
    	
    	$edit = optional_param('editmode', 0, PARAM_BOOL);
    	$studentid = optional_param('studentid', BLOCK_EXACOMP_SHOW_ALL_STUDENTS,PARAM_INT);
    	$subjectid = 
    	
    	$content = html_writer::start_div('topics_menu');
    	$content .= html_writer::start_tag('ul');
    	
    	foreach($topics as $topic) {
    		$content .= html_writer::tag('li',
    				html_writer::link($PAGE->url . "&studentid=" . $studentid . "&editmode=" . $edit . "&subjectid=" . $selectedSubject->id . "&topicid=" . $topic->id,
    						isset($topic->cattitle) ? $topic->cattitle : $topic->title, array('class' => ($topic->id == $selectedTopic->id) ? 'current' : ''))
    				);
    	}
    	
    	$content .= html_writer::end_tag('ul');
    	$content .= html_writer::end_tag('div');    	
    	return $content;
    }
    public function print_overview_metadata_teacher($subject,$topic){

        $table = new html_table();
        $table->attributes['class'] = 'exabis_comp_top';

        $rows = array();

        $row = new html_table_row();

        $cell = new html_table_cell();
        $cell->attributes['class'] = 'comp_grey_97';
        
        $cell->text = html_writer::tag('b', get_string('instruction', 'block_exacomp'))
        .html_writer::tag('p', isset($subject->description) ? $subject->description. '<br/>' : ''  . isset($topic->description) ? $topic->description : '');

        $row->cells[] = $cell;
        $rows[] = $row;
        $table->data = $rows;

        $content = html_writer::table($table);
        $content .= html_writer::empty_tag('br');
        if(isset($subject->description) || isset($topic->description))
            return $content;
    }
    public function print_overview_metadata_student($subject, $topic, $topic_evaluation, $showevaluation, $scheme, $icon = null){
        $table = new html_table();
        $table->attributes['class'] = 'exabis_comp_top';

        $rows = array();

        $row = new html_table_row();

        $cell = new html_table_cell();
        $cell->attributes['class'] = 'comp_grey_97';
        $cell->text = html_writer::tag('b', get_string('requirements', 'block_exacomp'))
        .html_writer::tag('p', $topic->requirement);

        $row->cells[] = $cell;
        $rows[] = $row;

        $row = new html_table_row();

        $cell = new html_table_cell();
        $cell->attributes['class'] = 'comp_grey_97';
        $cell->text = html_writer::tag('b', get_string('forwhat', 'block_exacomp'))
        .html_writer::tag('p', $topic->benefit);

        $row->cells[] = $cell;
        $rows[] = $row;

        $row = new html_table_row();

        $cell = new html_table_cell();
        $cell->attributes['class'] = 'comp_grey_97';
        $cell->text = html_writer::tag('b', get_string('howtocheck', 'block_exacomp'))
        .html_writer::tag('p', $topic->knowledgecheck);
            
        $p_content = get_string('reached_topic', 'block_exacomp');

        if($scheme == 1)
            $p_content .= "S: " . html_writer::checkbox("topiccomp", 1, ((isset($topic_evaluation->student[$topic->id]))?true:false))
            ." Bestätigung L: ".html_writer::checkbox("topiccomp", 1, ((isset($topic_evaluation->teacher[$topic->id]))?true:false), "", array("disabled"=>"disabled"));
        else{
            (isset($topic_evaluation->student[$topic->id]))?$value_student = $topic_evaluation->student[$topic->id] : $value_student = 0;
            (isset($topic_evaluation->teacher[$topic->id]))?$value_teacher = $topic_evaluation->teacher[$topic->id] : $value_teacher = 0;
                
            $p_content .= "S: " . html_writer::checkbox("topiccomp", $scheme, $value_student >= ceil($scheme/2))
            ." Bestätigung L: ". $value_teacher;

        }
            
        if(isset($icon))
            $p_content .= " ".html_writer::span($icon->img, 'exabis-tooltip', array('title'=>s($icon->text)));

        $p_content .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'topiccompid', 'value'=>$topic->id));
        $p_content .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'subjectcompid', 'value'=>$subject->id));
            
        $cell->text .= html_writer::tag('p', $p_content);

        $row->cells[] = $cell;
        $rows[] = $row;

        $table->data = $rows;

        return html_writer::table($table).html_writer::empty_tag('br');
    }
    public function print_overview_metadata($schooltype, $subject, $topic, $cat){
        global $version;
        
        $table = new html_table();
        $table->attributes['class'] = 'exabis_comp_info';

        $rows = array();

        $row = new html_table_row();

        $cell = new html_table_cell();
        $cell->text = html_writer::span(get_string('subject_singular', 'block_exacomp'), 'exabis_comp_top_small')
        . html_writer::tag('b', $schooltype);

        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text = html_writer::span(get_string('comp_field_idea', 'block_exacomp'), 'exabis_comp_top_small')
        . html_writer::tag('b', (isset($subject->numb) && strcmp($subject->numb, '')!=0)?$subject->numb." - ".$subject->title:$subject->title);

        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text = html_writer::span(get_string('comp', 'block_exacomp'), 'exabis_comp_top_small')
        . html_writer::tag('b', $topic->title);

        $row->cells[] = $cell;

        if($version){
            $cell = new html_table_cell();
            $cell->text = html_writer::span(get_string('progress', 'block_exacomp'), 'exabis_comp_top_small')
            . html_writer::tag('b', (($cat)?$cat->title:''));
    
            $row->cells[] = $cell;
    
            $cell = new html_table_cell();
            $cell->text = html_writer::span(get_string('tab_competence_overview', 'block_exacomp'), 'exabis_comp_top_small')
            . html_writer::tag('b', substr($schooltype, 0,1).$subject->numb.(($cat)?".".$cat->sourceid:''));
    
            $row->cells[] = $cell;
        }
        $rows[] = $row;
        $table->data = $rows;

        $content = html_writer::table($table);

        return $content;
    }

    public function print_competence_grid_legend() {
        $content = html_writer::span("&nbsp;&nbsp;&nbsp;&nbsp;","competenceyellow");
        $content .= ' '.get_string("selfevaluation","block_exacomp").' ';
        $content .= html_writer::span("&nbsp;&nbsp;&nbsp;&nbsp;","competenceok");
        $content .= ' '.get_string("teacherevaluation","block_exacomp").' ';
        return $content;
    }
    public function print_competence_grid_reports_dropdown() {
    	global $PAGE;
    	
    	$options = array();
    	
    	$options[BLOCK_EXACOMP_REPORT1] = get_string("report_competence","block_exacomp");
    	$options[BLOCK_EXACOMP_REPORT2] = get_string("report_detailcompetence","block_exacomp");
    	$options[BLOCK_EXACOMP_REPORT3] = get_string("report_examples","block_exacomp");
    	
    	$url = new block_exacomp_url($PAGE->url);
		$url->param("subjectid",optional_param("subjectid", 0, PARAM_INT));
		$url->param("studentid",optional_param("studentid", 0, PARAM_INT));
		
    	return get_string('reports','block_exacomp') . ": " . html_writer::select($options, "exacomp_competence_grid_report", optional_param("report", BLOCK_EXACOMP_REPORT1, PARAM_INT), true, array("data-url"=>$url));
    	 
    }
    public function print_competence_overview_LIS_student_topics($subs, &$row, &$columns, &$column_count, $scheme, $profoundness = false){
        global $USER, $COURSE;
        $supported = block_exacomp_get_supported_modules();
        foreach($subs as $topic){
            if(isset($topic->subs))
                $this->print_competence_overview_LIS_student_topics($topic->subs);

            if(isset($topic->descriptors)){
                foreach($topic->descriptors as $descriptor){
                    $cell = new html_table_cell();
                    $cell->attributes['class'] = 'exabis_comp_top_student';
                    $cell->attributes['title'] = $descriptor->title;
                    $cell->text = $columns[$column_count].html_writer::empty_tag('br');

                    $columns[$column_count] = new stdClass();
                    $columns[$column_count]->descriptor = $descriptor->id;

                    if($scheme == 1)
                        $cell->text .= "L:".$this->generate_checkbox('data', $descriptor->id, 'competencies', $USER, "teacher", $scheme, true)
                        .html_writer::empty_tag('br')
                        ."S:".$this->generate_checkbox('data', $descriptor->id, 'competencies', $USER, "student", $scheme);
                    else
                        $cell->text .= 'L:'.$this->generate_select('data', $descriptor->id, 'competencies', $USER, "teacher", $scheme, true,$profoundness)
                        .html_writer::empty_tag('br')
                        ."S:".$this->generate_select('data', $descriptor->id, 'competencies', $USER,"student", $scheme,false,$profoundness);

                    //$activities = block_exacomp_get_activities($descriptor->id, $COURSE->id);
                    $cm_mm = block_exacomp_get_course_module_association($COURSE->id);
                    $course_mods = get_fast_modinfo($COURSE->id)->get_cms();

                    if(isset($data->cm_mm->competencies[$descriptor->id])) {
                        $activities_student = array();
                        foreach($cm_mm->competencies[$descriptor->id] as $cmid)
                            $activities_student[] = $course_mods[$cmid];
                        if($activities_student && $stdicon = block_exacomp_get_icon_for_user($activities_student, $USER, $supported)){
                            $cell->text .= html_writer::empty_tag('br')
                            .html_writer::tag('span', $stdicon->img, array('title'=>$stdicon->text, 'class'=>'exabis-tooltip'));
                        }
                    }
                    $row->cells[] = $cell;
                    $column_count++;
                }
            }
        }
    }
public function print_competence_grid($niveaus, $skills, $topics, $data, $selection = array(), $courseid = 0,$studentid=0) {
        global $CFG, $version, $DB;

        $headFlag = false;
        
        $context = context_course::instance($courseid);
        $role = block_exacomp_is_teacher($context) ? block_exacomp::ROLE_TEACHER : block_exacomp::ROLE_STUDENT;
        $editmode = (($studentid == 0 || $studentid == BLOCK_EXACOMP_SHOW_STATISTIC) && $role == block_exacomp::ROLE_TEACHER) ? true : false;

        $table = new html_table();
        $table->attributes['class'] = 'competence_grid';
        $head = array();

        $schema = ($courseid == 0) ? 1 : block_exacomp_get_grading_scheme($courseid);
        $satisfied = ceil($schema/2);
        
        $profoundness = block_exacomp_get_settings_by_course($courseid)->profoundness;

        $spanningNiveaus = $DB->get_records(block_exacomp::DB_NIVEAUS,array('span' => 1));
        //calculate the col span for spanning niveaus
        $spanningColspan = block_exacomp_calculate_spanning_niveau_colspan($niveaus, $spanningNiveaus);
        
        $report = optional_param('report', BLOCK_EXACOMP_REPORT1, PARAM_INT);
        
        $rows = array();

        foreach($data as $skillid => $skill) {

            if(isset($skills[$skillid])) {
                $row = new html_table_row();
                $cell1 = new html_table_cell();
                $cell1->text = html_writer::tag("span",html_writer::tag("span",$skills[$skillid],array('class'=>'rotated-text__inner-header')),array('class'=>'rotated-text-header'));
                $cell1->attributes['class'] = 'skill';
                $cell1->rowspan = count($skill)+1;
                $row->cells[] = $cell1;
                //
                $rows[] = $row;

                if(!$headFlag)
                    $head[] = "";
            }

            if(!$headFlag) {
                $head[] = "";
                $head = array_merge($head,array_diff_key($niveaus, $spanningNiveaus));
                $table->head = $head;
                $headFlag = true;
            }

            foreach($skill as $topicid => $topic) {
                $row = new html_table_row();

                $cell2 = new html_table_cell();
                $cell2->text = html_writer::tag("span",html_writer::tag("span",$topics[$topicid],array('class'=>'rotated-text__inner')),array('class'=>'rotated-text'));
                $cell2->attributes['class'] = 'topic';
                $row->cells[] = $cell2;

                foreach($niveaus as $niveauid => $niveau) {
                    
                    if(isset($data[$skillid][$topicid][$niveauid])) {
                        $cell = new html_table_cell();
                        $compdiv = "";
                        $allTeachercomps = true;
                        $allStudentcomps = true;
                        foreach($data[$skillid][$topicid][$niveauid] as $descriptor) {
                            $compString = "";
                            
                            if(!isset($descriptor->visible))
                                $descriptor->visible = $DB->get_field(block_exacomp::DB_DESCVISIBILITY, 'visible', array('courseid'=>$courseid, 'descrid'=>$descriptor->id, 'studentid'=>0));
                            
                            // Check visibility
                            $descriptor_used = block_exacomp_descriptor_used($courseid, $descriptor, ($studentid != BLOCK_EXACOMP_SHOW_STATISTIC) ? $studentid : 0);
                            $visible = block_exacomp_check_descriptor_visibility($courseid, $descriptor, ($studentid != BLOCK_EXACOMP_SHOW_STATISTIC) ? $studentid : 0);
                            $visible_css = block_exacomp_get_descriptor_visible_css($visible, $role);
                            
                            /*
                            if (block_exacomp_is_teacher($context)) {
                                if(isset($descriptor->teachercomp) && array_key_exists($descriptor->topicid, $selection)) {
                                    $compString .= "L: ";
                                    if($schema == 1) {
                                        $compString .= html_writer::checkbox("data-".$descriptor->id."-".$studentid."-teacher", 1,$descriptor->teachercomp, '',($visible) ? array() : array("disabled"=>"disabled")).'&nbsp; ';
                                        
                                        $compString .= " S: ". html_writer::checkbox("data".$topicid."-".$descriptor->id."-student", 1,($descriptor->studentcomp >= $satisfied),"",array("disabled"=>"disabled")).'&nbsp; ';
                                    }else {
                                        $options = array();
                                        for($i=0;$i<=$schema;$i++)
                                            $options[] = (!$profoundness) ? $i : get_string('profoundness_'.$i,'block_exacomp');

                                        $name = "data-".$descriptor->id."-".$studentid."-teacher";
                                        $compString .= html_writer::select($options, $name, $descriptor->teachercomp, false);

                                        //$compString .= "&nbsp;S: " . html_writer::select($options,"student".$name, $descriptor->studentcomp,false,array("disabled"=>"disabled")).'&nbsp; ';
                                        $compString .= "&nbsp;S: " . html_writer::checkbox("student".$name, 0,$descriptor->studentcomp >= $satisfied,"",array("disabled"=>"disabled")).'&nbsp; ';
                                    }
                                }

                            } else if(has_capability('block/exacomp:student', $context) && array_key_exists($descriptor->topicid, $selection)) {
                                $compString.="S: ";
                                if($schema == 1) {
                                    $compString .= html_writer::checkbox("data-".$descriptor->id."-".$studentid."-student", 1,$descriptor->studentcomp).'&nbsp; ';
                                        
                                    $compString .= "&nbsp;L: " . html_writer::checkbox("data-".$studentid."-".$descriptor->id."-teacher", 0,$descriptor->teachercomp >= $satisfied,"",array("disabled"=>"disabled")).'&nbsp; ';
                                } else {
                                    $options = array();
                                    for($i=0;$i<=$schema;$i++)
                                        $options[] = $i;

                                    $name = "data[".$topicid."][".$descriptor->id."][student]";
                                    //$compString .= html_writer::select($options, $name, $descriptor->studentcomp, false);
                                    $compString .= html_writer::checkbox("data-".$descriptor->id."-".$studentid."-student", $schema,$descriptor->studentcomp).'&nbsp; ';

                                    $compString .= "&nbsp;L: " . (($descriptor->teachercomp) ? $descriptor->teachercomp : 0);
                                }
                            }*/

                            /*
                            if(isset($descriptor->icon))
                                $compString .= $descriptor->icon;
							*/

                            $text = $descriptor->title;
                            if(array_key_exists($descriptor->topicid, $selection)) {
                                $text = html_writer::link(new moodle_url("/blocks/exacomp/assign_competencies.php",array("courseid"=>$courseid,"subjectid"=>$topicid,"topicid"=>$descriptor->id,"studentid"=>$studentid)),$text,array("id" => "competence-grid-link-".$descriptor->id,"class" => ($visible) ? '' : 'deactivated'));
                            }

                            /*if(isset($descriptor->examples)) {
                                $text .= '<br/>';
                                foreach($descriptor->examples as $example) {
                                    $img = '<img src="pix/i_11x11.png" alt="Beispiel" />';
                                    
                                    if($example->task)
                                        $text .= "<a target='_blank' alt='".$example->title."' title='".$example->title."' href='".$example->task."'>".$img."</a>";
                                    if($example->externalurl)
                                        $text .= "<a target='_blank' alt='".$example->title."' title='".$example->title."' href='".$example->externalurl."'>".$img."</a>";
                                }
                            }*/
                            if(isset($descriptor->children) && count($descriptor->children) > 0 && !$version) {
                                $children = '<ul class="childdescriptors">';
                                foreach($descriptor->children as $child)
                                    $children .= '<li>' . $child->title . '</li>';
                                $children .= '</ul>';
                            }
                            $compString .= $text;

                            if(isset($descriptor->children) && count($descriptor->children) > 0 && !$version)
                                $compString .= $children;

                            $cssClass = "content";
                            if($descriptor->parentid > 0)
                                $cssClass .= ' child';
                            
                            if(isset($descriptor->teachercomp) && $descriptor->teachercomp)
                            	$cssClass = "contentok";
                            
                            // Check visibility
                            /*
                            if(!$descriptor_used && array_key_exists($descriptor->topicid, $selection) ){
                                if($editmode || ($descriptor->visible == 1 && $role == block_exacomp::ROLE_TEACHER)){
                                    $compString .= $this->print_visibility_icon($visible, $descriptor->id);
                                }
                            } */
                            $compdiv .= html_writer::tag('div', $compString,array('class'=>$cssClass));
                            
                            if($report != BLOCK_EXACOMP_REPORT1)    
                            if(array_key_exists($descriptor->topicid, $selection) && $visible && $studentid != 0) {
                                
                                $compdiv .= html_writer::start_div('crosssubjects');
                                $table_head = new html_table_row();
                                $table_head->attributes['class'] = 'statistic_head';
                                
                                $scheme = block_exacomp_get_grading_scheme($courseid);
                                $table_head->cells[] = new html_table_cell("");
                                if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
                                    $table_head->cells[] = new html_table_cell("&Sigma;");
                                for($i=0;$i<=$scheme;$i++)
                                    $table_head->cells[] = $i > 0 ? new html_table_cell($i) : new html_table_cell("nE");
                                $table_head->cells[] = new html_table_cell("oB");
                                $table_head->cells[] = new html_table_cell("iA");
                                if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
                                    $table_head->cells[] = new html_table_cell("Abschluss");
                                        
                                $crossubject_statistic = new html_table();
                                $crossubject_statistic_rows = array();
                                $crossubject_statistic_rows[] = $table_head;
                                
                                $crosssubjects = block_exacomp_get_cross_subjects_for_descriptor($courseid, $descriptor->id);
                                $statistic_type = ($report == BLOCK_EXACOMP_REPORT2) ? BLOCK_EXACOMP_DESCRIPTOR_STATISTIC : BLOCK_EXACOMP_EXAMPLE_STATISTIC;
                                    
                                foreach($crosssubjects as $crosssubject) {
                                    if($statistic_type == BLOCK_EXACOMP_DESCRIPTOR_STATISTIC)
                                        list($total, $gradings, $notEvaluated, $inWork,$totalGrade) = block_exacomp_get_descriptor_statistic_for_crosssubject($courseid, $crosssubject->id, $studentid);
                                    else
                                        list($total, $gradings, $notEvaluated, $inWork,$totalGrade) = block_exacomp_get_example_statistic_for_crosssubject($courseid, $crosssubject->id, $studentid);
                                        
                                    $table_entry = new html_table_row();
                                    $table_entry->cells[] = new html_table_cell(html_writer::link(new moodle_url("/blocks/exacomp/cross_subjects.php", array("courseid" => $courseid, "crosssubjid" => $crosssubject->id)), $crosssubject->title));
                                    if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
                                        $table_entry->cells[] = new html_table_cell($total);
                                    foreach($gradings as $key => $grading)
                                        $table_entry->cells[] = new html_table_cell($grading);
                                    $table_entry->cells[] = new html_table_cell($notEvaluated);
                                    $table_entry->cells[] = new html_table_cell($inWork);
                                    if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
                                        $table_entry->cells[] = new html_table_cell($totalGrade);
                                    
                                    $crossubject_statistic_rows[] = $table_entry;
                                }
                                if($statistic_type == BLOCK_EXACOMP_DESCRIPTOR_STATISTIC)
                                    list($total, $gradings, $notEvaluated, $inWork,$totalGrade) = block_exacomp_get_descriptor_statistic($courseid, $descriptor->id, $studentid);
                                else
                                    list($total, $gradings, $notEvaluated, $inWork,$totalGrade) = block_exacomp_get_example_statistic_for_descriptor($courseid, $descriptor->id, $studentid);
                                        
                                $table_entry = new html_table_row();
                                $table_entry->cells[] = new html_table_cell("LWL " . block_exacomp_get_descriptor_numbering($descriptor));
                                if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
                                    $table_entry->cells[] = new html_table_cell($total);
                                foreach($gradings as $key => $grading)
                                    $table_entry->cells[] = new html_table_cell($grading);
                                $table_entry->cells[] = new html_table_cell($notEvaluated);
                                $table_entry->cells[] = new html_table_cell($inWork);
                                if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
                                    $table_entry->cells[] = new html_table_cell($totalGrade);
                                    
                                $crossubject_statistic_rows[] = $table_entry;
                                
                                $crossubject_statistic->data = $crossubject_statistic_rows;
                                $compdiv .= html_writer::table($crossubject_statistic);
                                $compdiv .= html_writer::end_div();
                            }
                        }

                        // apply colspan for spanning niveaus
                        if(array_key_exists($niveauid,$spanningNiveaus)) {
                            $cell->colspan = $spanningColspan;
                        }
                        
                        $cell->text = $compdiv;
                        $row->cells[] = $cell;
                        
                        // do not print other cells for spanning niveaus
                        if(array_key_exists($niveauid,$spanningNiveaus))
                            break;
                        
                    } elseif(!array_key_exists($niveauid,$spanningNiveaus))
                        $row->cells[] = "";
                }
                $rows[] = $row;
            }
            //$rows[] = $row;
        }
        $table->data = $rows;

        return html_writer::tag("div", html_writer::table($table), array("id"=>"exabis_competences_block"));
    }
    public function print_competence_overview_form_start($selectedTopic=null, $selectedSubject=null, $studentid=null){
        global $PAGE, $COURSE;
        $url_params = array();
        $url_params['action'] = 'save';
        if(isset($selectedTopic))
            $url_params['topicid'] = $selectedTopic->id;
        if(isset($selectedSubject))
            $url_params['subjectid'] = $selectedSubject->id;
        if(isset($studentid))
            $url_params['studentid'] = $studentid;
                
        $url = new moodle_url($PAGE->url, $url_params);
        return html_writer::start_tag('form',array('id'=>'assign-competencies', "action" => $url, 'method'=>'post'));
    }
    public function print_competence_overview_LIS_student($subjects, $courseid, $showevaluation, $scheme, $examples){
        global $USER, $DB, $PAGE, $COURSE;

        $columns = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15');

        $table = new html_table();
        $table->attributes['class'] = 'exabis_comp_comp';
        $rows = array();
        $row = new html_table_row();
        $row->attributes['class'] = 'highlight';

        $cell = new html_table_cell();
        $cell->colspan = 4;
        $cell->text = html_writer::tag('h5', 'Teilkompetenzen', array('style'=>'float:right;'));

        $row->cells[] = $cell;

        $column_count = 0;
        //print header
        foreach($subjects as $subject){
            $this->print_competence_overview_LIS_student_topics($subject->subs, $row, $columns, $column_count, $scheme, block_exacomp_get_settings_by_course($courseid)->profoundness);
        }
        $rows[] = $row;

        //print subheader
        if(!empty($examples)){
            $row = new html_table_row();
            $cell = new html_table_cell();
            $cell->text = html_writer::tag('b', 'Lernmaterialien');
            $row->cells[] = $cell;
    
            $cell = new html_table_cell();
            $row->cells[] = $cell;
    
            $cell = new html_table_cell();
            $cell->text = 'In Arbeit';
            $row->cells[] = $cell;
    
            $cell = new html_table_cell();
            $cell->text = 'abgeschlossen';
            $row->cells[] = $cell;
    
            $cell = new html_table_cell();
            $cell->colspan = $column_count;
            $row->cells[] = $cell;
    
            $rows[] = $row;
        }
    //print examples
        foreach($examples as $example){
            $row = new html_table_row();
            $cell = new html_table_cell();
            $cell->text = $example->title;

            //$img = html_writer::img('pix/i_11x11.png', 'Beispiel');
            $img = html_writer::tag('img','',array('src'=>'pix/i_11x11.png','alt'=>'Beispiel'));
            
            if($task = block_exacomp_get_file_url($example, 'example_task'))
				$cell->text .= html_writer::link($task, $img, array('target'=>'_blank'));
			elseif(isset($example->externalurl))
            $cell->text .= html_writer::link($example->externalurl, $img);

            $row->cells[] = $cell;

            $cell = new html_table_cell();
            $cell->text = (isset($example->tax))?$example->tax:'';

            $row->cells[] = $cell;

            $exampleInfo = $DB->get_record(block_exacomp::DB_EXAMPLEEVAL, array("exampleid" => $example->id, "studentid" => $USER->id, "courseid" => $COURSE->id));
            
            $cell = new html_table_cell();
            //$cell->text = html_writer::img('pix/subjects_topics.gif', "edit", array('onclick'=>'AssignVisibility('.$example->id."2".')', 'style'=>'cursor:pointer;'));
            $cell->text = html_writer::tag('img','',array('src'=>'pix/subjects_topics.gif', 'alt'=>"edit", 'onclick'=>'AssignVisibility('.$example->id."2".')', 'style'=>'cursor:pointer;'));
            
            $dates = (isset($exampleInfo->starttime) && isset($exampleInfo->endtime))?date("d.m.Y", $exampleInfo->starttime)
            ." - ".date("d.m.Y", $exampleInfo->endtime):"";
            $div_1 = html_writer::div($dates, '', array('id'=>'exabis_assign_student_data'.$example->id."2"));

            $cell->text .= $div_1;

            $content = get_string('assignfrom','block_exacomp');
            $content .= ' '.html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples[' . $example->id . '][' . $USER->id . '][starttime]', 'disabled',
                    'value' => (isset($exampleInfo->starttime) ? date("Y-m-d",$exampleInfo->starttime) : null)));
            $content .= ' '.html_writer::link(new moodle_url($PAGE->url, array('exampleid'=>$example->id, 'deletestart'=>1)),
                    html_writer::tag('img','',array('src'=>'pix/x_11x11.png','alt'=>'delete')));
            $content .= html_writer::empty_tag('br');
            $content .= get_string('assignuntil','block_exacomp');
            $content .= ' '.html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples[' . $example->id . '][' . $USER->id . '][endtime]', 'disabled',
                    'value' => (isset($exampleInfo->endtime) ? date("Y-m-d",$exampleInfo->endtime) : null)));
            $content .= ' '.html_writer::link(new moodle_url($PAGE->url, array('exampleid'=>$example->id, 'deleteend'=>1)),
                    html_writer::tag('img','',array('src'=>'pix/x_11x11.png','alt'=>'delete')));

            $div_2 = html_writer::div($content, 'exabis_assign_student', array('id'=>'exabis_assign_student'.$example->id."2"));
            $cell->text .= $div_2;

            $row->cells[] = $cell;

            $cell = new html_table_cell();
            $options = array();
            $options['self'] = get_string('assignmyself','block_exacomp');
            $options['studypartner'] = get_string('assignlearningpartner','block_exacomp');
            $options['studygroup'] = get_string('assignlearninggroup','block_exacomp');
            $options['teacher'] = get_string('assignteacher','block_exacomp');

            //$cell->text = html_writer::img('pix/subjects_topics.gif', 'edit', array('onclick'=>'AssignVisibility('.$example->id."1".')', 'style'=>'cursor:pointer;'));
            $cell->text = html_writer::tag('img','',array('src' => 'pix/subjects_topics.gif', 'alt'=>'edit', 'onclick'=>'AssignVisibility('.$example->id."1".')', 'style'=>'cursor:pointer;'));
            
            $content = $this->generate_checkbox('dataexamples', $example->id, 'examples', $USER, "student", $scheme)
            . html_writer::select($options, 'dataexamples[' . $example->id . '][' . $USER->id . '][studypartner]', (isset($exampleInfo->studypartner) ? $exampleInfo->studypartner : null), false);

            $div_2 = html_writer::div($content, 'exabis_assign_student', array('id'=>'exabis_assign_student'.$example->id."1"));
            $cell->text .= $div_2;

            $row->cells[] = $cell;

            for($i=0; $i<$column_count; $i++){
                $cell = new html_table_cell();

                if(isset($example->descriptors[$columns[$i]->descriptor])){
                    if(isset($exampleInfo->teacher_evaluation) && $exampleInfo->teacher_evaluation>0){
                        $cell->attributes['class'] = 'exabis_comp_teacher_assigned';
                        $cell->text = '';
                        if(isset($exampleInfo->student_evaluation) && $exampleInfo->student_evaluation>0)
                            $cell->text = " S ";
                        $cell->text = " L: ".$exampleInfo->teacher_evaluation;
                    }
                    elseif(isset($exampleInfo->student_evaluation) && $exampleInfo->student_evaluation>0){
                        $cell->attributes['class'] = 'exabis_comp_student_assigned';
                        $cell->text = " S";
                    }elseif(isset($exampleInfo->starttime) && time() > $exampleInfo->starttime){
                        $cell->attributes['class'] = 'exabis_comp_student_started';
                        $cell->text = " X";
                    }else{
                        $cell->attributes['class'] = 'exabis_comp_student_not';
                        $cell->text = " X";
                    }
                }
                    
                $row->cells[] = $cell;

            }
            $rows[] = $row;
        }

        $table->data = $rows;

        $submit = html_writer::div(html_writer::empty_tag('input', array('name'=>'btn_submit', 'type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));

        $script_content = 'function AssignVisibility(id)
        {
        if(document.getElementById("exabis_assign_student"+id).style.display!="inherit"){
        document.getElementById("exabis_assign_student"+id).style.display = "inherit";
        document.getElementById("exabis_assign_student_data"+id).style.display ="none";
    }else {
    document.getElementById("exabis_assign_student"+id).style.display = "none";
    document.getElementById("exabis_assign_student_data"+id).style.display ="inherit";
    }

    }';
        $script = html_writer::tag('script', $script_content, array('type'=>'text/javascript'));
        $innerdiv = html_writer::div($script.html_writer::table($table).$submit, 'exabis_comp_comp_table');
        $div = html_writer::div($innerdiv, "exabis_competencies_lis", array("id"=>"exabis_competences_block"));
        return $div.html_writer::end_tag('form');
        //return html_writer::tag('form', $div, array('id'=>'assign-competencies', 'action'=>new moodle_url($PAGE->url, array('courseid'=>$courseid, 'action'=>'save')), 'method'=>'post'));
    }

    public function print_profoundness($subjects, $courseid, $students, $role) {
        $table = new html_table();
        $rows = array();
        $table->attributes['class'] = 'exabis_comp_comp';
        
        // 1st header row
        $headerrow = new html_table_row();
        
        $cell = new html_table_cell();
        $cell->rowspan = 2;
        $cell->colspan = 2;
        $cell->text = get_string('profoundness_description','block_exacomp');
        $headerrow->cells[] = $cell;
        
        $cell = new html_table_cell();
        $cell->text = get_string('profoundness_basic','block_exacomp');
        $cell->colspan = 2;
        $headerrow->cells[] = $cell;
        
        $cell = new html_table_cell();
        $cell->text = get_string('profoundness_extended','block_exacomp');
        $cell->colspan = 2;
        $headerrow->cells[] = $cell;
        
        $rows[] = $headerrow;

        // 2nd header row
        $headerrow = new html_table_row();
        
        $cell = new html_table_cell();
        $cell->text = get_string('profoundness_mainly','block_exacomp');
        $headerrow->cells[] = $cell;
        
        $cell = new html_table_cell();
        $cell->text = get_string('profoundness_entirely','block_exacomp');
        $headerrow->cells[] = $cell;
        
        $cell = new html_table_cell();
        $cell->text = get_string('profoundness_mainly','block_exacomp');
        $headerrow->cells[] = $cell;
        
        $cell = new html_table_cell();
        $cell->text = get_string('profoundness_entirely','block_exacomp');
        $headerrow->cells[] = $cell;
        
        $rows[] = $headerrow;
        
        if(block_exacomp_exaportexists())
            $eportfolioitems = block_exacomp_get_eportfolioitem_association($students);
        else
            $eportfolioitems = array();
        
        foreach($subjects as $subject) {
            if(!$subject->subs)
                continue;
            
            /* TOPICS */
            //for every topic
            $data = (object)array(
                    'rowgroup' => &$rowgroup,
                    'courseid' => $courseid,
                    'showevaluation' => 0,
                    'role' => $role,
                    'scheme' => 2,
                    'profoundness' => 1,
                    'cm_mm' => block_exacomp_get_course_module_association($courseid),
                    'eportfolioitems' => $eportfolioitems,
                    'exaport_exists'=>block_exacomp_exaportexists(),
                    'course_mods' => get_fast_modinfo($courseid)->get_cms(),
                    'selected_topicid' => null,
                    'supported_modules'=>block_exacomp_get_supported_modules(),
                    'showalldescriptors' => block_exacomp_get_settings_by_course($courseid)->show_all_descriptors
            );
            $this->print_topics($rows, 0, $subject->subs, $data, $students, '', true);
            $table->data = $rows;
        }
        
        $table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
        $table_html .= html_writer::div(html_writer::tag("input", "", array("name" => "btn_submit", "type" => "submit", "value" => get_string("save_selection", "block_exacomp"))),'', array('id'=>'exabis_save_button'));
        $table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));
        
        return $table_html.html_writer::end_tag('form');
    }
    public function print_competence_overview($subjects, $courseid, $students, $showevaluation, $role, $scheme = 1, $lis_singletopic = false, $crosssubs = false, $crosssubjid = 0, $statistic = false) {
        global $PAGE, $version;

        $editmode = (!$students && $role == block_exacomp::ROLE_TEACHER) ? true : false;
        $rowgroup = ($lis_singletopic) ? null : 0;
        //$rowgroup=0;
        $table = new html_table();
        $rows = array();
        $studentsColspan = $showevaluation ? 2 : 1;
        $table->attributes['class'] = 'exabis_comp_comp';

        if(block_exacomp_exaportexists())
            $eportfolioitems = block_exacomp_get_eportfolioitem_association($students);
        else
            $eportfolioitems = array();

        /* SUBJECTS */
        $first = true;
        $course_subs = block_exacomp_get_subjects_by_course($courseid);
        
        foreach($subjects as $subject) {
            if(!$subject->subs)
                continue;
                
            if($first){
                //for every subject
                $subjectRow = new html_table_row();
                $subjectRow->attributes['class'] = 'highlight';
    
                //subject-title
                $title = new html_table_cell();
                $title->colspan = 2;
                
                if($crosssubs)
                    $title->text = html_writer::tag("b", get_string('comps_and_material', 'block_exacomp'));
                else
                    $title->text = html_writer::tag("b", $subject->title);
    
                $subjectRow->cells[] = $title;
            }
            
            $nivCell = new html_table_cell();
            $nivCell->text = get_string('niveau', 'block_exacomp');

            if($first)
                $subjectRow->cells[] = $nivCell;
                
            $studentsCount = 0;
            
            if(!$statistic){
                foreach($students as $student) {
                    $studentCell = new html_table_cell();
                    $columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
    
                    $studentCell->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
                    $studentCell->colspan = $studentsColspan;
                    $studentCell->text = fullname($student);
    
                    if($first)
                        $subjectRow->cells[] = $studentCell;
                }
            }else{
                $groupCell = new html_table_cell();
                $groupCell->text = get_string('groupsize', 'block_exacomp').count($students);

                if($first)
                    $subjectRow->cells[] = $groupCell;
            }
            if($first)
                $rows[] = $subjectRow;

            if($showevaluation) {
                $studentsCount = 0;

                $evaluationRow = new html_table_row();
                $emptyCell = new html_table_cell();
                $emptyCell->colspan = 3;
                $evaluationRow->cells[] = $emptyCell;

                if(!$statistic){
                    foreach($students as $student) {
                        $columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
    
                        $firstCol = new html_table_cell();
                        $firstCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
                        $secCol = new html_table_cell();
                        $secCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
    
                        if($role == block_exacomp::ROLE_TEACHER) {
                            $firstCol->text = get_string('studentshortcut','block_exacomp');
                            $secCol->text = get_string('teachershortcut','block_exacomp');
                        } else {
                            $firstCol->text = get_string('teachershortcut','block_exacomp');
                            $secCol->text = get_string('studentshortcut','block_exacomp');
                        }
    
                        $evaluationRow->cells[] = $firstCol;
                        $evaluationRow->cells[] = $secCol;
                    }
                }
                $rows[] = $evaluationRow;
            }
                
            /* TOPICS */
            //for every topic
            $data = (object)array(
                    'rowgroup' => &$rowgroup,
                    'courseid' => $courseid,
                    'showevaluation' => $showevaluation,
                    'role' => $role,
                    'scheme' => $scheme,
                    'profoundness' => block_exacomp_get_settings_by_course($courseid)->profoundness,
                    'cm_mm' => block_exacomp_get_course_module_association($courseid),
                    'eportfolioitems' => $eportfolioitems,
                    'exaport_exists'=>block_exacomp_exaportexists(),
                    'course_mods' => get_fast_modinfo($courseid)->get_cms(),
                    'selected_topicid' => null,
                    'supported_modules'=>block_exacomp_get_supported_modules(),
                    'showalldescriptors' => block_exacomp_get_settings_by_course($courseid)->show_all_descriptors
            );
            $this->print_topics($rows, 0, $subject->subs, $data, $students, '', false, $editmode, $statistic);
            
            //total evaluation crosssub row
            if($crosssubs && !$editmode && !$statistic){
                $student = array_values($students)[0];
                $studentid = $student->id;
        
                $totalRow = new html_table_row();
                $totalRow->attributes['class'] = 'highlight';
                $firstCol = new html_table_cell();
                $firstCol->text = get_string('total', 'block_exacomp');
                $totalRow->cells[] = $firstCol;
                
                $totalRow->cells[] = new html_table_cell();
                
                $nivCell = new html_table_cell();
                $nivCell->text = "";
                $totalRow->cells[] = $nivCell;
                
                if($showevaluation){
                    $studentevalCol = new html_table_cell();
                    if($scheme == 1) {
                        $studentevalCol->text = $this->generate_checkbox('datacrosssubs', $crosssubjid, 'crosssubs', $student, 'student', $scheme, true);
                    }else{
                        $studentevalCol->text = $this->generate_select('datacrosssubs', $crosssubjid, 'crosssubs', $student, 'student', $scheme, true);
                    }
                    
                    $totalRow->cells[] = $studentevalCol;
                }
                foreach($students as $student){
                    $teacherevalCol = new html_table_cell();
                    if($scheme == 1) {
                        $teacherevalCol->text = $this->generate_checkbox('datacrosssubs', $crosssubjid, 'crosssubs', $student, 'teacher', $scheme, false);
                    }else{
                        $teacherevalCol->text = $this->generate_select('datacrosssubs', $crosssubjid, 'crosssubs', $student, 'teacher', $scheme, false);
                    }
                    $totalRow->cells[] = $teacherevalCol;
                }
                
                $rows[] = $totalRow;
            }
            $table->data = $rows;
            $first = false;
        }

        $table_html = html_writer::table($table);
        
        if($crosssubs && $role == block_exacomp::ROLE_TEACHER && !$students)
            $table_html .= html_writer::div(html_writer::tag("input", "", array("id"=>"btn_submit", "name" => "btn_submit", "type" => "submit", "value" => get_string("save_selection", "block_exacomp")))
            .html_writer::tag("input", "", array("id"=>"save_as_draft", "name" => "save_as_draft", "type" => "submit", "value" => get_string("save_as_draft", "block_exacomp")))
            .html_writer::tag("input", "", array("id"=>"share_crosssub", "name"=>"share_crosssub", "type"=>"submit", "value"=>get_string("share_crosssub", "block_exacomp")))
            .html_writer::tag("input", "", array("id"=>"delete_crosssub", "name"=>"delete_crosssub", "type"=>"submit", "value"=>get_string("delete_crosssub", "block_exacomp"), 'message'=>get_string('confirm_delete', 'block_exacomp'))),'', array('id'=>'exabis_save_button'));
        
        else
            $table_html .= html_writer::div(html_writer::tag("input", "", array("id"=>"btn_submit", "name" => "btn_submit", "type" => "submit", "value" => get_string("save_selection", "block_exacomp"))),'', array('id'=>'exabis_save_button'));
        
        $table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));

        return $table_html.html_writer::end_tag('form');
    }

    public function print_topics(&$rows, $level, $topics, &$data, $students, $rowgroup_class = '', $profoundness = false, $editmode = false, $statistic = false) {
        
        global $version;
        $topicparam = optional_param('topicid', 0, PARAM_INT);
        $padding = $level * 20 + 12;
        $evaluation = ($data->role == block_exacomp::ROLE_TEACHER) ? "teacher" : "student";

        foreach($topics as $topic) {
            
            list($outputid, $outputname) = block_exacomp_get_output_fields($topic);
            $studentsCount = 0;
            $studentsColspan = 1;

            $hasSubs = (!empty($topic->subs) || !empty($topic->descriptors) && (!$version));

            if ($hasSubs && !is_null($data->rowgroup)) {
                $data->rowgroup++;
                $this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$data->rowgroup.' '.$rowgroup_class;
                $sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$data->rowgroup.' '.$rowgroup_class;
            } else {
                $this_rowgroup_class = $rowgroup_class;
                $sub_rowgroup_class = '';
            }

            $topicRow = new html_table_row();
            $topicRow->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';

            $outputidCell = new html_table_cell();
            $outputidCell->text = ($version) ? block_exacomp_get_topic_numbering($topic->id) : '';
            $topicRow->cells[] = $outputidCell;

            $outputnameCell = new html_table_cell();
            $outputnameCell->attributes['class'] = 'rowgroup-arrow';
            $outputnameCell->style = "padding-left: ".$padding."px";
            if($version && $topicparam == SHOW_ALL_TOPICS)
                $outputnameCell->text = html_writer::div($outputname,"desctitle");
            else
                $outputnameCell->text = html_writer::div((($outputid) ? ($outputid.': ') : '').$outputname,"desctitle");
            $topicRow->cells[] = $outputnameCell;

            $nivCell = new html_table_cell();
            $nivCell->text = "";

            $topicRow->cells[] = $nivCell;
            
            if(!$statistic){
                foreach($students as $student) {
                    $studentCell = new html_table_cell();
                    $columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
                    $studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
                    $studentCell->colspan = (!$profoundness) ? $studentsColspan : 4;
    
                    if((isset($data->cm_mm->topics[$topic->id]) || $data->showalldescriptors) && !$profoundness) {
                        // SHOW EVALUATION
                        if($data->showevaluation) {
                            $studentCellEvaluation = new html_table_cell();
                            $studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
                        }
    
                        /*
                         * if scheme == 1: print checkbox
                        * if scheme != 1, role = student, version = LIS
                        */
                        if($data->scheme == 1 || ($data->scheme != 1 && $data->role == block_exacomp::ROLE_STUDENT && $version)) {
                            if($data->showevaluation)
                                $studentCellEvaluation->text = $this->generate_checkbox("datatopics", $topic->id,
                                        'topics', $student, ($evaluation == "teacher") ? "student" : "teacher",
                                        $data->scheme, true);
    
                            $studentCell->text = $this->generate_checkbox("datatopics", $topic->id, 'topics', $student, $evaluation, $data->scheme);
                        }
                        /*
                         * if scheme != 1, !version: print select
                        * if scheme != 1, version = LIS, role = teacher
                        */
                        elseif(!$version || ($version && $data->role == block_exacomp::ROLE_TEACHER)) {
                            if($data->showevaluation)
                                $studentCellEvaluation->text = $this->generate_select("datatopics", $topic->id, 'topics', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true, $data->profoundness);
    
                            $studentCell->text = $this->generate_select("datatopics", $topic->id, 'topics', $student, $evaluation, $data->scheme, false, $data->profoundness);
                        }
    
    
                        // ICONS
                        if(isset($data->cm_mm->topics[$topic->id])) {
                            //get CM instances
                            $cm_temp = array();
                            foreach($data->cm_mm->topics[$topic->id] as $cmid)
                                $cm_temp[] = $data->course_mods[$cmid];
    
                            $icon = block_exacomp_get_icon_for_user($cm_temp, $student, $data->supported_modules);
                            $studentCell->text .= '<span title="'.$icon->text.'" class="exabis-tooltip">'.$icon->img.'</span>';
                        }
    
                        // TIPP
                        if(block_exacomp_set_tipp($topic->id, $student, 'activities_topics', $data->scheme)){
                            $icon_img = html_writer::empty_tag('img', array('src'=>"pix/info.png", "alt"=>get_string('teacher_tipp', 'block_exacomp')));
                            $string = block_exacomp_get_tipp_string($topic->id, $student, $data->scheme, 'activities_topics', TYPE_TOPIC);
                            $studentCell->text .= html_writer::span($icon_img, 'exabis-tooltip', array('title'=>$string));
    
                        }
                        if($data->showevaluation)
                            $topicRow->cells[] = $studentCellEvaluation;
                    }else{
                        if($data->showevaluation)
                            $topicRow->cells[] = new html_table_cell();
                    }
                    
                    $topicRow->cells[] = $studentCell;
                }
            }else{
                $statCell = new html_table_cell();
                $statCell->text = "";

                $topicRow->cells[] = $statCell;
            }
            //do not display topic level for version
            if($version) {
                $level--;                
                $topicRow->style = "display:none;";
            }
            
            $rows[] = $topicRow;

            if (!empty($topic->descriptors)) {
                $this->print_descriptors($rows, $level+1, $topic->descriptors, $data, $students, $sub_rowgroup_class,$profoundness, $editmode, $statistic, false,  true);
            }

            if (!empty($topic->subs)) {
                $this->print_topics($rows, $level+1, $topic->subs, $data, $students, $sub_rowgroup_class,$profoundness, $editmode);
            }
        }
    }

    function print_descriptors(&$rows, $level, $descriptors, &$data, $students, $rowgroup_class, $profoundness = false, $editmode=false, $statistic=false, $custom_created_descriptors=false, $parent = false) {
        global $version, $PAGE, $USER, $COURSE, $CFG, $OUTPUT, $DB;

        $evaluation = ($data->role == block_exacomp::ROLE_TEACHER) ? "teacher" : "student";

        foreach($descriptors as $descriptor) {
            if(!$editmode || !$custom_created_descriptors && $descriptor->source != block_exacomp::CUSTOM_CREATED_DESCRIPTOR || ($custom_created_descriptors && $descriptor->source == block_exacomp::CUSTOM_CREATED_DESCRIPTOR)){
                //visibility
                //visible if 
                //        - visible in whole course 
                //    and - visible for specific student
                
                $one_student = false;
                $studentid = 0;
                if(!$editmode && count($students)==1){
                    $studentid = array_values($students)[0]->id;
                    $one_student = true;
                }
                $descriptor_used = block_exacomp_descriptor_used($data->courseid, $descriptor, $studentid);
                
                $visible = block_exacomp_check_descriptor_visibility($data->courseid, $descriptor, $studentid, ($one_student||$data->role==block_exacomp::ROLE_STUDENT) );
                //echo $descriptor->visible . " / " . $visible . " <br/> ";
                
                $visible_css = block_exacomp_get_descriptor_visible_css($visible, $data->role);
                
                $checkboxname = "data";
                list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor, false, $parent);
                $studentsCount = 0;
    
                $padding = ($level) * 20 + 4;
    
                //if($descriptor->parentid > 0)
                    //$padding += 20;
    
                if($descriptor->examples || (!is_null($data->rowgroup) && $parent)) {
                    $data->rowgroup++;
                    $this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$data->rowgroup.' '.$rowgroup_class.$visible_css;
                    $sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$data->rowgroup.' '.$rowgroup_class;
                } else {
                    $this_rowgroup_class = $rowgroup_class.$visible_css;
                    $sub_rowgroup_class = '';
                    
                }
                $descriptorRow = new html_table_row();
                
                
                $descriptorRow->attributes['class'] = 'exabis_comp_aufgabe ' . $this_rowgroup_class;
                if($version && $parent)
                    $descriptorRow->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';
                    
                
                $exampleuploadCell = new html_table_cell();
                if($data->role == block_exacomp::ROLE_TEACHER && !$profoundness ) {
                    $exampleuploadCell->text = html_writer::link(
                            new moodle_url('/blocks/exacomp/example_upload.php',array("courseid"=>$data->courseid,"descrid"=>$descriptor->id,"topicid"=>$descriptor->topicid)),
                            html_writer::empty_tag('img', array('src'=>'pix/upload_12x12.png', 'alt'=>'upload')),
                            array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
                }
    
                $exampleuploadCell->text .= $outputid . ($version) ? block_exacomp_get_descriptor_numbering($descriptor) :"";
    
                $descriptorRow->cells[] = $exampleuploadCell;
    
                $titleCell = new html_table_cell();
                
                if(($descriptor->examples || $descriptor->children || ($parent && $editmode)) && !is_null($data->rowgroup))
                    $titleCell->attributes['class'] = 'rowgroup-arrow';
                $titleCell->style = "padding-left: ".$padding."px";
                $titleCell->text = html_writer::div(html_writer::tag('span', $outputname, array('title'=>get_string('import_source', 'block_exacomp').$this->print_source_info($descriptor->source))));
                
    			//$titleCell->attributes['title'] = $this->print_statistic_table($data->courseid, $students, $descriptor, true, $data->scheme);
    			 
                // EDIT MODE BUTTONS 
                if ($editmode && (($version && !$parent) || !$version)){
                    //Adding to crosssubject only for "teilkompetenzen"
                    $titleCell->text .= html_writer::link(
                            new moodle_url('/blocks/exacomp/select_crosssubjects.php',array("courseid"=>$data->courseid,"descrid"=>$descriptor->id)),
                            $OUTPUT->pix_icon("i/withsubcat", get_string("crosssubject","block_exacomp")),
                            array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
                }
                //if hidden in course, cannot be shown to one student
                //TODO without $descriptor->visible kann deskriptor für einzelnen sch�ler eingeblendet werden --> sinnvoll?
                if(!$descriptor_used){
                    if($editmode || ($one_student && $descriptor->visible && $data->role == block_exacomp::ROLE_TEACHER)){
                        $titleCell->text .= $this->print_visibility_icon($visible, $descriptor->id);
                    }
                    if($editmode && $custom_created_descriptors){
                        $titleCell->text .= html_writer::link($PAGE->url . "&delete_descr=" . $descriptor->id, $OUTPUT->pix_icon("t/delete", get_string("delete"), "", array("onclick" => "return confirm('" . get_string('delete_confirmation_descr','block_exacomp') . "')")));
                    }
                }
                /*if ($editmode) {
                    $titleCell->text .= ' '.$this->print_source_info($descriptor->source);
                }*/
                
                $descriptorRow->cells[] = $titleCell;
                
                $nivCell = new html_table_cell();
                
                $nivText = "";
                foreach($descriptor->categories as $cat){
                    $nivText .= $cat->title;
                }
                $nivCell->text = $nivText;
                $descriptorRow->cells[] = $nivCell;
                        
                
                $visible_student = $visible;
                if(!$statistic){
                    foreach($students as $student) {
                    	

                    	//check reviewerid for teacher
                    	if($data->role == block_exacomp::ROLE_TEACHER)
                    		$reviewerid = $DB->get_field(block_exacomp::DB_COMPETENCIES,"reviewerid",array("userid" => $student->id, "compid" => $descriptor->id, "role" => block_exacomp::ROLE_TEACHER));
                    	
                        //check visibility for every student in overview
                        
                        if(!$one_student && !$editmode)
                            $visible_student = block_exacomp_descriptor_visible($data->courseid, $descriptor, $student->id);
                                
                        $studentCell = new html_table_cell();
                        $columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
                        $studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
        
                        // SHOW EVALUATION
                        if($data->showevaluation) {
                            $studentCellEvaluation = new html_table_cell();
                            $studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
                        }
        
                        // ICONS
                        if(isset($data->cm_mm->competencies[$descriptor->id])) {
                            //get CM instances
                            $cm_temp = array();
                            foreach($data->cm_mm->competencies[$descriptor->id] as $cmid)
                                $cm_temp[] = $data->course_mods[$cmid];
        
                            $icon = block_exacomp_get_icon_for_user($cm_temp, $student, $data->supported_modules);
                            $icontext = '<span title="'.$icon->text.'" class="exabis-tooltip">'.$icon->img.'</span>';
                        }
                        //EPORTFOLIOITEMS
                        if($data->exaport_exists){
                            if(isset($data->eportfolioitems[$student->id]) && isset($data->eportfolioitems[$student->id]->competencies[$descriptor->id])){
                                $shared = false;
                                $li_items = '';
                                foreach($data->eportfolioitems[$student->id]->competencies[$descriptor->id]->items as $item){
                                    $li_item = $item->name;
                                    if($item->shared){
                                        $li_item .= get_string('eportitem_shared', 'block_exacomp');
                                        $shared = true;
                                    }
                                    else
                                        $li_item .=  get_string('eportitem_notshared', 'block_exacomp');
                        
                                    $li_items .= html_writer::tag('li', $li_item);
                                }
                                $first_param = 'id';
                                $second_param = $item->viewid;
                                if($item->useextern){
                                    $second_param = $item->hash;
                                    $first_param = 'hash';
                                }
                                $link = new moodle_url('/blocks/exaport/shared_view.php', array('courseid'=>$COURSE->id, 'access'=>$first_param.'/'.$item->owner.'-'.$second_param));
                        
                                if($shared)
                                    $img = html_writer::link($link, html_writer::empty_tag("img", array("src" => "pix/folder_shared.png","alt" => '')));
                                //$img = html_writer::empty_tag("img", array("src" => "pix/folder_shared.png","alt" => ''));
                                else
                                    $img = html_writer::empty_tag("img", array("src" => "pix/folder_notshared.png","alt" => ''));
                                    
                                $text =  get_string('eportitems', 'block_exacomp').html_writer::tag('ul', $li_items);
                        
                                $eportfoliotext = '<span title="'.$text.'" class="exabis-tooltip">'.$img.'</span>';
                            }else{
                                $eportfoliotext = '';
                            }
                        }
                        // TIPP
                        if(block_exacomp_set_tipp($descriptor->id, $student, 'activities_competencies', $data->scheme)){
                            $icon_img = html_writer::empty_tag('img', array('src'=>"pix/info.png", "alt"=>get_string('teacher_tipp', 'block_exacomp')));
                            $string = block_exacomp_get_tipp_string($descriptor->id, $student, $data->scheme, 'activities_competencies', TYPE_DESCRIPTOR);
                            $tipptext = html_writer::span($icon_img, 'exabis-tooltip', array('title'=>$string));
                        }
                        
                        if(!$profoundness) {
                            /*
                             * if scheme == 1: print checkbox
                            * if scheme != 1, role = student, version = LIS
                            */
                            if($data->scheme == 1 || ($data->scheme != 1 && $data->role == block_exacomp::ROLE_STUDENT && $version)) {
                                if($data->showevaluation)
                                    $studentCellEvaluation->text = $this->generate_checkbox($checkboxname, $descriptor->id, 'competencies', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);
            
                                $studentCell->text = $this->generate_checkbox($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, $data->scheme, ($visible_student)?false:true, null, ($data->role == block_exacomp::ROLE_TEACHER) ? $reviewerid : null);
                            }
                            /*
                             * if scheme != 1, !version: print select
                            * if scheme != 1, version = LIS, role = teacher
                            */
                            elseif(!$version || ($version && $data->role == block_exacomp::ROLE_TEACHER)) {
                                if($data->showevaluation)
                                    $studentCellEvaluation->text = $this->generate_select($checkboxname, $descriptor->id, 'competencies', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true, $data->profoundness);
            
                                $studentCell->text = $this->generate_select($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, $data->scheme, ($visible_student)?false:true, $data->profoundness, ($data->role == block_exacomp::ROLE_TEACHER) ? $reviewerid : null);
                            }
            
                            // ICONS
                            if(isset($icontext)) 
                                $studentCell->text .= $icontext;
                            
                            //EPORTFOLIOITEMS
                            if(isset($eportfoliotext))
                                $studentCell->text .= $eportfoliotext;
                            
                            // TIPP
                            if(isset($tipptext))
                                $studentCell->text .= $tipptext;
            
                            if($data->showevaluation)
                                $descriptorRow->cells[] = $studentCellEvaluation;
            
                            $descriptorRow->cells[] = $studentCell;
                        } else {
                            // ICONS
                            if(isset($icontext))
                                $titleCell->text .= $icontext;
                                
                            //EPORTFOLIOITEMS
                            if(isset($eportfoliotext))
                                $titleCell->text .= $eportfoliotext;
                                
                            // TIPP
                            if(isset($tipptext))
                                $titleCell->text .= $tipptext;
                            
                            $cell1 = new html_table_cell();
                            $cell2 = new html_table_cell();
                            $disabledCell = new html_table_cell();
                            
                            $cell1->text = $this->generate_checkbox_profoundness($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, 1);
                            $cell2->text = $this->generate_checkbox_profoundness($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, 2);
                            $disabledCell->text = html_writer::checkbox("disabled", "",false,null,array("disabled"=>"disabled"));
                            $disabledCell->attributes['class'] = 'disabled';
                            
                            if($descriptor->profoundness == 0) {
                                $descriptorRow->cells[] = $cell1;
                                $descriptorRow->cells[] = $cell2;
                                $descriptorRow->cells[] = $disabledCell;
                                $descriptorRow->cells[] = $disabledCell;
                            } else {
                                $descriptorRow->cells[] = $disabledCell;
                                $descriptorRow->cells[] = $disabledCell;
                                $descriptorRow->cells[] = $cell1;
                                $descriptorRow->cells[] = $cell2;
                            }
                                
                        }
                    }
                }else{
                    
                    $statCell = new html_table_cell();
                    $statCell->text = $this->print_statistic_table($data->courseid, $students, $descriptor, true, $data->scheme);
            
                    $descriptorRow->cells[] = $statCell;
                }
    
                $rows[] = $descriptorRow;
    
                $checkboxname = "dataexamples";
    
                foreach($descriptor->examples as $example) {
                    $example_used = block_exacomp_example_used($data->courseid, $example, $studentid);
                
                    $visible_example = block_exacomp_check_example_visibility($data->courseid, $example, $studentid, ($one_student||$data->role==block_exacomp::ROLE_STUDENT) );
                    
                    $visible_example_css = block_exacomp_get_example_visible_css($visible_example, $data->role);
                    
                    $studentsCount = 0;
                    $exampleRow = new html_table_row();
                    $exampleRow->attributes['class'] = 'exabis_comp_aufgabe ' . $sub_rowgroup_class.$visible_example_css;
                    $exampleRow->cells[] = new html_table_cell();
    
                    $titleCell = new html_table_cell();
                    $titleCell->style = "padding-left: ". ($padding + 20 )."px";
                    $titleCell->text = html_writer::div(html_writer::tag('span', $example->title, array('title'=>get_string('import_source', 'block_exacomp').$this->print_source_info($descriptor->source))));
    
    
                    if(!$statistic){
                        
                        $titleCell->text .= '<span style="padding-left: 10px;" class="todo-change-stylesheet-icons">';
                        
                        if(!$example_used){
                            if($editmode || ($one_student && $visible_example && $data->role == block_exacomp::ROLE_TEACHER)){
                                $titleCell->text .= $this->print_visibility_icon_example($visible_example, $example->id);
                            }
                        }
                        if((block_exacomp_is_admin($COURSE->id) || (isset($example->creatorid) && $example->creatorid == $USER->id)) && $editmode) {
                            $titleCell->text .= html_writer::link(
                                    new moodle_url('/blocks/exacomp/example_upload.php',array("courseid"=>$data->courseid,"descrid"=>$descriptor->id,"topicid"=>$descriptor->topicid,"exampleid"=>$example->id)),
                                    $OUTPUT->pix_icon("i/edit", get_string("edit")),
                                    array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
                        
                            if(!$example_used)
                           		$titleCell->text .= html_writer::link($PAGE->url . "&delete=" . $example->id. "&studentid=" . optional_param('studentid',BLOCK_EXACOMP_SHOW_ALL_STUDENTS, PARAM_INT). "&subjectid=" . optional_param('subjectid', 0, PARAM_INT). "&topicid=" . optional_param('topicid', 0, PARAM_INT), $OUTPUT->pix_icon("t/delete", get_string("delete"), "", array("onclick" => "return confirm('" . get_string('delete_confirmation','block_exacomp') . "')")));
                        
                        	//print up & down icons
                            $titleCell->text .= html_writer::link("#", $OUTPUT->pix_icon("t/up", get_string('up')), array("id" => "example-up", "exampleid" => $example->id, "descrid" => $descriptor->id));
                            $titleCell->text .= html_writer::link("#", $OUTPUT->pix_icon("t/down", get_string('down')), array("id" => "example-down", "exampleid" => $example->id, "descrid" => $descriptor->id));
                            
                        }
                        
                        if ($url = block_exacomp_get_file_url($example, 'example_task')) {
                            $titleCell->text .= html_writer::link($url, $OUTPUT->pix_icon("i/preview", get_string("preview")),array("target" => "_blank"));
                        }
                        
                        
                        if($example->iseditable==7){
                            $iconforlink="pix/elc20_1.png";
                            $titleiconforlink='ELC 20 Etapa';
                        }else{
                            $iconforlink="pix/i_11x11.png";
                            $titleiconforlink='Link';
                        }
                        
                        if($example->externalurl){
                            $titleCell->text .= html_writer::link(str_replace('&amp;','&',$example->externalurl), $OUTPUT->pix_icon("i/preview", get_string("preview")),array("target" => "_blank"));
                        }elseif($example->externaltask){
                            $titleCell->text .= html_writer::link(str_replace('&amp;','&',$example->externaltask), $OUTPUT->pix_icon("i/preview", get_string("preview")),array("target" => "_blank"));
                        }
                        
                        if ($url = block_exacomp_get_file_url($example, 'example_solution')) {
                            $titleCell->text .= $this->print_example_solution_icon($url);
                        }
                        
                        if(!$example->externalurl && !$example->externaltask && !block_exacomp_get_file_url($example, 'example_solution') && !block_exacomp_get_file_url($example, 'example_task') && $example->description) 
                        	$titleCell->text .= $OUTPUT->pix_icon("i/preview", $example->description);
                        	 
                        if($data->role == block_exacomp::ROLE_STUDENT) {
                            $titleCell->text .= $this->print_schedule_icon($example->id, $USER->id, $data->courseid);
                            
                            $titleCell->text .= $this->print_submission_icon($data->courseid, $example->id, $USER->id);
                                
                            $titleCell->text .= $this->print_competence_association_icon($example->id, $data->courseid, false);
                            
                        } else if($data->role == block_exacomp::ROLE_TEACHER) {
                            $studentid = optional_param("studentid", BLOCK_EXACOMP_SHOW_ALL_STUDENTS, PARAM_INT);
            
                            if($studentid && $studentid != BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
                                $titleCell->text .= $this->print_submission_icon($data->courseid, $example->id, $studentid);
                                
                            }
                            //auch für alle schüler auf wochenplan legen
                            if(!$editmode){
                            	$titleCell->text .= $this->print_schedule_icon($example->id, ($studentid)?$studentid:BLOCK_EXACOMP_SHOW_ALL_STUDENTS, $data->courseid);
								
								if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS){
                            		$titleCell->text .= html_writer::link("#",
			                            $OUTPUT->pix_icon("e/increase_indent", get_string("pre_planning_storage","block_exacomp")),
			                            array('id' => 'add-example-to-schedule', 'exampleid' => $example->id, 'studentid' => 0, 'courseid' => $data->courseid));
    
                            	}
							}
                            $titleCell->text .= $this->print_competence_association_icon($example->id, $data->courseid, $editmode);
                        
                        }
                        $titleCell->text .= '</span>';
                        
                        /*if ($editmode) {
                            $titleCell->text .= ' '.$this->print_source_info($descriptor->source);
                        }*/
                        
                        $titleCell->attributes['title'] = '';
                        
                        if(!empty($example->description))
                            $titleCell->attributes['title'] .= $example->description;
                        if(!empty($example->timeframe))
                            $titleCell->attributes['title'] .= '&#013;' . $example->timeframe;
                        if(!empty($example->tips))
                            $titleCell->attributes['title'] .= '&#013;' . $example->tips;
                        
                    }
                    $exampleRow->cells[] = $titleCell;
    
                    $nivCell = new html_table_cell();
                    $nivCell->text = "";
    
                    $exampleRow->cells[] = $nivCell;
                    
                    $visible_student_example = $visible_example;
                    if(!$statistic){
                        foreach($students as $student) {
                            
                            if(!$one_student && !$editmode)
                                $visible_student_example = block_exacomp_example_visible($data->courseid, $example, $student->id);
                        
                            $columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
                            $studentCell = new html_table_cell();
                            $studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
        
                            // SHOW EVALUATION
                            if($data->showevaluation) {
                                $studentCellEvaluation = new html_table_cell();
                                $studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
                            }
        
                            $studentCell->text = html_writer::empty_tag("input",array("type" => "hidden", "value" => 0, "name" => $checkboxname . "-" . $example->id . "-" . $student->id . "-" . (($evaluation == "teacher") ? "teacher" : "student")));
                            /*
                             * if scheme == 1: print checkbox
                            * if scheme != 1, role = student, version = LIS
                            */
                            if($data->scheme == 1 || ($data->scheme != 1 && $data->role == block_exacomp::ROLE_STUDENT && $version)) {
                                if($data->showevaluation)
                                    $studentCellEvaluation->text = $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);
                                    
                                if($data->role == block_exacomp::ROLE_STUDENT) {
                                    $studentCell->text .= get_string('assigndone','block_exacomp');
                                    $studentCell->text .= $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, $evaluation, $data->scheme, ($visible_student_example)?false:true);
        
                                    //$studentCell->text .= $this->print_student_example_evaluation_form($example->id, $student->id, $data->courseid);
                                }
                                else {
                                    $studentCell->text .= $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, $evaluation, $data->scheme, ($visible_student_example)?false:true);
                                }
                            }
                            /*
                             * if scheme != 1, !version: print select
                            * if scheme != 1, version = LIS, role = teacher
                            */
                            elseif(!$version || ($version && $data->role == block_exacomp::ROLE_TEACHER)) {
                                if($data->showevaluation)
                                    $studentCellEvaluation->text = $this->generate_select($checkboxname, $example->id, 'examples', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true, $data->profoundness);
        
                                $studentCell->text .= $this->generate_select($checkboxname, $example->id, 'examples', $student, $evaluation, $data->scheme, ($visible_student_example)?false:true, $data->profoundness);
        
                                //if($data->role == block_exacomp::ROLE_STUDENT)
                                    //$studentCell->text .= $this->print_student_example_evaluation_form($example->id, $student->id, $data->courseid);
                            }
        
                            if($data->showevaluation)
                                $exampleRow->cells[] = $studentCellEvaluation;
                            
                            $exampleRow->cells[] = $studentCell;
                        }
                    }else{ 
                        $statCell = new html_table_cell();
                        $statCell->text = $this->print_statistic_table($data->courseid, $students, $example, false, $data->scheme);
                        
        
                        $exampleRow->cells[] = $statCell;
                    }
                    $rows[] = $exampleRow;
                }
                
                if (!empty($descriptor->children)) {
                    $this->print_descriptors($rows, $level+1, $descriptor->children, $data, $students, $sub_rowgroup_class,$profoundness, $editmode, $statistic);
                }
                //schulische ergänzungen und neue teilkompetenz
                if($editmode && $parent){
                    
                    $own_additionRow = new html_table_row();
                    $own_additionRow->attributes['class'] = 'exabis_comp_aufgabe ' . $sub_rowgroup_class;
                    $own_additionRow->cells[] = new html_table_cell();
                    
                    $cell = new html_table_cell();
                    $cell->text = get_string('own_additions', 'block_exacomp');
                    $own_additionRow->cells[] = $cell;
                    
                    $own_additionRow->cells[] = new html_table_cell();
                    
                    $rows[] = $own_additionRow;
                    
                    $this->print_descriptors($rows, $level+1, $descriptor->children, $data, $students, $sub_rowgroup_class,$profoundness, $editmode, $statistic, true);
                    
                    $own_additionRow = new html_table_row();
                    $own_additionRow->attributes['class'] = 'exabis_comp_aufgabe ' . $sub_rowgroup_class;
                    
                    $own_additionRow->cells[] = new html_table_cell();
                    
                    $cell = new html_table_cell();
                    $cell->style = "padding-left: ". ($padding + 20 )."px";
                    $cell->text = html_writer::empty_tag('input', array('name'=>'new_comp'.$descriptor->id, 'type'=>'textfield', 'placeholder'=>'[neue Teilkompetenz]', 'descrid'=>$descriptor->id));
                    $own_additionRow->cells[] = $cell;
                    $own_additionRow->cells[] = new html_table_cell();
                    $rows[] = $own_additionRow;
                }    
            }
        }
    }


    /*
    public function print_source_color($sourceid) {
        global $DB;
        
        if (!$sourceid) {
            return;
        } elseif ($sourceid == block_exacomp::EXAMPLE_SOURCE_TEACHER) {
            $color = '#FFFF00';
        } else {
            $cnt = $DB->get_field_sql("SELECT COUNT(*) FROM {block_exacompdatasources} WHERE id < ?", array($sourceid));
            $colors = array('#FF0000', '#00FF00', '#0000FF', '#FF00FF', '#00FFFF', '#800000', '#008000', '#000080', '#808000', '#800080', '#008080', '#C0C0C0', '#808080', '#9999FF', '#993366', '#FFFFCC', '#CCFFFF', '#660066', '#FF8080', '#0066CC', '#CCCCFF', '#000080');
            $color = $colors[$cnt%count($colors)];
        }

        return '<span style="border: 1px solid black; background: '.$color.'; margin-right: 5px;">&nbsp;&nbsp;&nbsp;</span>';
    }
    */
    
    public function print_source_info($sourceid) {
        global $DB;
        $info="";
        if ($sourceid == block_exacomp::EXAMPLE_SOURCE_TEACHER) {
            $info = get_string('local', 'block_exacomp');
        } elseif ($sourceid && $source = $DB->get_record("block_exacompdatasources", array('id'=>$sourceid))) {
            $info = $source->name;
        }
        if(empty($info)) {
            $info = get_string('unknown_src', 'block_exacomp')." ($sourceid)";
        }
           
        return $info;
    }

    public function print_sources() {
        global $DB, $OUTPUT, $courseid;
        
        $sources = block_exacomp_data::get_all_used_sources();
        
        if (!$sources) return;
        
        $ret = '<div>';
        foreach ($sources as $source) {
            $name = ($source->name ? $source->name : $source->source);
            $ret .= $OUTPUT->box("Importierte Daten von \"$name\" ".html_writer::link(new moodle_url('/blocks/exacomp/source_delete.php', array('courseid'=>$courseid, 'action'=>'select', 'source'=>$source->id)), 
                    "löschen"));
        }
        $ret .= '</div>';
        return $ret;
    }

    public function print_submission_icon($courseid, $exampleid, $studentid = 0) {
        global $CFG, $OUTPUT;
        
        $context = context_course::instance($courseid);
        $isTeacher = block_exacomp_is_teacher($context);
        
        if(!$isTeacher)
            return html_writer::link(
                    new moodle_url('/blocks/exacomp/example_submission.php',array("courseid"=>$courseid,"exampleid"=>$exampleid)),
                    $OUTPUT->pix_icon("i/manual_item", get_string('submission','block_exacomp')),
                    array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
        else if($studentid) {
            //works only if exaport is installed
            if(block_exacomp_exaportexists()){
                $url = block_exacomp_get_viewurl_for_example($studentid,$exampleid);
                if($url)
                    return html_writer::link(
                        $CFG->wwwroot . ('/blocks/exaport/shared_item.php?access='.$url),
                        $OUTPUT->pix_icon("i/manual_item", get_string("submission","block_exacomp")),
                        array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
            }else{
                return "";
            }
        }
    }
    public function print_schedule_icon($exampleid, $studentid, $courseid) {
        global $OUTPUT;
        
        return html_writer::link(
                            "#",
                            $OUTPUT->pix_icon("e/insert_date", get_string("weekly_schedule","block_exacomp")),
                            array('id' => 'add-example-to-schedule', 'exampleid' => $exampleid, 'studentid' => $studentid, 'courseid' => $courseid));
    }
    public function print_competence_association_icon($exampleid, $courseid, $editmode) {
        global $OUTPUT;
        
        return html_writer::link(
                new moodle_url('/blocks/exacomp/competence_associations.php',array("courseid"=>$courseid,"exampleid"=>$exampleid, "editmode"=>($editmode)?1:0)),
                 $OUTPUT->pix_icon("e/insert_edit_link", get_string('competence_associations','block_exacomp')), array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
    }
    public function print_example_solution_icon($solution) {
        global $OUTPUT;
        
        return html_writer::link($solution, $OUTPUT->pix_icon("e/fullpage", get_string('solution','block_exacomp')) ,array("target" => "_blank"));
    }
    public function print_visibility_icon($visible, $descriptorid) {
        global $OUTPUT;
        
        if($visible)
            $icon = $OUTPUT->pix_icon("i/hide", get_string("hide"));
        else
            $icon = $OUTPUT->pix_icon("i/show", get_string("show"));
            
        return html_writer::link("", $icon, array('name' => 'hide-descriptor','descrid' => $descriptorid, 'id' => 'hide-descriptor', 'state' => ($visible) ? '-' : '+',
                'showurl' => $OUTPUT->pix_url("i/hide"), 'hideurl' => $OUTPUT->pix_url("i/show")
        ));
        
    }
    public function print_visibility_icon_example($visible, $exampleid) {
        global $OUTPUT;
        
        if($visible)
            $icon = $OUTPUT->pix_icon("i/hide", get_string("hide"));
        else
            $icon = $OUTPUT->pix_icon("i/show", get_string("show"));
            
        return html_writer::link("", $icon, array('name' => 'hide-example','exampleid' => $exampleid, 'id' => 'hide-example', 'state' => ($visible) ? '-' : '+',
                'showurl' => $OUTPUT->pix_url("i/hide"), 'hideurl' => $OUTPUT->pix_url("i/show")
        ));
        
    }
    private function print_student_example_evaluation_form($exampleid, $studentid, $courseid) {
        global $DB;
        $exampleInfo = $DB->get_record(block_exacomp::DB_EXAMPLEEVAL, array("exampleid" => $exampleid, "studentid" => $studentid, "courseid" => $courseid));
        $options = array();
        $options['self'] = get_string('assignmyself','block_exacomp');
        $options['studypartner'] = get_string('assignlearningpartner','block_exacomp');
        $options['studygroup'] = get_string('assignlearninggroup','block_exacomp');
        $options['teacher'] = get_string('assignteacher','block_exacomp');

        $content = html_writer::select($options, 'dataexamples-' . $exampleid . '-' . $studentid . '-studypartner', (isset($exampleInfo->studypartner) ? $exampleInfo->studypartner : null), false);

        $content .= get_string('assignfrom','block_exacomp');
        $content .= html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples-' . $exampleid . '-' . $studentid . '-starttime', 'disabled',
                'value' => (isset($exampleInfo->starttime) ? date("Y-m-d",$exampleInfo->starttime) : null)));

        $content .= get_string('assignuntil','block_exacomp');
        $content .= html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples-' . $exampleid . '-' . $studentid . '-endtime', 'disabled',
                'value' => (isset($exampleInfo->endtime) ? date("Y-m-d",$exampleInfo->endtime) : null)));

        return $content;
    }

    /**
     *
     * @param int $students Amount of students
     */
    public function print_column_selector($students) {
        if($students < STUDENTS_PER_COLUMN)
            return;

        $content = html_writer::tag("b", get_string('columnselect','block_exacomp'));
        for($i=0; $i < ceil($students / STUDENTS_PER_COLUMN); $i++) {
            $content .= " ";
            $content .= html_writer::link('javascript:block_exacomp.onlyShowColumnGroup('.$i.');',
                    ($i*STUDENTS_PER_COLUMN+1).'-'.min($students, ($i+1)*STUDENTS_PER_COLUMN),
                    array('class' => 'colgroup-button colgroup-button-'.$i));
        }
        $content .= " " . html_writer::link('javascript:block_exacomp.onlyShowColumnGroup(-1);',
                get_string('allstudents','block_exacomp'),
                array('class' => 'colgroup-button colgroup-button-all'));
        
        global $COURSE;
        if(block_exacomp_get_settings_by_course($COURSE->id)->nostudents) {
            $content .= " " . html_writer::link('javascript:block_exacomp.onlyShowColumnGroup(-2);',
                get_string('nostudents','block_exacomp'),
                array('class' => 'colgroup-button colgroup-button-no'));
        }
        return html_writer::div($content,'spaltenbrowser');
    }
    public function print_student_evaluation($showevaluation, $isTeacher=true,$topic = SHOW_ALL_TOPICS,$subject=0, $studentid=0) {
        global $OUTPUT,$COURSE;

        $link = new moodle_url("/blocks/exacomp/assign_competencies.php",array("courseid" => $COURSE->id, "showevaluation" => (($showevaluation) ? "0" : "1"),'subjectid'=>$subject,'topicid'=>$topic, 'studentid'=>$studentid));
        $evaluation = $OUTPUT->box_start();
        $evaluation .= get_string('overview','block_exacomp');
        $evaluation .= html_writer::empty_tag("br");
        if($isTeacher)    $evaluation .= ($showevaluation) ? get_string('hideevaluation','block_exacomp',$link->__toString()) : get_string('showevaluation','block_exacomp',$link->__toString());
        else $evaluation .= ($showevaluation) ? get_string('hideevaluation_student','block_exacomp',$link->__toString()) : get_string('showevaluation_student','block_exacomp',$link->__toString());

        $evaluation .= $OUTPUT->box_end();

        return $evaluation;
    }
    public function print_overview_legend($teacher) {
        $legend = html_writer::empty_tag('br'). html_writer::empty_tag('br'). html_writer::empty_tag('br').html_writer::tag("img", "", array("src" => "pix/list_12x11.png", "alt" => get_string('legend_activities','block_exacomp')));
		$legend .= ' '.get_string('legend_activities','block_exacomp') . " - ";

        $legend .= html_writer::tag("img", "", array("src" => "pix/folder_fill_12x12.png", "alt" => get_string('legend_eportfolio','block_exacomp')));
        $legend .= ' '.get_string('legend_eportfolio','block_exacomp') . " - ";

        $legend .= html_writer::tag("img", "", array("src" => "pix/x_11x11.png", "alt" => get_string('legend_notask','block_exacomp')));
        $legend .= ' '.get_string('legend_notask','block_exacomp');

        if($teacher) {
            $legend .= " - ";
            $legend .= html_writer::tag("img", "", array("src" => "pix/upload_12x12.png", "alt" => get_string('legend_upload','block_exacomp')));
            $legend .= ' '.get_string('legend_upload','block_exacomp');
        }

        return html_writer::tag("p", $legend);
    }
    /**
     * Used to generate a checkbox for ticking topics/competencies/examples
     *
     * @param String $name name of the checkbox: data for competencies, dataexamples for examples, datatopic for topics
     * @param int $compid
     * @param String $type comptencies or topics or examples
     * @param stdClass $student
     * @param String $evaluation teacher or student
     * @param int $scheme grading scheme
     * @param bool $disabled disabled becomes true for the "show evaluation" option
     *
     * @return String $checkbox html code for checkbox
     */
	public function generate_checkbox($name, $compid, $type, $student, $evaluation, $scheme, $disabled = false, $activityid = null, $reviewerid = null) {
    	global $USER;
    	
    	$attributes = array();
    	if($disabled)
    		$attributes["disabled"] = "disabled";
    	if($reviewerid && $reviewerid != $USER->id)
    		$attributes["reviewerid"] = $reviewerid;
    	
        return html_writer::checkbox(
                ((isset($activityid)) ? 
                        $name . '-' .$compid .'-' . $student->id .'-' . $activityid . '-' . $evaluation
                        : $name . '-' . $compid . '-' . $student->id . '-' . $evaluation),
                $scheme,
                (isset($student->{$type}->{$evaluation}[$compid])) && $student->{$type}->{$evaluation}[$compid] >= ceil($scheme/2), null,
                $attributes);
    }
    public function generate_checkbox_old($name, $compid, $type, $student, $evaluation, $scheme, $disabled = false, $activityid = null) {
        return html_writer::checkbox(
                ((isset($activityid)) ?
                        $name . '[' .$compid .'][' . $student->id .'][' . $activityid . '][' . $evaluation . ']'
                        : $name . '[' . $compid . '][' . $student->id . '][' . $evaluation . ']'),
                $scheme,
                (isset($student->{$type}->{$evaluation}[$compid])) && $student->{$type}->{$evaluation}[$compid] >= ceil($scheme/2), null,
                (!$disabled) ? null : array("disabled"=>"disabled"));
    }
    public function generate_checkbox_profoundness($name, $compid, $type, $student, $evaluation, $scheme) {
        return html_writer::checkbox($name . '[' . $compid . '][' . $student->id . '][' . $evaluation . ']',
                $scheme,
                (isset($student->{$type}->{$evaluation}[$compid])) && $student->{$type}->{$evaluation}[$compid] == $scheme, null);
    }
    /**
     * Used to generate a checkbox for ticking activities topics and competencies
     *
     * @param String $name name of the checkbox: data for competencies, dataexamples for examples, datatopic for topics
     * @param int $compid
     * @param String $type comptencies or topics or examples
     * @param stdClass $student
     * @param String $evaluation teacher or student
     * @param int $scheme grading scheme
     * @param bool $disabled disabled becomes true for the "show evaluation" option
     *
     * @return String $checkbox html code for checkbox
     */
    public function generate_checkbox_activities($name, $compid, $activityid, $type, $student, $evaluation, $scheme, $disabled = false) {
        return html_writer::checkbox(
                $name . '[' .$compid .'][' . $student->id .'][' . $activityid . '][' . $evaluation . ']', $scheme,
                (isset($student->{$type}->activities[$activityid]->{$evaluation}[$compid])) && $student->{$type}->activities[$activityid]->{$evaluation}[$compid] >= ceil($scheme/2),
                null, (!$disabled) ? null : array("disabled"=>"disabled"));
    }
    /**
     * Used to generate a select for activities topics & competencies
     *
     * @param String $name name of the checkbox: data for competencies, dataexamples for examples, datatopic for topics
     * @param int $compid
     * @param String $type comptencies or topics or examples
     * @param stdClass $student
     * @param String $evaluation teacher or student
     * @param bool $disabled disabled becomes true for the "show evaluation" option
     *
     * @return String $select html code for select
     */
    public function generate_select_activities($name, $compid, $activityid, $type, $student, $evaluation, $scheme, $disabled = false, $profoundness = false) {
        $options = array();
        for($i=0;$i<=$scheme;$i++)
            $options[] = (!$profoundness) ? $i : get_string('profoundness_'.$i,'block_exacomp');

        return html_writer::select(
                $options,
                $name . '[' . $compid . '][' . $student->id . '][' . $activityid . '][' . $evaluation . ']',
                (isset($student->{$type}->activities[$activityid]->{$evaluation}[$compid])) ? $student->{$type}->activities[$activityid]->{$evaluation}[$compid] : 0,
                false,(!$disabled) ? null : array("disabled"=>"disabled"));
    }
    /**
     * Used to generate a select for topics/competencies/examples values
     *
     * @param String $name name of the checkbox: data for competencies, dataexamples for examples, datatopic for topics
     * @param int $compid
     * @param String $type comptencies or topics or examples
     * @param stdClass $student
     * @param String $evaluation teacher or student
     * @param bool $disabled disabled becomes true for the "show evaluation" option
     *
     * @return String $select html code for select
     */
    public function generate_select($name, $compid, $type, $student, $evaluation, $scheme, $disabled = false, $profoundness = false, $reviewerid = null) {
    	global $USER;
    	
    	$attributes = array();
    	if($disabled)
    		$attributes["disabled"] = "disabled";
    	if($reviewerid && $reviewerid != $USER->id)
    		$attributes["reviewerid"] = $reviewerid;
    	
        $options = array();
        for($i=0;$i<=$scheme;$i++)
            $options[$i] = (!$profoundness) ? $i : get_string('profoundness_'.$i,'block_exacomp');

        return html_writer::select(
                $options,
                $name . '-' . $compid . '-' . $student->id . '-' . $evaluation,
                (isset($student->{$type}->{$evaluation}[$compid])) ? $student->{$type}->{$evaluation}[$compid] : 0,
                false,$attributes);
    }

    public function print_edit_config($data, $courseid, $fromimport=0){
        global $OUTPUT;

        $header = html_writer::tag('p', $data->headertext).html_writer::empty_tag('br');

        $table = new html_table();
        $table->attributes['class'] = 'exabis_comp_comp';
        $rows = array();

        $temp = false;
        foreach($data->levels as $levelstruct){
            if($levelstruct->level->source > 1 && $temp == false){
                $row = new html_table_row();
                $row->attributes['class'] = 'highlight';

                $cell = new html_table_cell();
                //$cell->attributes['class'] = 'category catlevel1';
                $cell->colspan = 2;
                $cell->text = html_writer::tag('h2', get_string('specificcontent', 'block_exacomp'));
                    
                $row->cells[] = $cell;
                $rows[] = $row;
                $temp = true;
            }

            $row = new html_table_row();
            $row->attributes['class'] = 'highlight';

            $cell = new html_table_cell();
            $cell->colspan = 2;
            $cell->text = html_writer::tag('b', $levelstruct->level->title);

            $row->cells[] = $cell;
            $rows[] = $row;

            foreach($levelstruct->schooltypes as $schooltypestruct){
                $row = new html_table_row();
                $cell = new html_table_cell();
                $cell->text = $schooltypestruct->schooltype->title;
                $row->cells[] = $cell;
                    
                $cell = new html_table_cell();
                if($schooltypestruct->ticked){
                    $cell->text = html_writer::empty_tag('input', array('type'=>'checkbox', 'name'=>'data['.$schooltypestruct->schooltype->id.']', 'value'=>$schooltypestruct->schooltype->id, 'checked'=>'checked'));
                }else{
                    $cell->text = html_writer::empty_tag('input', array('type'=>'checkbox', 'name'=>'data['.$schooltypestruct->schooltype->id.']', 'value'=>$schooltypestruct->schooltype->id));
                }

                $row->cells[] = $cell;
                $rows[] = $row;
            }
        }

        $hiddenaction = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'action', 'value'=>'save'));
        $innerdiv = html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));

        $table->data = $rows;


        $div = html_writer::div(html_writer::tag('form', html_writer::table($table).$hiddenaction.$innerdiv, array('action'=>'edit_config.php?courseid='.$courseid.'&fromimport='.$fromimport, 'method'=>'post')), 'exabis_competencies_lis');


        $content = html_writer::tag("div", $header.$div, array("id"=>"exabis_competences_block"));

        return $content;
    }
    public function print_edit_course($settings, $courseid, $headertext){
        global $DB;
        $header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');
            
        $input_grading = get_string('grading_scheme', 'block_exacomp').": &nbsp"
        .html_writer::empty_tag('input', array('type'=>'text', 'size'=>2, 'name'=>'grading', 'value'=>block_exacomp_get_grading_scheme($courseid)))
        .html_writer::empty_tag('br');

        $input_activities = html_writer::checkbox('uses_activities', 1, $settings->uses_activities == 1, get_string('uses_activities', 'block_exacomp'))
        .html_writer::empty_tag('br');

        $input_descriptors = html_writer::checkbox('show_all_descriptors',1,$settings->show_all_descriptors == 1, get_string('show_all_descriptors', 'block_exacomp'),($settings->uses_activities != 1) ? array("disabled" => "disabled") :  array())
        .html_writer::empty_tag('br');

        $input_examples = html_writer::checkbox('show_all_examples', 1, $settings->show_all_examples == 1, get_string('show_all_examples', 'block_exacomp'))
        .html_writer::empty_tag('br');

        $input_profoundness = html_writer::checkbox('profoundness', 1, $settings->profoundness==1, get_string('useprofoundness', 'block_exacomp'))
        .html_writer::empty_tag('br');
        
        $input_profoundness = html_writer::checkbox('nostudents', 1, $settings->nostudents==1, get_string('usenostudents', 'block_exacomp'))
        .html_writer::empty_tag('br');
        
        $alltax = array(SHOW_ALL_TAXONOMIES => get_string('show_all_taxonomies','block_exacomp'));
        $taxonomies = $DB->get_records_menu('block_exacomptaxonomies',null,'','id,title');
        $taxonomies = $alltax + $taxonomies;
        $input_taxonomies = html_writer::empty_tag('br').html_writer::select($taxonomies, 'filteredtaxonomies[]',$settings->filteredtaxonomies,false,array('multiple'=>'multiple'));
        $input_submit = html_writer::empty_tag('br').html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save', 'admin')));

        $hiddenaction = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'action', 'value'=>'save_coursesettings'));

        $div = html_writer::div(html_writer::tag('form',
                $input_grading.$input_activities.$input_descriptors.$input_examples.$hiddenaction.$input_profoundness.$input_taxonomies.$input_submit,
                array('action'=>'edit_course.php?courseid='.$courseid, 'method'=>'post')), 'block_excomp_center');

        $content = html_writer::tag("div",$header.$div, array("id"=>"exabis_competences_block"));
            
        return $content;
    }

    public function print_my_badges($badges, $onlygained=false){
        $content = "";
        if($badges->issued){
            $content .= html_writer::tag('h4', get_string('my_badges', 'block_exacomp'));
            foreach ($badges->issued as $badge){
                $context = context_course::instance($badge->courseid);
                $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
                $img = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
                $innerdiv = html_writer::div($badge->name);
                $div = html_writer::div($img.$innerdiv, '', array('style'=>'padding:10px;'));
                $content .= $div;
            }

        }
        if(!$onlygained){
            if($badges->pending){
                $content .= html_writer::tag('h2', get_string('pendingbadges', 'block_exacomp'));
                foreach($badges->pending as $badge){
                    $context = context_course::instance($badge->courseid);
                    $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
                    $img = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
                    $innerdiv = html_writer::div($badge->name, "", array('style'=>'font-weight: bold;'));
                    $innerdiv2 = "";
                    if($badge->descriptorStatus){
                        $innerdiv2_content = "";
                        foreach($badge->descriptorStatus as $descriptor){
                            $innerdiv2_content .= html_writer::div($descriptor, "", array('style'=>'padding: 3px 0'));
                        }
                        $innerdiv2 = html_writer::div($innerdiv2_content, "", array('style'=>'padding: 2px 10px'));
                    }
                    $div = html_writer::div($img.$innerdiv.$innerdiv2, '', array('style'=>'padding: 10px;'));
                    $content .= $div;
                }
            }
        }
        return html_writer::div($content, 'exacomp_profile_badges');
    }
    public function print_head_view_examples($sort, $show_all_examples, $url, $context){
        $content = html_writer::start_tag('script', array('type'=>'text/javascript', 'src'=>'javascript/wz_tooltip.js'));
        $content .= html_writer::end_tag('script');
        $content .= html_writer::start_tag('script', array('type'=>'text/javascript', 'src'=>'javascript/simpletreemenu.js'));
        $content .= html_writer::end_tag('script');
        $text_link1 = ($sort=="desc") ? html_writer::tag('b', get_string("subject", "block_exacomp")) : get_string("subject", "block_exacomp");
        $text_link2 = ($sort=="tax") ? html_writer::tag('b', get_string("taxonomies", "block_exacomp")) : get_string("taxonomies", "block_exacomp");
        $content .= get_string('sorting', 'block_exacomp')
        .html_writer::link($url.'&sort=desc', $text_link1)." "
        .html_writer::link($url.'&sort=tax', $text_link2);

        if(block_exacomp_is_teacher($context) || block_exacomp_is_admin($context)){
            $input = '';
            if($show_all_examples != 0)
                $input = html_writer::empty_tag('input', array('type'=>'checkbox', 'name'=>'showallexamples_check', 'value'=>1, 'onClick'=>'showallexamples_form.submit();', 'checked'=>'checked'));
            else
                $input = html_writer::empty_tag('input', array('type'=>'checkbox', 'name'=>'showallexamples_check', 'value'=>1, 'onClick'=>'showallexamples_form.submit();'));

            $input .= get_string('show_all_course_examples', 'block_exacomp');

            $content .= html_writer::tag('form', $input, array('method'=>'post', 'name'=>'showallexamples_form'));
        }
        $div_exabis_competences_block = html_writer::start_div('', array('id'=>'exabis_competences_block'));
        return $div_exabis_competences_block.$content;
    }
    public function print_tree_head(){
        $content = html_writer::empty_tag('br').html_writer::empty_tag('br');
        $content .= html_writer::link("javascript:ddtreemenu.flatten('comptree', 'expand')", get_string("expandcomps", "block_exacomp"));
        $content .=' | ';
        $content .= html_writer::link("javascript:ddtreemenu.flatten('comptree', 'contact')", get_string("contactcomps", "block_exacomp"));
        return $content;
    }

    public function print_tree_view_examples_desc($tree, $do_form = true){
        $li_subjects = '';
        foreach($tree as $subject){
            $subject_example_content = (empty($subject->numb) || $subject->numb==0)? '' : $subject->numb;
            $li_topics = '';

            $li_topics = $this->print_tree_view_examples_desc_rec_topic($subject->subs, $subject_example_content);

            $ul_topics = html_writer::tag('ul', $li_topics);
            $li_subjects .= html_writer::tag('li', $subject->title
                    .$ul_topics);
        }

        $conditions = null;
        if($do_form)
            $conditions = array('id'=>'comptree', 'class'=>'treeview');
            
        $ul_subjects = html_writer::tag('ul', $li_subjects, $conditions);

        if($do_form)
            $content = html_writer::tag('form', $ul_subjects, array('name'=>'treeform'));
        else
            $content = $ul_subjects;

        return $content;
    }

    public function print_tree_view_examples_desc_rec_topic($subs, $subject_example_content){
        $li_topics = '';
        foreach($subs as $topic){
            $topic_example_content = (empty($topic->cat)) ? '' : '('.$topic->cat.')';
            $li_descriptors = '';
            if(isset($topic->descriptors)){
                foreach($topic->descriptors as $descriptor){
                    $li_examples = '';
                    foreach($descriptor->examples as $example){
                        //create description for on mouse over
                        $text=$example->description;
                        $text = str_replace("\"","",$text);
                        $text = str_replace("\'","",$text);
                        $text = str_replace("\n"," ",$text);
                        $text = str_replace("\r"," ",$text);
                        $text = str_replace(":","\:",$text);
                            
                        $example_content = '';

                        $inner_example_content = $subject_example_content .
                        ' ' . $example->title . ' ' .
                        $topic_example_content;

                        //if text is set, on mouseover is enabled, other wise just inner_example_content is displayed
                        if($text)
                            $example_content = html_writer::tag('a',
                                    $inner_example_content,
                                    array('onmouseover'=>'Tip(\''.$text.'\')', 'onmouseout'=>'UnTip()'));
                        else
                            $example_content = $inner_example_content;
                            
                        $icons = $this->example_tree_get_exampleicon($example);
                            
                        $li_examples .= html_writer::tag('li', $example_content.$icons);
                    }
                    $ul_examples = html_writer::tag('ul', $li_examples);
                    $li_descriptors .= html_writer::tag('li', $descriptor->title
                            .$ul_examples);
                }
            }
            $ul_descriptors = html_writer::tag('ul', $li_descriptors);

            $ul_subs = '';
            if(isset($topic->subs)){
                $li_subs = $this->print_tree_view_examples_desc_rec_topic($topic->subs, $subject_example_content);
                $ul_subs .= html_writer::tag('ul', $li_subs);
            }

            $li_topics .= html_writer::tag('li', $topic->title
                    .$ul_descriptors.$ul_subs);

        }
        return $li_topics;
    }
    public function example_tree_get_exampleicon($example) {
        $icon="";
        
        if($url = block_exacomp_get_file_url($example, 'example_task')) {
            $img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/pdf.gif'), 'alt'=>get_string("assigned_example", "block_exacomp"), 'width'=>16, 'height'=>16));
            $icon .= html_writer::link($url, $img,
                    array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('task_example', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
        } 
        if($url = block_exacomp_get_file_url($example, 'example_solution')) {
            $img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/pdf solution.gif'), 'alt'=>get_string("assigned_example", "block_exacomp"), 'height'=>16, 'width'=>16));
            $icon .= html_writer::link($url, $img,
                    array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('solution_example', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
        }
        if($example->externaltask) {
            $example->externaltask = str_replace('&amp;','&',$example->externaltask);
            $img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/link.png'), 'alt'=>get_string("task_example", "block_exacomp"), 'height'=>16, 'width'=>16));
            $icon .= html_writer::link($example->externaltask, $img,
                    array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('extern_task', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
        }
        if($example->externalurl) {
            $example->externalurl = str_replace('&amp;','&',$example->externalurl);
            $img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/link.png'), 'alt'=>get_string("assigned_example", "block_exacomp"), 'height'=>16, 'width'=>16));
            $icon .= html_writer::link($example->externalurl, $img,
                    array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('extern_task', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
        }
        if($example->completefile) {
            $example->completefile = str_replace('&amp;','&',$example->completefile);
            $img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/folder.png'), 'alt'=>get_string("assigned_example", "block_exacomp"), 'height'=>16, 'width'=>16));
            $icon .= html_writer::link($example->completefile, $img,
                    array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('total_example', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
        }
        return $icon;
    }

    public function print_tree_view_examples_tax($tree){
        $li_taxonomies = '';
        foreach($tree as $taxonomy){
            $ul_subjects = $this->print_tree_view_examples_desc($taxonomy->subs, false);
            $li_taxonomies .= html_writer::tag('li', $taxonomy->title->title
                    .$ul_subjects);
        }

        $ul_taxonomies = html_writer::tag('ul', $li_taxonomies, array('id'=>'comptree', 'class'=>'treeview'));
        $content = html_writer::tag('form', $ul_taxonomies, array('name'=>'treeform'));
        return $content;
    }

    public function print_foot_view_examples(){
        $content = html_writer::tag('script', 'ddtreemenu.createTree("comptree", true)', array('type'=>'text/javascript'));
        return $content.html_writer::end_div();
    }
    public function print_courseselection($schooltypes, $topics_activ, $headertext){
        global $PAGE;

        $header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

        $table = new html_table();
        $table->attributes['class'] = 'exabis_comp_comp';
        $rowgroup = 0;
        $rows = array();
        foreach($schooltypes as $schooltype){
            
            $row = new html_table_row();
            $row->attributes['class'] = 'exabis_comp_teilcomp highlight';
    
            $cell = new html_table_cell();
            $cell->text = html_writer::div(html_writer::tag('b', $schooltype->title));
            $cell->attributes['class'] = 'rowgroup-arrow';
                    
            $cell->colspan = 3;
            $row->cells[] = $cell;
            
            $rows[] = $row;
                    
            foreach($schooltype->subs as $subject){
                $hasSubs = !empty($subject->subs);
                    
                if ($hasSubs) {
                    $rowgroup++;
                    $this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$rowgroup;
                    $sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$rowgroup;
                } else {
                    $this_rowgroup_class = $rowgroup_class;
                    $sub_rowgroup_class = '';
                }
                $row = new html_table_row();
                $row->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';

                $cell = new html_table_cell();
                $cell->text = html_writer::div(html_writer::tag('b', $subject->title));
                $cell->attributes['class'] = 'rowgroup-arrow';
                
                $cell->colspan = 2;
                $row->cells[] = $cell;
                
                $selectAllCell = new html_table_cell();
                $selectAllCell->text = html_writer::tag("a", get_string('selectall','block_exacomp'),array("class" => "selectall"));
                $row->cells[] = $selectAllCell;

                $rows[] = $row;
                $this->print_topics_courseselection($rows, 0, $subject->subs, $rowgroup, $sub_rowgroup_class, $topics_activ);
                
            }
        }
        
        $table->data = $rows;


        $table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
        $table_html .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));
        $table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));

        return html_writer::tag("form", $header.$table_html, array("method" => "post", "action" => $PAGE->url . "&action=save", "id" => "course-selection"));
    }
    public function print_descriptor_selection_export(){
        global $PAGE;
        
        $headertext = "Bitte wählen";
        $topics_activ = array();

        $header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

        $table = new html_table();
        $table->attributes['class'] = 'exabis_comp_comp rowgroup';
        $rowgroup = 0;
        $rows = array();
        
        $subjects = block_exacomp_subject::get_objects();
        
        foreach ($subjects as $subject) {
            $row = new html_table_row();
            $row->attributes['class'] = 'exabis_comp_teilcomp highlight rowgroup-level-0';
            
            $cell = new html_table_cell();
            $cell->text = html_writer::div('<input type="checkbox" name="subjects['.$subject->id.']" value="'.$subject->id.'" />'.html_writer::tag('b', $subject->title));
            $cell->attributes['class'] = 'rowgroup-arrow';
            $row->cells[] = $cell;
            $rows[] = $row;
            
            foreach ($subject->topics as $topic) {
                $padding = 20;
                
                $row = new html_table_row();
                $row->attributes['class'] = 'exabis_comp_teilcomp rowgroup-level-1';
                
                $cell = new html_table_cell();
                $cell->attributes['class'] = 'rowgroup-arrow';
                $cell->style = "padding-left: ".$padding."px";
                $cell->text = html_writer::div('<input type="checkbox" name="topics['.$topic->id.']" value="'.$topic->id.'" ">'.$topic->numbering.' '.$topic->title,"desctitle");
                $row->cells[] = $cell;
                
                $rows[] = $row;
                
                foreach($topic->descriptors as $descriptor){
                    
                    $padding = 40;
                
                    $row = new html_table_row();
                    $row->attributes['class'] = 'rowgroup-level-2';
                    
                    $cell = new html_table_cell();
                    $cell->attributes['class'] = 'rowgroup-arrow';
                    $cell->style = "padding-left: ".$padding."px";
                    $cell->text = html_writer::div('<input type="checkbox" name="descriptors['.$descriptor->id.']" value="'.$descriptor->id.'" />'.$descriptor->numbering.' '.$descriptor->title,"desctitle");
                    $row->cells[] = $cell;
                    
                    $rows[] = $row;
                    
                    // child descriptors
                    foreach($descriptor->children as $descriptor){
                        
                        $padding = 60;
                    
                        $row = new html_table_row();
                        $row->attributes['class'] = 'rowgroup-level-3';
                        
                        $cell = new html_table_cell();
                        $cell->attributes['class'] = 'rowgroup-arrow';
                        $cell->style = "padding-left: ".$padding."px";
                        $cell->text = html_writer::div('<input type="checkbox" name="descriptors['.$descriptor->id.']" value="'.$descriptor->id.'" />'.$descriptor->numbering.' '.$descriptor->title,"desctitle");
                        $row->cells[] = $cell;
                        
                        $rows[] = $row;
                    }
                }
            }
        }
        
        $table->data = $rows;


        $table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
        $table_html .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>'Exportieren')), '', array('id'=>'exabis_save_button'));

        return html_writer::tag("form", $header.$table_html, array("method" => "post", "action" => $PAGE->url->out(false, array('action'=>'export_selected')), "id" => "course-selection"));
    }

    public function print_descriptor_selection_source_delete($source, $subjects){
        global $PAGE;
        
        $headertext = "Bitte wählen";
        $topics_activ = array();

        $header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

        $table = new html_table();
        $table->attributes['class'] = 'exabis_comp_comp rowgroup';
        $rowgroup = 0;
        $rows = array();
        
        foreach ($subjects as $subject) {
            $row = new html_table_row();
            $row->attributes['class'] = 'exabis_comp_teilcomp highlight rowgroup-level-0';
            
            $cell = new html_table_cell();
            $cell->text = html_writer::div('<input type="checkbox" name="subjects['.$subject->id.']" value="'.$subject->id.'"'.(!$subject->can_delete?' disabled="disabled"':'').' />'.
                    html_writer::tag('b', $subject->title));
            $cell->attributes['class'] = 'rowgroup-arrow';
            $row->cells[] = $cell;
            $rows[] = $row;
            
            foreach ($subject->topics as $topic) {
                $padding = 20;
                
                $row = new html_table_row();
                $row->attributes['class'] = 'exabis_comp_teilcomp rowgroup-level-1';
                
                $cell = new html_table_cell();
                $cell->attributes['class'] = 'rowgroup-arrow';
                $cell->style = "padding-left: ".$padding."px";
                $cell->text = html_writer::div('<input type="checkbox" name="topics['.$topic->id.']" value="'.$topic->id.'"'.(!$topic->can_delete?' disabled="disabled"':'').' />'.
                        $topic->numbering.' '.$topic->title,"desctitle");
                $row->cells[] = $cell;
                
                $rows[] = $row;
                
                foreach($topic->descriptors as $descriptor){
                    
                    $padding = 40;
                
                    $row = new html_table_row();
                    $row->attributes['class'] = 'rowgroup-level-2';
                    
                    $cell = new html_table_cell();
                    $cell->attributes['class'] = 'rowgroup-arrow';
                    $cell->style = "padding-left: ".$padding."px";
                    $cell->text = html_writer::div('<input type="checkbox" name="descriptors['.$descriptor->id.']" value="'.$descriptor->id.'"'.(!$descriptor->can_delete?' disabled="disabled"':'').' />'.
                            $descriptor->numbering.' '.$descriptor->title,"desctitle");
                    $row->cells[] = $cell;
                    
                    $rows[] = $row;
                    
                    // child descriptors
                    foreach($descriptor->children as $child_descriptor){
                        $padding = 60;
                    
                        $row = new html_table_row();
                        $row->attributes['class'] = 'rowgroup-level-3';
                        
                        $cell = new html_table_cell();
                        $cell->attributes['class'] = 'rowgroup-arrow';
                        $cell->style = "padding-left: ".$padding."px";
                        $cell->text = html_writer::div('<input type="checkbox" name="descriptors['.$child_descriptor->id.']" value="'.$child_descriptor->id.'"'.(!$child_descriptor->can_delete?' disabled="disabled"':'').' />'.
                                $child_descriptor->numbering.' '.$child_descriptor->title,"desctitle");
                        $row->cells[] = $cell;
                        
                        $rows[] = $row;

                        // examples
                        foreach($child_descriptor->examples as $example){
                            $padding = 80;
                        
                            $row = new html_table_row();
                            $row->attributes['class'] = 'rowgroup-level-4';
                            
                            $cell = new html_table_cell();
                            $cell->attributes['class'] = 'rowgroup-arrow';
                            $cell->style = "padding-left: ".$padding."px";
                            $cell->text = html_writer::div('<input type="checkbox" name="examples['.$example->id.']" value="'.$example->id.'"'.(!$example->can_delete?' disabled="disabled"':'').' />'.
                                    $example->numbering.' '.$example->title,"desctitle");
                            $row->cells[] = $cell;
                            
                            $rows[] = $row;
                        }
                    }

                    // examples
                    foreach($descriptor->examples as $example){
                        $padding = 60;
                    
                        $row = new html_table_row();
                        $row->attributes['class'] = 'rowgroup-level-3';
                        
                        $cell = new html_table_cell();
                        $cell->attributes['class'] = 'rowgroup-arrow';
                        $cell->style = "padding-left: ".$padding."px";
                        $cell->text = html_writer::div('<input type="checkbox" name="examples['.$example->id.']" value="'.$example->id.'"'.(!$example->can_delete?' disabled="disabled"':'').' />'.
                                $example->numbering.' '.$example->title,"desctitle");
                        $row->cells[] = $cell;
                        
                        $rows[] = $row;
                    }
                }
            }
        }
        
        $table->data = $rows;


        $table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
        $table_html .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>'Löschen')), '', array('id'=>'exabis_save_button'));

        return html_writer::tag("form", $header.$table_html, array("method" => "post", "action" => $PAGE->url->out(false, array('action'=>'delete_selected')), "id" => "course-selection"));
    }
    
    public function print_topics_courseselection(&$rows, $level, $topics, &$rowgroup, $rowgroup_class = '', $topics_activ){
        global $version;

        $padding = $level * 20 + 12;

        foreach($topics as $topic) {
            list($outputid, $outputname) = block_exacomp_get_output_fields($topic);

            $hasSubs = !empty($topic->subs);

            if ($hasSubs) {
                $rowgroup++;
                $this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$rowgroup.' '.$rowgroup_class;
                $sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$rowgroup.' '.$rowgroup_class;
            } else {
                $this_rowgroup_class = $rowgroup_class;
                $sub_rowgroup_class = '';
            }

            $topicRow = new html_table_row();
            //$topicRow->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';
            $topicRow->attributes['class'] = 'exabis_comp_aufgabe ' . $this_rowgroup_class;
            $outputidCell = new html_table_cell();
            $outputidCell->text = $outputid;
            $topicRow->cells[] = $outputidCell;

            $outputnameCell = new html_table_cell();
            $outputnameCell->attributes['class'] = 'rowgroup-arrow';
            $outputnameCell->style = "padding-left: ".$padding."px";
            $outputnameCell->text = html_writer::div($outputname,"desctitle");
            $topicRow->cells[] = $outputnameCell;

            $cell = new html_table_cell();
            $cell->text = html_writer::checkbox('data['.$topic->id.']', $topic->id, ((isset($topics_activ[$topic->id]))?true:false), '', array('class'=>'topiccheckbox-'.$rowgroup));
            $topicRow->cells[] = $cell;

            $rows[] = $topicRow;

            if (!empty($topic->subs)) {
                $this->print_topics_courseselection($rows, $level+1, $topic->subs, $rowgroup, $sub_rowgroup_class, $topics_activ);
            }
        }
    }
    public function print_activity_legend($headertext){
        $header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

        return $header.html_writer::tag('p', get_string("explaineditactivities_subjects", "block_exacomp")).html_writer::empty_tag('br');

    }
    public function print_activity_footer($niveaus, $modules, $selected_niveaus=array(), $selected_modules=array()){
        global $PAGE;
        $content = '';

        $form_content = '';
        if(!empty($niveaus) && isset($niveaus)){
            $selected = '';
            if(empty($selected_niveaus) || in_array('0', $selected_niveaus))
                $selected = ' selected';
                
            $options = html_writer::tag('option'.$selected, get_string('all_niveaus', 'block_exacomp'), array('value'=>0));
            $has_niveaus = false;
            foreach($niveaus as $niveau){
                if($niveau){
                    $selected = '';
                    if(!empty($selected_niveaus) && in_array($niveau->id, $selected_niveaus))
                        $selected = ' selected';
                    $has_niveaus = true;
                    $options .= html_writer::tag('option'.$selected, $niveau->title, array('value'=>$niveau->id));
                }
            }
            $select = html_writer::tag('select multiple', $options, array('name'=>'niveau_filter[]'));
            if($has_niveaus)
                $form_content .= html_writer::div(html_writer::tag('h5', get_string('niveau_filter', 'block_exacomp')).$select, '');
        }

        if(!empty($modules)){
            $selected = '';
            if(in_array('0', $selected_modules) || empty($selected_modules))
                $selected = ' selected';

            $options = html_writer::tag('option'.$selected, get_string('all_modules', 'block_exacomp'), array('value'=>0));
            foreach($modules as $module){
                $selected = '';
                if(in_array($module->id, $selected_modules))
                    $selected = ' selected';
                    
                $options .= html_writer::tag('option'.$selected, $module->name, array('value'=>$module->id));
            }
            $select = html_writer::tag('select multiple', $options, array('name'=>'module_filter[]'));
            $form_content .= html_writer::div(html_writer::tag('h5', get_string('module_filter', 'block_exacomp')).$select, '');
        }

        if(!empty($niveaus) || !empty($modules)){
            $form_content .= html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('apply_filter', 'block_exacomp')));
            $content .= html_writer::tag('form', $form_content, array('action'=>$PAGE->url.'&action=filter', 'method'=>'post'));
        }

        return $content;
    }
    public function print_activity_content($subjects, $modules, $courseid, $colspan){
        global $COURSE, $PAGE;

        $table = new html_table;
        $table->attributes['class'] = 'exabis_comp_comp';
        $table->attributes['id'] = 'comps';

        $rows = array();

        //print heading

        $row = new html_table_row();
        $row->attributes['class'] = 'heading r0';

        $cell = new html_table_cell();
        $cell->attributes['class'] = 'category catlevel1';
        $cell->attributes['scope'] = 'col';
        $cell->text = html_writer::tag('h1', $COURSE->fullname);

        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->attributes['class'] = 'category catlevel1 bottom';
        $cell->attributes['scope'] = 'col';
        $cell->colspan = $colspan;
        //$cell->text = html_writer::link('#colsettings', get_string('column_setting', 'block_exacomp'))."&nbsp;&nbsp;"
        //.html_writer::link('#colsettings', get_string('niveau_filter', 'block_exacomp')).'&nbsp;&nbsp; ##file_module_selector###';

        $row->cells[] = $cell;
        $rows[] = $row;

        //print row with list of activities
        $row = new html_table_row();
        $cell = new html_table_cell();

        $row->cells[] = $cell;

        $modules_printed = array();

        foreach($modules as $module){
            $cell = new html_table_cell();
            $cell->attributes['class'] = 'ec_tableheadwidth';
            $cell->attributes['module-type'] = $module->modname;
            $cell->text = html_writer::link(block_exacomp_get_activityurl($module), $module->name);

            $row->cells[] = $cell;
        }

        $rows[] = $row;
        $rowgroup = 1;
        //print tree
        foreach($subjects as $subject){
            $row = new html_table_row();
            $row->attributes['class'] = 'ec_heading';
            $cell = new html_table_cell();
            $cell->colspan = $colspan;
            $cell->text = html_writer::tag('b', $subject->title);
            $row->cells[] = $cell;
            $rows[] = $row;
            $this->print_topics_activities($rows, 0, $subject->subs, $rowgroup, $modules);
        }
        $table->data = $rows;

        $table_html = html_writer::div(html_writer::table($table), 'grade-report-grader');
        $div = html_writer::tag("div", html_writer::tag("div", $table_html, array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
        $div .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));
        //$table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));

        return html_writer::tag('form', $div, array('id'=>'edit-activities', 'action'=>$PAGE->url.'&action=save', 'method'=>'post'));

    }
    public function print_topics_activities(&$rows, $level, $topics, &$rowgroup, $modules, $rowgroup_class = '') {
        global $version;
        
        $padding = $level * 20 + 12;

        foreach($topics as $topic) {
            list($outputid, $outputname) = block_exacomp_get_output_fields($topic, true);
                
            $hasSubs = (!empty($topic->subs) || !empty($topic->descriptors));

            if ($hasSubs) {
                $rowgroup++;
                $this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$rowgroup.' '.$rowgroup_class;
                $sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$rowgroup.' '.$rowgroup_class;
            } else {
                $this_rowgroup_class = $rowgroup_class;
                $sub_rowgroup_class = '';
            }

            $topicRow = new html_table_row();
            $topicRow->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';

            $outputnameCell = new html_table_cell();
            $outputnameCell->attributes['class'] = 'rowgroup-arrow';
            $outputnameCell->style = "padding-left: ".$padding."px";
            $outputnameCell->text = html_writer::div($outputid.$outputname,"desctitle");
            $topicRow->cells[] = $outputnameCell;

            foreach($modules as $module) {
                $moduleCell = new html_table_cell();
                $moduleCell->attributes['module-type='] = $module->modname;
                if(!$version)
                $moduleCell->text = html_writer::checkbox('topicdata[' . $module->id . '][' . $topic->id . ']', "", (in_array($topic->id, $module->topics))?true:false,'',array('class' => 'topiccheckbox'));
                $topicRow->cells[] = $moduleCell;
            }

            $rows[] = $topicRow;

            if (!empty($topic->descriptors)) {
                $this->print_descriptors_activities($rows, $level+1, $topic->descriptors, $rowgroup, $modules, $sub_rowgroup_class);
            }

            if (!empty($topic->subs)) {
                $this->print_topics_activities($rows, $level+1, $topic->subs, $rowgroup, $modules, $sub_rowgroup_class);
            }
        }
    }
    public function print_descriptors_activities(&$rows, $level, $descriptors, &$rowgroup, $modules, $rowgroup_class) {
        global $version, $PAGE, $USER;

        foreach($descriptors as $descriptor) {
            list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor,false,false);

            $padding = ($level) * 20 + 4;

            if($descriptor->parentid > 0)
                $padding += 20;
            
            $this_rowgroup_class = $rowgroup_class;

            $descriptorRow = new html_table_row();
            $descriptorRow->attributes['class'] = 'exabis_comp_aufgabe ' . $this_rowgroup_class;

            $titleCell = new html_table_cell();
            $titleCell->style = "padding-left: ".$padding."px";
            $titleCell->text = html_writer::div($outputname);

            $descriptorRow->cells[] = $titleCell;

            foreach($modules as $module) {
                $moduleCell = new html_table_cell();
                $moduleCell->text = html_writer::checkbox('data[' . $module->id . '][' . $descriptor->id . ']', '', (in_array($descriptor->id, $module->descriptors))?true:false);
                $descriptorRow->cells[] = $moduleCell;
            }

            $rows[] = $descriptorRow;
        }
    }
    public function print_badge($badge, $descriptors, $context){
        global $CFG, $COURSE;;

        $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
        $content = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
        $content .= html_writer::div($badge->name, '', array('style'=>'font-weight:bold;'));

        if($badge->is_locked())
            $content .= get_string('statusmessage_'.$badge->status, 'badges');
        elseif ($badge->status == BADGE_STATUS_ACTIVE){
            $content_form = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id', 'value'=>$badge->id))
            .html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'lock', 'value'=>1))
            .html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey()))
            .html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'return', 'value'=>new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id))))
            .html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('deactivate', 'badges')));

            $form = html_writer::tag('form', $content_form, array('method'=>'post', 'action'=>new moodle_url('/badges/action.php')));
                
            $content .= html_writer::div($form);
        }elseif(!$badge->has_manual_award_criteria()){
            $link = html_writer::link(new moodle_url('/badges/edit.php', array('id'=>$badge->id, 'action'=>'details')), get_string('to_award_role', 'block_exacomp'));
            $content .= html_writer::div($link);
        }else{
            if(empty($descriptors)){
                $link = html_writer::link(new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id, 'badgeid'=>$badge->id)), get_string('to_award', 'block_exacomp'));
                $content .= html_writer::div($link);
            }else{
                $content_form = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id', 'value'=>$badge->id))
                .html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'activate', 'value'=>1))
                .html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey()))
                .html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'return', 'value'=>new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id))))
                .get_string('ready_to_activate', 'block_exacomp')
                .html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('activate', 'badges')));
                    
                $form = html_writer::tag('form', $content_form, array('method'=>'post', 'action'=>new moodle_url('/badges/action.php')));
                $content .= html_writer::div($form, '', array('style'=>'padding-bottom:20px;'));

                $link1 = html_writer::link(new moodle_url('/badges/edit.php', array('id'=>$badge->id, 'action'=>'details')), get_string('conf_badges', 'block_exacomp') );
                $link2 = html_writer::link(new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id, 'badgeid'=>$badge->id)), get_string('conf_comps', 'block_exacomp'));

                $content .= html_writer::div($link1.' / '.$link2);
            }
        }

        if($descriptors){
            $li_desc = '';
            foreach($descriptors as $descriptor){
                $li_desc .= html_writer::tag('li', $descriptor->title);
            }
            $content .= html_writer::tag('ul', $li_desc);
        }

        return html_writer::div($content, '', array('style'=>'padding:10px;'));
    }

    public function print_edit_badges($subjects, $badge){
        global $COURSE;
        $table = new html_table();
        $table->attributes['id'] = 'comps';
        $table->attributes['class'] = 'exabis_comp_comp';

        $rows = array();
        
        $rowgroup = 0;
        //print tree
        foreach($subjects as $subject){
            $row = new html_table_row();
            $row->attributes['class'] = 'ec_heading';
            $cell = new html_table_cell();
            //$cell->colspan = 2;
            $cell->text = html_writer::tag('b', $subject->title);
            $row->cells[] = $cell;
            
            $cell = new html_table_cell();
            $cell->attributes['class'] = 'ec_tableheadwidth';
            $cell->text = html_writer::link(new moodle_url('/badges/edit.php', array('id'=>$badge->id, 'action'=>'details')), $badge->name);
            $row->cells[] = $cell;
            $rows[] = $row;
                
            $this->print_topics_badges($rows, 0, $subject->subs, $rowgroup, $badge);
        }

        $table->data = $rows;

        $table_html = html_writer::div(html_writer::table($table), 'grade-report-grader');
        $div = html_writer::tag("div", html_writer::tag("div", $table_html, array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
        $div .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));

        return html_writer::div(get_string('description_edit_badge_comps', 'block_exacomp'))
            .html_writer::empty_tag('br')
            .html_writer::tag('form', $div, array('id'=>'edit-activities','action'=> new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id, 'badgeid'=>$badge->id, 'action'=>'save')), 'method'=>'post'));

    }
    public function print_topics_badges(&$rows, $level, $topics, &$rowgroup, $badge, $rowgroup_class = '') {
        $padding = $level * 20 + 12;

        foreach($topics as $topic) {
            list($outputid, $outputname) = block_exacomp_get_output_fields($topic);

            $hasSubs = (!empty($topic->subs) || !empty($topic->descriptors));
                
            if ($hasSubs) {
                $rowgroup++;
                $this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$rowgroup.' '.$rowgroup_class;
                $sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$rowgroup.' '.$rowgroup_class;
            } else {
                $this_rowgroup_class = $rowgroup_class;
                $sub_rowgroup_class = '';
            }

            $topicRow = new html_table_row();
            $topicRow->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';

            $outputnameCell = new html_table_cell();
            $outputnameCell->attributes['class'] = 'rowgroup-arrow';
            $outputnameCell->style = "padding-left: ".$padding."px";
            $outputnameCell->text = html_writer::div($outputname,"desctitle");
            $topicRow->cells[] = $outputnameCell;

            $badgeCell = new html_table_cell();
            $topicRow->cells[] = $badgeCell;
                
            $rows[] = $topicRow;

            if (!empty($topic->descriptors)) {
                $this->print_descriptors_badges($rows, $level+1, $topic->descriptors, $rowgroup, $badge, $sub_rowgroup_class);
            }

            if (!empty($topic->subs)) {
                $this->print_topics_badges($rows, $level+1, $topic->subs, $rowgroup, $badge, $sub_rowgroup_class);
            }
        }
    }
    public function print_descriptors_badges(&$rows, $level, $descriptors, &$rowgroup, $badge, $rowgroup_class) {
        global $version, $PAGE, $USER;

        foreach($descriptors as $descriptor) {
            list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor,false,false);

            $padding = ($level) * 20 + 4;

            $this_rowgroup_class = $rowgroup_class;
                
            $descriptorRow = new html_table_row();
            $descriptorRow->attributes['class'] = 'exabis_comp_aufgabe ' . $this_rowgroup_class;
                
            $titleCell = new html_table_cell();
            $titleCell->style = "padding-left: ".$padding."px";
            $titleCell->text = html_writer::div($outputname);

            $descriptorRow->cells[] = $titleCell;
                
            $badgeCell = new html_table_cell();
            $badgeCell->text = html_writer::checkbox('descriptors['.$descriptor->id.']', $descriptor->id, ((isset($badge->descriptors[$descriptor->id]))?true:false));
            $descriptorRow->cells[] = $badgeCell;
                
            $rows[] = $descriptorRow;
        }
    }
    public function print_no_topics_warning(){
        global $COURSE;
        return html_writer::link(new moodle_url('/blocks/exacomp/courseselection.php', array('courseid'=>$COURSE->id)), get_string("no_topics_selected", "block_exacomp"));
    }
    public function print_no_course_activities_warning(){
        global $COURSE;
        return html_writer::link(new moodle_url('/course/view.php', array('id'=>$COURSE->id, 'notifyeditingon'=>1)), get_string("no_course_activities", "block_exacomp"));
    }
    public function print_no_activities_warning($isTeacher = true){
        global $COURSE;
        if($isTeacher)
            return html_writer::link(new moodle_url('/blocks/exacomp/edit_activities.php', array('courseid'=>$COURSE->id)), get_string("no_activities_selected", "block_exacomp"));
        else 
            return get_string("no_activities_selected_student", "block_exacomp");
    }
    public function print_detail_legend($showevaluation, $isTeacher=true){
        global $OUTPUT, $COURSE;

        $link = new moodle_url("/blocks/exacomp/competence_detail.php",array("courseid" => $COURSE->id, "showevaluation" => (($showevaluation) ? "0" : "1")));
        $evaluation = $OUTPUT->box_start();
        $evaluation .= get_string('detail_description','block_exacomp');
        $evaluation .= html_writer::empty_tag("br");
        if($isTeacher)
            $evaluation .= ($showevaluation) ? get_string('hideevaluation','block_exacomp',$link->__toString()) : get_string('showevaluation','block_exacomp',$link->__toString());
        else
            $evaluation .= ($showevaluation) ? get_string('hideevaluation_student','block_exacomp',$link->__toString()) : get_string('showevaluation_student','block_exacomp',$link->__toString());

        $evaluation .= $OUTPUT->box_end();

        return $evaluation;
    }
    public function print_detail_content($activities, $courseid, $students, $showevaluation, $role, $scheme = 1){
        global $PAGE;

        $rowgroup = 0;
        $table = new html_table();
        $rows = array();
        $studentsColspan = $showevaluation ? 2 : 1;
        $colspan = 0;
        $table->attributes['class'] = 'exabis_comp_comp';

        /* ACTIVITIES */
        foreach($activities as $activity){
            $activityRow = new html_table_row();
            $activityRow->attributes['class'] = 'highlight';
                
            $title = new html_table_cell();
            $title->text = html_writer::tag('h5', $activity->title);
                
            $activityRow->cells[] = $title;
                
            $studentsCount = 0;

            foreach($students as $student) {
                $studentCell = new html_table_cell();
                $columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);

                $studentCell->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
                $studentCell->colspan = $studentsColspan;
                $studentCell->text = fullname($student);

                $activityRow->cells[] = $studentCell;
            }
                
            $colspan = $studentsCount;
                
            $rows[] = $activityRow;

            if($showevaluation) {
                $studentsCount = 0;

                $evaluationRow = new html_table_row();
                $emptyCell = new html_table_cell();
                $evaluationRow->cells[] = $emptyCell;

                foreach($students as $student) {
                    $columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);

                    $firstCol = new html_table_cell();
                    $firstCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
                    $secCol = new html_table_cell();
                    $secCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;

                    if($role == block_exacomp::ROLE_TEACHER) {
                        $firstCol->text = get_string('studentshortcut','block_exacomp');
                        $secCol->text = get_string('teachershortcut','block_exacomp');
                    } else {
                        $firstCol->text = get_string('teachershortcut','block_exacomp');
                        $secCol->text = get_string('studentshortcut','block_exacomp');
                    }

                    $evaluationRow->cells[] = $firstCol;
                    $evaluationRow->cells[] = $secCol;
                }
                $rows[] = $evaluationRow;
                $colspan += $studentsCount;
            }
                
            foreach($activity->subs as $subject) {
                if(!$subject->subs)
                    continue;

                //for every subject
                $subjectRow = new html_table_row();
                $subjectRow->attributes['class'] = 'highlight';

                //subject-title
                $title = new html_table_cell();
                $title->text = html_writer::tag('b',$subject->title);

                $subjectRow->cells[] = $title;

                $emptyCell = new html_table_cell();
                $emptyCell->colspan = $colspan;

                $subjectRow->cells[] = $emptyCell;

                $rows[] = $subjectRow;

                /* TOPICS */
                //for every topic
                $data = (object)array(
                        'rowgroup' => &$rowgroup,
                        'courseid' => $courseid,
                        'showevaluation' => $showevaluation,
                        'role' => $role,
                        'scheme' => $scheme,
                        'profoundness' => block_exacomp_get_settings_by_course($courseid)->profoundness,
                        'activityid' => $activity->id,
                        'selected_topicid' => null
                );
                $this->print_detail_topics($rows, 0, $subject->subs, $data, $students, $colspan);
                $table->data = $rows;
            }
        }
        $table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
        $table_html .= html_writer::div(html_writer::tag("input", "", array("name" => "btn_submit", "type" => "submit", "value" => get_string("save_selection", "block_exacomp"))),' ', array('id'=>'exabis_save_button'));
        $table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));



        return html_writer::tag("form", $table_html, array("id" => "competence-detail", "method" => "post", "action" => new moodle_url($PAGE->url, array('action'=>'save'))));

    }
    public function print_detail_topics(&$rows, $level, $topics, &$data, $students, $colspan, $rowgroup_class = '') {
        global $version;

        //$padding = ($version) ? ($level-1)*20 :  ($level-2)*20+12;
        $padding = $level * 20 + 12;
        $evaluation = ($data->role == block_exacomp::ROLE_TEACHER) ? "teacher" : "student";

        foreach($topics as $topic) {
            list($outputid, $outputname) = block_exacomp_get_output_fields($topic);
            $studentsColspan = 1;

            $hasSubs = (!empty($topic->subs) || !empty($topic->descriptors) && (!$version || ($version && $topic->id == SHOW_ALL_TOPICS)));

            if ($hasSubs) {
                $data->rowgroup++;
                $this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$data->rowgroup.' '.$rowgroup_class;
                $sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$data->rowgroup.' '.$rowgroup_class;
            } else {
                $this_rowgroup_class = $rowgroup_class;
                $sub_rowgroup_class = '';
            }

            $topicRow = new html_table_row();
            $topicRow->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';

            $outputnameCell = new html_table_cell();
            $outputnameCell->attributes['class'] = 'rowgroup-arrow';
            $outputnameCell->style = "padding-left: ".$padding."px";
            $outputnameCell->text = html_writer::div($outputid.$outputname,"desctitle");
                
            $topicRow->cells[] = $outputnameCell;

            if($topic->used){
                $studentsCount = 0;
                foreach($students as $student) {
                    $studentCell = new html_table_cell();
                    $columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
                    $studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
                    if(isset($student->topics->teacher[$topic->id]) && $student->topics->teacher[$topic->id]>= ceil($data->scheme/2)){
                        $studentCell->attributes['class'] .= ' exabis_comp_teacher_assigned';
                    }
                    $studentCell->colspan = $studentsColspan;

                    // SHOW EVALUATION
                    if($data->showevaluation) {
                        $studentCellEvaluation = new html_table_cell();
                        $studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
                    }

                    /*
                     * if scheme == 1: print checkbox
                    * if scheme != 1, role = student, version = LIS
                    */
                    if($data->scheme == 1 || ($data->scheme != 1 && $data->role == block_exacomp::ROLE_STUDENT && $version)) {
                        if($data->showevaluation)
                            $studentCellEvaluation->text = $this->generate_checkbox_activities("datatopics", $topic->id, $data->activityid,
                                    'activities_topics', $student, ($evaluation == "teacher") ? "student" : "teacher",
                                    $data->scheme, true);

                        $studentCell->text = $this->generate_checkbox_activities("datatopics", $topic->id,$data->activityid, 'activities_topics', $student, $evaluation, $data->scheme, false);
                    }
                    /*
                     * if scheme != 1, !version: print select
                    * if scheme != 1, version = LIS, role = teacher
                    */
                    elseif(!$version || ($version && $data->role == block_exacomp::ROLE_TEACHER)) {
                        if($data->showevaluation)
                            $studentCellEvaluation->text = $this->generate_select_activities("datatopics", $topic->id, $data->activityid, 'activities_topics', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true, $data->profoundness);

                        $studentCell->text = $this->generate_select_activities("datatopics", $topic->id, $data->activityid, 'activities_topics', $student, $evaluation, $data->scheme, false, $data->profoundness);
                    }

                    if($data->showevaluation)
                        $topicRow->cells[] = $studentCellEvaluation;

                    $topicRow->cells[] = $studentCell;
                }
            }
            else{
                $emptyCell = new html_table_cell();
                $emptyCell->colspan = $colspan;
                $topicRow->cells[] = $emptyCell;
            }

            $rows[] = $topicRow;

            if (!empty($topic->descriptors)) {
                $this->print_detail_descriptors($rows, $level+1, $topic->descriptors, $data, $students, $sub_rowgroup_class);
            }

            if (!empty($topic->subs)) {
                $this->print_detail_topics($rows, $level+1, $topic->subs, $data, $students, $sub_rowgroup_class);
            }
        }
    }

    function print_detail_descriptors(&$rows, $level, $descriptors, &$data, $students, $rowgroup_class) {
        global $version, $PAGE, $USER;

        $evaluation = ($data->role == block_exacomp::ROLE_TEACHER) ? "teacher" : "student";

        foreach($descriptors as $descriptor) {
            $checkboxname = "data";
            list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor,false,false);
            $studentsCount = 0;

            $padding = ($level) * 20 + 4;

            $this_rowgroup_class = $rowgroup_class;
                
            $descriptorRow = new html_table_row();
            $descriptorRow->attributes['class'] = 'exabis_comp_aufgabe ' . $this_rowgroup_class;

            $titleCell = new html_table_cell();
            if($descriptor->examples)
                $titleCell->attributes['class'] = 'rowgroup-arrow';
            $titleCell->style = "padding-left: ".$padding."px";
            $titleCell->text = html_writer::div($outputname);

            $descriptorRow->cells[] = $titleCell;

            foreach($students as $student) {
                $studentCell = new html_table_cell();
                $columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
                $studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
                if(isset($student->competencies->teacher[$descriptor->id]) && $student->competencies->teacher[$descriptor->id]>= ceil($data->scheme/2)){
                        $studentCell->attributes['class'] .= ' exabis_comp_teacher_assigned';
                    }
                // SHOW EVALUATION
                if($data->showevaluation) {
                    $studentCellEvaluation = new html_table_cell();
                    $studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
                }
                /*
                 * if scheme == 1: print checkbox
                * if scheme != 1, role = student, version = LIS
                */
                if($data->scheme == 1 || ($data->scheme != 1 && $data->role == block_exacomp::ROLE_STUDENT && $version)) {
                    if($data->showevaluation)
                        $studentCellEvaluation->text = $this->generate_checkbox_activities($checkboxname, $descriptor->id, $data->activityid, 'activities_competencies', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);
                        
                    $studentCell->text = $this->generate_checkbox_activities($checkboxname, $descriptor->id, $data->activityid, 'activities_competencies', $student, $evaluation, $data->scheme, false);
                }
                /*
                 * if scheme != 1, !version: print select
                * if scheme != 1, version = LIS, role = teacher
                */
                elseif(!$version || ($version && $data->role == block_exacomp::ROLE_TEACHER)) {
                    if($data->showevaluation)
                        $studentCellEvaluation->text = $this->generate_select_activities($checkboxname, $descriptor->id, $data->activityid, 'activities_competencies', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true, $data->profoundness);
                        
                    $studentCell->text = $this->generate_select_activities($checkboxname, $descriptor->id, $data->activityid, 'activities_competencies', $student, $evaluation, $data->scheme, false, $data->profoundness);
                }

                if($data->showevaluation)
                    $descriptorRow->cells[] = $studentCellEvaluation;

                $descriptorRow->cells[] = $studentCell;
            }

            $rows[] = $descriptorRow;
        }
    }
    function print_competence_profile_metadata($student) {
        global $OUTPUT;

        $namediv = html_writer::div(html_writer::tag('b',$student->firstname . ' ' . $student->lastname)
                .html_writer::div(get_string('name', 'block_exacomp'), ''), '');

        $imgdiv = html_writer::div($OUTPUT->user_picture($student,array("size"=>100)), '');

        (!empty($student->city))?$citydiv = html_writer::div($student->city
                .html_writer::div(get_string('city', 'block_exacomp'), ''), ''):$citydiv ='';
            
        return html_writer::div($namediv.$imgdiv.$citydiv, 'competence_profile_metadata clearfix');
    }
    
    function box_error($message) {
        global $OUTPUT;
        
        if (!$message) {
            $message = get_string('unknownerror');
        } elseif ($message instanceof moodle_exception) {
            $message = $message->getMessage();
        }
        
        $message = get_string('error').': '.$message;
        return $OUTPUT->notification($message);
    }
    
    function print_competene_profile_overview($student, $courses, $possible_courses, $badges, $exaport, $exaportitems, $exastud, $exastudperiods, $onlygainedbadges=false) {

        $table = $this->print_competence_profile_overview_table($student, $courses, $possible_courses);
        $overviewcontent = $table;
        //my badges
        if(!empty($badges))
            $overviewcontent .= html_writer::div($this->print_my_badges($badges, $onlygainedbadges), 'competence_profile_overview_badges');
        
        //my items
        if($exaport){
            $exaport_content = '';
            foreach($exaportitems as $item){
                $exaport_content .= html_writer::tag('li', html_writer::link('#'.$item->name.$item->id, $item->name));
            }
            $overviewcontent .= html_writer::div(html_writer::tag('h4', get_string('my_items', 'block_exacomp'))
                . html_writer::tag('ul',$exaport_content), 'competence_profile_overview_artefacts');
        }
        
        //my feedbacks
        if($exastud){
            $exastud_content  = '';
            foreach($exastudperiods as $period){
                $exastud_content .= html_writer::tag('li', html_writer::link('#'.$period->description.$period->id, $period->description));
            }
            $overviewcontent .= html_writer::div(html_writer::tag('h4', get_string('my_periods', 'block_exacomp'))
                . html_writer::tag('ul', $exastud_content), 'competence_profile_overview_feedback');
        }
        
        return html_writer::div($overviewcontent, 'competence_profile_overview clearfix');
    }
    function print_competence_profile_overview_table($student, $courses, $possible_courses){
        $total_total = 0;
        $total_reached = 0;
        $total_average = 0;

        $table = new html_table();
        $table->attributes['class'] = 'compstable flexible boxaligncenter generaltable';
        $rows = array();

        $row = new html_table_row();
        $cell = new html_table_cell();
        $cell->text = get_string('course', 'block_exacomp');
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = get_string('gained', 'block_exacomp');
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = get_string('total', 'block_exacomp');
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = '';
        $row->cells[] = $cell;
        $rows[] = $row;

        foreach($possible_courses as $course){
            $statistics = block_exacomp_get_course_competence_statistics($course->id, $student, block_exacomp_get_grading_scheme($course->id));
            //$pie_data = block_exacomp_get_competencies_for_pie_chart($course->id, $student, block_exacomp_get_grading_scheme($course->id));

            if(array_key_exists($course->id, $courses)){
                $row = new html_table_row();
                $cell = new html_table_cell();
                $cell->text = html_writer::link('#'.$course->fullname.$course->id, $course->fullname);
                $row->cells[] = $cell;
                    
                $cell = new html_table_cell();
                $cell->text = $statistics[1];
                $row->cells[] = $cell;
                    
                $cell = new html_table_cell();
                $cell->text = $statistics[0];
                $row->cells[] = $cell;
                    
                $perc_average = $statistics[0] > 0 ? $statistics[2]/$statistics[0]*100 : 0;
                $perc_reached = $statistics[0] > 0 ? $statistics[1]/$statistics[0]*100 : 0;
                    
                $cell = new html_table_cell();
                //$cell->colspan = 4;
                $cell->text = html_writer::div(html_writer::div(
                        html_writer::div('','lbmittelwert', array('style'=>'width:'.$perc_average.'%;')), 
                        'lbmittelwertcontainer') . 
                        html_writer::div('', 'ladebalkenstatus stripes', array('style'=>'width:'.$perc_reached.'%;')),
                    'ladebalken');
                        
                $row->cells[] = $cell;
                $rows[] = $row;
            }
                
            $total_total +=  $statistics[0];
            $total_reached += $statistics[1];
            $total_average += $statistics[2];
                
        }

        $row = new html_table_row();
        $cell = new html_table_cell();
        $cell->text = html_writer::link('#all_courses',get_string('allcourses', 'block_exacomp'));
        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text = $total_reached;
        $row->cells[] = $cell;

        $cell = new html_table_cell();
        $cell->text = $total_total;
        $row->cells[] = $cell;

        $perc_average = 0;
        $perc_reached = 0;
        if($total_total != 0){
            $perc_average = $total_average/$total_total*100;
            $perc_reached = $total_reached/$total_total*100;
        }
        $cell = new html_table_cell();
        $cell->text = html_writer::div(html_writer::div(
                    html_writer::div('','lbmittelwert', array('style'=>'width:'.$perc_average.'%;')), 
                    'lbmittelwertcontainer') . 
                    html_writer::div('', 'ladebalkenstatus stripes', array('style'=>'width:'.$perc_reached.'%;')),
                'ladebalken');
                        
        $row->cells[] = $cell;

        $rows[] = $row;
        $table->data = $rows;
        return html_writer::div(html_writer::tag('h4', get_string('my_comps', 'block_exacomp')).html_writer::table($table), 'competence_profile_overview_mycompetencies clearfix');;
    }

    function print_pie_graph($teachercomp, $studentcomp, $pendingcomp, $courseid){

        $content = html_writer::div(html_writer::empty_tag("canvas",array("id" => "canvas_doughnut".$courseid)),'piegraph',array("style" => "width:100%"));
        $content .= '
        <script>
        var pieChartData = [
        {
        value:'.$pendingcomp.',
        color:"#888888",
        highlight: "#3D3D3D",
        label: "'.get_string('pendingcomp', 'block_exacomp').'"
    },
    {
    value: '.$teachercomp.',
    color: "#48a53f",
    highlight: "#006532",
    label: "'.get_string('teachercomp', 'block_exacomp').'"
    },
    {
    value: '.$studentcomp.',
    color: "#f9b233",
    highlight: "#f39200",
    label: "'.get_string('studentcomp', 'block_exacomp').'"
    }
    ];
        
    var ctx_d = document.getElementById("canvas_doughnut'.$courseid.'").getContext("2d");
    ctx_d.canvas.height = 120;
            
    window.myDoughnut = new Chart(ctx_d).Doughnut(pieChartData, {
    responsive: true
    });

    </script>
    ';
        return $content;
    }
    function print_competence_profile_course($course, $student, $showall = true) {
        $scheme = block_exacomp_get_grading_scheme($course->id);
        $compTree = block_exacomp_get_competence_tree($course->id);
        //print heading
        $content = html_writer::tag("h4", html_writer::tag('a', $course->fullname, array('name'=>$course->fullname.$course->id)), array("class" => "competence_profile_coursetitle"));
        if(!$compTree) {
            $content .= html_writer::div(get_string("nodata","block_exacomp"),"error");
            return html_writer::div($content, 'competence_profile_coursedata');
        }
        //print graphs
        $topics = block_exacomp_get_topics_for_radar_graph($course->id, $student->id);
        $radar_graph = html_writer::div($this->print_radar_graph($topics,$course->id),"competence_profile_radargraph");

        list($teachercomp,$studentcomp,$pendingcomp) = block_exacomp_get_competencies_for_pie_chart($course->id,$student, $scheme, 0, true);
        $pie_graph = html_writer::div($this->print_pie_graph($teachercomp, $studentcomp, $pendingcomp, $course->id),"competence_profile_radargraph");
        
        $total_comps = $teachercomp+$studentcomp+$pendingcomp;
        $timeline_data= block_exacomp_get_timeline_data(array($course), $student, $total_comps);
        
        if($timeline_data)
            $timeline_graph =  html_writer::div($this->print_timeline_graph($timeline_data->x_values, $timeline_data->y_values_teacher, $timeline_data->y_values_student, $timeline_data->y_values_total, $course->id),"competence_profile_timelinegraph");
        else
            $timeline_graph = "";
            
        $content .= html_writer::div($radar_graph.$pie_graph.$timeline_graph, 'competence_profile_graphbox clearfix');
        $content .= html_writer::div($this->print_radar_graph_legend(),"radargraph_legend");
            
        //print list
        $student = block_exacomp_get_user_information_by_course($student, $course->id);

        $items = false;
        if($student != null && block_exacomp_get_profile_settings($student->id)->useexaport == 1) {
            $items = block_exacomp_get_exaport_items($student->id);
        }
        $content .= $this->print_competence_profile_tree($compTree,$course->id, $student,$scheme, false, $items);

        return html_writer::div($content,"competence_profile_coursedata");
    }

    private function print_competence_profile_tree($in, $courseid, $student = null,$scheme = 1, $showonlyreached = false, $eportfolioitems = false) {
        global $DB;
        if($student != null){
            $profile_settings = block_exacomp_get_profile_settings($student->id);
            $studentid= $student->id;
        }
        else 
            $studentid = 0;
        $showonlyreached_total = false;
        if($showonlyreached || ($student != null && $profile_settings->showonlyreached ==1))
            $showonlyreached_total = true;
            
        $ul_items = '';
        $content = "<ul>";
        
        foreach($in as $v) {
            if($v->tabletype =="descriptor"){
                $visibility = $DB->get_record(block_exacomp::DB_DESCVISIBILITY, array('courseid'=>$courseid, 'descrid'=>$v->id, 'studentid'=>$studentid));
                
                $v->visible = ($visibility)?$visibility->visible:($DB->get_record(block_exacomp::DB_DESCVISIBILITY, array('courseid'=>$courseid, 'descrid'=>$v->id, 'studentid'=>0))->visible);
                
            }
            if(($v->tabletype=="descriptor" && $v->visible == 1) || $v->tabletype!="descriptor"){
                $class = 'competence_profile_' . $v->tabletype;
                $reached = false;
                if($v->tabletype == "subject")
                    $class .= " reached";
                if(($v->tabletype == "topic" && isset($student->topics->teacher[$v->id]) && $student->topics->teacher[$v->id] >= ceil($scheme/2)) || $student == null){
                    $class .= " reached";
                    $reached = true;
                }
                if($v->tabletype == "descriptor" && isset($student->competencies->teacher[$v->id]) && $student->competencies->teacher[$v->id] >= ceil($scheme/2)){
                    $class .= " reached";
                    $reached = true;
                }
                if($eportfolioitems && $v->tabletype == "descriptor"){
                    //check for exaportitem
                    $items = $DB->get_records(block_exacomp::DB_COMPETENCE_ACTIVITY, array('compid'=>$v->id, 'comptype'=>TYPE_DESCRIPTOR, 'eportfolioitem'=>1));
                    
                    if($items){
                        $li_items = '';
                        foreach($items as $item){
                            if(!is_array($eportfolioitems) || (is_array($eportfolioitems) && !array_key_exists($item->activityid, $eportfolioitems)))
                                continue;
                            
                            $li_items .= html_writer::tag('li', html_writer::link('#'.$item->activitytitle.$item->activityid,
                                html_writer::empty_tag('img', array('src'=> new moodle_url('/blocks/exacomp/pix/folder_shared.png'), 'alt'=>''))
                                .' '.$item->activitytitle));
                        }
                        $ul_items = html_writer::tag('ul', $li_items, array('class'=>'competence_profile_complist_items'));
                    }
                }
                if($v->tabletype != "descriptor" && (isset($v->subs) && is_array($v->subs)) || isset($v->descriptors) && is_array($v->descriptors))
                    $class .= " category";
                
                if(!$showonlyreached_total || ($showonlyreached_total == 1 && $reached || $v->tabletype == 'subject')){
                    $content .= '<li class="'.$class.'">' . $v->title . $ul_items;
                    $ul_items = '';
                }
                if( isset($v->subs) && is_array($v->subs)) $content .= $this->print_competence_profile_tree($v->subs, $courseid, $student,$scheme, $showonlyreached_total, $eportfolioitems);
                if( isset($v->descriptors) && is_array($v->descriptors)) $content .= $this->print_competence_profile_tree($v->descriptors, $courseid, $student,$scheme, $showonlyreached_total, $eportfolioitems);
                
                if(!$showonlyreached_total || ($showonlyreached_total == 1 && $reached || $v->tabletype == 'subject'))
                    $content .= '</li>';
            }
        }
        $content .= "</ul>";
        return $content;
    }
    function print_radar_graph($records,$courseid) {
        global $CFG;
        
        if(count($records) >= 3 && count($records) <= 7) {

            $content = html_writer::div(html_writer::empty_tag("canvas",array("id" => "canvasradar".$courseid)),"radargraph",array("style" => "height:100%"));
            $content .= '
            <script>
            var radarChartData = {
            labels: [';

            foreach($records as $record)
                $content .= '"'.$record->title.'",';

            $content .= '],
            datasets: [
            {
            label: "'.get_string("studentcomp","block_exacomp").'",
            fillColor: "rgba(249,178,51,0.2)",
            strokeColor: "#f9b233",
            pointColor: "#f9b233",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(151,187,205,1)",
            data: [';

            foreach($records as $record)
                $content .= '"'.round($record->student, 2).'",';
            $content .= ']
            },
            {
            label: "'.get_string("teachercomp","block_exacomp").'",
            fillColor: "rgba(72,165,63,0.2)",
            strokeColor: "rgba(72,165,63,1)",
            pointColor: "rgba(72,165,63,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(151,187,205,1)",
            data: [';

            foreach($records as $record)
                $content .= '"'.round($record->teacher, 2).'",';
            $content .=']
        }
        ]
        };

        var ctx_r = document.getElementById("canvasradar'.$courseid.'").getContext("2d");
        ctx_r.canvas.height = 150;
        
        window.myRadar = new Chart(ctx_r).Radar(radarChartData, {
        responsive: true, multiTooltipTemplate: "<%= value %>"+"%"
        });
        
        </script>';
        } else {
            //print error
            $img = html_writer::div(html_writer::tag("img", "", array("src" => $CFG->wwwroot . "/blocks/exacomp/pix/graph_notavailable.png")));
            $content = html_writer::div($img . get_string("radargrapherror","block_exacomp"),"competence_profile_grapherror");
        }
        return $content;
    }
    public function print_radar_graph_legend() {
        $content = html_writer::span("&nbsp;&nbsp;&nbsp;&nbsp;","competenceyellow");
        $content .= ' '.get_string("studentcomp","block_exacomp").' ';
        $content .= html_writer::span("&nbsp;&nbsp;&nbsp;&nbsp;","competenceok");
        $content .= ' '.get_string("teachercomp","block_exacomp").' ';
        return $content;
    }
    
    public function print_timeline_graph($x_values, $y_values1, $y_values2, $y_values3, $courseid){
        $content = html_writer::div(html_writer::empty_tag("canvas",array("id" => "canvas_timeline".$courseid)),'timeline',array("style" => ""));
        $content .= '
        <script>
        var timelinedata = {
            labels: [';
            foreach($x_values as $val)
                $content .= '"'.$val.'",';

            $content .= '],
            datasets: [
            {
                label: "Teacher Timeline",
                fillColor: "rgba(72,165,63,0.2)",
                    strokeColor: "rgba(72,165,63,1)",
                pointColor: "rgba(72,165,63,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(151,187,205,1)",
                data: [';
                foreach($y_values1 as $val)
                    $content .= '"'.$val.'",';
    
                $content .= ']
            },
            {
                label: "Student Timeline",
                fillColor: "rgba(249,178,51,0.2)",
                strokeColor: "#f9b233",
                pointColor: "#f9b233",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(151,187,205,1)",
                data: [';
                foreach($y_values2 as $val)
                    $content .= '"'.$val.'",';
    
                $content .= ']
            },
            {
                label: "All Competencies",
                fillColor: "rgba(220,220,220,0.2)",
                strokeColor: "rgba(220,220,220,1)",
                pointColor: "rgba(220,220,220,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(220,220,220,1)",
                data: [';
                foreach($y_values3 as $val)
                    $content .= '"'.$val.'",';
    
                $content .= ']
            }
        ]
        };
            
        
        var ctx = document.getElementById("canvas_timeline'.$courseid.'").getContext("2d")
        ctx.canvas.height = 50;
        
        window.myTimeline = new Chart(ctx).Line(timelinedata, {
        responsive: true, bezierCurve : false
        });
    
        </script>
        ';
        return $content;
    }
    public function print_profile_settings($courses, $settings, $usebadges, $exaport, $exastud, $exastud_periods){
        global $COURSE;
        $exacomp_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exacomp'));
        
        $exacomp_div_content .= html_writer::div(
                html_writer::checkbox('showonlyreached', 1, ($settings->showonlyreached==1), get_string('profile_settings_showonlyreached', 'block_exacomp')));

        $content_courses = html_writer::tag('p', get_string('profile_settings_choose_courses', 'block_exacomp'));
        foreach($courses as $course){
            $content_courses .= html_writer::checkbox('profile_settings_course[]', $course->id, (isset($settings->exacomp[$course->id])), $course->fullname);
            $content_courses .= html_writer::empty_tag('br');
        }
        $exacomp_div_content .= html_writer::div($content_courses);
        
        $exacomp_div_content .= html_writer::div(
                html_writer::checkbox('profile_settings_showallcomps', 1, ($settings->showallcomps==1), get_string('profile_settings_showallcomps', 'block_exacomp')));
        
        if($usebadges){
            $badge_div_content = html_writer::tag('h4', get_string('profile_settings_badges_lineup', 'block_exacomp'));
            $badge_div_content .= html_writer::div(
                    html_writer::checkbox('usebadges', 1, ($settings->usebadges ==1), get_string('profile_settings_usebadges', 'block_exacomp')));
                
            $badge_div_content .= html_writer::checkbox('profile_settings_onlygainedbadges', 1, ($settings->onlygainedbadges==1), get_string('profile_settings_onlygainedbadges', 'block_exacomp'));
            $badge_div_content .= html_writer::empty_tag('br');
                
            $badges_div = html_writer::div($badge_div_content);
            $exacomp_div_content .= $badges_div;
        }
        $exacomp_div = html_writer::div($exacomp_div_content);

        $content = $exacomp_div;

        if($exaport){
            $exaport_items = block_exacomp_get_exaport_items();
            if(!empty($exaport_items)){
                $exaport_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exaport'));
                $exaport_div_content .= html_writer::div(
                        html_writer::checkbox('useexaport', 1, ($settings->useexaport==1), get_string('profile_settings_useexaport', 'block_exacomp')));
                    
                $exaport_div = html_writer::div($exaport_div_content);
                $content .= $exaport_div;
            }else{
                $exaport_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exaport'));
                $exaport_div_content .= get_string('profile_settings_no_item', 'block_exacomp');
                $exaport_div = html_writer::div($exaport_div_content);
                $content .= $exaport_div;
            }
        }

        if($exastud){
            $exastud_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exastud'));
            $exastud_div_content .= html_writer::div(
                    html_writer::checkbox('useexastud', 1, ($settings->useexastud ==1), get_string('profile_settings_useexastud', 'block_exacomp')));
                
            if(!empty($exastud_periods)){
                $content_periods = html_writer::tag('p', get_string('profile_settings_choose_periods', 'block_exacomp'));

                foreach($exastud_periods as $period){
                    $content_periods .= html_writer::checkbox('profile_settings_periods[]', $period->id, (isset($settings->exastud[$period->id])), $period->description);
                    $content_periods .= html_writer::empty_tag('br');
                }
            }else{
                $content_periods = html_writer::tag('p', get_string('profile_settings_no_period', 'block_exacomp'));
            }
            $exastud_div_content .= html_writer::div($content_periods);

            $exastud_div = html_writer::div($exastud_div_content);
            $content .= $exastud_div;
        }


        $div = html_writer::div(html_writer::tag('form',
                $content
                . html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), 'exabis_save_button'),
                array('action'=>'competence_profile_settings.php?courseid='.$COURSE->id.'&action=save', 'method'=>'post')), 'block_excomp_center');

        return html_writer::tag("div", $div, array("id"=>"exabis_competences_block"));
    }
    public function print_competence_profile_exaport($settings, $user, $items){
        global $COURSE, $CFG, $USER;

        $header = html_writer::tag('h3', get_string('my_items', 'block_exacomp'), array('class'=>'competence_profile_sectiontitle'));

        $content = '';
        //print items with comps
        $items_with_no_comps = false;
        foreach($items as $item){
            if($item->hascomps)
                $content .= $this->print_exaport_item($item, $user->id);
            else
                $items_with_no_comps = true;
        }

        if($items_with_no_comps){
            $content .= html_writer::tag('p', get_string('item_no_comps', 'block_exacomp'));
            foreach($items as $item){
                if($item->userid != $USER->id)
                    $url = $CFG->wwwroot.'/blocks/exaport/shared_item.php?courseid='.$COURSE->id.'&access=portfolio/id/'.$userid.'&itemid='.$item->id.'&backtype=&att='.$item->attachment;
                else
                    $url = new moodle_url('/blocks/exaport/item.php',array("courseid"=>$COURSE->id,"access"=>'portfolio/id/'.$userid,"id"=>$item->id,"sesskey"=>sesskey(),"action"=>"edit"));
                
                $li_items = '';
                if(!$item->hascomps){
                    $li_items .= html_writer::tag('li', html_writer::link($url, $item->name,array('name'=>$item->name.$item->id)));
                }
                $content .= html_writer::tag('ul', $li_items);
            }
            $content = html_writer::div($content,'competence_profile_noassociation');
        }
        return $header.$content;
    }

    public function print_exaport_item($item, $userid){
        global $COURSE, $CFG, $DB, $USER;
        $content = html_writer::tag('h4', html_writer::tag('a', $item->name, array('name'=>$item->name.$item->id)), array('class'=>'competence_profile_coursetitle'));
        
        $table = new html_table();
        $table->attributes['class'] = 'compstable flexible boxaligncenter generaltable';
        $rows = array();
        $row = new html_table_row();
        $cell = new html_table_cell();
        $cell->attributes['class'] = 'competence_tableright';
        $cell->text = get_string('item_type', 'block_exacomp').": "; 
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        if(strcmp($item->type, 'link')==0){
            $cell->text = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/link_32.png')))
                .get_string('item_link', 'block_exacomp');
        }elseif(strcmp($item->type, 'file')==0){
            $cell->text = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/file_32.png')))
                .get_string('item_file', 'block_exacomp');
        }elseif(strcmp($item->type, 'note')==0){
            $cell->text = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/note_32.png')))
                .get_string('item_note', 'block_exacomp');
        }
        $row->cells[] = $cell;
        $rows[] = $row;
        
        if(!empty($item->intro)){
            $row = new html_table_row();
            $cell = new html_table_cell();
            $cell->attributes['class'] = 'competence_tableright';
            $cell->text = get_string('item_title', 'block_exacomp').": "; 
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $cell->text = $item->intro;
            $row->cells[] = $cell;
            $rows[] = $row;
        }
        
        if(!empty($item->url)){
            $row = new html_table_row();
            $cell = new html_table_cell();
            $cell->attributes['class'] = 'competence_tableright';
            $cell->text = get_string('item_url', 'block_exacomp').": "; 
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $cell->text = $item->url;
            $row->cells[] = $cell;
            $rows[] = $row;
        }
        
        $row = new html_table_row();
        $cell = new html_table_cell();
        $cell->attributes['class'] = 'competence_tableright';
        $cell->text = get_string('item_link', 'block_exacomp').": "; 
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        
        if($userid != $USER->id)
            $url = $CFG->wwwroot.'/blocks/exaport/shared_item.php?courseid='.$COURSE->id.'&access=portfolio/id/'.$userid.'&itemid='.$item->id.'&backtype=&att='.$item->attachment;
        else
            $url = new moodle_url('/blocks/exaport/item.php',array("courseid"=>$COURSE->id,"access"=>'portfolio/id/'.$userid,"id"=>$item->id,"sesskey"=>sesskey(),"action"=>"edit"));
        
        $cell->text = html_writer::link($url, $url);
        $row->cells[] = $cell;
        $rows[] = $row;
            
        $table->data = $rows;
        $content .= html_writer::table($table);
        
        // STANDARDS
        $allSubjects = block_exacomp_get_all_subjects();
        $allTopics = block_exacomp_get_all_topics();
        // 3. GET DESCRIPTORS
        $allDescriptors = $item->descriptors;
        $usedTopics = array();
        foreach ($allDescriptors as $descriptor) {
            $descriptor->topicid = $DB->get_field(block_exacomp::DB_DESCTOPICS, 'topicid', array('descrid' => $descriptor->id), IGNORE_MULTIPLE);
            $descriptor->tabletype = 'descriptor';
            // get descriptor topic
            if (empty($allTopics[$descriptor->topicid])) continue;
            $topic = $allTopics[$descriptor->topicid];
            $topic->descriptors[$descriptor->id] = $descriptor;
            $usedTopics[$topic->id] = $topic;
        }
        $subjects = array();
        
        foreach ($usedTopics as $topic) {
            $found = true;
            for ($i = 0; $i < 10; $i++) {
                if ($topic->parentid) {
                    // parent is topic, find it
                    if (empty($allTopics[$topic->parentid])) {
                        $found = false;
                        break;
                    }
        
                    // found it
                    $allTopics[$topic->parentid]->subs[$topic->id] = $topic;
                    $usedTopics[$topic->parentid] = $allTopics[$topic->parentid];
                    // go up
                    $topic = $allTopics[$topic->parentid];
                } else {
                    // parent is subject, find it
                    if (empty($allSubjects[$topic->subjid])) {
                        $found = false;
                        break;
                    }
        
                    // found: add it to the subject result
                    $subject = $allSubjects[$topic->subjid];
                    $subject->subs[$topic->id] = $topic;
                    $subjects[$topic->subjid] = $subject;
        
                    // top found
                    break;
                }
            }
        }
        $list_descriptors = $this->print_competence_profile_tree($subjects, $COURSE->id);
        $list_heading = html_writer::tag('p', '<b>Verknüpfte Kompetenzen:</b>');
        
        return html_writer::div($content.$list_heading.$list_descriptors, 'competence_profile_artefacts');
    }
    public function print_competence_profile_exastud($settings, $user, $periods, $reviews){
        $header = html_writer::tag('h3', get_string('my_periods', 'block_exacomp'), array('class'=>'competence_profile_sectiontitle'));
        
        $profile_settings = block_exacomp_get_profile_settings();
        
        $content = '';
        foreach($periods as $period){
            
            if(isset($profile_settings->exastud[$period->id])){
                $content .= $this->print_exastud_period($period, $reviews);
            }
        }

        //$content .= html_writer::div($content, 'competence_profile_feedback');

        return $header.$content;
    }
    private function print_exastud_period($period, $reviews){
        $content = html_writer::tag('h4', html_writer::tag('a', $period->description, array('name'=>$period->description.$period->id)), array('class'=>'competence_profile_coursetitle'));
        
        $review = $reviews[$period->id];
        
        $table = new html_table();
        $table->attributes['class'] = 'compstable flexible boxaligncenter generaltable';
        $rows = array();
        $row = new html_table_row();
        $cell = new html_table_cell();
        $cell->attributes['class'] = 'competence_tableright';
        $cell->text = get_string('period_reviewer', 'block_exacomp').":";
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = $review->reviewer->firstname." ".$review->reviewer->lastname;
        $row->cells[] = $cell;
        $rows[] = $row;
            
        
        foreach($review->categories as $category){
            $row = new html_table_row();
            $cell = new html_table_cell();
            $cell->attributes['class'] = 'competence_tableright';
            $cell->text = $category->title.":";
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $cell->text = $category->evaluation."/10";
            $row->cells[] = $cell;
            $rows[] = $row;
        }

        foreach($review->descriptors as $descriptor){
            $row = new html_table_row();
            $cell = new html_table_cell();
            $cell->attributes['class'] = 'competence_tableright';
            $cell->text = $descriptor->title.":";
            $row->cells[] = $cell;
            $cell = new html_table_cell();
            $cell->text = $descriptor->evaluation."/10";
            $row->cells[] = $cell;
            $rows[] = $row;
        }

        $row = new html_table_row();
        $cell = new html_table_cell();
        $cell->attributes['class'] = 'competence_tableright';
        $cell->text = get_string('period_feedback', 'block_exacomp').":";
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->text = $review->feedback;
        $row->cells[] = $cell;
        $rows[] = $row;
        
        $table->data = $rows;
        $content .= html_writer::table($table);
        
        return html_writer::div($content, 'competence_profile_feedback');
    }
    public function print_competence_profile_course_all($courses, $student){
        $subjects = block_exacomp_get_subjects_for_radar_graph($student->id);
        
        $content = html_writer::div(html_writer::tag('h4', html_writer::tag('a', get_string('profile_settings_showallcomps', 'block_exacomp'), array('name'=>'all_courses'))), 'competence_profile_coursetitle');
        
        if(!$subjects) {
            $content .= html_writer::div(get_string("nodata","block_exacomp"),"error");
            return $content;
        }
        
        $teachercomp = 0;
        $studentcomp = 0;
        $pendingcomp = 0;
        foreach($courses as $course){
            $course_data = block_exacomp_get_competencies_for_pie_chart($course->id, $student, block_exacomp_get_grading_scheme($course->id), 0, true);
            $teachercomp += $course_data[0];
            $studentcomp += $course_data[1];
            $pendingcomp += $course_data[2];
        }
        
        //print graphs
        $radar_graph = html_writer::div($this->print_radar_graph($subjects, 0),"competence_profile_radargraph");

        $pie_graph = html_writer::div($this->print_pie_graph($teachercomp, $studentcomp, $pendingcomp, 0),"competence_profile_radargraph");
        
        $total_comps = $teachercomp+$studentcomp+$pendingcomp;
        $timeline_data= block_exacomp_get_timeline_data($courses, $student, $total_comps);
        
        if($timeline_data)
            $timeline_graph =  html_writer::div($this->print_timeline_graph($timeline_data->x_values, $timeline_data->y_values_teacher, $timeline_data->y_values_student, $timeline_data->y_values_total, 0),"competence_profile_timelinegraph");
        else    
            $timeline_graph = "";
            
        $content .= html_writer::div($radar_graph.$pie_graph.$timeline_graph, 'competence_profile_graphbox clearfix');
        $content .= html_writer::div($this->print_radar_graph_legend(),"radargraph_legend");
            
        //print list
        foreach($courses as $course){
            $student = block_exacomp_get_user_information_by_course($student, $course->id);
            $scheme = block_exacomp_get_grading_scheme($course->id);
            $compTree = block_exacomp_get_competence_tree($course->id);
            $items = false;
            if($student != null && block_exacomp_get_profile_settings($student->id)->useexaport == 1) {
                $items = block_exacomp_get_exaport_items($student->id);
            }
            if($compTree)
                $content .= html_writer::tag('h4', $course->fullname) .
                    $this->print_competence_profile_tree($compTree,$course->id, $student,$scheme, false, $items);
        }
        return html_writer::div($content,"competence_profile_coursedata");
    }
    public function print_wrapperdivstart(){
          return html_writer::start_tag('div',array('id'=>'block_exacomp'));
  }
    public function print_wrapperdivend(){
          return html_writer::end_tag('div');
    }
    public function print_profile_print_button(){
        $content = html_writer::link('javascript:window.print()',
                html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/view_print.png'), 'alt'=>'print')), array('class'=>'print'));
        return html_writer::div(html_writer::tag('form', $content), 'competence_profile_printbox');
    }
    public function print_cross_subjects_drafts($subjects, $isAdmin=false){
        global $PAGE, $USER;
        
        $draft_content = "";
        $drafts_exist = false;
        $draft_content .= html_writer::start_tag('ul', array("class"=>"collapsibleList"));
                
        foreach($subjects as $subject){
            if(isset($subject->crosssub_drafts)){
                $draft_content .= html_writer::start_tag('li');
                $draft_content .= $subject->title;
                
                $drafts_exist = true;
                $draft_content .= html_writer::start_tag('ul');
                
                //print_r($subject->crosssub_drafts);
                foreach($subject->crosssub_drafts as $draft){
                    $text = $draft->description;
                    $text = str_replace("\"","",$text);
                    $text = str_replace("\'","",$text);
                    $text = str_replace("\n"," ",$text);
                    $text = str_replace("\r"," ",$text);
                    $text = str_replace(":","\:",$text);

                    $draft_content .= html_writer::start_tag('li');
                    $draft_content .= html_writer::span(html_writer::checkbox('draft['.$draft->id.']', $draft->id, false, $draft->title), '', array('title'=>$text));
                    $draft_content .= html_writer::end_tag('li');
                }
                $draft_content .= html_writer::end_tag('ul');
                $draft_content .= html_writer::end_tag('li');
            }
        }
        $draft_content .= html_writer::end_tag('ul');
        $submit = "";
        if($drafts_exist){
            $submit .= html_writer::empty_tag('input', array('name'=>'btn_submit', 'type'=>'submit', 'value'=>get_string('add_drafts_to_course', 'block_exacomp')));
            if($isAdmin) $submit .= html_writer::empty_tag('input', array('name'=>'delete_crosssubs', 'type'=>'submit', 'value'=>get_string('delete_drafts', 'block_exacomp')));
        }
        $submit .= html_writer::empty_tag('br');
        $submit .= html_writer::empty_tag('input', array('name'=>'new_crosssub', 'type'=>'submit', 'value'=>get_string('new_crosssub', 'block_exacomp')));
    
        $submit = html_writer::div($submit, '', array('id'=>'exabis_save_button')); 
        $content = html_writer::tag('form', $draft_content.$submit, array('method'=>'post', 'action'=>$PAGE->url.'&action=save', 'name'=>'add_drafts_to_course'));
        
        $div_exabis_competences_block = html_writer::div($content, "", array('id'=>'exabis_competences_block'));
        return $div_exabis_competences_block;
    }
    
    
    public function print_cross_subjects_form_start($selectedCrosssubject=null, $studentid=null){
        global $PAGE, $COURSE;
        $url_params = array();
        $url_params['action'] = 'save';
        if(isset($selectedCrosssubject))
            $url_params['crosssubjid'] = $selectedCrosssubject->id;
        if(isset($studentid))
            $url_params['studentid'] = $studentid;
                
        $url = new moodle_url($PAGE->url, $url_params);
        return html_writer::start_tag('form',array('id'=>'assign-competencies', "action" => $url, 'method'=>'post'));
    }
    
    public function print_dropdowns_cross_subjects($crosssubjects, $selectedCrosssubject, $students, $selectedStudent = BLOCK_EXACOMP_SHOW_ALL_STUDENTS, $isTeacher = false){
        global $PAGE;
        
        $content = html_writer::empty_tag("br");

        $content .= get_string("choosecrosssubject", "block_exacomp").': ';
        $options = array();
        foreach($crosssubjects as $crosssub)
            $options[$crosssub->id] = $crosssub->title;
        $content .= html_writer::select($options, "lis_crosssubs", $selectedCrosssubject, false,
                array("onchange" => "document.location.href='".$PAGE->url."&studentid=".$selectedStudent."&crosssubjid='+this.value;"));

        if($isTeacher){
            $content .= html_writer::empty_tag("br");
    
            $content .= get_string("choosestudent", "block_exacomp");
            $content .= block_exacomp_studentselector($students,$selectedStudent,$PAGE->url."&crosssubjid=".$selectedCrosssubject,  BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN);
            
            $content .= $this->print_edit_mode_button("&crosssubjid=".$selectedCrosssubject."&studentid=".$selectedStudent);
        }    
        return $content;
    }
    public function print_crosssub_subject_dropdown($crosssubject){
        $subjects = block_exacomp_get_subjects();
        $options = array();
        $options[0] = get_string('nocrosssubsub', 'block_exacomp');
        foreach($subjects as $subject){
            $options[$subject->id] = $subject->title;
        }
        return html_writer::select($options, "lis_crosssubject_subject", $crosssubject->subjectid, false);
        
    }
    public function print_overview_metadata_cross_subjects($crosssubject, $isTeacher, $selectedStudent = null){
        global $version, $DB;
        
        $table = new html_table();
        $table->attributes['class'] = 'exabis_comp_info';

        $rows = array();

        $row = new html_table_row();

        $subject_title = get_string('nocrosssubsub', 'block_exacomp');
        if($crosssubject->subjectid != 0){
            $subject = $DB->get_record(block_exacomp::DB_SUBJECTS, array('id'=>$crosssubject->subjectid));
            $subject_title = $subject->title;
        }
        $cell = new html_table_cell();
        $cell->text = html_writer::span(get_string('subject_singular', 'block_exacomp'), 'exabis_comp_top_small')
        . (($selectedStudent == 0)?$this->print_crosssub_subject_dropdown($crosssubject):html_writer::tag('b',$subject_title));

        $row->cells[] = $cell;

        $cell = new html_table_cell();
        
        if($selectedStudent == 0)
            $cell->text = html_writer::span(get_string('crosssubject', 'block_exacomp'), 'exabis_comp_top_small')
                . html_writer::empty_tag('input', array('type'=>'text', 'value'=>$crosssubject->title, 'name'=>'crosssub-title'));
        else 
            $cell->text = html_writer::span(get_string('crosssubject', 'block_exacomp'), 'exabis_comp_top_small')
                . html_writer::tag('b', $crosssubject->title);
                
        $row->cells[] = $cell;
        
        $rows[] = $row;
        
        $row = new html_table_row();
        $cell = new html_table_cell();
        $cell->colspan = (isset($selectedStudent))?3:2;
        if($selectedStudent == 0)
            $cell->text = html_writer::span(get_string('description', 'block_exacomp'), 'exabis_comp_top_small')
                . html_writer::empty_tag('input', array('type'=>'textarea', 'size'=>200, 'value'=>$crosssubject->description, 'name'=>'crosssub-description'));
        else
             $cell->text = html_writer::span(get_string('description', 'block_exacomp'), 'exabis_comp_top_small')
                . html_writer::tag('b', $crosssubject->description);
                
        $row->cells[] = $cell;  
        $rows[] = $row;
            
        if($isTeacher){
            $row = new html_table_row();
            $cell = new html_table_cell();
            $cell->colspan = 2;
            $cell->text = html_writer::span(get_string('tab_help', 'block_exacomp'), 'exabis_comp_top_small')
                . get_string('help_crosssubject', 'block_exacomp');    
            $row->cells[] = $cell;  
            $rows[] = $row;   
        }
        $table->data = $rows;

        $content = html_writer::table($table);

        return $content;
    }
    
    public function print_competence_based_list_tree($tree, $isTeacher, $editmode, $show_examples = true) {
        global $PAGE;
        
        $html_tree = "";
        $html_tree .= html_writer::start_tag("ul",array("class"=>"collapsibleList"));
        foreach($tree as $skey => $subject) {
            if($subject->associated == 1 || ($isTeacher && $editmode==1)){
                $html_tree .= html_writer::start_tag("li", array('class'=>($subject->associated == 1)?"associated":""));
                $html_tree .= $subject->title;
                
                if(!empty($subject->subs))
                    $html_tree .= html_writer::start_tag("ul");
                
                foreach ( $subject->subs as $tkey => $topic ) {
                    if($topic->associated == 1 || ($isTeacher && $editmode==1)){
                        $html_tree .= html_writer::start_tag("li", array('class'=>($topic->associated == 1)?"associated":""));
                        $html_tree .= block_exacomp_get_topic_numbering($topic->id).' '.$topic->title;
                        
                        if(!empty($topic->descriptors))
                            $html_tree .= html_writer::start_tag("ul");
                        
                        foreach ( $topic->descriptors as $dkey => $descriptor ) {
                            if($descriptor->associated == 1 || ($isTeacher && $editmode==1))
                                $html_tree .= $this->print_competence_for_list_tree($descriptor, $isTeacher, $editmode, $show_examples);
                        }
                        
                        if(!empty($topic->descriptors))
                            $html_tree .= html_writer::end_tag("ul");
                    }
                    
                }
                if(!empty($subject->subs))
                    $html_tree .= html_writer::end_tag("ul");
                
                $html_tree .= html_writer::end_tag("li");
            }
        }
        $html_tree .= html_writer::end_tag("ul");
        return html_writer::div($html_tree, "associated_div", array('id'=>"associated_div"));
    }
    
    private function print_competence_for_list_tree($descriptor, $isTeacher, $editmode, $show_examples) {
        $html_tree = html_writer::start_tag("li", array('class'=>($descriptor->associated == 1)?"associated":""));
        if($isTeacher && $editmode==1)
            $html_tree .= html_writer::checkbox("descriptor[]", $descriptor->id, ($descriptor->direct_associated==1)?true:false);
        
        $html_tree .= block_exacomp_get_descriptor_numbering($descriptor).' '.$descriptor->title;
            
        if($show_examples){
	        if(!empty($descriptor->examples))
	            $html_tree .= html_writer::start_tag("ul");
	            
	        foreach($descriptor->examples as $example) {
	            if(!isset($example->associated)) $example->associated = 0;
	            if($example->associated == 1 || ($isTeacher && $editmode==1))
	                $html_tree .= html_writer::tag("li", $example->title, array('class'=>($example->associated == 1)?"associated":""));
	        }
	            
	        if(!empty($descriptor->examples))
	            $html_tree .= html_writer::end_tag("ul");
        }
        if(!empty($descriptor->children)) {
            $html_tree .= html_writer::start_tag("ul");
            
            foreach($descriptor->children as $child)
                if($child->associated == 1 || ($isTeacher && $editmode==1))
                    $html_tree .= $this->print_competence_for_list_tree($child, $isTeacher, $editmode, $show_examples);
            
            $html_tree .= html_writer::end_tag("ul");
        }
        $html_tree .= html_writer::end_tag("li");
        
        return $html_tree;
    }
    function print_statistic_table($courseid, $students, $item, $descriptor=true, $scheme=1){
        
        if($descriptor)
            list($self, $student_oB, $student_iA, $teacher, $teacher_oB, $teacher_iA,
                        $self_title, $student_oB_title, $student_iA_title, $teacher_title, 
                        $teacher_oB_title, $teacher_iA_title) = block_exacomp_calculate_statistic_for_descriptor($courseid, $students, $item, $scheme);
        else
            list($self, $student_oB, $student_iA, $teacher, $teacher_oB, $teacher_iA,
                        $self_title, $student_oB_title, $student_iA_title, $teacher_title, 
                        $teacher_oB_title, $teacher_iA_title) = block_exacomp_calculate_statistic_for_example($courseid, $students, $item, $scheme);
            
        
        $table = new html_table();
        $table->attributes['class'] = 'statistic';
        $table->border = 3;
        
        $rows = array();
        
        $self_row_header = new html_table_row();
        $self_row_header->attributes['class'] = 'statistic_head';
        
        $empty_cell = new html_table_cell();
        $self_row_header->cells[] = $empty_cell;
        
        $empty_cell = new html_table_cell();
        $self_row_header->cells[] = $empty_cell;
        
        foreach($self as $self_key => $self_value){
            $cell = new html_table_cell();
            $cell->text = $self_key;
            $self_row_header->cells[] = $cell;
        }
        
        $cell = new html_table_cell();
        $cell->text = "oB";
        $self_row_header->cells[] = $cell;
        
        $cell = new html_table_cell();
        $cell->text = "iA";
        $self_row_header->cells[] = $cell;
        
        $rows[] = $self_row_header;
        
        $self_row = new html_table_row();
        $self_row->attributes['class'] = '';
        
        $cell = new html_table_cell();
        $cell->text = "S";
        $self_row->cells[] = $cell;
        
        $empty_cell = new html_table_cell();
        $self_row->cells[] = $empty_cell;
        
        foreach($self as $self_key => $self_value){
            $cell = new html_table_cell();
            $cell->text =($self_value>0)?html_writer::tag('span', $self_value, array('title'=>$self_title[$self_key])):$self_value;
            $self_row->cells[] = $cell;
        }
    
        $cell = new html_table_cell();
        $cell->text = ($student_oB>0)?html_writer::tag('span', $student_oB, array('title'=>$student_oB_title)):$student_oB;
        $self_row->cells[] = $cell;
        
        $cell = new html_table_cell();
        $cell->text = ($student_iA>0)?html_writer::tag('span', $student_iA, array('title'=>$student_iA_title)):$student_iA;
        $self_row->cells[] = $cell;
        
        $rows[] = $self_row;
        
        $teacher_row_header = new html_table_row();
        $teacher_row_header->attributes['class'] = 'statistic_head';
        
        $empty_cell = new html_table_cell();
        $teacher_row_header->cells[] = $empty_cell;
        
        foreach($teacher as $teacher_key => $teacher_value){
            $cell = new html_table_cell();
            $cell->text = $teacher_key;
            $teacher_row_header->cells[] = $cell;
        }
        
        $cell = new html_table_cell();
        $cell->text = "oB";
        $teacher_row_header->cells[] = $cell;
        
        $cell = new html_table_cell();
        $cell->text = "iA";
        $teacher_row_header->cells[] = $cell;

        $rows[] = $teacher_row_header;
        
        $teacher_row = new html_table_row();
        $teacher_row->attributes['class'] = '';
        
        $cell = new html_table_cell();
        $cell->text = "L";
        $teacher_row->cells[] = $cell;
        
        foreach($teacher as $teacher_key => $teacher_value){
            $cell = new html_table_cell();
            $cell->text = ($teacher_value>0)?html_writer::tag('span', $teacher_value, array('title'=>$teacher_title[$teacher_key])):$teacher_value;
            $teacher_row->cells[] = $cell;
        }
        
        $cell = new html_table_cell();
        $cell->text = ($teacher_oB>0)?html_writer::tag('span', $teacher_oB, array('title'=>$teacher_oB_title)):$teacher_oB;
        $teacher_row->cells[] = $cell;
        
        $cell = new html_table_cell();
        $cell->text = ($teacher_iA>0)?html_writer::tag('span', $teacher_iA, array('title'=>$teacher_iA_title)):$teacher_iA;
        $teacher_row->cells[] = $cell;

        $rows[] = $teacher_row;
        
        $table->data = $rows;
        return html_writer::table($table);
    }
    public function print_example_pool($examples=array()){
        $content = html_writer::tag('h4', get_string('example_pool', 'block_exacomp'));
    
        foreach($examples as $example){
            $content .= html_writer::div($example->title, 'fc-event', array('exampleid'=>$example->exampleid));
        }
    
        return html_writer::div($content, '', array('id'=>'external-events'));
    }

    
    public function print_side_wrap_weekly_schedule(){
        $pool = $this->print_example_pool();
        $calendar = html_writer::div('', '', array('id'=>'calendar'));
        $trash = $this->print_example_trash();
        $clear = html_writer::div('', '', array('style'=>'clear:both'));
        
        return html_writer::div($pool.$calendar.$trash.$clear, '', array('id'=>'wrap'));
    }
    
    public function print_example_trash($trash_examples = array(), $persistent_trash=true){
        $content = html_writer::tag('h4', get_string('example_trash', 'block_exacomp'));
        
        foreach($trash_examples as $example){
            $content .= html_writer::div($example->title, 'fc-event');
        }
    
        if($persistent_trash) $content .= html_writer::empty_tag('input', array('type'=>'button', 'id'=>'empty_trash', 'value'=>get_string('empty_trash', 'block_exacomp')));
        return html_writer::div($content, '', array('id'=>'trash'));
    }
    public function print_course_dropdown($selectedCourse, $studentid=0){
        global $PAGE, $version, $DB;
        $content = get_string("choosecourse", "block_exacomp");
        $options = array();
        
        $courses = block_exacomp_get_courses();
        
        foreach($courses as $course){
            $course_db = $DB->get_record('course', array('id'=>$course));
            
            $options[$course] = $course_db->fullname;
        }
        
        $content .= html_writer::select($options, "lis_courses",$selectedCourse, false,
                array("onchange" => "document.location.href='".$PAGE->url."&studentid=".$studentid."&pool_course='+this.value;"));
        
        return $content;
    }
	
	public function print_view_example_header(){
		global $PAGE;
		$content = html_writer::empty_tag('input', array('type'=>'image', 'src'=>new moodle_url('/pix/i/withsubcat.png'), 'name'=>'comp_based', 'id'=>'comp_based', 
			'value'=>'comp_based', 'class'=>'view_examples_icon', 'onclick'=>"document.location.href='".$PAGE->url."&style=0';"))
			. html_writer::empty_tag('input', array('type'=>'image', 'src'=>new moodle_url('/pix/e/bullet_list.png'), 'name'=>'examp_based', 'id'=>'examp_based', 
			'value'=>'examp_based', 'class'=>'view_examples_icon', 'onclick'=>"document.location.href='".$PAGE->url."&style=1';"));

		return html_writer::div($content, '', array('id'=>'view_examples_header'));
	}
	
	public function print_example_based_list_tree($example, $tree, $isTeacher, $editmode, $showexamples = false){
		$html_tree = "";
        $html_tree .= html_writer::start_tag("ul",array("class"=>"collapsibleList"));
        
        $html_tree .= html_writer::start_tag("li", array('class'=>"associated"));
        $html_tree .= $example->title;
        
        $html_tree .= $this->print_competence_based_list_tree($tree, $isTeacher, $editmode, $showexamples);
        
        $html_tree .= html_writer::end_tag('li');
        $html_tree .= html_writer::end_tag('ul');
        return $html_tree;        
	}
	public function print_pre_planning_storage_students($students, $examples){
		$content = html_writer::start_tag('ul');
		foreach($students as $student){
			$student_has_examples = false;
			foreach($student->pool_examples as $example){
				if(in_array($example->exampleid, $examples))
					$student_has_examples = true;
			}
			
			$content .= html_writer::start_tag('li', array('class'=>($student_has_examples)?'has_examples':''));
			$content .= html_writer::empty_tag('input', array('type'=>'checkbox', 'id'=>'student_examp_mm', 'studentid'=>$student->id));
			$content .= $student->firstname." ".$student->lastname;
			$content .= html_writer::end_tag('li');
		}
		
		$content .= html_writer::end_tag('ul');
		
		return html_writer::div($content, 'external-students', array('id'=>'external-students'));
	}
	public function print_pre_planning_storage_pool(){
        $content = html_writer::tag('h4', get_string('example_pool', 'block_exacomp'));
    
        $content .= html_writer::tag('ul', '', array('id'=>'sortable'));
        return html_writer::div($content, 'external-events', array('id'=>'external-events'));
	}	
}
