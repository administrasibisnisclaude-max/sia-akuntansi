<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\NumberingService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with('parent')->orderBy('account_code')->paginate(50);
        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        $parents = Account::orderBy('account_code')->get();
        return view('accounts.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_code'   => 'required|string|max:20|unique:accounts',
            'name'           => 'required|string|max:100',
            'type'           => 'required|in:Asset,Liability,Equity,Revenue,Expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id'      => 'nullable|exists:accounts,id',
            'is_active'      => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Account::create($data);
        return redirect()->route('accounts.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(Account $account)
    {
        $parents = Account::where('id', '!=', $account->id)->orderBy('account_code')->get();
        return view('accounts.edit', compact('account', 'parents'));
    }

    public function update(Request $request, Account $account)
    {
        $data = $request->validate([
            'account_code'   => 'required|string|max:20|unique:accounts,account_code,' . $account->id,
            'name'           => 'required|string|max:100',
            'type'           => 'required|in:Asset,Liability,Equity,Revenue,Expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id'      => 'nullable|exists:accounts,id',
            'is_active'      => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $account->update($data);
        return redirect()->route('accounts.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Akun berhasil dihapus.');
    }
}
