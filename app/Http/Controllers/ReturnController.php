<?php

namespace App\Http\Controllers;

use App\Http\Requests\Return\StorePurchaseReturnRequest;
use App\Http\Requests\Return\StoreSaleReturnRequest;
use App\Http\Requests\Return\UpdatePurchaseReturnRequest;
use App\Http\Requests\Return\UpdateSaleReturnRequest;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Services\ReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReturnController extends Controller
{
    protected ReturnService $returnService;

    public function __construct(ReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    public function createSaleReturnForm(Sale $sale)
    {
        $sale->load('items.product');
        return view('returns.sale-return-form', compact('sale'));
    }

    public function storeSaleReturn(StoreSaleReturnRequest $request, Sale $sale)
    {
        $this->returnService->processSaleReturn($sale, $request->validated(), $request->user()?->id);
        return redirect()->route('sale-returns.index')->with('success', 'Retur penjualan berhasil dicatat.');
    }

    public function indexSaleReturns(Request $request)
    {
        $returnsQuery = SaleReturn::with(['sale', 'sale.customer', 'user']);

        $user = Auth::user();

        // Admin melihat semua return dari semua cabang
        // Supervisor melihat semua return dari cabang mereka
        // Kasir hanya melihat return yang mereka buat
        if ($user->hasRole('admin')) {
            // Admin: semua return
        } elseif ($user->hasRole('supervisor')) {
            // Supervisor: return dari cabang yang sama
            $returnsQuery->whereHas('sale', function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            });
        } else {
            
            // Kasir: hanya return yang mereka buat
            $returnsQuery->where('user_id', Auth::id());
        }

        $returns = $returnsQuery->latest()->paginate(20);
        return view('returns.sale-returns', compact('returns'));
    }

    public function createPurchaseReturnForm(Purchase $purchase)
    {
        $purchase->load('items.product');
        return view('returns.purchase-return-form', compact('purchase'));
    }

    public function storePurchaseReturn(StorePurchaseReturnRequest $request, Purchase $purchase)
    {
        $this->returnService->processPurchaseReturn($purchase, $request->validated(), $request->user()?->id);
        return redirect()->route('purchase-returns.index')->with('success', 'Retur pembelian berhasil dicatat.');
    }

    public function indexPurchaseReturns(Request $request)
    {
        $returns = PurchaseReturn::with(['purchase', 'purchase.supplier'])->latest()->paginate(20);
        return view('returns.purchase-returns', compact('returns'));
    }

    public function showSaleReturn(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale.customer', 'items.product', 'user']);
        return view('returns.sale-return-show', compact('saleReturn'));
    }

    public function editSaleReturn(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale', 'items.product']);
        return view('returns.sale-return-edit', compact('saleReturn'));
    }

    public function updateSaleReturn(UpdateSaleReturnRequest $request, SaleReturn $saleReturn)
    {
        $saleReturn->update($request->validated());
        return redirect()->route('sale-returns.index')->with('success', 'Data retur penjualan berhasil diperbarui.');
    }

    public function destroySaleReturn(SaleReturn $saleReturn)
    {
        $this->returnService->cancelSaleReturn($saleReturn, Auth::id());
        return redirect()->route('sale-returns.index')->with('success', 'Retur penjualan berhasil dibatalkan.');
    }

    public function showPurchaseReturn(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase.supplier', 'items.product', 'user']);
        return view('returns.purchase-return-show', compact('purchaseReturn'));
    }

    public function editPurchaseReturn(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase', 'items.product']);
        return view('returns.purchase-return-edit', compact('purchaseReturn'));
    }

    public function updatePurchaseReturn(UpdatePurchaseReturnRequest $request, PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->update($request->validated());
        return redirect()->route('purchase-returns.index')->with('success', 'Data retur pembelian berhasil diperbarui.');
    }

    public function destroyPurchaseReturn(PurchaseReturn $purchaseReturn)
    {
        $this->returnService->cancelPurchaseReturn($purchaseReturn, Auth::id());
        return redirect()->route('purchase-returns.index')->with('success', 'Retur pembelian berhasil dibatalkan.');
    }
}
