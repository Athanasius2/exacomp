<?php

define('IMPORT_SOURCE_NORMAL', 1);

$source = 1;
/**
 *
 * @param String $data xml content
 * @param int $source default is 1, for specific import 2 is used. A specific import can be done by teachers and only effects data from topic leven downwards (topics, descriptors, examples)
 * @param int $cron should always be 0, 1 if method is called by the cron job
 */
function block_exacomp_xml_do_import($data = null, $par_source = 1, $cron = 0) {
	global $DB,$CFG,$source;

	if($data == null)
		return false;

	$source = $par_source;
	/*
	 * LIBXML_NOCDATA is important at this point, because it converts CDATA Elements to Strings for
	* immediate useage
	*/
	$xml = simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOCDATA);

	if(isset($xml->table)){
		echo get_string('oldxmlfile', 'block_exacomp');
		return false;
	}
	if($source == IMPORT_SOURCE_NORMAL) {
		block_exacomp_xml_truncate(DB_SKILLS);
		if(isset($xml->skills)) {
			foreach($xml->skills->skill as $skill) {
				block_exacomp_insert_skill($skill);
			}
		}

		//niveaus are only updated within normal import -> TODO
		if(isset($xml->niveaus))
			foreach($xml->niveaus->niveau as $niveau) {
				block_exacomp_insert_niveau($niveau);
		}

		block_exacomp_xml_truncate(DB_TAXONOMIES);
		if(isset($xml->taxonomies)) {
			foreach($xml->taxonomies->taxonomy as $taxonomy) {
				block_exacomp_insert_taxonomy($taxonomy);
			}
		}

		if(isset($xml->categories)) {
			foreach($xml->categories->category as $category) {
				block_exacomp_insert_category($category);
			}
		}
	}
	
	if(isset($xml->examples)) {
		foreach($xml->examples->example as $example) {
			block_exacomp_insert_example($example);
		}
	}

	$crdate=time();
	if(isset($xml->descriptors)) {
    	foreach($xml->descriptors->descriptor as $descriptor) {
    		$descriptor->crdate = $crdate;
    		block_exacomp_insert_descriptor($descriptor);
    	}
	}
    if(isset($xml->crosssubjects)) {
        //insert empty draft as first entry
        block_exacomp_init_cross_subjects();
	    foreach($xml->crosssubjects->crosssubject as $crosssubject) {
	        block_exacomp_insert_crosssubject($crosssubject);
	    }
	}
	$insertedTopics = array();
	foreach($xml->edulevels->edulevel as $edulevel) {
		if($source == IMPORT_SOURCE_NORMAL)
			block_exacomp_insert_edulevel($edulevel);

		foreach($edulevel->schooltypes->schooltype as $schooltype) {
			$schooltype->elid = $edulevel->id;
			block_exacomp_insert_schooltype($schooltype);

			foreach($schooltype->subjects->subject as $subject) {
				$subject->stid = $schooltype->id;
					block_exacomp_insert_subject($subject);

				foreach($subject->topics->topic as $topic) {
					$topic->subjid = $subject->id;
					$insertedTopics[] = block_exacomp_insert_topic($topic);
				}
			}
		}
	}

	$founds = block_exacomp_xml_find_unused_descriptors($source,$crdate,implode(",", $insertedTopics));

	//block_exacomp_deleteIfNoSubcategories("block_exacompdescrexamp_mm","block_exacompdescriptors","id",$source,1,0,"descrid");
	block_exacomp_deleteIfNoSubcategories("block_exacompexamples","block_exacompdescrexamp_mm","exampid",$source,0);
	//block_exacomp_deleteIfNoSubcategories("block_exacompdescrtopic_mm","block_exacompdescriptors","id",$source,1,0,"descrid");
	block_exacomp_deleteIfNoSubcategories("block_exacomptopics","block_exacompdescrtopic_mm","topicid",$source,0,implode(",", $insertedTopics));
	block_exacomp_deleteIfNoSubcategories("block_exacompsubjects","block_exacomptopics","subjid",$source);
	block_exacomp_deleteIfNoSubcategories("block_exacompschooltypes","block_exacompsubjects","stid",$source);
	block_exacomp_deleteIfNoSubcategories("block_exacompedulevels","block_exacompschooltypes","elid",$source);

	block_exacomp_settstamp();
	
	return true;
}
function block_exacomp_insert_topic($topic, $parent = 0) {
	global $DB;
	$topic->sourceid = $topic['id']->__toString();
	$topic->parentid = $parent;

	if($stObj = $DB->get_record(DB_TOPICS, array("sourceid"=>$topic['id']->__toString()))) {
		$topic->id = $stObj->id;
		$DB->update_record(DB_TOPICS, simpleXMLElementToArray($topic));
	} else
		$topic->id = $DB->insert_record(DB_TOPICS, simpleXMLElementToArray($topic));

	if($topic->descriptors) {
		$DB->delete_records(DB_DESCTOPICS,array("topicid"=>$topic->id->__toString()));

		$i=1;
		foreach($topic->descriptors->descriptorid as $descriptor) {
			$descriptorid = $DB->get_field(DB_DESCRIPTORS, "id", array("sourceid"=>$descriptor['id']->__toString()));
			if($descriptorid > 0){
				$DB->insert_record(DB_DESCTOPICS, array("topicid"=>$topic->id->__toString(),"descrid"=>$descriptorid, "sorting"=>$i));
				$i++;
			}
		}
	}

	if($topic->children) {
		foreach($topic->children->topic as $child) {
			$child->subjid = $topic->subjid;
			block_exacomp_insert_topic($child,$topic->id);
		}
	}

	return $topic->id;
}
function block_exacomp_insert_subject(&$subject) {
	global $DB,$source;
	
	$subject->sourceid = $subject['id']->__toString();
	//$subject->source = $source;
	if($subject['categoryid'])
		$subject->catid = block_exacomp_get_database_id(DB_CATEGORIES,$subject['categoryid']->__toString());

	if($stObj = $DB->get_record(DB_SUBJECTS, array("sourceid"=>$subject['id']->__toString()))) {
		$subject->id = $stObj->id;
		$DB->update_record(DB_SUBJECTS, simpleXMLElementToArray($subject));
	} else
		$subject->id = $DB->insert_record(DB_SUBJECTS, simpleXMLElementToArray($subject));
}
function block_exacomp_insert_schooltype(&$schooltype) {
	global $DB,$source;
	
	if($source > IMPORT_SOURCE_NORMAL) {
		 if($dbschooltype = $DB->get_record(DB_SCHOOLTYPES, array("sourceid"=>$schooltype['id']->__toString(),"source"=>IMPORT_SOURCE_NORMAL)))
			$schooltype->id = $dbschooltype->id;
		
		return;
	}
	
	$schooltype->sourceid = $schooltype['id']->__toString();
	$schooltype->source = $source;

	if($stObj = $DB->get_record(DB_SCHOOLTYPES, array("sourceid"=>$schooltype['id']->__toString(),"source"=>$source))) {
		$schooltype->id = $stObj->id;
		$DB->update_record(DB_SCHOOLTYPES, simpleXMLElementToArray($schooltype));
	} else
		$schooltype->id = $DB->insert_record(DB_SCHOOLTYPES, simpleXMLElementToArray($schooltype));
}
function block_exacomp_insert_edulevel(&$edulevel) {
	global $DB,$source;
	$edulevel->sourceid = $edulevel['id']->__toString();
	$edulevel->source = $source;

	if($eduObj = $DB->get_record(DB_EDULEVELS, array("sourceid"=>$edulevel['id']->__toString(),"source"=>$source))) {
		$edulevel->id = $eduObj->id;
		$DB->update_record(DB_EDULEVELS, simpleXMLElementToArray($edulevel));
	} else
		$edulevel->id = $DB->insert_record(DB_EDULEVELS, simpleXMLElementToArray($edulevel));
}
function block_exacomp_insert_descriptor($descriptor, $parent = 0, $sorting = 0) {
	global $DB, $source;
	$descriptor->sourceid = $descriptor['id']->__toString();
	$descriptor->source = $source;
    
	if($parent > 0){
	    $descriptor->parentid = $parent;
		$descriptor->sorting = $sorting;
	}
	
	if($descriptor['skillid'])
		$descriptor->skillid = $descriptor['skillid']->__toString();
	if($descriptor['niveauid']) //niveaus have to be imported with normal import -> TODO
		$descriptor->niveauid = block_exacomp_get_database_id(DB_NIVEAUS,$descriptor['niveauid']->__toString());
	if($descriptor['categoryid'])
		$descriptor->catid = block_exacomp_get_database_id(DB_CATEGORIES,$descriptor['categoryid']->__toString());
	
	//if specific import and descriptor already normal imported -> return
	if($source != IMPORT_SOURCE_NORMAL) {
		if($descriptorObj = $DB->get_record(DB_DESCRIPTORS, array("sourceid"=>$descriptor['id']->__toString(),"source"=>IMPORT_SOURCE_NORMAL)))
			return;
	}

	//other way round: if normale import and descriptor already specific imported -> return
	if($source == IMPORT_SOURCE_NORMAL){
	    if($descriptorObj = $DB->get_record(DB_DESCRIPTORS, array("sourceid"=>$descriptor['id']->__toString(), "source"=>IMPORT_SOURCE_SPECIFIC)))
	        return;
	}
	
	//if descriptor already in db, imported from same source -> update
	if($descriptorObj = $DB->get_record(DB_DESCRIPTORS, array("sourceid"=>$descriptor['id']->__toString(),"source"=>$source))) {
		$descriptor->id = $descriptorObj->id;
		$descriptorarray = simpleXMLElementToArray($descriptor);
		if(!isset($descriptorarray['profoundness']))
			$descriptorarray['profoundness'] = 0;
		
		$DB->update_record(DB_DESCRIPTORS, $descriptorarray);
		$DB->delete_records(DB_DESCEXAMP,array("descrid" => $descriptor->id->__toString()));
	} else //descriptor not in db yet -> insert
		$descriptor->id = $DB->insert_record(DB_DESCRIPTORS, simpleXMLElementToArray($descriptor));

	if($descriptor->examples) {
		foreach($descriptor->examples->exampleid as $example) {
			$exampleid = $DB->get_field(DB_EXAMPLES, "id", array("sourceid"=>$example['id']->__toString(),"source"=>$source));
			//$exampleid = $examples->xpath('example[@id="'.$example['id']->__toString().'"]');
			//$conditions = array("descrid"=>$descriptor->id->__toString(),"exampid"=>$exampleid[0]->id->__toString());
			$conditions = array("descrid"=>$descriptor->id->__toString(),"exampid"=>$exampleid);

			//if(!$DB->record_exists(DB_DESCEXAMP, $conditions)) //all records deleted above delete_records(DB_DESCEXA...
				$DB->insert_record(DB_DESCEXAMP, $conditions);
		}
	}
	
	if($descriptor->children) {
		$sorting = 1;
		foreach($descriptor->children->descriptor as $child){
			block_exacomp_insert_descriptor($child,$descriptor->id, $sorting);
			$sorting++;
		}
	}

}
function block_exacomp_insert_category($category, $parent = 0) {
	global $DB, $source;
	$category->sourceid = $category['id']->__toString();
	$category->source = $source;
	$category->parentid = $parent;

	if($categoryObj = $DB->get_record(DB_CATEGORIES, array("sourceid"=>$category['id']->__toString(),"source" => $source))) {
		$category->id = $categoryObj->id;
		$DB->update_record(DB_CATEGORIES, simpleXMLElementToArray($category));
	} else
		$category->id = $DB->insert_record(DB_CATEGORIES, simpleXMLElementToArray($category));

	if($category->children) {
		foreach($category->children->category as $child)
			block_exacomp_insert_category($child,$category->id);
	}
}
function  block_exacomp_insert_crosssubject($crosssubject) {
    global $DB, $source;
    
    $crosssubject->sourceid = $crosssubject['id']->__toString();
    $crosssubject->source = $source;
    
    if($source != IMPORT_SOURCE_NORMAL) {
		if($crosssubjectObj = $DB->get_record(DB_CROSSSUBJECTS, array("sourceid"=>$crosssubject['id']->__toString(), "source" => IMPORT_SOURCE_NORMAL)))
			return;
	}
	
	if($crosssubjectObj = $DB->get_record(DB_CROSSSUBJECTS, array("sourceid"=>$crosssubject['id']->__toString(), "source" => $source))) {
		$crosssubject->id = $crosssubjectObj->id;
		$DB->update_record(DB_CROSSSUBJECTS, simpleXMLElementToArray($crosssubject));
	} else {
		$crosssubject->id = $DB->insert_record(DB_CROSSSUBJECTS, simpleXMLElementToArray($crosssubject));
	}
	
	//crosssubject in DB
	//insert descriptors
	
    if($crosssubject->descriptors) {
		$DB->delete_records(DB_DESCCROSS,array("crosssubjid"=>$crosssubject->id->__toString()));

		foreach($crosssubject->descriptors->descriptorid as $descriptor) {
			$descriptorid = $DB->get_field(DB_DESCRIPTORS, "id", array("sourceid"=>$descriptor['id']->__toString()));
			if($descriptorid > 0)
				$DB->insert_record(DB_DESCCROSS, array("crosssubjid"=>$crosssubject->id->__toString(),"descrid"=>$descriptorid));
		}
	}
    
    return $crosssubject->id;
}
function block_exacomp_insert_example($example, $parent = 0) {
	global $DB, $source;
	$example->sourceid = $example['id']->__toString();
	$example->source = $source;
	$example->parentid = $parent;
	if($example['taxid'])
		$example->taxid = block_exacomp_get_database_id(DB_TAXONOMIES,$example['taxid']->__toString(),$source);

	if($source != IMPORT_SOURCE_NORMAL) {
		if($exampleObj = $DB->get_record(DB_EXAMPLES, array("sourceid"=>$example['id']->__toString(), "source" => IMPORT_SOURCE_NORMAL)))
			return;
	}
	
	if($exampleObj = $DB->get_record(DB_EXAMPLES, array("sourceid"=>$example['id']->__toString(), "source" => $source))) {
		$example->id = $exampleObj->id;
		$DB->update_record(DB_EXAMPLES, simpleXMLElementToArray($example));
	} else {
		$example->id = $DB->insert_record(DB_EXAMPLES, simpleXMLElementToArray($example));
	}

	if($example->children) {
		foreach($example->children->example as $child)
			block_exacomp_insert_example($child,$example->id);
	}
}
function block_exacomp_insert_taxonomy($taxonomy, $parent = 0) {
	global $DB;
	$taxonomy->sourceid = $taxonomy['id']->__toString();
	$taxonomy->source = IMPORT_SOURCE_NORMAL;
	$taxonomy->parentid = $parent;
	$id = $DB->insert_record(DB_TAXONOMIES, simpleXMLElementToArray($taxonomy));

	if($taxonomy->children) {
		foreach($taxonomy->children->taxonomy as $child) {
			block_exacomp_insert_taxonomy($child,$id);
		}
	}
}

