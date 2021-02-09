projectDetailsTabbar.addTab('data', 'Database');
var projects_templates = projectDetailsTabbar.cells('data');
//projectDetailsTabbar.tabs("data").hide();

var projectDataTabbar = projects_templates.attachTabbar();
projectDataTabbar.addTab('projects_data', 'Records');
projectDataTabbar.addTab('data_templates', 'Templates');

var projects_data = projectDataTabbar.cells('projects_data');
projects_data.setActive();


projectsDataToolbar = projects_data.attachToolbar();
projectsDataToolbar.setIconsPath("Views/imgs/");
reloadProjectDataToolbar();
projectsDataToolbar.attachEvent("onClick", projectsDataToolbarClicked);

function projectsDataToolbarClicked(id) {

    if (assetsCatId > 0) {
        $.getJSON('Controller/php/projectData.php?action=13&id=' + assetsCatId, function (data) {

            if (data.response) {
                $.each(data.documents, function (key, value) {

                    var tab_id = "tab_" + value.id;
                    assetDetailsTabbar.tabs(tab_id).close("itemsForm");

                });
            }

        });
    }

    var dataSelectedIdParts = id.split("_");
    assetsCatId = dataSelectedIdParts[0];
    assetsTemplateId = dataSelectedIdParts[1];

    $.getJSON('Controller/php/projectData.php?action=12&templ_id=' + assetsTemplateId, function (data) {
        if (data.response) {
            var pos = 0;

            $.each(data.documents, function (key, value) {

                var tab_id = "tab_" + value.id;
                assetDetailsTabbar.addTab(tab_id, "Document " + ++pos);
//            dhxItemsTab.tabs(tab_id).attachEditor({content:value.body});//attachHTMLString(value.body);
            });
        }
    });

    showAssets(assetsCatId, projectId, assetsTemplateId);

}

var assetsTemplateId = null;
var assetsCatId = null;

var lytProjectsData = projects_data.attachLayout("2E");
lytProjectsData.cells('a').hideHeader();
lytProjectsData.cells('b').hideHeader();
lytProjectsData.cells('b').setHeight(420);

var devicesDataGrid = lytProjectsData.cells("a").attachGrid();
devicesDataGrid.setImagesPath('https://' + location.host + '/dhtmlxsuite4/skins/web/imgs/');
devicesDataGrid.setSkin('dhx_web');
devicesDataGrid.enableColumnAutoSize(true);
devicesDataGrid.enableAutoWidth(true);
devicesDataGrid.enableColumnMove(true);
devicesDataGrid.setColumnColor("#CCE2FE");
//devicesDataGrid.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=26");
//devicesDataGrid.load("Controller/php/json/category_105/assets.json","","json");
devicesDataGrid.attachEvent("onSelectStateChanged", doOndevicesDataGridRowSelect);//onRowSelect

devicesDataGrid.attachEvent("onBeforeSelect", function (new_row, old_row, new_col_index) {

    if (old_row > 0) {

        $.getJSON('Controller/php/projectData.php?action=14&id=' + old_row + '&templ_id=' + assetsTemplateId, function (data) {

            if (data.response) {
                $.each(data.documents, function (key, value) {

                    var tab_id = "tab_" + value.id;
                    assetDetailsTabbar.tabs(tab_id).detachObject(true);

                });
            }

        });
    }
    return true;
});

function doOndevicesDataGridRowSelect(id) {

    $.getJSON('Controller/php/projectData.php?action=14&id=' + id + '&templ_id=' + assetsTemplateId, function (data) {

        if (data.response) {
            $.each(data.documents, function (key, value) {

                var tab_id = "tab_" + value.id;
                assetDetailsTabbar.tabs(tab_id).attachEditor({content: value.body});
            });
        }
    });

    assetRowSelected(id);
    assetFilesGrid.clearAndLoad("https://bo.nts.nl/network/Controller/php/data_files.php?id=" + id);
    assetInfoIframe.contentWindow.tinymce.activeEditor.setContent("");
    $.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=37&device_id=" + id, function (data) {
        if (data.content !== null) {
            assetInfoIframe.contentWindow.tinymce.activeEditor.setContent(data.content);
        }
    }, 'json');

    assetEventForm.clear();
    assetEventsGrid.clearAndLoad("https://bo.nts.nl/network/Controller/php/data_planning.php?action=default&id=" + id);

}

//ITEMS Toolbar

var devicesToolbar = lytProjectsData.cells("a").attachToolbar();
devicesToolbar.setIconsPath('Views/imgs/');
devicesToolbar.addButton("new", 1, "New", "new.gif");
devicesToolbar.addSeparator("sep1", 2);
devicesToolbar.addButton("delete", 3, "Delete", "deleteall.png");
devicesToolbar.addSeparator("sep2", 4);
devicesToolbar.addButton("search", 5, "Search", "search.png");
devicesToolbar.addSeparator("sep3", 6);
devicesToolbar.addButton("refresh", 7, "Refresh", "refresh.png");
devicesToolbar.addSeparator("sep4", 8);
devicesToolbar.addButton("excel", 9, "Import From Excel", "excel.png");
devicesToolbar.addSeparator("sep5", 10);
devicesToolbar.addButton("export", 11, "Export to Excel", "excel.png");
devicesToolbar.addSeparator("sep6", 12);
devicesToolbar.addText("showstatuscheck", 13, "<input id=\"statusCheck\" style= '   -moz-user-select: none;  color: #000000;    float: left;    font-family: Tahoma;    font-size: 11px;    margin-left 0;    margin-right: 1px;    margin-top: 3px;    padding: 0 4px;    vertical-align: middle;' type = 'checkbox'  onclick='handleStatusCheckClick(this);'/>", "", "");
devicesToolbar.addText("showAllStatus", 14, "Show All Statuses", "", "");
devicesToolbar.addSeparator("sep7", 15);
devicesToolbar.addText("showbranchescheck", 16, "<input id=\"branchCheck\" style= '   -moz-user-select: none;  color: #000000;    float: left;    font-family: Tahoma;    font-size: 11px;    margin-left 0;    margin-right: 1px;    margin-top: 3px;    padding: 0 4px;    vertical-align: middle;' type = 'checkbox'  onclick='handleBranchCheckClick(this);'/>", "", "");
devicesToolbar.addText("showAllBranches", 17, "Show All Branches", "", "");
devicesToolbar.attachEvent("onClick", devicesToolbarClicked);

var assetGridSearchPop = new dhtmlXPopup({
    toolbar: devicesToolbar,
    id: "search"
});

var assetGridSearchPopForm = assetGridSearchPop.attachForm([
//    {type: "settings", position: "label-left", labelWidth: 100, inputWidth: 250},
    {type: "input", label: "Asset ID", name: "searchItem"},
    {type: "button", value: "Search", name: "searchButton", offsetLeft: 50}
]);

