<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AuditController extends Controller
{
    // 17. Audit: log aktivitas, riwayat transaksi, riwayat perubahan data, backup database

    public function index(Request $request)
    {
        $logs = ActivityLog::latest()->paginate(20);
        return view('audit.index', compact('logs'));
    }

    public function show(ActivityLog $activityLog)
    {
        return view('audit.show', compact('activityLog'));
    }

    /** Backup database (memicu proses backup, contoh menggunakan spatie/laravel-backup) */
    public function backupDatabase(Request $request)
    {
        // Artisan::call('backup:run', ['--only-db' => true]);
        return redirect()->back()->with('success', 'Backup database belum diimplementasikan.');
    }
}
