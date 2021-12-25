<?php

namespace Identity\Core;

if(!defined("IdentityFramework")){ die("Access Denied!"); }

if(!session_id())
{
    session_start();
}

class Session
{
    public static function Add($Key, $Value)
    {
        if(isset($_SESSION[$Key]))
        {
            $_SESSION[$Key] = null;
            unset($_SESSION[$Key]);
        }

        $_SESSION[$Key] = $Value;
    }

    public static function Get($Key)
    {
        if(isset($_SESSION[$Key]))
        {
            return $_SESSION[$Key];
        }
        else
        {
            return null;
        }
    }

    public static function Remove($Key)
    {
        if(isset($_SESSION[$Key]))
        {
            $_SESSION[$Key] = null;
            unset($_SESSION[$Key]);
        }
    }

    public static function AddCookie($Key, $Value, $ExpiryDate)
    {
        if(isset($_COOKIE[$Key]))
        {
            $_COOKIE[$Key] = null;
            unset($_COOKIE[$Key]);
        }
      
        setcookie($Key, $Value, strtotime($ExpiryDate));
    }

    public static function GetCookie($Key)
    {        
        if(isset($_COOKIE[$Key]))
        {
            return $_COOKIE[$Key];
        }
        else
        {
            return null;
        }
    }

    public static function RemoveCookie($Key)
    {
        if(isset($_COOKIE[$Key]))
        {
            $_COOKIE[$Key] = null;
            unset($_COOKIE[$Key]);
        }
    }

    public static function ClearAll()
    {
        $_SESSION["user"] = null;
        $_COOKIE["token"] = null;
        setcookie("token", null, -1, '/');
        setcookie("token", "", time() - 3600);
        unset($_SESSION["user"]);
        unset($_COOKIE["token"]);
        unset($_SESSION);
        unset($_COOKIE);
        session_unset();
        session_destroy();
    }
}

?>