assetGridSearchPopForm.attachEvent("onKeydown", function (inp, ev, name) {
    //check if key is Enter
    if (ev.keyCode === 13) {
        var value = assetGridSearchPopForm.getItemValue("searchItem");
        searchValue = value;
        assetGridSearchPopForm.clear();
        assetGridSearchPop.hide();
        lytProjectsData.cells("a").progressOn();

        window.dhx4.ajax.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=62&id=" + value, function (r) {
            var t = null;
            try {
                eval("t=" + r.xmlDoc.responseText);
            } catch (e) {
            }
            ;
            if (t !== null) {
                devicesDataGrid.selectRowById(t.projectId);
                lytProjectsData.cells("a").progressOff();
            }
        });
    }
});
assetGridSearchPopForm.attachEvent("onButtonClick", function (name) {
    var value = assetGridSearchPopForm.getItemValue("searchItem");
    searchValue = value;
    assetGridSearchPopForm.clear();
    assetGridSearchPop.hide();
    lytProjectsData.cells("a").progressOn();

    window.dhx4.ajax.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=62&id=" + value, function (r) {
        var t = null;
        try {
            eval("t=" + r.xmlDoc.responseText);
        } catch (e) {
        }
        ;
        if (t !== null) {
            devicesDataGrid.selectRowById(t.projectId);
            lytProjectsData.cells("a").progressOff();
        }
    });
});

var assetDetailsTabbar = lytProjectsData.cells("b").attachTabbar();
assetDetailsTabbar.addTab("asset_fields", "Fields");
var asset_fields = assetDetailsTabbar.cells('asset_fields');
asset_fields.setActive();

var asset_fields_layout = asset_fields.attachLayout('1C');
var asset_fields_cell = asset_fields_layout.cells('a');
asset_fields_cell.hideHeader();

var devForm = asset_fields_cell.attachForm();

//Form Submit Toolbar
var devFormToolbar = asset_fields_cell.attachToolbar();
devFormToolbar.setIconsPath('Views/imgs/');
devFormToolbar.addButton("save", 1, "Save", "save.png");
devFormToolbar.addSeparator("sep1", 2);
devFormToolbar.addButton("show", 3, "Show Password", "show.png");
devFormToolbar.addSeparator("sep2", 4);
devFormToolbar.addButton("generate_password", 5, "Generate Password", "generate.png");
devFormToolbar.addSeparator("sep3", 6);
devFormToolbar.addButton("print_label", 7, "Print Label", "common_printer.png");
devFormToolbar.addSeparator("sep4", 8);
devFormToolbar.addButton("print_pic", 9, "Print Picture", "common_printer.png");
devFormToolbar.addSeparator("sep5", 10);
devFormToolbar.attachEvent("onClick", doOndevFormToolbarClicked);

