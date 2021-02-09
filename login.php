<!DOCTYPE html>
<html>
    <head>
        <title>Projects Program</title>
        <link rel="shortcut icon" href="Views/imgs/laptop_settings-512.png"  type="image/x-icon" >
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <link rel="stylesheet" type="text/css" href="dhtmlxsuite4/codebase/dhtmlx.css"/> 
        <link rel="stylesheet" type="text/css" href="dhtmlxsuite4/skins/terrace/dhtmlx.css"/>
        <script src="dhtmlxsuite4/codebase/dhtmlx.js"></script>
        <style>
            html, body {
                background-color: #f5f5f5;
            }
            iframe.submit_iframe {
                position: absolute;
                width: 1px;
                height: 1px;
                left: -100px;
                top: -100px;
                font-size: 1px;
            }
            div.login_form {
                position: relative;
                margin-top: 200px;
                margin-left: auto;
                margin-right: auto;
                height: 205px;
                width: 300px;
                box-shadow: 0px 0px 8px rgba(127, 127, 127, 0.4);
                border: 1px solid #c0c0c0;
                border-radius: 2px;
                background-color: white;
            }
        </style>
        <script>

            var myForm, formData;

            function doOnLoad() {
                formData = [
                    {type: "settings", position: "label-left", labelWidth: 75, inputWidth: 150},
                    {type: "block", blockOffset: 30, offsetTop: 15, width: "auto", list: [
                            {type: "label", label: "Please Login", labelWidth: "auto", offsetLeft: 35},
                            {type: "input", label: "Login", name: "dhxform_demo_login", value: "", offsetTop: 20},
                            {type: "password", label: "Password", name: "dhxform_demo_pwd", value: ""},
                            {type: "button", name: "submit", value: "Log In", offsetTop: 20, offsetLeft: 72}
                        ]}
                ];

                myForm = new dhtmlXForm("dhxForm", formData);

                myForm.attachEvent("onButtonClick", function (name) {

                    // submit real form when user clicks Submit button on a dhtmlx form
                    if (name == "submit") {
                        document.getElementById("realForm").submit();
                    }
                });
                document.onkeypress = enterPressed;

                function enterPressed(evn) {
                    if (window.event && window.event.keyCode == 13)
                    {
                        document.getElementById("realForm").submit();
                    } else if (evn && evn.keyCode == 13)
                    {
                        document.getElementById("realForm").submit();
                    }
                }
            }

            function submitCallback(status, eid) {
                if (status === 1) {
                    document.location.href = "index.php?eid=" + eid;
                } else {
                    // reset form
                    myForm.setFormData({dhxform_demo_login: "", dhxform_demo_pwd: ""});
                }
            }

        </script>
    </head>
    <body onload="doOnLoad();">
        <div class="login_form">
            <form id="realForm" action="check.php" method="POST" target="submit_ifr">
                <div id="dhxForm"></div>
            </form>
        </div>
        <iframe border="0" frameBorder="0" name="submit_ifr" class="submit_iframe"></iframe>
    </body>
</html>
