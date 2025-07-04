<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnqueteConfigRequest;
use App\Http\Requests\UpdateEnqueteConfigRequest;
use App\Models\Enquete;
use App\Models\EnqueteConfig;

class EnqueteConfigController extends Controller
{
    

    

    

    

    

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $enqconfig_id)
    {
        $aEnq = Enquete::accessibleEnquetes(true);
        $enqConfig = EnqueteConfig::find($enqconfig_id);
        $enq_id = $enqConfig->enquete_id;
        if (!isset($aEnq[$enqConfig->enquete_id])) abort(403);
        $enqConfig->delete();
        return redirect()->route('enq.config', ["enq"=>$enq_id])->with('feedback.success', '行を削除しました');
        //
    }
}
