<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Jobs\ImportLedgerJob;
use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ImportController extends Controller
{
    public function index()
    {
        $importId = DB::table('imports')->latest('id')->value('id');
        $totalStagingLedgers = DB::table('staging_ledgers')->count();

        if ($importId === null) {
            return view('import', [
                'totalStagingLedgers' => $totalStagingLedgers,
                'sourceDueAmount' => 0,
                'parentAmount' => 0,
                'childAmount' => 0,
                'uniqueDueTransactions' => 0,
                'financialTransactionCount' => 0,
                'feeHeadLines' => 0,
                'financialTransactionDetailCount' => 0,
            ]);
        }

        $sourceDueAmount = DB::table('staging_ledgers')
            ->where('import_id', $importId)
            ->where('due_amount', '<>', 0)
            ->sum('due_amount');

        $parentAmount = DB::table('financial_transactions')
            ->where('import_id', $importId)
            ->sum('amount');

        $childAmount = DB::table('financial_transaction_details as d')
            ->join(
                'financial_transactions as t',
                't.id',
                '=',
                'd.financial_tran_id'
            )
            ->where('t.import_id', $importId)
            ->sum('d.amount');

        $uniqueDueTransactions = DB::table('staging_ledgers')
            ->where('import_id', $importId)
            ->where('due_amount', '<>', 0)
            ->distinct()
            ->count('voucher_no');

        $financialTransactionCount = DB::table('financial_transactions')
            ->where('import_id', $importId)
            ->count();

        $feeHeadLines = DB::table('staging_ledgers')
            ->where('import_id', $importId)
            ->where('due_amount', '<>', 0)
            ->count();

        $financialTransactionDetailCount = DB::table(
            'financial_transaction_details as d'
        )
            ->join(
                'financial_transactions as t',
                't.id',
                '=',
                'd.financial_tran_id'
            )
            ->where('t.import_id', $importId)
            ->count();

        return view('import', compact(
            'totalStagingLedgers',
            'sourceDueAmount',
            'parentAmount',
            'childAmount',
            'uniqueDueTransactions',
            'financialTransactionCount',
            'feeHeadLines',
            'financialTransactionDetailCount'
        ));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
            ],
        ]);

        $file = $request->file('csv_file');

        $path = $file->store('imports');

        $import = Import::create([
            'file_name' => $file->getClientOriginalName(),
            'status' => 'pending',
        ]);

        ImportLedgerJob::dispatch($import->id, storage_path('app/' . $path));

        return redirect()->back()->with(
            'success',
            'CSV uploaded. Import started in background.'
        );
    }

    public function uploadChunk(Request $request)
    {
        $directory = null;

        try {
            $validated = $request->validate([
                'upload_id' => ['required', 'uuid'],
                'chunk_index' => ['required', 'integer', 'min:0'],
                'total_chunks' => ['required', 'integer', 'min:1'],
                'file_name' => ['required', 'string', 'max:255'],
                'chunk' => ['required', 'file', 'max:10000'],
            ]);

            if ($validated['chunk_index'] >= $validated['total_chunks']) {
                abort(422, 'Invalid chunk index.');
            }

            $directory = 'imports/chunks/' . $validated['upload_id'];
            $chunkName = $validated['chunk_index'] . '.part';

            Storage::disk('local')->putFileAs(
                $directory,
                $validated['chunk'],
                $chunkName
            );

            $received = collect(range(0, $validated['total_chunks'] - 1))
                ->filter(fn($index) => Storage::disk('local')->exists(
                    $directory . '/' . $index . '.part'
                ))
                ->count();

            if ($received < $validated['total_chunks']) {
                return response()->json([
                    'complete' => false,
                    'received' => $received,
                    'total' => $validated['total_chunks'],
                ]);
            }

            $storedPath = 'imports/' . Str::uuid() . '.csv';
            $output = fopen(Storage::disk('local')->path($storedPath), 'wb');

            if (!$output) {
                abort(500, 'Unable to create the assembled upload.');
            }

            for ($index = 0; $index < $validated['total_chunks']; $index++) {
                $chunkPath = Storage::disk('local')->path(
                    $directory . '/' . $index . '.part'
                );
                $input = fopen($chunkPath, 'rb');

                if (!$input) {
                    fclose($output);
                    abort(422, 'A file chunk could not be read.');
                }

                stream_copy_to_stream($input, $output);
                fclose($input);
            }

            fclose($output);
            Storage::disk('local')->deleteDirectory($directory);

            $import = Import::create([
                'file_name' => $validated['file_name'],
                'status' => 'pending',
            ]);

            ImportLedgerJob::dispatch(
                $import->id,
                Storage::disk('local')->path($storedPath)
            );

            return response()->json([
                'complete' => true,
                'import_id' => $import->id,
            ]);
        } catch (Throwable $exception) {
            Log::error('Ledger upload failed.', [
                'upload_id' => $request->input('upload_id'),
                'chunk_index' => $request->input('chunk_index'),
                'exception' => $exception,
            ]);

            if ($directory !== null) {
                Storage::disk('local')->deleteDirectory($directory);
            }

            return response()->json([
                'message' => 'Upload could not be completed. Please try again.',
            ], 500);
        }
    }

    public function status(Import $import)
    {
        return response()->json([
            'status' => $import->status,
            'error_message' => $import->error_message,
        ]);
    }
}
