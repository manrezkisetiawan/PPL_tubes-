<?php

namespace Tests\Feature\Gamification;

use App\Models\Activity;
use App\Models\Badge;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\EmissionFactor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI-15 Keikutsertaan Tantangan.
 */
class ChallengeParticipationTest extends TestCase
{
    use RefreshDatabase;

    private function participation(User $user, Challenge $challenge): ?ChallengeUser
    {
        return $challenge->participationOf($user);
    }

    public function test_user_can_join_running_challenge(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        $this->actingAs($user)->post(route('participations.store', $challenge))
            ->assertRedirect(route('participations.index'));

        $participation = $this->participation($user, $challenge);
        $this->assertSame(ChallengeUser::STATUS_RUNNING, $participation->status);
        $this->assertEquals(0, (float) $participation->progress);
        $this->assertNotNull($participation->joined_at);
    }

    public function test_user_can_join_upcoming_challenge(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->upcoming()->create();

        $this->actingAs($user)->post(route('participations.store', $challenge));

        $this->assertSame(ChallengeUser::STATUS_RUNNING, $this->participation($user, $challenge)->status);
    }

    public function test_user_cannot_join_same_challenge_twice(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        $this->actingAs($user)->post(route('participations.store', $challenge));
        $this->actingAs($user)->post(route('participations.store', $challenge))
            ->assertSessionHas('error', 'Kamu sudah terdaftar pada tantangan ini.');

        $this->assertDatabaseCount('challenge_user', 1);
    }

    public function test_user_cannot_join_ended_challenge(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->ended()->create();

        $this->actingAs($user)->post(route('participations.store', $challenge))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('challenge_user', 0);
    }

    public function test_user_can_withdraw_and_rejoin(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();
        $this->actingAs($user)->post(route('participations.store', $challenge));

        $this->actingAs($user)->delete(route('participations.destroy', $challenge))
            ->assertRedirect(route('participations.index'));
        $this->assertSame(ChallengeUser::STATUS_CANCELLED, $this->participation($user, $challenge)->status);

        $this->actingAs($user)->post(route('participations.store', $challenge));
        $this->assertSame(ChallengeUser::STATUS_RUNNING, $this->participation($user, $challenge)->status);
        $this->assertDatabaseCount('challenge_user', 1);
    }

    public function test_user_cannot_update_or_withdraw_challenge_they_did_not_join(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();
        User::factory()->create()->challenges()->attach($challenge, ['status' => ChallengeUser::STATUS_RUNNING, 'joined_at' => now()]);

        $this->actingAs($user)->patch(route('participations.update', $challenge))->assertNotFound();
        $this->actingAs($user)->delete(route('participations.destroy', $challenge))->assertNotFound();
    }