var passPop = new dhtmlXPopup({
    toolbar: devFormToolbar,
    id: "show" //attaches popup to the "Open" button
});
var passForm = passPop.attachForm([
    //{type: "input",    label: "Email Address", name: "email"},
    {type: "password", label: "Password", name: "pwd"},
    {type: "button", name: "proceed", value: "Proceed", offsetLeft: "50"}
]);
passForm.attachEvent("onKeydown", function (inp, ev, name) {
    //check if key is Enter
    if (ev.keyCode == 13) {

        var device_id = devicesDataGrid.getSelectedRowId();
        if (device_id == null || device_id == "undefined") {
            dhtmlx.alert("No Item selected!");
        } else {
            var value = passForm.getItemValue("pwd");
            if (value == "admin1234") {
                passForm.clear();
                passPop.hide();
                devForm.unload();
                devForm = asset_fields_cell.attachForm();
                devForm.loadStruct("https://bo.nts.nl/network/Controller/php/data_devices.php?action=35&id=" + assetsCatId, function () {
                    loadDevForm(device_id, devForm);

                    devForm.attachEvent("onFocus", function (name) {
                        var nameParts = name.split("_");
                        var field_id = nameParts[1];
                        devFormSelctdId = field_id;

                        assetInfoIframe.contentWindow.tinymce.activeEditor.setContent("");
                        $.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=72&id=" + field_id, function (data) {
                            if (data.content !== null) {
                                assetInfoIframe.contentWindow.tinymce.activeEditor.setContent(data.content);
                            }
                        }, 'json');
                    });

                    devForm.attachEvent("onInfo", devFormOnInfo);

                    function devFormOnInfo(name) {
                        var nameParts = name.split("_");
                        var field_id = nameParts[1];
                        var infoWindow = lytProjectsData.dhxWins.createWindow("info_win", 0, 0, myWidth * 0.23, myHeight * 0.6);
                        infoWindow.center();
                        infoWindow.setText("Select Items");

//Form Submit Toolbar
                        var editFieldToolbar = infoWindow.attachToolbar();
                        editFieldToolbar.setIconsPath('Views/imgs/');
                        editFieldToolbar.addButton("save", 1, "Submit", "submit.gif");
                        editFieldToolbar.addSeparator("sep", 2);
                        editFieldToolbar.attachEvent("onClick", editFieldToolbarClicked);

                        addEditFieldGrid(field_id, device_id, infoWindow);

                        function editFieldToolbarClicked(id) {
                            var device_id = devicesDataGrid.getSelectedRowId();
                            if (device_id === null || device_id === "undefined") {
                                dhtmlx.alert("No Item selected!");
                                infoWindow.close();
                            } else {

                                devForm.send("https://bo.nts.nl/network/Controller/php/data_devices.php?action=24&id=" + device_id + "&templ_id=" + assetsTemplateId, function (loader, response) {
                                    var checked = editFieldGrid.getCheckedRows(2);
                                    lytProjectsData.cells('a').progressOn();
                                    $.post("https://bo.nts.nl/network/Controller/php/data_devices.php?action=51", {field_id: field_id, nValue: checked, device_id: device_id, templ_id: assetsTemplateId}, function (data)
                                    {
                                        lytProjectsData.cells('a').progressOff();
                                        if (data.data.response) {
                                            dhtmlx.message({title: 'Success', text: data.data.text});
                                            devForm.clear();

                                            devForm.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=25&id=" + id + "&templ_id=" + assetsTemplateId, function () {
                                            });
                                            window.dhx4.ajax.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=64&id=" + assetsCatId, function (r) {
                                                var t = null;
                                                try {
                                                    eval("t=" + r.xmlDoc.responseText);
                                                } catch (e) {
                                                }
                                                ;
                                                if (t !== null && t.mtime !== null) {
                                                    devicesDataGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/xml/category_" + assetsCatId + "/asset_values.xml?t=" + t.mtime, function () {
                                                        lytProjectsData.cells("a").progressOff();
                                                    });
//                        devicesDataGrid.updateFromJSON("Controller/php/json/category_" + projectId + "/asset_values.json");
                                                } else {
                                                    devicesDataGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/data_devices.php?action=22&id=" + assetsCatId, function () {
                                                        lytProjectsData.cells("a").progressOff();
                                                    });
                                                }
                                            });

                                        } else {
                                            dhtmlx.alert({title: 'Error', text: data.data.text});
                                        }
                                        infoWindow.close();
                                    }, 'json');
                                });
                            }
                        }
                    }
                });

            } else {
                passForm.clear();
                dhtmlx.alert({title: 'Error', text: 'Wrong Password!'});
            }
        }
    }
});
passForm.attachEvent("onButtonClick", function (name) {
    switch (name)
    {
        case 'proceed':

            var device_id = devicesDataGrid.getSelectedRowId();
            if (device_id == null || device_id == "undefined") {
                dhtmlx.alert("No Item selected!");
            } else {
                var value = passForm.getItemValue("pwd");
                if (value == "admin1234") {
                    passForm.clear();
                    passPop.hide();

                    devForm.unload();
                    devForm = asset_fields_cell.attachForm();
                    devForm.loadStruct("https://bo.nts.nl/network/Controller/php/data_devices.php?action=35&id=" + assetsCatId, function () {
                        loadDevForm(device_id, devForm);

                        devForm.attachEvent("onFocus", function (name) {
                            var nameParts = name.split("_");
                            var field_id = nameParts[1];
                            devFormSelctdId = field_id;

                            assetInfoIframe.contentWindow.tinymce.activeEditor.setContent("");
                            $.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=72&id=" + field_id, function (data) {
                                if (data.content !== null) {
                                    assetInfoIframe.contentWindow.tinymce.activeEditor.setContent(data.content);
                                }
                            }, 'json');
                        });

                        devForm.attachEvent("onInfo", devFormOnInfo);

                        function devFormOnInfo(name) {
                            var nameParts = name.split("_");
                            var field_id = nameParts[1];
                            var infoWindow = lytProjectsData.dhxWins.createWindow("info_win", 0, 0, myWidth * 0.23, myHeight * 0.6);
                            infoWindow.center();
                            infoWindow.setText("Select Items");

//Form Submit Toolbar
                            var editFieldToolbar = infoWindow.attachToolbar();
                            editFieldToolbar.setIconsPath('Views/imgs/');
                            editFieldToolbar.addButton("save", 1, "Submit", "submit.gif");
                            editFieldToolbar.addSeparator("sep", 2);
                            editFieldToolbar.attachEvent("onClick", editFieldToolbarClicked);

                            addEditFieldGrid(field_id, device_id, infoWindow);

                            function editFieldToolbarClicked(id) {
                                var device_id = devicesDataGrid.getSelectedRowId();
                                if (device_id === null || device_id === "undefined") {
                                    dhtmlx.alert("No Item selected!");
                                    infoWindow.close();
                                } else {

                                    devForm.send("https://bo.nts.nl/network/Controller/php/data_devices.php?action=24&id=" + device_id + "&templ_id=" + assetsTemplateId, function (loader, response) {
                                        var checked = editFieldGrid.getCheckedRows(2);
                                        lytProjectsData.cells('a').progressOn();
                                        $.post("https://bo.nts.nl/network/Controller/php/data_devices.php?action=51", {field_id: field_id, nValue: checked, device_id: device_id, templ_id: assetsTemplateId}, function (data)
                                        {
                                            lytProjectsData.cells('a').progressOff();
                                            if (data.data.response) {
                                                dhtmlx.message({title: 'Success', text: data.data.text});
                                                devForm.clear();

                                                devForm.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=25&id=" + id + "&templ_id=" + assetsTemplateId, function () {
                                                });
                                                window.dhx4.ajax.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=64&id=" + assetsCatId, function (r) {
                                                    var t = null;
                                                    try {
                                                        eval("t=" + r.xmlDoc.responseText);
                                                    } catch (e) {
                                                    }
                                                    ;
                                                    if (t !== null && t.mtime !== null) {
                                                        devicesDataGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/xml/category_" + assetsCatId + "/asset_values.xml?t=" + t.mtime, function () {
                                                            lytProjectsData.cells("a").progressOff();
                                                        });
                                                    } else {
                                                        devicesDataGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/data_devices.php?action=22&id=" + assetsCatId, function () {
                                                            lytProjectsData.cells("a").progressOff();
                                                        });
                                                    }
                                                });

                                            } else {
                                                dhtmlx.alert({title: 'Error', text: data.data.text});
                                            }
                                            infoWindow.close();
                                        }, 'json');
                                    });
                                }
                            }
                        }
                    });
                } else {
                    passForm.clear();
                    dhtmlx.alert({title: 'Error', text: 'Wrong Password!'});
                }
            }
            break;
    }
});

assetDetailsTabbar.addTab("asset_info", "Information");
var asset_info = assetDetailsTabbar.cells('asset_info');

var asset_info_layout = asset_info.attachLayout('1C');
asset_info_cell = asset_info_layout.cells('a');
asset_info_cell.hideHeader();

asset_info_cell.attachURL("Views/frames/asset_info.php", false,
        {report_content: '', height: (asset_info_layout.cells('a').getHeight()) / 1.85});
asset_info_layout.attachEvent("onContentLoaded", function (id) {
    assetInfoIframe = asset_info_layout.cells(id).getFrame();
});

assetDetailsTabbar.addTab("asset_files", "Files");
var asset_files = assetDetailsTabbar.cells('asset_files');

var asset_files_layout = asset_files.attachLayout('2U');
asset_file_list_cell = asset_files_layout.cells('a');
asset_file_list_cell.hideHeader();

asset_file_viewer_cell = asset_files_layout.cells('b');
asset_file_viewer_cell.setText('File Viewer');

