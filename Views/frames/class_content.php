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
            var lib_id = parent.classesGrid.getSelectedRowId();
            if (lib_id === null) {
                parent.dhtmlx.alert("No Row Selected!");
            } else {
                parent.classContentCell.progressOn();
                var parent_id = parent.classesGrid.getParentId(lib_id);
                var postData = {"notes": tinyMCE.activeEditor.getContent(), "id": lib_id, "eid": parent.uID, "parent_id": parent_id};
                $.post("../../Controller/php/data_libraries.php?action=6", postData, function (data) {
                    parent.classContentCell.progressOff();
                    parent.dhtmlx.message(data.message);
                    parent.classHistoryGrid.clearAndLoad("Controller/php/data_libraries.php?action=21&id=" + lib_id);
                }, 'json');
            }
        }
    });


</script>

<form method="post" action="somepage">
    <textarea name="content" id="content"  style="width:100%;height:<?= $_POST['height'] ?>"></textarea>
</form>