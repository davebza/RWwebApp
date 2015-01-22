<?php
//This page prints any errors with the login process
//and creates the entire login page, including the form
session_start();
#include the header:
$PageTitle = "Log in";
include_once 'loginHeader.php';

#The error messages, if they exist:

if (!empty($errors)) {
	echo '<h1>Error!</h1>
	
	<p class="error"> <h2>The following error(s) occurred:<br />';
	foreach ($errors as $msg){
		echo " - $msg<br />\n";
	}
	echo "</p><p>Please try again.</h2></p>";
}

# Display the form:
?>
<h1>Please log in</h1>
<fieldset>
<form action= "login.php" 
			method = "post">
			
			<p>Grade Level:<br/>
				<input type="radio" name="gradeLevel" value="4" /> P4</input><br />
				<input type="radio" name="gradeLevel" value="5" /> P5</input><br />
				<input type="radio" name="gradeLevel" value="T" /> Teacher</input>
			</p>
			
			<p>Class:
			<select name = "class">
				<option value = ""> ---</option>
				<option value = "A">A</option>
				<option value = "B">B</option>
				<option value = "C">C</option>
				<option value = "D">D</option>	
				<option value = "T">Teacher</option> //This needs to be changed. Teachers should login by radio button and class number.
				<option value = "X">Test</option>
			</select>
			</p>
			
			<p>Number:<input type ="text"
														name = "classNumber"
														size = "2"
														maxlength ="2" /></p>
														
			<p>Password <input type ="password"
														name = "pass"
														size = "20"
														maxlength ="80" /></p>
														
			<p><input type = "submit"
							class = "button orange"
							name = "submit"
							value = "Log in" /></p>
							
			<input type = "hidden"
						name = "submitted"
						value = "TRUE" />
</form>
</fieldset>

<?php 
include_once 'footer.html';
?>