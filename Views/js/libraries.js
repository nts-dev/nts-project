var classesGrid, classesGridContextMenu, class_details_form, classHistoryGrid, classHistoryContentIframe,
    classplanningGrid, classPlanningForm, classContentIframe, classContentCell, classPlanningEmployeeCombo,
    class_accordion_view;
var lib_id = null;

function openClassesWindow() {

    var windows = new dhtmlXWindows();
    var classes_window = windows.createWindow('classes_window', 0, 0, myWidth * 0.8, myHeight * 0.8);
    classes_window.setText('Libraries');
    classes_window.setModal(1);
    classes_window.centerOnScreen();
    classes_window.button('park').hide();
    classes_window.button('minmax').hide();

    var classes_layout = classes_window.attachLayout('2U');

    var classes = classes_layout.cells('a');
    classes.setText('Classes');
    classes.setWidth(myWidth * 0.2);

    var classes_toolbar = classes.attachToolbar();
    classes_toolbar.setIconsPath("Views/imgs/");
    classes_toolbar.addButton('new', 1, '<i class="fa fa-plus" aria-hidden="true"></i> New Library');
    classes_toolbar.addSeparator('button_separator_4', 2);
    classes_toolbar.addButton('delete', 3, '<i class="fa fa-times" aria-hidden="true"></i> Delete');
    classes_toolbar.attachEvent('onClick', classesToolbarClicked);

    //context menu add items
    classesGridContextMenu = new dhtmlXMenuObject();
    classesGridContextMenu.setIconsPath('Views/imgs/');
    classesGridContextMenu.renderAsContextMenu();
    classesGridContextMenu.attachEvent("onClick", classesGridContextMenuSelect);
    classesGridContextMenu.loadStruct("Controller/php/data_libraries.php?action=2");

    classesGrid = classes.attachGrid();
    classesGrid.setImagesPath(DHTMLXPATH + 'skins/web/imgs/');
    classesGrid.setSkin('dhx_web');
    classesGrid.setHeader(["Libraries"]);
    classesGrid.setColTypes("tree");
    classesGrid.setColSorting('str');
    classesGrid.enableCellIds(true);
    classesGrid.setColumnIds('title');
    classesGrid.setInitWidths('*');
    classesGrid.enableDragAndDrop(true);
    classesGrid.enableContextMenu(classesGridContextMenu);
    classesGrid.init();
    classesGrid.load("Controller/php/data_libraries.php?action=4", 'xml');

    classesGrid.attachEvent('onDrop', function (sId, tId, dId, sObj, tObj, sCol, tCol) {
        tId = tId > 0 ? tId : 0;

        $.get('Controller/php/data_libraries.php?action=13&sId=' + sId + '&tId=' + tId, function (data) {
            if (data.data.response) {
                classesGrid.updateFromXML("Controller/php/data_libraries.php?action=4", true, true);
                dhtmlx.message({title: 'Success', text: data.data.text});
            } else {
                dhtmlx.alert({title: 'Error', text: data.data.text});
            }
        }, 'json');
    });

    classesGrid.attachEvent('onEditCell', function (stage, rId, cInd, nValue, oValue) {
        var cell = classesGrid.cells(rId, cInd);
        if (stage === 2 && !cell.isCheckbox()) {
            var row_id = classesGrid.getSelectedRowId();
            if (row_id > 0 || typeof row_id !== 'undefined') {
                var colId = classesGrid.getColumnId(cInd);
                var colType = classesGrid.fldSort[cInd];

                $.post("Controller/php/data_libraries.php?action=10", {
                    id: row_id,
                    index: cInd,
                    fieldvalue: nValue,
                    colId: colId,
                    colType: colType
                }, function (data) {
                    if (data.data.response) {
                        dhtmlx.message({title: 'Success', text: data.data.text});
                        classesGrid.updateFromXML("Controller/php/data_libraries.php?action=4", true, true);
                        class_details_form.load("Controller/php/data_libraries.php?action=11&id=" + rId);
                    } else {
                        dhtmlx.alert({title: 'Error', text: data.data.text});
                    }
                }, 'json');

            }
        } else if (stage === 0 && cell.isCheckbox()) {
            return true;
        }
    });

    classesGrid.attachEvent('onSelectStateChanged', function (id) {
        lib_id = id;
        classContentCell.progressOn();
        window.dhx4.ajax.get("Controller/php/data_libraries.php?action=7&id=" + id, function (r) {
            classContentCell.progressOff();
            var t = null;
            try {
                eval("t=" + r.xmlDoc.responseText);
            } catch (e) {
            }
            ;
            if (t !== null && t.content !== null) {
                classContentIframe.contentWindow.tinymce.activeEditor.setContent(t.content);
            }
        });
        class_details_form.clear();
        class_details_form.load("Controller/php/data_libraries.php?action=11&id=" + id);
        classplanningGrid.clearAndLoad('Controller/php/data_libraries.php?action=17&id=' + id);
        classHistoryGrid.clearAndLoad("Controller/php/data_libraries.php?action=21&id=" + id);
        var activeDetailsTab = class_details_tabbar.getActiveTab();
        if (activeDetailsTab === 'class_viewer') {
            var class_id = classesGrid.getSelectedRowId();
//            if (class_id) {
            var activeViewTab = class_viewer_tabbar.getActiveTab();
            if (activeViewTab === 'class_normal_view') {
                class_normal_view.attachURL('Views/frames/class_viewer.php?id=' + class_id);
            }
            if (activeViewTab === 'class_accordion_view') {
                class_accordion_view.attachURL('Views/frames/class_accordion.php?id=' + class_id);
            }
//            }
        }
    });


    var b = classes_layout.cells('b');
    var class_details_tabbar = b.attachTabbar();
    class_details_tabbar.addTab('class_content', 'Content');

    var class_content = class_details_tabbar.cells('class_content');
    class_content.setActive();
    var classEditorLayout = class_content.attachLayout('1C');

    classContentCell = classEditorLayout.cells('a');
    classContentCell.hideHeader();
    classContentCell.attachURL("Views/frames/class_content.php", false,
        {report_content: '', height: (classContentCell.getHeight()) / 1.35});
    classEditorLayout.attachEvent("onContentLoaded", function (id) {
        classContentIframe = classEditorLayout.cells(id).getFrame();
    });


    class_details_tabbar.addTab('function_details', 'Details');
    var function_details = class_details_tabbar.cells('function_details');

    var function_details_toolbar = function_details.attachToolbar();
    function_details_toolbar.setIconsPath("Views/imgs/");
    function_details_toolbar.addButton('save', 6, '<i class="fa fa-floppy" aria-hidden="true"></i> Save');

    function_details_toolbar.attachEvent("onClick", function (id) {
        if (id === 'save') {
            var rowId = classesGrid.getSelectedRowId();
            if (rowId !== null) {
                function_details.progressOn();
                class_details_form.send("Controller/php/data_libraries.php?action=12&id=" + rowId, function (loader, response) {
                    function_details.progressOff();
                    var parsedJSON = eval('(' + response + ')');
                    if (parsedJSON.data.response) {
                        dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                        classesGrid.updateFromXML("Controller/php/data_libraries.php?action=4");
                    } else {
                        dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                    }
                });

            } else {
                dhtmlx.alert("No Row selected!");
            }
        }
    });

    var class_formdata = [
        {type: "settings", labelWidth: 120, inputWidth: 250, offsetLeft: "10", offsetTop: "10"},
        {type: "input", name: "title", label: "Title"},
        {type: "combo", name: "author", label: "Author"},
        {type: "calendar", name: "date_created", label: "Date Created", dateFormat: "%Y-%m-%d"},
        {type: "input", name: "comments", label: "Comments", rows: 3},
    ];
    class_details_form = function_details.attachForm(class_formdata);
    var class_detailsEmployeeCombo = class_details_form.getCombo("author");
    class_detailsEmployeeCombo.enableFilteringMode(true);
    class_detailsEmployeeCombo.load("Controller/php/data_libraries.php?action=9");

    class_details_tabbar.addTab('class_viewer', 'View Documentation');
    var class_viewer = class_details_tabbar.cells('class_viewer');

    var class_viewer_tabbar = class_viewer.attachTabbar();

    class_viewer_tabbar.addTab('class_normal_view', 'Normal View');
    var class_normal_view = class_viewer_tabbar.cells('class_normal_view');
    class_normal_view.setActive();

    class_viewer_tabbar.addTab('class_accordion_view', 'Accordion View');
    var class_accordion_view = class_viewer_tabbar.cells('class_accordion_view');

    class_details_tabbar.attachEvent("onSelect", function (id, lastId) {

        if (id === 'class_viewer') {
            var class_id = classesGrid.getSelectedRowId();
//            if (class_id) {
            var activeViewTab = class_viewer_tabbar.getActiveTab();
            if (activeViewTab === 'class_normal_view') {
                class_normal_view.attachURL('Views/frames/class_viewer.php?id=' + class_id);
            }
            if (activeViewTab === 'class_accordion_view') {
                class_accordion_view.attachURL('Views/frames/class_accordion.php?id=' + class_id);
            }
//            }
        }

        return true;
    });

    class_viewer_tabbar.attachEvent("onSelect", function (id, lastId) {

        var class_id = classesGrid.getSelectedRowId();
//        if (class_id) {
        if (id === 'class_normal_view') {
            class_normal_view.attachURL('Views/frames/class_viewer.php?id=' + class_id);
        }
        if (id === 'class_accordion_view') {
            class_accordion_view.attachURL('Views/frames/class_accordion.php?id=' + class_id);
        }
//        }

        return true;
    });

    class_details_tabbar.addTab('class_planning', 'Planning');
    var class_planning = class_details_tabbar.cells('class_planning');

    var class_planning_layout = class_planning.attachLayout('2U');

    var classplanningGridCell = class_planning_layout.cells('a');
    classplanningGridCell.hideHeader();

    var classPlanningFormCell = class_planning_layout.cells('b');
    classPlanningFormCell.setText('Details');

    var classPlanningGridToolbar = classplanningGridCell.attachToolbar();
    classPlanningGridToolbar.setIconsPath("Views/imgs/");
    classPlanningGridToolbar.addSeparator("sep1", 1);
    classPlanningGridToolbar.addButton("add", 2, "Add Event", "new.gif", "new.gif");
    classPlanningGridToolbar.addSeparator("sep2", 3);
    classPlanningGridToolbar.addButton("delete", 4, "Delete Event", "deleteall.png", "deleteall.png");
    classPlanningGridToolbar.attachEvent("onClick", classPlanningGridToolbarClicked);


    classplanningGrid = classplanningGridCell.attachGrid();
    classplanningGrid.setImagesPath(DHTMLXPATH + 'skins/web/imgs/');
    classplanningGrid.setSkin('dhx_web');
    classplanningGrid.setHeader(["ID", "Event Name", "Assigned To", "Begin Date", "End Date", "Details", "Visible", "Main Task", "Done"],
        null,
        ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:center;", "text-align:center;", "text-align:center;"]);
    classplanningGrid.setColumnIds("event_id,details,employee_id,start_date,end_date,event_name,visible,main_task,completed");
    classplanningGrid.attachHeader('#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,,,');
    classplanningGrid.setColTypes("ro,ro,ro,dhxCalendar,dhxCalendar,ed,ch,ch,ch");
    classplanningGrid.setDateFormat("%Y-%m-%d %H:%i");
    classplanningGrid.setColAlign('left,left,left,left,left,left,center,center,center');
    classplanningGrid.setColSorting('str,str,str,date,date,str,int,int,int');
    classplanningGrid.enableCellIds(true);
    classplanningGrid.enableMultiline(true);
    classplanningGrid.setInitWidthsP('0,*,*,*,*,*,*,*,*');
    classplanningGrid.attachEvent("onSelectStateChanged", classPlanningGridRowSelect);//onRowSelect
    classplanningGrid.attachEvent("onEditCell", classplanningGridEdit);
    classplanningGrid.attachEvent("onCheck", classPlanningGridChecked);
    for (var i = 5; i < 9; i++) {
        classplanningGrid.setColumnHidden(i, true);
    }
    classplanningGrid.init();

    var classplanningFormToolbar = classPlanningFormCell.attachToolbar();
    classplanningFormToolbar.setIconsPath("Views/imgs/");

    classplanningFormToolbar.loadStruct('<toolbar><item type="button" id="save" text="Save" img="save.gif" /></toolbar>', function () {
    });
    classplanningFormToolbar.attachEvent('onClick', classPlanningFormToolbarClicked);

    classPlanningFormdata = [
        {
            type: "settings",
            position: "label-left",
            labelWidth: classPlanningFormCell.getWidth() * 0.2,
            inputWidth: classPlanningFormCell.getWidth() * 0.6,
            offsetTop: 8,
            offsetLeft: 20
        },
//    {type: "fieldset", label: "Event Details", className: "formbox", width: taskDetailsCell.getWidth() * 0.9, offsetLeft: 10, list:
//                [
        {type: "hidden", label: "ID", name: "event_id", value: ""},
        {type: "input", label: "Event Name", name: "event_name", value: ""},
        {
            type: "editor",
            label: "Details",
            rows: 2,
            name: "libraries_details",
            position: "label-left",
            value: "",
            style: "width:" + classPlanningFormCell.getWidth() * 0.6 + ";height:" + classPlanningFormCell.getHeight() * 0.15 + ";"
        },
        {type: "combo", comboType: "checkbox", label: "Assigned To", name: "emp", value: ""},
        {
            type: "block", width: classPlanningFormCell.getWidth() * 0.8, offsetTop: 0, list: [
                {
                    type: "calendar",
                    position: "label-left",
                    dateFormat: "%Y-%m-%d",
                    serverDateFormat: "%Y-%m-%d",
                    enableTime: false,
                    label: "Start Date",
                    inputWidth: 90,
                    name: "start_date",
                    value: "",
                    readonly: false,
                    offsetLeft: 0
                },
                {
                    type: "calendar",
                    position: "label-left",
                    dateFormat: "%Y-%m-%d",
                    serverDateFormat: "%Y-%m-%d",
                    enableTime: false,
                    label: "End Date",
                    inputWidth: 90,
                    name: "end_date",
                    value: "",
                    readonly: false,
                    offsetLeft: 0
                }, //%H:%i
                {type: "newcolumn", offsetLeft: 10},
                {
                    type: "input",
                    label: "Time",
                    position: "label-left",
                    name: "begn",
                    value: "",
                    inputWidth: 50,
                    offsetLeft: 10,
                    labelWidth: 30
                },
                {
                    type: "input",
                    label: "Time",
                    position: "label-left",
                    name: "end",
                    value: "",
                    inputWidth: 50,
                    offsetLeft: 10,
                    labelWidth: 30
                }
            ]
        },
        {
            type: "combo", name: "period", offsetLeft: 20, inputWidth: 150, label: "Period", options: [
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
            ]
        },
        {
            type: "combo", name: "freq", label: "Frequency", offsetLeft: 20, inputWidth: 150, options: [
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
            ]
        },
//    {type: "newcolumn", offset: 30},
        {
            type: "fieldset", name: "label_days", label: "Select Days", width: classPlanningFormCell.getWidth() * 0.8,
            list: [
                {type: "checkbox", name: "days_select[1]", labelWidth: 25, label: "Mon"},
                {type: "newcolumn"},
                {type: "checkbox", name: "days_select[2]", labelWidth: 25, label: "Tue"},
                {type: "newcolumn"},
                {type: "checkbox", name: "days_select[3]", labelWidth: 25, label: "Wed"},
                {type: "newcolumn"},
                {type: "checkbox", name: "days_select[4]", labelWidth: 22, label: "Thur"},
                {type: "newcolumn"},
                {type: "checkbox", name: "days_select[5]", labelWidth: 25, label: "Fri"},
                {type: "newcolumn"},
                {type: "checkbox", name: "days_select[6]", labelWidth: 25, label: "Sat"},
                {type: "newcolumn"},
//            {type: "checkbox", name: "days_select[7]", labelWidth: 25, label: "Sun"},
            ],
        },
        {
            type: "checkbox",
            name: "variable",
            position: "label-left",
            labelWidth: 100,
            value: "1",
            label: "Enable Variable",
            checked: false,
            offsetLeft: 20
        },
        {
            type: "hidden",
            label: "Rec_Type",
            position: "label-left",
            name: "rec_type",
            value: "",
            inputWidth: 48,
            offsetLeft: 5
        },
        {
            type: "hidden",
            label: "Cat_id",
            position: "label-left",
            name: "cat_id",
            value: "",
            inputWidth: 48,
            offsetLeft: 5
        },
        {
            type: "editor",
            label: "Information",
            rows: 2,
            position: "label-left",
            name: "toc_info",
            value: "",
            style: "width:" + classPlanningFormCell.getWidth() * 0.6 + ";height:" + classPlanningFormCell.getHeight() * 0.15 + ";"
        },
        {type: "combo", comboType: "checkbox", label: "Approved by", name: "approved_by", value: ""},
        {type: "checkbox", name: "map", value: "0", label: "Show map", checked: true},
        {type: "checkbox", name: "masterrecord", value: "0", label: "Show masterrecord", checked: false},
        {type: "checkbox", name: "reoccur_map", value: "0", label: "Reoccur map", checked: false}
//                ]
//    }
    ];

    classPlanningFormCell.hideHeader();

    classPlanningForm = classPlanningFormCell.attachForm(classPlanningFormdata);
    classPlanningForm.getInput("start_date").style.backgroundImage = "url(" + DHTMLXPATH + "samples/dhtmlxCalendar/common/calendar.gif)";
    classPlanningForm.getInput("start_date").style.backgroundPosition = "center right";
    classPlanningForm.getInput("start_date").style.backgroundRepeat = "no-repeat";
    classPlanningForm.getInput("end_date").style.backgroundImage = "url("
    DHTMLXPATH + "samples/dhtmlxCalendar/common/calendar.gif)";
    classPlanningForm.getInput("end_date").style.backgroundPosition = "center right";
    classPlanningForm.getInput("end_date").style.backgroundRepeat = "no-repeat";

    classPlanningEmployeeCombo = classPlanningForm.getCombo("emp");
    classPlanningEmployeeCombo.enableFilteringMode(true);
    classPlanningEmployeeCombo.load("Controller/php/projectsPlanning.php?action=2");

    var classPlanningApproved_Combo = classPlanningForm.getCombo("approved_by");
    classPlanningApproved_Combo.load("Controller/php/recurring.php?action=110");

    classPlanningEmployeeCombo.attachEvent("onCheck", function (value, state) {
        var eventId = classplanningGrid.getSelectedRowId();
        var employeeId = value;

        $.post("Controller/php/projectsPlanning.php?action=28", {
            eventId: eventId,
            employeeId: employeeId,
            nValue: ((state) ? 1 : 0),
            eid: uID
        }, function (data) {
            if (data.data.response) {
                dhtmlx.message({title: 'Success', text: data.data.text});
            } else {
                dhtmlx.alert({title: 'Error', text: data.data.text});
            }
        }, 'json');

        return true;
    });

    class_details_tabbar.addTab('class_history', 'History');
    var class_history = class_details_tabbar.cells('class_history');

    var classHistoryLayout = class_history.attachLayout('2U');

    var classHistoryListCell = classHistoryLayout.cells('a');
    classHistoryListCell.hideHeader();

    classHistoryGrid = classHistoryListCell.attachGrid();
    classHistoryGrid.setIconsPath('./codebase/imgs/');
    classHistoryGrid.setSkin('dhx_web');
    classHistoryGrid.setHeader(["ID", "Date", "Author", "Char"]);
    classHistoryGrid.setColumnIds('toc_id,date_edited,author,char');
    classHistoryGrid.attachHeader("#numeric_filter,#text_filter,#text_filter,#text_filter");
    classHistoryGrid.setColTypes("ro,ro,ro,ro");
    classHistoryGrid.setInitWidthsP("14,25,*,15");
    classHistoryGrid.setColSorting('str,date,str,int');
    classHistoryGrid.enableCellIds(true);
    classHistoryGrid.setDateFormat("%Y-%m-%d %H:%i:%s");
    classHistoryGrid.attachEvent("onSelectStateChanged", classHistoryGridStateChanged);//onRowSelect
    classHistoryGrid.init();

    var classHistoryToolbar = classHistoryListCell.attachToolbar();
    classHistoryToolbar.setIconsPath("Views/imgs/");
    classHistoryToolbar.addButton("delete", 1, "Delete", "deleteall.png", "deleteall.png");
    classHistoryToolbar.addSeparator("sep1", 2);
    classHistoryToolbar.addButton("delete_all", 3, "Delete All", "deleteall.png", "deleteall.png");
    classHistoryToolbar.addSeparator("sep2", 4);

    classHistoryToolbar.attachEvent("onClick", classHistoryToolbarClicked);

    var classHistoryContentCell = classHistoryLayout.cells('b');
    classHistoryContentCell.hideHeader();
    classHistoryContentCell.attachURL("Views/frames/history_content.php", false,
        {report_content: '', height: (classHistoryContentCell.getHeight()) / 1.26});
    classHistoryLayout.attachEvent("onContentLoaded", function (id) {
        classHistoryContentIframe = classHistoryLayout.cells(id).getFrame();
    });

    class_details_tabbar.addTab('source_code', 'Source Code');
    var source_code = class_details_tabbar.cells('source_code');

    class_details_tabbar.addTab('source_code_history', 'Source Code History');
    var source_code_history = class_details_tabbar.cells('source_code_history');
}

function classesToolbarClicked(id) {
    switch (id) {
        case 'new':

            window.dhx4.ajax.get("Controller/php/data_libraries.php?action=1&parent_id=0", function (r) {
                var t = null;
                try {
                    eval("t=" + r.xmlDoc.responseText);
                } catch (e) {
                }
                ;
                if (t !== null && t.data.response) {
                    dhtmlx.message({title: 'Success', text: t.data.text});
                    classesGrid.clearAndLoad("Controller/php/data_libraries.php?action=4", function () {
                        classesGrid.selectRowById(t.data.row_id);
                    });
                } else {
                    dhtmlx.alert({title: 'Error', text: t.data.text});
                }
            });
            break;

        case 'delete':
            var rowId = classesGrid.getSelectedRowId();
            if (rowId === null) {
                dhtmlx.alert("No item selected!");
            } else {
                var hasChildren = classesGrid.hasChildren(rowId)
                if (hasChildren) {
                    dhtmlx.alert("Row has child items!")
                } else {
                    dhtmlx.confirm({
                        title: "Confirm",
                        type: "confirm-warning",
                        text: "Are you sure you  want to delete?",
                        callback: function (y) {
                            if (y) {
                                $.get("Controller/php/data_libraries.php?action=3&id=" + rowId, function (data) {
                                    if (data.data.response) {
                                        dhtmlx.message({title: 'Success', text: data.data.text});
                                        classesGrid.deleteRow(rowId);
                                    } else {
                                        dhtmlx.alert({title: 'Error', text: data.data.text});
                                    }
                                }, 'json');
                            } else {
                                return false;
                            }
                        }
                    });
                }
            }
            break;
    }
}

function classesGridContextMenuSelect(menuitemId, type) {
    switch (menuitemId) {
        case 'new_parent':

            window.dhx4.ajax.get("Controller/php/data_libraries.php?action=1&parent_id=0", function (r) {
                var t = null;
                try {
                    eval("t=" + r.xmlDoc.responseText);
                } catch (e) {
                }
                ;
                if (t !== null && t.data.response) {
                    dhtmlx.message({title: 'Success', text: t.data.text});
                    classesGrid.updateFromXML("Controller/php/data_libraries.php?action=4", true, true, function () {
                        classesGrid.selectRowById(t.data.row_id);
                    });
                } else {
                    dhtmlx.alert({title: 'Error', text: t.data.text});
                }
            });

            break;

        case 'new_child':

            var parentId = classesGrid.getSelectedRowId();
            window.dhx4.ajax.get("Controller/php/data_libraries.php?action=1&parent_id=" + parentId, function (r) {
                var t = null;
                try {
                    eval("t=" + r.xmlDoc.responseText);
                } catch (e) {
                }
                ;
                if (t !== null && t.data.response) {
                    dhtmlx.message({title: 'Success', text: t.data.text});
                    classesGrid.updateFromXML("Controller/php/data_libraries.php?action=4", true, true, function () {
                        classesGrid.openItem(parentId);
                        classesGrid.selectRowById(t.data.row_id);
                    });
                } else {
                    dhtmlx.alert({title: 'Error', text: t.data.text});
                }
            });

            break;

        case 'delete':
            var rowId = classesGrid.getSelectedRowId();
            if (rowId === null) {
                dhtmlx.alert("No item selected!");
            } else {
                var hasChildren = classesGrid.hasChildren(rowId)
                if (hasChildren) {
                    dhtmlx.alert("Row has child items!")
                } else {
                    dhtmlx.confirm({
                        title: "Confirm",
                        type: "confirm-warning",
                        text: "Are you sure you  want to delete?",
                        callback: function (y) {
                            if (y) {
                                $.get("Controller/php/data_libraries.php?action=3&id=" + rowId, function (data) {
                                    if (data.data.response) {
                                        dhtmlx.message({title: 'Success', text: data.data.text});
                                        classesGrid.deleteRow(rowId);
                                    } else {
                                        dhtmlx.alert({title: 'Error', text: data.data.text});
                                    }
                                }, 'json');
                            } else {
                                return false;
                            }
                        }
                    });
                }
            }
            break;
    }
}

function classHistoryGridStateChanged(id, ind) {

    classHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent("");
    window.dhx4.ajax.get("Controller/php/data_libraries.php?action=19&id=" + id, function (r) {
        var t = null;
        try {
            eval("t=" + r.xmlDoc.responseText);
        } catch (e) {
        }
        ;
        if (t !== null && t.content !== null) {
            classHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent(t.content);
        }
    });
}

function classHistoryToolbarClicked(id) {
    switch (id) {
        case "delete":

            var row_id = classHistoryGrid.getSelectedRowId();
            if (row_id > 0) {

                dhtmlx.confirm({
                    title: "Confirm",
                    type: "confirm-warning",
                    text: "Are you sure you to delete this Row?",
                    callback: function (ok) {
                        if (ok) {
                            window.dhx4.ajax.get("Controller/php/data_libraries.php?action=20&case=1&id=" + row_id, function (r) {
                                var t = null;
                                try {
                                    eval("t=" + r.xmlDoc.responseText);
                                } catch (e) {
                                }
                                ;
                                if (t !== null && t.data.response) {
                                    classHistoryGrid.deleteRow(row_id);
                                    classHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent("");
                                    dhtmlx.message({title: 'Success', text: t.data.text});
                                } else
                                    dhtmlx.alert({title: 'Error', text: t.data.text});
                            });
                        } else {
                            return false;
                        }
                    }

                });
            } else {
                dhtmlx.alert('No Row Selected');
            }
            break;

        case "delete_all":

            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you to delete all History?",
                callback: function (ok) {
                    if (ok) {
                        window.dhx4.ajax.get("Controller/php/data_libraries.php?action=20&case=default&id=" + lib_id, function (r) {
                            var t = null;
                            try {
                                eval("t=" + r.xmlDoc.responseText);
                            } catch (e) {
                            }
                            ;
                            if (t !== null && t.data.response) {
                                classHistoryGrid.clearAll();
                                classHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent("");
                                dhtmlx.message({title: 'Success', text: t.data.text});
                            } else
                                dhtmlx.alert({title: 'Error', text: t.data.text});
                        });
                    } else {
                        return false;
                    }
                }

            });

            break;
    }

}

