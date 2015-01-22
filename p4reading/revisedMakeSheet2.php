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

#function declarations go below:

function getCompletedQuestion($dbc, $masterName, $tableName){

	#Get the last correctly answered question number from the database:
	$getQuery = "SELECT (`questionCompleted`) from `$masterName` WHERE `wsName` = '$tableName' LIMIT 1";
	$runGetQuery = mysqli_query($dbc, $getQuery);
	$fetchedCompletedQuestion = mysqli_fetch_array($runGetQuery, MYSQLI_NUM);
	$completedQuestion = $fetchedCompletedQuestion[0];

	mysqli_free_result($runGetQuery);
	return $completedQuestion;
}

function getNumberOfQuestions($dbc, $answerSheet){

	$totalQuestionNumbersQuery = "SELECT COUNT(`questionNumber`) FROM $answerSheet";
	$totalQuestionNumberRun = mysqli_query($dbc, $totalQuestionNumbersQuery);
	$fetchedNumberOfQuestions = mysqli_fetch_array($totalQuestionNumberRun, MYSQLI_NUM);
	$numberOfQuestions = ($fetchedNumberOfQuestions[0]);

	mysqli_free_result($totalQuestionNumberRun);
	return $numberOfQuestions;
}

#This function outputs the question to the browser of the student
function makeQuestion($dbc, $answerSheet, $questionNumber, $numQ){

	$q = "SELECT  `questionNumber` ,  `description` ,  `questionText` ,  `textSize` ,  `option1` ,  `option2` ,  `option3`
	FROM  $answerSheet WHERE `questionNumber` = '$questionNumber' LIMIT 1";
	$r = mysqli_query($dbc, $q);

	if(!r){
		die("There is no worksheet here this week");
	}else {
		$row = mysqli_fetch_array($r, MYSQLI_ASSOC);

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
						
						#if there is no data for description:
						}else{
							#check to see if the worksheet is finished already:
							if ($questionNumber > $numQ){
								
								echo "<p><h2>You have finished the worksheet for this week. Well done!</h2></p>";
								
								#if not, output an error message:
							}else{
								echo "There was a problem with the worksheet creation. Sorry.";
							}
						}
		}#end of question making conditional
	
}#end function makeQuestion

#This function gets the last student submitted answer and returns it:
function getTheStudentAnswer($dbc, $workingTable, $answerSheet, $questionNumber){

	#get the submitted answers from the student worksheet table called $workingTable:
	$getAnswers = "SELECT * FROM $workingTable ORDER BY attemptNumber DESC LIMIT 1";
	$getAnswersRun = mysqli_query($dbc, $getAnswers);

	if(!$getAnswersRun){
	die("Problem getting the Student submitted answers from the database in function getTheStudentAnswer()!");
	}

	#get the key number of the last answer of the student answers table $workingTable. If there are no answers submitted, make the question:
	$answersArray = mysqli_fetch_array($getAnswersRun, MYSQLI_ASSOC);
	$lastStudentAnswer = $answersArray[studentAnswer];
	mysqli_free_result($getAnswersRun);
	return $lastStudentAnswer;
}#end of getTheStudentAnswer()

#this function gets the teacher submitted answer and returns it
function getTheTeacherAnswer($dbc, $questionNumber, $answerSheet){
	
	$getAnswers = "SELECT * FROM $answerSheet WHERE questionNumber = $questionNumber LIMIT 1";
	$getAnswersRun = mysqli_query($dbc, $getAnswers);
	
	if(!$getAnswersRun){
		die("Problem getting the Student submitted answers from the database in function getTheTeacherAnswer()!");
	}
	
	$answersArray = mysqli_fetch_array($getAnswersRun, MYSQLI_ASSOC);
	$teacherAnswer = $answersArray[correctAnswer];
	mysqli_free_result($getAnswersRun);
	return $teacherAnswer;
	
}#end of getTheTeacherAnswer()

