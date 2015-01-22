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
$PageTitle = "Home Reading Book Borrowing";
include_once('header.php');

if(substr($_SESSION['class'], 1,2)!= "T"){
	die("You are not authorized to view this page!");
}
 
#Create short variables for the POST data for the borrowingClass, student and the book ID:
$borrowingClass = $_POST['borrowingClass'];
$studentClassNumber = $_POST['studentClassNumber'];
$studentToRecord = $_POST['borrowingClass'].$_POST['studentClassNumber'];
$bookId = $_POST['bookId'];
$gradeDB = "P".substr($borrowingClass, 0, 1);

#COnnect to the book database for checking on existence and status:

include 'mysqliConnectHomeReadingBooksDB.php';

#Check that the book exists in the database - both in it's own table and in the Inventory table:
# First, in it's own table:

$dbq = "SELECT COUNT(`numberOfOuts`) FROM  `homeReadingBookRecords`.`$bookId`";
$dbr = mysqli_query($dbc, $dbq); #or die("There was a problem with the book barcode. It probably doesn't exist in our database, so please check on this. In the meantime, let the student choose a differnet book.");

if (!$dbr) {
	
	echo "This book has not been properly acquisitioned: no table for the book exists on the database. Please check on this. In the meantime, let the student choose a different book.";
	include 'studentBorrowingForm.php';
	die;
}

#Check the book's status according to the homeReadingBooksInventory table:


$dbq = "SELECT  `bookStatus`
FROM  `homeReadingBookRecords`.`homeReadingBooksInventory`
WHERE bookId =$bookId LIMIT 1";
$dbr = mysqli_query($dbc, $dbq)or die("Book Status column could not be checked. bookCheckout.php");

$statusCheck = $dbr->fetch_array(MYSQLI_NUM);
#If the book is not recorded as being checked out, end the script:
if ($statusCheck[0]=='Out'){
	echo "This book was not returned from its last checkout. Please return it first.";
	include 'bookReturnForm.php';	
	die;
}elseif ($statusCheck[0]=='In'){
	
	echo "Book was returned after last use. ";
}

#Get book title from database:

$dbq = "SELECT  `bookTitle`
FROM  `homeReadingBookRecords`.`homeReadingBooksInventory`
WHERE bookId =$bookId LIMIT 1";
$dbr = mysqli_query($dbc, $dbq)or die("Book Title column could not be checked. bookCheckout.php");

$bookTitleArray = $dbr->fetch_array(MYSQLI_NUM);
$bookTitle = mysqli_real_escape_string($dbc, $bookTitleArray[0]);
echo $bookTitle;

#include the connection file to the Home Reading book DB - this maybe needs to become a function and the calling page uses this as an includes:

#Check if the student has borrowed books before, if not make a table for them:
include 'mysqliConnectStudentHomeReading.php';
$dbq = "CREATE TABLE IF NOT EXISTS `studentHomeReading`.`$studentToRecord` (
`numberOfTimes` int(3) NOT NULL AUTO_INCREMENT,
`bookId` int(10) NOT NULL,
`dateOut` date NOT NULL,
`returned` date DEFAULT NULL,
PRIMARY KEY (  `numberOfTimes` )
) ENGINE = MYISAM" ;
$dbr = mysqli_query($dbc, $dbq) or die("There is no table for this student in the Home Reading Database, and none could be created.");

echo $studentToRecord."has a Home Reading Record Table. ";

#Count the number of entries in the student's borrowing record. 0 means that they have never borrowed before, and so can still take a book
$dbq = "SELECT COUNT( * ) FROM `studentHomeReading`.`$studentToRecord`";
$dbr = mysqli_query($dbc, $dbq) or die ("Couldn't connect to the student Home reading record table to check if they have ever borrowed books. bookCheckout.php");
$checkIfEver =  $dbr->fetch_array(MYSQLI_NUM);
echo $studentToRecord." has borrowed books ". $checkIfEver[0]." times. ";
# If the number of entries is greater than 0, run a check to see if the student has returned their previous book. If 
if ($checkIfEver[0] > 0){
	
	$dbq = "SELECT `returned`
	FROM `studentHomeReading`.`$studentToRecord`
	ORDER BY numberOfTimes DESC
	LIMIT 1";
	
	$dbr = mysqli_query($dbc, $dbq) or die ("Couldn't connect to the student Home reading record table to cxheck they can borrow books. bookCheckout.php");
	
	$row = $dbr->fetch_array(MYSQLI_NUM);
	
	if (is_null($row[0])){
		echo "According to our records, this student is ". $studentToRecord." and has not returned their last book, so cannot borrow another.";
		die;
	}
	
}


