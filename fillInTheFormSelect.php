<?php
  session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>貨物存銷紀錄系統</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="css/default.css" />
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans+TC:400" rel="stylesheet">
    <script src="main.js"></script>
</head>
<body>
    <?php
        // filling with staff info
        // login and check the database connection
        $loginUrl = "http://localhost/";
        $userName = $_SESSION["userName"];
        if($userName == ""){
            sleep(3);
            $_SESSION["unauthorizedLogin"] = "true";
            header("Location: " . $loginUrl);
            die();
        }
        $database = new mysqli("localhost", "root", "", "huangyu");
        if($database->connect_error){
            echo "<h1 class=\"alarm\">錯誤!<br>與資料庫無連接</h1>";
            die("Connection failed: " . $database->connect_error);
        }
    ?>
    <h1 class = "tip">請填上日期，並選擇客戶。</h1>
    <form method = "get" action = "fillInTheForm.php" style = "width:60%">
    <input type = "date" name = "date" required>
    <?php
        echo "  <th><h1 style=\"text-align:left\">報表連結</h1></th>";
        $sql = "SELECT name, id, categoryID FROM clientsInfo WHERE staff = ?";
        if($select = $database->prepare($sql)){
            $select->bind_param('s', $userName);
            $select->execute();
            $result = $select->get_result();
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
        $i = 0.5;
        while($eachRow = $result->fetch_row()){
            echo "      <button name = \"clientID\" value = \"" . $eachRow[1] . "-". $eachRow[2] . "\" class=\"row\" style=\"animation-duration: ".$i."s\">" . $eachRow[0] . "</button></h1>";
            $i += 0.3;
        }
        $sql = "SELECT name, id, categoryID FROM clientsInfo WHERE staff != ?";
        if($select = $database->prepare($sql)){
            $select->bind_param('s', $userName);
            $select->execute();
            $result = $select->get_result();
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
        while($eachRow = $result->fetch_row()){
            echo "      <button name = \"clientID\" value = \"" . $eachRow[1] . "-". $eachRow[2] . "\" class=\"row\" style=\"animation-duration: ".$i."s\">" . $eachRow[0] . "</button></h1>";
            $i += 0.3;
        }
    ?>
    </form>
    <button onclick = 'window.location.assign("http://localhost/menu.php")' class = "row" style = "width: 60%; background-color: #00000070; animation-duration: <?php echo $i?>s;">返回</button>
</body>
</html>