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
    <h1 class = "tip">請填上日期，並選擇廠商。</h1>
    <form method = "get" action = "purchasingNoting.php" style = "width:60%">
    <input type = "date" name = "date" required>
    <?php
        $sql = "SELECT id, name FROM suppliersInfo";
        if($select = $database->prepare($sql)){
            $select->execute();
            $result = $select->get_result();
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
        echo "       <h1 style=\"text-align:left\">報表連結</h1>";
        $i = 0.5;
        while($eachRow = $result->fetch_row()){
            echo "      <button name = \"supplierID\" value = \"" . $eachRow[0] . "\" class=\"row\" style=\"animation-duration: ".$i."s\">" . $eachRow[1] . "</button>";
            $i += 0.3;
        }
    ?>
    </form>
    <button onclick = 'window.location.assign("http://localhost/menu.php")' class = "row" style = "width: 60%; background-color: #00000070; animation-duration: <?php echo $i?>s;">返回</button>
</body>
</html>