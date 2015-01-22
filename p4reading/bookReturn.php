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
include('header.php');

if(substr($_SESSION['class'], 1,2)!= "T"){
	die("You are not authorized to view this page!");
}

#Start of the return section of the page:
$bookId = $_POST['bookId'];

include 'mysqliConnectHomeReadingBooksDB.php';

#Check the book's status according to the homeReadingBooksInventory table:

$dbq = "SELECT  `bookStatus` 
FROM  `homeReadingBookRecords`.`homeReadingBooksInventory` 
WHERE bookId =$bookId LIMIT 1";
$dbr = mysqli_query($dbc, $dbq)or die("Book Status column could not be checked. bookReturn.php");

$statusCheck = $dbr->fetch_array(MYSQLI_NUM);
#If the book is not recorded as being checked out, end the script:
if ($statusCheck[0]=='In'){
	echo "This book was not checked out and so cannot be returned";
	include 'bookReturnForm.php';
	include 'footer.html';
	die;
}

$dbq = "SELECT `studentId`, `lastIn`
FROM `homeReadingBookRecords`.`$bookId`
ORDER BY numberOfOuts DESC
LIMIT 1";

$dbr = mysqli_query($dbc, $dbq) or die ("Couldn't connect to the book table in the database to check if it's actually out or not. This probably means this book has not been added to the database. bookReturn.php");

$row = $dbr->fetch_array(MYSQLI_NUM);

if (!is_null($row[1]>0)) {
	#first return the book in the book table
	echo "Book was checked out by student ".$row[0].". Proceeding to update the book record.";
	$dbq = "UPDATE  `homeReadingBookRecords`.`$bookId` SET  `lastIn` = NOW( ) ORDER BY  `numberOfOuts` DESC LIMIT 1";
	$dbr = $dbr = mysqli_query($dbc, $dbq) or die ("Couldn't return the book in the book table. bookReturn.php");
	echo "The book is now returned in the book table.";
	#then update the book status column in the homeReadingBooksInventory table:
	
	#Update the book status column in the homeReadingBooksInventory table
	$dbq = "UPDATE  `homeReadingBookRecords`.`HomeReadingBooksInventory` SET  `bookStatus` =  'In' WHERE  `HomeReadingBooksInventory`.`bookId` =$bookId";
	$dbr = mysqli_query($dbc, $dbq)or die("Book Status column not updated.");
	
	#Update the student's home reading record:
	echo "Proceeding to update the student's Home Reading Record.";
	include 'mysqliConnectStudentHomeReading.php';
	$dbq = "UPDATE `studentHomeReading`.`$row[0]` SET `returned` = NOW( ) ORDER BY `numberOfTimes` DESC LIMIT 1";
	$dbr = mysqli_query($dbc, $dbq) or die ("Couldn't return the book in the student's table. bookReturn.php");
	echo "Book is now returned in the student table.";

	#update the lastHRReturned column in thew student's Alltable:
	#echo $row[0];
	$gradeDB = "P".substr($row[0], 0,1);
	$borrowingClass = substr($row[0], 0, 2);
	#echo $borrowingClass;
	$studentClassNumber = substr($row[0], 2);
	#echo $studentClassNumber;
	
		if ($gradeDB == 'P4') {
	
			include 'mysqliConnectP4.php';
			$allTable = "4all";
	
		}elseif ($gradeDB== 'P5'){
	
			include 'mysqliConnectP5.php';
			$allTable = "5all";
	
		}
		
		$dbq = "UPDATE `$gradeDB`.`$allTable` SET `lastHRReturned` = 'Yes' WHERE  `$allTable`.`class` = '$borrowingClass' && `$allTable`.`classNumber` = $studentClassNumber";
		$dbr = mysqli_query($dbc, $dbq) or die("Couldn't update the students master table column: returned");
	
} else {
	
	echo "This book was not checked out, so cannot be returned.";

}

include 'bookReturnForm.php';

include 'footer.html';
?>

