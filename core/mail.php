<?php

namespace Identity\Core;

if(!defined("IdentityFramework")){ die("Access Denied!"); }

class Mail
{
    public static function Send($From, $FromName, $To, $Subject, $Message, $HTML = false, $Attachments = null, $ReplayTo = null, $CC = null, $BCC = null)
    {
        $result = false;

        if($Subject != null && $Message != null && !empty($Message) && !empty($Subject) && $From != null && $FromName != null && !empty($From) && !empty($FromName))
        {
            echo "Subjct:<br>";
            echo $Subject;
            echo "<br><br><br>";
            echo "Message:<br>";
            echo $Message;
            $result = true;
        }

        return $result;
    }
}

?>