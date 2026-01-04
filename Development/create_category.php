<?php
  // create_category.php

  $some_data = array(
    'catname' => 'EduGreenTourism Booking', // UPDATED: Your Project Name
    'catdescription' => 'Payment for EduGreenTourism packages and activities', // UPDATED: Description
    'userSecretKey' => 'w5x7srq7-rx5r-3t89-2ou2-k7361x2jewhn' // REPLACE THIS with your actual Sandbox Secret Key
  );  

  $curl = curl_init();

  curl_setopt($curl, CURLOPT_POST, 1);
  // UPDATED: Changed to 'dev.toyyibpay.com' for testing
  curl_setopt($curl, CURLOPT_URL, 'https://dev.toyyibpay.com/index.php/api/createCategory');  
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);

  $result = curl_exec($curl);
  $info = curl_getinfo($curl);  
  curl_close($curl);

  $obj = json_decode($result);
  
  // Display the result clearly
  echo "<h1>Category Creation Result</h1>";
  echo "<pre>";
  print_r($result);
  echo "</pre>";
  
  if (isset($obj[0]->CategoryCode)) {
      echo "<p style='color:green; font-weight:bold;'>SUCCESS! Your Category Code is: " . $obj[0]->CategoryCode . "</p>";
      echo "<p>Copy this code into your process_booking.php file.</p>";
  }
?>