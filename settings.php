<?php
if (isset($LINKEDINSHAREMODULE_LOADED))
{
    $secretToken = "##YOURUNIQUEITEMFORREQUEST##";
    $linkedInConfigTableName = "##NAME_OF_TABLE_FOR_SETTINGS##";
    $linkedInShareTableName = "##NAME_OF_TABLE_TO_REGISTER_SHARING##";
    $cmsName="DRUPAL"; //could be wordpress, joomla. then you have to create your own include-file
    $fileLocation="/linkedin";

    require_once("linkedin/iLinkedInConnector.php");
    require_once("linkedin/LinkedInConnectorv2.php");
    $linkedInConnector = new LinkedInConnectorV2();

    require_once("cms/ilinkedinsharemodule.php");
    $shareModule = null;

    switch($cmsName) 
    {
        case "DRUPAL":
            require_once("cms/drupal.php");
            $shareModule = new LinkedInShareModule_Drupal();
            break;
        default:
            die("NOT IMPLEMENTED");
    }
    $shareModule->initialize($linkedInConfigTableName,$linkedInShareTableName);
}
else die("Invalid call!");
?>