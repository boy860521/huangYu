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
    <form method="post" style = "width: 50%">
        <input type = "text" placeholder = "帳號" name = "userName" required>
        <br>
        <input type = "password" placeholder = "密碼" name = "password">
        <br>
        <button type = "submit" name = "login">登入</button>
    </form>
    <?php

        if(isset($_SESSION["unauthorizedLogin"])){
            echo "<script type=\"text/javascript\">alert(\"請登入後再使用此\")</script>";
        }
        $menuUrl = "http://localhost/menu.php";
        date_default_timezone_set("Asia/Taipei");
        $database = new mysqli("localhost", "root", "", "huangyu");
        if($database->connect_error){
            echo "<h1 class=\"alarm\">錯誤!<br>與資料庫無連接</h1>";
            die("Connection failed: " . $database->connect_error);
        }
        if(isset($_POST["login"])){
            $userName = $_POST["userName"];
            $password = hash("sha384", $_POST["password"]);
            $sql = "SELECT id FROM userInfo WHERE name = ? AND password = ?";
            if($select = $database->prepare($sql)){
                $select->bind_param('ss', $userName, $password);
                $select->execute();
                $result = $select->get_result();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            if(mysqli_num_rows($result) == 0){
                $database->close();
                echo "<script type=\"text/javascript\">alert(\"帳號或密碼錯誤\")</script>";
                $_POST = array();
                header("Refresh:0");
            }
            else{
                $sql = "INSERT INTO loginInfo (who, time) VALUES (?, ?)";
                if($insert = $database->prepare($sql)){
                    $time = date("Y-m-d h:i:sa");
                    $insert->bind_param('ss', $userName, $time);
                    $insert->execute();
                    $result = $insert->get_result();
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
                $_SESSION["userName"] = $userName;
                header("Location: " . $menuUrl);
                die();
            }
            $database->close();
        }
    ?>
</body>
</html>