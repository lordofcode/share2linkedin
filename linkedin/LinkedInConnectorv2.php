<?php
    class LinkedInConnectorV2 implements iLinkedInConnector
    {
        public function registerAccess($clientID, $hash)
        {
            global $_SERVER;
            $redirectUri = "https://".$_SERVER["SERVER_NAME"].$fileLocation."/callback";
            $scope="r_liteprofile%20r_emailaddress%20w_member_social";
            $location = "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=".$clientID."&redirect_uri=".$redirectUri."&state=".$hash."&scope=".$scope;
            header("Location: ".$location);            
        }

        public function handleRegistrationCallback($clientID, $hash, $clientSecret)
        {
            global $_GET;
            $code = $_GET["code"];
            $state = $_GET["state"];
            if ($state != $hash) die("wrong call of the page");
        
            global $_SERVER;
            $redirectUri = "https://".$_SERVER["SERVER_NAME"].$fileLocation."/callback";            
            $url = "https://www.linkedin.com/oauth/v2/accessToken";
            $postdata = "grant_type=authorization_code&code=".$code."&redirect_uri=".$redirectUri."&client_id=".$clientID."&client_secret=".$clientSecret;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }

        public function postToLinkedIn($accessToken, $articleData)
        {
            $result = "";

            $postdata = "{
                \"author\": \"{AUTHOR}\",
                \"lifecycleState\": \"PUBLISHED\",
                \"specificContent\": {
                    \"com.linkedin.ugc.ShareContent\": {
                        \"shareCommentary\": {
                            \"text\": \"{COMMENT}\"
                        },
                        \"shareMediaCategory\": \"ARTICLE\",
                        \"media\": [
                            {
                                \"status\": \"READY\",
                                \"description\": {
                                    \"text\": \"{DESCRIPTION}\"
                                },
                                \"originalUrl\": \"{URL}\",
                                \"title\": {
                                    \"text\": \"{TITLE}\"
                                }
                            }
                        ]
                    }
                },
                \"visibility\": {
                    \"com.linkedin.ugc.MemberNetworkVisibility\": \"CONNECTIONS\"
                }
            }";
            
            $postdata = str_replace("{AUTHOR}", "urn:li:person:".$articleData["AUTHOR"], $postdata);                        
            $postdata = str_replace("{DESCRIPTION}", $articleData["DESCRIPTION"], $postdata);
            $postdata = str_replace("{TITLE}", $articleData["TITLE"], $postdata);
            $postdata = str_replace("{URL}", $articleData["URL"], $postdata);
            $postdata = str_replace("{COMMENT}", $articleData["COMMENT"], $postdata);

            $url = "https://api.linkedin.com/v2/ugcPosts";
            $header = array();
            $header[] = "X-Restli-Protocol-Version: 2.0.0";
            $header[] = "x-li-format: json";
            $header[] = "Content-Type: application/json";
            $header[] = "Authorization: Bearer ".$accessToken;        
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            $data = curl_exec($ch);
            curl_close($ch);
      
            $linkedInData = json_decode($data);
            if (isset($linkedInData->id)) 
            {
                $result = $linkedInData->id;
            }
            elseif (isset($linkedInData->status))
            {
                if ($linkedInData->status == 401) {
                    echo "need a token refresh!";
                    exit;
                }
                die("Failed with ".$linkedInData->status);
            }
            return $result;
        }

        public function getAuthor($accessToken)
        {
            $result = "";

            $url = "https://api.linkedin.com/v2/me";
            $header = array();
            $header[] = "X-Restli-Protocol-Version: 2.0.0";
            $header[] = "x-li-format: json";
            $header[] = "Content-Type: application/json";
            $header[] = "Authorization: Bearer ".$accessToken;        
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, 0);
            $data = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($data);
            $result = $result->id;

            return $result;
        }    
    }
?>