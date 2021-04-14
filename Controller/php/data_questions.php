<?php

ini_set('display_errors', '0');
session_start();
require 'config_mysqli.php';
include("GeneralFunctions.php");
date_default_timezone_set('UTC');
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_NUMBER_INT);

define('SHORT_ANSWER', 1);
define('TRUE_FALSE', 2);
define('MULTICHOICE', 3);
define('NUMERICAL', 8);
define('ESSAY', 10);
define('MATCHING', 5);

switch ($action) {

    default:
        break;

    case 1://create question

        $course_id = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_NUMBER_INT);
        $page_id = filter_input(INPUT_POST, 'page_id', FILTER_SANITIZE_NUMBER_INT);
        $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);

        $insert = "INSERT INTO project_course_question (`course_id`,`type`) VALUES (" . $course_id . ",'" . $type . "')";

        $insertResult = mysqli_query($dbc,$insert) ;
        if ($insertResult) {
            $question_id = mysqli_insert_id($dbc);

            $answers = array();

            switch ($type) {

                case ESSAY:
                    $answers[] = "($question_id,null,-1,1,null)";
                    break;

                case SHORT_ANSWER:
                case NUMERICAL:

                    $answers[] = "($question_id,null,-1,1,'correct')";
                    $answers[] = "($question_id,'@#wronganswer#@',-1,0,'wrong')";

                    break;

                case TRUE_FALSE:

                    $answers[] = "($question_id,'Yes',-1,1,'correct')";
                    $answers[] = "($question_id,'No',-1,0,'wrong')";
                    break;

                case MULTICHOICE:

                    $answers[] = "($question_id,null,-1,1,'correct')";
                    break;

                case MATCHING:
                    break;
            }

            if (count($answers) > 0) {
                $insertAnswers = "INSERT INTO project_course_choices (question_id,text,jumpto,score,response) VALUES " . implode(",", $answers);
                $insertAnswersResult = mysqli_query($dbc,$insertAnswers) ;
            }

            if ($page_id) {

                $insert = "INSERT INTO project_course_question_to_page (`question_id`,`page_id`, `sort_id`) SELECT " . $question_id . "," . $page_id . ",IF((MAX(sort_id)>0),MAX(sort_id)+1,1)sort_id FROM project_course_question_to_page WHERE page_id = " . $page_id;

                $insertResult = mysqli_query($dbc,$insert) ;

                if ($insertResult) {

                    $newId = mysqli_insert_id($dbc);
                    $data['data'] = array('response' => $insertResult, 'text' => 'Successfully Added', 'row_id' => $newId);
                } else {
                    $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
                }
            } else {
                $data['data'] = array('response' => $insertResult, 'text' => 'Successfully Added', 'row_id' => $question_id);
            }
        } else {
            $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);
        break;

    case 2://update question

        $index = filter_input(INPUT_POST, 'index');
        $fieldvalue = filter_input(INPUT_POST, 'fieldvalue');
        $id = filter_input(INPUT_POST, 'id');
        $field = filter_input(INPUT_POST, 'colId');
        $colType = filter_input(INPUT_POST, 'colType');
