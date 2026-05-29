<?php

namespace App\Http\Controllers;

use App\Models\{PaymentMethod, Account};
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::with('account')->orderBy('name')->paginate(20);
        return view('payment-methods.index', compact('methods'));
    }

    public function create()
    {
        $accounts = Account::where('type', 'Asset')->orderBy('account_code')->get();
        return view('payment-methods.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'type'       => 'required|in:cash,bank,other',
            'account_id' => 'nullable|exists:accounts,id',
            'is_active'  => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        PaymentMethod::create($data);
        return redirect()->route('payment-methods.index')->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        $accounts = Account::where('type', 'Asset')->orderBy('account_code')->get();
        return view('payment-methods.edit', compact('paymentMethod', 'accounts'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'type'       => 'required|in:cash,bank,other',
            'account_id' => 'nullable|exists:accounts,id',
            'is_active'  => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $paymentMethod->update($data);
        return redirect()->route('payment-methods.index')->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return redirect()->route('payment-methods.index')->with('success', 'Metode pembayaran berhasil dihapus.');
    }
}
