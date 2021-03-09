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

require __DIR__.'/inc.php';

require_capability('block/exacomp:admin', context_system::instance());

//$action = required_param('action', PARAM_ALPHANUMEXT);

//$output = block_exacomp_get_renderer();


//TODO: require secret? what for?

\block_exacomp\data::prepare();



block_exacomp\data_exporter::do_moodle_competencies_export(null);
die;

//$xml = block_exacomp\data_exporter::do_moodle_competencies_export(null);