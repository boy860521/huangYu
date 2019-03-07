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
        // login and chech the database connection
        $loginUrl = "http://localhost/";
        $userName = $_SESSION["userName"];
        if($userName != "admin" || $userName == ""){
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
        $sql = "SELECT name, hide FROM userInfo WHERE name != 'admin'";
        if($select = $database->prepare($sql)){
            $select->execute();
            $result = $select->get_result();
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
    ?>

    <?php
        // create table displaying data
        echo "<table style=\"width:50%\">";
        $i = 0.3;
        echo "  <tr class=\"row\" style=\"animation-duration: ".$i."s\"><th><h1 style=\"text-align:left\">代號</h1></th></tr>";
        while($eachRow = $result->fetch_row()){
            if($eachRow[1] == 0){
                $i += 0.2;
                echo "  <tr class=\"row\" style=\"animation-duration: ".$i."s\"><td><h1>" . $eachRow[0] . "</h1></td></tr>";
            }
        }
        echo "</table>";
    ?>

    <?php
        if(isset($_POST["add"])){
            if($_POST["password"] != $_POST["confirm"]){
                echo "<script type=\"text/javascript\">alert(\"請確認密碼輸入正確\")</script>";
            }
            $userName = $_POST["userName"];
            $sql = "SELECT id FROM userInfo WHERE name = ?";
            if($select = $database->prepare($sql)){
                $select->bind_param('s', $userName);
                $select->execute();
                $result = $select->get_result();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            if(mysqli_num_rows($result) == 1){
                $sql = "UPDATE userinfo SET password = ? WHERE name = ?";
                if($update = $database->prepare($sql)){
                    $password = hash('sha384', $_POST["password"]);
                    $update->bind_param('ss', $password, $userName);
                    $update->execute();
                    $result = $update->get_result();
                    $_POST = array();
                    header("Refresh:0");
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
                echo "<script type=\"text/javascript\">alert(\"密碼已更改\")</script>";
            }
            else{
                $sql = "INSERT INTO userInfo (name, password) VALUES (?, ?)";
                if($insert = $database->prepare($sql)){
                    $password = hash('sha384', $_POST["password"]);
                    $insert->bind_param('ss', $userName, $password);
                    $insert->execute();
                    $result = $insert->get_result();
                    $_POST = array();
                    header("Refresh:0");
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            }
        }
    ?>
    <form method = "post" style = "width:60%">
        <input type = "text" placeholder="帳號" name = "userName" style = "width:33%" required>
        <input type = "password" placeholder = "密碼" name = "password" style = "width:33%" required>
        <input type = "password" placeholder = "確認密碼" name = "confirm" style = "width:33%" required>
        <button type = "submit" name = "add">加入此筆資料或更改密碼</button>
        <button onclick = 'window.location.assign("http://localhost/menu.php")' class = "logoff">返回</button>
    </form>
</body>
</html>