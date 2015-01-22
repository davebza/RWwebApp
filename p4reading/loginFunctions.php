<?php
#This page defines 2 functions that will be used during the login and logout process
session_start();
/* This first function determines and returns an absolute URL. 
 * It takes one argument: the page that concludes the URL.
 * The argument defaults to loginIndex.php
 */

function absoluteUrl ($page = 'loginIndex.php') {
	#start defining the URL:
	#URL is http:// plus hostname plus current directory:
	$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	
	#remove any trailing slashes
	$url = rtrim($url, '/\\');
	
	#add the page:
	$url .='/' . $page;
	
	#return the URL:
	return $url;
}#end of absolute URL function

/* This function will validate the form data (i.e. the email address and password).
 * If both are present, the database is queried.
 * the function requires a database connection
 * The function returns an array of information, including:
 *  -  A TRUE/FALSE variable indicating success
 *  - An array of either errors or the database result 
 */

function checkLoginStudent($dbc, $class = '', $classNumber = '', $pass = '') {
	
	if  ($_POST['gradeLevel'] == 4){
		$allTable = '4all';
	}elseif ($_POST['gradeLevel'] == 5){
		$allTable = '5all';
	}else {
		echo "There has been an error with the choice of class";
	}
	
	#initialize error array:
	$errors = array();
	
	#validate class & classNumber:
	if (empty($class)) {
		$errors[] = 'You didn\'t choose your class.';
	}else {
		
		if (empty($classNumber)) {
		$errors[] = 'You didn\'t tell me your student number.';
	}else {
		
	}
	$cleanclassNumber = mysqli_real_escape_string($dbc, trim($classNumber));
	}
	
	#validate the password:
	if (empty($pass)) {
		$errors[] = 'You forgot to enter your password.';
	}else {
		$cleanPass = mysqli_real_escape_string($dbc, trim($pass));
	}
	
	if (empty($errors)){#if everything's OK so far
	
		#Get the user_id and first_name for that password/email combination:
		
		$dbq = "SELECT studentId, class, classNumber, firstName, lastName, grp FROM $allTable WHERE class = '$class' AND classNumber = '$cleanclassNumber' AND pass = SHA1('$cleanPass')";
		$dbr = @mysqli_query($dbc, $dbq); #run the query
		
		if (!$dbr){
			print_r($dbc);
			die("There is no result");
		}
		#check the result:
		if (mysqli_num_rows($dbr)==1) {
			#Fetch the record:
			$row = mysqli_fetch_array($dbr, MYSQLI_ASSOC);
			#return true and the record:
			return array(true, $row);
		}else {#not a match!
					$errors[] = 'You made a mistake with your class number and password.';			
		}
	} 
	#return false and the errors:
	return array(false, $errors);
	
}#end of checkLoginStudent function

function checkLoginTeacher($dbc, $number = '', $pass = '') {
	#initialize error array:
	$errors = array();
	
	#validate class & classNumber:
	
	if (empty($number)) {
		$errors[] = 'You didn\'t tell me your teacher number.';
	}else {
	
		$cleanNumber = mysqli_real_escape_string($dbc, trim($number));
	}
	
	#validate the password:
	if (empty($pass)) {
	$errors[] = 'You forgot to enter your password.';
	}else {
	$cleanPass = mysqli_real_escape_string($dbc, trim($pass));
	}
	
	if (empty($errors)){#if everything's OK so far
	
		#Get the user_id and first_name for that password/email combination:
	
		$dbq = "SELECT firstName, lastName, class1, class2 FROM teachers WHERE number = '$cleanNumber' AND pass = SHA1('$cleanPass')";
		$dbr = @mysqli_query($dbc, $dbq); #run the query
	
		if (!$dbr){
		print_r($dbc);
		die("There is no result");
	}
	}
	#check the result:
		if (mysqli_num_rows($dbr)==1) {
		#Fetch the record:
		$row = mysqli_fetch_array($dbr, MYSQLI_ASSOC);
		#return true and the record:
		return array(true, $row);
	}else {#not a match!
		$errors[] = 'You made a mistake with your teacher number and password.';
	}
	
		#return false and the errors:
		return array(false, $errors);
}#end of checkLoginTeacher function
?>