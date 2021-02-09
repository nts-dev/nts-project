var assetPlanningTabbar = asset_planning.attachTabbar();

assetPlanningTabbar.addTab("asset_events", "Event Details");
var asset_events = assetPlanningTabbar.cells('asset_events');
asset_events.setActive();

var asset_events_layout = asset_events.attachLayout("2U");
asset_events_layout.cells("a").setWidth(projectDetailsCell.getWidth() * 0.64);
asset_events_layout.cells("a").hideHeader();


//planning toolbar
var assetEventsToolbar = asset_events_layout.cells('a').attachToolbar();
assetEventsToolbar.setIconsPath("Views/imgs/");
assetEventsToolbar.addButton('new', 1, 'Add Event', 'new.gif', 'new.gif');
assetEventsToolbar.addSeparator('sep1', 2);
assetEventsToolbar.addButton('delete', 3, 'Delete Event', 'deleteall.png', 'deleteall.png');
assetEventsToolbar.addSeparator('sep2', 4);

assetEventsToolbar.attachEvent("onClick", function (id) {
    assetEventsToolbarClicked(id);
});

function assetEventsToolbarClicked(id) {
    switch (id) {
        case 'new':

            var device_id = devicesDataGrid.getSelectedRowId();
            if (device_id === null) {
                dhtmlx.alert("Please select Asset!");
            } else {
                $.get("https://bo.nts.nl/network/Controller/php/data_planning.php?action=1&id=" + device_id + "&eid=" + uID, function (data) {
                    if (data.data.success)
                    {
                        dhtmlx.message({type: "Success", text: data.data.text});
                        assetEventsGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/data_planning.php?action=default&id=" + device_id, true, true, function ()
                        {
                            assetEventsGrid.selectRowById(data.data.newId);
                        });
                    } else {
                        dhtmlx.alert({type: "Error", text: data.data.text});
                    }
                }, 'json');
            }
            break;

        case 'delete':

            var row_id = assetEventsGrid.getSelectedRowId();
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
                            $.get("https://bo.nts.nl/network/Controller/php/data_planning.php?action=2&id=" + row_id, function (data) {
                                if (data.response) {
                                    assetEventsGrid.deleteRow(row_id);
                                    assetEventForm.clear();
                                    dhtmlx.message({type: "Success", text: data.text});
                                } else {
                                    dhtmlx.alert({type: "Error", text: data.text});
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
    }

}

//planning grid
var assetEventsGrid = asset_events_layout.cells('a').attachGrid();
assetEventsGrid.setImagesPath('https://' + location.host + '/dhtmlxsuite4/skins/web/imgs/');
assetEventsGrid.setSkin('dhx_web');
assetEventsGrid.setHeader(",Event Name,Assigned To,Start Date,End Date,Details,Visible,Done");
assetEventsGrid.setColumnIds("event_id,details,employee_id,start_date,end_date,event_name,visible,completed");
assetEventsGrid.attachHeader('#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter');
assetEventsGrid.setInitWidthsP("0,20,15,15,15,*,7,7");
assetEventsGrid.setColTypes('ro,ro,ro,dhxCalendar,dhxCalendar,txt,ch,ch');
assetEventsGrid.setColAlign('left,left,left,left,left,left,center,center');
assetEventsGrid.setSkin('dhx_web');
assetEventsGrid.setDateFormat("%Y-%m-%d %H:%i");
assetEventsGrid.attachEvent("onSelectStateChanged", assetPlanningGridRowSelect);//onRowSelect
assetEventsGrid.attachEvent("onEditCell", assetPlanningGridEdit);
assetEventsGrid.attachEvent("onXLE", assetPlanningGridXLE);
assetEventsGrid.attachEvent("onCheck", assetPlanningGridChecked);
assetEventsGrid.init();

var assetEventsGridEmpCombo = assetEventsGrid.getColumnCombo(2);//takes the column index
assetEventsGridEmpCombo.enableFilteringMode(true);
assetEventsGridEmpCombo.load("https://bo.nts.nl/network/Controller/php/data_planning.php?action=4");


function assetPlanningGridXLE(grid, count) {
    var i = assetEventsGrid.getSelectedRowId();
    if ((i === -1) || (i === null)) {
        i = 0;
    }
    assetEventsGrid.setSelectedRow(i);
    assetEventsGrid.selectRow(i);
}

function assetPlanningGridRowSelect(id, ind) {

    //get the event selected 
    var event_id = assetEventsGrid.cells(id, 0).getValue();//event_id
    //load form details
    assetEventForm.load('Controller/php/projectsPlanning.php?action=35&id=' + event_id, function (id, response) {
        var rec_type = assetEventForm.getItemValue('rec_type');
        for (var i = 0; i < 8; i++)
        {
            assetEventForm.uncheckItem('days_select[' + i + ']');
        }
        var s = '' + rec_type + '';
        s = rec_type.split(",");
        for (var k = 0; k < s.length; k++) {
            var d = s[k];
            assetEventForm.checkItem('days_select[' + d + ']');
        }
        if (assetEventForm.isItemChecked('variable') == true)
        {
            disableCheckBox();
        } else {
            assetEventForm.setItemLabel("label_days", "Select Days");
        }
        //load the combo checked values
        assetEventFormEmplCombo.clearAll();
        assetEventFormEmplCombo.load(path + "/Schedule/Controller/recurring.php?action=110&load=1&evt_id=" + event_id);
//        assetEventFormEmplCombo.clearAll();
//        assetEventFormEmplCombo.load("Controller/php/projectsPlanning.php?action=29&evt_id=" + event_id);
    });
    assetEventReoccurencesGrid.clearAndLoad(path + "/Schedule/Controller/generated_tasks.php?id=" + event_id, function () {});

}


function assetPlanningGridEdit(stage, id, index, new_value, old_value, cellIndex) {
    var device_id = devicesDataGrid.getSelectedRowId();
    var event_id = assetEventsGrid.cells(id, 0).getValue();
    var cell = assetEventsGrid.cells(id, index);
    if (stage === 2 && !cell.isCheckbox()) {
        var selected_id = assetEventsGrid.getSelectedRowId();
        if (selected_id > 0 || typeof device_id != 'undefined') {
            var colId = assetEventsGrid.getColumnId(index);
            var colType = assetEventsGrid.fldSort[index];
            $.get("https://bo.nts.nl/network/Controller/php/data_planning.php?action=7&id=" + event_id + "&index=" + index + "&fieldvalue=" + new_value + "&colId=" + colId + "&colType=" + colType, function (data) {
                if (data.data.response) {
                    dhtmlx.message({type: "Success", text: data.data.text});
                    assetEventsGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/data_planning.php?action=default&id=" + device_id);
                    assetEventForm.clear();
                    assetEventForm.load('Controller/php/projectsPlanning.php?action=35&id=' + event_id, function (id, response) {
                        var rec_type = assetEventForm.getItemValue('rec_type');
                        for (var i = 0; i < 8; i++)
                        {
                            assetEventForm.uncheckItem('days_select[' + i + ']');
                        }
                        var s = '' + rec_type + '';
                        s = rec_type.split(",");
                        for (var k = 0; k < s.length; k++) {
                            var d = s[k];
                            assetEventForm.checkItem('days_select[' + d + ']');
                        }
                        if (assetEventForm.isItemChecked('variable') == true)
                        {
                            disableCheckBox();
                        } else {
                            assetEventForm.setItemLabel("label_days", "Select Days");
                        }
                        //load the combo checked values
                        assetEventFormEmplCombo.clearAll();
                        assetEventFormEmplCombo.load(path + "/Schedule/Controller/recurring.php?action=110&load=1&evt_id=" + event_id);
//        assetEventFormEmplCombo.clearAll();
//        assetEventFormEmplCombo.load("Controller/php/projectsPlanning.php?action=29&evt_id=" + event_id);
                    });
                } else
                    dhtmlx.alert({title: 'Error', text: data.data.text});
            }, 'json');
        }
    } else
    if (stage === 0 && cell.isCheckbox()) {
        return true;
    }
}

function assetPlanningGridChecked(id, index, state) {
    var colId = assetEventsGrid.getColumnId(index);
    var event_id = assetEventsGrid.cells(id, 0).getValue();
    $.post("https://bo.nts.nl/network/Controller/php/data_planning.php?action=6", {colId: colId, id: event_id, nValue: ((state) ? 1 : 0)}, function (data)
    {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
}


//css toolbar
var assetEventFormToolbar = asset_events_layout.cells('b').attachToolbar();
assetEventFormToolbar.setIconsPath("Views/imgs/");
assetEventFormToolbar.addButton('save', 1, 'Save', 'save.gif', 'save.gif');
assetEventFormToolbar.addSeparator('sep1', 2);
assetEventFormToolbar.attachEvent("onClick", assetEventFormToolbarClicked);


function assetEventFormToolbarClicked(id) {
    switch (id)
    {
        case 'save':
            var rowId = assetEventsGrid.getSelectedRowId();
            var eventId = assetEventsGrid.cells(rowId, 0).getValue();
            if (eventId > 0) {

                var device_id = devicesDataGrid.getSelectedRowId();
                var emp_assigned = assetEventForm.getCombo("emp").getChecked();
                var approved_by = assetEventForm.getCombo("approved_by").getChecked();

                //master_form_details.setItemValue("emp", emp_assigned);
                assetEventForm.setItemValue("approved_by", approved_by);

                if (emp_assigned.length < 1) {
                    dhtmlx.alert("Select employee from dropdown!")
                } else {
                    asset_events_layout.cells('a').progressOn();
                    assetEventForm.send("Controller/php/projectsPlanning.php?action=32&approved=" + approved_by + "&eid=" + uID, function (loader, response)
                    {
                        var parsedJSON = eval('(' + response + ')');
                        if (parsedJSON.data.response) {
                            dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                            assetEventsGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/data_planning.php?action=default&id=" + device_id, true, true, function () {
                                //load the combo checked values
                                assetEventFormEmplCombo.clearAll();
                                assetEventFormEmplCombo.load(path + "/Schedule/Controller/recurring.php?action=110&load=1&evt_id=" + event_id);
                                asset_events_layout.cells('a').progressOff();
                            });
                        } else {
                            dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                        }
                    });
                }

            } else {
                dhtmlx.alert("No item selected!");
            }

            break;
    }
}

asset_events_layout.cells("b").hideHeader();

assetEventDetailsFormdata = [
    {type: "settings", position: "label-left", labelWidth: projectPlanningDetailsCell.getWidth() * 0.07, inputWidth: projectPlanningDetailsCell.getWidth() * 0.25, offsetTop: 8, offsetLeft: 20},
//    {type: "fieldset", label: "Event Details", className: "formbox", width: taskDetailsCell.getWidth() * 0.9, offsetLeft: 10, list:
//                [
    {type: "hidden", label: "ID", name: "event_id", value: ""},
    {type: "input", label: "Event Name", name: "event_name", value: ""},
    {type: "input", label: "Details", rows: 5, name: "asset_event_details", value: ""},
    {type: "combo", comboType: "checkbox", label: "Assigned To", name: "emp", value: ""},
    {type: "label", offsetTop: 0, list: [
            {type: "calendar", position: "label-left", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", enableTime: false, label: "Start Date", inputWidth: 150, name: "start_date", value: "", readonly: false, offsetLeft: 0},
            {type: "newcolumn", offsetLeft: 20},
            {type: "input", label: "Begin Time", position: "label-left", name: "begn", value: "", inputWidth: 55, offsetLeft: 5},
        ]},
    {type: "label", offsetTop: 0, list: [
            {type: "calendar", position: "label-left", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", enableTime: false, label: "End Date", inputWidth: 150, name: "end_date", value: "", readonly: false, offsetLeft: 0}, //%H:%i
            {type: "newcolumn", offsetLeft: 20},
            {type: "input", label: "End Time", position: "label-left", name: "end", value: "", inputWidth: 55, offsetLeft: 5},
        ]},
    {type: "combo", name: "period", offsetLeft: 20, inputWidth: 150, label: "Period", options: [
            {value: "0", text: "_"},
            {value: "5", text: "5m", selected: false},
            {value: "10", text: "10m"},
            {value: "15", text: "15m"},
            {value: "30", text: "30m"},
            {value: "45", text: "45m"},
            {value: "60", text: "1hr"},
            {value: "120", text: "2hr"},
            {value: "180", text: "3hr"},
            {value: "240", text: "4hr"},
            {value: "480", text: "8hr"}
        ]},
    {type: "combo", name: "freq", label: "Frequency", offsetLeft: 20, inputWidth: 150, options: [
            {value: "1", text: "Every Week", selected: true},
            {value: "2", text: "Every (2) Weeks"},
            {value: "10", text: "Every (3) Weeks"},
            {value: "7", text: "Every (4) Weeks"},
            {value: "9", text: "Every (8) Weeks"},
            {value: "3", text: "Every Month"},
            {value: "8", text: "Every (2) Month"},
            {value: "4", text: "Every (12) Weeks"},
            {value: "5", text: "Every half year"},
            {value: "6", text: "Every year"}
        ]},
    {type: "newcolumn", offset: 30},
    {type: "label", name: "label_days", label: "Select Days",
        list: [
            {type: "checkbox", name: "days_select[1]", labelWidth: 25, offsetLeft: 0, label: "Mon"},
            {type: "newcolumn"},
            {type: "checkbox", name: "days_select[2]", labelWidth: 25, label: "Tue"},
            {type: "newcolumn"},
            {type: "checkbox", name: "days_select[3]", labelWidth: 25, label: "Wed"},
            {type: "newcolumn"},
            {type: "checkbox", name: "days_select[4]", labelWidth: 25, label: "Thur"},
            {type: "newcolumn"},
            {type: "checkbox", name: "days_select[5]", labelWidth: 25, label: "Fri"},
            {type: "newcolumn"},
            {type: "checkbox", name: "days_select[6]", labelWidth: 25, label: "Sat"},
            {type: "newcolumn"},
            {type: "checkbox", name: "days_select[7]", labelWidth: 25, label: "Sun"},
        ],
    },
    {type: "checkbox", name: "variable", position: "label-left", labelWidth: 100, value: "1", label: "Enable Variable", checked: false, offsetLeft: 20},
    {type: "hidden", label: "Rec_Type", position: "label-left", name: "rec_type", value: "", inputWidth: 48, offsetLeft: 5},
    {type: "hidden", label: "Cat_id", position: "label-left", name: "cat_id", value: "", inputWidth: 48, offsetLeft: 5},
    {type: "input", label: "Information", rows: 5, name: "asset_event_info", value: ""},
    {type: "combo", comboType: "checkbox", label: "Approved by", name: "approved_by", value: ""},
    {type: "checkbox", name: "map", value: "0", label: "Show map", checked: true},
    {type: "checkbox", name: "masterrecord", value: "0", label: "Show masterrecord", checked: false},
    {type: "checkbox", name: "reoccur_map", value: "0", label: "Reoccur map", checked: false}
//                ]
//    }
];


assetEventForm = asset_events_layout.cells('b').attachForm(assetEventDetailsFormdata);
assetEventForm.getInput("start_date").style.backgroundImage = "url(https://" + location.host + "/dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
assetEventForm.getInput("start_date").style.backgroundPosition = "center right";
assetEventForm.getInput("start_date").style.backgroundRepeat = "no-repeat";
assetEventForm.getInput("end_date").style.backgroundImage = "url(https://" + location.host + "/dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
assetEventForm.getInput("end_date").style.backgroundPosition = "center right";
assetEventForm.getInput("end_date").style.backgroundRepeat = "no-repeat";

var assetEventFormEmplCombo = assetEventForm.getCombo("emp");
assetEventFormEmplCombo.enableFilteringMode(true);
assetEventFormEmplCombo.load("Controller/php/projectsPlanning.php?action=2");

assetEventFormEmplCombo.attachEvent("onCheck", function (value, state) {
    var rowId = assetEventsGrid.getSelectedRowId();
    var eventId = assetEventsGrid.cells(rowId, 0).getValue();
    var employeeId = value;
    $.post("Controller/php/projectsPlanning.php?action=28", {eventId: eventId, employeeId: employeeId, nValue: ((state) ? 1 : 0), eid: uID}, function (data)
    {
        if (data.data.response) {
            var device_id = devicesDataGrid.getSelectedRowId();
            assetEventsGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/data_planning.php?action=default&id=" + device_id, true, true, function () {
//                asset_events_layout.cells('a').progressOff();
            });
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
    return true;
});

var asset_approved_Combo = assetEventForm.getCombo("approved_by");
asset_approved_Combo.load(path + "/Schedule/Controller/recurring.php?action=110");

/************************************** reoccurences *******************************************/

assetPlanningTabbar.addTab("asset_event_reoccurence", "Re-Occurences");
var asset_event_reoccurence = assetPlanningTabbar.cells('asset_event_reoccurence');

var assetEventReoccurencesLayout = asset_event_reoccurence.attachLayout('2U');
var assetEventReoccurencesListCell = assetEventReoccurencesLayout.cells('a');
assetEventReoccurencesListCell.hideHeader();

var assetEventReoccurencesGridToolbar = assetEventReoccurencesListCell.attachToolbar();
assetEventReoccurencesGridToolbar.setIconsPath("Views/imgs/");
assetEventReoccurencesGridToolbar.addButton("new_rec", 1, "New", "new.gif", "new.gif");
assetEventReoccurencesGridToolbar.addSeparator("sep1", 2);
assetEventReoccurencesGridToolbar.addButton("delete_row", 3, "Delete Row", "deleteall.png", "deleteall.png");
assetEventReoccurencesGridToolbar.addSeparator("sep2", 4);
assetEventReoccurencesGridToolbar.addButton("delete_srow", 5, "Delete Selected", "cancel.png", "cancel.png");
assetEventReoccurencesGridToolbar.addSeparator("sep3", 6);
assetEventReoccurencesGridToolbar.addButton("generate_events", 7, "GenerateEvents", "generate1.png", "generate1.png");
assetEventReoccurencesGridToolbar.addSeparator("sep4", 8);
assetEventReoccurencesGridToolbar.addButton("clear_all", 9, "ClearAll", "del_evt.png", "del_evt.png");
assetEventReoccurencesGridToolbar.addSeparator("sep5", 10);
assetEventReoccurencesGridToolbar.addButton("check", 11, "Select All", "checked.png", "checked.png");
assetEventReoccurencesGridToolbar.addButton("uncheck", 12, "Unselect All", "unchecked.png", "unchecked.png");
//eventReoccurencesGridToolbar.addText("check", 11, "<form  name='checkform'><div style='height: 0px;width:12px;margin-bottom:0px;'><span><input value= 'Check' type='checkbox' name='checkAll' id='checkAll' onClick='toggle();' style='width:2px; height:2px ! important;'/></span><span style='padding-bottom:10px;'></span></div></form>");
//eventReoccurencesGridToolbar.addText("checktext", 12, "Select All");
assetEventReoccurencesGridToolbar.addSeparator("sep6", 13);
assetEventReoccurencesGridToolbar.addButton("is_vis", 14, "Vis/Invisible All", "is_vis.png", "is_vis.png");
assetEventReoccurencesGridToolbar.addSeparator("sep7", 15);
assetEventReoccurencesGridToolbar.attachEvent("onClick", toolbarSaveAssetReoccurencesDetails);
assetEventReoccurencesGridToolbar.disableItem("clear_all");

function assetToggle() {
    var checkallbtn = document.getElementById('checkAll');
    if (checkallbtn.checked === true) {
        assetEventReoccurencesGrid.selectAll();
    }
    if (checkallbtn.checked === false) {
        assetEventReoccurencesGrid.clearSelection();
    }
}

//function generates the events  
function generateAssetEvents() {
    //check if parent tasks has been selected 
    var plan_id = assetEventsGrid.getSelectedRowId();
    if (plan_id === null) {
        dhtmlx.alert("Please select Parent Task In Event Details!");
    } else {
        var task_id = assetEventsGrid.cells(plan_id, 0).getValue();
        //ensure that the list employees to assign have been selected   
        var emp_assigned = assetEventFormEmplCombo.getChecked();
        if (emp_assigned.length < 1)
        {
            alert("Please select employees to assign to from employee drop down!");
        } else
        {
            //send to server
            var evt_id = task_id;
            if (assetEventForm.getItemValue('begn') != null && assetEventForm.getItemValue('end') != null) {
                assetEventForm.send("Controller/php/projectsPlanning.php?action=34&evtId=" + evt_id + "&ass_emp=" + emp_assigned, function (loader, response)
                {
                    //refresh the child task grid
                    dhtmlx.alert("Recurring Event Activated!");
                    assetEventReoccurencesGrid.clearAndLoad(path + "/Schedule/Controller/generated_tasks.php?id=" + evt_id, function () {
                        assetEventReoccurencesGrid.selectAll();
                    });
                });
            } else {
                dhtmlx.alert("Time period not specified!");
            }
        }
    }
}

function toolbarSaveAssetReoccurencesDetails(id) {

    switch (id)
    {
        case 'new_rec':
            //create a new reoccurence  
            var rowId = assetEventsGrid.getSelectedRowId();
            var event_id = assetEventsGrid.cells(rowId, 0).getValue();
            if (event_id !== null) {

                window.dhx4.ajax.get("Controller/php/projectsPlanning.php?action=7&id=" + event_id + "&eid=" + uID, function (r) {
                    var t = null;
                    try {
                        eval("t=" + r.xmlDoc.responseText);
                    } catch (e) {
                    }
                    ;
                    if (t !== null && t.data.response) {
                        dhtmlx.message({title: 'Success', text: t.data.text});
                        assetEventReoccurencesGrid.updateFromXML(path + "/Schedule/Controller/generated_tasks.php?id=" + event_id, true, true, function ()
                        {
                            assetEventReoccurencesGrid.selectRowById(t.data.newId);
                            assetEventReoccurencesForm.load(path + '/Schedule/Controller/recurring.php?action=3&id=' + event_id, function (id, response) {
                            });
                        });
                    } else {
                        dhtmlx.alert({title: 'Error', text: t.data.text});
                    }
                });
            } else {
                dhtmlx.alert("Select a task record on top grid!");
            }
            break;
        case 'rechedule':
            var rwTsk = assetEventReoccurencesGrid.getSelectedRowId(); //event_id
            if (rwTsk != null)
            {
                reschedule(rwTsk);
            } else {
                dhtmlx.alert("No recurring task selected!");
            }
            break;

        case 'transfer':

            var rwTsk = assetEventReoccurencesGrid.getSelectedRowId(); //event_id
            if (rwTsk != null)
            {
                transfer(rwTsk);
            } else {
                dhtmlx.alert("No recurring task selected!");
            }
            break;

        case 'update':

            var rwTsk = assetEventReoccurencesGrid.getSelectedRowId(); //event_id
            if (rwTsk != null)
            {
                updateTask(rwTsk);
            } else {
                dhtmlx.alert("No recurring task selected!");
            }

            break;

        case 'generate_events':
            generateAssetEvents();
            break;

        case 'clear_all':
            //clearEvents(); 
            break;

        case 'delete_srow':
            var row_id = assetEventReoccurencesGrid.getSelectedRowId();
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
                            $.get("Controller/php/projectsPlanning.php?action=8&id=" + row_id, function (data) {
                                if (data.data.response) {

                                    dhtmlx.message({title: 'Success', text: data.data.text});
                                    assetEventReoccurencesGrid.deleteSelectedRows();
                                    $("#checkAllReoccurences").attr('checked', false);
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

        case 'delete_row':
            var row_id = assetEventReoccurencesGrid.getSelectedRowId();
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
                            $.get("Controller/php/projectsPlanning.php?action=8&id=" + row_id, function (data) {

                                if (data.data.response) {

                                    dhtmlx.message({title: 'Success', text: data.data.text});
                                    assetEventReoccurencesGrid.deleteRow(row_id);
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

        case 'is_vis':
            //first check if main parent row has been selected 
            var rowId = assetEventsGrid.getSelectedRowId();
            var event_id = assetEventsGrid.cells(rowId, 0).getValue();
            if (event_id !== null)
            {
                //send the parent record
                $.get(path + "/Schedule/Controller/generated_tasks.php?action=4&grdRow=" + event_id, function (data)
                {

                    if (data.bool == true)
                    { //check all items on the grid
                        assetEventReoccurencesGrid.forEachRow(function (id) {
                            var cell = assetEventReoccurencesGrid.cells(id, 7);
                            if (cell.isCheckbox())
                                cell.setValue(1);
                        });
                    } else
                    {//uncheck all items on grid
                        assetEventReoccurencesGrid.forEachRow(function (id) {
                            var cell = assetEventReoccurencesGrid.cells(id, 7);
                            if (cell.isCheckbox())
                                cell.setValue(0);
                        });
                    }
                    dhtmlx.message(data.info);
                }, "json");
            } else {
                dhtmlx.alert("Please select the parent record!");
            }
            break;

        case 'check':
            assetEventReoccurencesGrid.selectAll();
            break;
        case 'uncheck':
            assetEventReoccurencesGrid.clearSelection();
            break;
    }
}

var assetEventReoccurencesGrid = assetEventReoccurencesListCell.attachGrid();
assetEventReoccurencesGrid.setIconsPath("https://" + location.host + "/dhtmlxsuite4/codebase/imgs/");
assetEventReoccurencesGrid.setSkin('dhx_web');
assetEventReoccurencesGrid.setHeader(["Event Name", "Assigned To", "Start Date", "End Date", "Details", "Protection", "Personal", "Visible", "Done"]);
assetEventReoccurencesGrid.setColumnIds("details,employee_id,start_date,end_date,event_name,protection,personal,visible,completed");
assetEventReoccurencesGrid.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
assetEventReoccurencesGrid.setColTypes("ro,edtxt,ro,edtxt,txt,ch,ch,ch,ch");
assetEventReoccurencesGrid.setColAlign('left,left,left,left,left,center,center,center,center');
assetEventReoccurencesGrid.enableTooltips('true,true,true,true,true,true,true,true,true');
assetEventReoccurencesGrid.setColSorting('str,str,str,str,str,str,int,int,int');
assetEventReoccurencesGrid.enableCellIds(true);
assetEventReoccurencesGrid.enableMultiselect(true);
assetEventReoccurencesGrid.setInitWidthsP('20,10,10,10,*,8,7,6,6');
assetEventReoccurencesGrid.attachEvent("onCheck", assetventReoccurencesGridChecked);
assetEventReoccurencesGrid.init();
assetEventReoccurencesGrid.attachEvent("onRowSelect", assetgrid_child_tskSelected);

function assetgrid_child_tskSelected(id) {
    assetEventReoccurencesForm.load('Controller/php/projectsPlanning.php?action=36&id=' + id, function (id, response) {
    });
}

function assetventReoccurencesGridChecked(id, index, state) {
    var colId = assetEventReoccurencesGrid.getColumnId(index);
    $.post("Controller/php/projectsPlanning.php?action=10", {colId: colId, id: id, nValue: ((state) ? 1 : 0)}, function (data)
    {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
}


var assetEventReoccurencesFormCell = assetEventReoccurencesLayout.cells('b');
assetEventReoccurencesFormCell.setWidth(projectPlanningDetailsCell.getWidth() * 0.3);
assetEventReoccurencesFormCell.hideHeader();

var assetEventReoccurencesFormToolbar = assetEventReoccurencesFormCell.attachToolbar();
assetEventReoccurencesFormToolbar.setIconsPath("Views/imgs/");
assetEventReoccurencesFormToolbar.addButton("save_detail", 1, "Save", "save.gif", "save.gif");
assetEventReoccurencesFormToolbar.addSeparator("sep1", 2);
assetEventReoccurencesFormToolbar.attachEvent("onClick", assetToolbarChildDetails);

function assetToolbarChildDetails(id) {
    switch (id)
    {
        case 'save_detail':
            assetEventReoccurencesForm.send("Controller/php/projectsPlanning.php?action=37", "post", function (loader, response)
            {
                //update the task grid                
                dhtmlx.message("Saved!");
                var parsedJSON = eval('(' + response + ')');
                var tsk_id = parsedJSON.event_id;
                //id of selected event 
                var plan_id = assetEventsGrid.getSelectedRowId();
                var PlanningId = assetEventsGrid.cells(plan_id, 0).getValue(); //event_id
                assetEventReoccurencesGrid.updateFromXML('Controller/php/projectsPlanning.php?action=16&id=' + PlanningId, function () {
                });
            });
            break;
    }
}

var assetEventReoccurencesFormData = [
    {type: "settings", position: "label-left", labelWidth: assetEventReoccurencesFormCell.getWidth() * 0.16, inputWidth: assetEventReoccurencesFormCell.getWidth() * 0.56, offsetTop: 8, offsetLeft: 0},
    {type: "editor", className: "formbox", label: "Details", rows: 2, name: "asset_event_name_child", value: "", style: "width:" + assetEventReoccurencesFormCell.getWidth() * 0.72 + ";height:" + myHeight * 0.12 + ";"},
    {type: "combo", className: "formbox", comboType: "checkbox", label: "Assigned To", name: "employee_id", value: ""},
    {type: "label", className: "formbox", offsetTop: 0, list: [
            {type: "calendar", className: "formbox", position: "label-left", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", enableTime: false, label: "Start Date", inputWidth: 90, name: "start_date", value: "", readonly: false, offsetLeft: 0},
            {type: "newcolumn", offsetLeft: 0},
            {type: "input", className: "formbox", label: "Begin Time", position: "label-left", name: "begn", value: "", inputWidth: 55, offsetLeft: 5},
        ]},
    {type: "label", offsetTop: 0, list: [
            {type: "calendar", className: "formbox", position: "label-left", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", enableTime: false, label: "End Date", inputWidth: 90, name: "end_date", value: "", readonly: false, offsetLeft: 0}, //%H:%i
            {type: "newcolumn", offsetLeft: 0},
            {type: "input", className: "formbox", label: "End Time", position: "label-left", name: "end", value: "", inputWidth: 55, offsetLeft: 5},
        ]},
    {type: "editor", className: "formbox", label: "Information", rows: 2, name: "asset_event_information", value: "", style: "width:" + assetEventReoccurencesFormCell.getWidth() * 0.72 + ";height:" + myHeight * 0.12 + ";"}
];


var assetEventReoccurencesForm = assetEventReoccurencesFormCell.attachForm(assetEventReoccurencesFormData);
assetEventReoccurencesForm.getInput("start_date").style.backgroundImage = "url(https://" + location.host + "/dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
assetEventReoccurencesForm.getInput("start_date").style.backgroundPosition = "center right";
assetEventReoccurencesForm.getInput("start_date").style.backgroundRepeat = "no-repeat";
assetEventReoccurencesForm.getInput("end_date").style.backgroundImage = "url(https://" + location.host + "/dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
assetEventReoccurencesForm.getInput("end_date").style.backgroundPosition = "center right";
assetEventReoccurencesForm.getInput("end_date").style.backgroundRepeat = "no-repeat";

var assetEmpChild_Combo = assetEventReoccurencesForm.getCombo("employee_id");
assetEmpChild_Combo.load(path + "/Schedule/Controller/recurring.php?action=110");
