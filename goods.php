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
        $loginUrl = "http://localhost";
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
        $sql = "SELECT suppliersinfo.name, goodsinfo.name, purchasePrice, quanlianSP, zonchuSP, coefficient, goodsCategory.category, isProxy, isHide, supplierID, howManyInside
            FROM goodsInfo
            INNER JOIN suppliersinfo ON supplierID = suppliersinfo.id
            INNER JOIN goodsCategory ON goodsCategoryID = goodsCategory.id
            ORDER BY supplierID, isHide, goodsCategoryID, isProxy";
        if($select = $database->prepare($sql)){
            $select->execute();
            $result = $select->get_result();
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
        $i = 0.1;
        echo "<table style=\"width:90%\">";
        echo "  <tr class=\"row\" style=\"animation-duration: ".$i."s\">";
        echo "       <th><h2>廠商</h2></th>";
        echo "       <th><h2>品項</h2></th>";
        echo "       <th><h2>一箱有幾份</h2></th>";
        echo "       <th><h2>買入價</h2></th>";
        echo "       <th><h2>全聯出貨價</h2></th>";
        echo "       <th><h2>總處出貨價</h2></th>";
        echo "       <th><h2>傭金係數</h2></th>";
        echo "       <th><h2>種類</h2></th>";
        echo "       <th><h2>代送</h2></th>";
        echo "   </tr>";
        $notedIDForRowspan = "It can't be the same.";
        while($eachRow = $result->fetch_row()){
            if($i <= 1.0) $i += 0.15;
            else $i += 0.05;
            echo "  <tr class=\"row\" style=\"animation-duration: ".$i."s\">";
            if($notedIDForRowspan != $eachRow[9]){
                $sql = "SELECT COUNT(id) FROM goodsInfo WHERE supplierID = ?";
                if($count = $database->prepare($sql)){
                    $notedIDForRowspan = $eachRow[9];
                    $count->bind_param('s', $notedIDForRowspan);
                    $count->execute();
                    $countResult = $count->get_result();
                    $howMany = $countResult->fetch_row();
                    echo "      <td rowspan = \"" . $howMany[0] . "\"><p>" . $eachRow[0] . "</td></p>";
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            }
            echo "      <td><p>" . $eachRow[1] . "</p></td><td><p>" . $eachRow[10] . "</p></td><td><p>" . $eachRow[2] . "</p></td><td><p>" . $eachRow[3] . "</p></td>";
            echo "      <td><p>" . $eachRow[4] . "</p></td><td><p>" . $eachRow[5] . "</p></td><td><p>" . $eachRow[6] . "</p></td>";
            echo "      <td><p>";
            if($eachRow[7]){
                echo "O";
            } 
            else{
                echo "X";
            }
            echo "</p></td>";
            echo "  </tr>";
        }
        echo "</table>";
    ?>
    <?php
        if(isset($_POST["add"])){
            $supplierID = $_POST["supplierID"];
            $categoryID = $_POST["categoryID"];
            $goodsName = $_POST["goodsName"];
            $howManyInside = $_POST["howManyInside"];
            $purchasePrice = $_POST["purchasePrice"];
            $quanlian = $_POST["quanlian"];
            $zonchu = $_POST["zonchu"];
            $coefficient = $_POST["coefficient"];
            $isProxy = $_POST["isProxy"];
            $sql = "SELECT id FROM goodsInfo WHERE name = ? and supplierID = ?";
            if($select = $database->prepare($sql)){
                $select->bind_param('ss', $goodsName, $supplierID);
                $select->execute();
                $result = $select->get_result();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            if(mysqli_num_rows($result) == 1){
                $sql = "UPDATE goodsInfo SET howManyInside = ?, purchasePrice = ?, quanlianSP = ?, zonchuSP = ?, coefficient = ?, goodsCategoryID = ?, isProxy = ? WHERE name = ? and supplierID = ?";
                if($update = $database->prepare($sql)){
                    $prices = $result->fetch_row();
                    $update->bind_param('ssssssiss', $howManyInside, $purchasePrice, $quanlian, $zonchu, $coefficient, $categoryID, $isProxy, $goodsName, $supplierID);
                    $update->execute();
                    $result = $update->get_result();
                    $_POST = array();
                    header("Refresh:0");
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
                echo "<script type=\"text/javascript\">alert(\"資料已更改\")</script>";
            }
            else{
                $sql = "INSERT INTO goodsInfo (supplierID, name, howManyInside, purchasePrice, quanlianSP, zonchuSP, coefficient, goodscategoryID, isProxy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                if($insert = $database->prepare($sql)){
                    $insert->bind_param('ssssssssi', $supplierID, $goodsName, $howManyInside, $purchasePrice, $quanlian, $zonchu, $coefficient, $categoryID, $isProxy);
                    $insert->execute();
                    $result = $insert->get_result();
                    $_POST = array();
                    header("Refresh:0");
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            }
        }
    ?>
    <form method = "post" style = "width:90%">
        <?php
            $sql = "SELECT name, ID FROM suppliersInfo";
            if($select = $database->prepare($sql)){
                $select->execute();
                $result = $select->get_result();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
    
            echo "<select name = \"supplierID\" style = \"width: 25%\">";
            while($eachRow = $result->fetch_row()){
                echo "<option value = \"$eachRow[1]\">$eachRow[0]</option>";
            }
            echo "</select>";
        ?>
        <input type = "text" placeholder="名稱" name = "goodsName" style = "width:55%" required>
        <input type = "number" placeholder = "份數" name = "howManyInside" min = "0" style = "width:15%" required>
        <input type = "text" placeholder = "買入價" name = "purchasePrice" style = "width:14%" required>
        <input type = "text" placeholder = "全聯出貨價" name = "quanlian" style = "width:14%" required>
        <input type = "text" placeholder = "總處出貨價" name = "zonchu" style = "width:14%" required>
        <input type = "text" placeholder = "傭金係數" name = "coefficient" style = "width:14%" required>
        <?php
            $sql = "SELECT category, ID FROM goodsCategory";
            if($select = $database->prepare($sql)){
                $select->execute();
                $result = $select->get_result();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
    
            echo "<select name = \"categoryID\" style = \"width: 25%\">";
            while($eachRow = $result->fetch_row()){
                echo "<option value = \"$eachRow[1]\">$eachRow[0]</option>";
            }
            echo "</select>";
        ?>
        <select name = "isProxy" style = "width:15%">
            <option value = "1">是</option>
            <option value = "0">否</option>
        </select>
        <button type = "submit" name = "add">加入或更改此筆資料</button>
        <button onclick = 'window.location.assign("http://localhost/menu.php")' class = "logoff">返回</button>
    </form>
</body>
</html>