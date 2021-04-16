<?php
include_once '../../../config.php';

require("GeneralClass.php");

switch ($_GET['action']) {
    case 1:
        header("Content-type:text/xml");
        print('<menu id="0" >');

        print('<item text="Add new row"  img="add.png"  id="main_add">');
        print('<item text="Add root item"  img="add.png"  id="addparent"/>');
        print('<item text="Add on selected row"  img="add.png"  id="add"/>');
        print('</item>');


        print('<item text="Delete row"  img="delete.png"  id="delete"/>');
        print('</menu>');
        break;
    case 2:
        $sql_sort_max = "SELECT max(o_sorting) as mx FROM projects_objects WHERE (o_parent is null or o_parent = 0) AND o_tree_id = '" . $_GET['treeId'] . "'";
        $res_mx = mysqli_query($dbc,$sql_sort_max);
        $row_mx = mysqli_fetch_assoc($res_mx);
        $sort = $row_mx['mx'];
        $sort++;
        $QRY_INSERT = "INSERT INTO projects_objects(`o_name`,`o_tree_id`,`o_sorting`,`o_parent`) VALUES('New','" . $_GET['treeId'] . "','" . $sort . "',0)";
        if (mysqli_query($dbc,$QRY_INSERT)) {
            $msg = "new row inserted";
            //insert to object translation
            $lang_id = $_COOKIE['lang_id'];
            if ($lang_id == null) {
                $lang_id = 1;
            }
            $QRY_INSERT_TRANSLATION = "INSERT INTO projects_object_translation(`object_description`,`language_id`,`object_name`,`tree_id`) 
           VALUES('','" . $lang_id . "','','" . mysqli_insert_id($dbc) . "')";
            mysqli_query($dbc,$QRY_INSERT_TRANSLATION);
        } else {
            $msg = mysqli_error($dbc);
        }
        echo json_encode(array("response" => $msg, "newId" => mysqli_insert_id($dbc), "dubug" => $QRY_INSERT));
        break;
    case 3:
        $QRYDEL = "DELETE FROM projects_objects WHERE id = '" . $_GET['id'] . "'";
        if (mysqli_query($dbc,$QRYDEL)) {
            $msg = "Deleted!";
            $lang_id = $_COOKIE['lang_id'];
            if ($lang_id == null) {
                $lang_id = 1;
            }
            $QRYDEL_TRANSLATION = "DELETE FROM projects_object_translation WHERE tree_id = '" . $_GET['id'] . "' AND language_id = '" . $lang_id . "'";
            mysqli_query($dbc,$QRYDEL);
        } else {
            $msg = mysqli_error($dbc);
        }
        echo json_encode(array("response" => $msg));
        break;
    case 4:
        $sql_sort_max = "SELECT max(o_sorting) as mx FROM projects_objects WHERE o_parent = '" . $_GET['parent'] . "' AND o_tree_id = '" . $_GET['treeId'] . "'";
        $res_mx = mysqli_query($dbc,$sql_sort_max);
        $row_mx = mysqli_fetch_assoc($res_mx);
        $sort = $row_mx['mx'];
        $sort++;
        $QRY_INSERT = "INSERT INTO projects_objects(`o_name`,`o_tree_id`,`o_parent`,`o_sorting`) VALUES('New','" . $_GET['treeId'] . "','" . $_GET['parent'] . "','" . $sort . "')";
        if (mysqli_query($dbc,$QRY_INSERT)) {
            $msg = "new child row inserted";
            $lang_id = $_COOKIE['lang_id'];
            if ($lang_id == null) {
                $lang_id = 1;
            }
            $QRY_INSERT_TRANSLATION = "INSERT INTO projects_object_translation(`object_description`,`language_id`,`object_name`,`tree_id`) 
           VALUES('','" . $lang_id . "','','" . mysqli_insert_id($dbc) . "')";
            mysqli_query($dbc,$QRY_INSERT_TRANSLATION);
        } else {
            $msg = mysqli_error($dbc);
        }
        echo json_encode(array("response" => $msg, "newId" => mysqli_insert_id($dbc)));
        break;
    case 5:
        $value = $_POST['fieldvalue'];
        $column = $_POST['colId'];
        $SQLUPDate = "UPDATE projects_objects SET $column = '" . $value . "' WHERE id = '" . $_POST['id'] . "'";
        if (mysqli_query($dbc,$SQLUPDate)) {
            $msg = "Successfully Updated!";
            if ($column == "o_name" || $column == "o_description") {
                $lang_id = $_COOKIE['lang_id'];
                if ($lang_id == null) {
                    $lang_id = 1;
                }
                $SQL = "SELECT * FROM projects_object_translation WHERE tree_id = '" . $_POST['id'] . "' AND language_id = '" . $lang_id . "'";
                $RES_CNT = mysqli_query($dbc,$SQL);
                $ROW_CNT = mysqli_num_rows($RES_CNT);
                $SQL_ = "SELECT * FROM projects_objects WHERE  id = '" . $_POST['id'] . "'";
                $RES_CNT_ = mysqli_query($dbc,$SQL_);
                $ROW_TRANS = mysqli_fetch_assoc($RES_CNT_);
                if ($ROW_CNT > 0) {
                    $QRY_INSERT_TRANSLATION = "UPDATE projects_object_translation SET object_name = '" . $ROW_TRANS['o_name'] . "' WHERE language_id = '" . $lang_id . "' AND tree_id = '" . $ROW_TRANS['id'] . "' ";
                } else {
                    $QRY_INSERT_TRANSLATION = "INSERT INTO projects_object_translation(`object_description`,`language_id`,`object_name`,`tree_id`) 
           VALUES('" . $ROW_TRANS['o_description'] . "','" . $lang_id . "','" . $ROW_TRANS['o_name'] . "','" . $ROW_TRANS['id'] . "')";
                }
                mysqli_query($dbc,$QRY_INSERT_TRANSLATION);
            }
        } else {
            $msg = "Error :" . mysqli_error($dbc);
        }
        echo json_encode(array('response' => $msg));
        break;
    case 6:
        //load form here 
        header("Content-type:text/xml");
        print("<?xml version=\"1.0\"?>");
        echo "<data>";
        $SQL = "SELECT * FROM projects_objects  WHERE id = " . $_GET['id']; // echo $SQL;
        $RES = mysqli_query($dbc,$SQL);
        $ROW = mysqli_fetch_assoc($RES);
        echo "<id>" . $ROW["id"] . "</id>";
        echo "<o_type><![CDATA[" . $ROW["o_type"] . "]]></o_type>";
        echo "<o_values><![CDATA[" . $ROW["o_values"] . "]]></o_values>";
        echo "<o_table_field><![CDATA[" . $ROW["o_table_field"] . "]]></o_table_field>";
        echo "<o_description><![CDATA[" . $ROW["o_description"] . "]]></o_description>";
        echo "<o_requirements><![CDATA[" . $ROW["o_requirements"] . "]]></o_requirements>";
        echo "<o_data_type><![CDATA[" . $ROW["o_data_type"] . "]]></o_data_type>";
        echo "<o_help_info><![CDATA[" . $ROW["o_help_info"] . "]]></o_help_info>";
        echo "<o_usage><![CDATA[" . $ROW["o_usage"] . "]]></o_usage>";
        echo "<o_results><![CDATA[" . $ROW["o_results"] . "]]></o_results>";
        echo "</data>";
        break;
    case 7:
        $xpos = $_POST['xpos'];
        $ypos = $_POST['ypos'];
        $o_width = $_POST['o_width'];
        if ($xpos == '') {
            $xpos = 0;
        }

        if ($ypos == '') {
            $ypos = 0;
        }
        if ($o_width == '') {
            $o_width = 0;
        }

        $SQL = "UPDATE projects_objects SET o_type = '" . $_POST['o_type'] . "',o_values = '" . $_POST['o_values'] . "',o_table_field = '" . $_POST['o_table_field'] . "', o_description = '" . $_POST['o_description'] . "',o_requirements = '" . $_POST['o_requirements'] . "',o_usage = '" . $_POST['o_usage'] . "',o_results = '" . $_POST['o_results'] . "',o_help_info='" . $_POST['o_help_info'] . "',o_data_type= '" . $_POST['o_data_type'] . "',xpos ='$xpos' ,ypos = '$ypos' ,o_width='$o_width' WHERE id = '" . $_POST['id'] . "'";
        if (mysqli_query($dbc,$SQL)) {
            $msg = "Saved!";
            $lang_id = $_COOKIE['lang_id'];
            if ($lang_id == null) {
                $lang_id = 1;
            }
            $SQL = "SELECT * FROM projects_object_translation WHERE tree_id = '" . $_POST['id'] . "' AND language_id = '" . $lang_id . "'";
            $RES_CNT = mysqli_query($dbc,$SQL);
            $ROW_CNT = mysqli_num_rows($RES_CNT);
            $SQL_ = "SELECT * FROM projects_objects WHERE  id = '" . $_POST['id'] . "'";
            $RES_CNT_ = mysqli_query($dbc,$SQL_);
            $ROW_TRANS = mysqli_fetch_assoc($RES_CNT_);
            if ($ROW_CNT > 0) {
                $QRY_INSERT_TRANSLATION = "UPDATE projects_object_translation SET object_description = '" . $_POST['o_help_info'] . "',object_name = '" . $ROW_TRANS['o_name'] . "' WHERE language_id = '" . $lang_id . "' AND tree_id = '" . $ROW_TRANS['id'] . "' ";
            } else {
                $QRY_INSERT_TRANSLATION = "INSERT INTO projects_object_translation(`object_description`,`language_id`,`object_name`,`tree_id`) 
           VALUES('" . $_POST['o_help_info'] . "','" . $lang_id . "','" . $ROW_TRANS['o_name'] . "','" . $ROW_TRANS['id'] . "')";
            }
            mysqli_query($dbc,$QRY_INSERT_TRANSLATION);
        } else {
            $msg = mysqli_error($dbc);
        }
        echo json_encode(array('response' => $msg));
        break;
    case 8:
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        print('<complete>');
        print('<option value="0">To Do</option>');
        print('<option value="1">Ok</option>');
        print(' </complete>');
        break;
    case 9:
        $sql = "SELECT o_help_info FROM projects_objects WHERE id = '" . $_POST['id'] . "' ";
        $res = mysqli_query($dbc,$sql);
        $row = mysqli_fetch_assoc($res);
        echo json_encode(array("response" => $row['o_help_info']));
        break;
    case 10:
        //moving objects

        $bool = true;
        $SQL_SEL = "SELECT * FROM projects_objects WHERE id = '" . $_POST['selId'] . "'";
        $RES_SEL = mysqli_query($dbc,$SQL_SEL);
        $ROW_SEL = mysqli_fetch_assoc($RES_SEL);

        //get the sort and parent
        $SORT_SEL = $ROW_SEL['o_sorting'];
        $PARENT = $ROW_SEL['o_parent'];
        //sort for the destination 
        switch ($_POST['type']) {
            case 'up':
                $SORT_SEL--;
                break;
            case 'down':
                $SORT_SEL++;
                break;
        }
        //query
        $SQL_DEST = "SELECT * FROM projects_objects WHERE o_sorting = '" . $SORT_SEL . "' AND o_parent = '" . $PARENT . "'"; // echo $SQL_DEST;
        $RES_DEST = mysqli_query($dbc,$SQL_DEST);
        $ROW_DEST = mysqli_fetch_assoc($RES_DEST);
        if ($ROW_DEST['id'] == null) {
            $bool = false;
        }
        //update the sort 
        $SQL_UPDATE = "UPDATE projects_objects SET o_sorting = '" . $ROW_SEL['o_sorting'] . "' WHERE id = '" . $ROW_DEST['id'] . "'";

        $SQL_UPDATE_ = "UPDATE projects_objects SET o_sorting = '" . $SORT_SEL . "' WHERE  id = '" . $_POST['selId'] . "'";

        if ($bool == true) {

            mysqli_query($dbc,$SQL_UPDATE);
            mysqli_query($dbc,$SQL_UPDATE_);
        }
        echo json_encode(array("dest" => $ROW_DEST['id'], "bool" => $bool, "parent" => $PARENT));
        break;
    case 11:
        //save positions
        $SQL = "UPDATE projects_objects SET xpos = '" . $_POST['xpos'] . "',ypos = '" . $_POST['ypos'] . "',o_width='" . $_POST['o_width'] . "' WHERE id = '" . $_GET['id'] . "'";  // echo $SQL; exit(); 
        if (mysqli_query($dbc,$SQL)) {
            $msg = "Saved!";
        } else {
            $msg = mysqli_error($dbc);
        }
        echo json_encode(array('response' => $msg));
        break;
    case 12:

        function getChild($parent, $source) {
            $sql_par = "SELECT * FROM projects_objects WHERE o_parent = '" . $parent . "'";
            $res_par = mysqli_query($dbc,$sql_par);
            while ($row_par = mysqli_fetch_assoc($res_par)) {
                $sql_sort_max = "SELECT max(o_sorting) as mx FROM projects_objects WHERE o_parent = '" . $source . "'";
                $res_mx = mysqli_query($dbc,$sql_sort_max);
                $row_mx = mysqli_fetch_assoc($res_mx);
                $sort = $row_mx['mx'];
                $sort++;
                $QRY_INSERT = "INSERT INTO projects_objects(`o_name`,`o_tree_id`,`o_sorting`,`o_parent`) VALUES('" . $row_par['o_name'] . "','" . $row_par['o_tree_id'] . "','" . $sort . "','" . $source . "')";
                mysqli_query($dbc,$QRY_INSERT);
                $newsrc = mysqli_insert_id($dbc);
                getChild($row_par['id'], $newsrc);
            }
        }

        switch ($_GET['case']) {
            case 1:
                //get the selected values
                $sql_sel = "SELECT * FROM projects_objects WHERE id = '" . $_POST['id'] . "'";
                $res_sel = mysqli_query($dbc,$sql_sel);
                while ($row_sel = mysqli_fetch_assoc($res_sel)) {
                    $sql_sort_max = "SELECT max(o_sorting) as mx FROM projects_objects WHERE o_parent = '" . $row_sel['o_parent'] . "'";
                    $res_mx = mysqli_query($dbc,$sql_sort_max);
                    $row_mx = mysqli_fetch_assoc($res_mx);
                    $sort = $row_mx['mx'];
                    $sort++;
                    $QRY_INSERT = "INSERT INTO projects_objects(`o_name`,`o_tree_id`,`o_sorting`,`o_parent`) VALUES('" . $row_sel['o_name'] . "','" . $row_sel['o_tree_id'] . "','" . $sort . "','" . $row_sel['o_parent'] . "')";
                    if (mysqli_query($dbc,$QRY_INSERT)) {
                        $msg = "new row inserted";
                        //insert to object translation
                        $lang_id = $_COOKIE['lang_id'];
                        if ($lang_id == null) {
                            $lang_id = 1;
                        }
                        $rws = mysqli_insert_id($dbc);
                        $QRY_INSERT_TRANSLATION = "INSERT INTO projects_object_translation(`object_description`,`language_id`,`object_name`,`tree_id`) 
               VALUES('" . $row_sel['o_description'] . "','" . $lang_id . "','" . $row_sel['o_name'] . "','" . mysqli_insert_id($dbc) . "')";
                        mysqli_query($dbc,$QRY_INSERT_TRANSLATION);
                        getChild($row_sel['id'], $rws);
                    } else {
                        $msg = mysqli_error($dbc);
                    }
                }
                echo json_encode(array("message" => $msg, "newId" => $rws, "dubug" => $QRY_INSERT));
                break;
            default:
                $bool = false;
                //check if the object id entered is correct
                $sql_check = "SELECT * FROM projects_objects WHERE id = '" . $_POST['val'] . "'";
                $res_check = mysqli_query($dbc,$sql_check);
                $cnt = mysqli_num_rows($res_check);
                if (mysqli_num_rows($res_check) > 0) {
                    $row_check = mysqli_fetch_assoc($res_check);
                    $bool = true;
                    $msg = "Do you want to create a copy of <br><b>" . $row_check['o_name'] . "</b> object ?";
                } else {
                    $msg = "No object exists of entered id!";
                }
                echo json_encode(array('message' => $msg, 'bool' => $bool));
                break;
        }

        break;
    default:
        switch ($_GET['all']) {
            case 'true':
                $condit = "";

                break;
            default:
                $condit = " AND p.o_status = 0";
                break;
        }
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<rows>";
        $id = $_GET['id'];
        if ($id == null) {
            $sql_Main_c = "SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1  WHERE (o_parent is null OR o_parent = 0) $condit ORDER BY p.o_sorting ASC";
        } else {
            $sql_Main_c = "SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1 WHERE  (o_parent is null OR o_parent = 0) AND o_tree_id = '" . $id . "' $condit ORDER BY p.o_sorting ASC";
        }
//echo $sql_Main_c; exit();             
        $res_Main_c = mysqli_query($dbc,$sql_Main_c);
        while ($row_Main_c = mysqli_fetch_array($res_Main_c)) {
//            echo "<row id = '{$row_Main_c["id"]}'>";
//            $count = mysqli_num_rows($res_Main_c);
//            if ($count > 0) {
//                echo "<cell image='folder.gif'> " . xmlEscape($row_Main_c["o_sorting"]) . " </cell>";
//            } else {
//                echo "<cell> " . xmlEscape($row_Main_c["o_sorting"]) . "</cell>";
//            }
//            echo "<cell> " . xmlEscape($row_Main_c["o_name"]) . "</cell>";
//            $desc = strip_tags($a["o_description"]);
//            echo "<cell><![CDATA[" . str_replace("&nbsp;", "", $desc) . "]]></cell>";
//            echo "<cell> " . $row_Main_c["o_shortcut"] . "</cell>";
//            echo "<cell> " . xmlEscape($row_Main_c["o_type"]) . "</cell>";
//            echo "<cell> " . xmlEscape($row_Main_c["status_name"]) . "</cell>";
//            getOthertreeGridDirectories($row_Main_c["id"],$condit);
//            echo "</row>";
            echo "<row id = '{$row_Main_c["id"]}'>";
            $res_Main_count = mysqli_query($dbc,"SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1  WHERE o_parent = '" . $row_Main_c['id'] . "' $condit ORDER BY p.o_sorting ASC");
            getRows($row_Main_c, $res_Main_count);
            $qry_Main_e = "SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1  WHERE o_parent = '" . $row_Main_c['id'] . "'     $condit ORDER BY p.o_sorting ASC";
            $resMain_e = mysqli_query($dbc,$qry_Main_e);
            while ($rowMain_e = mysqli_fetch_array($resMain_e)) {
                echo "<row id = '" . $rowMain_e["id"] . "'>";
                $res_Main_count_2 = mysqli_query($dbc,"SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1  WHERE o_parent = '" . $rowMain_e['id'] . "'  $condit ORDER BY p.o_sorting ASC");
                getRows($rowMain_e, $res_Main_count_2);
                $qry_Main_f = "SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1  WHERE o_parent = '" . $rowMain_e['id'] . "' $condit ORDER BY p.o_sorting ASC";
                $resMain_f = mysqli_query($dbc,$qry_Main_f);
                while ($rowMain_f = mysqli_fetch_array($resMain_f)) {
                    echo "<row id = '" . $rowMain_f["id"] . "'>";
                    $res_Main_count_3 = mysqli_query($dbc,"SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1  WHERE o_parent = '" . $rowMain_f['id'] . "' $condit ORDER BY p.o_sorting ASC");
                    getRows($rowMain_f, $res_Main_count_3);
                    $qry_Main_g = "SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1  WHERE o_parent = '" . $rowMain_f['id'] . "' $condit ORDER BY p.o_sorting ASC";
                    $resMain_g = mysqli_query($dbc,$qry_Main_g);
                    while ($rowMain_g = mysqli_fetch_array($resMain_g)) {
                        echo "<row id = '" . $rowMain_g["id"] . "'>";
                        $res_Main_count_4 = mysqli_query($dbc,"SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1  WHERE o_parent = '" . $rowMain_g['id'] . "' $condit ORDER BY p.o_sorting ASC");
                        getRows($rowMain_g, $res_Main_count_4);
                        $qry_Main_h = "SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1  WHERE o_parent = '" . $rowMain_g['id'] . "' $condit ORDER BY p.o_sorting ASC";
                        $resMain_h = mysqli_query($dbc,$qry_Main_h);
                        while ($rowMain_h = mysqli_fetch_array($resMain_h)) {
                            echo "<row id = '" . $rowMain_h["id"] . "'>";
                            getRows($rowMain_h, '');
                            echo "</row>";
                        }

                        echo "</row>";
                    }

                    echo "</row>";
                }

                echo "</row>";
            }
            echo "</row>";
        }
        echo "</rows>";
        break;
}

