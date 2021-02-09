projectDetailsTabbar.addTab('project_privileges', 'Privileges');
var project_privileges = projectDetailsTabbar.cells('project_privileges');
project_privileges.hide();

var projectPrivilegesLayout = project_privileges.attachLayout('1C');

var userGenerelPrivilegesTabbar = project_privileges.attachTabbar();

userGenerelPrivilegesTabbar.addTab('userMapPrivilegesTab', 'Map Privileges');
var userMapPrivilegesTab = userGenerelPrivilegesTabbar.cells('userMapPrivilegesTab');
userMapPrivilegesTab.setActive();

var userMapPrivilegesCellLayout = userMapPrivilegesTab.attachLayout('1C');
var userMapPrivilegesCell = userMapPrivilegesCellLayout.cells('a');
userMapPrivilegesCell.hideHeader();

var userPrivilegesCellToolbar = userMapPrivilegesCell.attachToolbar();
userPrivilegesCellToolbar.setIconsPath('Views/imgs/');
userPrivilegesCellToolbar.addButton('expand', 1, 'Expand All Items', '', '');
userPrivilegesCellToolbar.addSeparator('sep1', 2);
userPrivilegesCellToolbar.addButton('collapse', 3, 'Collapse All Items', '', '');
userPrivilegesCellToolbar.addSeparator('sep2', 4);
userPrivilegesCellToolbar.addButton('refresh', 5, 'Refresh Items', 'reload.png', 'reload.png');
userPrivilegesCellToolbar.attachEvent("onClick", doOnUserPrivilegesCellToolbarClicked);


var userPrivilegesGrid = userMapPrivilegesCell.attachGrid();
//userPrivilegesGrid.setImagePath('dhtmlxsuite4/codebase/imgs/');
userPrivilegesGrid.setImagesPath('dhtmlxsuite4/skins/web/imgs/');
userPrivilegesGrid.setSkin('dhx_web');
userPrivilegesGrid.setHeader(
        ["Item Name", "Read", "Write", "Create", "Delete"],
        null,
        ["text-align:left;", "text-align:center;", "text-align:center", "text-align:center", "text-align:center"]
        );
userPrivilegesGrid.setColTypes("tree,ch,ch,ch,ch");
userPrivilegesGrid.attachHeader('#text_filter,,,,', ["text-align:left;", "text-align:center;", "text-align:center", "text-align:center", "text-align:center"]);
userPrivilegesGrid.setColAlign('left,center,center,center,center');
userPrivilegesGrid.setColSorting('str,int,int,int,int');

userPrivilegesGrid.enableCellIds(true);
userPrivilegesGrid.setColumnIds('item,read_privilege,write_privilege,create_privilege,delete_privilege');

userPrivilegesGrid.setInitWidthsP('*,12,12,12,12');
userPrivilegesGrid.attachEvent('onEditCell', function (stage, rId, cInd, nValue, oValue) {
    if (cInd === '0') {
        return false;
    } else {

        if (projectId !== null) {
            return true;
        } else {
            dhtmlx.message({
                title: "Editing Error",
                type: "alert-warning",
                text: "Please Select a Project!"
            });
            return false;
        }
    }
});
userPrivilegesGrid.attachEvent("onCheckbox", userPrivilegesGridChecked);
userPrivilegesGrid.init();
userPrivilegesGrid.loadXML("Controller/php/project_privileges.php?action=1&tab=1");


userGenerelPrivilegesTabbar.addTab('userMapPermissionsTab', 'Map Permissons');
var userMapPermissionsTab = userGenerelPrivilegesTabbar.cells('userMapPermissionsTab');

var userMapPermissionsCellLayout = userMapPermissionsTab.attachLayout('1C');
var userMapPermissionsCell = userMapPermissionsCellLayout.cells('a');
userMapPermissionsCell.hideHeader();


var userPermissionsCellToolbar = userMapPermissionsCell.attachToolbar();
userPermissionsCellToolbar.setIconsPath('Views/imgs/');
userPermissionsCellToolbar.addButton('expand', 1, 'Expand All Items', '', '');
userPermissionsCellToolbar.addSeparator('sep1', 2);
userPermissionsCellToolbar.addButton('collapse', 3, 'Collapse All Items', '', '');
userPermissionsCellToolbar.addSeparator('sep2', 4);
userPermissionsCellToolbar.addButton('refresh', 5, 'Refresh Items', 'reload.png', 'reload.png');
userPermissionsCellToolbar.attachEvent("onClick", userPermissionsCellToolbarClicked);


