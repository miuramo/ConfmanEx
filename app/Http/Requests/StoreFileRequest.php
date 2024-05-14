<?php

namespace App\Http\Requests;

use App\Jobs\PdfJob;
use App\Models\Category;
use App\Models\File;
use App\Models\Paper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * ファイルを格納し、PDFならページ枚数を取得する。また、Jobでサムネイル画像を生成し、pdftotextを実行する。
     */
    public function storeFile(): object
    {
        $tmp = $this->file("file");
        $hashname = sprintf("%03d", $this->input("paper_id"))."_".Auth::user()->id."_".$tmp->hashName();

        // フォルダがなければ作る
        File::mkdir_ifnot(storage_path(File::apf()));

        $tmp->storeAs(File::pf(), $hashname);
        $file = new File();
        $uid = $file->user_id = Auth::user()->id;
        $pid = $file->paper_id = $this->input("paper_id");

        if (Paper::getAT($uid, $pid) != 1) {
            return redirect()->route('paper.edit', ['paper' => $pid])->with('feedback.error', "投稿者以外はアップロードできません。");
        }
        $file->fname = $hashname; 
        $fullpath = storage_path(File::apf() . '/' . $hashname);
        $file->key = shell_exec("md5sum {$fullpath}");
        $file->key = substr($file->key, 0, 32);
        $file->mime = trim(shell_exec("file --mime-type -b {$fullpath}")); 
        $file->origname = $tmp->getClientOriginalName();
        $file->save(); // 一回目のsave
        // get pdf page num
        if ($file->mime == "application/pdf") {
            // ページ番号を取得
            $pdfinfo = trim(shell_exec("pdfinfo {$fullpath}"));
            $ary = explode("\n", $pdfinfo);
            $pnum = -1;
            foreach ($ary as $n => $p) {
                if (preg_match('/^Pages:[ ]+(\d+)/', $p, $match)) {
                    $pnum = $match[1];
                }
            }
            $file->pagenum = $pnum;
            $file->save(); // 2回目のsave

            // 受け入れ期間をチェックする
            $paper = Paper::find($file->paper_id);
            $cat = Category::find($paper->category_id);
            if ($cat->is_accept_pdf()){ // 受け入れ開始日〜終了日のあいだなら
                // すでにPDFがあるか？
                if ($paper->pdf_file_id != null){
                    $old_pdf_file = File::find($paper->pdf_file_id);
                    if (!$old_pdf_file->locked){ // ロックされていなければ
                        info("old file not locked, delete and replace");
                        $old_pdf_file->deleted = true; // 古いファイルに削除フラグをつける
                        $old_pdf_file->save();
                        $paper->pdf_file_id = $file->id; //差し替える
                        $paper->save();
                    } else { // ロックされていれば Pending
                        $file->pending = true;
                        $file->save();
                    }
                } else {
                    $paper->pdf_file_id = $file->id; //差し替える
                    $paper->save();
                }
            } else {
                if ($cat->pdf_accept_revise){ // 受け入れ最終日を過ぎていても、Pendingにするか？
                    $file->pending = true;
                } else {
                    $file->deleted = true;
                    $file->valid = false;
                }
                $file->save();
            }
            // 1ページ目のサムネをつくる
            shell_exec("pdftoppm -png -singlefile {$fullpath} " . storage_path(File::apf() . '/' . substr($hashname, 0, -4)));
            // 残りのタスク
            PdfJob::dispatch($file);

        }
        return redirect()->route('paper.edit', ['paper' => $pid])->with('feedback.success', "ファイルをアップロードしました。");
    }
}
