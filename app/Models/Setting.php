<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasFactory;
    use FindByIdOrNameTrait, GetValueTrait;

    protected $fillable = [
        'name',
        'value',
        'isnumber',
        'isbool',
        'valid',
        'misc',
    ];


    /**
     * Settingの REVIEWER_MEMBER や PC_MEMBERをみて、自動でロールをわりあてる
     */
    public static function auto_role_member(){
        $sets = Setting::where("name","like","%_MEMBER")->where("valid",true)->get();
        foreach($sets as $set){
            $val = $set->value;
            if (strlen($val)<2) continue;
            // role name
            $role_name = strtolower(explode("_",$set->name)[0]);
            $role = Role::findByIdOrName($role_name);
            // | で区切る
            $ary = explode("|",$val);
            if (count($ary)<1) continue;
            foreach($ary as $name){
                $tmpu = User::where("name",$name)->first();
                if ($tmpu == null) continue;
                if (!$role->containsUser($tmpu->id)){ // ふくまれていなければ
                    $tmpu->roles()->syncWithoutDetaching($role);
                    dump("auto_role_member {$name} {$role->name}");
                }
            }
        }
    }


    public static function seeder()
    {
        Setting::firstOrCreate([
            'name' => "NAME_OF_META",
        ], [
            'value' => "メタ査読者",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "SKIP_BIBINFO",
        ], [
            'value' => '["keyword","etitle","eabst","ekeyword"]',
            'isnumber' => false,
            'isbool' => false,
        ]);
        // 
        Setting::firstOrCreate([
            'name' => "FILE_DESCRIPTIONS",
        ], [
            'value' => '{"pdf":"論文PDF","altpdf":"ティザー資料","img":"代表画像","video":"参考ビデオ","pptx":"PowerPoint(pptx)"}',
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "FILEPUT_DIR",
        ], [
            'value' => "z2024",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "PC_MEMBER",
        ], [
            'value' => "",
            'isnumber' => false,
            'isbool' => false,
            'valid' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "REVIEWER_MEMBER",
        ], [
            'value' => "",
            'isnumber' => false,
            'isbool' => false,
            'valid' => false,
        ]);
        $sets = Setting::where("name","like","%_MEMBER")->where("valid",true)->get();
        foreach($sets as $set){
            if (strlen($set->value)<1) {
                $set->valid = false;
                $set->misc = "（注意）氏 名を|で区切って設定しておくと、自動でROLE付与します。";
                $set->save();
            }
        }

        // 表彰状用JSON のダウンロードキー
        $temporal_key = Setting::findByIdOrName("CONFTITLE_YEAR", "value") . Str::random(10);
        Setting::firstOrCreate([
            'name' => "AWARDJSON_DLKEY",
        ], [
            'value' => $temporal_key,
            'misc' => "表彰状生成用JSON Download Key",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "LAST_QUEUEWORK_DATE",
        ], [
            'value' => "(TestQueueWork未実行)",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "TUTORIAL_URL",
        ], [
            'value' => "https://exconf.istlab.info/SSS_tutorial.mp4",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "CFP_LINKTEXT",
        ], [
            'value' => "論文募集 / Call for Paper に戻る",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "INTRO_VIDEO_URL",
        ], [
            'value' => "https://exconf.istlab.info/SSS_tutorial.mp4",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "CROP_YHWX",
        ], [
            'value' => "[80,500, 1100,-1]",
            'isnumber' => false,
            'isbool' => false,
            'misc' => '最後のXが負数だとセンタリング計算でXを求める',
        ]);
        Setting::firstOrCreate([
            'name' => "REPLACE_PUNCTUATION",
        ], [
            'value' => '{"。":"．","、":"，"}',
            'isnumber' => false,
            'isbool' => false,
            'misc' => '句読点。ReplaceKutenMiddlewareで使用する。valid=0で無効にできる。',
        ]);

        // Viewpoint::change_separator();

        Setting::firstOrCreate([
            'name' => "VOTING",
        ], [
            'value' => "false",
            'isnumber' => false,
            'isbool' => true,
        ]);
        Setting::firstOrCreate([
            'name' => "REDIRECT",
        ], [
            'value' => "/paper",
            'isnumber' => false,
            'isbool' => false,
            'misc' => '/paper/create | /paper | /vote',
        ]);

        Vote::init();
        VoteItem::init();

    }
}
