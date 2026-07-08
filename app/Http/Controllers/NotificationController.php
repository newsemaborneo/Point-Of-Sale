<?php
namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Product;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // 19. Notifikasi: stok habis, produk kedaluwarsa, target penjualan, pembayaran jatuh tempo

    public function index(Request $request)
    {
        $notifications = AppNotification::when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->is_read !== null, fn ($q) => $q->where('is_read', $request->boolean('is_read')))
            ->latest()
            ->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(AppNotification $notification)
    {
        $notification->update(['is_read' => true]);
        return redirect()->route('notifications.index')->with('success', 'Notifikasi berhasil ditandai sudah dibaca.');
    }

    public function markAllAsRead(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)->update(['is_read' => true]);
        return redirect()->route('notifications.index')->with('success', 'Semua notifikasi berhasil ditandai sudah dibaca.');
    }

    public function generateSystemNotifications(Request $request)
    {
        $lowStock = Product::with('stocks')->get()->filter(fn (Product $p) => $p->totalStock() <= $p->min_stock);

        foreach ($lowStock as $product) {
            AppNotification::firstOrCreate([
                'type' => $product->totalStock() <= 0 ? 'out_of_stock' : 'low_stock',
                'reference_type' => Product::class,
                'reference_id' => $product->id,
                'is_read' => false,
            ], [
                'title' => $product->totalStock() <= 0 ? 'Stok Habis' : 'Stok Menipis',
                'message' => "Produk {$product->name} tersisa {$product->totalStock()} unit",
            ]);
        }

        $expiring = Product::where('has_expiry', true)
            ->whereNotNull('expired_date')
            ->whereDate('expired_date', '<=', now()->addDays(30))
            ->get();

        foreach ($expiring as $product) {
            AppNotification::firstOrCreate([
                'type' => 'expired_product',
                'reference_type' => Product::class,
                'reference_id' => $product->id,
                'is_read' => false,
            ], [
                'title' => 'Produk Mendekati Kedaluwarsa',
                'message' => "Produk {$product->name} kedaluwarsa pada {$product->expired_date}",
            ]);
        }

        return redirect()->route('notifications.index')->with('success', 'Notifikasi sistem berhasil digenerate.');
    }
}
