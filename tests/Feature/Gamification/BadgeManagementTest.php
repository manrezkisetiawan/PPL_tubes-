<?php

namespace Tests\Feature\Gamification;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * PBI-13 Manajemen Lencana (CRUD administrator).
 */
class BadgeManagementTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Hemat Energi',
            'description' => 'Mencatat 10 aktivitas.',
            'type' => 'activities_logged',
            'required_value' => 10,
            'is_active' => '1',
        ], $overrides);
    }

    public function test_regular_user_cannot_access_badge_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.badges.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.badges.store'), $this->validPayload())->assertForbidden();
        $this->assertDatabaseCount('badges', 0);
    }

    public function test_admin_can_list_badges(): void
    {
        $admin = User::factory()->admin()->create();
        Badge::factory()->create(['name' => 'Langkah Pertama']);

        $this->actingAs($admin)->get(route('admin.badges.index'))
            ->assertOk()
            ->assertSee('Langkah Pertama');
    }

    public function test_admin_can_create_badge_with_icon(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.badges.store'), $this->validPayload([
                'icon' => UploadedFile::fake()->image('ikon.png', 128, 128),
            ]))
            ->assertRedirect(route('admin.badges.index'));

        $badge = Badge::firstWhere('name', 'Hemat Energi');
        $this->assertNotNull($badge);
        $this->assertSame('activities_logged', $badge->type);
        $this->assertTrue($badge->is_active);
        Storage::disk('public')->assertExists($badge->icon);
    }

    public function test_badge_validation_rejects_invalid_input(): void
    {
        $admin = User::factory()->admin()->create();
        Badge::factory()->create(['name' => 'Sudah Ada']);

        $this->actingAs($admin)
            ->post(route('admin.badges.store'), $this->validPayload([
                'name' => 'Sudah Ada',
                'type' => 'tidak_dikenal',
                'required_value' => 0,
            ]))
            ->assertSessionHasErrors(['name', 'type', 'required_value']);

        $this->assertDatabaseCount('badges', 1);
    }

    public function test_admin_can_update_badge_and_deactivate_it(): void
    {
        $admin = User::factory()->admin()->create();
        $badge = Badge::factory()->create(['name' => 'Lama', 'required_value' => 5]);

        $this->actingAs($admin)
            ->put(route('admin.badges.update', $badge), [
                'name' => 'Baru',
                'type' => 'streak',
                'required_value' => 14,
                // is_active tidak dikirim = checkbox tidak dicentang
            ])
            ->assertRedirect(route('admin.badges.index'));

        $badge->refresh();
        $this->assertSame('Baru', $badge->name);
        $this->assertSame('streak', $badge->type);
        $this->assertEquals(14, (float) $badge->required_value);
        $this->assertFalse($badge->is_active);
    }

    public function test_admin_can_remove_badge_icon(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $path = UploadedFile::fake()->image('lama.png')->store('badges', 'public');
        $badge = Badge::factory()->create(['icon' => $path]);

        $this->actingAs($admin)->put(route('admin.badges.update', $badge), [
            'name' => $badge->name,
            'type' => $badge->type,
            'required_value' => 1,
            'is_active' => '1',
            'remove_icon' => '1',
        ]);

        $this->assertNull($badge->refresh()->icon);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_can_delete_badge_that_nobody_has_earned(): void
    {
        $admin = User::factory()->admin()->create();
        $badge = Badge::factory()->create();

        $this->actingAs($admin)->delete(route('admin.badges.destroy', $badge))
            ->assertRedirect(route('admin.badges.index'));

        $this->assertModelMissing($badge);
    }

    public function test_admin_cannot_delete_badge_already_earned(): void
    {
        $admin = User::factory()->admin()->create();
        $badge = Badge::factory()->create();
        User::factory()->create()->badges()->attach($badge, ['earned_at' => now()]);

        $this->actingAs($admin)
            ->from(route('admin.badges.index'))
            ->delete(route('admin.badges.destroy', $badge))
            ->assertRedirect(route('admin.badges.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($badge);
    }
}
