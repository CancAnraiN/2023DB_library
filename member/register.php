<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "../header.php";
?>

<html>
	<head>
		<title>Register</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/form_styles.css">
		<link rel="stylesheet" href="css/register_style.css">
	</head>
	<body>
		<form class="cd-form" method="POST" action="#">
			<legend>Enter your details</legend>
			
				<div class="error-message" id="error-message">
					<p id="error"></p>
				</div>
				
				<div class="icon">
					<input class="m-user" type="text" name="m_user" id="m_user" placeholder="姓名" required />
				</div>
				
				<div class="icon">
					<input class="m-pass" type="password" name="m_pass" placeholder="密碼" required />
				</div>
				
				<div class="icon">
					<input class="m-name" type="text" name="m_name" placeholder="全名" required />
				</div>
				
				<div class="icon">
					<input class="m-email" type="email" name="m_email" id="m_email" placeholder="Email/信箱" required />
				</div>
				
				<div class="icon">
					<input class="m-balance" type="number" name="m_balance" id="m_balance" placeholder="初始借書點數" required />
				</div>
				
				<br />
				<input type="submit" name="m_register" value="註冊" />
		</form>
	</body>
	
	<?php
		if(isset($_POST['m_register']))
		{
			if($_POST['m_balance'] < 500)
				echo error_with_field("至少要500點以上的借書點數!", "m_balance");
			else
			{
				$query = $con->prepare("(SELECT username FROM member WHERE username = ?) UNION (SELECT username FROM pending_registrations WHERE username = ?);");
				$query->bind_param("ss", $_POST['m_user'], $_POST['m_user']);
				$query->execute();
				if(mysqli_num_rows($query->get_result()) != 0)
					echo error_with_field("此名子已經有人取走了!", "m_user");
				else
				{
					$query = $con->prepare("(SELECT email FROM member WHERE email = ?) UNION (SELECT email FROM pending_registrations WHERE email = ?);");
					$query->bind_param("ss", $_POST['m_email'], $_POST['m_email']);
					$query->execute();
					if(mysqli_num_rows($query->get_result()) != 0)
						echo error_with_field("此信箱已經被註冊過了", "m_email");
					else
					{
						$query = $con->prepare("INSERT INTO pending_registrations(username, password, name, email, balance) VALUES(?, ?, ?, ?, ?);");
						$m_user = $_POST['m_user'];
						$m_pass = sha1($_POST['m_pass']);
						$m_name = $_POST['m_name'];
						$m_email = $_POST['m_email'];
						$m_balance = $_POST['m_balance'];

						$query->bind_param("ssssd", $m_user, $m_pass, $m_name, $m_email, $m_balance);
						if($query->execute())
							echo success("註冊申請成功! 請待管理員審核!審核通過時，您將不會收到任何email通知(因為我寄不了信QQ)");
						else
							echo error_without_field("Couldn\'t record details. Please try again later");
					}
				}
			}
		}
	?>
	
</html>