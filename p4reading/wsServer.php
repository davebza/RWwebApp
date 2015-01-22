<?php
session_start();
/* This script will check for (or create) a table in database 4all for each student, for each worksheet, when that worksheet is accessed.
 * This table will store the answers that the student has given. 
 * The table name will be the student's class, classNumber aliased as classInfo, and added to the worksheet name.
 * It will then check the group, and serve the correct worksheet to the student. 
 */

#check that there is a valid login session in progress:
if (!isset($_SESSION['agent']) OR ($_SESSION['agent'] != md5($_SERVER['HTTP_USER_AGENT'] ) )) {
	
	#need the functions to create an absolute URL:
	require_once ('loginFunctions.php');
	$url = absoluteUrl();
	header("Location: $url");
	exit();#exit the script
}

#include the master variables:
require_once 'masterVariables.php';

#Send the worksheet, and drop and create the tables for this student's worksheet to be stored in the database:
include_once 'makeSheet.php';

?>