//escape xml characters        
function xmlEscape($string) {
    return str_replace(array('&', '<', '>', '\'', '"', '-'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;', ''), $string);
}

//get the rest of row items
function getRows($a, $b) {
    $count = mysqli_num_rows($b);
    if ($count > 0) {
        echo "<cell image='folder.gif'> " . xmlEscape($a["o_sorting"]) . " </cell>";
    } else {
        echo "<cell> " . xmlEscape($a["o_sorting"]) . "</cell>";
    }
    echo "<cell> ".xmlEscape($a["id"])."</cell>";
    echo "<cell> " . xmlEscape($a["o_name"]) . "</cell>";
    // echo "<cell> ".$a["o_table_field"]."</cell>";
    // echo "<cell> ".$a["o_values"]."</cell>";
    $desc = strip_tags($a["o_description"]);
    echo "<cell><![CDATA[" . str_replace("&nbsp;", "", $desc) . "]]></cell>";
    echo "<cell> " . $a["o_shortcut"] . "</cell>";
    echo "<cell> ".xmlEscape($a["o_type"])."</cell>";
   // echo "<cell> " . xmlEscape(strip_tags($a["o_requirements"])) . "</cell>";
    echo "<cell> " . xmlEscape($a["status_name"]) . "</cell>";
}

function getOthertreeGridDirectories($itemId,$condit) {
    
    $qry_Main_e = "SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1  WHERE o_parent = '" . $itemId . "'     $condit ORDER BY p.o_sorting ASC";
    while ($rowMain_e = mysqli_fetch_array($resMain_e)) {
    
    $res_Main_count = mysqli_query($dbc,"SELECT p.o_shortcut,p.o_sorting,p.id,p.o_name,p.o_type,p.o_table_field,p.o_values,p.o_description,p.o_requirements,p.o_status,l.Item_name as status_name  FROM projects_objects p  LEFT JOIN lookuptable l ON p.o_status = l.Item_value AND l.Sort_ID = 1641 AND l.Language_ID = 1  WHERE o_parent = '" . $itemId . "' $condit ORDER BY p.o_sorting ASC");
    
    echo "<row id = '{$rowMain_e["id"]}'>";
    $count = mysqli_num_rows($res_Main_count);
    if ($count > 0) {
        echo "<cell image='folder.gif'> " . xmlEscape($rowMain_e["o_sorting"]) . " </cell>";
    } else {
        echo "<cell> " . xmlEscape($rowMain_e["o_sorting"]) . "</cell>";
    }
    echo "<cell> " . xmlEscape($rowMain_e["o_name"]) . "</cell>";
    $desc = strip_tags($a["o_description"]);
    echo "<cell><![CDATA[" . str_replace("&nbsp;", "", $desc) . "]]></cell>";
    echo "<cell> " . $rowMain_e["o_shortcut"] . "</cell>";
    echo "<cell> " . xmlEscape($rowMain_e["o_type"]) . "</cell>";
    echo "<cell> " . xmlEscape($rowMain_e["status_name"]) . "</cell>";
    getOthertreeGridDirectories($rowMain_e["id"],$condit);
    echo "</row>";
}
}

?>
