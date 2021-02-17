/**
 * layout for the nts admin
 */
var myWidth;
var myHeight;

if (typeof (window.innerWidth) == 'number') {

//Non-IE 

    myWidth = window.innerWidth;
    myHeight = window.innerHeight;

} else if (document.documentElement &&
    (document.documentElement.clientWidth || document.documentElement.clientHeight)) {

//IE 6+ in 'standards compliant mode' 

    myWidth = document.documentElement.clientWidth;
    myHeight = document.documentElement.clientHeight;

} else if (document.body && (document.body.clientWidth || document.body.clientHeight)) {

//IE 4 compatible 

    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;

}
//dhtmlx.image_path = "https://" + location.host + "/dhtmlxsuite4/skins/web/imgs/";
window.dhx4.skin = 'dhx_terrace';

//first date values
var salary_date = new Date().format('Y-m-d');
var today_actual_date = new Date().format('Y-m-d');
var today_salary_date = new Date().format('F Y');
var projectTaskEventId = null;
var selected_doc_id = null;
var all = false;
var searchValue;
var devFormSelctdId = null;
var projectId = null;
var searchForm = null;
var searchGrid = null;
var selectedDragId = null;
var searchedDocId = null;
var searchedFileId = null;
var selectedGrid = null;
var projectsDataToolbar = null;
var firstId = true;
var assetEventForm = null;
var content_type = 0;
var course_id = null;
var server_id = null;
var is_moodle = false;
var languageId = 1;
var branchId = 1;