#This function gets the question type and returns it, and could be combined with the function above to return an array to be economical:
function getTheQuestionType($dbc, $questionNumber, $answerSheet){
	
	$getQuestionType = "SELECT description FROM $answerSheet WHERE questionNumber = $questionNumber LIMIT 1";
	$getQuestionTypeRun = mysqli_query($dbc, $getQuestionType);
	
	if(!$getQuestionTypeRun){
		die("Problem getting the Student submitted answers from the database in function getTheAnswer()!");
	}
	
	$questionTypeArray = mysqli_fetch_array($getQuestionTypeRun, MYSQLI_ASSOC);
	$questionType = $questionTypeArray[description];
	mysqli_free_result($getQuestionTypeRun);
	return $questionType;
}#end of getQuestionType()

#this function inserts the student answer to the database $workingtable
function insertStudentAnswer($dbc, $workingTable, $suppliedAnswer){
	
	$insertQuery = "INSERT INTO $workingTable(`studentAnswer`) VALUES ('$suppliedAnswer')";
	$insertQueryRun = mysqli_query($dbc, $insertQuery);
	
	if(!InsertQueryRun){
		echo "Insertion of student data to the student's question table failed. Sorry";
	}
}

#This function will create the masterWS table and also check to see whether the start values for a new worksheet are in the MasterWS table. If not, it will insert the appropriate values.
function masterWorksheetOperations($dbc, $masterName, $tableName){
	
	#Create a Master WS table for the student if it doesn't already exist:
	$dbq = "CREATE TABLE  IF NOT EXISTS $masterName LIKE masterplan";
	$dbr = mysqli_query($dbc, $dbq);
	
	#Check to see if the correct intial values for the question Number are set, and if not, set them:
	$initialCheck = "SELECT `number` FROM $masterName WHERE  `wsName` =  '$tableName' LIMIT 1";
	$initialCheckRun = mysqli_query($dbc, $initialCheck) or die("initial check failed to run in function masterWorksheetOperations");

	$initialCheckArray = mysqli_fetch_array($initialCheckRun, MYSQLI_ASSOC);
	
	
	if(!empty($initialCheckArray)){
		return;
	}else{
		
		$initialValueInsert = "INSERT INTO `$masterName` (`wsName`, `questionCompleted`) VALUES ('$tableName', '1') ";
		$initialValueInsertRun = mysqli_query($dbc, $initialValueInsert) or die("initial value insertion failed to run in function masterWorksheetOperations");
		
		if($initialValueInsertRun) {
			echo "Initial values inserted. Good to go.";
		}
	}
}#end of function masterWorksheetOperations()

#begin making the ws. First, make the Master GR table
masterWorksheetOperations($dbc, $masterName, $tableName);

#get the last completed question
$questionNumber = getCompletedQuestion($dbc, $masterName, $tableName);#this value will have to be stored in the student's master GR table, and retrieved at the start of the worksheet

#make the working table
$workingTable = $tableName."Q".$questionNumber;
#echo "Working Table is $workingTable";

#create a $workingTable for the question if it doesn't already exist:
$dbq = "CREATE TABLE IF NOT EXISTS $workingTable (
`attemptNumber` SMALLINT( 4) NOT NULL AUTO_INCREMENT,
`studentAnswer` VARCHAR (200) NOT NULL,
PRIMARY KEY (  `attemptNumber` )
)	ENGINE = MYISAM ";
$dbr = mysqli_query($dbc, $dbq);

#Check for form submission, to make sticky and to validate the input (make sure that there are answers submitted for each question):
if (isset($_POST['submitted'])){
	
	#set a short variable name for studentId:
	$studentId = $_SESSION['studentId'];
	#set a short variable for the student answer:
	$suppliedAnswer = mysql_real_escape_string($_POST[$questionNumber]);
	#insert _POST[$questionNumber] to Workingtable:	
	
	insertStudentAnswer($dbc, $workingTable, $suppliedAnswer);
	
	if(!InsertQueryRun){
		echo "Insertion of student data to the student's question table failed. Sorry";
	}
	
}#end of if isset post submitted condtional

#html form goes below
?>

