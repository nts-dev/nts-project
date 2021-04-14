function reloadProjectDataToolbar(reload = false) {

    if (reload) {
        projectsDataToolbar.clearAll();
    }
    projectsDataToolbar.loadStruct("Controller/php/projectData.php?action=1&id=" + projectId, function () {
//        projectsDataToolbar.hideItem("assets");
        var selectedId = projectsDataToolbar.getListOptionSelected("assets");
        if (selectedId !== null) {

            projects_data.show();
//            projectDetailsTabbar.tabs("object_viewer").show();
            projectsDataToolbar.setListOptionSelected("assets", selectedId);
            var dataSelectedId = projectsDataToolbar.getListOptionSelected("assets");
            var dataSelectedIdParts = dataSelectedId.split("_");

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

            assetCategoriesMenu.clearAll();
            object_viewer.detachObject(true);
            assetCategoriesMenu.loadStruct("Controller/php/projectData.php?action=7&templateId=" + assetsTemplateId, function () {
            });

            $.get("Controller/php/projectData.php?action=8&templateId=" + assetsTemplateId, function (data) {
                if (data.data.response) {
                    object_viewer.attachURL("https://" + location.host + "/network/Controller/php/onlineViewer/onlineViewer.php?action=1&id=" + data.data.values + "&cat_id=" + assetsCatId + "&dimmId=0&statusVal=0");
                }
            }, 'json');

        } else {
//            projects_data.hide("data_templates");
//            projectDocumentsContentTabbar.tabs("object_viewer").hide();
        }

    });
}
function projectsTreeToolbarStateChange(id, state) {
    switch (id) {
        case 'show_all':

            if (state === false) {
                projectsTreeCell.progressOn();
                projectsTree.deleteChildItems(0);
                projectsTree.loadXML("Controller/php/projectsTree.php?type=" + content_type + "&branch=" + branchId + "&language=" + languageId + "&eid=" + uID, afterCall);

            } else {
                projectsTreeCell.progressOn();
                projectsTree.deleteChildItems(0);
                projectsTree.loadXML("Controller/php/projectsTree.php?action=9&type=" + content_type + "&eid=" + uID + "&branch=" + branchId + "&language=" + languageId, afterCall);
            }

            break;

    }
    return true;
}

