<?php

namespace App\Jobs;

use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportLedgerJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 3600;

    public int $tries = 3;

    public function __construct(
        public int $importId,
        public string $filePath
    ) {}

    public function handle(): void
    {
        $import = Import::findOrFail($this->importId);

        if ($import->status === 'completed') {
            return;
        }

        $import->update([
            'status' => 'processing',
        ]);

        try {
            DB::transaction(function () use ($import): void {
                $this->importCsv($import);

                $this->processDueData($import);

                $this->validateImport($import);

                $import->update([
                    'status' => 'completed',
                ]);
            }, 3);

            $this->removeUploadedFile();
        } catch (Throwable $e) {

            Log::error('Ledger import failed.', [
                'import_id' => $this->importId,
                'file_path' => $this->filePath,
                'exception' => $e,
            ]);

            $import->update([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 60000),
            ]);

            throw $e;
        }
    }

    private function removeUploadedFile(): void
    {
        if (is_file($this->filePath)) {
            unlink($this->filePath);
        }
    }

    private function importCsv(Import $import): void
    {
        $handle = fopen($this->filePath, 'r');

        if (!$handle) {
            throw new \Exception(
                'Unable to open CSV file.'
            );
        }

        for ($i = 0; $i < 5; $i++) {
            fgetcsv($handle);
        }
        
        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);

            throw new \Exception(
                'CSV header not found.'
            );
        }

        $headers = array_map(
            fn($header) => trim($header),
            $headers
        );

        $batch = [];

        $total = 0;

        while (($row = fgetcsv($handle)) !== false) {

            if (empty(array_filter($row))) {
                continue;
            }

            $data = array_combine(
                $headers,
                array_pad(
                    $row,
                    count($headers),
                    null
                )
            );

            $batch[] = $this->prepareStagingData(
                $data,
                $import->id
            );

            $total++;

            if (count($batch) >= 1000) {

                DB::table('staging_ledgers')->insert($batch);

                $batch = [];

                $import->update([
                    'processed_records' => $total,
                ]);
            }

        }

        if (!empty($batch)) {

            DB::table('staging_ledgers')
                ->insert($batch);
        }

        if ($total > 200000) {
            throw new \Exception(
                'CSV cannot contain more than 200000 data rows.'
            );
        }

        $import->update([
            'total_records' => $total,
            'processed_records' => $total,
        ]);

        fclose($handle);
    }

    private function prepareStagingData(
        array $data,
        int $importId
    ): array {

        return [
            'import_id' => $importId,

            'sr_no' => $this->integer(
                $data['Sr.'] ?? null
            ),

            'operation_date' => $this->date(
                $data['Date'] ?? null
            ),

            'academic_year' => $this->string(
                $data['Academic Year'] ?? null
            ),

            'session' => $this->string(
                $data['Session'] ?? null
            ),

            'alloted_category' => $this->string(
                $data['Alloted Category'] ?? null
            ),

            'voucher_type' => $this->string(
                $data['Voucher Type'] ?? null
            ),

            'voucher_no' => $this->string(
                $data['Voucher No.'] ?? null
            ),

            'roll_no' => $this->string(
                $data['Roll No.'] ?? null
            ),

            'admno' => $this->string(
                $data['Admno/UniqueId'] ?? null
            ),

            'status' => $this->string(
                $data['Status'] ?? null
            ),

            'fee_category' => $this->string(
                $data['Fee Category'] ?? null
            ),

            'faculty' => $this->string(
                $data['Faculty'] ?? null
            ),

            'program' => $this->string(
                $data['Program'] ?? null
            ),

            'department' => $this->string(
                $data['Department'] ?? null
            ),

            'batch' => $this->string(
                $data['Batch'] ?? null
            ),

            'receipt_no' => $this->string(
                $data['Receipt No.'] ?? null
            ),

            'fee_head' => $this->string(
                $data['Fee Head'] ?? null
            ),

            'due_amount' => $this->decimal(
                $data['Due Amount'] ?? 0
            ),

            'paid_amount' => $this->decimal(
                $data['Paid Amount'] ?? 0
            ),

            'concession' => $this->decimal(
                $data['Concession Amount'] ?? 0
            ),

            'scholarship_amount' => $this->decimal(
                $data['Scholarship Amount'] ?? 0
            ),

            'reverse_concession_amount' => $this->decimal(
                $data['Reverse Concession Amount'] ?? 0
            ),

            'write_off' => $this->decimal(
                $data['Write Off Amount'] ?? 0
            ),

            'adjusted_amount' => $this->decimal(
                $data['Adjusted Amount'] ?? 0
            ),

            'refund_amount' => $this->decimal(
                $data['Refund Amount'] ?? 0
            ),

            'fund_transfer_amount' => $this->decimal(
                $data['Fund TranCfer Amount'] ?? 0
            ),

            'remark' => $this->string(
                $data['Remarks'] ?? null
            ),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function string($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function integer($value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (int) $value;
    }

    private function decimal($value): float
    {
        if ($value === null || trim((string) $value) === '') {
            return 0;
        }

        $value = str_replace(',', '', $value);

        return (float) $value;
    }

    private function date($value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        $formats = [
            'd-m-Y',
            'd/m/Y',
            'd-m-y',
            'd/m/y',
            'Y-m-d',
        ];

        foreach ($formats as $format) {

            $date = \DateTime::createFromFormat(
                $format,
                $value
            );

            if (
                $date &&
                $date->format($format) === $value
            ) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function processDueData(Import $import): void
    {
        DB::table('financial_transactions')
            ->where('import_id', $import->id)
            ->delete();

        $dueRows = DB::table('staging_ledgers')
            ->where('import_id', $import->id)
            ->where('due_amount', '<>', 0)
            ->get();

        $groups = $dueRows->groupBy('voucher_no');

        foreach ($groups as $voucherNo => $rows) {
            $first = $rows->first();

            $parentId = DB::table(
                'financial_transactions'
            )->insertGetId([
                'import_id' => $import->id,
                'module_id' => 1,
                'tran_id' => $voucherNo,
                'amount' => $rows->sum('due_amount'),
                'crdr' => 'D',
                'tran_date' => $first->operation_date,
                'acad_year' => $first->academic_year,
                'fee_category' => $first->fee_category,
                'entry_mode' => 0,
                'brid' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($rows as $row) {
                DB::table(
                    'financial_transaction_details'
                )->insert([
                    'financial_tran_id' => $parentId,
                    'module_id' => 1,
                    'amount' => $row->due_amount,
                    'head_id' => null,
                    'crdr' => 'D',
                    'head_name' => $row->fee_head,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function validateImport(Import $import): void
    {
        $stagingCount = DB::table('staging_ledgers')
            ->where('import_id', $import->id)
            ->count();

        if ($stagingCount !== (int) $import->total_records) {
            throw new \Exception(
                'Staging row count does not match imported row count.'
            );
        }

        $sourceAmount = DB::table('staging_ledgers')
            ->where('import_id', $import->id)
            ->where('due_amount', '<>', 0)
            ->sum('due_amount');

        $parentAmount = DB::table(
            'financial_transactions'
        )
            ->where('import_id', $import->id)
            ->sum('amount');

        $childAmount = DB::table(
            'financial_transaction_details as d'
        )
            ->join(
                'financial_transactions as t',
                't.id',
                '=',
                'd.financial_tran_id'
            )
            ->where('t.import_id', $import->id)
            ->sum('d.amount');

        $import->update([
            'source_due_amount' => $sourceAmount,
            'parent_amount' => $parentAmount,
            'child_amount' => $childAmount,
        ]);

        if (
            bccomp(
                (string) $sourceAmount,
                (string) $parentAmount,
                2
            ) !== 0
        ) {
            throw new \Exception(
                'Source amount does not match parent amount.'
            );
        }

        if (
            bccomp(
                (string) $sourceAmount,
                (string) $childAmount,
                2
            ) !== 0
        ) {
            throw new \Exception(
                'Source amount does not match child amount.'
            );
        }

        $uniqueVoucherCount = DB::table(
            'staging_ledgers'
        )
            ->where('import_id', $import->id)
            ->where('due_amount', '<>', 0)
            ->distinct('voucher_no')
            ->count('voucher_no');

        $parentCount = DB::table(
            'financial_transactions'
        )
            ->where('import_id', $import->id)
            ->count();

        if ($uniqueVoucherCount !== $parentCount) {

            throw new \Exception(
                'Unique voucher count does not match parent count.'
            );
        }

        $dueLineCount = DB::table('staging_ledgers')
            ->where('import_id', $import->id)
            ->where('due_amount', '<>', 0)
            ->count();

        $childCount = DB::table(
            'financial_transaction_details as d'
        )
            ->join(
                'financial_transactions as t',
                't.id',
                '=',
                'd.financial_tran_id'
            )
            ->where('t.import_id', $import->id)
            ->count();

        if ($dueLineCount !== $childCount) {

            throw new \Exception(
                'Due line count does not match child count.'
            );
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Ledger import permanently failed.', [
            'import_id' => $this->importId,
            'file_path' => $this->filePath,
            'exception' => $exception,
        ]);

        Import::where('id', $this->importId)
            ->update([
                'status' => 'failed',
                'error_message' => mb_substr(
                    $exception->getMessage(),
                    0,
                    60000
                ),
            ]);
    }
}
