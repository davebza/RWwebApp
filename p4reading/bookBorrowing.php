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
?>

<h1>To check out a book, please choose the class:</h1>
<fieldset>
<form action= "studentBorrowing.php" 
			method = "post">
			
			
			<table border="1">
					<tr>
							<td><p>P4 Classes:<br/>
										<input type="radio" name="borrowingClass" value="4A" /> P4A</input><br />
										<input type="radio" name="borrowingClass" value="4B" /> P4B</input><br />
										<input type="radio" name="borrowingClass" value="4C" /> P4C</input><br />
										<input type="radio" name="borrowingClass" value="4D" /> P4D</input><br />
									</p>
							</td>
							<td><p>P5 Classes:<br/>
										<input type="radio" name="borrowingClass" value="5A" /> P5A</input><br />
										<input type="radio" name="borrowingClass" value="5B" /> P5B</input><br />
										<input type="radio" name="borrowingClass" value="5C" /> P5C</input><br />
										<input type="radio" name="borrowingClass" value="5D" /> P5D</input><br />
									</p>
							</td>
					</tr>
			</table>
															
			<p><input type = "submit"
							class = "button orange"
							name = "submit"
							value = "Check out book" /></p>
							
			<input type = "hidden"
						name = "hiddenBorrow"
						value = "TRUE" />
</form>
</fieldset>

<h1>To return a book, scan the book and click the button:</h1>

<fieldset>
<form action= "bookReturn.php" 
			method = "post">
			
		<p>Book Code:
			<input type = "text"
		   		name = "bookId" autofocus
		   		id    = "bookId"
		  		size = "20"
		  		maxlength = "20"
		   		value = "" />
		</p>
			
	<p><input type = "submit"
							class = "button orange"
							name = "submit"
							value = "Return book" /></p>
							
			<input type = "hidden"
						name = "hiddenReturn"
						value = "TRUE" />
						
</form>
</fieldset>

<h1> Add new book to database:</h1>

<fieldset>
<form action = "bookAcquisition.php"
		  method = "post">
		  
		  <p><input type = "submit"
							class = "button orange"
							name = "submit"
							value = "Add new book" /></p>
</form>
</fieldset>
<?php 
include_once 'footer.html';
?>