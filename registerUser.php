<?php 

//require("../db/Conn.php");
require("../db/MySQLDAO.php");
$config = parse_ini_file('../../../../SwiftAppAndMySQL.ini');

$returnValue = array();

//Check of de parameters meegegeven zijn
if(empty($_REQUEST["userEmail"]) || empty($_REQUEST["userPassword"])
        || empty($_REQUEST["userFirstName"])
        || empty($_REQUEST["userLastName"]))
{
    $returnValue["status"]="400";
    $returnValue["message"]="Missing required information";
    echo json_encode($returnValue);
    return;
}

//Paramters checken om SQL injections tegen te gaan
$userEmail = htmlentities($_REQUEST["userEmail"]);
$userPassword = htmlentities($_REQUEST["userPassword"]);
$userFirstName = htmlentities($_REQUEST["userFirstName"]);
$userLastName = htmlentities($_REQUEST["userLastName"]);

//Password secure maken
$salt = openssl_random_pseudo_bytes(16);
$secured_password = sha1($userPassword . $salt);   

$dbhost = trim($config["dbhost"]);
$dbuser = trim($config["dbuser"]);
$dbpassword = trim($config["dbpassword"]);
$dbname = trim($config["dbname"]);

$dao = new MySQLDAO($dbhost,$dbuser,$dbpassword,$dbname);
$dao->openConnection();

//Check of de gebruiker bestaat
$userDetails = $dao->getUserDetails($userEmail);
if(!empty($userDetails))
{
    $returnValue["status"]="400";
    $returnValue["message"]="Please choose a different email address (PHP registerUser)";
    echo json_encode($returnValue);
    return;
}

//Toevoegen gebruiker
$result = $dao->registerUser($userEmail, $userFirstName, $userLastName, $secured_password, $salt);

if($result)
{
    $userDetails = $dao->getUserDetails($userEmail);
    $returnValue["status"]="200";
    $returnValue["message"]="Succesfully registered new user";
    $returnValue["userId"]=$userDetails["user_id"];
    $returnValue["userFirstName"]=$userDetails["first_name"];
    $returnValue["userLastName"]=$userDetails["last_name"];
    $returnValue["userEmail"]=$userDetails["email"];
} else {
    $returnValue["status"]="400";
    $returnValue["message"]="Could not register user with provided information (PHP registerUser)";
}

$dao->closeConnection();

echo json_encode($returnValue);
?>
