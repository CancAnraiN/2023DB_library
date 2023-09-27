<?php
	require "../db_connect.php";
	require "verify_librarian.php";
	require "header_librarian.php";
	$imagePath = 'img/bg.jfif';
?>

<html>
	<head>
		<title>Welcome</title>
		<style>
        body {
            background-image: url('<?php echo $imagePath; ?>');
            background-size: cover;
        }
    </style>
		<link rel="stylesheet" type="text/css" href="css/home_style.css" />
	</head>
	<body>
		<div id="allTheThings">
			<a href="pending_registrations.php">
				<input type="button" value="未審核的註冊申請" />
			</a><br />
			<a href="pending_book_requests.php">
				<input type="button" value="未審核的借書申請" />
			</a><br />
			<a href="insert_book.php">
				<input type="button" value="上架新書" />
			</a><br />
			<a href="update_copies.php">
				<input type="button" value="更新書的庫存" />
			</a><br />
			<a href="update_balance.php">
				<input type="button" value="更新會員的借書點數" />
			</a><br />
			<br />
		</div>
	</body>
</html>