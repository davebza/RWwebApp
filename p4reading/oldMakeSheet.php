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
$PageTitle = "Guided Reading Worksheet: ".ucfirst($grp);
include('header.php');


#Check for form submission, to make sticky and to validate the input (make sure that there are answers submitted for each question):
if (isset($_POST['submitted'])){
	#set a short variable name for studentId:
	$studentId = $_SESSION['studentId'];

	#create a table for the worksheet if it doesn't already exist:
	$dbq = "CREATE TABLE IF NOT EXISTS $tableName (
	`questionNumber` SMALLINT( 3) NOT NULL AUTO_INCREMENT,
	`correct` VARCHAR (200) NOT NULL,
	`answer1` VARCHAR( 200 ) NOT NULL ,
	PRIMARY KEY (  `questionNumber` )
	)	ENGINE = MYISAM ";
	$dbr = mysqli_query($dbc, $dbq);
   
	#Check for the column number:
	$columnExistsCheck = 0;
	$columnNumber = 1;
	$columnExistsCheckName = "answer".$columnNumber;
	
	do {
		$columnQuery = "SELECT $columnExistsCheckName FROM $tableName";
		@$columnQueryRun = mysqli_query($dbc, $columnQuery);
		@$columnCheckArray = mysqli_fetch_array($columnQueryRun, MYSQLI_NUM);
		#print_r($columnCheckArray);
		if (isset($columnCheckArray[0])) {
			#echo "Column exists!";
			$columnNumber = $columnNumber + 1;
			#echo $columnNumber;
			$columnExistsCheckName = "answer".$columnNumber;
			$columnExistsCheck = 1;
		}elseif (!isset($columnCheckArray[0])) {
			$columnUpdate = "ALTER TABLE $tableName ADD $columnExistsCheckName VARCHAR( 200 ) NOT NULL";
			$columnQueryRun = mysqli_query($dbc, $columnUpdate);
			$columnExistsCheck = 0;
		}
	} while ($columnExistsCheck ===1);
	
	
	#calculate the numbers of the questions the student has given by copying from $_POST and removing the last two hidden (submitted and cnt) items through array_pop
	#then initialize a new array of the key values from $theAnswerArray called $questionNumbers
	$theAnswerArray= $_POST;
	array_pop($theAnswerArray);
	array_pop($theAnswerArray);
	$questionNumbers = array_keys($theAnswerArray);
	#Create a variable for the score:
	$score = 0;
	
	#use array_shift to remove the first number of the answered question and use it to compare student answer and teacher answer. The variable is $loopPointer
	while(!empty($questionNumbers)){
		$loopPointer = array_shift($questionNumbers);
		$studentSubmittedAnswer = array();
		
		#set the array of student submitted answers to a key of the question number (determined by the loopPointer) and the $_POST item corresponding to that item:
		$studentSubmittedAnswer[$loopPointer] = $_POST[$loopPointer];

		#get the given answer from the sheet with teacher provided questions and answers:
		$q = "SELECT correctAnswer FROM $answerSheet WHERE questionNumber = $loopPointer LIMIT 1";
		$r = mysqli_query($dbc, $q);
		$teacherAnswer = mysqli_fetch_array($r, MYSQLI_NUM);
		
		#compare student and teacher answers
		if (htmlspecialchars($studentSubmittedAnswer[$loopPointer])== htmlspecialchars($teacherAnswer[0])) {
				$score ++;
			}
			$EscapedstudentSubmittedAnswer[$loopPointer] = mysql_real_escape_string($studentSubmittedAnswer[$loopPointer]);
			#insert the question number, student answer and teacher answer into the database. If there is already a row for that question, update the row by adding a new column
			#for the student answer:
			$dbq = "INSERT INTO $tableName (questionNumber, correct, answer1 ) VALUES ('$loopPointer', '$teacherAnswer[0]', '$EscapedstudentSubmittedAnswer[$loopPointer]')
			ON DUPLICATE KEY UPDATE questionNumber = '$loopPointer', $columnExistsCheckName = '$EscapedstudentSubmittedAnswer[$loopPointer]', correct = '$teacherAnswer[0]'";
			$dbr = mysqli_query($dbc, $dbq);
			
			if (!$dbr) {
				die('the update connection failed'.mysql_error());
			}
		
	}#end of while(!empty($questionNumbers)) loop
	
	#Create the after-submission page head
	echo "<h1>Your marks:</h1>";
	
			#output the score to the student's browser:
			echo "<p><h2>Well done! You got $score marks.</h2></p>";
	
			#Create a Master WS table for the student if it doesn't already exist:
			$dbq = "CREATE TABLE  IF NOT EXISTS $masterName LIKE masterplan";
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
} #This bracket ends the if isset $_POST submitted.

?>

<fieldset >
<legend><h1><?php echo $weekSheet ?></h1></legend><br />
<form action = "makeSheet.php"
		  method = "POST">

<?php
#Create the query to get the questions, descriptions and options from the $answerSheet table variable and run it

$q = "SELECT  `questionNumber` ,  `description` ,  `questionText` ,  `textSize` ,  `option1` ,  `option2` ,  `option3` 
FROM  $answerSheet";
$r = mysqli_query($dbc, $q);

if(!r){
	die("There is no worksheet here this week");
}