    public function test_progress_is_calculated_from_baseline_and_completion_awards_points_once(): void
    {
        $user = User::factory()->create();
        $transport = Category::factory()->create(['name' => 'Transportasi']);
        $electricity = Category::factory()->create(['name' => 'Listrik']);
        $motor = EmissionFactor::factory()->for($transport)->create(['factor_value' => 1]);
        $grid = EmissionFactor::factory()->for($electricity)->create(['factor_value' => 1]);
        $badge = Badge::factory()->create(['type' => 'challenges_completed', 'required_value' => 1]);

        $challenge = Challenge::factory()->for($transport)->create([
            'start_date' => today()->subDays(2),
            'end_date' => today()->addDays(4),
            'reduction_target' => 10,
            'reward_points' => 100,
        ]);
        $user->challenges()->attach($challenge, [
            'status' => ChallengeUser::STATUS_RUNNING, 'progress' => 0, 'joined_at' => today()->subDays(2),
        ]);

        // Garis dasar: 10 hari tercatat x 5 kg = 5 kg/hari.
        for ($daysAgo = 12; $daysAgo >= 3; $daysAgo--) {
            Activity::factory()->for($user)->for($motor)->create(['emission' => 5, 'activity_date' => today()->subDays($daysAgo)]);
        }
        // Selama tantangan: 2 hari x 1 kg -> hemat 2 x (5 - 1) = 8 kg.
        foreach ([2, 1] as $daysAgo) {
            Activity::factory()->for($user)->for($motor)->create(['emission' => 1, 'activity_date' => today()->subDays($daysAgo)]);
        }

        $this->actingAs($user)->patch(route('participations.update', $challenge))
            ->assertSessionHas('success');
        $participation = $this->participation($user, $challenge);
        $this->assertSame(ChallengeUser::STATUS_RUNNING, $participation->status);
        $this->assertEquals(80, (float) $participation->progress);

        // Hari ini hanya mencatat listrik: hemat 5 kg lagi -> total 13 kg >= 10 kg.
        $this->actingAs($user)->post(route('activities.store'), [
            'emission_factor_id' => $grid->id,
            'amount' => 3,
            'activity_date' => today()->toDateString(),
        ])->assertSessionHas('rewards', fn ($rewards) => $rewards['challenges'] === [$challenge->title]);

        $participation = $this->participation($user, $challenge);
        $this->assertSame(ChallengeUser::STATUS_COMPLETED, $participation->status);
        $this->assertEquals(100, (float) $participation->progress);
        $this->assertSame(100, $user->refresh()->points);
        $this->assertDatabaseHas('point_logs', ['user_id' => $user->id, 'points' => 100]);
        $this->assertTrue($user->badges()->whereKey($badge->id)->exists());

        // Evaluasi ulang tidak memberi poin dua kali.
        $this->actingAs($user)->patch(route('participations.update', $challenge))->assertSessionHas('error');
        $this->artisan('challenges:evaluate')->assertSuccessful();
        $this->assertSame(100, $user->refresh()->points);
        $this->assertDatabaseCount('point_logs', 1);
    }

    public function test_progress_stays_zero_without_baseline(): void
    {
        $user = User::factory()->create();
        $factor = EmissionFactor::factory()->create(['factor_value' => 1]);
        $challenge = Challenge::factory()->create(['category_id' => $factor->category_id]);
        $this->actingAs($user)->post(route('participations.store', $challenge));

        $this->actingAs($user)->post(route('activities.store'), [
            'emission_factor_id' => $factor->id,
            'amount' => 2,
            'activity_date' => today()->toDateString(),
        ]);

        $participation = $this->participation($user, $challenge);
        $this->assertSame(ChallengeUser::STATUS_RUNNING, $participation->status);
        $this->assertEquals(0, (float) $participation->progress);
        $this->actingAs($user)->get(route('challenges.show', $challenge))
            ->assertOk()
            ->assertSee('Belum ada data');
    }

    public function test_scheduled_evaluation_marks_unfinished_challenge_as_failed(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->ended()->create(['reward_points' => 50]);
        $user->challenges()->attach($challenge, [
            'status' => ChallengeUser::STATUS_RUNNING, 'progress' => 0, 'joined_at' => $challenge->start_date,
        ]);

        $this->artisan('challenges:evaluate')->assertSuccessful();

        $this->assertSame(ChallengeUser::STATUS_FAILED, $this->participation($user, $challenge)->status);
        $this->assertSame(0, $user->refresh()->points);
    }

    public function test_my_challenges_page_filters_by_status(): void
    {
        $user = User::factory()->create();
        $running = Challenge::factory()->create(['title' => 'Masih Jalan']);
        $done = Challenge::factory()->ended()->create(['title' => 'Sudah Tuntas']);
        $user->challenges()->attach([
            $running->id => ['status' => ChallengeUser::STATUS_RUNNING, 'joined_at' => now()],
            $done->id => ['status' => ChallengeUser::STATUS_COMPLETED, 'progress' => 100, 'joined_at' => now()->subDays(10)],
        ]);

        $this->actingAs($user)->get(route('participations.index', ['status' => 'selesai']))
            ->assertOk()
            ->assertSee('Sudah Tuntas')
            ->assertDontSee('Masih Jalan');
    }
}
