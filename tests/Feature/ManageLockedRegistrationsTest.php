<?php

namespace Tests\Feature;

use App\Models\Regist;
use App\Models\User;
use Tests\TestCase;

class ManageLockedRegistrationsTest extends TestCase
{
    public function test_accounting_can_lock_only_valid_registrations_older_than_the_delay(): void
    {
        $accounting = User::factory()->withRoles('acc')->create();
        $oldValid = $this->makeRegistration($accounting, true, now()->subHours(25));
        $recentValid = $this->makeRegistration($accounting, true, now()->subHours(23));
        $oldInvalid = $this->makeRegistration($accounting, false, now()->subHours(30));
        $unsubmitted = $this->makeRegistration($accounting, true, null);

        $this->actingAs($accounting)
            ->post(route('regist.update_locked'), ['delay_hours' => 24, 'action' => 'lock'])
            ->assertRedirect(route('regist.manage_locked'))
            ->assertSessionHas('feedback.success', '1件の有効な参加登録をロックしました。');

        $this->assertTrue((bool) $oldValid->fresh()->locked);
        $this->assertFalse((bool) $recentValid->fresh()->locked);
        $this->assertFalse((bool) $oldInvalid->fresh()->locked);
        $this->assertFalse((bool) $unsubmitted->fresh()->locked);
    }

    public function test_accounting_can_unlock_the_same_eligible_registrations(): void
    {
        $accounting = User::factory()->withRoles('acc')->create();
        $oldLocked = $this->makeRegistration($accounting, true, now()->subHours(25), true);
        $recentLocked = $this->makeRegistration($accounting, true, now()->subHours(23), true);

        $this->actingAs($accounting)
            ->post(route('regist.update_locked'), ['delay_hours' => 24, 'action' => 'unlock'])
            ->assertRedirect(route('regist.manage_locked'));

        $this->assertFalse((bool) $oldLocked->fresh()->locked);
        $this->assertTrue((bool) $recentLocked->fresh()->locked);
    }

    public function test_accounting_sees_registrations_in_submission_order_with_lock_fields(): void
    {
        $accounting = User::factory()->withRoles('acc')->create();
        $laterSubmittedAt = now()->subDay()->format('Y-m-d H:i:s');
        $earlierSubmittedAt = now()->subDays(2)->format('Y-m-d H:i:s');
        $this->makeRegistration($accounting, false, $laterSubmittedAt, true);
        $this->makeRegistration($accounting, true, $earlierSubmittedAt);
        $this->makeRegistration($accounting, true, null);

        $this->actingAs($accounting)
            ->get(route('regist.manage_locked'))
            ->assertOk()
            ->assertSee('名前')
            ->assertSee('isearly')
            ->assertSee('ロック状態')
            ->assertSeeInOrder([
                $earlierSubmittedAt,
                $laterSubmittedAt,
                '未提出',
            ])
            ->assertSee('早期')
            ->assertSee('通常')
            ->assertSee('Lock');
    }

    public function test_registration_lock_management_is_not_available_to_other_users(): void
    {
        $user = User::factory()->create();
        $regist = $this->makeRegistration($user, true, now()->subDay());

        $this->actingAs($user)
            ->post(route('regist.update_locked'), ['delay_hours' => 0, 'action' => 'lock'])
            ->assertRedirect(route('regist.index'))
            ->assertSessionHas('feedback.error');

        $this->assertFalse((bool) $regist->fresh()->locked);
    }

    private function makeRegistration(User $user, bool $valid, $submittedAt, bool $locked = false): Regist
    {
        $regist = Regist::create(['user_id' => $user->id, 'valid' => $valid]);
        $regist->submitted_at = $submittedAt;
        $regist->locked = $locked;
        $regist->save();

        return $regist;
    }
}
