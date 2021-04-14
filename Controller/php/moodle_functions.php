<?php

//load curl.php
require_once('curl.php');

function topicsTreeGrid($course_id, $server_id) {

    list($domainname, $wstoken) = getServerDetails($server_id);

    $wsfunctionname = 'core_course_get_contents';
    $restformat = 'json';

    $params = array('courseid' => $course_id);

//    header('Content-Type: application/json');
    $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;
    $curl = new curl;
    $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
    $resp = $curl->post($serverurl . $restformat, $params);
    $topics = json_decode($resp);

    //the php file must be recognized as XML document so necessary header sent
    header("Content-type:text/xml");
    //default xml file header (UTF-8 is a common value, but in some cases another encoding must be used)
    print('<?xml version="1.0" encoding="UTF-8"?>');
    //write top tag of xml document, the parent attribute contain id of parent row
    print ("<rows>");

    foreach ($topics as $topic) {

        echo '<row id="a_' . $topic->id . '">';
        echo '<cell image="folder.gif"><![CDATA[' . $topic->name . ']]></cell>';
        echo '<cell></cell>';
        echo '<cell>' . $topic->section . '</cell>';

        if (isset($topic->modules)) {
            foreach ($topic->modules as $module) {

                echo '<row id="b_' . $module->id . '">';
                echo '<cell><![CDATA[' . $module->name . ']]></cell>';
                echo '<cell>' . $module->modname . '</cell>';
                echo '<cell>' . $module->instance . '</cell>';

                if ($module->modname == 'lesson') {


                    $wsfunctionname = 'mod_lesson_get_pages';
                    $params = array('lessonid' => $module->instance);
                    $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;
                    $resp = $curl->post($serverurl . $restformat, $params);
                    $pages = json_decode($resp);

                    foreach ($pages->pages as $page) {

                        echo '<row id="c_' . $page->page->id . '">';
                        echo '<cell><![CDATA[' . $page->page->title . ']]></cell>';
                        echo '<cell>page</cell>';
                        echo '<cell>' . $page->page->id . '</cell>';
                        echo '</row>';
                    }
                }

                echo '</row>';
            }
        }

        echo '</row>';
    }
    //after drawing all childs of current row, the main tag must be closed

    print("</rows>");

    exit;

    //the php file must be recognized as XML document so necessary header sent
    header("Content-type:text/xml");
    //default xml file header (UTF-8 is a common value, but in some cases another encoding must be used)
    print('<?xml version="1.0" encoding="UTF-8"?>');
    //write top tag of xml document, the parent attribute contain id of parent row
    print ("<rows>");

    foreach ($result as $course) {
        printXML($course, true);
    }
    //after drawing all childs of current row, the main tag must be closed

    print("</rows>");
}

function printXML(stdClass $obj, $isRoot = false) {

    echo '<row id="' . ($isRoot ? 'a_' : '') . $obj->id . '">';
    if (!$isRoot) {
        echo '<cell>' . $obj->name . '</cell>';
    } else {
        echo '<cell image="folder.gif">' . $obj->name . '</cell>';
    }
    echo '<cell>' . $obj->modname . '</cell>';

    if (!$isRoot) {
        echo '<cell>' . $obj->instance . '</cell>';
    } else {
        echo '<cell>' . $obj->section . '</cell>';
    }

    if (isset($obj->modules)) {
        foreach ($obj->modules as $child) {
            printXML($child);
        }
    }
    echo '</row>';
}

function getServerDetails($server_id) {

    global $dbc;
    $query = "SELECT * FROM moodle_servers WHERE id =" . $server_id;
    $result = mysqli_query($dbc, $query);
    $row = mysqli_fetch_array($result);

    return [$row['path'], $row['token']];
}

function fetch_courses_by_field($course_id, $server_id) {

    list($domainname, $wstoken) = getServerDetails($server_id);

    $wsfunctionname = 'core_course_get_courses_by_field';
    $restformat = 'json';

    $params = array('field' => 'id', 'value' => $course_id);

//    header('Content-Type: application/json');
    $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;
    $curl = new curl;
    $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
    $resp = $curl->post($serverurl . $restformat, $params);
    $result = json_decode($resp);

//    print_r($result);exit;

    $data = array();
    $rows = array();

    foreach ($result->courses as $row) {
        echo "<row id = 'doc_" . $row->id . "'>";
        echo "<cell><![CDATA[" . $row->id . "]]></cell>";
        echo "<cell></cell>";
        echo "<cell></cell>";
        echo "<cell><![CDATA[" . $row->fullname . "]]></cell>";

        echo '<userdata name = "is_grid">1</userdata>';
        echo "</row>";
    }
}

function getloginurl($domainname, $useremail) {

    $token = 'aab4bfd6d2b31675978674039befb5c9';
    $functionname = 'auth_userkey_request_login_url';

    $param = [
        'user' => [
            'email' => $useremail
        ]
    ];

    $serverurl = $domainname . '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $functionname . '&moodlewsrestformat=json';

    $curl = new curl; // The required library curl can be obtained from https://github.com/moodlehq/sample-ws-clients 

    try {
        $resp = $curl->post($serverurl, $param);
//        var_dump($resp);exit;
        $resp = json_decode($resp);

        if ($resp && !empty($resp->loginurl)) {
            $loginurl = $resp->loginurl;
        }
    } catch (Exception $ex) {
        return false;
    }

    if (!isset($loginurl)) {
        return false;
    }

//    $path = '&wantsurl=' . urlencode("$domainname/course/modedit.php?add=lesson&type=&course=" . $courseid . "&section=" . $modname . "&return=0&sr=0");
//    $path = '';
//    if (isset($courseid)) {
//        $path = '&wantsurl=' . urlencode("$domainname/course/view.php?id=$courseid");
//    }
//    if (isset($modname) && isset($activityid)) {
//        $path = '&wantsurl=' . urlencode("$domainname/mod/$modname/view.php?id=$activityid");
//    }

    return $loginurl;
}
