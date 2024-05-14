<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaperTest extends TestCase
{
    protected static array $users = [];
    protected function setUp(): void
    {
        parent::setUp();

        if (count(self::$users) < 2) {
            // 変数を初期化する
            for ($i = 0; $i < 6; $i++) {
                self::$users[] = User::factory()->create();
                // echo self::$users[$i]->id . "\n";
            }
        }
    }

    public function a_user_can_create_a_paper()
    {
        // echo "*********** test1\n";
        $user = self::$users[0];
        $this->assertDatabaseHas('users', ['email' => $user->email]);
        // $role = Role::factory()->create(['name' => 'writer']);
        // $user->roles()->attach($role);
        $this->actingAs($user);
        $response = $this->get(route('paper.index'));
        $response->assertStatus(200)
            ->assertViewIs('paper.index')
            ->assertSee("あなたが作成した投稿情報はまだありません。");

        $response = $this->post(route('paper.store'), [
            'contactemails' => "",
            'action' => 1,
        ]);
        // $response->dumpSession();
        $response->assertStatus(302)
            ->assertRedirect(route('paper.create'))
            ->assertSessionHas("feedback.error", "投稿連絡用メールアドレスは1件以上" . env('CONTACTEMAILS_MAX', 5) . "件以内で入力してください。");

        $response = $this->post(route('paper.store'), [
            'contactemails' => $user->email . "\n" . self::$users[2]->email,
            'action' => 1,
        ]);
        $response->assertStatus(302)
            ->assertRedirect(route('paper.edit', ['paper' => 1]));

        $this->assertDatabaseHas('papers', ['owner' => $user->id]);
        $this->assertDatabaseHas('contacts', ['email' => self::$users[2]->email]);
        $this->assertDatabaseHas('contacts', ['email' => $user->email]);

        // $this->assertDatabaseCount('contacts', 2);
        $this->user = $user;
    }

    public function a_user_can_edit_contactemails()
    {
        // echo "*********** test2\n";

        $user = self::$users[0];
        $this->actingAs($user);
        $this->assertDatabaseCount('papers', 1);
        // $this->assertDatabaseCount('contacts', 2);
        $response = $this->put(route('paper.update',['paper'=>1]), [
            'contactemails' => $user->email . "\n" . self::$users[1]->email . "\n" . self::$users[2]->email,
            'action' => 1,
        ]);
        // $response->dumpSession();
        $response->assertStatus(302)
            ->assertRedirect(route('paper.edit', ['paper' => 1]))
            ->assertSessionHas("feedback.success", "投稿連絡用メールアドレスを修正しました。");

        $this->assertDatabaseHas('contacts', ['email' => self::$users[2]->email]);
        // $this->assertDatabaseCount('contacts', 4);

    }

    public function a_user_can_show_copapers()
    {
        // echo "*********** test3\n";

        // user1 => p1,
        // Paperをつくるときに、3件の適当な投稿連絡用メールアドレスをfakerで作成。
        // その後、updateContactsでContactを作成し、リレーションを設定する。
        for ($i = 0; $i < count(self::$users); $i++) {
            for ($c = 1; $c < 3; $c++) {
                \App\Models\Paper::factory()->cat($c)->owner(self::$users[$i]->id)->contactemails([self::$users[$i]->email, self::$users[($i + 1) % count(self::$users)]->email, self::$users[($i + 2) % count(self::$users)]->email])->create();
                // echo "{$i} {$c} \n";
            }
        }
        // for ($i = 0; $i < count(self::$users); $i++) {
        //     self::$users[$i]->print_coauthor_papers();
        // }
        // u2 => p2,3
        // u3 => p4,5
        // u4 => p6,7
        // u5 => p8,9
        for ($i = 0; $i < count(self::$users); $i++) {
            $this->actingAs(self::$users[$i]);
            // echo self::$users[0]->id . " " . self::$users[0]->email . "\n";

            $testdata = self::$users[$i]->coary();
            // dump($testdata);
            foreach ($testdata as $url => $status) {
                // echo $url . " " . $status . "\n";
                // dump($url . " " . $status);
                // $response = $this->get($url);
                // $response->assertStatus($status);
            }
        }

        $this->assertDatabaseHas('contacts', ['email' => self::$users[3]->email]);
    }

    #[Group('heavy')]
    public function a_user_can_upload_file_1()
    {
        // echo "*********** test4\n";
        $this->upfile(2, 4, 4, '_wis.pdf');
    }

    #[Group('heavy')]
    public function a_user_can_upload_file_2()
    {
        // echo "*********** test5\n";
        $this->upfile(1, 3, 8, '_int.pdf');
    }

    #[Group('heavy')]
    public function a_user_can_upload_file_3()
    {
        // echo "*********** test6\n";
        $this->upfile(1, 2, 6, '_sss.pdf');
    }
    /** */
    private function upfile($uid, $pid, $pagenum, $fn)
    {
        $mu = User::find($uid);
        $mu->name = "三浦 元喜";
        $mu->save();
        // echo $mu->id . " " . $mu->email . "\n";
        $this->actingAs($mu);
        // $mu->print_coauthor_papers();

        Storage::fake('public'); // フェイクのストレージを使用する
        // $file = UploadedFile::fake()->create('document.pdf', 100,'application/pdf');
        $file = new UploadedFile('./tests/Feature/' . $fn, $fn, 'application/pdf', null, true);
        $response = $this->post(route('file.store'), [
            'file' => $file,
            'paper_id' => $pid,
        ])->withHeaders([
            'Content-Type' => 'multipart/form-data',
        ]);
        // $response->dumpHeader();
        // dump($response);
        $this->assertDatabaseHas('files', [
            'origname' => $fn,
            'user_id' => $mu->id,
            'pagenum' => $pagenum,
        ]);


        $mu = User::factory()->create();
        $response = $this->actingAs($mu)->get(route('file.showhash',['file'=>1,'hash'=>'hogehoge']));
        $response->assertStatus(403);
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
