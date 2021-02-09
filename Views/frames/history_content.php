<!-- Place inside the <head> of your HTML -->
<script type="text/javascript" src="../tinymce4.4/tinymce.min.js"></script>
<!--  Jquery -->
<script src="../js/jquery.min.js"></script>
<script src="../js/jquery-ui.min.js"></script>

<script type="text/javascript">
tinymce.init({
//    menubar: false,
    selector: "textarea",
    plugins: [
        "code fullscreen"
    ],
    image_advtab: true

});
</script>

<form method="post" action="somepage">
    <textarea name="content" style="width:100%;height:<?= $_POST['height'] ?>"></textarea>
</form>