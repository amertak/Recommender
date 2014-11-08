<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

define('key', '.|RVu_4^=+N4Tmh|~7~Z');

if (isset($_POST['payload']))
{
    $found = False;
    foreach (getallheaders() as $name => $value)
    {
        if ($name == "X-Hub-Signature")
        {
            $found = true;
            if ($value != "sha1=" . hash_hmac('sha1', file_get_contents("php://input"), key))
            {
                exit('K');
            }
        }
    }

    if (!$found)
    {
        exit('H');
    }

    echo shell_exec("git pull");
}