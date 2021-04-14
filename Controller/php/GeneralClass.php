<?php
function updateSQL($table, $field, $fieldvalue, $id, $idfield, $colType)
{
    global $dbc;
    $updateSQL = "UPDATE {$table} SET {$field} =";

    if ($colType == "int") $updateSQL .= "{$fieldvalue}";
    else $updateSQL .= "'{$fieldvalue}'";

    $updateSQL .= " WHERE  {$idfield} = '{$id}'";

    //echo $updateSQL;
    $updateResult = mysqli_query($dbc, $updateSQL) or die ("SQL Error saving {$field} in {$table} table: " . mysqli_error($dbc) . $updateSQL);

    return $updateResult;
}

function getTableDetailField($table, $id, $idcol, $field)
{
    global $dbc;
    $sql = "SELECT {$field} FROM {$table} WHERE {$idcol} = {$id}";

    $res = mysqli_query($dbc, $sql);
    $row = mysqli_fetch_array($res);

    return $row["{$field}"];

}

function getTableTextField($table, $id, $idcol, $field)
{
    global $dbc;
    $sql = "SELECT {$field} FROM {$table} WHERE {$idcol} = '{$id}'";
    //echo $sql;
    $res = mysqli_query($dbc, $sql);
    $row = mysqli_fetch_array($res);

    return $row["{$field}"];

}

function getTableMaxField($table, $id, $idcol, $field)
{
    global $dbc;
    $sql = "SELECT MAX({$field}) as maxvalue FROM {$table} WHERE {$field} > 0";

    if (!empty($idcol)) $sql .= " AND {$idcol} = '{$id}'";

    $res = mysqli_query($dbc, $sql);
    $row = mysqli_fetch_array($res);

    return $row["maxvalue"];

}

function getTableMinField($table, $id, $idcol, $field)
{
    global $dbc;
    $sql = "SELECT MIN({$field}) as minvalue FROM {$table} WHERE {$idcol} = {$id} AND {$field} > 0";
    $res = mysqli_query($dbc, $sql);
    $row = mysqli_fetch_array($res);

    return $row["minvalue"];

}

function read_file_docx($filename)
{

    $striped_content = '';
    $content = '';

    if (!$filename || !file_exists($filename)) return false;

    $zip = zip_open($filename);

    if (!$zip || is_numeric($zip)) return false;

    while ($zip_entry = zip_read($zip)) {

        if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

        if (zip_entry_name($zip_entry) != "word/document.xml") continue;

        $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

        zip_entry_close($zip_entry);
    }// end while

    zip_close($zip);


    $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
    $content = str_replace('</w:r></w:p>', "\r\n", $content);
    $striped_content = strip_tags($content);

    return $striped_content;
}

function insertSpreadSheetData($sheet, $columnid, $rowid, $data, $style)
{
    global $dbc;
    $sql = "INSERT INTO data
                    (sheetid,columnid,rowid,data,style,parsed,calc) 
               VALUES 
                    ('{$sheet}',{$columnid},$rowid,'{$data}','{$style}','{$data}','{$data}')";
    $res = mysqli_query($dbc, $sql) or "Insert Spreadsheet data error " . die (mysqli_error($dbc));

    return $res;
}

function getChildGridXml($parentid)
{

    global $dbc;
    $sql = "SELECT name,id,link,published,access,ordering FROM jos_menu WHERE menutype = 'mainmenu' AND (published = 1 OR published = 0) AND parent = {$parentid} ORDER BY ordering ASC";
    $res = mysqli_query($dbc, $sql);

    while ($row = mysqli_fetch_array($res)) {
        echo "<row id='{$row["id"]}'>";
        echo "<cell>" . str_replace('&', '&amp;', $row["name"]) . "</cell>";
        echo "<cell>{$row["id"]}</cell>";
        echo "<cell>" . str_replace('&', '&amp;', $row["link"]) . "</cell>";
        echo "<cell>{$row["published"]}</cell>";
        echo "<cell>{$row["access"]}</cell>";
        echo "<cell>{$row["ordering"]}</cell>";
        getChildGridXml($row["id"]);
        echo "</row>";
    }
}