function projectsTreeToolbarClick(id) {
    document.onkeypress = enterPressed;

    switch (id)
    {
        case 'up':
            var treeSel = projectsTree.getSelectedItemId();
            var postVars = {
                "selId": treeSel,
                "type": "up"
            }
            $.post("Controller/php/projectsTree.php?action=10", postVars, function (data)
            {
                if (data.bool == false) {
                    dhtmlx.alert("Item cant be moved further!");
                } else {
                    projectsTree.moveItem(treeSel, 'up', data.selId, projectsTree);
                    projectsTree.openItem(treeSel);
                    projectsTree.selectItem(treeSel);
                    if (data.parent == 0) {
                        projectsTree.deleteChildItems(0);
                        projectsTree.loadXML("Controller/php/projectsTree.php?type=" + content_type + "&branch=" + branchId + "&language=" + languageId + "&eid=" + uID, function ()
                        {

                            projectsTree.openItem(treeSel);
                            projectsTree.selectItem(treeSel);
                        });
                    }
                }
            }, 'json');
            break;
        case 'down':
            var treeSel = projectsTree.getSelectedItemId();
            var postVars = {
                "selId": treeSel,
                "type": "down"
            }
            $.post("Controller/php/projectsTree.php?action=10", postVars, function (data)
            {
                if (data.bool == false) {
                    dhtmlx.alert("Item cant be moved further!")
                } else {
                    projectsTree.moveItem(treeSel, 'down', data.selId, projectsTree);
                    projectsTree.openItem(treeSel);
                    projectsTree.selectItem(treeSel);
                    if (data.parent === 0) {
                        projectsTree.deleteChildItems(0);
                        projectsTree.loadXML("Controller/php/projectsTree.php?type=" + content_type + "&branch=" + branchId + "&language=" + languageId + "&eid=" + uID, function ()
                        {
                            projectsTree.openItem(treeSel);
                            projectsTree.selectItem(treeSel);
                        });
                    }
                }
            }, 'json');

            break;

        case 'refresh':

            projectsTreeCell.progressOn();
            projectsTree.deleteChildItems(0);
            projectsTree.loadXML("Controller/php/projectsTree.php?type=" + content_type + "&branch=" + branchId + "&language=" + languageId + "&eid=" + uID, afterCall);
            break;

        case 'restore':

            var windows = new dhtmlXWindows();
            var window_4 = windows.createWindow('window_4', 0, 0, 500, 500);
            window_4.setText('Archived Projects');
            window_4.setModal(1);
            window_4.centerOnScreen();
            window_4.button('park').hide();
            window_4.button('minmax').hide();

            var archivesToolbar = window_4.attachToolbar();
            archivesToolbar.setIconsPath("View/images/");
            archivesToolbar.addButton("restore", 1, "Restore", "");
            archivesToolbar.addSeparator("sep1", 2);

            archivesToolbar.attachEvent("onClick", function (id)
            {
                var checked = archivesGrid.getCheckedRows(0);

                $.post('Controller/php/projectsTree.php?action=24', {id: checked}, function (data) {
                    if (data.data.response) {
                        window_4.close();
                        dhtmlx.message({type: "Success", text: data.data.text});
                        projectsTreeCell.progressOn();
                        projectsTree.deleteChildItems(0);
                        projectsTree.loadXML("Controller/php/projectsTree.php?type=" + content_type + "&branch=" + branchId + "&language=" + languageId + "&eid=" + uID, function () {
                            projectsTreeCell.progressOff();
                        });
                    } else {
                        window_4.close();
                        dhtmlx.alert({title: 'Error', text: data.data.text});
                    }

                }, 'json');
            });

            var archivesGrid = window_4.attachGrid();
            archivesGrid.setImagesPath('dhtmlxSuite4/skins/web/imgs/');
            archivesGrid.setSkin('dhx_web');
            archivesGrid.setHeader(["", "Project ID", "Description"]);
            archivesGrid.attachHeader(",#text_filter,#text_filter");
            archivesGrid.setColTypes("ch,ro,ro");
            archivesGrid.setColumnIds("sort,id,description");
            archivesGrid.setColAlign('left,left,left');
            archivesGrid.setColSorting('int,str,str');
            archivesGrid.setInitWidthsP('10,*,*');
            archivesGrid.init();
            archivesGrid.load("Controller/php/projectsTree.php?action=23");
            break;

        case 'search_pop':

            var searchFormData =
                    [{type: "settings", position: "label-left", labelWidth: myWidth * 0.07, inputWidth: myWidth * 0.2, offsetTop: 10, offsetLeft: 10},
                        //  {type: "hidden", label: "Template Details ", className: "formbox", width: myWidth * 0.2, list:
                        //             [
                        {type: "combo", name: "search_property", inputWidth: 150, options: [
//                                {text: "-- Select Item --", value: "0"},
                                {text: "Project ID", value: "1", selected: true},
                                {text: "Project Name", value: "2"},
                                {text: "Document ID", value: "3"},
                                {text: "Document Name", value: "4"},
                                {text: "File name", value: "5"},
                                {text: "File ID", value: "6"},
                            ]},
                        {type: "newcolumn"},
                        {type: "input", name: "item_value"},
                        {type: "newcolumn"},
                        {type: "button", name: "submit", value: "Search", width: "62", height: "10", className: ""}
                        //             ]}
                    ];
            var popupMainWindow = new dhtmlXWindows();
            var searchWindow = popupMainWindow.createWindow("searchWindow", 0, 0, 800, 350);
            searchWindow.center();
            searchWindow.setText(" Search ");
            //add Layout
            var searchLayout = searchWindow.attachLayout("2E", "dhx_skyblue");
            searchLayout.cells("a").hideHeader();
            searchLayout.cells("a").setHeight(myHeight * 0.05);
            searchLayout.cells("b").hideHeader();
            //add form
            searchForm = searchLayout.cells("a").attachForm(searchFormData);

            searchForm.attachEvent("onInputChange", function (name, value, form) {
                if (name == 'item_value') {
                    var item_id = searchForm.getItemValue('search_property');
                    if (item_id > 0) {
                        switch (item_id) {
                            case '1':
//                                var itemId = y(value);
//                                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=15&value=" + itemId);
                                break;

                            case '2':
                                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=11&value=" + value);
                                break;

                            case '3':
                                searchedDocId = value;
                                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=16&value=" + value);
                                break;

                            case '4':
                                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=17&value=" + value);
                                break;

                            case '5':
                                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=18&value=" + value);
                                break;
                            case '6':
                                searchedFileId = value;
                                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=21&value=" + value);
                                break;

                        }
                    } else {
                        dhtmlx.alert({title: 'Warning', text: 'No Item Selected'});
                    }
                }
            });

            searchForm.attachEvent("onButtonClick", function () {

                var item_id = searchForm.getItemValue('search_property');
                if (item_id > 0) {

                    var value = searchForm.getItemValue('item_value');
                    switch (item_id) {
                        case '1':
                            var itemId = y(value);
                            searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=15&value=" + itemId);
                            break;

                        case '2':
                            searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=11&value=" + value);
                            break;

                        case '3':
                            searchedDocId = value;
                            searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=16&value=" + value);
                            break;

                        case '4':
                            searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=17&value=" + value);
                            break;

                        case '5':
                            searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=18&value=" + value);
                            break;
                        case '6':
                            searchedFileId = value;
                            searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=21&value=" + value);
                            break;

                    }
                } else {
                    dhtmlx.alert({title: 'Warning', text: 'No Item Selected'});
                }


            });

            searchGrid = searchLayout.cells("b").attachGrid();
            searchGrid.setIconsPath('./codebase/imgs/');
            searchGrid.setHeader("ID,Name,Path");
            searchGrid.setColTypes("ro,ro,ro");
            searchGrid.setColumnIds("id,project_name,path");
            searchGrid.setInitWidthsP("10,25,*");
            searchGrid.setSkin('dhx_web');
            searchGrid.init();
            searchGrid.attachEvent("onRowSelect", function (id) {

                if (searchedDocId !== null) {
                    selected_doc_id = searchedDocId;
                    $.getJSON('Controller/php/data_toc.php?action=29&document_id=' + searchedDocId, function (results) {
                        if (results.response) {
                            branchId = results.id;
                        } else {
                            branchId = 0;
                        }

                        mainLayoutToolbar.setListOptionSelected('branch', branchId);
                        projectsTree.selectItem(id);
                        searchedDocId = null;
                        searchWindow.hide();
                    });
                } else if (searchedFileId !== null) {
                    $.getJSON('Controller/php/data_toc.php?action=30&file_id=' + searchedFileId, function (results) {
                        if (results.response) {
                            branchId = results.id;
                        } else {
                            branchId = 0;
                        }

                        mainLayoutToolbar.setListOptionSelected('branch', branchId);
                        projectsTree.selectItem(id);
                        searchedFileId = null;
                        searchWindow.hide();
                    });
                } else {
                    projectsTree.selectItem(id);
                    searchWindow.hide();
                }

            });
            break;

        case 'search1':

            var value = treeToolbar.getValue("search_input");

            var itemId = y(value);
            projectsTree.selectItem(itemId);


            break;
        case 'search_name':

            var searchFormData =
                    [{type: "settings", position: "label-left", labelWidth: myWidth * 0.07, inputWidth: myWidth * 0.1, offsetTop: 10, offsetLeft: 10},
                        //  {type: "hidden", label: "Template Details ", className: "formbox", width: myWidth * 0.2, list:
                        //             [
                        {type: "input", label: "Search Text:", name: "search_input", value: ""},
                                //             ]}
                    ];
            var popupMainWindow = new dhtmlXWindows();
            var searchWindow = popupMainWindow.createWindow("searchWindow", 0, 0, 400, 500);
            searchWindow.center();
            searchWindow.setText("Enter Search Text");
            //add Layout
            var searchLayout = searchWindow.attachLayout("2E", "dhx_skyblue");
            searchLayout.cells("a").hideHeader();
            searchLayout.cells("a").setHeight(myHeight * 0.05);
            searchLayout.cells("b").hideHeader();
            //add form
            searchForm = searchLayout.cells("a").attachForm(searchFormData);

            searchForm.attachEvent("onInputChange", function (name, value, form) {
                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=11&value=" + value);
            });

            searchGrid = searchLayout.cells("b").attachGrid();
            searchGrid.setIconsPath('./codebase/imgs/');
            searchGrid.setHeader("ID,Name");
            searchGrid.setColTypes("ro,ro");
            searchGrid.setColumnIds("id,project_name");
            searchGrid.setInitWidthsP("20,*");
            searchGrid.setSkin('dhx_web');
            searchGrid.init();
            searchGrid.attachEvent("onRowSelect", function (id) {

                projectsTree.selectItem(id);
                searchWindow.hide();

            });
            break;

        case 'search_id':
            var searchFormData =
                    [{type: "settings", position: "label-left", labelWidth: myWidth * 0.07, inputWidth: myWidth * 0.1, offsetTop: 10, offsetLeft: 10},
                        {type: "hidden", label: "Template Details ", className: "formbox", width: myWidth * 0.2, list:
                                    [
                                        {type: "input", label: "Search ID:", name: "search_input", value: ""},
                                        {type: "button", name: "submit", value: "submit", offsetLeft: 150}
                                    ]}
                    ];

            var popupMainWindow = new dhtmlXWindows();
            var searchWindow = popupMainWindow.createWindow("searchWindow", 0, 0, 400, 150);

            searchWindow.center();
            searchWindow.setText("Enter Project ID to Search");

            searchForm = searchWindow.attachForm(searchFormData);

            searchForm.attachEvent("onButtonClick", function () {
                var value = searchForm.getItemValue("search_input");

                var itemId = y(value);
                projectsTree.selectItem(itemId);
                searchWindow.hide();
            });
            break;

        case 'search_doc':
            var searchFormData =
                    [{type: "settings", position: "label-left", labelWidth: myWidth * 0.07, inputWidth: myWidth * 0.1, offsetTop: 10, offsetLeft: 10},
                        {type: "hidden", label: "Template Details ", className: "formbox", width: myWidth * 0.2, list:
                                    [
                                        {type: "input", label: "Document ID:", name: "search_doc_input", value: "", required: true},
                                        {type: "button", name: "submit", value: "submit", offsetLeft: 150}
                                    ]}
                    ];

            var popupMainWindow = new dhtmlXWindows();
            var searchWindow = popupMainWindow.createWindow("searchWindow", 0, 0, 400, 150);

            searchWindow.center();
            searchWindow.setText("Enter Document ID to Search");

            var searchDocForm = searchWindow.attachForm(searchFormData);

            searchDocForm.attachEvent("onButtonClick", function () {
                var value = searchDocForm.getItemValue("search_doc_input");
                searchDocForm.send("Controller/php/projectDocuments.php?action=16", function (loader, response) {
                    var parsedJSON = eval('(' + response + ')');
                    if (parsedJSON.data.response) {
                        searchWindow.hide();
                        selected_doc_id = value;
                        projectsTree.selectItem(parsedJSON.data.item_id, false, true);
                    } else {
                        dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                        searchWindow.hide();
                    }
                });

            });
            break;

        default:
            projectsTreeCell.progressOn();
            projectsTree.deleteChildItems(0);
            projectsTree.loadXML("Controller/php/projectsTree.php?type=" + id + "&branch=" + branchId + "&language=" + languageId + "&eid=" + uID, afterCall);
            break;
    }
}

