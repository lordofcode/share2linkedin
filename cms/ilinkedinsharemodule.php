<?php
interface iLinkedInShareModule
{
    public function initialize($configTable, $shareTable);
    public function getAccessToken();
    public function getClientID();
    public function getHash();
    public function getClientSecret();
    public function saveConfigurationVariable($name, $value);
    public function fetchLastPost();
    public function fetchPostFields($postID);
    public function registerSharedPost($postID, $linkedInID);
}
?>