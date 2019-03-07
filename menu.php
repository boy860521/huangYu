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
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans+TC:300" rel="stylesheet">
    <script src="main.js"></script>
</head>
<?php
    $loginUrl = "http://localhost/";
    $userName = $_SESSION["userName"];
    if($userName == ""){
        sleep(3);
        $_SESSION["unauthorizedLogin"] = "true";
        header("Location: " . $loginUrl);
        die();
    }
    echo "<h1 class = \"result\">Hello " . $userName . "!</h1>";
?>
<body>
    
        <button type = "button" onclick = 'window.location.assign("http://localhost/staffs.php")' id = "0" class="rowLikeDisplay" style="animation-duration: 0.5s">員工資料</button>
        <button type = "button" onclick = 'window.location.assign("http://localhost/suppliers.php")' id = "1" class="rowLikeDisplay" style="animation-duration: 0.55s">廠商資料</button>
        <button type = "button" onclick = 'window.location.assign("http://localhost/goodsCategory.php")' id = "2" class="rowLikeDisplay" style="animation-duration: 0.65s">貨物種類資料</button>
        <button type = "button" onclick = 'window.location.assign("http://localhost/goods.php")' id = "3" class="rowLikeDisplay" style="animation-duration: 0.7s">貨物資料</button>
        <button type = "button" onclick = 'window.location.assign("http://localhost/clientCategory.php")' id = "4" class="rowLikeDisplay" style="animation-duration: 0.75s">客戶種類資料</button>
        <button type = "button" onclick = 'window.location.assign("http://localhost/clients.php")' id = "5" class="rowLikeDisplay" style="animation-duration: 0.8s">客戶資料</button>
        <button type = "button" onclick = 'window.location.assign("http://localhost/purchasingNotingSelect.php")' id = "6" class="rowLikeDisplay" style="animation-duration: 0.85s">進貨資料</button>
        <button type = "button" onclick = 'window.location.assign("http://localhost/goodsPurchasingInquery.php")' class="rowLikeDisplay" style="animation-duration: 0.9s">進貨查詢</button>
        <button type = "button" onclick = 'window.location.assign("http://localhost/goodsSellingInquery.php")' class="rowLikeDisplay" style="animation-duration: 0.95s">出貨查詢</button>
        <button type = "button" onclick = 'window.location.assign("http://localhost/commissionInquery.php")' class="rowLikeDisplay" style="animation-duration: 1s">查詢傭金</button>
        <button type = "button" onclick = 'window.location.assign("http://localhost/fillInTheFormSelect.php")' class="rowLikeDisplay" style="animation-duration: 1.05s">填寫報表</button>
    <?php
        if($userName != "admin"){
            echo "<script type=\"text/javascript\">document.getElementById(\"0\").remove();";
            echo "document.getElementById(\"1\").remove();";
            echo "document.getElementById(\"2\").remove();";
            echo "document.getElementById(\"3\").remove();";
            echo "document.getElementById(\"4\").remove();";
            echo "document.getElementById(\"6\").remove();";
            echo "document.getElementById(\"5\").remove();</script>";
        }
    ?>
    <form method="post" class="rowLikeDisplay" style="animation-duration: 1.1s">
        <button type = "submit" name = "logoff" class = "logoff">登出</button>
    </form>
    <?php
        if(isset($_POST["logoff"])){
            session_unset();
            session_destroy();
            header("Location: " . $loginUrl);
            die();
        }
    ?>
</body>
</html>