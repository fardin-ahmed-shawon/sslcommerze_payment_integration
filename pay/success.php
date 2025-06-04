<h1>Payment Successful</h1>
<?php

if(isset($_POST['val_id'])){
$val_id=urlencode($_POST['val_id']);
$store_id=urlencode("abcac6107bdba8242f");
$store_passwd=urlencode("abcac6107bdba8242f@ssl");
$requested_url = ("https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php?val_id=".$val_id."&store_id=".$store_id."&store_passwd=".$store_passwd."&v=1&format=post");

$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $requested_url);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false); # IF YOU RUN FROM LOCAL PC
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); # IF YOU RUN FROM LOCAL PC

$result = curl_exec($handle);

$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

if($code == 200 && !( curl_errno($handle)))
{

	# TO CONVERT AS ARRAY
	# $result = json_decode($result, true);
	# $status = $result['status'];

	# TO CONVERT AS OBJECT
	$result = json_decode($result);

	# TRANSACTION INFO
	$status = $result->status;
	$tran_date = $result->tran_date;
	$tran_id = $result->tran_id;
	$val_id = $result->val_id;
	$amount = $result->amount;
	$store_amount = $result->store_amount;
	$bank_tran_id = $result->bank_tran_id;
	$card_type = $result->card_type;
    
   $cart_token = $result->value_a;
   $user_id = $result->value_b;
    


	# EMI INFO
	$emi_instalment = $result->emi_instalment;
	$emi_amount = $result->emi_amount;
	$emi_description = $result->emi_description;
	$emi_issuer = $result->emi_issuer;

	# ISSUER INFO
	$card_no = $result->card_no;
	$card_issuer = $result->card_issuer;
	$card_brand = $result->card_brand;
	$card_issuer_country = $result->card_issuer_country;
	$card_issuer_country_code = $result->card_issuer_country_code;

	# API AUTHENTICATION
	$APIConnect = $result->APIConnect;
	$validated_on = $result->validated_on;
	$gw_version = $result->gw_version;
//echo $status;


/// code to store update succe By sayeed amin 
if(isset($tran_id) AND  $tran_id !=''){
if($status == 'VALID'){

    require '../get-sa-connection.php';

    $order_token = $_SESSION['CurrentCartSession'];
    $customer_id = isset($_SESSION['np2_user_id']) ? $_SESSION['np2_user_id'] : 0; // Or handle guest checkout
    
    
    // Database insetion order information
    $full_name = 'Test User';
    $street = 'Test Street, Dhaka';
    $city = 'Test City';
    $phone = '01944667441';
    $email_address = 'test@gmail.com';
    $order_status = 'Pending';
    $coupon_code = '';
    $payment_option = 'sslCommerz';
    
    

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
        $stmt->bind_param("isisssssssss", $customer_id, $order_token, $vendor_id, $full_name, $phone, $email_address, $street, $total_price, $total_commission_price, $payment_option, $discount_amount, $order_status);

        if ($stmt->execute()) {
            $order_success = true;
            unset($_SESSION['CurrentCartSession']);
            echo "<script>window.location.href='../index.php?sm=1';</script>";
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();


    }
    $vendorStmt->close();
    

}else {
	echo "Invalid Transaction";
}
}



//echo $cart_token;
} else {
	echo "Failed to connect with SSLCOMMERZ";
}
	
} else {
    echo "Invalid Request";
}


  
?>