<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = 'Submit Payment Proof';
include BASE_PATH . '/view/layout/header.php';

$loan = $loan ?? null;
$borrowingFee = $borrowingFee ?? 5000; 
if (!$loan) {
    echo '<div class="container mx-auto px-4 py-8"><div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 p-6 rounded-xl text-center">Loan not found.</div></div>';
    include BASE_PATH . '/view/layout/footer.php';
    return;
}
?>

<style>
    :root {
        --bg-primary: #f8fafc;
        --bg-secondary: #ffffff;
        --bg-card: #ffffff;
        --bg-input: #f1f5f9;
        --text-primary: #0f172a;
        --text-secondary: #334155;
        --border-color: #e2e8f0;
        
        --primary-from: #296fb5;  
        --primary-to: #3f56be;   
        --shadow-color: rgba(41, 119, 208, 0.3);
        --active-border: #2a64c9;
        --active-bg: rgba(44, 141, 210, 0.1);
        
        --shadow: 0 20px 60px rgba(0,0,0,0.08);
        --shadow-hover: 0 25px 70px rgba(0,0,0,0.12);
        --tab-bg: #f1f5f9;
        --tab-hover: #e2e8f0;
    }

    html.dark {
        --bg-primary: #0f172a;
        --bg-secondary: #1e293b;
        --bg-card: #1e293b;
        --bg-input: #334155;
        --text-primary: #f1f5f9;
        --text-secondary: #cbd5e1;
        --border-color: #334155;
        
        --shadow-color: rgba(52, 142, 211, 0.3);
        --active-border: #3654c0;
        --active-bg: rgba(49, 135, 205, 0.2);
        
        --shadow: 0 20px 60px rgba(0,0,0,0.4);
        --shadow-hover: 0 25px 70px rgba(0,0,0,0.5);
        --tab-bg: #334155;
        --tab-hover: #475569;
    }

    body {
        background: var(--bg-primary);
        transition: background 0.3s ease, color 0.3s ease;
        color: var(--text-primary);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .payment-card {
        background: var(--bg-card);
        border-radius: 2rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        padding: 2.5rem;
    }

    .payment-card:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-2px);
    }

    .input-field {
        background: var(--bg-input);
        border: 1px solid var(--border-color);
        border-radius: 1rem;
        padding: 0.75rem 1.25rem;
        color: var(--text-primary);
        transition: all 0.3s ease;
        width: 100%;
        font-size: 1rem;
    }

    .input-field:focus {
        outline: none;
        border-color: var(--active-border);
        box-shadow: 0 0 0 4px var(--active-bg);
    }

    .input-field:read-only {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .btn-submit {
        background: linear-gradient(135deg, var(--primary-from), var(--primary-to));
        border: none;
        border-radius: 1.5rem;
        padding: 1rem 2rem;
        font-weight: 700;
        font-size: 1.1rem;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px var(--shadow-color);
        width: 100%;
        cursor: pointer;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px var(--shadow-color);
    }

    .btn-submit:active {
        transform: scale(0.98);
    }

    .info-box {
        background: var(--active-bg);
        border: 1px solid rgba(16, 185, 129, 0.15);
        border-radius: 1.25rem;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    html.dark .info-box {
        border-color: rgba(52, 142, 211, 0.25);
    }

    .tab-btn {
        background: var(--tab-bg);
        border: 2px solid transparent;
        border-radius: 0.75rem;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.3s ease;
        flex: 1;
        text-align: center;
    }

    .tab-btn:hover {
        background: var(--tab-hover);
        transform: translateY(-1px);
    }

    .tab-btn.active {
        border-color: var(--active-border);
        background: var(--active-bg);
        color: var(--text-primary);
        box-shadow: 0 0 0 3px var(--active-bg);
    }

    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .tab-content.active {
        display: block;
    }

    .tab-content img {
        width: 100px;
        height: 100px;
        object-fit: contain;
        border-radius: 0.75rem;
        background: white;
        padding: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 640px) {
        .payment-card {
            padding: 1.5rem;
        }
        .tab-btn {
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
        }
        .tab-content img {
            width: 80px;
            height: 80px;
        }
    }
</style>

<script>
    function selectPaymentMethod(method) {
        document.getElementById('paymentMethodInput').value = method;

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.method === method) {
                btn.classList.add('active');
            }
        });

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
            if (content.id === 'tab-' + method) {
                content.classList.add('active');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        selectPaymentMethod('kpay');
    });
</script>

<div class="container max-w-3xl mx-auto py-12 px-4">
    <div class="payment-card">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-3 bg-gradient-to-br from-emerald-600 to-teal-600 rounded-2xl text-white shadow-lg shadow-emerald-500/20">
                <i class="fas fa-credit-card text-xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Submit Payment Proof</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Complete your payment to borrow this book</p>
            </div>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 p-4 rounded-xl mb-6 flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                <span><?= htmlspecialchars($_SESSION['error_message']) ?></span>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="info-box mb-6">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-emerald-600 dark:text-emerald-400"></i>
                Payment Account Information
            </h3>

            <div class="flex gap-3 mb-4">
                <button class="tab-btn active" data-method="kpay" onclick="selectPaymentMethod('kpay')">KPay</button>
                <button class="tab-btn" data-method="wavepay" onclick="selectPaymentMethod('wavepay')">WavePay</button>
            </div>

            <div class="tab-content active" id="tab-kpay">
                <div class="text-center">
                    <p class="font-bold text-teal-600 dark:text-teal-400 text-lg">KPay</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Account: 09-123456789</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Name: Library Fund</p>
                    <img src="<?= BASE_URL ?>/images/kpay-qr.png" alt="KPay QR" class="mx-auto mt-3">
                </div>
            </div>

            <div class="tab-content" id="tab-wavepay">
                <div class="text-center">
                    <p class="font-bold text-blue-600 dark:text-blue-400 text-lg">WavePay</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Account: 09-987654321</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Name: Library Fund</p>
                    <img src="<?= BASE_URL ?>/images/wavepay-qr.png" alt="WavePay QR" class="mx-auto mt-3">
                </div>
            </div>
        </div>

        <form action="<?= BASE_URL ?>/payment/submit" method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="loan_id" value="<?= $loan->getId() ?>">
            <input type="hidden" name="payment_method" id="paymentMethodInput" value="kpay">
            
            <!-- 🔐 Idempotency Key - prevents duplicate submissions -->
            <input type="hidden" name="idempotency_key" value="<?= htmlspecialchars($idempotencyKey ?? '') ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Payment Amount (MMK)
                </label>
                
                <input
                    type="number"
                    name="amount"
                    value="<?= htmlspecialchars($borrowingFee ?? 5000) ?>"
                    readonly
                    class="input-field"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Transaction Reference <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="transaction_reference"
                    required
                    class="input-field"
                    placeholder="e.g. KPay-1234567890"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Payment Screenshot <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        type="file"
                        name="screenshot"
                        accept="image/*"
                        required
                        class="input-field file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-900/30 dark:file:text-emerald-300"
                    >
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Supported: JPG, PNG (Max 2 MB)
                </p>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-check mr-2"></i>
                Submit Payment Proof
            </button>
        </form>
    </div>
</div>

<?php include BASE_PATH . '/view/layout/footer.php'; ?>