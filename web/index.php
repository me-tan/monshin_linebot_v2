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
require('./make_group_point.php');
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
  $goal_achieved = false;
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
    $messages1 = [];
    $messages2 = [];
    $flexMessage1 = [];
    $flexMessage = [];
    $count = 0;
    
    $sender = "user";

    // //ユーザのタイプを取得
    // $item = "user_type";

    // $targetNum = "mnsn_sheet_linebot_test";

    // $userType = getOneMysql($targetNum, $item, $userId)['user_type'];

    // if(!empty($userType)){
    //   $sender = $userType;
    // }


    // //ユーザタイプをデータベースに保存
    // if($text == "1番です！"){
    //   $item = "user_type";
    //   updateOneMysql($sender,$targetNum, $item, $userId);
    // }else if("2番です！"){
    //   $item = "user_type";
    //   updateOneMysql($sender,$targetNum, $item, $userId);
    // }else if("3番です！"){
    //   $item = "user_type";
    //   updateOneMysql($sender,$targetNum, $item, $userId);
    // }else if("4番です！"){
    //   $item = "user_type";
    //   updateOneMysql($sender,$targetNum, $item, $userId);
    // }
    
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
      
    }else if($text == '一番目を選択します！' || $text == '二番目を選択します！' || $text == '三番目を選択します！' || $text == '四番目を選択します！'){
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
      $text = "目標を「" . $ary_from_gpt[$targetNumber] . "」に設定しました！";
      $logMessage1 = $text;
      $messages1 . array_push($messages1, ["type" => "text", "text" => $text]); // 適当にオウム返し
      $situation1 = "set_target";

      $text = "次にあなたのユーザ名を設定します．8文字以内で入力してください\n※他者に共有されてもよい名前でお願いします．";
      $logMessage2 = $text;
      $messages2 . array_push($messages2, ["type" => "text", "text" => $text]); // 適当にオウム返し
      $situation2 = "requirement_of_userName";
      $count = 7;

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
      
      // //データベースからランキングを取得
      // $situation = "flexMessage_of_ranking";
      // $item = "messages";
      // $serialize_flexMessage = getOtherTargetMysql($situation, $item, $userId)[0]['messages'];
      // $flexMessage = unserialize($serialize_flexMessage);
      // $count = 2;
      // $situation1 = "show_ranking_for_check_message";
      // $situation2 = "show_ranking_for_check_flexMessage";


      //ログから改善項目を習得
      $situation = "choose_improvement_item";
      $targetNum = getTargetMysql($situation, $userId)[0]['contents'];

      //継続日数を取得
      $item = "keep_days";
      $keep_days = getOneMysql($targetNum, $item, $userId)['keep_days'];

      //グループポイントを取得
      $item = "group_point";
      $group_point = getOneMysql($targetNum, $item, $userId)['group_point'];

      //グループランキングを取得
      $item = "group_ranking";
      $group_ranking = getOneMysql($targetNum, $item, $userId)['group_ranking'];

      //日数を取得
      $item = "day_num";
      $dayNum = getOneMysql($targetNum, $item, $userId)['day_num'];

      // $frexMessage = rankingMake($dayNum, $keep_days, $group_point, $group_ranking, $targetNum);
      
      //ユーザの名前を取得
      $item = "userName";
      $userName = getOneMysql($targetNum, $item, $userId)['userName'];
      




    }else if($text == 'その他ツールが見たい！') {
      //-----------------------------------その他ツールの出力-----------------------------------------
      //メッセージのログを残す
      $situation = "requirement_of_show_tool";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //ログから改善項目を習得
      $situation = "choose_improvement_item";
      $targetNum = getTargetMysql($situation, $userId)[0]['contents'];
      
      //ユーザの名前を取得
      $item = "userName";
      $userName = getOneMysql($targetNum, $item, $userId)['userName'];

      //通知時間を取得
      $item = "notification_time";
      $notificationTime = getOneMysql($targetNum, $item, $userId)['notification_time'];

      //チャットボットの性格を取得
      $item = "chat_personality";
      $chatPersonality = getOneMysql($targetNum, $item, $userId)['chat_personality'];

      //チャットボットの性別を取得
      $item = "chat_gender";
      $chatGender = getOneMysql($targetNum, $item, $userId)['chat_gender'];

      //方言を取得
      $item = "chat_dialect";
      $chatDialect = getOneMysql($targetNum, $item, $userId)['chat_dialect'];

      //目標を取得
      $item = "target";
      $target = getOneMysql($targetNum, $item, $userId)['target'];
      
      // $userName = "みきてぃ";
      // $notificationTime = "20:00";
      // $target = "毎日少なくとも2時間は運動を行い，水分も2リットル以上摂る";

      $flexMessage1 = check_set_data($userName, $notificationTime, $target, $chatPersonality, $chatGender, $chatDialect);
      $situation1 = "show_set_data_flexMessage";


      $text = "その他のツールを表示します";
      $logMessage = $text;
      $messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
      $flexMessage2 = tool();
      $count = 2;
      $situation2 = "show_tool_message";
      $situation3 = "show_tool_flexMessage";
      $count = 8;

    }elseif($text == "現在の目標と継続日数を確認したい！"){
      //-----------------------------------目標と継続日数の出力-----------------------------------------
      //メッセージのログを残す
      $situation = "requirement_of_check_target_and_keep_days";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //ログから改善項目を習得
      $situation = "choose_improvement_item";
      $targetNum = getTargetMysql($situation, $userId)[0]['contents'];
      
      //目標を取得
      $item = "target";
      $target = getOneMysql($targetNum, $item, $userId)['target'];

      //継続日数を取得
      $item = "keep_days";
      $keepDays = getOneMysql($targetNum, $item, $userId)['keep_days'];

      //実施日数を取得
      $item = "day_num";
      $dayNum = getOneMysql($targetNum, $item, $userId)['day_num'];

      //フレックスメッセージの表示
      $text = "現在の目標と継続日数を確認します";
      $logMessage = $text;
      $messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
      $flexMessage = check_target_and_keep_days($dayNum, $keepDays, $target);
      $count = 2;
      $situation1 = "show_target_and_keep_days_message";
      $situation2 = "show_target_and_keep_days_flexMessage";

    }else if ($text == 'できました！'){
      $goal_achieved = true; 
      //-------------------------継続確認の重複の有無---------------------------------
      // //入力日時の比較
      // $situation = "report_of_what_was_done";
      // $item = "created_time";
      // $created_time = getOtherTargetMysql($situation, $item, $userId)[0]['created_time'];

      // $situation2 = "report_of_what_could_not_be_done";
      // $created_time2 = getOtherTargetMysql($situation2, $item, $userId)[0]['created_time'];

      // $currentDatetime = date('Y-m-d H:i:s');


      // if (date('Y-m-d', strtotime($created_time)) === date('Y-m-d', strtotime($currentDatetime)) || date('Y-m-d', strtotime($created_time2)) === date('Y-m-d', strtotime($currentDatetime))) {
          // 同じ日の場合の処理
          // $text = "申し訳ありません．同じ日に何度も継続確認は行えません．明日になってから継続できたか教えてください(^_^)/";
          // $messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
          // $situation = "message_of_can't_check_keeping";
          // $logMessage = $text;

      // }else{
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

        // $message_log = 'weekNumを抽出しました';
        // error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        //何週間目の継続日数か抽出
        $item = "week_keep_num";
        $targetNum = $previousItem;
        $weekKeepNum = getOneMysql($targetNum, $item, $userId)['week_keep_num'];

        // $message_log = 'weekKeepNumを抽出しました';
        // error_log(print_r($message_log , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        //もし週間目だったら日数と継続日数をデータベースに保存
        if($dayNum % 7 == 0){
          $item = "week_num";
          updateOneMysql($dayNum,$targetNum, $item, $userId);
          // error_log(print_r("1" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


          $item = "week_keep_num";
          updateOneMysql($keepDay,$targetNum, $item, $userId);
          // error_log(print_r("2" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        } 

        //------------------------------------継続できた場合の言葉の生成-----------------------------------------

        //プロンプトの作成に必要な要素の取得
        $targetNum = $previousItem;
        // データ取得用の関数
        function getChatInfo($item, $targetNum, $userId) {
          return getOneMysql($targetNum, $item, $userId)[$item];
        }

        // 取得処理のまとめ
        $personality = getChatInfo('chat_personality', $previousItem, $userId);
        $gender = getChatInfo('chat_gender', $previousItem, $userId);
        $userName = getChatInfo('userName', $previousItem, $userId);
        $dialect = getChatInfo('chat_dialect', $previousItem, $userId);
        $target = getChatInfo('target', $previousItem, $userId);

        error_log(print_r("必要な情報の取得" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        
        // //チャットボットの性格の取得
        // $item = "chat_personality";
        // $targetNum = $previousItem;
        // $personarlity = getOneMysql($targetNum, $item, $userId)['chat_personality'];

        // //チャットボットの性別の取得
        // $item = "chat_gender";
        // $targetNum = $previousItem;
        // $gender = getOneMysql($targetNum, $item, $userId)['chat_gender'];

        // //ユーザの名前の取得
        // $item = "userName";
        // $targetNum = $previousItem;
        // $userName = getOneMysql($targetNum, $item, $userId)['userName'];

        // //ユーザの方言の取得
        // $item = "chat_dialect";
        // $targetNum = $previousItem;
        // $dialect = getOneMysql($targetNum, $item, $userId)['chat_dialect'];

        //チャットボットの性格リストの取得
        if($personality == "外向的な性格"){
          $personality_list = "他人との会話を好む\n" .
             "他者とのかかわりによる刺激を求める\n" .
             "社交的で適応が早い\n" .
             "考えるより先に行動する\n" .
             "友人や知人が多い\n" .
             "自己主張が強い\n" .
             "饒舌である\n" .
             "エネルギーに満ちており，ノリがいい\n" .
             "他人の行動や考えに意識が行きやすい\n" .
             "他人のことを信頼しやすい\n";
        }else if($personality == "協調的な性格"){
          $personality_list = "サポートや協力を積極的に行う\n" .
             "他者の意見を尊重できる\n" .
             "明るくポジティブ思考である\n" .
             "チームワークを重視している\n" .
             "コミュニケーション能力が高い\n" .
             "洞察力に優れ，相手の欠点を見つける\n" .
             "傾聴力がある\n" .
             "感情の起伏が少なく笑顔で寛容である\n" .
             "相手の立場で考え，空気を読んだ行動ができる\n" .
             "思いやりがあり，親切である\n";
        }
        error_log(print_r($personality_list , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


        if($dayNum % 7 != 0){
          if($dayNum % 2 != 0){
            $message = "あなたは" . $personality . "で，私と同級生です．そして" . $gender . "で" . $dialect . "を話します．特徴は以下の通りです．\n" . $personality_list . "私は今，" . $target. "を目標に頑張ってて，" .$dayNum. "日の間で". $keepDay .  "日目標達成できたの！しかも，今日も達成できたし！．この頑張りを，褒めてほしい！そして，励ましても欲しい！長文読むのしんどいから100字以内で見やすく書いてほしい！";
            $text_gpt = call_chatGPT($message); // GPTにプロンプトを送信
          }else if($dayNum % 2 == 0 && $dayNum % 4 == 0){
            $message = "あなたは" . $personality . "で，私と同級生です．そして" . $gender . "で" . $dialect . "を話します．特徴は以下の通りです．\n" . $personality_list . "私は今，" . $target. "を目標に頑張ってて，" .$dayNum. "日の間で". $keepDay .  "日目標達成できたの！しかも，今日も達成できたし！．この頑張りに対して，アドバイスと褒める言葉が欲しい！長文は苦手だから120字以内で見やすく書いてほしい";
            $text_gpt = call_chatGPT($message); // GPTにプロンプトを送信
          }else {
            $message = "あなたは" . $personality . "で，私と同級生です．そして" . $gender . "で" . $dialect . "を話します．特徴は以下の通りです．\n" . $personality_list . "私は今，" . $target. "を目標に頑張ってて，" .$dayNum. "日の間で". $keepDay .  "日目標達成できたの！しかも，今日も達成できたし！．この頑張りを，具体的な体験談をもとに共感してほしい！あと，褒める言葉も！長文は苦手だから120字以内で見やすく書いてほしい";
            $text_gpt = call_chatGPT($message); // GPTにプロンプトを送信
          }
          error_log(print_r($text_gpt , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
          // $message = "あなたは継続管理を行う褒め上手な人です．ユーザは現在，生活習慣病の改善維持に取り組んでいます．継続が" .$dayNum. "日の間で". $keepDay .  "日続いた人をほめてください．100字以内でお願いします．";
          // $text_gpt = call_chatGPT($message); // GPTにプロンプトを送信
          // error_log(print_r("3" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        }else{
          $message = "1週間お疲れ様です．次の週からは以下のメンバーと順位を競います．次も頑張っていきましょう！";
          $text_gpt = $message;
          // error_log(print_r("4" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        }

        //-----------------------------------継続できた場合のグループ情報の開示----------------------------------------
        //グループメンバーを決定
        $groupMember = array("みきてぃ", "わだにゃん", "イミグレーション" , "オッフェンバック");

        //グループポイントの抽出
        $item = "group_points";
        $groupPoint = getOneMysql($targetNum, $item, $userId)['group_points'];

        // 一週目か二週目かを判断
        if($dayNum > 7){
          $dayNum = $dayNum - $weekNum;
          $keepDay = $keepDay - $weekKeepNum;
        }

        $group_point = updateGroupPoint($goal_achieved, $groupPoint, $keepDay, $dayNum);

        //グループポイントの保存
        $item = "group_points";
        updateOneMysql($group_point,$targetNum, $item, $userId);



        // $groupPoint = 15;
        // $keepDay = 6;


        $flexMessage = group_data($groupMember, $keepDay, $group_point);

        //ランキングの生成
        //一週目か二週目かを判断
        // if($dayNum > 7){
        //   $dayNum = $dayNum - $weekNum;
        //   $keepDay = $keepDay - $weekKeepNum;
        // }


        // if($dayNum % 2 == 0 || $dayNum % 7 == 0){
        //   $userInfo = ["keepDays" => $keepDay, "dayNum" => $dayNum, "weekKeepNum" => $weekKeepNum];
        //   $resultRank = rankingMaker($userInfo, $userId);
        //   // error_log(print_r($dayNum , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
        //   // error_log(print_r($keepDay , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
        //   error_log(print_r("5" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        //   $allDay = $dayNum + $weekNum;
        //   //コンテンツへの全ユーザ順位の追加
        //   $contents = serialize($resultRank);
        //   $flexMessage2 = ranking($resultRank, $allDay);
        //   error_log(print_r("6" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


        //   // $userRank;
        //   if($dayNum % 7 == 0){
        //     foreach($resultRank as &$ranking){
        //       if($ranking['user'] == 'あなた'){
        //         $userRank = $ranking['rank'];
        //         $userDay = $ranking['point'];
        //         error_log(print_r("7" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
        //       }
        //     }

        //     $flexMessage1 = lastWeek($userRank);
        //     $flexMessage2 = makeUser($userDay);
        //     error_log(print_r("8" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
            
        //   }
        // }

        //ランキングの作成

          
        $logMessage = $text_gpt;
        $messages . array_push($messages, ["type" => "text", "text" => $text_gpt]); // 適当にオウム返し
        error_log(print_r("9" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');


        // if($dayNum % 2 == 0 ){
        //   $count = 5;
        //   $situation = "evaluation_and_words_of_praise";
        //   $situation1 = "flexMessage_of_group_data";
        //   $situation2 = "flexMessage_of_ranking";
        //   error_log(print_r("12" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        // }else if($dayNum % 7 == 0){
        //   $count = 4;
        //   $situation1 = "flexMessage_of_ranking";
        //   $situation2 = "words_of_next_praise";
        //   $situation3 = "flexMessage_of_lastWeek_rank";
        //   $situation4 = "flexMessage_of_nextUser";
        //   error_log(print_r("11" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        // }else{
          $count = 3;
          $situation1 = "flexMessage_of_group_data";
          $situation2 = "evaluation_and_words_of_praise";
          error_log(print_r("situationなどの保存を行う" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');

        // }
      // }

    }else if ($text == 'できませんでした'){
      $goal_achieved = false;

      //-----------------------------------------------------入力日時の比較-----------------------------------------------------
      $situation = "report_of_what_could_not_be_done";
      $item = "created_time";
      $created_time = getOtherTargetMysql($situation, $item, $userId)[0]['created_time'];

      $situation2 = "report_of_what_was_done";
      $created_time2 = getOtherTargetMysql($situation2, $item, $userId)[0]['created_time'];

      $currentDatetime = date('Y-m-d H:i:s');




      if (date('Y-m-d', strtotime($created_time)) === date('Y-m-d', strtotime($currentDatetime)) || date('Y-m-d', strtotime($created_time2)) === date('Y-m-d', strtotime($currentDatetime))) {
          // 同じ日の場合の処理
          $text = "申し訳ありません．同じ日に何度も継続確認は行えません．明日になってから継続できたか教えてください(^_^)/";
          $messages . array_push($messages, ["type" => "text", "text" => $text]); // 適当にオウム返し
          $situation = "message_of_can't_check_keeping";
          $logMessage = $text;

      }else{
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

    }else if($text == "自分で目標を決めたいです！"){
      $situation = "require_of_set_target";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      $message =  "目標を入力してください！";
      $logMessage = $message;
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 目標を入力してくださいと返す
      $situation = "requirement_of_target";

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
      //ログの保存
      $situation = "response_of_personality";
      $logMessage = $text;
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //--------------------------------------一番最初のメッセージであるかの確認------------------------------------------------
      $message = "性格を設定したい！";
      $judge_result = judgeFirstMessage($userId, $message);
      if($judge_result == true){
        $message = "次にチャットボットの方言を設定します．あなたが普段使う方言を選んでください";
        $situation2 = "first_response_of_personality";
        $logMessage2 = $message;
        $messages2 . array_push($messages2, ["type" => "text", "text" => $message]); // 適当にオウム返し

        $flexMessage = dialect();
        $situation3 = "flexMessage_of_choose_personality";

        $count = 6;
      }
      //--------------------------------------性格の設定------------------------------------------------

      //ログから改善項目を習得
      $situation = "choose_improvement_item";
      $targetNum = getTargetMysql($situation, $userId)[0]['contents'];

      //性格をデータベースに保存
      if($text == "協調的な性格がいい！"){
        $setWord = "協調的な性格";
      }else if($text == "外向的な性格がいい！"){
        $setWord = "外向的な性格";
      }
      error_log(print_r($setWord , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      error_log(print_r("性格をデータベースに保存" , true) . "\n", 3, dirname(__FILE__) . '/debug.log');
      $section = "chat_personality";
      updateOneMysql($setWord,$targetNum, $section, $userId);


      if($judge_result == true){
        //性格設定完了メッセージの作成
        $message = "性格を「". $setWord . "」に設定しました！";
        $logMessage1 = $message;
        $messages1 . array_push($messages1, ["type" => "text", "text" => $message]); // 適当にオウム返し
        $situation1 = "set_personality";
      }else{
        //性格設定完了メッセージの作成
        $message = "性格を「". $setWord . "」に設定しました！";
        $logMessage = $message;
        $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
        $situation = "set_personality";
      }
    }else if ($text == "男性がいい！" || $text == "女性がいい！"){
      //ログを保存
      $situation = "response_of_gender";
      $logMessage = $text;
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //--------------------------------------一番最初のメッセージであるかの確認------------------------------------------------
      $message = "性別を設定したい！";
      $judge_result = judgeFirstMessage($userId, $message);
      if($judge_result == true){

        $message = "以上でチャットボットの設定は終了です．次に通知時間の設定を行います．\n通知してほしい時間を例をもとにすべて半角で入力してください（例: 14:00）";
        $situation2 = "requirement_of_notification_time";
        $logMessage2 = $message;
        $messages2 . array_push($messages2, ["type" => "text", "text" => $message]); // 適当にオウム返し

        $count = 7;
      } 
      
      //--------------------------------------性別の設定------------------------------------------------

      //性別をデータベースに保存
      if($text == "男性がいい！"){
        $setWord = "男性";
      }else if($text == "女性がいい！"){
        $setWord = "女性";
      }
      //ログから改善項目を習得
      $situation = "choose_improvement_item";
      $targetNum = getTargetMysql($situation, $userId)[0]['contents'];

      $section = "chat_gender";
      updateOneMysql($setWord,$targetNum, $section, $userId);

      if($judge_result == true){
        //性別設定完了メッセージの作成
        $message = "性別を「". $setWord . "」に設定しました！";
        $logMessage1 = $message;
        $messages1 . array_push($messages1, ["type" => "text", "text" => $message]); // 適当にオウム返し
        $situation1 = "set_gender";
      }else{
        //性別設定完了メッセージの作成
        $message = "性別を「". $setWord . "」に設定しました！";
        $logMessage = $message;
        $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
        $situation = "set_gender";
      }

    }else if ($text == "標準語がいい！" || $text == "東北弁がいい！" || $text == "関西弁がいい！" || $text == "広島弁がいい！" || $text == "博多弁がいい！" || $text == "沖縄弁がいい！" || $text == "鹿児島弁がいい！"){
      //--------------------------------------方言の設定------------------------------------------------
      $situation = "response_of_dialect";
      $logMessage = $text;
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      //--------------------------------------一番最初のメッセージであるかの確認------------------------------------------------
      $message = "方言を設定したい！";
      $judge_result = judgeFirstMessage($userId, $message);
      if($judge_result == true){
        $message = "次にチャットボットの性別を設定します．チャットボットの性別を選んでください";
        $situation2 = "first_response_of_dialect";
        $logMessage2 = $message;
        $messages2 . array_push($messages2, ["type" => "text", "text" => $message]); // 適当にオウム返し

        $flexMessage = gender();
        $situation3 = "flexMessage_of_choose_gender";

        $count = 6;
      } 

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

      if($judge_result == true){
        //方言設定完了メッセージの作成
        $message = "方言を「". $setWord . "」に設定しました！";
        $logMessage1 = $message;
        $messages1 . array_push($messages1, ["type" => "text", "text" => $message]); // 適当にオウム返し
        $situation1 = "set_dialect";
      }else{
        //方言設定完了メッセージの作成
        $message = "方言を「". $setWord . "」に設定しました！";
        $logMessage = $message;
        $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
        $situation = "set_dialect";
      }

    }else if($text == "グループのデータを知りたい！"){
      //--------------------------------------グループのデータの取得------------------------------------------------
      //メッセージのログを残す
      $situation = "requirement_of_check_of_group_data";
      putMessageLogMysql($sender, $text, $situation, $contents, $userId);

      $message =  "以下が現在のグループデータになります！";
      $logMessage = $message;
      $messages . array_push($messages, ["type" => "text", "text" => $message]); // 適当にオウム返し
      $situation1 = "requirement_of_personality";

      //グループメンバーを決定
      $groupMember = array("みきてぃ", "わだにゃん", "イミグレーション" , "オッフェンバック");

      //データベースから情報を取る際に必要な情報を取得
      $situation = "choose_improvement_item";
      $item = "contents";
      $targetNum = getOtherTargetMysql($situation, $item, $userId)[0]['contents'];

      //継続日数の抽出
      $item = "keep_days";
      $keepDay = getOneMysql($targetNum, $item, $userId)['keep_days'];

      //グループポイントの抽出
      $item = "group_points";
      $groupPoint = getOneMysql($targetNum, $item, $userId)['group_points'];

      // $groupPoint = 15;
      // $keepDay = 6;


      $flexMessage = group_data($groupMember, $keepDay, $groupPoint);
      $situation2 = "flexMessage_of_choose_personality";
      $count = 2;
      
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
          $logMessage1 = $message;
          $messages1 . array_push($messages1, ["type" => "text", "text" => $message]); // ユーザ名の変更を通知
          $situation1 = "set_userName";

          $text = "次にこのチャットボットに関する設定を行います．チャットボットの性格を選択してください．";
          $logMessage2 = $text;
          $messages2 . array_push($messages2, ["type" => "text", "text" => $text]); // 適当にオウム返し
          $situation2 = "choose_chat_personality";

          $flexMessage = personality();
          $situation3 = "flexMessage_of_choose_personality";

          $count = 6;


        }else if($situation_log == "requirement_of_notification_time"){
          //--------------------------------------一番最初の通知時間の決定か確認------------------------------------------------
          $message = "通知時間を設定したい！";
          $judge_result = judgeFirstMessage($userId, $message);
          if($judge_result == true){

            //ログから改善項目を習得
            $situation = "choose_improvement_item";
            $targetNum = getTargetMysql($situation, $userId)[0]['contents'];
            //目標を取得
            $item = "target";
            $target = getOneMysql($targetNum, $item, $userId)['target'];

            $message = "これですべての設定は終了です．\nなお，チャットボットや通知の設定はその他のツールから変更可能です．\nまた，明日から，以下のメンバーとチームになり他のチームと総ポイント数で順位を競い合います．\nポイントは継続日数に応じて付与されます．\nこれから，一緒に頑張っていきましょう！！";
            $situation2 = "requirement_of_notification_time";
            $logMessage2 = $message;
            $messages2 . array_push($messages2, ["type" => "text", "text" => $message]); // 適当にオウム返し

            //継続日数の抽出
            $item = "keep_days";
            $keepDay = getOneMysql($targetNum, $item, $userId)['keep_days'];

            //グループポイントの抽出
            $item = "group_points";
            $groupPoint = getOneMysql($targetNum, $item, $userId)['group_points'];

            $userName = "userName";
            $userName = getOneMysql($targetNum, $userName, $userId)['userName'];

            // $groupPoint = 15;
            // $keepDay = 6;
            $gpt_message = "あなたはあだ名生成ボットです．現在，4人組のチームを考えています，一人の名前は". $userName . "です．余計なことは書かず，あと三人のあだ名のみを箇条書きで書いてください";
            $text_gpt = call_chatGPT($gpt_message); // GPTにプロンプトを送信
            $groupMember = explode("\n", $text_gpt);

            //自分の名前を先頭に追加
            array_unshift($groupMember, $userName);

            //データベースに保存できる形に変更
            $serialize_gpt = serialize($groupMember);
            $contents = $serialize_gpt;

            //メンバーをデータベースに保存
            $setWord = $serialize_gpt;
            $section = "group_members";
            updateOneMysql($setWord,$targetNum, $section, $userId);

            $flexMessage = group_data($groupMember, $keepDay, $groupPoint);
            $situation3 = "flexMessage_of_choose_personality";

            $count = 6;
          } 
          //--------------------------------------通知時間の決定------------------------------------------------
          $setWord = $text;
          $section = "notification_time";
          updateOneMysql($setWord,$targetNum, $section, $userId);

          if($judge_result == true){
            //通知時間設定完了メッセージの作成
            $message = "通知時間を「". $text . "」に設定しました！";
            $logMessage1 = $message;
            $messages1 . array_push($messages1, ["type" => "text", "text" => $message]); // 適当にオウム返し
            $situation1 = "set_notification_time";
          }else{
            $message = "通知時間を「". $text . "」に設定しました！";
            $logMessage = $message;
            $messages . array_push($messages, ["type" => "text", "text" => $message]); // ユーザ名の変更を通知
            $situation = "set_notification_time";
          }
        
        }else if($situation_log == "requirement_of_target"){
          //--------------------------------------目標の決定------------------------------------------------
          if (preg_match('/\d|[\x{FF10}-\x{FF19}]/u', $text)) {
            $setWord = $text;
            $section = "target";
            updateOneMysql($setWord,$targetNum, $section, $userId);

            //目標をデータベースに保存
            $message = "目標を「". $text . "」に設定しました！";
            $logMessage1 = $message;
            $messages1 . array_push($messages1, ["type" => "text", "text" => $message]); // ユーザ名の変更を通知
            $situation1 = "set_target";

            $text = "次にあなたのユーザ名を設定します．8文字以内で入力してください\n※他者に共有されてもよい名前でお願いします．";
            $logMessage2 = $text;
            $messages2 . array_push($messages2, ["type" => "text", "text" => $text]); // 適当にオウム返し
            $situation2 = "requirement_of_userName";
            $count = 7;

          }else{
            $message = "目標は数値目標を入力してください！";
            $logMessage = $message;
            $messages . array_push($messages, ["type" => "text", "text" => $message]); // ユーザ名の変更を通知
            $situation = "requirement_of_target";
          }

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
      //フレックスメッセージとメッセージの送信
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

    }else if($count == 5){
      //フレックスメッセージ・フレックスメッセージ・メッセージを送信
      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage1],
      ]);
      $serialize_flexMessage = serialize($flexMessage1);
      putMessageLogMysql($sender, $serialize_flexMessage, $situation1, $contents, $userId);

      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage2],
      ]);
      $serialize_flexMessage2 = serialize($flexMessage2);
      putMessageLogMysql($sender, $serialize_flexMessage2, $situation2, $contents, $userId);

      sendMessage([
        "replyToken" => $replyToken,
        "messages" => $messages
      ]);
      putMessageLogMysql($sender, $logMessage, $situation, $contents, $userId);

    }else if($count == 6){
      // メッセージ・メッセージ・フレックスメッセージを送信
      sendMessage([
        "replyToken" => $replyToken,
        "messages" => array_merge($messages1, $messages2)
      ]);

      putMessageLogMysql($sender, $logMessage1, $situation1, $contents, $userId);
      putMessageLogMysql($sender, $logMessage2, $situation2, $contents, $userId);

      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage],
      ]);
      $serialize_flexMessage = serialize($flexMessage);
      putMessageLogMysql($sender, $serialize_flexMessage, $situation3, $contents, $userId);

    }else if($count == 7){
      // メッセージ・メッセージを送信
      sendMessage([
        "replyToken" => $replyToken,
        "messages" => array_merge($messages1, $messages2)
      ]);

      putMessageLogMysql($sender, $logMessage1, $situation1, $contents, $userId);
      putMessageLogMysql($sender, $logMessage2, $situation2, $contents, $userId);

    }else if($count == 8){
      //フレックスメッセージ・メッセージ・フレックスメッセージを送信
      sendFlexMessage([
        "to" => $userId, //user id
        "messages" => [$flexMessage1],
      ]);
      $serialize_flexMessage = serialize($flexMessage1);
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
      putMessageLogMysql($sender, $serialize_flexMessage, $situation3, $contents, $userId);

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