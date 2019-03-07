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
    <datalist id = "units"><option value = "袋"><option value = "個"></datalist>
    <form method = "post" style = "width:60%">
    <table style = "width:100%">
        <tr>
            <th><h1>品項</h1></th>
            <th><h1>數字</h1></th>
            <th><h1>單位</h1></th>
        </tr>
    <?php
        if(isset($_GET["clientID"])){
            $temp = $_GET["clientID"];
            $pieces = explode("-", $temp);
            $clientID = $pieces[0];
            $categoryID = $pieces[1];
        }
        else{
            header("Location: http://localhost/fillInTheFormSelect.php");
            die();
        }
        if(isset($_GET["date"])){
            $date = $_GET["date"];
            $replacedDate = str_replace("-", "", $date);
        }
        else{
            header("Location: http://localhost/fillInTheFormSelect.php");
            die();
        }

        $sql = "SELECT clientID FROM clientFormFillingInInfo WHERE clientID = ? AND date = ? AND who = ?";
        if($select = $database->prepare($sql)){
            $select->bind_param('sss', $clientID, $date, $userName);
            $select->execute();
            $result = $select->get_result();
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
        $formExist = (mysqli_num_rows($result) != 0);

        if($formExist){
            $sql = "SELECT goodsID, number, unit FROM zzSz" . $userName . "z" . $clientID . "z" . $replacedDate;
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
        
        $toClientList = array();
        $sql = "SELECT goodsID FROM ZZclient$clientID";
        if($selectFromGoodsList = $database->prepare($sql)){
            $selectFromGoodsList->execute();
            $result = $selectFromGoodsList->get_result();
            while($goodsID = $result->fetch_row()){
                $toClientList[] = $goodsID[0];
            }
            $listLength = count($toClientList);
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";

        $sql = "SELECT id, name FROM goodsInfo WHERE isHide = false ORDER BY supplierID ";
        if($select = $database->prepare($sql)){
            $select->execute();
            $goodsList = $select->get_result();
            $n = 0;
            while($eachGoods = $goodsList->fetch_row()){
                $isInTheList = FALSE;
                for($i = 0; $i < $listLength; $i++){
                    if($eachGoods[0] == $toClientList[$i]){
                        $isInTheList = TRUE;
                        break;
                    }
                }
                if($isInTheList){
                    echo "  <tr>";
                    echo "      <td><h2>$eachGoods[1]</h2><input type=\"hidden\" name=\"id$n\" value=\"$eachGoods[0]\"></td>";
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
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
    ?>
        </table>
        <button type = "submit" name = "fill">上傳</button>
    </form>
    <button onclick="if(confirm('確定返回嗎?')){window.location.assign('http://localhost/fillInTheFormSelect.php');}" class = "logoff" style = "width: 60%">返回</button>
        <?php
        if(isset($_POST["fill"])){
            $time = date("Y-m-d h:i:sa");
            $sql = "DELETE FROM clientFormFillingInInfo WHERE clientID = ? AND date = ?";
            if($delete = $database->prepare($sql)){
                $delete->bind_param('ss', $clientID, $date);
                $delete->execute();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
            $sql = "SELECT categoryID FROM clientsInfo WHERE ID = ?";
            if($select = $database->prepare($sql)){
                $select->bind_param('s', $clientID);
                $select->execute();
                $result = $select->get_result();
                if(mysqli_num_rows($result) == 1){
                    $temp = $result->fetch_row();
                    $clientCategoryID = $temp[0];
                }
                else{
                    echo "something is wrong!";
                    die();
                }
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
            if($formExist){
                $sql = "DROP TABLE zzSz" . $userName . "z" . $clientID . "z" . $replacedDate;
                if($drop = $database->prepare($sql)){
                    $drop->execute();
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
            }
            $sql = "CREATE TABLE zzSz" . $userName . "z" . $clientID . "z" . $replacedDate . " (goodsID INT UNSIGNED NOT NULL, number INT NOT NULL, unit VARCHAR(6) NOT NULL) COLLATE = utf8mb4_unicode_ci ENGINE = InnoDB";
            if($create = $database->prepare($sql)){
                $create->execute();
                $sql = "INSERT INTO zzSz" . $userName . "z" . $clientID . "z" . $replacedDate . " (goodsID, number, unit) VALUES ( ?, ?, ?)";
                if($clientCategoryID == 4){
                    $findSql = "SELECT quanlianSP, coefficient FROM goodsInfo WHERE ID = ?";
                }
                else if($clientCategoryID == 6){
                    $findSql = "SELECT zonchuSP, coefficient FROM goodsInfo WHERE ID = ?";
                }
                else if($clientCategoryID == 7){
                    $findSql = "SELECT coefficient FROM goodsInfo WHERE ID = ?";
                    $findNormalPriceSql = "SELECT price FROM zzclient$clientID WHERE goodsID = ?";
                }
                $commision = 0.0;
                for($i = 0; $i < $n; $i++){
                    if(isset($_POST["number" . $i]) && $_POST["number" . $i] != "0" && $_POST["number" . $i] != ""){
                        if($insert = $database->prepare($sql)){
                            $goodsID = $_POST["id" . $i];
                            $number = $_POST["number" . $i];
                            $unit = $_POST["unit" . $i];
                            $insert->bind_param('sss', $goodsID, $number, $unit);
                            $insert->execute();
                            if($unit == "包"){
                                $unitFix = 1.0;
                            }
                            else{
                                $fixSql = "SELECT howManyInside FROM goodsInfo WHERE ID = ?";
                                if($select = $database->prepare($fixSql)){
                                    $select->bind_param('s', $goodsID);
                                    $select->execute();
                                    $result = $select->get_result();
                                    $temp = $result->fetch_row();
                                    $unitFix = 1/intval($temp[0]);
                                }
                                else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
                            }
                            if($find = $database->prepare($findSql)){
                                $find->bind_param('s', $goodsID);
                                $find->execute();
                                $result = $find->get_result();
                                $temp = $result->fetch_row();
                                if($clientCategoryID == 7){
                                    if($findNormalPrice = $database->prepare($findNormalPriceSql)){
                                        $findNormalPrice->bind_param('s', $goodsID);
                                        $findNormalPrice->execute();
                                        $result = $findNormalPrice->get_result();
                                        $normalPrice = $result->fetch_row();
                                        $commision += $temp[0] * $normalPrice[0] * intval($number) * $unitFix;
                                    }
                                    else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
                                }
                                else{
                                    $commision += $temp[0] * $temp[1] * intval($number) * $unitFix;
                                }
                            }
                        }
                        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
                    }
                }
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
            $sql = "INSERT INTO clientFormFillingInInfo (clientID, date, fillingTime, commission, who) VALUES (?, ?, ?, ?, ?)";
            if($insert = $database->prepare($sql)){
                $insert->bind_param('sssss', $clientID, $date, $time, $commision, $userName);
                $insert->execute();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            $_POST = array();
            header("Refresh:0");
        }
    ?>
</body>
</html>