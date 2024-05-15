<?php
ini_set('display_errors',1); // これを書いとくとPHPがエラー吐いたときにきちんとそれを表示してくれる
require_once dirname(__FILE__) . "/connect_mysql.php"; // mysql（データベースに接続するためのおまじない）を読み込み
require_once('./define.php');// $db_columnsを呼ぶため

require_once(dirname(__FILE__)."/vendor/autoload.php");//ライブラリの読み込み
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__); //.envを読み込む
$dotenv->load();


// 環境変数定義
// $healthy_enidence  = ["熟睡できる","毎日食べる","ほとんど間食しない","非喫煙","飲まない","ほぼ毎日","普通体重"];// 現状（2023/01/04）時点で考えうる最も健康なエビデンス
// $mnsn_name = ["睡眠の質","朝食","間食","タバコ","酒","運動","肥満度"];// 項目の名前を格納
$mnsn_name = ["運動","肥満度","間食","朝食","タバコ","睡眠の質","酒"];

/**
* データベースに引数の情報を書き込む関数
* summaryでコメントを入力するためだけに使う（変更：2023/03/02）
* @param string $pass_encrypt
* @param string $formValue
* @param string $arrayName
**/
function updateTable_Interview($pass_encrypt, $formValue, $arrayName) { // pass_encrypt:セッションで受け取ったID, formValue：POSTで受け取った値 arrayName：問診票のname

    $pdo = connectMysql(); // 4行目で読み込んだファイルの関数（ connectMysql() ）を実行

    $valuesName = ':'.$arrayName; //VALUESの中にphpの文字列演算子が使えないので，あらかじめ変数定義

    $stmt = $pdo -> prepare("UPDATE mnsn_sheet_linebot_test SET $arrayName = $valuesName WHERE :pass_hash = pass_hash ORDER BY id DESC limit 1");

    $params = array(
      $valuesName => $formValue,
      'pass_hash' => $pass_encrypt);

    $stmt->execute($params); // 実行
}




// //ログインした人の行を新たに挿入し，その情報を暗号化して登録  
/*
function insertTable_Interview($formValue_name,$formValue_birth, $formValue_tel, $formValue_email) {

    $pdo = connectMysql(); // 4行目で読み込んだファイルの関数（ connectMysql() ）を実行

    $stmt = $pdo -> prepare("INSERT INTO
    Interview_sheet (name, birth, tel, email)
    VALUES (:name, :birth, :tel, :email)");

    ////////////////////////////////////////////////////

    $stmt->bindValue(':name', $formValue_name, PDO::PARAM_STR); // PDO::以降で書き込むデータの型を指定する．だいたいINTかSTRかな
    $stmt->bindValue(':birth', $formValue_birth, PDO::PARAM_STR);
    $stmt->bindValue(':tel', $formValue_tel, PDO::PARAM_STR);
    $stmt->bindValue(':email', $formValue_email, PDO::PARAM_STR);

    $stmt->execute(); // 実行

    // 登録したデータのIDを取得して出力
    $id = $pdo -> lastInsertId();
    // var_dump($id); //出力用

    return $id;

}
*/


/** 送信した内容を暗号化した後DBに登録
 * 
 * 
 **/
function insertTable_Encrypted($pass_encrypt, $formValue_institution,$formValue_login_num, $formValue_year, $formValue_month, $formValue_day) {

    $pdo = connectMysql(); // 4行目で読み込んだファイルの関数（ connectMysql() ）を実行


    // 同じ入力内容の登録を防ぐ機能
    //$stmt_0 = $pdo -> prepare("SELECT id, pass_hash FROM mnsn_sheet_linebot_test ORDER BY id");
    $stmt_0 = $pdo -> prepare("SELECT id, pass_hash FROM mnsn_sheet_linebot_test where :pass_hash = pass_hash ORDER BY id");


    //bindValueメソッドでパラメータをセット
    $stmt_0->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); // PDO::以降で書き込むデータの型を指定する．だいたいINTかSTRかな
    $stmt_0->execute();
    $all = $stmt_0->fetchAll(PDO::FETCH_ASSOC); //全件取得 （取得内容）FETCH_ASSOC→【配列のキー】カラム名のみ

    
    //SQLで検索をかけて該当するものがあった時
    if( isset($all[0]["pass_hash"]) ){
        throw new Exception('こちらの内容は既に登録されています');
    }


    /* 一行ずつマッチング
    foreach ( $all as $row) {
        
        if ( ( password_verify( $pass_encrypt_ori, $row['pass_hash'])) ) { // もし入力内容が既に登録されていた時
            throw new Exception('こちらの内容は既に登録されています');
        }
    }
    */


    // 同じ内容が登録されていなければ以下の処理に入る
    $stmt = $pdo -> prepare("INSERT INTO
    mnsn_sheet_linebot_test (pass_hash, institution, login_num, year, month, day )
    VALUES (:pass_hash, :institution, :login_num, :year, :month, :day)");

    


    // 関数呼び出して公開鍵で暗号化
    $formValue_institution = value_Encrypted($formValue_institution);
    $formValue_login_num = value_Encrypted($formValue_login_num);
    $formValue_year = value_Encrypted($formValue_year);
    $formValue_month = value_Encrypted($formValue_month);
    $formValue_day = value_Encrypted($formValue_day);

    ////////////////////////////////////////////////////
    $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR);

    $stmt->bindValue(':institution', $formValue_institution, PDO::PARAM_STR); // PDO::以降で書き込むデータの型を指定する．だいたいINTかSTRかな
    $stmt->bindValue(':login_num', $formValue_login_num, PDO::PARAM_STR);
    $stmt->bindValue(':year', $formValue_year, PDO::PARAM_STR);
    $stmt->bindValue(':month', $formValue_month, PDO::PARAM_STR);
    $stmt->bindValue(':day', $formValue_day, PDO::PARAM_STR);

    $stmt->execute(); // 実行

    // 登録したデータのIDを取得して出力
    $id = $pdo -> lastInsertId();
    // var_dump($id); //出力用

    return $id;

}


