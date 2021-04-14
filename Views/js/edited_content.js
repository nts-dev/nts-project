projectDetailsTabbar.addTab('edited_documents', 'Edited Documents');
var edited_documents = projectDetailsTabbar.cells('edited_documents');

var editedDocumentsLayout = edited_documents.attachLayout('2E');

var editedDocumentsListCell = editedDocumentsLayout.cells('a');
editedDocumentsListCell.hideHeader();
editedDocumentsListCell.setHeight(projectDetailsCell.getHeight() * 0.3);

var editedDocumentsGrid = editedDocumentsListCell.attachGrid();
editedDocumentsGrid.setImagesPath('dhtmlxSuite4/skins/web/imgs/');
editedDocumentsGrid.setSkin('dhx_web');
editedDocumentsGrid.setHeader(["Document ID", "Subject", "Employee", "Author","Date","Char"]);
editedDocumentsGrid.attachHeader("#numeric_filter,#text_filter,#text_filter,#text_filter,,");
editedDocumentsGrid.setColTypes("ro,ro,ro,ro,ro,ro");
editedDocumentsGrid.setInitWidthsP("14,*,0,15,15,10");
editedDocumentsGrid.setColSorting('str,str,str,str,str,str');
editedDocumentsGrid.enableCellIds(true);
editedDocumentsGrid.setColumnHidden(2,true);
editedDocumentsGrid.attachEvent("onSelectStateChanged", editedDocumentsGridStateChanged); //onRowSelect
editedDocumentsGrid.init();
editedDocumentsGrid.clearAndLoad("Controller/php/projectDocuments.php?action=32");

function editedDocumentsGridStateChanged(id, ind) {

    editedDocumentsContentIframe.contentWindow.tinymce.activeEditor.setContent("");
    window.dhx4.ajax.get("Controller/php/projectDocuments.php?action=33&id=" + id, function (r) {
        var t = null;
        try {
            eval("t=" + r.xmlDoc.responseText);
        } catch (e) {
        }
        ;
        if (t !== null && t.content !== null) {
            editedDocumentsContentIframe.contentWindow.tinymce.activeEditor.setContent(t.content);
        }
    });
}

var editedDocumentsContentIframe;
var editedDocumentsContentCell = editedDocumentsLayout.cells('b');
editedDocumentsContentCell.hideHeader();
editedDocumentsContentCell.attachURL("Views/frames/edited_content.php", false,
        {report_content: '', height: (editedDocumentsContentCell.getHeight()) / 1.26});
editedDocumentsLayout.attachEvent("onContentLoaded", function (id) {
    editedDocumentsContentIframe = editedDocumentsLayout.cells(id).getFrame();
});

if(uID !== '2467'){
    projectDetailsTabbar.tabs("edited_documents").hide();
}


