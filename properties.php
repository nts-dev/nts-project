<html>

    <head>
        <script src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/script/dhtmlx3.6pro/dhtmlx_pro_full/dhtmlx.js"></script>
        <link rel="stylesheet" type="text/css" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/script/dhtmlx3.6pro/dhtmlxTreeGrid/codebase/ext/dhtmlxtreegrid_property.css">
        <script  src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/script/dhtmlx3.6pro/dhtmlxGrid/codebase/ext/dhtmlxgrid_export.js"></script>
        <link rel="stylesheet" type="text/css" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/script/dhtmlx3.6pro/dhtmlx_pro_full/dhtmlx.css"/>
        <script  src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/script/dhtmlx3.6pro/dhtmlxTreeGrid/codebase/ext/dhtmlxtreegrid_property.js"></script>

        <script src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/script/dhtmlx3.6pro/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4f.js"></script>
        <script src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/script/dhtmlx3.6pro/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4j.js"></script>

        <!--  Jquery -->
        <script src="Views/js/jquery.min.js"></script>
        <script src="Views/js/jquery-ui.min.js"></script>

    </head>
    <body>
        <div id= 'gridlayout'><div id="toolbar1" style="width:100%; "></div>
            <div id="grid_here" style="width:60%; height:93%;"></div>
        </div>
        <!--<div id="grid_here" style="width:100%; height:80%;"></div>-->
        <script>

            var project_id = "<?php echo $_GET['project_id'] ?>";
            var document_id = "<?php echo $_GET['document_id'] ?>";
            var project_data_id = "<?php echo $_GET['project_data_id'] ?>";

            var lytSpecs = new dhtmlXLayoutObject(document.body, "1C", "dhx_skyblue");
            lytSpecs.cells("a").hideHeader();
            lytSpecs.cells("a").attachObject('gridlayout');
            specsproToolbar = new dhtmlXToolbarObject('toolbar1', 'dhx_skyblue');
            specsproToolbar.setIconsPath("Views/imgs/");
            specsproToolbar.addButton("refresh", 2, "Refresh", "refresh.png", "refresh.png");
//            specsproToolbar.addButton("delete", 2, "Delete Propertie(s)", "delete.png", "delete.png");

            specsproToolbar.attachEvent("onClick", function (id) {
                switch (id) {

                    case 'refresh':
                        mygrid.clearAndLoad("Controller/php/data_templates.php?action=9&project_id=" + project_id + "&document_id=" + document_id + "&project_data_id=" + project_data_id);
                        break;
//                    case 'delete':
//                        var id = mygrid.getSelectedRowId();
//                        var field = mygrid.cells(id, 0).getValue();
//                        $.get("data_files.php?action=28&cat_id=" + myId + "&field=" + field + "&id=" + id, function() {
//                            mygrid.deleteRow(id);
//
//                        });
                        //break;
                }
            });
            mygrid = new dhtmlXPropertyGrid('grid_here');
            mygrid.setImagePath('https://<?php echo $_SERVER['HTTP_HOST']; ?>/script/dhtmlx3.6pro/dhtmlxGrid/codebase/imgs/');
            mygrid.setColumnIds("label_name,project_data_label_data");
            mygrid.init();
            mygrid.load("Controller/php/data_templates.php?action=9&project_id=" + project_id + "&document_id=" + document_id + "&project_data_id=" + project_data_id);
            mygrid.collapseAll();

            mygrid.attachEvent("onEditCell", function (stage, rId, cInd, nValue, oValue) {

                var field = mygrid.getColumnId(cInd);
                if (stage === 2)

                {

                    $.get("Controller/php/data_templates.php?action=10&id=" + rId + "&field=" + field + "&fieldvalue=" + nValue, function (data) {

                        if (data.data.success)
                        {
                            mygrid.clearAndLoad("Controller/php/data_templates.php?action=9&project_id=" + project_id + "&document_id=" + document_id + "&project_data_id=" + project_data_id);

                        }
                    }, 'json');

                }
            });

        </script>

    </body>
</html>



