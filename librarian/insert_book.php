<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "verify_librarian.php";
	require "header_librarian.php";

?>

<html>
	<head>
		<title>Add book</title>

		<link rel="stylesheet" type="text/css" href="../css/global_styles.css" />
		<link rel="stylesheet" type="text/css" href="../css/form_styles.css" />
		<link rel="stylesheet" href="css/insert_book_style.css">
	</head>
	<body>
		<form class="cd-form" method="POST" action="#">
			<legend>輸入書的資訊</legend>
			
				<div class="error-message" id="error-message">
					<p id="error"></p>
				</div>
				
				<div class="icon">
					<input class="b-isbn" id="b_isbn" type="number" name="b_isbn" placeholder="ISBN" required />
				</div>
				
				<div class="icon">
					<input class="b-title" type="text" name="b_title" placeholder="書名" required />
				</div>
				
				<div class="icon">
					<input class="b-author" type="text" name="b_author" placeholder="作者" required />
				</div>
				
				<div>
				<h4>類別</h4>
				
					<p class="cd-select icon">
						<select class="b-category" name="b_category">
							<option>科幻</option>
							<option>人物傳記</option>
							<option>日本文學</option>
							<option>教育</option>
							<option>語言學習</option>
							<option>電腦資訊</option>
							<option>考試用書</option>
							<option>漫畫</option>
							<option>其他</option>
							
						</select>
					</p>
				</div>
				
				<div class="icon">
					<input class="b-price" type="number" name="b_price" placeholder="價格" required />
				</div>
				
				<div class="icon">
					<input class="b-copies" type="number" name="b_copies" placeholder="庫存" required />
				</div>
				
				<br />
				<input class="b-isbn" type="submit" name="b_add" value="新增" />
		</form>
	<body>
	
	<?php
		if(isset($_POST['b_add']))
		{
			$query = $con->prepare("SELECT isbn FROM book WHERE isbn = ?;");
			$query->bind_param("s", $_POST['b_isbn']);
			$query->execute();
			
			if(mysqli_num_rows($query->get_result()) != 0)
				echo error_with_field("書已經存在!", "b_isbn");
			else
			{
				$query = $con->prepare("INSERT INTO book VALUES(?, ?, ?, ?, ?, ?);");
				$query->bind_param("ssssdd", $_POST['b_isbn'], $_POST['b_title'], $_POST['b_author'], $_POST['b_category'], $_POST['b_price'], $_POST['b_copies']);
				
				if(!$query->execute())
					die(error_without_field("ERROR: 無法新增!"));
				echo success("成功新增!");
			}
		}
	?>
</html>