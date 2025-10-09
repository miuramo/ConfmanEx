<?php

namespace App\Livewire;

use Livewire\Component;

class SettingSwitch extends Component
{
    public $name;
    public $setting;
    public $message;
    public $inputtext;
    public int $textsize = 10;

    public function mount($name)
    {
        $this->name = $name;
        $this->message = "testing...";
        $this->setting = \App\Models\Setting::where('name', $name)->first();
        $this->setting->valid = true;
        $this->setting->save();
        if ($this->setting->isbool == false) {
            $this->inputtext = $this->setting->value;
        }
    }
    public function updatedInputtext()
    {
        $this->setting->value = $this->inputtext;
        $this->setting->save();
        $this->message = "Input text updated to: " . $this->inputtext;
    }
    
    public function refreshSetting()
    {
        $this->setting = \App\Models\Setting::where('name', $this->name)->first();
    }
    public function render()
    {
        return view('livewire.setting-switch');
    }
    public function updatedSetting()
    {
        $this->message = "updatedSetting called for: " . $this->name;
        $this->toggleSetting();
    }
    
    public function testMethod()
    {
        $this->message = "toggleSetting called for: " . $this->name;
    }
    
    public function toggleSetting()
    {
        $this->message = "toggleSetting called for: " . $this->name;        
        $this->setting->value = $this->setting->value == "true" ? "false" : "true";
        $this->setting->save();
        
        // 設定を再読み込みして最新の状態を確保
        $this->refreshSetting();
        
        \Log::info("SettingSwitch: {$this->name} changed to {$this->setting->value}");
        
        // フロントエンドでも確認できるようにセッションメッセージを追加
        session()->flash('message', "Setting {$this->name} toggled to {$this->setting->value}");
    }
    public function validateSetting()
    {
        info("SettingSwitch: {$this->name} validated.");
        $this->setting->valid = true;
        $this->setting->save();
        $this->refreshSetting();
        session()->flash('message', "Setting {$this->name} validated.");
    }
}