var assetFilesToolbar = asset_file_list_cell.attachToolbar();
assetFilesToolbar.setIconsPath("Views/imgs/");
assetFilesToolbar.addButton('upload', 1, 'Upload New', 'uploads.png', 'uploads.png');
assetFilesToolbar.addSeparator('sep1', 2);
assetFilesToolbar.addButton('delete', 3, 'Delete', 'deleteall.png', 'deleteall.png');
assetFilesToolbar.addSeparator('sep2', 4);
assetFilesToolbar.attachEvent("onClick", function (id) {
    switch (id)
    {
        case 'upload':
            var row_id = devicesDataGrid.getSelectedRowId();
            if (row_id == null || row_id == "undefined") {
                dhtmlx.alert("No Device Selected!");
                return false;
            } else {

                var uploadBoxformData = [{
                        type: "fieldset",
                        label: "Uploader",
                        list: [{
                                type: "upload",
                                name: "myFiles",
                                inputWidth: 330,
                                url: "https://bo.nts.nl/network/Controller/php/data_files.php?action=1&id=" + row_id,
                                swfPath: "https://" + location.host + "/dhtmlxsuite4/codebase/ext/uploader.swf",
//                                            swfUrl: "https://" + location.host + "/script/dhtmlx3.6pro/dhtmlxForm/samples/07_uploader/php/dhtmlxform_item_upload.php"
                            }]
                    }];

                var popupMainWindow = new dhtmlXWindows();
                var popupWindow = popupMainWindow.createWindow("upload_win1", 0, 0, 400, 180);
                popupWindow.center();
                popupWindow.setText("Upload picture(s)");
                //add form
                var uploadForm = popupWindow.attachForm(uploadBoxformData);
                uploadForm.attachEvent("onUploadComplete", function () {
                    dhtmlx.message('file uploaded');
                    assetFilesGrid.clearAndLoad("https://bo.nts.nl/network/Controller/php/data_files.php?id=" + row_id);
                    popupMainWindow.window('upload_win1').hide();
                });

                uploadForm.attachEvent("onUploadFail", function (realName) {
                    dhtmlx.alert({title: 'Error', text: 'The was an error uploading ' + realName});
                });
            }
            break;

        case 'delete':
            var row_id = assetFilesGrid.getSelectedRowId();
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
                            $.get("https://bo.nts.nl/network/Controller/php/data_files.php?action=2&id=" + row_id, function (data) {

                                assetFilesGrid.deleteRow(row_id);
                                dhtmlx.message(data.response);
                                asset_file_viewer_cell.detachObject(true);
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
});

//files grid
var assetFilesGrid = asset_file_list_cell.attachGrid();
assetFilesGrid.setImagesPath('https://' + location.host + '/dhtmlxsuite4/skins/web/imgs/');
assetFilesGrid.setHeader("#,File Name,Info,File size,Upload Date,Type,Viewer");
assetFilesGrid.setColumnIds("id,name,info,filesize,upload_date,type,viewer");
assetFilesGrid.attachHeader('#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter');
assetFilesGrid.setInitWidthsP("6,*,17,10,19,12,10");
assetFilesGrid.setColTypes('cntr,ed,combo,ro,ro,ro,ch');
assetFilesGrid.setColAlign('left,left,left,left,left,left,center');
assetFilesGrid.setSkin('dhx_web');
assetFilesGrid.attachEvent("onSelectStateChanged", doOnassetFilesGridRowSelect);//onRowSelect
assetFilesGrid.attachEvent("onEditCell", doOnEditassetFilesGrid);
assetFilesGrid.init();

assetFilesGridInfoCombo = assetFilesGrid.getColumnCombo(2);
assetFilesGridInfoCombo.enableFilteringMode(true);
assetFilesGridInfoCombo.addOption([
    ["1", "Left-side"],
    ["2", "Bottom-side"],
    ["3", "Right-side"],
    ["4", "Top-side"],
    ["5", "SVG Top-side"]
]);

function doOnassetFilesGridRowSelect(id) {
    var filename = assetFilesGrid.cells(id, 1).getValue();
    var filename = filename.replace(/(<([^>]+)>)/ig, "");

    asset_file_viewer_cell.attachURL("https://bo.nts.nl/network/Controller/files/" + filename);
}

function doOnEditassetFilesGrid(stage, id, index, new_value, old_value, cellIndex) {
    var device_id = devicesDataGrid.getSelectedRowId();
    var cell = assetFilesGrid.cells(id, index);
    if (stage === 2 && !cell.isCheckbox()) {
        if (id > 0 || typeof id != 'undefined') {
            var colId = assetFilesGrid.getColumnId(index);
            var colType = assetFilesGrid.fldSort[index];
            $.get("https://bo.nts.nl/network/Controller/php/data_files.php?action=3&id=" + id + "&index=" + index + "&fieldvalue=" + new_value + "&colId=" + colId + "&colType=" + colType, function (data) {
                if (data.data.response) {
                    dhtmlx.message({type: "Success", text: data.data.text});
                    assetFilesGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/data_files.php?id=" + device_id);
                } else
                    dhtmlx.alert({title: 'Error', text: data.data.text});
            }, 'json');
        }
    } else
    if (stage == 0 && cell.isCheckbox()) {
        return true;
    }
}

assetFilesGrid.attachEvent("onCheck", function (id, index, state) {
    var colId = assetFilesGrid.getColumnId(index);
    $.post("https://bo.nts.nl/network/Controller/php/data_files.php?action=4", {colId: colId, id: id, nValue: ((state) ? 1 : 0)}, function (data)
    {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
});

assetDetailsTabbar.addTab("asset_planning", "Planning");
var asset_planning = assetDetailsTabbar.cells('asset_planning');



assetDetailsTabbar.addTab("asset_manual", "Field Manual");
var asset_manual = assetDetailsTabbar.cells('asset_manual');

var asset_manual_layout = asset_manual.attachLayout('1C');
asset_manual_cell = asset_manual_layout.cells('a');
asset_manual_cell.hideHeader();

asset_manual_cell.attachURL("Views/frames/field_procedure.php", false,
        {report_content: '', height: (asset_manual_layout.cells('a').getHeight()) / 1.85});
asset_manual_layout.attachEvent("onContentLoaded", function (id) {
    assetfieldManualIframe = asset_manual_layout.cells(id).getFrame();
});

projectDetailsTabbar.addTab('object_viewer', 'Object Viewer');
var object_viewer = projectDetailsTabbar.cells('object_viewer');
projectDetailsTabbar.tabs("object_viewer").hide();

var assetCategoriesMenu = object_viewer.attachMenu();
assetCategoriesMenu.setIconsPath('Views/imgs/');
//assetCategoriesMenu.setSkin("dhx_skyblue");

assetCategoriesMenu.attachEvent("onCheckboxClick", function (id, state, zoneId, cas) {

    newState = (state === true) ? false : true;
    assetCategoriesMenu.setCheckboxState(id, newState);
    var checked = [];
    assetCategoriesMenu.forEachItem(function (itemId) {
        var isChecked = assetCategoriesMenu.getCheckboxState(itemId);
        if (isChecked) {
            var roomIdParts = id.split("_");
            var room_id = roomIdParts[1];
            checked.push(room_id);
        }
    });

    object_viewer.attachURL("https://bo.nts.nl/network/Controller/php/onlineViewer/onlineViewer.php?action=1&id=" + checked.join() + "&cat_id=" + assetsCatId + "&dimmId=0&statusVal=0");
    // allow checkbox to be checked
    return true;
});

function doOndevFormToolbarClicked(id) {

    switch (id)
    {
        case 'save':

            var device_id = devicesDataGrid.getSelectedRowId();

            if (device_id === null || device_id === "undefined") {
                dhtmlx.alert("No Item selected!");
            } else {
                lytProjectsData.cells('a').progressOn();
                devForm.send("https://bo.nts.nl/network/Controller/php/data_devices.php?action=24&id=" + device_id + "&templ_id=" + assetsTemplateId + "&cat_id=" + assetsCatId, function (loader, response) {
                    lytProjectsData.cells('a').progressOff();
                    var parsedJSON = eval('(' + response + ')');

                    if (parsedJSON.data.success) {

                        devicesDataGrid.updateFromXML("Controller/php/projectData.php?action=11&id=" + assetsCatId + "&projectId=" + projectId + "&templateId=" + assetsTemplateId, true, true);

                        dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                    } else {
                        dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                    }
                }, 'json');
            }
            break;

        case 'generate_password':

            var device_id = devicesDataGrid.getSelectedRowId();

            if (device_id == null || device_id == "undefined") {
                dhtmlx.alert("No Item selected!");
            } else {
                var postData = {"id": device_id, "templ_id": assetsTemplateId};
                $.post("https://bo.nts.nl/network/Controller/php/data_devices.php?action=58", postData, function (data) {
                    if (data.data.response) {
                        dhtmlx.message({type: "Success", text: data.data.text});
                        devForm.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=25&id=" + device_id + "&templ_id=" + assetsTemplateId, function () {
                            $.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=48&id=" + assetsCatId, function (data)
                            {
                                var branchCombo = devForm.getCombo(data.branch);
                                var officeCombo = devForm.getCombo(data.office);
                                var selected = branchCombo.getActualValue();
                                var value = devForm.getItemValue(data.office);
                                officeCombo.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=49&id=" + selected + "&value=" + value);

                                branchCombo.attachEvent("onChange", function () {
                                    var selected2 = branchCombo.getActualValue();
                                    officeCombo.clearAll();
                                    officeCombo.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=49&id=" + selected2 + "&value=" + value);
                                });
                            }, "json");
                        });
                    } else
                        dhtmlx.alert({title: 'Error', text: data.data.text});
                }, 'json');
            }
            break;
        case 'print_label':

            var device_id = devicesDataGrid.getSelectedRowId();
            if (device_id !== null) {
                window.open("https://bo.nts.nl/network/Controller/php/printPdfLabel.php?action=default&id=" + device_id + "&templ_id=" + assetsCatId);
            }

            break;
        case 'print_pic':

            var device_id = devicesDataGrid.getSelectedRowId();
            if (device_id !== null) {
                window.open("https://bo.nts.nl/network/Controller/php/printPdfLabel.php?action=1&id=" + device_id + "&templ_id=" + assetsCatId);
            }
            break;
    }
}


function showAssets(selectedId, projectId, templateId) {
    devForm.unload();
    devForm = asset_fields_cell.attachForm();
    devicesDataGrid.clearAll(true);
    lytProjectsData.cells("a").progressOn();

    devicesDataGrid.load("Controller/php/projectData.php?action=10&id=" + selectedId + "&projectId=" + projectId + "&templateId=" + templateId, function () {
        if (searchValue > 0) {
            devicesDataGrid.selectRowById(searchValue);
        }
        lytProjectsData.cells("a").progressOff();
    });

    devForm.loadStruct("https://bo.nts.nl/network/Controller/php/data_devices.php?action=20&id=" + selectedId, function () {
        $.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=48&id=" + selectedId, function (data)
        {
            var branchCombo = devForm.getCombo(data.branch);
            var officeCombo = devForm.getCombo(data.office);
            var selected = branchCombo.getActualValue();
            officeCombo.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=49&id=" + selected);

            branchCombo.attachEvent("onChange", function () {
                var selected2 = branchCombo.getActualValue();
                officeCombo.clearAll();
                officeCombo.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=49&id=" + selected2);
            });

        }, "json");
    });
}

function assetRowSelected(id) {
    devForm.unload();
    devForm = asset_fields_cell.attachForm();
    devForm.loadStruct("https://bo.nts.nl/network/Controller/php/data_devices.php?action=20&id=" + assetsCatId + "&record_id=" + id, function () {

        loadDevForm(id, devForm);

        devForm.attachEvent("onFocus", function (name) {
            var nameParts = name.split("_");
            var field_id = nameParts[1];
            devFormSelctdId = field_id;

            assetInfoIframe.contentWindow.tinymce.activeEditor.setContent("");
            $.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=72&id=" + field_id, function (data) {
                if (data.content !== null) {
                    assetInfoIframe.contentWindow.tinymce.activeEditor.setContent(data.content);
                }
            }, 'json');
        });

        devForm.attachEvent("onInfo", devFormOnInfo);

        function devFormOnInfo(name) {
            var nameParts = name.split("_");
            var field_id = nameParts[1];
            var infoWindow = lytProjectsData.dhxWins.createWindow("info_win", 0, 0, myWidth * 0.23, myHeight * 0.6);
            infoWindow.center();
            infoWindow.setText("Select Items");

//Form Submit Toolbar
            var editFieldToolbar = infoWindow.attachToolbar();
            editFieldToolbar.setIconsPath('Views/imgs/');
            editFieldToolbar.addButton("save", 1, "Submit", "submit.gif");
            editFieldToolbar.addSeparator("sep", 2);
            editFieldToolbar.attachEvent("onClick", editFieldToolbarClicked);

            addEditFieldGrid(field_id, id, infoWindow);

            function editFieldToolbarClicked(id) {
                var device_id = devicesDataGrid.getSelectedRowId();
                if (device_id === null || device_id === "undefined") {
                    dhtmlx.alert("No Item selected!");
                    infoWindow.close();
                } else {

                    devForm.send("https://bo.nts.nl/network/Controller/php/data_devices.php?action=24&id=" + device_id + "&templ_id=" + assetsTemplateId, function (loader, response) {
                        var checked = editFieldGrid.getCheckedRows(2);
                        lytProjectsData.cells('a').progressOn();
                        $.post("https://bo.nts.nl/network/Controller/php/data_devices.php?action=51", {field_id: field_id, nValue: checked, device_id: device_id, templ_id: assetsTemplateId}, function (data)
                        {
                            lytProjectsData.cells('a').progressOff();
                            if (data.data.response) {
                                dhtmlx.message({title: 'Success', text: data.data.text});
                                devForm.clear();

                                devForm.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=25&id=" + id + "&templ_id=" + assetsTemplateId, function () {
                                });
                                window.dhx4.ajax.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=64&id=" + assetsCatId, function (r) {
                                    var t = null;
                                    try {
                                        eval("t=" + r.xmlDoc.responseText);
                                    } catch (e) {
                                    }
                                    ;
                                    if (t !== null && t.mtime !== null) {
                                        devicesDataGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/xml/category_" + assetsCatId + "/asset_values.xml?t=" + t.mtime, function () {
                                            lytProjectsData.cells("a").progressOff();
                                        });
//                        devicesDataGrid.updateFromJSON("Controller/php/json/category_" + projectId + "/asset_values.json");
                                    } else {
                                        devicesDataGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/data_devices.php?action=22&id=" + assetsCatId, function () {
                                            lytProjectsData.cells("a").progressOff();
                                        });
                                    }
                                });

                            } else {
                                dhtmlx.alert({title: 'Error', text: data.data.text});
                            }
                            infoWindow.close();
                        }, 'json');
                    });
                }
            }
        }
    });
}

