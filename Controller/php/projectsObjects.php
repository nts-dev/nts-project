<?php

ini_set('display_errors', '0');
require 'config_mysqli.php';
include_once 'GeneralClass.php';
date_default_timezone_set('UTC');
$action = $_GET['action'];
switch ($action) {

    default:

        break;

    case 1:

        header('Content-type:text/xml');
        print '<?xml version = "1.0"?>' . PHP_EOL;
        print('<menu id="0" >');
        print ('<toolbar>');

        /* $lang_id = 1; //$_COOKIE['lang_id'];
          $select = "SELECT lower(name) as nm,name,languages_id  FROM nts_site.xoops_shop_languages WHERE languages_id = '" . $lang_id . "'";
          $result = mysqli_query($dbc,$select);
          $row = mysqli_fetch_array($result);
          $languageimg = $row['name'] . '.png';
          $language = $row['name']; */

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

       print ('</toolbar>');
        print('</menu>');
        break;

    case 2:

        header('Content-type:text/xml');
        print '<?xml version = "1.0"?>' . PHP_EOL;
        print('<menu id="0" >');

        print('<item text="Add new row"  img="new.gif"  id="main_add">');
        print('<item text="Add root item"  img="new.gif"  id="addparent"/>');
        print('<item text="Add on selected row"  img="new.gif"  id="add"/>');
        print('</item>');

        print('<item text="Delete row"  img="delete.png"  id="delete"/>');
        print('</menu>');
        break;

    case 3:
        header("Content-type:text/xml");
        ini_set('max_execution_time', 600);
        print("<?xml version=\"1.0\"?>");

        $Qry = "SELECT Item_value as a1,Item_name as a2 FROM lookuptable WHERE Sort_Id = 1641 AND Language_ID = 1";
        $resQry = mysqli_query($dbc,$Qry);
        echo " <complete>";
        while ($row = mysqli_fetch_array($resQry)) {
            print("<option value=\"" . (int) $row["a1"] . "\"><![CDATA[" . $row['a2'] . "]]></option>");
        }
        print("</complete>");
        break;
        
    case 4:
        header("Content-type:text/xml");
        ini_set('max_execution_time', 600);
        print("<?xml version=\"1.0\"?>");
        echo " <complete>";
        $select = "SELECT lower(name) as nm,name,languages_id  FROM nts_site.xoops_shop_languages  ORDER BY languages_id ASC";
        $result = mysqli_query($dbc,$select) or die("SQL advTemp grid rendering: " . mysqli_error($dbc));
        if ($result) {
            while ($row = mysqli_fetch_array($result)) {
                print("<option value=\"" . (int) $row["languages_id"] . "\"><![CDATA[" . $row['name'] . "]]></option>");
            }
        }
        print("</complete>");
        break;
}
