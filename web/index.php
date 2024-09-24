<?php

require('./vendor/autoload.php');

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__); //.envを読み込む
$dotenv->load();

//アカウント情報
$channel_access_token = getenv('CHANNEL_ACCESS_TOKEN');
$channel_secret = getenv('CHANNEL_SECRET');

//他のファイルから関数を呼び出す
require('./backend.php');
require('./flexMessage.php');
require('./makeRanking.php');
require('./setTarget.php');
//require ('./chatapi.php');

function sendMessage($post_data)
{
  // LINEBOT用の関数．いじらなくてOK
  $accessToken = getenv('CHANNEL_ACCESS_TOKEN');
  $ch = curl_init("https://api.line.me/v2/bot/message/reply");
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . $accessToken
  ));
  $result = curl_exec($ch);
  curl_close($ch);
}

function sendFlexMessage($post_data)
{
  // LINEBOT用の関数．いじらなくてOK
  $accessToken = getenv('CHANNEL_ACCESS_TOKEN');
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/push');
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $accessToken,
  ]);
  $result = curl_exec($ch);
  curl_close($ch);

  echo $result;
}

function callApi($url)
{
  // 外部APIを呼び出すときに使える関数
  $ch = curl_init(); //開始

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // 証明書の検証を行わない
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // curl_execの結果を文字列で返す

  $response =  curl_exec($ch);
  $result = json_decode($response, true);

  curl_close($ch); //終了

  return $result;
}

