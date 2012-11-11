<!DOCTYPE html>
<html>
	<head>
		<title>PHPCheckstyle Web Interface</title>
	</head>
	
	<body>
	
	<h1><img src="./html/images/Logo_phpcheckstyle.png"/>&nbsp;PHPCheckstyle</h1>
	
	<form name="myform" action="runFromWeb.php" method="POST">
	
	<p>File(s) to analyse
	<input type="test" id="sourceDir" name="sourceDir" value="./test"></input>
	</p>
	
	<p>Destination directory
	<input type="test" id="resultDir" name ="resultDir" value="./checkstyle_result"></input>
	</p>
	
	<input type="submit" value="Run"></input>
	
	</form>
	
	</body>
	
</html>