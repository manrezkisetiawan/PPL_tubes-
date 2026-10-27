<?php

namespace Tests\Feature\Gamification;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI-14 Manajemen Tantangan.
 */
class ChallengeManagementTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Seminggu Tanpa Motor',
            'category_id' => Category::factory()->create()->id,
            'description' => 'Gunakan transportasi umum.',
            'start_date' => today()->toDateString(),
            'end_date' => today()->addDays(6)->toDateString(),
            'reduction_target' => 3.5,
            'reward_points' => 100,
        ], $overrides);
    }

    public function test_regular_user_cannot_manage_challenges(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        $this->actingAs($user)->get(route('admin.challenges.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.challenges.store'), $this->validPayload())->assertForbidden();
        $this->actingAs($user)->delete(route('admin.challenges.destroy', $challenge))->assertForbidden();
        $this->assertModelExists($challenge);
    }

    public function test_admin_can_create_challenge(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.challenges.store'), $this->validPayload());

        $challenge = Challenge::firstWhere('title', 'Seminggu Tanpa Motor');
        $this->assertNotNull($challenge);
        $response->assertRedirect(route('admin.challenges.show', $challenge));
        $this->assertEquals(3.5, (float) $challenge->reduction_target);
        $this->assertSame(100, $challenge->reward_points);
    }

    public function test_challenge_validation_rejects_invalid_period_and_target(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.challenges.store'), $this->validPayload([
                'start_date' => today()->addDays(5)->toDateString(),
                'end_date' => today()->addDays(2)->toDateString(),
                'reduction_target' => 0,
                'reward_points' => -5,
                'category_id' => 999,
            ]))
            ->assertSessionHasErrors(['end_date', 'reduction_target', 'reward_points', 'category_id']);

        $this->actingAs($admin)
            ->post(route('admin.challenges.store'), $this->validPayload([
                'start_date' => today()->subDays(10)->toDateString(),
                'end_date' => today()->subDays(3)->toDateString(),
            ]))
            ->assertSessionHasErrors('end_date');

        $this->assertDatabaseCount('challenges', 0);
    }

    public function test_admin_can_update_challenge(): void
    {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->ended()->create();

        $this->actingAs($admin)
            ->put(route('admin.challenges.update', $challenge), $this->validPayload([
                'title' => 'Judul Baru',
                'category_id' => $challenge->category_id,
                // Tantangan yang sudah berakhir tetap bisa dikoreksi.
                'start_date' => $challenge->start_date->toDateString(),
                'end_date' => $challenge->end_date->toDateString(),
                'reward_points' => 250,
            ]))
            ->assertRedirect(route('admin.challenges.show', $challenge));

        $challenge->refresh();
        $this->assertSame('Judul Baru', $challenge->title);
        $this->assertSame(250, $challenge->reward_points);
    }

    public function test_admin_can_see_participants(): void
    {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $participant = User::factory()->create(['name' => 'Peserta Satu']);
        $participant->challenges()->attach($challenge, ['status' => ChallengeUser::STATUS_RUNNING, 'progress' => 42.5, 'joined_at' => now()]);

        $this->actingAs($admin)->get(route('admin.challenges.show', $challenge))
            ->assertOk()
            ->assertSee('Peserta Satu')
            ->assertSee('42,50%');
    }

    public function test_admin_can_delete_challenge_without_participants(): void
    {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        // Peserta yang sudah mengundurkan diri tidak menghalangi penghapusan.
        User::factory()->create()->challenges()->attach($challenge, ['status' => ChallengeUser::STATUS_CANCELLED, 'joined_at' => now()]);

        $this->actingAs($admin)->delete(route('admin.challenges.destroy', $challenge))
            ->assertRedirect(route('admin.challenges.index'));

        $this->assertModelMissing($challenge);
        $this->assertDatabaseCount('challenge_user', 0);
    }

    public function test_admin_cannot_delete_challenge_with_active_participants(): void
    {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        User::factory()->create()->challenges()->attach($challenge, ['status' => ChallengeUser::STATUS_RUNNING, 'joined_at' => now()]);

        $this->actingAs($admin)
            ->delete(route('admin.challenges.destroy', $challenge))
            ->assertSessionHas('error');

        $this->assertModelExists($challenge);
    }

    public function test_user_sees_running_and_upcoming_challenges_only(): void
    {
        $user = User::factory()->create();
        Challenge::factory()->create(['title' => 'Tantangan Berjalan']);
        Challenge::factory()->upcoming()->create(['title' => 'Tantangan Mendatang']);
        Challenge::factory()->ended()->create(['title' => 'Tantangan Lampau']);

        $this->actingAs($user)->get(route('challenges.index'))
            ->assertOk()
            ->assertSee('Tantangan Berjalan')
            ->assertSee('Tantangan Mendatang')
            ->assertDontSee('Tantangan Lampau');
    }
}