var userPermissionsGrid = userMapPermissionsCell.attachGrid();
userPermissionsGrid.setImagePath('dhtmlxsuite4/skins/web/imgs/');
userPermissionsGrid.setSkin('dhx_web');
userPermissionsGrid.setHeader(
        ["Item Name", "Create New Maps", "Rename Maps", "Delete Maps"],
        null,
        ["text-align:left;", "text-align:center;", "text-align:center", "text-align:center"]
        );
userPermissionsGrid.setColTypes("tree,ch,ch,ch");
userPermissionsGrid.attachHeader('#text_filter,,,', ["text-align:left;", "text-align:center;", "text-align:center", "text-align:center"]);
userPermissionsGrid.setColAlign('left,center,center,center');
userPermissionsGrid.setColSorting('str,int,int,int');

userPermissionsGrid.enableCellIds(true);
userPermissionsGrid.setColumnIds('item,create_maps,rename_maps,delete_maps');

userPermissionsGrid.setInitWidthsP('*,20,20,20');

userPermissionsGrid.attachEvent('onEditCell', function (stage, rId, cInd, nValue, oValue) {
    if (cInd == '0') {
        return false;
    } else {

        if (projectId !== null) {
            return true;
        } else {
            dhtmlx.message({
                title: "Editing Error",
                type: "alert-warning",
                text: "Please Select a Project!"
            });
            return false;
        }
    }
});
userPermissionsGrid.attachEvent("onCheckbox", userPermissionsGridChecked);
userPermissionsGrid.init();
userPermissionsGrid.loadXML("Controller/php/project_privileges.php?action=1&tab=2");


userGenerelPrivilegesTabbar.addTab('defaultRightsTab', 'Default Rights');
var defaultRightsTab = userGenerelPrivilegesTabbar.cells('defaultRightsTab');

var defaultRightsTabbar = defaultRightsTab.attachTabbar();

defaultRightsTabbar.addTab('newlyCreatedMapsDefaultRightsTab', 'Newly Created Maps');
var newlyCreatedMapsDefaultRightsTab = defaultRightsTabbar.cells('newlyCreatedMapsDefaultRightsTab');
newlyCreatedMapsDefaultRightsTab.setActive();

var newlyCreatedMapsDefaultRightsCellLayout = newlyCreatedMapsDefaultRightsTab.attachLayout('1C');
var newlyCreatedMapsDefaultRightsCell = newlyCreatedMapsDefaultRightsCellLayout.cells('a');
newlyCreatedMapsDefaultRightsCell.hideHeader();


var newlyCreatedMapsDefaultRightsCellToolbar = newlyCreatedMapsDefaultRightsCell.attachToolbar();
newlyCreatedMapsDefaultRightsCellToolbar.setIconsPath('Views/imgs/');
newlyCreatedMapsDefaultRightsCellToolbar.addButton('expand', 1, 'Expand All Items', '', '');
newlyCreatedMapsDefaultRightsCellToolbar.addSeparator('sep1', 2);
newlyCreatedMapsDefaultRightsCellToolbar.addButton('collapse', 3, 'Collapse All Items', '', '');
newlyCreatedMapsDefaultRightsCellToolbar.addSeparator('sep2', 4);
newlyCreatedMapsDefaultRightsCellToolbar.addButton('refresh', 5, 'Refresh Items', 'reload.png', 'reload.png');
newlyCreatedMapsDefaultRightsCellToolbar.attachEvent("onClick", newlyCreatedMapsDefaultRightsCellToolbarClicked);



var newlyCreatedMapsDefaultRightsGrid = newlyCreatedMapsDefaultRightsCell.attachGrid();
newlyCreatedMapsDefaultRightsGrid.setSkin('dhx_web');
newlyCreatedMapsDefaultRightsGrid.setImagePath('dhtmlxsuite4/skins/web/imgs/');

newlyCreatedMapsDefaultRightsGrid.setHeader(
        ["Item Name", "Read", "Write", "Create", "Delete"],
        null,
        ["text-align:left;", "text-align:center;", "text-align:center", "text-align:center", "text-align:center"]
        );
newlyCreatedMapsDefaultRightsGrid.setColTypes("tree,ch,ch,ch,ch");
newlyCreatedMapsDefaultRightsGrid.attachHeader('#text_filter,,,,', ["text-align:left;", "text-align:center;", "text-align:center", "text-align:center", "text-align:center"]);
newlyCreatedMapsDefaultRightsGrid.setColAlign('left,center,center,center,center');
newlyCreatedMapsDefaultRightsGrid.setColSorting('str,int,int,int,int');

newlyCreatedMapsDefaultRightsGrid.enableCellIds(true);
newlyCreatedMapsDefaultRightsGrid.setColumnIds('item,default_new_read,default_new_write,default_new_create,default_new_delete');

