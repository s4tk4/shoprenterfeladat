<?php

class database
{
    private static $host = "localhost";
    private static $dbname = "shoprenter";
    private static $username = "shoprenter_usr";
    private static $password = '$h0pr3nt3r';

    private static $conn = null;

    public static function getConnection()
    {
        try{
            self::$conn = new mysqli(self::$host,self::$username, self::$password, self::$dbname);
        }catch(Exception $ex)
        {
            echo "Database not connected: ".$ex->getMessage();
        }
        return self::$conn;
    }

}