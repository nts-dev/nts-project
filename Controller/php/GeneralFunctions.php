<?php
function updateSQL($table, $field, $fieldvalue, $id, $idfield, $colType)
{
    global $dbc;
    $updateSQL = "UPDATE {$table} SET {$field} =";

    if ($colType == "int") {
        $updateSQL .= "{$fieldvalue}";
    } else {
        $updateSQL .= "'{$fieldvalue}'";
    }
    $updateSQL .= " WHERE  {$idfield} = '{$id}'";
    //$updateSQL .= $condition;
    $updateResult = mysqli_query($dbc, $updateSQL) or die ("SQL Error saving {$field} in {$table} table: " . mysqli_error($dbc) . $updateSQL);

    return $updateResult;
}

function getTableDetailField($table, $id, $idcol, $field)
{
    global $dbc;
    if (!empty($id)) {
        $sql = "SELECT {$field} FROM {$table} WHERE {$idcol} = {$id}";
        $res = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_array($res);

        $fieldvalue = $row["{$field}"];
    } else {
        $fieldvalue = 0;
    }

    return $fieldvalue;
}

function getContactName($contact_id)
{

    global $dbc;
    if (!empty($contact_id)) {
        $sql = "SELECT contact_firstname,contact_lastname FROM relation_contact WHERE contact_id = {$contact_id}";
        $res = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_array($res);

        $fieldvalue = $row["contact_firstname"] . " " . $row["contact_lastname"];
    } else {
        $fieldvalue = "";
    }

    return $fieldvalue;

}

function getVATVAlue($id)
{

    if ($id == 502) {
        $fieldvalue = 6;
    }
    if ($id == 503 || $id == 0) {
        $fieldvalue = 0;
    }
    if ($id == 504) {
        $fieldvalue == 19;
    }
    if ($id == 506) {
        $fieldvalue = 21;
    }
    if ($id == 507) {
        $fieldvalue = 16;
    }

    return $fieldvalue;
}

function getTableDetailFieldCondition($table, $id, $idcol, $field, $condition)
{

    global $dbc;
    if (!empty($id)) {
        $sql = "SELECT {$field} FROM {$table} WHERE {$idcol} = {$id} " . $condition;

        $res = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_array($res);

        $fieldvalue = $row["{$field}"];
    } else {
        $fieldvalue = 0;
    }

    return $fieldvalue;
}

function getTableTextField($table, $id, $idcol, $field)
{

    global $dbc;
    if (!empty($id)) {
        $sql = "SELECT {$field} FROM {$table} WHERE trim({$idcol}) = '" . trim($id) . "'";
        $res = mysqli_query($dbc, $sql) or die(mysqli_error($dbc) . $sql);
        $row = mysqli_fetch_array($res);

        if (!empty($row["{$field}"])) {
            $fieldvalue = $row["{$field}"];
        } else {
            $fieldvalue = 0;
        }
    } else {
        $fieldvalue = 0;
    }

    return $fieldvalue;
}

function getTableTextFieldCondition($table, $id, $idcol, $field, $condition)
{

    global $dbc;
    if (!empty($id)) {
        $sql = "SELECT {$field} FROM {$table} WHERE trim({$idcol}) = '" . trim($id) . "' " . $condition;
        $res = mysqli_query($dbc, $sql) or die(mysqli_error($dbc) . $sql);
        $row = mysqli_fetch_array($res);

        if (!empty($row["{$field}"])) {
            $fieldvalue = $row["{$field}"];
        } else {
            $fieldvalue = 0;
        }
    } else {
        $fieldvalue = 0;
    }

    return $fieldvalue;
}

function getTableRowCount($table, $id, $idcol)
{

    global $dbc;

    if (!empty($id)) {
        $sql = "SELECT count(*) as tbl_count FROM {$table} WHERE {$idcol} = '" . trim($id) . "'";
        $res = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_array($res);

        return $row["tbl_count"];
    } else {
        return 0;
    }
}

function getTableRowCountGeneralConn($table, $id, $idcol, $Cn)
{
    global $dbc;
    if (!empty($id)) {
        $sql = "SELECT count(*) as tbl_count FROM {$table} WHERE {$idcol} = '" . trim($id) . "'";
        $res = mysqli_query($dbc, $sql, $Cn);
        $row = mysqli_fetch_array($res);

        return $row["tbl_count"];
    } else {
        return 0;
    }
}

