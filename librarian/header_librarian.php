<html>
	<head>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,700">
		<link rel="stylesheet" type="text/css" href="css/header_librarian_style.css" />
	</head>
	<body>
		<header>
			<div id="cd-logo">
				<a href="../">
					<img src="img/logo.png" alt="Logo" />
					<p>NSYSU 搏嗑來</p>
				</a>
			</div>
			
			<div class="dropdown">
				<button class="dropbtn">
					<p id="librarian-name"><?php echo $_SESSION['username'] ?></p>
				</button>
				<div class="dropdown-content">
					<a href="../logout.php">登出</a>
				</div>
			</div>
		</header>
	</body>
</html>