#If the above checks are good, and the student is eligible to borrow a book, run the book checkout script, inserting the book Id and time checked out into the student's home reading record table:
$bookCheckOut = "INSERT INTO `studentHomeReading`.`$studentToRecord` (`bookId`, `dateOut`) VALUES ('$bookId', now()) ";
$bookCheckOutRun = mysqli_query($dbc, $bookCheckOut) or die("The book was not checked out, bookCheckOut.php");

#Insert the date into the student's master table:
if (substr($borrowingClass, 0, 1)== '4') {

	include 'mysqliConnectP4.php';
	$allTable = "4all";

}elseif (substr($borrowingClass, 0, 1)== '5'){

	include 'mysqliConnectP5.php';
	$allTable = "5all";

} 

$dbq = "UPDATE `$gradeDB`.`$allTable` SET `lastHRBorrowing` = NOW( ) WHERE  `$allTable`.`class` = '$borrowingClass' && `$allTable`.`classNumber` = $studentClassNumber";
$dbr = mysqli_query($dbc, $dbq) or die("Couldn't update the students master table column lastHRBorrowing");

#Then insert the book title into the student's master table:
$dbq = "UPDATE `$gradeDB`.`$allTable` SET `lastHRBookTitle` = '$bookTitle' WHERE  `$allTable`.`class` = '$borrowingClass' && `$allTable`.`classNumber` = $studentClassNumber";
$dbr = mysqli_query($dbc, $dbq) or die("Couldn't update the students master table column lastHRBookTitle");

#And change the master record for returned? from null or yes to no:
$dbq = "UPDATE `$gradeDB`.`$allTable` SET `lastHRReturned` = 'No' WHERE  `$allTable`.`class` = '$borrowingClass' && `$allTable`.`classNumber` = $studentClassNumber";
$dbr = mysqli_query($dbc, $dbq) or die("Couldn't update the students master table column returned");

#Update the book table for this book:
include 'mysqliConnectHomeReadingBooksDB.php';

$dbq = "INSERT INTO  `homeReadingBookRecords`.`$bookId` (
`numberOfOuts` ,
`studentId` ,
`lastOut` ,
`lastIn`
)
VALUES (
NULL , '$studentToRecord', NOW( ) , NULL
)";

$dbr = mysqli_query($dbc, $dbq) or die("The book record table has not been updated. bookCheckout.php");

#Update the book record in the master record table for all Home reading books:

$dbq = "UPDATE `homeReadingBookRecords`.`HomeReadingBooksInventory` SET numberOfTimesBorrowed=numberOfTimesBorrowed+1, lastBorrowing=now()
WHERE bookId=$bookId";

$dbr = mysqli_query($dbc, $dbq) or die("The master home reading books record table has not been updated. bookCheckout.php");

#Update the book status column in the homeReadingBooksInventory table

$dbq = "UPDATE  `homeReadingBookRecords`.`HomeReadingBooksInventory` SET  `bookStatus` =  'Out' WHERE  `HomeReadingBooksInventory`.`bookId` =$bookId";
$dbr = mysqli_query($dbc, $dbq)or die("Book Status column not updated.");

#display the student's reading record as a table for the teacher to see if needed:

$dbq = "SELECT `numberOfTimes` AS `Number of Borrowings`, `bookId` AS `Book Code`, `dateOut` AS `Date Borrowed`, `returned` AS `Date Returned` FROM  `studentHomeReading`.`$studentToRecord`";
$dbr = mysqli_query($dbc, $dbq) or die("Couldn't check whether the book has already been entered into the database for the second time in the script.");

if($dbr) {# if the query ran ok

	#get headers for table
	$headers = mysqli_num_fields($dbr);

	#output headers:
	?><table><?php echo "<h1>Student Borrowing Record: $studentToRecord</h1>";
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

include 'studentBorrowingForm.php';

?>