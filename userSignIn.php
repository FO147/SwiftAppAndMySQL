<?php

require("../db/MySQLDAO.php");
$config = parse_ini_file('../../../../SwiftAppAndMySQL.ini');

$returnValue = array();

//Check of email adres en password meegegeven is
if(empty($_REQUEST["userEmail"]) || empty($_REQUEST["userPassword"]))
{
    $returnValue["status"]="400";
    $returnValue["message"]="Missing required information (PHP userSignIn)";
    echo json_encode($returnValue);
    return;
}

//Paramters checken om SQL injections tegen te gaan
$userEmail = htmlentities($_REQUEST["userEmail"]);
$userPassword = htmlentities($_REQUEST["userPassword"]);

$dbhost = trim($config["dbhost"]);
$dbuser = trim($config["dbuser"]);
$dbpassword = trim($config["dbpassword"]);
$dbname = trim($config["dbname"]);

$dao = new MySQLDAO($dbhost,$dbuser,$dbpassword,$dbname);
$dao->openConnection();
//Check of de gebruiker bestaat
$userDetails = $dao->getUserDetails($userEmail);

if(empty($userDetails))
{
    $returnValue["status"]="403";
    $returnValue["message"]="User not found (PHP userSignIn)";
    echo json_encode($returnValue);
    return;
}

$userSecuredPassword = $userDetails["user_password"];
$userSalt = $userDetails["salt"];

if($userSecuredPassword === sha1($userPassword . $userSalt))
{
   $returnValue["status"] = "200";
   $returnValue["userFirstName"] = $userDetails["first_name"];
   $returnValue["userLastName"] = $userDetails["last_name"];
   $returnValue["userEmail"] = $userDetails["email"];
   $returnValue["userId"] = $userDetails["user_id"];
} else {
    $returnValue["status"]="403";
    $returnValue["message"]="User not found (PHP userSignIn)";
    echo json_encode($returnValue);
    return;
}

$dao->closeConnection();

echo json_encode($returnValue);
 
?>