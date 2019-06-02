<?php


class db
{
 function connection(){
     $dbConnection = new PDO('mysql:host=127.0.0.1;dbname=community', 'root', '');
//       $dbConnection=new PDO("mysql:host=$this->dbHost;dbname=$this->dbname",$this->dbUser,$this->passwrd);
     $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $dbConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

     return $dbConnection;
 }


}