newlyCreatedMapsDefaultRightsGrid.setInitWidthsP('*,12,12,12,12');
newlyCreatedMapsDefaultRightsGrid.attachEvent('onEditCell', function (stage, rId, cInd, nValue, oValue) {
    if (cInd == '0') {
        return false;
    } else {

        if (projectId !== null) {
            return true;
        } else {
            dhtmlx.message({
                title: "Editing Error",
                type: "alert-warning",
                text: "Please Select a Project!"
            });
            return false;
        }
    }
});
newlyCreatedMapsDefaultRightsGrid.attachEvent("onCheckbox", newlyCreatedMapsDefaultRightsGridChecked);
newlyCreatedMapsDefaultRightsGrid.init();
newlyCreatedMapsDefaultRightsGrid.loadXML("Controller/php/project_privileges.php?action=1&tab=3");



defaultRightsTabbar.addTab('selfCreatedMapsDefaultRightsTab', 'Self Created Maps');
var selfCreatedMapsDefaultRightsTab = defaultRightsTabbar.cells('selfCreatedMapsDefaultRightsTab');

var selfCreatedMapsDefaultRightsCellLayout = selfCreatedMapsDefaultRightsTab.attachLayout('1C');
var selfCreatedMapsDefaultRightsCell = selfCreatedMapsDefaultRightsCellLayout.cells('a');
selfCreatedMapsDefaultRightsCell.hideHeader();


var selfCreatedMapsDefaultRightsCellToolbar = selfCreatedMapsDefaultRightsCell.attachToolbar();
selfCreatedMapsDefaultRightsCellToolbar.setIconsPath('Views/imgs/');
selfCreatedMapsDefaultRightsCellToolbar.addButton('expand', 1, 'Expand All Items', '', '');
selfCreatedMapsDefaultRightsCellToolbar.addSeparator('sep1', 2);
selfCreatedMapsDefaultRightsCellToolbar.addButton('collapse', 3, 'Collapse All Items', '', '');
selfCreatedMapsDefaultRightsCellToolbar.addSeparator('sep2', 4);
selfCreatedMapsDefaultRightsCellToolbar.addButton('refresh', 5, 'Refresh Items', 'reload.png', 'reload.png');
selfCreatedMapsDefaultRightsCellToolbar.attachEvent("onClick", selfCreatedMapsDefaultRightsCellToolbarClicked);



var selfCreatedMapsDefaultRightsGrid = selfCreatedMapsDefaultRightsCell.attachGrid();
selfCreatedMapsDefaultRightsGrid.setSkin('dhx_web');
selfCreatedMapsDefaultRightsGrid.setImagePath('dhtmlxsuite4/skins/web/imgs/');

selfCreatedMapsDefaultRightsGrid.setHeader(
        ["Item Name", "Read", "Write", "Create", "Delete"],
        null,
        ["text-align:left;", "text-align:center;", "text-align:center", "text-align:center", "text-align:center"]
        );
selfCreatedMapsDefaultRightsGrid.setColTypes("tree,ch,ch,ch,ch");
selfCreatedMapsDefaultRightsGrid.attachHeader('#text_filter,,,,', ["text-align:left;", "text-align:center;", "text-align:center", "text-align:center", "text-align:center"]);
selfCreatedMapsDefaultRightsGrid.setColAlign('left,center,center,center,center');
selfCreatedMapsDefaultRightsGrid.setColSorting('str,int,int,int,int');

selfCreatedMapsDefaultRightsGrid.enableCellIds(true);
selfCreatedMapsDefaultRightsGrid.setColumnIds('item,default_self_read,default_self_write,default_self_create,default_self_delete');

selfCreatedMapsDefaultRightsGrid.setInitWidthsP('*,12,12,12,12');

selfCreatedMapsDefaultRightsGrid.attachEvent('onEditCell', function (stage, rId, cInd, nValue, oValue) {
    if (cInd == '0') {
        return false;
    } else {

        if (projectId !== null) {
            return true;
        } else {
            dhtmlx.message({
                title: "Editing Error",
                type: "alert-warning",
                text: "Please Select a Project!"
            });
            return false;
        }
    }
});
selfCreatedMapsDefaultRightsGrid.attachEvent("onCheckbox", selfCreatedMapsDefaultRightsGridChecked);
selfCreatedMapsDefaultRightsGrid.init();
selfCreatedMapsDefaultRightsGrid.loadXML("Controller/php/project_privileges.php?action=1&tab=4");


userGenerelPrivilegesTabbar.addTab('masterRightsTab', 'Master Rights');
var masterRightsTab = userGenerelPrivilegesTabbar.cells('masterRightsTab');

