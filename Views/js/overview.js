projectDetailsTabbar.addTab('overview', 'Overview');
var overview = projectDetailsTabbar.cells('overview');
var overviewLayout = overview.attachLayout('2E');

var overviewGridCell = overviewLayout.cells('a');
overviewGridCell.hideHeader();
var overviewGrid = overviewGridCell.attachGrid();
overviewGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
overviewGrid.setSkin('dhx_web');
overviewGrid.setHeader(["ID", "Date", "Done by", "TOC ID", "Location", "Proc. by", "Proc. name", "Proc. ID", "Impl. Doc ID", "Ch.", "BOM", "Bought", "Delivered", "Completed", "Verified", "Duration"], null, ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:center;", "text-align:left;"]);
overviewGrid.attachHeader('#numeric_filter,#text_filter,#combo_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter', ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:center;", "text-align:left;"]);
overviewGrid.setColTypes("ro,dhxCalendar,combo,ed,ed,coro,ed,ed,ed,ed,dhxCalendarA,dhxCalendarA,dhxCalendarA,dhxCalendarA,ch,ed");

overviewGrid.setColSorting('str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str');
overviewGrid.enableCellIds(true);
overviewGrid.setColumnIds('id,date_added,employee_id,toc_id,location,procedure_by,procedure_name,procedure_id,doc_id,chapter,bom,bought,delivered,completed,verified,duration');
overviewGrid.setColAlign('left,left,left,left,left,left,left,left,left,left,left,left,left,left,center,left');
overviewGrid.setInitWidthsP('3,*,*,4,*,*,*,5,5,4,*,*,*,*,4,*');
overviewGrid.setDateFormat('%Y-%m-%d');
overviewGrid.attachEvent("onEditCell", overviewGridEditCell);
overviewGrid.attachEvent("onCheck", overviewGridChecked);
overviewGrid.attachEvent("onRowSelect", overviewGridRowSelect);
overviewGrid.init();
overviewGridCell.progressOn();
var overviewGridEmployeeCombo = overviewGrid.getColumnCombo(2);
overviewGridEmployeeCombo.load("Controller/php/projectDocuments.php?action=1", function () {
    overviewGrid.load("Controller/php/data_overview.php?action=3", doAfteroverviewGridRefresh());
});

function overviewGridRowSelect(id, ind) {
    overviewFormCell.progressOn();
    overviewForm.clear();
    overviewForm.load('Controller/php/data_overview.php?action=7&id=' + id, function () {
        overviewFormCell.progressOff();
    });

    var doc_id = overviewGrid.cells(id, 8).getValue();
    if (doc_id > 0) {
        overviewPlanningGrid.clearAndLoad('Controller/php/data_overview.php?action=10&id=' + doc_id);
    }else{
        overviewPlanningGrid.clearAll();
    }
    
}

function overviewGridEditCell(stage, id, index, new_value, old_value, cellIndex) {

    var cell = overviewGrid.cells(id, index);
    if (stage == 2 && !cell.isCheckbox()) {

        var row_id = overviewGrid.getSelectedRowId();
        if (row_id > 0 || typeof row_id != 'undefined') {
            var colId = overviewGrid.getColumnId(index);
            var colType = overviewGrid.fldSort[index];

            window.dhx4.ajax.get("Controller/php/data_overview.php?action=5&id=" + id + "&index=" + index + "&fieldvalue=" + new_value + "&colId=" + colId + "&colType=" + colType, function (r) {
                var t = null;
                try {
                    eval("t=" + r.xmlDoc.responseText);
                } catch (e) {
                }
                ;
                if (t !== null && t.data.response) {
                    dhtmlx.message({type: "Success", text: t.data.text});
                    overviewGrid.updateFromXML("Controller/php/data_overview.php?action=3", true, true);
                    overviewGrid.load("Controller/php/data_overview.php?action=3");
                } else
                    dhtmlx.alert({title: 'Error', text: t.data.text});
            });
        }

    } else
    if (stage == 0 && cell.isCheckbox()) {
        return true;
    }

}

function overviewGridChecked(id, index, state) {
    var colId = overviewGrid.getColumnId(index);

    $.post("Controller/php/data_overview.php?action=6", {colId: colId, id: id, nValue: ((state) ? 1 : 0)}, function (data)
    {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
            overviewGrid.updateFromXML("Controller/php/data_overview.php?action=3", true, true);
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
}



var overviewFormCell = overviewLayout.cells('b');
overviewFormCell.setText('Details');

var overviewRecordDetailsTabbar = overviewFormCell.attachTabbar();
overviewRecordDetailsTabbar.addTab('overviewFormTab', 'Details');

var overviewFormTab = overviewRecordDetailsTabbar.cells('overviewFormTab');
overviewFormTab.setActive();

var overviewFormToolbar = overviewFormTab.attachToolbar();
overviewFormToolbar.setIconsPath('Views/imgs/');

overviewFormToolbar.loadStruct('<toolbar><item type="button" id="save" text="Save" img="save.gif"/></toolbar>', function () {});

overviewFormToolbar.attachEvent('onClick', function (id) {
    if (id === 'save') { //save button
        var row_id = overviewGrid.getSelectedRowId();

        if (row_id === null) {
            dhtmlx.alert({
                type: "alert-error",
                text: "No Record Selected.",
                title: "Error!"
            });
            return;
        }
        overviewGridCell.progressOn();
        overviewForm.send("Controller/php/data_overview.php?action=8&id=" + row_id, function (loader, response) {

            var parsedJSON = eval('(' + response + ')');
            if (parsedJSON.data.response) {
                overviewForm.load('Controller/php/data_overview.php?action=7&id=' + row_id);
                dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                overviewGrid.updateFromXML("Controller/php/data_overview.php?action=3", true, true, function () {
                    overviewGridCell.progressOff();
                });
            } else {
                dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                overviewGridCell.progressOff();
            }
        });
    }
});

var overviewFormdata =
        [{type: "settings", labelWidth: 110, inputWidth: 220, offsetLeft: "20", offsetTop: "8"},
            {type: "hidden", label: "ID", name: "id"},
            {type: "calendar", label: "Date", name: "date_added", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", calendarPosition: "bottom"},
            {type: "combo", label: "Location", name: "location", options: [
                    {value: "Bendor", text: "Bendor"},
                    {value: "Longonot", text: "Longonot"},
                    {value: "Tradestar Office", text: "Tradestar Office"}
                ]},
            {type: "combo", label: "Done by", name: "employee_id"},
            {type: "input", label: "TOC ID", name: "toc_id"},
            {type: "input", label: "Procedure ID", name: "procedure_id"},
            {type: "input", label: "Procedure Name", name: "procedure_name"},
            {type: "input", label: "Procedure By", name: "procedure_by"},
            {type: "calendar", label: "Procedure Date", name: "procedure_date", value: "", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", calendarPosition: "bottom"},
            {type: "input", label: "Impl. Doc ID", name: "doc_id"},
            {type: "input", label: "Chapter", name: "chapter"},
            {type: "newcolumn", offset: 10},
            {type: "input", label: "Observation", rows: "4", name: "observation", inputWidth: 280, value: ""},
            {type: "input", label: "Solution", rows: "4", name: "solution", inputWidth: 280, value: ""},
            {type: "input", label: "Problems", rows: "4", name: "problems", inputWidth: 280, value: ""},

            {type: "newcolumn", offset: 10},
            {type: "calendar", label: "BOM", name: "bom", value: "", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", calendarPosition: "bottom"},
            {type: "calendar", label: "Bought", name: "bought", value: "", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", calendarPosition: "bottom"},
            {type: "calendar", label: "Delivered", name: "delivered", value: "", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", calendarPosition: "bottom"},
            {type: "calendar", label: "Completed", name: "completed", value: "", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", calendarPosition: "bottom"},
            {type: "checkbox", label: "Verified", name: "verified"},
            {type: "input", label: "Duration", name: "duration"},
        ];
var overviewForm = overviewFormTab.attachForm(overviewFormdata);

var overviewFormEmployeeCombo = overviewForm.getCombo("employee_id");
overviewFormEmployeeCombo.load("Controller/php/projectDocuments.php?action=1");

overviewForm.getInput("date_added").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
overviewForm.getInput("date_added").style.backgroundPosition = "center right";
overviewForm.getInput("date_added").style.backgroundRepeat = "no-repeat";

overviewForm.getInput("procedure_date").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
overviewForm.getInput("procedure_date").style.backgroundPosition = "center right";
overviewForm.getInput("procedure_date").style.backgroundRepeat = "no-repeat";

overviewForm.getInput("bom").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
overviewForm.getInput("bom").style.backgroundPosition = "center right";
overviewForm.getInput("bom").style.backgroundRepeat = "no-repeat";

overviewForm.getInput("delivered").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
overviewForm.getInput("delivered").style.backgroundPosition = "center right";
overviewForm.getInput("delivered").style.backgroundRepeat = "no-repeat";

overviewForm.getInput("completed").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
overviewForm.getInput("completed").style.backgroundPosition = "center right";
overviewForm.getInput("completed").style.backgroundRepeat = "no-repeat";

overviewForm.getInput("bought").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
overviewForm.getInput("bought").style.backgroundPosition = "center right";
overviewForm.getInput("bought").style.backgroundRepeat = "no-repeat";

var overviewMainToolbar = overviewGridCell.attachToolbar();
overviewMainToolbar.setIconsPath('Views/imgs/');

overviewMainToolbar.loadStruct('<toolbar><item type="button" id="new" text="Add New" img="new.gif" imgdis="new.gif" /><item type="separator" id="button_separator_0" /><item type="button" id="delete" text="Delete" img="deleteall.png" imgdis="deleteall.png" /><item type="separator" id="button_separator_04" /><item type="button" id="show" text="Show All Open" img="" /><item type="separator" id="button_separator_1" /><item type="buttonSelect" id="employee" text="Select Employee"   openAll="true" renderSelect="true" mode="select"/><item type="separator" id="button_separator_3" /><item type="button" id="date" text="Date" /><item type="buttonInput" id="button_input_1" /><item type="button" id="show_all" text="Show All" /><item type="separator" id="button_separator_4" /><item type="button" id="problems" text="Problems" /><item type="separator" id="button_separator_2" /><item type="button" id="goto" text="Go to" /></toolbar>', function () {
    $.getJSON('Controller/php/data_overview.php?action=1', function (results) {
        var pos = 0;

        $.each(results.options, function (key, value) {
            overviewMainToolbar.addListOption('employee', value.id, pos++, 'button', value.text);
        });


    });
    overviewMainToolbar.getInput("button_input_1").style.backgroundImage = "url(https://" + location.host + "/dhtmlx4.5/samples/dhtmlxCalendar/common/calendar.gif)";
    overviewMainToolbar.getInput("button_input_1").style.backgroundPosition = "center right";
    overviewMainToolbar.getInput("button_input_1").style.backgroundRepeat = "no-repeat";
    overviewMainToolbar.setWidth("button_input_1", 120);
    overviewCalendar = new dhtmlxCalendarObject(overviewMainToolbar.getInput("button_input_1"));
    overviewCalendar.hideTime();
//    myCalendar.setSkin("material");
    overviewCalendar.setDateFormat("%d-%m-%Y");
    overviewMainToolbar.setValue("button_input_1", overviewCalendar.getFormatedDate());

    overviewCalendar.attachEvent("onClick", function (date) {
        overview_date = new Date(overviewCalendar.getDate()).format('Y-m-d');
        $.get("Controller/php/data_overview.php?action=3&date=" + overview_date, function (data) {

            overviewGridCell.progressOn();
            overviewGrid.clearAndLoad("Controller/php/data_overview.php?action=3&date=" + overview_date, function () {
                doAfteroverviewGridRefresh();
            });

        });
    });
});

overviewMainToolbar.attachEvent("onClick", doOnoverviewMainToolbarClicked);

function doOnoverviewMainToolbarClicked(id) {
    switch (id)
    {
        case "new":

            $.get("Controller/php/data_overview.php?action=2", function (data) {
                if (data.data.response) {

                    dhtmlx.message({title: 'Success', text: data.data.text});
                    overviewGrid.clearAndLoad("Controller/php/data_overview.php?action=3", function () {
                        overviewGrid.selectRowById(data.data.row_id);
                    });
                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');

            break;
        case "delete":
            //delete selected items
            var row_id = overviewGrid.getSelectedRowId();
            if (row_id === null) {
                dhtmlx.alert("No item selected!");
            } else {
                dhtmlx.confirm({
                    title: "Confirm",
                    type: "confirm-warning",
                    text: "Are you sure you  want to delete?",
                    callback: function (y) {
                        if (y)
                        {
                            $.get("Controller/php/data_overview.php?action=29&id=" + row_id, function (data) {
                                if (data.data.response) {

                                    dhtmlx.message({title: 'Success', text: data.data.text});
                                    overviewGrid.deleteSelectedRows();
                                } else {
                                    dhtmlx.alert({title: 'Error', text: data.data.text});
                                }
                            }, 'json');
                        } else
                        {
                            return false;
                        }
                    }
                });
            }
            break;

        case 'show':
            var text = overviewMainToolbar.getItemText(id);
            if (text === 'Show All Open') {
                overviewGridCell.progressOn();
                overviewGrid.clearAndLoad("Controller/php/data_overview.php?action=3&open=1", function () {
                    doAfteroverviewGridRefresh();
                });
                overviewMainToolbar.setItemText(id, "Show All");
            } else {
                overviewGridCell.progressOn();
                overviewGrid.clearAndLoad("Controller/php/data_overview.php?action=3", function () {
                    doAfteroverviewGridRefresh();
                });
                overviewMainToolbar.setItemText(id, "Show All Open");
            }

            break;

        case 'show_all':

            overviewGridCell.progressOn();
            overviewGrid.clearAndLoad("Controller/php/data_overview.php?action=3", function () {
                doAfteroverviewGridRefresh();
            });
            break;

        case 'problems':
            overviewGridCell.progressOn();
            overviewGrid.clearAndLoad("Controller/php/data_overview.php?action=3&problems=1", function () {
                doAfteroverviewGridRefresh();
            });
            break;

        case 'goto':

            var row_id = overviewGrid.getSelectedRowId();
            if (row_id === null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Record Selected.",
                    title: "Error!"
                });
                return;
            }
            var doc_id = overviewGrid.cells(row_id, 8).getValue();
            if (doc_id === null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "Record Has no Document ID.",
                    title: "Error!"
                });
                return;
            }
            $.post("Controller/php/data_overview.php?action=9", {search_doc_input: doc_id}, function (data)
            {
                selected_doc_id = doc_id;
                projectsTree.selectItem(data.data.item_id, false, true);
                projectDetailsTabbar.tabs("project_documents").setActive();
            }, 'json');

            break;


        default:
            overviewGridCell.progressOn();
            overviewGrid.clearAndLoad("Controller/php/data_overview.php?action=3&employee_id=" + id, function () {
                doAfteroverviewGridRefresh();
            });
            break;
    }
}


function doAfteroverviewGridRefresh() {
    overviewGridCell.progressOff();
}

overviewRecordDetailsTabbar.addTab('overviewPlanningTab', 'Planning');
var overviewPlanningTab = overviewRecordDetailsTabbar.cells('overviewPlanningTab');
var overviewPlanningGrid = overviewPlanningTab.attachGrid();
overviewPlanningGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
overviewPlanningGrid.setSkin('dhx_web');
overviewPlanningGrid.setHeader(["ID", "Event Name", "Assigned To", "Begin Date", "End Date", "Details", "Visible", "Main Task", "Done", "Comment"],
        null,
        ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:center;", "text-align:center;", "text-align:center;", "text-align:left;"]);
overviewPlanningGrid.setColumnIds("event_id,details,employee_id,start_date,end_date,event_name,visible,main_task,completed,comment");
overviewPlanningGrid.attachHeader('#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,,,,');
overviewPlanningGrid.setColTypes("ro,ro,combo,dhxCalendar,dhxCalendar,ed,ch,ch,ch,ed");
overviewPlanningGrid.setDateFormat("%Y-%m-%d %H:%i");
overviewPlanningGrid.setColAlign('left,left,left,left,left,left,center,center,center,left');
overviewPlanningGrid.setColSorting('str,str,str,date,date,str,int,int,int,str');
overviewPlanningGrid.enableCellIds(true);
overviewPlanningGrid.enableMultiline(true);
overviewPlanningGrid.setInitWidthsP('0,16,9,10,10,*,4,5,4,19');
overviewPlanningGrid.attachEvent("onEditCell", overviewPlanningGridEdit);
overviewPlanningGrid.attachEvent("onCheck", overviewPlanningGridChecked);
overviewPlanningGrid.init();

var overviewPlanningGridEmployeeCombo = overviewPlanningGrid.getColumnCombo(2);
overviewPlanningGridEmployeeCombo.load("Controller/php/projectDocuments.php?action=1");

function overviewPlanningGridEdit(stage, id, index, new_value, old_value, cellIndex) {

    var cell = overviewPlanningGrid.cells(id, index);
    if (stage == 2 && !cell.isCheckbox()) {
        if (id > 0 || typeof id != 'undefined') {
            var colId = overviewPlanningGrid.getColumnId(index);
            var colType = overviewPlanningGrid.fldSort[index];
            $.get("Controller/php/projectsPlanning.php?action=11&id=" + id + "&index=" + index + "&fieldvalue=" + new_value + "&colId=" + colId + "&colType=" + colType, function (data) {
                if (data.data.response) {
                    dhtmlx.message({type: "Success", text: data.data.text});
                } else
                    dhtmlx.alert({title: 'Error', text: data.data.text});
            }, 'json');
        }
    } else
    if (stage === 0 && cell.isCheckbox()) {
        return true;
    }
}

function overviewPlanningGridChecked(id, index, state) {
    var colId = overviewPlanningGrid.getColumnId(index);

    $.post("Controller/php/projectsPlanning.php?action=10", {colId: colId, id: id, nValue: ((state) ? 1 : 0)}, function (data)
    {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
}