function block_exacomp_insert_skill($skill) {
	global $DB;
	$skill->sourceid = $skill['id']->__toString();
	$skill->source = IMPORT_SOURCE_NORMAL;
	$DB->insert_record(DB_SKILLS, simpleXMLElementToArray($skill));
}

function block_exacomp_insert_niveau($niveau, $parent = 0) {
	global $DB;
	$niveau->sourceid = $niveau['id']->__toString();
	$niveau->source = IMPORT_SOURCE_NORMAL;
	$niveau->parentid = $parent;
	
	if($niveauObj = $DB->get_record(DB_NIVEAUS, array("sourceid"=>$niveau['id']->__toString()))) {
		$niveau->id = $niveauObj->id;
		$DB->update_record(DB_NIVEAUS, simpleXMLElementToArray($niveau));
	} else
		$niveau->id = $DB->insert_record(DB_NIVEAUS, simpleXMLElementToArray($niveau));
	
	if($niveau->children) {
		foreach($niveau->children->niveau as $child) {
			block_exacomp_insert_niveau($child,$niveau->id);
		}
	}
}
/**
 * Moodle prohibits to use SimpleXML Objects as parameter values for $DB functions, therefore we need to convert
 * it to an array, which is done by encoding and decoding it as JSON.
 * Afterwards we need to filter the empty values, otherwise $DB functions throw warnings
 *
 * @param SimpleXMLElement $xmlobject
 */
