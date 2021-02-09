projectDetailsTabbar.addTab('project_tasks', 'Tasks');
var project_tasks = projectDetailsTabbar.cells('project_tasks');
var project_tasks_layout = project_tasks.attachLayout('2E');

var projectTasksMenuCalendar;
var SelectedEmpl = uID;
var projectTasksCheckedId = 'reccuring';

var projectTasksCell = project_tasks_layout.cells('a');
projectTasksCell.hideHeader();
var projectTasksMenu = projectTasksCell.attachMenu();
projectTasksMenu.setIconsPath('Views/imgs/')
projectTasksMenu.setSkin("dhx_web");
projectTasksMenu.loadStruct("Controller/php/projectsPlanning.php?action=24", function () {

    projectTasksMenu.setItemText('web_admin', projectTasksMenu.getItemText('user_' + SelectedEmpl));
    projectTasksMenu.addNewSibling("seper", 'date_select', "<input id=\"date_picker\" size=\"11\" style= '    color: #000000;    float: left;    font-family: Tahoma;    font-size: 11px;    margin-left 0;    margin-right: 1px;    margin-top: 2px;    padding: 0 4px;    vertical-align: middle;' type = 'text'  /><span><img id=\"calendar_icon\" src='' border=\"0\"></span>", false, '', '');

    projectTasksMenuCalendar = new dhtmlXCalendarObject("date_picker");
    projectTasksMenuCalendar.hideTime();
    projectTasksMenuCalendar.setDateFormat("%d.%m.%Y");
    $("#date_picker").val(projectTasksMenuCalendar.getFormatedDate());

    projectTasksMenuCalendar.attachEvent("onClick", function (date) {
        projectTasksCell.progressOn();
        actual_task_date = new Date(date).format('Y-m-d');
        projectTasksGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=25&id=" + SelectedEmpl + "&start_date=" + actual_task_date, function () {
            projectTasksCell.progressOff();
        });
    });
});

projectTasksMenu.attachEvent("onClick", projectTasksMenuClicked);

