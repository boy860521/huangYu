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
        $sql = "SELECT name, category, staff, clientsInfo.id, categoryID
            FROM clientsInfo
            INNER JOIN clientCategory ON clientsInfo.categoryID = clientCategory.id";
        if($select = $database->prepare($sql)){
            $select->execute();
            $result = $select->get_result();
        }
        else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
        $i = 0.1;
        echo "<table style=\"width:50%\">";
        echo "  <tr class=\"row\" style=\"animation-duration: ".$i."s\">";
        echo "       <th><h1 style=\"text-align:left\">名稱</h1></th>";
        echo "       <th><h1 style=\"text-align:left\">種類</h1></th>";
        echo "       <th><h1 style=\"text-align:left\">負責業務</h1></th>";
        echo "       <th><h1 style=\"text-align:left\">貨物清單</h1></th>";
        echo "  </tr>";
        while($eachRow = $result->fetch_row()){
            if($i <= 1) $i += 0.2;
            else $i += 0.05;
            echo "  <tr class=\"row\" style=\"animation-duration: ".$i."s\">";
            echo "      <td><p>" . $eachRow[0] . "</p></td>";
            echo "      <td><p>" . $eachRow[1] . "</p></td>";
            echo "      <td><p>" . $eachRow[2] . "</p></td>";
            echo "      <form method = \"get\" action = \"goodsList.php\"><td><button name = \"clientInfo\" value = \"" . $eachRow[3] . "-" . $eachRow[4] . "\">瀏覽清單</button></td></form>";
            echo "  </tr>";
        }
        echo "</table>";
    ?>
    <form method = "post" style = "width:60%">
        <input type = "text" placeholder="名稱" name = "clientName" style = "width:33%" required>
        <select name = "clientCategoryID" style = "width: 33%">
        <?php
            $sql = "SELECT category, ID FROM clientCategory";
            if($select = $database->prepare($sql)){
                $select->execute();
                $result = $select->get_result();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
    
            echo "";
            while($eachRow = $result->fetch_row()){
                echo "<option value = \"$eachRow[1]\">$eachRow[0]</option>";
            }
        ?>
        </select>
        <select name = "staff" style = "width:33%">
        <?php
            $sql = "SELECT name FROM userInfo WHERE hide = 0";
            if($select = $database->prepare($sql)){
                $select->execute();
                $result = $select->get_result();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            while($eachRow = $result->fetch_row()){
                echo "<option value = \"$eachRow[0]\">$eachRow[0]</option>";
            }
        ?>
        </select>
        <button type = "submit" name = "add">加入或更改</button>
        <button onclick = 'window.location.assign("http://localhost/menu.php")' class = "logoff">返回</button>
    </form>
    <?php
        if(isset($_POST["add"])){
            $clientName = $_POST["clientName"];
            $categoryID = $_POST["clientCategoryID"];
            $staff = $_POST["staff"];
            if(mysqli_num_rows($result) < 1){
                $_POST = array();
                header("Refresh:0");
                echo "<script type=\"text/javascript\">alert(\"無此業務資料，請先加上此業務\")</script>";
                die();
            }
            $sql = "SELECT id FROM clientsInfo WHERE name = ?";
            if($select = $database->prepare($sql)){
                $select->bind_param('s', $clientName);
                $select->execute();
                $result = $select->get_result();
            }
            else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
            if(mysqli_num_rows($result) == 1){
                $sql = "UPDATE clientsInfo SET categoryID = ?, staff = ? WHERE name = ?";
                if($update = $database->prepare($sql)){
                    $update->bind_param('sss', $categoryID, $staff, $clientName);
                    $update->execute();
                    $result = $update->get_result();
                    $_POST = array();
                    header("Refresh:0");
                }
                else echo "<h1 class=\"alarm\">Failed to prepare!</h1>";
                echo "<script type=\"text/javascript\">alert(\"資料已更改\")</script>";
            }
            else{
                $sql = "INSERT INTO clientsInfo (name, categoryID, staff) VALUES (?, ?, ?)";
                if($insert = $database->prepare($sql)){
                    $insert->bind_param('sss', $clientName, $categoryID, $staff);
                    $insert->execute();
                    $sql = "SELECT id FROM clientsInfo WHERE name = ?";
                    if($select = $database->prepare($sql)){
                        $select->bind_param('s', $clientName);
                        $select->execute();
                        $result = $select->get_result();
                        $id = $result->fetch_row();
                        if($categoryID == 7){
                            // for the normal selling path
                            $sql = "CREATE TABLE zzclient$id[0] (goodsID INT UNSIGNED NOT NULL, price FLOAT NOT NULL DEFAULT 0) COLLATE = utf8mb4_unicode_ci ENGINE = InnoDB";
                            if($create = $database->prepare($sql)){
                                $create->execute();
                                $_POST = array();
                                header("Refresh:0");
                            }
                            else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
                        }
                        else{
                            $sql = "CREATE TABLE ZZclient$id[0] (goodsID INT UNSIGNED NOT NULL) COLLATE = utf8mb4_unicode_ci ENGINE = InnoDB";
                            if($create = $database->prepare($sql)){
                                $create->execute();
                                $_POST = array();
                                header("Refresh:0");
                            }
                            else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
                        }
                    }
                    else echo "<h1 class=\"alarm\">Failed to prepare!<br> ---$sql--- </h1>";
                }
                else echo "<h1 class=\"alarm\">Failed to prepare ---$sql--- !</h1>";
            }
        }
    ?>
</body>
</html>