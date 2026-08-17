<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Models\Sale;
use App\Services\PosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    protected PosService $posService;

    public function __construct(PosService $posService)
    {
        $this->posService = $posService;
    }

    public function index(Request $request)
    {
        $transactions = Sale::with('customer')
            ->when($request->search, fn ($query, $search) => $query->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%")))
            ->when(!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('supervisor'), function ($query) {
                $query->where('branch_id', Auth::user()->branch_id);
            })
            ->latest()->paginate(20);

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $data = $this->posService->getPosData(Auth::user());
        return view('transactions.pos', $data);
    }

    public function store(StoreTransactionRequest $request)
    {
        $result = $this->posService->processSale($request->validated(), Auth::user());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['status_code'] ?? 400);
        }

        return response()->json([
            'success'        => true,
            'message'        => $result['message'],
            'sale_id'        => $result['sale_id'],
            'invoice_number' => $result['invoice_number'],
        ]);
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product']);
        return view('transactions.show', compact('sale'));
    }

    public function receipt(Request $request, Sale $sale)
    {
        $receiptData = $this->posService->getReceiptData($sale);
        
        if ($request->query('format') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transactions.receipt', $receiptData);
            // Thermal printer 80mm width (~226.77 pt), height auto but set to 800 for safety
            $pdf->setPaper([0, 0, 226.77, 800], 'portrait');
            return $pdf->stream('struk-' . $sale->invoice_number . '.pdf');
        }

        return view('transactions.receipt', $receiptData);
    }

    public function held()
    {
        $heldSales = Sale::where('status', 'held')->with(['customer', 'user'])->latest()->paginate(20);
        return view('transactions.held', compact('heldSales'));
    }

    public function findByInvoice($invoice)
    {
        $sale = Sale::where('invoice_number', $invoice)->first();
        if ($sale) {
            return response()->json(['success' => true, 'sale' => $sale]);
        }
        return response()->json(['success' => false, 'message' => 'Invoice tidak ditemukan']);
    }
}
