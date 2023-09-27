<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "../verify_logged_out.php";
	require "../header.php";

?>

<script>
function redirectToPage() {
	window.location.href = "../index.php";
}
</script>

<html>
	<head>
		<title>Librarian Login</title>

		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/form_styles.css">
		<link rel="stylesheet" type="text/css" href="css/index_style.css">
	</head>
	<body>
		<form class="cd-form" method="POST" action="#">
		
		<legend>管理員登入</legend>
		
			<div class="error-message" id="error-message">
				<p id="error"></p>
			</div>
			
			<div class="icon">
				<input class="l-user" type="text" name="l_user" placeholder="管理員" required />
			</div>
			
			<div class="icon">
				<input class="l-pass" type="password" name="l_pass" placeholder="密碼" required />
			</div>
			
			<input type="submit" value="登入" name="l_login"/>
			<button class="back-btn" onclick="redirectToPage()">回到首頁</button>
		</form>
	</body>
	
	<?php
		//phpinfo();
		if(isset($_POST['l_login']))
		{
			$result = mysqli_query($con,"SELECT id FROM librarian");
			$query = $con->prepare("SELECT id FROM librarian WHERE username = ? AND password = ?;");
			$l_user = $_POST['l_user'];
			$l_pass = sha1($_POST['l_pass']);
			$query->bind_param("ss", $l_user, $l_pass);
			$query->execute();
			if(mysqli_num_rows($query->get_result()) != 1)
				echo error_without_field("帳號或密碼輸入錯誤");
			else
			{
				$_SESSION['type'] = "librarian";
				$_SESSION['id'] = mysqli_fetch_array($result)[0];
				$_SESSION['username'] = $_POST['l_user'];
				header('Location: home.php');
			}
		}
	?>
	
</html>