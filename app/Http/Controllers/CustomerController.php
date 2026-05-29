<?php

namespace App\Http\Controllers;

use App\Models\{Customer, Account};
use App\Services\NumberingService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('name')->paginate(20);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $accounts = Account::where('type', 'Asset')->orderBy('account_code')->get();
        return view('customers.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'nullable|email|max:100',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'ar_account_id' => 'nullable|exists:accounts,id',
            'is_active'     => 'boolean',
        ]);
        $data['customer_code'] = NumberingService::generate('CUST', 'customers', 'customer_code');
        $data['is_active'] = $request->boolean('is_active', true);
        Customer::create($data);
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        $accounts = Account::where('type', 'Asset')->orderBy('account_code')->get();
        return view('customers.edit', compact('customer', 'accounts'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'nullable|email|max:100',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'ar_account_id' => 'nullable|exists:accounts,id',
            'is_active'     => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $customer->update($data);
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}