//        $fieldvalue = mysqli_real_escape_string($dbc,$fieldvalue);

        $updateResult = updateSQL("project_course_question", $field, $fieldvalue, $id, "id", $colType);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 3://remove question from page

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        // Get details of selected record.
        $sql = "SELECT page_id,sort_id FROM project_course_question_to_page WHERE id = " . $id;
        $result = mysqli_query($dbc,$sql);
        $row = mysqli_fetch_assoc($result);
        $sortorder = $row['sort_id'];
        $parent_id = $row['page_id'];

        $delete = "DELETE FROM project_course_question_to_page WHERE id = " . $id;
        $deleteResult = mysqli_query($dbc,$delete);
        if ($deleteResult) {
            if ($sortorder > 1) {
                // Update remaining records.
                $sql = "UPDATE project_course_question_to_page SET sort_id = sort_id-1 WHERE page_id = $parent_id AND sort_id > $sortorder";
                $updated = mysqli_query($dbc,$sql);
            }
            $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
        } else {
            $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
        }
        echo json_encode($data);
        break;

    case 4://fetch page questions

        $page_id = filter_input(INPUT_GET, 'page_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "
            SELECT 
              project_course_question_to_page.id,
              project_course_question_to_page.question_id,
              project_course_question.title,
              project_course_question.text,
              project_course_question.type 
            FROM
              project_course_question_to_page 
              JOIN project_course_question 
                ON project_course_question.id = project_course_question_to_page.question_id 
            WHERE project_course_question_to_page.page_id = " . $page_id;

        $res = mysqli_query($dbc,$query) ;
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {
            echo "<row id = '" . $row["id"] . "'>";
            echo "<cell>" . $row["question_id"] . "</cell>";
            echo "<cell><![CDATA[" . $row["title"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["text"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["type"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 5://create choices

        $question_id = filter_input(INPUT_POST, 'question_id', FILTER_SANITIZE_NUMBER_INT);

        $insert = "INSERT INTO project_course_choices (`question_id`) VALUES ($question_id)";
        $insertResult = mysqli_query($dbc,$insert) ;

        if ($insertResult) {
            $data['data'] = array('response' => $insertResult, 'text' => 'Successfully Added', 'row_id' => mysqli_insert_id($dbc));
        } else {
            $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;


    case 6://delete choices

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $delete = "DELETE FROM project_course_choices WHERE id = " . $id;
        $deleteResult = mysqli_query($dbc,$delete) ;

        if ($deleteResult) {
            $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
        } else {
            $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
        }
        echo json_encode($data);
        break;


    case 7://delete question

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $delete = "DELETE FROM project_course_question WHERE id = " . $id;
        $deleteResult = mysqli_query($dbc,$delete) ;

        if ($deleteResult) {
            $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
        } else {
            $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
        }
        break;

    case 8://fetch questions

        $id = filter_input(INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT);
        $page_id = filter_input(INPUT_GET, 'page_id', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT * FROM project_course_question WHERE course_id =" . $id." ORDER BY id";

        if ($page_id) {
            $query = "SELECT * FROM project_course_question WHERE course_id =" . $id . " AND id NOT IN (SELECT question_id FROM project_course_question_to_page WHERE page_id = $page_id)";
        }
        $result = mysqli_query($dbc,$query) ;

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';

        while ($row = mysqli_fetch_array($result)) {

            $type = $page_id ? getQuestionTypeName($row["type"]) : $row["type"];

            echo "<row id = '" . $row["id"] . "'>";
            if ($page_id)
                echo "<cell></cell>";
            echo "<cell>" . $row["id"] . "</cell>";
            echo "<cell><![CDATA[" . $row["title"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["text"] . "]]></cell>";
            echo "<cell><![CDATA[" . $type . "]]></cell>";
            echo "</row>";
        }

        echo "</rows>";
        break;

    case 9://fetch choices

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $query = "
            SELECT 
              * 
            FROM
              project_course_choices
            WHERE question_id=" . $id;

        $res = mysqli_query($dbc,$query) ;
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {
            echo "<row id = '" . $row["id"] . "'>";
            echo "<cell></cell>";
            echo "<cell><![CDATA[" . $row["text"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["response"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["score"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["jumpto"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 10://update choice

        $index = filter_input(INPUT_POST, 'index');
        $fieldvalue = filter_input(INPUT_POST, 'fieldvalue');
        $id = filter_input(INPUT_POST, 'id');
        $field = filter_input(INPUT_POST, 'colId');
        $colType = filter_input(INPUT_POST, 'colType');
//        $fieldvalue = mysqli_real_escape_string($dbc,$fieldvalue);

        $updateResult = updateSQL("project_course_choices", $field, $fieldvalue, $id, "id", $colType);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;


    case 11:

        $fieldvalue = filter_input(INPUT_POST, "nValue");
        $id = filter_input(INPUT_POST, "id");
        $field = filter_input(INPUT_POST, "colId");

        $updateResult = updateSQL("project_course_choices", $field, $fieldvalue, $id, "id", $colType);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 12:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT * FROM project_course_question WHERE id =" . $id;

        $res = mysqli_query($dbc,$query) ;
        $row = mysqli_fetch_array($res);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<data>';
        echo "<title><![CDATA[" . $row["title"] . "]]></title>";
        echo "<text><![CDATA[" . $row["text"] . "]]></text>";
        echo "<type><![CDATA[" . $row["type"] . "]]></type>";
        echo "<qoption><![CDATA[" . $row["qoption"] . "]]></qoption>";
        echo "</data>";
        break;


    case 13:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $title = filter_input(INPUT_POST, 'title');
        $text = filter_input(INPUT_POST, 'text');
        $type = filter_input(INPUT_POST, 'type');
        $qoption = filter_input(INPUT_POST, 'qoption');


        $update = "UPDATE project_course_question SET title='$title',text='$text',type='$type',qoption=$qoption WHERE id =" . $id;

        $updateResult = mysqli_query($dbc,$update) or die(mysqli_error($dbc) . $update);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);


        break;

    case 14:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $query = "
            SELECT 
              * 
            FROM
              project_course_choices
            WHERE id =" . $id;

        $res = mysqli_query($dbc,$query) ;
        $row = mysqli_fetch_array($res);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<data>';
        echo "<response><![CDATA[" . $row["response"] . "]]></response>";
        echo "<text><![CDATA[" . $row["text"] . "]]></text>";
        echo "<score><![CDATA[" . $row["score"] . "]]></score>";
        echo "<jumpto><![CDATA[" . $row["jumpto"] . "]]></jumpto>";
        echo "</data>";
        break;


    case 15:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $response = filter_input(INPUT_POST, 'response');
        $text = filter_input(INPUT_POST, 'text');
        $score = filter_input(INPUT_POST, 'score');
        $qoption = filter_input(INPUT_POST, 'jumpto');


        $update = "UPDATE project_course_choices SET response='$response',text='$text',score=$score,jumpto='$jumpto' WHERE id =" . $id;

        $updateResult = mysqli_query($dbc,$update) or die(mysqli_error($dbc) . $update);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);


        break;

    case 16:

        $page_id = filter_input(INPUT_POST, 'page_id', FILTER_SANITIZE_NUMBER_INT);
        $ids = filter_input(INPUT_POST, 'ids');

        $rowIds = explode(",", $ids);

        foreach ($rowIds as $question_id) {
            $insert = "INSERT INTO project_course_question_to_page (`question_id`,`page_id`, `sort_id`) SELECT " . $question_id . "," . $page_id . ",IF((MAX(sort_id)>0),MAX(sort_id)+1,1)sort_id FROM project_course_question_to_page WHERE page_id = " . $page_id;

            $insertResult = mysqli_query($dbc,$insert) ;
        }

        $data['data'] = array('response' => $insertResult, 'text' => 'Successfully Added');
        echo json_encode($data);
        break;
}

function getQuestionTypeName($type) {

    switch ($type) {
        case SHORT_ANSWER:
            return 'SHORT ANSWER';
        case TRUE_FALSE:
            return 'TRUE FALSE';
        case MULTICHOICE:
            return 'MULTICHOICE';
        case NUMERICAL:
            return 'NUMERICAL';
        case ESSAY:
            return 'ESSAY';
        case MATCHING:
            return 'MATCHING';
        default:
            return 'SHORT ANSWER';
    }
}