function addEditFieldGrid(field_id, device_id, infoWindow) {
    var editFieldGrid = infoWindow.attachGrid();
    editFieldGrid.setImagesPath('https://' + location.host + '/dhtmlxsuite4/skins/web/imgs/');
    editFieldGrid.setHeader("ID,Description,Check");
    editFieldGrid.attachHeader("#text_filter,#text_filter,#master_checkbox");
    editFieldGrid.setColumnIds("id,name,select");
    editFieldGrid.setColSorting("int,str,int");
    editFieldGrid.setSkin('dhx_web');
    editFieldGrid.enableMultiselect(true);
    editFieldGrid.setInitWidthsP("20,*,14");
    editFieldGrid.setColTypes("ro,ro,ch");
    editFieldGrid.setColAlign("left,left,center");
    editFieldGrid.init();
    editFieldGrid.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=50&id=" + field_id, function () {
        $.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=52&field_id=" + field_id + "&device_id=" + device_id, function (data)
        {
            var checked = data.value;
            if (checked !== null) {
                var checkedArray = checked.split(",");
                for (var i = 0; i < checkedArray.length; i++)
                {
                    var row_id = checkedArray[i];
                    editFieldGrid.cells(row_id, 2).setValue("1");
                }
            }
        }, "json");
    });
}