#Use the description column to choose the question elements, and a counter to keep track of the question numbers:
while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC))
{
	switch ($_POST['submitted']){
		
		case 'TRUE':

		 #get the row in the answer comparison table:
		$rqn = $row['questionNumber'];
		$compareQuery = "SELECT * FROM `$tableName` WHERE `questionNumber` = $rqn";
		$compareQueryRun = mysqli_query($dbc, $compareQuery);
		$compareAnswers = mysqli_fetch_array($compareQueryRun, MYSQLI_ASSOC);
		$compareAnswers = @array_filter($compareAnswers);
		$answerCheckArray =  @array_count_values($compareAnswers);
		#echo $compareAnswers['correct'];
		if ($answerCheckArray[$compareAnswers['correct']] > 1){
			break;
		}elseif ($row['description'] == 'textArea') {
			#this is for student input where there is no clear answer - it will display the last submitted answer.
			echo $row['questionNumber']. '.) '. $row['questionText'] . "<br /> <br />";
			echo "Your answer is this: <br /> <br />";
			#this will connect to the db and fetch the last answer: Needs work, 'cos if the last answer is empty this will display whitespace.
			$repeatQuery = "SELECT * FROM $tableName WHERE questionNumber = $rqn LIMIT 1";
			$repeatQueryRun = mysqli_query($dbc, $repeatQuery);
			$repeatAnswer = mysqli_fetch_array($repeatQueryRun, MYSQLI_NUM);
			#remove the first two values (question number and blank space for correct answer) from the array
			array_shift($repeatAnswer);
			array_shift($repeatAnswer);
			#because the array starts at element 0, remove one more item from the count number:
			$repeatCount = count($repeatAnswer) - 1;
			#Find the last answer that was submitted, ignoring any left blank.
			while(empty($repeatAnswer[$repeatCount]) && $repeatCount > 0){
				$repeatCount = $repeatCount -1;
				}
			#display the answer to the browser. 
			echo "$repeatAnswer[$repeatCount]" . "<br /> <br />";
			echo "You can change your answer below, if you want to. <br /> <br />";
			#allow for editing:	
				#echo $row['questionNumber']. '.) '. $row['questionText'] . "<br />";
				?><p><textarea
										name = "<?php echo $row['questionNumber']?>"
										rows = "5"
										cols = "100"
										wrap = "soft"
										value = <?php echo(isset($_POST[$row['questionNumber']])) ? htmlspecialchars($_POST[$row['questionNumber']]) : ' '; ?>></textarea>
						</p><br />
						
			<?php 
			break;
		}#end of textArea check loop
		
		default:#this makes the questions:
			
				?> <p class = "question"> <?php
				
				#for multiple choice:
				if ($row['description'] == 'mc'){
					echo $row['questionNumber']. '.) '. $row['questionText'] . "</p>";
					?><p><input type = "radio"
		   			name="<?php echo $row['questionNumber'] ?>" id="opt1" value="<?php echo htmlspecialchars($row['option1']) ?>"/> <?php echo $row['option1'] ?><br /> 
						<input type = "radio"
		   			name="<?php echo $row['questionNumber'] ?>" id="opt2" value="<?php echo htmlspecialchars($row['option2']) ?>" /> <?php echo $row['option2'] ?><br />
						<input type = "radio"
		  	 		name="<?php echo $row['questionNumber'] ?>" id="opt3" value="<?php echo htmlspecialchars($row['option3']) ?>" /> <?php echo $row['option3'] ?><br />
					</p><br />
					<?php
				
					#for picture mc questions
					
					}elseif ($row['description'] == 'img'){
						echo $row['questionNumber']. '.) '. $row['questionText'] . "</p>";
						?><p><input type = "radio"
						  name="<?php echo $row['questionNumber'] ?>" id="opt1" value="<?php echo htmlspecialchars($row['option1']) ?>"/> <?php echo $row['option1'] ?><br /> 
							<input type = "radio"
						name="<?php echo $row['questionNumber'] ?>" id="opt2" value="<?php echo htmlspecialchars($row['option2']) ?>" /> <?php echo $row['option2'] ?><br />
							<input type = "radio"
						name="<?php echo $row['questionNumber'] ?>" id="opt3" value="<?php echo htmlspecialchars($row['option3']) ?>" /> <?php echo $row['option3'] ?><br />
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
		
				#For textArea questions with no fixed answer:
				
				}elseif ($row['description'] == 'textArea'){
					echo $row['questionNumber']. '.) '. $row['questionText'] . "<br />";
					?><p><textarea
							name = "<?php echo $row['questionNumber']?>"
							rows = "5"
							cols = "100"
							wrap = "soft"
							value = <?php echo(isset($_POST[$row['questionNumber']])) ? htmlspecialchars($_POST[$row['questionNumber']]) : ' '; ?>></textarea>
					</p><br />
		
				<?php		
				
				#if there was a problem:
				}else
					{
					echo "There was a problem with the worksheet creation. Sorry.";
					}
	
	}#end of switch for making questions

}#end of while immediately above switch

?>

<input type = "submit"
			class = "button orange submit"
			value = "Check your answers" />

<input type = "hidden" name="submitted" value = "TRUE" />

<?php $cnt = isset($_POST["cnt"]) ? $_POST["cnt"] : 0; ?>
<?php
#Above creates a hidden counter. Code below increments on submission of form
  if(isset($_POST['submitted'])){
   
      $cnt++;
      #echo $cnt;
    }
  
?>
<input type="hidden" name="cnt" value="<?php echo $cnt;?>" />
</form>
</fieldset>

<?php 
include 'footer.html';
?>