<?php 
$router->add("/api/client/user/info", function () {
    require("../include/main.php");
    require("../api/client/user/info.php");
});

$router->add("/api/store/failedsubscription", function(): void {
    require("../include/main.php");
    require("../api/store/removesubscription.php");
});

?>