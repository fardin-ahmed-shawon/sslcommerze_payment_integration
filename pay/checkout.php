<?php
include ('../get-sa-connection.php');
if(isset($_GET['stk'])){
$token = $_GET['stk'];

//////////////
$sql = "SELECT * FROM cart WHERE cart_session=?";
$stmt = $np2con->prepare($sql); 
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$cnttk = mysqli_num_rows($result);
if($cnttk>0){
$ins_mc_title2 = '';	
$totalamount = 0;	

while ($row = $result->fetch_assoc()) {
$user_id = $_SESSION['np2_user_id'];
$user_name = $_POST['full_name'];
$user_phone = $_POST['phone'];
$user_email = '';

// $ins_mc_title = $row['ins_mc_title'] ?? '';
// $ins_mc_title2 .= $ins_mc_title. ' ~ ';
// $totalamount += $row['ins_mc_offer_fee'] ?? '';

$totalamount = $_POST['total_amount'];

}	
}
/* echo $ins_mc_title2;
echo $user_name;
echo $totalamount; */
////////////////////

}else {
die();	
}
//die();
/* PHP */
$post_data = array();
$post_data['store_id'] = "abcac6107bdba8242f";
$post_data['store_passwd'] = "abcac6107bdba8242f@ssl";
$post_data['total_amount'] = "$totalamount";
$post_data['currency'] = "BDT";
$post_data['tran_id'] = "SSL_".uniqid();
$post_data['success_url'] = "http://localhost/test/Amali-E-commerce/pay/success.php";
$post_data['fail_url'] = "http://localhost/test/Amali-E-commerce/pay/fail.php";
$post_data['cancel_url'] = "http://localhost/test/Amali-E-commerce/pay/cancel.php";
# $post_data['multi_card_name'] = "mastercard,visacard,amexcard";  # DISABLE TO DISPLAY ALL AVAILABLE

# EMI INFO
$post_data['emi_option'] = "1";
$post_data['emi_max_inst_option'] = "9";
$post_data['emi_selected_inst'] = "9";

# CUSTOMER INFORMATION
$post_data['cus_name'] = "$user_name";
$post_data['cus_email'] = "$user_email";
/* $post_data['cus_add1'] = "Dhaka";
$post_data['cus_add2'] = "Dhaka";
$post_data['cus_city'] = "Dhaka";
$post_data['cus_state'] = "Dhaka";
$post_data['cus_postcode'] = "1000";
$post_data['cus_country'] = "Bangladesh"; */
$post_data['cus_phone'] = "$user_phone";
//$post_data['cus_fax'] = "01711111111";

# SHIPMENT INFORMATION
$post_data['ship_name'] = "Abc Academy";
$post_data['ship_add1 '] = "Dhaka";
$post_data['ship_add2'] = "Dhaka";
$post_data['ship_city'] = "Dhaka";
$post_data['ship_state'] = "Dhaka";
$post_data['ship_postcode'] = "1000";
$post_data['ship_country'] = "Bangladesh"; 
/* 
*/

# OPTIONAL PARAMETERS
//Token 
$post_data['value_a'] = "$token";
//user_id 
$post_data['value_b'] = "$user_id";
/* $post_data['value_b '] = "ref002";
$post_data['value_c'] = "ref003";
$post_data['value_d'] = "ref004"; */

# CART PARAMETERS
$post_data['cart'] = json_encode(array(
    array("product"=>"DHK TO BRS AC A1","amount"=>"200.00"),
    array("product"=>"DHK TO BRS AC A2","amount"=>"200.00"),
    array("product"=>"DHK TO BRS AC A3","amount"=>"200.00"),
    array("product"=>"DHK TO BRS AC A4","amount"=>"200.00")
));
$post_data['product_amount'] = "100";
$post_data['vat'] = "5";
$post_data['discount_amount'] = "5";
$post_data['convenience_fee'] = "3";



# REQUEST SEND TO SSLCOMMERZ
$direct_api_url = "https://sandbox.sslcommerz.com/gwprocess/v3/api.php";

$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $direct_api_url );
curl_setopt($handle, CURLOPT_TIMEOUT, 30);
curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($handle, CURLOPT_POST, 1 );
curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC


$content = curl_exec($handle );

$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

if($code == 200 && !( curl_errno($handle))) {
	curl_close( $handle);
	$sslcommerzResponse = $content;
} else {
	curl_close( $handle);
	echo "FAILED TO CONNECT WITH SSLCOMMERZ API";
	exit;
}

# PARSE THE JSON RESPONSE
$sslcz = json_decode($sslcommerzResponse, true );

if(isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL']!="" ) {
        # THERE ARE MANY WAYS TO REDIRECT - Javascript, Meta Tag or Php Header Redirect or Other
        # echo "<script>window.location.href = '". $sslcz['GatewayPageURL'] ."';</script>";
	echo "<meta http-equiv='refresh' content='0;url=".$sslcz['GatewayPageURL']."'>";
	# header("Location: ". $sslcz['GatewayPageURL']);
	exit;
} else {
	echo "JSON Data parsing error!";
}

?>
