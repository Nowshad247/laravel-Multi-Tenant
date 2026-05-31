<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSiteSettingsRequest;
use App\Models\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index(): \Inertia\Response
    {
        $defaults = [
            'site_name' => '',
            'site_title' => '',
            'site_description' => '',
            'site_keywords' => '',
            'site_author' => '',
            'site_url' => '',
            'site_logo' => null,
            'site_icon' => null,
            'site_cover_image' => null,
            'site_favicon' => null,
            'contact_email' => '',
            'contact_phone' => '',
            'address' => '',
            'facebook_url' => '',
            'twitter_url' => '',
            'linkedin_url' => '',
            'instagram_url' => '',
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
            'google_analytics_id' => '',
            'maintenance_mode' => 'off',
            'timezone' => 'UTC+06:00',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'default_language' => 'en',
            'default_user_role' => 'student',
            'currency' => 'BDT',
            'currency_symbol' => '৳',
        ];

        $saved = Settings::pluck('value', 'key')->all();
        $siteSettings = array_merge($defaults, $saved);

        return inertia('configurationWebsite/index', compact('siteSettings'));
    }

    public function update(UpdateSiteSettingsRequest $request)
    {
        $existing = Settings::pluck('value', 'key')->toArray();
        $changed = false;

        $validatedData = $request->validated();

        $fileKeys = ['site_logo', 'site_icon', 'site_cover_image', 'site_favicon'];
        $textKeys = array_diff(array_keys($validatedData), $fileKeys);

        foreach ($textKeys as $key) {
            $value = $validatedData[$key];

            if ($key === 'enable_registration') {
                $value = in_array((string) $value, ['1', 'true'], true) ? 'true' : 'false';
            }

            $old = $existing[$key] ?? null;
            $new = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;

            if (($old ?? '') === $new) {
                continue;
            }

            settings::updateOrCreate(['key' => $key], ['value' => $new]);
            $changed = true;
        }

        foreach ($fileKeys as $key) {
            if (! $request->hasFile($key)) {
                continue;
            }

            $file = $request->file($key);

            $oldValue = $existing[$key] ?? null;
            $oldPath = $this->storagePathFromSettingValue($oldValue);
            if ($oldPath !== null && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $file->storePublicly('site-settings', 'public');
            $url = Storage::url($path);

            settings::updateOrCreate(['key' => $key], ['value' => $url]);
            $changed = true;
        }

        if ($changed) {
            Cache::forget('settings');
            Cache::forget('site_name');
            Settings::clearStaticCache();
        }

        return redirect()->back()->with('success', 'Site settings updated successfully.');
    }

    private function storagePathFromSettingValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $path = $value;

        $parts = parse_url($value);
        if (is_array($parts) && isset($parts['path'])) {
            $path = $parts['path'];
        }

        if (! str_starts_with($path, '/storage/')) {
            return null;
        }

        return ltrim(substr($path, strlen('/storage/')), '/');
    }
}
