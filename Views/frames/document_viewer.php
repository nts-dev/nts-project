<?php
include_once '../../../config.php';

$id = filter_input(INPUT_GET, 'doc_id', FILTER_SANITIZE_NUMBER_INT);

$query = "
    SELECT
            tr.Report_Subject title,
            tr.explorer_id,
            tr.goal,
            tr.scope,
            (SELECT CONCAT(COALESCE(FirstName, ''),' ',COALESCE(SecondName, ''),' ',COALESCE(LastName, '')) FROM trainees WHERE ID = tr.supervisor)`supervisor`,
            tr.doc_input,
            tr.doc_output,
            tr.doc_frequency,
            (SELECT GROUP_CONCAT(CONCAT(COALESCE(FirstName, ''))) FROM trainees WHERE ID IN(SELECT employee_id FROM `tradestar_reports_to_employees` WHERE `report_id` = $id ))employees
    FROM
            tradestar_reports tr
    WHERE
            tr.Report_ID = " . $id;
$result = mysqli_query($dbc,$query) ;
$row = mysqli_fetch_assoc($result);


$doc_details = '<h4>Document Details</h4>
            <table class="table table-bordered table-condensed" style="width:40%">
            <tbody>
            <tr>
            <td class="col-md-2">Goal</td>
            <td style="word-wrap: break-word; white-space: pre-line;">' . $row['goal'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Scope</td>
            <td style="word-wrap: break-word; white-space: pre-line;">' . $row['scope'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Supervisor</td>
            <td>' . $row['supervisor'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Employee</td>
            <td>' . $row['employees'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Frequency</td>
            <td>' . $row['doc_frequency'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Input</td>
            <td>' . $row['doc_input'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Output</td>
            <td>' . $row['doc_output'] . '</td>
            </tr>
            <tr>
            <td class="col-md-2">Procedures</td>
            <td>' . $row['explorer_id'] . '</td>
            </tr>
            </tbody>
            </table>
            <h4>Table of Content</h4>';

$toc = '';
$doc_str = '';
$doc_str = generateDocument($id, $doc_str);

$full_document = $doc_details . $toc . $doc_str;

function generateDocument($id, $doc_str) {
    global $toc;
    $query = "SELECT
                        id,
                        parent_id,
                        title,
                        sort,
                        content
                FROM
                        document_toc
                WHERE
                        doc_id = " . $id . "
                    AND `visible` = 1        
                ORDER BY
                        parent_id = 0 DESC,
                        sort ASC";
    $result = mysqli_query($dbc,$query) ;
    $objects = array();
    $roots = array();
    while ($row = mysqli_fetch_assoc($result)) {
        if (!isset($objects[$row['id']])) {
            $objects[$row['id']] = new stdClass;
            $objects[$row['id']]->children = array();
        }

        $obj = $objects[$row['id']];
        $obj->id = $row['id'];
        $obj->title = $row['title'];
        $obj->sort = $row['sort'];
        $obj->content = $row['content'];

        if ($row['parent_id'] == 0) {
            $roots[] = $obj;
        } else {
            if (!isset($objects[$row['parent_id']])) {
                $objects[$row['parent_id']] = new stdClass;
                $objects[$row['parent_id']]->children = array();
            }

            $objects[$row['parent_id']]->children[$row['id']] = $obj;
        }
    }

    foreach ($roots as $obj) {
        $doc_str = printDocument($obj, $doc_str, '', true);
    }

    $doc_str .= '<br/>';
    $doc_str .= '<a href="#">Move to top</a>';
    $doc_str .= '<br/>';
    
    return $doc_str;
}

function printDocument(stdClass $obj, $doc_str, $chapter, $isRoot = false) {
    global $toc;
    if ($isRoot) {
        $link = $obj->sort;
        $chapter = $obj->sort . '.';
        $toc .= '<a href="#C' . $link . '">' . $chapter . ' ' . $obj->title . '</a><br />';
        $doc_str .= '<h4 id="C' . $link . '">' . $chapter . ' ' . $obj->title . '</h4>';
    } else {
        $link = '_' . $obj->sort;
        $chapter .= $obj->sort . '.';
        $toc .= '<a href="#C' . $link . '">' . $chapter . ' ' . $obj->title . '</a><br />';
        $doc_str .= '<h5 id="C' . $link . '">' . $chapter . ' ' . $obj->title . '</h5>';
    }

    $doc_str .= $obj->content;
    $doc_str .= '<br />';

    foreach ($obj->children as $child) {
        $doc_str = printDocument($child, $doc_str, $chapter);
    }
    
    return $doc_str;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $row['title'] ?></title>


        <!-- Bootstrap core CSS -->
        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <style>
            @font-face {
                font-family: 'museo_sans500';
                src: url('../css/fonts/museosans/500/MuseoSans_500-webfont.eot');
                src: url('../css/fonts/museosans/500/MuseoSans_500-webfont.eot?#iefix') format('embedded-opentype'),
                    url('../css/fonts/museosans/500/MuseoSans_500-webfont.woff2') format('woff2'),
                    url('../css/fonts/museosans/500/MuseoSans_500-webfont.woff') format('woff'),
                    url('../css/fonts/museosans/500/MuseoSans_500-webfont.ttf') format('truetype'),
                    url('../css/fonts/museosans/500/MuseoSans_500-webfont.svg#museo_sans500') format('svg');
                font-weight: normal;
                font-style: normal;

            }
            /*            .table-condensed{
                            font-size: 12px;
                        }*/
            h3, .h3 {
                font-size: 18px;
            }
            h1, .h1, h2, .h2, h3, .h3 {
                margin-bottom: 5px;
                margin-top: 10px;
            }
            h4, .h4 {
                font-size: 14px;
                font-weight: bold;
            }
            h5, .h5 {
                font-size: 12px;
                font-weight: bold;
            }
            h6, .h6 {
                font-size: 11px;
                font-weight: bold;
            }
            h4, .h4, h5, .h5, h6, .h6 {
                margin-bottom: 5px;
                margin-top: 10px;
            }

            html, body {
                font-family: "museo_sans500" !important;
                font-size: 11px !important;
            }
        </style>

        <!-- Custom styles for this template -->
        <!--<link href="../css/blog.css" rel="stylesheet">-->

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body>

        <div class="container-fluid">
<?php echo $full_document ?>
        </div><!-- /.container -->


        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
    </body>
</html>
