<?php
$LINKEDINSHAREMODULE_LOADED = true;
chdir("..");
require("settings.php");
if ((isset($_GET["state"]))&&(isset($_GET["code"]))) 
{
    $data = $linkedInConnector->handleRegistrationCallback($shareModule->getClientID(), $shareModule->getHash(), $shareModule->getClientSecret());
    $linkedInData = json_decode($data);
    if (isset($linkedInData->access_token)&&isset($linkedInData->expires_in)) 
    {
        $timespan = time() + intval($linkedInData->expires_in);

        $shareModule->saveConfigurationVariable("access_token", $linkedInData->access_token);
        $shareModule->saveConfigurationVariable("expires_in", $timespan);

        echo "the data is saved";
    }            
    else
    {
        die("cannot get an access-token from this data...");
    }
}
?>