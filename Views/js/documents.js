projectDetailsTabbar.addTab('documents', 'New Documents');
var documents = projectDetailsTabbar.cells('documents');
var toc_layout = documents.attachLayout('2E');

var toc_id = null;
var documentsCell = toc_layout.cells('a');
documentsCell.setText('Project Documents');
documentsCell.setHeight(350);
var toolbar_1 = documentsCell.attachToolbar();
toolbar_1.setIconsPath("Views/imgs/");

toolbar_1.loadStruct('<toolbar><item type="button" id="create" text="Create New" img="new.gif" /><item type="separator" id="sep_1" /><item type="button" id="delete" text="Delete" img="deleteall.png" /><item type="separator" id="sep_2" /><item type="button" id="reload" text="Refresh" img="refresh.png" /><item type="separator" id="sep_3" /><item type="button" id="export_to_pdf" text="Export to PDF" img="pdf.png" /><item type="separator" id="sep_4" /><item type="button" id="publish" text="Publish"  enabled="false" img="" /><item type="separator" id="sep_5" /><item type="buttonSelect" id="show" text="Show" img="show.png"  openAll="true" renderSelect="true" mode="select"><item type="button" id="show_all" text="Show All Documents" img="show.png"/><item type="button" id="show_visible" text="Show Visible Documents" img="show.png"/></item><item type="separator" id="sep_9" /><item type="button" id="libraries" text="Libraries" img="" /><item type="separator" id="sep_11" /><item type="button" id="cover_page" text="Cover Page PDF" img="pdf.png" /></toolbar>', function () {
});

toolbar_1.attachEvent("onClick", toolbar_1Clicked);

