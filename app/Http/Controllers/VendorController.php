<?php

namespace App\Http\Controllers;

use App\Models\{Vendor, Account};
use App\Services\NumberingService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::orderBy('name')->paginate(20);
        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        $accounts = Account::where('type', 'Liability')->orderBy('account_code')->get();
        return view('vendors.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'nullable|email|max:100',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'ap_account_id' => 'nullable|exists:accounts,id',
            'is_active'     => 'boolean',
        ]);
        $data['vendor_code'] = NumberingService::generate('VNDR', 'vendors', 'vendor_code');
        $data['is_active'] = $request->boolean('is_active', true);
        Vendor::create($data);
        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function edit(Vendor $vendor)
    {
        $accounts = Account::where('type', 'Liability')->orderBy('account_code')->get();
        return view('vendors.edit', compact('vendor', 'accounts'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'nullable|email|max:100',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'ap_account_id' => 'nullable|exists:accounts,id',
            'is_active'     => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $vendor->update($data);
        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil diperbarui.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil dihapus.');
    }
}
