<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Win;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-03 12:00:00'));
        $this->category = Category::create(['name' => 'Health', 'slug' => 'health']);
    }

    public function test_no_wins_returns_zero_statistics_with_the_expected_shape(): void
    {
        $this->getJson('/api/stats')->assertOk()->assertExactJson([
            'total_wins' => 0,
            'wins_this_week' => 0,
            'current_streak' => 0,
        ]);
    }

    #[DataProvider('streakCases')]
    public function test_current_streak_follows_calendar_days(array $dates, int $expectedStreak): void
    {
        $this->createWins($dates);

        $this->getJson('/api/stats')
            ->assertOk()
            ->assertJsonPath('total_wins', count($dates))
            ->assertJsonPath('current_streak', $expectedStreak);
    }

    public function test_this_week_counts_win_dates_from_monday_through_sunday(): void
    {
        $this->createWins([
            '2026-09-27', // Previous Sunday.
            '2026-09-28', // Current Monday.
            '2026-10-03', // Today.
            '2026-10-04', // Current Sunday.
            '2026-10-05', // Next Monday.
        ]);

        $this->getJson('/api/stats')->assertOk()->assertExactJson([
            'total_wins' => 5,
            'wins_this_week' => 3,
            'current_streak' => 1,
        ]);
    }

    public function test_the_week_resets_on_monday_while_the_streak_continues(): void
    {
        $this->travelTo(Carbon::parse('2026-10-05 00:00:00'));
        $this->createWins(['2026-10-04', '2026-10-04', '2026-10-05']);

        $this->getJson('/api/stats')->assertOk()->assertExactJson([
            'total_wins' => 3,
            'wins_this_week' => 1,
            'current_streak' => 2,
        ]);
    }

    public function test_a_streak_continues_across_a_year_boundary(): void
    {
        $this->travelTo(Carbon::parse('2027-01-01 12:00:00'));
        $this->createWins(['2026-12-30', '2026-12-31', '2027-01-01']);

        $this->getJson('/api/stats')->assertOk()->assertExactJson([
            'total_wins' => 3,
            'wins_this_week' => 3,
            'current_streak' => 3,
        ]);
    }

    public static function streakCases(): array
    {
        return [
            'multiple wins on one day' => [['2026-10-03', '2026-10-03', '2026-10-03'], 1],
            'consecutive days ending today' => [['2026-09-30', '2026-10-01', '2026-10-02', '2026-10-03'], 4],
            'consecutive days ending yesterday' => [['2026-09-30', '2026-10-01', '2026-10-02'], 3],
            'a gap breaks the streak' => [['2026-09-29', '2026-09-30', '2026-10-02', '2026-10-03'], 2],
            'older wins do not form a current streak' => [['2026-09-30', '2026-10-01'], 0],
            'future wins do not start a streak' => [['2026-10-04', '2026-10-05'], 0],
            'future dates and repeated days do not extend an active streak' => [['2026-10-02', '2026-10-02', '2026-10-03', '2026-10-03', '2026-10-04'], 2],
        ];
    }

    private function createWins(array $dates): void
    {
        foreach ($dates as $date) {
            Win::create([
                'title' => 'Went for a walk',
                'category_id' => $this->category->id,
                'win_date' => $date,
            ]);
        }
    }
}
