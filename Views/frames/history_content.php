<!-- Place inside the <head> of your HTML -->
<?php
include ('../../../includes.php');

JSPackage::TINYMCE();
JSPackage::JQUERY();
?>

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