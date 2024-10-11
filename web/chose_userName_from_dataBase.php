<?php
ini_set('display_errors', 1); // PHPがエラーを吐いたら表示する

require './vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->load(); // .envを使用する

// データベースに接続するための情報を用意する関数
function connectMysql() {
    $dbname = getenv("DB_DSN");
    $userName = getenv("DB_USER");
    $pass = getenv("DB_PASSWORD");
  
    $pdo = new PDO(
        $dbname,
        $userName,
        $pass,
        [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    return $pdo;
  }
try{
    // ランダムに3人の名前を取得するクエリ
    $pdo = connectMysql();
    $sql = 'SELECT holiday_dinnerStartTime FROM mnsn_sheet_linebot_test ORDER BY RAND() LIMIT 3';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // 結果を配列に格納
    $groupMember = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($groupMember as $item){
      $groupMember_array[] = $item['holiday_dinnerStartTime'];
    }

    // 配列の内容を表示
     error_log(print_r($groupMember_array , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>