/** 
* テーブルに入力した内容があるかどうか検証（ログイン機能）
*
**/
function certificateTable_Interview( $pass_encrypt ) {

    $pdo = connectMysql(); // 4行目で読み込んだファイルの関数（ connectMysql() ）を実行
    
    $stmt = $pdo -> prepare("SELECT * FROM mnsn_sheet_linebot_test where :pass_hash = pass_hash");


    //bindValueメソッドでパラメータをセット
    $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); // PDO::以降で書き込むデータの型を指定する．だいたいINTかSTRかな
    $stmt->execute();
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC); //全件取得

    
    if( !isset($all[0]["pass_hash"]) ){
        // throw new Exception('入力内容に誤りがあります 再度入力をお願い致します');
        $alert = "<script type='text/javascript'>alert('入力内容に誤りがあります．再度入力をお願い致します．'); window.location.href = './index.php'</script>";
		echo $alert;
    }
    $id = $all[0]["id"];

    return $id;

}


/** 
* テーブルに必須項目（性別）が登録されているかを確認
*
**/
function certificateTable_Interview_gender( $pass_encrypt ) {

    $pdo = connectMysql(); // 4行目で読み込んだファイルの関数（ connectMysql() ）を実行

    $stmt = $pdo -> prepare("SELECT * FROM mnsn_sheet_linebot_test where :pass_hash = pass_hash");


    //bindValueメソッドでパラメータをセット
    $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); // PDO::以降で書き込むデータの型を指定する．だいたいINTかSTRかな
    $stmt->execute();
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC); //全件取得

    
    if( !isset($all[0]["pass_hash"]) ){
        throw new Exception('入力内容に誤りがあります 再度入力をお願い致します');
    }
    $gender = $all[0]["gender"];

    return $gender;
}

/** 
* テーブルに入力日時が登録されているかを確認
*
**/
function certificateTable_Interview_updatetime( $pass_encrypt ) {

    $pdo = connectMysql(); // 4行目で読み込んだファイルの関数（ connectMysql() ）を実行
    
    //$stmt = $pdo -> prepare("SELECT id, pass_hash FROM mnsn_sheet_linebot_test ORDER BY id");
    $stmt = $pdo -> prepare("SELECT * FROM mnsn_sheet_linebot_test where :pass_hash = pass_hash ORDER BY id DESC");


    //bindValueメソッドでパラメータをセット
    $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); // PDO::以降で書き込むデータの型を指定する．だいたいINTかSTRかな
    $stmt->execute();
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC); //全件取得

    
    if( !isset($all[0]["pass_hash"]) ){
        throw new Exception('入力内容に誤りがあります 再度入力をお願い致します');
    }
    $update_time = $all[0]["update_time"];

    return $update_time;
}

// 暗号化用のコード
function value_Encrypted($value) {
    
    //秘密鍵・暗号かぎへのファイルパス
    /*小池クリニック用*/
    $keyPath_public = getenv('PATH_PUBLICKEY');

    //暗号化部分
    $key_public = file_get_contents($keyPath_public);

    if( empty($value) ){
        return $value;
    }else{
        // 結果が$crypted変数に入っている
        openssl_public_encrypt($value, $crypted, $key_public, OPENSSL_PKCS1_OAEP_PADDING);

        // $cryptedに入っているのはバイナリなのでbase64して表示する
        $encrypted = base64_encode($crypted); // 返り値はstring型

        return $encrypted;
    }

}
