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
	$PageTitle = "Teacher Dashboard";
	include('header.php');
	
	if(substr($_SESSION['class'], 1,2)!= "T"){
		die("You are not authorized to view this page!");
	}
	

#Display the book borrowing data for a selected class or student, depending on what was filled in:	
if ($_POST['hidden'] == 'TRUE'){

	#check to see whether we should be displaying the full class records, ie if gradeClass is empty or not in $_POST
	if (!empty($_POST['gradeClass'])){
		
		#Make the short variable and uppercase the letters:
		$gradeClass = strtoupper($_POST['gradeClass']);
		
		#use the value of $_POST['gradeClass'] to sleect database and class to view:
		
		#first, check which grade and class:
		if (substr($_POST['gradeClass'], 0, 1) == '4'){
		include 'mysqliConnectP4.php';
		$dBase = "P4";
		$table = "4all";
		
		}elseif (substr($_POST['gradeClass'], 0, 1) == '5'){
		
		include 'mysqliConnectP5.php';
		$dBase = "P5";
		$table = "5all";
		
		}
		#Make a query to get information on home reading:
		$dbq = "SELECT (
		
				SELECT CONCAT( `class` , `classNumber` )
				) AS `Class Number` , (
		
				SELECT CONCAT( `firstName` , ' ', `lastName` )
				) AS `Name` , (
		
				SELECT `grp`) AS `Group` , (
				SELECT `readingLevel`) AS `HR Level`, (
				SELECT `lastHRBorrowing`) AS `Date of last book borrowing`, (
				SELECT `lastHRBookTitle`) AS `Book title`, (
				SELECT `lastHRReturned`)AS `Returned?`
		
				FROM `$dBase`.`$table` WHERE `class` = '$gradeClass' ";
		
						$dbr = mysqli_query($dbc, $dbq) or die("Dbase connect error.");
		
						if($dbr) {# if the query ran ok
		
						#get headers for table
						$headers = mysqli_num_fields($dbr);
		
						#output headers:
		?><table><?php echo "<h1>Home Reading Records: $gradeClass</h1>";
								?><tr><?php 	
									for($i=0; $i<$headers; $i++){
											
										$field = mysqli_fetch_field($dbr);
										echo "<th><a href = '#'>{$field->name}</a></th>";
									}
									echo "</tr>\n";
									#output row data:	
									while($row = mysqli_fetch_row($dbr)){
								    
										echo "<tr>";
								
								   	 // $row is array... foreach( .. ) puts every element
								   	 // of $row to $cell variable
								   	 foreach($row as $cell){
								    	    echo "<td>$cell</td>";
								    		}
								  	  echo "</tr>\n";
									}
							?></table><?php					
									mysqli_free_result($dbr);
								
				}#end if result condition
	}#end of: if (!empty($_POST['gradeClass'])) condition
	
	#conditional to check for individual student:
	if (!empty($_POST['studentId'])){
		
		#make short variables:
		$studentId = $_POST['studentId'];
		$studentId = strtoupper($studentId);
		echo $studentId;
		
		#display the student's reading record as a table for the teacher to see if needed:
		
		$dbq = "SELECT `numberOfTimes` AS `Number of Borrowings`, `bookId` AS `Book Code`, `dateOut` AS `Date Borrowed`, `returned` AS `Date Returned` FROM  `studentHomeReading`.`$studentId`";
		$dbr = mysqli_query($dbc, $dbq) or die("Couldn't check whether the book has already been entered into the database for the second time in the script. Is _POST[StudentId] empty?");
		
		if($dbr) {# if the query ran ok
		
			#get headers for table
			$headers = mysqli_num_fields($dbr);
		
			#output headers:
			?><table><?php echo "<h1>Student Borrowing Record: $studentId</h1>";
						?><tr><?php 	
							for($i=0; $i<$headers; $i++){
										
								$field = mysqli_fetch_field($dbr);
								echo "<th><a href = '#'>{$field->name}</a></th>";
							}
							echo "</tr>\n";
							#output row data:	
							while($row = mysqli_fetch_row($dbr)){
							    
								echo "<tr>";
							
							    // $row is array... foreach( .. ) puts every element
							    // of $row to $cell variable
							    foreach($row as $cell){
							        echo "<td>$cell</td>";
							    }
							    echo "</tr>\n";
							}
				?></table><?php					
							mysqli_free_result($dbr);
							
						}#end if result condition
	}#end of individual student check
	
}#end of checking for class or student
	
#Form inputs for Home Reading data:
?>
<fieldset>
<form action ="teacherDash.php"
		method = "post">
		
		<p>Which class would you like to view:
			<input type = "text"
		   		name = "gradeClass" autofocus
		   		id    = "gradeClass"
		  		size = "2"
		  		maxlength = "2"
		   		value = "" />
		</p>
		
		<p>Which student record would you like to view:
			<input type = "text"
		   		name = "studentId" autofocus
		   		id    = "studentId"
		  		size = "4"
		  		maxlength = "4"
		   		value = "" />
		</p>
		
		 <p><input type = "submit"
							class = "button orange"
							name = "submit"
							value = "Check records" /></p>
		
		<input type = "hidden"
						name = "hidden"
						value = "TRUE" />
		
</form>		
</fieldset>


<?php

makeWsOutputTable($dbc, '4x2mod3gr1b');

makeTableOutput($dbc, '5all', '5A', 'yellow');
makeTableOutput($dbc, '4all', 'B', 'yellow');
makeTableOutput($dbc, '4all', '4C', 'yellow');
makeTableOutput($dbc, '4all', '4D', 'yellow');

makeTableOutput($dbc, '5all', '5A', 'blue');
makeTableOutput($dbc, '4all', '4B', 'blue');
makeTableOutput($dbc, '4all', '4C', 'blue');
makeTableOutput($dbc, '4all', '4D', 'blue');

makeTableOutput($dbc, '5all', '5A', 'red');
makeTableOutput($dbc, '4all', '4B', 'red');
makeTableOutput($dbc, '4all', '4C', 'red');
makeTableOutput($dbc, '4all', '4D', 'red');

include 'footer.html';
?>