function toolbar_1Clicked(id) {
    switch (id)
    {
        case 'libraries':
            openClassesWindow();
            break;

        case "create":
            var project_id = projectsTree.getSelectedItemId();
            if (project_id === "") {
                dhtmlx.alert("Please select a project!");
            } else {
                window.dhx4.ajax.post("Controller/php/projectDocuments.php?action=7", "id=" + project_id + "&eid=" + uID, function (r) {
                    var t = null;
                    try {
                        eval("t=" + r.xmlDoc.responseText);
                    } catch (e) {
                    }
                    ;
                    if (t !== null && t.data.response) {
                        dhtmlx.message({title: 'Success', text: t.data.text});
                        grid_1.updateFromXML("Controller/php/projectDocuments.php?action=2&id=" + project_id, true, true, function () {
                            grid_1.selectRowById(t.data.newId);
                        });
                    } else {
                        dhtmlx.alert({title: 'Error', text: t.data.text});
                    }
                });
            }
            break;
        case "delete":

            var row_id = grid_1.getSelectedRowId();

            if (row_id) {

                dhtmlx.confirm({
                    title: "Confirm",
                    type: "confirm-warning",
                    text: "Are you sure you to delete this Option?",
                    callback: function (ok) {
                        if (ok)
                        {
                            $.post("Controller/php/projectDocuments.php?action=8", {id: row_id, project_id: projectsTree.getSelectedItemId()}, function (data) {
                                if (data.data.response) {
                                    grid_1.deleteRow(grid_1.getSelectedRowId());
                                    tocContentIframe.contentWindow.tinymce.activeEditor.setContent("");
                                    tocHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent("");
                                    toc_form.clear();
                                    grid_3.clearAll();
                                    tocHistoryGrid.clearAll();

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
        case "reload":
            var project_id = projectsTree.getSelectedItemId();
            if (project_id > 0) {
                documentsCell.progressOn();
                grid_1.clearAndLoad("Controller/php/projectDocuments.php?action=2&id=" + project_id, function () {
                    documentsCell.progressOff();
                });
            } else {
                dhtmlx.alert("Please select a project!");
            }

            break;

        case 'publish':

            var row_id = grid_1.getSelectedRowId();
            if (row_id !== null) {
                var doc_id = grid_1.cells(row_id, 7).getValue();
                var lang_id = grid_1.cells(row_id, 6).getValue();
                var report_id = row_id.substring(4);
//                var content = projectDocumentsContentIframe.contentWindow.tinymce.activeEditor.getContent();

                $.post("Controller/projectDocuments.php?action=28", {id: report_id, doc_id: doc_id, lang_id: lang_id, content: content, eid: uID}, function (data)
                {
                    if (data.data.response) {
                        dhtmlx.message({title: 'Success', text: data.data.text});
                    } else {
                        dhtmlx.alert({title: 'Error', text: data.data.text});
                    }
                }, 'json');
            }

            break;

        case "document_number":
            var resumeFormWidth = myWidth * 0.8;
            var resumeFormData =
                    [{type: "settings", position: "label-left", labelWidth: resumeFormWidth * 0.08, inputWidth: resumeFormWidth * 0.2, offsetTop: 10, offsetLeft: 10},
                        {type: "hidden", label: "Applicants Details ", className: "formbox", width: resumeFormWidth * 0.57, list:
                                    [{type: "input", label: "Document No.", name: "document", value: ""},
                                        {type: "hidden", name: "report", value: ""},
                                        {type: "button", name: "submit", value: "submit", offsetLeft: 100}
                                    ]}
                    ];

            var row_id = grid_1.getSelectedRowId();

            if (row_id === null) {
                dhtmlx.alert("Please Select a Report!");
            } else {
                row_id = row_id.substring(4);
                var popupMainWindow = new dhtmlXWindows();
                var popupWindow = popupMainWindow.createWindow("resumes_win", 0, 0, myWidth * 0.25, myHeight * 0.25);
                popupWindow.center();
                popupWindow.setText("Enter Document Number");
                var addResumeForm = popupWindow.attachForm(resumeFormData);

                addResumeForm.attachEvent("onButtonClick", function () {

                    addResumeForm.setItemValue("report", row_id);

                    addResumeForm.send("Controller/php/projectDocuments.php?action=9", function (loader, response)
                    {
                        popupWindow.hide();
                        var parsedJSON = eval('(' + response + ')');
                        if (parsedJSON.data.response) {
                            dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                        } else
                            dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                    });
                });
            }
            break;

        case 'cover_page':

            var projectId = projectsTree.getSelectedItemId();
            if (projectId > 0) {
                var documentId = grid_1.getSelectedRowId();
                if (documentId) {
                    var documentId = grid_1.getSelectedRowId().substring(4);
                    var url = "Controller/tcpdf/coverpage/cover_page.php?project_id=" + projectId + "&doc_id=" + documentId;
                    window.open(url, 'Download');
//                    window.open(url, 'download_window', 'toolbar=0,location=no,directories=0,status=0,scrollbars=0,resizeable=0,width=1,height=1,top=0,left=0');
//window.focus();

                } else {
                    dhtmlx.alert({type: "Warning", text: "Please select a Document!"});
                }
            } else {
                dhtmlx.alert({type: "Warning", text: "Please select a Project and a Document"});
            }

            break;

        case 'export_to_pdf':

            var row_id = grid_1.getSelectedRowId().substring(4);
            if (row_id > 0) {
                var url = "Controller/php/generate_pdf.php?id=" + row_id;
//                var url = "https://213.201.143.93/pdflib/php/generate_pdf.php?id=" + row_id;
                window.open(url);
            } else {
                dhtmlx.alert("Please Select a Report!");
            }
            break;

        case 'show_all':

            var project_id = projectsTree.getSelectedItemId();
            if (project_id > 0) {
                documentsCell.progressOn();
                grid_1.clearAndLoad("Controller/php/projectDocuments.php?action=18&id=" + project_id, function () {
                    documentsCell.progressOff();
                });
            } else {
                dhtmlx.alert("Please select a project!");
            }
            break;
        case 'show_visible':

            var project_id = projectsTree.getSelectedItemId();
            if (project_id > 0) {
                documentsCell.progressOn();
                grid_1.clearAndLoad("Controller/php/projectDocuments.php?action=2&id=" + project_id, function () {
                    documentsCell.progressOff();
                });
            } else {
                dhtmlx.alert("Please select a project!");
            }
            break;
    }
}

var grid_1 = documentsCell.attachGrid();
grid_1.setSkin('dhx_web');
grid_1.setImagesPath('dhtmlxsuite4/skins/web/imgs/');

grid_1.setHeader(["ID", "Employee", "Date", "Subject", "Category", "Author", "Language", "Explorer ID", "Template ID", "Accordion", "Visible", "ID2", "Default", "Char"], null, ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:center;", "text-align:left;", "text-align:left;", "text-align:left;"]);
grid_1.setColumnIds('Report_ID,Report_Employee_ID,Report_Date,Report_Subject,category_id,Report_Author,language_id,explorer_id,template_id,accordion,visible_in_projects,proj_doc_id,default_report,char');
grid_1.attachHeader("#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#numeric_filter,#numeric_filter,#master_checkbox,#master_checkbox,,,", ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:center;", "text-align:center;", "text-align:left;", "text-align:center;", "text-align:left;"]);
grid_1.setColAlign("left,left,left,left,left,left,left,right,right,center,center,center,center,right");
grid_1.setColTypes("ro,combo,ro,ed,combo,ro,combo,ed,ed,ch,ch,ro,ch,ro");
grid_1.setInitWidthsP("5,10,10,*,10,10,8,6,6,6,5,0,4,6");
grid_1.setColSorting('str,str,date,str,str,str,int,int,int,int,int,int,int,str');
grid_1.enableCellIds(true);
//grid_1.enableDragAndDrop(true);
grid_1.setDateFormat("%Y-%m-%d %H:%i:%s");
grid_1.attachEvent("onSelectStateChanged", grid_1StateChanged);//onRowSelect
grid_1.attachEvent("onEditCell", grid_1EditCell);
grid_1.setColumnHidden(8, true);
grid_1.setColumnHidden(12, true);
grid_1.init();

grid_1EmployeeCombo = grid_1.getColumnCombo(1);
grid_1EmployeeCombo.setSkin("dhx_web");
grid_1EmployeeCombo.enableFilteringMode(true);
grid_1EmployeeCombo.load("Controller/php/projectDocuments.php?action=1");

grid_1LanguageCombo = grid_1.getColumnCombo(6);
grid_1LanguageCombo.setSkin("dhx_web");
grid_1LanguageCombo.enableFilteringMode(true);
grid_1LanguageCombo.load("Controller/php/projectDocuments.php?action=24");

grid_1CategoryCombo = grid_1.getColumnCombo(4);
grid_1CategoryCombo.setSkin("dhx_web");
grid_1CategoryCombo.enableFilteringMode(true);
grid_1CategoryCombo.load("Controller/php/projectDocuments.php?action=26");

grid_1.attachEvent("onBeforeDrag", function (id) {

    firstId = true;

    var rowIds = grid_1.getSelectedRowId();
    if (rowIds !== null) {
        var rowIdsArray = rowIds.split(",");
        if (rowIdsArray.length > 1) {
            selectedDragId = grid_1.getSelectedRowId();
        } else {
            selectedDragId = id;
        }
    } else {
        selectedDragId = id;
    }
    return true;
});

//grid_1.attachEvent("onXLE", function (grid_obj, count) {
//    if (selected_doc_id > 0) {
//        grid_1.selectRowById('doc_' + selected_doc_id);
//    }
//    selected_doc_id = null;
//});

function grid_1StateChanged(id, ind) {

    toc_form.clear();

    var checked_branches = toc_branchCombo.getChecked();
    $.each(checked_branches, function (i) {
        var optId = checked_branches[i];
        var index = toc_branchCombo.getIndexByValue(optId);
//        var props = toc_branchCombo.getOption(checked_branches[i]);console.log(props);
        toc_branchCombo.setChecked(index, false);
    });

    var checked_employees = toc_employeeCombo.getChecked();
    $.each(checked_employees, function (i) {
        var optId = checked_employees[i];
        var index = toc_employeeCombo.getIndexByValue(optId);
        toc_employeeCombo.setChecked(index, false);
    });

    if (is_moodle) {

        grid_3.kidsXmlFile = "Controller/php/courses.php?action=4&course=" + course_id + '&server=' + server_id;
        grid_3.clearAndLoad('Controller/php/courses.php?action=4&course=' + course_id + '&server=' + server_id, function () {

            var domainname = getDomainName();
            grid_3PreviousCombo.clearAll();
            grid_3NextCombo.clearAll();

            $.getJSON(domainname + '/data_content.php?action=6&course=' + course_id, function (results) {
                grid_3PreviousCombo.addOption(results);
                grid_3NextCombo.addOption(results);
            });

        });

        return;
    }

    id = id.split("_")[1];

    toc_form.load("Controller/php/data_toc.php?action=9&id=" + id, function () {

        $.getJSON('Controller/php/data_toc.php?action=15&id=' + id, function (results) {

            var text = '';
            $.each(results.options, function (key, value) {
                var index = toc_employeeCombo.getIndexByValue(value.id);
                toc_employeeCombo.setChecked(index, true);
                text = text + value.name + ',';
            });
//            toc_employeeCombo.setComboText(text);
        });
        $.getJSON('Controller/php/data_toc.php?action=25&id=' + id, function (results) {

            var text = '';
            $.each(results.options, function (key, value) {
                var index = toc_branchCombo.getIndexByValue(value.id);
                toc_branchCombo.setChecked(index, true);
                toc_branchCombo.selectOption(index);
                text = text + value.name + ',';
            });

            text = text.substr(0, text.length - 1);

//            alert(text);

//            toc_branchCombo.setComboText(text);
        });
    });

    grid_3.clearAndLoad("Controller/php/data_toc.php?action=4&doc_id=" + id);
}

var tocCell = toc_layout.cells('b');
tocCell.setHeight(myHeight * 0.6);
tocCell.setText('Table Of Content');
var tabbar_2 = tocCell.attachTabbar();
tabbar_2.addTab('tab_3', 'Document Details');
var tab_3 = tabbar_2.cells('tab_3');
tab_3.setActive();
var layout_1 = tab_3.attachLayout('1C');

var cell_1 = layout_1.cells('a');
cell_1.hideHeader();
var toolbar_3 = cell_1.attachToolbar();
toolbar_3.setIconsPath('./codebase/imgs/');

toolbar_3.loadStruct('<toolbar><item type="button" id="save" text="Save" /></toolbar>', function () {});
toolbar_3.attachEvent('onClick', toolbar_3Clicked);
function toolbar_3Clicked(id)
{
    switch (id)
    {
        case 'save':
            var docId = grid_1.getSelectedRowId();
            if (docId !== null) {
                docId = docId.substring(4);
                documentsCell.progressOn();
                toc_form.send("Controller/php/data_toc.php?action=8&id=" + docId, function (loader, response)
                {
                    documentsCell.progressOff();
                    var parsedJSON = eval('(' + response + ')');
                    if (parsedJSON.data.response) {
                        dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                        grid_1.updateFromXML('Controller/php/projectDocuments.php?action=2&id=' + projectsTree.getSelectedItemId());
                    } else {
                        dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                    }
                });

            } else {
                dhtmlx.alert("No Document selected!");
            }

            break;
    }
}


var toc_formdata = [
    {type: "settings", labelWidth: 100, inputWidth: 400, offsetLeft: "20", offsetTop: "10"},
    {type: "input", name: "goal", label: "Goal", rows: "4"},
    {type: "input", name: "scope", label: "Scope", rows: "10"},
    {type: "combo", name: "supervisor", label: "Supervisor", inputWidth: 300, required: true},
    {type: "combo", name: "employee", label: "Employee", comboType: "checkbox", inputWidth: 300, required: true},
    {type: "combo", name: "branch", label: "Branch", comboType: "checkbox", inputWidth: 300, required: true},
    {type: "combo", name: "category", label: "Category", inputWidth: 300},
    {type: "combo", name: "doc_frequency", label: "Frequency", inputWidth: 300, options: [
            {value: "0", text: "", selected: true},
            {value: "1", text: "Every Week"},
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
    {type: "input", name: "doc_input", label: "Input", rows: "2"},
    {type: "input", name: "doc_output", label: "Output", rows: "2"},
    {type: "input", name: "explorer_id", label: "Procedure"}
];

var toc_form = cell_1.attachForm(toc_formdata);

var toc_CategoryCombo = toc_form.getCombo("category");
toc_CategoryCombo.enableFilteringMode(true);
toc_CategoryCombo.allowFreeText(true);
toc_CategoryCombo.load("Controller/php/projectDocuments.php?action=26");

var toc_employeeCombo = toc_form.getCombo("employee");
toc_employeeCombo.enableFilteringMode(true);
toc_employeeCombo.allowFreeText(true);
toc_employeeCombo.load("Controller/php/projectsPlanning.php?action=2");

toc_employeeCombo.attachEvent("onCheck", function (value, state) {
    var docId = grid_1.getSelectedRowId();
    var employeeId = value;

    $.post("Controller/php/data_toc.php?action=14", {docId: docId, employeeId: employeeId, nValue: ((state) ? 1 : 0), eid: uID}, function (data)
    {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');

    return true;
});

var toc_branchCombo = toc_form.getCombo("branch");
toc_branchCombo.enableFilteringMode(true);
toc_branchCombo.allowFreeText(true);
toc_branchCombo.load("Controller/php/data_toc.php?action=23");

toc_branchCombo.attachEvent("onCheck", function (value, state) {
    var docId = grid_1.getSelectedRowId();
    var branchId = value;

    $.post("Controller/php/data_toc.php?action=24", {docId: docId, branchId: branchId, nValue: ((state) ? 1 : 0), eid: uID}, function (data)
    {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');

    return true;
});

var toc_supervisorCombo = toc_form.getCombo("supervisor");
toc_supervisorCombo.enableFilteringMode(true);
toc_supervisorCombo.load("Controller/php/projectsPlanning.php?action=2");

tabbar_2.addTab('tab_4', 'Table of Content');
var tab_4 = tabbar_2.cells('tab_4');
tab_4.setActive();

var layout_2 = tab_4.attachLayout('2U');

var cell_4 = layout_2.cells('a');
cell_4.setWidth(myWidth * 0.3);
cell_4.hideHeader();
var toolbar_2 = cell_4.attachToolbar();
toolbar_2.setIconsPath('Views/imgs/');

toolbar_2.loadStruct('<toolbar><item type="button" id="new" text="New" img="new.gif"/><item type="separator" id="button_separator_6" /><item type="button" id="delete" text="Delete"  img="deleteall.png"/><item type="separator" id="button_separator_7" /><item type="button" id="up" text="Up"  img="up.png"/><item type="separator" id="button_separator_8" /><item type="button" id="down" text="Down"  img="down.png"/><item type="separator" id="button_separator_9" /><item type="buttonTwoState" id="details" text="Details On" /><item type="separator" id="sep_8" /><item type="button" id="save_all" text="Save All" img="save.gif" /></toolbar>', function () {});
toolbar_2.attachEvent('onClick', toolbar_2Clicked);

toolbar_2.attachEvent("onStateChange", function (id, state) {
    if (id === 'details') {
        var text = state ? 'Details Off' : 'Details On';
        toolbar_2.setItemText(id, text);
        if (state) {
            for (var i = 3; i <= 7; i++) {
                grid_3.setColumnHidden(i, false);
            }
        } else {
            for (var i = 3; i <= 7; i++) {
                grid_3.setColumnHidden(i, true);
            }
        }
    }
});

function toolbar_2Clicked(id)
{
    switch (id)
    {
        case 'new':
            var docId = grid_1.getSelectedRowId();
            if (docId !== null) {
                docId = docId.substring(4);
                var parentId = grid_3.getSelectedRowId();
                window.dhx4.ajax.get("Controller/php/data_toc.php?action=1&parent_id=" + parentId + "&doc_id=" + docId, function (r) {
                    var t = null;
                    try {
                        eval("t=" + r.xmlDoc.responseText);
                    } catch (e) {
                    }
                    ;
                    if (t !== null && t.data.response) {
                        dhtmlx.message({title: 'Success', text: t.data.text});
                        grid_3.clearAndLoad("Controller/php/data_toc.php?action=4&doc_id=" + docId, function () {
                            grid_3.selectRowById(t.data.row_id);
                        });
                    } else {
                        dhtmlx.alert({title: 'Error', text: t.data.text});
                    }
                });
            } else {
                dhtmlx.alert('No Document Selected!');
            }
            break;

        case 'delete':
            var rowId = grid_3.getSelectedRowId();
            if (rowId === null) {
                dhtmlx.alert("No item selected!");
            } else {
                var hasChildren = grid_3.hasChildren(rowId)
                if (hasChildren) {
                    dhtmlx.alert("Row has child items!")
                } else {
                    dhtmlx.confirm({
                        title: "Confirm",
                        type: "confirm-warning",
                        text: "Are you sure you  want to delete?",
                        callback: function (y) {
                            if (y)
                            {
                                $.get("Controller/php/data_toc.php?action=3&id=" + rowId, function (data) {
                                    if (data.data.response) {
                                        dhtmlx.message({title: 'Success', text: data.data.text});
                                        grid_3.deleteRow(rowId);
                                    } else {
                                        dhtmlx.alert({title: 'Error', text: data.data.text});
                                    }
                                }, 'json');
                            } else
                            {
                                return false;
                            }
                        }});
                }
            }
            break;

        case 'up':
            var docId = grid_1.getSelectedRowId();
            docId = docId.substring(4);
            toc_id = grid_3.getSelectedRowId();

            if (toc_id == 'undefined' || toc_id == null) {
                dhtmlx.alert('Please select a row to move!');
                return false;
            } else {
                var parentId = grid_3.getParentId(toc_id);
                var sortId = grid_3.cells(toc_id, 2).getValue();

                $.get("Controller/php/data_toc.php?action=5&itemId=" + toc_id + "&parentId=" + parentId + "&sortId=" + sortId + "&direction=" + id, function (data)
                {
                    if (data.data.success) {
                        grid_3.updateFromXML("Controller/php/data_toc.php?action=4&doc_id=" + docId);
                        grid_3.moveRowUp(grid_3.getSelectedId());
                    }
                }, "json");
            }
            break;
        case 'down':
            docId = grid_1.getSelectedRowId();
            docId = docId.substring(4);
            toc_id = grid_3.getSelectedRowId();

            if (toc_id == 'undefined' || toc_id == 0) {
                dhtmlx.alert('Please select a row to move!');
                return false;
            } else {
                var parentId = grid_3.getParentId(toc_id);
                var sortId = grid_3.cells(toc_id, 2).getValue();

                $.get("Controller/php/data_toc.php?action=5&itemId=" + toc_id + "&parentId=" + parentId + "&sortId=" + sortId + "&direction=" + id, function (data)
                {

                    if (data.data.success) {
                        grid_3.updateFromXML("Controller/php/data_toc.php?action=4&doc_id=" + docId);
                        grid_3.moveRowDown(grid_3.getSelectedId());
                    }

                }, "json");
            }
            break;

        case 'save_all':
            docId = grid_1.getSelectedRowId();
            docId = docId.substring(4);

            $.get("Controller/php/data_toc.php?action=22&id=" + docId, function (data) {
                if (data.data.response) {
                    dhtmlx.message({title: 'Success', text: data.data.text});
                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, "json");

            break;
    }
}

function grid_3ContextMenuSelect(menuitemId, type)
{
    var docId = grid_1.getSelectedRowId().split('_')[1];
//    docId = docId.substring(4);
    switch (menuitemId)
    {
        case 'new_parent':
            //check if row is selected
            if (docId === null) {
                dhtmlx.alert("No Document Selected!");
            } else {
                window.dhx4.ajax.get("Controller/php/data_toc.php?action=1&parent_id=0&doc_id=" + docId, function (r) {
                    var t = null;
                    try {
                        eval("t=" + r.xmlDoc.responseText);
                    } catch (e) {
                    }
                    ;
                    if (t !== null && t.data.response) {
                        dhtmlx.message({title: 'Success', text: t.data.text});
                        grid_3.updateFromXML("Controller/php/data_toc.php?action=4&doc_id=" + docId, true, true, function () {
                            grid_3.selectRowById(t.data.row_id);
                        });
                    } else {
                        dhtmlx.alert({title: 'Error', text: t.data.text});
                    }
                });
            }
            break;

        case 'new_child':
            //check if row is selected
            if (docId === null) {
                dhtmlx.alert("No Document Selected!");
            } else {
                var parentId = grid_3.getSelectedRowId();
                window.dhx4.ajax.get("Controller/php/data_toc.php?action=1&parent_id=" + parentId + "&doc_id=" + docId, function (r) {
                    var t = null;
                    try {
                        eval("t=" + r.xmlDoc.responseText);
                    } catch (e) {
                    }
                    ;
                    if (t !== null && t.data.response) {
                        dhtmlx.message({title: 'Success', text: t.data.text});
                        grid_3.updateFromXML("Controller/php/data_toc.php?action=4&doc_id=" + docId, true, true, function () {
                            grid_3.openItem(parentId);
                            grid_3.selectRowById(t.data.row_id);
                        });
                    } else {
                        dhtmlx.alert({title: 'Error', text: t.data.text});
                    }
                });
            }
            break;

        case 'topic':

            if (course_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select a course.",
                    title: "Error!"
                });
                return;
            }

            var count = 0;
            grid_3.forEachRow(function (id) {
                if (grid_3.getLevel(id) === 0) {
                    count++;
                }
            });

            createTopic(course_id, count);
            break;

            //add page module
        case 'page':

//            var section_id = grid_3.cells(rowId, 2).getValue();
//            createTopic(tree_1.getSelectedItemId(), section_id, rowId);

            var data = grid_3.contextID.split("_"); //rowId_colInd
            var rowId = data[0] + '_' + data[1];
            var section_id = grid_3.cells(rowId, 3).getValue();

            createTopic(course_id, section_id, rowId);

            break;

        case 'lesson':

            var data = grid_3.contextID.split("_"); //rowId_colInd
            var rowId = data[0] + '_' + data[1];
            var section_id = grid_3.cells(rowId, 3).getValue();
            createLesson(section_id);
            break;

        case 'content_page':

            var data = grid_3.contextID.split("_"); //rowId_colInd
            data.pop();
            console.log(data);

            var rowId = data.join("_");
            var level = grid_3.getLevel(rowId);

            if (level == 1) {
                createLessonPage(data[1], data[2]);
            } else {
                data = grid_3.getParentId(rowId).split("_");
                createLessonPage(data[1], data[2], grid_3.contextID.split("_")[1]);
            }

            break;

        case 'quiz':

            var data = grid_3.contextID.split("_"); //rowId_colInd
            data.pop();
            console.log(data);

            var rowId = data.join("_");
            var level = grid_3.getLevel(rowId);

            if (level == 1) {
                createLessonQuiz(data[1], data[2]);
            } else {
                data = grid_3.getParentId(rowId).split("_");
                createLessonQuiz(data[1], data[2], grid_3.contextID.split("_")[1]);
            }

            break;

        case 'delete':
            var rowId = grid_3.getSelectedRowId();
            if (rowId === null) {
                dhtmlx.alert("No item selected!");
            } else {
                var hasChildren = grid_3.hasChildren(rowId)
                if (hasChildren) {
                    dhtmlx.alert("Row has child items!")
                } else {
                    dhtmlx.confirm({
                        title: "Confirm",
                        type: "confirm-warning",
                        text: "Are you sure you  want to delete?",
                        callback: function (y) {
                            if (y)
                            {
                                $.get("Controller/php/data_toc.php?action=3&id=" + rowId, function (data) {
                                    if (data.data.response) {
                                        dhtmlx.message({title: 'Success', text: data.data.text});
                                        grid_3.deleteRow(rowId);
                                    } else {
                                        dhtmlx.alert({title: 'Error', text: data.data.text});
                                    }
                                }, 'json');
                            } else
                            {
                                return false;
                            }
                        }});
                }
            }
            break;
    }
}

var struct = [
    {id: "new", text: "New", items: [
            {id: "new_parent", text: "Root Item", img: "new.gif"},
            {id: "new_child", text: "Child Item", img: "new.gif"},
            {id: "topic", text: "Topic", img: "new.gif"},
            {id: "page", text: "Page", img: "new.gif"},
            {id: "lesson", text: "Lesson", img: "new.gif"},
            {id: "content_page", text: "Content Page", img: "new.gif"},
            {id: "quiz", text: "Question Page", img: "new.gif"},
        ]},
    {id: "delete", text: "Delete", img: "deleteall.png"}
];

//context menu add items
var grid_3ContextMenu = new dhtmlXMenuObject();
grid_3ContextMenu.setIconsPath('Views/imgs/');
grid_3ContextMenu.renderAsContextMenu();
grid_3ContextMenu.attachEvent("onClick", grid_3ContextMenuSelect);
//grid_3ContextMenu.loadStruct("Controller/php/data_toc.php?action=2");
grid_3ContextMenu.loadStruct(struct);



status_opts = [
    ["To do", "To do"],
    ["Planned", "Planned"],
    ["Done", "Done"],
    ["Verified", "Verified"],
    ["Implemented", "Implemented"]
];

var grid_3 = cell_4.attachGrid();
grid_3.setSkin('dhx_web');
grid_3.setImagesPath('dhtmlxsuite4/skins/web/imgs/');

grid_3.setHeader(["Chapter", "Title", "Type", "Topics", "Prevoius Page", "Next Page", "Comments", "Date", "Employee", "Status", "Visible"]);
grid_3.setColTypes("ro,tree,ro,ed,combo,combo,ed,ro,ro,combo,ch");
grid_3.setColSorting('str,str,str,str,str,str,str,str,str,str,int');
grid_3.enableCellIds(true);
grid_3.setColumnIds('chapter,title,sort,topics,prevpageid,nextpageid,comments,date,employee,status,visible');
grid_3.setInitWidthsP('10,*,15,*,15,15,*,*,*,*,10');
grid_3.attachEvent('onEditCell', grid_1EditCell);
grid_3.enableDragAndDrop(true);
grid_3.enableContextMenu(grid_3ContextMenu);
grid_3.setColumnHidden(3, true);

for (var i = 6; i <= 9; i++) {
    grid_3.setColumnHidden(i, true);
}

grid_3.init();

var grid_3StatusCombo = grid_3.getColumnCombo(7);
grid_3StatusCombo.setSkin("dhx_web");
grid_3StatusCombo.enableFilteringMode(true);
grid_3StatusCombo.addOption(status_opts);

var grid_3PreviousCombo = grid_3.getColumnCombo(4);
grid_3PreviousCombo.setSkin("dhx_web");
grid_3PreviousCombo.enableFilteringMode(true);

var grid_3NextCombo = grid_3.getColumnCombo(5);
grid_3NextCombo.setSkin("dhx_web");
grid_3NextCombo.enableFilteringMode(true);

grid_3.attachEvent("onBeforeContextMenu", function (id, ind, obj) {
//    if (grid_3.getLevel(id) !== 0)
//        return false;
    if (is_moodle) {

        var level = grid_3.getLevel(id);
        if (level == 0) {
            var arr = ['lesson', 'page', 'topic'];
            manipulateMenu(arr);

        } else {

            var arr = ['content_page', 'quiz'];
            manipulateMenu(arr);
        }

    } else {

        var arr = ['new_parent', 'new_child'];
        manipulateMenu(arr);
    }
    return true;
});

function manipulateMenu(arr) {

    grid_3ContextMenu.forEachItem(function (itemId) {

        var parentId = grid_3ContextMenu.getParentId(itemId);

        if (parentId === 'new') {
            if (arr.indexOf(itemId) !== -1) {
                grid_3ContextMenu.showItem(itemId);
            } else {
                grid_3ContextMenu.hideItem(itemId);
            }
        }
    });
}

function grid_1EditCell(stage, id, index, new_value) {

    if (is_moodle) {

        var cell = grid_3.cells(id, index);
        if (stage === 2 && !cell.isCheckbox()) {
            var row_id = grid_3.getSelectedRowId();
            if (row_id > 0 || typeof row_id !== 'undefined') {
                var colId = grid_3.getColumnId(index);
                var colType = grid_3.fldSort[index];

                var domainname = getDomainName();
                var token = getMoodleToken();

                var level = grid_3.getLevel(row_id);

                if (level === 0) {

                    var functionname = 'core_update_inplace_editable';
                    var serverurl = domainname + '/webservice/rest/server.php';

                    var data = {
                        wstoken: token,
                        wsfunction: functionname,
                        moodlewsrestformat: 'json',
                        component: 'format_topics',
                        itemtype: 'sectionname',
                        itemid: id.split("_")[1],
                        value: new_value
                    };

                    $.ajax({
                        type: 'POST',
                        data: data,
                        url: serverurl,
                        dataType: 'json',
                        error: function () {
                            dhtmlx.alert({title: 'Warning', text: 'An error has occurred'});
                        },
                        success: function (data) {
                            dhtmlx.message({title: 'Success', text: 'Successfully Updated'});
                        }
                    });
                } else if (level === 1) {

                    var serverurl = domainname + '/moosh.php?action=3&course=' + course_id;
                    var postData = {id: grid_3.cells(row_id, 3).getValue(), index: index, fieldvalue: new_value, colId: colId, colType: colType};

                    $.post(serverurl, postData, function (data)
                    {
                        if (data.data.response) {
                            dhtmlx.message({title: 'Success', text: data.data.text});
                        } else {
                            dhtmlx.alert({title: 'Error', text: data.data.text});
                        }
                    }, 'json');

                } else {

                    var modname = grid_3.cells(grid_3.getParentId(row_id), 2).getValue();
                    var post_id = null;

                    if (modname == 'lesson') {

                        post_id = row_id.split("_")[1];
                        var serverurl = domainname + '/data_content.php?action=2';

                        $.post(serverurl, {
                            id: post_id,
                            fieldvalue: new_value
                        }, function (data) {
                            if (data.data.response) {
                                dhtmlx.message({title: 'Success', text: data.data.text});
                            } else {
                                dhtmlx.alert({title: 'Error', text: data.data.text});
                            }
                        }, 'json');
                    }
                }
            }
            return true;
        } else if (stage === 0 && cell.isCheckbox()) {
            return true;
        }

    } else {


        var docId = grid_1.getSelectedRowId();
        docId = docId.substring(4);

        var cell = grid_3.cells(id, index);
        var row_id = grid_3.getSelectedRowId();

        var colId = grid_3.getColumnId(index);
        var colType = grid_3.fldSort[index];

        if (stage === 2 && !cell.isCheckbox()) {

            if (row_id > 0 || typeof row_id !== 'undefined') {

                $.post("Controller/php/data_toc.php?action=10", {id: row_id, index: index, fieldvalue: new_value, colId: colId, colType: colType}, function (data)
                {
                    if (data.data.response) {
                        dhtmlx.message({title: 'Success', text: data.data.text});
                        grid_3.updateFromXML("Controller/php/data_toc.php?action=4&doc_id=" + docId, true, true);
                        toc_form.load("Controller/php/data_toc.php?action=9&id=" + id, function () {
//                        toc_employeeCombo.setComboText('text');
                        });
                    } else {
                        dhtmlx.alert({title: 'Error', text: data.data.text});
                    }
                }, 'json');

            }

        } else if (colId == "visible") {

            cell = grid_3.cells(id, index);
            new_value = (cell.getValue() == '1') ? '0' : '1';

            $.post("Controller/php/data_toc.php?action=10", {id: id, index: index, fieldvalue: new_value, colId: colId, colType: colType}, function (data) {
                if (data.data.response) {

                    cell.setValue(new_value);

                    dhtmlx.message({title: 'Success', text: data.data.text});
                    grid_3.updateFromXML("Controller/php/data_toc.php?action=4&doc_id=" + docId, true, true);
                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');

        } else if (stage === 0 && cell.isCheckbox()) {
            return true;
        }

    }
}

grid_3.attachEvent('onDrop', function (sId, tId, dId, sObj, tObj, sCol, tCol) {

    tId = tId > 0 ? tId : 0;
    var docId = grid_1.getSelectedRowId();
    docId = docId.substring(4);

    $.get('Controller/php/data_toc.php?action=13&sId=' + sId + '&tId=' + tId + "&doc_id=" + docId, function (data) {
        if (data.data.response)
        {
            grid_3.updateFromXML("Controller/php/data_toc.php?action=4&doc_id=" + docId, true, true);
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
});

grid_3.attachEvent('onSelectStateChanged', function (id, ind) {

    if (is_moodle) {

        if (grid_3.getLevel(id) == 1 && grid_3.cells(id, 2).getValue() == 'lesson') {
            tab_moodle.show();
            tab_6.hide();
            tabMoodleCell.progressOn();
            tabMoodleCell.attachURL('Controller/php/moodle_login.php?action=1&lesson=' + id.split("_")[1]);
        } else {

            tab_6.show();
            tab_6.setActive();
            tab_moodle.hide();

            var modname = grid_3.cells(grid_3.getParentId(id), 2).getValue();

            if (modname === 'lesson') {

                var lesson_id = grid_3.cells(grid_3.getParentId(id), 3).getValue();
                
                grid_page_questions.clearAndLoad("Controller/php/data_questions.php?action=4&page_id=" + id.split("_")[1]);



//                tabMoodleCell.progressOn();
//                tabMoodleCell.attachURL('Controller/php/moodle_login.php?action=2&lesson=' + lesson_id.split("_")[1] + '&page=' + id.split("_")[1]);

//                var serverurl = getDomainName() + '/data_content.php?action=4';

                $.getJSON('Controller/php/courses.php?action=6&module=' + id.split("_")[1] + '&server=' + server_id + '&lesson=' + lesson_id, function (results) {

//                $.getJSON(serverurl + '&module=' + id.split("_")[1] + '&lesson=' + lesson_id, function (results) {
                    if (results.item) {
//                        var content = $(results.item.content).html();
                        tocContentIframe.contentWindow.tinymce.activeEditor.setContent(results.item.content);
                    } else {
                        tocContentIframe.contentWindow.tinymce.activeEditor.setContent('');
                    }
                });

            } else {

                $.getJSON('Controller/php/courses.php?action=5&course=' + course_id + '&module=' + id.split("_")[1] + '&server=' + server_id, function (results) {

                    if (results.item) {
                        tocContentIframe.contentWindow.tinymce.activeEditor.setContent(results.item.content);
                    } else {
                        tocContentIframe.contentWindow.tinymce.activeEditor.setContent('');
                    }
                });
            }
        }
    } else {

        tab_6.show();
        tab_6.setActive();
        tab_moodle.hide();

        toc_id = id;
        tocContentCell.progressOn();
        window.dhx4.ajax.get("Controller/php/data_toc.php?action=7&id=" + id + "&doc_id=" + grid_1.getSelectedRowId().substring(4), function (r) {
            tocContentCell.progressOff();
            var t = null;
            try {
                eval("t=" + r.xmlDoc.responseText);
            } catch (e) {
            }
            ;
            if (t !== null && t.content !== null) {
                tocContentIframe.contentWindow.tinymce.activeEditor.setContent(t.content);
//            document_viewer.attachURL("document_viewer_content.php?id=" + id);
            }
        });
        form_4.clear();
        form_4.load("Controller/php/data_toc.php?action=11&id=" + id);

        toc_planningGrid.clearAndLoad('Controller/php/data_toc.php?action=17&id=' + id);
        tocHistoryGrid.clearAndLoad("Controller/php/data_toc.php?action=21&id=" + id);
        comments_grid.clearAndLoad("Controller/php/data_toc.php?action=32&id=" + id);
    }
});

var cell_5 = layout_2.cells('b');
cell_5.setText('Content');
var tabbar_3 = cell_5.attachTabbar();
tabbar_3.addTab('tab_6', 'Chapter Content');
var tab_6 = tabbar_3.cells('tab_6');
tab_6.setActive();

var tocEditorLayout = tab_6.attachLayout('1C');

var tocContentIframe;
var tocContentCell = tocEditorLayout.cells('a');
tocContentCell.hideHeader();
tocContentCell.attachURL("Views/frames/toc_content.php", false,
        {report_content: '', height: (tocContentCell.getHeight()) / 1.65});
tocEditorLayout.attachEvent("onContentLoaded", function (id) {
    tocContentIframe = tocEditorLayout.cells(id).getFrame();
});

tabbar_3.addTab('tab_moodle', 'Chapter Content');
var tab_moodle = tabbar_3.cells('tab_moodle');
tab_moodle.hide();

var tabMoodleLayout = tab_moodle.attachLayout('1C');
var tabMoodleCell = tabMoodleLayout.cells('a');
tabMoodleCell.hideHeader();

tabMoodleLayout.attachEvent("onContentLoaded", function (id) {
    tabMoodleCell.progressOff();
});

tabbar_3.addTab('tab_7', 'Chapter Details');
var tab_7 = tabbar_3.cells('tab_7');
var toolbar_4 = tab_7.attachToolbar();
toolbar_4.setIconsPath('./codebase/imgs/');

toolbar_4.loadStruct('<toolbar><item type="button" id="save" text="Save" /></toolbar>', function () {});
toolbar_4.attachEvent("onClick", function (id) {
    if (id === 'save') {
        var chapterId = grid_3.getSelectedRowId();
        if (chapterId !== null) {
            tab_7.progressOn();
            form_4.send("Controller/php/data_toc.php?action=12&id=" + chapterId, function (loader, response)
            {
                tab_7.progressOff();
                var parsedJSON = eval('(' + response + ')');
                if (parsedJSON.data.response) {
                    dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                    var docId = grid_1.getSelectedRowId();
                    docId = docId.substring(4);
                    grid_3.updateFromXML("Controller/php/data_toc.php?action=4&doc_id=" + docId);
                } else {
                    dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                }
            });

        } else {
            dhtmlx.alert("No Row selected!");
        }
    }
});

var toc_formdata = [
    {type: "settings", labelWidth: 80, inputWidth: 300, offsetLeft: "20", offsetTop: "10"},
//    {type: "input", name: "form_input_7", label: "Chapter", readonly: true},

    {type: "input", name: "title", label: "Title"},
    {type: "calendar", className: "formbox", position: "label-left", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", enableTime: false, label: "Date", name: "chapter_date", value: "", readonly: true},
    {type: "input", name: "chapter_author", label: "Author", readonly: true},
    {type: "input", name: "topics", label: "Topics", rows: "8"},
    {type: "input", name: "comments", label: "Comments", rows: "8"},
    {type: "combo", name: "status", label: "Status", inputWidth: 300, options: [
            {value: "To do", text: "To do", selected: true},
            {value: "Planned", text: "Planned"},
            {value: "Done", text: "Done"},
            {value: "Verified", text: "Verified"},
            {value: "Implemented", text: "Implemented"}
        ]},
];
var form_4 = tab_7.attachForm(toc_formdata);

tabbar_3.addTab('planning', 'Planning');
var toc_planning = tabbar_3.cells('planning');

var toc_planning_layout = toc_planning.attachLayout('2U');

var toc_planningGridCell = toc_planning_layout.cells('a');
toc_planningGridCell.hideHeader();

var toc_planningFormCell = toc_planning_layout.cells('b');
toc_planningFormCell.setText('Details');

var toc_PlanningGridToolbar = toc_planningGridCell.attachToolbar();
toc_PlanningGridToolbar.setIconsPath("Views/imgs/");
toc_PlanningGridToolbar.addSeparator("sep1", 1);
toc_PlanningGridToolbar.addButton("add", 2, "Add Event", "new.gif", "new.gif");
toc_PlanningGridToolbar.addSeparator("sep2", 3);
toc_PlanningGridToolbar.addButton("delete", 4, "Delete Event", "deleteall.png", "deleteall.png");
toc_PlanningGridToolbar.attachEvent("onClick", toc_PlanningGridToolbarClicked);

function toc_PlanningGridToolbarClicked(id) {
    var projectId = projectsTree.getSelectedItemId();
    if (projectId > 0) {
        switch (id) {
            case 'add':
                var projectId = projectsTree.getSelectedItemId();
                if (projectId > 0) {
                    var documentId = grid_1.getSelectedRowId();
                    if (documentId) {
                        var documentId = grid_1.getSelectedRowId().substring(4);
                        if (toc_id) {

                            window.dhx4.ajax.get("Controller/php/data_toc.php?action=16&project_id=" + projectId + "&doc_id=" + documentId + "&toc_id=" + toc_id + "&eid=" + uID, function (r) {
                                var t = null;
                                try {
                                    eval("t=" + r.xmlDoc.responseText);
                                } catch (e) {
                                }
                                ;
                                if (t !== null && t.data.response) {
                                    dhtmlx.message({title: 'Success', text: t.data.text});
                                    toc_planningGrid.clearAndLoad('Controller/php/data_toc.php?action=17&id=' + toc_id, function ()
                                    {
                                        toc_planningGrid.selectRowById(t.data.newId);
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
                } else {
                    dhtmlx.alert({type: "Warning", text: "Please select a project!"});
                }

                break;

            case 'delete':

                var projectId = projectsTree.getSelectedItemId();
                var row_id = toc_planningGrid.getSelectedRowId();
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
                                    if (y)
                                    {
                                        window.dhx4.ajax.get("Controller/php/projectsPlanning.php?action=4&id=" + row_id + "&project_id=" + projectId, function (r) {
                                            var t = null;
                                            try {
                                                eval("t=" + r.xmlDoc.responseText);
                                            } catch (e) {
                                            }
                                            ;
                                            if (t != null && t.data.response) {
                                                dhtmlx.message({title: 'Success', text: t.data.text});
                                                toc_planningGrid.deleteRow(row_id);
                                                toc_planningForm.clear();
                                            } else {
                                                dhtmlx.alert({title: 'Error', text: t.data.text});
                                            }
                                        });
                                    } else
                                    {
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

var toc_planningGrid = toc_planningGridCell.attachGrid();
toc_planningGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
toc_planningGrid.setSkin('dhx_web');
toc_planningGrid.setHeader(["ID", "Event Name", "Assigned To", "Begin Date", "End Date", "Details", "Visible", "Main Task", "Done"],
        null,
        ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:center;", "text-align:center;", "text-align:center;"]);
toc_planningGrid.setColumnIds("event_id,details,employee_id,start_date,end_date,event_name,visible,main_task,completed");
toc_planningGrid.attachHeader('#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,,,');
toc_planningGrid.setColTypes("ro,ro,ro,dhxCalendar,dhxCalendar,ed,ch,ch,ch");
toc_planningGrid.setDateFormat("%Y-%m-%d %H:%i");
toc_planningGrid.setColAlign('left,left,left,left,left,left,center,center,center');
toc_planningGrid.setColSorting('str,str,str,date,date,str,int,int,int');
toc_planningGrid.enableCellIds(true);
toc_planningGrid.enableMultiline(true);
toc_planningGrid.setInitWidthsP('0,*,*,*,*,*,*,*,*');
toc_planningGrid.attachEvent("onSelectStateChanged", toc_planningGridRowSelect);//onRowSelect
toc_planningGrid.attachEvent("onEditCell", toc_planningGridEdit);
toc_planningGrid.attachEvent("onCheck", toc_planningGridChecked);
for (var i = 5; i < 9; i++) {
    toc_planningGrid.setColumnHidden(i, true);
}
toc_planningGrid.init();

//var projectPlanningGridEmpCombo = projectPlanningGrid.getColumnCombo(2);
//projectPlanningGridEmpCombo.enableFilteringMode(true);
//projectPlanningGridEmpCombo.load("Controller/php/projectsPlanning.php?action=2");

function toc_planningGridEdit(stage, id, index, new_value, old_value, cellIndex) {

    var event_id = toc_planningGrid.getSelectedRowId();
    var cell = toc_planningGrid.cells(id, index);
    if (stage === 2 && !cell.isCheckbox()) {
        if (event_id > 0 || typeof event_id != 'undefined') {
            var colId = toc_planningGrid.getColumnId(index);
            var colType = toc_planningGrid.fldSort[index];
            $.get("Controller/php/projectsPlanning.php?action=11&id=" + event_id + "&index=" + index + "&fieldvalue=" + new_value + "&colId=" + colId + "&colType=" + colType, function (data) {
                if (data.data.response) {
                    dhtmlx.message({type: "Success", text: data.data.text});
                    toc_planningGrid.updateFromXML('Controller/php/data_toc.php?action=17&id=' + toc_id, true, true);
                    toc_planningForm.clear();
                    toc_planningForm.load('Controller/php/data_toc.php?action=18&id=' + event_id, function (id, response)
                    {
                        var rec_type = toc_planningForm.getItemValue('rec_type');
                        for (var i = 0; i < 8; i++)
                        {
                            toc_planningForm.uncheckItem('days_select[' + i + ']');
                        }
                        var s = '' + rec_type + '';
                        s = rec_type.split(",");
                        for (var k = 0; k < s.length; k++) {
                            var d = s[k];
                            toc_planningForm.checkItem('days_select[' + d + ']');
                        }
                        if (toc_planningForm.isItemChecked('variable') == true)
                        {
                            disableCheckBox();
                        } else {
                            toc_planningForm.setItemLabel("label_days", "Select Days");
                        }
                        //load the combo checked values
                        toc_planningEmployeeCombo.clearAll();
                        toc_planningEmployeeCombo.load("Controller/recurring.php?action=110&load=1&evt_id=" + event_id);
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

function toc_planningGridChecked(id, index, state) {
    var colId = toc_planningGrid.getColumnId(index);

    $.post("Controller/php/projectsPlanning.php?action=10", {colId: colId, id: id, nValue: ((state) ? 1 : 0)}, function (data)
    {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
}

function toc_planningGridRowSelect(id, ind) {

    //get the event selected 
    var event_id = id;

    //load form details
    toc_planningForm.load('Controller/php/data_toc.php?action=18&id=' + event_id, function (id, response)
    {
        var rec_type = toc_planningForm.getItemValue('rec_type');
        for (var i = 0; i < 8; i++)
        {
            toc_planningForm.uncheckItem('days_select[' + i + ']');
        }
        var s = '' + rec_type + '';
        s = rec_type.split(",");
        for (var k = 0; k < s.length; k++) {
            var d = s[k];
            toc_planningForm.checkItem('days_select[' + d + ']');
        }
        if (toc_planningForm.isItemChecked('variable') == true)
        {
            disableCheckBox();
        } else {
            toc_planningForm.setItemLabel("label_days", "Select Days");
        }
        //load the combo checked values
        toc_planningEmployeeCombo.clearAll();
        toc_planningEmployeeCombo.load("Controller/php/projectsPlanning.php?action=29&evt_id=" + event_id);
    });
//    eventReoccurencesGrid.clearAndLoad("Controller/generated_tasks.php?id=" + event_id, function () {});

}

var toc_planningFormToolbar = toc_planningFormCell.attachToolbar();
toc_planningFormToolbar.setIconsPath("Views/imgs/");

toc_planningFormToolbar.loadStruct('<toolbar><item type="button" id="save" text="Save" img="save.gif" /></toolbar>', function () {
});
toc_planningFormToolbar.attachEvent('onClick', toc_planningFormToolbarClicked);

function toc_planningFormToolbarClicked(id)
{
    switch (id)
    {
        case 'save':
            var eventId = toc_planningGrid.getSelectedRowId();
            if (eventId > 0) {

                var emp_assigned = toc_planningForm.getCombo("emp").getChecked();
                var approved_by = toc_planningForm.getCombo("approved_by").getChecked();

                //master_form_details.setItemValue("emp", emp_assigned);
                toc_planningForm.setItemValue("approved_by", approved_by);

                if (emp_assigned.length < 1) {
                    dhtmlx.alert("Select employee from dropdown!")
                } else {
                    toc_planningGridCell.progressOn();
                    toc_planningForm.send("Controller/php/projectsPlanning.php?action=38&approved=" + approved_by + "&eid=" + uID, function (loader, response)
                    {
                        var parsedJSON = eval('(' + response + ')');
                        if (parsedJSON.data.response) {
                            dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                            toc_planningGrid.updateFromXML('Controller/php/data_toc.php?action=17&id=' + toc_id, true, true, function () {
                                //load the combo checked values
                                toc_planningEmployeeCombo.clearAll();
                                toc_planningEmployeeCombo.load("Controller/php/projectsPlanning.php?action=29&evt_id=" + eventId);
                                toc_planningGridCell.progressOff();
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


tocPlanningFormdata = [
    {type: "settings", position: "label-left", labelWidth: toc_planningFormCell.getWidth() * 0.2, inputWidth: toc_planningFormCell.getWidth() * 0.6, offsetTop: 8, offsetLeft: 20},
//    {type: "fieldset", label: "Event Details", className: "formbox", width: taskDetailsCell.getWidth() * 0.9, offsetLeft: 10, list:
//                [
    {type: "hidden", label: "ID", name: "event_id", value: ""},
    {type: "input", label: "Event Name", name: "event_name", value: ""},
    {type: "editor", label: "Details", rows: 2, name: "toc_details", position: "label-top", value: "", style: "width:" + toc_planningFormCell.getWidth() * 0.8 + ";height:" + toc_planningFormCell.getHeight() * 0.2 + ";"},
    {type: "combo", comboType: "checkbox", label: "Assigned To", name: "emp", value: ""},
    {type: "block", width: toc_planningFormCell.getWidth() * 0.8, offsetTop: 0, list: [
            {type: "calendar", position: "label-left", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", enableTime: false, label: "Start Date", inputWidth: 90, name: "start_date", value: "", readonly: false, offsetLeft: 0},
            {type: "calendar", position: "label-left", dateFormat: "%Y-%m-%d", serverDateFormat: "%Y-%m-%d", enableTime: false, label: "End Date", inputWidth: 90, name: "end_date", value: "", readonly: false, offsetLeft: 0}, //%H:%i
            {type: "newcolumn", offsetLeft: 10},
            {type: "input", label: "Time", position: "label-left", name: "begn", value: "", inputWidth: 50, offsetLeft: 10, labelWidth: 30},
            {type: "input", label: "Time", position: "label-left", name: "end", value: "", inputWidth: 50, offsetLeft: 10, labelWidth: 30}
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
//    {type: "newcolumn", offset: 30},
    {type: "fieldset", name: "label_days", label: "Select Days", width: toc_planningFormCell.getWidth() * 0.8,
        list: [
            {type: "checkbox", name: "days_select[1]", labelWidth: 25, label: "Mon"},
//            {type: "newcolumn"},
            {type: "checkbox", name: "days_select[2]", labelWidth: 25, label: "Tue"},
//            {type: "newcolumn"},
            {type: "checkbox", name: "days_select[3]", labelWidth: 25, label: "Wed"},
            {type: "newcolumn"},
            {type: "checkbox", name: "days_select[4]", labelWidth: 25, label: "Thur"},
//            {type: "newcolumn"},
            {type: "checkbox", name: "days_select[5]", labelWidth: 25, label: "Fri"},
//            {type: "newcolumn"},
            {type: "checkbox", name: "days_select[6]", labelWidth: 25, label: "Sat"},
//            {type: "newcolumn"},
//            {type: "checkbox", name: "days_select[7]", labelWidth: 25, label: "Sun"},
        ],
    },
    {type: "checkbox", name: "variable", position: "label-left", labelWidth: 100, value: "1", label: "Enable Variable", checked: false, offsetLeft: 20},
    {type: "hidden", label: "Rec_Type", position: "label-left", name: "rec_type", value: "", inputWidth: 48, offsetLeft: 5},
    {type: "hidden", label: "Cat_id", position: "label-left", name: "cat_id", value: "", inputWidth: 48, offsetLeft: 5},
    {type: "editor", label: "Information", rows: 2, position: "label-top", name: "toc_info", value: "", style: "width:" + toc_planningFormCell.getWidth() * 0.8 + ";height:" + toc_planningFormCell.getHeight() * 0.2 + ";"},
    {type: "combo", comboType: "checkbox", label: "Approved by", name: "approved_by", value: ""},
    {type: "checkbox", name: "map", value: "0", label: "Show map", checked: true},
    {type: "checkbox", name: "masterrecord", value: "0", label: "Show masterrecord", checked: false},
    {type: "checkbox", name: "reoccur_map", value: "0", label: "Reoccur map", checked: false}
//                ]
//    }
];


toc_planningFormCell.hideHeader();
var toc_planningForm = toc_planningFormCell.attachForm(tocPlanningFormdata);
toc_planningForm.getInput("start_date").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
toc_planningForm.getInput("start_date").style.backgroundPosition = "center right";
toc_planningForm.getInput("start_date").style.backgroundRepeat = "no-repeat";
toc_planningForm.getInput("end_date").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
toc_planningForm.getInput("end_date").style.backgroundPosition = "center right";
toc_planningForm.getInput("end_date").style.backgroundRepeat = "no-repeat";

var toc_planningEmployeeCombo = toc_planningForm.getCombo("emp");
toc_planningEmployeeCombo.enableFilteringMode(true);
toc_planningEmployeeCombo.load("Controller/php/projectsPlanning.php?action=2");

var toc_planningApproved_Combo = toc_planningForm.getCombo("approved_by");
toc_planningApproved_Combo.load("Controller/recurring.php?action=110");

toc_planningEmployeeCombo.attachEvent("onCheck", function (value, state) {
    var eventId = toc_planningGrid.getSelectedRowId();
    var employeeId = value;

    $.post("Controller/php/projectsPlanning.php?action=28", {eventId: eventId, employeeId: employeeId, nValue: ((state) ? 1 : 0), eid: uID}, function (data)
    {
        if (data.data.response) {
//            var projectId = projectsTree.getSelectedItemId();
//            projectPlanningGrid.updateFromXML("Controller/php/projectsPlanning.php?action=1&id=" + projectId);
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');

    return true;
});

tabbar_2.addTab('tab_5', 'Document Viewer');
var tab_5 = tabbar_2.cells('tab_5');

document_viewer_tabbar = tab_5.attachTabbar();

document_viewer_tabbar.addTab('doc_normal_view', 'Normal View');
var doc_normal_view = document_viewer_tabbar.cells('doc_normal_view');
doc_normal_view.setActive();

document_viewer_tabbar.addTab('doc_accordion_view', 'Accordion View');
var doc_accordion_view = document_viewer_tabbar.cells('doc_accordion_view');

tabbar_2.attachEvent("onSelect", function (id, lastId) {

    if (id === 'tab_5') {
        var doc_id = grid_1.getSelectedRowId();
        if (doc_id) {
            var activeViewTab = document_viewer_tabbar.getActiveTab();
            if (activeViewTab === 'doc_normal_view') {
                doc_normal_view.attachURL('Views/frames/document_viewer.php?doc_id=' + doc_id);
            }
            if (activeViewTab === 'doc_accordion_view') {
                doc_accordion_view.attachURL('Views/frames/document_accordion.php?doc_id=' + doc_id);
            }
        }
    }

    return true;
});

document_viewer_tabbar.attachEvent("onSelect", function (id, lastId) {

    var doc_id = grid_1.getSelectedRowId();
    if (doc_id) {
        if (id === 'doc_normal_view') {
            doc_normal_view.attachURL('Views/frames/document_viewer.php?doc_id=' + doc_id);
        }
        if (id === 'doc_accordion_view') {
            doc_accordion_view.attachURL('Views/frames/document_accordion.php?doc_id=' + doc_id);
        }
    }

    return true;
});

tabbar_3.addTab('toc_history', 'History');
var toc_history = tabbar_3.cells('toc_history');

var tocHistoryLayout = toc_history.attachLayout('2U');

var tocHistoryListCell = tocHistoryLayout.cells('a');
tocHistoryListCell.hideHeader();
var tocHistoryGrid = tocHistoryListCell.attachGrid();
tocHistoryGrid.setIconsPath('./codebase/imgs/');
tocHistoryGrid.setSkin('dhx_web');
tocHistoryGrid.setHeader(["ID", "Date", "Author", "Char"]);
tocHistoryGrid.setColumnIds('toc_id,date_edited,author,char');
tocHistoryGrid.attachHeader("#numeric_filter,#text_filter,#text_filter,#text_filter");
tocHistoryGrid.setColTypes("ro,ro,ro,ro");
tocHistoryGrid.setInitWidthsP("14,25,*,15");
tocHistoryGrid.setColSorting('str,date,str,int');
tocHistoryGrid.enableCellIds(true);
tocHistoryGrid.setDateFormat("%Y-%m-%d %H:%i:%s");
tocHistoryGrid.attachEvent("onSelectStateChanged", tocHistoryGridStateChanged);//onRowSelect
tocHistoryGrid.init();

function tocHistoryGridStateChanged(id, ind) {

    tocHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent("");
    window.dhx4.ajax.get("Controller/php/data_toc.php?action=19&id=" + id, function (r) {
        var t = null;
        try {
            eval("t=" + r.xmlDoc.responseText);
        } catch (e) {
        }
        ;
        if (t !== null && t.content !== null) {
            tocHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent(t.content);
        }
    });
}

var tocHistoryToolbar = tocHistoryListCell.attachToolbar();
tocHistoryToolbar.setIconsPath("Views/imgs/");
tocHistoryToolbar.addButton("delete", 1, "Delete", "deleteall.png", "deleteall.png");
tocHistoryToolbar.addSeparator("sep1", 2);
tocHistoryToolbar.addButton("delete_all", 3, "Delete All", "deleteall.png", "deleteall.png");
tocHistoryToolbar.addSeparator("sep2", 4);

tocHistoryToolbar.attachEvent("onClick", tocHistoryToolbarClicked);

function tocHistoryToolbarClicked(id) {
    switch (id)
    {
        case "delete":

            var row_id = tocHistoryGrid.getSelectedRowId();
            if (row_id > 0) {

                dhtmlx.confirm({
                    title: "Confirm",
                    type: "confirm-warning",
                    text: "Are you sure you to delete this Row?",
                    callback: function (ok) {
                        if (ok)
                        {
                            window.dhx4.ajax.get("Controller/php/data_toc.php?action=20&case=1&id=" + row_id, function (r) {
                                var t = null;
                                try {
                                    eval("t=" + r.xmlDoc.responseText);
                                } catch (e) {
                                }
                                ;
                                if (t !== null && t.data.response) {
                                    tocHistoryGrid.deleteRow(row_id);
                                    tocHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent("");
                                    dhtmlx.message({title: 'Success', text: t.data.text});
                                } else
                                    dhtmlx.alert({title: 'Error', text: t.data.text});
                            });
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
        case "delete_all":

            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you to delete all History?",
                callback: function (ok) {
                    if (ok)
                    {
                        window.dhx4.ajax.get("Controller/php/data_toc.php?action=20&id=" + toc_id, function (r) {
                            var t = null;
                            try {
                                eval("t=" + r.xmlDoc.responseText);
                            } catch (e) {
                            }
                            ;
                            if (t !== null && t.data.response) {
                                tocHistoryGrid.clearAll();
                                tocHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent("");
                                dhtmlx.message({title: 'Success', text: t.data.text});
                            } else
                                dhtmlx.alert({title: 'Error', text: t.data.text});
                        });
                    } else
                    {
                        return false;
                    }
                }

            });

            break;
    }
}

var tocHistoryContentIframe;
var tocHistoryContentCell = tocHistoryLayout.cells('b');
tocHistoryContentCell.hideHeader();
tocHistoryContentCell.attachURL("Views/frames/history_content.php", false,
        {report_content: '', height: (tocHistoryContentCell.getHeight()) / 1.26});
tocHistoryLayout.attachEvent("onContentLoaded", function (id) {
    tocHistoryContentIframe = tocHistoryLayout.cells(id).getFrame();
});

tabbar_3.addTab('toc_comments', 'Comments');
var toc_comments = tabbar_3.cells('toc_comments');

var tocCommentsLayout = toc_comments.attachLayout('1C');
tocCommentsLayout.cells("a").hideHeader();

var comments_toolbar = tocCommentsLayout.cells("a").attachToolbar();
comments_toolbar.setIconsPath("Views/imgs/");
comments_toolbar.addButton("new", 1, "New", "new.gif");
comments_toolbar.addSeparator("sep1", 2);
comments_toolbar.addButton("delete", 3, "Delete", "deleteall.png");
comments_toolbar.addSeparator("sep2", 4);

comments_toolbar.attachEvent("onClick", function (id)
{
    comment_id = comments_grid.getSelectedRowId();
    chapter_id = grid_3.getSelectedRowId();

    if (id == "new")
    {
        if (chapter_id)
        {
            $.post("Controller/php/data_toc.php?action=31", "eid=" + uID + "&chapter_id=" + chapter_id, function (data) {
                if (data.data.success)
                {
                    comments_grid.clearAndLoad("Controller/php/data_toc.php?action=32&id=" + chapter_id);
                } else
                {
                    dhtmlx.alert({
                        title: "Important!",
                        type: "alert-error",
                        text: data.data.message
                    });
                }
            }, 'json');
        } else
        {
            dhtmlx.alert({
                title: "Important!",
                type: "alert-error",
                text: "Please select a Chapter"
            });
        }
    } else if (id == "delete")
    {
        dhtmlx.confirm({
            title: "Delete Record",
            ok: "Yes",
            cancel: "No",
            text: "Do you want to Delete this Error Record?",
            callback: function (result) {
                if (result) {
                    $.post("Controller/php/data_toc.php?action=33", "comment_id=" + comment_id, function (data) {
                        if (data.data.success)
                        {
                            comments_grid.deleteRow(comment_id);
                        } else
                        {
                            dhtmlx.alert({
                                title: "Important!",
                                type: "alert-error",
                                text: data.data.message
                            });
                        }
                        ;
                    }, 'json');
                }
            }
        });
    }
});

var comments_grid = tocCommentsLayout.cells("a").attachGrid();
comments_grid.setHeader("ID,Date,EID,Comment");
comments_grid.setColumnIds("id,comment_date,eid,details");
comments_grid.attachHeader("#numeric_filter,#text_filter,#numeric_filter,#text_filter");
comments_grid.setColSorting('int,date,int,str');
comments_grid.setInitWidthsP("7,16,15,*");
comments_grid.setColTypes('ro,ro,ro,txt');
comments_grid.setColAlign('left,left,left,left');
comments_grid.setDateFormat("%Y-%m-%d");
comments_grid.setSkin("dhx_web");
comments_grid.init();

comments_grid.attachEvent("onXLE", function (grid_obj, count) {
    tocCommentsLayout.cells("a").progressOff();
});

comments_grid.attachEvent("onXLS", function (grid_obj) {
    tocCommentsLayout.cells("a").progressOn();
});

comments_grid.attachEvent("onEditCell", function (stage, id, index, new_value, old_value, cellIndex)
{
    var colId = comments_grid.getColumnId(index);
    var colType = comments_grid.fldSort[index];
    chapter_id = grid_3.getSelectedRowId();

    if (stage == 2)
    {
        var cont = {
            table: "documents_comments",
            id: id,
            index: index,
            fieldvalue: new_value,
            colType: colType,
            colId: colId
        }

        $.post("Controller/php/data_toc.php?action=34", cont, function (data)
        {
            if (data.data.response) {
                dhtmlx.message({title: 'Success', text: data.data.text});
                comments_grid.updateFromXML("Controller/php/data_toc.php?action=32&id=" + chapter_id, true, true);
            } else {
                dhtmlx.alert({title: 'Error', text: data.data.text});
            }
        }, 'json');
    }
});


function createTopic(course_id, section_id, module_id = null) {

    var windows = new dhtmlXWindows();
    var window_4 = windows.createWindow('window_4', 0, 0, 400, 150);
    window_4.setText('Add Module');
    window_4.setModal(1);
    window_4.centerOnScreen();
    window_4.button('park').hide();
    window_4.button('minmax').hide();

    var str = [
        {type: "settings", position: "label-left", labelWidth: 80, inputWidth: 250, offsetTop: 8, offsetLeft: 10},
        {type: "hidden", name: "course", label: "course"},
        {type: "hidden", name: "section", label: "section"},
        {type: "input", name: "name", label: "Name"},
        {type: "button", name: "form_button_2", value: "Create", inputLeft: 150}
    ];
    var form_3 = window_4.attachForm(str);
    form_3.setItemFocus("name");

    form_3.attachEvent('onButtonClick', function () {

        var formdata = form_3.getFormData();
        formdata['course'] = course_id;
        formdata['section'] = section_id;

        var domainname = getDomainName();
        var serverurl = domainname + '/moosh.php?action=1';

        $.post(serverurl, formdata, function (data) {
            if (data.data.response) {
                dhtmlx.message({title: 'Success', text: 'Successfully Added'});

                grid_3.clearAndLoad('Controller/php/courses.php?action=4&course=' + course_id + '&server=' + server_id, function () {
//                    grid_3.selectRowById(data.data.row_id);
                    if (module_id !== null) {
                        grid_3.openItem(module_id);
                    }
                });
            } else {
                dhtmlx.alert({title: 'Warning', text: 'An error has occurred'});
            }
            window_4.close();

        }, 'json');
    });
}

function deleteTopic(topic_id) {

    var courseids = [course_id];

    var domainname = getDomainName();
    var token = projectsTree.getUserData(projectsTree.getParentId(projectsTree.getSelectedItemId()), "token");

//    var domainname = 'https://192.168.1.137/moodle';
//    var token = '8065e890ebd56ebaa9283292458fa2a8';
    var functionname = 'core_course_delete_courses';

    var serverurl = domainname + '/webservice/rest/server.php';

    var data = {
        wstoken: token,
        wsfunction: functionname,
        moodlewsrestformat: 'json',
        courseids: courseids
    };

    $.ajax({
        type: 'POST',
        data: data,
        url: serverurl,
        dataType: 'json',
        error: function () {
            dhtmlx.alert({title: 'Warning', text: 'An error has occurred'});
        },
        success: function (data) {
            dhtmlx.message({title: 'Success', text: 'Successfully Removed'});
            grid_3.deleteRow(course_id);
        }
    });
}

function createLesson(section_id) {

    var domainname = getDomainName();

    var windows = new dhtmlXWindows();
    var window_4 = windows.createWindow('window_4', 0, 0, myWidth * 0.7, myHeight * 0.8);
    window_4.setText('Add Module');
    window_4.setModal(1);
    window_4.centerOnScreen();
    window_4.button('park').hide();
    window_4.button('minmax').hide();
    window_4.attachURL('Controller/php/moodle_login.php?course=' + course_id + '&section=' + section_id);

//    window_4.attachURL(domainname + '/course/modedit.php?add=lesson&type=&course=' + course_id + '&section=' + section_id + '&return=0&sr=0')
//    window_4.attachURL('https://education.nts.nl/course/view.php?id=' + course_id + '&notifyeditingon=1');

    window_4.attachEvent("onClose", function (win) {
        grid_3.clearAndLoad('Controller/php/courses.php?action=4&course=' + course_id + '&server=' + server_id);

        return true;
    });
}

function createLessonPage(lesson_id, instance_id, page_id = null) {

    var domainname = getDomainName();

    var windows = new dhtmlXWindows();
    var window_4 = windows.createWindow('window_4', 0, 0, myWidth * 0.7, myHeight * 0.8);
    window_4.setText('Add Content Page');
    window_4.setModal(1);
    window_4.centerOnScreen();
    window_4.button('park').hide();
    window_4.button('minmax').hide();

    window_4.attachURL('Controller/php/moodle_login.php?action=3&type=page&lesson=' + lesson_id + '&instance=' + instance_id + '&server=' + server_id + '&page=' + page_id);

//    window_4.attachURL(domainname + '/course/modedit.php?add=lesson&type=&course=' + course_id + '&section=' + section_id + '&return=0&sr=0')
//    window_4.attachURL('https://education.nts.nl/course/view.php?id=' + course_id + '&notifyeditingon=1');

    window_4.attachEvent("onClose", function (win) {
        grid_3.clearAndLoad('Controller/php/courses.php?action=4&course=' + course_id + '&server=' + server_id);

        return true;
    });
}

function createLessonQuiz(lesson_id, instance_id, page_id = null) {

    var domainname = getDomainName();

    var windows = new dhtmlXWindows();
    var window_4 = windows.createWindow('window_4', 0, 0, myWidth * 0.7, myHeight * 0.8);
    window_4.setText('Add Content Page');
    window_4.setModal(1);
    window_4.centerOnScreen();
    window_4.button('park').hide();
    window_4.button('minmax').hide();
    window_4.attachURL('Controller/php/moodle_login.php?action=3&type=quiz&lesson=' + lesson_id + '&instance=' + instance_id + '&server=' + server_id + '&page=' + page_id);

//    window_4.attachURL(domainname + '/course/modedit.php?add=lesson&type=&course=' + course_id + '&section=' + section_id + '&return=0&sr=0')
//    window_4.attachURL('https://education.nts.nl/course/view.php?id=' + course_id + '&notifyeditingon=1');

    window_4.attachEvent("onClose", function (win) {
        grid_3.clearAndLoad('Controller/php/courses.php?action=4&course=' + course_id + '&server=' + server_id);

        return true;
    });
}

function getDomainName() {
    return projectsTree.getUserData(projectsTree.getParentId(projectsTree.getSelectedItemId()), "path");
}

function getMoodleToken() {
    return projectsTree.getUserData(projectsTree.getParentId(projectsTree.getSelectedItemId()), "token");
}









