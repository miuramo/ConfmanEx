<?php

namespace Tests\Feature;

use App\Livewire\RegistCheck;
use App\Mail\RegistrationConfirmation;
use App\Models\Regist;
use App\Models\Setting;
use App\Models\User;
use App\Models\Validation;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistWorkflowTest extends TestCase
{
    public function test_user_without_registration_permission_cannot_start_registration(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get(route('regist.create'))
            ->assertRedirect(route('regist.index'))
            ->assertSessionHas('feedback.error');

        $this->assertDatabaseMissing('regists', ['user_id' => $user->id]);
    }

    public function test_eligible_user_reuses_their_existing_registration(): void
    {
        $user = $this->makeUser();
        $this->enableSetting('REG_START_FOR_ALL');
        $existing = Regist::create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('regist.create'))
            ->assertRedirect(route('regist.edit', ['regist' => $existing->id]));

        $this->assertSame(1, Regist::where('user_id', $user->id)->count());
    }

    public function test_user_cannot_edit_or_view_another_users_registration(): void
    {
        $owner = $this->makeUser();
        $otherUser = $this->makeUser();
        $regist = Regist::create(['user_id' => $owner->id, 'valid' => true]);

        $this->actingAs($otherUser)
            ->get(route('regist.edit', ['regist' => $regist->id]))
            ->assertRedirect(route('regist.index'));

        $this->actingAs($otherUser)
            ->get(route('regist.show', ['regist' => $regist->id]))
            ->assertRedirect(route('regist.index'));

        $this->assertSame(1, (int) $regist->fresh()->valid);
    }

    public function test_owner_editing_during_their_registration_period_invalidates_registration(): void
    {
        $user = $this->makeUser();
        $regist = Regist::create(['user_id' => $user->id, 'valid' => true]);
        $regist->isearly = false;
        $regist->save();
        $this->setRegistrationLimits(now()->subDay()->toDateString(), now()->addDay()->toDateString());

        $this->actingAs($user)
            ->get(route('regist.edit', ['regist' => $regist->id]))
            ->assertOk();

        $this->assertSame(0, (int) $regist->fresh()->valid);
    }

    public function test_only_the_owner_can_email_a_valid_registration(): void
    {
        Mail::fake();
        $owner = $this->makeUser();
        $otherUser = $this->makeUser();
        $regist = Regist::create(['user_id' => $owner->id, 'valid' => true]);

        $this->actingAs($owner)
            ->get(route('regist.email', ['regist' => $regist->id]))
            ->assertRedirect(route('regist.index'))
            ->assertSessionHas('feedback.success');
        Mail::assertQueued(RegistrationConfirmation::class, 1);

        $this->actingAs($otherUser)
            ->get(route('regist.email', ['regist' => $regist->id]))
            ->assertRedirect(route('regist.index'));
        Mail::assertQueued(RegistrationConfirmation::class, 1);
    }

    public function test_only_pc_or_accounting_can_cancel_and_restore_registration(): void
    {
        $owner = $this->makeUser();
        $otherUser = $this->makeUser();
        $pc = User::factory()->withRoles('pc')->create();
        $regist = Regist::create(['user_id' => $owner->id, 'valid' => true]);

        $this->actingAs($otherUser)
            ->get(route('regist.cancel', ['regist' => $regist->id]))
            ->assertRedirect(route('regist.index'));
        $this->assertSame(0, (int) $regist->fresh()->canceled);

        $this->actingAs($pc)
            ->get(route('regist.cancel', ['regist' => $regist->id]))
            ->assertRedirect(route('role.top', ['role' => 'acc']));
        $this->assertSame(1, (int) $regist->fresh()->canceled);
        $this->assertNotNull($regist->fresh()->canceled_at);

        $this->actingAs($pc)
            ->get(route('regist.cancel', ['regist' => $regist->id, 'iscancel' => 0]))
            ->assertRedirect(route('role.top', ['role' => 'acc']));
        $this->assertSame(0, (int) $regist->fresh()->canceled);
        $this->assertNull($regist->fresh()->canceled_at);
    }

    public function test_registration_completion_rejects_another_users_registration(): void
    {
        $owner = $this->makeUser();
        $otherUser = $this->makeUser();
        $regist = Regist::create(['user_id' => $owner->id]);
        $this->actingAs($otherUser);

        $component = new RegistCheck();
        $component->regid = $regist->id;
        $response = $component->doregist();

        $this->assertSame(route('regist.index'), $response->getTargetUrl());
        $this->assertNull($regist->fresh()->submitted_at);
        $this->assertSame(0, (int) $regist->fresh()->valid);
    }

    public function test_registration_completion_revalidates_required_answers(): void
    {
        $user = $this->makeUser();
        $regist = Regist::create(['user_id' => $user->id]);
        $this->setRegistrationLimits(now()->addDay()->toDateString(), now()->addDays(2)->toDateString());
        $this->actingAs($user);

        $component = new RegistCheck();
        $component->regid = $regist->id;
        $component->doregist();

        $this->assertNotEmpty($component->errors);
        $this->assertNull($regist->fresh()->submitted_at);
        $this->assertSame(0, (int) $regist->fresh()->valid);
    }

    public function test_first_submission_uses_server_side_early_period_and_resubmission_preserves_type(): void
    {
        $user = $this->makeUser();
        $regist = Regist::create(['user_id' => $user->id]);
        $this->setRegistrationLimits(now()->addDay()->toDateString(), now()->addDays(2)->toDateString());
        Validation::where('event_id', 1)->delete();
        $this->actingAs($user);

        $component = new RegistCheck();
        $component->regid = $regist->id;
        $component->is_early = false;
        $response = $component->doregist();

        $this->assertSame(route('regist.index'), $response->getTargetUrl());
        $this->assertSame(1, (int) $regist->fresh()->isearly);
        $this->assertSame(1, (int) $regist->fresh()->valid);
        $submittedAt = $regist->fresh()->submitted_at;

        $regist->valid = false;
        $regist->save();
        $component->is_early = false;
        $response = $component->doregist();

        $this->assertSame(route('regist.index'), $response->getTargetUrl());
        $this->assertSame(1, (int) $regist->fresh()->isearly);
        $this->assertSame(1, (int) $regist->fresh()->valid);
        $this->assertSame($submittedAt, $regist->fresh()->submitted_at);
    }

    private function makeUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create();

        return $user;
    }

    private function enableSetting(string $name): void
    {
        $setting = Setting::where('name', $name)->firstOrFail();
        $setting->value = 'true';
        $setting->valid = true;
        $setting->save();
    }

    private function setRegistrationLimits(string $early, string $late): void
    {
        Setting::setval('REG_EARLY_LIMIT', $early);
        Setting::setval('REG_LATE_LIMIT', $late);
    }
}