function classPlanningGridToolbarClicked(id) {

    var projectId = '9524';//projectsTree.getSelectedItemId();
    if (projectId > 0) {
        switch (id) {
            case 'add':

                var documentId = '19688';//grid_1.getSelectedRowId();
                if (documentId) {
//                        var documentId = grid_1.getSelectedRowId().substring(4);
                    if (lib_id) {

                        window.dhx4.ajax.get("Controller/php/data_libraries.php?action=16&project_id=" + projectId + "&doc_id=" + documentId + "&libraries_id=" + lib_id + "&eid=" + uID, function (r) {
                            var t = null;
                            try {
                                eval("t=" + r.xmlDoc.responseText);
                            } catch (e) {
                            }
                            ;
                            if (t !== null && t.data.response) {
                                dhtmlx.message({title: 'Success', text: t.data.text});
                                classplanningGrid.clearAndLoad('Controller/php/data_libraries.php?action=17&id=' + lib_id, function () {
                                    classplanningGrid.selectRowById(t.data.newId);
                                });
                            } else {
                                dhtmlx.alert({title: 'Error', text: t.data.text});
                            }
                        });
                    } else {
                        dhtmlx.alert({type: "Warning", text: "Please select a Chapter!"});
                    }
                } else {
                    dhtmlx.alert({type: "Warning", text: "Please select a Document!"});
                }

                break;
            case 'delete':

                var row_id = classplanningGrid.getSelectedRowId();
                $.get("Controller/php/projectDocuments.php?action=29&id=" + row_id, function (data) {
                    if (data.bool) {
                        dhtmlx.alert(data.response);
                    } else {
                        if (row_id === null) {
                            dhtmlx.alert("No item selected!");
                        } else {

                            dhtmlx.confirm({
                                title: "Confirm",
                                type: "confirm-warning",
                                text: "Are you sure you  want to delete?",
                                callback: function (y) {
                                    if (y) {
                                        window.dhx4.ajax.get("Controller/php/projectsPlanning.php?action=4&id=" + row_id + "&project_id=" + projectId, function (r) {
                                            var t = null;
                                            try {
                                                eval("t=" + r.xmlDoc.responseText);
                                            } catch (e) {
                                            }
                                            ;
                                            if (t !== null && t.data.response) {
                                                dhtmlx.message({title: 'Success', text: t.data.text});
                                                classplanningGrid.deleteRow(row_id);
                                                classPlanningForm.clear();
                                            } else {
                                                dhtmlx.alert({title: 'Error', text: t.data.text});
                                            }
                                        });
                                    } else {
                                        return false;
                                    }
                                }
                            });

                        }
                    }
                }, "json");

                break;
        }
    }
}

