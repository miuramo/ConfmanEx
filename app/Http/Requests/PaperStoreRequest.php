<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Confirm;
use App\Models\Paper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaperStoreRequest extends FormRequest
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
        $kakunin = Confirm::select('name', 'mes')->where('valid', 1)
            ->whereIn('grp', [1, 2])->get()->pluck('grp', 'name')->toArray();
        foreach ($kakunin as $nm => $grp) {
            $kakunin[$nm] = 'required';
        }
        $kakunin['action'] = 'required|integer';
        return $kakunin;
    }
    public function messages(): array
    {
        $kakunin = Confirm::select('name', 'mes')->where('valid', 1)
            ->whereIn('grp', [1, 2])->get()->pluck('grp', 'name')->toArray();
        foreach ($kakunin as $nm => $grp) {
            $kakunin[$nm] = '↓ 確認し、チェックをいれてください。';
        }
        return $kakunin;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $emValidator = $this->validate_em();
            if ($emValidator == null) {
                $validator->errors()->add("contactemails", "投稿連絡用メールアドレスは1件以上" . env('CONTACTEMAILS_MAX', 5) . "件以内で入力してください。");
            } else if ($emValidator->fails()) {
                // エラーがある場合の処理
                $ary = [];
                $ems = $this->emlist();
                $errors = $emValidator->errors()->all();
                foreach ($errors as $error) {
                    // 数字をとりだす The ema.1 field must be a valid email address. => 1
                    if (preg_match('/\d+/', $error, $matches)) {
                        // 最初にマッチした数字を取得
                        $number = $matches[0];
                        $ary[] = $ems[$number];
                    }
                }
                $mes = implode("】【", $ary);
                $validator->errors()->add("contactemails", "【" . $mes . "】は有効なメールアドレスではありません。");
                // $validator->errors()->merge($emValidator->errors());
            }
        });
    }

    public function emlist()
    {
        $em = $this->input("contactemails");
        // メールアドレスのバリデーション
        $ema = explode("\n", trim($em));
        $ema = array_map("trim", $ema);
        $ema = array_filter($ema, function ($v) {
            return $v !== "";
        });
        return $ema;
    }

    public function validate_em(): null|\Illuminate\Validation\Validator
    {
        $ema = $this->emlist();

        if (count($ema) == 0 || count($ema) > env('CONTACTEMAILS_MAX', 5)) return null;
        $validator = Validator::make(["ema" => $ema, "contactemails" => $this->input("contactemails")], [
            'ema.*' => 'required|email|max:255',
            'contactemails' => 'required',
        ], ['contactemails' => "投稿連絡用メールアドレスは1件以上" . env('CONTACTEMAILS_MAX', 5) . "件以内で入力してください。"]);
        return $validator;
    }
    // papers.create からのPost
    public function shori(): object
    {
        $cat = Category::find($this->input("action"));
        if (!$cat->isnotUpperLimit()) {
            return redirect()->route('paper.create')->with('feedback.error', "申し訳ありませんが、{$cat->name}は受け入れ件数の上限に達しているため、投稿情報を作成できませんでした。");
        }
        if ($cat->upperlimit > 0) {
            // 重複投稿の禁止： すでに投稿があるか？
            $count = Paper::where("category_id", $cat->id)->where("deleted", 0)->where("owner", auth()->id())->count();
            if ($count > 0) return redirect()->route('paper.create')->with('feedback.error', "申し訳ありませんが、{$cat->name}の投稿は一人一件に制限されているため、投稿情報を作成できませんでした。");
        }
        // バリデーションが成功した場合の処理
        $em = implode("\n", $this->emlist());
        try {
            $paper = Paper::create([
                'category_id' => $this->input("action"),
                'contactemails' => $em,
                'owner' => Auth::user()->id,
            ]);
            // $paper->updateContacts();
        } catch (QueryException $e) {
            return redirect()->route('paper.create')->with('feedback.error', "QueryException on Paper create");
        }
        return redirect()->route('paper.edit', ['paper' => $paper->id])->with('feedback.success', "投稿情報を作成しました。");
        // }
        return null;
    }

    // papers.edit からの更新
    // 上との違いは最後のメッセージと、リダイレクト先と、DB更新処理
    public function shori_update(int $id): object
    {
        // $validator = $this->validate_em();
        // if ($validator == null) {
        //     return redirect()->route('paper.edit', ['paper' => $id])
        //         ->with('feedback.error', "投稿連絡用メールアドレスは1件以上" . env('CONTACTEMAILS_MAX', 5) . "件以内で入力してください。");
        // } else if ($validator->fails()) {
        //     // エラーがある場合の処理
        //     $ary = [];
        //     $errors = $validator->errors()->all();
        //     foreach ($errors as $error) {
        //         // 数字をとりだす The ema.1 field must be a valid email address. => 1
        //         if (preg_match('/\d+/', $error, $matches)) {
        //             // 最初にマッチした数字を取得
        //             $number = $matches[0];
        //             $ary[] = $this->emlist[$number];
        //         }
        //     }
        //     $mes = implode("】【", $ary);
        //     return redirect()->route('paper.edit', ['paper' => $id])
        //         ->with('feedback.error', "以下の投稿連絡用メールアドレスを修正して再入力してください。【" . $mes . "】");
        // } else {
        // バリデーションが成功した場合の処理
        $em = implode("\n", $this->emlist());
        try {
            $paper = Paper::findOrFail($id);
            $paper->contactemails = $em;
            $paper->save();

            // $paper->updateContacts();
        } catch (QueryException $e) {
            return redirect()->route('paper.edit')->with('feedback.error', "QueryException on Paper create");
        }
        return redirect()->route('paper.edit', ['paper' => $id])->with('feedback.success', "投稿連絡用メールアドレスを修正しました。");
        // }
    }
}
