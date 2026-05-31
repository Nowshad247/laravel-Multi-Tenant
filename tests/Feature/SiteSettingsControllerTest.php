<?php

use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Settings::clearStaticCache();
});

function superAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    return $user;
}

it('shows the site settings page to a Super Admin', function () {
    Settings::updateOrCreate(['key' => 'site_name'], ['value' => 'TestSite']);

    $response = $this
        ->actingAs(superAdmin())
        ->get(route('settings.index'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('configurationWebsite/index')
            ->has('siteSettings')
    );
});

it('denies the settings page to non-Super Admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.index'))
        ->assertForbidden();
});

it('updates text settings', function () {
    $this->actingAs(superAdmin())
        ->post(route('settings.update'), [
            'site_name' => 'My Updated Site',
            'site_title' => 'Updated Title',
            'timezone' => 'UTC+06:00',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'default_language' => 'en',
            'currency' => 'BDT',
            'currency_symbol' => '৳',
        ])
        ->assertRedirect();

    expect(Settings::getValue('site_name'))->toBe('My Updated Site');
    expect(Settings::getValue('site_title'))->toBe('Updated Title');
});

it('updates contact and social fields', function () {
    $this->actingAs(superAdmin())
        ->post(route('settings.update'), [
            'contact_email' => 'admin@example.com',
            'contact_phone' => '+880 1234 567890',
            'facebook_url' => 'https://facebook.com/testpage',
        ])
        ->assertRedirect();

    expect(Settings::getValue('contact_email'))->toBe('admin@example.com');
    expect(Settings::getValue('contact_phone'))->toBe('+880 1234 567890');
    expect(Settings::getValue('facebook_url'))->toBe('https://facebook.com/testpage');
});

it('uploads and stores site logo', function () {
    Storage::fake('public');

    $this->actingAs(superAdmin())
        ->post(route('settings.update'), [
            'site_logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ])
        ->assertRedirect();

    $storedUrl = Settings::getValue('site_logo');
    expect($storedUrl)->not->toBeNull();
    expect($storedUrl)->toContain('site-settings');
});

it('replaces old logo when a new one is uploaded', function () {
    Storage::fake('public');

    $first = UploadedFile::fake()->image('logo1.png');
    $this->actingAs(superAdmin())
        ->post(route('settings.update'), ['site_logo' => $first])
        ->assertRedirect();

    $firstUrl = Settings::getValue('site_logo');

    $second = UploadedFile::fake()->image('logo2.png');
    $this->actingAs(superAdmin())
        ->post(route('settings.update'), ['site_logo' => $second])
        ->assertRedirect();

    $secondUrl = Settings::getValue('site_logo');
    expect($secondUrl)->not->toBe($firstUrl);
});

it('rejects invalid timezone', function () {
    $this->actingAs(superAdmin())
        ->post(route('settings.update'), ['timezone' => 'Invalid/Zone'])
        ->assertSessionHasErrors('timezone');
});

it('rejects invalid currency code', function () {
    $this->actingAs(superAdmin())
        ->post(route('settings.update'), ['currency' => 'XYZ'])
        ->assertSessionHasErrors('currency');
});

it('skips updating a field when the value has not changed', function () {
    Settings::updateOrCreate(['key' => 'site_name'], ['value' => 'Unchanged']);

    $updatedAtBefore = Settings::where('key', 'site_name')->value('updated_at');

    $this->travel(2)->seconds();

    $this->actingAs(superAdmin())
        ->post(route('settings.update'), ['site_name' => 'Unchanged'])
        ->assertRedirect();

    $updatedAtAfter = Settings::where('key', 'site_name')->value('updated_at');
    expect($updatedAtAfter)->toEqual($updatedAtBefore);
});
