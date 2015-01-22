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
$PageTitle = "Home Reading Book Acquisition";
include_once('header.php');

if(substr($_SESSION['class'], 1,2)!= "T"){
	die("You are not authorized to view this page!");
}
#start the acquisition section:

#COnnect to the database:
include 'mysqliConnectHomeReadingBooksDB.php';

#Make short variables for the $_POST data:
$bookId = mysqli_real_escape_string($dbc, $_POST[bookId]);
$bookTitle = mysqli_real_escape_string($dbc, $_POST[bookTitle]);
$bookPublisher = mysqli_real_escape_string($dbc, $_POST[bookPublisher]);
$bookCost = mysqli_real_escape_string($dbc, $_POST[bookCost]);
$bookLevel = mysqli_real_escape_string($dbc, $_POST[bookLevel]);

# Check if book is already on the database:

$dbq = "SELECT `bookId` FROM  `homeReadingBookRecords`.`HomeReadingBooksInventory`
WHERE  `bookId` ='$bookId' LIMIT 1";
echo $dbq;
$dbr = mysqli_query($dbc, $dbq) or die("Couldn't check whether the book has already been entered into the database.");
#Make an associatiev array with the data returned, if any. This will be used to show the book info in case of conflict:
$dbaseCheck = $dbr->fetch_array(MYSQLI_NUM);

#If there is no record of the book in the database already:
if (!isset($dbaseCheck[0])){
	echo "No existing book is found with the same barcode! Book is being added to the database. You can view the book details below"?><p><?php ;
	
	#insert the data into the homeReadingInventory table:
	$dbq = "INSERT INTO `homeReadingBookRecords`.`HomeReadingBooksInventory` (`bookId`, `bookTitle`, `bookPublisher`, `bookCost`, `bookLevel`, `numberOfTimesBorrowed`, `bookStatus`) VALUES ('$bookId', '$bookTitle', '$bookPublisher', '$bookCost', '$bookLevel', '0', 'In')";
	$dbr = mysqli_query($dbc, $dbq) or die("Error: Book not entered into INventory database.");
	
	#Create a book table for the individual book:
	$dbq = "CREATE TABLE  IF NOT EXISTS `homeReadingBookRecords`.`$bookId` (
				`numberOfOuts` INT( 6 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`studentId` VARCHAR( 6 ) NOT NULL ,
				`lastOut` DATE DEFAULT NULL ,
				`lastIn` DATE DEFAULT NULL
				) ENGINE = MYISAM ";
	$dbr = mysqli_query($dbc, $dbq) or die("Individual book table not created.");
	
	#Display the new book information:
	$dbq = "SELECT `bookId` AS `Book Code`, `bookTitle` AS `Title`, `bookPublisher` AS `Publisher`,`bookCost` AS `Cost`, `bookLevel` AS `Level`, `numberOfTimesBorrowed` AS `No. of uses`, `lastBorrowing` AS `Last checked out`, `bookStatus` AS `Status`
	FROM  `homeReadingBookRecords`.`HomeReadingBooksInventory`
	WHERE  `bookId` ='$bookId' LIMIT 1";
	
	$dbr = mysqli_query($dbc, $dbq) or die("Couldn't check whether the book has already been entered into the database for the second time in the script.");
	
	if($dbr) {# if the query ran ok
	
		#get headers for table
		$headers = mysqli_num_fields($dbr);
	
		#output headers:
		?><table><?php echo "<h1>Book Details</h1>";
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
								
						mysqli_free_result($dbr);
						
					}#end if result comdition
						
					?> 
					</table>
					<?php
	
}

if (isset($dbaseCheck[0]) && $dbaseCheck[0] == $bookId){

	echo "There appears to be a problem with the adding: we seem to have this book already.";
	#make a table to show book details:
	$dbq = "SELECT `bookId` AS `Book Code`, `bookTitle` AS `Title`, `bookPublisher` AS `Publisher`,`bookCost` AS `Cost`, `bookLevel` AS `Level`, `numberOfTimesBorrowed` AS `No. of uses`, `lastBorrowing` AS `Last checked out`, `bookStatus` AS `Status`
	FROM  `homeReadingBookRecords`.`HomeReadingBooksInventory`
	WHERE  `bookId` ='$bookId' LIMIT 1";
	
	$dbr = mysqli_query($dbc, $dbq) or die("Couldn't check whether the book has already been entered into the database for the second time in the script.");
	
	if($dbr) {# if the query ran ok
	
		#get headers for table
		$headers = mysqli_num_fields($dbr);
	
		#output headers:
		?><table><?php echo "<h1>Book Details</h1>";
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
							
					mysqli_free_result($dbr);
					
				}#end if result condition
					
				?> 
				</table>
				<?php
	
}

?><h1>Add another book?</h1> <?php
 
 include "bookAcquisition.php";

include_once 'footer.html';
?>