<?php
$pageTitle = 'QR Scanner';
include BASE_PATH . '/view/layout/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <i class="fas fa-qrcode text-blue-600"></i> QR Scanner
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Scan QR code from invoice or enter Loan ID manually</p>

        <hr class="my-6 border-gray-200 dark:border-gray-700">

        <!-- Scanner Area -->
        <div id="scanner-container" class="mb-6">
            <div id="reader" style="width:100%; max-width:400px; margin:0 auto;"></div>
            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-3">
                <i class="fas fa-camera mr-1"></i> Allow camera access when prompted.
            </p>
        </div>

        <!-- Manual Input -->
        <div class="mt-6">
            <label for="loan_id_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Or enter Loan ID manually:
            </label>
            <div class="flex gap-3">
                <input type="number" id="loan_id_input" placeholder="Loan ID (e.g., 1)" 
                       class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <button onclick="manualScan()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                    <i class="fas fa-search"></i> Go
                </button>
            </div>
        </div>

        <!-- Messages -->
        <div id="scan-result" class="mt-4 hidden">
            <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i> <span id="scan-message"></span>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Scanner Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    let html5QrCode;

    function onScanSuccess(decodedText, decodedResult) {
        // decodedText will be the URL (e.g., http://localhost:8080/Library/public/librarian/scan?loan_id=1)
        // Extract loan_id from URL
        const url = new URL(decodedText);
        const loanId = url.searchParams.get('loan_id');
        if (loanId) {
            window.location.href = '<?= BASE_URL ?>/librarian/scan?loan_id=' + loanId;
        } else {
            showError('Invalid QR Code – loan_id not found.');
        }
    }

    function onScanFailure(error) {
        // ignore, keep scanning
    }

    // Start scanner
    function startScanner() {
        const readerElement = document.getElementById('reader');
        if (!readerElement) return;

        html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            onScanFailure
        ).catch(err => {
            console.warn('Camera access denied or error:', err);
            document.getElementById('scanner-container').innerHTML = `
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-300 p-4 rounded-lg text-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Camera not available. Please use the manual input above.
                </div>
            `;
        });
    }

    // Manual scan
    function manualScan() {
        const input = document.getElementById('loan_id_input');
        const loanId = input.value.trim();
        if (!loanId || isNaN(loanId)) {
            alert('Please enter a valid Loan ID (numeric).');
            return;
        }
        window.location.href = '<?= BASE_URL ?>/librarian/scan?loan_id=' + loanId;
    }

    // Show error on page
    function showError(message) {
        const resultDiv = document.getElementById('scan-result');
        const msgSpan = document.getElementById('scan-message');
        resultDiv.classList.remove('hidden');
        resultDiv.innerHTML = `<div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i> ${message}
        </div>`;
    }

    // Start scanner on page load
    document.addEventListener('DOMContentLoaded', startScanner);
</script>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>