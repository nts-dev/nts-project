<!-- Place inside the <head> of your HTML -->
<script type="text/javascript" src="../tinymce4.4/tinymce.min.js"></script>
<!--  Jquery -->
<script src="../js/jquery.min.js"></script>
<script src="../js/jquery-ui.min.js"></script>

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
            var project_id = parent.projectId;

            if (project_id === null) {
                parent.dhtmlx.alert({
                    type: "alert-error",
                    text: "First select an Project.",
                    title: "Error!"
                });
                return;
            }

            parent.projectDetailsFormCommentsCell.progressOn();
            var postData = {"notes": tinyMCE.activeEditor.getContent(), "id": project_id, "eid": parent.uID};
            $.post("../../Controller/php/projectsTree.php?action=35", postData, function (data) {
                parent.projectDetailsFormCommentsCell.progressOff();
                parent.dhtmlx.message(data.message);
//                parent.projectDocumentsHistoryGrid.clearAndLoad("Controller/php/projectDocuments.php?action=13&id=" + parent.projectDocumentsGrid.getSelectedRowId());
            }, 'json');


        }
    });


</script>

<form method="post" action="somepage">
    <textarea name="content" id="content"  style="width:100%;height:<?= $_POST['height'] ?>"></textarea>
</form>