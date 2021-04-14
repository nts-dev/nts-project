function projectDocumentsToolbarClicked(id) {
    switch (id) {
        case "create":
            var project_id = projectsTree.getSelectedItemId();

            if (!(project_id > 0)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select an project.",
                    title: "Error!"
                });
                return;
            }


            var map_access = projectsTree.getUserData(project_id, "doc_access");
            if (project_id === '9856' && !(map_access > 1)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "You Don't have document create rights for this map",
                    title: "Error!"
                });
                return;
            }

            window.dhx4.ajax.post("Controller/php/projectDocuments.php?action=7", "id=" + project_id + "&eid=" + uID + "&branch=" + branchId + "&language=" + languageId, function (r) {
                var t = null;
                try {
                    eval("t=" + r.xmlDoc.responseText);
                } catch (e) {
                }
                ;
                if (t !== null && t.data.response) {
                    dhtmlx.message({title: 'Success', text: t.data.text});
                    projectDocumentsGrid.updateFromXML("Controller/php/projectDocuments.php?action=2&id=" + project_id, true, true, function () {
                        projectDocumentsGrid.selectRowById(t.data.newId);
                    });
                } else {
                    dhtmlx.alert({title: 'Error', text: t.data.text});
                }
            });

            break;

        case "delete":

            var project_id = projectsTree.getSelectedItemId();

            if (!(project_id > 0)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select an project.",
                    title: "Error!"
                });
                return;
            }

            var map_access = projectsTree.getUserData(project_id, "doc_access");
            if (project_id === '9856' && !(map_access > 3)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "You Don't have delete rights for this document",
                    title: "Error!"
                });
                return;
            }

            var row_id = projectDocumentsGrid.getSelectedRowId();
            if (row_id === null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select a document.",
                    title: "Error!"
                });
                return;
            }

            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you to delete this Document(s)?",
                callback: function (ok) {
                    if (ok) {
                        $.post("Controller/php/projectDocuments.php?action=8", {
                            id: row_id,
                            project_id: project_id
                        }, function (data) {
                            if (data.data.response) {
                                projectDocumentsGrid.deleteRow(projectDocumentsGrid.getSelectedRowId());
                                projectDocumentsContentIframe.contentWindow.tinymce.activeEditor.setContent("");
                                dhtmlx.message({title: 'Success', text: data.data.text});
                            } else
                                dhtmlx.alert({title: 'Error', text: data.data.text});
                        }, 'json');
                    } else {
                        return false;
                    }
                }
            });

            break;

        case "reload":

            if (projectId > 0) {
                projectDocumentsListCell.progressOn();
                projectDocumentsGrid.clearAndLoad("Controller/php/projectDocuments.php?action=2&id=" + projectId + '&branch=' + branchId + '&language=' + languageId, function () {
                    projectDocumentsListCell.progressOff();
                });
            } else {
                dhtmlx.alert("Please select a project!");
            }

            break;

        case 'publish':

            var row_id = projectDocumentsGrid.getSelectedRowId();
            if (row_id !== null) {
                var doc_id = projectDocumentsGrid.cells(row_id, 7).getValue();
                var lang_id = projectDocumentsGrid.cells(row_id, 6).getValue();
                var report_id = row_id.substring(4);
                var content = projectDocumentsContentIframe.contentWindow.tinymce.activeEditor.getContent();
                $.post("Controller/php/projectDocuments.php?action=28", {
                    id: report_id,
                    doc_id: doc_id,
                    lang_id: lang_id,
                    content: content,
                    eid: uID
                }, function (data) {
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
                [{
                    type: "settings",
                    position: "label-left",
                    labelWidth: resumeFormWidth * 0.08,
                    inputWidth: resumeFormWidth * 0.2,
                    offsetTop: 10,
                    offsetLeft: 10
                },
                    {
                        type: "hidden",
                        label: "Applicants Details ",
                        className: "formbox",
                        width: resumeFormWidth * 0.57,
                        list:
                            [{type: "input", label: "Document No.", name: "document", value: ""},
                                {type: "hidden", name: "report", value: ""},
                                {type: "button", name: "submit", value: "submit", offsetLeft: 100}
                            ]
                    }
                ];
            var row_id = projectDocumentsGrid.getSelectedRowId();
            if (row_id == null) {
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
                    addResumeForm.send("Controller/php/projectDocuments.php?action=9", function (loader, response) {
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

        case 'export_to_pdf':

            var row_id = projectDocumentsGrid.getSelectedRowId().substring(4);
            if (row_id > 0) {
//                var url = "Controller/php/projectDocuments.php?action=10&id=" + row_id;
                var url = "Controller/php/test_pdf.php?id=" + row_id;
                window.open(url);
            } else {
                dhtmlx.alert("Please Select a Report!");
            }
            break;

        case 'cover_page':

//            var projectId = projectsTree.getSelectedItemId();
            if (projectId > 0) {
                var documentId = projectDocumentsGrid.getSelectedRowId();
                if (documentId) {
                    var documentId = projectDocumentsGrid.getSelectedRowId().substring(4);
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

        case 'show_all':

//            var projectId = projectsTree.getSelectedItemId();

            if (projectId > 0) {
//                projectDocumentsListCell.progressOn();
                projectDocumentsGrid.clearAndLoad("Controller/php/projectDocuments.php?action=18&id=" + projectId + '&branch=' + branchId + '&language=' + languageId, function () {
//                    projectDocumentsListCell.progressOff();
                });
            } else {
                dhtmlx.alert("Please select a project!");
            }
            break;

        case 'show_visible':

            if (projectId > 0) {
//                projectDocumentsListCell.progressOn();
                projectDocumentsGrid.clearAndLoad("Controller/php/projectDocuments.php?action=2&id=" + projectId + '&branch=' + branchId + '&language=' + languageId, function () {
//                    projectDocumentsListCell.progressOff();
                });
            } else {
                dhtmlx.alert("Please select a project!");
            }
            break;

        case 'restore_document':

            var windows = new dhtmlXWindows();
            var window_5 = windows.createWindow('window_5', 0, 0, 1300, 500);
            window_5.setText('Restore Document');
            window_5.setModal(1);
            window_5.centerOnScreen();
            window_5.button('park').hide();
            window_5.button('minmax').hide();

            var docArchivesLayout = window_5.attachLayout('1C');

            var docArchivesGidCell = docArchivesLayout.cells('a');
            docArchivesGidCell.hideHeader();

            var docArchivesToolbar = docArchivesGidCell.attachToolbar();
            docArchivesToolbar.setIconsPath("View/images/");
            docArchivesToolbar.addButton("id", 1, "Enter Document ID", "");
            docArchivesToolbar.addSeparator("sep1", 2);
            docArchivesToolbar.addButton("list", 3, "Show Full List", "");
            docArchivesToolbar.addSeparator("sep2", 4);
            docArchivesToolbar.addButton("restore", 5, "Restore", "");

            var passPop = new dhtmlXPopup({
                toolbar: docArchivesToolbar,
                id: "id" //attaches popup to the "id" button
            });
            var passForm = passPop.attachForm([
                {type: "input", label: "Document ID", name: "document_id"},
                {type: "button", name: "proceed", value: "Proceed", offsetLeft: "50"}
            ]);
            passForm.attachEvent("onKeydown", function (inp, ev, name) {//check if key is Enter
                if (ev.keyCode === 13) {
                    var value = passForm.getItemValue("document_id");
                    docArchivesGrid.clearAndLoad("Controller/php/projectDocuments.php?action=30&id=" + value);
                    passForm.clear();
                    passPop.hide();
                }
            });
            passForm.attachEvent("onButtonClick", function (name) {
                var value = passForm.getItemValue("document_id");
                docArchivesGrid.clearAndLoad("Controller/php/projectDocuments.php?action=30&id=" + value);
                passForm.clear();
                passPop.hide();
            });

            docArchivesToolbar.attachEvent("onClick", function (id) {

                switch (id) {

                    case 'list':
                        docArchivesGrid.clearAndLoad("Controller/php/projectDocuments.php?action=30");
                        break;

                    case 'restore':
                        var checked = docArchivesGrid.getCheckedRows(0);
                        $.post('Controller/php/projectDocuments.php?action=31', {id: checked}, function (data) {
                            if (data.data.response) {
                                window_5.close();
                                dhtmlx.message({type: "Success", text: data.data.text});
                            } else {
                                window_5.close();
                                dhtmlx.alert({title: 'Error', text: data.data.text});
                            }

                        }, 'json');
                        break;
                }
            });

            var docArchivesGrid = docArchivesGidCell.attachGrid();
            docArchivesGrid.setImagesPath('dhtmlxSuite4/skins/web/imgs/');
            docArchivesGrid.setSkin('dhx_web');
            docArchivesGrid.setHeader(["", "Project", "Document ID", "Employee", "Date", "Subject", "Category", "Author", "Language"], null, [, "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;"]);
            docArchivesGrid.attachHeader(",#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter", [, "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;", "text-align:left;"]);
            docArchivesGrid.setColAlign("left,left,left,left,left,left,left,left,left");
            docArchivesGrid.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,ro");
            docArchivesGrid.setInitWidthsP("5,16,8,13,13,*,12,12,10");
            docArchivesGrid.setColSorting('int,str,str,str,date,str,str,str,str');
            docArchivesGrid.setDateFormat("%Y-%m-%d %H:%i:%s");
            docArchivesGrid.init();
            docArchivesGrid.load("Controller/php/projectDocuments.php?action=30");

            break;
    }

}

function projectDocumentsGridEditCell(stage, id, index, new_value) {
    var project_id = projectsTree.getSelectedItemId();

    var cell = projectDocumentsGrid.cells(id, index);
    if (stage === 2 && !cell.isCheckbox()) {
        var row_id = projectDocumentsGrid.getSelectedRowId().substring(4);
        if (row_id > 0 || typeof row_id !== 'undefined') {
            var colId = projectDocumentsGrid.getColumnId(index);
            var colType = projectDocumentsGrid.fldSort[index];

            if (colId === 'explorer_id') {
                window.dhx4.ajax.post("Controller/php/projectDocuments.php?action=25", "id=" + row_id + "&index=" + index + "&fieldvalue=" + new_value + "&colId=" + colId + "&colType=" + colType + "&eid=" + uID, function (r) {
                    var t = null;
                    try {
                        eval("t=" + r.xmlDoc.responseText);
                    } catch (e) {
                    }
                    ;
                    if (t !== null && t.data.response) {
                        dhtmlx.message({title: 'Success', text: t.data.text});
                        projectDocumentsGrid.updateFromXML("Controller/php/projectDocuments.php?action=2&id=" + project_id);
                        projectDocumentsContentIframe.contentWindow.tinymce.activeEditor.setContent("");
                        if (t.data.content !== null) {
                            projectDocumentsContentIframe.contentWindow.tinymce.activeEditor.setContent(t.data.content);
                            document_viewer.attachURL("document_viewer_content.php?id=" + row_id);
                            projectDocumentsToolbar.enableItem('publish');
                        }
                    } else {
                        dhtmlx.alert({title: 'Error', text: t.data.text});
                    }
                });
            } else {
                $.post("Controller/php/projectDocuments.php?action=6", {
                    id: row_id,
                    index: index,
                    fieldvalue: new_value,
                    colId: colId,
                    colType: colType
                }, function (data) {
                    if (data.data.response) {
                        dhtmlx.message({title: 'Success', text: data.data.text});
                        projectDocumentsGrid.updateFromXML("Controller/php/projectDocuments.php?action=2&id=" + project_id);

                        if (colId === 'template_id' && new_value > 0) {
                            var templateId = new_value;
                            projectsDataToolbar.clearAll();
                            projectsDataToolbar.loadStruct("Controller/php/projectData.php?action=6&id=" + templateId, function () {
                                var selectedId = projectsDataToolbar.getListOptionSelected("assets");
                                if (selectedId !== null) {
                                    projectDocumentsContentTabbar.tabs("data").show();
                                    projectDocumentsContentTabbar.tabs("object_viewer").show();
                                    projectsDataToolbar.setListOptionSelected("assets", selectedId);
                                    var dataSelectedId = projectsDataToolbar.getListOptionSelected("assets");
                                    var dataSelectedIdParts = dataSelectedId.split("_");
                                    var cat_id = dataSelectedIdParts[0];

                                    showAssets(cat_id);
                                } else {
                                    projectDocumentsContentTabbar.tabs("data").hide();
                                    projectDocumentsContentTabbar.tabs("object_viewer").hide();
                                }

                            });
                        }
                    } else {
                        dhtmlx.alert({title: 'Error', text: data.data.text});
                    }
                }, 'json');
            }
        }
    } else if (stage == 0 && cell.isCheckbox()) {
        return true;
    }
}

function projectDocumentsGridStateChanged(id, ind) {

    var doc_id = projectDocumentsGrid.cells(id, 7).getValue();
    var templateId = projectDocumentsGrid.cells(id, 8).getValue();
    if (doc_id > 0) { //procedure id is set
        projectDocumentsToolbar.enableItem('publish');
    } else {
        projectDocumentsToolbar.disableItem('publish');
    }
    id = id.substring(4);
    var project_id = projectsTree.getSelectedItemId();
    projectDocumentsContentIframe.contentWindow.tinymce.activeEditor.setContent("");

    projectDocumentBranchesGrid.updateFromXML("Controller/php/data_toc.php?action=26&document_id=" + id, true, true);
    projectDocumentsContentCell.progressOn();

    window.dhx4.ajax.get("Controller/php/document_content.php?action=3&id=" + id, function (r) {
        projectDocumentsContentCell.progressOff();
        var t = null;
        try {
            eval("t=" + r.xmlDoc.responseText);
        } catch (e) {
        }
        ;
        if (t !== null && t.content !== null) {
            projectDocumentsContentIframe.contentWindow.tinymce.activeEditor.setContent(t.content);
            document_viewer.attachURL("document_viewer_content.php?id=" + id);
        }
    });

    projectDocumentsHistoryGrid.clearAndLoad("Controller/php/projectDocuments.php?action=13&id=" + id);
}

function documentFilesToolbarClicked(id) {
    switch (id) {
        case 'upload':

            var project_id = projectsTree.getSelectedItemId();

            if (!(project_id > 0)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select an project.",
                    title: "Error!"
                });
                return;
            }

            var map_access = projectsTree.getUserData(project_id, "file_access");

            if (project_id === '9856' && !(map_access > 1)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "You Don't have upload rights for this map",
                    title: "Error!"
                });
                return;
            }

            var uploadBoxformData = [{
                type: "fieldset",
                label: "Uploader",
                list: [{
                    type: "upload",
                    name: "myFiles",
                    inputWidth: 330,
                    url: "Controller/php/projectDocuments.php?action=11&id=" + project_id + "&eid=" + uID + "&language=" + languageId + "&branchId=" + branchId,
                    swfPath: "dhtmlxSuite4/codebase/ext/uploader.swf",
//                          swfUrl: "https://" + location.host + "/script/dhtmlx3.6pro/dhtmlxForm/samples/07_uploader/php/dhtmlxform_item_upload.php"
                }]
            }];
            var picUploadMainWindow = new dhtmlXWindows();
            var picUploadWindow = picUploadMainWindow.createWindow("uploadpic_win", 0, 0, 420, 210);
            picUploadWindow.center();
            picUploadWindow.setText("Upload file(s)");
            //add form
            var uploadpicForm = picUploadWindow.attachForm(uploadBoxformData);
            uploadpicForm.attachEvent("onUploadComplete", function () {
                dhtmlx.message('file uploaded');
                documentFilesGrid.updateFromXML('Controller/php/projectDocuments.php?action=5&id=' + project_id + '&branch=' + branchId + '&language=' + languageId);
                picUploadMainWindow.window('uploadpic_win').hide();
            });
            uploadpicForm.attachEvent("onUploadFail", function (realName) {
                dhtmlx.alert({title: 'Error', text: 'The was an error uploading ' + realName});
            });
            break;

        case 'delete':

            var project_id = projectsTree.getSelectedItemId();

            if (!(project_id > 0)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select an project.",
                    title: "Error!"
                });
                return;
            }

            var map_access = projectsTree.getUserData(project_id, "file_access");

            if (project_id === '9856' && !(map_access > 3)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "You Don't have delete rights for this file",
                    title: "Error!"
                });
                return;
            }

            var file_id = documentFilesGrid.getSelectedRowId();
            if (file_id === null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select a file.",
                    title: "Error!"
                });
                return;
            }

            row_id = file_id.substring(4);
            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you to delete this Option?",
                callback: function (ok) {
                    if (ok) {
                        window.dhx4.ajax.get("Controller/php/projectDocuments.php?action=12&id=" + row_id, function (r) {
                            var t = null;
                            try {
                                eval("t=" + r.xmlDoc.responseText);
                            } catch (e) {
                            }
                            ;
                            if (t !== null && t.data.response) {
                                documentFilesGrid.deleteRow(file_id);
                                documentFilesViewerCell.detachObject(true);
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

        case 'show_all':

            var project_id = projectsTree.getSelectedItemId();
            if (project_id > 0) {
                documentFilesListCell.progressOn();
                documentFilesGrid.clearAndLoad("Controller/php/projectDocuments.php?action=19&id=" + project_id, function () {
                    documentFilesListCell.progressOff();
                });
            } else {
                dhtmlx.alert("Please select a project!");
            }
            break;

        case 'show_visible':

            var project_id = projectsTree.getSelectedItemId();
            if (project_id > 0) {
                documentFilesListCell.progressOn();
                documentFilesGrid.clearAndLoad("Controller/php/projectDocuments.php?action=5&id=" + project_id, function () {
                    documentFilesListCell.progressOff();
                });
            } else {
                dhtmlx.alert("Please select a project!");
            }
            break;

        case 'copy':

            var row_id = documentFilesGrid.getSelectedRowId().substring(4);
            if (row_id > 0) {

                var copyFormData =
                    [{
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
                            list:
                                [{type: "input", label: "Project ID:", name: "proj_id", value: ""},
                                    {type: "button", name: "submit", value: "submit", offsetLeft: 100}
                                ]
                        }
                    ];
                var popupMainWindow = new dhtmlXWindows();
                var popupWindow = popupMainWindow.createWindow("w12", 0, 0, myWidth * 0.25, myHeight * 0.15);
                popupWindow.center();
                popupWindow.setText("Enter Project ID to copy to");
                var copyEventForm = popupWindow.attachForm(copyFormData);
                copyEventForm.attachEvent("onButtonClick", function () {

                    var proj_id = copyEventForm.getItemValue("proj_id");
                    var itemId = y(proj_id);
                    documentFilesListCell.progressOn();
                    window.dhx4.ajax.get("Controller/php/projectDocuments.php?action=23&proj_id=" + itemId + "&file_id=" + row_id, function (r) {
                        documentFilesListCell.progressOff();
                        popupWindow.hide();
                        var t = null;
                        try {
                            eval("t=" + r.xmlDoc.responseText);
                        } catch (e) {
                        }
                        ;
                        if (t != null && t.data.response) {
                            dhtmlx.message({title: 'Success', text: t.data.text});
                        } else
                            dhtmlx.alert({title: 'Error', text: t.data.text});
                    });
                });
            } else {
                dhtmlx.alert('No File Selected');
            }
            break;
    }
}




