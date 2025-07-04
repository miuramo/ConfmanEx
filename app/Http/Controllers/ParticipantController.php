<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParticipantRequest;
use App\Http\Requests\UpdateParticipantRequest;
use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\EnqueteItem;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $part = Participant::firstOrCreate(
            [
                'user_id' => auth()->id(),
            ],
            [
                'event_id' => 1,
            ]
        );
        return redirect()->route("part.edit", ["part" => $part]);
        //
    }
    

    

    


    

    
}
