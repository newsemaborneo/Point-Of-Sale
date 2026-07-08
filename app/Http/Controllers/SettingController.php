<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // 18. Pengaturan: profil toko, logo, pajak, mata uang, nomor invoice otomatis, template struk, backup/restore

    public function index(Request $request)
    {
        $settings  = Setting::when($request->group, fn ($q) => $q->where('group', $request->group))->get();
        $storeLogo = Setting::get('store_logo');

        // Jam buka/tutup toko
        $storeHours = [
            'enabled'    => (bool) Setting::get('store_hours_enabled', false),
            'open_time'  => Setting::get('store_open_time',  '08:00'),
            'close_time' => Setting::get('store_close_time', '21:00'),
        ];

        return view('settings.index', compact('settings', 'storeLogo', 'storeHours'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.group' => 'nullable|string',
        ]);

        foreach ($data['settings'] as $item) {
            Setting::set($item['key'], $item['value'] ?? null, $item['group'] ?? 'general');
        }

        return redirect()->route('settings')->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);
        $path = $request->file('logo')->store('settings', 'public');
        Setting::set('store_logo', $path, 'general');

        return redirect()->route('settings')->with('success', 'Logo toko berhasil diperbarui.');
    }

    public function updateStoreHours(Request $request)
    {
        $data = $request->validate([
            'store_hours_enabled' => 'nullable|boolean',
            'store_open_time'     => 'required|date_format:H:i',
            'store_close_time'    => 'required|date_format:H:i',
        ]);

        Setting::set('store_hours_enabled', $request->boolean('store_hours_enabled') ? '1' : '0', 'general');
        Setting::set('store_open_time',  $data['store_open_time'],  'general');
        Setting::set('store_close_time', $data['store_close_time'], 'general');

        return redirect()->route('settings')->with('success', 'Jam operasional toko berhasil disimpan.');
    }
}
