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
    <h1 class = "result" style = "animation-duration: 0.1s">總計</h1>
    <div id = "result"></div>
    <?php
        if(isset($_POST["submit"])){
            $fromDate = $_POST["fromDate"];
            $toDate = $_POST["toDate"];
            $supplierID = $_POST["supplierID"];
            $sum = array();

            $findFormSql = "SELECT date FROM supplierFormFillingInInfo WHERE date BETWEEN ? AND ? AND supplierID = ? ORDER BY date";
            if($findForm = $database->prepare($findFormSql)){
                $findForm->bind_param('sss', $fromDate, $toDate, $supplierID);
                $findForm->execute();
                $findFormResult = $findForm->get_result();
                while($eachForm = $findFormResult->fetch_row()){
                    $replacedDate = str_replace("-", "", $eachForm[0]);
                    $readFormSql = "SELECT goodsInfo.name, SF.number, SF.unit FROM zzpzadminz$supplierID"."z".$replacedDate." SF INNER JOIN goodsInfo ON goodsInfo.ID = SF.goodsID";
                    if($read = $database->prepare($readFormSql)){
                        $read->execute();
                        $result = $read->get_result();
                        if(mysqli_num_rows($result) != 0){
                            echo "<h1>$eachForm[0]</h1>";
                        }
                        while($eachRow = $result->fetch_row()){
                            echo "<p>$eachRow[0] - $eachRow[1] $eachRow[2]</p>";
                            if(!array_key_exists($eachRow[0], $sum)){
                                $sum[$eachRow[0]] = array();
                            }
                            if(!array_key_exists($eachRow[2], $sum[$eachRow[0]])){
                                $sum[$eachRow[0]][$eachRow[2]] = 0;
                            }
                            $sum[$eachRow[0]][$eachRow[2]] += $eachRow[1];
                        }
                    }
                    else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
                }
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            echo "<script type=\"text/javascript\">";
            $i = 0;
            $t = 0.3;
            foreach($sum as $goodsName => $goods){
                foreach($goods as $goodsUnit => $goodsNumber){
                    echo "var p$i = document.createElement(\"p\");";
                    echo "p$i.setAttribute(\"style\", \"animation-duration: $t"."s;\");";
                    echo "p$i.classList.add('result');";
                    echo "var textnode = document.createTextNode(\"$goodsName - $goodsNumber $goodsUnit"."\");";
                    echo "p$i.appendChild(textnode);";
                    echo "document.getElementById(\"result\").appendChild(p$i);";
                    $i++;
                    if($t < 1) $t += 0.15;
                    else $t += 0.05;
                }
            }
            if($i == 0){
                echo "var p0 = document.createElement(\"p\");";
                echo "p0.setAttribute(\"style\", \"animation-duration: 0.3s;\");";
                echo "p0.classList.add('result');";
                echo "var textnode = document.createTextNode(\"無資料\");";
                echo "p0.appendChild(textnode);";
                echo "document.getElementById(\"result\").appendChild(p0);";
            }
            echo "</script>";
            echo "<div style = \"width: 60%; background-color: #161616; height: 3px; margin-top: 80px;\"></div>";
            echo "<p style = \"color: #161616;\">以上為查詢結果</p>";
            echo "<div style = \"width: 60%; background-color: #161616; height: 3px; margin-bottom:100px;\"></div>";
        }
        else echo "<h1 class = \"tip\">請選擇日期範圍和廠商</h1>";
        
    ?>
    
    <form method = "post" style = "width:60%">
        <h1>從</h1>
        <input type = "date" name = "fromDate" required>
        <h1>到</h1>
        <input type = "date" name = "toDate" required>
        <select name = "supplierID">
            <?php
                $sql = "SELECT name, ID FROM suppliersInfo";
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
        <button type = "submit" name = "submit">確認</button>
    </form>
    <button onclick = 'window.location.assign("http://localhost/menu.php")' class = "logoff" style = "width: 60%">返回</button>
</body>
</html>