function projectTasksMenuClicked(id) {
    switch (id)
    {
        case "delete":

            var row_id = projectTasksGrid.getSelectedRowId();
            if (row_id > 0) {

                dhtmlx.confirm({
                    title: "Confirm",
                    type: "confirm-warning",
                    text: "Are you sure you to delete this Task?",
                    callback: function (ok) {
                        if (ok)
                        {
                            $.post("Controller/php/projectsPlanning.php?action=40", {id: row_id}, function (data) {
                                if (data.data.response) {
                                    projectTasksGrid.deleteRow(projectTasksGrid.getSelectedRowId());

                                    dhtmlx.message({title: 'Success', text: data.data.text});
                                } else
                                    dhtmlx.alert({title: 'Error', text: data.data.text});
                            }, 'json');

                        } else
                        {
                            return false;
                        }
                    }

                });
            } else {
                dhtmlx.alert('No Row Selected');
            }
            break;
        case 'show_procedure':
            var row_id = projectTasksGrid.getSelectedRowId();
            if (row_id !== null) {
                var doc_id = projectTasksGrid.cells(row_id, 3).getValue();
                var project_id = projectTasksGrid.cells(row_id, 1).getValue();
                project_id = y(project_id);
                if (doc_id > 0) {
                    searchedDocId = doc_id;
                    if (searchedDocId !== null) {
                        selected_doc_id = searchedDocId;
                        $.getJSON('Controller/php/data_toc.php?action=29&document_id=' + searchedDocId, function (results) {
                            if (results.response) {
                                branchId = results.id;
                            } else {
                                branchId = 0;
                            }
//console.log(project_id);
                            mainLayoutToolbar.setListOptionSelected('branch', branchId);
                            projectsTree.selectItem(project_id);
                            searchedDocId = null;
                            project_documents.setActive();
                            document_content.setActive();
                        });
                    }
                }
            }
            break;

        case 'export':
            projectTasksGrid.toExcel('Model/grid-excel/generate.php');
            break;

        case 'date_select':
            break;

        case 'reccuring':
            actual_task_date = new Date(projectTasksMenuCalendar.getDate()).format('Y-m-d');
            projectTasksCell.progressOn();
            projectTasksGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=25&id=" + SelectedEmpl + "&start_date=" + actual_task_date, doAfterprojectTasksGridRefresh);
            break;

        case 'day':
            actual_task_date = new Date(projectTasksMenuCalendar.getDate()).format('Y-m-d');
            projectTasksCell.progressOn();
            projectTasksGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=26&id=" + SelectedEmpl + "&start_date=" + actual_task_date, doAfterprojectTasksGridRefresh);
            break;

        case 'all':
            actual_task_date = new Date(projectTasksMenuCalendar.getDate()).format('Y-m-d');
            projectTasksCell.progressOn();
            projectTasksGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=27&id=" + SelectedEmpl + "&start_date=" + actual_task_date, doAfterprojectTasksGridRefresh);
            break;

        

        case 'edit':
            var row_id = projectTasksGrid.getSelectedRowId();
            openEditTaskWindow(row_id);
            break;

        default:
            projectTasksCell.progressOn();
            projectTasksMenu.setItemText('web_admin', projectTasksMenu.getItemText(id));

            var usr = id.split('_');
            var employee_id = usr[1];
            SelectedEmpl = employee_id;

            actual_task_date = new Date(projectTasksMenuCalendar.getDate()).format('Y-m-d');

            projectTasksCheckedId = projectTasksMenu.getRadioChecked('visible');

            switch (projectTasksCheckedId) {
                case 'reccuring':
                    actual_task_date = new Date(projectTasksMenuCalendar.getDate()).format('Y-m-d');
                    projectTasksGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=25&id=" + SelectedEmpl + "&start_date=" + actual_task_date, doAfterprojectTasksGridRefresh);
                    break;

                case 'day':
                    actual_task_date = new Date(projectTasksMenuCalendar.getDate()).format('Y-m-d');
                    projectTasksGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=26&id=" + SelectedEmpl + "&start_date=" + actual_task_date, doAfterprojectTasksGridRefresh);
                    break;

                case 'all':
                    actual_task_date = new Date(projectTasksMenuCalendar.getDate()).format('Y-m-d');
                    projectTasksGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=27&id=" + SelectedEmpl + "&start_date=" + actual_task_date, doAfterprojectTasksGridRefresh);
                    break;
            }

            break;
    }
}

function openEditTaskWindow(rowId) {
    if (rowId === null) {
        dhtmlx.alert({
            type: "alert-error",
            text: "You first have to select the task you need to edit.",
            title: "Error!"
        });
        return;
    }

    var windows = new dhtmlXWindows();
    var window_4 = windows.createWindow('window_4', 0, 0, 600, 150);
    window_4.setText('Edit Task Details');
    window_4.setModal(1);
    window_4.centerOnScreen();
    window_4.button('park').hide();
    window_4.button('minmax').hide();

    var str = [
        {type: "hidden", name: "event_id", label: "ID", value: rowId, labelWidth: 80},
        {type: "input", name: "event_name", label: "Task Name", labelWidth: 80, inputWidth: 400, value: projectTasksGrid.cells(rowId,0).getValue()},
        {type: "button", name: "form_button_2", value: "Save", inputLeft: 80}
    ];
    var form_3 = window_4.attachForm(str);

    form_3.attachEvent('onButtonClick', function () {
        var data = form_3.getFormData();

        $.post('Controller/php/projectsPlanning.php?action=41', data, function (data) {
            if (data.data.response) {
                window_4.close();
                dhtmlx.message({title: 'Success', text: data.data.text});
                switch (projectTasksCheckedId) {
                    case 'reccuring':
                        actual_task_date = new Date(projectTasksMenuCalendar.getDate()).format('Y-m-d');
                        projectTasksGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=25&id=" + SelectedEmpl + "&start_date=" + actual_task_date, doAfterprojectTasksGridRefresh);
                        break;

                    case 'day':
                        actual_task_date = new Date(projectTasksMenuCalendar.getDate()).format('Y-m-d');
                        projectTasksGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=26&id=" + SelectedEmpl + "&start_date=" + actual_task_date, doAfterprojectTasksGridRefresh);
                        break;

                    case 'all':
                        actual_task_date = new Date(projectTasksMenuCalendar.getDate()).format('Y-m-d');
                        projectTasksGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=27&id=" + SelectedEmpl + "&start_date=" + actual_task_date, doAfterprojectTasksGridRefresh);
                        break;
                }
            } else {
                dhtmlx.alert({title: 'Error', text: data.data.text});
            }
        }, 'json');
    })
}

