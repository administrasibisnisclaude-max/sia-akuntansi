<?php

namespace App\Http\Controllers;

use App\Models\{Journal, JournalLine, Account};
use App\Services\NumberingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    public function index()
    {
        $journals = Journal::with('createdBy')->latest('date')->paginate(20);
        return view('journals.index', compact('journals'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)->orderBy('account_code')->get();
        return view('journals.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'              => 'required|date',
            'description'       => 'required|string',
            'lines'             => 'required|array|min:2',
            'lines.*.account_id'=> 'required|exists:accounts,id',
            'lines.*.debit'     => 'required|numeric|min:0',
            'lines.*.credit'    => 'required|numeric|min:0',
        ]);

        $lines = $request->input('lines');
        $totalDebit  = collect($lines)->sum('debit');
        $totalCredit = collect($lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            return back()->withInput()->withErrors(['lines' => 'Total debit dan kredit harus seimbang.']);
        }

        DB::transaction(function () use ($request, $lines) {
            $journal = Journal::create([
                'journal_number' => NumberingService::generate('JRN', 'journals', 'journal_number'),
                'date'           => $request->date,
                'description'    => $request->description,
                'status'         => 'draft',
                'created_by'     => auth()->id(),
            ]);

            foreach ($lines as $i => $line) {
                JournalLine::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit'       => $line['debit'],
                    'credit'      => $line['credit'],
                    'order'       => $i,
                ]);
            }
        });

        return redirect()->route('journals.index')->with('success', 'Jurnal berhasil dibuat.');
    }

    public function show(Journal $journal)
    {
        $journal->load('lines.account', 'createdBy', 'postedBy');
        return view('journals.show', compact('journal'));
    }

    public function edit(Journal $journal)
    {
        if ($journal->status === 'posted') {
            return back()->withErrors(['status' => 'Jurnal yang sudah diposting tidak dapat diedit.']);
        }
        $accounts = Account::where('is_active', true)->orderBy('account_code')->get();
        $journal->load('lines.account');
        return view('journals.edit', compact('journal', 'accounts'));
    }

    public function update(Request $request, Journal $journal)
    {
        if ($journal->status === 'posted') {
            return back()->withErrors(['status' => 'Jurnal yang sudah diposting tidak dapat diedit.']);
        }

        $request->validate([
            'date'              => 'required|date',
            'description'       => 'required|string',
            'lines'             => 'required|array|min:2',
            'lines.*.account_id'=> 'required|exists:accounts,id',
            'lines.*.debit'     => 'required|numeric|min:0',
            'lines.*.credit'    => 'required|numeric|min:0',
        ]);

        $lines = $request->input('lines');
        $totalDebit  = collect($lines)->sum('debit');
        $totalCredit = collect($lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            return back()->withInput()->withErrors(['lines' => 'Total debit dan kredit harus seimbang.']);
        }

        DB::transaction(function () use ($request, $journal, $lines) {
            $journal->update([
                'date'        => $request->date,
                'description' => $request->description,
            ]);
            $journal->lines()->delete();

            foreach ($lines as $i => $line) {
                JournalLine::create([
                    'journal_id'  => $journal->id,
                    'account_id'  => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit'       => $line['debit'],
                    'credit'      => $line['credit'],
                    'order'       => $i,
                ]);
            }
        });

        return redirect()->route('journals.index')->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function post(Journal $journal)
    {
        if ($journal->status === 'posted') {
            return back()->withErrors(['status' => 'Jurnal sudah diposting.']);
        }

        $journal->load('lines');
        if (!$journal->isBalanced()) {
            return back()->withErrors(['balance' => 'Jurnal tidak seimbang, tidak dapat diposting.']);
        }

        $journal->update([
            'status'    => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        return back()->with('success', 'Jurnal berhasil diposting.');
    }

    public function unpost(Journal $journal)
    {
        if ($journal->status !== 'posted') {
            return back()->withErrors(['status' => 'Jurnal belum diposting.']);
        }

        if ($journal->reference_type) {
            return back()->withErrors(['reference' => 'Jurnal otomatis tidak dapat di-unpost.']);
        }

        $journal->update([
            'status'    => 'draft',
            'posted_by' => null,
            'posted_at' => null,
        ]);

        return back()->with('success', 'Jurnal berhasil di-unpost.');
    }

    public function destroy(Journal $journal)
    {
        if ($journal->status === 'posted') {
            return back()->withErrors(['status' => 'Jurnal yang sudah diposting tidak dapat dihapus.']);
        }
        $journal->delete();
        return redirect()->route('journals.index')->with('success', 'Jurnal berhasil dihapus.');
    }
}
