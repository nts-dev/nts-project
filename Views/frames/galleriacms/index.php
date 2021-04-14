<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=0.3">
        <link rel="shortcut icon" href="img/log.png"  type="image/x-icon" >
        <title>Tradestar Kenya::Photo Pluging</title>
        <style>

            /* Demo styles */
            html,body{background:#eee;margin:0;}
            .content{color:#777;font:12px/1.4 "helvetica neue",arial,sans-serif;max-width:820px;margin:auto;}
            h1{font-size:12px;font-weight:normal;color:#222;margin:0;}
            p{margin:0 0 20px}
            a {color:#22BCB9;text-decoration:none;}
            .cred{margin-top:20px;font-size:11px;}

            /* This rule is read by Galleria to define the gallery height: */
            .galleria{height:420px;}

        </style>

        <!-- load jQuery -->
        <script src="../../js/jquery.js"></script>

        <!-- load Galleria -->
        <script src="galleria/galleria-1.4.2.min.js"></script>

    </head>
    <body>

        <div class="content">

            <!-- Adding gallery images. We use resized thumbnails here for better performance, but it’s not necessary -->

            <div class="galleria">
                <script>
                    Galleria.loadTheme('galleria/themes/azur/galleria.azur.min.js');

                    $.get("Controller/data_galleria.php?action=1&ulbum_id=<?php echo $_GET['id']; ?>", function (data) {

                        Galleria.run('.galleria', {dataSource: data});

                    }, "json");
                </script>

            </div>
            <script>

            </script>
        </div>

    </body>
</html>
