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
        $sql = "SELECT name FROM suppliersInfo";
        if($select = $database->prepare($sql)){
            $select->execute();
            $result = $select->get_result();
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
        
        echo "<table style=\"width:58%\">";
        $i = 0.5;
        echo "  <tr class=\"row\" style=\"animation-duration: ".$i."s\">";
        echo "       <th><h1 style=\"text-align:left\">名稱</h1></th>";
        echo "   </tr>";
        while($eachRow = $result->fetch_row()){
            $i += 0.2;
            echo "  <tr class=\"row\" style=\"animation-duration: ".$i."s\"><td><h1>" . $eachRow[0] . "</h1></td></tr>";
        }
        echo "</table>";
        if(isset($_POST["add"])){
            $supplierName = $_POST["supplierName"];
            $sql = "SELECT * FROM suppliersInfo WHERE name = ?";
            if($select = $database->prepare($sql)){
                $select->bind_param('s', $supplierName);
                $select->execute();
                $result = $select->get_result();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            if(mysqli_num_rows($result) == 1){
                echo "<script type=\"text/javascript\">alert(\"已存在此廠商\")</script>";
            }
            else{
                $sql = "INSERT INTO suppliersInfo (name) VALUES (?)";
                if($insert = $database->prepare($sql)){
                    $insert->bind_param('s', $supplierName);
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
        <input type = "text" placeholder="名稱" name = "supplierName" style = "width:100%" required>
        <button type = "submit" name = "add">加入此筆資料</button>
        <button onclick = 'window.location.assign("http://localhost/menu.php")' class = "logoff">返回</button>
    </form>
</body>
</html>