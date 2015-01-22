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
#$PageTitle = ucfirst($grp). " Group";
$PageTitle = "Sightwords";
include('header.php');

if (isset($_POST)){
	
	#set the time time taken to do the form:
	$time = (time() - $_SESSION['time_start']);
	
	#create variables to insert data to the student's unique sightWords record tables
	$tableGrade= "sightWordsP".substr(($_SESSION['class']), 0,1);
	$sightLevel ="level1";#this needs to be made into a variable for use across all levels and grades
	$studentSightRecordTable = $classInfo.$sightLevel;
	$studentSightQuestionRecordTable = $classInfo.$_POST[teacherAnswer];
	$errorsTableInfo = $studentSightQuestionRecordTable."Errors";
	
	#create the table in MYSQL if it doesn't already exist:
	$createQ = "CREATE TABLE IF NOT EXISTS `$tableGrade`.`$studentSightRecordTable` (
	`wordNumber` TINYINT( 2 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
	`word`VARCHAR ( 200 )  NOT NULL ,
	`numberOfAttempts` SMALLINT( 4 ) UNSIGNED NOT NULL ,
	`numberCorrect` SMALLINT( 4 ) UNSIGNED NOT NULL ,
	`numberIncorrect` SMALLINT( 4 ) UNSIGNED NOT NULL ,
	PRIMARY KEY (  `wordNumber` ),
	UNIQUE ( `word` ) 
	) ENGINE = MYISAM";
	
	$createQRun = mysqli_query($dbc, $createQ);
	
	if(!$createQRun){
		echo "There has been an error creating a records table. No table exists for this user";
	}
	
	#Fill the student's unique sight words table with the correct words for this level:
	$seedQ ="INSERT INTO `$tableGrade`.`$studentSightRecordTable`(`word`) SELECT (`word`) FROM `sightWords`.`$sightLevel`";
	$seedQRun = mysqli_query($dbc, $seedQ);
	
	#increment the counter for the word the student has just seen:
	$numberOfAttemptsIncrementCounterQuery = "UPDATE `$tableGrade`.`$studentSightRecordTable` SET `numberOfAttempts` = `numberOfAttempts` + 1 WHERE  `word` =  '$_POST[teacherAnswer]' LIMIT 1";
	$numberOfAttemptsIncrementCounterQueryRun = mysqli_query($dbc, $numberOfAttemptsIncrementCounterQuery);
	
	#create a question record table if one doesn't exist for this user:
	$questionRecordTableQuery = "CREATE TABLE IF NOT EXISTS `$tableGrade`.`$studentSightQuestionRecordTable` (
	`questionNumber` TINYINT( 2 ) UNSIGNED NOT NULL ,
	`numberCorrect` SMALLINT( 4 ) UNSIGNED NOT NULL ,
	`numberIncorrect` SMALLINT( 4 ) UNSIGNED NOT NULL ,
	`time` INT UNSIGNED NOT NULL ,
	PRIMARY KEY (  `questionNumber` )
	) ENGINE = MYISAM";
		
	$questionRecordTableQueryRun = mysqli_query($dbc, $questionRecordTableQuery);
	
	#create a $word Errors table for this user if it doesn't exist yet:
	$errorsTableQuery = "CREATE TABLE  `$tableGrade`.`$errorsTableInfo` (
	`number` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`questionNumber` INT( 2 ) UNSIGNED NOT NULL ,
	`incorrectAnswer` VARCHAR( 200 ) NOT NULL ,
	`time` INT UNSIGNED NOT NULL
	) ENGINE = MYISAM";
	
	$errorsTableQueryRun = mysqli_query($dbc, $errorsTableQuery);
	
	#If there is a student submitted answer, check it:
	if (isset($_POST[studentAnswer])){
		
		if ($_POST[studentAnswer] == $_POST[teacherAnswer]){
		
			echo " Correct!";
			#increment number correct:
			$numberCorrectIncrementCounterQuery = "UPDATE `$tableGrade`.`$studentSightRecordTable` SET `numberCorrect` = `numberCorrect` + 1 WHERE  `word` =  '$_POST[teacherAnswer]' LIMIT 1";
			$numberCorrectIncrementCounterQueryRun = mysqli_query($dbc, $numberCorrectIncrementCounterQuery);
			
			#update the question table for this student:
			$numberRecordsCorrectIncrementCounterQuery = "INSERT INTO `$tableGrade`.`$studentSightQuestionRecordTable` (`questionNumber`, `numberCorrect`) VALUES ('$_POST[questionNumber]', '1') ON DUPLICATE KEY UPDATE `numberCorrect` = `numberCorrect` + 1";
			$numberRecordsCorrectIncrementCounterQueryRun = mysqli_query($dbc, $numberRecordsCorrectIncrementCounterQuery);
			
		
			}else{
				
				echo " No, sorry, that's wrong.";
				#increment number incorrect:
				$numberIncorrectIncrementCounterQuery = "UPDATE `$tableGrade`.`$studentSightRecordTable` SET `numberIncorrect` = `numberIncorrect` + 1 WHERE  `word` =  '$_POST[teacherAnswer]' LIMIT 1";
				$numberIncorrectIncrementCounterQueryRun = mysqli_query($dbc, $numberIncorrectIncrementCounterQuery);
				
				#update the question table for this student:
				$numberRecordsCorrectIncrementCounterQuery = "INSERT INTO `$tableGrade`.`$studentSightQuestionRecordTable` (`questionNumber`, `numberIncorrect`) VALUES ('$_POST[questionNumber]', '1') ON DUPLICATE KEY UPDATE `numberIncorrect` = `numberIncorrect` + 1";
				$numberRecordsCorrectIncrementCounterQueryRun = mysqli_query($dbc, $numberRecordsCorrectIncrementCounterQuery);
				
				#insert the values into the errors table for this question:
				
				$errorsInsert = "INSERT INTO `$tableGrade`.`$errorsTableInfo` (`questionNumber`, `incorrectAnswer`, `time`) VALUES ('$_POST[questionNumber]', '$_POST[studentAnswer]', '$time')";
				$errorsInsertRun = mysqli_query($dbc, $errorsInsert);
				
				
		}#end of answer checking
		
		#update the time:
		$numberRecordsTimeIncrementQuery = "UPDATE `$tableGrade`.`$studentSightQuestionRecordTable` SET `time` = `time` + $time WHERE `questionNumber` = $_POST[questionNumber] LIMIT 1";
		$numberRecordsTimeIncrementQueryRun = mysqli_query($dbc, $numberRecordsTimeIncrementQuery);
		
	}#end of If isset$_POST student answer
	
}#end of if isset $_POST