function simpleXMLElementToArray(SimpleXMLElement $xmlobject) {
	$array = json_decode(json_encode((array)$xmlobject), true);
	$array_final = array();
	foreach($array as $key => $value){
	    if(is_array($value) && empty($value)){
	        $array_final[$key] = null;
	    }else{
	        $array_final[$key] = $value;
	    }
	}
	return $array_final;
}

function block_exacomp_get_database_id($table, $sourceid, $par_source = 1) {
	global $DB;
	return $DB->get_field($table, "id", array("sourceid" => $sourceid, "source" => $par_source));
}

function block_exacomp_xml_truncate($tablename) {
	global $DB, $source;
	$DB->delete_records($tablename,array("source" => $source));
}

/* this function deletes all categories if there are no subcategories
 i.e. if there are no topics to a subject, the subject can be deleted*/
function block_exacomp_deleteIfNoSubcategories($parenttable,$subtable,$subforeignfield,$source,$use_source_in_subtable=1,$pidlist="") {
	global $DB;
	$wherepid="";
	if ($use_source_in_subtable==1) $wheresource="source"; //zb source=1
	else $wheresource=$source; //zb 1=1
	if ($pidlist!="" AND $pidlist!="0") {
		$wherepid="AND (parentid NOT IN (".$pidlist.") OR parentid IS NULL)";
	}
	$sql='SELECT * FROM {'.$parenttable.'} pt WHERE source=? AND id NOT IN(Select '.$subforeignfield.' FROM {'.$subtable.'} WHERE '.$wheresource.'=? AND '.$subforeignfield.'=pt.id)';
	$sql='SELECT * FROM {'.$parenttable.'} pt WHERE source=? '.$wherepid.' AND id NOT IN(Select '.$subforeignfield.' FROM {'.$subtable.'} WHERE '.$wheresource.'=?)';

	$todelets = $DB->get_records_sql($sql,array($source,$source));
	foreach ($todelets as $todelete) {
		$DB->delete_records($parenttable, array("id" => $todelete->id));
	}
}
function block_exacomp_xml_find_unused_descriptors($source,$crdate,$topiclist){
	global $DB;

	/* descriptoren löscent, wenn sie

	1) nicht im xml sind (crdate <> $crdate)
	2) nicht einer aktivität zugeordnet sind
	3) wenn es keine schüler/lehrer bewertung dazu direkt oder bei einer aktivität gibt
	4) wenn der zugehörige topic nirgends augewählt ist (bei settings/subjectselection)
	5) wenn der zugehörige schultyp nirgends augewählt ist (bei modulkonfiguration/schultypauswahl)
	6) wenn kein selbst hinaufgeladenes beispiel drannhängt
	*/

	$sql="SELECT distinct descr.id,descr.sourceid FROM {block_exacompcompuser} u
	RIGHT JOIN {block_exacompdescriptors} descr ON descr.id=u.compid
	JOIN {block_exacompdescrtopic_mm} tmm ON tmm.descrid=descr.id
	JOIN {block_exacomptopics} top ON top.id=tmm.topicid
	JOIN {block_exacompsubjects} subj ON subj.id=top.subjid
	JOIN {block_exacompschooltypes} st ON st.id=subj.stid
	LEFT JOIN {block_exacompcoutopi_mm} cou ON cou.topicid=tmm.topicid
	LEFT JOIN ({block_exacompdescrexamp_mm} emm
	JOIN {block_exacompexamples} ex ON (ex.id=emm.exampid AND ex.source=3)) ON emm.descrid=descr.id
	LEFT JOIN {block_exacompmdltype_mm} typmm ON typmm.stid=st.id
	LEFT JOIN {block_exacompcompuser_mm} umm ON umm.compid=descr.id
	LEFT JOIN {block_exacompcompactiv_mm} act ON act.compid=descr.id
	WHERE typmm.id IS NULL AND ex.id IS NULL AND act.id IS NULL AND cou.id IS NULL AND  umm.id IS NULL AND u.id IS NULL AND descr.source=? AND descr.crdate <> (?)";

	$rs=$DB->get_records_sql($sql, array($source, $crdate));
	foreach($rs as $row){
		$DB->delete_records('block_exacompdescriptors', array("id" => $row->id));
		//topic, auch prüfen ob untertopics vorhanden, den dann nicht löschen
		$sql="DELETE FROM {block_exacompdescrtopic_mm} WHERE descrid=? AND topicid NOT IN (".$topiclist.")";
		$DB->Execute($sql, array($row->id));
		$DB->delete_records('block_exacompdescrexamp_mm', array("descrid" => $row->id));
	}
}

