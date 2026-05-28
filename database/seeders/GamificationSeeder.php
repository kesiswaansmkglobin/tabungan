<?php

namespace Database\Seeders;

use App\Models\Quest;
use App\Models\Tier;
use Illuminate\Database\Seeder;

class GamificationSeeder extends Seeder
{
    public function run(): void
    {
        Tier::create(['name' => 'Bronze', 'min_balance' => 0, 'icon' => '🥉', 'color' => '#CD7F32', 'order_index' => 1]);
        Tier::create(['name' => 'Silver', 'min_balance' => 50000, 'icon' => '🥈', 'color' => '#C0C0C0', 'order_index' => 2]);
        Tier::create(['name' => 'Gold', 'min_balance' => 200000, 'icon' => '🥇', 'color' => '#FFD700', 'order_index' => 3]);
        Tier::create(['name' => 'Platinum', 'min_balance' => 500000, 'icon' => '💎', 'color' => '#E5E4E2', 'order_index' => 4]);

        Quest::create([
            'title' => 'Rajin Menabung',
            'description' => 'Lakukan 5 kali setoran',
            'xp_reward' => 50,
            'type' => 'deposit_count',
            'criteria' => ['count' => 5],
            'active' => true,
        ]);

        Quest::create([
            'title' => 'Saldo Pertama',
            'description' => 'Capai saldo Rp 50.000',
            'xp_reward' => 30,
            'type' => 'savings_milestone',
            'criteria' => ['amount' => 50000],
            'active' => true,
        ]);

        Quest::create([
            'title' => 'Pelajar Teladan',
            'description' => 'Capai saldo Rp 200.000',
            'xp_reward' => 100,
            'type' => 'savings_milestone',
            'criteria' => ['amount' => 200000],
            'active' => true,
        ]);

        Quest::create([
            'title' => 'Konsisten Menabung',
            'description' => 'Menabung 3 hari berturut-turut',
            'xp_reward' => 75,
            'type' => 'streak',
            'criteria' => ['days' => 3],
            'active' => true,
        ]);
    }
}
