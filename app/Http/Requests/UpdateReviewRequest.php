<?php

namespace App\Http\Requests;

use App\Models\Score;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateReviewRequest extends FormRequest
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
        // じっさいには Score に格納する
        return [
            'review_id' => ['required', 'integer'],
            'viewpoint_id' => ['required', 'integer'],
            'paper_id' => ['required', 'integer'],
            // 'value' => ['required', 'integer'],
            // 'valuestr' => ['required', 'integer'],
            //
        ];
    }

    public function shori()
    {
        $data = $this->all();
        $rev_id = $data['review_id'];
        $viewpoint_id = $data['viewpoint_id'];
        unset($data['_token']);
        unset($data['_method']);
        unset($data['paper_id']);
        unset($data['review_id']);
        unset($data['viewpoint_id']);
        unset($data['url']);
        // 基本的には、ひとつのviewpointに対するデータしかはいらない
        foreach ($data as $key => $value) {
            // info($key." => ".$value);
            $valueNum = is_numeric($value) ? $value : null;
            $valueStr = !is_null($value) ? $value : null;
            $key3 = [
                'review_id'    => $rev_id,
                'user_id'      => Auth::id(),
                'viewpoint_id' => $viewpoint_id,
            ];
            $values = ['value' => $valueNum, 'valuestr' => $valueStr];
            try {
                // updateOrCreate は firstOrCreate と同様に SELECT→INSERT/UPDATE の2ステップだが、
                // 既存行があれば UPDATE になるため重複エラーが起きにくい。
                // それでも並走時に INSERT が競合した場合は catch でフォールバックする。
                DB::transaction(function () use ($key3, $values) {
                    Score::updateOrCreate($key3, $values);
                });
            } catch (\Illuminate\Database\QueryException $e) {
                // 1062: Duplicate entry — 並走リクエストが先に INSERT したケース
                if (($e->errorInfo[1] ?? 0) === 1062) {
                    Score::where($key3)->update($values);
                } else {
                    throw $e;
                }
            }
        }
        return json_encode($data);
    }

    public function shori_dummy()
    {
        $data = $this->all();
        // $rev_id = $data['review_id'];
        // $viewpoint_id = $data['viewpoint_id'];
        unset($data['_token']);
        unset($data['_method']);
        unset($data['paper_id']);
        unset($data['review_id']);
        unset($data['viewpoint_id']);
        unset($data['url']);
        return json_encode($data);
    }
}
