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
$PageTitle = "Worksheet creation page";
include('header.php');

if(substr($_SESSION['class'], 1,2)!= "T"){
		die("You are not authorized to view this page!");
	}

#This catches the name from the worksheetNamePage.php and attaches the name to the $worksheetName variable:
if(isset($_POST['worksheetNamePageSubmitted'])){
	$worksheetName = $_POST['worksheetName'];
	#Create a WS table for this worksheet if it doesn't already exist:
	$dbq = "CREATE TABLE  IF NOT EXISTS`P4`.`$worksheetName` LIKE masterworksheet";
	$dbr = mysqli_query($dbc, $dbq);
}

if(isset($_POST['submitted'])){
	
	#check to see if the essential variables $worksheetName, $questionNumber, $description have been set:
	if(empty($_POST['worksheetName'])){
	echo "<p class='error'>You must set the worksheet name!</p>";
	}

	if(empty($_POST['questionNumber'])){
		echo "<p class='error'>You must set the question Number!</p>";
	}
	
	if(empty($_POST['description'])){
		echo "<p class='error'>You must set the question type: it's very important!</p>";
		exit();
	}
	
	#create short variable names for POST data:
	$worksheetName = $_POST['worksheetName'];
	$questionNumber =  $_POST['questionNumber']; 
	$description =  $_POST['description'];
	$questionText = $_POST['questionText'];
	$textSize = strlen($_POST['correctAnswer']);
	$correctAnswer = $_POST['correctAnswer'];
	
	#If question involves mc with pics, set file path and option path
	#THIS IS BROKEN, UNTIL I CAN FIX IT ITS REMOVED
	/*if($description == "img") {
		$filePath = $_POST['filePath']."/";
		$optionOne = '<img src ='.$filePath.$questionNumber.'a.jpg'.'>';
		$optionTwo = '<img src ='.$filePath.$questionNumber.'b.jpg'.'>';
		$optionThree ='<img src ='.$filePath.$questionNumber.'c.jpg'.'>';
	}else{*/
	$optionOne = $_POST['optionOne'];
	$optionTwo = $_POST['optionTwo'];
	$optionThree = $_POST['optionThree'];	
	#}
	
	#Here is the database interaction:
	#Make a database query to put the values into the worksheet table:
	$dbInsertQuery = "INSERT INTO  $worksheetName(
			`questionNumber`,
			`description` ,
			`questionText`,
			`textSize`,
			`option1`,
			`option2`,
			`option3`,
			`correctAnswer`)
			VALUES
			('$questionNumber',  '$description', '$questionText', '$textSize', '$optionOne', '$optionTwo', '$optionThree', '$correctAnswer') 
			ON DUPLICATE KEY UPDATE 
			questionNumber='$questionNumber', 
			description='$description', 
			questionText='$questionText',
			textSize='$textSize',
			option1='$optionOne',
			option2='$optionTwo',
			option3='$optionThree',
			correctAnswer='$correctAnswer' ";
	
	$dbInsertQueryRun = mysqli_query($dbc, $dbInsertQuery); 
	
}#end of isset(_POST)
	
?> 

<!-- This page will probably be better if I make it in two pages, one with the table name, number of questions and question types, and the next for qtext and answers? -->
<h1><?php echo $PageTitle; ?></h1>

<fieldset>
<form action = "teacherInput.php"
			method = "POST">

<p> 
<label>Check the worksheet name, and amend if needed. Be careful - any change to this will mean you have to redo any questions already submitted:</label>
<input type = "text"
		   name  = "worksheetName"
		   id    = "worksheetName"
		   size = "30"
		   maxlength = "30"
		   value = "<?php if(isset($worksheetName)) { echo $worksheetName; } ?>" />
</p>

<p> 
<label>Question number:</label>
<input type = "text"
		   name = "questionNumber"
		   id    = "questionNumber"
		   size = "3"
		   maxlength = "3"
		   value = "" />
</p>

<p>
<label>Write the question here.</label>
<textarea name = "questionText"
				id = "questionText"
			   rows = "3"
			   cols = "100"
			   wrap = "soft">
</textarea>
</p>

<p>
<label>Question type:</label>
<select name = "description" 
			id = "description">
	<option value = "">---</option>
	<option value = "mc">Multiple Choice</option>
	<option value = "img">Multiple Choice with Pictures</option>
	<option value = "text">Closed text answer</option>
	<option value = "textArea">Open-ended question</option>
</select>
</p>

<!-- this is not needed until the path creatin conditional can work 
<p> 
<label>If MCQ with pictures, write the folder name here:</label>
<input type = "text"
		   name = "filePath"
		   id    = "filePath"
		   size = "20"
		   maxlength = "20"
		   value = "<?php if(isset($filePath)){ echo $filePath; }?>" /> 
</p> -->

<p>
<label>If MCQ, write option 1 here:</label>
<textarea name = "optionOne" 
				id = "optionOne"
			   rows = "3"
			   cols = "100"
			   wrap = "soft">
</textarea>
</p>

<p>
<label>If MCQ, write option 2 here:</label>
<textarea name = "optionTwo"  
				id = "optionTwo"
			   rows = "3"
			   cols = "100"
			   wrap = "soft">
</textarea>
</p>

<p>
<label>If MCQ, write option 3 here:</label>
<textarea name = "optionThree" 
				id = "optionThree"
			   rows = "3"
			   cols = "100"
			   wrap = "soft">
</textarea>
</p>

<p>
<label>Write the answer here, exactly as you want the students to write it.</label>
<textarea name = "correctAnswer" 
				id = "correctAnswer"
			   rows = "3"
			   cols = "100"
			   wrap = "soft">
</textarea>
</p>

<input type = "hidden" name="submitted" value = "TRUE" />

<input type = "submit" value = "Upload the question" class="button orange submit" />

</form>
</fieldset>

<?php 
#this section shows the worksheet in progress to the teachers as they work on it:
if(isset($_POST['submitted'])) {
	
	?>
	<h1> Here is your worksheet so far:</h1>
	
	<?php #Create the query to get the questions, descriptions and options from the $answerSheet table variable and run it
	
	$q = "SELECT  `questionNumber` ,  `description` ,  `questionText` ,  `textSize` ,  `option1` ,  `option2` ,  `option3` , `correctAnswer` 
	FROM  $worksheetName";
	$r = mysqli_query($dbc, $q);
	#Use the description column to choose the question elements, and a counter to keep track of the question numbers:
	while($row = mysqli_fetch_array($r, MYSQLI_ASSOC)){
		
		?><p class = "question"> <?php
		
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
					#and show the answer for the teacher to review:
					echo "And the answer is: ". $row['correctAnswer'];
			}
}
		
?>
</div> <!-- end the content div -->

<?php 
include 'footer.html';
?>