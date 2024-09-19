<?php

namespace App\Http\Requests;

use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\EnqueteItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateEnqueteRequest extends FormRequest
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
        return [
            "enq_id" => ["required", "integer"],
            "paper_id" => ["required", "integer"],
            //
        ];
    }

    public function shori(): string
    {
        $data = $this->all();
        $enq_id = $data['enq_id'];
        $paper_id = $data['paper_id']; // 参加登録だとPartIdになる
        unset($data['paper_id']);
        unset($data['enq_id']);
        unset($data['_token']);
        unset($data['_method']);
        unset($data['url']);
        unset($data['_url']);
        foreach ($data as $key => $value) {
            $ei = EnqueteItem::where("enquete_id", $enq_id)->where("name", $key)->first();
            if ($value != null && !$ei->validate_rule($value)) $data[$key] = $ei->pregerrmes;
            else {
                DB::transaction(function () use ($enq_id, $paper_id, $ei, $value) {
                    $enq = EnqueteAnswer::firstOrCreate([
                        'enquete_id' => $enq_id,
                        'user_id' => Auth::id(),
                        'paper_id' => $paper_id,
                        'enquete_item_id' => $ei->id,
                    ]);
                    if (is_numeric($value)) {
                        if ($value <= 2 ** 31 - 1 && $value >= -2 ** 31) $enq->value = $value; // 整数の範囲を越えなければ数値として
                        else $enq->value = null;
                        $enq->valuestr = $value;
                    } else if (is_string($value)) {
                        $enq->value = null;
                        $enq->valuestr = $value;
                    } else if (is_null($value)) {
                        $enq->value = null;
                        $enq->valuestr = null;
                    }
                    $enq->save();
                });
            }
        }
        return json_encode($data);
    }
}
