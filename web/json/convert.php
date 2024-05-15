<?php

class Player {
    public $name;
    public $points;

    public function __construct($name) {
        $this->name = $name;
        $this->points = 0;
    }
}

function generateFakeScores() {
    // 0か1のランダムなポイントを生成
    return array_map(function() { return rand(0, 1); }, range(1, 7));
}

function adjustFakeScores($scores, $targetPlayerRank) {
    // 他のユーザーのポイントを調整して、目標プレイヤーが上位になるようにする
    while (max($scores) > $targetPlayerRank) {
        $indexToAdjust = array_search(max($scores), $scores);
        $scores[$indexToAdjust]--;
        $scores[rand(0, count($scores) - 1)]++;
    }
    return $scores;
}

function printDailyRanking($players) {
    // デイリーランキングを出力
    usort($players, function($a, $b) {
        return $b->points - $a->points;
    });

    foreach ($players as $player) {
        echo "{$player->name}: {$player->points} points\n";
    }
    echo "\n";
}

function simulateOneWeek() {
    // 一週間のシミュレーション
    $players = [new Player("A")];
    for ($i = 1; $i <= 9; $i++) {
        $players[] = new Player("B$i");
    }

    for ($day = 1; $day <= 7; $day++) {
        // Aさん以外のプレイヤーのポイントを生成
        foreach ($players as $key => $player) {
            if ($key == 0) continue; // Aさんはスキップ
            $player->points += rand(0, 1);
        }

        // Aさんのポイントを設定
        $aPoints = [1, 1, 0, 1, 1, 1, 1];
        $players[0]->points = array_sum(array_slice($aPoints, 0, $day));

        // Aさん以外のプレイヤーのポイントを調整
        $adjustedScores = adjustFakeScores(array_map(function($player) {
            return $player->points;
        }, array_slice($players, 1)), $players[0]->points);

        // 調整されたポイントをプレイヤーに反映
        foreach ($players as $key => $player) {
            if ($key == 0) continue; // Aさんはスキップ
            $player->points = $adjustedScores[$key - 1];
        }

        // デイリーランキングを出力
        echo "Day $day:\n";
        printDailyRanking($players);
    }
}
// function customRandom() {
//    // 0から5までのランダムな整数を生成
//    $randomNumber = rand(0, 7);

//    // 2が出る確率を3分の1に設定
//    if ($randomNumber < 3) {
//        return 2;
//    } else {
//        // 0または1が出る確率を2分の1に設定
//        return rand(0, 1);
//    }
// }

// // 0から2までの数字を8回出力
// for ($i = 0; $i < 8; $i++) {
//    $result = customRandom();
//    echo $result . " ";
// }

// ユーザーポイントの配列
$userPoints = [
    'ユーザ１' => 2,
    'ユーザ2' => 2,
    'ユーザ3' => 1,
    'ユーザ4' => 1,
    'ユーザ5' => 0,
    'ユーザ7' => 1,
    'ユーザ８' => 2,
];

// ポイントでユーザーを降順にソート
arsort($userPoints);

// 順位を生成
$rank = 1;
$prevPoints = null;
$rankings = [];

foreach ($userPoints as $user => $points) {
    if ($prevPoints !== null && $prevPoints != $points) {
        $rank++;
    }
    $rankings[$user] = $rank;
    $prevPoints = $points;
}

// 順位を表示
foreach ($rankings as $user => $rank) {
    echo "$user: 位 $rank\n";
}


//simulateOneWeek();

?>
