<?php

#do a session check, and bounce the person if they aren't logged in yet:
session_start();

if (!isset($_SESSION['agent']) OR ($_SESSION['agent'] != md5($_SERVER['HTTP_USER_AGENT'] ) )){

	#need the functions to create an absolute URL:
	require_once ('loginFunctions.php');
	$url = absoluteUrl();
	header("Location: $url");
	exit();#exit the script
}

#include master variables, set the pagetitle variable and include the header file
include_once 'masterVariables.php';
$PageTitle = "Home Reading Book Borrowing";
include('header.php');

if(substr($_SESSION['class'], 1,2)!= "T"){
	die("You are not authorized to view this page!");
}



include 'studentBorrowingForm.php';
 
include_once 'footer.html';
?>