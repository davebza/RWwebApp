<?php
#include the master variables, and set the look of the page:
session_start();

if (!isset($_SESSION['agent']) OR ($_SESSION['agent'] != md5($_SERVER['HTTP_USER_AGENT'] ) )){
	
	#need the functions to create an absolute URL:
	require_once ('loginFunctions.php');
	$url = absoluteUrl();
	header("Location: $url");
	exit();#exit the script
}

include_once 'masterVariables.php';
$PageTitle = "New GR Worksheet Creation V.3: ".ucfirst($grp);
include('header.php');

print_r($_POST);
if (!isset($counter)){
	$counter = 1;
}

 function make_question($counter, $answerSheet, $dbc){
 	#Create the query to get the questions, descriptions and options from the $answerSheet variable and run it
 	
 	$q = "SELECT  * FROM  $answerSheet WHERE `questionNumber` = $counter";
 	$r = mysqli_query($dbc, $q);
 	
 	#Use the description column to choose the question elements, and a counter to keep track of the question numbers:
 	$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
 	#$answer = $row['correctAnswer'];
 	
 	if ($row['description'] == 'mc'){
 		
 		echo $row['questionNumber']. '.) '. $row['questionText'] . "<br />";
 		?><p><input type = "radio"
 			   			name="<?php echo $row['questionNumber'] ?>" id="opt1" value="<?php echo $row['option1'] ?>"/> <?php echo $row['option1'] ?><br /> 
 							<input type = "radio"
 			   			name="<?php echo $row['questionNumber'] ?>" id="opt2" value="<?php echo $row['option2'] ?>" /> <?php echo $row['option2'] ?><br />
 							<input type = "radio"
 			  	 		name="<?php echo $row['questionNumber'] ?>" id="opt3" value="<?php echo $row['option3'] ?>" /> <?php echo $row['option3'] ?><br />
 						</p><br />
 						<?php
 					#for text input lines
 					}elseif ($row['description'] == 'text'){
 	
 						echo $row['questionNumber']. '.) '. $row['questionText'] . "<br />";
 						?><p><input type = "text"
 						name = "<?php echo $row['questionNumber']?>"
 						size = "<?php echo $row['textSize']?>"
 						maxlength = "<?php echo $row['textSize']?>"
 						value = <?php echo(isset($_POST[$row['questionNumber']])) ? htmlspecialchars($_POST[$row['questionNumber']]) : ' '; ?>></input>
 		</p><br />
 		<?php
 		
 		#if there was a problem:
 		}else
 			{
 				echo "There was a problem with the worksheet creation. Sorry.";
 			}
 			
 }#end of make_question function


#Check for form submission, to make sticky and to validate the input (make sure that there are answers submitted for each question):
if (isset($_POST['submitted'])){
	#set a short variable name for studentId:
	$studentId = $_SESSION['studentId'];

#Drop the table and create it again, in case a student is resubmitting the form:
#$dbq = "DROP TABLE `P4`.`$tableName`;";
#$dbr = mysqli_query($dbc, $dbq);
#Create the table:
$dbq = "CREATE TABLE IF NOT EXISTS `P4`.`$tableName` (
`questionNumber` SMALLINT( 3) NOT NULL AUTO_INCREMENT,
`correct` VARCHAR (200) NOT NULL,
`answer1` VARCHAR( 200 ) NOT NULL ,
PRIMARY KEY (  `questionNumber` )
)	ENGINE = MYISAM ";
$dbr = mysqli_query($dbc, $dbq);

}# end of isset "submitted" 
?>

<fieldset >
<legend><h1><?php echo $weekSheet ?></h1></legend><br />
<form action = "newMakeSheet3.php"
		  method = "post">

<?php
#create a counter that will increment with submission, and will be used to serve only one question number at a time.
# This will also be used to check how many time a student has submitted a wrong answer, giving a possibility of hinting

#Above creates a hidden counter. Code below increments on submission of form

make_question($counter, $answerSheet, $dbc);
echo "end of script counter is $counter";

$q2 = "SELECT   `questionNumber` ,  `description` ,  `questionText` ,  `textSize` ,  `option1` ,  `option2` ,  `option3` , `correctAnswer` FROM  $answerSheet";
$r2 = mysqli_query($dbc, $q2);
print_r($r2);
$checkArray = array();

#Use the description column to choose the question elements, and a counter to keep track of the question numbers:
#$checkArray = mysqli_fetch_array($r2, MYSQLI_ASSOC);
#print_r($checkArray);

$row2 = mysqli_fetch_array($r2, MYSQLI_ASSOC);
print_r($row2);
?>
<input type = "submit"
			value = "Check your answers" />

<input type = "hidden" name="submitted" value = "TRUE" />
   
<?php $cnt = isset($_POST["cnt"]) ? $_POST["cnt"] : 0; ?>
<?php
#Above creates a hidden counter. Code below increments on submission of form
  if(isset($_POST['submitted'])){
   
      $cnt++;
      echo $cnt;
    }
  
?>
<input type="hidden" name="cnt" value="<?php echo $cnt;?>" />
</form>
</fieldset>
			
<?php			
include 'footer.html';
?>

