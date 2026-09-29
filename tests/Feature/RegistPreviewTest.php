<?php

namespace Tests\Feature;

use App\Models\Regist;
use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\User;
use App\Models\Validation;
use Livewire\Livewire;
use Tests\TestCase;

class RegistPreviewTest extends TestCase
{
    public function test_pc_can_preview_registration_with_foradmin_key_after_deadline(): void
    {
        $owner = $this->makeUser();
        $pc = User::factory()->withRoles('pc')->create();
        $regist = Regist::create(['user_id' => $owner->id, 'valid' => false]);

        $this->actingAs($pc)
            ->get(route('regist.preview', ['regist' => $regist->id, 'key' => 'foradmin']))
            ->assertOk()
            ->assertSee($owner->name)
            ->assertSee('参加登録の参照');
    }

    public function test_non_admin_cannot_use_foradmin_preview_key(): void
    {
        $owner = $this->makeUser();
        $regist = Regist::create(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->get(route('regist.preview', ['regist' => $regist->id, 'key' => 'foradmin']))
            ->assertForbidden();
    }

    public function test_registration_preview_requires_matching_token_for_non_admin_users(): void
    {
        $owner = $this->makeUser();
        $viewer = $this->makeUser();
        $regist = Regist::create(['user_id' => $owner->id]);

        $this->actingAs($viewer)
            ->get(route('regist.preview', ['regist' => $regist->id, 'key' => $regist->token()]))
            ->assertOk()
            ->assertSee($owner->name);

        $this->get(route('regist.preview', ['regist' => $regist->id, 'key' => 'wrong-token']))
            ->assertForbidden();
    }

    public function test_mock_form_preview_renders_selectable_controls_without_persisting_answers(): void
    {
        $owner = $this->makeUser();
        $pc = User::factory()->withRoles('pc')->create();
        Enquete::whereKey(1)->update(['withpaper' => false]);
        $answersBefore = EnqueteAnswer::count();
        $registrationsBefore = Regist::count();

        $this->actingAs($pc)
            ->get(route('regist.edit_dummy', ['key' => 'foradmin']))
            ->assertOk()
            ->assertSee('参加登録フォームのプレビュー')
            ->assertSee('name="happyo"', false)
            ->assertSee('onchange="updateMockAnswer(this)"', false)
            ->assertSee('action="#"', false)
            ->assertDontSee('enquete.update');

        $this->assertSame($answersBefore, EnqueteAnswer::count());
        $this->assertSame($registrationsBefore, Regist::count());
    }

    public function test_mock_form_preview_accepts_matching_hash_for_non_admin_users(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get(route('regist.edit_dummy', ['key' => Regist::previewkey()]))
            ->assertOk()
            ->assertSee('参加登録フォームのプレビュー');

        $this->get(route('regist.edit_dummy', ['key' => 'invalid-hash']))
            ->assertForbidden();
    }

    public function test_mock_validation_reports_missing_answers_and_accepts_complete_answers_without_saving(): void
    {
        Enquete::whereKey(1)->update(['withpaper' => false]);
        Validation::where('event_id', 1)->delete();
        $answersBefore = EnqueteAnswer::count();
        $registrationsBefore = Regist::count();

        Livewire::test(\App\Livewire\RegistMockCheck::class)
            ->call('checkMockAnswers', [])
            ->assertSet('checked', true)
            ->assertSee('発表カテゴリ')
            ->assertSee('回答してください');

        Livewire::test(\App\Livewire\RegistMockCheck::class)
            ->call('checkMockAnswers', [
                'happyo' => '研究発表',
                'theme' => 'その他',
            ])
            ->assertSet('checked', true)
            ->assertSet('errors', [])
            ->assertSee('問題はありませんでした');

        $this->assertSame($answersBefore, EnqueteAnswer::count());
        $this->assertSame($registrationsBefore, Regist::count());
    }

    public function test_mock_validation_can_run_nogood_checks_without_a_user_id(): void
    {
        Validation::where('event_id', 1)->delete();
        $validation = new Validation();
        $validation->event_id = 1;
        $validation->orderint = 1;
        $validation->name = 'mock nogood check';
        $validation->script = "\$res[] = \$this->nogood(['happyo' => 1]);";
        $validation->save();

        $regist = new Regist();
        $regist->event_id = 1;
        $errors = array_values(array_filter($regist->check(['happyo' => '研究発表']), function ($value) {
            return !is_null($value);
        }));

        $this->assertSame([], $errors);
        $this->assertNull($regist->user_id);
    }

    private function makeUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create();

        return $user;
    }
}