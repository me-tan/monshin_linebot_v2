<?php

require('./vendor/autoload.php');

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__); //.envを読み込む
$dotenv->load();

//アカウント情報
$channel_access_token = getenv('CHANNEL_ACCESS_TOKEN');
$channel_secret = getenv('CHANNEL_SECRET');


require('./backend.php');
// require('./flexMessage.php');
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

  
      //$messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
      $flexMessage = [
        "type" => "flex",
        "altText" => "this is a flex message",
        "contents" => [
          "type" => "carousel",
          "contents" => [
              [
                  "type" => "bubble",
                  "size" => "giga",
                  "direction" => "ltr",
                  "header" => [
                      "type" => "box",
                      "layout" => "vertical",
                      "contents" => [
                          [
                              "type" => "text",
                              "text" => "目標を選択してください",
                              "color" => "#ffffff",
                              "align" => "center",
                              "size" => "sm",
                              "gravity" => "center",
                              "decoration" => "none",
                              "position" => "relative",
                              "margin" => "none",
                              "wrap" => true,
                              "style" => "italic",
                              "weight" => "bold",
                          ],
                      ],
                      "backgroundColor" => "#27ACB2",
                      "paddingTop" => "19px",
                      "paddingAll" => "12px",
                      "paddingBottom" => "16px",
                  ],
                  "body" => [
                      "type" => "box",
                      "layout" => "vertical",
                      "contents" => [
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[0],
                                  "text" => "一番目を選択します！",
                              ],
                              "adjustMode" => "shrink-to-fit",
                              "style" => "secondary",
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[1],
                                  "text" => "二番目を選択します！",
                              ],
                              "style" => "secondary",
                              "adjustMode" => "shrink-to-fit",
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[2],
                                  "text" => "三番目を選択します！",
                              ],
                              "style" => "secondary",
                              "adjustMode" => "shrink-to-fit",
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[3],
                                  "text" => "四番目を選択します！",
                              ],
                              "style" => "secondary",
                              "gravity" => "center",
                              "adjustMode" => "shrink-to-fit",
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => "他の目標を見る",
                                  "text" => "他の目標を見たいです！",
                              ],
                              "style" => "secondary",
                              "gravity" => "center",
                              "adjustMode" => "shrink-to-fit",
                          ],
                      ],
                      "spacing" => "md",
                      "paddingAll" => "12px",
                  ],
              ],
          ],
        ]
      ]; // 適当にオウム返し
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
      $flexMessage = [
        "type" => "flex",
        "altText" => "this is a flex message",
        "contents" => [
          "type" => "carousel",
          "contents" => [
              [
                  "type" => "bubble",
                  "size" => "giga",
                  "direction" => "ltr",
                  "header" => [
                      "type" => "box",
                      "layout" => "vertical",
                      "contents" => [
                          [
                              "type" => "text",
                              "text" => "目標を選択してください",
                              "color" => "#ffffff",
                              "align" => "center",
                              "size" => "sm",
                              "gravity" => "center",
                              "decoration" => "none",
                              "position" => "relative",
                              "margin" => "none",
                              "wrap" => true,
                              "style" => "italic",
                              "weight" => "bold",
                          ],
                      ],
                      "backgroundColor" => "#27ACB2",
                      "paddingTop" => "19px",
                      "paddingAll" => "12px",
                      "paddingBottom" => "16px",
                  ],
                  "body" => [
                      "type" => "box",
                      "layout" => "vertical",
                      "contents" => [
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[0],
                                  "text" => "一番目を選択します！",
                              ],
                              "adjustMode" => "shrink-to-fit",
                              "style" => "secondary",
                              // "wrap" => true,
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[1],
                                  "text" => "二番目を選択します！",
                              ],
                              "style" => "secondary",
                              "adjustMode" => "shrink-to-fit",
                              // "wrap" => true,
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[2],
                                  "text" => "三番目を選択します！",
                              ],
                              "style" => "secondary",
                              "adjustMode" => "shrink-to-fit",
                              // "wrap" => true,
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => $ary_from_gpt[3],
                                  "text" => "四番目を選択します！",
                              ],
                              "style" => "secondary",
                              "gravity" => "center",
                              "adjustMode" => "shrink-to-fit",
                              // "wrap" => true,
                          ],
                          [
                              "type" => "button",
                              "action" => [
                                  "type" => "message",
                                  "label" => "他の目標を見る",
                                  "text" => "他の目標を見たいです！",
                              ],
                              "style" => "secondary",
                              "gravity" => "center",
                              "adjustMode" => "shrink-to-fit",
                              // "wrap" => true,
                          ],
                      ],
                      "spacing" => "md",
                      "paddingAll" => "12px",
                  ],
              ],
          ],
        ]
      ]; 
      $situation = "show targets";
      $count = 1;


    }else if($text == '継続の確認がしたい！！') {

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
      //メッセージのログを残す
      $situation = "requirement_of_check_of_user_id";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);



      // ユーザIDを取得する
      $userId = $event["source"]["userId"];
      // メッセージで返す
      $message =  $userId;
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
      $situation1 = "replay_user_id";

      $flexMessage = [
        'type' => 'flex',
        'altText' => '問診票の登録完了',
        'contents' => [
            'type' => 'bubble',
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'button',
                        'action' => [
                            'type' => 'message',
                            'label' => '問診票の登録完了',
                            'text' => '問診票を記入しました！'
                        ]
                    ]
                ]
            ]
        ]
      ];
    

      $logMessage = $message;
      $count = 2;
      $situation2 = "do_use_user_id";

    }else if ($text == "問診票を記入しました！") {
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
      $situation = "require_of_set_userName";
      $logMessage = $text;
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      
      $message =  "ユーザ名を入力してください！";
      $logMessage = $message;
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // ユーザ名を入力してくださいと返す
      $situation = "requirement_of_userName";

    }else{
      if($type == "text") { // メッセージがテキストのとき
        // $text = $event->{"message"}->{"text"}; // ユーザから送信されたメッセージテキスト
        $text = $event["message"]["text"]; // メッセージテキスト
        // $text = "テスト！";

        //ログを残す前に，ユーザ名の入力を要求していたか確認
        $situation_log = getLatestSituation($userId)['situation'];
        error_log(print_r($situation_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        //メッセージのログを残す
        $situation = "send_something";
        putMessageLogMysql($sender, $text, $situation, $contents, $userId);

        //ログから改善項目を習得
        $situation = "choose_improvement_item";
        $targetNum = getTargetMysql($situation, $userId)[0]['contents'];


        if($situation_log == "requirement_of_userName"){
          $setWord = $text;
          $section = "userName";
          updateOneMysql($setWord,$targetNum, $section, $userId);

          $message = "ユーザ名を「". $text . "」に設定しました！";
          $logMessage = $message;
          $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
          $situation = "set_userName";
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
    if ($count == 1){
      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage],
      ]);
      $serialize_flexMessage = serialize($flexMessage);
      putMessageLogMysql($sender, $serialize_flexMessage, $situation, $contents, $userId);
    }else if($count == 2) {

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

      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage],
      ]);
      error_log(print_r("13" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      $serialize_flexMessage = serialize($flexMessage);
      error_log(print_r("13.5" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      putMessageLogMysql($sender, $serialize_flexMessage, $situation1, $contents, $userId);
      error_log(print_r("14" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


      sendMessage([
        "replyToken" => $replyToken,
        "messages" => $messages
      ]);
      putMessageLogMysql($sender, $logMessage, $situation2, $contents, $userId);

    }else if($count == 4){
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

function target($text, $targetNum, $userId) {
  // $pass_encrypt = main_backend();
  $pdo = connectMysql(); // DBとの接続開始
  $stmt = $pdo->prepare("SELECT * FROM mnsn_sheet_linebot_test where :line_uid = line_uid ORDER BY id DESC");
  $stmt->bindValue(':line_uid', $userId, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
  $stmt->execute();
  $all = $stmt->fetchAll(PDO::FETCH_ASSOC); //全件取得
  error_log(print_r('データベースから問診票の内容を取得' , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

    if($text == '運動を改善したい！'){
      $stmt2 = $pdo->prepare("INSERT INTO exercise_management (pass_hash, age, task, task_walk, commute, commuteTime, job_pedometerYes, holiday_pedometerYes, job_pedometerNo, holiday_pedometerNo, walkNum, walkCareer, gender, walkDayOfWeek, otherMotion, otherMotionFreq, OtherwalkCareer, otherMotionDayOfWeek, strongPoint, shortcoming, line_uid) SELECT pass_hash, age, task, task_walk, commute, commuteTime, job_pedometerYes, holiday_pedometerYes, job_pedometerNo, holiday_pedometerNo, walkNum, walkCareer, gender, walkDayOfWeek, otherMotion, otherMotionFreq, OtherwalkCareer, otherMotionDayOfWeek, strongPoint, shortcoming, line_uid FROM mnsn_sheet_linebot_test where :line_uid = line_uid ORDER BY id DESC");
      $stmt2->bindValue(':line_uid', $userId, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
      $stmt2->execute();
      error_log(print_r("インサートしました" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      $pass_hash = (string)$all[0]["pass_hash"];
      $age = (string)$all[0]["age"];
      $task = (string)$all[0]["task"]; 
      $task_walk = (string)$all[0]["task_walk"];
      $commute = (string)$all[0]["commute"];
      $commuteTime = (string)$all[0]["commuteTime"];
      // $job_pedometerYes = (string)$all[0]["job_pedometerYes"];
      // $holiday_pedometerYes = (string)$all[0]["holiday_pedometerYes"];
      // $job_pedometerNo = (string)$all[0]["job_pedometerNo"];
      // $holiday_pedometerNo = (string)$all[0]["holiday_pedometerNo"];
      $walkNum = (string)$all[0]["walkNum"];
      $walkCareer = (string)$all[0]["walkCareer"];
      $gender = (string)$all[0]["gender"];
      $walkDayOfWeek = (string)$all[0]["walkDayOfWeek"];
      $otherMotion = (string)$all[0]["otherMotion"];
      $otherMotionFreq = (string)$all[0]["otherMotionFreq"];
      $OtherwalkCareer = (string)$all[0]["OtherwalkCareer"];
      //$otherMotionDayOfWeek = (string)$all[0]["otherMotionDayOfWeek"];
      $strongPoint = (string)$all[0]["strongPoint"];
      $shortcoming = (string)$all[0]["shortcoming"];
      //$keep_days = (string)$all[0]["keep_days"];
      
      $message = "あなたは生活習慣病対策のための目標生成システムです．私の生活習慣をもとに，「毎日～する」というように「～」に当てはまる目標を考えてください．". "\n" . "条件：目標のみを箇条書きに答えてください．目標は４つ答えてください．”運動に関する目標”であること．”毎日行える内容である”こと．";
      $message = $message . "\n" . "私のデータ：";
      $message = $message . "性別は" . $gender . "です.";
      $message = $message . "年齢は" . $walkNum . ".";
      $message = $message . "仕事は" . $task . ".";
      $message = $message . $task_walk . ".";
      $message = $message . "通勤方法は" . $commute . ".";
      $message = $message . "通勤時間は" . $commuteTime . ".";
      $message = $message . "運動頻度は" . $walkNum . ".";
      $message = $message . "以前までの運動経歴は" . $OtherwalkCareer . ".";
      $message = $message . "1週間での運動頻度は" . $walkDayOfWeek . ".";
      $message = $message . "歩く以外の運動は" . $otherMotion . ".";
      $message = $message . "歩く以外の運動の頻度は" . $otherMotionFreq . "です．";
      //$message = $message . "歩く以外の運動の一週間あたりの頻度は" . $otherMotionDayOfWeek . ".";
      $message = $message . "歩く以外の運動の経歴は" . $OtherwalkCareer . ".";
      $message = $message . "短所は" . $shortcoming . ".";
      $message = $message . "長所は" . $strongPoint . ".";
      //$message_from_gpt = "こんにちは";

    }else if($text == 'タバコに関して改善したい！'){
      $stmt2 = $pdo->prepare("INSERT INTO smoking_management (pass_hash,age,gender,tabacco,tabaccoNum,tabaccoYear,tabaccoQuitNum,tabaccoQuitYear,strongPoint,shortcoming, line_uid) SELECT pass_hash,age,gender,tabacco,tabaccoNum,tabaccoYear,tabaccoQuitNum,tabaccoQuitYear,strongPoint,shortcoming, line_uid FROM mnsn_sheet_linebot_test where :line_uid = line_uid ORDER BY id DESC");
      $stmt2->bindValue(':line_uid', $userId, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
      $stmt2->execute();
      error_log(print_r("インサートしました" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


      $pass_hash = (string)$all[0]["pass_hash"];
      $age = (string)$all[0]["age"];
      $gender = (string)$all[0]["gender"];
      $tabacco = (string)$all[0]["tabacco"];
      $tabaccoNum = (string)$all[0]["tabaccoNum"];
      $tabaccoYear = (string)$all[0]["tabaccoYear"];
      $tabaccoQuitNum = (string)$all[0]["tabaccoQuitNum"];
      $tabaccoQuitYear =(string)$all[0]["tabaccoQuitYear"];
      $strongPoint = (string)$all[0]["strongPoint"];
      $shortcoming = (string)$all[0]["shortcoming"];
      $keep_days = (string)$all[0]["keep_days"];

      $message = "あなたは生活習慣病対策のための目標生成システムです．私の生活習慣をもとに，「毎日～する」というように「～」に当てはまる目標を考えてください．". "\n" . "条件：目標のみを箇条書きに答えてください．目標は４つ答えてください．”タバコに関する目標”であること．”毎日行える内容である”こと．";
      $message = $message . "\n" . "私のデータ：";
      $message = $message . "性別は" . $gender . "です.";
      $message = $message . "年齢は" . $age . ".";
      $message = $message . "タバコを吸ったことがあるかは" . $tabacco . ".";
      $message = $message . "タバコの本数は" . $tabaccoNum . ".";
      $message = $message . "タバコを吸ってからの年数は" . $tabaccoYear . ".";
      $message = $message . "タバコを止めたことがあるがその時の本数は" . $tabaccoQuitNum . ".";
      $message = $message . "タバコを止めたことがあるがその時の吸っていた年数は" . $tabaccoQuitYear . ".";
      $message = $message . "短所は" . $shortcoming . ".";
      $message = $message . "長所は" . $strongPoint . ".";


    }else if($text == 'お酒に関して改善したい！'){
      $stmt2 = $pdo->prepare("INSERT INTO alcohol_management (pass_hash,age,gender,sake,sakeType,sakeAmount,strongPoint,shortcoming,line_uid) SELECT pass_hash,age,gender,sake,sakeType,sakeAmount,strongPoint,shortcoming, line_uid FROM mnsn_sheet_linebot_test where :line_uid = line_uid ORDER BY id DESC");
      $stmt2->bindValue(':line_uid', $userId, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
      $stmt2->execute();

      $pass_hash = (string)$all[0]["pass_hash"];
      $age = (string)$all[0]["age"];
      $gender = (string)$all[0]["gender"];
      $sake = (string)$all[0]["sake"];
      $sakeType = (string)$all[0]["sakeType"];
      $sakeAmount = (string)$all[0]["sakeAmount"];
      $strongPoint = (string)$all[0]["strongPoint"];
      $shortcoming = (string)$all[0]["shortcoming"];
      $keep_days = (string)$all[0]["keep_days"];

      $message = "あなたは生活習慣病対策のための目標生成システムです．私の生活習慣をもとに，「毎日～する」というように「～」に当てはまる目標を考えてください．". "\n" . "条件：目標のみを箇条書きに答えてください．目標は４つ答えてください．”お酒に関する目標”であること．”毎日行える内容である”こと．";
      $message = $message . "\n" . "私のデータ：";
      $message = $message . "性別は" . $gender . "です.";
      $message = $message . "年齢は" . $age . ".";
      $message = $message . "お酒は" . $sake . ".";
      $message = $message . "お酒の種類は" . $sakeType . ".";
      $message = $message . "お酒の量は" . $sakeAmount . ".";
      $message = $message . "短所は" . $shortcoming . ".";
      $message = $message . "長所は" . $strongPoint . ".";
      

    }else if($text == '間食を改善したい！'){
      $stmt2 = $pdo->prepare("INSERT INTO snack_management (pass_hash,age,gender,snackFreq,snackMeal,snackStartTime,snack_compare,strongPoint,shortcoming,line_uid) SELECT pass_hash,age,gender,snackFreq,snackMeal,snackStartTime,snack_compare,strongPoint,shortcoming,line_uid FROM mnsn_sheet_linebot_test where :line_uid = line_uid ORDER BY id DESC");
      $stmt2->bindValue(':line_uid', $userId, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
      $stmt2->execute();

      $pass_hash = (string)$all[0]["pass_hash"];
      $age = (string)$all[0]["age"];
      $gender = (string)$all[0]["gender"];
      $snackFreq = (string)$all[0]["snackFreq"]; 
      $snackMeal = (string)$all[0]["snackMeal"]; 
      $snackStartTime = (string)$all[0]["snackStartTime"];
      $snack_compare = (string)$all[0]["snack_compare"];
      $strongPoint = (string)$all[0]["strongPoint"];
      $shortcoming = (string)$all[0]["shortcoming"];
      $keep_days = (string)$all[0]["keep_days"];

      $message = "あなたは生活習慣病対策のための目標生成システムです．私の生活習慣をもとに，「毎日～する」というように「～」に当てはまる目標を考えてください．". "\n" . "条件：目標のみを箇条書きに答えてください．目標は４つ答えてください．”間食に関する目標”であること．”毎日行える内容である”こと．";
      $message = $message . "\n" . "私のデータ：";
      $message = $message . "性別は" . $gender . "です.";
      $message = $message . "年齢は" . $age . ".";
      $message = $message . "間食の頻度は" . $snackFreq . ".";
      $message = $message . "間食で食べるものは" . $snackMeal . ".";
      $message = $message . "間食を食べる時間は" . $snackStartTime . ".";
      $message = $message . "以前と比べて間食は" . $snack_compare . ".";
      $message = $message . "短所は" . $shortcoming . ".";
      $message = $message . "長所は" . $strongPoint . ".";

    }else if($text == '朝食を改善したい！'){
      $stmt2 = $pdo->prepare("INSERT INTO  breakfast_management (pass_hash,age,gender,job_breakfastFreq,holiday_breakfastFreq,job_breakfastMeal,holiday_breakfastMeal,job_breakfastStartTime,holiday_breakfastStartTime,job_breakfastTime,holiday_breakfastTime,job_break_maker,holiday_break_maker,strongPoint,shortcoming,line_uid) SELECT pass_hash,age,gender,job_breakfastFreq,holiday_breakfastFreq,job_breakfastMeal,holiday_breakfastMeal,job_breakfastStartTime,holiday_breakfastStartTime,job_breakfastTime,holiday_breakfastTime,job_break_maker,holiday_break_maker,strongPoint,shortcoming,line_uid FROM mnsn_sheet_linebot_test where :line_uid = line_uid ORDER BY id DESC");
      $stmt2->bindValue(':line_uid', $userId, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
      $stmt2->execute();


      $pass_hash = (string)$all[0]["pass_hash"];
      $age = (string)$all[0]["age"];
      $gender = (string)$all[0]["gender"];
      $job_breakfastFreq = (string)$all[0]["job_breakfastFreq"]; 
      $holiday_breakfastFreq = (string)$all[0]["holiday_breakfastFreq"]; 
      $job_breakfastMeal = (string)$all[0]["job_breakfastMeal"];
      $holiday_breakfastMeal = (string)$all[0]["holiday_breakfastMeal"];
      $job_breakfastStartTime = (string)$all[0]["job_breakfastStartTime"];
      $holiday_breakfastStartTime = (string)$all[0]["holiday_breakfastStartTime"];
      $job_breakfastTime = (string)$all[0]["job_breakfastTime"];
      $holiday_breakfastTime = (string)$all[0]["holiday_breakfastTime"];
      $job_break_maker = (string)$all[0]["job_break_maker"];
      $holiday_break_maker = (string)$all[0]["holiday_break_maker"];
      $strongPoint = (string)$all[0]["strongPoint"];
      $shortcoming = (string)$all[0]["shortcoming"];
      $keep_days = (string)$all[0]["keep_days"];

      $message = "あなたは生活習慣病対策のための目標生成システムです．私の生活習慣をもとに，「毎日～する」というように「～」に当てはまる目標を考えてください．". "\n" . "条件：目標のみを箇条書きに答えてください．目標は４つ答えてください．”朝食に関する目標”であること．”毎日行える内容である”こと．";
      $message = $message . "\n" . "私のデータ：";
      $message = $message . "性別は" . $gender . "です.";
      $message = $message . "年齢は" . $age . ".";
      $message = $message . "仕事がある日の朝食は" . $job_breakfastFreq . ".";
      $message = $message . "仕事がある日の朝食のメニューは" . $job_breakfastMeal . ".";
      $message = $message . "仕事がある日の朝食が始まる時間は" . $job_breakfastStartTime . ".";
      $message = $message . "仕事がある日の朝食を食べる時間は" . $job_breakfastTime . ".";
      $message = $message . "仕事のある日の朝食を作る人は" . $job_break_maker . ".";
      $message = $message . "休みの日の朝食は" . $holiday_breakfastFreq . ".";
      $message = $message . "休みの日の朝食のメニューは" . $holiday_breakfastMeal . ".";
      $message = $message . "休みの日の朝食が始まる時間は" . $holiday_breakfastStartTime . ".";
      $message = $message . "休みの日の朝食を食べる時間は" . $holiday_breakfastTime . ".";
      $message = $message . "休みの日の朝食を作る人は" . $holiday_break_maker . ".";
      $message = $message . "短所は" . $shortcoming . ".";
      $message = $message . "長所は" . $strongPoint . ".";

    }else if($text == '睡眠を改善したい！'){
      $stmt2 = $pdo->prepare("INSERT INTO  sleep_management (pass_hash,age,gender,job_bedTime,holiday_bedTime,job_fallasleepTime,holiday_fallasleepTime,job_wakeupTime,holiday_wakeupTime,job_sleepQuality,holiday_sleepQuality,strongPoint,shortcoming,line_uid) SELECT pass_hash,age,gender,job_bedTime,holiday_bedTime,job_fallasleepTime,holiday_fallasleepTime,job_wakeupTime,holiday_wakeupTime,job_sleepQuality,holiday_sleepQuality,strongPoint,shortcoming,line_uid FROM mnsn_sheet_linebot_test where :line_uid = line_uid ORDER BY id DESC");
      $stmt2->bindValue(':line_uid', $userId, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
      $stmt2->execute();


      $pass_hash = (string)$all[0]["pass_hash"];
      $age = (string)$all[0]["age"];
      $gender = (string)$all[0]["gender"];
      $job_bedTime = (string)$all[0]["job_bedTime"];
      $holiday_bedTime = (string)$all[0]["holiday_bedTime"];
      $job_fallasleepTime = (string)$all[0]["job_fallasleepTime"];
      $holiday_fallasleepTime = (string)$all[0]["holiday_fallasleepTime"];
      $job_wakeupTime = (string)$all[0]["job_wakeupTime"];
      $holiday_wakeupTime = (string)$all[0]["holiday_wakeupTime"];
      $job_sleepQuality = (string)$all[0]["job_sleepQuality"];
      $holiday_sleepQuality = (string)$all[0]["holiday_sleepQuality"];
      $strongPoint = (string)$all[0]["strongPoint"];
      $shortcoming = (string)$all[0]["shortcoming"];
      $keep_days = (string)$all[0]["keep_days"];

      $message = "あなたは生活習慣病対策のための目標生成システムです．私の生活習慣をもとに，「毎日～する」というように「～」に当てはまる目標を考えてください．". "\n" . "条件：目標のみを箇条書きに答えてください．目標は４つ答えてください．”睡眠に関する目標”であること．”毎日行える内容である”こと．";
      $message = $message . "\n" . "私のデータ：";
      $message = $message . "性別は" . $gender . "です.";
      $message = $message . "年齢は" . $age . ".";
      $message = $message . "仕事がある日の寝る時間は" . $job_bedTime . ".";
      $message = $message . "仕事がある日の眠りにつくまでかかる時間は" . $job_fallasleepTime . ".";
      $message = $message . "仕事がある日の起きる時間は" . $job_wakeupTime . ".";
      $message = $message . "仕事がある日の睡眠の質は" . $job_sleepQuality . ".";
      $message = $message . "休みの日の寝る時間は" . $holiday_bedTime . ".";
      $message = $message . "休みの日の眠りにつくまでかかる時間は" . $holiday_fallasleepTime . ".";
      $message = $message . "休みの日の起きる時間は" . $holiday_wakeupTime . ".";
      $message = $message . "休みの日の睡眠の質は" . $holiday_sleepQuality . ".";
      $message = $message . "短所は" . $shortcoming . ".";
      $message = $message . "長所は" . $strongPoint . ".";


    }else if($text == '肥満を改善したい！'){
      $stmt2 = $pdo->prepare("INSERT INTO  obesity_management (pass_hash,age,gender,job_breakfastMeal,holiday_breakfastMeal,job_lunchMeal,holiday_lunchMeal,job_lunch_out_Freq,holiday_lunch_out_Freq,job_dinner_out_Freq,holiday_dinner_out_Freq,job_dinnerMeal,holiday_dinnerMeal,snackFreq,snackMeal,strongPoint,shortcoming,line_uid) SELECT pass_hash,age,gender,job_breakfastMeal,holiday_breakfastMeal,job_lunchMeal,holiday_lunchMeal,job_lunch_out_Freq,holiday_lunch_out_Freq,job_dinner_out_Freq,holiday_dinner_out_Freq,job_dinnerMeal,holiday_dinnerMeal,snackFreq,snackMeal,strongPoint,shortcoming,line_uid FROM mnsn_sheet_linebot_test where :line_uid = line_uid ORDER BY id DESC");
      $stmt2->bindValue(':line_uid', $userId, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
      $stmt2->execute();


      // $pass_hash = (string)$all[0]["pass_hash"];
      $age = (string)$all[0]["age"];
      $gender = (string)$all[0]["gender"];
      $job_breakfastMeal = (string)$all[0]["job_breakfastMeal"];
      $holiday_breakfastMeal = (string)$all[0]["holiday_breakfastMeal"];
      $job_lunchMeal = (string)$all[0]["job_lunchMeal"];
      $holiday_lunchMeal = (string)$all[0]["holiday_lunchMeal"];
      $job_lunch_out_Freq = (string)$all[0]["job_lunch_out_Freq"];
      $holiday_lunch_out_Freq = (string)$all[0]["holiday_lunch_out_Freq"];
      $job_dinner_out_Freq = (string)$all[0]["job_dinner_out_Freq"];
      $holiday_dinner_out_Freq = (string)$all[0]["holiday_dinner_out_Freq"];
      $job_dinnerMeal = (string)$all[0]["job_dinnerMeal"];
      $holiday_dinnerMeal = (string)$all[0]["holiday_dinnerMeal"];
      $snackFreq = (string)$all[0]["snackFreq"]; 
      $snackMeal = (string)$all[0]["snackMeal"]; 
      $strongPoint = (string)$all[0]["strongPoint"];
      $shortcoming = (string)$all[0]["shortcoming"];
      $keep_days = (string)$all[0]["keep_days"];

      $message = "あなたは生活習慣病対策のための目標生成システムです．私の生活習慣をもとに，「毎日～する」というように「～」に当てはまる目標を考えてください．". "\n" . "条件：目標のみを箇条書きに答えてください．目標は４つ答えてください．”肥満に関する目標”であること．”毎日行える内容である”こと．";
      $message = $message . "\n" . "私のデータ：";
      $message = $message . "性別は" . $gender . "です.";
      $message = $message . "年齢は" . $age . ".";
      $message = $message . "仕事がある日の朝食は" . $job_breakfastMeal . ".";
      $message = $message . "仕事がある日の昼食は" . $job_lunchMeal . ".";
      $message = $message . "仕事がある日の昼食で外食をする頻度は" . $job_lunch_out_Freq . ".";
      $message = $message . "仕事がある日の晩飯は" . $job_dinnerMeal . ".";
      $message = $message . "仕事がある日の晩飯で外食に行く頻度は" . $job_dinner_out_Freq . ".";
      $message = $message . "休みの日の朝食は" . $holiday_breakfastMeal . ".";
      $message = $message . "休みの日の昼食は" . $holiday_lunchMeal . ".";
      $message = $message . "休みの日の昼食で外食をする頻度は" . $holiday_lunch_out_Freq . ".";
      $message = $message . "休みの日の晩飯は" . $holiday_dinnerMeal . ".";
      $message = $message . "休みの日の晩飯で外食をする頻度は" . $holiday_dinner_out_Freq . ".";
      $message = $message . "間食の頻度は" . $snackFreq . ".";
      $message = $message . "間食で食べるものは" . $snackMeal . ".";
      $message = $message . "短所は" . $shortcoming . ".";
      $message = $message . "長所は" . $strongPoint . ".";

    }
    //ラインIDから同じ内容のデータが複数個保存されていないかを確認＆複数ある場合は最新のデータを保存して古いデータは削除
    // 最新のレコードを特定します。
    $latestRecordStmt = $pdo->prepare("SELECT id FROM " . $targetNum . " WHERE line_uid = :line_uid ORDER BY id DESC LIMIT 1");
    $latestRecordStmt->bindValue(':line_uid', $userId, PDO::PARAM_STR);
    $latestRecordStmt->execute();
    $latestRecordId = $latestRecordStmt->fetchColumn();

    if ($latestRecordId) {
        // 最新のレコード以外のレコードを削除します。
        $deleteStmt = $pdo->prepare("DELETE FROM " . $targetNum . " WHERE line_uid = :line_uid AND id != :latest_id");
        $deleteStmt->bindValue(':line_uid', $userId, PDO::PARAM_STR);
        $deleteStmt->bindValue(':latest_id', $latestRecordId, PDO::PARAM_INT);
        $deleteStmt->execute();

        error_log(print_r("Deleted old records for line_uid: $userId" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
    } else {
        error_log(print_r("No records found for line_uid: $userId" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
    }


    $message_from_gpt = call_chatGPT($message); // GPTにプロンプトを送信
  // }
  return $message_from_gpt;

}

function confirmation() {
  $flexMessage = [
    "type" => "flex",
    "altText" => "this is a flex message",
    "contents" => [
      "type" => "carousel",
      "contents" => [
          [
              "type" => "bubble",
              "size" => "giga",
              "header" => [
                  "type" => "box",
                  "layout" => "vertical",
                  "contents" => [
                      [
                          "type" => "text",
                          "text" => "今日は目標達成できましたか？",
                          "align" => "center",
                          "size" => "lg",
                          "gravity" => "center",
                          "position" => "relative"
                      ]
                  ],
                  "paddingTop" => "19px",
                  "paddingAll" => "12px",
                  "paddingBottom" => "16px"
              ],
              "body" => [
                  "type" => "box",
                  "layout" => "vertical",
                  "contents" => [
                      [
                          "type" => "box",
                          "layout" => "horizontal",
                          "contents" => [
                              [
                                  "type" => "button",
                                  "action" => [
                                      "type" => "message",
                                      "label" => "はい",
                                      "text" => "できました！"
                                  ],
                                  "style" => "primary",
                                  "color" => "#f7082f",
                                  "position" => "relative",
                                  "margin" => "none",
                                  "height" => "md",
                                  "scaling" => true,
                                  "adjustMode" => "shrink-to-fit",
                                  "offsetStart" => "xs",
                                  "offsetEnd" => "none"
                              ],
                              [
                                  "type" => "button",
                                  "action" => [
                                      "type" => "message",
                                      "label" => "いいえ",
                                      "text" => "できませんでした"
                                  ],
                                  "style" => "primary",
                                  "color" => "#1b08f7"
                              ]
                          ],
                          "flex" => 1,
                          "spacing" => "md"
                      ]
                  ],
                  "spacing" => "lg",
                  "paddingAll" => "12px",
                  "borderWidth" => "light"
              ],
              "styles" => [
                  "footer" => [
                      "separator" => false
                  ]
              ]
          ]
      ]
    ]
  ];
  return $flexMessage;
}
function targetAdd($text, $ary_from_gpt, $targetNum, $userId) {

  if($text == "一番目を選択します！" ){
    $targetName = $ary_from_gpt[0];
    $sql = "UPDATE " . $targetNum . " SET target = :targetName WHERE :line_uid = line_uid ORDER BY id DESC";
    putOneTargetMysql($sql, $targetName, $userId);
    $targetNumber = 0; 
  } else if ($text == "二番目を選択します！") {
    $targetName = $ary_from_gpt[1];
    $sql = "UPDATE " . $targetNum . " SET target = :targetName WHERE :line_uid = line_uid ORDER BY id DESC";
    putOneTargetMysql($sql, $targetName, $userId);
    $targetNumber = 1; 

  } else if ($text == "三番目を選択します！") {
    $targetName = $ary_from_gpt[2];
    $sql = "UPDATE " . $targetNum . " SET target = :targetName WHERE :line_uid = line_uid ORDER BY id DESC";
    putOneTargetMysql($sql, $targetName, $userId);
    $targetNumber = 2; 

  } else if ($text == "四番目を選択します！"){
    $targetName = $ary_from_gpt[3];
    $sql = "UPDATE " . $targetNum . " SET target = :targetName WHERE :line_uid = line_uid ORDER BY id DESC";
    putOneTargetMysql($sql, $targetName, $userId);
    $targetNumber = 3; 

  }
  return $targetNumber;
}

function treatment() {

}

function ranking($resultRank, $dayNum) {

  error_log(print_r("23" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


  
  $rankingColor = ["#DAA520", "#808080", "#8c4841", "#111111", "#111111", "#111111", "#111111", "#111111", "#111111", "#111111"];
  $rankingIcon = ["https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png", "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png"];
  $rankingSize = ["lg", "lg", "lg", "md", "md", "md", "md", "md", "md", "md"];
  
  $rankings = [];
  foreach($resultRank as $ranking){

    $rankings[] = ["rank" => $ranking['rank'], "user" => $ranking['user'], "icon" => $rankingIcon[$ranking['rank'] - 1], "color" => $rankingColor[$ranking['rank'] - 1], "size" => $rankingSize[$ranking['rank'] - 1], "keepDays" => $ranking['point']];
  }

  // $rankings = [
  //   ["rank" => $rank, "user" => "ユーザ１", "icon" => $rankingIcon[$rank - 1], "color" => $rankingColor[$rank - 1], "size" => $rankingSize[$rank - 1], "keepDays" => $keep_days]
    
  //   // 他のランキングデータも同様に追加
  // ];

  $rankingName = "ランキング " . $dayNum . "日目";

  $flexMessage = [
    "type" => "flex",
    "altText" => "this is a flex message",
    "contents" => [
      "type" => "bubble",
      "body" => [
          "type" => "box",
          "layout" => "vertical",
          "contents" => [
              [
                  "type" => "box",
                  "layout" => "baseline",
                  "contents" => [
                      [
                          "type" => "icon",
                          "url" => "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png",
                          "size" => "xl"
                      ],
                      [
                          "type" => "text",
                          "text" => $rankingName,
                          "weight" => "bold",
                          "size" => "xl",
                          "margin" => "md",
                          "decoration" => "none",
                          "position" => "relative",
                          "align" => "center",
                          "color" => "#CC6600"
                      ],
                      [
                          "type" => "icon",
                          "url" => "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gold_star_28.png",
                          "size" => "xl"
                      ]
                  ]
              ],
              [
                  "type" => "box",
                  "layout" => "vertical",
                  "margin" => "lg",
                  "spacing" => "sm",
                  "contents" => [
                      [
                          "type" => "separator",
                          "color" => "#CCCCCC"
                      ]
                    ]
                      ],
                    ],
              ]
            ]
  ];

  foreach ($rankings as $ranking) {
    $backgroundColor = ($ranking["user"] === "あなた") ? "#F3FFD8" : "#FFFFFF";
    $flexMessage["contents"]["body"]["contents"][1]["contents"][] = [
        "type" => "box",
        "layout" => "baseline",
        "backgroundColor" => $backgroundColor, // 背景色を動的に設定
        "contents" => [
            [
                "type" => "icon",
                "url" => $ranking["icon"],
                "size" => "sm"
            ],
            [
                "type" => "text",
                "text" => $ranking["rank"] . "位: " . $ranking["user"] . "　継続" . $ranking["keepDays"] . "日",
                "wrap" => true,
                "size" => $ranking["size"],
                "color" => $ranking["color"]
            ]
        ]
    ];
    $flexMessage["contents"]["body"]["contents"][1]["contents"][] = [
        "type" => "separator",
        "color" => "#CCCCCC"
    ];
  }

  error_log(print_r("24" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


  return $flexMessage;
}

function tool() {
  $flexMessage = [
    "type" => "flex",
    "altText" => "this is a flex message",
    "contents" => [
      "type" => "carousel",
      "contents" => [
          [
              "type" => "bubble",
              "body" => [
                  "type" => "box",
                  "layout" => "vertical",
                  "contents" => [
                      [
                          "type" => "button",
                          "action" => [
                              "type" => "message",
                              "label" => "目標の確認",
                              "text" => "目標を確認したい！"
                          ]
                      ]
                  ]
              ],
              "size" => "deca"
          ],
          [
              "type" => "bubble",
              "size" => "deca",
              "body" => [
                  "type" => "box",
                  "layout" => "vertical",
                  "contents" => [
                      [
                          "type" => "button",
                          "action" => [
                              "type" => "message",
                              "label" => "継続日数の確認",
                              "text" => "継続日数を確認したい！"
                          ]
                      ]
                  ]
              ]
          ],
      ]
    ]
  ];
  return $flexMessage;
}

function listManagement() {
  $flexMessage = [
    "type" => "flex",
    "altText" => "this is a flex message",
    "contents" => [
      "type" => "carousel",
      "contents" => [
          [
              "type" => "bubble",
              "body" => [
                  "type" => "box",
                  "layout" => "vertical",
                  "contents" => [
                      [
                          "type" => "button",
                          "action" => [
                              "type" => "message",
                              "label" => "運動を改善",
                              "text" => "運動を改善したい！"
                          ]
                      ]
                  ]
              ],
              "size" => "deca"
          ],
          [
              "type" => "bubble",
              "size" => "deca",
              "body" => [
                  "type" => "box",
                  "layout" => "vertical",
                  "contents" => [
                      [
                          "type" => "button",
                          "action" => [
                              "type" => "message",
                              "label" => "タバコを改善",
                              "text" => "タバコに関して改善したい！"
                          ]
                      ]
                  ]
              ]
          ],
          [
            "type" => "bubble",
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "button",
                        "action" => [
                            "type" => "message",
                            "label" => "お酒を改善",
                            "text" => "お酒に関して改善したい！"
                        ]
                    ]
                ]
            ],
            "size" => "deca"
        ],
        [
          "type" => "bubble",
          "body" => [
              "type" => "box",
              "layout" => "vertical",
              "contents" => [
                  [
                      "type" => "button",
                      "action" => [
                          "type" => "message",
                          "label" => "間食を改善",
                          "text" => "間食を改善したい！"
                      ]
                  ]
              ]
          ],
          "size" => "deca"
        ],
        [
          "type" => "bubble",
          "body" => [
              "type" => "box",
              "layout" => "vertical",
              "contents" => [
                  [
                      "type" => "button",
                      "action" => [
                          "type" => "message",
                          "label" => "朝食を改善",
                          "text" => "朝食を改善したい！"
                      ]
                  ]
              ]
          ],
          "size" => "deca"
        ],
        [
          "type" => "bubble",
          "body" => [
              "type" => "box",
              "layout" => "vertical",
              "contents" => [
                  [
                      "type" => "button",
                      "action" => [
                          "type" => "message",
                          "label" => "睡眠を改善",
                          "text" => "睡眠を改善したい！"
                      ]
                  ]
              ]
          ],
          "size" => "deca"
        ],
        [
          "type" => "bubble",
          "body" => [
              "type" => "box",
              "layout" => "vertical",
              "contents" => [
                  [
                      "type" => "button",
                      "action" => [
                          "type" => "message",
                          "label" => "肥満を改善",
                          "text" => "肥満を改善したい！"
                      ]
                  ]
              ]
          ],
          "size" => "deca"
        ]
      ]
    ]
  ];
  return $flexMessage;
}

function rankingMaker($userInfo, $userId) {
  // $userRank;
  error_log(print_r("16" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

  if ($userInfo["dayNum"] == 2) {
    if($userInfo["dayNum"] == $userInfo["keepDays"]){
      $userRank = 1;
    }else if($userInfo["keepDays"] == 1){
      $options = [2, 3];
      $randomIndex = array_rand($options);
      $userRank = $options[$randomIndex];
    }else if($userInfo["keepDays"] == 0){
      $userRank = 5;
    }

    if($userRank == 1){
      
      $userPoints["あなた"] = $userInfo["keepDays"];
      // 0から2までの数字を8回出力
      $num = 0;
      $options = [4, 5];
      $randomIndex = array_rand($options);
      $randomNum = $options[$randomIndex];

      for ($i = 0; $i < 9; $i++) {
        $result = customRandom();
        
        if($result == 2){
          $num++;
        }
        if($num > $randomNum){
          while($result == 2){
            $result = customRandom();
          }
        }
        $userPoints["ユーザ" . ($i + 1)] = $result;
      }
    }else if($userRank == 2 || $userRank == 3){
      $userPoints["あなた"] = $userInfo["keepDays"];
      // 0から2までの数字を8回出力
      $num2 = 0;
      $num1 = 0;
      for ($i = 0; $i < 9; $i++) {
        $result = customRandom1();
        
        if($result == 2){
          $num2++;
        }
        if($num2 >= $userRank){
          while($result == 2){
            $result = customRandom1();
          }
        }
        if($result == 1){
          $num1++;
        }
        if($num1 >= 5){
          while($result == 1){
            $result = 0;
          }
        }
        $userPoints["ユーザ" . ($i + 1)] = $result;
      }
    }else if($userRank == 5){
      $userPoints["あなた"] = $userInfo["keepDays"];

      $rankNum = 0;

      $options = [2, 3];
      $randomIndex = array_rand($options);
      $forNum = $options[$randomIndex];

      for ($i = 1; $i <= $forNum; $i++){
        $result = 2;
        $userPoints["ユーザ" . ($i)] = $result;
        $rankNum++;
      }

      $rankNum1 = $rankNum;
      for ($i = $rankNum; $i < 5; $i++){
        $result = 1;
        $userPoints["ユーザ" . ($rankNum1)] = $result;
        $rankNum1++;
      }
  
      $rankNum2 = $rankNum1;
      for ($i = $rankNum1; $i < 10; $i++){
        $result = 0;
        $userPoints["ユーザ" . ($rankNum2)] = $result;
        $rankNum2++;
      }

        // $result = customRandom2();
        
        // if($result == 2){
        //   $num2++;
        // }
        // if($num2 > 3){
        //   while($result == 2){
        //     $result = 1;
        //   }
        // }
        // if($result == 1){
        //   $num1++;
        // }
        // $sum12 = $num1 + $num2;
        // if($sum12 >= 4) {
        //   $result = 0;
        // }
        // $userPoints["ユーザ" . ($i + 1)] = $result;
      // }
    }

    // ポイントでユーザーを降順にソート
    arsort($userPoints);

    // 順位を生成
    $rank = 1;
    $prevPoints = null;
    $rankings = [];
    $count = 1;
    
    foreach ($userPoints as $user => $points) {
        if ($prevPoints !== null && $prevPoints != $points) {
            $rank = $count;
        }
        $rankings[] = [
        'user' => $user,
        'point' => $points,
        'rank' => $rank
        ];
        $prevPoints = $points;
        $count++;
    }
    
  }else if ($userInfo["dayNum"] == 4){
    if($userInfo["dayNum"] == $userInfo["keepDays"]){
      $userRank = 1;
    }else if($userInfo["keepDays"] == 3){
      $userRank = 2;
    }else if($userInfo["keepDays"] == 2){
      $userRank = 3;
    }else if($userInfo["keepDays"] == 1){
      // $options = [5, 4];
      // $randomIndex = array_rand($options);
      $userRank = 4;
    }else if($userInfo["keepDays"] == 0){
      $userRank = 5;
    }

    error_log(print_r("17" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


    $rankings = [];

    //ログからランキング作成前の配列を習得
    $situation = "flexMessage_of_ranking";
    $flexMessage_serialize = getTargetMysql($situation, $userId)[0]['contents'];
    $rankings = unserialize($flexMessage_serialize);

    foreach($rankings as &$ranking){
      $ranking['point'] = $ranking['point'] - $userInfo['weekKeepNum'];
    }
    error_log(print_r("18" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

    
    // $previousRank;
    // $differenceRank;
    $day0num = 0;
    $day1num = 0;
    $day2num = 0;
    // $day0rank;
    // $day1rank;
    // $day2rank;

    foreach($rankings as $ranking){
      if($ranking['user'] == "あなた"){
        $previousRank = $ranking['rank'];

      }else if($ranking['point'] == 0){
        $day0num++;
        $day0rank = $ranking['rank'];

      }else if($ranking['point'] == 1){
        $day1num++;
        $day1rank = $ranking['rank'];

      }else if($ranking['point'] == 2){
        $day2num++;
        $day2rank = $ranking['rank'];
      }
    }
    error_log(print_r("19" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
    

    if($userRank == 1){
      if($day2num == 5){
        $options = [3, 2];
        $randomIndex = array_rand($options);
        $sameRankNum = $options[$randomIndex];
      }else{
        $options = [1, 2];
        $randomIndex = array_rand($options);
        $sameRankNum = $options[$randomIndex];
      }
      
      $countSameRankNum = 0;

      while($countSameRankNum < $sameRankNum) {
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $previousRank){
            // error_log(print_r($countSameRankNum , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
            // error_log(print_r($sameRankNum , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

            $options = [0, 1, 2];
            $randomIndex = array_rand($options);
            $doDecreaseNum = $options[$randomIndex];
            if($doDecreaseNum == 1){
              $ranking['point'] = $ranking['point'] - 1;
              if($ranking['rank'] == 1){
                $countSameRankNum++;
              }
              
            }else if($doDecreaseNum == 2){
              $ranking['point'] = $ranking['point'] - 2;
              if($ranking['rank'] == 1){
                $countSameRankNum++;
              }
            }
            
          } 
          if($countSameRankNum >= $sameRankNum){
            break;
          }
        }
      }
      
      //breakでリセットされず，全部処理されるように分けてforeach
      $first_player1 = true;
      $first_player2 = true;
      foreach($rankings as &$ranking){
        if($ranking['user'] != 'あなた' && $ranking['rank'] != $previousRank){ 
          if($ranking['rank'] == $day0rank){
            if($first_player1){
              $ranking['point'] = $ranking['point'] - 2;
              $first_player1 = false;
            }else{
              $options = [1, 2, 0];
              $randomIndex = array_rand($options);
              $doDecreaseNum = $options[$randomIndex];
              if($doDecreaseNum == 1){
                $ranking['point'] = $ranking['point'] - 1;
                
              }else if($doDecreaseNum == 2){
                $ranking['point'] = $ranking['point'] - 2;
              }
            }
            
          }else{
            //日数が変わらない人を一人必ず作る
            if($first_player2){
              $ranking['point'] = $ranking['point'] - 2;
              $first_player2 = false;
            }else{
              //１位になるものがいないようにする．
              $options = [1, 2];
              $randomIndex = array_rand($options);
              $doDecreaseNum = $options[$randomIndex];
              if($doDecreaseNum == 1){
                $ranking['point'] = $ranking['point'] - 1;
                
              }else if($doDecreaseNum == 2){
                $ranking['point'] = $ranking['point'] - 2;
              }
            }
          }
          
        } 
      }
    

      foreach($rankings as &$ranking){
        $ranking['point'] = $ranking['point'] + 2;
      }

      // ポイントで降順に配列を並び替える
      usort($rankings, function($a, $b) {
        return $b['point'] - $a['point'];
      });

      // ランクを更新する
      $current_rank = 1;
      $count = 1;
      $current_point = $rankings[0]['point'];
      foreach ($rankings as &$item) {
        if ($item['point'] < $current_point){
          $current_point = $item['point'];
          $current_rank = $count;
        }
        $item['rank'] = $current_rank;
        $count++;
      }

    
    // $userPoints = [
    //   'あなた' => 7,
    //   'ユーザ8' => 6,
    //   'ユーザ7' => 6,
    //   'ユーザ4' => 5,
    //   'ユーザ1' => 4,
    //   'ユーザ2' => 4,
    //   'ユーザ5' => 3,
    //   'ユーザ6' => 3,
    //   'ユーザ9' => 2,
    //   'ユーザ3' => 1,
    // ];



    }else if($userRank == 2){
      $differenceRank = $userRank - $previousRank;
      if($day2num > 1){
        $count = $day2num;
        $first_player1 = true;
        while($count != 1){
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank && $ranking['point'] == 2){
              $options = [1, 2, 0];
              $randomIndex = array_rand($options);
              $doDecreaseNum = $options[$randomIndex];
              if($doDecreaseNum == 1){
                $ranking['point'] = $ranking['point'] - 1;
                $count--;
                
              }else if($doDecreaseNum == 2){
                $ranking['point'] = $ranking['point'] - 2;
                $count--;
              }
            }
            if($count == 1){
              break;
            } 
          }
        }
      }
      foreach($rankings as &$ranking){
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
          $ranking['point'] = $ranking['point'] + 2;
          $message_log = "ポイントをプラス２しました";
          error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
        }
      }

        // $options = [2, 3];
        // $randomIndex = array_rand($options);
        // $sameRankNum = $options[$randomIndex];
        // $countSameRankNum = 0;

        // while($countSameRankNum <= $sameRankNum) {
        //   foreach($rankings as &$ranking){
        //     if($ranking['user'] != 'あなた' && ($ranking['rank'] == $day1rank || $ranking['point'] == 3)){
        //       $options = [0, 1, 2];
        //       $randomIndex = array_rand($options);
        //       $doDecreaseNum = $options[$randomIndex];
        //       if($doDecreaseNum == 1){
        //         $ranking['point'] = $ranking['point'] - 1;
        //         if($ranking['rank'] == 1){
        //           $countSameRankNum++;
        //         }
                
        //       }else if($doDecreaseNum == 2){
        //         $ranking['point'] = $ranking['point'] - 2;
        //         if($ranking['rank'] == 1){
        //           $countSameRankNum++;
        //         }
        //       }
              
        //     } 
        //     if($countSameRankNum > $sameRankNum){
        //       break;
        //     }
        //   }
        // }
      $first_player = 0;
      $options = [2, 3];
      $randomIndex = array_rand($options);
      $sameRankNum = $options[$randomIndex];
      $userCount = 0;

      $chooseUser = array();
      $selectedUser = array();
      foreach($rankings as &$ranking){
        if($ranking['rank'] == $day1rank){
          $ranking['point'] = $ranking['point'] + 2;
        }
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
          array_push($chooseUser, $ranking['user']);
        }
        if($ranking['rank'] == $day2rank && $ranking['point'] == 3 && $ranking['user'] != 'あなた'){
          $userCount++;
        }
      }


      
      $sameRankNum = $sameRankNum - $userCount;
      $selectedUser = array_rand($chooseUser, $sameRankNum);
      
      if($sameRankNum > 0){
        if($sameRankNum == 1){
          
          $userName = $chooseUser[$selectedUser];
        
          foreach($rankings as &$ranking){
            
            if($ranking['user'] == $userName){
              $ranking['point'] = 3;

            }else if($ranking['rank'] == $day1rank && $ranking['user'] != 'あなた' && $first_player == 0){
              $options = [1, 2];
              $randomIndex = array_rand($options);
              $doDecreaseNum = $options[$randomIndex];
              if($doDecreaseNum == 1){
                $ranking['point'] = 2;
              }else if($doDecreaseNum == 2){
                $ranking['point'] = 1;

              }
            }
          }
        }else{
          foreach($selectedUser as $user){
            $userName = $chooseUser[$user];
            foreach($rankings as &$ranking){
              
              if($ranking['user'] == $userName){
                $ranking['point'] = 3;

              }else if($ranking['rank'] == $day1rank && $ranking['user'] != 'あなた' && $first_player == 0){
                $options = [1, 2];
                $randomIndex = array_rand($options);
                $doDecreaseNum = $options[$randomIndex];
                if($doDecreaseNum == 1){
                  $ranking['point'] = 2;
                }else if($doDecreaseNum == 2){
                  $ranking['point'] = 1;

                }
              }
            }
            $first_player = 1;
          }
        }
      }else {
        foreach($rankings as &$ranking){
          if($ranking['rank'] == $day1rank && $ranking['user'] != 'あなた'){
            $options = [1, 2];
            $randomIndex = array_rand($options);
            $doDecreaseNum = $options[$randomIndex];
            if($doDecreaseNum == 1){
              $ranking['point'] = 2;
              
            }else if($doDecreaseNum == 2){
              $ranking['point'] = 1;
            }
          }
        }
      }
      
      //前回継続日数が０日だった人の日数を変化させる
      foreach($rankings as &$ranking){
        if($ranking['rank'] == $day0rank){
          $ranking['point'] = $ranking['point'] + 2;
        }
      }

      $first_player1 = true;
      foreach($rankings as &$ranking){
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day0rank){ 
          if($first_player1){
            $ranking['point'] = $ranking['point'] - 2;
            $first_player1 = false;
          }else{
            $options = [1, 2, 0];
            $randomIndex = array_rand($options);
            $doDecreaseNum = $options[$randomIndex];
            if($doDecreaseNum == 1){
              $ranking['point'] = $ranking['point'] - 1;
              
            }else if($doDecreaseNum == 2){
              $ranking['point'] = $ranking['point'] - 2;
            }
          }   
        }
      }
      foreach($rankings as &$ranking){
        if($ranking['user'] == 'あなた'){
          $ranking['point'] = $userInfo['keepDays'];
        }
      }

      error_log(print_r("20" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


      // ポイントで降順に配列を並び替える
      usort($rankings, function($a, $b) {
        return $b['point'] - $a['point'];
      });

      // ランクを更新する
      $current_rank = 1;
      $count = 1;
      $current_point = $rankings[0]['point'];
      foreach ($rankings as &$item) {
        if ($item['point'] < $current_point){
          $current_point = $item['point'];
          $current_rank = $count;
        }
        $item['rank'] = $current_rank;
        $count++;
      }




        // $num = 0;
        // $userPoints["あなた"] = $userInfo["keepDays"];
        // // 0から2までの数字を8回出力
        // for ($i = 0; $i < 9; $i++) {
        //   $result = customRandom();
        //   if($result == 2){
        //     $num++;
        //   }
          
        //   if($num > 6){
        //     while($result != 2){
        //       $result = customRandom();
        //     }
        //   }
        //   $userToUpdate = "ユーザ" . ($i + 1);
        //   $pointsToAdd = $result;
        //   // $userPoints["あなた"] = 6;
        //   foreach ($userPoints1 as $user => $points) {
        //     if ($user == $userToUpdate) {
        //       $userPoints[$userToUpdate] = $points + $pointsToAdd;
        //     }
        //   }
        //   // $userPoints["ユーザ" . ($i + 1)] = $result;
        // }



       
       
      //  // ポイントでユーザーを降順にソート
      //  arsort($userPoints);
  
      //  // 順位を生成
      //  $rank = 1;
      //  $prevPoints = null;
      //  $rankings = [];
      //  $count = 1;
       
      //  foreach ($userPoints as $user => $points) {
      //      if ($prevPoints !== null && $prevPoints != $points) {
      //          $rank = $count;
      //      }
      //      $rankings[] = [
      //       'user' => $user,
      //       'point' => $points,
      //       'rank' => $rank
      //      ];
      //      $prevPoints = $points;
      //      $count++;
      //  }
       
    }else if($userRank == 3){
      $differenceRank = $userRank - $previousRank;
      $onlyKeeper = 1;
      $userCount = 0;
      if($day2num > 1){
        $count = $day2num;
        $first_player1 = 0;
        if($count > 1){
          while($count != 1){
            foreach($rankings as &$ranking){
              error_log(print_r($ranking , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
              $message_log = 'ランキングの詳細';
              error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
              error_log(print_r($day2rank , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
              $message_log = '上はデイ2ランク';
              error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank && $ranking['point'] == 2){
                $message_log = '1位と2位を決定';
                error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

                if($first_player1 == 0){
                  $doDecreaseNum = 1;
                  
                }else if($first_player1 == 1){
                  $options = [2, 0];
                  $randomIndex = array_rand($options);
                  $doDecreaseNum = $options[$randomIndex];
                }
                
                if($doDecreaseNum == 1 && $first_player1 == 0){
                  $ranking['point'] = $ranking['point'] - 1;
                  $count--;
                  $first_player1 = 1;
                  $message_log = '2位を決定';
                  error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
                  
                }else if($doDecreaseNum == 2){
                  $ranking['point'] = $ranking['point'] - 2;
                  $count--;
                  $message_log = '全部決まったからあとは3位に';
                  error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
                }
              } 
              if($count == 1){
                break;
              }
            }
          }
        }
      }else{
        $message_log = 'onlyKeeperの値を０にしました';
        error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
        $onlyKeeper = 0;
      }
      
      foreach($rankings as &$ranking){
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
          $ranking['point'] = $ranking['point'] + 2;
        }
      }

      $first_player = 0;
      $options = [3, 2];
      $randomIndex = array_rand($options);
      $sameRankNum = $options[$randomIndex];
    
      $chooseUser = array();
      $selectedUser = array();
      foreach($rankings as &$ranking){
        $message_log = '1位と2位以外を決定';
        error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
        if($ranking['rank'] == $day1rank && $ranking['user'] != 'あなた'){
          $ranking['point'] = $ranking['point'] + 2;
        }
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
          array_push($chooseUser, $ranking['user']);
        }

        if($ranking['rank'] == $day2rank && $ranking['point'] == 2 && $ranking['user'] != 'あなた'){
          $userCount++;
        }
        error_log(print_r($userCount , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

      }
      error_log(print_r($userCount , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      $message_log = '1位のランクから降りてきた人の数かつ，ユーザと同じ順位になる人';
      error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      error_log(print_r($chooseUser , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      $sameRankNum = $sameRankNum - $userCount;
      $selectedUser = array_rand($chooseUser, $sameRankNum);
      //１位から降りてきた人で充分であるかそうでないか
      if($sameRankNum > 0){
        if($sameRankNum == 1){
          foreach($rankings as &$ranking){
            $userName = $chooseUser[$selectedUser];
            if($ranking['user'] == $userName){
              error_log(print_r($userName , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
              $message_log = '選ばれたユーザが一人の時のユーザ名';
              error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
              $ranking['point'] = 2;
            }else if($ranking['rank'] == $day1rank && $ranking['user'] != 'あなた'){
                //まだ２位の人がいない場合は２位の人を作る
                if($onlyKeeper == 0){
                  $doDecreaseNum = 1;
                  if($doDecreaseNum == 1){
                    $ranking['point'] = 3;
                    $onlyKeeper = 1;
                  }else if($doDecreaseNum == 2){
                    $ranking['point'] = 1;
                  }
                }else{
                  $ranking['point'] = 1;
                }
            }
          }
        }else if($sameRankNum > 1){
          //選ばれたユーザの数だけ継続日数を２日にする
          foreach($selectedUser as $user){
            $userName = $chooseUser[$user];
            error_log(print_r($userName , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
            $message_log = '選ばれたユーザが複数人の時のユーザ名';
            error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
            foreach($rankings as &$ranking){
              if($ranking['user'] == $userName){
                $ranking['point'] = 2;
              }else if($first_player == 0 && $ranking['rank'] == $day1rank && $ranking['user'] != 'あなた' && $ranking['point'] == 1){
                  //まだ２位の人がいない場合は２位の人を作る
                    if($onlyKeeper == 0){
                      $doDecreaseNum = 1;
                    }else{
                      $doDecreaseNum = 2;
                    }
                    if($doDecreaseNum == 1){
                      $ranking['point'] = 3;
                      $onlyKeeper = 1;
                      error_log(print_r($onlyKeeper , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
                      $message_log = 'onlykeeperの値を1にしました';
                      error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
                    }else if($doDecreaseNum == 2){
                      $ranking['point'] = 1;
                    }
              }
            }
            
            $first_player = 1;
          }
          error_log(print_r($onlyKeeper , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
          $message_log = 'onlykeeperの値を返す';
          error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
          if($onlyKeeper == 0){
            $message_log = '強制的に三位を作るように変えました';
            error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
            foreach($rankings as &$ranking){
              if($ranking['rank'] == $day1rank && $ranking['user'] != 'あなた'){
                if($onlyKeeper == 0){
                  $doDecreaseNum = 1;
                }else{
                  $doDecreaseNum = 2;
                }
                if($doDecreaseNum == 1){
                  $ranking['point'] = 3;
                  $onlyKeeper = 1;
                }
              }
            }
          }
        }else{
          foreach($rankings as &$ranking){
            if($ranking['rank'] == $day1rank && $ranking['user'] != 'あなた'){
              //まだ２位の人がいない場合は２位の人を作る
              if($onlyKeeper == 0){
                $options = [1, 2];
                $randomIndex = array_rand($options);
                $doDecreaseNum = $options[$randomIndex];
                if($doDecreaseNum == 1){
                  $ranking['point'] = 3;
                  $onlyKeeper = 1;
                }else if($doDecreaseNum == 2){
                  $ranking['point'] = 1;
                }
              }else{
                $ranking['point'] = 1;
              }
            }
          }
        }
      }else {
        foreach($rankings as &$ranking){
          if($ranking['rank'] == $day1rank && $ranking['user'] != 'あなた'){
            //まだ２位の人がいない場合は２位の人を作る
            if($onlyKeeper == 0){
              $doDecreaseNum = 1;
              if($doDecreaseNum == 1){
                $ranking['point'] = 3;
                $onlyKeeper = 1;
              }else if($doDecreaseNum == 2){
                $ranking['point'] = 1;
              }
            }else{
              $ranking['point'] = 1;
            }
          }
        }
        // foreach($rankings as &$ranking){
        //   if($ranking['rank'] == $day1rank && $ranking['user'] != 'あなた'){
        //     $ranking['point'] = 1;
        //   }
        // }
      }
      if($day0num > 4){
        while ($day0num > 3){
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day0rank){
              $options = [1, 2];
              $randomIndex = array_rand($options);
              $doDecreaseNum = $options[$randomIndex];
              if($doDecreaseNum == 1){
                $ranking['point'] = 1;
                $day0num--;
              }else if($doDecreaseNum == 2){
                $ranking['point'] = 0;
              }
            }
            if($day0num == 3){
              break;
            }
          }
        }
      }else if($day0num == 4){
        while ($day0num > 2){
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day0rank){
              $options = [1, 2];
              $randomIndex = array_rand($options);
              $doDecreaseNum = $options[$randomIndex];
              if($doDecreaseNum == 1){
                $ranking['point'] = 1;
                $day0num--;
              }else if($doDecreaseNum == 2){
                $ranking['point'] = 0;
              }
            }
            if($day0num == 2){
              break;
            }
          }
        }
      }


      foreach($rankings as &$ranking){
        if($ranking['user'] == 'あなた'){
          $ranking['point'] = $userInfo['keepDays'];
        }
      }



      // ポイントで降順に配列を並び替える
      usort($rankings, function($a, $b) {
        return $b['point'] - $a['point'];
      });

      // ランクを更新する
      $current_rank = 1;
      $count = 1;
      $current_point = $rankings[0]['point'];
      foreach ($rankings as &$item) {
        if ($item['point'] < $current_point){
          $current_point = $item['point'];
          $current_rank = $count;
        }
        $item['rank'] = $current_rank;
        $count++;
      }

      

    }else if($userRank == 4){
      $differenceRank = $userRank - $previousRank;
      $onlyKeeper = false;
      if($day2num > 1){
        $count = $day2num;
        $first_player1 = 0;
        $first_player2 = 0;
        if($day2num == 2){
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank && $ranking['point'] == 2){
              if($first_player1 == 0){
                $ranking['point'] = 4;
                $first_player1 = 1;
              }else if($first_player1 == 1){
                $ranking['point'] = 3;
              } 
            }
          }
        }else{
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank && $ranking['point'] == 2){
              if($first_player1 == 0){
                $ranking['point'] = 4;
                $first_player1 = 1;
              }else if($first_player1 == 1){
                $ranking['point'] = 3;
                $first_player1 == 2;
              } else {
                $ranking['point'] = 2;
              }
            }
          }
        }
      }else{
        $onlyKeeper = true;
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
            $ranking['point'] = $ranking['point'] + 2;
          }
        }
      }

      $chooseUser = array();
      $selectedUser = array();
      foreach($rankings as &$ranking){
        // if($ranking['rank'] == $day1rank){
        //   $ranking['point'] = $ranking['point'] + 2;
        // }
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
          array_push($chooseUser, $ranking['user']);
        }
      }
      //１位だけが決まっているか，２位まで決まっているか
      if($day2num == 1){
        $pointNum = 2;
        $selectedUser = array_rand($chooseUser, 2);
        foreach($selectedUser as $user){
          $userName = $chooseUser[$user];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = $pointNum;
              $pointNum++;
            }
          }
        }
      }else if($day2num == 2){
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 2;
          }
        }
      }
      $keep1num = 0;
      foreach($rankings as &$ranking){
        if($ranking['point'] == 1 && $ranking['user'] != 'あなた'){
          $keep1num++;
        }
      }
      $chooseUser = array();
      $selectedUser = array();
      if($keep1num < 3){
        $needNum = 3 - $keep1num;
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day0rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, $needNum);
        if($needNum > 1){
          foreach($selectedUser as $user){
            $userName = $chooseUser[$user];
            foreach($rankings as &$ranking){
              if($ranking['user'] == $userName){
                $ranking['point'] = 1;
              }
            }
          }
        }else{
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 1;
            }
          }
        }

      }

      foreach($rankings as &$ranking){
        if($ranking['user'] == 'あなた'){
          $ranking['point'] = $userInfo['keepDays'];
        }
      }



      // ポイントで降順に配列を並び替える
      usort($rankings, function($a, $b) {
        return $b['point'] - $a['point'];
      });

      // ランクを更新する
      $current_rank = 1;
      $count = 1;
      $current_point = $rankings[0]['point'];
      foreach ($rankings as &$item) {
        if ($item['point'] < $current_point){
          $current_point = $item['point'];
          $current_rank = $count;
        }
        $item['rank'] = $current_rank;
        $count++;
      }


    }else if($userRank == 5){
      // dayが2日のデータをもとにランキングを変更
      $differenceRank = $userRank - $previousRank;
      $options = [1, 2];
      $randomIndex = array_rand($options);
      $doKeepNum = $options[$randomIndex];
      // KeepDayが2日だった人をdoKeepNum人選ぶ
      $chooseUser = array();
      $selectedUser = array();
      foreach($rankings as &$ranking){
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
          array_push($chooseUser, $ranking['user']);
        }
      }
      $selectedUser = array_rand($chooseUser, $doKeepNum);
      
      foreach($selectedUser as $user){
        $userName = $chooseUser[$user];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 4;
          }
        }
      }
      $first_player = 0;
      if($doKeepNum == 1){
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank && $ranking['point'] == 2 && $first_player == 0){
            $ranking['point'] = 3;
          }
          $first_player = 1;
        }
      }    
      $options = [1, 0];
      $randomIndex = array_rand($options);
      $doKeep1dayNum = $options[$randomIndex];  
      $chooseUser = array();
      $selectedUser = array();
      foreach($rankings as &$ranking){
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
          array_push($chooseUser, $ranking['user']);
        }
      }
      if($doKeep1dayNum > 0){
        $selectedUser = array_rand($chooseUser, $doKeep1dayNum);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 2;
            }
          }
      }
      foreach($rankings as &$ranking){
        if($ranking['user'] == 'あなた'){
          $ranking['point'] = $userInfo['keepDays'];
        }
      }



      // ポイントで降順に配列を並び替える
      usort($rankings, function($a, $b) {
        return $b['point'] - $a['point'];
      });

      // ランクを更新する
      $current_rank = 1;
      $count = 1;
      $current_point = $rankings[0]['point'];
      foreach ($rankings as &$item) {
        if ($item['point'] < $current_point){
          $current_point = $item['point'];
          $current_rank = $count;
        }
        $item['rank'] = $current_rank;
        $count++;
      }

    }

    error_log(print_r("21" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


  }else if($userInfo["dayNum"] == 6){
    if($userInfo["dayNum"] == $userInfo["keepDays"]){
      $userRank = 1;
    }else if($userInfo["keepDays"] == 5){
      $userRank = 2;
    }else if($userInfo["keepDays"] == 4){
      $userRank = 3;
    }else if($userInfo["keepDays"] == 3){
      $userRank = 3;
    }else if($userInfo["keepDays"] ==2){
      $userRank = 4;
    }else if($userInfo["keepDays"] ==1){
      $userRank = 5;
    }else if($userInfo["keepDays"] ==0){
      $userRank = 5;
    }

    $rankings = [];

    //ログからランキング作成前の配列を習得
    $situation = "flexMessage_of_ranking";
    $flexMessage_serialize = getTargetMysql($situation, $userId)[0]['contents'];
    $rankings = unserialize($flexMessage_serialize);

    foreach($rankings as &$ranking){
      $ranking['point'] = $ranking['point'] - $userInfo['weekKeepNum'];
    }

    // $previousRank;
    // $differenceRank;
    $day0num = 0;
    $day1num = 0;
    $day2num = 0;
    $day3num = 0;
    $day4num = 0;
    // $day0rank;
    // $day1rank;
    // $day2rank;
    // $day3rank;
    // $day4rank;


    foreach($rankings as $ranking){
      if($ranking['user'] == "あなた"){
        $previousRank = $ranking['rank'];

      }else if($ranking['point'] == 0){
        $day0num++;
        $day0rank = $ranking['rank'];

      }else if($ranking['point'] == 1){
        $day1num++;
        $day1rank = $ranking['rank'];

      }else if($ranking['point'] == 2){
        $day2num++;
        $day2rank = $ranking['rank'];
      }else if($ranking['point'] == 3){
        $day3num++;
        $day3rank = $ranking['rank'];
      }else if($ranking['point'] == 4){
        $day4num++;
        $day4rank = $ranking['rank'];
      }
    }

    if($userRank == 1){
      //dayが4日のデータをもとにランキングを変更
      $differenceRank = $userRank - $previousRank;
      // KeepDayが6日だった人を1人選ぶ
      $chooseUser = array();
      $selectedUser = array();
      foreach($rankings as &$ranking){
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
          array_push($chooseUser, $ranking['user']);
        }
      }
      $selectedUser = array_rand($chooseUser, 1);
      $userName = $chooseUser[$selectedUser];
      foreach($rankings as &$ranking){
        if($ranking['user'] == $userName){
          $ranking['point'] = 6;
          $day4num--;
        }
      }
      //5位になる人を作って置く
      $day5num = 0;
      //配列に３日継続できた人を格納
      $chooseUser = array();
      $selectedUser = array();
      foreach($rankings as &$ranking){
        if($day4num > 0 && $ranking['rank'] == $day4rank && $ranking['user'] != 'あなた' && $day5num == 0 && $ranking['point'] == 4){
          $ranking['point'] = 5;
          $day5num++;
          $day4num--;
        }
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
          array_push($chooseUser, $ranking['user']);
        }
      }
      
      if($day4num == 0 && $day3num > 0){
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 4;
            $day4num++;
            $day3num--;
          }
        }

      }else if($day4num == 0 && $day3num == 0 && $day2num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 4;
            $day4num++;
            $day2num--;
          }
        }
      }

      if($day3num == 0 && $day2num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank && $ranking['point'] == 2){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 3;
            $day3num++;
            $day2num--;
          }
        }
      }else if($day3num == 0 && $day2num == 0 && $day1num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 3;
            $day3num++;
            $day1num--;
          }
        }
      }
      if($day2num == 0 && $day1num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank && $ranking['point'] == 1){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 2;
            $day2num++;
            $day1num--;
          }
        }
      }else if($day2num == 0 && $day1num == 0 && $day0num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day0rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 2;
            $day2num++;
            $day0num--;
          }
        }
      }

      foreach($rankings as &$ranking){
        if($ranking['user'] == 'あなた'){
          $ranking['point'] = $userInfo['keepDays'];
        }
      }


      // ポイントで降順に配列を並び替える
      usort($rankings, function($a, $b) {
        return $b['point'] - $a['point'];
      });

      // ランクを更新する
      $current_rank = 1;
      $count = 1;
      $current_point = $rankings[0]['point'];
      foreach ($rankings as &$item) {
        if ($item['point'] < $current_point){
          $current_point = $item['point'];
          $current_rank = $count;
        }
        $item['rank'] = $current_rank;
        $count++;
      }



    }else if($userRank == 2){
      // day4のデータをもとにランキングを変更
      $differenceRank = $userRank - $previousRank;
      if($day4num == 1){
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
            $ranking['point'] = 6;
          }
        }
        //ランダムで５位の人を一人選ぶ
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 5;
          }else if($ranking['rank'] == $day3rank && $ranking['user'] != 'あなた' && $ranking['point'] == 3){
            $ranking['point'] = 4;
          }
        }
      }else {
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $first_player = 0;
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 6;
          }else if($ranking['rank'] == $day4rank && $ranking['user'] != 'あなた' && $first_player == 0){
            $ranking['point'] = 5;
            $first_player = 1;
          }
        }
        if($day4num == 0 && $day3num > 0){
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 4;
            }
          }
        }else if($day4num == 0 && $day3num == 0 && $day2num > 0){
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 4;
            }
          }
        }
      }
      if($day3num == 0 && $day2num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank && $ranking['point'] == 2){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 3;
            $day3num++;
            $day2num--;
          }
        }
      }else if($day3num == 0 && $day2num == 0 && $day1num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 3;
            $day3num++;
            $day1num--;
          }
        }
      }
      if($day2num == 0 && $day1num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank && $ranking['point'] == 1){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 2;
            $day2num++;
            $day1num--;
          }
        }
      }else if($day2num == 0 && $day1num == 0 && $day0num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day0rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 2;
            $day2num++;
            $day0num--;
          }
        }
      }

      foreach($rankings as &$ranking){
        if($ranking['user'] == 'あなた'){
          $ranking['point'] = $userInfo['keepDays'];
        }
      }



      // ポイントで降順に配列を並び替える
      usort($rankings, function($a, $b) {
        return $b['point'] - $a['point'];
      });

      // ランクを更新する
      $current_rank = 1;
      $count = 1;
      $current_point = $rankings[0]['point'];
      foreach ($rankings as &$item) {
        if ($item['point'] < $current_point){
          $current_point = $item['point'];
          $current_rank = $count;
        }
        $item['rank'] = $current_rank;
        $count++;
      }

    }else if($userRank == 3){
      $day5num = 0;
      if($userInfo["keepDays"] == 4){
        if($day4num == 1){
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
              $ranking['point'] = 6;
              $day4num--;
            }
          }
          if($day3num == 1){
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
                $ranking['point'] = 5;
                $day5num++;
                $day3num--;
              }
            }
          }else{
            //ランダムで５位の人を一人選ぶ
            $chooseUser = array();
            $selectedUser = array();
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
                array_push($chooseUser, $ranking['user']);
              }
            }
            $selectedUser = array_rand($chooseUser, 1);
            $userName = $chooseUser[$selectedUser];
            foreach($rankings as &$ranking){
              if($ranking['user'] == $userName){
                $ranking['point'] = 5;
                $day5num++;
                $day3num--;
              }
            }
          }
        }else{
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 6;
              $day4num--;
            }
          }
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank && $ranking['point'] == 4){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 5;
              $day5num++;
              $day4num--;
            }
          }

        }
        if($day4num > 0){
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank && $ranking['point'] == 4){
              $ranking['point'] = 4;
            }
          }
        }else if($day4num == 0 && $day3num > 0){
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 4;
              $day4num++;
              $day3num--;
            }
          }

        }else if($day4num == 0 && $day3num == 0 && $day2num > 0){
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 4;
              $day4num++;
              $day2num--;
            }
          }
        }
        if($day3num == 0 && $day2num > 0){
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank && $ranking['point'] == 2){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 3;
              $day3num++;
              $day2num--;
            }
          }
        }
        if($day2num == 0 && $day1num > 0){
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 2;
              $day2num++;
              $day1num--;
            }
          }
        }


      }else{
        if($day4num == 1){
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
              $ranking['point'] = 6;
            }
          }
          if($day3num == 1){
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
                $ranking['point'] = 5;
                $day5num++;
                $day3num--;
              }
            }
          }else{
            //ランダムで５位の人を一人選ぶ
            $chooseUser = array();
            $selectedUser = array();
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
                array_push($chooseUser, $ranking['user']);
              }
            }
            $selectedUser = array_rand($chooseUser, 1);
            $userName = $chooseUser[$selectedUser];
            foreach($rankings as &$ranking){
              if($ranking['user'] == $userName){
                $ranking['point'] = 5;
                $day5num++;
                $day3num--;
              }
            }
          }
        }else{
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $first_player = 0;
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 6;
            }else if($ranking['rank'] == $day4rank && $ranking['user'] != 'あなた' && $first_player == 0){
              $ranking['point'] = 5;
              $first_player = 1;
            }
          }
        }
        if($day3num == 0 && $day2num > 0){
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 3;
              $day3num++;
              $day2num--;
            }
          }
        }else if($day3num == 0 && $day2num == 0 && $day1num > 0){
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 3;
              $day3num++;
              $day1num--;
            }
          }
        }
      }

      foreach($rankings as &$ranking){
        if($ranking['user'] == 'あなた'){
          $ranking['point'] = $userInfo['keepDays'];
        }
      }



      // ポイントで降順に配列を並び替える
      usort($rankings, function($a, $b) {
        return $b['point'] - $a['point'];
      });

      // ランクを更新する
      $current_rank = 1;
      $count = 1;
      $current_point = $rankings[0]['point'];
      foreach ($rankings as &$item) {
        if ($item['point'] < $current_point){
          $current_point = $item['point'];
          $current_rank = $count;
        }
        $item['rank'] = $current_rank;
        $count++;
      }
    }else if($userRank == 4){
      $day5num = 0;
      if($day4num == 1){
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
            $ranking['point'] = 6;
          }
        }
        if($day3num == 1){
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
              $ranking['point'] = 4;
              $day5num++;
              $day3num--;
            }
          }
        }else{
          //ランダムで５位の人を一人選ぶ
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 4;
              $day5num++;
              $day3num--;
            }
          }
        }
      }else{
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 6;
            $day4num--;
          }
        }
        

      }
      if($day3num == 0 && $day2num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 3;
            $day3num++;
            $day2num--;
          }
        }

      }else if($day3num == 0 && $day2num == 0 && $day1num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 3;
            $day3num++;
            $day1num--;
          }
        }
      }
      if($day2num == 0 && $day1num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank && $ranking['point'] == 1){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 2;
            $day2num++;
            $day1num--;
          }
        }
      }else if($day2num == 0 && $day1num == 0 && $day0num > 0){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day0rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 2;
            $day2num++;
            $day0num--;
          }
        }
      }

      foreach($rankings as &$ranking){
        if($ranking['user'] == 'あなた'){
          $ranking['point'] = $userInfo['keepDays'];
        }
      }




      // ポイントで降順に配列を並び替える
      usort($rankings, function($a, $b) {
        return $b['point'] - $a['point'];
      });

      // ランクを更新する
      $current_rank = 1;
      $count = 1;
      $current_point = $rankings[0]['point'];
      foreach ($rankings as &$item) {
        if ($item['point'] < $current_point){
          $current_point = $item['point'];
          $current_rank = $count;
        }
        $item['rank'] = $current_rank;
        $count++;
      }

    }else if($userRank == 5){
      $day5num = 0;
      if($userInfo["keepDays"] == 1){
        if($day4num == 1){
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
              $ranking['point'] = 6;
            }
          }
          if($day3num == 1){
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
                $ranking['point'] = 4;
                $day4num++;
                $day3num--;
              }
            }
          }else{
            //ランダムで５位の人を一人選ぶ
            $chooseUser = array();
            $selectedUser = array();
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
                array_push($chooseUser, $ranking['user']);
              }
            }
            $selectedUser = array_rand($chooseUser, 1);
            $userName = $chooseUser[$selectedUser];
            foreach($rankings as &$ranking){
              if($ranking['user'] == $userName){
                $ranking['point'] = 4;
                $day5num++;
                $day3num--;
              }
            }
          }
          if($day2num == 1){
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
                $ranking['point'] = 3;
                $day3num++;
                $day2num--;
              }
            }
          }else{
            //ランダムで５位の人を一人選ぶ
            $chooseUser = array();
            $selectedUser = array();
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
                array_push($chooseUser, $ranking['user']);
              }
            }
            $selectedUser = array_rand($chooseUser, 1);
            $userName = $chooseUser[$selectedUser];
            foreach($rankings as &$ranking){
              if($ranking['user'] == $userName){
                $ranking['point'] = 3;
                $day3num++;
                $day2num--;
              }
            }
          }
          if($day1num > 1){
            $chooseUser = array();
            $selectedUser = array();
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
                array_push($chooseUser, $ranking['user']);
              }
            }
            $selectedUser = array_rand($chooseUser, 1);
            $userName = $chooseUser[$selectedUser];
            foreach($rankings as &$ranking){
              if($ranking['user'] == $userName){
                $ranking['point'] = 2;
                $day2num++;
                $day1num--;
              }
            }
          }

        }

      }else{
        if($userInfo["keepDays"] == 1){
          if($day4num == 1){
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
                $ranking['point'] = 6;
              }
            }
            if($day3num == 1){
              foreach($rankings as &$ranking){
                if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
                  $ranking['point'] = 4;
                  $day4num++;
                  $day3num--;
                }
              }
            }else{
              //ランダムで５位の人を一人選ぶ
              $chooseUser = array();
              $selectedUser = array();
              foreach($rankings as &$ranking){
                if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
                  array_push($chooseUser, $ranking['user']);
                }
              }
              $selectedUser = array_rand($chooseUser, 1);
              $userName = $chooseUser[$selectedUser];
              foreach($rankings as &$ranking){
                if($ranking['user'] == $userName){
                  $ranking['point'] = 4;
                  $day5num++;
                  $day3num--;
                }
              }
            }
            if($day2num == 1){
              foreach($rankings as &$ranking){
                if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
                  $ranking['point'] = 3;
                  $day3num++;
                  $day2num--;
                }
              }
            }else{
              //ランダムで５位の人を一人選ぶ
              $chooseUser = array();
              $selectedUser = array();
              foreach($rankings as &$ranking){
                if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
                  array_push($chooseUser, $ranking['user']);
                }
              }
              $selectedUser = array_rand($chooseUser, 1);
              $userName = $chooseUser[$selectedUser];
              foreach($rankings as &$ranking){
                if($ranking['user'] == $userName){
                  $ranking['point'] = 3;
                  $day3num++;
                  $day2num--;
                }
              }
            }
            
  
          }else{
            //ランダムで6位の人を一人選ぶ
            $chooseUser = array();
            $selectedUser = array();
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
                array_push($chooseUser, $ranking['user']);
              }
            }
            $selectedUser = array_rand($chooseUser, 1);
            $userName = $chooseUser[$selectedUser];
            foreach($rankings as &$ranking){
              if($ranking['user'] == $userName){
                $ranking['point'] = 6;
                $day5num++;
                $day3num--;
              }
            }
            if($day2num > 1){
              foreach($rankings as &$ranking){
                if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
                  array_push($chooseUser, $ranking['user']);
                }
              }
              $selectedUser = array_rand($chooseUser, 1);
              $userName = $chooseUser[$selectedUser];
              foreach($rankings as &$ranking){
                if($ranking['user'] == $userName){
                  $ranking['point'] = 3;
                  $day3num++;
                  $day2num--;
                }
              }
            }
          }
        }
      }
    }

    foreach($rankings as &$ranking){
      if($ranking['user'] == 'あなた'){
        $ranking['point'] = $userInfo['keepDays'];
      }
    }




    // ポイントで降順に配列を並び替える
    usort($rankings, function($a, $b) {
      return $b['point'] - $a['point'];
    });

    // ランクを更新する
    $current_rank = 1;
    $count = 1;
    $current_point = $rankings[0]['point'];
    foreach ($rankings as &$item) {
      if ($item['point'] < $current_point){
        $current_point = $item['point'];
        $current_rank = $count;
      }
      $item['rank'] = $current_rank;
      $count++;
    }

  }else if($userInfo["dayNum"] == 7){
    if($userInfo["dayNum"] == $userInfo["keepDays"]){
      $userRank = 1;
    }else if($userInfo["keepDays"] == 6){
      $userRank = 1;
    }else if($userInfo["keepDays"] == 5){
      $userRank = 2;
    }else if($userInfo["keepDays"] == 4){
      $userRank = 3;
    }else if($userInfo["keepDays"] == 3){
      $userRank = 4;
    }else if($userInfo["keepDays"] ==2){
      $userRank = 4;
    }else if($userInfo["keepDays"] ==1){
      $userRank = 5;
    }else if($userInfo["keepDays"] ==0){
      $userRank = 5;
    }

    $rankings = [];

    //ログからランキング作成前の配列を習得
    $situation = "flexMessage_of_ranking";
    $flexMessage_serialize = getTargetMysql($situation, $userId)[0]['contents'];
    $rankings = unserialize($flexMessage_serialize);

    foreach($rankings as &$ranking){
      $ranking['point'] = $ranking['point'] - $userInfo['weekKeepNum'];
    }

    // $previousRank;
    // $differenceRank;
    $day0num = 0;
    $day1num = 0;
    $day2num = 0;
    $day3num = 0;
    $day4num = 0;
    $day5num = 0;
    $day6num = 0;
    // $day0rank;
    // $day1rank;
    // $day2rank;
    // $day3rank;
    // $day4rank;
    // $day5rank;
    // $day6rank;
    // $previousDay;


    foreach($rankings as $ranking){
      if($ranking['user'] == "あなた"){
        $previousRank = $ranking['rank'];
        $previousDay = $ranking['point'];

      }else if($ranking['point'] == 0){
        $day0num++;
        $day0rank = $ranking['rank'];

      }else if($ranking['point'] == 1){
        $day1num++;
        $day1rank = $ranking['rank'];

      }else if($ranking['point'] == 2){
        $day2num++;
        $day2rank = $ranking['rank'];
      }else if($ranking['point'] == 3){
        $day3num++;
        $day3rank = $ranking['rank'];
      }else if($ranking['point'] == 4){
        $day4num++;
        $day4rank = $ranking['rank'];
      }else if($ranking['point'] == 5){
        $day5num++;
        $day5rank = $ranking['rank'];
      }else if($ranking['point'] == 6){
        $day6num++;
        $day6rank = $ranking['rank'];
      }
    }

    if($userRank == 1){
      //dayが4日のデータをもとにランキングを変更
      $differenceRank = $userRank - $previousRank;
      // KeepDayが6日だった人を1人選ぶ
      while($day6num > 0){
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day6rank){
            $ranking['point'] = 6;
            $day6num--;
          }
        }
        if($day6num == 0){
          break;
        }
      }
      if($day2num > 1){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 3;
            $day3num++;
            $day2num--;
          }
        }
      }
      if($day3num > 1){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 4;
            $day4num++;
            $day3num--;
          }
        }
      }
      if($day1num > 1){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 2;
            $day2num++;
            $day1num--;
          }
        }
      }
      if($day0num > 1){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day0rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 1;
            $day1num++;
            $day0num--;
          }
        }
      }
    }else if($userRank == 2){
      if($day6num == 1){
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day6rank){
            $ranking['point'] = 6;
            $day6num--;
          }
        }
      }
      foreach($rankings as &$ranking){
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day5rank){
          $ranking['point'] = 5;
        }
      }
          
      if($day1num > 1){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 1;
            $day1num--;
          }
        }
      }
      if($day2num > 1){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 3;
            $day3num++;
            $day2num--;
          }
        }
      }
      if($day3num > 1){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 4;
            $day4num++;
            $day3num--;
          }
        }
      }
      if($day0num > 1){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day0rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 1;
            $day1num++;
            $day0num--;
          }
        }
      }
    }else if($userRank == 3){
      $day7num = 0;
      if($day6num == 1){
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day6rank){
            $ranking['point'] = 7;
            $day7num++;
            $day6num--;
          }
        }
      }
      if($day5num == 1){
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day5rank){
            $ranking['point'] = 6;
            $day6num++;
            $day5num--;
          }
        }
      }
      if($userInfo["keepDays"] == 5){
        
        if($day4num == 1){
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
              $ranking['point'] = 4;
            }
          }
        }

        if($day3num > 1 && $day4num == 1){
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 4;
              $day4num++;
              $day3num--;
            }
          }
        }
        

      }else{
        if($previousDay != 4){
          if($day4num == 1){
            foreach($rankings as &$ranking){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
                $ranking['point'] = 5;
                $day5num++;
                $day4num--;
              }
            }
          }
        }
        if($day3num == 1){
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
              $ranking['point'] = 3;
            }
          }
        }

      }
      //共通で位を挙げていいものの順位を上げる
      if($day0num > 1){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day0rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 1;
            $day1num++;
            $day0num--;
          }
        }
      }
      if($day1num > 1){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day1rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 1;
            $day1num--;
          }
        }
      }
      if($day2num > 1){
        $chooseUser = array();
        $selectedUser = array();
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day2rank){
            array_push($chooseUser, $ranking['user']);
          }
        }
        $selectedUser = array_rand($chooseUser, 1);
        $userName = $chooseUser[$selectedUser];
        foreach($rankings as &$ranking){
          if($ranking['user'] == $userName){
            $ranking['point'] = 3;
            $day3num++;
            $day2num--;
          }
        }
      }

    }else if($userRank == 4){
      if($day6num == 1){
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day6rank){
            $ranking['point'] = 7;
            $day6num--;
          }
        }
      }
      foreach($rankings as &$ranking){
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day5rank){
          $ranking['point'] = 6;
        }
      }
      if($userInfo["keepDays"] == 3){
        if($previousDay == 3){
          foreach($rankings as &$ranking){
            if($day3num == 1){
              if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
                $ranking['point'] = 4;
                $day3num--;
                $day4num++;
              }
            }else if($day3num > 1){
              $chooseUser = array();
              $selectedUser = array();
              foreach($rankings as &$ranking){
                if($ranking['user'] != 'あなた' && $ranking['rank'] == $day3rank){
                  array_push($chooseUser, $ranking['user']);
                }
              }
              $selectedUser = array_rand($chooseUser, 1);
              $userName = $chooseUser[$selectedUser];
              foreach($rankings as &$ranking){
                if($ranking['user'] == $userName){
                  $ranking['point'] = 4;
                  $day3num--;
                  $day4num++;
                }
              }
            }
            
          }
        }
        if($day0num > 1){
          $chooseUser = array();
          $selectedUser = array();
          foreach($rankings as &$ranking){
            if($ranking['user'] != 'あなた' && $ranking['rank'] == $day0rank){
              array_push($chooseUser, $ranking['user']);
            }
          }
          $selectedUser = array_rand($chooseUser, 1);
          $userName = $chooseUser[$selectedUser];
          foreach($rankings as &$ranking){
            if($ranking['user'] == $userName){
              $ranking['point'] = 1;
              $day1num++;
              $day0num--;
            }
          }
        }
        
      }


    }else if($userRank == 5){
      if($day6num == 1){
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day6rank){
            $ranking['point'] = 7;
            $day6num--;
          }
        }
      }
      foreach($rankings as &$ranking){
        if($ranking['user'] != 'あなた' && $ranking['rank'] == $day5rank){
          $ranking['point'] = 6;
          $day6num++;
          $day5num--;
        }
      }
      if($day4num == 1){
        foreach($rankings as &$ranking){
          if($ranking['user'] != 'あなた' && $ranking['rank'] == $day4rank){
            $ranking['point'] = 5;
            $day5num++;
            $day4num--;
          }
        }
      }

    }

    foreach($rankings as &$ranking){
      if($ranking['user'] == 'あなた'){
        $ranking['point'] = $userInfo['keepDays'];
      }
    }




    // ポイントで降順に配列を並び替える
    usort($rankings, function($a, $b) {
      return $b['point'] - $a['point'];
    });

    // ランクを更新する
    $current_rank = 1;
    $count = 1;
    $current_point = $rankings[0]['point'];
    foreach ($rankings as &$item) {
      if ($item['point'] < $current_point){
        $current_point = $item['point'];
        $current_rank = $count;
      }
      $item['rank'] = $current_rank;
      $count++;
    }


  }
  error_log(print_r("22" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

  foreach($rankings as &$ranking){
    $ranking['point'] = $ranking['point'] + $userInfo['weekKeepNum'];
  }
  error_log(print_r($rankings , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

  
  return $rankings;
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

function lastWeek($userRank) {
  $userRanks = [];
  $userRanks = $userRank . "位";

  $flexMessage1 = [
    "type" => "flex",
    "altText" => "this is a flex message",
    "contents" => [
      "type" => "bubble",
      "body" => [
          "type" => "box",
          "layout" => "vertical",
          "contents" => [
            [
              "type" => "text",
              "text" => "おめでとうございます！ 最終順位は",
              "wrap" => true,
              "color" => "#000000",
              "size" => "md"
            ],
            [
              "type" => "text",
              "text" => $userRanks,
              "wrap" => true,
              "color" => "#FF0000",
              "size" => "3xl",
              "weight" => "bold",
              "align" => "center"
            ],
            [
              "type" => "text",
              "text" => "です！",
              "wrap" => false,
              "color" => "#000000",
              "size" => "md",
              "align" => "end"
            ],
          ]
      ]
    ]
  ];

  return $flexMessage1;
}

function makeUser($userDay){
  for($i = 0; $i < 9; $i++){
    $userPoints["ユーザ" . ($i + 1)] = $userDay;
  }
  foreach ($userPoints as $user => $points) {
    $rankings[] = [
      'user' => $user,
      'point' => $points,
    ];
  }

  $flexMessage = [
    "type" => "flex",
    "altText" => "this is a flex message",
    "contents" => [
      "type" => "bubble",
      "body" => [
          "type" => "box",
          "layout" => "vertical",
          "contents" => [
              [
                  "type" => "box",
                  "layout" => "baseline",
                  "contents" => [
                      [
                          "type" => "text",
                          "text" => "次週のメンバー",
                          "weight" => "bold",
                          "size" => "xl",
                          "margin" => "md",
                          "decoration" => "none",
                          "position" => "relative",
                          "align" => "center",
                          "color" => "#CC6600"
                      ],
                    ]
              ],
              [
                  "type" => "box",
                  "layout" => "vertical",
                  "margin" => "lg",
                  "spacing" => "sm",
                  "contents" => [
                      [
                          "type" => "separator",
                          "color" => "#CCCCCC"
                      ]
                    ]
                      ],
                    ],
              ]
      ]
  ];

  foreach ($rankings as $ranking) {
    $backgroundColor = ($ranking["user"] === "あなた") ? "#F3FFD8" : "#FFFFFF";
    $flexMessage["contents"]["body"]["contents"][1]["contents"][] = [
      
        "type" => "box",
        "layout" => "baseline",
        "backgroundColor" => $backgroundColor, // 背景色を動的に設定
        "contents" => [
            [
                "type" => "icon",
                "url" => "https://scdn.line-apps.com/n/channel_devcenter/img/fx/review_gray_star_28.png",
                "size" => "sm"
            ],
            [
                "type" => "text",
                "text" => $ranking["user"] . "　継続" . $userDay . "日",
                "wrap" => true,
                "size" => "lg",
                "color" => "#111111"
            ]
        ]
    ];
    $flexMessage["contents"]["body"]["contents"][1]["contents"][] = [
      "type" => "separator",
      "color" => "#CCCCCC"
    ];
  }
  return $flexMessage;
}
// コメントアウト
// function makeUser($userDay){
main();

?>