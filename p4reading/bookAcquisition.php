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
include_once('header.php');

if(substr($_SESSION['class'], 1,2)!= "T"){
	die("You are not authorized to view this page!");
}

#Start of book acquisition area:
?>
<fieldset>
<form action ="processBookData.php"
		method = "post">
		
		<p>Book Barcode:
			<input type = "text"
		   		name = "bookId" autofocus
		   		id    = "bookId"
		  		size = "20"
		  		maxlength = "20"
		   		value = "" />
		</p>
		
		<p>Book Title:
			<input type = "text"
		   		name = "bookTitle" 
		   		id    = "bookTitle"
		  		size = "40"
		  		maxlength = "40"
		   		value = "" />
		</p>
		
		<p>Publisher:
			<input type = "text"
		   		name = "bookPublisher" 
		   		id    = "bookPublisher"
		  		size = "20"
		  		maxlength = "20"
		   		value = "" />
		
		
		Cost:
			<input type = "text"
		   		name = "bookCost" 
		   		id    = "bookCost"
		  		size = "6"
		  		maxlength = "6"
		   		value = "" />
		</p>
		
		<p>Book Level:
			<input type = "text"
		   		name = "bookLevel" 
		   		id    = "bookLevel"
		  		size = "3"
		  		maxlength = "3"
		   		value = "" />
		</p>
		
		 <p><input type = "submit"
							class = "button orange"
							name = "submit"
							value = "Add information to database" /></p>
</form>		
</fieldset>


<?php
include_once 'footer.html';
?>