<fieldset >
<legend><h1><?php echo $weekSheet ?></h1></legend><br />
<form action = "revisedMakeSheet2.php"
		  method = "POST">
		  
<?php #Find out how many questions there are in the teacher created worksheet called $answerSheet:

#get the total number of questions:
$numQ = getNumberOfQuestions($dbc, $answerSheet);

#check to see if the student has completed all questions or the worksheet. If yes, end the script:
if ($questionNumber > $numQ){
	echo "<p><h2>You have finished the worksheet for this week. Well done!</h2></p>";
	include_once 'footer.html';
	return;
}

#Get the student's last answer, if any
$lastStudentAnswer = getTheStudentAnswer($dbc, $workingTable, $answerSheet, $questionNumber);
#Get the teacher answer:
$teacherAnswer = getTheTeacherAnswer($dbc, $questionNumber, $answerSheet);
#get the question type:
$questionType = getTheQuestionType($dbc, $questionNumber, $answerSheet);

#Answer checking conditional:
if(!isset($lastStudentAnswer)){#If there is no student answer yet:
	
	makeQuestion($dbc, $answerSheet, $questionNumber, $numQ);
	
}elseif (htmlspecialchars($lastStudentAnswer) != htmlspecialchars($teacherAnswer)){#if the student answer is different from the teacher answer

	if($questionType == "textArea"){#first, check if it's a text area question, and there is no teacher answer:
		
		if(!isset($_POST['editTextArea'])){#If the student has not yet edited th question, that is, this is the second time to see the question - and the function should eb changed to allow for first answer to be sticky:
		
		echo "<p><h2>Here is your answer to question $questionNumber:</h2></p>";
		echo "<p>".htmlspecialchars($lastStudentAnswer)."</p>";
		echo "You can change it below, or just hit the button if you want to keep it the same.";
		
		makeQuestion($dbc, $answerSheet, $questionNumber, $numQ);

		?>	<input type = "hidden" name="editTextArea" value = "TRUE" />
		
		<input type = "submit"
			class = "button orange submit"
			value = "Check your answers" />

		<input type = "hidden" name="submitted" value = "TRUE" />
						
		<?php		
		return;
		}elseif (isset($_POST['editTextArea'])){#the student has edited the question already seen and confirmed the answer:
			
			$questionNumber ++;
			
			#update the number of questions that are finished in the masterGR table for the student:
			$updateQuestionCompletedQuery = "UPDATE $masterName SET questionCompleted = $questionNumber WHERE wsName = '$tableName' LIMIT 1";
			$updateQuestionCompletedQueryRun = mysqli_query($dbc, $updateQuestionCompletedQuery);
		
				if (!$updateQuestionCompletedQueryRun){
					echo "There was a problem getting the next question in the finished edit conditional";
				}
		
				
		}#end of edit if
	
	}#end of textArea if
	
	makeQuestion($dbc, $answerSheet, $questionNumber, $numQ);
	
}elseif(htmlspecialchars($lastStudentAnswer)== htmlspecialchars($teacherAnswer)){#if the answers are the same:
	#increment the $questionNumber variable

	$questionNumber ++;
	if ($questionNumber > $numQ){
		echo "<p><h2>You have finished the worksheet for this week. Well done!</h2></p>";
		return;
	}elseif ($questionNumber <= $numQ){
		
	
	#update the number of questions that are finished in the masterGR table for the student:
	$updateQuestionCompletedQuery = "UPDATE $masterName SET questionCompleted = $questionNumber WHERE wsName = '$tableName' LIMIT 1";
	$updateQuestionCompletedQueryRun = mysqli_query($dbc, $updateQuestionCompletedQuery);
	
	if (!$updateQuestionCompletedQueryRun){
		die ("couldn't update the new question completed after doing the answer check");
	}
	
	#make the new question
	makeQuestion($dbc, $answerSheet, $questionNumber, $numQ);
}else {
	
	echo "There was a problem in the question check. Sorry.";
	
}#end of answer checking conditional
}

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
include_once 'footer.html';

?>

