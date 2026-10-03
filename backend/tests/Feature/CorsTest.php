<?php

namespace Tests\Feature;

use Tests\TestCase;

class CorsTest extends TestCase
{
    public function test_configured_frontend_origins_can_read_the_api(): void
    {
        config(['cors' => $this->corsConfig('production', ' https://demo.web.app, https://demo.firebaseapp.com, ,*')]);

        foreach (['https://demo.web.app', 'https://demo.firebaseapp.com'] as $origin) {
            $this->withHeader('Origin', $origin)->getJson('/api/health')
                ->assertOk()
                ->assertHeader('Access-Control-Allow-Origin', $origin)
                ->assertHeaderMissing('Access-Control-Allow-Credentials');
        }
    }

    public function test_an_unlisted_origin_does_not_receive_cors_permission(): void
    {
        config(['cors' => $this->corsConfig('production', 'https://demo.web.app,https://demo.firebaseapp.com')]);

        $this->withHeader('Origin', 'https://unlisted.example')->getJson('/api/health')
            ->assertOk()
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_a_single_allowed_origin_is_never_replaced_by_an_unlisted_origin(): void
    {
        config(['cors' => $this->corsConfig('production', 'https://demo.web.app')]);

        // A fixed allowed-origin header still denies browsers from any other origin.
        $this->withHeader('Origin', 'https://unlisted.example')->getJson('/api/health')
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'https://demo.web.app');
    }

    public function test_a_preflight_allows_crud_methods_and_json_headers(): void
    {
        config(['cors' => $this->corsConfig('production', 'https://demo.web.app')]);

        $response = $this->withHeaders([
            'Origin' => 'https://demo.web.app',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type,accept',
        ])->options('/api/wins');

        $response->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'https://demo.web.app')
            ->assertHeader('Access-Control-Max-Age', '600');
        $this->assertStringContainsString('POST', $response->headers->get('Access-Control-Allow-Methods'));
        $this->assertStringContainsString('PUT', $response->headers->get('Access-Control-Allow-Methods'));
        $this->assertStringContainsString('DELETE', $response->headers->get('Access-Control-Allow-Methods'));
        $this->assertStringContainsString('content-type', strtolower($response->headers->get('Access-Control-Allow-Headers')));
    }

    public function test_validation_errors_are_readable_by_the_frontend(): void
    {
        config(['cors' => $this->corsConfig('production', 'https://demo.web.app')]);

        $this->withHeader('Origin', 'https://demo.web.app')->postJson('/api/wins', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'category_id', 'win_date'])
            ->assertHeader('Access-Control-Allow-Origin', 'https://demo.web.app');
    }

    public function test_production_without_origins_and_wildcards_deny_cors(): void
    {
        foreach ([null, '', '*', 'https://*.web.app'] as $origins) {
            $cors = $this->corsConfig('production', $origins);
            $this->assertSame([], $cors['allowed_origins']);
        }

        config(['cors' => $this->corsConfig('production', null)]);
        $this->withHeader('Origin', 'http://localhost:5173')->getJson('/api/health')
            ->assertOk()
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_local_defaults_support_vite_dev_and_preview(): void
    {
        $this->assertSame([
            'http://localhost:5173', 'http://127.0.0.1:5173',
            'http://localhost:4173', 'http://127.0.0.1:4173',
        ], $this->corsConfig('local', null)['allowed_origins']);
    }

    private function corsConfig(string $environment, ?string $origins): array
    {
        $previousEnv = $_ENV;
        $previousServer = $_SERVER;
        $variables = ['APP_ENV' => $environment, 'FRONTEND_ORIGINS' => $origins];
        $previousValues = array_map(fn (string $name) => getenv($name), array_keys($variables));

        try {
            foreach ($variables as $name => $value) {
                if ($value === null) {
                    unset($_ENV[$name], $_SERVER[$name]);
                    putenv($name);
                } else {
                    $_ENV[$name] = $_SERVER[$name] = $value;
                    putenv($name.'='.$value);
                }
            }

            return require config_path('cors.php');
        } finally {
            $_ENV = $previousEnv;
            $_SERVER = $previousServer;
            foreach (array_keys($variables) as $index => $name) {
                $previousValues[$index] === false
                    ? putenv($name)
                    : putenv($name.'='.$previousValues[$index]);
            }
        }
    }
}
