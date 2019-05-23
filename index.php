<?php
$LINKEDINSHAREMODULE_LOADED = true;
require("settings.php");    
if (isset($_GET["action"]) && ($_GET["action"] == $secretToken))
{
    $linkedInConnector->registerAccess($shareModule->getClientID(), $shareModule->getHash());
}
else die("Invalid call!");
?>