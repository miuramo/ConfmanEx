<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affil extends Model
{
    use HasFactory;

    protected $fillable = ['before', 'matchrule', 'after', 'orderint', 'pids'];

    protected $casts = ['pids' => 'json'];

    public $timestamps = false;

    /**
     * 書誌情報の著者所属を、抽出する
     */
    public static function distill()
    {
        Affil::truncate();

        $cats = Category::pluck('id')->toArray();
        foreach ($cats as $catid) {
            // MailTemplateのmt_accept(1,2,3)から、採録された論文を取得する
            $papers = MailTemplate::mt_accept($catid);

            // 著者所属を抽出する
            foreach ($papers as $paper) {
                $authorlist_ary = $paper->authorlist_ary();
                foreach ($authorlist_ary as $uu) {
                    $affil = $uu[1];
                    if ($affil) {
                        // ・、，/で区切る
                        $affil = str_replace("、", "/", $affil);
                        $affil = str_replace(",", "/", $affil);
                        $affil = str_replace("，", "/", $affil);
                        $afary = explode("/", $affil);
                        $afary = array_map('trim', $afary);
                        foreach ($afary as $a) {
                            $after = Affil::after_kouho($a);
                            $obj = Affil::firstOrCreate(['before' => $a], ['after' => $after]);
                            // pidsを配列として取得し、新しいpaper.idを追加（重複を避ける）
                            $currentPids = $obj->pids ?? [];
                            $obj->pids = array_unique(array_merge($currentPids, [$paper->id]));
                            $obj->save();
                        }
                    }
                }
            }
        }
    }

    public static function after_kouho(string $str)
    {
        if (preg_match('|大学大学院|u', $str, $matches, PREG_OFFSET_CAPTURE)) {
            $str = substr($str, 0, $matches[0][1] + 3);
        }
        if (preg_match('|大学|u', $str, $matches, PREG_OFFSET_CAPTURE)) {
            $str = substr($str, 0, $matches[0][1] + 3);
        }
        $reps = [
            "株式会社" => "",
            "合同会社" => "",
            "\(株\)" => "",
            "（株）" => "",
            "　" => "",
            "高等専門学校" => "高専",
            "研究所" => "研",
        ];
        $pats = [
            "明石工業高等" => "明石高専",
            "首都大学東京" => "首都大",
            "東京大" => "東大",
            "京都大" => "京大",
            "北海道大" => "北大",
            "名古屋大" => "名大",
            "東京工業大" => "東工大",
            "東京農工大" => "東京農工大",
            "東京電機大" => "東京電機大",
            "大阪電気通信大" => "大阪電通大",
            "電気通信大" => "電通大",
            "多摩美術大" => "多摩美大",
            "JST" => "JST",
            "Adobe" => "Adobe",
            "ATR" => "ATR",
            "国際電気通信基礎" => "ATR",
            "東芝" => "東芝",
            "ソニーコンピュータサイエンス" => "ソニーCSL",
            "電通国際情報サービス" => "ISID",
            "Google" => "Google",
            "Microsoft Research$" => "MSR",
            "^Microsoft Resea.+ Asia" => "MSRA",
            "Massachusetts Institute" => "MIT",
            "MIT" => "MIT",
            "National Chiao Tung" => "NCTU",
            "Kochi University of Technology" => "高知工科大",
            "高知工科" => "高知工科大",
            "奈良先端" => "奈良先端大",
            "NAIST" => "奈良先端大",
            "北陸先端" => "北陸先端大",
            "JAIST" => "北陸先端大",
            "総合研究大学院" => "総研大",
            "NTTドコモ" => "NTTドコモ",
            "NTTアイティ" => "NTT-IT",
            "NTT" => "NTT",
            "日本電信電話" => "NTT",
            "NHK" => "NHK",
            "LINEヤフー" => "LINEヤフー",
            "楽天モバイル" => "楽天モバイル",
            "楽天" => "楽天",
            "NEC *ビッグローブ" => "NECビッグローブ",
            "NEC" => "NEC",
            "日本電気" => "NEC",
            "日本アイ・ビー・エム" => "日本IBM",
            "日本アイビーエム" => "日本IBM",
            "名古屋工業大" => "名工大",
            "大阪工業大" => "大阪工大",
            "はこだて未来" => "はこだて未来大",
            "お茶の水" => "お茶の水女子大",
            "お茶大" => "お茶の水女子大",
            "中京大" => "中京大",
            "九州大" => "九大",
            "九州工業大" => "九工大",
            "芝工大" => "芝浦工大",
            "京都産業" => "京産大",
            "静岡文化芸術大" => "静岡文芸大",
            "京都工芸" => "京都工繊大",
            "和歌山大" => "和歌山大",
            "岩手県立大" => "岩手県立大",
            "岩手大" => "岩手大",
            "大阪大" => "阪大",
            "慶應" => "慶大",
            "慶応" => "慶大",
            "Keio U" => "慶大",
            "早稲田" => "早大",
            "明治大" => "明治大",
            "日本大" => "日大",
            "産業技術総合研" => "産総研",
            "産業技術大学院" => "産業技術大",
            "情報学研" => "NII",
            "情報科学芸術" => "IAMAS",
            "情報通信研究機構" => "NICT",
            "情報処理学会" => "IPSJ",
            "日本学術振興会" => "JSPS",
            "科学技術振興機構" => "JST",
            "豊橋技術科学" => "豊橋技科大",
            "台湾大" => "台湾大",
            "ＪＳＴ" => "JST",
            "資生堂" => "資生堂",
            "日本写真印刷" => "NISSHA",
            "電力中央研" => "電中研",
            "沖電気" => "沖電気",
            "ニコン" => "ニコン",
            "循環器病" => "国循",
            "RWTH" => "RWTH",
            "Vestfold" => "HiVe",
            "MUTIENLIAO" => "MUTIENLIAO",
            "imaginaryShort" => "imaginaryShort",
        ];

        foreach ($reps as $o => $r) {
            // $o = Normalizer::normalize($o, Normalizer::FORM_C);
            // $r = Normalizer::normalize($r, Normalizer::FORM_C);

            $str = preg_replace('/' . $o . '/u', $r, $str);
        }

        if (preg_match('|高専|u', $str, $matches, PREG_OFFSET_CAPTURE)) {
            $str = substr($str, 0, $matches[0][1] + 6);
        }

        foreach ($pats as $o => $r) {
            // $o = Normalizer::normalize($o, Normalizer::FORM_C);
            // $r = Normalizer::normalize($r, Normalizer::FORM_C);
            if (strlen($o) > 0) {
                if (preg_match('|' . $o . '|u', $str)) {
                    return $r;
                }
            }
        }
        $str = preg_replace('/工業大/u', '工大', $str);
        $str = preg_replace('/技術大/u', '技大', $str);
        $str = preg_replace('/美術大/u', '美大', $str);
        $str = preg_replace('/高等学校/u', '高校', $str);
        $str = preg_replace('/^国立/u', '', $str);
        return $str; //preg_replace('/大学/u','大',$str);
    }
}
