var projectDetailsFormCommentsIframe = null;
projectDetailsTabbar.addTab('project_details', 'Project Details');
var project_details = projectDetailsTabbar.cells('project_details');
var projectDetailsLayout = project_details.attachLayout('3L');

var projectDetailsFormCell = projectDetailsLayout.cells('a');
var projectDetailsFormCellLayout = projectDetailsFormCell.attachLayout('2E');

var projectDetailsFormDetailsCell = projectDetailsFormCellLayout.cells('a');
projectDetailsFormDetailsCell.hideHeader();

var projectsDetailsFormdata = [
    {type: "settings", labelWidth: 100, inputWidth: 200, offsetLeft: "10", offsetTop: "0"},
    {type: "block", name: "form_block_3", blockOffset: 30, offsetTop: 15, width: "auto", list: [
            {type: "input", name: "ProjectID", label: "Project ID", readonly: true},
            {type: "input", name: "ProjectName", label: "Project Name"},
            {type: "input", name: "ProjectDuration", label: "Project Duration"},
            {type: "input", name: "ProjectDescription", label: "Project Description", rows: "3"},
            {type: "checkbox", name: "has_training", label: "Contains Video"},
            {type: "checkbox", name: "has_moodle", label: "Contains Moodle"},
            {type: "checkbox", name: "has_project", label: "Contains Project"}
        ]}
];

var projectsDetailsForm = projectDetailsFormDetailsCell.attachForm(projectsDetailsFormdata);
projectsDetailsForm.setSkin('dhx_web');

var projectDetailsToolbar = projectDetailsFormDetailsCell.attachToolbar();
projectDetailsToolbar.setIconsPath("Views/imgs/");

projectDetailsToolbar.loadStruct('<toolbar><item type="button" id="save" text="Save" /></toolbar>', function () {});

projectDetailsToolbar.attachEvent("onClick", function () {

    projectsDetailsForm.send('Controller/php/projectsTree.php?action=34', function (loader, response) {
        var parsedJSON = eval('(' + response + ')');

        if (parsedJSON.data.success) {
            dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
//            assetProjectsGrid.updateFromXML('Controller/php/assetDrawingProjectsData.php?action=1', true, true);
        } else {
            dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
        }
    });
});

var projectDetailsFormCommentsCell = projectDetailsFormCellLayout.cells('b');
projectDetailsFormCommentsCell.setText('Comments');

projectDetailsFormCommentsCell.attachURL("Views/frames/project_comments.php", false, {report_content: '', height: (projectDetailsFormCommentsCell.getHeight()) / 1.9});
projectDetailsFormCellLayout.attachEvent("onContentLoaded", function (id) {
    projectDetailsFormCommentsIframe = projectDetailsFormCellLayout.cells(id).getFrame();
});

var projectDetailsBranchCell = projectDetailsLayout.cells('b');
projectDetailsBranchCell.setText('Branches');

var projectDetailsBranchGrid = projectDetailsBranchCell.attachGrid();
projectDetailsBranchGrid.setImagesPath('dhtmlxSuite4/skins/web/imgs/');
projectDetailsBranchGrid.setHeader(",Branch Name");
projectDetailsBranchGrid.setColumnIds("visible,branch_id");
projectDetailsBranchGrid.setInitWidthsP("7,*");
projectDetailsBranchGrid.setColTypes('ch,ro');
projectDetailsBranchGrid.setColAlign('center,left');
projectDetailsBranchGrid.setColSorting("int,str");
projectDetailsBranchGrid.setSkin('dhx_web');
projectDetailsBranchGrid.init();
projectDetailsBranchGrid.load("Controller/php/projectsTree.php?action=31");

projectDetailsBranchGrid.attachEvent("onCheck", function (id, index, state) {

    if (projectId !== null) {
        $.post("Controller/php/projectsTree.php?action=32", {project_id: projectId, branch_id: id, nValue: ((state) ? 1 : 0)}, function (data)
        {
            if (data.data.response) {
                dhtmlx.message({title: 'Success', text: data.data.text});
            } else {
                dhtmlx.alert({title: 'Error', text: data.data.text});
            }
        }, 'json');
    } else {
        dhtmlx.alert({title: 'Error', text: "No Project Currently Selected!"});
    }
});