function moveItemUpDownGrid($parentid, $parentfield, $itemid, $itemidfield, $sortid, $sortfield, $table, $direction)
{

    global $dbc;
    if ($direction == "up") {

        $minmax = getMinMaxSortID($parentid, $parentfield, $sortid, $sortfield, $table, "MIN");

        if ($sortid > $minmax) {

            $xsql = "UPDATE {$table} SET {$sortfield} = {$sortid} WHERE {$sortfield} = {$sortid}-1";
            if (!empty($parentid)) {
                $xsql .= " AND {$parentfield} = {$parentid}";
            }
            $xres = mysqli_query($dbc, $xsql);

            $sql = "UPDATE {$table} SET {$sortfield} = {$sortid}-1 WHERE {$itemidfield} = {$itemid}";
            if (!empty($parentid)) {
                $sql .= " AND {$parentfield} = {$parentid}";
            }
            $res = mysqli_query($dbc, $sql);
        }

    } else if ($direction == "down") {

        $minmax = getMinMaxSortID($parentid, $parentfield, $sortid, $sortfield, $table, "MAX");

        if ($sortid < $minmax) {

            $xsql = "UPDATE {$table} SET {$sortfield} = {$sortid} WHERE {$sortfield} = {$sortid}+1";
            if (!empty($parentid)) {
                $xsql .= " AND {$parentfield} = {$parentid}";
            }
            $xres = mysqli_query($dbc, $xsql);

            $sql = "UPDATE {$table} SET {$sortfield} = {$sortid}+1 WHERE {$itemidfield} = {$itemid}";
            if (!empty($parentid)) {
                $sql .= " AND {$parentfield} = {$parentid}";
            }
            $res = mysqli_query($dbc, $sql);
        }
    }

    $data['data'] = array('success' => true, 'u' => $minmax, 'x' => $xsql);

    return $data;
}

function getMinMaxSortID($parentid, $parentfield, $sortid, $sortfield, $table, $minmax)
{

    global $dbc;
    $sql = "SELECT {$minmax}({$sortfield}) AS minmax FROM {$table} WHERE {$sortid} > 0";
    if (!empty($parentid)) {
        $sql .= " AND {$parentfield} = {$parentid}";
    }
    $res = mysqli_query($dbc, $sql);
    $row = mysqli_fetch_array($res);

    return $row["minmax"];
}

function updateSortIDonDelete($parentid, $parentfield, $itemid, $itemidfield, $sortid, $sortfield, $table)
{

    global $dbc;
    $minmax = getMinMaxSortID($parentid, $parentfield, $sortid, $sortfield, $table, "MAX");

    if ($sortid < $minmax) {
        $sql = "SELECT {$sortfield} FROM {$table} WHERE {$parentfield} = {$parentid} AND {$sortfield} > {$sortid} ORDER BY {$sortfield}";

        $res = mysqli_query($dbc, $sql);

        $count = 0;
        while ($row = mysqli_fetch_array($res)) {

            $usql = "UPDATE {$table} SET {$sortfield} = {$sortid}+{$count} WHERE {$sortfield} = {$row[$sortfield]}";
            $ures = mysqli_query($dbc, $usql) or die (mysqli_error($dbc) . "  " . $usql);

            $count++;
        }
    }

    $data['data'] = array('success' => true);

    return $data;
}

function datediff($interval, $datefrom, $dateto, $using_timestamps = false)
{
    /*
    $interval can be:
    yyyy - Number of full years
    q - Number of full quarters
    m - Number of full months
    y - Difference between day numbers
    (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
    d - Number of full days
    w - Number of full weekdays
    ww - Number of full weeks
    h - Number of full hours
    n - Number of full minutes
    s - Number of full seconds (default)
    */
    if (!$using_timestamps) {
        $datefrom = strtotime($datefrom, 0);
        $dateto = strtotime($dateto, 0);
    }

    $difference = $dateto - $datefrom; // Difference in seconds

    switch ($interval) {
        case 'yyyy': // Number of full years
            $years_difference = floor($difference / 31536000);
            if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom) + $years_difference) > $dateto) {
                $years_difference--;
            }
            if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto) - ($years_difference + 1)) > $datefrom) {
                $years_difference++;
            }
            $datediff = $years_difference;
            break;

        case "q": // Number of full quarters
            $quarters_difference = floor($difference / 8035200);
            while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom) + ($quarters_difference * 3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                $months_difference++;
            }
            $quarters_difference--;
            $datediff = $quarters_difference;
            break;

        case "m": // Number of full months
            $months_difference = floor($difference / 2678400);
            while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom) + ($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                $months_difference++;
            }
            $months_difference--;
            $datediff = $months_difference;
            break;

        case 'y': // Difference between day numbers
            $datediff = date("z", $dateto) - date("z", $datefrom);
            break;

        case "d": // Number of full days
            $datediff = floor($difference / 86400);
            break;

        case "w": // Number of full weekdays
            $days_difference = floor($difference / 86400);
            $weeks_difference = floor($days_difference / 7); // Complete weeks
            $first_day = date("w", $datefrom);
            $days_remainder = floor($days_difference % 7);
            $odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
            if ($odd_days > 7) { // Sunday
                $days_remainder--;
            }
            if ($odd_days > 6) { // Saturday
                $days_remainder--;
            }
            $datediff = ($weeks_difference * 5) + $days_remainder;
            break;

        case "ww": // Number of full weeks
            $datediff = floor($difference / 604800);
            break;

        case "h": // Number of full hours
            $datediff = floor($difference / 3600);
            break;

        case "n": // Number of full minutes
            $datediff = floor($difference / 60);
            break;

        default: // Number of full seconds (default)
            $datediff = $difference;
            break;

    }
    return $datediff;
}

