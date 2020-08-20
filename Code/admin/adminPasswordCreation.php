<?php
    // prevent direct access, in case htaccess file not set
    if (get_included_files()[0] === __FILE__) exit;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collector Admin Password Creation</title>
    <link rel="icon" href="<?= admin\url_to_root() ?>/Code/icon.png" type="image/png">
    <style>
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-size: 125%;
        }
        
        body > div {
            display: table;
            width: 100%;
            height: 100%;
        }
        
        body > div > div {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
        }
        
        .invis { visibility: hidden; }
        .error { color: #600; }
        
        input { margin: 10px; font-size: 100%; }
        
        span {
            display: inline-block;
            width: 110px;
            text-align: left;
        }
        
        button {
            font-size: 100%;
            margin-top: 20px;
        }
    </style>
    <script>
        function validate_inputs() {
            const inputs = document.querySelectorAll("input");
            const warning = document.getElementById("mismatch-error");
            const submit = document.getElementById("submit-btn");
            
            if (inputs[0].value !== inputs[1].value || inputs[1].value === "") {
                if (inputs[0].value === "" || inputs[1].value === "") {
                    warning.classList.add("invis");
                } else {
                    warning.classList.remove("invis");
                }
                
                submit.disabled = true;
                return false;
            } else {
                warning.classList.add("invis");
                submit.disabled = false;
                return true;
            }
        }
        
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            
            form.addEventListener("input", validate_inputs);
            
            form.addEventListener("submit", function(e) {
                if (!validate_inputs()) {
                    e.preventDefault();
                    return;
                }
            });
        });
    </script>
</head>
<body><div><div>
    <form method="post">
        <p class="invis" id="mismatch-error">
            Password does not match
        </p>
        
        <p>Please create a password.</p>
        
        <div>
            <span>Password:</span> <input type="password" autofocus> <br>
            <span>Repeat:</span>   <input type="password" name="col_adm_new_pas" required> <br>
            <button type="submit" id="submit-btn" disabled>Submit</button>
        </div>
    </form>
</div></div></body>
</html>
