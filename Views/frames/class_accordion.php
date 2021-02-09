<?php
include_once '../../Controller/php/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$chapter = 1;
$doc_str = generateDocument($id);

function generateDocument($id, $doc_str = '') {
    $query = "SELECT
                        id,
                        parent_id,
                        title,
                        sort,
                        content
                FROM
                        libraries
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

        if ($row['id'] == $id) {
            $roots[] = $obj;
        } else {
            if (!isset($objects[$row['parent_id']])) {
                $objects[$row['parent_id']] = new stdClass;
                $objects[$row['parent_id']]->children = array();
            }

            $objects[$row['parent_id']]->children[$row['id']] = $obj;
        }
    }
    $chapter = 1;
    foreach ($roots as $obj) {

        $doc_str = printDocument($obj, $doc_str, $chapter, true);
        $chapter++;
    }

    return $doc_str;
}

function printDocument(stdClass $obj, $doc_str, $chapter, $isRoot = false) {
    if ($isRoot) {
    $chapter = $obj->sort . '.';

    $doc_str .= '<h3>' . $chapter . ' ' . $obj->title . '</h3><div>';
    } else {
        $chapter .= $obj->sort . '.';
        $doc_str .= '<h3>' . $chapter . ' ' . $obj->title . '</h3><div>';
    }

    $doc_str .= $obj->content;

    foreach ($obj->children as $child) {
        $doc_str .= '</div>';
//        $chapter++;
        $doc_str = printDocument($child, $doc_str, $chapter);
    }
    if ($isRoot) {
        $doc_str .= '</div>';
    }
    return $doc_str;
}

/*
  function generateDocument($id, $doc_str = '') {
  $query = "SELECT
  id,
  parent_id,
  title,
  sort,
  content
  FROM
  libraries
  WHERE
  parent_id = " . $id . "
  ORDER BY
  sort ASC";

  $result = mysqli_query($dbc,$query) ;
  while ($row = mysqli_fetch_assoc($result)) {
  $chapter = $row['sort'] . '.';
  $doc_str .= '<h3>' . $chapter . ' ' . $row['title'] . '</h3><div>';
  $doc_str .= $row['content'];
  $doc_str .= '</div>';
  }

  return $doc_str;
  }

 */
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
        <link href="../css/themes/smoothness/jquery-ui.css" rel="stylesheet" >

        <!--        <style>
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
                    }
                    h5, .h5 {
                        font-size: 12px;
                    }
                    h4, .h4, h5, .h5, h6, .h6 {
                        margin-bottom: 5px;
                        margin-top: 10px;
                    }

                    html, body {
                        font-family: "museo_sans500" !important;
                        font-size: 11px !important;
                    }
                </style>-->

        <!-- Custom styles for this template -->
        <!--<link href="../css/blog.css" rel="stylesheet">-->

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body>
        <div id="accordion">
            <?php echo $doc_str ?>
        </div>

        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="../js/jquery.min.js"></script>
        <script src="../js/jquery-ui.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script>

            jQuery(document).ready(function ($) {
                $("#accordion").accordion({
                    heightStyle: "content",
                    collapsible: true
                });
            });
        </script>
    </body>
</html>
