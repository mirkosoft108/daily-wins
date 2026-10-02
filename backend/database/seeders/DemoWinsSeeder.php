<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Win;
use Illuminate\Database\Seeder;

class DemoWinsSeeder extends Seeder
{
    public function run(): void
    {
        $today = today();

        $wins = [
            [
                'title' => 'Went to the gym',
                'description' => 'Trained even though I felt tired.',
                'category' => 'health',
                'days_ago' => 0,
            ],
            [
                'title' => 'Finished a Laravel exercise',
                'description' => 'Practiced migrations and Eloquent relationships.',
                'category' => 'learning',
                'days_ago' => 0,
            ],
            [
                'title' => 'Applied to a backend role',
                'description' => 'Updated my CV and sent a thoughtful application.',
                'category' => 'career',
                'days_ago' => 1,
            ],
            [
                'title' => 'Meditated for 20 minutes',
                'description' => 'Made time to pause before starting the day.',
                'category' => 'mindfulness',
                'days_ago' => 1,
            ],
            [
                'title' => 'Started a conversation with someone new',
                'description' => 'Introduced myself to a neighbor on my walk.',
                'category' => 'social',
                'days_ago' => 2,
            ],
            [
                'title' => 'Cooked instead of ordering food',
                'description' => 'Prepared dinner with ingredients I already had.',
                'category' => 'personal',
                'days_ago' => 3,
            ],
            [
                'title' => 'Read a chapter about Vue',
                'description' => 'Learned how computed properties work.',
                'category' => 'learning',
                'days_ago' => 4,
            ],
            [
                'title' => 'Finished an overdue errand',
                'description' => null,
                'category' => 'other',
                'days_ago' => 6,
            ],
        ];

        foreach ($wins as $win) {
            $category = Category::where('slug', $win['category'])->firstOrFail();

            Win::firstOrCreate(
                ['title' => $win['title'], 'category_id' => $category->id],
                [
                    'description' => $win['description'],
                    'win_date' => $today->copy()->subDays($win['days_ago'])->toDateString(),
                ],
            );
        }
    }
}
