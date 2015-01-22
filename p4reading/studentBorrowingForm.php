<?php
?>
<h1>Please supply the student number for class <?php echo $_POST['borrowingClass']?> and the book code below:</h1>
<fieldset>
<form action= "bookCheckout.php" 
			method = "post">
			
		<input type = "hidden"
						name = "borrowingClass"
						value = <?php echo $_POST['borrowingClass']; ?> />
		
		<p>Student's class number: <?php echo $_POST['borrowingClass']?>
			<input type = "text"
		   		name = "studentClassNumber" autofocus
		   		id    = "studentClassNumber"
		  		size = "2"
		  		maxlength = "2"
		   		value = "" />
		</p>
			
			
		<p>Book Code:
			<input type = "text"
		   		name = "bookId"
		   		id    = "bookId"
		  		size = "20"
		  		maxlength = "20"
		   		value = "" />
		</p>
														
			<p><input type = "submit"
							class = "button orange"
							name = "submit"
							value = "Choose" /></p>
							
			<input type = "hidden"
						name = "submitted"
						value = "TRUE" />
</form>
</fieldset>