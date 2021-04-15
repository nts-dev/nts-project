/*************************** MAIN QUESTIONS  *****************************/
projectDetailsTabbar.addTab('tab_main_questions', 'Questions');
var tab_main_questions = projectDetailsTabbar.cells('tab_main_questions');
var layout_main_questions = tab_main_questions.attachLayout('2E');

var cell_main_questions = layout_main_questions.cells('a');
cell_main_questions.hideHeader();

var toolbar_question_types = [
    ["10", 'obj', "Essay"],
    ["3", 'obj', "Multichoice"],
//    ['sep01', 'sep', '', ''],
    ["5", 'obj', "Matching"],
//    ['sep02', 'sep', '', ''],
    ["8", 'obj', "Numerical"],
//    ['sep03', 'sep', '', ''],
    ["1", 'obj', "Short Answer"],
//    ['sep04', 'sep', '', ''],
    ["2", 'obj', "True/False"]
];

var toolbar_main_questions = cell_main_questions.attachToolbar();
toolbar_main_questions.setIconsPath('./codebase/imgs/');
toolbar_main_questions.addButtonSelect('new', 1, '<i class="fa fa-plus" aria-hidden="true"></i> New', toolbar_question_types);
toolbar_main_questions.addSeparator('sep1', 2);
toolbar_main_questions.addButton('import', 3, 'Import From Google Doc', '', '');
toolbar_main_questions.addSeparator('sep4', 4);
toolbar_main_questions.addButton('delete', 5, 'Delete', '', '');
toolbar_main_questions.addSeparator('sep5', 6);

