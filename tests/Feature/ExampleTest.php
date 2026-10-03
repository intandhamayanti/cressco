<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_design_system_preview_returns_successful_response(): void
    {
        // 1. Text Field
        $responseText = $this->get('/design-system?tab=text-field');
        $responseText->assertStatus(200);
        $responseText->assertSee('Text Field');
        $responseText->assertSee('Drop Down');
        $responseText->assertSee('Pin Number');

        // 2. Typography
        $responseTypography = $this->get('/design-system?tab=typography');
        $responseTypography->assertStatus(200);
        $responseTypography->assertSee('DM Sans');
        $responseTypography->assertSee('Typography');

        // 3. Color
        $responseColor = $this->get('/design-system?tab=color');
        $responseColor->assertStatus(200);
        $responseColor->assertSee('Terracotta');
        $responseColor->assertSee('Color');

        // 4. Button Section Test
        $responseButton = $this->get('/design-system/button');
        $responseButton->assertStatus(200);
        $responseButton->assertSee('Button');
        $responseButton->assertSee('Check Box');
        $responseButton->assertSee('Radio Button');
        $responseButton->assertSee('Toggle Button');
        $responseButton->assertSee('Social Button');
        $responseButton->assertSee('Sign In with Google');
        $responseButton->assertSee('Sign In with Apple');

        // 5. Navigation Section Test
        $responseNav = $this->get('/design-system/navigation');
        $responseNav->assertStatus(200);
        $responseNav->assertSee('Navigation');
        $responseNav->assertSee('Sidebar (5 State Specifications)');
        $responseNav->assertSee('Top Bar (Breadcrumb Hierarchies)');
        $responseNav->assertSee('Navigation Item');
        $responseNav->assertSee('Title / Page Header');
        $responseNav->assertSee('Storage Almost Full!');
        $responseNav->assertSee('Upgrade Pro');

        // 6. UI Kit Internal Navigation Sidebar Test
        $responseNav->assertSee('Style Guide');
        $responseNav->assertSee('Components');
        $responseNav->assertSee('Documentation');
    }
}
