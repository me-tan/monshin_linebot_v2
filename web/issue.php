<?php
// PHPは基本的に上から順番に実行されるよ
//ini_set('display_errors',1); // これを書いとくとPHPがエラー吐いたときにきちんとそれを表示してくれる
//ini_set('error_reporting', E_ALL);

session_start();


require_once dirname(__FILE__) . "/dbFunctions.php";


$file_job = './json/job.json'; // 仕事の日の一覧データ
$json_job = file_get_contents($file_job); //指定したファイルの要素をすべて取得する
$json_job = mb_convert_encoding($json_job, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN'); //jsonファイルの変換
$json_job = json_decode($json_job, true); // json形式のデータを連想配列の形式にする


// サニタイズ
function sanitaize(){
	if( !empty($_POST) ) {
		foreach( $_POST as $key => $value ) {
			if(is_array($value)){
				$clean[$key] = htmlspecialchars( $value[0], ENT_QUOTES);

			}else{
				$clean[$key] = htmlspecialchars( $value, ENT_QUOTES);
			}

		}
	}
}

sanitaize();





// 変数の初期化
$page_flag = 0;


$year = '';
$month = '';
$day = '';

$i =0;



// セレクトボックスの値を格納する配列
$institutionsList = array(
	"和歌山大学",
	"その他"
	// "市医師会成人病センター",
	// "小池クリニック",
	// "和歌山県警",
	// "キクロン",
	// "剤生堂",
	// "紀陽銀行",
	// "和歌山トヨタ",
	// "関西電力",
);


// 表示内容の変更（flagを変える）
if( !empty($_POST['btn_issue']) ) {
	$page_flag = 1; //一回提出したら旗を1にする

}elseif( !empty($_POST['btn_reissue']) ){
	$page_flag = 0; //登録完了後、もう1件登録するときflagを元に戻す
}






//$_POST['applyId'] が空じゃないとき⇒なにかしらの値を入力したとき (何も入力せずボタンを押すと再リロード)
if( !empty($_POST['institution']) && !empty($_POST['login_num']) && !empty($_POST['year']) && !empty($_POST['month']) && !empty($_POST['day']) && !empty($_POST['line_uid'])) :

	try {
			//空白文字列の削除
			$_POST['login_num'] = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['login_num']);
			// 全角アルファベット→半角アルファベットへ変換を実行
			$_POST['login_num'] = mb_convert_kana( $_POST['login_num'], 'a');

			//ログイン認証用の文字列を作成（各要素を結合）
			$pass_encrypt = $_POST['institution']. $_POST['login_num']. $_POST['year']. $_POST['month']. $_POST['day'] . $_POST['line_uid'];
			$pass_encrypt = hash('sha256',$pass_encrypt); //ハッシュ化を済ませておく


			$birth =  $_POST['year']. "年". $_POST['month']. "月". $_POST['day']. "日";


			$id = insertTable_Encrypted( $pass_encrypt, $_POST['institution'] , $_POST['login_num'], $_POST['year'], $_POST['month'] ,$_POST['day'] ,$_POST['line_uid']); //暗号化した後、挿入した行のidが返ってくる
			$_SESSION['id'] = $id;

			

			// ID発行したあと、POSTの中を空にする
			$_POST['institution']="";
			$_POST['login_num']="";
			$_POST['year']="";
			$_POST['month']="";
			$_POST['day']="";
			$_POST['line_uid']="";



			$alert = "<script type='text/javascript'>alert('IDの発行が完了しました');</script>";

			echo "$alert";

			



	} catch (Exception $e) {
			
			$page_flag = 0; //flagを元に戻す
			$e = $e->getMessage(); //例外メッセージを取得する 
			//alert = "<script type='text/javascript'>alert('IDの登録が完了しませんでした。');</script>";
			$alert = "<script type='text/javascript'>alert('"  .$e. " ');</script>";

			echo "$alert";
	    

	}
endif;


?>


<!DOCTYPE html>
<html lang= "ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">


  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"/>
  <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="./css/common.css" />
  <link rel="stylesheet" href="./css/main.css" />
	<!-- Font Awesome -->
	<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>


</head>

<title>問診票</title>

<body>
<div id="wrapper">





  <div class="container-fluid">




		<!-- header -->



		<!-- 登録完了後の画面 $flag=1 の時　-->
		<?php if( $page_flag === 1 ):?>
			<section class ="greeting" >IDの発行が完了しました。</section>

			<form action="" method="post" id="login_form">
				<div class="form_parts"><input type="submit" name="btn_reissue" value="別のIDを発行する"
				class="button_design" style="margin: 1.5em "/></div>
			</form>









		<!-- 登録の画面 $flag=0 の時　-->
		<?php else:?>
			<section class ="greeting" >パスワード発行画面 </section>

			<form action="" method="post" id="login_form">

					<!-- １つ目の説明文  -->
					<div class="form_parts_login" style="margin-top: 1em!important;">
						<span >
						在学中の大学を選択してください
						</span>
					</div>

					<!-- １つ目の質問  -->
						<table class="login_table" align="center" >
							<tbody>
								<tr>
										<th class="login_table_th">大学</th>
										<td class="login_table_td">
											<select name="institution" required>
												<option value="">-</option>

												<?php
													foreach($institutionsList as $value){
														$i++;
															echo "<option value='$value'>" .$i. ".".$value."</option>";
													
													}
												?>
											</select>


										</td>
								</tr>
							</tbody>
						</table>

					<!-- ２つ目の説明文 -->
						<div class="form_parts_login">
							<span  >
							LINEのIDと氏名（姓と名の間に空白を入れず，ひらがなで入力してください）を<br>入力してください</span>
						</div>




						<table class="login_table" align="center" >
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
											<input  type="text" name="login_num" size="19em"  placeholder="例）123456"

											value = "" autocomplete="off"
											title=""/>
									</td>
								</tr>

								<!-- ３つ目の質問  生年月日-->
								<tr>
										<th class="login_table_th">生年月日</th>
										<td class="login_table_td">
											<select name="year" required>
												<option value="">-</option>
												<?php
													for ($j=1900; $j <= 2022; $j++):

														$year = '<option value="'.$j.'">'.$j.'</option>';
														echo $year;

													endfor;
												?>
										</select> 年

										<select name="month" required>
											<option value="">-</option>
											<?php
												for ($j=1; $j <= 12; $j++):
													// 0で埋める
													if( $j < 10 ):
														$j = '0' . strval($j);
													endif;

													$month = '<option value="'.$j.'">'.$j.'</option>';
													echo $month;

												endfor;
											?>
											</select>月



											<select name="day" required>
												<option value="">-</option>
												<?php
													for ($j=1; $j <= 31; $j++):

														// 0で埋める
														if( $j < 10 ):
															$j = '0' . strval($j);
														endif;

														$day = '<option value="'.$j.'">'.$j.'</option>';
														echo $day;

													endfor;
												?>
											</select> 日
										</td>
								</tr>

							</tbody>
						</table>


        		<div class="form_parts"><input type="submit" name="btn_issue" value="パスワードを発行する"
							class="button_design" style="margin: 1.5em "/></div>
		  </form>

		<?php endif; ?>





  </div><!--container-fluid -->

</div>  <!--wrapper -->

</body>

</html>
