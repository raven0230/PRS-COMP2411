<?php
switch ($_POST["action"]) {
    case "login":
        $loginDB = new Login();
        if ($loginDB->checkCred($_POST["email"], $_POST["password"])) {

        }
        break;
    default:
        break;
}
