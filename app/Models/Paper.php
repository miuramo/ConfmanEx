<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Class Paper
 *
 * @property int $id
 * @property int $category_id
 * @property string|null $title
 * @property string|null $contactemails
 * @property string|null $etitle
 * @property string|null $kwd
 * @property string|null $ekwd
 * @property string|null $abst
 * @property string|null $eabst
 * @property string|null $zipcode
 * @property string|null $address
 * @property string|null $telnum
 * @property string|null $faxnum
 * @property int $registid
 * @property string|null $discussagenda
 * @property bool $nopublishcatalog
 * @property string|null $remarks
 * @property int $numauthor
 * @property bool $authorchecked
 * @property bool $demoifaccepted
 * @property bool $demoifrejected
 * @property bool $donotwantshortaccept
 * @property int $finalizecount
 * @property Carbon|null $created_at
 * @property Carbon|null $modified_at
 * @property int $owner
 * @property Carbon|null $deleted_at
 *
 * @package App\Models
 */
class Paper extends Model
{
    use HasFactory;
    use SoftDeletes;
    // protected $table = 'papers';
    // public $timestamps = false;

    protected $casts = [
        'category_id' => 'int',
        'owner' => 'int',
        'registid' => 'int',
        'demoifaccepted' => 'bool',
        'nopublishcatalog' => 'bool',
        'pdf_file_id' => 'int',
        'locked' => 'bool',
        'status' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        // 'deleted' => 'bool',
        'maydirty' => 'json',
    ];

