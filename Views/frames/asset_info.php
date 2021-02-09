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
        save_onsavecallback: function () {
            var device_id = parent.devicesDataGrid.getSelectedRowId();
            if (device_id === null || device_id === "undefined") {
                parent.dhtmlx.alert("No Item selected!");
            } else {
                var postData = {"content": tinyMCE.activeEditor.getContent(), "id": device_id, "templ_id": parent.assetsTemplateId};
                $.post("http://" + location.host + "/network/Controller/php/data_devices.php?action=38", postData, function (data) {
                    if (data.data.response) {
                        parent.dhtmlx.message({type: "Success", text: data.data.text});
                    } else
                        parent.dhtmlx.alert({title: 'Error', text: data.data.text});
                }, 'json');
            }
        }
    });
</script>

<form method="post" action="somepage">
    <textarea name="content" style="width:100%;height:<?= $_POST['height'] ?>"></textarea>
</form>

