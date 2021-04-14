projectDetailsTabbar.addTab('project_map_privileges', 'Map Privileges');
var project_map_privileges = projectDetailsTabbar.cells('project_map_privileges');
var projectMapPrivilegesLayout = project_map_privileges.attachLayout('1C');

var projectMapPrivilegesCell = projectMapPrivilegesLayout.cells('a');
projectMapPrivilegesCell.hideHeader();

var projectMapPrivilegesCellToolbar = projectMapPrivilegesCell.attachToolbar();
projectMapPrivilegesCellToolbar.setIconsPath('Views/imgs/');
projectMapPrivilegesCellToolbar.addButton('expand', 1, 'Expand All Items', '', '');
projectMapPrivilegesCellToolbar.addSeparator('sep1', 2);
projectMapPrivilegesCellToolbar.addButton('collapse', 3, 'Collapse All Items', '', '');
projectMapPrivilegesCellToolbar.addSeparator('sep2', 4);
projectMapPrivilegesCellToolbar.addButton('refresh', 5, 'Refresh Items', 'reload.png', 'reload.png');
projectMapPrivilegesCellToolbar.attachEvent("onClick", doOnUserPrivilegesCellToolbarClicked);


var projectMapPrivilegesGrid = projectMapPrivilegesCell.attachGrid();
//userPrivilegesGrid.setImagePath('dhtmlxSuite4/codebase/imgs/');
projectMapPrivilegesGrid.setImagesPath('dhtmlxSuite4/skins/web/imgs/');
projectMapPrivilegesGrid.setSkin('dhx_web');
projectMapPrivilegesGrid.setHeader(
        ["Employee Name", "Employee ID","Map Access", "Document Access", "File access"],
        null,
        ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left", "text-align:left"]
        );
projectMapPrivilegesGrid.setColTypes("tree,ro,combo,combo,combo");
projectMapPrivilegesGrid.attachHeader('#text_filter,#text_filter,#text_filter,#text_filter,#text_filter', ["text-align:left;", "text-align:left;", "text-align:left;", "text-align:left", "text-align:left"]);
projectMapPrivilegesGrid.setColAlign('left,left,left,left,left');
projectMapPrivilegesGrid.setColSorting('str,int,int,int,int');

projectMapPrivilegesGrid.enableCellIds(true);
projectMapPrivilegesGrid.setColumnIds('item,employee_id,map_access,doc_access,file_access');

projectMapPrivilegesGrid.setInitWidthsP('*,12,12,12,12');
projectMapPrivilegesGrid.attachEvent('onEditCell', projectMapPrivilegesGridEdit);
projectMapPrivilegesGrid.init();
projectMapPrivilegesGrid.loadXML("Controller/php/map_privileges.php?action=1");

var mapAccessCombo = projectMapPrivilegesGrid.getColumnCombo(2);
mapAccessCombo.enableFilteringMode(true);
mapAccessCombo.addOption([
    ["0", "No Access"],
    ["1", "1"],
    ["2", "2"],
    ["3", "3"],
    ["4", "4"]
]);

var docAccessCombo = projectMapPrivilegesGrid.getColumnCombo(3);
docAccessCombo.enableFilteringMode(true);
docAccessCombo.addOption([
    ["0", "No Access"],
    ["1", "1"],
    ["2", "2"],
    ["3", "3"],
    ["4", "4"]
]);

var fileAccessCombo = projectMapPrivilegesGrid.getColumnCombo(4);
fileAccessCombo.enableFilteringMode(true);
fileAccessCombo.addOption([
    ["0", "No Access"],
    ["1", "1"],
    ["2", "2"],
    ["3", "3"],
    ["4", "4"]
]);


function projectMapPrivilegesGridEdit(stage, id, index, new_value) {
    if (index === '0') {
        return false;
    } else {

        if (projectId !== null) {
            
            updateMapPrivilegeDown(stage, id, index, new_value, projectId) ;

            updateProjectMapPrivilegeUP(stage, id, index, new_value, projectId);

        } else {
            dhtmlx.message({
                title: "Editing Error",
                type: "alert-warning",
                text: "Please Select a Project!"
            });
            return false;
        }
    }
}

function updateMapPrivilegeDown(stage, id, index, new_value, project_id) {

    var cell = projectMapPrivilegesGrid.cells(id, index);
    if (stage === 2 && !cell.isCheckbox()) {
        var row_id = projectMapPrivilegesGrid.getSelectedRowId();
        if (row_id > 0 || typeof row_id !== 'undefined') {
            var colId = projectMapPrivilegesGrid.getColumnId(index);
            var colType = projectMapPrivilegesGrid.fldSort[index];

            $.post("Controller/php/map_privileges.php?action=2", {id: row_id, index: index, fieldvalue: new_value, colId: colId, colType: colType, projectId: project_id}, function (data)
            {
                if (data.data.response) {

                    dhtmlx.message({title: 'Success', text: data.data.text});
                    projectMapPrivilegesGrid.updateFromXML("Controller/php/map_privileges.php?action=1&tab=1&id=" + projectId, true, true);

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

function updateMapPrivilegeUp(stage, id, index, new_value, project_id) {

    var cell = projectMapPrivilegesGrid.cells(id, index);
    if (stage === 2 && !cell.isCheckbox()) {
        var row_id = projectMapPrivilegesGrid.getSelectedRowId();
        if (row_id > 0 || typeof row_id !== 'undefined') {
            var colId = projectMapPrivilegesGrid.getColumnId(index);
            var colType = projectMapPrivilegesGrid.fldSort[index];

            $.post("Controller/php/map_privileges.php?action=3", {id: row_id, index: index, fieldvalue: new_value, colId: colId, colType: colType, projectId: project_id}, function (data)
            {
                if (data.data.response) {

                    dhtmlx.message({title: 'Success', text: data.data.text});

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

function updateProjectMapPrivilegeUP(stage, id, index, new_value, project_id) {

    updateMapPrivilegeUp(stage, id, index, new_value, project_id);

    var projectMapLevel = projectsTree.getLevel(project_id);
    if (projectMapLevel > 1) {
        if (new_value > 0) {
            var parent_project_id = projectsTree.getParentId(project_id);
            updateProjectMapPrivilegeUP(stage, id, index, new_value, parent_project_id);
        }
    }

}