var projectDetailsTranslationCell = projectDetailsLayout.cells('c');
projectDetailsTranslationCell.setText('Translations');
var projectDetailsTranslationToolbar = projectDetailsTranslationCell.attachToolbar();
projectDetailsTranslationToolbar.setIconsPath("Views/imgs/");
//projectDetailsTranslationToolbar.addButton("add", 1, "New", "new.gif", "new.gif");
projectDetailsTranslationToolbar.addButtonSelect('add', 1, 'New', [], 'new.gif', 'new.gif');
projectDetailsTranslationToolbar.addSeparator("sep1", 2);
projectDetailsTranslationToolbar.addButton("delete", 3, "Delete", "deleteall.png", "deleteall.png");
projectDetailsTranslationToolbar.addSeparator("sep2", 4);

$.getJSON('Controller/php/projectsTree.php?action=26', function (results) {
    var pos = 0;

    $.each(results.options, function (key, value) {
        projectDetailsTranslationToolbar.addListOption('add', value.id, pos++, 'button', value.text);
    });
});

projectDetailsTranslationToolbar.attachEvent("onClick", projectDetailsTranslationToolbarClicked);

function projectDetailsTranslationToolbarClicked(id) {
    switch (id)
    {
        case 'delete':

            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you to delete this  Field?",
                callback: function (ok) {
                    if (ok)
                    {

                        var label_id = projectDetailsTranslationGrid.getSelectedRowId();
                        $.get("Controller/php/projectsTree.php?action=27&id=" + label_id, function (data) {
                            projectDetailsTranslationGrid.deleteRow(label_id);
                            dhtmlx.message(data.response);
                        }, 'json');
                    } else
                    {
                        return false;
                    }
                }

            });

            break;

        default:
            var project_id = projectsTree.getSelectedItemId();
            if (project_id) {
                var language_id = id.substring(2);
                $.get("Controller/php/projectsTree.php?action=28&project_id=" + project_id + "&language_id=" + language_id, function (data) {

                    if (data.data.response)
                    {
                        projectDetailsTranslationGrid.updateFromXML("Controller/php/projectsTree.php?action=29&project_id=" + project_id, true, true, function ()
                        {
                            projectDetailsTranslationGrid.setSelectedRow(data.data.new_id);
                            projectDetailsTranslationGrid.selectRow(data.data.new_id);
                        });
                    } else {
                        dhtmlx.alert({title: 'Error', text: data.data.text});
                    }
                }, 'json');
            } else {
                dhtmlx.alert("Please select Document!");

            }
            break;
    }
}


var projectDetailsTranslationGrid = projectDetailsTranslationCell.attachGrid();
projectDetailsTranslationGrid.setIconsPath('./codebase/imgs/');
projectDetailsTranslationGrid.setHeader("#,Language,Label Name");
projectDetailsTranslationGrid.setColumnIds("counter,language_id,title");
projectDetailsTranslationGrid.setSkin('dhx_web');
projectDetailsTranslationGrid.setInitWidthsP("2,10,*");
projectDetailsTranslationGrid.setColTypes("cntr,ro,ed");
projectDetailsTranslationGrid.setColAlign('left,left,left');
projectDetailsTranslationGrid.setColSorting("int,int,str");
projectDetailsTranslationGrid.attachEvent("onEditCell", doOnEditProjectDetailsTranslationGrid);
projectDetailsTranslationGrid.init();

//projectDetailsTranslationGridLangCombo = projectDetailsTranslationGrid.getColumnCombo(1);
//projectDetailsTranslationGridLangCombo.setSkin("dhx_web");
//projectDetailsTranslationGridLangCombo.enableFilteringMode(true);
//projectDetailsTranslationGridLangCombo.load("Controller/php/projectDocuments.php?action=24");
function doOnEditProjectDetailsTranslationGrid(stage, id, index, new_value) {
    var project_id = projectsTree.getSelectedItemId();

    var cell = projectDetailsTranslationGrid.cells(id, index);
    if (stage === 2 && !cell.isCheckbox()) {
        var row_id = projectDetailsTranslationGrid.getSelectedRowId();
        if (row_id > 0 || typeof row_id !== 'undefined') {
            var colId = projectDetailsTranslationGrid.getColumnId(index);
            var colType = projectDetailsTranslationGrid.fldSort[index];

            $.post("Controller/php/projectsTree.php?action=30", {id: row_id, index: index, fieldvalue: new_value, colId: colId, colType: colType}, function (data)
            {
                if (data.data.response) {
                    dhtmlx.message({title: 'Success', text: data.data.text});
                    projectDetailsTranslationGrid.updateFromXML("Controller/php/projectsTree.php?action=29&project_id=" + project_id, true, true);

                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');

        }
    } else
    if (stage === 0 && cell.isCheckbox()) {
        return true;
    }
}