function classplanningGridEdit(stage, id, index, new_value, old_value, cellIndex) {

    var event_id = classplanningGrid.getSelectedRowId();
    var cell = classplanningGrid.cells(id, index);
    if (stage === 2 && !cell.isCheckbox()) {
        if (event_id > 0 || typeof event_id !== 'undefined') {
            var colId = classplanningGrid.getColumnId(index);
            var colType = classplanningGrid.fldSort[index];
            $.get("Controller/php/projectsPlanning.php?action=11&id=" + event_id + "&index=" + index + "&fieldvalue=" + new_value + "&colId=" + colId + "&colType=" + colType, function (data) {
                if (data.data.response) {
                    dhtmlx.message({type: "Success", text: data.data.text});
                    classplanningGrid.updateFromXML('Controller/php/data_libraries.php?action=17&id=' + lib_id, true, true);
                    classPlanningForm.clear();
                    classPlanningForm.load('Controller/php/data_libraries.php?action=18&id=' + event_id, function (id, response) {
                        var rec_type = classPlanningForm.getItemValue('rec_type');
                        for (var i = 0; i < 8; i++) {
                            classPlanningForm.uncheckItem('days_select[' + i + ']');
                        }
                        var s = '' + rec_type + '';
                        s = rec_type.split(",");
                        for (var k = 0; k < s.length; k++) {
                            var d = s[k];
                            classPlanningForm.checkItem('days_select[' + d + ']');
                        }
                        if (classPlanningForm.isItemChecked('variable') == true) {
                            disableCheckBox();
                        } else {
                            classPlanningForm.setItemLabel("label_days", "Select Days");
                        }
                        //load the combo checked values
                        classPlanningEmployeeCombo.clearAll();
                        classPlanningEmployeeCombo.load("Controller/php/recurring.php?action=110&load=1&evt_id=" + event_id);
                    });
                } else
                    dhtmlx.alert({title: 'Error', text: data.data.text});
            }, 'json');
        }
    } else if (stage === 0 && cell.isCheckbox()) {
        return true;
    }
}

