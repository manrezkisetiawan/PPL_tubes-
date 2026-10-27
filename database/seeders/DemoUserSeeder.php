<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\EmissionFactor;
use App\Models\Role;
use App\Models\User;
use App\Services\ChallengeProgressService;
use App\Services\EmissionCalculator;
use App\Services\GamificationService;
use Illuminate\Database\Seeder;

/**
 * Akun demo beserta riwayat aktivitas 30+ hari agar garis dasar tantangan
 * dapat dihitung saat demonstrasi.
 */
class DemoUserSeeder extends Seeder
{
    public function run(EmissionCalculator $calculator, GamificationService $gamification, ChallengeProgressService $progress): void
    {
        User::updateOrCreate(['email' => 'admin@carbontrace.test'], [
            'name' => 'Admin CarbonTrace',
            'password' => 'password',
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'email_verified_at' => now(),
        ]);

        $user = User::updateOrCreate(['email' => 'user@carbontrace.test'], [
            'name' => 'Pengguna Demo',
            'password' => 'password',
            'role_id' => Role::where('name', Role::USER)->value('id'),
            'campus' => 'Telkom University',
            'study_program' => 'S1 Sistem Informasi',
            'email_verified_at' => now(),
        ]);

        $newcomer = User::updateOrCreate(['email' => 'budi@carbontrace.test'], [
            'name' => 'Budi Santoso',
            'password' => 'password',
            'role_id' => Role::where('name', Role::USER)->value('id'),
            'email_verified_at' => now(),
        ]);

        if ($user->activities()->exists()) {
            return;
        }

        $factor = fn (string $name) => EmissionFactor::where('name', $name)->firstOrFail();
        $log = function (User $owner, string $factorName, float $amount, $date) use ($factor, $calculator) {
            $emissionFactor = $factor($factorName);
            $owner->activities()->create([
                'emission_factor_id' => $emissionFactor->id,
                'amount' => $amount,
                'emission' => $calculator->calculate($emissionFactor, $amount),
                'activity_date' => $date,
            ]);
        };

        // Kebiasaan lama: 33 hari terakhir sebelum tantangan transportasi dimulai.
        for ($daysAgo = 33; $daysAgo >= 3; $daysAgo--) {
            $date = today()->subDays($daysAgo);
            $log($user, 'Sepeda motor bensin', 12, $date);
            $log($user, 'Listrik jaringan PLN', 4, $date);
            $log($user, 'Nasi (porsi 100 g)', 2, $date);
            $log($user, $daysAgo % 3 === 0 ? 'Daging sapi (porsi 100 g)' : 'Daging ayam (porsi 100 g)', 1, $date);
            if ($daysAgo >= 21) {
                $log($user, 'Sampah campuran ke TPA', 0.5, $date);
            } elseif ($daysAgo >= 7) {
                $log($user, 'Sampah organik dikompos', 0.5, $date);
            }
        }

        // Dua hari terakhir: mulai beralih ke KRL.
        for ($daysAgo = 2; $daysAgo >= 1; $daysAgo--) {
            $date = today()->subDays($daysAgo);
            $log($user, 'Sepeda motor bensin', 4, $date);
            $log($user, 'Kereta komuter (KRL)', 10, $date);
            $log($user, 'Listrik jaringan PLN', 4, $date);
            $log($user, 'Nasi (porsi 100 g)', 2, $date);
            $log($user, 'Tahu/tempe (porsi 100 g)', 1, $date);
        }

        $join = function (User $participant, string $title, string $status = ChallengeUser::STATUS_RUNNING) {
            $challenge = Challenge::where('title', $title)->firstOrFail();
            $participant->challenges()->syncWithoutDetaching([
                $challenge->id => ['status' => $status, 'progress' => 0, 'joined_at' => $challenge->start_date],
            ]);
        };

        $join($user, 'Pilah Sampah Dua Pekan');
        $join($user, 'Seminggu Kurangi Motor');
        $join($newcomer, 'Seminggu Kurangi Motor');

        $gamification->updateStreak($user);
        $progress->evaluateRunningFor($user);
        $progress->evaluateRunningFor($newcomer);
        $gamification->evaluateBadges($user);
    }
}
