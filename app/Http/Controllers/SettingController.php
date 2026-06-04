<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = CompanySetting::all()->pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name'    => 'required|string|max:100',
            'company_email'   => 'nullable|email|max:100',
            'company_phone'   => 'nullable|string|max:30',
            'company_address' => 'nullable|string|max:255',
            'company_city'    => 'nullable|string|max:100',
            'company_website' => 'nullable|string|max:100',
            'company_npwp'    => 'nullable|string|max:30',
            'company_tagline' => 'nullable|string|max:150',
            'invoice_footer'  => 'nullable|string|max:255',
            'invoice_terms'   => 'nullable|string|max:500',
            'company_logo'    => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $oldLogo = CompanySetting::get('company_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('company_logo')->store('logos', 'public');
            CompanySetting::updateOrCreate(['key' => 'company_logo'], ['value' => $path]);
        }

        $fields = [
            'company_name', 'company_tagline', 'company_address', 'company_city',
            'company_phone', 'company_email', 'company_website', 'company_npwp',
            'invoice_footer', 'invoice_terms',
        ];

        foreach ($fields as $field) {
            CompanySetting::updateOrCreate(
                ['key' => $field],
                ['value' => $request->input($field, '')]
            );
        }

        Cache::forget('company_settings');

        return back()->with('success', 'Pengaturan perusahaan berhasil disimpan.');
    }
}
