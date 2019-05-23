<?php
    interface iLinkedInConnector
    {
        public function registerAccess($clientID, $hash);
        public function handleRegistrationCallback($clientID, $hash, $clientSecret);
        public function postToLinkedIn($accessToken, $articleData);
        public function getAuthor($accessToken);
    }
?>