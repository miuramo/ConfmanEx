<?php

namespace Tests\Feature;

use App\Livewire\RegistCheck;
use App\Models\Regist;
use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

class RegistDeadlineTest extends TestCase
{
    public function test_unsubmitted_registration_cannot_be_completed_after_late_deadline(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $regist = Regist::create(['user_id' => $user->id]);
        $this->setRegistrationLimits(now()->subDays(2)->toDateString(), now()->subDay()->toDateString());
        $this->actingAs($user);

        $component = new RegistCheck();
        $component->regid = $regist->id;
        $component->is_early = true;
        $response = $component->doregist();

        $this->assertSame(route('regist.index'), $response->getTargetUrl());
        $this->assertNull($regist->fresh()->submitted_at);
        $this->assertSame(0, $regist->fresh()->valid);
    }

    public function test_unsubmitted_registration_cannot_be_edited_after_late_deadline(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $regist = Regist::create(['user_id' => $user->id]);
        $this->setRegistrationLimits(now()->subDays(2)->toDateString(), now()->subDay()->toDateString());

        $this->actingAs($user)
            ->get(route('regist.edit', ['regist' => $regist->id]))
            ->assertRedirect(route('regist.index'));
    }

    public function test_owner_can_delete_late_registration_during_late_period(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $regist = Regist::create(['user_id' => $user->id]);
        $regist->isearly = false;
        $regist->save();
        $this->setRegistrationLimits(now()->subDay()->toDateString(), now()->addDay()->toDateString());

        $this->actingAs($user)
            ->from(route('regist.index'))
            ->delete(route('regist.destroy', ['regist' => $regist->id]))
            ->assertRedirect(route('regist.index'));

        $this->assertSoftDeleted('regists', ['id' => $regist->id]);
    }

    public function test_owner_cannot_delete_late_registration_during_early_period(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $regist = Regist::create(['user_id' => $user->id]);
        $regist->isearly = false;
        $regist->save();
        $this->setRegistrationLimits(now()->addDay()->toDateString(), now()->addDays(2)->toDateString());

        $this->actingAs($user)
            ->delete(route('regist.destroy', ['regist' => $regist->id]))
            ->assertRedirect(route('regist.index'));

        $this->assertNotSoftDeleted('regists', ['id' => $regist->id]);
    }

    private function setRegistrationLimits(string $early, string $late): void
    {
        Setting::setval('REG_EARLY_LIMIT', $early);
        Setting::setval('REG_LATE_LIMIT', $late);
    }
}