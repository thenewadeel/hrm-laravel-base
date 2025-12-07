<?php

use App\Models\User;
use App\View\Components\Navigation\UserProfile;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserProfileTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new UserProfile;

        expect($component)->toBeInstanceOf(UserProfile::class);
    }

    #[Test]
    public function it_has_default_null_user()
    {
        $component = new UserProfile;

        expect($component->user)->toBeNull();
    }

    #[Test]
    public function it_has_default_show_role_true()
    {
        $component = new UserProfile;

        expect($component->showRole)->toBeTrue();
    }

    #[Test]
    public function it_has_default_show_status_true()
    {
        $component = new UserProfile;

        expect($component->showStatus)->toBeTrue();
    }

    #[Test]
    public function it_has_default_show_avatar_true()
    {
        $component = new UserProfile;

        expect($component->showAvatar)->toBeTrue();
    }

    #[Test]
    public function it_has_default_size_md()
    {
        $component = new UserProfile;

        expect($component->size)->toBe('md');
    }

    #[Test]
    public function it_accepts_user_in_constructor()
    {
        $user = new User(['name' => 'Test User']);
        $component = new UserProfile($user);

        expect($component->user)->toBe($user);
    }

    #[Test]
    public function it_accepts_show_role_parameter()
    {
        $component = new UserProfile(null, false);

        expect($component->showRole)->toBeFalse();
    }

    #[Test]
    public function it_accepts_show_status_parameter()
    {
        $component = new UserProfile(null, true, false);

        expect($component->showStatus)->toBeFalse();
    }

    #[Test]
    public function it_accepts_show_avatar_parameter()
    {
        $component = new UserProfile(null, true, true, false);

        expect($component->showAvatar)->toBeFalse();
    }

    #[Test]
    public function it_accepts_size_parameter()
    {
        $component = new UserProfile(null, true, true, true, 'lg');

        expect($component->size)->toBe('lg');
    }

    #[Test]
    public function it_accepts_all_parameters()
    {
        $user = new User(['name' => 'Test User']);
        $component = new UserProfile($user, false, false, false, 'sm');

        expect($component->user)->toBe($user);
        expect($component->showRole)->toBeFalse();
        expect($component->showStatus)->toBeFalse();
        expect($component->showAvatar)->toBeFalse();
        expect($component->size)->toBe('sm');
    }

    #[Test]
    public function it_handles_valid_sizes()
    {
        $sizes = ['sm', 'md', 'lg'];

        foreach ($sizes as $size) {
            $component = new UserProfile(null, true, true, true, $size);
            expect($component->size)->toBe($size);
        }
    }

    #[Test]
    public function it_renders_user_profile_view()
    {
        $component = new UserProfile;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.user-profile');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new UserProfile;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    }
}
