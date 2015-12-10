<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Conference Paper Review System</title>
	<link rel="stylesheet" href="css/style.css">
	</head>

	<body>
	<div id="wrap">
	<div id="regbar">
	<table width="1200px" class="tableCentre" >
		<tr><td width="90%"><div id="leftmargin"><h2>Paper Review System</h2></div></td>
		<td align="right">
		<div id="rightmargin">
		<h2><a href="#" id="loginform">Login</a></h2></td>
		<tr><td><p>&nbsp </p></td><td>
		<div class="login">
		<div class="arrow-up"></div>
		<div class="formholder">
		<div class="randompad">
		<fieldset>
			<label name="email">Email</label>
			<input type="email"/>
			<label name="password">Password</label>
			<input type="password" />
			<input type="submit" value="Login" />

		</fieldset>
		</div>
		</div>
		</div>
		</div>
		</td></tr>
		
		</table>
	</div>
	</div>
	<script src='jquery-2.1.4.min.js'></script>
	<script src="js/index.js"></script>
	<table class="table1">
	<tr>
	<td>
	<ul>
	  <li><a href="index.php"><font color="000000" size="4">Home</a></li>
	  <li><a href="work.php"><font color="000000">Review</a></li>
	  <li class="inpage"><a href="decision.php"><font color="000000">Decision</a></li>
	  <li><a href="search.php"><font color="000000">Search paper</a></li>
	  <li><a href="#"><font color="000000">TempButton 5</a></li>
	  <li><a href="#"><font color="000000">TempButton 6</a></li>
	</ul>
	</td>
	<td width="80%">
	<p>&nbsp </p>
	<h1>Paper waiting for final decision</h1>
	<p>&nbsp </p>
	<!-- if no -->
	<p> if no work </p>
	<p> [Text message] No paper in waiting queue. Come back later.</p>
	<!-- if yes-->
	<p>&nbsp </p>
	<p> else </p>
	<table width="100%" class="table3">
	<tr><th>Title</th><th>Author</th><th>Type</th><th>Status</th></tr>
	<tr><td><a href="decisionMaking.php"><u>ASFWQFSACFWFCWQFCWQCFQWCFQW</u></a></td><td>Author</td><td>Type</td><td>Status</td></tr>
	<tr><td><a href="decisionMaking.php"><u>ASFWQFSACFWFCWQFCWQCFQWCFQWASFWQFSACFWFCWQFCWQCFQWCFQW</u></a></td><td>Author</td><td>Type</td><td>Status</td></tr>
	<tr><td><a href="decisionMaking.php"><u>ASFWQWCFQWASFWQFSACFWFCWQFCWQCFQWCFQW</u></a></td><td>Author</td><td>Type</td><td>Status</td></tr>
	<tr><td><a href="decisionMaking.php"><u>ASFWQFSACFWFCWQFCWQCFQWCFQW</u></a></td><td>Author</td><td>Type</td><td>Status</td></tr>
	</table>
	<p>Note: show at most 2 author, et al. for 3 or more</p>
	</td>
	</tr>
	</table>
	
	</body>
</html>
