<?php

include_once '../../../config/config.php';
require_once 'moodle_functions.php';

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_NUMBER_INT);


switch ($action) {

    default:

        $courseid = filter_input(INPUT_GET, 'course', FILTER_SANITIZE_NUMBER_INT);
        $modname = filter_input(INPUT_GET, 'section', FILTER_SANITIZE_NUMBER_INT);
        $domainname = 'https://education.nts.nl';
        $loginurl = getloginurl($domainname, 'abdallah@nts.nl');
        $path = '&wantsurl=' . urlencode("$domainname/course/modedit.php?add=lesson&type=&course=" . $courseid . "&section=" . $modname . "&return=0&sr=0");
        header("location: " . $loginurl . $path); //getloginurl('geoffrey', 18);
        break;

    case 1:

        $lesson_id = filter_input(INPUT_GET, 'lesson', FILTER_SANITIZE_NUMBER_INT);

        $domainname = 'https://education.nts.nl';
        $loginurl = getloginurl($domainname, 'abdallah@nts.nl');
        $path = '&wantsurl=' . urlencode("$domainname/mod/lesson/edit.php?id=" . $lesson_id);
        header("location: " . $loginurl . $path);
        break;


    case 2:

        $lesson_id = filter_input(INPUT_GET, 'lesson', FILTER_SANITIZE_NUMBER_INT);
        $page_id = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);

        $domainname = 'https://education.nts.nl';
        $loginurl = getloginurl($domainname, 'abdallah@nts.nl');
        $path = '&wantsurl=' . urlencode("$domainname/mod/lesson/edit.php?id=" . $lesson_id . "&mode=single&pageid=" . $page_id);
        header("location: " . $loginurl . $path);
        break;

    case 3:

        $lesson_id = filter_input(INPUT_GET, 'lesson', FILTER_SANITIZE_NUMBER_INT);
        $instance_id = filter_input(INPUT_GET, 'instance', FILTER_SANITIZE_NUMBER_INT);
        $server_id = filter_input(INPUT_GET, 'server', FILTER_SANITIZE_NUMBER_INT);
        $page_id = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?: 0;
        $type = filter_input(INPUT_GET, 'type');

        list($domainname, $wstoken) = getServerDetails($server_id);


        $restformat = 'json';
        $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
        $wsfunctionname = 'mod_lesson_get_pages';
        $params = array('lessonid' => $instance_id);
        $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;

        $curl = new curl;
        $resp = $curl->post($serverurl . $restformat, $params);
        $pages = json_decode($resp);

        $firstpage = count($pages->pages) > 0 ? '' : '&firstpage=1';
        
        $loginurl = getloginurl($domainname, 'abdallah@nts.nl');

        if ($type == 'page') {
            $path = '&wantsurl=' . urlencode("$domainname/mod/lesson/editpage.php?id=" . $lesson_id . "&pageid=$page_id&qtype=20" . $firstpage);
        }

        if ($type == 'quiz') {
            $path = '&wantsurl=' . urlencode("$domainname/mod/lesson/editpage.php?id=" . $lesson_id . "&pageid=$page_id" . $firstpage);
            
        }
        header("location: " . $loginurl . $path);
        break;
}