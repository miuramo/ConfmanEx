<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Crudajax extends Component
{
    public string $search = '';
    public $model;
    public string $modelName = '';
    public string $tableName = '';
    public $fs = [];
    public $data = [];

    protected $coldetails;


    public function mount($modelName)
    {
        $this->model = app("App\\Models\\" . $modelName);
        $this->tableName = $this->model->getTable();
        $this->fs = self::column_details($this->tableName);
        $this->updatedSearch();
    }

    public function render()
    {
        return view('livewire.crudajax');
    }

    public function updatedSearch()
    {
        if ($this->search == '') {
            $this->data = $this->model::limit(3)->get();
        } else {
            $this->data = $this->model::where('name', 'like', "%{$this->search}%")
                ->get();
        }
    }

    public function resetSearch()
    {
        $this->search = '';
        $this->data = [];
    }


    public static function column_details($tableName)
    {
        $driver = DB::connection()->getDriverName();
        $coldetails = [];
        if ($driver === 'sqlite') {
            $columns = DB::select("pragma table_info('{$tableName}')");
            foreach ($columns as $cc) {
                $coldetails[$cc->name] = $cc->type;
            }
        } else if ($driver === 'mysql') {
            $columns = DB::select("show full columns from `{$tableName}`");
            // カラム名とデータ型の取得
            foreach ($columns as $colary) {
                $coldetails[$colary->Field] = $colary->Type;
            }
        }
        // Type() のかっこ以下は取り除く
        // foreach ($coldetails as $f => $t) {
        //     $pos = strpos($t, '(');
        //     if ($pos > 0) {
        //         $coldetails[$f] = substr($t, 0, $pos);
        //     }
        // }
        return $coldetails;
    }
}
