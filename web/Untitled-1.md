次のプログラムでエラー（「PHP Notice:  Array to string conversion」）がでる．対処方法を教えて．

```php
$pdo = connectMysql(); // DBとの接続開始
$stmt = $pdo->prepare("SELECT * FROM mnsn_sheet where :pass_hash = pass_hash ORDER BY id DESC");
$stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
$stmt->execute();

$job_sleepQuality = $all[0]["job_sleepQuality"];//睡眠の質_仕事の日
```