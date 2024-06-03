<?php
require ('./chatapi.php');//ファイルの読み込み

// テキストファイルのパス
$file_path = 'textfile.txt';

// ファイルの内容を読み込み、変数に格納
$file_contents = file_get_contents($file_path);

// echo "You entered: " . $file_contents . "\n\n";
$text_gpt = call_chatGPT($file_contents);

echo $text_gpt . "\n";


// if ($argc > 1) {
//     $input = $argv[1];
//     echo "You entered: " . $input . "\n";

//     $text_gpt = call_chatGPT($input);
//     echo $text_gpt . "\n";
// } else {
//     echo "Please provide an input argument.\n";
// }

?>
