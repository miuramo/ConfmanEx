<?php

namespace App\Http\Requests;

use App\Models\Bidding;
use App\Models\RevConflict;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpdateRevConflictRequest extends FormRequest
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
            "paper_id" => ["required", "integer"],
        ];
    }

    public function shori(): string
    {
        $data = $this->all();
        $paper_id = $data['paper_id'];
        $bidding_id = $data['bidding_id'];
        $revcon = RevConflict::firstOrCreate([
            'user_id' => Auth::id(),
            'paper_id' => $paper_id,
        ]);
        $revcon->bidding_id = $bidding_id;
        $revcon->save();

        // 色と文字をjsonで返す
        $bid = Bidding::find($bidding_id);
        return json_encode($bid);
    }
}
