<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
	<title><?php echo htmlspecialchars($PageTitle); ?></title>	
	<link rel="shortcut icon" href="image/favicon.ico" />
	<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="content-language" content="en-gb" />
	<meta http-equiv="imagetoolbar" content="false" />
	<meta name="author" content="Christopher Robinson" />
	<meta name="copyright" content="Copyright (c) Christopher Robinson 2005 - 2007" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />	
	<meta name="last-modified" content="Sat, 01 Jan 2007 00:00:00 GMT" />
	<meta name="mssmarttagspreventparsing" content="true" />	
	<meta name="robots" content="index, follow, noarchive" />
	<meta name="revisit-after" content="7 days" />
</head>
<?php

#check to see if this is a student or teacher, and give the appropriate header links accordingly:
# This is for teachers:
if(substr($_SESSION['class'], 1, 2) == "T"){
	
	?>
	<body>
	<div id="header">
		<h1><?php echo "$PageTitle"; ?></h1>
		<h2>P4 R&ampW Teacher</h2>
	</div>
	<div id="navigation">
		<ul>
			<li><a href="loggedIn.php" class= "button orange">Overview</a></li>
			<li><a href="bookBorrowing.php" class= "button orange">Books</a></li>
			<li><a href="worksheetNamePage.php" class= "button orange" >New WS</a></li>
			<li><a href="teacherDash.php" class= "button orange">Dashboard</a></li>
			<li><a href="changePassword.php" class= "button orange">Password</a></li>
			<li><?php # create a login/logout link:

				if ( (isset($_SESSION['studentId'])) && (!strpos($_SERVER['PHP_SELF'], 'logout.php')) ) {
				echo '<a href="logOut.php" class= "button orange">Log out</a>';

				}else {

				echo '<a href="login.php" class= "button orange">Log out</a>';

				}
		?></li>
		</ul>
	</div>
	<div id="content">
	<!--  This is the beginning of the page-specific content div -->
<?php
#if not a teacher, here is the student header links
}else{
	
?>
<body>
	<div id="header">
		<h1><?php echo "$PageTitle"; ?></h1>
		<h2>P4 R&ampW worksheets</h2>
	</div>
	<div id="navigation">
		<ul>
			<li><a href="loggedIn.php" class= "button orange">Start</a></li>
			<li><a href="wsServer.php" class= "button orange">Worksheet</a></li>
			<li><a href="sightWords.php" class= "button orange" >Sight words</a></li>
			<li><a href="#" class= "button orange">Empty</a></li>
			<li><a href="#" class= "button orange">Empty</a></li>
			<li><?php # create a login/logout link:

				if ( (isset($_SESSION['studentId'])) && (!strpos($_SERVER['PHP_SELF'], 'logout.php')) ) {
				echo '<a href="logOut.php" class= "button orange">Log out</a>';

				}else {

				echo '<a href="login.php" class= "button orange">Log in</a>';

				}
		?></li>
		</ul>
	</div>
	<div id="content">
	<!--  This is the beginning of the page-specific content div -->
<?php	
}
?>
