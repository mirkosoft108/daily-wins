<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Win;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_categories_and_demo_wins(): void
    {
        $this->travelTo(Carbon::parse('2026-10-02 12:00:00'));

        $this->seed();

        $this->assertSame(
            ['Career', 'Health', 'Learning', 'Mindfulness', 'Other', 'Personal', 'Social'],
            Category::orderBy('name')->pluck('name')->all(),
        );
        $this->assertDatabaseCount('categories', 7);
        $this->assertDatabaseCount('wins', 8);
        $this->assertSame(8, Win::whereHas('category')->count());
        $this->assertSame('2026-10-02', Win::where('title', 'Went to the gym')->firstOrFail()->win_date->toDateString());
        $this->assertSame('2026-09-26', Win::where('title', 'Finished an overdue errand')->firstOrFail()->win_date->toDateString());
    }

    public function test_seeding_again_does_not_duplicate_data_or_overwrite_a_win(): void
    {
        $this->seed();

        $win = Win::firstOrFail();
        $win->update(['description' => 'Edited after the initial seed.']);

        $this->seed();

        $this->assertDatabaseCount('categories', 7);
        $this->assertDatabaseCount('wins', 8);
        $this->assertSame('Edited after the initial seed.', $win->fresh()->description);
    }

    public function test_relationships_work_with_a_nullable_description_and_date_cast(): void
    {
        $category = Category::create(['name' => 'Career', 'slug' => 'career']);

        $win = $category->wins()->create([
            'title' => 'Updated my portfolio',
            'win_date' => '2026-10-02',
        ]);

        $storedWin = $win->fresh();

        $this->assertTrue($storedWin->category->is($category));
        $this->assertTrue($category->wins->first()->is($win));
        $this->assertNull($storedWin->description);
        $this->assertSame('2026-10-02', $storedWin->win_date->toDateString());
    }

    public function test_category_slugs_must_be_unique(): void
    {
        Category::create(['name' => 'Career', 'slug' => 'career']);

        $this->expectException(QueryException::class);

        Category::create(['name' => 'Another career category', 'slug' => 'career']);
    }

    public function test_a_win_cannot_reference_a_missing_category(): void
    {
        $this->expectException(QueryException::class);

        Win::create([
            'title' => 'Updated my portfolio',
            'category_id' => 999,
            'win_date' => '2026-10-02',
        ]);
    }

    public function test_a_category_with_wins_cannot_be_deleted(): void
    {
        $category = Category::create(['name' => 'Career', 'slug' => 'career']);

        $category->wins()->create([
            'title' => 'Updated my portfolio',
            'win_date' => '2026-10-02',
        ]);

        $this->expectException(QueryException::class);

        $category->delete();
    }
}