function main()
{
  /* イベント（ユーザからの何らかのアクション）を取得．特にいじらなくてOK． */
  $json_string = file_get_contents('php://input');
  $jsonObj = json_decode($json_string,true);
  // $events = $jsonObj->{"events"};
  $events = $jsonObj["events"];
  $resultRank = [];
  $ary_from_gpt = [];
  // $targetNum;
  $rankings = [];
  $contents = "";
  // $situation = "";
  /* ***** */
  
  // ユーザから来たメッセージを1件ずつ処理
  foreach ($events as $event) {
    // $replyToken = $event->{"replyToken"}; // メッセージを返すのに必要
    // $type = $event->{"message"}->{"type"}; // メッセージタイプ
    // $text = $event->{"message"}->{"text"}; // メッセージテキスト
    $replyToken = $event["replyToken"]; // メッセージを返すのに必要
    $type = $event["message"]["type"]; // メッセージタイプ
    $text = $event["message"]["text"]; // メッセージテキスト
    $userId = $event["source"]["userId"];// メッセージテキスト
    $messages = [];
    $flexMessage1 = [];
    $flexMessage = [];
    $count = 0;
    $SearchAgain = 0;
    
    $sender = "user";
    
    if($text == '運動を改善したい！' || $text == 'タバコに関して改善したい！' || $text == 'お酒に関して改善したい！' || $text == '間食を改善したい！' || $text == '朝食を改善したい！' || $text == '睡眠を改善したい！' || $text == '肥満を改善したい！' ){
      $situation = "choose_improvement_item";
      //---------------------------項目ごとに生成した目標の出力(フレックスメッセージ)-----------------------------------
      
      if($text == '運動を改善したい！') {
        $targetNum = "exercise_management"; 
      }else if($text == 'タバコに関して改善したい！'){
        $targetNum = "smoking_management";
      }else if($text == 'お酒に関して改善したい！'){
        $targetNum = "alcohol_management";
      }else if($text == '間食を改善したい！'){
        $targetNum = "snack_management";
      }else if($text == '朝食を改善したい！'){
        $targetNum = "breakfast_management";
      }else if($text == '睡眠を改善したい！'){
        $targetNum = "sleep_management";
      }else if($text == '肥満を改善したい！' ){
        $targetNum = "obesity_management";
      }
      $contents = $targetNum;
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      $text_gpt = target($text,$targetNum, $userId);
      
      $str_from_gpt = str_replace(array("\r\n", "\r", "\n"), "\n", $text_gpt);//改行コードを一つに統一
      $ary_from_gpt = explode("\n", $str_from_gpt);
      
      $serialize_gpt = serialize($ary_from_gpt);
      $contents = $serialize_gpt;
      error_log(print_r($ary_from_gpt , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      
      $sql = "UPDATE " . $targetNum . " SET target = :serialize_gpt WHERE :line_uid = line_uid ORDER BY id DESC";
      putTargetMysql($sql, $serialize_gpt, $userId);
      error_log(print_r("5" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      $flexMessage = getTargetFlexMessage($ary_from_gpt);
      //$messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
      
      $situation = "show targets";
      $count = 1;
      
    }
    else if($text == '一番目を選択します！' || $text == '二番目を選択します！' || $text == '三番目を選択します！' || $text == '四番目を選択します！'){
      //メッセージのログを残す
      $situation = "choose_target";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);
      
      //ログから目標と改善項目を習得
      $situation = "choose_improvement_item";
      $targetNum = getTargetMysql($situation, $userId)[0]['contents'];
      // error_log(print_r($targetNum , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      $item = "target";
      $serialize_gpt = getOneMysql($targetNum, $item, $userId)['target'];
      // error_log(print_r($serialize_gpt , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      $ary_from_gpt = unserialize($serialize_gpt);

      // 目標をデータベースに保存
      $targetNumber = targetAdd($text, $ary_from_gpt, $targetNum, $userId);
      $text = "目標を設定しました. 「" . $ary_from_gpt[$targetNumber] . "」を目標にこれから頑張りましょう！";
      $logMessage = $text;
      $messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
      $situation = "set_target";

    }else if ($text == '他の目標を見たいです！'){
      //-----------------------------------他の目標の出力(フレックスメッセージ)-----------------------------------------
      //メッセージのログを残す
      $situation = "requirement_of_check_other_target";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //改善項目をデータベースから取る
      $SearchAgain = 0;
      $situation = "choose_improvement_item";
      $item = "messages";
      $previousItem = getOtherTargetMysql($situation, $item, $userId)[0]['messages'];
      error_log(print_r($previousItem , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


      $situation = "choose_improvement_item";
      $item = "contents";
      $targetNum = getOtherTargetMysql($situation, $item, $userId)[0]['contents'];
      error_log(print_r($targetNum , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      
      //チャットGPTを用いて目標を作る
      $text_gpt = target($previousItem, $targetNum, $userId);
      $str_from_gpt = str_replace(array("\r\n", "\r", "\n"), "\n", $text_gpt);//改行コードを一つに統一
      $ary_from_gpt = explode("\n", $str_from_gpt);
      error_log(print_r($ary_from_gpt , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      $serialize_gpt = serialize($ary_from_gpt);
      $contents = $serialize_gpt;
      

      $sql = "UPDATE " . $targetNum . " SET target = :serialize_gpt WHERE :line_uid = line_uid ORDER BY id DESC";
      putTargetMysql($sql, $serialize_gpt, $userId);

  
      //$messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
      $flexMessage = getTargetFlexMessage($ary_from_gpt);
      $situation = "show targets";
      $count = 1;


    }else if($text == '継続の確認がしたい！！') {
      //-----------------------------------継続の確認のフレックスメッセージの表示-----------------------------------------

      //メッセージのログを残す
      $situation = "requirement_of_check_keeping";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //入力日時の比較
      $situation = "check_keeping_flexMessage";
      $item = "created_time";
      $created_time = getOtherTargetMysql($situation, $item, $userId)[0]['created_time'];

      $currentDatetime = date('Y-m-d H:i:s');


      if (date('Y-m-d', strtotime($created_time)) === date('Y-m-d', strtotime($currentDatetime))) {
          // 同じ日の場合の処理
          $text = "申し訳ありません．同じ日に継続確認は行えません．明日，目標達成してから継続できたか教えてください(^_^)/";
          $messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
          $situation = "message_of_can't_check_keeping";
          $logMessage = $text;

      }else{
      
        $text = "継続の確認を行います";
        $logMessage = $text;
        $messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
        $flexMessage = confirmation();
        $count = 2;
        $situation1 = "check_keeping_message";
        $situation2 = "check_keeping_flexMessage";
      }

    }else if($text == '使い方を教えて！') {
      //-----------------------------------使い方の出力-----------------------------------------
      //メッセージのログを残す
      $situation = "requirement_of_check_keeping";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      $text = "使い方について説明します";
      $logMessage = $text;
      $messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
      // array_push($messages, ["type" => "text", "text" => $text]);
      treatment();
      $situation = "explain_way_of_use";

    }else if($text == 'ランキングが見たい！') {
      //-----------------------------------ランキングの出力-----------------------------------------
      //メッセージのログを残す
      $situation = "requirement_of_check_ranking";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      $text = "ランキングを表示します";
      $logMessage = $text;
      $messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
      
      //データベースからランキングを取得
      $situation = "flexMessage_of_ranking";
      $item = "messages";
      $serialize_flexMessage = getOtherTargetMysql($situation, $item, $userId)[0]['messages'];
      $flexMessage = unserialize($serialize_flexMessage);
      $count = 2;
      $situation1 = "show_ranking_for_check_message";
      $situation2 = "show_ranking_for_check_flexMessage";


    }else if($text == 'その他ツールが見たい！') {
      //-----------------------------------その他ツールの出力-----------------------------------------
      //メッセージのログを残す
      $situation = "requirement_of_show_tool";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);
      $text = "その他のツールを表示します";
      $logMessage = $text;
      $messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
      $flexMessage = tool();
      $count = 2;
      $situation1 = "show_tool_message";
      $situation2 = "show_tool_flexMessage";

    }else if ($text == 'できました！'){
      //-------------------------継続できた場合の出力・言葉生成・ランキング生成---------------------------------
      //メッセージのログを残す
      $situation = "report_of_what_was_done";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //継続日数の抽出
      $situation = "choose_improvement_item";
      $item = "contents";
      $previousItem = getOtherTargetMysql($situation, $item, $userId)[0]['contents'];

      $item = "keep_days";
      $targetNum = $previousItem;
      $keepDay = getOneMysql($targetNum, $item, $userId)['keep_days'];
      $keepDay = $keepDay + 1;

      //継続日数の保存
      $item = "keep_days";
      updateOneMysql($keepDay,$targetNum, $item, $userId);

      //実施日数の抽出
      $item = "day_num";
      $targetNum = $previousItem;
      $dayNum = getOneMysql($targetNum, $item, $userId)['day_num'];
      $dayNum = $dayNum + 1;

      //日数の保存
      $item = "day_num";
      updateOneMysql($dayNum,$targetNum, $item, $userId);


      //何週間目の継続日数か抽出
      $item = "week_num";
      $targetNum = $previousItem;
      $weekNum = getOneMysql($targetNum, $item, $userId)['week_num'];

      $message_log = 'weekNumを抽出しました';
      error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      //何週間目の継続日数か抽出
      $item = "week_keep_num";
      $targetNum = $previousItem;
      $weekKeepNum = getOneMysql($targetNum, $item, $userId)['week_keep_num'];

      $message_log = 'weekKeepNumを抽出しました';
      error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      //もし週間目だったら日数と継続日数をデータベースに保存
      if($dayNum % 7 == 0){
        $item = "week_num";
        updateOneMysql($dayNum,$targetNum, $item, $userId);
        error_log(print_r("1" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


        $item = "week_keep_num";
        updateOneMysql($keepDay,$targetNum, $item, $userId);
        error_log(print_r("2" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      } 

      if($dayNum % 7 != 0){
        $message = "あなたは継続管理を行う褒め上手な人です．ユーザは現在，生活習慣病の改善維持に取り組んでいます．継続が" .$dayNum. "日の間で". $keepDay .  "日続いた人をほめてください．100字以内でお願いします．";
        $text_gpt = call_chatGPT($message); // GPTにプロンプトを送信
        error_log(print_r("3" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      }else{
        $message = "1週間お疲れ様です．次の週からは以下のメンバーと順位を競います．次も頑張っていきましょう！";
        $text_gpt = $message;
        error_log(print_r("4" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      }

      //一週目か二週目かを判断
      if($dayNum > 7){
        $dayNum = $dayNum - $weekNum;
        $keepDay = $keepDay - $weekKeepNum;
      }
      if($dayNum % 2 == 0 || $dayNum % 7 == 0){
        $userInfo = ["keepDays" => $keepDay, "dayNum" => $dayNum, "weekKeepNum" => $weekKeepNum];
        $resultRank = rankingMaker($userInfo, $userId);
        // error_log(print_r($dayNum , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
        // error_log(print_r($keepDay , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
        error_log(print_r("5" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        $allDay = $dayNum + $weekNum;
        //コンテンツへの全ユーザ順位の追加
        $contents = serialize($resultRank);
        $flexMessage = ranking($resultRank, $allDay);
        error_log(print_r("6" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


        // $userRank;
        if($dayNum % 7 == 0){
          foreach($resultRank as &$ranking){
            if($ranking['user'] == 'あなた'){
              $userRank = $ranking['rank'];
              $userDay = $ranking['point'];
              error_log(print_r("7" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
            }
          }

          $flexMessage1 = lastWeek($userRank);
          $flexMessage2 = makeUser($userDay);
          error_log(print_r("8" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
          
        }
      }
        
      $logMessage = $text_gpt;
      $messages . array_push($messages, ["type" => "text", "text" => $text_gpt]); // 適当にオウム返し
      error_log(print_r("9" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


      if($dayNum % 2 == 0 ){
        $count = 3;
        $situation1 = "flexMessage_of_ranking";
        $situation2 = "evaluation_and_words_of_praise";
        error_log(print_r("10" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      }else if($dayNum % 7 == 0){
        $count = 4;
        $situation1 = "flexMessage_of_ranking";
        $situation2 = "words_of_next_praise";
        $situation3 = "flexMessage_of_lastWeek_rank";
        $situation4 = "flexMessage_of_nextUser";
        error_log(print_r("11" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      }else{
        $count = 0;
        $situation = "evaluation_and_words_of_praise";
        error_log(print_r("12" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      }

    }else if ($text == 'できませんでした'){
      //-------------------------継続できなかった場合の出力・言葉生成・ランキング生成---------------------------------
      //メッセージのログを残す
      $situation = "report_of_what_could_not_be_done";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);


      //継続日数の抽出
      $situation = "choose_improvement_item";
      $item = "contents";
      $previousItem = getOtherTargetMysql($situation, $item, $userId)[0]['contents'];

      $item = "keep_days";
      $targetNum = $previousItem;
      $keepDay = getOneMysql($targetNum, $item, $userId)['keep_days'];
  
      

      $item = "day_num";
      $targetNum = $previousItem;
      $dayNum = getOneMysql($targetNum, $item, $userId)['day_num'];
      $dayNum = $dayNum + 1;

      //日数の保存
      $item = "day_num";
      updateOneMysql($dayNum,$targetNum, $item, $userId);

      //何週間目かの抽出
      $item = "week_num";
      $targetNum = $previousItem;
      $weekNum = getOneMysql($targetNum, $item, $userId)['week_num'];

      //何週間目の継続日数か抽出
      $item = "week_keep_num";
      $targetNum = $previousItem;
      $weekKeepNum = getOneMysql($targetNum, $item, $userId)['week_keep_num'];

      //もし週間目だったら日数と継続日数をデータベースに保存
      if($dayNum % 7 == 0){
        $item = "week_num";
        updateOneMysql($dayNum,$targetNum, $item, $userId);

        $item = "week_keep_num";
        updateOneMysql($keepDay,$targetNum, $item, $userId);

      }

      if($dayNum % 7 != 0){
        $message = "あなたは継続管理を行う褒め上手な人です．ユーザは現在，生活習慣病の改善維持に取り組んでいます．継続が" .$dayNum. "日の間で". $keepDay . "日続けていましたが今日は目標を達成できませんでした．このユーザを元気づけてください．100字以内でお願いします．";
        $text_gpt = call_chatGPT($message); // GPTにプロンプトを送信
      }else{
        $message = "1週間お疲れ様です．次の週からは以下のメンバーと順位を競います．次も頑張っていきましょう！";
        $text_gpt = $message;
      }

      //一週目か二週目かを判断
      if($dayNum > 7){
        $dayNum = $dayNum - $weekNum;
        $keepDay = $keepDay - $weekKeepNum;
      }

      if($dayNum % 2 == 0 || $dayNum % 7 == 0){
        $userInfo = ["keepDays" => $keepDay, "dayNum" => $dayNum, "weekKeepNum" => $weekKeepNum];
        $resultRank = rankingMaker($userInfo, $userId);

        $allDay = $dayNum + $weekNum;
        //コンテンツへの全ユーザ順位の追加
        $contents = serialize($resultRank);
        $flexMessage = ranking($resultRank, $allDay);

        if($dayNum % 7 == 0){
          foreach($resultRank as &$ranking){
            if($ranking['user'] == 'あなた'){
              $userRank = $ranking['rank'];
              $userDay = $ranking['point'];
            }
          }
          
          $flexMessage1 = lastWeek($userRank);
          $flexMessage2 = makeUser($userDay);
          
        }
      }

      $logMessage = $text_gpt;
      $messages . array_push($messages, ["type" => "text", "text" => $text_gpt, "weekKeepNum" => $weekKeepNum]); // 適当にオウム返し
      
      if($dayNum % 2 == 0){
        $count = 3;
        $situation1 = "flexMessage_of_ranking";
        $situation2 = "evaluation_and_words_of_praise";
      }else if($dayNum % 7 == 0){
        $count = 4;
        $situation1 = "flexMessage_of_ranking";
        $situation2 = "words_of_next_praise";
        $situation3 = "flexMessage_of_lastWeek_rank";
        $situation4 = "flexMessage_of_nextUser";
      }else{
        $count = 0;
        $situation = "cheer_up_message";
      }
    }else if($text == "目標を確認したい！"){
      //--------------------------------------データベースに保存された目標の取得------------------------------------------------
      //メッセージのログを残す
      $situation = "requirement_of_check_of_marking";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //ログから改善項目を，テーブルから目標を習得
      $situation = "choose_improvement_item";
      $targetNum = getTargetMysql($situation, $userId)[0]['contents'];
      // error_log(print_r($targetNum , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      $item = "target";
      $target = getOneMysql($targetNum, $item, $userId)['target'];
      // error_log(print_r($serialize_gpt , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      $message = "あなたが設定した目標は" . "\n" . "「" . $target . "」" . "\n" . "です．";
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
      $situation = "replay_decided_target";

    }else if($text == "継続日数を確認したい！") {
      //--------------------------------------データベースに保存された継続日数の取得------------------------------------------------

      //メッセージのログを残す
      $situation = "requirement_of_check_of_keep_days";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //ログから改善項目を，テーブルから目標を習得
      $situation = "choose_improvement_item";
      $targetNum = getTargetMysql($situation, $userId)[0]['contents'];
      // error_log(print_r($targetNum , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      $item = "keep_days";
      $keepDay = getOneMysql($targetNum, $item, $userId)['keep_days'];
      // error_log(print_r($serialize_gpt , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      $item = "day_num";
      $dayNum = getOneMysql($targetNum, $item, $userId)['day_num'];

      $message = "あなたの継続日数は" . "\n" . "「" . $dayNum . "日中　" . $keepDay . "日」です．";
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
      $situation = "replay_keep_day";

    }else if($text == "LINEのIDを取得したい！"){
      //--------------------------------------ユーザIDの取得------------------------------------------------
      //メッセージのログを残す
      $situation = "requirement_of_check_of_user_id";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);



      // ユーザIDを取得する
      $userId = $event["source"]["userId"];
      // メッセージで返す
      $message =  $userId;
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
      $situation1 = "replay_user_id";

      $flexMessage = getMnsnRegistration();
    

      $logMessage = $message;
      $count = 2;
      $situation2 = "do_use_user_id";

    }else if ($text == "問診票を記入しました！") {
      //--------------------------------------問診票の記入完了メッセージに対する返答------------------------------------------------
      $situation = "requirement_of_check_of_user_id";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      // メッセージで返す
      $message =  "改善したい生活習慣病の項目を選択してください！";
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
      $situation1 = "requirement_of_choose_improvement_item";

      $flexMessage = listManagement();

      $logMessage = $message;
      $count = 2;
      $situation2 = "flexMessage_of_choose_improvement_item";
      
    }else if ($text == "わだにゃん"){
      //--------------------------------------ユーザIDの取得------------------------------------------------
      //メッセージのログを残す
      $situation = "requirement_of_check_of_user_id";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);



      // ユーザIDを取得する
      $userId = $event["source"]["userId"];
      // メッセージで返す
      $message =  $userId;
      $logMessage = $message;
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
      $situation = "replay_user_id";
    }else if ($text == "ユーザ名を設定したい！"){
      //--------------------------------------ユーザ名の設定------------------------------------------------
      $situation = "require_of_set_userName";
      $logMessage = $text;
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      
      $message =  "ユーザ名を入力してください！";
      $logMessage = $message;
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // ユーザ名を入力してくださいと返す
      $situation = "requirement_of_userName";

    }else if ($text == "性格を設定したい！" || $text == "性別を設定したい！" || $text == "方言を設定したい！"){
      $situation = "require_of_set_chatbot";
      $logMessage = $text;
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);
      //--------------------------------------チャットボットの基本設定------------------------------------------------
      if($text == "性格を設定したい！"){
        $message =  "チャットボットの性格を選んでください！";
        $logMessage = $message;
        $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
        $situation1 = "requirement_of_personality";

        $flexMessage = personality();
        $situation2 = "flexMessage_of_choose_personality";
        $count = 2;

      }else if($text == "性別を設定したい！"){
        $message =  "チャットボットの性別を選んでください！";
        $logMessage = $message;
        $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
        $situation1 = "requirement_of_gender";

        $flexMessage = gender();
        $situation2 = "flexMessage_of_gender";
        $count = 2;

      }else if ($text == "方言を設定したい！"){
        $message =  "チャットボットの方言を選んでください！";
        $logMessage = $message;
        $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
        $situation1 = "requirement_of_dialect";

        $flexMessage = dialect();
        $situation2 = "flexMessage_of_choose_dialect";
        $count = 2;
      }
      
    }else if ($text == '通知時間を設定したい！') {
      //--------------------------------------通知時間の設定------------------------------------------------
      $situation = "require_of_set_notification_time";
      $logMessage = $text;
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      $message =  "通知してほしい時間を例をもとにすべて半角で入力してください（例: 14:00）";
      $logMessage = $message;
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
      $situation = "requirement_of_notification_time";
      
    }else if ($text == "協調的な性格がいい！" || $text == "外向的な性格がいい！"){
      //--------------------------------------性格の設定------------------------------------------------
      $situation = "response_of_personality";
      $logMessage = $text;
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //ログから改善項目を習得
      $situation = "choose_improvement_item";
      $targetNum = getTargetMysql($situation, $userId)[0]['contents'];

      //性格をデータベースに保存
      if($text == "協調的な性格がいい！"){
        $setWord = "協調的な性格";
      }else if($text == "外向的な性格がいい！"){
        $setWord = "外向的な性格";
      }
      $section = "chat_personality";
      updateOneMysql($setWord,$targetNum, $section, $userId);

      //性格設定完了メッセージの作成
      $message = "性格を「". $setWord . "」に設定しました！";
      $logMessage = $message;
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
      $situation = "set_personality";

    }else if ($text == "男性がいい！" || $text == "女性がいい！"){
      //--------------------------------------性別の設定------------------------------------------------
      $situation = "response_of_gender";
      $logMessage = $text;
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //ログから改善項目を習得
      $situation = "choose_improvement_item";
      $targetNum = getTargetMysql($situation, $userId)[0]['contents'];

      //性別をデータベースに保存
      if($text == "男性がいい！"){
        $setWord = "男性";
      }else if($text == "女性がいい！"){
        $setWord = "女性";
      }
      $section = "chat_gender";
      updateOneMysql($setWord,$targetNum, $section, $userId);

      //性別設定完了メッセージの作成
      $message = "性別を「". $setWord . "」に設定しました！";
      $logMessage = $message;
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
      $situation = "set_personality";

    }else if ($text == "標準語がいい！" || $text == "東北弁がいい！" || $text == "関西弁がいい！" || $text == "広島弁がいい！" || $text == "博多弁がいい！" || $text == "沖縄弁がいい！" || $text == "鹿児島弁がいい！"){
      //--------------------------------------方言の設定------------------------------------------------
      $situation = "response_of_dialect";
      $logMessage = $text;
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //ログから改善項目を習得
      $situation = "choose_improvement_item";
      $targetNum = getTargetMysql($situation, $userId)[0]['contents'];

      //性別をデータベースに保存
      if($text == "標準語がいい！"){
        $setWord = "標準語";
      }else if($text == "東北弁がいい！"){
        $setWord = "東北弁";
      }else if($text == "関西弁がいい！"){
        $setWord = "関西弁";
      }else if($text == "広島弁がいい！"){
        $setWord = "広島弁";
      }else if($text == "博多弁がいい！"){
        $setWord = "博多弁";
      }else if($text == "沖縄弁がいい！"){
        $setWord = "沖縄弁";
      }else if($text == "鹿児島弁がいい！"){
        $setWord = "鹿児島弁";
      }
      $section = "chat_dialect";
      updateOneMysql($setWord,$targetNum, $section, $userId);

      //方言設定完了メッセージの作成
      $message = "方言を「". $setWord . "」に設定しました！";
      $logMessage = $message;
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
      $situation = "set_personality";

    }else{
      if($type == "text") { // メッセージがテキストのとき

        $text = $event["message"]["text"]; // メッセージテキスト

        //ログを残す前に，何か特定の入力を要求していたか確認
        $situation_log = getLatestSituation($userId)['situation'];
        error_log(print_r($situation_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        //メッセージのログを残す
        $situation = "send_something";
        putMessageLogMysql($sender, $text, $situation, $contents, $userId);

        //ログから改善項目を習得
        $situation = "choose_improvement_item";
        $targetNum = getTargetMysql($situation, $userId)[0]['contents'];

        //--------------------------------------ユーザ名の変更------------------------------------------------
        if($situation_log == "requirement_of_userName"){
          $setWord = $text;
          $section = "userName";
          updateOneMysql($setWord,$targetNum, $section, $userId);

          $message = "ユーザ名を「". $text . "」に設定しました！";
          $logMessage = $message;
          $messages . array_push($messages, ["type" => "text", "text" => $message]); // ユーザ名の変更を通知
          $situation = "set_userName";
        }else if($situation_log == "requirement_of_notification_time"){
          //--------------------------------------通知時間の決定------------------------------------------------
          $setWord = $text;
          $section = "notification_time";
          updateOneMysql($setWord,$targetNum, $section, $userId);

          $message = "通知時間を「". $text . "」に設定しました！";
          $logMessage = $message;
          $messages . array_push($messages, ["type" => "text", "text" => $message]); // ユーザ名の変更を通知
          $situation = "set_notification_time";
        
        }else{
          $logMessage = $text;
          $messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
          $situation = "not_defined_message";
        }

      } else if ($type == "sticker") { // メッセージがスタンプのとき
        $messages . array_push($messages, ["type" => "sticker", "packageId" => "446", "stickerId" => "1988"]); // 適当なステッカーを返す

      } else { // その他は無視．必要に応じて追加．
      return;
      }
    }
    $sender = "LINE Bot";


    //----------------------------------メッセージを返信----------------------------------
    if ($count == 1){
      //メッセージのみの送信
      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage],
      ]);
      $serialize_flexMessage = serialize($flexMessage);
      putMessageLogMysql($sender, $serialize_flexMessage, $situation, $contents, $userId);

    }else if($count == 2) {
      //メッセージとフレックスメッセージの送信
      sendMessage([
        "replyToken" => $replyToken,
        "messages" => $messages
      ]);
      putMessageLogMysql($sender, $logMessage, $situation1, $contents, $userId);

      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage],
      ]);
      $serialize_flexMessage = serialize($flexMessage);
      putMessageLogMysql($sender, $serialize_flexMessage, $situation2, $contents, $userId);

    }else if($count == 3) {
      //メッセージを二つ送信
      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage],
      ]);
      // error_log(print_r("13" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      $serialize_flexMessage = serialize($flexMessage);
      // error_log(print_r("13.5" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      putMessageLogMysql($sender, $serialize_flexMessage, $situation1, $contents, $userId);
      // error_log(print_r("14" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      sendMessage([
        "replyToken" => $replyToken,
        "messages" => $messages
      ]);
      putMessageLogMysql($sender, $logMessage, $situation2, $contents, $userId);

    }else if($count == 4){
      //フレックスメッセージ・フレックスメッセージ・メッセージ・フレックスメッセージの送信
      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage1],
      ]);
      $serialize_flexMessage = serialize($flexMessage1);
      putMessageLogMysql($sender, $serialize_flexMessage, $situation3, $contents, $userId);

      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage],
      ]);
      $serialize_flexMessage = serialize($flexMessage);
      putMessageLogMysql($sender, $serialize_flexMessage, $situation1, $contents, $userId);

      sendMessage([
        "replyToken" => $replyToken,
        "messages" => $messages
      ]);
      putMessageLogMysql($sender, $logMessage, $situation2, $contents, $userId);

      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage2],
      ]);
      $serialize_flexMessage = serialize($flexMessage2);
      putMessageLogMysql($sender, $serialize_flexMessage, $situation4, $contents, $userId);

    }else{
      sendMessage([
        "replyToken" => $replyToken,
        "messages" => $messages
      ]);
      putMessageLogMysql($sender, $logMessage, $situation, $contents , $userId);
    }
  }
}


function treatment() {

}

function customRandom() {
  // 0から5までのランダムな整数を生成
  $randomNumber = rand(0, 10);

  // 2が出る確率を3分の1に設定
  if ($randomNumber < 7) {
      return 2;
  } else if ($randomNumber < 11 && $randomNumber > 8){
      // 0または1が出る確率を2分の1に設定
      return 1;
  }else{
    return 0;
  }
}

function customRandom1() {
  // 0から5までのランダムな整数を生成
  $randomNumber = rand(0, 10);

  // 2が出る確率を3分の1に設定
  if ($randomNumber < 4) {
      return 2;
  } else if ($randomNumber < 9 && $randomNumber > 3){
      // 0または1が出る確率を2分の1に設定
    return 1;
  }else{
    return 0;
  }
}

function customRandom2() {
  // 0から5までのランダムな整数を生成
  $randomNumber = rand(0, 10);

  // 2が出る確率を3分の1に設定
  if ($randomNumber < 6) {
      return 2;
  } else if ($randomNumber > 5){
      // 0または1が出る確率を2分の1に設定
      return 1;
  }
}
// コメントアウト
// function makeUser($userDay){
main();

?>