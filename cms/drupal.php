<?php
if (isset($LINKEDINSHAREMODULE_LOADED))
{
    class LinkedInShareModule_Drupal implements iLinkedInShareModule
    {
        private $configTable;
        private $shareTable;

        private $host;
        private $user;
        private $pass;
        private $database;
        private $prefix;

        private $clientID;
        private $clientSecret;
        private $accessToken;
        private $expiresIn;
        private $hash;

        public function initialize($configTable, $shareTable)
        {
            $this->configTable = $configTable;
            $this->shareTable = $shareTable;

            $this->configureDatabaseConnection();         

            $this->createTables();

            $this->loadConfigurationVariables();
        }

        private function createTables()
        {
            mysql_query("CREATE TABLE IF NOT EXISTS `".$this->prefix.$this->configTable."` (
                `name` varchar(250) COLLATE latin1_german1_ci NOT NULL,
                `value` varchar(512) COLLATE latin1_german1_ci NOT NULL,
                PRIMARY KEY (`name`)
              ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;");
            mysql_query("CREATE TABLE IF NOT EXISTS `".$this->prefix.$this->shareTable."` (
                `nid` int(11) NOT NULL,
                `updateKey` varchar(250) COLLATE latin1_german1_ci NOT NULL,
                `updateUrl` varchar(250) COLLATE latin1_german1_ci NOT NULL,
                `sharedate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`nid`)
              ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;");
        }

        public function getAccessToken()
        {
            return $this->accessToken;
        }

        public function getClientID()
        {
            return $this->clientID;
        }

        public function getHash()
        {
            return $this->hash;
        }

        public function getClientSecret()
        {
            return $this->clientSecret;
        }

        private function configureDatabaseConnection()
        {
            chdir("..");
            require("sites/default/settings.php");
            $this->host = $databases["default"]["default"]["host"];
            $this->user = $databases["default"]["default"]["username"];
            $this->pass = $databases["default"]["default"]["password"];
            $this->database = $databases["default"]["default"]["database"];
            $this->prefix = $databases["default"]["default"]["prefix"];
            $this->hash = $settings['hash_salt'];
        }

        private function loadConfigurationVariables()
        {
            $this->accessToken = $this->getConfigurationVariableValue("access_token");
            $this->clientID = $this->getConfigurationVariableValue("clientid");
            $this->clientSecret = $this->getConfigurationVariableValue("clientsecret");
            $this->expiresIn = $this->getConfigurationVariableValue("expires_in");
        }

        private function getConfigurationVariableValue($configurationVariable)
        {
            $result = "";

            $dbconn = mysql_connect($this->host, $this->user, $this->pass);
            mysql_select_db($this->database, $dbconn);

            $row = mysql_query("SELECT value FROM ".$this->prefix.$this->configTable." WHERE name='".addslashes($configurationVariable)."'");
            if ($data = mysql_fetch_assoc($row))
            {
                $result = $data["value"];
            }
            mysql_close($dbconn);                        

            return $result;
        }

        public function saveConfigurationVariable($name, $value)
        {
            $dbconn = mysql_connect($this->host, $this->user, $this->pass);
            mysql_select_db($this->database, $dbconn);

            $row = mysql_query("SELECT COUNT(1) AS aantal FROM ".$this->prefix.$this->configTable." WHERE name='".addslashes($name)."'");
            if ($data = mysql_fetch_assoc($row))
            {
                if (intval($data["aantal"]) == 0)
                {
                    mysql_query("INSERT INTO ".$this->prefix.$this->configTable." (name,value) SELECT '".addslashes($name)."','".addslashes($value)."'");
                }
                else
                {
                    mysql_query("UPDATE ".$this->prefix.$this->configTable." SET value='".addslashes($value)."' WHERE name='".addslashes($value)."'");
                }
            }

            mysql_close($dbconn);    
        }

        public function fetchLastPost()
        {
            $dbconn = mysql_connect($this->host, $this->user, $this->pass);
            mysql_select_db($this->database, $dbconn);
            $result = mysql_query("SELECT nid FROM ".$this->prefix."node_field_data WHERE status=1 ORDER BY changed DESC LIMIT 0,1");
      
            $field = intval(mysql_fetch_assoc($result)["nid"]);
            if ($field > 0)
            {
                $result = mysql_query("SELECT COUNT(1) as amount FROM ".$this->prefix.$this->shareTable." WHERE nid=".$field);
                if ( intval(mysql_fetch_assoc($result)["amount"]) > 0 )
                {
                    $field = 0;
                }
            }
            mysql_close($dbconn);    
            
            return $field;
        }

        public function fetchPostFields($postID)
        {
            $author = $this->getConfigurationVariableValue("author");

            $dbconn = mysql_connect($this->host, $this->user, $this->pass);
            mysql_select_db($this->database, $dbconn);

            $result = mysql_query("SELECT body_summary FROM ".$this->prefix."node__body WHERE entity_id=".$postID);
            $description = str_replace("\'", "'", addslashes(mysql_fetch_assoc($result)["body_summary"]));
                
            if ((strlen($description) == 0)||(strlen($description) > 256))
            {
                mysql_close($dbconn);
                die("invalid summary, larger as zero, less then 256! length is " . strlen($description));
            }
    
            $description_copy = "";
            for ($k=0; $k < strlen($description); $k++){
                if (strlen(json_encode(substr($description, $k, 1))) == 0) continue;
                $description_copy .= substr($description, $k, 1);
            }
            $description = $description_copy;
           
            $result = mysql_query("SELECT type, title FROM ".$this->prefix."node_field_data WHERE nid=".$postID);
            $row = mysql_fetch_row($result);
            $title = str_replace("\'", "'", addslashes($row[0] . ": " . $row[1]));
       
            global $_SERVER;
            $url = "https://".$_SERVER["SERVER_NAME"]."/node/".$postID;
            $comment = $description . " https://".$_SERVER["SERVER_NAME"]."/node/".$postID;
            
            mysql_close($dbconn);  

            return array("AUTHOR" => $author, "DESCRIPTION" => $description, "TITLE" => $title, "COMMENT" => $comment, "URL" => $url);
        }

        public function registerSharedPost($postID, $linkedInID)
        {
            $dbconn = mysql_connect($this->host, $this->user, $this->pass);
            mysql_select_db($this->database, $dbconn);
            mysql_query("INSERT INTO ".$this->prefix.$this->shareTable." (nid, updateKey) SELECT ".$postID.",'".addslashes($linkedInID)."'");
            mysql_close($dbconn);      
        }
    }
}
?>