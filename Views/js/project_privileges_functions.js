function updatePrivilege(id, state, index, project_id) {

    var colId = selectedGrid.getColumnId(index);
    var level = selectedGrid.getLevel(id);
    $.post("Controller/php/project_privileges.php?action=2", {colId: colId, userId: ((level === 0) ? id.substr(7) : id), projectId: project_id, nValue: ((state) ? 1 : 0), level: level}, function (data)
    {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');


    if (level > 0) {
        if (state) {
            var parentId = selectedGrid.getParentId(id);
//            var isChecked = userPrivilegesGrid.cells(parentId, index).isChecked();
//            if (!isChecked) {
//                userPrivilegesGrid.cells(parentId, index).setValue("1");
//            }
            updatePrivilege(parentId, state, index, project_id);
        }
    }
}

function updateProjectPrivilege(project_id, row_id, index, state) {

    updatePrivilege(row_id, state, index, project_id);

    var projectLevel = projectsTree.getLevel(project_id);
    if (projectLevel > 1) {
        if (state) {
            var parent_project_id = projectsTree.getParentId(project_id);
            updateProjectPrivilege(parent_project_id, row_id, index, state);
        }
    }
}

function userPrivilegesGridChecked(id, index, state) {
    selectedGrid = userPrivilegesGrid;
    updateProjectPrivilege(projectId, id, index, state);
}

function userPermissionsGridChecked(id, index, state) {
    selectedGrid = userPermissionsGrid;
    updateProjectPrivilege(projectId, id, index, state);
}

function newlyCreatedMapsDefaultRightsGridChecked(id, index, state) {
    selectedGrid = newlyCreatedMapsDefaultRightsGrid;
    updateProjectPrivilege(projectId, id, index, state);
}

function selfCreatedMapsDefaultRightsGridChecked(id, index, state) {
    selectedGrid = selfCreatedMapsDefaultRightsGrid;
    updateProjectPrivilege(projectId, id, index, state);
}

function masterRightsGridChecked(id, index, state) {
    selectedGrid = masterRightsGrid;
    updateProjectPrivilege(projectId, id, index, state);
}

function locationRightsGridChecked(id, index, state) {
    selectedGrid = locationRightsGrid;
    updateProjectPrivilege(projectId, id, index, state);
}

function doOnUserPrivilegesCellToolbarClicked(id) {
    switch (id) {
        case "expand":
            userPrivilegesGrid.expandAll();
            break;
        case "collapse":
            userPrivilegesGrid.collapseAll();
            break;
        case "refresh":

            userPrivilegesGrid.updateFromXML("Controller/php/project_privileges.php?action=1&tab=1&id=" + projectId, true, true);
            break;
    }

}

function userPermissionsCellToolbarClicked(id) {
    switch (id) {
        case "expand":
            userPermissionsGrid.expandAll();
            break;
        case "collapse":
            userPermissionsGrid.collapseAll();
            break;
        case "refresh":
            userPermissionsGrid.updateFromXML("Controller/php/project_privileges.php?action=1&tab=2&id=" + projectId, true, true);
            break;
    }

}

function newlyCreatedMapsDefaultRightsCellToolbarClicked(id) {
    switch (id) {
        case "expand":
            newlyCreatedMapsDefaultRightsGrid.expandAll();
            break;
        case "collapse":
            newlyCreatedMapsDefaultRightsGrid.collapseAll();
            break;
        case "refresh":
            newlyCreatedMapsDefaultRightsGrid.updateFromXML("Controller/php/project_privileges.php?action=1&tab=3&id=" + projectId, true, true);
            break;
    }

}

function selfCreatedMapsDefaultRightsCellToolbarClicked(id) {
    switch (id) {
        case "expand":
            selfCreatedMapsDefaultRightsGrid.expandAll();
            break;
        case "collapse":
            selfCreatedMapsDefaultRightsGrid.collapseAll();
            break;
        case "refresh":

            selfCreatedMapsDefaultRightsGrid.updateFromXML("Controller/php/project_privileges.php?action=1&tab=4&id=" + projectId, true, true);
            break;
    }

}


function masterRightsCellToolbarClicked(id) {
    switch (id) {
        case "expand":
            masterRightsGrid.expandAll();
            break;
        case "collapse":
            masterRightsGrid.collapseAll();
            break;
        case "refresh":

            masterRightsGrid.updateFromXML("Controller/php/project_privileges.php?action=1&tab=5&id=" + projectId, true, true);
            break;
    }

}

function locationRightsCellToolbarClicked(id) {
    switch (id) {
        case "expand":
            locationRightsGrid.expandAll();
            break;
        case "collapse":
            locationRightsGrid.collapseAll();
            break;
        case "refresh":

            locationRightsGrid.updateFromXML("Controller/php/project_privileges.php?action=1&tab=6&id=" + projectId, true, true);
            break;
    }

}

