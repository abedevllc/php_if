<?php

namespace Identity\Core;

if(!defined("IdentityFramework")){ die("Access Denied!"); }

class Security
{
    public static function Salt($Length)
    {
        $Length = ($Length < 4) ? 4 : $Length;
        return bin2hex(random_bytes(($Length-($Length%2))/2));
    }

    public function Password($Password, $Hash)
    {
        return hash('sha256', $Password . $Hash);
    }
}

?>