<!-- Place inside the <head> of your HTML -->
<script type="text/javascript" src="../tinymce4/tinymce.min.js"></script>
<!--  Jquery -->
<script src="../js/jquery.min.js"></script>
<script src="../js/jquery-ui.min.js"></script>

<script type="text/javascript">
    tinymce.init({
        selector: "textarea",
        plugins: [
            "save advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste emoticons textcolor colorpicker textpattern"
        ],
        toolbar1: "save | undo redo |  bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent |  code | formatselect",
        menubar: false,
        statusbar: false,
        image_advtab: true,
        save_enablewhendirty: true,
        paste_data_images: true,
        save_onsavecallback: function () {
            var postData = {"notes": tinyMCE.activeEditor.getContent(), "id": parent.objectTranslationsGrid.getSelectedRowId()};
            $.post("../../Controller/php/object_translations.php?action=4", postData, function (data) {
                parent.dhtmlx.message(data.message);
            }, 'json');
        }
    });
</script>

<form method="post" action="somepage">
    <textarea name="content" style="width:100%;height:<?= $_POST['height'] ?>"></textarea>
</form>