function doAfterprojectTasksGridRefresh() {
    projectTasksCell.progressOff();
}

var projectTasksGrid = projectTasksCell.attachGrid();
projectTasksGrid.setSkin('dhx_web');
projectTasksGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
projectTasksGrid.setHeader(["ID","Task Name", "Project ID", "PID", "Document ID", "Frequency", "Begin Time", "End Time", "Duration", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"], null, []);
projectTasksGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ch,ch,ch,ch,ch,ch");
projectTasksGrid.attachHeader("#numeric_filter,#text_filter,#text_filter,,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,,,,,,");
projectTasksGrid.setColSorting('int,str,int,int,int,str,str,str,str,int,int,int,int,int,int');
projectTasksGrid.enableCellIds(true);
projectTasksGrid.setColumnIds('event_id,details,project_id,pid,doc_id,frequency,begin_time,end_time,duration,mon,tue,wed,thu,fri,sat');
projectTasksGrid.setColAlign('left,left,left,left,left,left,center,center,center,center,center,center,center,center,center');
projectTasksGrid.setInitWidthsP('8,*,6,0,7,8,6,6,5,4,4,4,4,4,4');
projectTasksGrid.setDateFormat('%Y-%m-%d');
projectTasksGrid.init();
projectTasksGrid.attachEvent("onSelectStateChanged", projectTasksGridStateChanged);
//projectTasksGrid.attachEvent("onRowDblClicked", doOnprojectTasksGridRowSelect);//onRowSelect
projectTasksCell.progressOn();
projectTasksGrid.load("Controller/php/projectsPlanning.php?action=25&id=" + SelectedEmpl, doAfterprojectTasksGridRefresh);

projectTasksGrid.attachEvent("onRowDblClicked", function (row_id, cInd) {
    var doc_id = projectTasksGrid.cells(row_id, 3).getValue();
    var project_id = projectTasksGrid.cells(row_id, 1).getValue();
    project_id = y(project_id);
    if (doc_id > 0) {
        searchedDocId = doc_id;
        if (searchedDocId !== null) {
            selected_doc_id = searchedDocId;
            $.getJSON('Controller/php/data_toc.php?action=29&document_id=' + searchedDocId, function (results) {
                if (results.response) {
                    branchId = results.id;
                } else {
                    branchId = 0;
                }
//console.log(project_id);
                mainLayoutToolbar.setListOptionSelected('branch', branchId);
                projectsTree.selectItem(project_id);
                searchedDocId = null;
                document_content.setActive();
            });
        }
    }
});

function projectTasksGridStateChanged(id, ind) {
    projectTasksReoccurencesListCell.progressOn();
    projectTasksReoccurencesGrid.clearAndLoad("Controller/php/data_recurring.php?id=" + id, function () {
        projectTasksReoccurencesListCell.progressOff();
    });
}
function doOnprojectTasksGridRowSelect(rId, cInd) {

    var pid = projectTasksGrid.cells(rId, 2).getValue();
    projectTaskEventId = pid;
    var value = projectTasksGrid.cells(rId, 1).getValue();

    var itemId = y(value);
    projectsTree.selectItem(itemId);
    projectDetailsTabbar.tabs("planning").setActive();
}

var projectTasksReoccurencesListCell = project_tasks_layout.cells('b');
projectTasksReoccurencesListCell.setText('Task Details');
var projectTasksReoccurencesGridToolbar = projectTasksReoccurencesListCell.attachToolbar();
projectTasksReoccurencesGridToolbar.setIconsPath("Views/imgs/");
projectTasksReoccurencesGridToolbar.addButton("new_rec", 1, "New", "new.gif", "new.gif");
projectTasksReoccurencesGridToolbar.addSeparator("sep1", 2);
projectTasksReoccurencesGridToolbar.addButton("delete_row", 3, "Delete Row", "deleteall.png", "deleteall.png");
projectTasksReoccurencesGridToolbar.addSeparator("sep2", 4);
projectTasksReoccurencesGridToolbar.addButton("delete_srow", 5, "Delete Selected", "cancel.png", "cancel.png");
projectTasksReoccurencesGridToolbar.addSeparator("sep3", 6);
projectTasksReoccurencesGridToolbar.addButton("generate_events", 7, "GenerateEvents", "generate1.png", "generate1.png");
projectTasksReoccurencesGridToolbar.addSeparator("sep4", 8);
projectTasksReoccurencesGridToolbar.addButton("clear_all", 9, "ClearAll", "del_evt.png", "del_evt.png");
projectTasksReoccurencesGridToolbar.addSeparator("sep5", 10);
projectTasksReoccurencesGridToolbar.addButton("check", 11, "Select All", "checked.png", "checked.png");
projectTasksReoccurencesGridToolbar.addButton("uncheck", 12, "Unselect All", "unchecked.png", "unchecked.png");
//eventReoccurencesGridToolbar.addText("check", 11, "<form  name='checkform'><div style='height: 0px;width:12px;margin-bottom:0px;'><span><input value= 'Check' type='checkbox' name='checkAll' id='checkAll' onClick='toggle();' style='width:2px; height:2px ! important;'/></span><span style='padding-bottom:10px;'></span></div></form>");
//eventReoccurencesGridToolbar.addText("checktext", 12, "Select All");
projectTasksReoccurencesGridToolbar.addSeparator("sep6", 13);
projectTasksReoccurencesGridToolbar.addButton("is_vis", 14, "Vis/Invisible All", "is_vis.png", "is_vis.png");
projectTasksReoccurencesGridToolbar.addSeparator("sep7", 15);
projectTasksReoccurencesGridToolbar.attachEvent("onClick", projectTasksReoccurencesGridToolbarClicked);
projectTasksReoccurencesGridToolbar.disableItem("clear_all");

function toggle() {
    var checkallbtn = document.getElementById('checkAll');
    if (checkallbtn.checked == true) {
        projectTasksReoccurencesGrid.selectAll();
    }
    if (checkallbtn.checked == false) {
        projectTasksReoccurencesGrid.clearSelection();
    }
}

function projectTasksReoccurencesGridToolbarClicked(id) {
    switch (id)
    {
        case 'new_rec':
            //create a new reoccurence  
            var event_id = projectTasksGrid.getSelectedRowId();
            if (event_id !== null)
            {

                window.dhx4.ajax.get("Controller/php/projectsPlanning.php?action=7&id=" + event_id + "&eid=" + uID, function (r) {
                    var t = null;
                    try {
                        eval("t=" + r.xmlDoc.responseText);
                    } catch (e) {
                    }
                    ;
                    if (t !== null && t.data.response) {
                        dhtmlx.message({title: 'Success', text: t.data.text});
                        projectTasksReoccurencesGrid.updateFromXML(path + "/Schedule/Controller/generated_tasks.php?id=" + event_id, true, true, function ()
                        {
                            projectTasksReoccurencesGrid.selectRowById(t.data.newId);
//                            eventReoccurencesForm.load(path + '/Schedule/Controller/recurring.php?action=3&id=' + event_id);
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
            var rwTsk = projectTasksReoccurencesGrid.getSelectedRowId(); //event_id
            if (rwTsk != null)
            {
                reschedule(rwTsk);
            } else {
                dhtmlx.alert("No recurring task selected!");
            }
            break;
        case 'transfer':

            var rwTsk = projectTasksReoccurencesGrid.getSelectedRowId(); //event_id
            if (rwTsk != null)
            {
                transfer(rwTsk);
            } else {
                dhtmlx.alert("No recurring task selected!");
            }
            break;
        case 'update':
            var rwTsk = projectTasksReoccurencesGrid.getSelectedRowId(); //event_id
            if (rwTsk != null)
            {
                updateTask(rwTsk);
            } else {
                dhtmlx.alert("No recurring task selected!");
            }

            break;
        case 'generate_events':
            generateEvents();
            break;
        case 'clear_all':
            //clearEvents(); 
            break;
        case 'delete_srow':
            var row_id = projectTasksReoccurencesGrid.getSelectedRowId();
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
                            $.post("Controller/php/projectsPlanning.php?action=8", {id:row_id},function (data) {
                                if (data.data.response) {

                                    dhtmlx.message({title: 'Success', text: data.data.text});
                                    projectTasksReoccurencesGrid.deleteSelectedRows();
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
            var row_id = projectTasksReoccurencesGrid.getSelectedRowId();
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
                            $.post("Controller/php/projectsPlanning.php?action=8", {id:row_id},function (data) {

                                if (data.data.response) {

                                    dhtmlx.message({title: 'Success', text: data.data.text});
                                    projectTasksReoccurencesGrid.deleteRow(row_id);
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
            var event_id = projectTasksGrid.getSelectedRowId();
            if (event_id !== null)
            {
                //send the parent record
                $.get(path + "/Schedule/Controller/generated_tasks.php?action=4&grdRow=" + event_id, function (data)
                {

                    if (data.bool == true)
                    { //check all items on the grid
                        projectTasksReoccurencesGrid.forEachRow(function (id) {
                            var cell = projectTasksReoccurencesGrid.cells(id, 7);
                            if (cell.isCheckbox())
                                cell.setValue(1);
                        });
                    } else
                    {//uncheck all items on grid
                        projectTasksReoccurencesGrid.forEachRow(function (id) {
                            var cell = projectTasksReoccurencesGrid.cells(id, 7);
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
            projectTasksReoccurencesGrid.selectAll();
            break;
        case 'uncheck':
            projectTasksReoccurencesGrid.clearSelection();
            break;
    }
}

var projectTasksReoccurencesGrid = projectTasksReoccurencesListCell.attachGrid();
projectTasksReoccurencesGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
projectTasksReoccurencesGrid.setSkin('dhx_web');
projectTasksReoccurencesGrid.setHeader(["Event Name", "Assigned To", "Start Date", "End Date", "Details", "Protection", "Personal", "Visible", "Done"]);
projectTasksReoccurencesGrid.setColumnIds("details,employee_id,start_date,end_date,event_name,protection,personal,visible,completed");
projectTasksReoccurencesGrid.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
projectTasksReoccurencesGrid.setColTypes("ro,edtxt,ro,edtxt,txt,ch,ch,ch,ch");
projectTasksReoccurencesGrid.setColAlign('left,left,left,left,left,center,center,center,center');
projectTasksReoccurencesGrid.enableTooltips('true,true,true,true,true,true,true,true,true');
projectTasksReoccurencesGrid.setColSorting('str,str,str,str,str,str,int,int,int');
projectTasksReoccurencesGrid.enableCellIds(true);
projectTasksReoccurencesGrid.enableMultiselect(true);
projectTasksReoccurencesGrid.setInitWidthsP('20,10,10,10,*,8,7,6,6');
projectTasksReoccurencesGrid.attachEvent("onCheck", projectTasksReoccurencesGridChecked);
projectTasksReoccurencesGrid.init();


function projectTasksReoccurencesGridChecked(id, index, state) {
    var colId = projectTasksReoccurencesGrid.getColumnId(index);
    $.post("Controller/php/projectsPlanning.php?action=10", {colId: colId, id: id, nValue: ((state) ? 1 : 0)}, function (data)
    {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
}
