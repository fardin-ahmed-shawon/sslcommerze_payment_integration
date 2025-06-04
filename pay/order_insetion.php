<?php
require_once '../get-sa-connection.php';

// Check if user is logged in
if (!isset($_SESSION['np2_signd_in']) || !isset($_SESSION['np2_user_id'])) {
    echo reloader("404.php?status=not_logged_in", 0);
    exit;
}

$order_token = $_SESSION['CurrentCartSession'];

// Place Order Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['placeOrder'])) {
    $customer_id = isset($_SESSION['np2_user_id']) ? $_SESSION['np2_user_id'] : 0; // Or handle guest checkout
    
    // $first_name = $_POST['first_name'];
    // $last_name = $_POST['last_name'];
    // $full_name = $first_name . ' ' . $last_name;

    $full_name = $_POST['full_name'];

    $street = $_POST['street'];
    $city = $_POST['city'];
    $phone = $_POST['phone'];
    $email_address = $_POST['email_address'] ?? '';
    $order_status = 'Pending';
    $coupon_code = $_POST['coupon_code'] ?? '';

    $payment_option = $_POST['payment_option'];
    
    

    // Get distinct vendor IDs from the cart table for the current order token
    $vendorQuery = "SELECT DISTINCT vendor_id FROM cart WHERE cart_session = ?";
    $vendorStmt = $con->prepare($vendorQuery);
    $vendorStmt->bind_param("s", $order_token);
    $vendorStmt->execute();
    $vendorResult = $vendorStmt->get_result();

    while ($vendorRow = $vendorResult->fetch_assoc()) {
        $vendor_id = $vendorRow['vendor_id'];
        $total_price = 0;
        $total_commission_price = 0;
        $sql = "SELECT * FROM cart WHERE cart_session = '$order_token' AND vendor_id = '$vendor_id'";
        $cartResult = $con->query($sql);
        if ($cartResult && $cartResult->num_rows > 0) {
            while ($item = mysqli_fetch_assoc($cartResult)) {
                $unit_price = $item['product_price'];
                $quantity = $item['qty'];
                $product_price = $unit_price * $quantity;
                $total_price += $product_price;

                $unit_commission_price = $item['product_commission_price'];
                $product_commission_price = $unit_commission_price * $quantity;
                $total_commission_price += $product_commission_price;
            }
        }

        $discount_amount = 0;
        if (!empty($coupon_code)) {
            $couponQuery = "SELECT * FROM coupon WHERE coupon_code = ? AND vendor_id = ?";
            $couponStmt = $con->prepare($couponQuery);
            $couponStmt->bind_param("si", $coupon_code, $vendor_id);
            $couponStmt->execute();
            $couponResult = $couponStmt->get_result();
            if ($couponResult->num_rows > 0) {
                $discountRow = $couponResult->fetch_assoc();
                $discount_amount = $discountRow['coupon_discount'];
                $total_price -= $discount_amount;
            }
            $couponStmt->close();
        }


        $stmt = $con->prepare("INSERT INTO orders (customer_id, order_token, vendor_id, order_name, order_phone, order_email, order_shipping_address, amount_to_pay, total_commission_amount, amount_paid_by, amount_discount, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isisssssssssss", $customer_id, $order_token, $vendor_id, $full_name, $phone, $email_address, $street, $total_price, $total_commission_price, $payment_option, $discount_amount, $order_status);

        if ($stmt->execute()) {
            $order_success = true;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();


    }
    $vendorStmt->close();
    unset($_SESSION['CurrentCartSession']);
    echo "<script>window.location.href='index.php?sm=1';</script>";
    exit();
}
?>