function getItemByIDGeneralConn($table, $id, $idfield, $Cn)
{

    global $dbc;
    if (!empty($id)) {
        $sql = "SELECT * FROM {$table} WHERE {$idfield} = {$id} LIMIT 1";
        $rs = mysqli_query($dbc, $sql, $Cn) or die(mysql_error() . $sql);
        if ($item = mysqli_fetch_array($rs)) {
            return $item;
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}


function getTableRowSum($table, $id, $idcol, $sumcol)
{

    global $dbc;
    if (!empty($id)) {
        $sql = "SELECT sum({$sumcol}) as tbl_sum FROM {$table} WHERE {$idcol} = '" . trim($id) . "'";
        $res = mysqli_query($dbc, $sql) or die(mysql_error());
        $row = mysqli_fetch_array($res);

        return $row["tbl_sum"];
    } else {
        return 0;
    }
}

function getTableRowCountCondition($table, $id, $idcol, $condition)
{
    global $dbc;
    if (!empty($id)) {
        $sql = "SELECT count(*) as tbl_count FROM {$table} WHERE {$idcol} = '" . trim($id) . "'";
        $res = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_array($res);

        return $row["tbl_count"];
    } else {
        return 0;
    }
}

function getMinMaxSortID($parentid, $parentfield, $sortid, $sortfield, $table, $minmax)
{

    global $dbc;
    $sql = "SELECT {$minmax}({$sortfield}) AS minmax FROM {$table} WHERE {$sortid} > 0";
    if (!empty($parentid)) {
        $sql .= " AND {$parentfield} = '{$parentid}'";
    }

    $res = mysqli_query($dbc, $sql);
    $row = mysqli_fetch_array($res);

    return $row["minmax"];
}

function getMinMaxSortID2($parentid, $parentfield, $sortid, $sortfield, $table, $minmax, $journal_id)
{

    global $dbc;
    $sql = "SELECT {$minmax}({$sortfield}) AS minmax FROM {$table} WHERE {$sortid} > 0";
    if (!empty($parentid)) {
        $sql .= " AND {$parentfield} = '{$parentid}'";
    }

    $sql .= " AND journal_id = {$journal_id}";

    $res = mysqli_query($dbc, $sql);
    $row = mysqli_fetch_array($res);

    return $row["minmax"];
}

function insertSpreadSheetData($sheet, $columnid, $rowid, $data, $style)
{

    global $dbc;
    $sql = "INSERT INTO dev.data(sheetid,columnid,rowid,data,style,parsed,calc) VALUES ('{$sheet}',{$columnid},$rowid,'{$data}','{$style}','{$data}','{$data}')";
    $res = mysqli_query($dbc, $sql) or "Insert Spreadsheet data error " . die (mysqli_error($dbc));

    return $res;
}

function getItemByID($table, $id, $idfield)
{
    global $dbc;
    if (!empty($id)) {
        $sql = "SELECT * FROM {$table} WHERE {$idfield} = {$id} LIMIT 1";
        $rs = mysqli_query($dbc, $sql);
        if ($item = mysqli_fetch_array($rs)) {
            return $item;
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function getItemByIDCondition($table, $id, $idfield, $condition)
{
    global $dbc;
    if ($id != "")//!empty($id)
    {
        $sql = "SELECT * FROM {$table} WHERE {$idfield} = {$id}";
        $sql .= " {$condition} LIMIT 1";
        $rs = mysqli_query($dbc, $sql);
        if ($item = mysqli_fetch_array($rs)) {
            return $item;
        } else {
            return NULL;
        }
    } else {
        return NULL;
    }
}

function xmlEscape($string)
{
    return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;', ''), $string);
}

function updateSortIDonDelete($parentid, $parentfield, $itemid, $itemidfield, $sortid, $sortfield, $table)
{

    global $dbc;
    $minmax = getMinMaxSortID($parentid, $parentfield, $sortid, $sortfield, $table, "MAX");

    if ($sortid < $minmax) {
        $sql = "SELECT {$sortfield} FROM {$table} WHERE {$parentfield} = {$parentid} AND {$sortfield} > {$sortid} ORDER BY {$sortfield}";
        $res = mysqli_query($dbc, $sql) or die (mysqli_error($dbc) . "  " . $sql);

        $count = 0;
        while ($row = mysqli_fetch_array($res)) {
            $usql = "UPDATE {$table} SET {$sortfield} = {$sortid}+{$count} WHERE {$sortfield} = {$row[$sortfield]} AND {$parentfield} = {$parentid}";
            $ures = mysqli_query($dbc, $usql) or die (mysqli_error($dbc) . "  " . $usql);

            $count++;
        }
    }

    $data['data'] = array('success' => true);

    return $data;
}

function updateSortIDonDelete2($parentid, $parentfield, $itemid, $itemidfield, $sortid, $sortfield, $table, $journal_id)
{
    global $dbc;

    $minmax = getMinMaxSortID2($parentid, $parentfield, $sortid, $sortfield, $table, "MAX", $journal_id);

    if ($sortid < $minmax) {
        $sql = "SELECT {$sortfield} FROM {$table} WHERE {$parentfield} = '{$parentid}' AND {$sortfield} > {$sortid} AND journal_id = {$journal_id} ORDER BY {$sortfield}";
        $res = mysqli_query($dbc, $sql);

        $count = 0;
        while ($row = mysqli_fetch_array($res)) {

            $usql = "UPDATE {$table} SET {$sortfield} = {$sortid}+{$count} WHERE {$sortfield} = {$row[$sortfield]} AND {$parentfield} = '{$parentid}' AND journal_id = {$journal_id}";
            $ures = mysqli_query($dbc, $usql) or die (mysqli_error($dbc) . "  " . $usql);

            $count++;
        }
    }

    $data['data'] = array('success' => true);

    return $data;
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

function moveItemUpDownGrid2($parentid, $parentfield, $itemid, $itemidfield, $sortid, $sortfield, $table, $direction, $journal_id)
{
    global $dbc;
    if ($direction == "up") {

        $minmax = getMinMaxSortID2($parentid, $parentfield, $sortid, $sortfield, $table, "MIN", $journal_id);

        if ($sortid > $minmax) {

            $xsql = "UPDATE {$table} SET {$sortfield} = {$sortid} WHERE {$sortfield} = {$sortid}-1";
            if (!empty($parentid)) {
                $xsql .= " AND {$parentfield} = '{$parentid}'";
            }
            $xsql .= " AND journal_id = {$journal_id}";
            $xres = mysqli_query($dbc, $xsql);

            $sql = "UPDATE {$table} SET {$sortfield} = {$sortid}-1 WHERE {$itemidfield} = {$itemid}";
            if (!empty($parentid)) {
                $sql .= " AND {$parentfield} = '{$parentid}'";
            }
            $sql .= " AND journal_id = {$journal_id}";
            $res = mysqli_query($dbc, $sql);
        }

    } else if ($direction == "down") {

        $minmax = getMinMaxSortID2($parentid, $parentfield, $sortid, $sortfield, $table, "MAX", $journal_id);

        if ($sortid < $minmax) {

            $xsql = "UPDATE {$table} SET {$sortfield} = {$sortid} WHERE {$sortfield} = {$sortid}+1";
            if (!empty($parentid)) {
                $xsql .= " AND {$parentfield} = '{$parentid}'";
            }
            $xsql .= " AND journal_id = {$journal_id}";
            $xres = mysqli_query($dbc, $xsql);

            $sql = "UPDATE {$table} SET {$sortfield} = {$sortid}+1 WHERE {$itemidfield} = {$itemid}";
            if (!empty($parentid)) {
                $sql .= " AND {$parentfield} = '{$parentid}'";
            }
            $sql .= " AND journal_id = {$journal_id}";
            $res = mysqli_query($dbc, $sql);
        }
    }

    $data['data'] = array('success' => true, 'u' => $minmax, 'x' => $xsql);

    return $data;
}

function round_nearest_half($num)
{
    if ($num >= ($half = ($ceil = ceil($num)) - 0.5) + 0.25) return $ceil;
    else if ($num < $half - 0.25) return floor($num);
    else return $half;
}

