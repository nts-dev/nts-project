<?php

include '../config/config.php';

$start_date = (isset($_GET['start_date'])) ? $_GET['start_date'] : date("Y-m-d");
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$result = mysqli_query($dbc,"SELECT COUNT(1) FROM projects_planning p JOIN `events` e ON e.event_id = p.event_id AND e.multiuser = 0 AND e.visible = 1 AND YEAR(start_date)= YEAR(CURDATE()) WHERE p.parent = " . $id . " ORDER BY e.start_date");
$row = mysqli_fetch_array($result);
$amountEvents = $row[0];
?>
<!DOCTYPE html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Bootstrap layout</title>
    <script src="Model/gantt4/codebase/dhtmlxgantt.js" type="text/javascript" charset="utf-8"></script>
    <link rel="stylesheet" href="Model/gantt4/codebase/dhtmlxgantt.css" type="text/css" media="screen" title="no title" charset="utf-8">

    <script src="Views/js/jquery.min.js" type="text/javascript" charset="utf-8"></script>

    <link rel="stylesheet" href="Views/css/bootstrap.min.css">
    <link rel="stylesheet" href="Views/css/bootstrap-theme.min.css">
    <script src="Views/js/bootstrap.min.js"></script>
    <script src="Views/js/ats_functions.js"></script>
    <script>
        var today_actual_date = new Date().format('Y-m-d');
        var uID = <?php echo $_GET['eid'] ?>;
    </script>
    <style type="text/css">
        html, body{ height:100%; padding:0px; margin:0px; overflow: hidden;}
        .weekend{ background: #f4f7f4 !important;}
        .gantt_selected .weekend{ background:#FFF3A1 !important; }
        .well {
            text-align: right;
        }
        @media (max-width: 991px) {
            .nav-stacked>li{ float: left;}
        }
        .container-fluid .row {
            margin-bottom: 10px;
        }
        .container-fluid .gantt_wrapper {
            height: 310px;
            width: 100%;
        }
        .gantt_container {
            border-radius: 4px;
        }
        .gantt_grid_scale { background-color: transparent; }
        .gantt_hor_scroll { margin-bottom: 1px; }
        .btn-default {
            background-image: none;
        }
        .nested_task .gantt_add{
            display: none;
        }
    </style>
</head>
<body onload="doOnLoad()">
    <div class="container-fluid">
        <div class="row">
            <div id="my_buttons" style="margin: 10px 10px 0 10px">
                <div class="col-md-4">

                    <div class="btn-group" role="group" aria-label="...">
                        <button type="button" class="btn btn-default" name="scale" value="1">Day View</button>
                        <button type="button" class="btn btn-default" name="scale" value="2">Week View</button>
                        <button type="button" class="btn btn-default" name="scale" value="3">Month View</button>
                        <button type="button" class="btn btn-default" name="scale" value="4">Year View</button>
                    </div>

                </div>
                <div class="col-md-5">
                    <div class="btn-group" role="group" aria-label="...">
                        <button type="button" class="btn btn-default">Show All Tasks</button>
                        <button type="button" class="btn btn-default">Show Open TAsks</button>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Export To Excel <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#">All Tasks</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="#">Open Tasks</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-default" onclick='updateInfo("refresh")'>Refresh</button>
                    <button type="button" class="btn btn-default"  onclick='ev_set_toolbar_date(today_actual_date)'>Today</button>
                    <div class="btn-group" role="group" aria-label="...">
                        <button type="button" class="btn btn-default" onclick='updateInfo("previous")'>Prev</button>
                        <button type="button" class="btn btn-default" onclick='updateInfo("next")'>Next</button>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="gantt_wrapper panel" id="gantt_here"></div>
        </div>
    </div>

    <script type="text/javascript">

        var num_rows = <?= $amountEvents; ?>;
        var projectId = parent.projectsTree.getSelectedItemId();
        var start_date = parent.projectsPlanningGridToolbar.getValue("actual_date");
        var xhr = gantt.ajax;

        function doOnLoad() {
//            parent.projectPlanningListCell.progressOff();
        }

        function ev_set_toolbar_date(actual_date)
        {

            parent.projectPlanningListCell.progressOn();
            var projectId = parent.projectsTree.getSelectedItemId();
            parent.projectsPlanningGridToolbar.setValue("actual_date", actual_date);
            parent.projectPlanningGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=1&id=" + projectId + "&start_date=" + actual_date);
            location.href = 'gantt.php?id=' + projectId + "&start_date=" + actual_date;
//            gantt.clearAll();
//            gantt.load("Controller/php/projectsPlanning.php?action=9&id=" + projectId + "&start_date=" + actual_date, "xml");

        }

        var updateInfo = function (id) {
            switch (id) {
                case "previous":

                    var actual_date = parent.projectsPlanningGridToolbar.getValue("actual_date");
                    actual_date = new Date(actual_date);
                    actual_date = new Date(actual_date.getFullYear(), actual_date.getMonth(), actual_date.getDate() - 1).format('Y-m-d');
                    ev_set_toolbar_date(actual_date);
                    break;
                case "next":

                    var actual_date = parent.projectsPlanningGridToolbar.getValue("actual_date");
                    actual_date = new Date(actual_date);
                    actual_date = new Date(actual_date.getFullYear(), actual_date.getMonth(), actual_date.getDate() + 1).format('Y-m-d');
                    ev_set_toolbar_date(actual_date);
                    break;
                case "refresh":

                    var actual_date = parent.projectsPlanningGridToolbar.getValue("actual_date");
                    actual_date = new Date(actual_date).format('Y-m-d');
//                    actual_date = new Date(actual_date.getFullYear(), actual_date.getMonth(), actual_date.getDate() - 1).format('Y-m-d');
                    ev_set_toolbar_date(actual_date);
                    break;
            }
        };
        function setScaleConfig(value) {
            switch (value) {
                case "1":
                    gantt.config.scale_unit = "day";
                    gantt.config.step = 1;
                    gantt.config.date_scale = "%F %d";

                    gantt.config.scale_height = 54;

                    gantt.config.subscales = [
                        {unit: "hour", step: 1, date: "%H:%i"}
                    ];
                    gantt.templates.date_scale = null;
                    
                   break;
                case "2":
                    var weekScaleTemplate = function (date) {
                        var dateToStr = gantt.date.date_to_str("%d %M");
                        var endDate = gantt.date.add(gantt.date.add(date, 1, "week"), -1, "day");
                        return dateToStr(date) + " - " + dateToStr(endDate);
                    };

                    gantt.config.scale_unit = "week";
                    gantt.config.step = 1;
                    gantt.templates.date_scale = weekScaleTemplate;
                    gantt.config.subscales = [
                        {unit: "day", step: 1, date: "%D"}
                    ];
                    gantt.config.scale_height = 50;
                    break;
                case "3":
                    gantt.config.scale_unit = "month";
                    gantt.config.date_scale = "%F, %Y";
                    gantt.config.subscales = [
                        {unit: "day", step: 1, date: "%j"}
                    ];
                    gantt.config.scale_height = 50;
                    gantt.templates.date_scale = null;
                    break;
                case "4":
                    gantt.config.scale_unit = "year";
                    gantt.config.step = 1;
                    gantt.config.date_scale = "%Y";
                    gantt.config.min_column_width = 50;

                    gantt.config.scale_height = 90;
                    gantt.templates.date_scale = null;

                    var monthScaleTemplate = function (date) {
                        var dateToStr = gantt.date.date_to_str("%M");
                        var endDate = gantt.date.add(date, 2, "month");
                        return dateToStr(date) + " - " + dateToStr(endDate);
                    };

                    gantt.config.subscales = [
                        {unit: "month", step: 3, template: monthScaleTemplate},
                        {unit: "month", step: 1, date: "%M"}
                    ];
                    break;
                default:
                    break;

            }
        }
        var func = function (e) {
            e = e || window.event;
            var el = e.target || e.srcElement;
            var value = el.value;
            setScaleConfig(value);
            gantt.render();
        };

        var els = document.getElementsByName("scale");
        for (var i = 0; i < els.length; i++) {
            els[i].onclick = func;
        }

        gantt.config.date_grid = "%Y-%m-%d %H:%i";
        gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";
        setScaleConfig('1');

//        gantt.config.lightbox_additional_height =180;
//        gantt.config.min_column_width = 50;
        gantt.config.scale_height = 40;
        if (num_rows < 1) {
            gantt.config.start_date = new Date(start_date);
            gantt.config.end_date = new Date(start_date);
        }
        gantt.config.subscales = [
            {unit: "hour", step: 1, date: "%H:%i"}
        ];

        gantt.config.grid_width = 550;

        gantt.locale.labels["section_owner"] = "Owner";
        gantt.locale.labels["section_description"] = "Task title";
        gantt.locale.labels["section_details"] = "Task details";
//        gantt.locale.labels["complete_button"] = "Complete";
//        gantt.config.buttons_left = ["dhx_save_btn", "dhx_cancel_btn", "complete_button"];

        gantt.config.columns = [
            {name: "text", label: "Task name", tree: true, width: '*'},
            {name: "start_date", label: "Start", align: "center", width: 110},
//            {name: "end_date", label: "End", align: "center", width: 100},
            {name: "employee", label: "Owner", align: "center", width: 80},
            {name: "done", align: "center", label: "Done", template: function (task) {
                    if (task.done === '1') {
                        return "<input type='checkbox' checked='1' name='doneCheckbox' onclick='handleDoneClick(this);'>";
                    } else {
                        return "<input type='checkbox' name='doneCheckbox' onclick='handleDoneClick(this);'>";
                    }

                }, width: 60, resize: true},
            {name: "add", label: "", width: 44}
        ];

        gantt.templates.grid_row_class = function (start, end, task) {
            return "nested_task";
        };

        gantt.config.lightbox.sections = [
            {name: "description", height: 25, map_to: "text", type: "textarea", focus: true},
            {name: "details", height: 48, map_to: "details", type: "textarea"},
            {name: "owner", height: 30, map_to: "owner", type: "select", options: gantt.serverList("nameList")},
            {name: "time", type: "duration", map_to: "auto", time_format: ["%d", "%m", "%Y", "%H:%i"]}
        ];

        gantt.attachEvent("onLightboxSave", function (id, task, is_new) {

//alert(task.employee_id);
            var start_date = new Date(task.start_date).format('Y-m-d H:i:s');
            var end_date = new Date(task.end_date).format('Y-m-d H:i:s');
            var employee = task.owner;
            var details = task.text;
            var title = task.details;
            var documentId = parent.projectDocumentsGrid.getSelectedRowId().substring(4);
            if (documentId > 0) {
                parent.projectPlanningListCell.progressOn();
                xhr.post("Controller/php/projectsPlanning.php?action=12", "start_date=" + start_date + "&end_date=" + end_date + "&employee=" + employee + "&details=" + details + "&title=" + title + "&id=" + projectId + "&doc_id=" + documentId + "&is_new=" + ((is_new) ? 1 : 0) + "&event_id=" + id + "&eid=" + uID, function (r) {
                    var t = JSON.parse(r.xmlDoc.responseText);
                    if (t && t.data.response) {
                        parent.projectPlanningListCell.progressOff();
                        parent.dhtmlx.message({title: 'Success', text: t.data.text});
                        gantt.clearAll();
                        gantt.load("Controller/php/projectsPlanning.php?action=9&id=" + projectId + "&start_date=" + new Date(task.start_date).format('Y-m-d'), "xml");
                        parent.projectPlanningGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=1&id=" + projectId + "&start_date=" + new Date(task.start_date).format('Y-m-d'));
                    } else {
                        gantt.alert({title: 'Error', text: t.data.text});
                    }
                });
            } else {
                gantt.alert({type: "Warning", text: "Please select a Document!"});
            }

            return true;
        });

        gantt.attachEvent("onLightboxDelete", function (id) {
            gantt.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you  want to delete?",
                callback: function (y) {
                    if (y)
                    {
                        xhr.get("Controller/php/projectsPlanning.php?action=4&id=" + id + "&project_id=" + projectId, function (r) {
                            var t = JSON.parse(r.xmlDoc.responseText); // convert response to json object
                            if (t && t.data.response) {
                                parent.dhtmlx.message({title: 'Success', text: t.data.text});
                                gantt.hideLightbox();
                                gantt.deleteTask(id);
                                parent.projectPlanningGrid.deleteRow(id);
                                parent.eventDetailsForm.clear();
                            } else {
                                gantt.hideLightbox();
                                parent.dhtmlx.alert({title: 'Error', text: t.data.text});
                            }
                        });
                    }
                    else
                    {
                        return false;
                    }
                }
            });
        });
        gantt.attachEvent("onTaskClick", function (id, e) {
            gantt.selectTask(id);
            parent.projectPlanningGrid.selectRowById(id);
            parent.doOnProjectPlanningGridRowSelect(id);
        });
        gantt.init("gantt_here");
        gantt.load("Controller/php/projectsPlanning.php?action=9&id=" + projectId + "&start_date=" + start_date, "xml", function () {
            parent.projectPlanningListCell.progressOff();
        });

    </script>
</body>