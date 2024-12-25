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
            DB::transaction(function () use ($rev_id, $viewpoint_id, $value) {
                $scr = Score::firstOrCreate([
                    'review_id' => $rev_id,
                    'user_id' => Auth::id(),
                    'viewpoint_id' => $viewpoint_id,
                ]);
                if (is_numeric($value)) {
                    $scr->value = $value;
                    $scr->valuestr = $value;
                } else if (is_string($value)){
                    $scr->value = null;
                    $scr->valuestr = $value;
                } else if (is_null($value)){
                    $scr->value = null;
                    $scr->valuestr = null;
                }
                $scr->save();                    
            });
        }
        info($data);
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
