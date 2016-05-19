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

namespace block_exacomp;

use block_exacomp\globals as g;

// we want cookies, so moodle remembers the login
define('NO_MOODLE_COOKIES', false);

require __DIR__.'/inc.php';
require_once $CFG->dirroot . '/webservice/lib.php';

//authenticate the user
$wstoken = required_param('wstoken', PARAM_ALPHANUM);
$url = required_param('url', PARAM_LOCALURL);
if (!$url) {
	// if not localurl, moodle returns empty string
	$url = '/';
}

$webservicelib = new \webservice();
$authenticationinfo = $webservicelib->authenticate_user($wstoken);

// check if it is a exacomp token
if ($authenticationinfo['service']->name != 'exacompservices') {
	throw new moodle_exception('not an exacomp webservice token');
}

redirect(new \moodle_url($url));
