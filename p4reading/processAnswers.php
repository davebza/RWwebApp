<?php
session_start();
if (!isset($_SESSION['agent']) OR ($_SESSION['agent'] != md5($_SERVER['HTTP_USER_AGENT'] ) ))
 {
	
	#need the functions to create an absolute URL:
	require_once ('loginFunctions.php');
	$url = absoluteUrl();
	header("Location: $url");
	exit();#exit the script
}

#Include the master variables:
require_once 'masterVariables.php';

#Set the look of the page:
$PageTitle = "Answers: G/R Worksheet 1";
include_once 'header.php';

#set a short variable name for studentId:
$studentId = $_SESSION['studentId'];

#Drop the table and create it again, in case a student is resubmitting the form:
$dbq = "DROP TABLE `P4`.`$tableName`;";
$dbr = mysqli_query($dbc, $dbq);
#Create the table:
$dbq = "CREATE TABLE `P4`.`$tableName` (
`questionNumber` SMALLINT( 3) NOT NULL AUTO_INCREMENT,
`answer` VARCHAR( 200 ) NOT NULL ,
`correct` VARCHAR (200) NOT NULL,
PRIMARY KEY (  `questionNumber` )
) ENGINE = MYISAM ;";
$dbr = mysqli_query($dbc, $dbq);

#Create a variable for the score:
$score = 0;
#print_r($_POST);

#Create an array of the submitted answers called $answers:

$answers= array();
foreach($_POST AS $value)
{
   $answers[] = $value;
}
#print_r($answers);


#Create an array of the correct answers from the database:
#Connect to the database and get the column from the table:
$q = "SELECT correctAnswer FROM `$answerSheet`";
$r = mysqli_query($dbc, $q);

#intialize the array as $correctAnswers:.
$correctAnswers = array();
while ($row = mysqli_fetch_array($r, MYSQL_NUM)) 
{
    $correctAnswers[] = $row[0];  
}
#print_r($correctAnswers);

#Create the page head
echo "<h1>Check your answers:</h1>";

#Create an array of the submitted answers and the correct answers as key - value pairs:
$checkArray = array_combine($answers, $correctAnswers);
#print_r($checkArray);

#insert the values of the checkArray into the database, 
#and check the score by checking if the key and the value are the same:

foreach($checkArray AS $key => $value)
{
$dbq = "INSERT INTO `P4`.`$tableName` ( 
`answer` , `correct`) VALUES 
('$key', '$value')";
$dbr = mysqli_query($dbc, $dbq);
    if ($key == $value)
    {
    	$score = $score +1;
    }
}

#output the score to the student's browser:
echo "<p><h2>Well done! Your final score is $score</h2></p>";
		
#Create a Master WS table for the student if it doesn't already exist:
$dbq = "CREATE TABLE  IF NOT EXISTS`P4`.`$masterName` LIKE masterplan";
$dbr = mysqli_query($dbc, $dbq);

#Make a database query to put these values into the student's Master GR table:
$dbq = "INSERT INTO  `$masterName` (
 `wsName` ,
 `totalMarks` ,
 `dateCompleted`)
VALUES
 ('$tableName',  '$score', 
CURRENT_TIMESTAMP
)";

$dbr = mysqli_query($dbc, $dbq);

#Finish the look:
include_once 'footer.html';
?>