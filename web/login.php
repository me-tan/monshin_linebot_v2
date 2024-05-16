<?php
// PHPは基本的に上から順番に実行されるよ
//ini_set('display_errors',1); // これを書いとくとPHPがエラー吐いたときにきちんとそれを表示してくれる
//ini_set('error_reporting', E_ALL);
ini_set('session.gc_maxlifetime', 24 * 60 * 60); // sessionの有効時間を設定

session_start();
// var_dump(phpinfo()); // セッションファイルの場所を確認するときにも使える


require_once dirname(__FILE__) . "/dbFunctions.php";

require_once(dirname(__FILE__) . "/vendor/autoload.php"); //ライブラリの読み込み
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__); //.envを読み込む
$dotenv->load();


$file_job = './json/job.json'; // 仕事の日の一覧データ
$json_job = file_get_contents($file_job); //指定したファイルの要素をすべて取得する
$json_job = mb_convert_encoding($json_job, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN'); //jsonファイルの変換
$json_job = json_decode($json_job, true); // json形式のデータを連想配列の形式にする


// サニタイズ
function sanitaize()
{
	if (!empty($_POST)) {
		foreach ($_POST as $key => $value) {
			if (is_array($value)) {
				$clean[$key] = htmlspecialchars($value[0], ENT_QUOTES);
			} else {
				$clean[$key] = htmlspecialchars($value, ENT_QUOTES);
			}
		}
	}
}

sanitaize();





// 変数の初期化
$page_flag = 0;
// POST を受け取る変数を初期化
$institution = '';
$login_num = '';

$year = '';
$month = '';
$day = '';
$line_uid = '';





// セレクトボックスの値を格納する配列
$institutionsList = array(
	// "1" => "市医師会成人病センター",
	// "2" => "小池クリニック",
	// "3" => "和歌山県警",
	// "4" => "和歌山トヨタ",
	// "00" => "その他事業所",
	"1" => "和歌山大学",
	"2" => "その他"
);



// ログイン関連の処理を関数
function login() {
    $_SESSION = array(); // 戻ってきた時用にセッションを初期化
    // var_dump($_SESSION);
    // var_dump($_POST);
	if (!empty($_POST['institution']) && !empty($_POST['login_num']) && !empty($_POST['year']) && !empty($_POST['month']) && !empty($_POST['day']) && !empty($_POST['line_uid'])) {
        //空白文字列の削除
        $_POST['login_num'] = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['login_num']);

        // 全角アルファベット→半角アルファベットへ変換を実行
        $_POST['login_num'] = mb_convert_kana($_POST['login_num'], 'a');

        //ログイン認証用の文字列を作成（各要素を結合）
        $pass_encrypt = $_POST['institution'] . $_POST['login_num'] . $_POST['year'] . $_POST['month'] . $_POST['day'] . $_POST['line_uid']; //全ての要素を結合
        $pass_encrypt = hash('sha256', $pass_encrypt); //結合した要素を暗号化

        // DBに格納されている中身を確認
        $id = certificateTable_Interview($pass_encrypt); //DBにIDが登録されているかを確認
        $gender = certificateTable_Interview_gender($pass_encrypt); //DBに必須項目が登録されているかを確認

        $pass_mode = 0; // パスワード入力の有無を格納する変数


        // ページ遷移後もデータを保持させる
        $_SESSION['id'] = $id;
        $_SESSION['institution'] = $_POST['institution'];
        $_SESSION['login_num'] = $_POST['login_num'];
        $_SESSION['year'] = $_POST['year'];
        $_SESSION['month'] = $_POST['month'];
        $_SESSION['day'] = $_POST['day'];
		$_SESSION['line_uid'] = $_POST['line_uid'];
        $birth =  $_POST['year'] . "年" . $_POST['month'] . "月" . $_POST['day'] . "日";
        $_SESSION['birth'] = $birth;
        $_SESSION['gender'] = $gender;
        $_SESSION['admin'] = "non_admin"; // 管理者のフラグ
        $_SESSION['pass_mode'] = $pass_mode; // パスワードの入力の有無を格納

        // var_dump("OK");
        header("Location: ./medical_questionnaire.php"); //確認ページに遷移する．
	}
};

// // 戻ってきた場合
// if (isset($_POST['institution'])) {
// 	$institution = $_POST['institution'];
// }
// if (isset($_POST['login_num'])) {
// 	$login_num = $_POST['login_num'];
// }


// var_dump($_POST);

// if ( !empty($_POST['institution']) ) {
login();
// }
?>


<!DOCTYPE html>
<html lang="ja" xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">


	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
	<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="./css/common.css" />
	<link rel="stylesheet" href="./css/main.css" />
	<!-- Font Awesome -->
	<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet" />
	<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
	<script>
		history.replaceState(null, null, null);
		window.addEventListener('popstate', function(e) {
			alert('戻るボタンを押さないで下さい \nログイン画面に戻ります');
		});
	</script>