getMonthName = function (v) {
    var n = ["", "All", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    return n[v]
}

getMonthNum = function (trans_month) {

    if (trans_month == "All") {
        trans_month_num = "00";
    } else if (trans_month == "Jan") {
        trans_month_num = "01";
    } else if (trans_month == "Feb") {
        trans_month_num = "02";
    } else if (trans_month == "Mar") {
        trans_month_num = "03";
    } else if (trans_month == "Apr") {
        trans_month_num = "04";
    } else if (trans_month == "May") {
        trans_month_num = "05";
    } else if (trans_month == "Jun") {
        trans_month_num = "06";
    } else if (trans_month == "Jul") {
        trans_month_num = "07";
    } else if (trans_month == "Aug") {
        trans_month_num = "08";
    } else if (trans_month == "Sep") {
        trans_month_num = "09";
    } else if (trans_month == "Oct") {
        trans_month_num = "10";
    } else if (trans_month == "Nov") {
        trans_month_num = "11";
    } else if (trans_month == "Dec") {
        trans_month_num = "12";
    }

    return trans_month_num;
}

var year_data = [];
for (var i = 2010; i <= 2020; i++) {
    year_data.push(
        {id: i, type: "obj", text: i}
    );
}

var month_data = [];
for (var i = 1; i <= 13; i++) {
    month_data.push(
        {id: getMonthName(i), type: "obj", text: getMonthName(i)}
    );
}

var path = "https://bo.nts.nl/site/html/modules/wiwimod";
var main_layout = new dhtmlXLayoutObject(document.body, '2U');

var projectsTreeCell = main_layout.cells('a');
projectsTreeCell.setText('Projects');
//projectsTreeCell.hideHeader();
projectsTreeCell.setWidth('370');
var projectsTreeToolbar = projectsTreeCell.attachRibbon();
//var projectsTreeToolbar = projectsTreeCell.attachToolbar();
projectsTreeToolbar.setIconPath("Views/imgs/");
projectsTreeToolbar.setSkin('dhx_terrace');
//projectsTreeToolbar.setSizes();
projectsTreeToolbar.loadStruct("Controller/php/projectsTree.php?action=22");

projectsTreeToolbar.attachEvent("onStateChange", projectsTreeToolbarStateChange);
projectsTreeToolbar.attachEvent("onClick", projectsTreeToolbarClick);


//create a context menus to add to the trees 
var projectsTreeContextMenu = new dhtmlXMenuObject();
projectsTreeContextMenu.renderAsContextMenu();
projectsTreeContextMenu.setIconsPath('Views/imgs/');
projectsTreeContextMenu.attachEvent("onClick", projectsTreeContextMenuClicked);
projectsTreeContextMenu.loadStruct("Controller/php/projectsTree.php?action=2");

projectsTreeContextMenu.attachEvent("onCheckboxClick", function (id, state, zoneId, cas) {

    var parentId = projectsTreeContextMenu.getParentId(id);
    var itemId = projectsTree.getSelectedItemId();

    if (parentId == "type") {

        var value = state ? "0" : "1";
        var ids = addSubItemsType(itemId, value, projectsTreeContextMenu.getItemText(id));
        addProjectType(ids, id.substring(5), value);

    } else {

        if (state === false) {
            window.dhx4.ajax.get("Controller/php/projectsTree.php?action=3&id=" + itemId, function (r) {
                var t = null;
                try {
                    eval("t=" + r.xmlDoc.responseText);
                } catch (e) {
                }
                ;
                if (t !== null && t.data.response) {
                    projectsTreeContextMenu.setCheckboxState(id, true);
                    projectsTree.deleteItem(itemId);
                    dhtmlx.message({title: 'Success', text: t.data.text});
                } else {
                    dhtmlx.alert({title: 'Warning', text: t.data.text});
                }
            });
        } else {
            window.dhx4.ajax.get("Controller/php/projectsTree.php?action=4&id=" + itemId, function (r) {
                var t = null;
                try {
                    eval("t=" + r.xmlDoc.responseText);
                } catch (e) {
                }
                ;
                if (t !== null && t.data.response) {
                    projectsTreeContextMenu.setCheckboxState(id, false);
                    dhtmlx.message({title: 'Success', text: t.data.text});
                } else {
                    dhtmlx.alert({title: 'Warning', text: t.data.text});
                }
            });
        }
    }

    return true;
});

function addSubItemsType(tree_id, value, name, ids = []) {

    if (tree_id == null)
        return;

    projectsTree.setUserData(tree_id, name, value);
    ids.push(tree_id);

    var subItems = projectsTree.getSubItems(tree_id);

    if (subItems) {

        var subItemsArray = subItems.split(",");
        console.log(subItems);

        subItemsArray.forEach(element => {
            addSubItemsType(element, value, name, ids);
        });
    }
    return ids;
}

function addProjectType(ids, type, value) {

    let data = {ids: ids, type_id: type, n_value: value};
    $.post("Controller/php/projectsTree.php?action=37", data, function (data) {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
}

var addItemPopupFormdata = [
    {type: "settings", position: "label-left", labelWidth: 110, inputWidth: 220, offsetTop: 10, offsetLeft: 10},
    {type: "hidden", name: "parent", label: "Parent"},
    {type: "input", name: "item_name", label: "Name", required: true},
    {type: "button", name: "submit", value: "Submit", width: "62", className: "", inputWidth: 60, offsetLeft: 150}
];

var addCategoryPopupFormdata = [
    {type: "settings", position: "label-left", labelWidth: 110, inputWidth: 220, offsetTop: 10, offsetLeft: 10},
    {type: "input", name: "item_name", label: "Name", required: true},
    {type: "button", name: "submit", value: "Submit", width: "62", className: "", inputWidth: 60, offsetLeft: 150}
];

var renamePopupFormdata = [
    {type: "settings", position: "label-left", labelWidth: 110, inputWidth: 220, offsetTop: 10, offsetLeft: 10},
    {type: "input", name: "item_name", label: "New Name", required: true},
    {type: "button", name: "submit", value: "Submit", width: "62", className: "", inputWidth: 60, offsetLeft: 150}
];

var projectsTree = projectsTreeCell.attachTree();
projectsTree.setImagePath('dhtmlxsuite4/codebase/imgs/dhxtree_skyblue/');
projectsTree.enableHighlighting('1');
projectsTree.enableDragAndDrop('1', true);
projectsTree.setSkin('dhx_skyblue');
//employeeTree.enableItemEditor(1);
projectsTree.enableContextMenu(projectsTreeContextMenu);
projectsTree.enableTreeImages(false);
projectsTree.enableTreeLines(true);
projectsTree.attachEvent("onSelect", projectsTreeClicked);
projectsTree.attachEvent("onDrag", projectsTreeItemDrag);
projectsTree.loadXML("Controller/php/projectsTree.php?branch=" + branchId + "&language=" + languageId + "&eid=" + uID, afterCall);
projectsTree.attachEvent("onRightClick", function (id, ev) {
    projectsTree.selectItem(id, false, false);
});

projectsTree.attachEvent("onBeforeDrag", function (id) {
    firstId = true;
    selectedDragId = null;
    return true;
});

projectsTree.attachEvent("onBeforeContextMenu", function (itemId) {
    window.dhx4.ajax.get("Controller/php/projectsTree.php?action=19&id=" + itemId, function (r) {
        var t = null;
        try {
            eval("t=" + r.xmlDoc.responseText);
        } catch (e) {
        }
        ;
        if (t !== null) {
            if (t.rowNum > 0) {

                projectsTreeContextMenu.hideItem('set_password');
                projectsTreeContextMenu.showItem('change_password');

            } else {
                projectsTreeContextMenu.hideItem('change_password');
                projectsTreeContextMenu.showItem('set_password');
            }
        }
    });

    window.dhx4.ajax.get("Controller/php/projectsTree.php?action=20&id=" + itemId, function (r) {
        var t = null;
        try {
            eval("t=" + r.xmlDoc.responseText);
        } catch (e) {
        }
        ;
        if (t !== null) {
            if (t.value > 0) {
                projectsTreeContextMenu.setCheckboxState('archive', true);
            } else {
                projectsTreeContextMenu.setCheckboxState('archive', false);
            }
        }
    });

    projectsTreeContextMenu.showItem('add');
    projectsTreeContextMenu.setCheckboxState("type_1", projectsTree.getUserData(itemId, "Video") == "1" ? true : false);
    projectsTreeContextMenu.setCheckboxState("type_2", projectsTree.getUserData(itemId, "Moodle") == "1" ? true : false);
    projectsTreeContextMenu.setCheckboxState("type_3", projectsTree.getUserData(itemId, "Project") == "1" ? true : false);

    return true;
});


function afterCall() {

    if (projectId !== null) {
        projectsTree.selectItem(projectId);
    }
    projectsTreeCell.progressOff();
}

function projectsTreeClicked(id) {

    is_moodle = projectsTree.getUserData(id, "Moodle") === "1";

    if (is_moodle) {
        documents.setActive();

        server_id = projectsTree.getParentId(id).split("_")[1];
        course_id = id.split("_")[1];

        grid_1.clearAndLoad('Controller/php/courses.php?action=3&course=' + course_id + '&server=' + server_id);

        grid_main_questions.clearAndLoad('Controller/php/data_questions.php?action=8&course_id=' + course_id);

        return;
    }

    projectId = id;
    toc_form.clear();
    grid_3.clearAll();
    form_4.clear();

    tocContentIframe.contentWindow.tinymce.activeEditor.setContent("");
    projectDocumentsContentIframe.contentWindow.tinymce.activeEditor.setContent("");
    documentFilesViewerCell.detachObject(true);

    projectDocumentsHistoryGrid.clearAll();

    projectsDetailsForm.clear();
    projectsDetailsForm.load('Controller/php/projectsTree.php?action=33&id=' + projectId + '&branch=' + branchId + '&language=' + languageId);

    $.get("Controller/php/projectsTree.php?action=36&id=" + projectId, function (data) {
        if (data.content !== null) {
            projectDetailsFormCommentsIframe.contentWindow.tinymce.activeEditor.setContent(data.content);
        }
    }, 'json');

    projectMapPrivilegesGrid.updateFromXML("Controller/php/map_privileges.php?action=1&tab=1&id=" + projectId, true, true);

    var has_video = projectsTree.getUserData(id, "Video");

    if (has_video == "1") {
        videos.show();
        videos.attachURL("https://video.nts.nl/presentation/content/?eid=" + uID + "&projectId=" + id + "&title=" + projectsTree.getItemText(id));
    } else {
        videos.hide();
    }

    projectDocumentsGrid.clearAndLoad('Controller/php/projectDocuments.php?action=2&id=' + projectId + '&branch=' + branchId + '&language=' + languageId + "&eid=" + uID);

    grid_1.clearAndLoad('Controller/php/projectDocuments.php?action=2&id=' + projectId + '&branch=' + branchId + '&language=' + languageId + "&eid=" + uID);

    documentFilesGrid.clearAndLoad('Controller/php/projectDocuments.php?action=5&id=' + projectId + '&branch=' + branchId + '&language=' + languageId + "&eid=" + uID);

    projectObjectsGrid.clearAndLoad("Controller/php/assignments_objectgrd.php?all=true&id=" + id);

    projectDetailsTranslationGrid.clearAndLoad("Controller/php/projectsTree.php?action=29&project_id=" + id);

    projectDetailsBranchGrid.updateFromXML("Controller/php/projectsTree.php?action=31&project_id=" + id, true, true);

    projectPlanningListCell.progressOn();

    projectPlanningGrid.clearAndLoad('Controller/php/projectsPlanning.php?action=1&id=' + id, function () {
        if (projectTaskEventId !== null) {
            projectPlanningGrid.selectRowById(projectTaskEventId);
            projectPlanningDetailsTabber.tabs("event_reoccurences").setActive();
            projectTaskEventId = null;
        }
        projectPlanningListCell.progressOff();
    });

    var actvId = projectPlanningListTabbar.getActiveTab();

    if (actvId === "gantt") {
        gantt.attachURL('gantt.php?id=' + id + "&eid=" + uID);
    }

    relationsGrid.clearAndLoad("Controller/php/data_relation.php?id=" + id);
    relationContactsGrid.clearAll();
    eventDetailsForm.clear();

    // dataTemplatesGrid.clearAndLoad("Controller/php/data_projects.php?action=3&id=" + id);
    // reloadProjectDataToolbar(true);
}

var projectDetailsCell = main_layout.cells('b');
var projectDetailsTabbar = projectDetailsCell.attachTabbar();
projectDetailsTabbar.addTab('project_documents', 'Documents');
var project_documents = projectDetailsTabbar.cells('project_documents');
project_documents.setActive();

var projectDocumentsContentTabbar = project_documents.attachTabbar();
projectDocumentsContentTabbar.addTab('document_content', 'Documents');
var document_content = projectDocumentsContentTabbar.cells('document_content');
document_content.setActive();

var projectDocumentsLayout = document_content.attachLayout('2E');

var projectDocumentsListCell = projectDocumentsLayout.cells('a');
projectDocumentsListCell.hideHeader();
projectDocumentsListCell.setHeight(projectDetailsCell.getHeight() * 0.4);

var projectDocumentsGrid = projectDocumentsListCell.attachGrid();
projectDocumentsGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
projectDocumentsGrid.setSkin('dhx_web');
projectDocumentsGrid.setHeader(["ID", "Employee", "Date", "Subject", "Category", "Author", "Language", "Explorer ID", "Template ID", "Accordion", "Visible", "ID2", "Default", "Char"], null, ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:center;", "text-align:left;", "text-align:left;", "text-align:left;"]);
projectDocumentsGrid.setColumnIds('Report_ID,Report_Employee_ID,Report_Date,Report_Subject,category_id,Report_Author,language_id,explorer_id,template_id,accordion,visible_in_projects,proj_doc_id,default_report,char');
projectDocumentsGrid.attachHeader("#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#numeric_filter,#numeric_filter,#master_checkbox,#master_checkbox,,,", ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:center;", "text-align:center;", "text-align:left;", "text-align:center;", "text-align:left;"]);
projectDocumentsGrid.setColAlign("left,left,left,left,left,left,left,right,right,center,center,center,center,right");
projectDocumentsGrid.setColTypes("ro,combo,ro,ed,combo,ro,combo,ed,ed,ch,ch,ro,ch,ro");
projectDocumentsGrid.setInitWidthsP("5,10,10,*,10,10,8,6,6,6,5,0,4,6");
projectDocumentsGrid.setColSorting('str,str,date,str,str,str,int,int,int,int,int,int,int,str');
projectDocumentsGrid.enableMultiselect(true);
projectDocumentsGrid.enableCellIds(true);
projectDocumentsGrid.enableDragAndDrop(true);
projectDocumentsGrid.setDateFormat("%Y-%m-%d %H:%i:%s");
projectDocumentsGrid.attachEvent("onSelectStateChanged", projectDocumentsGridStateChanged);//onRowSelect
projectDocumentsGrid.attachEvent("onEditCell", projectDocumentsGridEditCell);
projectDocumentsGrid.attachEvent("onCheck", projectDocumentsGridChecked);
projectDocumentsGrid.attachEvent("onDrag", projectDocumentsGridDragged);
projectDocumentsGrid.setColumnHidden(8, true);
projectDocumentsGrid.setColumnHidden(12, true);
projectDocumentsGrid.init();

projectDocumentsGrid.attachEvent("onBeforeDrag", function (id) {

    firstId = true;

    var rowIds = projectDocumentsGrid.getSelectedRowId();
    if (rowIds !== null) {
        var rowIdsArray = rowIds.split(",");
        if (rowIdsArray.length > 1) {
            selectedDragId = projectDocumentsGrid.getSelectedRowId();
        } else {
            selectedDragId = id;
        }
    } else {
        selectedDragId = id;
    }
    return true;
});

projectDocumentsGrid.attachEvent("onXLE", function (grid_obj, count) {
    if (selected_doc_id > 0) {
        projectDocumentsGrid.selectRowById('doc_' + selected_doc_id);
    }
    selected_doc_id = null;
});

projectDocumentsGridEmployeeCombo = projectDocumentsGrid.getColumnCombo(1);
projectDocumentsGridEmployeeCombo.setSkin("dhx_web");
projectDocumentsGridEmployeeCombo.enableFilteringMode(true);
projectDocumentsGridEmployeeCombo.load("Controller/php/projectDocuments.php?action=1");

projectDocumentsGridLanguageCombo = projectDocumentsGrid.getColumnCombo(6);
projectDocumentsGridLanguageCombo.setSkin("dhx_web");
projectDocumentsGridLanguageCombo.enableFilteringMode(true);
projectDocumentsGridLanguageCombo.load("Controller/php/projectDocuments.php?action=24");

projectDocumentsGridCategoryCombo = projectDocumentsGrid.getColumnCombo(4);
projectDocumentsGridCategoryCombo.setSkin("dhx_web");
projectDocumentsGridCategoryCombo.enableFilteringMode(true);
projectDocumentsGridCategoryCombo.load("Controller/php/projectDocuments.php?action=26");

function projectDocumentsGridDragged(sId, tId, sObj, tObj, sInd, tInd) {
//    alert(sId);
}

function projectDocumentsGridChecked(id, index, state) {
    id = id.substring(4);
    var colId = projectDocumentsGrid.getColumnId(index);

    $.post("Controller/php/projectDocuments.php?action=17", {
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

var projectDocumentsToolbar = projectDocumentsListCell.attachToolbar();
projectDocumentsToolbar.setIconsPath("Views/imgs/");
projectDocumentsToolbar.loadStruct('<toolbar><item type="button" id="create" text="Create New" img="new.gif" /><item type="separator" id="sep_1" /><item type="button" id="delete" text="Delete" img="deleteall.png" /><item type="separator" id="sep_2" /><item type="button" id="reload" text="Refresh" img="refresh.png" /><item type="separator" id="sep_3" /><item type="button" id="export_to_pdf" text="Export to PDF" img="pdf.png" /><item type="separator" id="sep_4" /><item type="button" id="publish" text="Publish"  enabled="false" img="" /><item type="separator" id="sep_5" /><item type="buttonSelect" id="show" text="Show" img="show.png"  openAll="true" renderSelect="true" mode="select"><item type="button" id="show_all" text="Show All Documents" img="show.png"/><item type="button" id="show_visible" text="Show Visible Documents" img="show.png"/></item><item type="separator" id="sep_8" /><item type="button" id="restore_document" text="Restore Document" img="restore.png" /><item type="separator" id="sep_9" /><item type="button" id="cover_page" text="Cover Page PDF" img="pdf.png" /></toolbar>', function () {
});

projectDocumentsToolbar.attachEvent("onClick", projectDocumentsToolbarClicked);

var projectDocumentsContentViewerCell = projectDocumentsLayout.cells('b');
var projectDocumentsContentViewerTabbar = projectDocumentsContentViewerCell.attachTabbar();

projectDocumentsContentViewerTabbar.addTab('document_editor', 'Document Editor');
var document_editor = projectDocumentsContentViewerTabbar.cells('document_editor');
document_editor.setActive();

var documentEditorLayout = document_editor.attachLayout('1C');

var projectDocumentsContentIframe;
var projectDocumentsContentCell = documentEditorLayout.cells('a');
projectDocumentsContentCell.hideHeader();

projectDocumentsContentCell.attachURL("Views/frames/cv_content.php", false,
    {report_content: '', height: (projectDocumentsContentCell.getHeight()) / 1.4});
documentEditorLayout.attachEvent("onContentLoaded", function (id) {
    projectDocumentsContentIframe = documentEditorLayout.cells(id).getFrame();
});

projectDocumentsContentViewerTabbar.addTab('document_viewer', 'Document Viewer');
var document_viewer = projectDocumentsContentViewerTabbar.cells('document_viewer');


projectDocumentsContentViewerTabbar.addTab('document_branches', 'Branches Applicable');
var document_branches = projectDocumentsContentViewerTabbar.cells('document_branches');

var projectDocumentBranchesLayout = document_branches.attachLayout('1C');

var projectDocumentBranchesCell = projectDocumentBranchesLayout.cells('a');
projectDocumentBranchesCell.setText('Branches');

var projectDocumentBranchesGrid = projectDocumentBranchesCell.attachGrid();
projectDocumentBranchesGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
projectDocumentBranchesGrid.setHeader(",Branch Name");
projectDocumentBranchesGrid.setColumnIds("visible,branch_id");
projectDocumentBranchesGrid.setInitWidthsP("7,*");
projectDocumentBranchesGrid.setColTypes('ch,ro');
projectDocumentBranchesGrid.setColAlign('center,left');
projectDocumentBranchesGrid.setColSorting("int,str");
projectDocumentBranchesGrid.setSkin('dhx_web');
projectDocumentBranchesGrid.init();
projectDocumentBranchesGrid.load("Controller/php/data_toc.php?action=26");

projectDocumentBranchesGrid.attachEvent("onCheck", function (id, index, state) {

    var docId = projectDocumentsGrid.getSelectedRowId();
    if (docId) {
        docId = docId.substring(4);
        $.post("Controller/php/data_toc.php?action=24", {
            docId: docId,
            branchId: id,
            nValue: ((state) ? 1 : 0)
        }, function (data) {
            if (data.data.response) {
                dhtmlx.message({title: 'Success', text: data.data.text});
            } else {
                dhtmlx.alert({title: 'Error', text: data.data.text});
            }
        }, 'json');
    } else {
        dhtmlx.alert({title: 'Error', text: "No Document Selected!"});
    }
});

projectDocumentsContentTabbar.addTab('document_files', 'Files');

var document_files = projectDocumentsContentTabbar.cells('document_files');
var documentFilesLayout = document_files.attachLayout('3J');

var documentFilesListCell = documentFilesLayout.cells('a');
documentFilesListCell.hideHeader();
var documentFilesToolbar = document_files.attachToolbar();
documentFilesToolbar.setIconsPath("Views/imgs/");
documentFilesToolbar.loadStruct('<toolbar><item type="button" id="upload" text="Upload New" img="uploads.png" /><item type="separator" id="sep_1" /><item type="button" id="delete" text="Delete" img="deleteall.png" /><item type="separator" id="sep_2" /><item type="button" id="copy" text="Copy" img="newcopy.png" /><item type="separator" id="sep_3" /><item type="buttonSelect" id="show" text="Show" img="show.png"  openAll="true" renderSelect="true" mode="select"><item type="button" id="show_all" text="Show All Documents" img="show.png"/><item type="button" id="show_visible" text="Show Visible Documents" img="show.png"/></item></toolbar>', function () {
});
documentFilesToolbar.attachEvent("onClick", documentFilesToolbarClicked);


var documentFilesGrid = documentFilesListCell.attachGrid();
documentFilesGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
documentFilesGrid.setHeader(["ID", "File Name", "File Type", "Language", "File Size", "Upload Date", "Uploaded By", "Visible"]);
documentFilesGrid.setColTypes("ro,ro,ro,combo,ro,ro,ro,ch");
documentFilesGrid.setSkin('dhx_web');
documentFilesGrid.setColSorting('str,str,str,int,str,str,str,str');
documentFilesGrid.enableCellIds(true);
documentFilesGrid.setColumnIds('id,download,file_type,language_id,file_size,file_upload_date,uploader,visible');
documentFilesGrid.setInitWidthsP('7,*,15,12,8,*,*,8');
documentFilesGrid.enableDragAndDrop(true);
documentFilesGrid.enableMultiselect(true);
documentFilesGrid.attachEvent("onSelectStateChanged", documentFilesGridRowSelect);
documentFilesGrid.attachEvent("onCheck", documentFilesGridChecked);
documentFilesGrid.attachEvent("onEditCell", documentFilesGridEditCell);
documentFilesGrid.init();

documentFilesGridLanguageCombo = documentFilesGrid.getColumnCombo(3);
documentFilesGridLanguageCombo.setSkin("dhx_web");
documentFilesGridLanguageCombo.enableFilteringMode(true);
documentFilesGridLanguageCombo.load("Controller/php/projectDocuments.php?action=24");

documentFilesGrid.attachEvent("onBeforeDrag", function (id) {

    firstId = true;

    var rowIds = documentFilesGrid.getSelectedRowId();
    if (rowIds !== null) {
        var rowIdsArray = rowIds.split(",");
        if (rowIdsArray.length > 1) {
            selectedDragId = documentFilesGrid.getSelectedRowId();
        } else {
            selectedDragId = id;
        }
    } else {
        selectedDragId = id;
    }
    return true;

});

function documentFilesGridEditCell(stage, id, index, new_value) {
    var project_id = projectsTree.getSelectedItemId();
    var cell = documentFilesGrid.cells(id, index);
    if (stage === 2 && !cell.isCheckbox()) {
        var row_id = documentFilesGrid.getSelectedRowId().substring(4);
        if (row_id > 0 || typeof row_id !== 'undefined') {
            var colId = documentFilesGrid.getColumnId(index);
            var colType = documentFilesGrid.fldSort[index];
            $.post("Controller/php/projectDocuments.php?action=27", {
                id: row_id,
                index: index,
                fieldvalue: new_value,
                colId: colId,
                colType: colType
            }, function (data) {
                if (data.data.response) {
                    dhtmlx.message({title: 'Success', text: data.data.text});
                    documentFilesGrid.updateFromXML('Controller/php/projectDocuments.php?action=5&id=' + project_id);
                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');
        }
    } else if (stage === 0 && cell.isCheckbox()) {
        return true;
    }
}

function documentFilesGridChecked(id, index, state) {
    var colId = documentFilesGrid.getColumnId(index);
    var row_id = id.substring(4);
    $.post("Controller/php/projectDocuments.php?action=20", {
        colId: colId,
        id: row_id,
        nValue: ((state) ? 1 : 0)
    }, function (data) {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
}


function documentFilesGridRowSelect(id) {
    projectFileBranchesGrid.updateFromXML("Controller/php/data_toc.php?action=27&file_id=" + id, true, true);
    var filename = documentFilesGrid.cells(id, 1).getValue();
    var filename = filename.replace(/(<([^>]+)>)/ig, "");
    documentFilesViewerCell.attachURL("Controller/files/" + filename);
}

var projectFilesBranchesCell = documentFilesLayout.cells('c');
projectFilesBranchesCell.setText('Branches');

var projectFileBranchesGrid = projectFilesBranchesCell.attachGrid();
projectFileBranchesGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
projectFileBranchesGrid.setHeader(",Branch Name");
projectFileBranchesGrid.setColumnIds("visible,branch_id");
projectFileBranchesGrid.setInitWidthsP("7,*");
projectFileBranchesGrid.setColTypes('ch,ro');
projectFileBranchesGrid.setColAlign('center,left');
projectFileBranchesGrid.setColSorting("int,str");
projectFileBranchesGrid.setSkin('dhx_web');
projectFileBranchesGrid.init();
projectFileBranchesGrid.load("Controller/php/data_toc.php?action=27");

projectFileBranchesGrid.attachEvent("onCheck", function (id, index, state) {

    var fileId = documentFilesGrid.getSelectedRowId();
    if (fileId) {
        fileId = fileId.substring(4);
        $.post("Controller/php/data_toc.php?action=28", {
            fileId: fileId,
            branchId: id,
            nValue: ((state) ? 1 : 0)
        }, function (data) {
            if (data.data.response) {
                dhtmlx.message({title: 'Success', text: data.data.text});
            } else {
                dhtmlx.alert({title: 'Error', text: data.data.text});
            }
        }, 'json');
    } else {
        dhtmlx.alert({title: 'Error', text: "No Document Selected!"});
    }
});

var documentFilesViewerCell = documentFilesLayout.cells('b');
documentFilesViewerCell.setText('Files Viewer');

projectDocumentsContentTabbar.addTab('document_history', 'Document History');

var document_history = projectDocumentsContentTabbar.cells('document_history');

var projectDocumentsHistoryLayout = document_history.attachLayout('2U');
var projectDocumentsHistoryListCell = projectDocumentsHistoryLayout.cells('a');
projectDocumentsHistoryListCell.hideHeader();

var projectDocumentsHistoryGrid = projectDocumentsHistoryListCell.attachGrid();
projectDocumentsHistoryGrid.setIconsPath('./codebase/imgs/');
projectDocumentsHistoryGrid.setSkin('dhx_web');
projectDocumentsHistoryGrid.setHeader(["ID", "Employee", "Date", "Subject", "Category", "Author", "Char"]);
projectDocumentsHistoryGrid.setColumnIds('Report_ID,Report_Employee_ID,Report_Date,Report_Subject,Report_Category,Report_Author,char');
projectDocumentsHistoryGrid.attachHeader("#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
projectDocumentsHistoryGrid.setColTypes("ro,ro,ro,ed,ro,ro,ro");
projectDocumentsHistoryGrid.setInitWidthsP("14,0,25,*,0,25,15");
projectDocumentsHistoryGrid.setColSorting('str,str,date,str,str,str,int');
projectDocumentsHistoryGrid.enableCellIds(true);
projectDocumentsHistoryGrid.setDateFormat("%Y-%m-%d %H:%i:%s");
projectDocumentsHistoryGrid.attachEvent("onSelectStateChanged", projectDocumentsHistoryGridStateChanged); //onRowSelect
projectDocumentsHistoryGrid.init();

function projectDocumentsHistoryGridStateChanged(id, ind) {

    projectDocumentsHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent("");
    window.dhx4.ajax.get("Controller/php/projectDocuments.php?action=14&id=" + id, function (r) {
        var t = null;
        try {
            eval("t=" + r.xmlDoc.responseText);
        } catch (e) {
        }
        ;
        if (t !== null && t.content !== null) {
            projectDocumentsHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent(t.content);
        }
    });
}

var projectDocumentsHistoryToolbar = projectDocumentsHistoryListCell.attachToolbar();
projectDocumentsHistoryToolbar.setIconsPath("Views/imgs/");
projectDocumentsHistoryToolbar.addButton("delete", 1, "Delete", "deleteall.png", "deleteall.png");
projectDocumentsHistoryToolbar.addSeparator("sep1", 2);
projectDocumentsHistoryToolbar.addButton("delete_all", 3, "Delete All", "deleteall.png", "deleteall.png");
projectDocumentsHistoryToolbar.addSeparator("sep2", 4);
projectDocumentsHistoryToolbar.attachEvent("onClick", projectDocumentsHistoryToolbarClicked);

function projectDocumentsHistoryToolbarClicked(id) {
    switch (id) {

        case "delete":

            var row_id = projectDocumentsHistoryGrid.getSelectedRowId();
            if (row_id > 0) {

                dhtmlx.confirm({
                    title: "Confirm",
                    type: "confirm-warning",
                    text: "Are you sure you to delete this Row?",
                    callback: function (ok) {
                        if (ok) {
                            window.dhx4.ajax.get("Controller/php/projectDocuments.php?action=15&id=" + row_id, function (r) {
                                var t = null;
                                try {
                                    eval("t=" + r.xmlDoc.responseText);
                                } catch (e) {
                                }
                                ;
                                if (t !== null && t.data.response) {
                                    projectDocumentsHistoryGrid.deleteRow(row_id);
                                    projectDocumentsHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent("");
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

            var report_id = projectDocumentsGrid.getSelectedRowId().substring(4);
            if (report_id > 0) {

                dhtmlx.confirm({
                    title: "Confirm",
                    type: "confirm-warning",
                    text: "Are you sure you to delete all History?",
                    callback: function (ok) {
                        if (ok) {
                            window.dhx4.ajax.get("Controller/php/projectDocuments.php?action=15&case=1&id=" + report_id, function (r) {
                                var t = null;
                                try {
                                    eval("t=" + r.xmlDoc.responseText);
                                } catch (e) {
                                }
                                ;
                                if (t != null && t.data.response) {
                                    projectDocumentsHistoryGrid.clearAll();
                                    projectDocumentsHistoryContentIframe.contentWindow.tinymce.activeEditor.setContent("");
                                    dhtmlx.message({title: 'Success', text: t.data.text});
                                } else
                                    dhtmlx.alert({title: 'Error', text: t.data.text});
                            });
                        } else {
                            return false;
                        }
                    }

                });
            }
            break;
    }

}

var projectDocumentsHistoryContentIframe;
var projectDocumentsHistoryContentCell = projectDocumentsHistoryLayout.cells('b');
projectDocumentsHistoryContentCell.hideHeader();
projectDocumentsHistoryContentCell.attachURL("Views/frames/history_content.php", false,
    {report_content: '', height: (projectDocumentsHistoryContentCell.getHeight()) / 1.26});
projectDocumentsHistoryLayout.attachEvent("onContentLoaded", function (id) {
    projectDocumentsHistoryContentIframe = projectDocumentsHistoryLayout.cells(id).getFrame();
});

/************************** videos ************************************************/

projectDetailsTabbar.addTab('videos', 'Videos');
var videos = projectDetailsTabbar.cells('videos');
videos.hide();

/************************** objects ************************************************/


projectDetailsTabbar.addTab('objects', 'Objects');
var objects = projectDetailsTabbar.cells('objects');

var projectObjectsToolbar = objects.attachToolbar();
projectObjectsToolbar.setIconsPath("Views/imgs/");

projectObjectsToolbar.loadStruct('Controller/xml/tree_context_xml.php?case=2', function () {
    var text = "<input id = 'objcheck' style= ' -moz-user-select: none;  color: #000000;    float: left;    font-family: Tahoma; font-size: 11px;    margin-left 0;    margin-right: 1px;    margin-top: 3px;    padding: 0 4px;    vertical-align: middle;' type = 'checkbox' checked onclick='handlecheckClick(this);'/>";
    projectObjectsToolbar.setItemText('showall', text);
    projectObjectsToolbar.disableItem("show");
});
projectObjectsToolbar.attachEvent("onClick", projectObjectsToolbarClicked);

function projectObjectsToolbarClicked(id) {
    switch (id) {
        case 'add':
            //check if item is selected
            var treeSel = projectsTree.getSelectedItemId();
            if (treeSel == "") {
                dhtmlx.alert("Please select a project!");
            } else {

                $.post("Controller/php/assignments_objectgrd.php?action=2&treeId=" + treeSel, function (data) {
                    projectObjectsGrid.clearAndLoad("Controller/php/assignments_objectgrd.php?all=true&id=" + treeSel, function () {
                        projectObjectsGrid.selectRowById(data.newId);
                    });
                    dhtmlx.message(data.response);
                }, 'json');
            }
            break;
        case 'refresh':
            var treeSel = projectsTree.getSelectedItemId();
            projectObjectsGrid.clearAndLoad("Controller/php/assignments_objectgrd.php?all=true&id=" + treeSel, function () {

            });
            break;
        case 'up':
            var _id = projectObjectsGrid.getSelectedRowId();
            var postVars = {
                "selId": _id,
                "type": "up"
            }
            $.post("Controller/php/assignments_objectgrd.php?action=10", postVars, function (data) {
                if (data.bool == false) {
                    dhtmlx.alert("Item cant be moved further!")
                } else {
                    projectObjectsGrid.moveRow(_id, "up");
                }
            }, 'json');
            break;
        case 'down':
            var _id = projectObjectsGrid.getSelectedRowId();
            var postVars = {
                "selId": _id,
                "type": "down"
            }
            $.post("Controller/php/assignments_objectgrd.php?action=10", postVars, function (data) {
                if (data.bool == false) {
                    dhtmlx.alert("Item cant be moved further!")
                } else {
                    projectObjectsGrid.moveRow(_id, "down");
                }
            }, 'json');
            break;
        case 'copy2':
            createCopyPop();
            break;
        case 'copy':
            var objectFormData =
                [{
                    type: "settings",
                    position: "label-left",
                    labelWidth: myWidth * 0.07,
                    inputWidth: myWidth * 0.1,
                    offsetTop: 10,
                    offsetLeft: 10
                },
                    {
                        type: "hidden", label: "Template Details ", className: "formbox", width: myWidth * 0.2, list:
                            [
                                {
                                    type: "input",
                                    label: "Object ID:",
                                    name: "val",
                                    value: projectObjectsGrid.getSelectedRowId(),
                                    required: true
                                },
                                {type: "button", name: "submit", value: "submit", offsetLeft: 150}
                            ]
                    }
                ];

            var popupMainWindow = new dhtmlXWindows();
            var objectWindow = popupMainWindow.createWindow("objectWindow", 0, 0, 400, 150);
            objectWindow.center();
            objectWindow.setText("Enter ID to Copy From");
            var objectForm = objectWindow.attachForm(objectFormData);
            objectForm.attachEvent("onButtonClick", function () {

                objectForm.send("Controller/php/assignments_objectgrd.php?action=12", function (loader, response) {
                    //update the task grid                
                    dhtmlx.message("Saved!");
                    var parsedJSON = eval('(' + response + ')');
                    if (parsedJSON.bool == true) {

                        //confrimation
                        dhtmlx.confirm({
                            title: "Create copy of item",
                            ok: "Yes",
                            cancel: "No",
                            text: parsedJSON.message,
                            callback: function (result) {
                                if (result) {
                                    var postVars = {
                                        'id': objectForm.getItemValue("val"),
                                        'treeId': projectsTree.getSelectedItemId()
                                    }
                                    objectWindow.hide();
                                    //create copy
                                    $.post("Controller/php/assignments_objectgrd.php?action=12&case=1", postVars, function (data) {
                                        dhtmlx.message(data.message);
                                        //refresh the grid

                                        projectObjectsGrid.updateFromXML("Controller/php/assignments_objectgrd.php?all=true&id=" + projectsTree.getSelectedItemId(), function () {
                                            projectObjectsGrid.selectRowById(data.newId);
                                        });
                                    }, 'json');
                                }
                            }
                        });
                    } else {
                        dhtmlx.alert(parsedJSON.message);
                    }

                });
            });
            break;
        default:

            if (id.match(/[a-zA-Z]/g)) {
            } else {
                var nm = "English"; //alert(id);
                setCookie('lang_id', id, 1);
                switch (id) {
                    case '1':
                        nm = "English";
                        break;
                    case '4':
                        nm = "Dutch";
                        break;
                    case '6':
                        nm = "French";
                        break;
                    case '7':
                        nm = "German";
                        break;
                }
                projectObjectsToolbar.setItemText("langauge", nm);
                projectObjectsToolbar.setItemImage("langauge", nm + ".png");
            }
            break;
    }
}

function handlecheckClick(obj) {
    switch (obj.checked) {
        case true:
            var treeSel = projectsTree.getSelectedItemId();
            projectObjectsListCell.progressOn();
            projectObjectsGrid.clearAndLoad("Controller/php/assignments_objectgrd.php?all=true&id=" + treeSel, function () {
                projectObjectsListCell.progressOff();
            });
            break;
        case false:
            var treeSel = projectsTree.getSelectedItemId();
            projectObjectsListCell.progressOn();
            projectObjectsGrid.clearAndLoad("Controller/php/assignments_objectgrd.php?all=true&id=" + treeSel, function () {
                projectObjectsListCell.progressOff();
            });
            break;
    }
}

var projectObjectsLayout = objects.attachLayout('2E');
var projectObjectsListCell = projectObjectsLayout.cells('a');
projectObjectsListCell.hideHeader();
//context menu add items
projectObjectsGridContextMenu = new dhtmlXMenuObject();
projectObjectsGridContextMenu.setIconsPath("Views/imgs/");
projectObjectsGridContextMenu.renderAsContextMenu();
projectObjectsGridContextMenu.attachEvent("onClick", treeContextSelect);
projectObjectsGridContextMenu.loadStruct("Controller/php/projectsObjects.php?action=2");

function treeContextSelect(menuitemId, type) {

    switch (menuitemId) {
        case 'addparent':
            //check if item is selected
            var treeSel = projectsTree.getSelectedItemId();
            if (treeSel === "") {
                dhtmlx.alert("Please select a project!");
            } else {
                $.post("Controller/php/assignments_objectgrd.php?action=2&treeId=" + treeSel, function (data) {
                    projectObjectsGrid.updateFromXML("Controller/php/assignments_objectgrd.php?all=true&id=" + treeSel, function () {
                        projectObjectsGrid.selectRowById(data.newId);
                    });
                    dhtmlx.message(data.response);
                }, 'json');
            }
            break;
        case 'add':
            //check if item is selected
            var treeSel = projectsTree.getSelectedItemId();
            if (treeSel == "") {
                dhtmlx.alert("Please select a project!");
            } else {
                //check if row is selected
                var _id = projectObjectsGrid.getSelectedRowId();
                if (_id == null) {
                    dhtmlx.alert("Please select parent row!");
                } else {
                    $.post("Controller/php/assignments_objectgrd.php?action=4&treeId=" + treeSel + "&parent=" + _id, function (data) {
                        projectObjectsGrid.updateFromXML("Controller/php/assignments_objectgrd.php?all=true&id=" + treeSel, function () {
                            projectObjectsGrid.selectRowById(data.newId);
                        });
                        dhtmlx.message(data.response);
                    }, 'json');
                }
            }
            break;
        case 'delete':
            var _id = projectObjectsGrid.getSelectedRowId();
            if (_id == null) {
                dhtmlx.alert("No item selected!");
            } else {
                var hasChildren = projectObjectsGrid.hasChildren(_id)
                if (hasChildren) {
                    dhtmlx.alert("Row has child items!")
                } else {
                    $.post("Controller/php/assignments_objectgrd.php?action=3&id=" + _id, function (data) {
                        projectObjectsGrid.deleteRow(_id);
                        dhtmlx.message(data.response);
                    }, 'json');
                }
            }
            break;
    }
}

var projectObjectsGrid = projectObjectsListCell.attachGrid();
//projectObjectsGrid.setImagePath("https://" + location.host + "/dhtmlxsuite4/codebase/imgs/");
projectObjectsGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
projectObjectsGrid.setHeader(["#", "ID", "Name", "Description", "shortcut", "Type", "Status"]);
projectObjectsGrid.setColumnIds("Nr,id,o_name,o_description,o_shortcut,o_type,o_status");
projectObjectsGrid.attachHeader("#numeric_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
projectObjectsGrid.setColTypes("tree,ro,ed,ed,ed,combo,combo");
projectObjectsGrid.setSkin('dhx_web');
projectObjectsGrid.setColSorting('int,int,str,str,str,str,str');
projectObjectsGrid.setInitWidthsP("9,10,20,*,10,10,6");
projectObjectsGrid.attachEvent("onSelectStateChanged", doOnProjectObjectsGridRowSelect); //onRowSelect
projectObjectsGrid.attachEvent("onEditCell", doOnEditProjectObjectsGrid);
//projectObjectsGrid.attachEvent("onCheck", doOnProjectObjectsGridChecked);
projectObjectsGrid.enableContextMenu(projectObjectsGridContextMenu);
projectObjectsGrid.init();

var objectsGridTypeCombo = projectObjectsGrid.getColumnCombo(5);
objectsGridTypeCombo.load("Controller/xml/objects_type.php");

var statusCombo = projectObjectsGrid.getColumnCombo(6);
statusCombo.load("Controller/php/projectsObjects.php?action=3");

function doOnProjectObjectsGridRowSelect(id, ind) {
    objectInfoIframe.contentWindow.tinymce.activeEditor.setContent("");
    objectDetailsForm.clear();
    objectDetailsForm.load('Controller/php/assignments_objectgrd.php?action=6&id=' + id);
    objectTranslationsGrid.clearAndLoad('Controller/php/object_translations.php?all=true&id=' + id, function () {
    });
}

function doOnProjectObjectsGridChecked(id, index, state) {

    var colId = projectObjectsGrid.getColumnId(index);
    window.dhx4.ajax.post("Controller/php/productTemplates.php?action=6", "colId=" + colId + "&id=" + id + "&nValue=" + ((state) ? 1 : 0), function (r) {
        var t = null;
        try {
            eval("t=" + r.xmlDoc.responseText);
        } catch (e) {
        }
        ;
        if (t !== null && t.data.response) {
            dhtmlx.message({title: 'Success', text: t.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: t.data.text});
        }
    });
}

function doOnEditProjectObjectsGrid(stage, id, index, new_value) {
    var colId = projectObjectsGrid.getColumnId(index);
    var colType = projectObjectsGrid.fldSort[index];
    if (stage == 2) {
        $.post("Controller/php/assignments_objectgrd.php?action=5", "id=" + id + "&index=" + index + "&fieldvalue=" + new_value + "&colId=" + colId + "&colType=" + colType, function (data) {
            dhtmlx.message(data.response);
            objectDetailsForm.clear();
            objectDetailsForm.load('Controller/php/assignments_objectgrd.php?action=6&id=' + id);
        }, 'json');
    }
    return true;
}


var projectObjectsDetailsCell = projectObjectsLayout.cells('b');
var projectObjectsDetailsTabbar = projectObjectsDetailsCell.attachTabbar();
projectObjectsDetailsTabbar.addTab('object_details', 'Object Details');
var object_details = projectObjectsDetailsTabbar.cells('object_details');
object_details.setActive();
var objectDetailsToolbar = object_details.attachToolbar();
objectDetailsToolbar.setIconsPath("Views/imgs/");
objectDetailsToolbar.loadStruct('<toolbar><item type="button" id="save" text="Save" img="save.gif" /></toolbar>', function () {
});
objectDetailsToolbar.attachEvent("onClick", objectDetailsToolbarClicked);

function objectDetailsToolbarClicked(id) {
    switch (id) {
        case 'save':
            var _id = projectObjectsGrid.getSelectedRowId();
            if (_id == null) {
                dhtmlx.alert("Please select parent row!");
            } else {
                objectDetailsForm.send('Controller/php/assignments_objectgrd.php?action=7&id=' + _id, function () {
                    var treeSel = projectsTree.getSelectedItemId();
                    projectObjectsGrid.updateFromXML("Controller/php/assignments_objectgrd.php?all=true&id=" + treeSel);
                    dhtmlx.message("Saved!");
                });
            }
            break;
    }
}

var objectDetailsFormdata =
    [{
        type: "settings",
        position: "label-left",
        labelWidth: myWidth * 0.06,
        inputWidth: myWidth * 0.2,
        offsetTop: 8,
        offsetLeft: 10
    },
        {
            type: "hidden", label: "General ", className: "formbox", width: myWidth * 0.7, list:
                [
                    {type: "input", label: "Object ID", name: "id", value: ""},
                    {
                        type: "input",
                        label: "Description",
                        name: "o_description",
                        value: "",
                        style: "width:" + (myWidth * 0.25) + ";"
                    },
                    {
                        type: "editor",
                        label: "Requirements",
                        rows: 4,
                        name: "o_requirements",
                        value: "",
                        style: "width:" + (myWidth * 0.25) + ";height:" + (myWidth * 0.07) + ";"
                    },
                    {
                        type: "editor",
                        label: "How to use",
                        name: "o_usage",
                        rows: 4,
                        value: "",
                        style: "width:" + (myWidth * 0.25) + ";height:" + (myWidth * 0.08) + ";"
                    },
                    {type: "newcolumn", offset: 20},
                    {
                        type: "editor",
                        label: "Results",
                        name: "o_results",
                        rows: 4,
                        value: "",
                        style: "width:" + (myWidth * 0.25) + ";height:" + (myWidth * 0.07) + ";"
                    }, {type: "combo", label: "Type", name: "o_type", value: ""},
                    {type: "input", label: "Table Field", name: "o_table_field", value: ""},
                    {type: "combo", label: "Data Type", name: "o_data_type", value: ""},
                    {type: "input", label: "Values", name: "o_values", value: ""}
                ]
        }
    ];
var objectDetailsForm = object_details.attachForm(objectDetailsFormdata);
var type_Combo = objectDetailsForm.getCombo("o_type");
type_Combo.load("Controller/xml/objects_type.php");

var data_type_Combo = objectDetailsForm.getCombo("o_data_type");
data_type_Combo.load("Controller/xml/data_types.php");

projectObjectsDetailsTabbar.addTab('object_info', 'Information');
var object_info = projectObjectsDetailsTabbar.cells('object_info');
var objectInformationLayout = object_info.attachLayout('2U');
var objectInformationListCell = objectInformationLayout.cells('a');
objectInformationListCell.setText('Translations');
var objectInformationToolbar = objectInformationListCell.attachToolbar();
objectInformationToolbar.setIconsPath("Views/imgs/");
objectInformationToolbar.loadStruct('<toolbar><item type="button" id="new" text="Add New" img="new.gif" /><item type="separator" id="sep_16" /><item type="button" id="delete" text="Delete" img="deleteall.png" /></toolbar>', function () {
});
objectInformationToolbar.attachEvent("onClick", objectInformationToolbarClicked);

function objectInformationToolbarClicked(id) {
    switch (id) {
        case 'new':
            //check if item is selected
            var _id = projectObjectsGrid.getSelectedRowId();
            if (_id == null) {
                dhtmlx.alert("Please select a row from object description!");
            } else {
                $.post("Controller/php/object_translations.php?action=1&id=" + _id, function (data) {
                    objectTranslationsGrid.updateFromXML('Controller/php/object_translations.php?id=' + _id, function () {
                        objectTranslationsGrid.selectRowById(data.newId)
                    });
                    if (data.bool == true) {
                        dhtmlx.alert(data.response);
                    } else {
                        dhtmlx.message(data.response);
                    }
                }, 'json');
            }
            break;
        case 'delete':
            var _id = objectTranslationsGrid.getSelectedRowId();
            if (_id == null) {
                dhtmlx.alert("No item selected!");
            } else {
                $.post("Controller/php/object_translations.php?action=5&id=" + _id, function (data) {
                    objectTranslationsGrid.deleteRow(_id);
                    dhtmlx.message(data.response);
                }, 'json');
            }
            break;
    }
}


var objectTranslationsGrid = objectInformationListCell.attachGrid();
objectTranslationsGrid.setIconsPath('./codebase/imgs/');
objectTranslationsGrid.setHeader(["#", "Object Name", "Language", "Help Title", "Help Information"]);
objectTranslationsGrid.setColumnIds("Nr,object_name,language_id,object_title,object_description");
objectTranslationsGrid.setColTypes("cntr,ed,combo,ed,ed");
objectTranslationsGrid.setSkin('dhx_web');
objectTranslationsGrid.setColSorting('int,str,str,str,str');
objectTranslationsGrid.enableCellIds(true);
objectTranslationsGrid.setColumnIds("Nr,object_name,language_id,object_title,object_description");
objectTranslationsGrid.setInitWidthsP("9,*,20,*,0");
objectTranslationsGrid.attachEvent("onSelectStateChanged", doOnObjectTranslationsGridRowSelect); //onRowSelect
objectTranslationsGrid.attachEvent("onEditCell", doOnEditObjectTranslationsGrid);
//objectTranslationsGrid.attachEvent("onCheck", doOnObjectTranslationsGridChecked);
objectTranslationsGrid.init();
var objectTranslationsGridLangCombo = objectTranslationsGrid.getColumnCombo(2);
objectTranslationsGridLangCombo.load("Controller/php/projectsObjects.php?action=4");

function doOnObjectTranslationsGridRowSelect(id, ind) {
    objectInfoIframe.contentWindow.tinymce.activeEditor.setContent("");
    window.dhx4.ajax.get("Controller/php/object_translations.php?action=3&id=" + id, function (r) {
        var t = null;
        try {
            eval("t=" + r.xmlDoc.responseText);
        } catch (e) {
        }
        ;
        if (t !== null && t.content !== null) {
            objectInfoIframe.contentWindow.tinymce.activeEditor.setContent(t.content);
        }
    });
}

function doOnObjectTranslationsGridChecked(id, index, state) {

    var colId = projectObjectsGrid.getColumnId(index);
    window.dhx4.ajax.post("Controller/php/productTemplates.php?action=6", "colId=" + colId + "&id=" + id + "&nValue=" + ((state) ? 1 : 0), function (r) {
        var t = null;
        try {
            eval("t=" + r.xmlDoc.responseText);
        } catch (e) {
        }
        ;
        if (t !== null && t.data.response) {
            dhtmlx.message({title: 'Success', text: t.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: t.data.text});
        }
    });
}

function doOnEditObjectTranslationsGrid(stage, id, index, new_value) {
    var colId = objectTranslationsGrid.getColumnId(index);
    var colType = objectTranslationsGrid.fldSort[index];
    if (stage == 2) {
        $.post("Controller/php/object_translations.php?action=2", "id=" + id + "&index=" + index + "&fieldvalue=" + new_value + "&colId=" + colId + "&colType=" + colType, function (data) {
            dhtmlx.message(data.response);
        }, 'json');
    }
    return true;
}


var objectInfoContentCell = objectInformationLayout.cells('b');
objectInfoContentCell.setText('Information Content');
var objectInfoIframe;
objectInfoContentCell.hideHeader();
objectInfoContentCell.attachURL("Views/frames/info_content.php", false,
    {report_content: '', height: (objectInfoContentCell.getHeight()) / 1.25});
objectInformationLayout.attachEvent("onContentLoaded", function (id) {
    objectInfoIframe = objectInformationLayout.cells(id).getFrame();
});
projectDetailsTabbar.addTab('planning', 'Planning');
var planning = projectDetailsTabbar.cells('planning');
var projectPlanningLayout = planning.attachLayout('2E');
var projectPlanningListCell = projectPlanningLayout.cells('a');
var projectPlanningListToolbar = projectPlanningListCell.attachToolbar();
projectPlanningListToolbar.setIconsPath("Views/imgs/");
projectPlanningListToolbar.loadStruct('<toolbar><item type="buttonTwoState" id="grid" text="Grid View" img="" /><item type="separator" id="sep_16" img="" /><item type="buttonTwoState" id="gantt" text="Gantt View" /></toolbar>', function () {
    projectPlanningListToolbar.setItemState("grid", true, false);
});
projectPlanningListToolbar.attachEvent("onStateChange", function (id, state) {
    if (id === "grid" && state === true) {
        projectPlanningListToolbar.setItemState("gantt", false, false);
        projectPlanningListTabbar.tabs("gantt").hide();
        projectPlanningListTabbar.tabs("grid").show(true);
    } else if (id === "gantt" && state === true) {
        projectPlanningListToolbar.setItemState("grid", false, false);
        projectPlanningListTabbar.tabs("grid").hide();
        projectPlanningListTabbar.tabs("gantt").show(true);
        var projectId = projectsTree.getSelectedItemId();
        if (projectId > 0) {
            projectPlanningListCell.progressOn();
            gantt.attachURL('gantt.php?id=' + projectId + "&eid=" + uID);
        }
    } else {
        projectPlanningListToolbar.setItemState(id, true, false);
    }

});

var projectPlanningListTabbar = projectPlanningListCell.attachTabbar();
projectPlanningListTabbar.addTab('grid', 'Events');

var grid = projectPlanningListTabbar.cells('grid');
grid.setActive();

var projectsPlanningGridToolbar = grid.attachToolbar();
projectsPlanningGridToolbar.setIconsPath("Views/imgs/");
projectsPlanningGridToolbar.addSeparator("sep1", 1);
projectsPlanningGridToolbar.addButton("add", 2, "Add Event", "new.gif", "new.gif");
projectsPlanningGridToolbar.addSeparator("sep2", 3);
projectsPlanningGridToolbar.addButton("delete", 4, "Delete Event", "deleteall.png", "deleteall.png");
projectsPlanningGridToolbar.addSeparator("sep3", 5);
projectsPlanningGridToolbar.addButton("postpone", 6, "Postpone Event(s)", "postpone.png", "postpone.png");
projectsPlanningGridToolbar.addSeparator("sep4", 7);
projectsPlanningGridToolbar.addButton("copy", 8, "Copy Planning", "newcopy.png", "newcopy.png");
projectsPlanningGridToolbar.addSeparator("sep5", 9);
projectsPlanningGridToolbar.addButton("previous", 10, "Previous", "ArrowLeft.png", "ArrowLeft.png");
//projectsPlanningGridToolbar.addInput("salary_date", 11, "Salary Date");
projectsPlanningGridToolbar.addInput("actual_date", 12, "Actual Date");
projectsPlanningGridToolbar.addButton("next", 13, "Next", "ArrowRight.png", "ArrowRight.png");
projectsPlanningGridToolbar.addButton("excel", 14, "Import From Excel", "excel.png");
//projectsPlanningGridToolbar.addButtonSelect("month_report", 15, "Choose Month", month_data);
projectsPlanningGridToolbar.setValue("actual_date", today_actual_date);
projectsPlanningGridToolbar.hideItem("actual_date");
projectsPlanningGridToolbar.hideItem("next");
projectsPlanningGridToolbar.hideItem("previous");


var ev_actual_date = projectsPlanningGridToolbar.objPull[projectsPlanningGridToolbar.idPrefix + "actual_date"].obj.firstChild;
var ev_calendar = new dhtmlxCalendarObject(ev_actual_date);
ev_calendar.attachEvent("onClick", function (date, state) {
    var actual_date = new Date(date);
    var actual_date = new Date(actual_date).format('Y-m-d');
    ev_set_toolbar_date(actual_date, all);
});

function ev_set_toolbar_date(actual_date, all) {

    var projectId = projectsTree.getSelectedItemId();
    projectsPlanningGridToolbar.setValue("actual_date", actual_date);

    projectPlanningGrid.clearAndLoad("Controller/php/projectsPlanning.php?action=1&id=" + projectId + "&start_date=" + actual_date + "&all=" + all);
    gantt.attachURL('gantt.php?id=' + projectId + '&start_date=' + actual_date + "&eid=" + uID);
}

projectsPlanningGridToolbar.attachEvent("onClick", projectsPlanningGridToolbarClicked);

function projectsPlanningGridToolbarClicked(id) {

    var projectId = projectsTree.getSelectedItemId();

    if (projectId > 0) {

        switch (id) {

            case 'add':
                var projectId = projectsTree.getSelectedItemId();
                if (projectId > 0) {
                    var documentId = projectDocumentsGrid.getSelectedRowId();
                    if (documentId) {
                        var documentId = projectDocumentsGrid.getSelectedRowId().substring(4);
                        window.dhx4.ajax.get("Controller/php/projectsPlanning.php?action=3&project_id=" + projectId + "&doc_id=" + documentId + "&eid=" + uID, function (r) {
                            var t = null;
                            try {
                                eval("t=" + r.xmlDoc.responseText);
                            } catch (e) {
                            }
                            ;
                            if (t !== null && t.data.response) {
                                dhtmlx.message({title: 'Success', text: t.data.text});
                                projectPlanningGrid.updateFromXML('Controller/php/projectsPlanning.php?action=1&id=' + projectId, true, true, function () {
                                    projectPlanningGrid.selectRowById(t.data.newId);
                                });
                            } else {
                                dhtmlx.alert({title: 'Error', text: t.data.text});
                            }
                        });
                    } else {
                        dhtmlx.alert({type: "Warning", text: "Please select a Document!"});
                    }
                } else {
                    dhtmlx.alert({type: "Warning", text: "Please select a project!"});
                }

                break;

            case 'delete':

                var projectId = projectsTree.getSelectedItemId();
                var row_id = projectPlanningGrid.getSelectedRowId();

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
                                            if (t != null && t.data.response) {
                                                dhtmlx.message({title: 'Success', text: t.data.text});
                                                projectPlanningGrid.deleteRow(row_id);
                                                eventDetailsForm.clear();
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

            case 'postpone':
                //postpone selected grid event
                var projectId = projectsTree.getSelectedItemId();
                var postponeFormData =
                    [
                        {
                            type: "settings",
                            position: "label-left",
                            labelWidth: myWidth * 0.05,
                            inputWidth: myWidth * 0.1,
                            offsetTop: 10,
                            offsetLeft: 10
                        },
                        {
                            type: "hidden", label: "Task Details ", className: "formbox", width: myWidth * 0.18, list:
                                [
                                    {type: "input", label: "Days:", name: "days", value: "1"},
                                    {type: "button", name: "submit", value: "submit", offsetLeft: 100}
                                ]
                        }
                    ];

                var popupMainWindow = new dhtmlXWindows();
                var popupWindow = popupMainWindow.createWindow("w12", 0, 0, myWidth * 0.25, myHeight * 0.15);
                popupWindow.center();
                popupWindow.setText("Enter Number of Days");
                var postponeEventForm = popupWindow.attachForm(postponeFormData);
                postponeEventForm.attachEvent("onButtonClick", function () {

                    var days = postponeEventForm.getItemValue("days");
                    var row_id = projectPlanningGrid.getSelectedRowId();
                    var row_id_array = row_id.split(',');
                    //var xrow_id;

                    for (var i = 0; i < row_id_array.length; i++) {
                        row_id = row_id_array[i];
                        $.get("Controller/php/projectsPlanning.php?action=5&id=" + row_id + "&days=" + days, function (data) {
                            projectPlanningGrid.updateFromXML('Controller/php/projectsPlanning.php?action=1&id=' + projectId);
                        }, "json");
                    }
                    popupWindow.hide();
                });
                break;

            case 'copy':

                var master = projectsTree.getSelectedItemId();

                if (master == '') {
                    dhtmlx.alert("Please select a project!");
                    return false;
                }

                var event_id = projectPlanningGrid.getSelectedRowId();

                var copyFormData =
                    [
                        {
                            type: "settings",
                            position: "label-left",
                            labelWidth: myWidth * 0.05,
                            inputWidth: myWidth * 0.1,
                            offsetTop: 10,
                            offsetLeft: 10
                        },
                        {
                            type: "hidden",
                            label: "Applicants Details ",
                            className: "formbox",
                            width: myWidth * 0.18,
                            list: [
                                {type: "hidden", label: "Project", name: "project_id", value: ""},
                                {type: "hidden", label: "Event", name: "event_id", value: ""},
                                {
                                    type: "label", offsetTop: 0, list: [
                                        {
                                            type: "calendar",
                                            position: "label-left",
                                            dateFormat: "%Y-%m-%d",
                                            serverDateFormat: "%Y-%m-%d",
                                            enableTime: false,
                                            label: "Start Date",
                                            inputWidth: 150,
                                            name: "start_date",
                                            value: "",
                                            readonly: false,
                                            offsetLeft: 0
                                        },
                                        {type: "newcolumn", offset: 30},
                                        {
                                            type: "input",
                                            label: "Begin Time",
                                            position: "label-left",
                                            name: "begn",
                                            value: "",
                                            inputWidth: 55,
                                            offsetLeft: 5
                                        },
                                    ]
                                },
                                {
                                    type: "label", offsetTop: 0, list: [
                                        {
                                            type: "calendar",
                                            position: "label-left",
                                            dateFormat: "%Y-%m-%d",
                                            serverDateFormat: "%Y-%m-%d",
                                            enableTime: false,
                                            label: "End Date",
                                            inputWidth: 150,
                                            name: "end_date",
                                            value: "",
                                            readonly: false,
                                            offsetLeft: 0
                                        }, //%H:%i
                                        {type: "newcolumn", offset: 30},
                                        {
                                            type: "input",
                                            label: "End Time",
                                            position: "label-left",
                                            name: "end",
                                            value: "",
                                            inputWidth: 55,
                                            offsetLeft: 5
                                        },
                                    ]
                                },
                                {type: "button", name: "submit", value: "SUBMIT", offsetLeft: 250, offsetTop: 50}
                            ]
                        }
                    ];

                var windows = new dhtmlXWindows();
                var window_4 = windows.createWindow('window_4', 0, 0, myWidth * 0.3, myHeight * 0.3);
                window_4.setText('Postpone Task');
                window_4.setModal(1);
                window_4.centerOnScreen();
                window_4.button('park').hide();
                window_4.button('minmax').hide();

                var copyEventForm = window_4.attachForm(copyFormData);
                copyEventForm.getInput("start_date").style.backgroundImage = "url(https://" + location.host + "/dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
                copyEventForm.getInput("start_date").style.backgroundPosition = "center right";
                copyEventForm.getInput("start_date").style.backgroundRepeat = "no-repeat";
                copyEventForm.getInput("end_date").style.backgroundImage = "url(https://" + location.host + "/dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
                copyEventForm.getInput("end_date").style.backgroundPosition = "center right";
                copyEventForm.getInput("end_date").style.backgroundRepeat = "no-repeat";

                var start_date = projectPlanningGrid.cells(event_id, 3).getValue();
                var actual_start_date = new Date(start_date);
                actual_start_date = new Date(actual_start_date.getFullYear(), actual_start_date.getMonth(), actual_start_date.getDate() + 1).format('Y-m-d');

                copyEventForm.setItemValue("start_date", actual_start_date);

                var begin_time = new Date(start_date).format('H:i');
                copyEventForm.setItemValue("begn", begin_time);

                var end_date = projectPlanningGrid.cells(event_id, 4).getValue();
                var actual_end_date = new Date(end_date);
                actual_end_date = new Date(actual_end_date.getFullYear(), actual_end_date.getMonth(), actual_end_date.getDate() + 1).format('Y-m-d');
                copyEventForm.setItemValue("end_date", actual_end_date);

                var end_time = new Date(end_date).format('H:i');
                copyEventForm.setItemValue("end", end_time);

                copyEventForm.attachEvent("onButtonClick", function () {

                    copyEventForm.setItemValue('event_id', event_id);
                    copyEventForm.setItemValue('project_id', master);

                    var values = copyEventForm.getFormData();

                    values['start_date'] = copyEventForm.getCalendar('start_date').getFormatedDate('%d-%m-%Y');
                    values['end_date'] = copyEventForm.getCalendar('end_date').getFormatedDate('%d-%m-%Y');
                    projectPlanningListCell.progressOn();

                    $.post("Controller/php/projectsPlanning.php?action=6", values, function (data) {
                        projectPlanningListCell.progressOff();
                        window_4.close();
                        if (data.data.response) {
                            projectPlanningGrid.updateFromXML('Controller/php/projectsPlanning.php?action=1&id=' + master, true, true, function () {
                                projectPlanningGrid.selectRowById(data.data.newId);
                            });
                            dhtmlx.message({type: "Success", text: data.data.text});
                        } else {
                            dhtmlx.alert({type: "Error", text: data.data.text});
                        }
                    }, 'json');
                });
                break;

            case "previous":

                var actual_date = projectsPlanningGridToolbar.getValue("actual_date");
                actual_date = new Date(actual_date);
                actual_date = new Date(actual_date.getFullYear(), actual_date.getMonth(), actual_date.getDate() - 1).format('Y-m-d');
                ev_set_toolbar_date(actual_date, all);
                break;

            case "next":

                var actual_date = projectsPlanningGridToolbar.getValue("actual_date");
                actual_date = new Date(actual_date);
                actual_date = new Date(actual_date.getFullYear(), actual_date.getMonth(), actual_date.getDate() + 1).format('Y-m-d');
                ev_set_toolbar_date(actual_date, all);
                break;

            case 'excel':
                var projectId = projectsTree.getSelectedItemId();
                if (projectId > 0) {
                    var documentId = projectDocumentsGrid.getSelectedRowId().substring(4);
                    if (documentId > 0) {
                        var uploadBoxformData = [{
                            type: "fieldset",
                            label: "Uploader",
                            list: [{
                                type: "upload",
                                name: "myFiles",
                                inputWidth: 330,
                                url: "Controller/php/projectsPlanning.php?action=13&project_id=" + projectId + "&doc_id=" + documentId + "&eid=" + uID,
                                swfPath: "https://" + location.host + "/dhtmlxsuite4/codebase/ext/uploader.swf",
//                                            swfUrl: "https://" + location.host + "/script/dhtmlx3.6pro/dhtmlxForm/samples/07_uploader/php/dhtmlxform_item_upload.php"
                            }]
                        }];

                        var popupMainWindow = new dhtmlXWindows();
                        var popupWindow = popupMainWindow.createWindow("upload_win1", 0, 0, 450, 210);
                        popupWindow.center();
                        popupWindow.setText("Upload excel file");

                        //add form
                        var uploadForm = popupWindow.attachForm(uploadBoxformData);
                        uploadForm.attachEvent("onUploadFile", function (realName, serverName) {
                            popupMainWindow.window('upload_win1').hide();
                            dhtmlx.message({type: "Success", text: "upload successful"});
                            projectPlanningGrid.updateFromXML('Controller/php/projectsPlanning.php?action=1&id=' + projectId, true, true);
                        }, 'json');

                        uploadForm.attachEvent("onUploadFail", function (realName) {
                            dhtmlx.alert('The was an error uploading ' + realName);
                        });
                    } else {
                        dhtmlx.alert({type: "Warning", text: "Please select a Document!"});
                    }
                } else {
                    dhtmlx.alert({type: "Warning", text: "Please select a project!"});
                }
                break;
        }
    } else {
        dhtmlx.alert({type: "Error", text: 'No Project Selected!'});
    }
}

var projectPlanningGrid = grid.attachGrid();
projectPlanningGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
projectPlanningGrid.setSkin('dhx_web');
projectPlanningGrid.setHeader(["ID", "Event Name", "Assigned To", "Begin Date", "End Date", "Details", "Visible", "Main Task", "Done", "Procedure"],
    null,
    ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:center;", "text-align:center;", "text-align:center;", "text-align:center;"]);
projectPlanningGrid.setColumnIds("event_id,details,employee_id,start_date,end_date,event_name,visible,main_task,completed,is_procedure");
projectPlanningGrid.attachHeader('#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,,,,');
projectPlanningGrid.setColTypes("ro,ro,ro,dhxCalendar,dhxCalendar,ed,ch,ch,ch,ch");
projectPlanningGrid.setDateFormat("%Y-%m-%d %H:%i");
projectPlanningGrid.setColAlign('left,left,left,left,left,left,center,center,center,center');
projectPlanningGrid.setColSorting('str,str,str,date,date,str,int,int,int,int');
projectPlanningGrid.enableCellIds(true);
projectPlanningGrid.enableMultiline(true);
projectPlanningGrid.enableMultiselect(true);
projectPlanningGrid.enableDragAndDrop(true);
projectPlanningGrid.setInitWidthsP('8,18,9,14,14,*,6,6,6,6');
projectPlanningGrid.attachEvent("onSelectStateChanged", doOnProjectPlanningGridRowSelect); //onRowSelect
projectPlanningGrid.attachEvent("onEditCell", doOnEditprojectPlanningGrid);
//tasksGrid.attachEvent("onXLE", doOntasksGridXLE);
projectPlanningGrid.attachEvent("onCheck", doOnProjectPlanningGridChecked);
projectPlanningGrid.init();


projectPlanningGrid.attachEvent("onBeforeDrag", function (id) {
    selectedDragId = projectPlanningGrid.getSelectedRowId();
    return true;
});

function doOnEditprojectPlanningGrid(stage, id, index, new_value, old_value, cellIndex) {

    var item_id = projectsTree.getSelectedItemId();
    var event_id = projectPlanningGrid.getSelectedRowId();
    var cell = projectPlanningGrid.cells(id, index);
    if (stage == 2 && !cell.isCheckbox()) {
        if (event_id > 0 || typeof event_id != 'undefined') {
            var colId = projectPlanningGrid.getColumnId(index);
            var colType = projectPlanningGrid.fldSort[index];
            $.get("Controller/php/projectsPlanning.php?action=11&id=" + event_id + "&index=" + index + "&fieldvalue=" + new_value + "&colId=" + colId + "&colType=" + colType, function (data) {
                if (data.data.response) {
                    dhtmlx.message({type: "Success", text: data.data.text});
                    projectPlanningGrid.updateFromXML('Controller/php/projectsPlanning.php?action=1&id=' + item_id, true, true);
                    eventDetailsForm.clear();
                    eventDetailsForm.load('Controller/php/recurring.php?action=3&id=' + event_id, function (id, response) {
                        var rec_type = eventDetailsForm.getItemValue('rec_type');
                        for (var i = 0; i < 8; i++) {
                            eventDetailsForm.uncheckItem('days_select[' + i + ']');
                        }
                        var s = '' + rec_type + '';
                        s = rec_type.split(",");
                        for (var k = 0; k < s.length; k++) {
                            var d = s[k];
                            eventDetailsForm.checkItem('days_select[' + d + ']');
                        }
                        if (eventDetailsForm.isItemChecked('variable') == true) {
                            disableCheckBox();
                        } else {
                            eventDetailsForm.setItemLabel("label_days", "Select Days");
                        }
                        //load the combo checked values
                        employeeCombo.clearAll();
                        employeeCombo.load("Controller/php/projectsPlanning.php?action=29&evt_id=" + event_id);
                    });
                } else
                    dhtmlx.alert({title: 'Error', text: data.data.text});
            }, 'json');
        }
    } else if (stage === 0 && cell.isCheckbox()) {
        return true;
    }
}

function doOnProjectPlanningGridChecked(id, index, state) {
    var colId = projectPlanningGrid.getColumnId(index);
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

function doOnProjectPlanningGridRowSelect(id, ind) {

    //get the event selected 
    var event_id = id;
    //load form details
    eventDetailsForm.load('Controller/php/recurring.php?action=3&id=' + event_id, function (id, response) {
        var rec_type = eventDetailsForm.getItemValue('rec_type');
        for (var i = 0; i < 8; i++) {
            eventDetailsForm.uncheckItem('days_select[' + i + ']');
        }
        var s = '' + rec_type + '';
        s = rec_type.split(",");
        for (var k = 0; k < s.length; k++) {
            var d = s[k];
            eventDetailsForm.checkItem('days_select[' + d + ']');
        }
        if (eventDetailsForm.isItemChecked('variable') == true) {
            disableCheckBox();
        } else {
            eventDetailsForm.setItemLabel("label_days", "Select Days");
        }
        //load the combo checked values
        employeeCombo.clearAll();
        employeeCombo.load("Controller/php/projectsPlanning.php?action=29&evt_id=" + event_id);
    });
    eventReoccurencesGrid.clearAndLoad("Controller/php/generated_tasks.php?id=" + event_id);
}

projectPlanningListTabbar.addTab('gantt', 'Events');
var gantt = projectPlanningListTabbar.cells('gantt');
projectPlanningListTabbar.tabs("gantt").hide();
var projectPlanningDetailsCell = projectPlanningLayout.cells('b');
var projectPlanningDetailsTabber = projectPlanningDetailsCell.attachTabbar();
projectPlanningDetailsTabber.addTab('event_details', 'Event Details');
var event_details = projectPlanningDetailsTabber.cells('event_details');
event_details.setActive();
var eventDetailsToolbar = event_details.attachToolbar();
eventDetailsToolbar.setIconsPath("Views/imgs/");
eventDetailsToolbar.loadStruct('<toolbar><item type="button" id="save" text="Save" img="save.gif" /></toolbar>', function () {
});
eventDetailsToolbar.attachEvent('onClick', eventDetailsToolbarClicked);

function eventDetailsToolbarClicked(id) {
    switch (id) {
        case 'save':
            var eventId = projectPlanningGrid.getSelectedRowId();
            var projectId = projectsTree.getSelectedItemId();
            if (eventId > 0) {

                var emp_assigned = eventDetailsForm.getCombo("emp").getChecked();
                var approved_by = eventDetailsForm.getCombo("approved_by").getChecked();
                //master_form_details.setItemValue("emp", emp_assigned);
                eventDetailsForm.setItemValue("approved_by", approved_by);
                if (emp_assigned.length < 1) {
                    dhtmlx.alert("Select employee from dropdown!")
                } else {
                    projectPlanningListCell.progressOn();
                    eventDetailsForm.send("Controller/php/projectsPlanning.php?action=30&approved=" + approved_by + "&eid=" + uID, function (loader, response) {
                        var parsedJSON = eval('(' + response + ')');
                        if (parsedJSON.data.response) {
                            dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                            var actvId = projectPlanningListTabbar.getActiveTab();
                            if (actvId === "grid") {
                                projectPlanningGrid.updateFromXML("Controller/php/projectsPlanning.php?action=1&id=" + projectId, true, true, function () {
                                    //load the combo checked values
                                    employeeCombo.clearAll();
                                    employeeCombo.load("Controller/php/projectsPlanning.php?action=29&evt_id=" + eventId);
                                    projectPlanningListCell.progressOff();
                                });
                            }
                            if (actvId === "gantt") {
                                var actual_date = projectsPlanningGridToolbar.getValue("actual_date");
                                actual_date = new Date(actual_date);
                                actual_date = new Date(actual_date.getFullYear(), actual_date.getMonth(), actual_date.getDate() - 1).format('Y-m-d');
                                gantt.attachURL('gantt.php?id=' + projectId + '&start_date=' + actual_date + "&eid=" + uID);
                            }

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


eventDetailsFormdata = [
    {
        type: "settings",
        position: "label-left",
        labelWidth: projectPlanningDetailsCell.getWidth() * 0.07,
        inputWidth: projectPlanningDetailsCell.getWidth() * 0.25,
        offsetTop: 8,
        offsetLeft: 20
    },

    {type: "hidden", label: "ID", name: "event_id", value: ""},
    {type: "input", label: "Event Name", name: "event_name", value: ""},
    {type: "input", label: "Details", rows: 5, name: "details", value: ""},
    {type: "combo", comboType: "checkbox", label: "Assigned To", name: "emp", value: ""},
    {
        type: "label", offsetTop: 0, list: [
            {
                type: "calendar",
                position: "label-left",
                dateFormat: "%Y-%m-%d",
                serverDateFormat: "%Y-%m-%d",
                enableTime: false,
                label: "Start Date",
                inputWidth: 150,
                name: "start_date",
                value: "",
                readonly: false,
                offsetLeft: 0
            },
            {type: "newcolumn", offsetLeft: 20},
            {
                type: "input",
                label: "Begin Time",
                position: "label-left",
                name: "begn",
                value: "",
                inputWidth: 55,
                offsetLeft: 5
            },
        ]
    },
    {
        type: "label", offsetTop: 0, list: [
            {
                type: "calendar",
                position: "label-left",
                dateFormat: "%Y-%m-%d",
                serverDateFormat: "%Y-%m-%d",
                enableTime: false,
                label: "End Date",
                inputWidth: 150,
                name: "end_date",
                value: "",
                readonly: false,
                offsetLeft: 0
            }, //%H:%i
            {type: "newcolumn", offsetLeft: 20},
            {
                type: "input",
                label: "End Time",
                position: "label-left",
                name: "end",
                value: "",
                inputWidth: 55,
                offsetLeft: 5
            },
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
    {type: "newcolumn", offset: 30},
    {
        type: "label", name: "label_days", label: "Select Days",
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
    {type: "hidden", label: "Cat_id", position: "label-left", name: "cat_id", value: "", inputWidth: 48, offsetLeft: 5},
    {type: "input", label: "Information", rows: 5, name: "info", value: ""},
    {type: "combo", comboType: "checkbox", label: "Approved by", name: "approved_by", value: ""},
    {type: "checkbox", name: "map", value: "0", label: "Show map", checked: true},
    {type: "checkbox", name: "masterrecord", value: "0", label: "Show masterrecord", checked: false},
    {type: "checkbox", name: "reoccur_map", value: "0", label: "Reoccur map", checked: false}
];


var eventDetailsForm = event_details.attachForm(eventDetailsFormdata);
eventDetailsForm.getInput("start_date").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
eventDetailsForm.getInput("start_date").style.backgroundPosition = "center right";
eventDetailsForm.getInput("start_date").style.backgroundRepeat = "no-repeat";
eventDetailsForm.getInput("end_date").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
eventDetailsForm.getInput("end_date").style.backgroundPosition = "center right";
eventDetailsForm.getInput("end_date").style.backgroundRepeat = "no-repeat";

var employeeCombo = eventDetailsForm.getCombo("emp");
employeeCombo.enableFilteringMode(true);
employeeCombo.load("Controller/php/projectsPlanning.php?action=2");

var approved_Combo = eventDetailsForm.getCombo("approved_by");
approved_Combo.load("Controller/php/recurring.php?action=110");

employeeCombo.attachEvent("onCheck", function (value, state) {

    var eventId = projectPlanningGrid.getSelectedRowId();
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

projectPlanningDetailsTabber.addTab('event_reoccurences', 'Re-Occurences');
var event_reoccurences = projectPlanningDetailsTabber.cells('event_reoccurences');

var eventReoccurencesLayout = event_reoccurences.attachLayout('2U');
var eventReoccurencesListCell = eventReoccurencesLayout.cells('a');
eventReoccurencesListCell.hideHeader();

var eventReoccurencesGridToolbar = eventReoccurencesListCell.attachToolbar();
eventReoccurencesGridToolbar.setIconsPath("Views/imgs/");
eventReoccurencesGridToolbar.addButton("new_rec", 1, "New", "new.gif", "new.gif");
eventReoccurencesGridToolbar.addSeparator("sep1", 2);
eventReoccurencesGridToolbar.addButton("delete_row", 3, "Delete Row", "deleteall.png", "deleteall.png");
eventReoccurencesGridToolbar.addSeparator("sep2", 4);
eventReoccurencesGridToolbar.addButton("delete_srow", 5, "Delete Selected", "cancel.png", "cancel.png");
eventReoccurencesGridToolbar.addSeparator("sep3", 6);
eventReoccurencesGridToolbar.addButton("generate_events", 7, "GenerateEvents", "generate1.png", "generate1.png");
eventReoccurencesGridToolbar.addSeparator("sep4", 8);
eventReoccurencesGridToolbar.addButton("clear_all", 9, "ClearAll", "del_evt.png", "del_evt.png");
eventReoccurencesGridToolbar.addSeparator("sep5", 10);
eventReoccurencesGridToolbar.addButton("check", 11, "Select All", "checked.png", "checked.png");
eventReoccurencesGridToolbar.addButton("uncheck", 12, "Unselect All", "unchecked.png", "unchecked.png");
eventReoccurencesGridToolbar.addSeparator("sep6", 13);
eventReoccurencesGridToolbar.addButton("is_vis", 14, "Vis/Invisible All", "is_vis.png", "is_vis.png");
eventReoccurencesGridToolbar.addSeparator("sep7", 15);
eventReoccurencesGridToolbar.attachEvent("onClick", toolbarSaveReoccurencesDetails);
eventReoccurencesGridToolbar.disableItem("clear_all");

function toggle() {
    var checkallbtn = document.getElementById('checkAll');
    if (checkallbtn.checked == true) {
        eventReoccurencesGrid.selectAll();
    }
    if (checkallbtn.checked == false) {
        eventReoccurencesGrid.clearSelection();
    }
}

//function generates the events
function generateEvents() {
    //check if parent tasks has been selected 
    var plan_id = projectPlanningGrid.getSelectedRowId();
    if (plan_id == null) {
        dhtmlx.alert("Please select Parent Task In Event Details!");
    } else {
        var task_id = projectPlanningGrid.cells(plan_id, 0).getValue();
        //ensure that the list employees to assign have been selected   
        var empCombo = eventDetailsForm.getCombo("emp");
        var emp_assigned = empCombo.getChecked();
        if (emp_assigned.length < 1) {
            alert("Please select employees to assign to from employee drop down!");
        } else {
            //send to server
            var evt_id = task_id;
            if (eventDetailsForm.getItemValue('begn') != null && eventDetailsForm.getItemValue('end') != null) {
                eventDetailsForm.send("Controller/php/recurring.php?action=6&evtId=" + evt_id + "&ass_emp=" + emp_assigned, function (loader, response) {
                    //refresh the child task grid
                    dhtmlx.alert("Recurring Event Activated!");
                    eventReoccurencesGrid.clearAndLoad("Controller/php/generated_tasks.php?id=" + evt_id, function () {
                        eventReoccurencesGrid.selectAll();
                    });
                });
            } else {
                dhtmlx.alert("Time period not specified!");
            }
        }
    }
}

function toolbarSaveReoccurencesDetails(id) {
    switch (id) {

        case 'new_rec':
            //create a new reoccurence  
            var event_id = projectPlanningGrid.getSelectedRowId();
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
                        eventReoccurencesGrid.updateFromXML("Controller/php/generated_tasks.php?id=" + event_id, true, true, function () {
                            eventReoccurencesGrid.selectRowById(t.data.newId);
                            eventReoccurencesForm.load('Controller/php/recurring.php?action=3&id=' + event_id, function (id, response) {
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
            var rwTsk = eventReoccurencesGrid.getSelectedRowId(); //event_id
            if (rwTsk != null) {
                reschedule(rwTsk);
            } else {
                dhtmlx.alert("No recurring task selected!");
            }
            break;

        case 'transfer':

            var rwTsk = eventReoccurencesGrid.getSelectedRowId(); //event_id
            if (rwTsk != null) {
                transfer(rwTsk);
            } else {
                dhtmlx.alert("No recurring task selected!");
            }
            break;

        case 'update':
            var rwTsk = eventReoccurencesGrid.getSelectedRowId(); //event_id
            if (rwTsk != null) {
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
            var row_id = eventReoccurencesGrid.getSelectedRowId();
            if (row_id === null) {
                dhtmlx.alert("No item selected!");
            } else {
                dhtmlx.confirm({
                    title: "Confirm",
                    type: "confirm-warning",
                    text: "Are you sure you  want to delete?",
                    callback: function (y) {
                        if (y) {
                            $.post("Controller/php/projectsPlanning.php?action=8", {id: row_id}, function (data) {
                                if (data.data.response) {

                                    dhtmlx.message({title: 'Success', text: data.data.text});
                                    eventReoccurencesGrid.deleteSelectedRows();
                                    $("#checkAllReoccurences").attr('checked', false);
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
            break;

        case 'delete_row':
            var row_id = eventReoccurencesGrid.getSelectedRowId();
            if (row_id === null) {
                dhtmlx.alert("No item selected!");
            } else {
                dhtmlx.confirm({
                    title: "Confirm",
                    type: "confirm-warning",
                    text: "Are you sure you  want to delete?",
                    callback: function (y) {
                        if (y) {
                            $.post("Controller/php/projectsPlanning.php?action=8", {id: row_id}, function (data) {

                                if (data.data.response) {

                                    dhtmlx.message({title: 'Success', text: data.data.text});
                                    eventReoccurencesGrid.deleteRow(row_id);
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
            break;

        case 'is_vis':
            //first check if main parent row has been selected 
            var event_id = projectPlanningGrid.getSelectedRowId();
            if (event_id !== null) {
                //send the parent record
                $.get("Controller/php/generated_tasks.php?action=4&grdRow=" + event_id, function (data) {

                    if (data.bool == true) { //check all items on the grid
                        eventReoccurencesGrid.forEachRow(function (id) {
                            var cell = eventReoccurencesGrid.cells(id, 7);
                            if (cell.isCheckbox())
                                cell.setValue(1);
                        });
                    } else {//uncheck all items on grid
                        eventReoccurencesGrid.forEachRow(function (id) {
                            var cell = eventReoccurencesGrid.cells(id, 7);
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
            eventReoccurencesGrid.selectAll();
            break;
        case 'uncheck':
            eventReoccurencesGrid.clearSelection();
            break;
    }
}

var eventReoccurencesGrid = eventReoccurencesListCell.attachGrid();
eventReoccurencesGrid.setIconsPath("dhtmlxsuite4/codebase/imgs/");
eventReoccurencesGrid.setSkin('dhx_web');
eventReoccurencesGrid.setHeader(["Event Name", "Assigned To", "Start Date", "End Date", "Details", "Protection", "Personal", "Visible", "Done"]);
eventReoccurencesGrid.setColumnIds("details,employee_id,start_date,end_date,event_name,protection,personal,visible,completed");
eventReoccurencesGrid.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
eventReoccurencesGrid.setColTypes("ro,edtxt,ro,edtxt,txt,ch,ch,ch,ch");
eventReoccurencesGrid.setColAlign('left,left,left,left,left,center,center,center,center');
eventReoccurencesGrid.enableTooltips('true,true,true,true,true,true,true,true,true');
eventReoccurencesGrid.setColSorting('str,str,str,str,str,str,int,int,int');
eventReoccurencesGrid.enableCellIds(true);
eventReoccurencesGrid.enableMultiselect(true);
eventReoccurencesGrid.setInitWidthsP('20,10,10,10,*,8,7,6,6');
eventReoccurencesGrid.attachEvent("onCheck", eventReoccurencesGridChecked);
eventReoccurencesGrid.init();
eventReoccurencesGrid.attachEvent("onRowSelect", grid_child_tskSelected);

function grid_child_tskSelected(id) {
    eventReoccurencesForm.load('Controller/php/projectsPlanning.php?action=17&id=' + id, function (id, response) {
    });
}

function eventReoccurencesGridChecked(id, index, state) {
    var colId = eventReoccurencesGrid.getColumnId(index);
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


var eventReoccurencesFormCell = eventReoccurencesLayout.cells('b');
eventReoccurencesFormCell.setWidth(projectPlanningDetailsCell.getWidth() * 0.3);
eventReoccurencesFormCell.hideHeader();

var eventReoccurencesFormToolbar = eventReoccurencesFormCell.attachToolbar();
eventReoccurencesFormToolbar.setIconsPath("Views/imgs/");
eventReoccurencesFormToolbar.addButton("save_detail", 1, "Save", "save.gif", "save.gif");
eventReoccurencesFormToolbar.addSeparator("sep1", 2);
eventReoccurencesFormToolbar.attachEvent("onClick", toolbarChildDetails);

function toolbarChildDetails(id) {
    switch (id) {
        case 'save_detail':
            eventReoccurencesForm.send("Controller/php/projectsPlanning.php?action=22", "post", function (loader, response) {
                //update the task grid                
                dhtmlx.message("Saved!");
                var parsedJSON = eval('(' + response + ')');
                var tsk_id = parsedJSON.event_id;
                //id of selected event 
                var plan_id = projectPlanningGrid.getSelectedRowId();
                var PlanningId = projectPlanningGrid.cells(plan_id, 0).getValue(); //event_id
                eventReoccurencesGrid.updateFromXML('Controller/php/projectsPlanning.php?action=16&id=' + PlanningId, function () {
                });
            });
            break;
    }
}

var reoccurencesFormData = [
    {
        type: "settings",
        position: "label-left",
        labelWidth: eventReoccurencesFormCell.getWidth() * 0.16,
        inputWidth: eventReoccurencesFormCell.getWidth() * 0.56,
        offsetTop: 8,
        offsetLeft: 0
    },
    {type: "input", className: "formbox", label: "Details", rows: 5, name: "event_name_child", value: ""},
    {type: "combo", className: "formbox", comboType: "checkbox", label: "Assigned To", name: "employee_id", value: ""},
    {
        type: "label", className: "formbox", offsetTop: 0, list: [
            {
                type: "calendar",
                className: "formbox",
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
            {type: "newcolumn", offsetLeft: 0},
            {
                type: "input",
                className: "formbox",
                label: "Begin Time",
                position: "label-left",
                name: "begn",
                value: "",
                inputWidth: 55,
                offsetLeft: 5
            },
        ]
    },
    {
        type: "label", offsetTop: 0, list: [
            {
                type: "calendar",
                className: "formbox",
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
            {type: "newcolumn", offsetLeft: 0},
            {
                type: "input",
                className: "formbox",
                label: "End Time",
                position: "label-left",
                name: "end",
                value: "",
                inputWidth: 55,
                offsetLeft: 5
            },
        ]
    },
    {type: "input", className: "formbox", label: "Comments", rows: 5, name: "information", value: ""}
];


var eventReoccurencesForm = eventReoccurencesFormCell.attachForm(reoccurencesFormData);
eventReoccurencesForm.getInput("start_date").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
eventReoccurencesForm.getInput("start_date").style.backgroundPosition = "center right";
eventReoccurencesForm.getInput("start_date").style.backgroundRepeat = "no-repeat";
eventReoccurencesForm.getInput("end_date").style.backgroundImage = "url(dhtmlxsuite4/samples/dhtmlxCalendar/common/calendar.gif)";
eventReoccurencesForm.getInput("end_date").style.backgroundPosition = "center right";
eventReoccurencesForm.getInput("end_date").style.backgroundRepeat = "no-repeat";


var empChild_Combo = eventReoccurencesForm.getCombo("employee_id");
empChild_Combo.load("Controller/php/recurring.php?action=110");


projectDetailsTabbar.addTab('admin', 'Admin');
var admin = projectDetailsTabbar.cells('admin');
admin.hide();

var tabbar;
var xoops_group_grid;
var xoops_group_to_link_grid;
var xoops_wiwimod_grd;
var xoops_wiwimod_contactPriv_grd;
var formSubscription;
var formEmail;
var tabbhistory;
var xoops_hist_grd;

var lyt = admin.attachLayout("2E");
lyt.cells("a").setHeight(120);
lyt.cells("a").hideHeader();
lyt.cells("b").hideHeader();

//create a toolbar to save the settings  
var toolbarPriviledge = lyt.cells("a").attachMenu();
toolbarPriviledge.setSkin("dhx_terrace");
toolbarPriviledge.setIconsPath("Views/imgs/");
toolbarPriviledge.attachEvent("onClick", function (id) {
    switch (id) {

        default:
            var proj_id = projectsTree.getSelectedItemId();
            var i = 0;
            var settingsArr = new Array();

            toolbarPriviledge.forEachItem(function (itemId) {
                if (toolbarPriviledge.getCheckboxState(itemId)) {
                    var usr = itemId.split('_');
                    settingsArr[i] = usr[1];
                    i++;
                }
            });

            var postVars = {
                "arrayObj": settingsArr,
                "proj_id": proj_id
            };

            $.post("Controller/php/data_priviledges.php?action=5", postVars, function () {
                layoutSchedule.cells("a").progressOn();
                xoops_wiwimod_contactPriv_grd.clearAndLoad("Controller/php/data_priviledges.php?action=8&id=" + proj_id,
                    function () {
                        layoutSchedule.cells("a").progressOff();
                        xoops_wiwimod_contactPriv_grd.forEachRow(function (id) {
                            xoops_wiwimod_contactPriv_grd.cells(id, 2).setDisabled(true);
                            xoops_wiwimod_contactPriv_grd.cells(id, 3).setDisabled(true);
                        });
                    });
            });

            return true;
            break;
    }

});

function toolBarPriviledgeEvent(id) {

}

//create the form that displays the 1st level priviledges
var formLevel;

formDataLevel =
    [
        {type: "settings", position: "label-top", labelWidth: 150, inputWidth: 75},

        {
            type: "label",
            label: "[E] : A project will be viewable in the grid if E has been flagged",
            position: "label-left",
            labelWidth: 460,
            name: "salland",
            value: "",
            offsetTop: 0,
            offsetLeft: 120,
            readonly: false
        },
        {
            type: "label",
            label: "[S] : A project has supervision on edit tab if S has been flagged",
            position: "label-left",
            labelWidth: 460,
            name: "salland",
            value: "",
            offsetTop: 0,
            offsetLeft: 120,
            readonly: false
        },
        //{type: "newcolumn"},
        {
            type: "label",
            label: "[A] : A project will be altered in the grid if A has been flagged",
            position: "label-left",
            labelWidth: 460,
            name: "salland",
            value: "",
            offsetTop: 0,
            offsetLeft: 120,
            readonly: false
        }
    ];

var formLevel = lyt.cells("a").attachForm(formDataLevel);

//create a layout to hold the scheduler 
var layoutSchedule = lyt.cells("b").attachLayout("1C");
layoutSchedule.cells("a").setText("Privileges");

xoops_wiwimod_contactPriv_grd = layoutSchedule.cells("a").attachGrid();
xoops_wiwimod_contactPriv_grd.setImagePath('../dhtmlx3.6pro/dhtmlxGrid/codebase/imgs/');
xoops_wiwimod_contactPriv_grd.setHeader("Contact ID,Employee Name,NTS User,Web Admin,E,S,A");
xoops_wiwimod_contactPriv_grd.setColumnIds("contact_id,eid,nts_user,web_admin,e,s,a");
xoops_wiwimod_contactPriv_grd.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
xoops_wiwimod_contactPriv_grd.setColSorting("int,str,str,str,str,str,str");
xoops_wiwimod_contactPriv_grd.setSkin('dhx_web');
xoops_wiwimod_contactPriv_grd.setInitWidthsP("*,27,11,11,10,10,13");
xoops_wiwimod_contactPriv_grd.enableAutoWidth(true);
xoops_wiwimod_contactPriv_grd.setColTypes('ro,ro,ch,ch,ch,ch,ch');
xoops_wiwimod_contactPriv_grd.setColAlign('left,left,center,center,center,center,center');
xoops_wiwimod_contactPriv_grd.init();

//edit events    
xoops_wiwimod_contactPriv_grd.attachEvent("onEditCell", function (stage, id, index, new_value, old_value, cellIndex) {

    var colId = xoops_wiwimod_contactPriv_grd.getColumnId(index);
    var colType = xoops_wiwimod_contactPriv_grd.fldSort[index];

    if (stage == 0 && colId == "e") {

        var cell = xoops_wiwimod_contactPriv_grd.cells(id, index);
        if (cell.getValue() == 1) {
            var cValue = 0;
        } else {
            var cValue = 1;
        }

        var postVars = {
            "doc_id": projectsTree.getSelectedItemId(),
            "contact_id": id,
            "value": cValue,
            "column": colId
        }
        $.post("Controller/php/data_priviledges.php?action=10", postVars, function (data) {
            dhtmlx.message(data.response);
        }, 'json');
    }

    if (stage == 0 && colId == "s") {

        var cell = xoops_wiwimod_contactPriv_grd.cells(id, index);
        if (cell.getValue() == 1) {
            var cValue = 0;
        } else {
            var cValue = 1;
        }

        var postVars = {
            "doc_id": projectsTree.getSelectedItemId(),
            "contact_id": id,
            "value": cValue,
            "column": colId
        }
        $.post("Controller/php/data_priviledges.php?action=10", postVars, function (data) {
            dhtmlx.message(data.response);
        }, 'json');
    }

    if (stage == 0 && colId == "a") {

        var cell = xoops_wiwimod_contactPriv_grd.cells(id, index);
        if (cell.getValue() == 1) {
            var cValue = 0;
        } else {
            var cValue = 1;
        }

        var postVars = {
            "doc_id": projectsTree.getSelectedItemId(),
            "contact_id": id,
            "value": cValue,
            "column": colId
        }

        $.post("Controller/php/data_priviledges.php?action=10", postVars, function (data) {
            dhtmlx.message(data.response);
        }, 'json');
    }

    return true;
});

projectDetailsTabbar.addTab('relations', 'Relations');
var relations = projectDetailsTabbar.cells('relations');

var lytRelations = relations.attachLayout("2E");
lytRelations.cells('a').setText("Relations");

var relationsToolbar = lytRelations.cells('a').attachToolbar();
relationsToolbar.setIconsPath("Views/imgs/");
relationsToolbar.addButton("add", 1, "Add New", "new.gif", "new.gif");
relationsToolbar.addSeparator("sep3", 2);
relationsToolbar.addButton("delete", 3, "Delete", "deleteall.png", "deleteall.png");

relationsToolbar.attachEvent("onClick", function (id) {
        switch (id) {
            case "add":

                var addFormData =
                    [{
                        type: "settings",
                        position: "label-left",
                        labelWidth: myWidth * 0.05,
                        inputWidth: myWidth * 0.1,
                        offsetTop: 10,
                        offsetLeft: 10
                    },
                        {
                            type: "hidden", label: "Applicants Details ", className: "formbox", width: myWidth * 0.18, list:
                                [{type: "input", label: "Relation ID", name: "relation_id", value: ""},
                                    {type: "hidden", name: "project_id", value: ""},
                                    {type: "button", name: "submit", value: "submit", offsetLeft: 100}
                                ]
                        }
                    ];
                var project_id = projectsTree.getSelectedItemId();
                //var resumeFormHeight = myHeight * 0.8;

                if (project_id == "") {
                    dhtmlx.alert("Please select a project!");
                } else {

                    var popupWindow = lytRelations.dhxWins.createWindow("resumes_win", 0, 0, myWidth * 0.2, myHeight * 0.15);
                    popupWindow.center();
                    popupWindow.setText("Enter Relation ID");
                    var addResumeForm = popupWindow.attachForm(addFormData);
                    addResumeForm.attachEvent("onButtonClick", function () {

                        addResumeForm.setItemValue("project_id", project_id);
                        addResumeForm.send("Controller/php/data_relation.php?action=3", "post", function (loader, response) {
                            var parsedJSON = eval('(' + response + ')');
                            dhtmlx.alert(parsedJSON.message);
                            relationsGrid.clearAndLoad("Controller/php/data_relation.php?id=" + projectsTree.getSelectedItemId());
                            //relationsGrid.selectRowById(parsedJSON.id);

                        }, 'json');
                        popupWindow.close();
                    });
                }
                break;
            case 'delete':

                var relation_id = relationsGrid.getSelectedRowId();
                var project_id = projectsTree.getSelectedItemId();
                $.get("Controller/php/data_relation.php?action=4&relation_id=" + relation_id + "&project_id=" + project_id, function (data) {

                    relationsGrid.deleteRow(relation_id);
                    dhtmlx.alert(data.response);
                }, 'json');
                break;
        }
    }
);

var relationsGrid = lytRelations.cells('a').attachGrid();
relationsGrid.setIconsPath('./codebase/imgs/');
relationsGrid.setHeader("Relation ID,Search Code,Company,Country,Status");
relationsGrid.setColumnIds("relation_id,search_code,relation_company,RelCountryId,StatusID");
relationsGrid.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
relationsGrid.setSkin('dhx_web');
relationsGrid.setInitWidthsP("25,25,25,25,*");
relationsGrid.setColTypes("ro,ro,ro,ro,ro");
//relationsGrid.enableMultiline(true);
relationsGrid.enableAutoWidth(true);
relationsGrid.init();

relationsGrid.attachEvent("onRowSelect", function (id) {
    relationContactsGrid.clearAndLoad("Controller/php/data_relation.php?action=1&id=" + id);
});

lytRelations.cells('b').setText("Contacts");

var relationContactsGrid = lytRelations.cells('b').attachGrid();
relationContactsGrid.setIconsPath('./codebase/imgs/');
relationContactsGrid.setHeader("Contact ID,First Name,Middle Name,Last Name,Birthday,Gender,Telephone,Email,Position,Status");
relationContactsGrid.setColumnIds("contact_id,contact_firstname,contact_secondname,contact_lastname,contact_birthday,contact_gender,contact_telephone,email,a1,contact_status_id");
relationContactsGrid.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
relationContactsGrid.setSkin('dhx_web');
relationContactsGrid.setInitWidthsP("10,10,10,10,10,10,10,10,10,*");
relationContactsGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
//relationContactsGrid.enableMultiline(true);
relationContactsGrid.enableAutoWidth(true);
relationContactsGrid.init();

function disableCheckBox() {
    eventDetailsForm._disableItem("days_select[1]");
    eventDetailsForm._disableItem("days_select[2]");
    eventDetailsForm._disableItem("days_select[3]");
    eventDetailsForm._disableItem("days_select[4]");
    eventDetailsForm._disableItem("days_select[5]");
    eventDetailsForm.setItemLabel("label_days", "Select day to skip");
}

function y(x) {

    a = x.substring(1);
    if (a.substring(0, 1) == 0) {
        return y(a);
    } else {
        return a;
    }
}






