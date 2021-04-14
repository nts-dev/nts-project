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
        save_onsavecallback: function () {
            var postData = {"procedure": tinyMCE.activeEditor.getContent(), "id": parent.devFormSelctdId};
            $.post("http://" + location.host + "/network/Controller/php/data_devices.php?action=71", postData, function (data) {
                parent.dhtmlx.message(data.message);
            }, 'json');
            //console.log("Save");
        }
    });
</script>

<form method="post" action="somepage">
    <textarea name="content" style="width:100%;height:<?= $_POST['height'] ?>"></textarea>
</form>

