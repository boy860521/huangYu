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
        // login and check the database connection
        $loginUrl = "http://localhost/";
        $userName = $_SESSION["userName"];
        if($userName == ""){
            sleep(3);
            $_SESSION["unauthorizedLogin"] = "true";
            header("Location: " . $loginUrl);
            die();
        }
        date_default_timezone_set("Asia/Taipei");
        $database = new mysqli("localhost", "root", "", "huangyu");
        if($database->connect_error){
            echo "<h1 class=\"alarm\">錯誤!<br>與資料庫無連接</h1>";
            die("Connection failed: " . $database->connect_error);
        }
    ?>
    <datalist id = "units"><option value = "包"><option value = "個"></datalist>
    <form method = "post" style = "width:60%">
    <table style = "width:100%">
        <tr>
            <th><h1>品項</h1></th>
            <th><h1>數字</h1></th>
            <th><h1>單位</h1></th>
        </tr>
    <?php
        if(isset($_GET["supplierID"])){
            $supplierID = $_GET["supplierID"];
        }
        else{
            header("Location: http://localhost/purchasingNotingSelect.php");
            die();
        }
        if(isset($_GET["date"])){
            $date = $_GET["date"];
            $replacedDate = str_replace("-", "", $date);
        }
        else{
            header("Location: http://localhost/purchasingNotingSelect.php");
            die();
        }

        $sql = "SELECT supplierID FROM supplierformfillingininfo WHERE supplierID = ? AND date = ?";
        if($select = $database->prepare($sql)){
            $select->bind_param('ss', $supplierID, $date);
            $select->execute();
            $result = $select->get_result();
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
        $formExist = (mysqli_num_rows($result) != 0);

        if($formExist){
            $sql = "SELECT goodsID, number, unit FROM zzpz" . $userName . "z" . $supplierID . "z" . $replacedDate;
            if($select = $database->prepare($sql)){
                $select->execute();
                $result = $select->get_result();
                $formDataID = array();
                $formDataNumber = array();
                $formDataUnit = array();
                while($eachRow = $result->fetch_row()){
                    $formID[] = $eachRow[0];
                    $formNumber[] = $eachRow[1];
                    $formUnit[] = $eachRow[2];
                }
                $howManyRowInForm = count($formID);
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
        }

        $sql = "SELECT id, name FROM goodsInfo WHERE supplierID = ?";
        if($select = $database->prepare($sql)){
            $select->bind_param('s', $supplierID);
            $select->execute();
            $goodsList = $select->get_result();
            $n = 0;
            while($eachGoods = $goodsList->fetch_row()){
                echo "  <tr>";
                echo "      <td><p>$eachGoods[1]</p><input type=\"hidden\" name=\"id$n\" value=\"$eachGoods[0]\"></td>";
                if($formExist){
                    for($i = 0; $i < $howManyRowInForm; $i++){
                        if($eachGoods[0] == $formID[$i]){
                            echo "      <td><input type=\"number\" name=\"number$n\" min=\"0\" value=\"$formNumber[$i]\"></td>";
                            echo "      <td><input type=\"text\" name=\"unit$n\" value=\"$formUnit[$i]\" list=\"units\"></td>";
                            break;
                        }
                    }
                    if($i == $howManyRowInForm){
                        echo "      <td><input type=\"number\" name=\"number$n\" min=\"0\"></td>";
                        echo "      <td><input type=\"text\" name=\"unit$n\" value=\"包\" list=\"units\"></td>";
                    }
                }
                else{
                    echo "      <td><input type=\"number\" name=\"number$n\" min=\"0\"></td>";
                    echo "      <td><input type=\"text\" name=\"unit$n\" value=\"包\" list=\"units\"></td>";
                }
                echo "  </tr>";
                $n++;
            }
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
    ?>
        </table>
        <button type = "submit" name = "fill">上傳</button>
    </form>
    <button onclick="if(confirm('確定返回嗎?')){window.location.assign('http://localhost/purchasingNotingSelect.php');}" class = "logoff" style = "width: 60%">返回</button>
        <?php
        if(isset($_POST["fill"])){
            $time = date("Y-m-d h:i:sa");
            if($formExist){
                $sql = "DROP TABLE zzpz" . $userName . "z" . $supplierID . "z" . $replacedDate;
                if($drop = $database->prepare($sql)){
                    $drop->execute();
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
            }
            $sql = "CREATE TABLE zzpz" . $userName . "z" . $supplierID . "z" . $replacedDate . " (goodsID INT UNSIGNED NOT NULL, number INT UNSIGNED NOT NULL, unit VARCHAR(6) NOT NULL) COLLATE = utf8mb4_unicode_ci ENGINE = InnoDB";
            
            if($create = $database->prepare($sql)){
                $create->execute();
                $sql = "INSERT INTO zzpz" . $userName . "z" . $supplierID . "z" . $replacedDate . " (goodsID, number, unit) VALUES ( ?, ?, ?)";
                for($i = 0; $i < $n; $i++){
                    if(isset($_POST["number" . $i])){
                        if($insert = $database->prepare($sql)){
                            $goodsID = $_POST["id" . $i];
                            $number = $_POST["number" . $i];
                            $unit = $_POST["unit" . $i];
                            $insert->bind_param('sss', $goodsID, $number, $unit);
                            $insert->execute();
                        }
                        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
                    }
                }
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";

            $sql = "INSERT INTO supplierFormFillingInInfo (supplierID, date, fillingTime) VALUES (?, ?, ?)";
            if($insert = $database->prepare($sql)){
                $insert->bind_param('sss', $supplierID, $date, $time);
                $insert->execute();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            $_POST = array();
            header("Refresh:0");
        }
    ?>
</body>
</html>