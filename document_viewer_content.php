<?php
include '../config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$query = "SELECT Report_Body,accordion FROM tradestar_reports WHERE Report_ID=" . $id;
$result = mysqli_query($dbc,$query);
$row = mysqli_fetch_assoc($result);
$article_text = $row["Report_Body"];
$accordion = $row["accordion"];
$image_path = "http://localhost";

//format article text

$article_text = str_replace("<object", "<object id=\"player\"", $article_text);
$article_text = str_replace("../../Controller/files", $image_path . "/projects_new/Controller/files", $article_text);
$article_text = str_replace("../userfiles", $image_path . "/userfiles", $article_text);
$article_text = str_replace("../video", $image_path . "/video", $article_text);
$article_text = str_replace("../nts_admin", $image_path . "/nts_admin", $article_text);
$article_text = str_replace("tinymce/jscripts", $image_path . "/script/tinymce/jscripts", $article_text);
?>

<?php
if ($accordion) {
    $article_text = str_replace('<h3>', '</div><h3>', $article_text);
    $article_text = str_replace('</h3>', '</h3><div>', $article_text);
    $article_text = substr($article_text, 6);
    $article_text = $article_text . '</div>';
    ?> 
    <div id="accordion">

    <?php } else { ?> 
        <div id="article"> 
            <?php
        }
        echo $article_text;
        ?>
    </div> 

    <?php if ($accordion) { ?> 
        <link href="Views/css/themes/smoothness/jquery-ui.css" rel="stylesheet" >
        <script src="Views/js/jquery.min.js"></script>
        <script src="Views/js/jquery-ui.js"></script>

        <script>

            jQuery(document).ready(function ($) {
                $("#accordion").accordion({
                    heightStyle: "content",
                    collapsible: true
                });
            });
        </script>
    <?php } ?>
