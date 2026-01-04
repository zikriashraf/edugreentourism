<?php
// payment_return.php
$status_id = $_GET['status_id'] ?? 0;
$billcode = $_GET['billcode'] ?? '';
$msg = ($status_id == 1) ? "Payment Successful! Thank you." : "Payment Failed or Pending.";
?>
<!DOCTYPE html>
<html>
<head><title>Payment Status</title></head>
<body style="text-align:center; padding:50px; font-family:sans-serif;">
    <h1 style="color: <?php echo ($status_id==1)?'green':'red'; ?>;"><?php echo $msg; ?></h1>
    <p>Bill Code: <?php echo htmlspecialchars($billcode); ?></p>
    <a href="index.html" style="padding:10px 20px; background:green; color:white; text-decoration:none;">Return Home</a>
    <script>
        if("<?php echo $status_id; ?>" == "1") {
            sessionStorage.removeItem('checkout_session');
            sessionStorage.removeItem('standard_cart');
        }
    </script>
</body>
</html>