</head>

<title>問診票</title>

<body>
	<div id="wrapper">

		<!-- header -->



		<div class="container-fluid">

			

			<section class="greeting">パスワードの入力 </section>


			<form action="" method="post" id="login_form">

				<!-- １つ目の説明文  -->
				<div class="form_parts_login" style="margin-top: 1em!important;">
					<span>
						在学中の大学を選択してください<br>
					</span>
				</div>

				<!-- １つ目の質問  -->
				<table class="login_table" align="center">
					<tbody>
						<tr>
							<th class="login_table_th">大学</th>
							<td class="login_table_td">
								<select name="institution" required>
									<option value="">-</option>

									<?php

									foreach ($institutionsList as $key => $value) {

										if ($value === $institution) {
											// ① POST データが存在する場合はこちらの分岐に入る
											echo "<option value='$value' selected>" . $key . "." . $value . "</option>";
										} else {
											// ② POST データが存在しない場合はこちらの分岐に入る
											echo "<option value='$value'>" . $key . "." . $value . "</option>";
										}
									}
									?>
								</select>


							</td>
						</tr>
					</tbody>
				</table>

				<!-- ２つ目の説明文 -->
				<div class="form_parts_login">
					<span>
						氏名をひらがなで入力してください<br>（姓と名の間に空白を入れないでください）</span>
				</div>




				<table class="login_table" align="center">
					<tbody>
						<tr>
							<th class="login_table_th">LINEのID</th>
							<td class="login_table_td">
									<input  type="text" name="line_uid" size="19em"  placeholder="例）123456"

									value = "" autocomplete="off"
									title=""/>
							</td>
						</tr>
						<!-- ２つ目の質問  健診番号-->
						<tr>
							<th class="login_table_th">氏名</th>
							<td class="login_table_td">
								<input type="text" name="login_num" size="19em" placeholder="例）123456" autocomplete="off" value="<?php echo $login_num; ?>" title="" />
							</td>
						</tr>

						<!-- ３つ目の質問  生年月日-->
						<tr>
							<th class="login_table_th">生年月日</th>
							<td class="login_table_td">
								<select name="year" required>
									<option value="">-</option>
									<?php
									for ($j = 1900; $j <= 2022; $j++) :
										// POSTで受け取った値があるとき
										if (isset($_POST['year'])) :
											if ($j == $_POST['year']) :
												$year = '<option value="' . $j . '" selected>' . $j . '</option>';
											else :
												$year = '<option value="' . $j . '">' . $j . '</option>';
											endif;

										// POSTで受け取った値がないとき
										else :
											$year = '<option value="' . $j . '">' . $j . '</option>';
										endif;
										echo $year;

									endfor;
									?>
								</select> 年

								<select name="month" required>
									<option value="">-</option>
									<?php
									for ($j = 1; $j <= 12; $j++) :
										// 0で埋める
										if ($j < 10) :
											$j = '0' . strval($j);
										endif;

										// POSTで受け取った値があるとき
										if (isset($_POST['month'])) :
											if ($j == $_POST['month']) :
												$month = '<option value="' . $j . '" selected>' . $j . '</option>';
											else :
												$month = '<option value="' . $j . '">' . $j . '</option>';
											endif;

										// POSTで受け取った値がないとき
										else :
											$month = '<option value="' . $j . '">' . $j . '</option>';
										endif;
										echo $month;

									endfor;
									?>
								</select> 月



								<select name="day" required>
									<option value="">-</option>
									<?php
									for ($j = 1; $j <= 31; $j++) :

										// 0で埋める
										if ($j < 10) :
											$j = '0' . strval($j);
										endif;

										// POSTで受け取った値があるとき
										if (isset($_POST['day'])) :
											if ($j == $_POST['day']) :
												$day = '<option value="' . $j . '" selected>' . $j . '</option>';
											else :
												$day = '<option value="' . $j . '">' . $j . '</option>';
											endif;

										// POSTで受け取った値がないとき
										else :
											$day = '<option value="' . $j . '">' . $j . '</option>';
										endif;
										echo $day;

									endfor;
									?>
								</select> 日
							</td>
						</tr>
					</tbody>
				</table>



				<div class=" form_parts"><input type="submit" name="submit_apply" value="問診票の回答に進む" class="button_design" style="margin: 1.5em " />
				</div>
			</form>



		</div> <!--container-fluid -->

	</div> <!--wrapper -->

	<style>
		.passwordmessage {
			font-size: 15px;
		}

		.passwordmessage p {
			text-align: center
		}
	</style>
</body>

</html>