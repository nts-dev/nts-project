<?php

include_once '../../../config/config.php';
require_once 'moodle_functions.php';

define('FETCH_COURSES', 1);
define('FETCH_COURSE_TOPICS', 2);
define('FETCH_COURSES_BY_FIELD', 3);
define('FETCH_TOPICS_DYNAMIC', 4);
define('FETCH_PAGE_CONTENT', 5);
define('FETCH_LESSON_PAGE_CONTENT', 6);
define('FETCH_LESSON_PAGES', 7);
define('EXPORT_PAGE_QUESTIONS', 8);


$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_NUMBER_INT);

switch ($action) {

    case FETCH_COURSES:

        $query = "SELECT * FROM moodle_servers";
        $result = mysqli_query($dbc, $query);

        $objects = array();
        $roots = array();

        header('Content-type:text/xml');
        echo '<?xml version="1.0"?>' . PHP_EOL;
        echo '<tree id="0">';

        while ($row = mysqli_fetch_array($result)) {

            echo "<item id='" . $row['id'] . "' text='" . $row['name'] . "'>";
            echo "<userdata name='path'>" . $row['path'] . "</userdata>";
            echo "<userdata name='token'>" . $row['token'] . "</userdata>";

            $domainname = $row['path']; //paste your domain here
            $wstoken = $row['token']; //here paste your enrol token 
            $wsfunctionname = 'core_course_get_courses';
            $restformat = 'json';

            $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;
            $curl = new curl;
            $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
            $resp = $curl->post($serverurl . $restformat);
            $courses = json_decode($resp);

            foreach ($courses as $course) {
                echo "<item id='" . $row['id'] . '_' . $course->id . "' text='" . $course->fullname . "' />";
            }

            echo '</item>';
        }
        echo '</tree>';

        break;

    case FETCH_COURSE_TOPICS:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $server_id = filter_input(INPUT_GET, 'server', FILTER_SANITIZE_NUMBER_INT);
        topicsTreeGrid($id, $server_id);

        break;


    case FETCH_COURSES_BY_FIELD:

        $course_id = filter_input(INPUT_GET, 'course', FILTER_SANITIZE_NUMBER_INT);
        $server_id = filter_input(INPUT_GET, 'server', FILTER_SANITIZE_NUMBER_INT);

//        fetch_courses_by_field($course_id, $server_id);

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        fetch_courses_by_field($course_id, $server_id);
        echo "</rows>";

        break;

    case FETCH_TOPICS_DYNAMIC:

        //the script receive a parent item id from GET scope as my_script.php?id=PARENT_ID
        //if parent id not sent - top level in related sample - then  set it equal to 0
        $parent = (!isset($_GET['id'])) ? 0 : $_GET['id'];
        $course_id = filter_input(INPUT_GET, 'course', FILTER_SANITIZE_NUMBER_INT);
        $server_id = filter_input(INPUT_GET, 'server', FILTER_SANITIZE_NUMBER_INT);

        list($domainname, $wstoken) = getServerDetails($server_id);
        $restformat = 'json';
        $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';

        //the php file must be recognized as XML document so necessary header sent
        header("Content-type:text/xml");
        //default xml file header (UTF-8 is a common value, but in some cases another encoding must be used)
        print('<?xml version="1.0" encoding="UTF-8"?>');

        if ($parent === 0) {

            $wsfunctionname = 'core_course_get_contents';
            $params = array('courseid' => $course_id);
            $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;

            $curl = new curl;
            $resp = $curl->post($serverurl . $restformat, $params);
            $topics = json_decode($resp);


            //write top tag of xml document, the parent attribute contain id of parent row
            print ("<rows parent='" . $parent . "'>");

            foreach ($topics as $topic) {

                $xmlkids = (isset($topic->modules) && count($topic->modules) > 0) ? '1' : '';

                echo '<row id="a_' . $topic->id . '" xmlkids="' . $xmlkids . '">';
                echo '<cell></cell>';
                echo '<cell image="folder.gif"><![CDATA[' . $topic->name . ']]></cell>';
                echo '<cell></cell>';
                echo '<cell>' . $topic->section . '</cell>';
                echo '</row>';
            }
            //after drawing all childs of current row, the main tag must be closed

            print("</rows>");
        } else {

            list($mode, $parent_id, $instance) = explode("_", $parent);

            if ($mode == 'a') {

                $wsfunctionname = 'core_course_get_contents';
                $params = array('courseid' => $course_id);
                $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;

                $curl = new curl;
                $resp = $curl->post($serverurl . $restformat, $params);
                $topics = json_decode($resp);

                $topic = null;

                foreach ($topics as $item) {

                    if ($parent_id == $item->id) {
                        $topic = $item;
                        break;
                    }
                }

                //write top tag of xml document, the parent attribute contain id of parent row
                print ("<rows parent='" . $_GET['id'] . "'>");

                if (isset($topic->modules)) {
                    foreach ($topic->modules as $module) {
                        echo '<row id="b_' . $module->id . '_' . $module->instance . '" xmlkids="' . (($module->modname == 'lesson') ? "1" : "") . '" >';
                        echo '<cell></cell>';
                        echo '<cell><![CDATA[' . $module->name . ']]></cell>';
                        echo '<cell>' . $module->modname . '</cell>';
                        echo '<cell>' . $module->instance . '</cell>';
                        echo '</row>';
                    }
                }
                //after drawing all childs of current row, the main tag must be closed

                print("</rows>");
            }

            if ($mode == 'b') {

                $wsfunctionname = 'mod_lesson_get_pages';
                $params = array('lessonid' => $instance);
                $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;

                $curl = new curl;
                $resp = $curl->post($serverurl . $restformat, $params);
                $pages = json_decode($resp);

                //write top tag of xml document, the parent attribute contain id of parent row
                print ("<rows parent='" . $_GET['id'] . "'>");

                foreach ($pages->pages as $page) {

                    echo '<row id="c_' . $page->page->id . '">';
                    echo '<cell></cell>';
                    echo '<cell><![CDATA[' . $page->page->title . ']]></cell>';
                    echo '<cell>page</cell>';
                    echo '<cell>' . $page->page->id . '</cell>';
                    echo '<cell>' . $page->page->prevpageid . '</cell>';
                    echo '<cell>' . $page->page->nextpageid . '</cell>';
                    echo '</row>';
                }
                //after drawing all childs of current row, the main tag must be closed

                print("</rows>");
            }
        }

        break;

    case FETCH_PAGE_CONTENT:

        $course_id = filter_input(INPUT_GET, 'course', FILTER_SANITIZE_NUMBER_INT);
        $module_id = filter_input(INPUT_GET, 'module', FILTER_SANITIZE_NUMBER_INT);
        $server_id = filter_input(INPUT_GET, 'server', FILTER_SANITIZE_NUMBER_INT);

        list($domainname, $wstoken) = getServerDetails($server_id);
        $wsfunctionname = 'mod_page_get_pages_by_courses';
        $restformat = 'json';

        $params = array('courseids' => array($course_id));

        header('Content-Type: application/json');
        $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;
        $curl = new curl;
        $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
        $resp = $curl->post($serverurl . $restformat, $params);
        $result = json_decode($resp);

        $item = null;
        foreach ($result->pages as $struct) {

            if ($module_id == $struct->coursemodule) {
                $item = $struct;
                break;
            }
        }

        echo json_encode(['item' => $item]);

        break;

    case FETCH_LESSON_PAGE_CONTENT:

        $lesson_id = filter_input(INPUT_GET, 'lesson', FILTER_SANITIZE_NUMBER_INT);
        $module_id = filter_input(INPUT_GET, 'module', FILTER_SANITIZE_NUMBER_INT);
        $server_id = filter_input(INPUT_GET, 'server', FILTER_SANITIZE_NUMBER_INT);

        list($domainname, $wstoken) = getServerDetails($server_id);

        /*
          $wsfunctionname = 'mod_lesson_get_page_data';
          $restformat = 'json';

          $params = array('lessonid' => $lesson_id, 'pageid' => $module_id);

          header('Content-Type: application/json');
          $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;
          $curl = new curl;
          $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
          $resp = $curl->post($serverurl . $restformat, $params);
          $result = json_decode($resp);

         */


        $params = array('lesson' => $lesson_id, 'module' => $module_id);
        $serverurl = $domainname . "/data_content.php?action=4";
        $curl = new curl;
        $resp = $curl->post($serverurl, $params);
        $result = json_decode($resp);

        $item = ['content' => $result->item->content];
        echo json_encode(['item' => $item]);
        break;

    case FETCH_LESSON_PAGES:

        $lesson_id = filter_input(INPUT_GET, 'lesson', FILTER_SANITIZE_NUMBER_INT);
        $server_id = filter_input(INPUT_GET, 'server', FILTER_SANITIZE_NUMBER_INT);

        list($domainname, $wstoken) = getServerDetails($server_id);
        $restformat = 'json';
        $restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';

        $wsfunctionname = 'mod_lesson_get_pages';
        $params = array('lessonid' => $lesson_id);
        $serverurl = $domainname . "/webservice/rest/server.php?wstoken=" . $wstoken . "&wsfunction=" . $wsfunctionname;

        $curl = new curl;
        $resp = $curl->post($serverurl . $restformat, $params);
        $pages = json_decode($resp);

        header("Content-type:text/xml");
        print('<?xml version="1.0" encoding="UTF-8"?>');

        echo '<complete>';
        foreach ($pages->pages as $page) {
            echo '<option value="' . $page->page->id . '">' . $page->page->title . '</option>';
        }
        echo '</complete>';

        break;

    case EXPORT_PAGE_QUESTIONS:

        $lesson_id = filter_input(INPUT_POST, 'lesson_id', FILTER_SANITIZE_NUMBER_INT);
        $page_id = filter_input(INPUT_POST, 'page_id', FILTER_SANITIZE_NUMBER_INT);
        $server_id = filter_input(INPUT_POST, 'server_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "
            SELECT
                question.id,
                question.title,
                question.text,
                question.type,
                question.qoption,
                choices.id choice_id,
                choices.text choice_text,
                choices.score,
                choices.response,
                choices.responseformat,
                choices.jumpto
            FROM
                project_course_question_to_page question_page
            JOIN project_course_question question ON question.id = question_page.question_id
            LEFT JOIN project_course_choices choices ON choices.question_id = question.id
            WHERE
                question_page.page_id = $page_id
            ORDER BY
                question.id";

        $result = mysqli_query($dbc, $query);

        $objects = [];
        $prevpageid = $page_id;

        while ($row = mysqli_fetch_array($result)) {

            if (!isset($objects[$row['id']])) {
                $objects[$row['id']] = new stdClass;
                $objects[$row['id']]->choices = [];
            }

            $question = $objects[$row['id']];
            $question->id = $row['id'];
            $question->qtype = $row['type'];
            $question->title = $row['title'];
            $question->contents = $row['text'];
            $question->qoption = $row['qoption'];


            if ($row['choice_id']) {

                $choice = new stdClass;
                $choice->id = $row['choice_id'];
                $choice->score = $row['score'];
                $choice->answer = $row['choice_text'];
                $choice->response = $row['response'];
                $choice->responseformat = $row['responseformat'];
                $choice->jumpto = $row['jumpto'];

                $question->choices[$row['choice_id']] = $choice;
            }
        }

        list($domainname, $wstoken) = getServerDetails($server_id);

        $serverurl = $domainname . "/data_content.php";

        $params = array(
            'lessonid' => $lesson_id,
            'prevpageid' => $prevpageid,
            'question' => serialize($objects)
        );

        $curl = new curl;
        $resp = $curl->post($serverurl . "?action=13", $params);
        $response = json_decode($resp);


        if ($response->success) {

            foreach ($response->page_ids as $pid => $mid) {
                $updatePages = "UPDATE project_course_question_to_page SET moodle_id = $mid,is_updated=0 WHERE question_id = $pid AND page_id = $page_id";
                mysqli_query($dbc, $updatePages);
            }

            foreach ($response->choice_ids as $pid => $mid) {
                $updateAnswers = "UPDATE project_course_choices SET moodle_id = $mid,is_updated=0 WHERE id = $pid";
                mysqli_query($dbc, $updateAnswers);
            }

            $data['data'] = array('response' => true, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => false, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    default:
        break;
}

