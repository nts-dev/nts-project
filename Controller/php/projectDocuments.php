<?php

require 'config_mysqli.php';
include_once 'GeneralClass.php';

$action = $_GET['action'];
switch ($action) {

    default:


        break;

    case 1:

        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<complete>";
        $query = "SELECT
                    ID a1,
                    CONCAT(
                            COALESCE(FirstName, ''),
                            ' ',
                            COALESCE(SecondName, ''),
                            ' ',
                            COALESCE(LastName, '')
                    )a2
                    FROM
                            trainees
                    WHERE (
                            IntranetId <> 0 || IntranetId IS NOT NULL
                    )
                    AND ID <> 33";

        $start = "SELECT
                            trainees.ID,
                            contact_attendent
                    FROM
                            relation_contact
                    JOIN trainees ON trainees.IntranetID = relation_contact.contact_id
                    AND trainees.status_id = 1
                    ORDER BY
                            trainees.ID";
        $result = mysqli_query($dbc, $start);
        echo "<option value='0'></option>";
        while ($row = mysqli_fetch_array($result)) {
            echo "<option value='{$row["ID"]}'><![CDATA[" . $row['contact_attendent'] . "]]></option>";
        }
        echo "</complete>";
        break;

    case 2:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $branchId = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_NUMBER_INT);
        $languageId = filter_input(INPUT_GET, 'language', FILTER_SANITIZE_NUMBER_INT);
        $userlggd = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);
        $access_rights = 1;

        if ($id === '9856') {
            $access_result = mysqli_query($dbc,
                "SELECT doc_access FROM project_map_privileges WHERE project_id = '" . $id . "' AND employee_id = " . $userlggd
            );
            $num_rows = mysqli_num_rows($access_result);
            if ($num_rows) {
                $row = mysqli_fetch_assoc($access_result);
                $access_rights = $row['map_access'];
            } else {
                $access_rights = 0;
            }
        }

        if ($access_rights > 0) {

            $qry = "SELECT
                        tr.Report_ID,
                        tr.Report_Employee_ID,
                        tr.Report_Date,
                        tr.Report_Subject,
                        tr.PrId,
                        tr.Report_Author,
                        tr.visible_in_projects,
                        tr.language_id,
                        tr.explorer_id,
                        tr.template_id,
                        tr.accordion,
                        tr.Report_Body,
                        c.FirstName Employee,
                        c2.contact_attendent Author,
                        p.project_name,
                        tradestar_reports_category.category_name,
                        projects_to_documents.id proj_doc_id,
                        projects_to_documents.default_report
                FROM
                       projects_to_documents
                JOIN tradestar_reports tr ON projects_to_documents.report_id = tr.Report_ID  AND visible_in_projects = 1";
            $qry .= $languageId > 0 ? " AND tr.language_id  = " . $languageId : "";
            $qry .= $branchId > 0 ? " JOIN document_to_branch ON document_to_branch.document_id = tr.Report_ID AND document_to_branch.branch_id = " . $branchId : "";
            $qry .= " LEFT JOIN projects_dir p ON p.id = tr.PrId
                LEFT JOIN trainees c ON c.ID = tr.Report_Employee_ID
                LEFT JOIN relation_contact c2 ON c2.contact_id = tr.Report_Author
                LEFT JOIN tradestar_reports_category ON tradestar_reports_category.id = tr.category_id WHERE projects_to_documents.project_id = " . $id . " AND projects_to_documents.is_active = 1";
            $qry .= " ORDER BY
                        Report_Date DESC";


            $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);

            header('Content-type:text/xml');
            echo '<?xml version = "1.0"?>' . PHP_EOL;
            echo '<rows>';
            while ($row = mysqli_fetch_array($res)) {

                $content = $row['Report_Body'];

                $trim = strip_tags($content);
                $chars = array(" ", "\n", "\t", "&ndash;", "&rsquo;", "&#39;", "&quot;", "&nbsp;");
                $trim = str_replace($chars, '', $trim);

                $totalCharacter = strlen(utf8_decode($trim));

                echo "<row id = 'doc_" . $row["Report_ID"] . "'>";
                echo "<cell> {$row["Report_ID"]} </cell>";
                echo "<cell><![CDATA[" . $row["Report_Employee_ID"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["Report_Date"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["Report_Subject"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["category_name"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["Author"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["language_id"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["explorer_id"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["template_id"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["accordion"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["visible_in_projects"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["proj_doc_id"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["default_report"] . "]]></cell>";
                echo "<cell><![CDATA[" . $totalCharacter . "]]></cell>";

                echo '<userdata name = "is_grid">1</userdata>';
                echo "</row>";
            }
            echo "</rows>";
        } else {
            header('Content-type:text/xml');
            echo '<?xml version = "1.0"?>' . PHP_EOL;
            echo '<rows>';
            echo "</rows>";
        }
        break;

    case 3:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc, "SELECT Report_Body FROM tradestar_reports WHERE Report_ID=" . $id);
        $row = mysqli_fetch_array($result);
        $content = $row[0];

        $image_path = "http://localhost";

        //format article text
//        $content = str_replace('"../../Controller/files', '"' . $image_path . '/projects_new/Controller/files', $content);
//        $content = str_replace('"../userfiles', '"' . $image_path . '/userfiles', $content);
//        $content = str_replace("../video", $image_path . "/video", $content);
//        $content = str_replace("../nts_admin", $image_path . "/nts_admin", $content);
//        $content = str_replace("tinymce/jscripts", $image_path . "/script/tinymce/jscripts", $content);

        echo json_encode(array("content" => $content));
        break;

    case 4:

        //update report document
        $id = $_POST['id'];
        //$content = $_POST['notes'];
        $content = mysqli_real_escape_string($dbc, $_POST['notes']);
        $userlggd = filter_input(INPUT_POST, 'eid', FILTER_SANITIZE_NUMBER_INT);

        $sql = "UPDATE tradestar_reports SET Report_Body = '" . $content . "' WHERE Report_ID =" . $id;
        if (mysqli_query($dbc, $sql)) {
//            setArchive($id, $content, $userlggd);
            $msg = "Successfully saved!";
        } else {
            $msg = "Error : " . mysqli_error($dbc);
        }
        echo json_encode(array("message" => $msg));
        break;

    case 5:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $branchId = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_NUMBER_INT);
        $languageId = filter_input(INPUT_GET, 'language', FILTER_SANITIZE_NUMBER_INT);
        $userlggd = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);
        $access_rights = 1;

        if ($id === '9856') {
            $access_result = mysqli_query($dbc,
                "SELECT file_access FROM project_map_privileges WHERE project_id = '" . $id . "' AND employee_id = " . $userlggd
            );
            $num_rows = mysqli_num_rows($access_result);
            if ($num_rows) {
                $row = mysqli_fetch_assoc($access_result);
                $access_rights = $row['map_access'];
            } else {
                $access_rights = 0;
            }
        }

        if ($access_rights > 0) {

            $qry = "SELECT
                        p.*, CONCAT(
                                COALESCE(t.FirstName, ''),
                                ' ',
                                COALESCE(t.SecondName, ''),
                                ' ',
                                COALESCE(t.LastName, '')
                        )uploader
                        
                FROM
                        projects_uploads p";
            $qry .= $branchId > 0 ? " JOIN projects_uploads_to_branch ON projects_uploads_to_branch.file_id = p.id AND projects_uploads_to_branch.branch_id = " . $branchId : "";
            $qry .= " LEFT JOIN trainees t ON t.IntranetID = p.file_uploader
                WHERE
                        file_parent = " . $id . "
                AND p.visible = 1";
            $qry .= $languageId > 0 ? " AND p.language_id  = " . $languageId : "";

            $res = mysqli_query($dbc, $qry);

            header('Content-type:text/xml');
            echo '<?xml version = "1.0"?>' . PHP_EOL;
            echo '<rows>';
            while ($row = mysqli_fetch_array($res)) {
                echo "<row id = 'fil_" . $row["id"] . "'>";
                echo "<cell><![CDATA[" . $row["id"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["file_name"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["file_type"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["language_id"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["file_size"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["file_upload_date"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["uploader"] . "]]></cell>";
                echo "<cell><![CDATA[" . $row["visible"] . "]]></cell>";
                echo "</row>";
            }
            echo "</rows>";
        } else {
            header('Content-type:text/xml');
            echo '<?xml version = "1.0"?>' . PHP_EOL;
            echo '<rows>';
            echo "</rows>";
        }
        break;

    case 6:
        $index = $_POST["index"];
        $fieldvalue = filter_input(INPUT_POST, 'fieldvalue', FILTER_SANITIZE_STRING);
        $id = $_POST["id"];
        $field = $_POST["colId"];
        $colType = $_POST["colType"];
        $fieldvalue = mysqli_real_escape_string($dbc, $fieldvalue);

        if ($field == 'language_id' || $field == 'template_id') {
            $fieldvalue = $fieldvalue > 0 ? $fieldvalue : 'NULL';
        }

        if ($field == "Report_Employee_ID") {
            $bID = getTableDetailField("trainees", $fieldvalue, "ID", "branch_id");
            // if($bID == 11){$bID = 5;}
            switch ($bID) {
                case 6:
                    $page = 1;
                    break;
                case 11:
                    $page = 5;
                    break;
                case 1:
                    $page = 3;
                    break;
                case 2:
                    $page = 2;
                    break;
                default:
                    $page = 1;
                    break;
            }
            $updateResult = mysqli_query($dbc, "UPDATE tradestar_reports SET $field = '" . $fieldvalue . "' ,Report_BranchID = '" . $page . "'  WHERE Report_ID = " . $id);
        } else {
            $updateResult = updateSQL("tradestar_reports", $field, $fieldvalue, $id, "Report_ID", $colType);
        }
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 7:

        $projectId = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $userlggd = filter_input(INPUT_POST, 'eid', FILTER_SANITIZE_NUMBER_INT);
        $branchId = filter_input(INPUT_POST, 'branch', FILTER_SANITIZE_NUMBER_INT);
        $languageId = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_NUMBER_INT);

        $tID = getTableDetailField("trainees", $userlggd, "IntranetID", "ID");
        $bID = getTableDetailField("trainees", $userlggd, "IntranetID", "branch_id");
        $subject = getTableDetailField("projects_dir", $projectId, "id", "project_name");
        $subject = mysqli_real_escape_string($dbc, $subject);

        switch ($bID) {
            case 6:
                $page = 1;
                break;
            case 11:
                $page = 5;
                break;
            case 1:
                $page = 3;
                break;
            case 2:
                $page = 2;
                break;
            default:
                $page = 1;
                break;
        }
        $insert = "INSERT INTO tradestar_reports(`Report_Date`,`Report_Employee_ID`,`Report_Body`,`PrId`,`Report_Author`,`Report_BranchID`,`Report_Subject`,`Is_Visible`,Report_Type,language_id) SELECT now(),487,'','" . $projectId . "','" . $userlggd . "','" . $page . "','" . $subject . "',0,2,contact_language_id FROM relation_contact WHERE contact_id =" . $userlggd;
        $result = mysqli_query($dbc, $insert)  or die(mysqli_error($dbc));

        if ($result) {

            $newId = mysqli_insert_id($dbc);
            $insert = "INSERT IGNORE INTO projects_to_documents (`report_id`,`project_id`,default_report) SELECT " . $newId . "," . $projectId . ",IF(COUNT(1)>0,0,1) FROM projects_to_documents WHERE project_id=" . $projectId . " AND default_report = 1";
            $insertResult = mysqli_query($dbc, $insert) or die(mysqli_error($dbc));
            if ($insertResult) {
                if ($branchId > 0) {
                    $query = "INSERT INTO document_to_branch (document_id,branch_id) VALUES ($newId,$branchId)";
                    mysqli_query($dbc, $query) or die(mysqli_error($dbc));
                }
                $data['data'] = array('response' => $result, 'newId' => $newId, 'text' => 'Successfully Saved');
            } else {
                $data['data'] = array('response' => $result, 'text' => 'An Error Occured While Saving');
            }
        } else {
            $data['data'] = array('response' => $result, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 8:

        $id = filter_input(INPUT_POST, 'id');
        $projectId = filter_input(INPUT_POST, 'project_id', FILTER_SANITIZE_NUMBER_INT);

        $idlist = explode(",", $id);
        foreach ($idlist as $value) {
            $ids[] = substr($value, 4, strlen($value));
        }

        $delete = "UPDATE projects_to_documents SET is_active = 0 WHERE project_id=" . $projectId . " AND report_id IN (" . implode(",", $ids) . ")";
//        echo $delete; exit;
        $deleteResult = mysqli_query($dbc, $delete);
        if ($deleteResult) {
            $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
        } else {
            $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
        }
        echo json_encode($data);
        break;

    case 9:

        $value = $_POST['document'];
        $update = "UPDATE tradestar_reports SET Doc_ID = '" . $value . "' WHERE Report_ID = " . $_POST['report'];
        $updateResult = mysqli_query($dbc, $update);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 10:

        error_reporting(E_ALL ^ E_DEPRECATED);
        ini_set('display_errors', FALSE);
        ini_set('display_startup_errors', FALSE);
        require("../../Model/phpToPDF.php");

        $reportId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $query = "SELECT * FROM tradestar_reports WHERE Report_ID =" . $reportId;
        $result = mysqli_query($dbc, $query);
        $row = mysqli_fetch_assoc($result);
        $report = $row['Report_Body'];
        $subject = $row['Report_Subject'];
        $title = 'mypdf.pdf';
//        $title = str_replace('?', '', $subject);
//        $title = str_replace('/', '-', $title);
//        $title = str_replace(' ', '_', $title);
//        $title.='.pdf'; //echo $title; exit;
// PUT YOUR HTML IN A VARIABLE
        $my_html = "<HTML><h3>" . $subject . "</h3>" . $report . "</HTML>";

// SET YOUR PDF OPTIONS -- FOR ALL AVAILABLE OPTIONS, VISIT HERE:  http://phptopdf.com/documentation/
        $pdf_options = array(
            "source_type" => 'html',
            "source" => $my_html,
            "footer" => 'Page phptopdf_on_page_number of phptopdf_pages_total',
            "action" => 'download',
            "file_name" => $title,
            "page_size" => 'A5');

// CALL THE phpToPDF FUNCTION WITH THE OPTIONS SET ABOVE
        phptopdf($pdf_options);
        break;

    case 11:

        ini_set('display_errors', '1');

        $projectId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $userId = filter_input(INPUT_GET, 'eid', FILTER_SANITIZE_NUMBER_INT);
        $languageId = filter_input(INPUT_GET, 'language', FILTER_SANITIZE_NUMBER_INT);
        $branch = filter_input(INPUT_GET, 'branchId', FILTER_SANITIZE_NUMBER_INT);

        $errors = array();

        $target_dir = "../files/";
        $target_file = $target_dir . basename($_FILES["file"]["name"]);
        $uploadOk = 1;

        $fileExtension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file already exists
        if (file_exists($target_file)) {
            $errors[] = "Sorry, file already exists.";
            $uploadOk = 0;
        }
        // Check file size
        if ($_FILES["file"]["size"] > 500000) {
            $errors[] = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        $hasMultipleExtensions = substr_count(basename($_FILES["file"]["name"]), '.') > 1;

        // Allow certain file formats
        $disallowed_extensions = array('php', 'php3', 'php4', 'phtml', 'pl', 'jsp', 'asp', 'htm');

        if (in_array($fileExtension, $disallowed_extensions) || $hasMultipleExtensions) {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {

            $errors[] = "Sorry, your file was not uploaded.";
            print_r("{state:'cancelled'}");

            // if everything is ok, try to upload file
        } else {

            $id = $_GET['id'];
            $filename = $_FILES["file"]["name"];
            $file_type = $_FILES["file"]['type'];
            $file_size = $_FILES["file"]["size"];

            $result = mysqli_query($dbc, "SELECT branch_id FROM `trainees` WHERE `IntranetID` =" . $userId);
            $row = mysqli_fetch_array($result);
            $branchId = $row[0];

            if ($branchId == '1') {
                date_default_timezone_set('Europe/Amsterdam');
            } elseif ($branchId == '6') {
                date_default_timezone_set('Africa/Nairobi');
            } elseif ($branchId == '8') {
                date_default_timezone_set('Asia/Kuala_Lumpur');
            } elseif ($branchId == '11') {
                date_default_timezone_set('Europe/Amsterdam');
            } else {
                date_default_timezone_set('Africa/Nairobi');
            }

            $date = date('Y-m-d H:i:s');

            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {

                if ($languageId > 0) {
                    $language = $languageId;
                } else {
                    $language = 1;
                }

                $QRY_INSERT = "INSERT INTO projects_uploads(file_name,file_type,file_parent,file_size,file_upload_date,file_uploader,language_id) VALUES ('$filename','$file_type'," . $projectId . ",$file_size,'$date',$userId," . $language . ")";

                $insertResult = mysqli_query($dbc, $QRY_INSERT);
                if ($insertResult) {
                    $fileId = mysqli_insert_id($dbc);
                    if ($branch > 0) {

                        $query = "INSERT INTO projects_uploads_to_branch (file_id,branch_id) VALUES ($fileId,$branch)";
                        mysqli_query($dbc, $query);
                    }
                }

                print_r("{state: true, name:'" . str_replace("'", "\\'", $filename) . "', size:" . $file_size . "}");
//                echo "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded.";
            } else {
                print_r("{state:'cancelled'}");
//                echo "Sorry, there was an error uploading your file.";
            }
        }

        break;

    case 12:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $delete = "DELETE FROM projects_uploads WHERE id = " . $id;
        $deleteResult = mysqli_query($dbc, $delete);
        if ($deleteResult) {
            $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
        } else {
            $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
        }
        echo json_encode($data);
        break;

    case 13:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $qry = "SELECT
                        th.History_ID,
                        th.History_Report_ID,
                        th.History_Body,
                        tr.Report_Employee_ID,
                        th.History_Date,
                        tr.Report_Subject,
                        th.History_Author,
                        c.FirstName Employee,
                        c2.contact_attendent Author,
                        p.project_name
                FROM
                        tradestar_reports_archive th
                JOIN tradestar_reports tr ON tr.Report_ID = th.History_Report_ID
                LEFT JOIN projects_dir p ON p.id = tr.PrId
                LEFT JOIN trainees c ON c.ID = tr.Report_Employee_ID
                LEFT JOIN relation_contact c2 ON c2.contact_id = th.History_Author
                WHERE
                        th.History_Report_ID = " . $id . "
                ORDER BY
                        th.History_Date DESC";

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {

            $content = $row['History_Body'];

            $trim = strip_tags($content);
            $chars = array(" ", "\n", "\t", "&ndash;", "&rsquo;", "&#39;", "&quot;", "&nbsp;");
            $trim = str_replace($chars, '', $trim);

            $totalCharacter = strlen(utf8_decode($trim));
            echo "<row id = '" . $row["History_ID"] . "'>";
            echo "<cell> {$row["History_ID"]} </cell>";
            echo "<cell><![CDATA[" . $row["Report_Employee_ID"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["History_Date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Report_Subject"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["project_name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Author"] . "]]></cell>";
            echo "<cell><![CDATA[" . $totalCharacter . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 14:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc, "SELECT History_Body FROM tradestar_reports_archive WHERE History_ID=" . $id);
        $row = mysqli_fetch_array($result);
        $content = $row[0];

        echo json_encode(array("content" => $content));
        break;

    case 15:
        switch ($_GET['case']) {
            case 1:
                $QRYDEL = "DELETE FROM tradestar_reports_archive WHERE History_Report_ID = '" . $_GET['id'] . "'";
                $deleteResult = mysqli_query($dbc, $QRYDEL);
                if ($deleteResult) {
                    $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
                } else {
                    $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
                }
                break;
            default:
                $QRYDEL = "DELETE FROM tradestar_reports_archive WHERE History_ID = '" . $_GET['id'] . "'";
                $deleteResult = mysqli_query($dbc, $QRYDEL);
                if ($deleteResult) {
                    $data['data'] = array('response' => $deleteResult, 'text' => 'Successfully Deleted');
                } else {
                    $data['data'] = array('response' => $deleteResult, 'text' => 'An Error Occured While Deleting');
                }
                break;
        }

        echo json_encode($data);

        break;

    case 16:
        $docId = filter_input(INPUT_POST, 'search_doc_input', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc, "SELECT PrId from tradestar_reports WHERE Report_ID =" . $docId);
        $row = mysqli_fetch_array($result);
        $projectId = $row[0];

        if ($projectId) {
            $data['data'] = array('response' => TRUE, 'text' => 'Successfully Added', 'item_id' => $projectId);
        } else {
            $data['data'] = array('response' => FALSE, 'text' => 'Document does not exist');
        }
        echo json_encode($data);
        break;

    case 17:

        $fieldvalue = $_POST["nValue"];
        $id = $_POST["id"];
        $field = $_POST["colId"];

        if ($field === 'default_report') {
            mysqli_query($dbc, "UPDATE projects_to_documents SET default_report = 0 WHERE report_id=" . $id . " AND default_report=1");
            $updateResult = updateSQL("projects_to_documents", $field, $fieldvalue, $id, "report_id", $colType);
        } else {
            $updateResult = updateSQL("`tradestar_reports`", $field, $fieldvalue, $id, "Report_ID", $colType);
        }

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 18:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $qry = "SELECT
                        tr.Report_ID,
                        tr.Report_Employee_ID,
                        tr.Report_Date,
                        tr.Report_Subject,
                        tr.PrId,
                        tr.Report_Author,
                        tr.Report_ID,
                        tr.visible_in_projects,
                        tr.language_id,
                        tr.explorer_id,
                        tr.template_id,
                        tr.accordion,
                        c.FirstName Employee,
                        c2.contact_attendent Author,
                        p.project_name,
                        tradestar_reports_category.category_name,
                        projects_to_documents.id proj_doc_id,
                        projects_to_documents.default_report
                FROM
                        projects_to_documents
                JOIN tradestar_reports tr ON projects_to_documents.report_id = tr.Report_ID
                LEFT JOIN projects_dir p ON p.id = tr.PrId
                LEFT JOIN trainees c ON c.ID = tr.Report_Employee_ID
                LEFT JOIN relation_contact c2 ON c2.contact_id = tr.Report_Author
                LEFT JOIN tradestar_reports_category ON tradestar_reports_category.id = tr.category_id
                WHERE projects_to_documents.project_id = " . $id . " AND projects_to_documents.is_active = 1
                ORDER BY
                        Report_Date DESC";

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {

            $content = $row['Report_Body'];

            $trim = strip_tags($content);
            $chars = array(" ", "\n", "\t", "&ndash;", "&rsquo;", "&#39;", "&quot;", "&nbsp;");
            $trim = str_replace($chars, '', $trim);

            $totalCharacter = strlen(utf8_decode($trim));

            echo "<row id = 'doc_" . $row["Report_ID"] . "'>";
            echo "<cell> {$row["Report_ID"]} </cell>";
            echo "<cell><![CDATA[" . $row["Report_Employee_ID"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Report_Date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Report_Subject"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["category_name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Author"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["language_id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["explorer_id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["template_id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["accordion"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["visible_in_projects"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["proj_doc_id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["default_report"] . "]]></cell>";
            echo "<cell><![CDATA[" . $totalCharacter . "]]></cell>";
            echo '<userdata name = "is_grid">1</userdata>';
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 19:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $qry = "SELECT
                        p.*, CONCAT(
                                COALESCE(t.FirstName, ''),
                                ' ',
                                COALESCE(t.SecondName, ''),
                                ' ',
                                COALESCE(t.LastName, '')
                        )uploader
                FROM
                        projects_uploads p
                LEFT JOIN trainees t ON t.IntranetID = p.file_uploader
                WHERE
                        file_parent = " . $id;
        $res = mysqli_query($dbc, $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {
            echo "<row id = 'fil_" . $row["id"] . "'>";
            echo "<cell><![CDATA[" . $row["id"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["file_name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["file_type"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["file_description"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["file_size"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["file_upload_date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["uploader"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["visible"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 20:

        $fieldvalue = $_POST["nValue"];
        $id = $_POST["id"];
        $field = $_POST["colId"];

        $updateResult = updateSQL("`projects_uploads`", $field, $fieldvalue, $id, "id", $colType);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);

        break;

    case 21:

        $projectId = filter_input(INPUT_POST, 'tId', FILTER_SANITIZE_NUMBER_INT);
        $docId = filter_input(INPUT_POST, 'sId', FILTER_SANITIZE_NUMBER_INT);
        $event = filter_input(INPUT_POST, 'event', FILTER_SANITIZE_STRING);

        if ($event == 'move') {
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);

            $update = "UPDATE projects_to_documents SET project_id = '" . $projectId . "' WHERE `id` =" . $id;
        }
        if ($event == 'link') {
            $update = "INSERT IGNORE INTO projects_to_documents (`report_id`,`project_id`) VALUES (" . $docId . "," . $projectId . ")";
        }

        $updateResult = mysqli_query($dbc, $update);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 22:

        $projectId = filter_input(INPUT_POST, 'tId', FILTER_SANITIZE_NUMBER_INT);
        $fileId = filter_input(INPUT_POST, 'sId', FILTER_SANITIZE_NUMBER_INT);
        $update = "UPDATE projects_uploads SET file_parent = '" . $projectId . "' WHERE id = " . $fileId;
        $updateResult = mysqli_query($dbc, $update);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 23:
//new blank recur
        $projectId = filter_input(INPUT_GET, 'proj_id', FILTER_SANITIZE_NUMBER_INT);
        $fileId = filter_input(INPUT_GET, 'file_id', FILTER_SANITIZE_NUMBER_INT);


        $insert = "INSERT INTO projects_uploads(file_name,file_type,file_parent,file_size,file_upload_date,file_uploader) SELECT `file_name`,`file_type`," . $projectId . ",file_size,file_upload_date,file_uploader FROM projects_uploads WHERE id =" . $fileId;


        $insertResult = mysqli_query($dbc, $insert) or die(mysqli_error($dbc) . $insert);
        if ($insertResult) {
            $data['data'] = array('response' => $insertResult, 'text' => 'Successfully Copied');
        } else {
            $data['data'] = array('response' => $insertResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 24:

        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<complete>";
        $query = "SELECT * from xoops_shop_languages ORDER BY sort_order ASC";
        $result = mysqli_query($dbc, $query);
        while ($row = mysqli_fetch_array($result)) {
            if ($row["languages_id"] === 1) {
                echo "<option value='{$row["languages_id"]}' selected='1'>" . $row["name"] . "</option>";
            } else {
                echo "<option value='{$row["languages_id"]}'>" . $row["name"] . "</option>";
            }
        }
        echo "</complete>";
        break;

    case 25:

        $index = $_POST["index"];
        $fieldvalue = filter_input(INPUT_POST, 'fieldvalue', FILTER_SANITIZE_NUMBER_INT);
        $id = $_POST["id"];
        $field = $_POST["colId"];
        $colType = $_POST["colType"];
        $fieldvalue = mysqli_real_escape_string($dbc, $fieldvalue);

        $fieldvalue = $fieldvalue ? $fieldvalue : 'NULL';

        $updateResult = updateSQL("tradestar_reports", $field, $fieldvalue, $id, "Report_ID", $colType);

        if ($updateResult) {

            if ($fieldvalue > 0) {

                $query = "
                SELECT
                    doc_content
                FROM
                    tbdocuments
                WHERE
                    doc_id = " . $fieldvalue . "
                AND doc_lang_id =(
                    SELECT
                    IF(
                    (language_id > 0),
                    language_id,
                    (
                        SELECT
                                relation_contact.contact_language_id
                        FROM
                                relation_contact
                        JOIN trainees ON trainees.IntranetID = relation_contact.contact_id
                        WHERE
                                trainees.ID = Report_Employee_ID
                    )
                    )language_id
                    FROM
                        tradestar_reports
                    WHERE
                        Report_ID = " . $id . "
                )";

                $result = mysqli_query($dbc, $query);
                $row = mysqli_fetch_array($result);
                $content = $row[0];

                //update report document
            } else {
                $content = '';
            }
            $sql = "UPDATE tradestar_reports SET Report_Body = '" . $content . "' WHERE Report_ID =" . $id;
            if (mysqli_query($dbc, $sql)) {
                $userlggd = filter_input(INPUT_POST, 'eid', FILTER_SANITIZE_NUMBER_INT);
                setArchive($id, $content, $userlggd);
            }

            $image_path = "http://localhost";

            $content = str_replace('"../../Controller/files', '"' . $image_path . '"/projects_new/Controller/files', $content);
            $content = str_replace('"../userfiles', '"' . $image_path . '/userfiles', $content);

            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated', "content" => $content);
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }
        echo json_encode($data);
        break;

    case 26:
        header("Content-type:text/xml");
        print("<?xml version = \"1.0\"?>");
        echo "<complete>";
        $query = "SELECT * from tradestar_reports_category";
        $result = mysqli_query($dbc, $query);
        while ($row = mysqli_fetch_array($result)) {
            echo "<option value='{$row["id"]}'>" . $row["category_name"] . "</option>";
        }
        echo "</complete>";
        break;

    case 27:
        $index = $_POST["index"];
        $fieldvalue = $_POST["fieldvalue"];
        $id = $_POST["id"];
        $field = $_POST["colId"];
        $colType = $_POST["colType"];

        $updateResult = updateSQL("projects_uploads", $field, $fieldvalue, $id, "id", $colType);

        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Updated');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Saving');
        }

        echo json_encode($data);

        break;

    case 28:

        $langId = $_POST['lang_id'];
        $docId = $_POST['doc_id'];
        $article_text = $_POST["content"];
        $article_text = mysqli_real_escape_string($dbc, $article_text);
        $userId = filter_input(INPUT_POST, 'eid', FILTER_SANITIZE_NUMBER_INT);

        if ($langId > 0) {
            $updateSQL = "UPDATE tbdocuments SET doc_content = '" . $article_text . "',doc_status = IF(doc_status='1','8','6') WHERE doc_id = " . $docId . " AND doc_lang_id = " . $langId;
        } else {
            $updateSQL = "UPDATE tbdocuments SET doc_content = '" . $article_text . "',doc_status = IF(doc_status='1','8','6') WHERE doc_id = " . $docId . " AND doc_lang_id = SELECT contact_language_id FROM relation_contact contact_id = " . $userId;
        }

        $updateResult = mysqli_query($dbc, $updateSQL) or die(mysqli_error($dbc) . $updateSQL);
        if ($updateResult) {

            $qry_insert = "INSERT INTO tbdocuments_history(`doc_lang_id`,`doc_datetime`,`doc_author_id`,`doc_content`,`doc_parent_id`,`doc_revised_by`)
              values(" . $langId . ",Now()," . $userId . ",'" . $article_text . "'," . $docId . "," . $userId . ")";

            $res_insert = mysqli_query($dbc, $qry_insert) or die("" . mysqli_error($dbc));

            $data['data'] = array('response' => $updateResult, 'text' => 'Data Successfully Saved');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'Error: Data Not Saved');
        }

        echo json_encode($data);
        break;

    case 29:
        $eventid = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $bool = false;
        $sql = "SELECT count(*) as Cnt FROM events WHERE event_pid = '" . $eventid . "'";
        $result = mysqli_query($dbc, $sql);
        $row = mysqli_fetch_assoc($result);
        if ($row['Cnt'] > 0) {
            $bool = true;
            $message = "You cant delete task with subtasks!";
        } else {
            $message = "Task successfully deleted!";
        }
        echo json_encode(array("response" => $message, "bool" => $bool));
        break;

    case 30:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $qry = "
                SELECT
                        tr.Report_ID,
                        tr.Report_Date,
                        tr.Report_Subject,
                        xsl.`name` `language`,
                        CONCAT(COALESCE(c.FirstName, ''),' ',COALESCE(c.SecondName, ''),' ',COALESCE(c.LastName, ''))Employee,
                        c1.contact_attendent Report_Employee,
                        c2.contact_attendent Author,
                        tradestar_reports_category.category_name,
                        projects_to_documents.project_id,
                        projects_to_documents.id,
                        projects_dir.project_name
                FROM
                        projects_to_documents
                JOIN tradestar_reports tr ON projects_to_documents.report_id = tr.Report_ID
                JOIN projects_dir  ON projects_dir.id = projects_to_documents.project_id
                LEFT JOIN trainees c ON c.ID = tr.Report_Employee_ID
                LEFT JOIN relation_contact c1 ON c1.contact_id = c.IntranetID
                LEFT JOIN xoops_shop_languages xsl ON xsl.languages_id = tr.language_id
                LEFT JOIN relation_contact c2 ON c2.contact_id = tr.Report_Author
                LEFT JOIN tradestar_reports_category ON tradestar_reports_category.id = tr.category_id WHERE projects_to_documents.is_active = 0 ";
        if ($id) {
            $qry .= " AND projects_to_documents.report_id = $id";
        }
        $qry .= " ORDER BY
                        Report_Date DESC";

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);

        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {

            echo "<row id = '" . $row["id"] . "'>";
            echo "<cell></cell>";
            echo "<cell><![CDATA[[" . generateProjectId($row["project_id"]) . "] " . $row["project_name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Report_ID"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Report_Employee"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Report_Date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Report_Subject"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["category_name"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Author"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["language"] . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 31:

        $id = filter_input(INPUT_POST, 'id');
//        $projectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);

        $update = "UPDATE projects_to_documents SET is_active = 1 WHERE id IN ('" . $id . "')";

        $updateResult = mysqli_query($dbc, $update);
        if ($updateResult) {
            $data['data'] = array('response' => $updateResult, 'text' => 'Successfully Restored');
        } else {
            $data['data'] = array('response' => $updateResult, 'text' => 'An Error Occured While Retrieving');
        }
        echo json_encode($data);
        break;

    case 32:

        $qry = "SELECT 
                    th.History_Report_ID,
                    tr.Report_Employee_ID,
                    tr.Report_Subject,
                    tr.Report_Body,
                    th.History_Author,
                    th.History_Date,
                    c.FirstName Employee,
                    c2.contact_attendent Author
            FROM
                    tradestar_reports_archive th
            JOIN tradestar_reports tr ON tr.Report_ID = th.History_Report_ID
            LEFT JOIN trainees c ON c.ID = tr.Report_Employee_ID
            LEFT JOIN relation_contact c2 ON c2.contact_id = th.History_Author
            WHERE
                    DATE_FORMAT(th.History_Date, '%Y-%m-%d')= CURDATE()
            ORDER BY
                    History_Report_ID";

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {

            $content = $row['Report_Body'];

            $trim = strip_tags($content);
            $chars = array(" ", "\n", "\t", "&ndash;", "&rsquo;", "&#39;", "&quot;", "&nbsp;");
            $trim = str_replace($chars, '', $trim);

            $totalCharacter = strlen(utf8_decode($trim));

            echo "<row id = '" . $row["History_Report_ID"] . "'>";
            echo "<cell> {$row["History_Report_ID"]} </cell>";
            echo "<cell><![CDATA[" . $row["Report_Subject"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Employee"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Author"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["History_Date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $totalCharacter . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;

    case 33:

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        $result = mysqli_query($dbc, "SELECT Report_Body FROM tradestar_reports WHERE Report_ID=" . $id);
        $row = mysqli_fetch_array($result);
        $content = $row[0];

        echo json_encode(array("content" => $content));
        break;

    case 34:
        $actualDate = date('Y-m-d', strtotime(filter_input(INPUT_GET, "actualDate")));

        $qry = "SELECT count(*) as counts,
                    th.History_Report_ID,
                    tr.Report_Employee_ID,
                    tr.Report_Subject,
                    tr.Report_Body,
                    th.History_Author,
                    th.History_Date,
                    c.FirstName Employee,
                    c2.contact_attendent Author
            FROM
                    tradestar_reports_archive th
            JOIN tradestar_reports tr ON tr.Report_ID = th.History_Report_ID
            LEFT JOIN trainees c ON c.ID = tr.Report_Employee_ID
            LEFT JOIN relation_contact c2 ON c2.contact_id = th.History_Author
            WHERE
                    DATE_FORMAT(th.History_Date, '%Y-%m-%d')= '" . $actualDate . "'
            GROUP BY th.History_Report_ID
            ORDER BY
                    History_Report_ID";

        $res = mysqli_query($dbc, $qry) or die(mysqli_error($dbc) . $qry);
        header('Content-type:text/xml');
        echo '<?xml version = "1.0"?>' . PHP_EOL;
        echo '<rows>';
        while ($row = mysqli_fetch_array($res)) {

            $content = $row['Report_Body'];

            $trim = strip_tags($content);
            $chars = array(" ", "\n", "\t", "&ndash;", "&rsquo;", "&#39;", "&quot;", "&nbsp;");
            $trim = str_replace($chars, '', $trim);

            $totalCharacter = strlen(utf8_decode($trim));

            echo "<row id = '" . $row["History_Report_ID"] . "'>";
            echo "<cell> {$row["History_Report_ID"]} </cell>";
            echo "<cell><![CDATA[" . $row["Report_Subject"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Employee"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["Author"] . "]]></cell>";
            echo "<cell><![CDATA[" . $row["History_Date"] . "]]></cell>";
            echo "<cell><![CDATA[" . $totalCharacter . "]]></cell>";
            echo "</row>";
        }
        echo "</rows>";
        break;
}

function setArchive($tradestar_report_id, $report_editor, $userlggd)
{
    $date = new DateTime();
    $today = $date->format('Y-m-d H:i:s');
//get author of the already logged
    $author = $userlggd;
    if ($author == null) {
        $author = 'NULL';
    }
    $category = getTableDetailField("tradestar_reports", $tradestar_report_id, "Report_ID", "Report_Category");
    $subject = getTableDetailField("tradestar_reports", $tradestar_report_id, "Report_ID", "Report_Subject");
    $rptCategory = getTableDetailField("tradestar_reports", $tradestar_report_id, "Report_ID", "PrId");
//    $report_editor = mysqli_real_escape_string($dbc,$report_editor);
    $sql = "INSERT INTO tradestar_reports_archive(History_Date,History_Report_ID,History_Body,History_Category,History_Subject,History_Author,Report_Category)
                        VALUES ('{$today}','{$tradestar_report_id}','{$report_editor}','{$category}','{$subject}','{$author}','{$rptCategory}')";
    $res = mysqli_query($dbc, $sql) or die(mysqli_error($dbc) . " INSERT TRADESTAR HISTORY ERROR");
//update time of report on changes
    $sql = "Update tradestar_reports SET Report_Date = '$today' where Report_ID = '$tradestar_report_id'";
    $res = mysqli_query($dbc, $sql) or die(mysqli_error($dbc) . " INSERT TRADESTAR HISTORY ERROR");


    $result = mysqli_query($dbc, "SELECT History_ID FROM tradestar_reports_archive WHERE History_Report_ID = " . $tradestar_report_id . " ORDER BY History_ID DESC LIMIT 49,1");
    if (mysqli_num_rows($result) > 0) {
        $fetch = mysqli_fetch_assoc($result);
        mysqli_query($dbc, "DELETE FROM tradestar_reports_archive WHERE History_ID <" . $fetch['History_ID'] . " AND History_Report_ID = " . $tradestar_report_id);
    }
}

function generateProjectId($itemId)
{
    if (strlen($itemId) == 1) {
        $projectId = "P00000" . $itemId . "";
    } else if (strlen($itemId) == 2) {
        $projectId = "P0000" . $itemId . "";
    } else if (strlen($itemId) == 3) {
        $projectId = "P000" . $itemId . "";
    } else if (strlen($itemId) == 4) {
        $projectId = "P00" . $itemId . "";
    } else if (strlen($itemId) == 5) {
        $projectId = "P0" . $itemId . "";
    } else {
        $projectId = $itemId;
    }

    return $projectId;
}
