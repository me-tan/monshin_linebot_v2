<?php
#########################################################
// 小池サーバからデータを持ってくるのに使ったサンプルコード
#########################################################


ini_set('display_errors', 1); // PHPがエラーを吐いたら表示する
require_once dirname(__FILE__) . "/backend.php"; // バックエンドの読み込み


// 変数の初期化
$file_job = './json/job.json'; // 仕事の日の一覧データ
$json_job = file_get_contents($file_job); //指定したファイルの要素をすべて取得する
$json_job = mb_convert_encoding($json_job, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN'); //jsonファイルの変換
$json_job = json_decode($json_job, true); // json形式のデータを連想配列の形式にする
$institution = '';
$login_num = '';
$year = '';
$month = '';
$day = '';
$institutionsList = array("1" => "市医師会成人病センター", "2" => "小池クリニック", "3" => "和歌山県警", "4" => "和歌山トヨタ", "00" => "その他事業所",); // セレクトボックスの値を格納する配列


// 以下POSTに患者の番号が正しく入っていれば動作する
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!empty($_POST['institution']) && !empty($_POST['login_num']) && !empty($_POST['year']) && !empty($_POST['month']) && !empty($_POST['day'])) {
    try {
      $_POST['login_num'] = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['login_num']); //空白文字列の削除
      $_POST['login_num'] = mb_convert_kana($_POST['login_num'], 'a'); // 全角アルファベット→半角アルファベットへ変換を実行

      $pass_encrypt = $_POST['institution'] . $_POST['login_num'] . $_POST['year'] . $_POST['month'] . $_POST['day']; //ログイン認証用の文字列を作成（各要素を結合）
      $pass_encrypt = hash('sha256', $pass_encrypt); //結合した要素を暗号化

      // $_SESSION['id'] = $id;
      // $_SESSION['institution'] = $_POST['institution'];
      // $_SESSION['login_num'] = $_POST['login_num'];
      // $_SESSION['year'] = $_POST['year'];
      // $_SESSION['month'] = $_POST['month'];
      // $_SESSION['day'] = $_POST['day'];
      // $birth =  $_POST['year'] . "年" . $_POST['month'] . "月" . $_POST['day'] . "日";
      // $_SESSION['birth'] = $birth;
      // $_SESSION['gender'] = $gender;

      $mnsn = getMnsn($pass_encrypt); // DBから問診票を取得
      var_dump($mnsn);
    } catch (Exception $e) {
      $e = $e->getMessage(); //例外メッセージを取得する 
      $alert = "<script type='text/javascript'>alert('"  . $e . " ');</script>";
      echo $alert;
    }
  }
}


?>


<!-- ここからhtmlの共通部分 -->
<!DOCTYPE html>
<html lang="ja" xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>B3用システム</title>
</head>

<body>
  <div id="wrapper">
    <div class="container-fluid">

      <form action="" method="post" id="login_form">
        <table class="login_table">
          <tbody>
            <tr>
              <th class="login_table_th">健診受診機関<br>職場</th>
              <td class="login_table_td">
                <select name="institution" required>
                  <option value="">-</option>
                  <?php
                  foreach ($institutionsList as $key => $value) {
                    if ($value === $institution) { // ① POST データが存在する場合はこちらの分岐に入る
                      echo "<option value='$value' selected>" . $key . "." . $value . "</option>";
                    } else { // ② POST データが存在しない場合はこちらの分岐に入る
                      echo "<option value='$value'>" . $key . "." . $value . "</option>";
                    }
                  }
                  ?>
                </select>
              </td>
            </tr>
          </tbody>
        </table>

        <table class="login_table">
          <tbody>
            <tr>
              <th class="login_table_th">健診受診番号<br>職員番号</th>
              <td class="login_table_td">
                <input type="text" name="login_num" size="19em" placeholder="例）123456" autocomplete="off" value="<?php echo $login_num; ?>" title="" />
              </td>
            </tr>

            <tr>
              <th class="login_table_th">生年月日</th>
              <td class="login_table_td">
                <select name="year" required>
                  <option value="">-</option>
                  <?php
                  for ($j = 1900; $j <= 2022; $j++) {

                    if (isset($_POST['year'])) { // POSTで受け取った値があるとき
                      if ($j == $_POST['year']) {
                        $year = '<option value="' . $j . '" selected>' . $j . '</option>';
                      } else {
                        $year = '<option value="' . $j . '">' . $j . '</option>';
                      }
                    } else { // POSTで受け取った値がないとき
                      $year = '<option value="' . $j . '">' . $j . '</option>';
                    }

                    echo $year;
                  }
                  ?>
                </select> 年

                <select name="month" required>
                  <option value="">-</option>
                  <?php
                  for ($j = 1; $j <= 12; $j++) {
                    if ($j < 10) {
                      $j = '0' . strval($j); // 0で埋める
                    }

                    if (isset($_POST['month'])) { // POSTで受け取った値があるとき
                      if ($j == $_POST['month']) {
                        $month = '<option value="' . $j . '" selected>' . $j . '</option>';
                      } else {
                        $month = '<option value="' . $j . '">' . $j . '</option>';
                      }
                    } else { // POSTで受け取った値がないとき
                      $month = '<option value="' . $j . '">' . $j . '</option>';
                    }

                    echo $month;
                  }
                  ?>
                </select> 月



                <select name="day" required>
                  <option value="">-</option>
                  <?php
                  for ($j = 1; $j <= 31; $j++) {
                    if ($j < 10) {
                      $j = '0' . strval($j); // 0で埋める
                    }


                    if (isset($_POST['day'])) { // POSTで受け取った値があるとき
                      if ($j == $_POST['day']) {
                        $day = '<option value="' . $j . '" selected>' . $j . '</option>';
                      } else {
                        $day = '<option value="' . $j . '">' . $j . '</option>';
                      }
                    } else { // POSTで受け取った値がないとき
                      $day = '<option value="' . $j . '">' . $j . '</option>';
                    }

                    echo $day;
                  }
                  ?>
                </select> 日
              </td>
            </tr>

          </tbody>
        </table>


        <div class="form_parts"><input type="submit" name="submit_apply" value="問診票の表示" class="button_design" onclick="OnButtonClick();" /></div>
      </form>






    </div>
  </div>

</body>

</html>