<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationTest extends TestCase
{
    public function test_openapi_spec_is_available(): void
    {
        $this->getJson('/api/openapi.json')
            ->assertOk()
            ->assertJsonPath('info.title', 'Mini Binance API')
            ->assertJsonPath('paths./register.post.summary', 'Registrar usuário');
    }

    public function test_swagger_ui_is_available(): void
    {
        $this->get('/api/docs')
            ->assertOk()
            ->assertSee('SwaggerUIBundle', false)
            ->assertSee('/api/openapi.json', false);
    }
}
