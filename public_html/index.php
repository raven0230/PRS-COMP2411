<body>
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../util/autoload.php";

$stmt = DBHelper::getConnection()->prepare("SELECT time AS 'time' FROM time_tester");
if ($stmt->execute()) {
    $stmt->bindColumn("time", $time);
    $stmt->fetch(PDO::FETCH_BOUND);
    echo $time;
}

?>
</body>
