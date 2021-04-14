<!-- Place inside the <head> of your HTML -->
<?php
include ('../../../includes.php');

JSPackage::TINYMCE();
JSPackage::JQUERY();
?>

<script type="text/javascript">
    var report_id;

    tinymce.init({
        selector: "textarea",
        plugins: [
            "save advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste imagetools emoticons textcolor colorpicker textpattern"
        ],
        toolbar1: "save | insertfile undo redo | styleselect | fontselect |  bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | forecolor backcolor emoticons ",
        image_advtab: true,
        save_enablewhendirty: true,
        paste_data_images: true,
        autosave_ask_before_unload: true,
        media_live_embeds: true,
        save_onsavecallback: function () {

            var project_id = parent.projectsTree.getSelectedItemId();

            if (!(project_id > 0)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select an project.",
                    title: "Error!"
                });
                return;
            }

            var map_access = parent.projectsTree.getUserData(project_id, "doc_access");
            if (project_id === '9856' && !(map_access > 2)) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "You Don't have delete rights for this document",
                    title: "Error!"
                });
                return;
            }

            var doc_id = parent.projectDocumentsGrid.getSelectedRowId();
            if (doc_id === null) {
                dhtmlx.alert({
                    type: "alert-error",
                    text: "First select a document.",
                    title: "Error!"
                });
                return;
            }

            parent.document_content.progressOn();
            var postData = {"notes": tinyMCE.activeEditor.getContent(), "id": parent.projectDocumentsGrid.getSelectedRowId().substring(4), "eid": parent.uID};
            $.post("../../Controller/php/document_content.php?action=4", postData, function (data) {
                parent.document_content.progressOff();
                parent.dhtmlx.message(data.message);
                report_id = data.report_id;

                parent.projectDocumentsGrid.updateFromXML('Controller/php/projectDocuments.php?action=2&id=' + parent.projectId + '&branch=' + parent.branchId + '&language=' + parent.languageId, true, true);

                setArchive(report_id, parent.uID);

            }, 'json');
        }
    });


    function setArchive(report_id, eid) {

        $.post("../../Controller/php/document_content.php?action=1", {'report_id': report_id, eid: eid}, function () {
            parent.projectDocumentsHistoryGrid.clearAndLoad("Controller/php/projectDocuments.php?action=13&id=" + parent.projectDocumentsGrid.getSelectedRowId().substring(4));
        });
    }
    
    function escapeXml(unsafe) {
        return unsafe.replace(/[<>&'"]/g, function (c) {
            switch (c) {
                case '<': return '&lt;';
                case '>': return '&gt;';
                case '&': return '&amp;';
                case '\'': return '&apos;';
                case '"': return '&quot;';
            }
        });
    }
</script>

<form method="post" action="somepage">
    <textarea name="content" id="content"  style="width:100%;height:<?= $_POST['height'] ?>"></textarea>
</form>