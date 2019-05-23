<?php
$LINKEDINSHAREMODULE_LOADED = true;
require("settings.php");    
if (isset($_GET["action"]) && ($_GET["action"] == $secretToken))
{
    $lastPost = intval($shareModule->fetchLastPost());
    
    if ($lastPost > 0)
    {
        $forceTokenRefresh = false;
    
        $valid = true;

        $articleData = $shareModule->fetchPostFields($lastPost);
        if ($articleData["AUTHOR"] == "")
        {
            $author = $linkedInConnector->getAuthor($shareModule->getAccessToken());
            $shareModule->saveConfigurationVariable("author", $author);
            $articleData["AUTHOR"] = $author;
        }

        $result = $linkedInConnector->postToLinkedIn($shareModule->getAccessToken(), $articleData);

        if ($result != "")
        {
            $shareModule->registerSharedPost($lastPost, $result);
            echo "A new post has been shared!";
        }
        else
        {
            die("Post returned an empty result!");
        }
    }
    else
    {
        echo "Nothing to post!";
    }
}
else die("Invalid call!");
?>