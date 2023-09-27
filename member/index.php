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
		<title>會員登入</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/form_styles.css">
		<link rel="stylesheet" type="text/css" href="css/index_style.css">
	</head>
	<body>
		<form class="cd-form" method="POST" action="#">
		
			<legend>會員登入</legend>
			
			<div class="error-message" id="error-message">
				<p id="error"></p>
			</div>
			
			<div class="icon">
				<input class="m-user" type="text" name="m_user" placeholder="使用者" required />
			</div>
			
			<div class="icon">
				<input class="m-pass" type="password" name="m_pass" placeholder="密碼" required />
			</div>
			
			<input type="submit" value="登入" name="m_login" />
			<button class="back-btn" onclick="redirectToPage()">回到首頁</button>
			<br /><br /><br /><br />
			
			<p align="center">還沒有帳號嗎?點&nbsp;<a href="register.php">這裡註冊</a>
		</form>
	</body>
	
	<?php
		if(isset($_POST['m_login']))
		{
			$query = $con->prepare("SELECT id, balance FROM member WHERE username = ? AND password = ?;");
			$m_user = $_POST['m_user'];
  		$m_pass = sha1($_POST['m_pass']);
			$query->bind_param("ss", $m_user, $m_pass);
			$query->execute();
			$result = $query->get_result();
			
			if(mysqli_num_rows($result) != 1)
				echo error_without_field("帳號或密碼輸入錯誤");
			else 
			{
				$resultRow = mysqli_fetch_array($result);
				$balance = $resultRow[1];
				if($balance < 0)
					echo error_without_field("Your account has been suspended. Please contact a librarian for further information");
				else
				{
					$_SESSION['type'] = "member";
					$_SESSION['id'] = $resultRow[0];
					$_SESSION['username'] = $_POST['m_user'];
					header('Location: home.php');
				}
			}
		}
	?>
	
</html>