<?php
include_once '../../../config/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$query = "
        SELECT
                event_id,
                IFNULL(event_pjd,0) event_pjd,
                details,
                start_date,
                end_date,
                event_name,
                protection,
                personal,
                visible,
                completed,
                target,
                result,
                tag_id,
                CONCAT(COALESCE(FirstName,''),' ',COALESCE(SecondName,''),' ',COALESCE(LastName,'')) assigned_eid
        FROM
                `events`
        JOIN trainees ON `events`.employee_id = trainees.ID       
        WHERE
                event_pid = " . $id . "
        AND(tag_id IS NULL OR event_pid = 0)
        AND(event_pjd IS NULL OR event_pjd = 0)
        ORDER BY
                event_pjd = 0 DESC,
                start_date ASC";

header('Content-type:text/xml');
echo '<?xml version="1.0"?>' . PHP_EOL;
echo '<rows>';
reccuringTreeXML($query);
echo '</rows>';

function reccuringTreeXML($query) {

    $result = mysqli_query($dbc,$query);

    $objects = array();
    $roots = array();
    while ($row = mysqli_fetch_assoc($result)) {
        if (!isset($objects[$row['event_id']])) {
            $objects[$row['event_id']] = new stdClass;
            $objects[$row['event_id']]->children = array();
        }

        $obj = $objects[$row['event_id']];
        $obj->event_id = $row['event_id'];
        $obj->details = $row['details'];
        $obj->assigned_eid = $row['assigned_eid'];
        $obj->start_date = $row['start_date'];
        $obj->end_date = $row['end_date'];
        $obj->event_name = $row['event_name'];
        $obj->protection = $row['protection'];
        $obj->personal = $row['personal'];
        $obj->visible = $row['visible'];
        $obj->completed = $row['completed'];
        $obj->target = $row['target'];
        $obj->result = $row['result'];
        $obj->tag_id = $row['tag_id'];

        if ($row['event_pjd'] == 0) {
            $roots[] = $obj;
        } else {
            if (!isset($object[$row['event_pjd']])) {
                $object[$row['event_pjd']] = new stdClass;
                $object[$row['event_pjd']]->children = array();
            }

            $objects[$row['event_pjd']]->children[$row['event_id']] = $obj;
        }
    }
    foreach ($roots as $obj) {
        printInnerReccuringTreeXML($obj, true);
    }
}

function printInnerReccuringTreeXML(stdClass $obj, $isRoot = false) {

    echo '<row id="' . $obj->event_id . '">';
    if (count($obj->children) > 0) {
        echo "<cell image=\"folder.gif\"><![CDATA[" . $obj->details . "]]></cell>";
    } else {
        echo "<cell><![CDATA[" . $obj->details . "]]></cell>";
    }
    echo "<cell><![CDATA[" . $obj->assigned_eid . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->start_date . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->end_date . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->event_name . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->protection . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->personal . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->visible . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->completed . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->target . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->result . "]]></cell>";
    echo "<cell><![CDATA[" . $obj->tag_id . "]]></cell>";

    foreach ($obj->children as $child) {
        printInnerReccuringTreeXML($child);
    }

    echo '</row>';
}