function loadDevForm(id, devForm) {
    devForm.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=25&id=" + id + "&templ_id=" + assetsTemplateId, function () {
        $.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=48&id=" + assetsCatId, function (data)
        {
            var branchCombo = devForm.getCombo(data.branch);
            var officeCombo = devForm.getCombo(data.office);
            var selected = branchCombo.getActualValue();
            var value = devForm.getItemValue(data.office);
            officeCombo.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=49&id=" + selected + "&value=" + value);

            branchCombo.attachEvent("onChange", function () {
                var selected2 = branchCombo.getActualValue();
                officeCombo.clearAll();
                officeCombo.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=49&id=" + selected2 + "&value=" + value);
            });

            if (assetsTemplateId === '362') {
                var categoryCombo = devForm.getCombo(data.category);
                var subcategoryCombo = devForm.getCombo(data.subcategory);
                var value = devForm.getItemValue(data.subcategory);
//                    var selected = categoryCombo.getActualValue();
//                    
//                    subcategoryCombo.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=60&id=" + selected + "&value=" + value);

                categoryCombo.attachEvent("onChange", function () {
                    var selected2 = categoryCombo.getActualValue();
                    subcategoryCombo.clearAll(true);
                    subcategoryCombo.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=60&id=" + selected2 + "&value=" + value);
                });
            }

        }, "json");

    });
}

