<?php
//include and instantiate UM API class
include_once 'access-class.php';
$webservice = new UMapis;

//get incoming method param to determine routing
switch ($_REQUEST['method']) {
	case 'get_person':
		$json = $webservice->get_person($_REQUEST['uniqname']);
		$out = $json;
		break;
	case 'search_people':
		$json = $webservice->search_people($_REQUEST['query']);
		$out = $json;
		break;
	case 'get_campuses':
		$json = $webservice->get_campuses();
		$out = $json;
		break;
	case 'get_buildings':
		$json = $webservice->get_buildings($_REQUEST['campus']);
		$out = $json;
		break;
	case 'get_building':
		$json = $webservice->get_building($_REQUEST['building']);
		$out = $json;
		break;
	case 'get_buildings_nearby':
		$json = $webservice->get_buildings_nearby($_REQUEST['latitude'], $_REQUEST['longitude']);
		$out = $json;
		break;
	case 'get_terms':
		$json = $webservice->get_terms();
		$out = $json;
		break;
	case 'get_schools':
		$json = $webservice->get_schools($_REQUEST['term']);
		$out = $json;
		break;
	case 'get_subjects':
		$json = $webservice->get_subjects($_REQUEST['term'], $_REQUEST['school']);
		$out = $json;
		break;
	case 'get_catalogs':
		$json = $webservice->get_catalogs($_REQUEST['term'], $_REQUEST['school'], $_REQUEST['subject']);
		$out = $json;
		break;
	case 'get_sections':
		$json = $webservice->get_sections($_REQUEST['term'], $_REQUEST['school'], $_REQUEST['subject'], $_REQUEST['catalog']);
		$out = $json;
		break;
	case 'get_course_description':
		$json = $webservice->get_course_description($_REQUEST['term'], $_REQUEST['school'], $_REQUEST['subject'], $_REQUEST['catalog']);
		$out = $json;
		break;
	case 'get_course_meetings':
		$json = $webservice->get_course_meetings($_REQUEST['term'], $_REQUEST['school'], $_REQUEST['subject'], $_REQUEST['catalog'], $_REQUEST['section']);
		$out = $json;
		break;
	case 'get_course_instructors':
		$json = $webservice->get_course_instructors($_REQUEST['term'], $_REQUEST['school'], $_REQUEST['subject'], $_REQUEST['catalog'], $_REQUEST['section']);
		$out = $json;
		break;
	case 'get_course_textbooks':
		$json = $webservice->get_course_textbooks($_REQUEST['term'], $_REQUEST['school'], $_REQUEST['subject'], $_REQUEST['catalog'], $_REQUEST['section']);
		$out = $json;
		break;
	case 'search_classes':
		$json = $webservice->search_classes($_REQUEST['term'], $_REQUEST['query']);
		$out = $json;
		break;
	case 'get_class':
		$json = $webservice->get_class($_REQUEST['term'], $_REQUEST['class']);
		$out = $json;
		break;
		
	default:
		$out = 'No method parameter was supplied - API cannot be called';
		break;

} //end switch

echo $out;

?>
