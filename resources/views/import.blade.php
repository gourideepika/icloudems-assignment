<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bulk Ledger Import</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-4">

        <div class="mx-auto" style="max-width: 850px;">

            <div class="card shadow-sm">

                <div class="card-header bg-dark text-white p-3">
                    <div class="">
                        <div>
                            <h1 class="h4 mb-1" style="text-align: center;">
                                icloudEMS Bulk Ledger Import
                            </h1>
                        </div>
                    </div>
                </div>


                <div class="card-body p-4">
                    <form
                        id="upload-form"
                        data-upload-url="{{ route('import.chunk') }}"
                        data-csrf-token="{{ csrf_token() }}"
                        data-status-url-template="{{ route('import.status', ['import' => '__IMPORT_ID__']) }}">

                        @csrf

                        <div class="mb-4">

                        <span class="text-muted small mb-2">
                            Total Records: {{ number_format($totalStagingLedgers) }}
                        </span><br><br>
                            <label
                                for="csv-file"
                                class="form-label fw-semibold">
                                Select CSV File
                            </label>

                            <div class="input-group">

                                <input type="file" id="csv-file" name="csv_file" class="form-control" accept=".csv" required>

                                <button id="upload-button" class="btn btn-dark" type="submit">
                                    Upload CSV
                                </button>

                            </div>

                            <div class="form-text">
                                Only CSV files are allowed. Large files are uploaded in chunks.
                            </div>

                        </div>

                        <div
                            id="progress-section"
                            class="mb-4"
                            style="display: none;">

                            <div class="d-flex justify-content-between mb-2">

                                <span
                                    id="progress-label"
                                    class="small text-muted">
                                    Preparing upload...
                                </span>

                                <span
                                    id="progress-percent"
                                    class="small fw-semibold">
                                    0%
                                </span>

                            </div>

                            <div class="progress">

                                <div
                                    id="progress-fill"
                                    class="progress-bar"
                                    style="width: 0%;">
                                </div>

                            </div>

                            <div
                                id="upload-status"
                                class="small text-muted mt-2">
                                Preparing upload...
                            </div>

                        </div>

                    </form>

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <h5 class="mb-0">Amount Match</h5>

                        <span>
                            <span class="text-muted">Result -</span>
                            @if(
                            $sourceDueAmount == $parentAmount &&
                            $parentAmount == $childAmount
                            )

                            <span class="fw-bold text-success">PASS</span>

                            @else

                            <span class="fw-bold text-danger">FAIL</span>
                            @endif
                        </span>
                    </div>


                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <div class="small text-muted mb-1">
                                        SOURCE DUE AMOUNT
                                    </div>
                                    <div class="fw-semibold">
                                        ₹{{ number_format($sourceDueAmount, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">

                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <div class="small text-muted mb-1">
                                        FINANCIAL TRANSACTION
                                    </div>
                                    <div class="fw-semibold">
                                        ₹{{ number_format($parentAmount, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">

                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <div class="small text-muted mb-1">
                                        FINANCIAL TRANSACTION DETAIL
                                    </div>
                                    <div class="fw-semibold">
                                        ₹{{ number_format($childAmount, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Count Match -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            Count Match
                        </h5>
                        <span>
                            <span class="text-muted">Result -</span>

                            @if(
                            $uniqueDueTransactions == $financialTransactionCount &&
                            $feeHeadLines == $financialTransactionDetailCount
                            )

                            <span class="fw-bold text-success">PASS</span>
                            @else
                            <span class="fw-bold text-danger">FAIL</span>
                            @endif
                        </span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <div class="small text-muted mb-1">UNIQUE DUE TRANSACTIONS</div>

                                    <div class="fw-semibold">
                                        {{ number_format($uniqueDueTransactions) }}
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">

                            <div class="card bg-light h-100">

                                <div class="card-body">

                                    <div class="small text-muted mb-1">FINANCIAL TRANSACTION COUNT</div>

                                    <div class="fw-semibold">
                                        {{ number_format($financialTransactionCount) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <div class="small text-muted mb-1">FEE-HEAD LINES</div>

                                    <div class="fw-semibold">
                                        {{ number_format($feeHeadLines) }}
                                    </div>

                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="card bg-light h-100">

                                <div class="card-body">

                                    <div class="small text-muted mb-1">
                                        FINANCIAL TRANSACTION DETAIL COUNT
                                    </div>

                                    <div class="fw-semibold">
                                        {{ number_format($financialTransactionDetailCount) }}
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


    <script>
        const form = document.getElementById('upload-form');

        const fileInput =
            document.getElementById('csv-file');

        const button =
            document.getElementById('upload-button');

        const status =
            document.getElementById('upload-status');

        const uploadUrl =
            form.dataset.uploadUrl;

        const csrfToken =
            form.dataset.csrfToken;

        const statusUrlTemplate =
            form.dataset.statusUrlTemplate;

        const progressSection =
            document.getElementById('progress-section');

        const progressFill =
            document.getElementById('progress-fill');

        const progressPercent =
            document.getElementById('progress-percent');

        const progressLabel =
            document.getElementById('progress-label');

        const chunkSize = 5 * 1024 * 1024;


        fileInput.addEventListener('change', function() {

            const file = this.files[0];

            if (!file) {
                return;
            }

            const sizeMB =
                (file.size / (1024 * 1024)).toFixed(2);

            status.textContent =
                `${file.name} selected (${sizeMB} MB)`;

        });

        form.addEventListener('submit', async (event) => {

            event.preventDefault();


            const file =
                fileInput.files[0];


            if (!file) {

                status.textContent =
                    'Please select a CSV file first.';

                return;
            }
            const extension =
                file.name
                .split('.')
                .pop()
                .toLowerCase();


            if (extension !== 'csv') {

                status.textContent =
                    'Only CSV files are allowed.';
                return;
            }

            const uploadId =
                crypto.randomUUID();

            const totalChunks =
                Math.ceil(
                    file.size / chunkSize
                );

            button.disabled = true;

            button.textContent =
                '⏳ Uploading...';

            progressSection.style.display =
                'block';


            try {

                for (
                    let index = 0; index < totalChunks; index++
                ) {

                    const formData =
                        new FormData();


                    const start =
                        index * chunkSize;


                    const end =
                        Math.min(
                            start + chunkSize,
                            file.size
                        );


                    const chunk =
                        file.slice(
                            start,
                            end
                        );


                    formData.append(
                        'upload_id',
                        uploadId
                    );


                    formData.append(
                        'chunk_index',
                        index
                    );


                    formData.append(
                        'total_chunks',
                        totalChunks
                    );


                    formData.append(
                        'file_name',
                        file.name
                    );


                    formData.append(
                        'chunk',
                        chunk,
                        file.name
                    );

                    const response =
                        await fetch(
                            uploadUrl, {
                                method: 'POST',

                                headers: {
                                    'X-CSRF-TOKEN': csrfToken
                                },

                                body: formData
                            }
                        );


                    if (!response.ok) {

                        let errorMessage =
                            'The server rejected this chunk.';


                        try {

                            const error =
                                await response.json();

                            errorMessage =
                                error.message ||
                                errorMessage;

                        } catch (parseError) {

                            errorMessage =
                                await response.text();

                        }


                        throw new Error(
                            errorMessage
                        );

                    }


                    const result =
                        await response.json();

                    const percentage =
                        Math.round(
                            (
                                (index + 1) /
                                totalChunks
                            ) * 100
                        );


                    progressFill.style.width =
                        `${percentage}%`;


                    progressPercent.textContent =
                        `${percentage}%`;


                    progressLabel.textContent =
                        `Uploading chunk ${index + 1} of ${totalChunks}`;


                    status.textContent =
                        `Uploaded chunk ${index + 1} of ${totalChunks}`;


                    if (result.complete) {

                        progressFill.style.width =
                            '100%';

                        progressPercent.textContent =
                            '100%';

                        progressLabel.textContent =
                            'Upload completed successfully';

                        status.textContent =
                            '✅ Upload complete. Import has been queued for processing.';


                        pollImportStatus(
                            result.import_id
                        );

                    }

                }

                progressFill.style.width =
                    '100%';

                progressPercent.textContent =
                    '100%';

                progressLabel.textContent =
                    'Upload completed successfully';


                button.textContent =
                    '✅ Upload Complete';


            } catch (error) {

                console.error(error);


                status.textContent =
                    `❌ ${error.message || 'Upload failed. Please try again.'}`;


                progressLabel.textContent =
                    'Upload failed';


                button.disabled =
                    false;


                button.textContent =
                    '🚀 Start CSV Import';

            }

        });
        async function pollImportStatus(importId) {

            const statusUrl =
                statusUrlTemplate.replace(
                    '__IMPORT_ID__',
                    importId
                );


            const poll = async () => {

                const response =
                    await fetch(
                        statusUrl, {
                            headers: {
                                'Accept': 'application/json'
                            },
                        }
                    );


                if (!response.ok) {

                    throw new Error(
                        'Unable to check import status.'
                    );

                }


                const importStatus =
                    await response.json();

                if (
                    importStatus.status ===
                    'completed'
                ) {

                    progressLabel.textContent =
                        'Import completed successfully';

                    status.textContent =
                        '✅ CSV imported and validated successfully.';

                    return;
                }

                if (
                    importStatus.status ===
                    'failed'
                ) {

                    progressLabel.textContent =
                        'Import failed';

                    status.textContent =
                        `❌ ${
                            importStatus.error_message ||
                            'The import could not be completed.'
                        }`;


                    button.disabled =
                        false;

                    button.textContent =
                        '🚀 Start CSV Import';

                    return;
                }

                status.textContent =
                    'Processing CSV and validating financial data...';


                window.setTimeout(
                    poll,
                    2000
                );

            };


            try {

                await poll();

            } catch (error) {

                status.textContent =
                    `❌ ${error.message}`;


                button.disabled =
                    false;


                button.textContent =
                    '🚀 Start CSV Import';

            }

        }
    </script>

</body>

</html>