<?php
?>
<h1>To return a book, scan the book and click the button:</h1>

<fieldset>
<form action= "bookReturn.php" 
			method = "post">
			
		<p>Book Code:
			<input type = "text"
		   		name = "bookId" autofocus
		   		id    = "bookId"
		  		size = "20"
		  		maxlength = "20"
		   		value = "" />
		</p>
			
	<p><input type = "submit"
							class = "button orange"
							name = "submit"
							value = "Return" /></p>
							
			<input type = "hidden"
						name = "hiddenReturn"
						value = "TRUE" />
						
</form>
</fieldset>
<?php 
?>