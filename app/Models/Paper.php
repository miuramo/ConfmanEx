<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use STS\ZipStream\Builder;
use STS\ZipStream\Models\File as ZipFile;
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
    protected $with = ['submits'];

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

    public static function mandatory_bibs()
    {
        $koumoku = [
            'title' => '和文タイトル',
            'abst' => '和文アブストラクト',
            'keyword' => '和文キーワード',
            'authorlist' => '和文著者名',
            'etitle' => '英文Title',
            'eabst' => '英文Abstract',
            'ekeyword' => '英文Keyword',
            'eauthorlist' => '英文Author(s)'
        ];
        $skip_bibinfo = Setting::findByIdOrName("SKIP_BIBINFO", "value");
        $skip_bibinfo = json_decode($skip_bibinfo);
        foreach ($skip_bibinfo as $key) {
            unset($koumoku[$key]);
        }
        return $koumoku;
    }

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
    // https://github.com/stechstudio/laravel-zipstream を使用
    public function addFilesToZipStream(Builder $zip, array $filetypes)
    {
        $count = 0;
        foreach ($filetypes as $ft) {
            $fti = "{$ft}_file_id"; // file_id for $ft (filetype)
            $file = File::find($this->{$fti});
            if ($file == null) continue;
            $zip->add(ZipFile::make($file->fullpath(), $this->id_03d() . "_{$ft}." . $file->extension()));
            $count++;
        }
        return $count;
    }
    // こちらも https://github.com/stechstudio/laravel-zipstream を使用
    public function addFilesToZip_ForPub(Builder $zip, array $filetypes, string $fn_prefix, string $fn)
    {
        $count = 0;
        foreach ($filetypes as $ft) {
            $fti = "{$ft}_file_id"; // file_id for $ft (filetype)
            $file = File::find($this->{$fti});
            if ($file == null) continue;
            $zip->add(ZipFile::make($file->fullpath(), $fn_prefix. $fn . "." . $file->extension()));
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
    //   ->syncWithoutDetaching(5);　で、Contact.id = 5 を追加する。反対はdetach,配列も可。
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
    public function img_file()
    {
        return $this->belongsTo(File::class, 'img_file_id');
    }
    public function video_file()
    {
        return $this->belongsTo(File::class, 'video_file_id');
    }
    public function altpdf_file()
    {
        return $this->belongsTo(File::class, 'altpdf_file_id');
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
     * 採択済みのBooth ID
     */
    public function boothes_accepted()
    {
        $bs = [];
        foreach ($this->submits as $sub) {
            if ($sub->accept->judge > 0) {
                $bs[] = $sub->booth;
            }
        }
        return implode(" / ", $bs);
    }

    /**
     * この論文の査読結果のトークンを生成（著者がみえる査読結果）
     */
    public function token()
    {
        return sha1($this->id . $this->user_id . $this->paper_id . $this->category_id . $this->title);
    }

    /**
     * getAuthorType
     */
    public function getAuthorType(): int
    {
        if (Auth::guest()) return -1; // not logged in
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
            DB::transaction(function () use ($e) {
                $con = Contact::firstOrCreate([
                    'email' => $e,
                ]);
                if ($con->infoprovider == null) {
                    $con->infoprovider = $this->owner;
                    $con->save();
                }
                $this->contacts()->syncWithoutDetaching($con->id);
            });
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
        $this->contacts()->syncWithoutDetaching($con->id);
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

    public function get_mail_to_cc()
    {
        $cclist = [];
        $bcclist = [];
        foreach ($this->contacts as $con) {
            $cclist[] = $con->email;
        }
        if ($this->bcc_contactemails != null) {
            $bccs = explode("\n", trim($this->bcc_contactemails));
            foreach ($bccs as $bcc) {
                $bcclist[] = $bcc;
            }
        }
        return ["to" => $this->paperowner->email, "cc" => $cclist, "bcc" => $bcclist];
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

    /** Contactから推測される範囲での、共著者UIDリストを返す */
    // public function contact_coauthors()
    // {
    //     $emails = $this->contacts->pluck('email')->toArray();
    //     return User::whereIn('email', $emails)->get()->pluck('id')->toArray();
    // }
    /**
     * このPaperと、引数pidで与えられた論文との、contactsの重複があるか調べる。
     */
    public function hasSharedContacts(int $pid)
    {
        $thispid = $this->id;
        $contacts = DB::select("select contact_id from paper_contact where paper_id in ({$pid} , {$thispid}) order by contact_id");
        $chkary = [];
        foreach ($contacts as $colary) {
            if (isset($chkary[$colary->contact_id])) return true;
            $chkary[$colary->contact_id] = 1;
        }
        return false;
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

    public function softdelete_me()
    {
        $this->delete();
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
        if ($this->pdf_file->deleted) {
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
        $koumoku = Paper::mandatory_bibs();
        // 設定されていないものがあれば、error配列として返す。
        $errors = [];
        foreach ($koumoku as $key => $expr) {
            if ($this->{$key} == null || strlen($this->{$key}) < 2) {
                $errors[$key] = "書誌情報の設定から、" . $expr . " を入力してください。";
            }
        }

        // 著者名(所属) のチェック
        foreach ($koumoku as $key => $expr) {
            if ($key == "authorlist" || $key == "eauthorlist") {
                $ret = $this->authorlist_check($key);
                if (!$ret) {
                    $errors[$key] = ($key == "authorlist" ? "和文著者名(所属)" : "英文Authors(所属)") . " の書式が正しくありません。";
                }
            }
        }
        return $errors;
    }

    public function bibinfo_error()
    {
        $errors = $this->validateBibinfo();
        return implode("\n", $errors);
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
        if ($this->locked) {
            // info("note: paper is locked. SKIPPING.");
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

    public function demo_ifaccepted()
    {
        $demoenqitem = EnqueteItem::where("name", "demoifaccepted")->first();
        if ($demoenqitem != null) {
            $demoenqitemid = $demoenqitem->id;
            $ans = $this->enqans->where("enquete_item_id", $demoenqitemid)->first();
            if ($ans != null && $ans->valuestr == "はい") {
                return true;
            }
        }
        return false;
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

    /**
     * 著者名(所属) のチェック
     */
    public function authorlist_check($field = "authorlist")
    {
        $src = $this->{$field};
        $src = str_replace("（", "(", $src);
        $src = str_replace("）", ")", $src);
        $lines = explode("\n", $src);
        $lines = array_map("trim", $lines);
        if (count($lines) == 0) return true;
        $pattern = '/^([\p{Hiragana}\p{Katakana}\p{Han}\w\-,.]+(?:\s[\p{Hiragana}\p{Katakana}\p{Han}\w\-,.]+)*)\s*\([^\)]+\)$/u';
        foreach ($lines as $line) {
            if (!preg_match($pattern, $line)) {
                return false;
            }
            // もし、()がない場合、エラー
            if (strpos($line, "(") === false && strpos($line, ")") === false) {
                return false;
            }
        }
        return true;
    }

    // 著者名と所属のパース結果を配列で返す。英文所属は引数にeauthorlist を指定する。
    public function authorlist_ary($field = "authorlist", bool $use_short = false)
    {
        $ret = [];
        // まず、カッコをおきかえる
        if (!isset($this->{$field})) return $ret; // 著者名と所属が設定されてない
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
            if ($use_short) $ary[1] = $this->apply_affil_fix($ary[1]);
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
    public function bibinfo(bool $use_short = false)
    {
        $ret = [];
        $ret['title'] = $this->title;
        $ret['authors'] = [];
        $ret['affils'] = [];
        foreach ($this->authorlist_ary() as $uu) {
            $ret['authors'][] = $uu[0];
            if (!isset($uu[1])) $fixed_affil = "未設定";
            else
            $fixed_affil = $uu[1];

            if ($use_short) $fixed_affil = $this->apply_affil_fix($fixed_affil);

            $ret['affils'][] = $fixed_affil;
        }
        return $ret;
    }
    public function apply_affil_fix($affil)
    {
        $affil = str_replace("、", "/", $affil);
        $affil = str_replace(",", "/", $affil);
        $affil = str_replace("，", "/", $affil);
        $afary = explode("/", $affil);
        $afary = array_map('trim', $afary);

        $ret = [];
        foreach ($afary as $af){
            $obj = Affil::where('before', $af)->first();
            if ($obj != null){
                $ret[] = $obj->after;
            } else {
                $ret[] = $af;
            }
        }
        return implode("/", $ret);
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

    /**
     * いずれかのカテゴリで、採択されているならtrue
     * ただし、著者に査読結果が返っている場合のみ返す。それ以外はfalse
     */
    public function is_accepted_in_any_category()
    {
        $revreturn = Category::select('status__revreturn_on', 'id')
            ->where('id', $this->category_id)
            ->get()
            ->pluck('status__revreturn_on', 'id')
            ->toArray();
        if ($revreturn[$this->category_id] == 0) return false;
        $subs = $this->submits;
        foreach ($subs as $sub) {
            if ($sub->accept->judge > 0) return true;
        }
        return false;
    }
    /**
     * 著者が、当初のフォームでファイルアップロードできるならtrue
     * 投稿時はtrue
     * 査読開始後はfalse
     * カメラレディ投稿期間は、採択者のみtrue
     * 予稿集編集準備期間は、false
     */
    public function can_upload_files()
    {
        $cat = Category::find($this->category_id);
        if ($cat->status__revedit_on == 0) return true; // 投稿時（査読開始前）
        else if ($cat->status__revreturn_on == 0) return false; // 査読中はfalse
        else if ($cat->status__revreturn_on == 1) { // 査読開始後で、結果開示前
            if ($this->locked) return false; // カメラレディ投稿期間が過ぎて、ロックされているならアップロード不可
            else {
                if ($this->is_accepted_in_any_category()) return true; // カメラレディ投稿期間のあいだ、採択者はアップロードできる
                else return false; // 採択者以外はアップロード不可
            }
        }
        return false; // ここは使わない
    }

    /**
     * このファイルの予稿集収録をとりやめる（参照をはずす）
     */
    public function file_abandon(int $fileid)
    {
        if ($this->pdf_file_id == $fileid) {
            $this->pdf_file_id = null;
        }
        if ($this->img_file_id == $fileid) {
            $this->img_file_id = null;
        }
        if ($this->video_file_id == $fileid) {
            $this->video_file_id = null;
        }
        if ($this->altpdf_file_id == $fileid) {
            $this->altpdf_file_id = null;
        }
        $this->save();
    }
}