function classPlanningGridChecked(id, index, state) {
    var colId = classplanningGrid.getColumnId(index);

    $.post("Controller/php/projectsPlanning.php?action=10", {
        colId: colId,
        id: id,
        nValue: ((state) ? 1 : 0)
    }, function (data) {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
}

function classPlanningGridRowSelect(id, ind) {

    //get the event selected 
    var event_id = id;

    //load form details
    classPlanningForm.load('Controller/php/data_libraries.php?action=18&id=' + event_id, function (id, response) {

        var rec_type = classPlanningForm.getItemValue('rec_type');
        for (var i = 0; i < 8; i++) {
            classPlanningForm.uncheckItem('days_select[' + i + ']');
        }
        var s = '' + rec_type + '';
        s = rec_type.split(",");
        for (var k = 0; k < s.length; k++) {
            var d = s[k];
            classPlanningForm.checkItem('days_select[' + d + ']');
        }
        if (classPlanningForm.isItemChecked('variable') === true) {
            disableCheckBox();
        } else {
            classPlanningForm.setItemLabel("label_days", "Select Days");
        }
        //load the combo checked values
        classPlanningEmployeeCombo.clearAll();
        classPlanningEmployeeCombo.load("Controller/php/projectsPlanning.php?action=29&evt_id=" + event_id);
    });
//    eventReoccurencesGrid.clearAndLoad(path + "/Schedule/Controller/generated_tasks.php?id=" + event_id, function () {});

}

function classPlanningFormToolbarClicked(id) {
    switch (id) {
        case 'save':
            var eventId = classplanningGrid.getSelectedRowId();
            var projectId = '9524';

            if (eventId > 0) {

                var emp_assigned = classPlanningForm.getCombo("emp").getChecked();
                var approved_by = classPlanningForm.getCombo("approved_by").getChecked();

                //master_form_details.setItemValue("emp", emp_assigned);
                classPlanningForm.setItemValue("approved_by", approved_by);

                if (emp_assigned.length < 1) {
                    dhtmlx.alert("Select employee from dropdown!")
                } else {
                    classplanningGridCell.progressOn();
                    classPlanningForm.send("Controller/php/projectsPlanning.php?action=30&approved=" + approved_by + "&eid=" + uID, function (loader, response) {
                        var parsedJSON = eval('(' + response + ')');
                        if (parsedJSON.data.response) {
                            dhtmlx.message({title: 'Success', text: parsedJSON.data.text})
                            classplanningGrid.updateFromXML('Controller/php/data_libraries.php?action=17&id=' + lib_id, true, true, function () {
                                //load the combo checked values
                                classPlanningEmployeeCombo.clearAll();
                                classPlanningEmployeeCombo.load("Controller/php/projectsPlanning.php?action=29&evt_id=" + eventId);
                                classplanningGridCell.progressOff();
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

