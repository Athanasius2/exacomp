<?php
$services = array(
		'exacompservices' => array(                        //the name of the web service
				'functions' => array (
						'block_exacomp_get_courses',
						'block_exacomp_get_subjects',
						'block_exacomp_get_topics',
						'block_exacomp_get_subtopics',
						'block_exacomp_set_subtopic',
						'block_exacomp_get_competencies',
						'block_exacomp_set_competence',
						'block_exacomp_get_associated_content',
						'block_exacomp_get_assign_information',
						'block_exacomp_update_assign_submission',
						'block_exacomp_get_competence_by_id',
						'block_exacomp_get_topic_by_id',
						'block_exacomp_get_subtopics_by_topic',
                        'block_exacomp_get_examples_for_subject',
                        'block_exacomp_get_example_by_id',
						'block_exacomp_get_descriptors_for_example',
                        'block_exacomp_get_user_role',
				        'block_exacomp_get_external_trainer_students',
                        'block_exacomp_get_item_example_status',
						'block_exacomp_get_subjects_for_user',
				        'block_exacomp_get_item_for_example',
                        'block_exacomp_get_competencies_for_upload',
				        'block_exacomp_submit_example',
                        'block_exacomp_create_example',
						'block_exacomp_grade_item',
						'block_exacomp_get_item_grading',
						'block_exacomp_get_user_examples',
				        'block_exacomp_get_user_profile',
						'block_exacomp_update_example',
						'block_exacomp_get_competencies_by_topic',
						'block_exacomp_delete_item',
						'block_exacomp_delete_example', 
						'dakora_get_courses', 
						'dakora_get_topics_by_course',
						'dakora_get_descriptors', 
						'dakora_get_descriptor_children',
						'dakora_get_examples_for_descriptor', 
						'dakora_get_example_overview',
						'dakora_add_example_to_learning_calendar',
						'dakora_get_descriptors_for_example',
						'dakora_get_example_grading',
						'dakora_get_user_role',
						'dakora_get_students_for_course',
						'dakora_get_examples_pool',
						'dakora_set_example_time_slot',
						'dakora_remove_example_from_schedule',
						'dakora_get_examples_for_time_slot',
						'dakora_get_cross_subjects'
						), 	//web service functions of this service
				'restrictedusers' =>0,                      //if enabled, the Moodle administrator must link some user to this service
				//into the administration
				'enabled'=>1,                               //if enabled, the service can be reachable on a default installation
		)
);


$functions = array(
		'block_exacomp_get_courses' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_courses',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get courses with exacomp block instances.',    //human readable description of the web service function
				'type'        => 'read',                  //database rights of the web service function (read, write)
		),
		
		'block_exacomp_get_subjects' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_subjects',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get active subjects for a given course. Will usualy return exactly 1 subject, if so no dropdown selection for the user is neccessary',    //human readable description of the web service function
				'type'        => 'read',                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_topics' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_topics',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get topics for a given subject.',    //human readable description of the web service function
				'type'        => 'read',                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_subtopics' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_subtopics',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get subtopics for a given topic.',    //human readable description of the web service function
				'type'        => 'read',                  //database rights of the web service function (read, write)
		),
		'block_exacomp_set_subtopic' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'set_subtopic',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Set a users subtopic evaluation.',    //human readable description of the web service function
				'type'        => 'write',                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_competencies' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_competencies',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get subtopic competencies and teacher/student evaluation.',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_set_competence' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'set_competence',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Set a student evaluation for a particular competence',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_associated_content' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_associated_content',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get contents for a competence (exaport,example,assign)',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_assign_information' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_assign_information',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get information about a particular assign',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_update_assign_submission' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'update_assign_submission',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Updates info for particular assign',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_competence_by_id' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_competence_by_id',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get competence title',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		)
		,
		'block_exacomp_get_topic_by_id' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_topic_by_id',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get topic title',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_subtopics_by_topic' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_subtopics_by_topic',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get subtopics by topic id',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_examples_for_subject' => array(     //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_examples_for_subject',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get examples for subtopic',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_example_by_id' => array(     //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_example_by_id',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get example',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_descriptors_for_example' => array(     //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_descriptors_for_example',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get desciptors for example',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_user_role' => array(     //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_user_role',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get role for user: 1=trainer 2=student',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
        'block_exacomp_get_external_trainer_students' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_external_trainer_students',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get external trainer\'s students' ,    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_item_example_status' => array(    //web service function name
		        'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_item_example_status',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get Example Status',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		), 
		'block_exacomp_get_subjects_for_user' => array(    //web service function name
		        'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_subjects_for_user',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get Subjects',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		), 
		'block_exacomp_get_item_for_example' => array(    //web service function name
		        'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_item_for_example',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get Item',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		), 
		'block_exacomp_get_competencies_for_upload' => array(    //web service function name
		        'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_competencies_for_upload',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get competencetree',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		), 
		'block_exacomp_submit_example' => array(    //web service function name
		        'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'submit_example',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Submit example',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_create_example' => array(    //web service function name
		        'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'create_example',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Create an example',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_grade_item' => array(    //web service function name
		        'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'grade_item',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Grade an item',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_item_grading' => array(    //web service function name
		        'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_item_grading',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get grading of an item',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_user_examples' => array(    //web service function name
		        'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_user_examples',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get examples created by a specific user',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
        'block_exacomp_get_user_profile' => array(    //web service function name
                'classname'   => 'block_exacomp_external',  //class containing the external function
                'methodname'  => 'get_user_profile',          //external function name
                'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
                'description' => 'get a list of courses with their competencies',    //human readable description of the web service function
                'type'        => 'read'                  //database rights of the web service function (read, write)
        ),
		'block_exacomp_update_example' => array(    //web service function name
		        'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'update_example',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'update an example',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_competencies_by_topic' => array(
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_competencies_by_topic',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get competencies for a specific topic',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_delete_item' => array(
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'delete_item',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'delete a submitted and wrong item',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_delete_example' => array(
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'delete_example',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'delete a custom item',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'dakora_get_courses' => array (
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_courses',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get courses for user for dakora app',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_get_topics_by_course'=> array (
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_topics_by_course',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get topics for course for dakora app',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_get_descriptors'=> array (
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_descriptors',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get descriptors for topic for dakora app',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_get_descriptor_children' => array (
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_descriptor_children',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get children (childdescriptor and examples) for descriptor for dakora app',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_get_examples_for_descriptor' => array (
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_examples_for_descriptor',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get examples for descriptor for dakora app',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_get_example_overview' => array (
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_example_overview',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get example overview for dakora app',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_add_example_to_learning_calendar'  => array (
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_add_example_to_learning_calendar',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'add example to learning calendar for dakora',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'dakora_get_descriptors_for_example' => array (
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_descriptors_for_example',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get descriptors where example is associated',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_get_example_grading' => array (
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_example_grading',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get student and teacher evaluation for example',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_get_user_role' => array (
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_user_role',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get user role 1= trainer, 2= student',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_get_students_for_course'=> array (
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_students_for_course',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get list of students for course',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_get_examples_pool' => array(
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_examples_pool',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get list of examples for weekly schedule pool',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_set_example_time_slot' => array(
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_set_example_time_slot',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'set start and end time for example',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'dakora_remove_example_from_schedule' => array(
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_remove_example_from_schedule',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'remove example from weekly schedule',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'dakora_get_examples_for_time_slot' => array(
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_examples_for_time_slot',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get examples for a special start to end period (e.g. day)',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'dakora_get_cross_subjects' => array(
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'dakora_get_cross_subjects',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'get cross subjects',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		)
);