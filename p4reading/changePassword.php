<?php
#This script will allow the user to edit their password
#create a page title variable, and call the header file:
session_start();
if (!isset($_SESSION['agent']) OR ($_SESSION['agent'] != md5($_SERVER['HTTP_USER_AGENT'] ) )) {
	
	#need the functions to create an absolute URL:
	require_once ('loginFunctions.php');
	$url = absoluteUrl();
	header("Location: $url");
	exit();#exit the script
}
$PageTitle = "Change your password";
include_once 'header.php';

#check for submission of form:

if (isset($_POST['submitted'])) {
	
	#connect to the database:
	require_once 'mysqliConnectP4.php';
	
	#intialize the errors array:
	$errors = array();
	
	#check for the student Id connected to a class and class number:
	if (empty($_POST['class'])){
		$errors [] = "You didn't give us your class.";
	}elseif (empty($_POST['classNumber'])){
		$errors [] = "You didn't give us your number.";
	}elseif (empty($_POST['pass'])) {
	#check for the current password
		$errors [] = "You forgot to enter your current password";
	}else {
		$pass = mysqli_real_escape_string($dbc, trim($_POST['pass']));
	}
	$class = $_POST['class'];
	$classNumber =$_POST['classNumber'];

	#check for a new password and match it with the confirmation password:
	if(!empty($_POST['newPass1'])) {
		if ($_POST['newPass1'] != ($_POST['newPass2'])){
			$errors [] = "Your passwords didn't match. Please try again.";
			}else {
				$newPass = mysqli_real_escape_string($dbc, trim($_POST['newPass1']));
			}
		}else {
			$errors [] = "You forgot to enter a new password.";
	}#end of new password conditional
	
	#if there are no errors:
	if (empty($errors)){
		
		#Check that the correct email address and password combination:
		
		#create a database check query:
		$dbq = "SELECT studentId FROM 4all WHERE (class = '$class' AND classNumber = '$classNumber' AND pass = SHA1('$pass'))";
		#create a variable call to run the query:
		$dbr = @mysqli_query($dbc, $dbq);
		#create a variable for the number of rows affected:
		$num = mysqli_num_rows($dbr);
		#if there was a match
		if ($num == 1) {
			#get the user id
			$row = mysqli_fetch_array($dbr, MYSQLI_NUM);
			#create the database UPDATE query:
			$dbq = "UPDATE 4all SET pass = SHA1('$newPass') WHERE studentId = $row[0]";
            #run the query:
			$dbr = @mysqli_query($dbc, $dbq);
			
			#check to see if the UPDATE query ran:
				if (mysqli_affected_rows($dbc) == 1) {# if it ran ok
				echo "<h1>Thank you!</h1>
				 
				<p>Thank you, {$_SESSION['firstName']}! Your password has been updated.</p>";
				}else {#if the UPDATE query failed:
					#The public message:
					echo " <h1>System Error!</h1>
					
					<p class = 'error'>There has been a system error, and your password has not been updated. </p>";
					
					#debugging message:
					echo '<p>' . mysqli_error($dbc) . '<br /><br />Query: '.$dbq.'</p>';
				}
				#include the footer and quit the script, so that there will be no form displayed:
				include_once 'footer.html';
				exit();
					
		}else { #invalid email address and password combination:
			echo "<h1>Error!<h1>
			
			<p class = 'error'>Your student information and password do not match those on the database.</p>";
			
		}#end of UPDATE query conditional
		
	}else {#report the errors on the form:
		echo '<h1>Error!</h1>
		
		<p class = "error">The following errors were detected:<br />';
		
		foreach ($errors as $msg) {#print each error
		echo "- $msg<br />\n";
		}
		echo '</p> <p>Please try again.</p><p><br /></p>';
		
	}#end of if(empty($errors) conditional
	
	mysqli_close($dbc);
	
}#end of main submit conditional
?>

<h1>Change your password:</h1>

<form action = "changePassword.php"
			method = "post">

			<p>Class:
				<select name = "class">
					<option value = ""> ---</option>
					<option value = "4A">4A</option>
					<option value = "4B">4B</option>
					<option value = "4C">4C</option>
					<option value = "4D">4D</option>	
				</select>
			</p>
			
			<p>Number:<input type ="text"
														name = "classNumber"
														size = "2"
														maxlength ="2" /></p>
														
			<p>Old Password: <input type = "password"
															name = "pass"
															size = "10"
															maxlength = "20" /> </p>
									
			<p>New Password: <input type = "password"
														name = "newPass1"
														size = "10"
														maxlength = "20" /> </p>
														
			<p>New password again: <input type = "password"
																			  name = "newPass2"
																			  size = "10"
																			  maxlength = "20" /></p>
																			  
			<p><input type = "submit" 
							  name = "submit"
							  value = "Change Password" /> </p>
							  
			<input type = "hidden"
						name = "submitted"
						value = "TRUE" /> 
																			  	
</form>

<?php 
include_once 'footer.html';
?>