<?php
ini_set('display_errors', 1); // PHPがエラーを吐いたら表示する

require './vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->load(); // .envを使用する

require_once(dirname(__FILE__) . "/chatapi.php"); //ライブラリの読み込み
// require './chatapi.php';

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


// DBの中身を取得する関数
function getMnsn($pass_encrypt){
    $pdo = connectMysql(); // DBとの接続開始
    $stmt = $pdo->prepare("SELECT * FROM mnsn_sheet where :pass_hash = pass_hash ORDER BY id DESC");
    $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
    //$gender = "女性";
    //$stmt = $pdo->prepare("SELECT * FROM mnsn_sheet where :gender = gender");
    // $stmt->bindValue(':gender', $gender, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
    $stmt->execute();
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC); //全件取得
    // ============= ここまでDBからの取得 =============

    $job_sleepQuality = (string)$all[0]["job_sleepQuality"]; //睡眠の質_仕事の日

    $job_breakfastFreq = (string)$all[0]["job_breakfastFreq"];//朝食_仕事の日
    $snackFreq = (string)$all[0]["snackFreq"];//間食
    $tabacco = (string)$all[0]["tabacco"];//タバコ有無
    $tabaccoNum = (string)$all[0]["tabaccoNum"];//タバコ本数
    $sake = (string)$all[0]["sake"];//飲酒有無
    $walking_Yes_job = (string)$all[0]["job_pedometerYes"];// 万歩計所持・歩数（仕事の日）
    $walking_No_job = (string)$all[0]["job_pedometerNo"];// 万歩計未所持・歩数（仕事の日）


    $message = "あなたは生活習慣病の指導医師です．私の生活習慣をもとに，日常的な目標を目標だけ答えてください．";
    $message = $message . "睡眠の質は" . $job_sleepQuality . ".";
    $message = $message . "朝食頻度は" . $job_breakfastFreq . ".";
    $message = $message . "たばこの有無は" . $tabacco . ".";
    $message = $message . "お酒の頻度は" . $sake . ".";

    // $message = "こんにちわ！！！";

    //$message_from_gpt = call_chatGPT($message); // GPTにプロンプトを送信
    $message_from_gpt = $message;


    // // $mnsn = implode(",",$all[0]); // 問診票の内容をすべて入れる
    // if (!empty($all[0])) {
    //   $mnsn = "からじゃない";
    // } else {
    //     // $all が空の場合の処理
    //     $mnsn = "なにもない";
    // }

    // $mnsn = gettype($all[0]["job_sleepQuality"]);
    // $mnsn = $job_sleepQuality;

    // $mnsn = count($all);
    // $mnsn = "こんにちは";

    // return $mnsn;
    return $message_from_gpt;
}

function main_backend(){
  $institution = '市医師会成人病センター';
  $login_num = '115360';
  $year = '1993';
  $month = '10';
  $day = '29';

  // $institution = '小池クリニック';
  // $login_num = 'test';
  // $year = '2000';
  // $month = '01';
  // $day = '01';


  $pass_encrypt = $institution . $login_num . $year . $month . $day; //ログイン認証用の文字列を作成（各要素を結合）
  $pass_encrypt = hash('sha256', $pass_encrypt); //結合した要素を暗号化

  //$mnsn = getMnsn($pass_encrypt); // DBから問診票を取得
  //return $mnsn;
  return $pass_encrypt;
}

function getMysql() {
  $pass_encrypt = main_backend();
  $pdo = connectMysql(); // DBとの接続開始
  $stmt = $pdo->prepare("SELECT * FROM mnsn_sheet where :pass_hash = pass_hash ORDER BY id DESC");
  $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->execute();
  $all = $stmt->fetchAll(PDO::FETCH_ASSOC); //全件取得
  // ============= ここまでDBからの取得 =============
  return $all;
}

function putTargetMysql($sql, $serialize_gpt) {
  $pass_encrypt = main_backend();
  $pdo = connectMysql(); // DBとの接続開始
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':serialize_gpt', $serialize_gpt, PDO::PARAM_STR); // bindValueメソッドでパラメータをセット
  $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->execute();
  //$all = $stmt->fetchAll(PDO::FETCH_ASSOC); //全件取得
  // ============= ここまでDBからの取得 =============
}

