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

            var rowId = parent.grid_3.getSelectedRowId();

            if (rowId === null) {
                parent.dhtmlx.alert({
                    type: "alert-error",
                    text: "First select a record.",
                    title: "Error!"
                });
                return;
            }


            if (parent.is_moodle) {

                var level = parent.grid_3.getLevel(rowId);

                if (level > 0) {

                    var post_id = null;

                    var domainname = parent.getDomainName();
                    var modname = parent.grid_3.cells(parent.grid_3.getParentId(rowId), 2).getValue();

                    if (modname == 'lesson') {
                        post_id = rowId.split("_")[1];
                        var serverurl = domainname + '/data_content.php?action=1';
                    } else {
                        post_id = parent.grid_3.cells(rowId, 3).getValue();
                        var serverurl = domainname + '/moosh.php?action=4&course=' + parent.course_id;
                    }
//                parent.tocContentCell.progressOn();
//                var domainname = 'http://192.168.1.137';

                    $.post(serverurl, {
                        id: post_id,
                        fieldvalue: tinyMCE.activeEditor.getContent()
                    }, function (data) {
                        if (data.data.response) {
//                        parent.tocContentCell.progressOff();
                            parent.dhtmlx.message({title: 'Success', text: data.data.text});
                        } else {
                            parent.dhtmlx.alert({title: 'Error', text: data.data.text});
                        }
                    }, 'json');
                }

            } else {

                var main_doc_id = parent.grid_1.getSelectedRowId().substring(4);

                parent.tocContentCell.progressOn();
                var postData = {"notes": tinyMCE.activeEditor.getContent(), "id": rowId, "eid": parent.uID, "doc_id": main_doc_id};
                $.post("../../Controller/php/data_toc.php?action=6", postData, function (data) {
                    parent.tocContentCell.progressOff();
                    parent.dhtmlx.message(data.message);
//                parent.projectDocumentsHistoryGrid.clearAndLoad("Controller/php/projectDocuments.php?action=13&id=" + parent.projectDocumentsGrid.getSelectedRowId());
                }, 'json');

            }
        }
    });


</script>

<form method="post" action="somepage">
    <textarea name="content" id="content"  style="width:100%;height:<?= $_POST['height'] ?>"></textarea>
</form>