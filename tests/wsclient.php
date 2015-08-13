<?php
// This file is NOT a part of Moodle - http://moodle.org/
//
// This client for Moodle 2 is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

$domainname = 'http://gtn-solutions.com/moodle29';

$params = new stdClass();

require_once('./curl.php');
$curl = new curl;
$token_google = 879878;

print_r($token_google);
echo "



";
$serverurl = 'http://gtn-solutions.com/moodle29/login/token.php?username=student2&password=Student2!&token='.$token_google.'&service=exacompservices';
$resp = $curl->get($serverurl);
$resp = json_decode($resp)->token;
$token = $resp;
print_r($token);
echo "



";

$serverurl_exaport = 'http://gtn-solutions.com/moodle29/login/token.php?username=student2&password=Student2!&token='.$token_google.'&service=exaportservices';
$resp_exaport = $curl->get($serverurl_exaport);
$resp_exaport = json_decode($resp_exaport)->token;
print_r($resp_exaport);
echo "



";

$serverurl_moodle = 'http://gtn-solutions.com/moodle29/login/token.php?username=student2&password=Student2!&token='.$token_google.'&service=moodle_mobile_app';
$resp_moodle = $curl->get($serverurl_exaport);
$resp_moodle = json_decode($resp_moodle)->token;
print_r($resp_moodle);
echo "

courses:

";

/// REST CALL BLOCK_EXACOMP_GET_COURSES
header('Content-Type: text/plain');

$functionname = 'dakora_get_courses';
$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;

$params = new stdClass();
$params->userid = 0;

$resp = $curl->get($serverurl, $params);
print_r($resp);
echo "

topics:

";

/// REST CALL BLOCK_EXACOMP_GET_TOPICS_BY_COURSE

$functionname = 'dakora_get_topics_by_course';

$params = new stdClass();
$params->courseid = 3;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

descriptors:

";

/// REST CALL dakora_get_descriptors

$functionname = 'dakora_get_descriptors';

$params = new stdClass();
$params->courseid = 3;
$params->topicid = 13;
$params->userid = 0;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

descriptors children:

";

/// REST CALL dakora_get_descriptor_children
$functionname = 'dakora_get_descriptor_children';

$params = new stdClass();
$params->courseid = 3;
$params->descriptorid = 326;
$params->userid = 0;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

examples

";

/// REST CALL dakora_get_descriptor_children
$functionname = 'dakora_get_examples_for_descriptor';

$params = new stdClass();
$params->courseid = 3;
$params->descriptorid = 327;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

example overview

";

/// REST CALL dakora_get_descriptor_children
$functionname = 'dakora_get_example_overview';

$params = new stdClass();
$params->exampleid = 33;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

add example to schedule

";

/// REST CALL dakora_add_example_to_learning_calendar
$functionname = 'dakora_add_example_to_learning_calendar';

$params = new stdClass();
$params->courseid = 3;
$params->exampleid = 33;
$params->creatorid = 5;
$params->studentid = 0;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

descriptors for example

";



// REST CALL dakora_get_descriptors_for_example
$functionname = 'dakora_get_descriptors_for_example';

$params = new stdClass();
$params->exampleid = 33;
$params->courseid = 3;
$params->userid = 4;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

example grading:

";
// REST CALL dakora_get_example_grading
$functionname = 'dakora_get_example_grading';

$params = new stdClass();
$params->exampleid = 33;
$params->courseid = 3;
$params->studentid = 4;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

user role:

";

//REST CALL dakora_get_user_role
$functionname = 'dakora_get_user_role';

$params = new stdClass();
$params->courseid = 3;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "


";