    protected $fillable = [
        'category_id',
        'owner',
        'authorlist',
        'title',
        'abst',
        'keyword',
        'etitle',
        'eabst',
        'ekeyword',
        'maydirty',
        'contactemails',
        'demoifaccepted',
        'nopublishcatalog',
        'remarks',
        'pdf_file_id',
        'zipcode',
        'address',
        'telnum',
        'registid',
        'locked',
        'history',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function addFilesToZip(ZipArchive $zip, array $filetypes)
    {
        $count = 0;
        foreach ($filetypes as $ft) {
            $fti = "{$ft}_file_id"; // file_id for $ft (filetype)
            $file = File::find($this->{$fti});
            if ($file == null) continue;
            $zip->addFile($file->fullpath(), $this->id_03d() . "_{$ft}." . $file->extension());
            $count++;
        }
        return $count;
    }
    public function addFilesToZip_ForPub(ZipArchive $zip, array $filetypes, string $fn)
    {
        $count = 0;
        foreach ($filetypes as $ft) {
            $fti = "{$ft}_file_id"; // file_id for $ft (filetype)
            $file = File::find($this->{$fti});
            if ($file == null) continue;
            $zip->addFile($file->fullpath(), $fn . "." . $file->extension());
            $count++;
        }
        return $count;
    }


    public function files()
    {
        return $this->hasMany(File::class, 'paper_id')->where('valid', 1)->where('deleted', 0);
    }

    public function contacts()
    {
        // $table_fields = Schema::getColumnListing('paper_contact');
        return $this->belongsToMany(Contact::class, 'paper_contact'); // ->withPivot($table_fields)->using(PapersUser::class);
    }
    // $p = Paper::find(1)
    //   ->contacts()
    //   ->attach(5);　で、Contact.id = 5 を追加する。反対はdetach,配列も可。
    // syncで、削除＆追加をする。
    public function paperowner()
    {
        return $this->belongsTo(User::class, 'owner');
    }

    public function submits()
    {
        return $this->hasMany(Submit::class);
    }

    public function id_03d()
    {
        return sprintf("%03d", $this->id);
    }
    public function pdf_file()
    {
        return $this->belongsTo(File::class, 'pdf_file_id');
    }
    public function enqans()
    {
        return $this->hasMany(EnqueteAnswer::class, 'paper_id');
    }
    public function enqansByItemId($enq_itm_id)
    {
        return null;
    }

    /**
     * getAuthorType
     */
    public function getAuthorType(): int
    {
        if ($this->owner == Auth::user()->id) {
            return 1; // main author
        } else if ($this->isCoAuthorEmail(Auth::user()->email)) {
            return 2; // coauthor
        }
        return -1;
    }
    public static function getAT($uid, $pid): int
    {
        $p = Paper::find($pid);
        $u = User::find($uid);
        if ($p->owner == $uid) {
            return 1; // main author
        } else if ($p->isCoAuthorEmail($u->email)) {
            return 2; // coauthor
        }
        return -1;
    }

    /**
     * contactemails から、Contactを作成する
     * ここは、contactemailsが変更されたら、かならず実行する。
     *
     * （投稿者アカウントのメールが変更されたら、どうする？＞基本的に、すべてのPaperについて、ここを実行すればよいが、もうすこし省力化できるかも）
     */
    public function updateContacts()
    {
        $this->contacts()->detach(); // 既存のはすべて削除する
        //contactemails から、Contactを作成する（重複はtableのunique制約で保証される）
        $ema = explode("\n", trim($this->contactemails));
        foreach ($ema as $e) {
            $con = Contact::firstOrCreate([
                'email' => $e,
            ]);
            if ($con->infoprovider == null) {
                $con->infoprovider = $this->owner;
                $con->save();
            }
            $this->contacts()->attach($con->id);
        }
        $this->refresh();
    }
    // 主にテスト用。現在のContactリレーションからcontactemailsを逆に生成する。
    public function updateContactemailsFromContacts()
    {
        // ここで、いったん$this->contactsを再読み込みする必要があるらしい。
        $this->refresh();
        $cmlist = [];
        foreach ($this->contacts as $c) {
            $cmlist[] = $c->email;
        }
        $this->contactemails = implode("\n", $cmlist);
        $this->save();
        return $cmlist;
    }

    // contactemailsから抜く
    public function remove_contact(Contact $con)
    {
        $this->contacts()->detach($con->id);
        // ここで、いったんcontactsを再読み込みする必要があるらしい。
        $this->updateContactemailsFromContacts();
        $this->refresh();
    }
    // contactemailsに足す
    public function add_contact(Contact $con)
    {
        $this->contacts()->attach($con->id);
        $this->updateContactemailsFromContacts();
        $this->refresh();
    }
    public function add_contactemail(string $em)
    {
        $ema = explode("\n", trim($this->contactemails));
        $ema[] = $em;
        $this->contactemails = implode("\n", $ema);
        $this->save();
        $this->updateContacts();
    }
    // public function categories()
    // {
    //     $tbl = 'papers_categories';
    //     $table_fields = Schema::getColumnListing($tbl);
    //     return $this->belongsToMany(Category::class, $tbl, 'paper_id', 'category_id')->withPivot($table_fields)->using(PapersCategory::class);

    // }

    public function get_mail_to_cc()
    {
        $cclist = [];
        foreach ($this->contacts as $con) {
            $cclist[] = $con->email;
        }
        return ["to" => $this->paperowner->email, "cc" => $cclist];
    }

    /**
     * 共著者ならtrue
     */
    public function isCoAuthorEmail(string $em): bool
    {
        // 以前は、地道にやっていたが、時間がかかるので
        // $ema = explode("\n", trim($em));
        // $ema = array_map("trim", $ema);
        // foreach ($ema as $e) {
        //     if ($e == $em) return true;
        // }
        // return false;
        // Contactのリレーションを利用する方法にする
        try {
            $contact = Contact::where('email', $em)->firstOrFail();
            // if ($contact == null) return false; // なくてもよさそう
            return $this->contacts()->where("contact_id", $contact->id)->exists();
        } catch (ModelNotFoundException $ex) {
            return false;
        }
    }

    // public function submits()
    // {
    //     return $this->hasMany(Submit::class);
    // }

    public function delete_me()
    {
        $this->contacts()->detach(); //belongsToManyリレーションを削除する
        Paper::destroy($this->id);
    }

    /**
     * 投稿ファイルのバリデーション（注：投稿可能期間のみ有効）
     */
    public function validateFiles()
    {
        $checkary = [];
        $checkary['pdf'] = [];
        $checkary['altpdf'] = [];
        $checkary['video'] = [];
        $checkary['img'] = [];
        $checkary['pptx'] = [];

        $errorary = [];
        $cat = Category::find($this->category_id);
        if ($cat == null) return []; //通常はありえないが、テストを通すため...
        foreach ($this->files as $file) {
            if ($file->mime == "application/pdf") {

                if ($file->deleted) continue;
                if ($file->pending) continue;
                if ($cat->accept_altpdf > 0 && $this->between($cat->altpdf_page_min, $file->pagenum, $cat->altpdf_page_max)) {
                    $checkary['altpdf'][] = $file->id;
                } else if ($this->between($cat->pdf_page_min, $file->pagenum, $cat->pdf_page_max)) {
                    $checkary['pdf'][] = $file->id;
                } else {
                    $errorary[] = "PDFのページ数を確認してください。";
                }
                continue;
            }
            if ($file->mime == "image/png" || $file->mime == "image/jpeg" || $file->mime == "image/jpg") {
                $checkary['img'][] = $file->id;
                continue;
            }
            if (strpos($file->mime, "video") === 0) {
                $checkary['video'][] = $file->id;
            }
            if ($file->mime == "application/vnd.openxmlformats-officedocument.presentationml.presentation") {
                $checkary['pptx'][] = $file->id;
            }
        }
        $maxnum = [];
        $minnum = [];
        $minnum['pdf'] = $maxnum['pdf'] = 1; //PDFは普通は必須、min=max=1
        $minnum['altpdf'] = $maxnum['altpdf'] = isset($cat) ? $cat->accept_altpdf : 1;
        if ($cat->accept_altpdf == 2) { //2はオプション。0or1
            $minnum['altpdf'] = 0;
            $maxnum['altpdf'] = 1;
        }
        $minnum['img'] = $maxnum['img'] = isset($cat) ? $cat->accept_img : 0;
        if ($cat->accept_img == 2) { //2はオプション。0or1
            $minnum['img'] = 0;
            $maxnum['img'] = 1;
        }
        $minnum['video'] = $maxnum['video'] = isset($cat) ? $cat->accept_video : 0;
        if ($cat->accept_video == 2) { //2はオプション。0or1
            $minnum['video'] = 0;
            $maxnum['video'] = 1;
        }
        $minnum['pptx'] = $maxnum['pptx'] = isset($cat) ? $cat->accept_pptx : 0;
        if ($cat->accept_pptx == 2) { //2はオプション。0or1
            $minnum['pptx'] = 0;
            $maxnum['pptx'] = 1;
        }
        // ['pdf'=>'論文PDF', 'altpdf'=>'ティザー資料', 'img'=>'代表画像', 'video'=>'参考ビデオ', 'pptx'=>'PowerPoint(pptx)']
        $file_desc = Setting::findByIdOrName("FILE_DESCRIPTIONS", "value");
        $file_desc = json_decode($file_desc);

        foreach ($file_desc as $ft => $ffname) {
            if (!$this->between($minnum[$ft], count($checkary[$ft]), $maxnum[$ft])) {
                if ($minnum[$ft] == 1 && $maxnum[$ft] == 1) {
                    $errorary[] = "{$ffname}は必須です（1つのファイルのみ受け付けます）。";
                } else {
                    if ($minnum[$ft] == 0) {
                        $errorary[] = "{$ffname}は {$maxnum[$ft]}個以下にしてください。";
                    } else {
                        $errorary[] = "{$ffname}は {$minnum[$ft]}個〜{$maxnum[$ft]}個にしてください。";
                    }
                }
            }
        }
        if (count($errorary) > 0) return $errorary;

        // ALL OKなら、paperにセットする
        $this->pdf_file_id = $checkary['pdf'][0];
        $this->img_file_id = isset($checkary['img'][0]) ? $checkary['img'][0] : null;
        $this->video_file_id = isset($checkary['video'][0]) ? $checkary['video'][0] : null;
        $this->altpdf_file_id = isset($checkary['altpdf'][0]) ? $checkary['altpdf'][0] : null;
        $this->save();
        return [];
    }

    /**
     * PDFファイルがなければ true (@MailTemplate mt_nofile)
     */
    public function check_nofile()
    {
        if ($this->pdf_file_id == null) return true;
        // もし、pdf_file_id が無効なら、もう一度validateする。
        if ($this->pdf_file()->deleted) {
            $this->validateFiles();
            $this->refresh(); // validate reload
            if ($this->pdf_file()->deleted) {
                return true;
            }
        }
        return false;
    }

    /**
     * 書誌情報のチェック。足りないものを配列で返す。
     */
    public function validateBibinfo()
    {
        // 何が必須か？は、全部から、SKIP_BIBINFOを引く。
        // $manda = ["title", "etitle", "authorlist", "eauthorlist", "abst", "eabst", "keyword", "ekeyword"];
        // 書誌情報の設定項目
        $koumoku = ['title' => '和文タイトル', 'abst' => '和文アブストラクト', 
        'keyword' => '和文キーワード', 'authorlist' => '和文著者名', 
        'etitle' => '英文Title', 'eabst' => '英文Abstract', 
        'ekeyword' => '英文Keyword', 'eauthorlist' => '英文Author(s)'];
        $skip_bibinfo = Setting::findByIdOrName("SKIP_BIBINFO", "value");
        $skip_bibinfo = json_decode($skip_bibinfo);
        foreach ($skip_bibinfo as $key) {
            unset($koumoku[$key]);
        }
        // 設定されていないものがあれば、error配列として返す。
        $errors = [];
        foreach($koumoku as $key=>$expr){
            if ($this->{$key} == null || strlen($this->{$key}) < 2){
                $errors[$key] = "書誌情報の設定から、".$expr." を入力してください。";
            }
        }
        return $errors;
    }


    public function between(int $s, int $x, int $e)
    {
        return ($s <= $x && $x <= $e);
    }

    /**
     * PdfJob => File(2ページ以上のとき) =>　ここでタイトル設定・更新
     */
    public function extractTitleAndAuthors(string $text)
    {
        // もし、カテゴリの投稿受付設定 extract_title が　0　だったら、実行しない。
        $cat = Category::find($this->category_id);
        if (!$cat->extract_title) {
            // info("note: category->extract_title is 0. SKIPPING.");
            return;
        }

        // 下処理として、改行をとりのぞく
        if (function_exists("mb_strpos")) {
            $nocr_text = str_replace(["\r", "\n"], "", $text);
            $first_author_name = trim($this->paperowner->name);
            $pos = mb_strpos($nocr_text, $first_author_name);
            if ($pos !== false) {
                $title_candidate = mb_substr($nocr_text, 0, $pos);
            } else {
                // みつからなかったので、
                $title_candidate = mb_substr($nocr_text, 0, 120);
            }
        } else {
            $nocr_text = str_replace("\n", "", $text);
            $first_author_name = $this->paperowner->name;
            $pos = strpos($nocr_text, $first_author_name);
            if ($pos > -1) {
                $title_candidate = substr($nocr_text, 0, $pos);
            } else {
                // みつからなかったので、
                $title_candidate = substr($nocr_text, 0, 120) . "...";
            }
        }
        // SKIP_HEAD_n を適用する。（先頭にあれば、削除する
        $sets = Setting::where("name", "like", "SKIP_HEAD_%")->where("valid", true)->get();
        foreach ($sets as $set) {
            $title_candidate = str_replace($set->value, "", $title_candidate);
        }
        $this->title = $title_candidate;
        $this->save();
    }

    public function validate_accepted()
    {
        //ファイルエラー
        $fileerrors = $this->validateFiles();
        // アンケートエラー
        $enqerrors = Enquete::validateEnquetes($this);

        $this->accepted = (count($fileerrors) == 0 && count($enqerrors) == 0);
        $this->save();
    }

    // 著者名と所属のパース結果を配列で返す。英文所属は引数にeauthorlist を指定する。
    public function authorlist_ary($field = "authorlist")
    {
        $ret = [];
        // まず、カッコをおきかえる
        $lines = explode("\n", $this->{$field});
        $lines = array_map("trim", $lines);
        foreach ($lines as $line) {
            $line = str_replace("（", "(", $line);
            $line = str_replace("）", ")", $line);
            $line = str_replace("(", "\t", $line);
            $line = str_replace(")", "\t", $line);
            $ary = explode("\t", trim($line));
            $ary = array_map("trim", $ary);
            // ここまでで、ary[0]には氏名、ary[1]には所属がはいる
            $ret[] = $ary;
        }
        return $ret;
    }
    public function getAllAffils($idx = 1, $prefix = "")
    {
        $ary = $this->authorlist_ary($prefix . "authorlist");
        $ret = [];
        foreach ($ary as $a) {
            if (isset($a[$idx])) $ret[] = $a[$idx];
        }
        return implode(";;", $ret);
    }

    /**
     * 配列をかえす
     */
    public function bibinfo()
    {
        $ret = [];
        $ret['title'] = $this->title;
        $ret['authors'] = [];
        $ret['affils'] = [];
        foreach ($this->authorlist_ary() as $uu) {
            $ret['authors'][] = $uu[0];
            $ret['affils'][] = (isset($uu[1])) ? $uu[1] : "未設定";
        }
        return $ret;
    }

    /**
     * 著者名、文字列をかえす
     * abbr 連続する著者の所属を省略する
     */
    public function bibauthors(bool $abbr = false)
    {
        $name = [];
        $affil = [];
        $count = 0;
        foreach ($this->authorlist_ary() as $uu) {
            $name[] = $uu[0];
            $affil[] = (isset($uu[1])) ? $uu[1] : ""; //そもそも所属がなければ、空にせざるを得ない
            $count++;
        }
        if ($abbr) {
            for ($i = 0; $i < $count; $i++) {
                if ($i < ($count - 1) && $affil[$i] == $affil[$i + 1]) {
                    $affil[$i] = ""; // 重複しており、最後より1つ前なら、省略するため空にする。
                }
            }
        }
        $ret = [];
        for ($i = 0; $i < $count; $i++) {
            if (strlen($affil[$i]) > 0) { // 所属が空じゃなければ、（）で表示する
                $ret[] = $name[$i] . " (" . $affil[$i] . ")";
            } else {
                $ret[] = $name[$i];
            }
        }
        return implode("，", $ret); // カンマでつなげて出力
    }

    public function writeHintFile()
    {
        $txt = "pdf_file_id\t" . $this->pdf_file_id . "\n";
        $txt .= "title\t" . $this->title . "\n";
        $txt .= "titletail\t" . $this->titletail . "\n";
        $txt .= "authorhead\t" . $this->authorhead . "\n";
        $txt .= "updated\t" . date("Y-m-d_H:i:s") . "\n";

        $this->pdf_file->writeHintFile($txt);
    }

    public function pdftotext()
    {
        if ($this->pdf_file)
            return $this->pdf_file->getPdfText();
        return "(pdftotext準備中)";
    }
    public function title_candidate()
    {
        $title = str_replace("\n", "", $this->pdftotext());
        // owner name
        $owner = $this->paperowner->name;
        $pos1 = mb_strpos($title, $owner);
        if ($pos1 > -1) {
            $title = mb_substr($title, 0, $pos1);
        }
        return $title;
    }
}
