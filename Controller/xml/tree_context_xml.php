<?php

include_once '../../../config.php';
header("Content-type:text/xml");

print('<menu id="0" >');
switch ($_GET['case']) {
    case 1:
        print('<item text="Create Task"  img="View/images/add.png"  id="add_task">');
        print('</item>');
        print('<item text="Delete Task"  img="View/images/delete.png"  id="delete_task"/>');

        break;
    case 2:
        //load items here
        $lang = $_COOKIE['language'];
        print ('<toolbar>');

        $lang_id = $_COOKIE['lang_id'];
        $select = "SELECT lower(name) as nm,name,languages_id  FROM xoops_shop_languages WHERE languages_id = '" . $lang_id . "'";
        $result = mysqli_query($dbc,$select);
        $row = mysqli_fetch_array($result);
        $languageimg = $row['name'] . '.png';
        $language = $row['name'];

        print('<item id="add" type="button" img="new.gif" imgdis="new.gif" text="Create"/>');
        print('<item type="separator" id="sep_1" />');
        print('<item id="copy" type="button" img="copy.png" imgdis="copy.png" text="Copy"/>');
        print('<item type="separator" id="sep_2" />');
        print('<item id="up" type="button" img="up.png" imgdis="up.png" text="Up"/>');
        print('<item type="separator" id="sep_3" />');
        print('<item id="down" type="button" img="down.png" imgdis="down.png" text="Down"/>');
        print('<item type="separator" id="sep_4" />');
        print('<item id="refresh" type="button" img="refresh.png" imgdis="refresh.png" text="Refresh"/>');
        print('<item type="separator" id="sep_5" />');
        print('<item id="show" type="button" img="" imgdis="" text="Display To do/All"/>');
        print('<item id="showall" type="text" img="showall.png" imgdis="showall.png"/>');
        print('<item type="separator" id="sep_6" />');

        if ($lang_id != null) {
            print('<item id="langauge" type="buttonSelect" img="' . $languageimg . '" imgdis="' . $languageimg . '" text="' . $language . '">');
        } else {
            $languageimg = "English" . '.png';
            $language = "English";
            print('<item id="langauge" type="buttonSelect" img="' . $languageimg . '" imgdis="' . $languageimg . '" text="' . $language . '">');
        }

        $select = "SELECT lower(name) as nm,name,languages_id  FROM xoops_shop_languages  ORDER BY languages_id ASC";
        $result = mysqli_query($dbc,$select) or die("SQL advTemp grid rendering: " . mysqli_error($dbc));
        if ($result) {
            while ($row = mysqli_fetch_array($result)) {
                print('<item type="button" id="' . $row['languages_id'] . '" text="  ' . $row['name'] . '" img="' . $row['name'] . '.png" width="200" />');
                print('<item id="sep1" type="separator"/>');
            }
        }
        print ('</item>');

        print ('</toolbar>');
        break;
    case 3:
        //load items here
        $lang = $_COOKIE['language'];
        print ('<toolbar>');

        $lang_id = $_COOKIE['lang_id'];
        $select = "SELECT lower(name) as nm,name,languages_id  FROM xoops_shop_languages WHERE languages_id = '" . $lang_id . "'";
        $result = mysqli_query($dbc,$select);
        $row = mysqli_fetch_array($result);
        $languageimg = $row['name'] . '.png';
        $language = $row['name'];
        if ($lang_id != null) {
            print('<item id="langauge" type="buttonSelect" img="' . $languageimg . '" imgdis="' . $languageimg . '" text="' . $language . '">');
        } else {
            $languageimg = "English" . '.png';
            $language = "English";
            print('<item id="langauge" type="buttonSelect" img="' . $languageimg . '" imgdis="' . $languageimg . '" text="' . $language . '">');
        }

        $select = "SELECT lower(name) as nm,name,languages_id  FROM xoops_shop_languages  ORDER BY languages_id ASC";
        $result = mysqli_query($dbc,$select) or die("SQL advTemp grid rendering: " . mysqli_error($dbc));
        if ($result) {
            while ($row = mysqli_fetch_array($result)) {
                print('<item type="button" id="' . $row['languages_id'] . '" text="  ' . $row['name'] . '" img="' . $row['name'] . '.png" width="200" />');
                print('<item id="sep1" type="separator"/>');
            }
        }
        print ('</item>');
        print ('</toolbar>');
        break;

    default:
        print('<item text="Add"  img="View/images/add.png"  id="main_add_dir">');
        print('<item text="Add Main Project"  img="View/images/add.png"  id="add_root"/>');
        print('<item text="Add Sub Project"  img="View/images/add.png"  id="add_sub"/>');
        print('</item>');
        print('<item text="Add Specifications Template"  img="View/images/add.png"  id="tpl_add_dir">');
        $qry = "SELECT * FROM specification_template_name";
        $res = mysqli_query($dbc,$qry) ;
        while ($rows = mysqli_fetch_array($res)) {
            print("<item text='" . $rows['spec_tpl_name'] . "' id='" . $rows['spec_tpl_cat'] . "'/>");
        }
        print('</item>');
        print('<item text="Rename Item"  img="View/images/rename.png"  id="rename"/>');
        print('<item text="Delete Item"  img="View/images/delete.png"  id="delete"/>');
        print('<item text="Archive" id="archive" type="checkbox"/>');
        print('<item text="Set Password"  img="View/images/add.png"  id="set_password"/>');
        print('<item text="Change Password"  img="View/images/add.png"  id="change_password"/>');
        break;
}
print('</menu>');
?>
