<?php
//This page prints any errors with the login process
//and creates the entire login page, including the form
session_start();
#include the header:
$PageTitle = "Log in";
include_once 'header.php';

#The error messages, if they exist:

if (!empty($errors)) {
	echo '<h1>Error!
	
	<p class="error"> The following error(s) occurred:<br />';
	foreach ($errors as $msg){
		echo " - $msg<br />\n";
	}
	echo "</p><p>Please try again.</p>";
}

# Display the form:
?>
<h1>Please log in</h1>
<fieldset>
<form action= "login.php" 
			method = "post">
			
			<p>
				<input type="radio" name="sex" value="male" /> Male<br />
				<input type="radio" name="sex" value="female" /> Female
			</p>
			
			<p>Class:
			<select name = "class">
				<option value = ""> --- </option>
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
														
			<p>Password <input type ="password"
														name = "pass"
														size = "20"
														maxlength ="80" /></p>
														
			<p><input type = "submit"
							class="button orange"
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