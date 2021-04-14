<!-- Place inside the <head> of your HTML -->
<?php
include ('../../../includes.php');

JSPackage::TINYMCE();
JSPackage::JQUERY();
?>

<script type="text/javascript">
    tinymce.init({
        selector: "textarea",
        plugins: [
            "save advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste emoticons textcolor colorpicker textpattern autosave"
        ],
        toolbar1: "save | insertfile undo redo | styleselect | fontselect |  bold italic underline strikethrough | localautosave",
        toolbar2: "alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | forecolor backcolor emoticons",
        image_advtab: true,
        save_enablewhendirty: true,
        paste_data_images: true,
        autosave_ask_before_unload: true,
        media_live_embeds: true,

        save_onsavecallback: function () {
//            var doc_id = parent.projectDocumentsGrid.getSelectedRowId();
//            if (doc_id === "" || typeof doc_id !== 'undefined' || doc_id === null) {
//                parent.dhtmlx.alert("Please select a Documnet!");
//            } else {
            parent.document_content.progressOn();
            var postData = {"notes": tinyMCE.activeEditor.getContent(), "id": parent.projectDocumentsGrid.getSelectedRowId().substring(4), "eid": parent.uID};
            $.post("../../Controller/php/projectDocuments.php?action=4", postData, function (data) {
                parent.document_content.progressOff();
                parent.dhtmlx.message(data.message);
                parent.projectDocumentsHistoryGrid.clearAndLoad("Controller/php/projectDocuments.php?action=13&id=" + parent.projectDocumentsGrid.getSelectedRowId().substring(4));
            }, 'json');
        }
//        }
    });


</script>

<form method="post" action="somepage">
    <textarea name="content" id="content"  style="width:100%;height:<?= $_POST['height'] ?>"></textarea>
</form>