echo "<p><h1>Sight word practice area</h1></p>";

#this function creates an array with four random words, which is used to make the question and options for mcq:
function getRandomWordArray($dbc){
	
	#Create a query to get the sightwords from the correct table:
	$sightQuery = "SELECT * FROM sightWords.level1";
	$sightQueryRun = mysqli_query($dbc, $sightQuery);
	
	#Change the result of the query into an associative array, number keyed to word.
	$sightArray = array();
	while($row = mysqli_fetch_assoc($sightQueryRun)){
		$sightArray [$row[number]] = $row[word];
	}
	
	$count = count($sightArray);
	#this below is the correct way, but as I'm testing, I've turned it off:
	#$callNumber = rand(1, $count);
	#this below is for testing:
	$callNumber = rand(1, 8);
	
	#create an array to store the four random words:
	$randomWordArray = array();
	
	#populate the array with four words chosen at random from the sightwords table.
	for($i =1; $i < 5; $i++ ){
		
		$randomWord = $sightArray[rand(1,8)];
		
		if(isset($randomWord)){
			
		$randomWordArray[$i] = $randomWord;
		#unset the word from the $sightWords Array:
		
		$searchAndUnset = array_search($randomWordArray[$i], $sightArray);
		unset($sightArray[$searchAndUnset]);
		
		}elseif(!isset($randomWord)){
			#if the word chosen has been unset from the $sightwords array, decrement the counter and repeat the process:
			$i--;

		}#end decrement
		
	}#end populate array loop
	
	return $randomWordArray;
	
}#end of function getRandomWordArray($dbc)

function getRandomQuestion($dbc){
	
	#call getRandomWordArray() to get four options for the question:
	$randomWordArray = getRandomWordArray($dbc);
	
	#get $word initialized - this is the word we'll be testing, and will have an answer supplied:
	$word = $randomWordArray[rand(1, 4)];
	
	#count the rows in the question table for $word for max value of the rand numbers:
	$maxNumQuery = "SELECT `number` FROM sightwords.questions$word";
	$maxNumQueryRun = mysqli_query($dbc, $maxNumQuery);
	$maxNum = mysqli_num_rows($maxNumQueryRun);
	
	#Select the question from the table of questions for this word:
	$randomQuestionNumber = rand(1, $maxNum);
	$randomQuestionQuery = "SELECT * FROM sightwords.questions$word WHERE number = '$randomQuestionNumber' ";
	$randomQuestionQueryRun = mysqli_query($dbc, $randomQuestionQuery);
	
	if(!$randomQuestionQueryRun){
		echo "There is a problem getting the questions from the database in getRandomQuestion";
	}
	#make an array of the question data:
	$questionArray = mysqli_fetch_assoc($randomQuestionQueryRun);
	
	#Output the question and the options as a form:
	?> 
	
	<fieldset>
	<form action= "sightwords.php" 
			method = "POST">
			
			<?php echo "<p> $questionArray[question]</p>"; ?>
			
			<p>
				<input type="radio" name="studentAnswer"  value="<?php echo $randomWordArray[1] ?>" /><?php echo " ".$randomWordArray[1] ?><br />
				<input type="radio" name="studentAnswer"  value="<?php echo $randomWordArray[2] ?>" /> <?php echo " ".$randomWordArray[2] ?><br />
				<input type="radio" name="studentAnswer"  value="<?php echo $randomWordArray[3] ?>" /><?php echo " ".$randomWordArray[3] ?><br />
				<input type="radio" name="studentAnswer"  value="<?php echo $randomWordArray[4] ?>" /> <?php echo " ".$randomWordArray[4] ?><br />
			</p>
			
			<!-- send the real (teacher) answer, and the question number shown to the student, as $_POST variables  -->
			<input type = "hidden" name="teacherAnswer" value = "<?php echo $questionArray[answer]?>" />
			<input type = "hidden" name="questionNumber" value = "<?php echo $questionArray[number]?>" />
			
				<input type = "submit"
				class = "button orange submit"
				value = "Check your answers" />

				
				<?php /*start the timer*/ $_SESSION['time_start'] = time();?>			
	</form>
	</fieldset>
	
<?php	
}#end of getRandomQuestion($dbc, $sightArray, $word)

getRandomQuestion($dbc);

#end of page content
include_once 'footer.html';
?>