function enterPressed(evn) {
    if (window.event && window.event.keyCode == 13)
    {
        sendLogin()
    } else if (evn && evn.keyCode == 13)
    {
        sendLogin()
    }
}

function sendLogin() {
    var dhxForm = searchForm.getForm();

    var item_id = dhxForm.getItemValue('search_property');
    if (item_id > 0) {

        var value = dhxForm.getItemValue('item_value');
        switch (item_id) {
            case '1':
                var itemId = y(value);
                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=15&value=" + itemId);
                break;

            case '2':
                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=11&value=" + value);
                break;

            case '3':
                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=16&value=" + value);
                break;

            case '4':
                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=17&value=" + value);
                break;

            case '5':
                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=18&value=" + value);
                break;
            case '6':
                searchGrid.clearAndLoad("Controller/php/projectsTree.php?action=21&value=" + value);
                break;

        }
    }
}

function projectsTreeContextMenuClicked(id) {
    switch (id) {
        case 'add_root':

            if (branchId > 0) {
                var popupMainWindow = new dhtmlXWindows();
                var popupWindow = popupMainWindow.createWindow("add_root_win", 0, 0, 400, 150);
                popupWindow.center();
                popupWindow.setText("Add New Category");

                //add root form
                var addRootPopupForm = popupWindow.attachForm(addCategoryPopupFormdata);
                addRootPopupForm.attachEvent("onButtonClick", function (name) {
                    switch (name)
                    {
                        case 'submit':
                            addRootPopupForm.send("Controller/php/projectsTree.php?action=5&level=0&eid=" + uID + "&branch=" + branchId, function (loader, response) {
                                var parsedJSON = eval('(' + response + ')');
                                if (parsedJSON.data.response) {
                                    popupMainWindow.window('add_root_win').hide();
                                    dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                                    projectsTree.deleteChildItems(0);
                                    projectsTree.loadXML("Controller/php/projectsTree.php?type=" + content_type + "&branch=" + branchId + "&language=" + languageId + "&eid=" + uID, function () {
                                        projectsTree.selectItem(parsedJSON.data.item_id, false, true);
                                    });
                                } else {
                                    dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                                }
                            });
                            break;
                    }
                });
            } else {
                dhtmlx.alert({title: 'Error', text: 'Please Select a Branch!'});
            }
            break;
        case 'add_sub':

            var item_id = projectsTree.contextID;
            if (!(item_id > 0)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select an Item.",
                    title: "Error!"
                });
                return;
            }


            var map_access = projectsTree.getUserData(item_id, "map_access");
            if (item_id === '9856' && !(map_access > 1)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "You Don't have create rights for this map",
                    title: "Error!"
                });
                return;
            }


            if (!(branchId)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "Please Select a Branch!",
                    title: "Error!"
                });
                return;
            }

            var popupMainWindow = new dhtmlXWindows();
            var popupWindow = popupMainWindow.createWindow("add_sub_win", 0, 0, 400, 150);
            popupWindow.center();
            popupWindow.setText("Add New Item");

            //add form
            var addSubPopupForm = popupWindow.attachForm(addItemPopupFormdata);
            addSubPopupForm.attachEvent("onButtonClick", function (name) {
                switch (name)
                {
                    case 'submit':
                        addSubPopupForm.setItemValue("parent", item_id);
                        addSubPopupForm.send("Controller/php/projectsTree.php?action=5&level=1&eid=" + uID + "&branch=" + branchId, function (loader, response) {
                            var parsedJSON = eval('(' + response + ')');
                            if (parsedJSON.data.response) {
                                popupMainWindow.window('add_sub_win').hide();
                                dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                                projectsTree.deleteChildItems(0);
                                projectsTree.loadXML("Controller/php/projectsTree.php?type=" + content_type + "&branch=" + branchId + "&language=" + languageId + "&eid=" + uID, function () {
                                    projectsTree.selectItem(parsedJSON.data.item_id, false, true);
                                });
                            } else {
                                dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                            }
                        });
                        break;
                }
            });

            break;

        case 'rename':

            var item_id = projectsTree.contextID;
            if (!(item_id > 0)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select an Item.",
                    title: "Error!"
                });
                return;
            }


            var map_access = projectsTree.getUserData(item_id, "map_access");
            if (item_id === '9856' && !(map_access > 2)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "You Don't have edit rights for this map",
                    title: "Error!"
                });
                return;
            }

            var current_name = projectsTree.getItemText(item_id);
            var current_name_array = current_name.split("|");
            var current_num = current_name_array[0];

            var popupMainWindow = new dhtmlXWindows();
            var popupWindow = popupMainWindow.createWindow("edit_win", 0, 0, 400, 150);
            popupWindow.center();
            popupWindow.setText("Rename");

            //add form
            var addRootPopupForm = popupWindow.attachForm(renamePopupFormdata);
            addRootPopupForm.load("Controller/php/projectsTree.php?action=6&id=" + item_id);
            addRootPopupForm.attachEvent("onButtonClick", function (name) {
                switch (name)
                {
                    case 'submit':
                        var newValue = addRootPopupForm.getItemValue("item_name");
                        addRootPopupForm.send("Controller/php/projectsTree.php?action=7&id=" + item_id, function (loader, response) {
                            var parsedJSON = eval('(' + response + ')');
                            if (parsedJSON.data.response) {
                                popupMainWindow.window('edit_win').hide();
                                dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                                newValue = current_num + "| " + newValue;
                                projectsTree.setItemText(item_id, newValue, newValue);
                            } else {
                                dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                            }
                        });
                        break;
                }
            });





            break;
        case 'delete':

            //delete selected tree item
            var item_id = projectsTree.getSelectedItemId();
            if (!(item_id > 0)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select an Item.",
                    title: "Error!"
                });
                return;
            }


            var map_access = projectsTree.getUserData(item_id, "map_access");
            if (item_id === '9856' && !(map_access > 3)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "You Don't have delete rights for this map",
                    title: "Error!"
                });
                return;
            }

            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you  want to delete?",
                callback: function (y) {
                    if (y)
                    {
                        window.dhx4.ajax.get("Controller/php/projectsTree.php?action=8&id=" + item_id, function (r) {
                            var t = null;
                            try {
                                eval("t=" + r.xmlDoc.responseText);
                            } catch (e) {
                            }
                            ;
                            if (t != null && t.data.response) {
                                dhtmlx.message({title: 'Success', text: t.data.text});
                                projectsTree.deleteItem(item_id, false);
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

            break;
        case 'set_password':
            //set password
            var passwordFormData =
                    [{type: "settings", position: "label-left", labelWidth: myWidth * 0.07, inputWidth: myWidth * 0.1, offsetTop: 10, offsetLeft: 10},
                        {type: "hidden", label: "Template Details ", className: "formbox", width: myWidth * 0.2, list:
                                    [
                                        {type: "hidden", name: "project", value: ""},
                                        {type: "hidden", name: "user", value: ""},
                                        {type: "input", label: "Password:", name: "password", value: ""},
                                        {type: "input", label: "Code:", name: "code", value: ""},
                                        {type: "button", name: "submit", value: "submit", offsetLeft: 150}

                                    ]}
                    ];
            var itemId = projectsTree.getSelectedItemId();
            var popupMainWindow = new dhtmlXWindows();
            var popupWindow = popupMainWindow.createWindow("w12", 0, 0, myWidth * 0.25, myHeight * 0.2);
            popupWindow.center();
            popupWindow.setText("Set Password");

            //add form
            var passwordForm = popupWindow.attachForm(passwordFormData);
            passwordForm.attachEvent("onButtonClick", function () {
                passwordForm.setItemValue("project", itemId);
                passwordForm.setItemValue("user", getCookie('userlggd'));
                passwordForm.send("Controller/php/projectsTree.php?action=12", function (loader, response) {
                    popupWindow.hide();
                    var parsedJSON = eval('(' + response + ')');
                    if (parsedJSON.data.response) {
                        dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                    } else {
                        dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                    }
                });
            });

            break;
        case 'change_password':
            //set password
            var passwordFormData =
                    [{type: "settings", position: "label-left", labelWidth: myWidth * 0.07, inputWidth: myWidth * 0.1, offsetTop: 10, offsetLeft: 10},
                        {type: "hidden", label: "Template Details ", className: "formbox", width: myWidth * 0.2, list:
                                    [
                                        {type: "hidden", name: "project", value: ""},
                                        {type: "hidden", name: "user", value: ""},
                                        {type: "input", label: "Old Password:", name: "old_password", value: ""},
                                        {type: "input", label: "New Password:", name: "new_password", value: ""},
                                        {type: "input", label: "Code:", name: "code", value: ""},
                                        {type: "button", name: "submit", value: "submit", offsetLeft: 150}

                                    ]}
                    ];

            var itemId = projectsTree.getSelectedItemId();
            var popupMainWindow = new dhtmlXWindows();
            var popupWindow = popupMainWindow.createWindow("w12", 0, 0, myWidth * 0.25, myHeight * 0.25);
            popupWindow.center();
            popupWindow.setText("Change Password");

            //add form
            var passwordForm = popupWindow.attachForm(passwordFormData);
            var myPop = new dhtmlXPopup({
                form: passwordForm,
                id: ["new_password"] //attaches the popup
            });
            myPop.attachHTML("Leave new password blank to reset the password");
            passwordForm.attachEvent("onButtonClick", function () {
                passwordForm.setItemValue("project", itemId);
                passwordForm.setItemValue("user", getCookie('userlggd'));
                passwordForm.send("Controller/php/projectsTree.php?action=13", function (loader, response) {
                    var parsedJSON = eval('(' + response + ')');
                    if (parsedJSON.data.response) {
                        popupWindow.hide();
                        dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
                    } else {
                        dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
                    }
                });
            });

            break;

        default:
            var parentId = projectsTreeContextMenu.getParentId(id);
            if (parentId === 'tpl_add_dir') {

                var itemId = projectsTree.getSelectedItemId();
                window.dhx4.ajax.get("Controller/php/projectsTree.php?action=14&id=" + itemId + "&cat_id=" + id, function (r) {
                    var t = null;
                    try {
                        eval("t=" + r.xmlDoc.responseText);
                    } catch (e) {
                    }
                    ;
                    if (t != null && t.data.response) {
                        dhtmlx.message({title: 'Success', text: t.data.text});
                    } else {
                        dhtmlx.alert({title: 'Error', text: t.data.text});
                    }
                });
            }
            break;
    }
}

function projectsTreeItemDrag(sId, tId, id, sObject, tObject) {
    if (firstId) {
//    alert(selectedDragId);
//    selectedDragId = null;
//    return;

        if (selectedDragId !== null) {
            var res = selectedDragId.split(",");

            if (res.length > 1) {

                if (res[0].substring(0, 3) === 'doc') {
                    var moveDocWindowFormData = [
                        {type: "settings", labelWidth: 80, inputWidth: 250, position: "absolute"},
                        {type: "label", name: "form_label_1", label: "Do you want to move or link the documents?", width: 250, labelWidth: 250, labelLeft: 25, labelTop: 5},
                        {type: "button", name: "move", label: "Move", value: "Move", width: "75", inputWidth: 75, inputLeft: 25, inputTop: 50},
                        {type: "button", name: "link", label: "Link", value: "Link", width: "75", inputWidth: 75, inputLeft: 175, inputTop: 50}
                    ];

                    var moveDocWindow = new dhtmlXWindows();
                    var moveDocMainWindow = moveDocWindow.createWindow("moveDocWindow", 0, 0, myWidth * 0.2, myHeight * 0.2);
                    moveDocMainWindow.center();
                    moveDocMainWindow.setText("Confirm");
                    var moveDocWindowForm = moveDocMainWindow.attachForm(moveDocWindowFormData);

                    moveDocWindowForm.attachEvent("onButtonClick", function (name) {
                        moveDocMainWindow.hide();
                        switch (name) {
                            case 'move':

                                dhtmlx.confirm({
                                    title: "Confirm",
                                    type: "confirm-warning",
                                    text: "Are you sure you  want to move the document to this project?",
                                    callback: function (y) {
                                        if (y)
                                        {
//                                    for(var i in res)
                                            for (var i = 0; i < res.length; i++) {
                                                var rowId = projectDocumentsGrid.cells(res[i], 11).getValue();
                                                sId = res[i].substring(4);
                                                window.dhx4.ajax.post("Controller/php/projectDocuments.php?action=21", "sId=" + sId + "&tId=" + tId + "&event=move" + "&id=" + rowId, function (r) {
                                                    var t = null;
                                                    try {
                                                        eval("t=" + r.xmlDoc.responseText);
                                                    } catch (e) {
                                                    }
                                                    ;
                                                    if (t !== null && t.data.response) {
                                                        dhtmlx.message({title: 'Success', text: t.data.text});
                                                        projectDocumentsGrid.clearAndLoad("Controller/php/projectDocuments.php?action=2&id=" + tId + '&branch=' + branchId + '&language=' + languageId);
                                                        projectsTree.selectItem(tId);
                                                    } else {
                                                        dhtmlx.alert({title: 'Error', text: t.data.text});
                                                    }
                                                });
                                            }
                                        } else
                                        {
                                            return false;
                                        }
                                    }
                                });
                                break;
                            case 'link':

                                dhtmlx.confirm({
                                    title: "Confirm",
                                    type: "confirm-warning",
                                    text: "Are you sure you  want to link the document to this project?",
                                    callback: function (y) {
                                        if (y)
                                        {
                                            for (var i = 0; i < res.length; i++) {
                                                sId = res[i].substring(4);
                                                window.dhx4.ajax.post("Controller/php/projectDocuments.php?action=21", "sId=" + sId + "&tId=" + tId + "&event=link", function (r) {
                                                    var t = null;
                                                    try {
                                                        eval("t=" + r.xmlDoc.responseText);
                                                    } catch (e) {
                                                    }
                                                    ;
                                                    if (t !== null && t.data.response) {
                                                        dhtmlx.message({title: 'Success', text: t.data.text});
                                                        projectDocumentsGrid.clearAndLoad("Controller/php/projectDocuments.php?action=2&id=" + tId + '&branch=' + branchId + '&language=' + languageId);
                                                        projectsTree.selectItem(tId);
                                                    } else {
                                                        dhtmlx.alert({title: 'Error', text: t.data.text});
                                                    }
                                                });
                                            }
                                        } else
                                        {
                                            return false;
                                        }
                                    }
                                });
                                break;
                        }

                    });
                } else if (res[0].substring(0, 3) === 'fil') {
                    dhtmlx.confirm({
                        title: "Confirm",
                        type: "confirm-warning",
                        text: "Are you sure you  want to add the file to this project?",
                        callback: function (y) {
                            if (y)
                            {
                                for (var i = 0; i < res.length; i++) {
                                    sId = res[i].substring(4);
                                    window.dhx4.ajax.post("Controller/php/projectDocuments.php?action=22", "sId=" + sId + "&tId=" + tId, function (r) {
                                        var t = null;
                                        try {
                                            eval("t=" + r.xmlDoc.responseText);
                                        } catch (e) {
                                        }
                                        ;
                                        if (t !== null && t.data.response) {
                                            documentFilesGrid.clearAndLoad('Controller/php/projectDocuments.php?action=5&id=' + tId);
                                            projectsTree.selectItem(tId);
                                            dhtmlx.message({title: 'Success', text: t.data.text});
                                        } else {
                                            dhtmlx.alert({title: 'Error', text: t.data.text});
                                        }
                                    });
                                }
                            } else
                            {
                                return false;
                            }
                        }
                    });
                } else {
                    dhtmlx.confirm({
                        title: "Confirm",
                        type: "confirm-warning",
                        text: "Are you sure you  want to move planning to this project?",
                        callback: function (y) {
                            if (y)
                            {
                                for (var i = 0; i < res.length; i++) {
                                    sId = res[i];
                                    window.dhx4.ajax.post("Controller/php/projectsPlanning.php?action=31", "sId=" + sId + "&tId=" + tId, function (r) {
                                        var t = null;
                                        try {
                                            eval("t=" + r.xmlDoc.responseText);
                                        } catch (e) {
                                        }
                                        ;
                                        if (t !== null && t.data.response) {
                                            projectPlanningGrid.clearAndLoad('Controller/php/projectsPlanning.php?action=1&id=' + tId);
                                            projectsTree.selectItem(tId);
                                            dhtmlx.message({title: 'Success', text: t.data.text});
                                        } else {
                                            dhtmlx.alert({title: 'Error', text: t.data.text});
                                        }
                                    });
                                }
                            } else
                            {
                                return false;
                            }
                        }
                    });
                }

            } else {

                if (sId.substring(0, 3) === 'doc') {
                    var moveDocWindowFormData = [
                        {type: "settings", labelWidth: 80, inputWidth: 250, position: "absolute"},
                        {type: "label", name: "form_label_1", label: "Do you want to move or link the document?", width: 250, labelWidth: 250, labelLeft: 25, labelTop: 5},
                        {type: "button", name: "move", label: "Move", value: "Move", width: "75", inputWidth: 75, inputLeft: 25, inputTop: 50},
                        {type: "button", name: "link", label: "Link", value: "Link", width: "75", inputWidth: 75, inputLeft: 175, inputTop: 50}
                    ];

                    var moveDocWindow = new dhtmlXWindows();
                    var moveDocMainWindow = moveDocWindow.createWindow("moveDocWindow", 0, 0, myWidth * 0.2, myHeight * 0.2);
                    moveDocMainWindow.center();
                    moveDocMainWindow.setText("Confirm");
                    var moveDocWindowForm = moveDocMainWindow.attachForm(moveDocWindowFormData);

                    moveDocWindowForm.attachEvent("onButtonClick", function (name) {
                        moveDocMainWindow.hide();
                        switch (name) {
                            case 'move':

                                dhtmlx.confirm({
                                    title: "Confirm",
                                    type: "confirm-warning",
                                    text: "Are you sure you  want to move the document to this project?",
                                    callback: function (y) {
                                        if (y)
                                        {
                                            var rowId = projectDocumentsGrid.cells(sId, 11).getValue();
                                            sId = sId.substring(4);
                                            window.dhx4.ajax.post("Controller/php/projectDocuments.php?action=21", "sId=" + sId + "&tId=" + tId + "&event=move" + "&id=" + rowId, function (r) {
                                                var t = null;
                                                try {
                                                    eval("t=" + r.xmlDoc.responseText);
                                                } catch (e) {
                                                }
                                                ;
                                                if (t !== null && t.data.response) {
                                                    dhtmlx.message({title: 'Success', text: t.data.text});
                                                    projectDocumentsGrid.clearAndLoad("Controller/php/projectDocuments.php?action=2&id=" + tId + '&branch=' + branchId + '&language=' + languageId);
                                                    projectsTree.selectItem(tId);
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
                                break;
                            case 'link':

                                dhtmlx.confirm({
                                    title: "Confirm",
                                    type: "confirm-warning",
                                    text: "Are you sure you  want to link the document to this project?",
                                    callback: function (y) {
                                        if (y)
                                        {
                                            sId = sId.substring(4);
                                            window.dhx4.ajax.post("Controller/php/projectDocuments.php?action=21", "sId=" + sId + "&tId=" + tId + "&event=link", function (r) {
                                                var t = null;
                                                try {
                                                    eval("t=" + r.xmlDoc.responseText);
                                                } catch (e) {
                                                }
                                                ;
                                                if (t !== null && t.data.response) {
                                                    dhtmlx.message({title: 'Success', text: t.data.text});
                                                    projectDocumentsGrid.clearAndLoad("Controller/php/projectDocuments.php?action=2&id=" + tId + '&branch=' + branchId + '&language=' + languageId);
                                                    projectsTree.selectItem(tId);
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
                                break;
                        }

                    });
                } else if (sId.substring(0, 3) === 'fil') {
                    dhtmlx.confirm({
                        title: "Confirm",
                        type: "confirm-warning",
                        text: "Are you sure you  want to add the file to this project?",
                        callback: function (y) {
                            if (y)
                            {
                                sId = sId.substring(4);
                                window.dhx4.ajax.post("Controller/php/projectDocuments.php?action=22", "sId=" + sId + "&tId=" + tId, function (r) {
                                    var t = null;
                                    try {
                                        eval("t=" + r.xmlDoc.responseText);
                                    } catch (e) {
                                    }
                                    ;
                                    if (t !== null && t.data.response) {
                                        documentFilesGrid.clearAndLoad('Controller/php/projectDocuments.php?action=5&id=' + tId);
                                        projectsTree.selectItem(tId);
                                        dhtmlx.message({title: 'Success', text: t.data.text});
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
                } else {
                    dhtmlx.confirm({
                        title: "Confirm",
                        type: "confirm-warning",
                        text: "Are you sure you  want to move planning to this project?",
                        callback: function (y) {
                            if (y)
                            {
                                window.dhx4.ajax.post("Controller/php/projectsPlanning.php?action=31", "sId=" + sId + "&tId=" + tId, function (r) {
                                    var t = null;
                                    try {
                                        eval("t=" + r.xmlDoc.responseText);
                                    } catch (e) {
                                    }
                                    ;
                                    if (t !== null && t.data.response) {
                                        projectPlanningGrid.clearAndLoad('Controller/php/projectsPlanning.php?action=1&id=' + tId);
                                        projectsTree.selectItem(tId);
                                        dhtmlx.message({title: 'Success', text: t.data.text});
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

//        selectedDragId = null;
        } else {
            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you  want to move this project?",
                callback: function (y) {
                    if (y)
                    {
                        window.dhx4.ajax.post("Controller/php/projectsTree.php?action=1", "sId=" + sId + "&tId=" + tId + "&id=" + tId, function (r) {
                            var t = null;
                            try {
                                eval("t=" + r.xmlDoc.responseText);
                            } catch (e) {
                            }
                            ;
                            if (t !== null && t.data.response) {
                                projectsTreeCell.progressOn();
                                projectsTree.deleteChildItems(0);
                                projectsTree.loadXML("Controller/php/projectsTree.php?type=" + content_type + "&branch=" + branchId + "&language=" + languageId + "&eid=" + uID, function ()
                                {
                                    projectsTreeCell.progressOff();
                                    projectsTree.openItem(t.data.id);
                                    projectsTree.selectItem(t.data.id);
                                });
                                dhtmlx.message({title: 'Success', text: t.data.text});
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

        firstId = false;
    } else {
        return false;
    }
}