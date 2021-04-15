<?php
require '../auth.php';

if (!isset($_SESSION['USER'])) {
    header("location: ../login.php");
}

$session = unserialize($_SESSION['USER']);

$user = $session->getBOUser();
$eid = $user->getTraineeId();
$username = $user->getAttendent();
?>
<html>
    <head>
        <title>Projects Program</title>
        <link rel="shortcut icon" href="Views/imgs/laptop_settings-512.png"  type="image/x-icon"/>

        <?php
            CSSPackage::DHTMLX();
            CSSPackage::FONTAWESOME();

            JSPackage::DHTMLX();
            JSPackage::JQUERY();
        ?>
        <link rel="stylesheet" type="text/css" href="Views/css/custom.css"/>

        <style>

            html, body {
                width: 100%;
                height: 100%;
                margin: 0px;
                overflow: hidden;
            }

            .formbox{
                background-color: #ffffff;
                color: blue;
                font-family:Tahoma;
                font-size: 92%;
                padding-left: 10px;
                padding-top: 10px;
            }
            @font-face {
                font-family: 'museo_sans500';
                src: url('Views/css/fonts/museosans/500/MuseoSans_500-webfont.eot');
                src: url('Views/css/fonts/museosans/500/MuseoSans_500-webfont.eot?#iefix') format('embedded-opentype'),
                    url('Views/css/fonts/museosans/500/MuseoSans_500-webfont.woff2') format('woff2'),
                    url('Views/css/fonts/museosans/500/MuseoSans_500-webfont.woff') format('woff'),
                    url('Views/css/fonts/museosans/500/MuseoSans_500-webfont.ttf') format('truetype'),
                    url('Views/css/fonts/museosans/500/MuseoSans_500-webfont.svg#museo_sans500') format('svg');
                font-weight: normal;
                font-style: normal;

            }
            .dhxtree_dhx_skyblue .standartTreeRow, .dhxtree_dhx_skyblue .standartTreeRow_lor {
                font-family: "museo_sans500" !important;
                font-size: 11px !important;
            }
            .dhxtree_dhx_skyblue .selectedTreeRow_lor, .dhxtree_dhx_skyblue .selectedTreeRow {
                background-color: #b5deff !important;
                background-repeat: repeat-x;
                font-family: "museo_sans500" !important;
                font-size: 11px !important;
                overflow: hidden;
            }
            .dhxtree_dhx_skyblue span.selectedTreeRow_lor {
                background-color: rgb(225, 244, 255) !important;
                box-sizing: border-box;
                height: 13px;
                line-height: 12px;
                padding: 0 0 1px;
            }
            div.gridbox_dhx_web.gridbox table.obj.row20px tr.rowselected td {
                background-color: rgb(225, 244, 255) !important;
                border-right-color: #fff !important;
            }
            .dhxform_obj_dhx_terrace input.dhxform_textarea, .dhxform_obj_dhx_terrace textarea.dhxform_textarea {
                background-color: white;
                margin: 0;
                padding: 4px 2px !important;
                font-family: "museo_sans500" !important;
                font-size: 11px !important;
            }

            .dhxform_obj_dhx_terrace div.dhxform_item_label_left div.dhxform_control {
                float: left;
                margin-left: 3px;
                font-family: "museo_sans500" !important;
                font-size: 11px !important;
            }
            .dhxform_obj_dhx_terrace div.dhxform_label {
                color: black;
                white-space: normal;
                font-family: "museo_sans500" !important;
                font-size: 11px !important;
            }
            .dhxform_obj_dhx_terrace div.dhxform_label {
                color: black;
                font-family: "museo_sans500" !important;
                font-size: 11px !important;
                overflow: hidden;
                white-space: normal;
            }
            div.dhxcombo_dhx_terrace input.dhxcombo_input {
                font-family: "museo_sans500" !important;
                font-size: 11px !important;
            }
            div.dhxcombolist_dhx_terrace {
                font-family: "museo_sans500" !important;
                font-size: 11px !important;
            }
            div.dhx_toolbar_poly_dhx_terrace td {
                font-family: "museo_sans500" !important;
                font-size: 11px !important;
            }
            .dhxwins_vp_dhx_terrace div.dhxwin_active div.dhx_cell_wins div.dhx_cell_toolbar_def{
                padding: 6px; border-width: 1px;
            }

            .dhx_toolbar_dhx_terrace div.dhx_toolbar_btn div.dhxtoolbar_text{
                margin-left: 0;
                line-height: 18px;
            }

            .dhx_toolbar_dhx_terrace div.dhx_toolbar_btn i.fa{
                margin-top: 0;
                color: #0072BC;
            }
        </style>
    </head>
    <body>
        <script>
            var uID = "<?= $eid ?>";
            var username = "<b><?= $_SESSION['contact_attendent'] ?></b>";

            const DHTMLXPATH = "<?=  Boot::WWWROOT .'packages/lib/dhtmlxSuite4/' ?>";
            // var branchId = '1';
            // var languageId = '1';


        </script> 
        <script src="Views/js/ats_functions.js"></script>
        <script src="Views/js/projects_functions.js"></script>
        <script src="Views/js/document_functions.js"></script>
        <script src="Views/js/main.js"></script>
<!--        <script src="Views/js/assets.js"></script>-->
        <script src="Views/js/tasks.js"></script>
        <script src="Views/js/overview.js"></script>
        <script src="Views/js/documents.js"></script>
        <script src="Views/js/libraries.js"></script>
        <script src="Views/js/edited_content.js"></script>
        <script src="Views/js/project_details.js"></script>
        <script src="Views/js/project_privileges_functions.js"></script>
        <script src="Views/js/project_privileges.js"></script>
        <script src="Views/js/map_privileges.js"></script>
<!--        <script src="Views/js/asset_planning.js"></script>-->
        <script src="Views/js/questions.js"></script>
    </body>

</html>
