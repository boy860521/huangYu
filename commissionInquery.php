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
    <?php
        if(isset($_POST["submit"])){
            $fromDate = $_POST["fromDate"];
            $toDate = $_POST["toDate"];
            $staff = $_POST["staff"];
            $client = $_POST["client"];
            
            if($client == "全部"){
                $sql = "SELECT SUM(commission) FROM clientFormFillingInInfo WHERE date BETWEEN ? AND ? AND who = ?";
                if($select = $database->prepare($sql)){
                    $select->bind_param('sss', $fromDate, $toDate, $staff);
                    $select->execute();
                    $result = $select->get_result();
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
            }
            else{
                $sql = "SELECT SUM(commission) FROM clientFormFillingInInfo WHERE date BETWEEN ? AND ? AND who = ? AND clientID = ?";
                if($select = $database->prepare($sql)){
                    $select->bind_param('ssss', $fromDate, $toDate, $staff, $client);
                    $select->execute();
                    $result = $select->get_result();
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
            }
            $sum = $result->fetch_row();
            echo "<h1 class = \"result\">查詢結果為: $sum[0]</h1>";
        }
        else echo "<h1 class = \"tip\">請選擇日期範圍、員工和客戶</h1>";
    ?>
    <form method = "post" style = "width:60%">
        <h1 class="row" style="animation-duration: 0.2s">從</h1>
        <input type = "date" name = "fromDate" class="row" style="animation-duration: 0.4s" required>
        <h1 class="row" style="animation-duration: 0.6s">到</h1>
        <input type = "date" name = "toDate" class="row" style="animation-duration: 0.8s" required>
        <select name = "staff" class="row" style="animation-duration: 0.9s">
            <?php
                $sql = "SELECT name FROM userInfo WHERE hide = 0";
                if($select = $database->prepare($sql)){
                    $select->execute();
                    $result = $select->get_result();
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
                while($eachRow = $result->fetch_row()){
                    echo "<option value\"$eachRow[0]\">$eachRow[0]</option>";
                }
            ?>
        </select>
        <select name = "client" class="row" style="animation-duration: 1.0s">
            <option value="全部">全部</option>
            <?php
                $sql = "SELECT name, ID FROM clientsInfo";
                if($select = $database->prepare($sql)){
                    $select->execute();
                    $result = $select->get_result();
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
                while($eachRow = $result->fetch_row()){
                    echo "<option value=\"$eachRow[1]\">$eachRow[0]</option>";
                }
            ?>
        </select>
        <button type = "submit" name = "submit" class="row" style="animation-duration: 1.1s">確認</button>
    </form>
    <button onclick = 'window.location.assign("http://localhost/menu.php")' class="row" style="animation-duration: 1.3s;width: 60%; background-color: #00000070;">返回</button>
</body>
</html>