/**
 * checks if data is imported
 */
function block_exacomp_xml_check_import() {
	global $DB;
	return ($DB->get_records('block_exacompdescriptors')) ? true : false;
}
function block_exacomp_xml_check_custom_import() {
    global $DB;
    return ($DB->get_records(DB_DESCRIPTORS,array("source" => IMPORT_SOURCE_SPECIFIC))) ? true : false;
}
function block_exacomp_delete_custom_competencies() {
    global $DB;
    $DB->delete_records(DB_SUBJECTS,array('source' => IMPORT_SOURCE_SPECIFIC));
    $DB->delete_records(DB_TOPICS,array('source' => IMPORT_SOURCE_SPECIFIC));
    $DB->delete_records(DB_DESCRIPTORS,array('source' => IMPORT_SOURCE_SPECIFIC));
    $examples = $DB->get_records(DB_EXAMPLES,array('source' => IMPORT_SOURCE_SPECIFIC));
    foreach($examples as $example) 
        block_exacomp_delete_custom_example($example->id);
    
    return true;
}

global $CFG;
require_once $CFG->libdir . '/formslib.php';

class block_exacomp_xml_upload_form extends moodleform {

	function definition() {
		global $CFG, $USER, $DB;
		$mform = & $this->_form;

		$this->_form->_attributes['action'] = $_SERVER['REQUEST_URI'];
		$mform->addElement('header', 'comment', get_string("doimport_own", "block_exacomp"));

		$mform->addElement('filepicker', 'file', get_string("file"),null);
		$mform->addRule('file', get_string("commentshouldnotbeempty", "block_exacomp"), 'required', null, 'client');

		$this->add_action_buttons(false, get_string('add'));

	}

}

class block_exacomp_generalxml_upload_form extends moodleform {

	function definition() {
		global $CFG, $USER, $DB;
		$mform = & $this->_form;

		$importtype = optional_param('importtype', 'normal', PARAM_TEXT);

		$this->_form->_attributes['action'] = $_SERVER['REQUEST_URI'];
		$check = block_exacomp_xml_check_import();
		if($importtype == 'custom') {
			$mform->addElement('header', 'comment', get_string("doimport_own", "block_exacomp"));
		}
		elseif($check){
			$mform->addElement('header', 'comment', get_string("doimport", "block_exacomp"));
		} else
			$mform->addElement('header', 'comment', get_string("doimport_again", "block_exacomp"));


		$mform->addElement('filepicker', 'file', get_string("file"),null);
		$mform->addRule('file', null, 'required', null, 'client');

		$this->add_action_buttons(false, get_string('add'));

	}

}