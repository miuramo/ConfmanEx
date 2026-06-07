<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BibEntry extends Model
{
    protected $fillable = [
        'key',
        'name_jp',
        'name_en',
        'dtype',
        'is_required',
        'for_manage',
        'display_order',
        'color',
    ];
    // no timestamps
    public $timestamps = false;

    public static function seeder()
    {
        BibEntry::firstOrCreate([
            'key' => "title",
        ], [
            'name_jp' => "和文タイトル",
            'name_en' => "Title (in Japanese)",
            'dtype' => "varchar",
            'is_required' => true,
            'for_manage' => false,
            'display_order' => 1,
            'color' => "teal",
        ]);
        BibEntry::firstOrCreate([
            'key' => "authorlist",
        ], [
            'name_jp' => "和文著者名(所属)",
            'name_en' => "Author list (in Japanese)",
            'dtype' => "mediumtext",
            'is_required' => true,
            'for_manage' => false,
            'display_order' => 2,
            'color' => "teal",
        ]);
        BibEntry::firstOrCreate([
            'key' => "etitle",
        ], [
            'name_jp' => "英文Title",
            'name_en' => "Title (in English)",
            'dtype' => "varchar",
            'is_required' => true,
            'for_manage' => false,
            'display_order' => 3,
            'color' => "lime",
        ]);
        BibEntry::firstOrCreate([
            'key' => "eauthorlist",
        ], [
            'name_jp' => "英文著者名(所属)",
            'name_en' => "Author list (in English)",
            'dtype' => "mediumtext",
            'is_required' => true,
            'for_manage' => false,
            'display_order' => 4,
            'color' => "lime",
        ]);
        BibEntry::firstOrCreate([
            'key' => "abst",
        ], [
            'name_jp' => "和文アブストラクト",
            'name_en' => "Abstract (in Japanese)",
            'dtype' => "mediumtext",
            'is_required' => true,
            'for_manage' => false,
            'display_order' => 5,
            'color' => "cyan",
        ]);
        BibEntry::firstOrCreate([
            'key' => "keyword",
        ], [
            'name_jp' => "和文キーワード ",
            'name_en' => "Keyword (in Japanese)",
            'dtype' => "varchar",
            'is_required' => true,
            'for_manage' => false,
            'display_order' => 6,
            'color' => "cyan",
        ]);
        BibEntry::firstOrCreate([
            'key' => "eabst",
        ], [
            'name_jp' => "英文アブストラクト",
            'name_en' => "Abstract (in English)",
            'dtype' => "mediumtext",
            'is_required' => false,
            'for_manage' => false,
            'display_order' => 7,
            'color' => "lime",
        ]);
        BibEntry::firstOrCreate([
            'key' => "ekeyword",
        ], [
            'name_jp' => "英文キーワード",
            'name_en' => "Keyword (in English)",
            'dtype' => "varchar",
            'is_required' => false,
            'for_manage' => false,
            'display_order' => 8,
            'color' => "lime",
        ]);
        // BibEntry::firstOrCreate([
        //     'key' => "ror",
        // ], [
        //     'name_jp' => "ROR",
        //     'name_en' => "ROR",
        //     'dtype' => "varchar",
        //     'is_required' => false,
        //     'for_manage' => true,
        //     'display_order' => 9,
        //     'color' => "orange",
        // ]);
    }
}
