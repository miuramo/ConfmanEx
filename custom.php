<?php
$url = "https://exconf.istlab.info/awards/json_booth_title_author/xxxxxx";
$json = file_get_contents($url);
$ary = json_decode($json, true);

// データの確認
var_dump($ary);
exit();

$last_session_id = -1;
$in_session_num = 1; // セッション内での通し番号
foreach ($ary as $booth => $bib) {

    if ($bib['category'] == 1) { // 登壇なら　1-1, 1-2 のようにセッション番号-セッション内での通し番号にする
        $cur_session_id = $bib['session'];
        if ($last_session_id != $cur_session_id) {
            $in_session_num = 1;
        }
        echo $cur_session_id . "-" . $in_session_num;
        $in_session_num++;
        $last_session_id = $cur_session_id;
    } else { // デモポスターの場合、システムで設定したP-数字を表示する
        echo $booth;
    }
    echo " ";
    echo $bib['title'];
    echo "\n";
    foreach ($bib['authors'] as $n => $u) {
        echo "    ";
        echo $u;
        echo " ";
        echo "(" . $bib['affils'][$n] . ")";
        echo "\n";
    }
}
