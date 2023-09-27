<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "verify_librarian.php";
	require "header_librarian.php";
	require_once('../PHPMailer/PHPMailerAutoload.php');
?>

<html>
	<head>
		<title>借書申請列表</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/custom_checkbox_style.css">
		<link rel="stylesheet" type="text/css" href="css/pending_book_requests_style.css">
	</head>
	<body>
		<?php
			$query = $con->prepare("SELECT * FROM pending_book_requests;");
			$query->execute();
			$result = $query->get_result();;
			$rows = mysqli_num_rows($result);
			if($rows == 0)
				echo "<h2 align='center'>目前沒有人申請</h2>";
			else
			{
				echo "<form class='cd-form' method='POST' action='#'>";
				echo "<legend>借書申請列表</legend>";
				echo "<div class='error-message' id='error-message'>
						<p id='error'></p>
					</div>";
				echo "<table width='100%' cellpadding=10 cellspacing=10>
						<tr>
							<th></th>
							<th>用戶名稱<hr></th>
							<th>申請的書籍<hr></th>
							<th>申請時間<hr></th>
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
					for($j=1; $j<4; $j++)
						echo "<td>".$row[$j]."</td>";
					echo "</tr>";
				}
				echo "</table>";
				echo "<br /><br /><div style='float: right;'>";
				echo "<input type='submit' value='沒有 沒有 沒有' name='l_reject' />&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<input type='submit' value='好 通過' name='l_grant'/>";
				echo "</div>";
				echo "</form>";
			}
			
			$header = 'From: <noreply@library.com>' . "\r\n";
			
			if(isset($_POST['l_grant']))
			{
				$requests = 0;
				for($i=0; $i<$rows; $i++)
				{
					if(isset($_POST['cb_'.$i]))
					{
						$request_id =  $_POST['cb_'.$i];
						$query = $con->prepare("SELECT member, book_isbn FROM pending_book_requests WHERE request_id = ?;");
						$query->bind_param("d", $request_id);
						$query->execute();
						$resultRow = mysqli_fetch_array($query->get_result());
						$member = $resultRow[0];
						$isbn = $resultRow[1];
						$query = $con->prepare("INSERT INTO book_issue_log(member, book_isbn) VALUES(?, ?);");
						$query->bind_param("ss", $member, $isbn);
						if(!$query->execute())
							die(error_without_field("ERROR: Couldn\'t issue book"));
						$requests++;
						
						$query = $con->prepare("SELECT email FROM member WHERE username = ?;");
						$query->bind_param("s", $member);
						$query->execute();
						$to = mysqli_fetch_array($query->get_result())[0];
						$subject = "Book successfully issued";
						
						$query = $con->prepare("SELECT title FROM book WHERE isbn = ?;");
						$query->bind_param("s", $isbn);
						$query->execute();
						$title = mysqli_fetch_array($query->get_result())[0];
						
						$query = $con->prepare("SELECT due_date FROM book_issue_log WHERE member = ? AND book_isbn = ?;");
						$query->bind_param("ss", $member, $isbn);
						$query->execute();
						$due_date = mysqli_fetch_array($query->get_result())[0];
						//$message = "The book '".$title."' with ISBN ".$isbn." has been issued to your account. The due date to return the book is ".$due_date.".";
						
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
						$mail->Subject ="書籍申請結果通知"; 				//郵件標題
						$mail->Body = "The book '".$title."' with ISBN ".$isbn." has been issued to your account. The due date to return the book is ".$due_date."."; //郵件內容
						$mail->IsHTML(false);							//郵件內容為html
						$mail->AddAddress("$to");					//收件者郵件及名稱

						if(!$mail->Send()){
							echo "寄信發生錯誤：" . $mail->ErrorInfo;
							//如果有錯誤會印出原因
							}
							else{
							echo "寄信成功";
							}

						//mail($to, $subject, $message, $header);
					}
				}
				if($requests > 0)
					echo success("通過了 ".$requests." 個申請");
				else
					echo error_without_field("No request selected");
			}
			
			if(isset($_POST['l_reject']))
			{
				$requests = 0;
				for($i=0; $i<$rows; $i++)
				{
					if(isset($_POST['cb_'.$i]))
					{
						$requests++;
						$request_id =  $_POST['cb_'.$i];
						
						$query = $con->prepare("SELECT member, book_isbn FROM pending_book_requests WHERE request_id = ?;");
						$query->bind_param("d", $request_id);
						$query->execute();
						$resultRow = mysqli_fetch_array($query->get_result());
						$member = $resultRow[0];
						$isbn = $resultRow[1];
						
						$query = $con->prepare("SELECT email FROM member WHERE username = ?;");
						$query->bind_param("s", $member);
						$query->execute();
						$to = mysqli_fetch_array($query->get_result())[0];
						$subject = "Book issue rejected";
						
						$query = $con->prepare("SELECT title FROM book WHERE isbn = ?;");
						$query->bind_param("s", $isbn);
						$query->execute();
						$title = mysqli_fetch_array($query->get_result())[0];
						//$message = "Your request for issuing the book '".$title."' with ISBN ".$isbn." has been rejected. You can request the book again or visit a librarian for further information.";
						
						$query = $con->prepare("DELETE FROM pending_book_requests WHERE request_id = ?");
						$query->bind_param("d", $request_id);
						if(!$query->execute())
							die(error_without_field("ERROR: Couldn\'t delete values"));
						

						
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
						$mail->Subject ="書籍申請結果通知"; 				//郵件標題
						$mail->Body = "Your request for issuing the book '".$title."' with ISBN ".$isbn." has been rejected. You can request the book again or visit a librarian for further information."; //郵件內容
						$mail->IsHTML(false);							//郵件內容為html
						$mail->AddAddress("$to");					//收件者郵件及名稱

						if(!$mail->Send()){
							echo "寄信發生錯誤：" . $mail->ErrorInfo;
							//如果有錯誤會印出原因
							}
							else{
							echo "寄信成功";
							}	
						//mail($to, $subject, $message, $header);
					}
				}
				if($requests > 0)
					echo success("拒絕了 ".$requests." 個申請");
				else
					echo error_without_field("No request selected");
			}