var masterRightsCellLayout = masterRightsTab.attachLayout('1C');
var masterRightsCell = masterRightsCellLayout.cells('a');
masterRightsCell.hideHeader();


var masterRightsCellToolbar = masterRightsCell.attachToolbar();
masterRightsCellToolbar.setIconsPath('Views/imgs/');
masterRightsCellToolbar.addButton('expand', 1, 'Expand All Items', '', '');
masterRightsCellToolbar.addSeparator('sep1', 2);
masterRightsCellToolbar.addButton('collapse', 3, 'Collapse All Items', '', '');
masterRightsCellToolbar.addSeparator('sep2', 4);
masterRightsCellToolbar.addButton('refresh', 5, 'Refresh Items', 'reload.png', 'reload.png');
masterRightsCellToolbar.attachEvent("onClick", masterRightsCellToolbarClicked);


var masterRightsGrid = masterRightsCell.attachGrid();
masterRightsGrid.setSkin('dhx_web');
masterRightsGrid.setImagePath('dhtmlxsuite4/skins/web/imgs/');

masterRightsGrid.setHeader(
        ["Item Name", "Master Rights"],
        null,
        ["text-align:left;", "text-align:center;"]
        );
masterRightsGrid.setColTypes("tree,ch");
masterRightsGrid.attachHeader('#text_filter', ["text-align:left;", "text-align:center;"]);
masterRightsGrid.setColAlign('left,center');
masterRightsGrid.setColSorting('str,int');

masterRightsGrid.enableCellIds(true);
masterRightsGrid.setColumnIds('item,master_rights');

masterRightsGrid.setInitWidthsP('*,*');

masterRightsGrid.attachEvent('onEditCell', function (stage, rId, cInd, nValue, oValue) {
    if (cInd == '0') {
        return false;
    } else {

        if (projectId !== null) {
            return true;
        } else {
            dhtmlx.message({
                title: "Editing Error",
                type: "alert-warning",
                text: "Please Select a Project!"
            });
            return false;
        }
    }
});
masterRightsGrid.attachEvent("onCheckbox", masterRightsGridChecked);
masterRightsGrid.init();
masterRightsGrid.loadXML("Controller/php/project_privileges.php?action=1&tab=5");




userGenerelPrivilegesTabbar.addTab('locationRightsTab', 'Location Rights');
var locationRightsTab = userGenerelPrivilegesTabbar.cells('locationRightsTab');

var locationRightsCellLayout = locationRightsTab.attachLayout('1C');
var locationRightsCell = locationRightsCellLayout.cells('a');
locationRightsCell.hideHeader();

var locationRightsCellToolbar = locationRightsCell.attachToolbar();
locationRightsCellToolbar.setIconsPath('Views/imgs/');
locationRightsCellToolbar.addButton('expand', 1, 'Expand All Items', '', '');
locationRightsCellToolbar.addSeparator('sep1', 2);
locationRightsCellToolbar.addButton('collapse', 3, 'Collapse All Items', '', '');
locationRightsCellToolbar.addSeparator('sep2', 4);
locationRightsCellToolbar.addButton('refresh', 5, 'Refresh Items', 'reload.png', 'reload.png');
locationRightsCellToolbar.attachEvent("onClick", locationRightsCellToolbarClicked);



var locationRightsGrid = locationRightsCell.attachGrid();
locationRightsGrid.setSkin('dhx_web');
locationRightsGrid.setImagePath('dhtmlxsuite4/skins/web/imgs/');

locationRightsGrid.setHeader(
        ["Item Name", "New Location", "Own New Location"],
        null,
        ["text-align:left;", "text-align:center;", "text-align:center"]
        );
locationRightsGrid.setColTypes("tree,ch,ch");
locationRightsGrid.attachHeader('#text_filter,,', ["text-align:left;", "text-align:center;", "text-align:center"]);
locationRightsGrid.setColAlign('left,center,center');
locationRightsGrid.setColSorting('str,int,int');

locationRightsGrid.enableCellIds(true);
locationRightsGrid.setColumnIds('item,new_location,new_own_location');

locationRightsGrid.setInitWidthsP('*,30,30');

locationRightsGrid.attachEvent('onEditCell', function (stage, rId, cInd, nValue, oValue) {
    if (cInd == '0') {
        return false;
    } else {

        if (projectId !== null) {
            return true;
        } else {
            dhtmlx.message({
                title: "Editing Error",
                type: "alert-warning",
                text: "Please Select a Project!"
            });
            return false;
        }
    }
});
locationRightsGrid.attachEvent("onCheckbox", locationRightsGridChecked);
locationRightsGrid.init();
locationRightsGrid.loadXML("Controller/php/project_privileges.php?action=1&tab=6");
