<?php 
#This page lets the user log out.
session_start();
# Check for cookies and redirect:

if (!isset($_SESSION['user_id'])){
	
	#need the functions to create an absolute URL:
	require_once 'loginFunctions.php';
	
	$url = absoluteUrl();
	header("Location: $url");
	exit(); #exit the script
	
} else {#cancel the session
	$_SESSION = array(); #Clear the session array
	session_destroy();#Destroy the session itself
	setcookie('PHPSESSID',' ', time()-3600, '/', ' ',
	0, 0); # destroy the cookie
}

#Set the page title and include the header.html file:

$PageTitle = 'Logged Out!';
include 'header.php';

#print a customized message:
echo "<h1>Logged out successfully!</h1>

<p> You have sucessfully logged out!</p>";

include_once 'footer.html';
?>