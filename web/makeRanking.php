<?php
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

?>