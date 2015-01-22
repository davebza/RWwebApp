<?php
#The user is redirected here from login.php
session_start();

#if there is no session set, redirect to loginIndex.php

if (!isset($_SESSION['agent']) OR ($_SESSION['agent'] != md5($_SERVER['HTTP_USER_AGENT'] ) )) {
	
	#need the functions to create an absolute URL:
	require_once ('loginFunctions.php');
	$url = absoluteUrl();
	header("Location: $url");
	exit();#exit the script
}

#Set the page title and include the html header:
$PageTitle = "Hello {$_SESSION['firstName']}!";

include_once ('header.php');
include('masterVariables.php');

switch (substr($_SESSION['class'], 1, 2)) {
	
	case 'T':
		echo "<h1>Class Overview</h1>";
		
		makeTableOutput($dbc, '4all', '4A', 'yellow');
		makeTableOutput($dbc, '4all', '4A', 'blue');
		makeTableOutput($dbc, '4all', '4A', 'red');
		break;
		
	case 'A'||'B'||'C'||'D':
		
		#Print a customized message:
		echo "<h1> Welcome, {$_SESSION['firstName']}.</h1>
		
		
		<p><h2>Here is your information: </h2></p>
		
		<p><ul>
		<li> Class: {$_SESSION['class']} Number: {$_SESSION['classNumber']}</li>
		<li> Name: {$_SESSION['firstName']} {$_SESSION['lastName']}</li>
		<li> Group: {$_SESSION['grp']}</li>
		</ul>
		</p>";
		
		
		#first, check which grade and class:
		if (substr($classInfo, 0, 1) == '4'){
			include 'mysqliConnectP4.php';
			$dBase = "P4";
			$table = "4all";
		
		}elseif (substr($classInfo, 0, 1) == '5'){
		
			include 'mysqliConnectP5.php';
			$dBase = "P5";
			$table = "5all";
		
		}
		
		$dbq = "SELECT `numberOfTimes` AS `Number of Borrowings`, `bookId` AS `Book Code`, `dateOut` AS `Date Borrowed`, `returned` AS `Date Returned` FROM  `studentHomeReading`.`$classInfo`";
		$dbr = mysqli_query($dbc, $dbq) or die("Couldn't check whether the book has already been entered into the database for the second time in the script.");
		
		if($dbr) {# if the query ran ok
		
			#get headers for table
			$headers = mysqli_num_fields($dbr);
		
			#output headers:
			?><table><?php echo "<h1>Book Borrowing Record: $classInfo</h1>";
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
		
		echo "<p>You can now choose your worksheet, practice your sightwords or look at the worksheets you have already finished, or logout.</p>
		
		<p><a href=\"logout.php\" class = \"button orange\">Logout</a></p>";
		
		break;
		
		
	default:
		echo "There seems to be a problem, loggedIn.php can't decide what class you are in, or if you are a teacher.";
		break;
}# end of switch grp
?>

<?php
include_once 'footer.html';
?>