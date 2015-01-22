<?php
#This page will store all the variables related to the student's info and the tablenames for the database queries and worksheet names,
#allowing this to be dynamically generated easily.
#This page will be invisible

#Start the session, to import the student info: 
session_start();

#Include the mysql connection information, so that each page can access it:
#First, make sure that the correct connect is given for each grade level:

if (substr($_SESSION['class'], 0, 1)== '4') {
		
		include 'mysqliConnectP4.php';
	
	}elseif (substr($_SESSION['class'], 0, 1)== '5'){
		
		include 'mysqliConnectP5.php';
	
	}elseif (substr($_SESSION['class'], 0, 1)== 'T'){
		include 'mysqliConnectP4.php';
	}

#For P4:
#This is the old standard way, trying a new one below 
#$baseSheet  = 'mod1GR1';
$baseSheet = 'mod3GR1';
#$baseSheet = 'module1gr1';
$weekSheet = 'P4 Module 3 Guided Reading Worksheet 1';

#Create short variable names from the needed session data: 
$userId = $_SESSION['userId'];
$studentId = $_SESSION['studentId'];
$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];
$grp = $_SESSION['grp'];
$class =  $_SESSION['class'];
$classNumber = 	$_SESSION['classNumber'];

#Use these short names to create the needed table and worksheet names:
$classInfo = $class.$classNumber;
#echo " classInfo = $classInfo ";
$masterName = $classInfo."MasterGR";
#echo "Master table name  = $masterName ";

#Use the $grp variable to create $tablename and $worksheet:
switch ($grp) {
	
	case 'yellow':
		$worksheet = $baseSheet.'Y.php';
		$answerSheet = substr($worksheet, 0, -4);
		$tableName = $classInfo.$worksheet;
		$tableName = substr($tableName, 0, -4);
		break;
		
	case 'blue':
		$worksheet = $baseSheet.'B.php';
		$answerSheet = substr($worksheet, 0, -4);
		$tableName = $classInfo.$worksheet;
		$tableName = substr($tableName, 0, -4);
		break;
		
	case 'red':
		$worksheet = $baseSheet.'R.php';
		$answerSheet = substr($worksheet, 0, -4);
		$tableName = $classInfo.$worksheet;
		$tableName = substr($tableName, 0, -4);
		break;
}

function makeWsOutputTable($dbc){
	
	$query  =  "SELECT*
	FROM `4t3mod3gr1r`";
	$result = mysqli_query($dbc, $query);
	
	
	if($result) {# if the query ran ok
	
		#get headers for table
		$headers = mysqli_num_fields($result);
	
		#output headers:
		?><table><?php echo "<h1>4t3mod3gr1r</h1>";
				?><tr><?php 	
					for($i=0; $i<$headers; $i++){
							
						$field = mysqli_fetch_field($result);
						echo "<th><a href = '#'>{$field->name}</a></th>";
					}
					echo "</tr>\n";
				
				#output row data:	
				while($row = mysqli_fetch_row($result)){
				    
					echo "<tr>";
				
				    // $row is array... foreach( .. ) puts every element
				    // of $row to $cell variable
				    foreach($row as $cell){
				        echo "<td>$cell</td>";
				    }
				    echo "</tr>\n";
				}
						
				mysqli_free_result($result);
				
				}#end if result comdition
				
				?> 
				</table>
				<?php
}


function makeTableOutput($dbc, $outputTable, $outputClass, $outputGroup) {
	$query  =  "SELECT `studentId`, `classNumber` AS 'Number', CONCAT(`firstName`,' ', `lastName`) AS 'Name', `readingLevel` as 'Reading Level'
	FROM $outputTable WHERE `class` = '$outputClass' AND `grp` = '$outputGroup' ";
	$result = mysqli_query($dbc, $query);


	if($result) {# if the query ran ok

		#get headers for table
		$headers = mysqli_num_fields($result);

		#output headers:
		?><table><?php echo "<h1>$outputClass: ".ucfirst($outputGroup)." Group</h1>";
			?><tr><?php 	
				for($i=0; $i<$headers; $i++){
						
					$field = mysqli_fetch_field($result);
					echo "<th><a href = '#'>{$field->name}</a></th>";
				}
				echo "</tr>\n";
			
			#output row data:	
			while($row = mysqli_fetch_row($result)){
			    
				echo "<tr>";
			
			    // $row is array... foreach( .. ) puts every element
			    // of $row to $cell variable
			    foreach($row as $cell){
			        echo "<td>$cell</td>";
			    }
			    echo "</tr>\n";
			}
					
			mysqli_free_result($result);
			
			}#end if result comdition
			
			?> 
			</table>
			<?php 
}
#echo "worksheet = $worksheet and tableName = $tableName and answerSheet = $answerSheet";
#echo "StudentId = $studentId and firstName = $firstName and lastName = $lastName and grp = $grp and class = $class and classNumber = $classNumber and baseSheet = $baseSheet";
?>


