<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "verify_librarian.php";
	require "header_librarian.php";
	require_once('../PHPMailer/PHPMailerAutoload.php'); //這個東西很重要

?>

<html>
	<head>
		<title>Pending Registrations</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/custom_checkbox_style.css">
		<link rel="stylesheet" type="text/css" href="css/pending_registrations_style.css">
	</head>
	<body>
		<?php
			ini_set('SMTP','ssl://smtp.gmail.com '); 
			ini_set('smtp_port',465);
			$query = $con->prepare("SELECT username, name, email, balance FROM pending_registrations");
			$query->execute();
			$result = $query->get_result();
			$rows = mysqli_num_rows($result);
			if($rows == 0)
				echo "<h2 align='center'>目前沒有人申請</h2>";
			else
			{
				echo "<form class='cd-form' method='POST' action='#'>";
				echo "<legend>新用戶註冊待審核列表</legend>";
				echo "<div class='error-message' id='error-message'>
						<p id='error'></p>
					</div>";
				echo "<table width='100%' cellpadding=10 cellspacing=10>
						<tr>
							<th></th>
							<th>用戶名稱<hr></th>
							<th>全名<hr></th>
							<th>Email<hr></th>
							<th>借書點數<hr></th>
						</tr>";
				for($i=0; $i<$rows; $i++)
				{
					$row = mysqli_fetch_array($result);
					echo "<tr>";
					echo "<td>
							<label class='control control--checkbox'>
								<input type='checkbox' name='cb_".$i."' value='".$row[0]."' />
								<div class='control__indicator'></div>
							</label>
						</td>";
					$j;
					for($j=0; $j<3; $j++)
						echo "<td>".$row[$j]."</td>";
					echo "<td>$".$row[$j]."</td>";
					echo "</tr>";
				}
				echo "</table><br /><br />";
				echo "<div style='float: right;'>";
				echo "<input type='submit' value='拒絕申請' name='l_delete' />&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<input type='submit' value='接受申請' name='l_confirm' />";
				echo "</div>";
				echo "</form>";
			}
			
			$header = 'From: <noreply@library.com>' . "\r\n";
			
			if(isset($_POST['l_confirm']))
			{
				$members = 0;
				for($i=0; $i<$rows; $i++)
				{
					if(isset($_POST['cb_'.$i]))
					{
						$username =  $_POST['cb_'.$i];
						$query = $con->prepare("SELECT * FROM pending_registrations WHERE username = ?;");
						$query->bind_param("s", $username);
						$query->execute();
						$row = mysqli_fetch_array($query->get_result());
						
						$query = $con->prepare("INSERT INTO member(username, password, name, email, balance) VALUES(?, ?, ?, ?, ?);");
						$query->bind_param("ssssd", $username, $row[1], $row[2], $row[3], $row[4]);
						if(!$query->execute())
							die(error_without_field("ERROR: Couldn\'t insert values"));
						$members++;
						
						$to = $row[3];
						
						$mail= new PHPMailer();							//建立新物件
						$mail->SMTPDebug = 0;                        
						$mail->IsSMTP();								//設定使用SMTP方式寄信
						$mail->SMTPAuth = true;							//設定SMTP需要驗證
						$mail->SMTPSecure = "ssl";						// Gmail的SMTP主機需要使用SSL連線
						$mail->Host = "smtp.gmail.com";					//Gamil的SMTP主機
						$mail->Port = 465;								//Gamil的SMTP主機的埠號(Gmail為465)。
						$mail->CharSet = "utf-8";						//郵件編碼
						$mail->Username = "garyko0406@gmail.com";		//Gamil帳號
						$mail->Password = "ihcytsagplpablym";					//Gmail密碼
						$mail->From = "root@cse.db.nsysu.edu.tw";		//寄件者信箱
						$mail->FromName = "NSYSU搏嗑來";			//寄件者姓名
						$mail->Subject ="註冊結果通知"; 				//郵件標題
						$mail->Body = "Your membership has been accepted by the library. You can now issue books using your account."; //郵件內容
						$mail->IsHTML(false);							//郵件內容為html
						$mail->AddAddress("$to");					//收件者郵件及名稱

						if(!$mail->Send()){
							echo "寄信發生錯誤：" . $mail->ErrorInfo;
							//如果有錯誤會印出原因
							}
							else{
							echo "寄信成功";
							}

					}
				}
				if($members > 0)
					echo success("Successfully added ".$members." members");
				else
					echo error_without_field("No registration selected");
			}
			
			if(isset($_POST['l_delete']))
			{
				$requests = 0;
				for($i=0; $i<$rows; $i++)
				{
					if(isset($_POST['cb_'.$i]))
					{
						$username =  $_POST['cb_'.$i];
						$query = $con->prepare("SELECT email FROM pending_registrations WHERE username = ?;");
						$query->bind_param("s", $username);
						$query->execute();
						$email = mysqli_fetch_array($query->get_result())[0];
						
						$query = $con->prepare("DELETE FROM pending_registrations WHERE username = ?;");
						$query->bind_param("s", $username);
						if(!$query->execute())
							die(error_without_field("ERROR: Couldn\'t delete values"));
						$requests++;

						$mail= new PHPMailer();							//建立新物件
						$mail->SMTPDebug = 0;                        
						$mail->IsSMTP();								//設定使用SMTP方式寄信
						$mail->SMTPAuth = true;							//設定SMTP需要驗證
						$mail->SMTPSecure = "ssl";						// Gmail的SMTP主機需要使用SSL連線
						$mail->Host = "smtp.gmail.com";					//Gamil的SMTP主機
						$mail->Port = 465;								//Gamil的SMTP主機的埠號(Gmail為465)。
						$mail->CharSet = "utf-8";						//郵件編碼
						$mail->Username = "garyko0406@gmail.com";		//Gamil帳號
						$mail->Password = "ihcytsagplpablym";					//Gmail密碼
						$mail->From = "root@cse.db.nsysu.edu.tw";		//寄件者信箱
						$mail->FromName = "NSYSU搏嗑來";			//寄件者姓名
						$mail->Subject ="註冊結果通知"; 				//郵件標題
						$mail->Body = "Your membership has been rejected by the library. Please contact a librarian for further information."; //郵件內容
						$mail->IsHTML(false);							//郵件內容為html
						$mail->AddAddress("$email");					//收件者郵件及名稱

						if(!$mail->Send()){
							echo "寄信發生錯誤：" . $mail->ErrorInfo;
							//如果有錯誤會印出原因
							}
							else{
							echo "寄信成功";
							}
					}
				}
				if($requests > 0)
					echo success("Successfully deleted ".$requests." requests");
				else
					echo error_without_field("No registration selected");
			}
		?>
	</body>
</html>