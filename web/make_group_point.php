<?php
function updateGroupPoint($goal_acheived, $group_point, $keep_days, $dayNum){
    if($goal_acheived == true){
        $addPoint = generateRandomNumber($group_point, $keep_days, $dayNum, $goal_acheived);
        $group_point += $addPoint + 1;
    }else {
        $addPoint = generateRandomNumber($group_point, $keep_days, $dayNum, $goal_acheived);
        $group_point += $addPoint;
    }
    return $group_point;
}

function generateRandomNumber($group_point, $keep_days, $dayNum, $goal_acheived) {
    if($dayNum % 7 == 0){
        $remaining_days = 0;
    }else{
        $remaining_days = 7 - ($dayNum % 7);
    }
    $max_point = ($keep_days < 4) ? 14 : 28;
    $min_point = ($keep_days < 4) ? 0 : 20;
    $max_point = ($keep_days < 2) ? 13 : $max_point;

    // 残りの日数で目標ポイントに収まるように調整
    $remaining_point = $max_point - $group_point;
    $max_add_point = min(3, max(0, $remaining_point - $remaining_days));

    // 乱数生成
    if($dayNum % 7 == 1){
        // 1日目の場合
        $get_point = rand(2, 3);

    // }else if($dayNum % 7 == 4 && $keep_days < 2 && $group_point > 7){
    //     // 4日目で継続が2日未満で7ポイント未満の場合はポイントが増えすぎないように調整
    //     $get_point = min(0, 1);

    }else if($dayNum % 7 == 0 && ($group_point == 15 || $group_point == 14) && $keep_days < 4 && $keep_days > 1 ){
        if($goal_acheived == false && $group_point == 14){
            if($keep_days <= 2){
                // 「14ポイントで7日目,継続が2日かつその日目標を達成していない場合」はポイントを1にする
                $get_point = 0;
            }else{
                $get_point = 1;
            }
        }else{
            // 「15ポイントで7日目,継続が4日未満の場合」と，「14ポイントで7日目,継続が4日未満かつその日目標を達成した場合」でポイントをそれ以上増やさない
            $get_point = 0;
        }
    }else if($dayNum % 7 == 6 && $group_point < 14 && $keep_days < 4 && $keep_days > 1){
        // 14ポイント未満で6日目,継続が4日未満の場合はポイントを14にする
        $get_point = min(3, $max_point - $group_point);

    }else if(($group_point < $min_point && $keep_days > 4)){
        // 20ポイント未満で4日以上継続している場合はポイントを20にするため最大数を付与
        $get_point = 3;
    }else if($dayNum == $keep_days){
        // 継続日数と同じ日にはポイントを付与
        $get_point = rand(2,3);
    }else{
        $get_point = rand(1, $max_add_point);
    }

    return $get_point;
}

// // テスト
// $group_point = 0;
// $goal_acheived = [true, false, true, true, true, true, false];
// $keep_days = 0;
// $dayNum = 1;


// for ($i = 1; $i <= 7; $i++) {
//     if($goal_acheived[$i-1] == true){
//         $keep_days++;
//     }
//     $group_point = updateGroupPoint($goal_acheived[$i-1], $group_point, $keep_days, $i);
//     echo "Day $i: Group Point = $group_point\n";
    
// }

?>