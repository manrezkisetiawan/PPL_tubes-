<?php

namespace Tests\Feature\Gamification;

use App\Models\Activity;
use App\Models\Badge;
use App\Models\EmissionFactor;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI-13 Manajemen Lencana: pemberian lencana dan galeri pengguna.
 */
class BadgeAwardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_logging_first_activity_awards_matching_badge(): void
    {
        $user = User::factory()->create();
        $factor = EmissionFactor::factory()->create(['factor_value' => 0.5]);
        $first = Badge::factory()->create(['name' => 'Langkah Pertama', 'type' => 'activities_logged', 'required_value' => 1]);
        $tenth = Badge::factory()->create(['type' => 'activities_logged', 'required_value' => 10]);
        $inactive = Badge::factory()->create(['type' => 'activities_logged', 'required_value' => 1, 'is_active' => false]);

        $this->actingAs($user)
            ->post(route('activities.store'), [
                'emission_factor_id' => $factor->id,
                'amount' => 4,
                'activity_date' => today()->toDateString(),
            ])
            ->assertRedirect(route('activities.index'))
            ->assertSessionHas('rewards', fn ($rewards) => $rewards['badges'] === ['Langkah Pertama']);

        $this->assertDatabaseHas('activities', ['user_id' => $user->id, 'emission' => 2]);
        $this->assertTrue($user->badges()->whereKey($first->id)->exists());
        $this->assertFalse($user->badges()->whereKey($tenth->id)->exists());
        $this->assertFalse($user->badges()->whereKey($inactive->id)->exists());
    }

    public function test_badges_are_not_awarded_twice(): void
    {
        $user = User::factory()->create();
        Activity::factory()->for($user)->create();
        Badge::factory()->create(['type' => 'activities_logged', 'required_value' => 1]);
        $service = app(GamificationService::class);

        $this->assertCount(1, $service->evaluateBadges($user));
        $this->assertCount(0, $service->evaluateBadges($user));
        $this->assertSame(1, $user->badges()->count());
    }

    public function test_streak_counts_consecutive_days_up_to_today(): void
    {
        $user = User::factory()->create();
        $factor = EmissionFactor::factory()->create();
        foreach ([0, 1, 2, 4] as $daysAgo) {
            Activity::factory()->for($user)->for($factor)->create(['activity_date' => today()->subDays($daysAgo)]);
        }

        $this->assertSame(3, app(GamificationService::class)->updateStreak($user));
        $this->assertSame(3, $user->refresh()->streak_count);
    }

    public function test_streak_continues_from_yesterday_when_today_not_logged_yet(): void
    {
        $user = User::factory()->create();
        $factor = EmissionFactor::factory()->create();
        foreach ([1, 2] as $daysAgo) {
            Activity::factory()->for($user)->for($factor)->create(['activity_date' => today()->subDays($daysAgo)]);
        }

        $this->assertSame(2, app(GamificationService::class)->updateStreak($user));
    }

    public function test_gallery_shows_locked_and_earned_badges(): void
    {
        $user = User::factory()->create();
        $earned = Badge::factory()->create(['name' => 'Sudah Diraih', 'type' => 'points', 'required_value' => 1000]);
        Badge::factory()->create(['name' => 'Masih Terkunci', 'type' => 'points', 'required_value' => 500]);
        $retired = Badge::factory()->create(['name' => 'Edisi Lama', 'is_active' => false]);
        Badge::factory()->create(['name' => 'Tidak Pernah Diraih', 'is_active' => false]);
        $user->badges()->attach([$earned->id => ['earned_at' => now()], $retired->id => ['earned_at' => now()]]);

        $this->actingAs($user)->get(route('badges.index'))
            ->assertOk()
            ->assertSee('Sudah Diraih')
            ->assertSee('Masih Terkunci')
            ->assertSee('Edisi Lama')
            ->assertDontSee('Tidak Pernah Diraih')
            ->assertSee('Terkunci')
            ->assertSee('2 dari 3 lencana');
    }
}
