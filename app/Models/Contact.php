<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Contact extends Model
{
    use HasFactory;
    use FindByIdOrNameTrait;

    protected $fillable = [
        'email',
    ];

    public function papers()
    {
        $tbl = 'paper_contact';
        // $table_fields = Schema::getColumnListing($tbl);
        // return $this->belongsToMany(User::class, $tbl, 'role_id', 'user_id');// ->withPivot($table_fields)->using(RolesUser::class);
        return $this->belongsToMany(Paper::class, $tbl); //->using(RolesUser::class);
    }

    public static function top_n($n = 10)
    {
        // paper_contact テーブルをcontact_idでグループ化して、最も多くのpaperに関連しているcontactを取得する
        $top_contacts = self::join('paper_contact', 'contacts.id', '=', 'paper_contact.contact_id')
            ->select('contacts.id', 'contacts.email', 'contacts.valid', 'contacts.infoprovider', 'contacts.created_at', 'contacts.updated_at', \DB::raw('COUNT(paper_contact.paper_id) as paper_count'), \DB::raw('GROUP_CONCAT(paper_contact.paper_id) as paper_ids'))
            ->groupBy('contacts.id', 'contacts.email', 'contacts.valid', 'contacts.infoprovider', 'contacts.created_at', 'contacts.updated_at')
            ->orderByRaw('COUNT(paper_contact.paper_id) DESC')
            ->limit($n)
            ->get();
        return $top_contacts;
    }

    // paper_contactに存在しないcontactを取得するための関数
    public static function unused()
    {
        // paper_contact には存在するが、contactには存在しないcontactを取得する
        $unused_contacts = self::whereNotIn('id', function ($query) {
            $query->select('contact_id')->from('paper_contact');
        })->whereNotIn('email', function ($query) {
            $query->select('email')->from('users');
            // })->where('email', 'not like', function ($query) {
            //     $query->select('contactemails')->from('papers')->first(); 
        })->get();
        return $unused_contacts;
    }

    public static function rebuild_from_papers()
    {
        // すべてのPaperContactを削除してから、すべてのPaperに対してupdateContacts()を呼び出す
        PaperContact::truncate();
        $papers = Paper::withTrashed()->with('paperowner')->get();
        foreach ($papers as $paper) {
            $paper->updateContacts();
        }
    }

    public static function invalidate()
    {
        $unused_contacts = self::unused();
        foreach ($unused_contacts as $contact) {
            $contact->valid = false;
            $contact->save();
        }
    }
    public static function bundle_delete()
    {
        $unused_contacts = self::unused();
        foreach ($unused_contacts as $contact) {
            $contact->delete();
        }
    }
    public static function delete_invalid()
    {
        $invalid_contacts = self::where('valid', false)->get();
        foreach ($invalid_contacts as $contact) {
            $contact->delete();
        }
    }


    // 問題のあるメールアドレスを無効にする（未テスト）
    public static function disable_email(string $em)
    {
        // Contactから辿れる、papersについて、投稿連絡用メールアドレスcontactemails から抜く。抜いた後でcontactsリレーションを更新。
        $contact = Contact::findByIdOrName($em, null, "email");
        $ids = [];
        if ($contact != null && $contact->papers != null) {
            foreach ($contact->papers as $paper) {
                $ids[] = $paper->id_03d();
                $paper2 = Paper::with("contacts")->find($paper->id);
                $paper2->remove_contact($contact); // ここでの修正は、log_modifiesに反映されない
                // メール送信（またはスプール） TODO: mail send
                if ($paper2) (new DisableEmail($paper2, $em))->process_send();
            }
        }
        // return redirect()->route('role.top', ['role' => 'admin'])->with('feedback.success', 'すべてのPaperの投稿連絡用メールアドレスから削除しました。' . implode(",", $ids));
    }
}