function devicesToolbarClicked(id) {
    switch (id)
    {
        case 'new':

            if (assetsCatId === null) {
                dhtmlx.alert("Please Select Category!");
            } else {
                $.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=23&id=" + assetsCatId, function (data) {
                    if (data.data.response) {

                        window.dhx4.ajax.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=64&id=" + assetsCatId, function (r) {
                            var t = null;
                            try {
                                eval("t=" + r.xmlDoc.responseText);
                            } catch (e) {
                            }
                            ;
                            if (t !== null && t.mtime !== null) {

                                devicesDataGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/xml/category_" + assetsCatId + "/asset_values.xml?t=" + t.mtime, true, true, function () {
                                    devicesDataGrid.selectRowById(data.data.newId);
                                });
                            } else {
                                devicesDataGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/data_devices.php?action=22&id=" + assetsCatId, true, true, function ()

                                {
                                    devicesDataGrid.selectRowById(data.data.newId);
                                });
                            }
                        });

                        dhtmlx.message({title: 'Success', text: data.data.text});

                    } else {
                        dhtmlx.alert({title: 'Error', text: data.data.text});
                    }
                }, 'json');
            }
            break;


        case 'delete':

            var row_id = devicesDataGrid.getSelectedRowId();
            if (row_id > 0) {
                dhtmlx.confirm({
                    title: "Confirm",
                    type: "confirm-warning",
                    text: "Are you sure you to delete this record?",
                    callback: function (ok) {
                        if (ok)
                        {
                            $.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=29&id=" + row_id + "&cat_id=" + assetsCatId, function (data) {
                                if (data.data.response) {
                                    devicesDataGrid.deleteRow(row_id);
                                    devForm.clear();
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
                dhtmlx.alert({title: 'Error', text: 'No Row Selected!'});
            }
            break;

        case 'refresh':

            if (assetsCatId == null) {
                dhtmlx.alert("Please Select Category!");
            } else {
                devicesDataGrid.clearAll(true);

                window.dhx4.ajax.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=64&id=" + assetsCatId, function (r) {
                    var t = null;
                    try {
                        eval("t=" + r.xmlDoc.responseText);
                    } catch (e) {
                    }
                    ;
                    if (t !== null && t.mtime !== null) {
                        devicesDataGrid.load("https://bo.nts.nl/network/Controller/php/xml/category_" + assetsCatId + "/assets.xml?t=" + t.mtime);

                    } else {
                        devicesDataGrid.load("https://bo.nts.nl/network/Controller/php/data_devices.php?action=4&id=" + assetsCatId);
                    }
                });
            }
            break;

        case 'excel':

            if (assetsCatId == null) {
                dhtmlx.alert("Please Select Category!");
            } else {
                var uploadBoxformData = [{
                        type: "fieldset",
                        label: "Uploader",
                        list: [{
                                type: "upload",
                                name: "myFiles",
                                inputWidth: 330,
                                url: "https://bo.nts.nl/network/Controller/php/data_import.php?action=1",
                                swfPath: "https://" + location.host + "/dhtmlxsuite4/codebase/ext/uploader.swf",
//                                            swfUrl: "https://" + location.host + "/script/dhtmlx3.6pro/dhtmlxForm/samples/07_uploader/php/dhtmlxform_item_upload.php"
                            }]
                    }];

                var popupMainWindow = new dhtmlXWindows();
                var popupWindow = popupMainWindow.createWindow("upload_win1", 0, 0, 400, 180);
                popupWindow.center();
                popupWindow.setText("Upload excel file");
                //add form
                var uploadForm = popupWindow.attachForm(uploadBoxformData);
                uploadForm.attachEvent("onUploadFile", function (realName, serverName) {
                    var filename = serverName;
                    popupMainWindow.window('upload_win1').hide();
                    var popupMainWindow1 = new dhtmlXWindows();
                    var popupWindow1 = popupMainWindow1.createWindow("upload_win2", 0, 0, 400, 600);
                    popupWindow1.center();
                    popupWindow1.setText("Fields Matching");
                    //add form
                    var tempFieldsForm = popupWindow1.attachForm();
                    tempFieldsForm.loadStruct("https://bo.nts.nl/network/Controller/php/data_import.php?action=3&id=" + assetsCatId + "&file=" + filename);
                    tempFieldsForm.attachEvent("onButtonClick", function (name) {
                        data.progressOn();
                        tempFieldsForm.send("https://bo.nts.nl/network/Controller/php/data_import.php?action=2&id=" + assetsCatId + "&file=" + filename, function (loader, response) {
                            data.progressOff();
                            var parsedJSON = eval('(' + response + ')');
                            if (parsedJSON.success) {
                                popupMainWindow1.window('upload_win2').hide();

                                var popupMainWindow2 = new dhtmlXWindows();
                                var popupWindow2 = popupMainWindow2.createWindow("upload_win3", 0, 0, 1200, 600);
                                popupWindow2.center();
                                popupWindow2.setText("Records View");

                                //add grid
                                var myLayout = popupWindow2.attachLayout("1C");
                                myLayout.cells("a").hideHeader();
                                var devicesPopUpGrid = myLayout.cells("a").attachGrid();
                                devicesPopUpGrid.setImagesPath("https://" + location.host + "/dhtmlxsuite4/codebase/imgs/");
                                devicesPopUpGrid.setSkin('dhx_web');
                                devicesPopUpGrid.enableColumnAutoSize(true);
                                devicesPopUpGrid.enableAutoWidth(true);
                                devicesPopUpGrid.enableColumnMove(true);
                                devicesPopUpGrid.load("https://bo.nts.nl/network/Controller/php/data_import.php?action=5");
                                //devicesPopUpGrid.attachEvent("onSelectStateChanged", doOndevicesDataGridRowSelect);//onRowSelect
                                devicesPopUpGrid.attachEvent("onCheck", function (rId, cInd, state) {
                                    var colId = devicesPopUpGrid.getColumnId(cInd);
                                    $.post("https://bo.nts.nl/network/Controller/php/data_import.php?action=7", {colId: colId, id: rId, nValue: ((state) ? 1 : 0)}, function (data)
                                    {
                                        if (data.data.response) {
                                            dhtmlx.message({title: 'Success', text: data.data.text});
                                        } else {
                                            dhtmlx.alert({title: 'Error', text: data.data.text});
                                        }
                                    }, 'json');
                                });
                            }
                            var myToolbar = myLayout.cells("a").attachToolbar();
                            myToolbar.setIconsPath('Views/imgs/');
                            myToolbar.addButton("submit", 1, "Submit", "submit.gif");
                            myToolbar.addSeparator("sep", 2);
                            myToolbar.attachEvent("onClick", myToolbarClicked);
                            function myToolbarClicked(id) {
                                switch (id)
                                {
                                    case 'submit':
                                        popupMainWindow2.window('upload_win3').hide();
                                        lytProjectsData.cells("a").progressOn();
                                        $.get("https://bo.nts.nl/network/Controller/php/data_import.php?action=6&id=" + assetsCatId, function (data) {
                                            window.dhx4.ajax.get("https://bo.nts.nl/network/Controller/php/data_devices.php?action=64&id=" + assetsCatId, function (r) {
                                                var t = null;
                                                try {
                                                    eval("t=" + r.xmlDoc.responseText);
                                                } catch (e) {
                                                }
                                                ;
                                                if (t !== null && t.mtime !== null) {
                                                    devicesDataGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/xml/category_" + assetsCatId + "/asset_values.xml?t=" + t.mtime, true, true, function () {
                                                        lytProjectsData.cells("a").progressOff();
                                                    });
//                        devicesDataGrid.updateFromJSON("Controller/php/json/category_" + projectId + "/asset_values.json");
                                                } else {
                                                    devicesDataGrid.updateFromXML("https://bo.nts.nl/network/Controller/php/data_devices.php?action=22&id=" + assetsCatId, true, true, function () {
                                                        lytProjectsData.cells("a").progressOff();
                                                    });
                                                }
                                            });

                                        }, 'json');

                                        break;
                                }
                            }
                            //dhtmlx.message(parsedJSON.message);
                        }, 'json');

                    });
                }, 'json');

                uploadForm.attachEvent("onUploadFail", function (realName) {
                    dhtmlx.alert('The was an error uploading ' + realName);
                });
            }
            break;

        case 'export':

            if (assetsCatId === null) {
                dhtmlx.alert("Please Select Category!");
            } else {

                var exportToExcelWindow = lytProjectsData.dhxWins.createWindow("export_win", 0, 0, myWidth * 0.23, myHeight * 0.8);
                exportToExcelWindow.center();
                exportToExcelWindow.setText("Select Items To Export");

                //Export to excel popup window Toolbar
                var exportToExcelToolbar = exportToExcelWindow.attachToolbar();
                exportToExcelToolbar.setIconsPath('Views/imgs/');
                exportToExcelToolbar.addButton("proceed", 1, "Proceed", "submit.gif");
                exportToExcelToolbar.addSeparator("sep", 2);
                exportToExcelToolbar.attachEvent("onClick", function () {

                    var branch_ids = exportToExcelBranchesGrid.getCheckedRows(1);
                    var field_ids = exportToExcelFieldsGrid.getCheckedRows(1);
                    var url = "https://bo.nts.nl/network/Controller/php/data_excel.php?action=default&id=" + assetsCatId + "&branch=" + branch_ids + "&fields=" + field_ids + "&templ_id=" + assetsTemplateId;
                    window.open(url);
                    exportToExcelWindow.close();
                });

                //export to excel popup window for branch and fields seletion
                var exportToExcelLyt = exportToExcelWindow.attachLayout('2E');
                exportToExcelLyt.cells('a').setText('Select Branch');
                exportToExcelLyt.cells('b').setText('Select Fields');
                exportToExcelLyt.cells('a').setHeight(myHeight * 0.25);

                //export to excel branch selection grid
                var exportToExcelBranchesGrid = exportToExcelLyt.cells('a').attachGrid();
                exportToExcelBranchesGrid.setImagesPath('https://' + location.host + '/dhtmlxsuite4/skins/web/imgs/');
                exportToExcelBranchesGrid.setHeader("Branch Name,Check");
                exportToExcelBranchesGrid.attachHeader("#text_filter,#master_checkbox");
                exportToExcelBranchesGrid.setColumnIds("name,select");
                exportToExcelBranchesGrid.setColSorting("str,int");
                exportToExcelBranchesGrid.setSkin('dhx_web');
                exportToExcelBranchesGrid.enableMultiselect(true);
                exportToExcelBranchesGrid.setInitWidthsP("*,14");
                exportToExcelBranchesGrid.setColTypes("ro,ch");
                exportToExcelBranchesGrid.setColAlign("left,center");
                exportToExcelBranchesGrid.init();
                exportToExcelBranchesGrid.load("https://bo.nts.nl/network/Controller/php/data_excel.php?action=1");

                //export to excel fields selection grid
                var exportToExcelFieldsGrid = exportToExcelLyt.cells('b').attachGrid();
                exportToExcelFieldsGrid.setImagesPath('https://' + location.host + '/dhtmlxsuite4/skins/web/imgs/');
                exportToExcelFieldsGrid.setHeader("Field Name,Check");
                exportToExcelFieldsGrid.attachHeader("#text_filter,#master_checkbox");
                exportToExcelFieldsGrid.setColumnIds("name,select");
                exportToExcelFieldsGrid.setColSorting("str,int");
                exportToExcelFieldsGrid.setSkin('dhx_web');
                exportToExcelFieldsGrid.enableMultiselect(true);
                exportToExcelFieldsGrid.setInitWidthsP("*,14");
                exportToExcelFieldsGrid.setColTypes("ro,ch");
                exportToExcelFieldsGrid.setColAlign("left,center");
                exportToExcelFieldsGrid.init();
                exportToExcelFieldsGrid.load("https://bo.nts.nl/network/Controller/php/data_excel.php?action=2&id=" + assetsTemplateId);

            }
            break;

        case 'report':
//            reportPopup();

            break;
    }
}