function putOneTargetMysql($sql, $targetName) {
  $pass_encrypt = main_backend();
  $pdo = connectMysql(); // DBとの接続開始
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':targetName', $targetName, PDO::PARAM_STR); // bindValueメソッドでパラメータをセット
  $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->execute();
  //$all = $stmt->fetchAll(PDO::FETCH_ASSOC); //全件取得
  // ============= ここまでDBからの取得 =============
}

// function putMessageLogMysql($sender, $messages, $situation) {
//   $pass_encrypt = main_backend();
//   $pdo = connectMysql(); // DBとの接続開始
//   $stmt = $pdo->prepare("INSERT INTO message_log (pass_hash, sender, messages, situation) VALUES (:pass_hash, :sender, :messages, :situation)");
//   $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
//   $stmt->bindValue(':sender', $sender, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
//   $stmt->bindValue(':messages', $messages, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
//   $stmt->bindValue(':situation', $situation, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
//   $stmt->execute();
// }

function putMessageLogMysql($sender, $messages, $situation, $contents) {
  $pass_encrypt = main_backend();
  $pdo = connectMysql(); // DBとの接続開始

  // 現在の日時を取得
  $currentDateTime = date('Y-m-d H:i:s');

  $stmt = $pdo->prepare("INSERT INTO message_log (pass_hash, sender, messages, situation, contents, created_time) VALUES (:pass_hash, :sender, :messages, :situation, :contents, :created_time)");
  $stmt->bindValue(':messages', $messages, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->bindValue(':sender', $sender, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->bindValue(':situation', $situation, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->bindValue(':contents', $contents, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->bindValue(':created_time', $currentDateTime, PDO::PARAM_STR); // 現在の日時を追加
  $stmt->execute();
}

function getTargetMysql($situation){

  $pass_encrypt = main_backend();
  $pdo = connectMysql(); // DBとの接続開始
  $stmt = $pdo->prepare("SELECT contents FROM message_log WHERE :pass_hash = pass_hash AND :situation = situation ORDER BY id DESC LIMIT 1");
  $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->bindValue(':situation', $situation, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  // error_log(print_r($stmt , true) . "\n", 3, dirname(_FILE_) . '/debug.log');

  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC); //取得
// AND :situation = situation ORDER BY id DESC LIMIT 1
  return $result;
}

function getOtherTargetMysql($situation, $item){

  $pass_encrypt = main_backend();
  $pdo = connectMysql(); // DBとの接続開始
  $stmt = $pdo->prepare("SELECT $item FROM message_log WHERE :pass_hash = pass_hash AND :situation = situation ORDER BY id DESC LIMIT 1");
  $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->bindValue(':situation', $situation, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  // error_log(print_r($stmt , true) . "\n", 3, dirname(_FILE_) . '/debug.log');

  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC); //取得
// AND :situation = situation ORDER BY id DESC LIMIT 1
  return $result;
}

function getOneMysql($targetNum, $item) {
  $pass_encrypt = main_backend();
  $pdo = connectMysql(); // DBとの接続開始
  $stmt = $pdo->prepare("SELECT $item FROM $targetNum WHERE :pass_hash = pass_hash ORDER BY id DESC");
  $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  // error_log(print_r($stmt , true) . "\n", 3, dirname(_FILE_) . '/debug.log');
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC); //全件取得
  // ============= ここまでDBからの取得 =============
  return $result;
}

function updateOneMysql($keepDay,$targetNum, $item) {
  $pass_encrypt = main_backend();
  $pdo = connectMysql(); // DBとの接続開始
  $stmt = $pdo->prepare("UPDATE $targetNum SET $item = :keepDay WHERE :pass_hash = pass_hash ORDER BY id DESC");
  $stmt->bindValue(':keepDay', $keepDay, PDO::PARAM_STR); // bindValueメソッドでパラメータをセット
  $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->execute();
  //$all = $stmt->fetchAll(PDO::FETCH_ASSOC); //全件取得
  // ============= ここまでDBからの取得 =============
}

?>
