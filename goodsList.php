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
    ?>

    <?php
        if(isset($_GET["clientInfo"])){
            $temp = $_GET["clientInfo"];
            $pieces = explode("-", $temp);
            $clientID = $pieces[0];
            $categoryID = $pieces[1];
        }
        else{
            header("Location: http://localhost/clients.php");
            die();
        }
        if($categoryID == "7"){
            echo "<form method = \"post\" style = \"width: 40%\">";
            echo "<button type = \"submit\" name = \"submit\" >更改價錢</button>";
            $toClientList = array();
            $priceList = array();
            $sql = "SELECT goodsID, price FROM ZZclient$clientID";
            if($selectFromGoodsList = $database->prepare($sql)){
                $selectFromGoodsList->execute();
                $result = $selectFromGoodsList->get_result();
                while($eachGoods = $result->fetch_row()){
                    $toClientList[] = $eachGoods[0];
                    $priceList[] = $eachGoods[1];
                }
                $listLength = count($toClientList);
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            $sql = "SELECT id, name FROM goodsInfo ORDER BY supplierID";
            if($select = $database->prepare($sql)){
                $select->execute();
                $goodsList = $select->get_result();
                while($eachGoods = $goodsList->fetch_row()){
                    echo "  <button name = \"goodsButton\" value = \"" . $eachGoods[0] . "\"";
                    $isInTheList = FALSE;
                    for($i = 0; $i < $listLength; $i++){
                        if($eachGoods[0] == $toClientList[$i]){
                            $isInTheList = TRUE;
                            break;
                        }
                    }
                    if($isInTheList){
                        echo "class = \"inTheList\">" . $eachGoods[1] . "</button>";
                        echo "<input type = \"text\" name = \"priceForGoods$eachGoods[0]\" value = \"$priceList[$i]\">";
                    }
                    else{
                        echo "class = \"notInTheList\">" . $eachGoods[1] . "</button>";
                    }
                }
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            echo "</form>";
        }
        else{
            echo "<form method = \"post\" style = \"width: 40%\">";
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
            $sql = "SELECT id, name FROM goodsInfo ORDER BY supplierID";
            if($select = $database->prepare($sql)){
                $select->execute();
                $goodsList = $select->get_result();
                while($eachGoods = $goodsList->fetch_row()){
                    echo "  <button name = \"goodsButton\" value = \"" . $eachGoods[0] . "\"";
                    $isInTheList = FALSE;
                    for($i = 0; $i < $listLength; $i++){
                        if($eachGoods[0] == $toClientList[$i]){
                            $isInTheList = TRUE;
                            break;
                        }
                    }
                    if($isInTheList){
                        echo "class = \"inTheList\">";
                    }
                    else{
                        echo "class = \"notInTheList\">";
                    }
                    echo $eachGoods[1] . "</button>";
                }
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            echo "</form>";
        }
    ?>
    <?php
        if(isset($_POST["goodsButton"])){
            $goodsID = $_POST["goodsButton"];
            $sql = "SELECT goodsID FROM ZZclient$clientID WHERE goodsID = ?";
            if($select = $database->prepare($sql)){
                $select->bind_param('s', $goodsID);
                $select->execute();
                $result = $select->get_result();
                $temp = $result->fetch_row();
                $goodsListID = $temp[0];
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            if(mysqli_num_rows($result) == 1){
                $sql = "DELETE FROM ZZclient$clientID WHERE goodsID = ?";
                if($delete = $database->prepare($sql)){
                    $delete->bind_param('s', $goodsListID);
                    $delete->execute();
                    $_POST = array();
                    header("Refresh:0");
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            }
            else{
                $sql = "INSERT INTO ZZclient$clientID (goodsID) VALUES(?)";
                if($insert = $database->prepare($sql)){
                    $insert->bind_param('s', $goodsID);
                    $insert->execute();
                    $_POST = array();
                    header("Refresh:0");
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            }
        }
        if(isset($_POST["submit"])){
            $sql = "UPDATE ZZclient$clientID SET price = ? WHERE goodsID = ?";
            for($i = 0; $i < $listLength; $i++){
                if($update = $database->prepare($sql)){
                    echo $toClientList[$i]."-";
                    $price = $_POST["priceForGoods$toClientList[$i]"];
                    echo $_POST["priceForGoods$toClientList[$i]"];
                    $goodsID = $toClientList[$i];
                    $update->bind_param('ss', $price, $goodsID);
                    $update->execute();
                    //$_POST = array();
                    header("Refresh:0");
                }
            }
        }
    ?>
    <button onclick = 'window.location.assign("http://localhost/clients.php")' class = "logoff" style = "width: 60%">返回</button>
</body>
</html>