var data_templates = projectDataTabbar.cells('data_templates');
data_templates.setActive();

var dataTemplatesLayout = data_templates.attachLayout("1C");
dataTemplatesLayout.cells("a").hideHeader();

var dataTemplatesGrid = dataTemplatesLayout.cells("a").attachGrid();
dataTemplatesGrid.setImagesPath('https://' + location.host + '/dhtmlxsuite4/skins/web/imgs/');
dataTemplatesGrid.setSkin('dhx_web');
dataTemplatesGrid.setHeader("#,Template ID,Template Title,Default,Query");
dataTemplatesGrid.setColumnIds("counter,asset_cat_id,template,default_value,query");
dataTemplatesGrid.attachHeader("#numeric_filter,#text_filter,#text_filter,,");
dataTemplatesGrid.setColSorting('cntr,str,str,int,str');
dataTemplatesGrid.setInitWidthsP("5,10,20,10,*");
dataTemplatesGrid.setColTypes('cntr,ed,ro,ra,ed');
dataTemplatesGrid.setColAlign('left,left,left,left,left');
dataTemplatesGrid.setDateFormat("%Y-%m-%d %H:%i:%s");
//dataTemplatesGrid.attachEvent("onSelectStateChanged", doOnarticleExplorerTranslationGridRowSelect);//onRowSelect
dataTemplatesGrid.attachEvent("onEditCell", doOnEditProjectsGrid);
dataTemplatesGrid.attachEvent("onCheck", doOnProjectsGridChecked);
dataTemplatesGrid.init();

var dataTemplatesGridToolbar = dataTemplatesLayout.cells("a").attachToolbar();
dataTemplatesGridToolbar.setIconsPath('Views/imgs/');
dataTemplatesGridToolbar.addButton("add", 1, "Add New", "new.gif", "new.gif");
dataTemplatesGridToolbar.addSeparator("sep1", 2);
dataTemplatesGridToolbar.addButton("delete", 3, "Delete", "deleteall.png", "deleteall.png");
dataTemplatesGridToolbar.addSeparator("sep2", 4);

dataTemplatesGridToolbar.attachEvent("onClick", dataTemplatesGridToolbarClicked);

function dataTemplatesGridToolbarClicked(id) {
    switch (id)
    {
        case 'add':

            if (projectId > 0) {

                var projectFormData =
                        [{type: "settings", position: "label-left", labelWidth: myWidth * 0.07, inputWidth: myWidth * 0.1, offsetTop: 10, offsetLeft: 10},
                            {type: "hidden", label: "Template ID", className: "formbox", width: myWidth * 0.2, list:
                                        [
                                            {type: "input", label: "Template ID:", name: "template_id", value: ""},
                                            {type: "button", name: "submit", value: "submit", offsetLeft: 150}
                                        ]}
                        ];

                var popupMainWindow = new dhtmlXWindows();
                var addprojectWindow = popupMainWindow.createWindow("addprojectWindow", 0, 0, 400, 150);

                addprojectWindow.center();
                addprojectWindow.setText("Enter Template ID to Add");

                var addProjectForm = addprojectWindow.attachForm(projectFormData);

                addProjectForm.attachEvent("onButtonClick", function () {
                    var itemId = addProjectForm.getItemValue("template_id");

                    $.get("Controller/php/data_projects.php?action=1&id=" + projectId + "&template_id=" + itemId, function (data) {
                        if (data.data.success)
                        {

                            dhtmlx.message({type: "Success", text: data.data.text});
                            dataTemplatesGrid.updateFromXML("Controller/php/data_projects.php?action=3&id=" + projectId, true, true, function ()
                            {
                                dataTemplatesGrid.selectRowById(data.data.id);
                                projectsDataToolbar.clearAll();
                                reloadProjectDataToolbar(projectId);
                            });
                        } else
                            dhtmlx.alert({title: 'Error', text: data.data.text});
                    }, 'json');
                    addprojectWindow.hide();
                });

            } else {
                dhtmlx.alert("Please select Asset Category!");
            }
            break;

        case 'delete':

            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you to delete this  Field?",
                callback: function (ok) {
                    if (ok)
                    {
                        var row_id = dataTemplatesGrid.getSelectedRowId();
                        $.get("Controller/php/data_projects.php?action=2&id=" + row_id, function (data) {
//                            dataTemplatesGrid.deleteRow(row_id);
                            dataTemplatesGrid.updateFromXML("Controller/php/data_projects.php?action=3&id=" + projectId, true, true, function () {});
                            dhtmlx.message({type: "Success", text: data.data.text});
                            reloadProjectDataToolbar(projectId);
                        }, 'json');
                    } else
                    {
                        return false;
                    }
                }

            });
            break;
    }
}

function doOnEditProjectsGrid(stage, id, index, new_value, old_value, cellIndex) {

    var cell = dataTemplatesGrid.cells(id, index);
    if (stage === 2 && !cell.isCheckbox()) {
        var device_id = dataTemplatesGrid.getSelectedRowId();
        if (device_id > 0 || typeof device_id != 'undefined') {
            var colId = dataTemplatesGrid.getColumnId(index);
            var colType = dataTemplatesGrid.fldSort[index];

            $.post("Controller/php/data_projects.php?action=4", {id: id, index: index, fieldvalue: new_value, colId: colId, colType: colType}, function (data) {
                if (data.data.response) {
                    dhtmlx.message({type: "Success", text: data.data.text});
                    dataTemplatesGrid.updateFromXML("Controller/php/data_projects.php?action=3&id=" + projectId);
                    reloadProjectDataToolbar(projectId);
                } else
                    dhtmlx.alert({title: 'Error', text: data.data.text});
            }, 'json');
        }
    } else
    if (stage === 0 && cell.isCheckbox()) {
        return true;
    }
}

function doOnProjectsGridChecked(id, index, state) {

    var colId = dataTemplatesGrid.getColumnId(index);
    $.post("Controller/php/data_projects.php?action=5", {colId: colId, id: id, projectId: projectId, nValue: ((state) ? 1 : 0)}, function (data)
    {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
}