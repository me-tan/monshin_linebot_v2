<?php
// PHPは基本的に上から順番に実行されるよ
// ini_set('display_errors',1); // これを書いとくとPHPがエラー吐いたときにきちんとそれを表示してくれる

ini_set('session.gc_maxlifetime', 24 * 60 * 60); // sessionの有効時間を設定



session_start();
require_once dirname(__FILE__) . "/dbFunctions.php";

// require_once dirname(__FILE__) . "/pdf_download.php";
$filename = basename(__FILE__);

$passed_Id = $_SESSION['id'];

// 変数の初期化
$page_flag = 0;
$update_val = 0;
$clean = array();

$q_num = 0; //設問の番号カウントに使う

$error_text = ""; //未入力・未選択切り替え用

$jobholi_names = array('仕事の日' => 'job_', '仕事のない日' => 'holiday_');

$checkbox_array = array();

$file_job = './json/job.json'; // 仕事の日の一覧データ

$json_job = file_get_contents($file_job); //指定したファイルの要素をすべて取得する
$json_job = mb_convert_encoding($json_job, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN'); //jsonファイルの変換
$json_job = json_decode($json_job, true); // json形式のデータを連想配列の形式にする


$list_quest = array(
	'お仕事について' => $json_job
);

//アンケートの回答を登録後、ブラウザバックをした場合、ホームに戻る
if (empty($_SESSION['id']) or empty($_SESSION['institution'])) {
	echo '<script>
			alert("戻るボタンを押さないで下さい \nパスワード入力画面に戻ります");
			location.href="./index.php";
		  </script>';
}





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

//$_POST[btn_confirm] を受け取った時
if (!empty($_POST['btn_confirm'])) {
	$page_flag = 1; //一回提出したら旗を1にする

} elseif (!empty($_POST['btn_submit'])) {
	//提出ボタンを押したとき、ページ遷移
	header("Location: ./summary.php");
	exit;
} elseif (!empty($_POST['btn_login'])) {
	//提出ボタンを押したとき、ページ遷移
	header("Location: ./login.php");
	exit;
}

#######################################################
#######################################################
require_once('./define.php'); // $db_columnsを呼ぶため
$flag_db_exisit = empty($_SESSION["gender"]); // 過去に記入済みか判定
$db_columns = return_db_columns();

// var_dump($_SESSION);

// writeLog($filename, $_SESSION['institution'], $_SESSION['login_num']);

?>



<!-- ここからhtmlの共通部分 -->
<!DOCTYPE html>
<html lang="ja" xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

	<script src="https://rawgit.com/kimmobrunfeldt/progressbar.js/master/dist/progressbar.js"></script><!-- プログレスバー -->
	<script src="https://rawgit.com/kimmobrunfeldt/progressbar.js/master/dist/progressbar.min.js"></script><!-- プログレスバー -->

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
	<!-- Font Awesome -->
	<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
	<link rel="stylesheet" href="./css/common.css" />
	<link rel="stylesheet" href="./css/main.css" />


	<script src="./js/validation.js"></script>

</head>

<title>定期健康診断問診票</title>


<body>
	<div id="wrapper">

		<div class="container-fluid">

			<!-- header -->
			<header>
				<div id="pc-header" class="hidden-sp">
					<div class="header-inner flbox">
						<img src="assets/logo_koike_3.png" alt="和歌山 内科 小池クリニック KC検診システム">
						<h1 class="headline">和歌山 内科｜小池クリニック(和歌山市)</h1>
					</div><!-- //header-inner -->
				</div>
			</header>

			<header class="site-header">
				<div class="row" id="title_Num">
					<div class="col " style="text-align: left; ;">
						<span>
							<?php echo "受診機関・職員名　：" . $_SESSION['institution']; ?><br>
							<?php echo "受診番号・職員番号：" . $_SESSION['login_num']; ?><br>
							<?php echo "生年月日の月日　　：" . $_SESSION['birth']; ?>

						</span>
					</div>
				</div>
			</header>




			<!-- ここまでhtmlの共通部分 -->


			<!--     ここに確認ページが入る      -->
			<?php if ($page_flag === 1) :
				// insertTable_Interview($passed_Id, "applyId"); //提出した後申し込み番号をDB tableへ書き込み
			?>

				<form method="post" action="">
					<?php foreach ($list_quest as $key => $value) : ?>


						<!-- 質問の数だけ行を作成 -->
						<?php for ($i = 0; $i < count($value['question']); $i++) : ?>
							<!-- jsonファイルを一項目ずつ探索するので，参照部分を変数化して短くした  (1)-->
							<?php $quest_val = $value['question'][$i]; ?>

							<?php if ($quest_val["jenre_No"] == "1") : ?><!-- 質問のジャンルが変わるごとに帯をつける -->
								<div class="row" id="jenre_title">
									<span class="jenre_title_text"><?php echo $quest_val["jenre"]; ?></span>
								</div>
							<?php endif; ?>


							<!-- 一つの質問行のまとまり if文分岐で仕事・休みの日を表示-->
							<section class="row" id="question_contain">
								<!-- 質問文を表示 ”必須”か”該当者のみ”かを条件分岐-->
								<?php if ($quest_val["applicable"] == 0) : ?><p class="quest_0">
									<?php elseif ($quest_val["applicable"] == 1) : ?>
									<p class="quest_1">
									<?php elseif ($quest_val["applicable"] == 2) : ?>
									<p class="quest_2">
									<?php endif; ?>
									<?php echo "Q." . $quest_val["id"] . " " . $quest_val["text"]; ?>
									</p>

									<!-- 仕事の日・休みの日　欄を場合によって表示 -->
									<div class="job_or_holiday">

										<!-- 回答欄を分けなくていい質問 -->
										<?php if ($quest_val["job_or_holi"] == "0") : ?>
											<div class="col" id="job_or_holiday_child">
												<div class="answer">
													<!-- checkboxの時 配列なので要素を列挙-->
													<?php if ($quest_val["type"] == "checkbox") :
														$update_val = "";
														$checkbox_name = "";
														$checkbox_array = [];
														$checkbox_name = str_replace('[]', '', $quest_val["name"]); //input : checkboxの時，nameに[]が不要なため

														if (empty($_POST[$checkbox_name][0])) :
															echo "未選択";
														else :
															foreach ($_POST[$checkbox_name] as $val) : ?>
																<!-- 受け渡し用の箱 -->
																<input type="hidden" name="<?php echo $quest_val["name"]; ?>" value="<?php echo $val; ?>">

																<ul class="answer_list"><?php echo $val; ?></ul>
														<?php array_push($checkbox_array, $val);
															endforeach;
															$update_val = implode('+', $checkbox_array);
															$db_columns[$checkbox_name] = $update_val;

														endif; ?>
														<!-- それ以外の時 -->
													<?php else : ?>
														<!-- 受け渡し用の箱 -->
														<input type="hidden" name="<?php echo $quest_val["name"]; ?>" value="<?php echo $_POST[$quest_val["name"]]; ?>">

														<ul class="answer_list"><?php echo $_POST[$quest_val["name"]]; ?> </ul>
														<?php $db_columns[$quest_val["name"]] = $_POST[$quest_val["name"]]; ?>
													<?php endif; ?>
												</div>
											</div>




											<!-- お仕事の日とお休みの日で別に回答欄がある場合 -->
											<?php elseif ($quest_val["job_or_holi"] == "1") :
											foreach ($jobholi_names as $key => $jobholi_name) :
												$jobholi = "";
												$jobholi =  $jobholi_name . $quest_val["name"];
												$update_val = "";
												$checkbox_name = "";
												$checkbox_array = [];
												$checkbox_name = str_replace('[]', '', $jobholi); //input : checkboxの時，nameに[]が不要なため
											?>
												<div class="col" id="job_or_holiday_child">
													<p class=<?php echo $jobholi_name . "title_s"; ?>><?php echo $key; ?></p>
													<div class="answer">
														<!-- checkboxの時 配列なので要素を列挙-->
														<?php if ($quest_val["type"] == "checkbox") : ?>
															<?php if (empty($_POST[$checkbox_name][0])) :
																echo "未選択";
															else :
																foreach ($_POST[$checkbox_name] as $val) : ?>
																	<!-- 受け渡し用の箱 -->
																	<input type="hidden" name="<?php echo $jobholi; ?>" value="<?php echo $val; ?>">

																	<ul class="answer_list"><?php echo $val; ?></ul>
															<?php array_push($checkbox_array, $val);
																endforeach;
																$update_val = implode('+', $checkbox_array);
																$db_columns[$checkbox_name] = $update_val;

															endif; ?>


															<!-- それ以外の時 -->
														<?php else : ?>
															<!-- 受け渡し用の箱 -->
															<input type="hidden" name="<?php echo $jobholi; ?>" value="<?php echo $_POST[$jobholi]; ?>">

															<ul class="answer_list"><?php echo $_POST[$jobholi]; ?> </ul>
															<?php $db_columns[$jobholi] = $_POST[$jobholi]; ?>
														<?php endif; ?>
													</div>

												</div>
											<?php endforeach; ?>


										<?php endif; ?>
									</div>
							</section>




						<?php endfor; ?>
					<?php endforeach; ?>

					<div class="row" id="jenre_title">
						<span class="jenre_title_text">体重変化について</span>
					</div>
					<section class="row" id="question_contain">
						<p class="quest_0">Q.74 過去３年間の体重変化についてわかる範囲でお答えください</p>


						<table class="weight_table" align="center" border="1">
							<thead>
								<tr>
									<th></th>
									<th>3年前→2年前</th>
									<th>2年前→1年前</th>
									<th>1年前→現在</th>
								</tr>
							</thead>
							<tbody>
								<!-- 一行目 -->
								<tr>
									<th>体重変化</th>
									<td><?php echo $_POST['weight_3year']; ?></td>
									<td><?php echo $_POST['weight_2year']; ?></td>
									<td><?php echo $_POST['weight_1year']; ?></td>
								</tr>
								<!-- 二行目 -->
								<tr>
									<th>主たる体重変化の原因</th>
									<td><?php echo $_POST['weight_cause_3year']; ?></td>
									<td><?php echo $_POST['weight_cause_2year']; ?></td>
									<td><?php echo $_POST['weight_cause_1year']; ?></td>
								</tr>
							</tbody>
						</table><br>
					</section>
					<br>
					<?php
					$db_columns['weight_3year'] = $_POST['weight_3year'];
					$db_columns['weight_2year'] = $_POST['weight_2year'];
					$db_columns['weight_1year'] = $_POST['weight_1year'];

					$db_columns['weight_cause_3year'] = $_POST['weight_cause_3year'];
					$db_columns['weight_cause_2year'] = $_POST['weight_cause_2year'];
					$db_columns['weight_cause_1year'] = $_POST['weight_cause_1year'];
					$db_columns['update_time'] = date("Y/m/d H:i");

					setDB($passed_Id, $db_columns, $flag_db_exisit);
					// var_dump($db_columns);
					?>


					<!-- 受け渡し用の箱 -->
					<?php foreach (["weight_3year", "weight_2year", "weight_1year", "weight_cause_3year", "weight_cause_2year", "weight_cause_1year"] as $val_list) : ?>
						<input type="hidden" name="<?php echo $val_list; ?>" value="<?php echo $_POST[$val_list]; ?>">
					<?php endforeach; ?>





					<div class="form_parts">
						<input type="submit" name="btn_back" value="戻る" class="button_design">
						<input type="submit" name="btn_submit" value="上記の内容で登録" class="button_design">
					</div>

				</form>











				<!-- 入力ページ -->
			<?php else : ?>



				<form action="" method="post" name="input_form" onsubmit="return validation()">

					<?php foreach ($list_quest as $key => $value) : ?>

						<!-- 質問の数だけ行を作成 -->
						<?php for ($i = 0; $i < count($value['question']); $i++) : ?>
							<!-- 参照部分を変数化して短くした (2)-->
							<?php $quest_val = $value['question'][$i]; ?>

							<?php if ($quest_val["jenre_No"] == "1") : ?><!-- 質問のジャンルが変わるごとに帯をつける -->
								<div class="row" id="jenre_title">
									<span class="jenre_title_text"><?php echo $quest_val["jenre"]; ?></span>
								</div>
							<?php endif; ?>


							<!--    1つの質問行のまとまり   -->
							<section class="row" id="question_contain">
								<!-- 質問文を表示 ”必須”か”該当者のみ”かを条件分岐-->
								<?php if ($quest_val["applicable"] == 0) : ?><p class="quest_0">
									<?php elseif ($quest_val["applicable"] == 1) : ?>
									<p class="quest_1">
									<?php elseif ($quest_val["applicable"] == 2) : ?>
									<p class="quest_2">
									<?php endif; ?>
									<?php echo "Q." . $quest_val["id"] . " " . $quest_val["text"]; ?>
									</p>


									<!-- エラー表示部分（通常は非表示設定） -->
									<div id="fk-error" style="display:block;">
										<span id="<?php echo "fk-error-" . $quest_val["name"]; ?>" style="display:none;"><?php
																															if (($quest_val["applicable"] == 0) or ($quest_val["applicable"] == 1)) :
																																$error_text = "※未回答です";
																																echo $error_text;
																															// elseif ($quest_val["applicable"]==1):
																															// 		$error_text="※いずれかが未回答です";
																															endif;
																															?>
										</span>
									</div>



									<!-- 仕事の日・休みの日　の回答欄を場合によって表示 -->
									<div class="job_or_holiday">

										<!-- 回答欄を分けなくていい質問 -->
										<?php if ($quest_val["job_or_holi"] == "0") : ?>
											<!-- エラー表示部分（通常は非表示設定） -->
											<div class="col" id="job_or_holiday_child">
												<!-- 回答時の選択肢を表示 -->
												<div class="answer">
													<!-- 自由入力タイプの質問文の場合 -->
													<?php if ($quest_val["type"] == "form") : ?>
														<input type="number" name="<?php echo $quest_val["name"]; ?>" size="8" value="<?php if (!empty($_POST[$quest_val["name"]])) {
																																			echo $_POST[$quest_val["name"]];
																																		} ?>" placeholder="※半角数字で入力して下さい" id="textID">


														<!-- セレクトタイプの質問文の場合 -->
													<?php elseif ($quest_val["type"] == "select") : ?>
														<select class="select_class_one" name="<?php echo $quest_val["name"]; ?>" id="">
															<option hidden value="">選択してください</option>
															<!-- <option value="" disabled>選択してください</option> -->
															<?php for ($j = 0; $j < count($quest_val["answer"]); $j++) : ?>
																<option <?php if (
																			!empty($_POST[$quest_val["name"]]) &&
																			$_POST[$quest_val["name"]] === $quest_val["answer"][$j]
																		) {
																			echo "selected";
																		} ?>><?php echo $quest_val["answer"][$j]; ?>
																</option>

															<?php endfor; ?>
														</select>



														<!-- ラジオボタンタイプの質問文の場合 -->
													<?php elseif ($quest_val["type"] == "radio") : ?>
														<div class="vertical-align">
															<!-- 選択肢の数だけ繰り返す -->
															<input type="radio" name="<?php echo $quest_val["name"]; ?>" value="" checked="checked" style="display:none;">



															<!-- 質問 -->
															<?php for ($j = 0; $j < count($quest_val["answer"]); $j++) : ?>
																<label style="display:block;">
																	<input type="radio" name="<?php echo $quest_val["name"]; ?>" value="<?php echo $quest_val["answer"][$j]; ?>" <?php
																																													/*POSTで受け取った値が空でない，かつPOSTの値と一致したjの時"checked"をつける*/
																																													if (
																																														!empty($_POST[$quest_val["name"]])
																																														&& $_POST[$quest_val["name"]] === $quest_val["answer"][$j]
																																													) {
																																														echo "checked";
																																													}
																																													?>>

																	<?php echo $quest_val["answer"][$j]; ?>

																	<?php /*選択肢の中に"その他"の項目があるとき*/
																	if (($quest_val["answer"][$j] == "その他") && ($quest_val["free_text"] == "1")) : ?>
																		<span id="<?php echo $quest_val["name"]; ?>">
																			( 具体的にどうぞ：<input type="text" name="othertext" value="" size="15"> )</span>
																	<?php endif; ?>

																</label>
															<?php endfor; ?>




														</div>

														<!-- チェックボックスタイプの質問文の場合 -->
													<?php elseif ($quest_val["type"] == "checkbox") : ?>
														<div class="vertical-align">

															<?php for ($j = 0; $j < count($quest_val["answer"]); $j++) : ?>
																<label style="display:block;">
																	<input class="checkBoxes" type="checkbox" name="<?php echo $quest_val["name"]; ?>" value="<?php echo $quest_val["answer"][$j]; ?>" <?php /*POSTで受け取った値が空でない，かつPOSTの値と一致したjの時"checked"をつける*/
																																																		$checkbox_name = str_replace('[]', '', $quest_val["name"]);


																																																		if (!empty($_POST[$checkbox_name])) {
																																																			foreach ($_POST[$checkbox_name] as $check_val) {
																																																				if ($check_val === $quest_val["answer"][$j]) {
																																																					echo "checked";
																																																				}
																																																			}
																																																		}
																																																		?> onclick="Climit('<?php echo $quest_val["name"]; ?>', '<?php echo $quest_val["check_Num"]; ?>' );" />

																	<?php echo $quest_val["answer"][$j]; ?>
																</label>

															<?php endfor; ?>
														</div>


													<?php endif; ?>



												</div>
												<!-- 選択肢の表示終わり -->
											</div>


											<!-- 回答欄を分ける必要がある質問 -->
											<?php elseif ($quest_val["job_or_holi"] == "1") :
											$jobholi = $quest_val["name"];
											foreach ($jobholi_names as $key => $jobholi_name) :
												$jobholi_2 =  $jobholi_name . $jobholi;
												$quest_val["name"] = $jobholi_2;
												$jobholi_2 = ""; ?>
												<div class="col" id="job_or_holiday_child">
													<p class=<?php echo $jobholi_name . "title_s"; ?>><?php echo $key; ?></p>








													<!-- 回答時の選択肢を表示 -->
													<div class="answer">
														<!-- 自由入力タイプの質問文の場合 -->
														<?php if ($quest_val["type"] == "form") : ?>
															<input type="number" name="<?php echo $quest_val["name"]; ?>" size="8" value="<?php if (!empty($_POST[$quest_val["name"]])) {
																																				echo $_POST[$quest_val["name"]];
																																			} ?>" placeholder="※半角数字で入力して下さい" id="textID">


															<!-- セレクトタイプの質問文の場合 -->
														<?php elseif ($quest_val["type"] == "select") : ?>
															<select class="select_class_two" name="<?php echo $quest_val["name"]; ?>" id="">
																<option hidden value="">選択してください</option>
																<!-- <option value="" disabled>選択してください</option> -->
																<?php for ($j = 0; $j < count($quest_val["answer"]); $j++) : ?>
																	<option <?php if (
																				!empty($_POST[$quest_val["name"]]) &&
																				$_POST[$quest_val["name"]] === $quest_val["answer"][$j]
																			) {
																				echo "selected";
																			} ?>><?php echo $quest_val["answer"][$j]; ?>
																	</option>

																<?php endfor; ?>
															</select>



															<!-- ラジオボタンタイプの質問文の場合 -->
														<?php elseif ($quest_val["type"] == "radio") : ?>
															<div class="vertical-align">
																<!-- 選択肢の数だけ繰り返す -->
																<input type="radio" name="<?php echo $quest_val["name"]; ?>" value="" checked="checked" style="display:none;">
																<?php for ($j = 0; $j < count($quest_val["answer"]); $j++) : ?>

																	<label style="display:block;">
																		<input type="radio" name="<?php echo $quest_val["name"]; ?>" value="<?php echo $quest_val["answer"][$j]; ?>" <?php
																																														/*POSTで受け取った値が空でない，かつPOSTの値と一致したjの時"checked"をつける*/
																																														if (
																																															!empty($_POST[$quest_val["name"]])
																																															&& $_POST[$quest_val["name"]] === $quest_val["answer"][$j]
																																														) {
																																															echo "checked";
																																														}
																																														?>>

																		<?php echo $quest_val["answer"][$j]; ?>

																		<?php /*選択肢の中に"その他"の項目があるとき*/
																		if (($quest_val["answer"][$j] == "その他") && ($quest_val["free_text"] == "1")) : ?>
																			<span id="<?php echo $quest_val["name"]; ?>">
																				( 具体的にどうぞ：<input type="text" name="othertext" value="" size="15"> )</span>
																		<?php endif; ?>
																	</label>

																<?php endfor; ?>



															</div>

															<!-- チェックボックスタイプの質問文の場合 -->
														<?php elseif ($quest_val["type"] == "checkbox") : ?>
															<div class="vertical-align">

																<?php for ($j = 0; $j < count($quest_val["answer"]); $j++) : ?>
																	<label style="display:block;">
																		<input class="checkBoxes" type="checkbox" name="<?php echo $quest_val["name"]; ?>" value="<?php echo $quest_val["answer"][$j]; ?>" <?php /*POSTで受け取った値が空でない，かつPOSTの値と一致したjの時"checked"をつける*/

																																																			$checkbox_name = str_replace('[]', '', $quest_val["name"]);
																																																			if (!empty($_POST[$checkbox_name])) {
																																																				foreach ($_POST[$checkbox_name] as $check_val) {
																																																					if ($check_val === $quest_val["answer"][$j]) {
																																																						echo "checked";
																																																					}
																																																				}
																																																			}
																																																			?> onclick="Climit('<?php echo $quest_val["name"]; ?>', '<?php echo $quest_val["check_Num"]; ?>' );" />

																		<?php echo $quest_val["answer"][$j]; ?>
																	</label>
																<?php endfor; ?>
															</div>
														<?php endif; ?>


													</div>
													<!-- 選択肢の表示終わり -->
												</div>
											<?php endforeach; ?>
										<?php endif; ?>
									</div>

							</section>
							<!--    {終わり} 1つの質問のまとまり   -->


						<?php endfor; ?>

					<?php endforeach; ?>

					<div class="row" id="jenre_title">
						<span class="jenre_title_text">体重変化について</span>
					</div>
					<section class="row" id="question_contain">
						<p class="quest_0">Q.74 過去３年間の体重についてわかる範囲でお答えください
							<!-- <span style="font-size: 16px; color: rgba(81,201,165,0.8);">(体重の値を入力してください)</span> -->
						</p>

						<div id="fk-error" style="display:block;">
							<span id="fk-error-weight-error" style="display:none;">※いずれかが未回答です</span>
						</div>

						<table class="weight_table" align="center" border="1">
							<thead>
								<tr>
									<th style="width: 15%;"></th>
									<th>3年前→2年前</th>
									<th>2年前→1年前</th>
									<th>1年前→現在</th>
								</tr>
							</thead>
							<tbody>

								<!-- 一行目 -->
								<tr>
									<th>体重変化</th>
									<td><select name="weight_3year" style="width: 90%;">
											<option hidden value="">選択してください</option>
											<option value="増えた" <?php if (
																	!empty($_POST["weight_3year"]) &&
																	$_POST["weight_3year"] === "増えた"
																) {
																	echo "selected";
																} ?>>増えた</option>

											<option value="減った" <?php if (
																	!empty($_POST["weight_3year"]) &&
																	$_POST["weight_3year"] === "減った"
																) {
																	echo "selected";
																} ?>>減った</option>

											<option value="変わらない" <?php if (
																		!empty($_POST["weight_3year"]) &&
																		$_POST["weight_3year"] === "変わらない"
																	) {
																		echo "selected";
																	} ?>>変わらない</option>


										</select>
									</td>

									<td><select name="weight_2year" style="width: 90%;">
											<option hidden value="">選択してください</option>
											<option value="増えた" <?php if (
																	!empty($_POST["weight_2year"]) &&
																	$_POST["weight_2year"] === "増えた"
																) {
																	echo "selected";
																} ?>>増えた</option>

											<option value="減った" <?php if (
																	!empty($_POST["weight_2year"]) &&
																	$_POST["weight_2year"] === "減った"
																) {
																	echo "selected";
																} ?>>減った</option>

											<option value="変わらない" <?php if (
																		!empty($_POST["weight_2year"]) &&
																		$_POST["weight_2year"] === "変わらない"
																	) {
																		echo "selected";
																	} ?>>変わらない</option>

										</select>
									</td>

									<td><select name="weight_1year" style="width: 90%;">
											<option hidden value="">選択してください</option>
											<option value="増えた" <?php if (
																	!empty($_POST["weight_1year"]) &&
																	$_POST["weight_1year"] === "増えた"
																) {
																	echo "selected";
																} ?>>増えた</option>

											<option value="減った" <?php if (
																	!empty($_POST["weight_1year"]) &&
																	$_POST["weight_1year"] === "減った"
																) {
																	echo "selected";
																} ?>>減った</option>

											<option value="変わらない" <?php if (
																		!empty($_POST["weight_1year"]) &&
																		$_POST["weight_1year"] === "変わらない"
																	) {
																		echo "selected";
																	} ?>>変わらない</option>

										</select>
									</td>



								</tr>


								<!-- 二行目 -->
								<tr>
									<th>主たる体重変化<br>の原因</th>
									<td><select name="weight_cause_3year" style="width: 90%;">
											<option hidden value="">選択してください</option>
											<option value="体重変化なし" <?php if (
																		!empty($_POST["weight_cause_3year"]) &&
																		$_POST["weight_cause_3year"] === "体重変化なし"
																	) {
																		echo "selected";
																	} ?>>体重変化なし</option>

											<option value="過食" <?php if (
																	!empty($_POST["weight_cause_3year"]) &&
																	$_POST["weight_cause_3year"] === "過食"
																) {
																	echo "selected";
																} ?>>過食</option>

											<option value="仕事の変化" <?php if (
																		!empty($_POST["weight_cause_3year"]) &&
																		$_POST["weight_cause_3year"] === "仕事の変化"
																	) {
																		echo "selected";
																	} ?>>仕事の変化</option>

											<option value="運動不足" <?php if (
																		!empty($_POST["weight_cause_3year"]) &&
																		$_POST["weight_cause_3year"] === "運動不足"
																	) {
																		echo "selected";
																	} ?>>運動不足</option>

											<option value="単身赴任" <?php if (
																		!empty($_POST["weight_cause_3year"]) &&
																		$_POST["weight_cause_3year"] === "単身赴任"
																	) {
																		echo "selected";
																	} ?>>単身赴任</option>

											<option value="ストレス" <?php if (
																		!empty($_POST["weight_cause_3year"]) &&
																		$_POST["weight_cause_3year"] === "ストレス"
																	) {
																		echo "selected";
																	} ?>>ストレス</option>

											<option value="生活習慣の改善" <?php if (
																		!empty($_POST["weight_cause_3year"]) &&
																		$_POST["weight_cause_3year"] === "生活習慣の改善"
																	) {
																		echo "selected";
																	} ?>>生活習慣の改善</option>

											<option value="運動による成果" <?php if (
																		!empty($_POST["weight_cause_3year"]) &&
																		$_POST["weight_cause_3year"] === "運動による成果"
																	) {
																		echo "selected";
																	} ?>>運動による成果</option>

											<option value="食事改善" <?php if (
																		!empty($_POST["weight_cause_3year"]) &&
																		$_POST["weight_cause_3year"] === "食事改善"
																	) {
																		echo "selected";
																	} ?>>食事改善</option>

											<option value="その他" <?php if (
																	!empty($_POST["weight_cause_3year"]) &&
																	$_POST["weight_cause_3year"] === "その他"
																) {
																	echo "selected";
																} ?>>その他</option>
										</select>
									</td>


									<td><select name="weight_cause_2year" style="width: 90%;">
											<option hidden value="">選択してください</option>
											<option value="体重変化なし" <?php if (
																		!empty($_POST["weight_cause_2year"]) &&
																		$_POST["weight_cause_2year"] === "体重変化なし"
																	) {
																		echo "selected";
																	} ?>>体重変化なし</option>

											<option value="過食" <?php if (
																	!empty($_POST["weight_cause_2year"]) &&
																	$_POST["weight_cause_2year"] === "過食"
																) {
																	echo "selected";
																} ?>>過食</option>

											<option value="仕事の変化" <?php if (
																		!empty($_POST["weight_cause_2year"]) &&
																		$_POST["weight_cause_2year"] === "仕事の変化"
																	) {
																		echo "selected";
																	} ?>>仕事の変化</option>

											<option value="運動不足" <?php if (
																		!empty($_POST["weight_cause_2year"]) &&
																		$_POST["weight_cause_2year"] === "運動不足"
																	) {
																		echo "selected";
																	} ?>>運動不足</option>

											<option value="単身赴任" <?php if (
																		!empty($_POST["weight_cause_2year"]) &&
																		$_POST["weight_cause_2year"] === "単身赴任"
																	) {
																		echo "selected";
																	} ?>>単身赴任</option>

											<option value="ストレス" <?php if (
																		!empty($_POST["weight_cause_2year"]) &&
																		$_POST["weight_cause_2year"] === "ストレス"
																	) {
																		echo "selected";
																	} ?>>ストレス</option>

											<option value="生活習慣の改善" <?php if (
																		!empty($_POST["weight_cause_2year"]) &&
																		$_POST["weight_cause_2year"] === "生活習慣の改善"
																	) {
																		echo "selected";
																	} ?>>生活習慣の改善</option>

											<option value="運動による成果" <?php if (
																		!empty($_POST["weight_cause_2year"]) &&
																		$_POST["weight_cause_2year"] === "運動による成果"
																	) {
																		echo "selected";
																	} ?>>運動による成果</option>

											<option value="食事改善" <?php if (
																		!empty($_POST["weight_cause_2year"]) &&
																		$_POST["weight_cause_2year"] === "食事改善"
																	) {
																		echo "selected";
																	} ?>>食事改善</option>

											<option value="その他" <?php if (
																	!empty($_POST["weight_cause_2year"]) &&
																	$_POST["weight_cause_2year"] === "その他"
																) {
																	echo "selected";
																} ?>>その他</option>
										</select>
									</td>


									<td><select name="weight_cause_1year" style="width: 90%;">
											<option hidden value="">選択してください</option>
											<option value="体重変化なし" <?php if (
																		!empty($_POST["weight_cause_1year"]) &&
																		$_POST["weight_cause_1year"] === "体重変化なし"
																	) {
																		echo "selected";
																	} ?>>体重変化なし</option>

											<option value="過食" <?php if (
																	!empty($_POST["weight_cause_1year"]) &&
																	$_POST["weight_cause_1year"] === "過食"
																) {
																	echo "selected";
																} ?>>過食</option>

											<option value="仕事の変化" <?php if (
																		!empty($_POST["weight_cause_1year"]) &&
																		$_POST["weight_cause_1year"] === "仕事の変化"
																	) {
																		echo "selected";
																	} ?>>仕事の変化</option>

											<option value="運動不足" <?php if (
																		!empty($_POST["weight_cause_1year"]) &&
																		$_POST["weight_cause_1year"] === "運動不足"
																	) {
																		echo "selected";
																	} ?>>運動不足</option>

											<option value="単身赴任" <?php if (
																		!empty($_POST["weight_cause_1year"]) &&
																		$_POST["weight_cause_1year"] === "単身赴任"
																	) {
																		echo "selected";
																	} ?>>単身赴任</option>

											<option value="ストレス" <?php if (
																		!empty($_POST["weight_cause_1year"]) &&
																		$_POST["weight_cause_1year"] === "ストレス"
																	) {
																		echo "selected";
																	} ?>>ストレス</option>

											<option value="生活習慣の改善" <?php if (
																		!empty($_POST["weight_cause_1year"]) &&
																		$_POST["weight_cause_1year"] === "生活習慣の改善"
																	) {
																		echo "selected";
																	} ?>>生活習慣の改善</option>

											<option value="運動による成果" <?php if (
																		!empty($_POST["weight_cause_1year"]) &&
																		$_POST["weight_cause_1year"] === "運動による成果"
																	) {
																		echo "selected";
																	} ?>>運動による成果</option>

											<option value="食事改善" <?php if (
																		!empty($_POST["weight_cause_1year"]) &&
																		$_POST["weight_cause_1year"] === "食事改善"
																	) {
																		echo "selected";
																	} ?>>食事改善</option>

											<option value="その他" <?php if (
																	!empty($_POST["weight_cause_1year"]) &&
																	$_POST["weight_cause_1year"] === "その他"
																) {
																	echo "selected";
																} ?>>その他</option>
										</select>
									</td>




								</tr>

							</tbody>
						</table><br>
					</section>






					<div class="form_parts">


						<input type="submit" name="btn_confirm" value="確認画面へ" class="button_design">
					</div>




				</form>



			<?php endif; ?>


		</div><!--container-fluid -->




		<script src="./js/header_fixed.js"></script>
		<script src="./js/check_count.js"></script>




	</div> <!--wrapper -->

	<style>
		.quest_0::after {
			content: '必須';
		}

		.quest_1::after {
			content: '（退職された方は「仕事のない日」に回答して下さい）';
		}
	</style>

</body>

</html>