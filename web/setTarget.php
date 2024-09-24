<?php
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

?>