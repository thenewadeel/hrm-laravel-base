<?php

use App\Models\User;
use App\View\Components\Navigation\Breadcrumb;
use App\View\Components\Navigation\Dropdown;
use App\View\Components\Navigation\Link;
use App\View\Components\Navigation\Search;
use App\View\Components\Navigation\ThemeToggle;
use App\View\Components\Navigation\UserProfile;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class NavigationEdgeCasesTest extends TestCase
{
    #[Test]
    public function navigation_link_handles_empty_href()
    {
        $component = new Link('');

        expect($component->href)->toBe('');
    }

    #[Test]
    public function navigation_link_handles_null_values()
    {
        $component = new Link('#', false, null, null, '_self');

        expect($component->icon)->toBeNull();
        expect($component->badge)->toBeNull();
    }

    #[Test]
    public function navigation_link_handles_special_characters_in_href()
    {
        $specialUrl = 'https://example.com/path?param=value&other=test#section';
        $component = new Link($specialUrl);

        expect($component->href)->toBe($specialUrl);
    }

    #[Test]
    public function breadcrumb_handles_malformed_pages_array()
    {
        $malformedPages = [
            'invalid-string',
            ['name' => 'Valid Page'],
            null,
            ['url' => '/invalid'], // Missing name
        ];

        $component = new Breadcrumb($malformedPages);

        expect($component->pages)->toBe($malformedPages);
    }

    #[Test]
    public function user_profile_handles_user_without_name()
    {
        $user = new User;
        $component = new UserProfile($user);

        expect($component->user)->toBe($user);
    }

    #[Test]
    public function user_profile_handles_invalid_size()
    {
        $component = new UserProfile(null, true, true, true, 'invalid');

        expect($component->size)->toBe('invalid');
    }

    #[Test]
    public function dropdown_handles_invalid_alignment()
    {
        $component = new Dropdown('invalid-alignment');

        expect($component->align)->toBe('invalid-alignment');
    }

    #[Test]
    public function dropdown_handles_invalid_width()
    {
        $component = new Dropdown('left', 'invalid-width');

        expect($component->width)->toBe('invalid-width');
    }

    #[Test]
    public function theme_toggle_handles_boolean_conversion()
    {
        $component1 = new ThemeToggle(1);
        expect($component1->darkMode)->toBeTrue();

        $component2 = new ThemeToggle(0);
        expect($component2->darkMode)->toBeFalse();

        $component3 = new ThemeToggle('true');
        expect($component3->darkMode)->toBeTrue();
    }

    #[Test]
    public function search_handles_empty_method()
    {
        $component = new Search('Search...', '/search', '');

        expect($component->method)->toBe('');
    }

    #[Test]
    public function search_handles_special_characters_in_placeholder()
    {
        $specialPlaceholder = 'Search ñiño & "special" chars!';
        $component = new Search($specialPlaceholder);

        expect($component->placeholder)->toBe($specialPlaceholder);
    }

    #[Test]
    public function navigation_components_handle_large_data()
    {
        $largePages = [];
        for ($i = 0; $i < 1000; $i++) {
            $largePages[] = ['name' => "Page {$i}", 'url' => "/page/{$i}"];
        }

        $component = new Breadcrumb($largePages);

        expect($component->pages)->toHaveCount(1000);
    }

    #[Test]
    public function navigation_components_handle_unicode_characters()
    {
        $unicodeText = '🏠 🌟 Navigation with émojis & ñiño';
        $component = new Link('#', false, $unicodeText);

        expect($component->icon)->toBe($unicodeText);
    }

    #[Test]
    public function navigation_components_handle_extremely_long_strings()
    {
        $longString = str_repeat('a', 10000);
        $component = new Link($longString);

        expect($component->href)->toBe($longString);
    }

    #[Test]
    public function navigation_components_handle_json_injection_attempts()
    {
        $maliciousJson = '{"malicious": "script"}';
        $component = new Link($maliciousJson);

        expect($component->href)->toBe($maliciousJson);
    }

    #[Test]
    public function navigation_components_handle_html_injection_attempts()
    {
        $maliciousHtml = '<script>alert("xss")</script>';
        $component = new Link($maliciousHtml);

        expect($component->href)->toBe($maliciousHtml);
    }

    #[Test]
    public function navigation_components_handle_javascript_injection_attempts()
    {
        $maliciousJs = 'javascript:alert("xss")';
        $component = new Link($maliciousJs);

        expect($component->href)->toBe($maliciousJs);
    }

    #[Test]
    public function navigation_components_handle_sql_injection_attempts()
    {
        $maliciousSql = "'; DROP TABLE users; --";
        $component = new Link($maliciousSql);

        expect($component->href)->toBe($maliciousSql);
    }

    #[Test]
    public function navigation_components_handle_null_bytes()
    {
        $nullByteString = "test\x00string";
        $component = new Link($nullByteString);

        expect($component->href)->toBe($nullByteString);
    }

    #[Test]
    public function navigation_components_handle_control_characters()
    {
        $controlCharString = "test\r\n\tstring";
        $component = new Link($controlCharString);

        expect($component->href)->toBe($controlCharString);
    }
}
