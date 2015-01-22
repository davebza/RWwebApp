<?php
#This page processes the login form.
#If successful, the user is redirected.
# Send nothing to the browser before setting the cookies.

#Check if the form is submitted:

if (isset($_POST['submitted'])) {
	#for processing the login:
	require_once 'loginFunctions.php';
	
	$loginClass =  $_POST['gradeLevel'].$_POST['class'];
	$_POST['class'] = $loginClass;
	
	#need a database connection: Check whether P4 or P5 or teacher
	if ($_POST['gradeLevel']== '4'){
		
		require_once 'mysqliConnectP4.php';
	
	}elseif($_POST['gradeLevel']== '5'){
		
		require_once 'mysqliConnectP5.php';
	
	}elseif($_POST['gradeLevel']== 'T'){
		
		require_once 'mysqliConnectTeacher.php';
		
	}else{
		
		echo "There was a problem connecting to the database for login checking.";
	
	}
	
	#check the login:
	
	#first, check if we should use a teacher or a student login function using the same $_POST['gradeLevel'] variable as we used to include the correct database connection above:
	if ($_POST['gradeLevel'] == 'T') {
		list ($check, $data)  = checkLoginTeacher($dbc, $_POST['classNumber'], $_POST['pass']);
		
		if ($check) {#OK!
			# set session data:
			session_start();
			$_SESSION = array();
			$_SESSION['userId'] = $data;
			$_SESSION['firstName'] = $data['firstName'];
			$_SESSION['lastName'] = $data['lastName'];
			$_SESSION['class'] = TT; #This is hardcoded, to allow teachers to get an overview of their classes
			$_SESSION['class1'] = $data['class1'];
			$_SESSION['class2'] = $data['class2'];
			
			#grab and store an encrypted version of the HTTP_USER_AGENT info:
			$_SESSION['agent'] = md5($_SERVER['HTTP_USER_AGENT']);
			#redirect:
			$url = absoluteUrl('loggedIn.php');
			header("Location: $url");
			exit();#quit the script
			
		} else{#unsuccessful:
		#assign $data to $errors for error report in the login.php file:
		$errors = $data;
		
	}
	
	mysqli_close($dbc);#close the database connection
	
	}elseif ($POST['gradeLevel'] == 4 || 5) {
	
	list ($check, $data)  = checkLoginStudent($dbc, $_POST['class'], $_POST['classNumber'], $_POST['pass']);
	
	if ($check) {#OK!
		# set session data:
		session_start();
		$_SESSION = array();
		$_SESSION['userId'] = $data;
		$_SESSION['studentId'] = $data['studentId'];
		$_SESSION['firstName'] = $data['firstName'];
		$_SESSION['lastName'] = $data['lastName'];
		$_SESSION['grp'] = $data['grp'];
		$_SESSION['class'] = $data['class'];
		$_SESSION['classNumber'] = $data['classNumber'];
		
		#grab and store an encrypted version of the HTTP_USER_AGENT info:
		$_SESSION['agent'] = md5($_SERVER['HTTP_USER_AGENT']);
		#redirect:
		$url = absoluteUrl('loggedIn.php');
		header("Location: $url");
		print_r($_SESSION);
		exit();#quit the script
	
	
	}else{#unsuccessful:
		#assign $data to $errors for error report in the login.php file:
		$errors = $data;
		
	}
	
	mysqli_close($dbc);#close the database connection
	
	}
}#end of isset - submitted conditional

#create the page:
include 'loginIndex.php';
?>