toolbar_main_questions.attachEvent('onClick', function (id) {
    switch (id) {

        default:

            if (course_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Course Selected.",
                    title: "Error!"
                });
                return;
            }

            $.post('Controller/php/data_questions.php?action=1', {course_id: course_id, type: id}, function (data) {
                if (data.data.response) {
                    dhtmlx.message({title: 'Success', text: data.data.text});

                    grid_main_questions.updateFromXML('Controller/php/data_questions.php?action=8&course_id=' + course_id, true, true, function () {
                        grid_main_questions.selectRowById(data.data.row_id);
                    });
                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');
            break;

        case "import":

            if (course_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Course Selected.",
                    title: "Error!"
                });
                return;
            }
            openImportQuestionsWindow(course_id);

            break;

        case "delete":

            var row_id = grid_main_questions.getSelectedRowId();

            if (row_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Record Selected.",
                    title: "Error!"
                });
                return;
            }

            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you to delete this Record?",
                callback: function (ok) {
                    if (ok) {
                        $.get("Controller/php/data_questions.php?action=7&id=" + row_id, function (data) {
                            if (data.data.response) {
                                grid_main_questions.deleteRow(row_id);
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
    }
});

function openImportQuestionsWindow(doc_id, page_id = null) {

    var windows = new dhtmlXWindows();
    var window_4 = windows.createWindow('window_4', 400, 100, 600, 300);
    window_4.setText('Upload Document as zip');
    window_4.centerOnScreen();

    var url = "https://" + location.host + "/Google_docs_extract/Controller/upload_questions.php?doc_id=" + doc_id + ((page_id != null) ? "&page_id=" + page_id : "");

    var formData = [
        {
            type: "fieldset",
            label: "Enter document link",
            iconset: "awesome",
            list: [{
                type: "input",
                name: "url",
                inputWidth: 500,
            },
                {
                    type: "button",
                    name: "btn",
                    value: "Import",
                    img: "fa fa-download", imgdis: "fa fa-download"

                }]
        },
        {
            type: "fieldset",
            label: "Uploader",
            list: [{
                type: "upload",
                name: "myFiles",
                inputWidth: 330,
                url: url,
                autoStart: true,
                swfPath: "/plugin/dhtmlx/dhtmlxSuite502/codebase/ext/uploader.swf",
                autoRemove: true
            }]
        }];

    var form_2 = window_4.attachForm(formData);

    form_2.attachEvent("onButtonClick", function (id) {

        var doc_url = form_2.getItemValue('url');
        window_4.close();
        cell_main_questions.progressOn();

        url = url + "&action=2&url=" + doc_url;

        $.get(url, function (data) {

            cell_main_questions.progressOff();
            dhtmlx.message({
                title: 'Success',
                expire: 2000,
                text: "Your document has been extracted successfully extracted"
            });

            if (page_id != null) {
                grid_page_questions.updateFromXML('Controller/php/data_questions.php?action=4&page_id=' + page_id, true, true);
            }

            grid_main_questions.updateFromXML('Controller/php/data_questions.php?action=8&course_id=' + course_id, true, true);
        }, "json");
    });

    form_2.attachEvent("onUploadComplete", function (count) {

        dhtmlx.message({
            title: 'Success',
            expire: 2000,
            text: "Your File has been Uploaded and extracted"
        });

        if (page_id != null) {
            grid_page_questions.updateFromXML('Controller/php/data_questions.php?action=4&page_id=' + page_id, true, true);
        }

        grid_main_questions.updateFromXML('Controller/php/data_questions.php?action=8&course_id=' + course_id, true, true);
        window_4.close();
    });

    form_2.attachEvent("onUploadFail", function (realName) {

        dhtmlx.alert({
            title: 'Error',
            expire: 2000,
            text: "Unsuccessful!"
        });
        window_4.hide();
    });
}

var grid_main_questions = cell_main_questions.attachGrid();
grid_main_questions.setSkin('dhx_web');
grid_main_questions.setImagesPath(DHTMLXPATH + 'skins/web/imgs/');

grid_main_questions.setHeader(["ID", "Title", "Content", "Type"]);
grid_main_questions.setColTypes("ro,ed,ed,combo");

grid_main_questions.setColSorting('int,str,str,str');
grid_main_questions.enableCellIds(true);
grid_main_questions.setColumnIds('id,title,text,type');
grid_main_questions.setInitWidthsP('10,*,*,20');
grid_main_questions.init();
//grid_main_questions.load("Controller/php/data_questions.php?action=8");

var combo_question_types = [
    ["10", "Essay"],
    ["3", "Multichoice"],
    ["5", "Matching"],
    ["8", "Numerical"],
    ["1", "Short Answer"],
    ["2", "True/False"]
];

var questions_type_combo = grid_main_questions.getColumnCombo(3);//takes the column index
questions_type_combo.enableFilteringMode(true);
questions_type_combo.addOption(combo_question_types);

grid_main_questions.attachEvent('onRowSelect', function (id, ind) {
    grid_main_choices.clearAndLoad("Controller/php/data_questions.php?action=9&id=" + id);
});


grid_main_questions.attachEvent('onEditCell', function (stage, id, index, new_value, oValue) {

    var cell = grid_main_questions.cells(id, index);
    var row_id = grid_main_questions.getSelectedRowId();

    var colId = grid_main_questions.getColumnId(index);
    var colType = grid_main_questions.fldSort[index];

    if (stage === 2 && !cell.isCheckbox()) {

        if (row_id > 0 || typeof row_id !== 'undefined') {

            var post_data = {
                id: row_id,
                index: index,
                fieldvalue: new_value,
                colId: colId,
                colType: colType
            };

            $.post("Controller/php/data_questions.php?action=10", post_data, function (data) {
                if (data.data.response) {
                    dhtmlx.message({title: 'Success', text: data.data.text});
                    grid_main_questions.updateFromXML("Controller/php/data_questions.php?action=8&course_id=" + course_id, true, true);
                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');
        }
    } else if (stage === 0 && cell.isCheckbox()) {
        return true;
    }
});


var cell_main_choices = layout_main_questions.cells('b');
cell_main_choices.setText('Question Responses');

var toolbar_main_choices = cell_main_choices.attachToolbar();
toolbar_main_choices.setIconsPath('./codebase/imgs/');

toolbar_main_choices.addButton('new', 1, 'New', '', '');
toolbar_main_choices.addSeparator('sep1', 2);
toolbar_main_choices.addButton('delete', 3, 'Delete', '', '');
toolbar_main_choices.addSeparator('sep3', 4);

toolbar_main_choices.attachEvent('onClick', function (id) {
    switch (id) {

        case "new":

            var question_id = grid_main_questions.getSelectedRowId();

            if (question_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Question Selected.",
                    title: "Error!"
                });
                return;
            }

            $.post('Controller/php/data_questions.php?action=5', {question_id: question_id}, function (data) {
                if (data.data.response) {
                    dhtmlx.message({title: 'Success', text: data.data.text});

                    grid_main_choices.updateFromXML('Controller/php/data_questions.php?action=9&id=' + question_id, true, true, function () {
                        grid_main_choices.selectRowById(data.data.row_id);
                    });

                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');
            break;

        case "delete":

            var row_id = grid_main_choices.getSelectedRowId();

            if (row_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Record Selected.",
                    title: "Error!"
                });
                return;
            }

            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you to delete this Record?",
                callback: function (ok) {
                    if (ok) {
                        $.get("Controller/php/data_questions.php?action=6&id=" + row_id, function (data) {
                            if (data.data.response) {
                                grid_main_choices.deleteRow(row_id);
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
    }
});

var grid_main_choices = cell_main_choices.attachGrid();
grid_main_choices.setSkin('dhx_web');
grid_main_choices.setImagesPath(DHTMLXPATH + 'skins/web/imgs/');

grid_main_choices.setHeader(["#", "Answer", "Response", "Score", "JumpTo"]);
grid_main_choices.setColTypes("cntr,ed,ed,ed,ed");

grid_main_choices.setColSorting('int,str,str,str,str');
grid_main_choices.enableCellIds(true);
grid_main_choices.setColumnIds('cntr,text,response,score,jumpto');
grid_main_choices.setInitWidthsP('10,*,*,10,20');
grid_main_choices.init();

grid_main_choices.attachEvent('onEditCell', function (stage, id, index, new_value, oValue) {

    var cell = grid_main_choices.cells(id, index);
    var row_id = grid_main_choices.getSelectedRowId();

    var colId = grid_main_choices.getColumnId(index);
    var colType = grid_main_choices.fldSort[index];

    if (stage === 2 && !cell.isCheckbox()) {

        if (row_id > 0 || typeof row_id !== 'undefined') {

            var post_data = {
                id: row_id,
                index: index,
                fieldvalue: new_value,
                colId: colId,
                colType: colType
            };

            $.post("Controller/php/data_questions.php?action=10", post_data, function (data) {
                if (data.data.response) {
                    dhtmlx.message({title: 'Success', text: data.data.text});
                    grid_main_choices.updateFromXML("Controller/php/data_questions.php?action=9&id=" + grid_main_questions.getSelectedRowId(), true, true);
                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');
        }
    } else if (stage === 0 && cell.isCheckbox()) {
        return true;
    }
});


/*********************** PAGE QUESTIONS ********************************/

tabbar_3.addTab('page_questions', 'Questions');
var tab_page_questions = tabbar_3.cells('page_questions');
var layout_page_questions = tab_page_questions.attachLayout('2U');

var cell_page_questions = layout_page_questions.cells('a');
cell_page_questions.hideHeader();
cell_page_questions.setWidth('350');

var toolbar_page_questions = cell_page_questions.attachToolbar();
toolbar_page_questions.setIconsPath('./codebase/imgs/');

toolbar_page_questions.addButtonSelect('new', 1, '<i class="fa fa-plus" aria-hidden="true"></i> New', toolbar_question_types);
toolbar_page_questions.addSeparator('sep1', 2);
toolbar_page_questions.addButton('import', 3, 'Import', '', '');
toolbar_page_questions.addSeparator('sep5', 4);
toolbar_page_questions.addButton('link', 5, 'Link', '', '');
toolbar_page_questions.addSeparator('sep2', 6);
toolbar_page_questions.addButton('export', 7, 'Export', '', '');
toolbar_page_questions.addSeparator('sep3', 8);
toolbar_page_questions.addButton('delete', 9, 'Delete', '', '');
toolbar_page_questions.addSeparator('sep4', 10);

toolbar_page_questions.attachEvent('onClick', function (id) {

    switch (id) {
        case "delete":

            var row_id = grid_page_questions.getSelectedRowId();

            if (row_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Record Selected.",
                    title: "Error!"
                });
                return;
            }

            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you to delete this Record?",
                callback: function (ok) {
                    if (ok) {
                        $.get("Controller/php/data_questions.php?action=3&id=" + row_id, function (data) {
                            if (data.data.response) {
                                grid_page_questions.deleteRow(row_id);
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

        case "export":

            var row_id = grid_3.getSelectedRowId();

            var lesson_id = grid_3.cells(grid_3.getParentId(row_id), 3).getValue();
            var page_id = row_id.split("_")[1];

            var post_data = {
                lesson_id: lesson_id,
                page_id: page_id,
                server_id: server_id
            };

            $.post("Controller/php/courses.php?action=8", post_data, function (data) {
                if (data.data.response) {
                    dhtmlx.message({title: 'Success', text: data.data.text});
                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');

            break;

        case "import":
            if (course_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Course Selected.",
                    title: "Error!"
                });
                return;
            }

            var page_id = grid_3.getSelectedRowId();
            if (page_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Page Selected.",
                    title: "Error!"
                });
                return;
            }
            openImportQuestionsWindow(course_id, page_id.split("_")[1]);
            break;

        case "link":

            if (course_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Course Selected.",
                    title: "Error!"
                });
                return;
            }

            var page_id = grid_3.getSelectedRowId();
            if (page_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Page Selected.",
                    title: "Error!"
                });
                return;
            }

            openLinkQuestionsWindow(course_id, page_id.split("_")[1]);

            break;

        default:

            if (course_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Course Selected.",
                    title: "Error!"
                });
                return;
            }

            var page_id = grid_3.getSelectedRowId();
            if (page_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Page Selected.",
                    title: "Error!"
                });
                return;
            }

            $.post('Controller/php/data_questions.php?action=1', {
                course_id: course_id,
                type: id,
                page_id: page_id.split("_")[1]
            }, function (data) {
                if (data.data.response) {
                    dhtmlx.message({title: 'Success', text: data.data.text});

                    grid_page_questions.updateFromXML('Controller/php/data_questions.php?action=4&page_id=' + page_id.split("_")[1], true, true, function () {
                        grid_page_questions.selectRowById(data.data.row_id);
                    });

                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');
            break;
    }
});

function openLinkQuestionsWindow(course_id, page_id) {

    var windows = new dhtmlXWindows();
    var window_4 = windows.createWindow('window_4', 0, 0, 900, 500);
    window_4.setText('Link Questions');
    window_4.centerOnScreen();
    window_4.button('park').hide();
    window_4.button('minmax').hide();

    var layout_main_questions = window_4.attachLayout('1C');

    var cell_main_questions = layout_main_questions.cells('a');
    cell_main_questions.hideHeader();

    var toolbar_page_questions = cell_main_questions.attachToolbar();
    toolbar_page_questions.setIconsPath('./codebase/imgs/');
    toolbar_page_questions.addButton('link', 3, 'Link', '', '');

    toolbar_page_questions.attachEvent('onClick', function (id) {

        if (id === "link") {

            var checked = grid_main_questions.getCheckedRows(0);
            $.post("Controller/php/data_questions.php?action=16", {ids: checked, page_id: page_id}, function (data) {

                dhtmlx.message({title: 'Success', text: data.data.text});
                grid_page_questions.updateFromXML("Controller/php/data_questions.php?action=4&page_id=" + page_id, true, true);
                window_4.close();
            }, 'json');
        }
    });

    var grid_main_questions = cell_main_questions.attachGrid();
    grid_main_questions.setSkin('dhx_web');
    grid_main_questions.setImagesPath(DHTMLXPATH + 'skins/web/imgs/');

    grid_main_questions.setHeader(["S", "ID", "Title", "Content", "Type"]);
    grid_main_questions.setColTypes("ch,ro,ed,ed,combo");

    grid_main_questions.setColSorting('int,int,str,str,str');
    grid_main_questions.enableCellIds(true);
    grid_main_questions.setColumnIds('s,id,title,text,type');
    grid_main_questions.setInitWidthsP('10,10,*,*,20');
    grid_main_questions.init();
    grid_main_questions.load("Controller/php/data_questions.php?action=8&course_id=" + course_id + "&page_id=" + page_id);

}

var grid_page_questions = cell_page_questions.attachGrid();
grid_page_questions.setSkin('dhx_web');
grid_page_questions.setImagesPath(DHTMLXPATH + 'skins/web/imgs/');

grid_page_questions.setHeader(["ID", "Title", "Content", "Type"]);
grid_page_questions.setColTypes("ro,ed,ed,combo");

grid_page_questions.setColSorting('int,str,str,str');
grid_page_questions.enableCellIds(true);
grid_page_questions.setColumnIds('id,title,text,type');
grid_page_questions.setInitWidthsP('*,*,*,*');
grid_page_questions.setColumnHidden(0, true);
grid_page_questions.setColumnHidden(2, true);
grid_page_questions.init();

var page_questions_type_combo = grid_page_questions.getColumnCombo(3);//takes the column index
page_questions_type_combo.enableFilteringMode(true);
page_questions_type_combo.addOption(combo_question_types);


grid_page_questions.attachEvent('onEditCell', function (stage, id, index, new_value, oValue) {

    var cell = grid_page_questions.cells(id, index);
    var row_id = grid_page_questions.cells(id, 0).getValue();

    var colId = grid_page_questions.getColumnId(index);
    var colType = grid_page_questions.fldSort[index];

    if (stage === 2 && !cell.isCheckbox()) {

        var post_data = {
            id: row_id,
            index: index,
            fieldvalue: new_value,
            colId: colId,
            colType: colType
        };

        $.post("Controller/php/data_questions.php?action=2", post_data, function (data) {
            if (data.data.response) {
                dhtmlx.message({title: 'Success', text: data.data.text});

                var page_id = grid_3.getSelectedRowId().split("_")[1];
                grid_page_questions.updateFromXML("Controller/php/data_questions.php?action=4&page_id=" + page_id, true, true);
            } else {
                dhtmlx.alert({title: 'Error', text: data.data.text});
            }
        }, 'json');

    } else if (stage === 0 && cell.isCheckbox()) {
        return true;
    }
});

grid_page_questions.attachEvent('onRowSelect', function (id, ind) {
    var question_id = grid_page_questions.cells(id, 0).getValue();
    form_page_question_details.clear();
    form_page_question_details.load("Controller/php/data_questions.php?action=12&id=" + question_id);
    grid_page_choices.clearAndLoad("Controller/php/data_questions.php?action=9&id=" + question_id);
});


var cell_page_choices = layout_page_questions.cells('b');

var tabbar_page_question_details = cell_page_choices.attachTabbar();
tabbar_page_question_details.addTab('question_details', 'Question Details');
var tab_page_question_details = tabbar_page_question_details.cells('question_details');
tab_page_question_details.setActive();

var toolbar_page_question_details = tab_page_question_details.attachToolbar();
toolbar_page_question_details.setIconsPath("Views/imgs/");
toolbar_page_question_details.addButton('save', 1, 'Save', '', '');

toolbar_page_question_details.attachEvent('onClick', function (id) {

    var row_id = grid_page_questions.getSelectedRowId();
    if (row_id == null) {
        dhtmlx.alert({
            type: "alert-error",
            text: "No Record Selected.",
            title: "Error!"
        });
        return;
    }

    var question_id = grid_page_questions.cells(row_id, 0).getValue();
    form_page_question_details.send("Controller/php/data_questions.php?action=13&id=" + question_id, function (loader, response) {

        var parsedJSON = eval('(' + response + ')');

        if (parsedJSON.data.response) {

            dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
            var page_id = grid_3.getSelectedRowId().split("_")[1];
            grid_page_questions.updateFromXML("Controller/php/data_questions.php?action=4&page_id=" + page_id, true, true);

        } else {
            dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
        }
    });
});

var question_details_formdata = [
    {type: "settings", position: "label-left", labelWidth: 110, inputWidth: 220, offsetTop: 10, offsetLeft: 10},
    {type: "input", name: "title", label: "Title", required: true},
    {type: "input", name: "text", label: "Contents", required: true, rows: 3},
    {type: "combo", name: "type", label: "Type"},
    {type: "checkbox", name: "qoption", label: "Multiple_answer", hidden: "true"}
];

var form_page_question_details = tab_page_question_details.attachForm(question_details_formdata);
var page_question_type_combo = form_page_question_details.getCombo("type");
page_question_type_combo.enableFilteringMode(true);
page_question_type_combo.addOption(combo_question_types);

tabbar_page_question_details.addTab('question_answers', 'Answers');
var tab_page_question_answers = tabbar_page_question_details.cells('question_answers');

var layout_page_question_answers = tab_page_question_answers.attachLayout('2U');

var cell_page_question_answers = layout_page_question_answers.cells('a');
cell_page_question_answers.hideHeader();

var toolbar_page_choices = cell_page_question_answers.attachToolbar();
toolbar_page_choices.setIconsPath('./codebase/imgs/');

toolbar_page_choices.addButton('new', 1, 'New', '', '');
toolbar_page_choices.addSeparator('sep1', 2);
toolbar_page_choices.addButton('delete', 3, 'Delete', '', '');
toolbar_page_choices.addSeparator('sep3', 4);

toolbar_page_choices.attachEvent('onClick', function (id) {
    switch (id) {

        case "new":

            var row_id = grid_page_questions.getSelectedRowId();

            if (row_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Question Selected.",
                    title: "Error!"
                });
                return;
            }

            var question_id = grid_page_questions.cells(row_id, 0).getValue();

            $.post('Controller/php/data_questions.php?action=5', {question_id: question_id}, function (data) {
                if (data.data.response) {
                    dhtmlx.message({title: 'Success', text: data.data.text});

                    grid_page_choices.updateFromXML('Controller/php/data_questions.php?action=9&id=' + question_id, true, true, function () {
                        grid_page_choices.selectRowById(data.data.row_id);
                    });

                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');
            break;

        case "delete":

            var row_id = grid_page_choices.getSelectedRowId();

            if (row_id == null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "No Record Selected.",
                    title: "Error!"
                });
                return;
            }

            dhtmlx.confirm({
                title: "Confirm",
                type: "confirm-warning",
                text: "Are you sure you to delete this Record?",
                callback: function (ok) {
                    if (ok) {
                        $.get("Controller/php/data_questions.php?action=6&id=" + row_id, function (data) {
                            if (data.data.response) {
                                grid_page_choices.deleteRow(row_id);
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
    }
});

var grid_page_choices = cell_page_question_answers.attachGrid();
grid_page_choices.setSkin('dhx_web');
grid_page_choices.setImagesPath(DHTMLXPATH + 'skins/web/imgs/');

grid_page_choices.setHeader(["#", "Answer", "Response", "Score", "JumpTo"]);
grid_page_choices.setColTypes("cntr,ed,ed,ed,ed");

grid_page_choices.setColSorting('int,str,str,str,str');
grid_page_choices.enableCellIds(true);
grid_page_choices.setColumnIds('cntr,text,response,score,jumpto');
grid_page_choices.setInitWidthsP('10,*,*,10,20');
grid_page_choices.init();
grid_page_choices.setColumnHidden(2, true);
grid_page_choices.setColumnHidden(3, true);
grid_page_choices.setColumnHidden(4, true);

grid_page_choices.attachEvent('onEditCell', function (stage, id, index, new_value, oValue) {

    var cell = grid_page_choices.cells(id, index);
    var row_id = grid_page_choices.getSelectedRowId();

    var colId = grid_page_choices.getColumnId(index);
    var colType = grid_page_choices.fldSort[index];

    if (stage === 2 && !cell.isCheckbox()) {

        if (row_id > 0 || typeof row_id !== 'undefined') {

            var post_data = {
                id: row_id,
                index: index,
                fieldvalue: new_value,
                colId: colId,
                colType: colType
            };

            $.post("Controller/php/data_questions.php?action=10", post_data, function (data) {
                if (data.data.response) {
                    dhtmlx.message({title: 'Success', text: data.data.text});
                    grid_page_choices.updateFromXML("Controller/php/data_questions.php?action=9&id=" + grid_page_questions.getSelectedRowId(), true, true);
                } else {
                    dhtmlx.alert({title: 'Error', text: data.data.text});
                }
            }, 'json');
        }
    } else if (stage === 0 && cell.isCheckbox()) {
        return true;
    }
});

grid_page_choices.attachEvent('onCheck', function (rId, cInd, state) {

    var colId = grid_page_choices.getColumnId(cInd);

    $.post("Controller/php/data_questions.php?action=11", {
        colId: colId,
        id: rId,
        nValue: ((state) ? 1 : 0)
    }, function (data) {
        if (data.data.response) {
            dhtmlx.message({title: 'Success', text: data.data.text});
        } else {
            dhtmlx.alert({title: 'Error', text: data.data.text});
        }
    }, 'json');
});

grid_page_choices.attachEvent('onRowSelect', function (id, ind) {
    form_page_answers.clear();
    form_page_answers.load("Controller/php/data_questions.php?action=14&id=" + id);
});


var cell_form_page_answers = layout_page_question_answers.cells('b');
cell_form_page_answers.hideHeader();
var toolbar_page_answer_details = cell_form_page_answers.attachToolbar();
toolbar_page_answer_details.setIconsPath("Views/imgs/");
toolbar_page_answer_details.addButton('save', 1, 'Save', '', '');

toolbar_page_answer_details.attachEvent('onClick', function (id) {

    var row_id = grid_page_choices.getSelectedRowId();
    if (row_id == null) {
        dhtmlx.alert({
            type: "alert-error",
            text: "No Record Selected.",
            title: "Error!"
        });
        return;
    }

    form_page_answers.send("Controller/php/data_questions.php?action=15&id=" + row_id, function (loader, response) {

        var parsedJSON = eval('(' + response + ')');

        if (parsedJSON.data.response) {

            dhtmlx.message({title: 'Success', text: parsedJSON.data.text});
            grid_page_choices.updateFromXML("Controller/php/data_questions.php?action=9&id=" + grid_page_questions.getSelectedRowId(), true, true);

        } else {
            dhtmlx.alert({title: 'Error', text: parsedJSON.data.text});
        }
    });
});

var question_answers_formdata = [
    {type: "settings", position: "label-left", labelWidth: 110, inputWidth: 220, offsetTop: 10, offsetLeft: 10},
    {type: "input", name: "text", label: "Answer", required: true, rows: 3},
    {type: "input", name: "response", label: "Response", required: true},
    {type: "input", name: "score", label: "Score", required: true},
    {type: "combo", name: "jumpto", label: "Jumpto"}
];

var form_page_answers = cell_form_page_answers.attachForm(question_answers_formdata);

var page_answers_jumpto_combo = form_page_answers.getCombo("jumpto");
page_answers_jumpto_combo.enableFilteringMode(true);
page_answers_jumpto_combo.addOption([
    ["0", "This Page"],
    ["-1", "Next Page"]
]);