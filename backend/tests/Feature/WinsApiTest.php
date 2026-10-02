<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Win;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WinsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    public function test_categories_are_returned_as_a_list_without_timestamps(): void
    {
        $response = $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonCount(7)
            ->assertJsonPath('0.name', 'Career')
            ->assertJsonPath('1.slug', 'health');

        $this->assertSame(['id', 'name', 'slug'], array_keys($response->json('0')));
    }

    public function test_wins_are_listed_by_win_date_then_creation_time(): void
    {
        $this->travelTo(Carbon::parse('2026-10-02 08:00:00'));
        $morningWin = Win::create($this->payload(['title' => 'Morning walk']));

        $this->travelTo(Carbon::parse('2026-10-02 10:00:00'));
        $laterWin = Win::create($this->payload(['title' => 'Went to the gym']));

        $this->travelTo(Carbon::parse('2026-10-02 12:00:00'));
        $olderWin = Win::create($this->payload(['win_date' => '2026-10-01']));

        $this->getJson('/api/wins')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', $laterWin->id)
            ->assertJsonPath('data.1.id', $morningWin->id)
            ->assertJsonPath('data.2.id', $olderWin->id)
            ->assertJsonPath('data.0.win_date', '2026-10-02')
            ->assertJsonPath('data.0.category.slug', 'health');
    }

    public function test_an_empty_list_matches_the_api_contract(): void
    {
        $this->getJson('/api/wins')->assertOk()->assertExactJson(['data' => []]);
    }

    public function test_a_win_can_be_created(): void
    {
        $payload = $this->payload();

        $response = $this->postJson('/api/wins', $payload)
            ->assertCreated()
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.description', $payload['description'])
            ->assertJsonPath('data.win_date', $payload['win_date'])
            ->assertJsonPath('data.category.id', $payload['category_id']);

        $this->assertDatabaseCount('wins', 1);
        $this->assertDatabaseHas('wins', [
            'id' => $response->json('data.id'),
            'title' => $payload['title'],
            'description' => $payload['description'],
            'category_id' => $payload['category_id'],
        ]);
    }

    public function test_a_description_is_optional(): void
    {
        $payload = $this->payload();
        unset($payload['description']);

        $this->postJson('/api/wins', $payload)
            ->assertCreated()
            ->assertJsonPath('data.description', null);
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_create_requests_return_validation_errors(array $overrides, array $fields): void
    {
        $this->postJson('/api/wins', $this->payload($overrides))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($fields);

        $this->assertDatabaseCount('wins', 0);
    }

    public function test_validation_errors_are_json_even_without_an_accept_header(): void
    {
        $this->post('/api/wins', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'category_id', 'win_date']);
    }

    public function test_a_win_can_be_read_with_the_expected_resource_shape(): void
    {
        $win = Win::create($this->payload());
        $category = $win->category;

        $this->getJson('/api/wins/'.$win->id)->assertOk()->assertExactJson([
            'data' => [
                'id' => $win->id,
                'title' => 'Went to the gym',
                'description' => 'Trained even though I felt tired.',
                'win_date' => '2026-10-02',
                'category' => [
                    'id' => $category->id,
                    'name' => 'Health',
                    'slug' => 'health',
                ],
            ],
        ]);
    }

    public function test_a_win_can_be_updated_and_its_description_cleared(): void
    {
        $win = Win::create($this->payload());
        $payload = $this->payload([
            'title' => 'Updated my portfolio',
            'description' => null,
            'category_id' => Category::where('slug', 'career')->firstOrFail()->id,
            'win_date' => '2026-10-01',
        ]);

        $this->putJson('/api/wins/'.$win->id, $payload)
            ->assertOk()
            ->assertJsonPath('data.id', $win->id)
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.category.slug', 'career')
            ->assertJsonPath('data.win_date', '2026-10-01');

        $this->assertDatabaseCount('wins', 1);
        $this->assertDatabaseHas('wins', [
            'id' => $win->id,
            'title' => $payload['title'],
            'description' => null,
            'category_id' => $payload['category_id'],
        ]);
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_updates_leave_the_win_unchanged(array $overrides, array $fields): void
    {
        $win = Win::create($this->payload());
        $originalAttributes = $win->fresh()->getAttributes();

        $this->putJson('/api/wins/'.$win->id, $this->payload($overrides))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($fields);

        $this->assertSame($originalAttributes, $win->fresh()->getAttributes());
    }

    public function test_a_win_can_be_deleted(): void
    {
        $win = Win::create($this->payload());

        $this->deleteJson('/api/wins/'.$win->id)->assertNoContent();

        $this->assertDatabaseMissing('wins', ['id' => $win->id]);
    }

    public function test_missing_wins_return_json_not_found_for_read_update_and_delete(): void
    {
        $this->get('/api/wins/999')->assertNotFound()->assertJsonStructure(['message']);
        $this->putJson('/api/wins/999', $this->payload())->assertNotFound()->assertJsonStructure(['message']);
        $this->deleteJson('/api/wins/999')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_wins_can_be_filtered_by_category_slug(): void
    {
        $healthWin = Win::create($this->payload());
        Win::create($this->payload(['category_id' => Category::where('slug', 'career')->firstOrFail()->id]));

        $this->getJson('/api/wins?category=health')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $healthWin->id);
    }

    public function test_search_matches_titles_and_descriptions_without_case_sensitivity(): void
    {
        Win::create($this->payload());
        Win::create($this->payload(['title' => 'Showed up for training', 'description' => 'Made it to the GYM.']));
        Win::create($this->payload(['title' => 'Went for a walk', 'description' => null]));

        $this->getJson('/api/wins?search=GyM')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_search_and_category_filters_are_combined(): void
    {
        $healthWin = Win::create($this->payload());
        $careerId = Category::where('slug', 'career')->firstOrFail()->id;
        Win::create($this->payload(['category_id' => $careerId]));
        Win::create($this->payload(['title' => 'Finished an application', 'description' => 'Sent my CV to a gym.', 'category_id' => $careerId]));
        Win::create($this->payload(['title' => 'Went for a walk', 'description' => null]));

        $this->getJson('/api/wins?search=gym&category=health')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $healthWin->id);
    }

    public function test_unmatched_filters_return_an_empty_list(): void
    {
        Win::create($this->payload());

        $this->getJson('/api/wins?search=unmatched')->assertOk()->assertExactJson(['data' => []]);
        $this->getJson('/api/wins?category=unknown')->assertOk()->assertExactJson(['data' => []]);
    }

    public function test_zero_is_a_valid_search_term(): void
    {
        $win = Win::create($this->payload(['title' => 'Finished exercise 0', 'description' => null]));
        Win::create($this->payload());

        $this->getJson('/api/wins?search=0')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $win->id);
    }

    public function test_array_filters_return_validation_errors(): void
    {
        $this->getJson('/api/wins?search[]=gym')->assertUnprocessable()->assertJsonValidationErrors(['search']);
        $this->getJson('/api/wins?category[]=health')->assertUnprocessable()->assertJsonValidationErrors(['category']);
    }

    public static function invalidPayloads(): array
    {
        return [
            'required fields' => [['title' => null, 'category_id' => null, 'win_date' => null], ['title', 'category_id', 'win_date']],
            'title too long' => [['title' => str_repeat('a', 121)], ['title']],
            'missing category' => [['category_id' => 999], ['category_id']],
            'invalid calendar date' => [['win_date' => '2026-02-30'], ['win_date']],
            'invalid description' => [['description' => ['unexpected']], ['description']],
        ];
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Went to the gym',
            'description' => 'Trained even though I felt tired.',
            'category_id' => Category::where('slug', 'health')->firstOrFail()->id,
            'win_date' => '2026-10-02',
        ], $overrides);
    }
}
