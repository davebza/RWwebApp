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
$PageTitle = "Worksheet creation page";
include('header.php');

if(substr($_SESSION['class'], 1,2)!= "T"){
		die("You are not authorized to view this page!");
	}

?>

<h1>Worksheet Creation Process</h1>

<p>
<fieldset>

<form action="teacherInput.php"
			method="POST">
			
<legend>Name of worksheet:</legend>

<p> 
<label>Worksheet name, in the format [MOD]#[GR]#[GROUPCOLOR:Initial Letter] eg. MOD1GR2Y:</label>
<input type = "text"
		   name  = "worksheetName"
		   id    = "worksheetName"
		   size = "30"
		   maxlength = "30"
		   value = "" />
</p>

<input type = "hidden" name="worksheetNamePageSubmitted" value = "TRUE" />


<input type = "submit" value = "Start making the questions" class="button orange submit" />


</form>
</fieldset>
</p>