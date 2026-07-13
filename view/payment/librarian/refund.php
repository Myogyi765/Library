<!-- view/payment/librarian/refund.php -->
<?php include __DIR__ . '/../../layout/header.php'; ?>
<div class="container">
    <h2>Refund Payment</h2>
    <p>Payment ID: <?= htmlspecialchars($payment->id) ?></p>
    <p>Amount: <?= htmlspecialchars($payment->amount) ?></p>
    <form action="<?= BASE_URL ?>/librarian/payments/<?= $payment->id ?>/refund" method="POST">
        <div class="form-group">
            <label for="refund_reason">Refund Reason:</label>
            <textarea name="refund_reason" id="refund_reason" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to refund this payment?')">Confirm Refund</button>
        <a href="<?= BASE_URL ?>/librarian/payments" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>