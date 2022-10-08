<?php
session_start();
include_once "../config/db_connection.php";
$con = connection();

if (isset($_POST['btn_update'])){
    #-----------checking alphabetical error-----------------------------
    $errors = array();
    $error_count = null;
    echo print_r($_POST)."<br>"."<br>"."<br>";
    echo print_r($_SESSION['user_info'])."<br>"."<br>";
    $user_id = $_SESSION['user_info']['id'];

    if(ctype_alpha(trim($_POST['first_name'])) && (trim($_POST['last_name']))){
        $name =strtolower(trim($_POST['first_name'])." ". trim($_POST['last_name']));
    }else{
        $error= "Please type your name with alphabetical later";
        $error_count++;
        array_push($errors,$error);

    }
    $gender = $_POST['gender'];

    # ---------------------------date validation ------------------------
    /*
    $current_year= date('Y');
    $usr_year = date('Y', strtotime($_POST['dob']));
    */
    $birth_date = new DateTime($_POST['dob']);
    $today     = new DateTime();
    $interval  = $today->diff($birth_date);
    if ($interval->days < 5840){
        $error =  "Sorry! you are under 16 years old be mature first";
        $error_count++;
        array_push($errors,$error);
    }else{
        $age = $interval->days;
        if ($interval->days >54750 ){
            $error = "Your age is logically invalid please enter valid data";
            $error_count++;
            array_push($errors,$error);
        }else{
            $d_o_b = $_POST['dob'];
        }
    }

    $country = $_POST['country'];
    #-----------checking alphabetical error---------------------

    $city = strtolower($_POST['city']);



    $zip_code = strtoupper($_POST['zip_code']);

    $phone = $_POST['phone'];

    // Check connection
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    //Validating data
    if ($error_count!= null) {
        header("Location:../edit_profile.php?error_reg=" . urlencode(serialize($errors)));

    }else{
        $sql_address_info = "SELECT * FROM adrees_info WHERE zip_code='$zip_code' && city='$city' && country_id=$country;";
        $add_info = $con->query($sql_address_info);

        #address info
        $address_info = $add_info->fetch_assoc();
        $add_id = $address_info['id'];
        echo print_r($add_info);


        if ($add_info->num_rows<1){
            $sql = "INSERT INTO `adrees_info` (`zip_code`, `city`, `country_id`) VALUES ('$zip_code', '$city', '$country');";
            $con->query($sql);

            //Retrieve new information of address
            $sql_address_info = "SELECT * FROM adrees_info WHERE zip_code='$zip_code' && city='$city' && country_id=$country;";
            $add_info = $con->query($sql_address_info);

            #address info
            $address_info = $add_info->fetch_assoc();
            $add_id = $address_info['id'];


            $sql = "UPDATE user_address SET address_id_fk = $add_id WHERE user_id_fk = $user_id;";
            if ($con->query($sql)===TRUE){
                echo "new address data inserted";
            }else{
                echo "new address data error";
            }

        }elseif($add_info->num_rows==1){
            $sql = "UPDATE user_address SET address_id_fk = $add_id WHERE user_id_fk = $user_id;";
            if ($con->query($sql)===TRUE){
                echo "relational table updated";
            }else{
                echo "error relational table ";
            }

        }



        // Updating users table
        $sql_user = "UPDATE users SET name = '$name', gender = $gender,dob = '$d_o_b',phone = '$phone' WHERE id = $user_id";

        if ($con->query($sql_user)){
            #updated data
            #login autometicaly
            $sql = "SELECT users.*,adrees_info.id AS add_id, adrees_info.zip_code, adrees_info.city,adrees_info.country_id,country.name AS country_name,country.phonecode
                FROM users
                left join user_address on user_address.user_id_fk = users.id
                 left join adrees_info on adrees_info.id = user_address.address_id_fk
                left join country on country.id = adrees_info.country_id
                where users.id = $user_id;";
            #-----------------User data----------------------
            session_unset();
            $user_info = $con->query($sql);
            $_SESSION['user_info'] = $user_info->fetch_assoc();
            $msg = "Your information updated successfully";
            header("Location: ../profile_view.php?success=$msg");
        }else{
            echo "";
            exit;
        }
//        echo "<br>Name : $name<br>Gender : $gender<br>Date of birth: $dob<br>CIty : $city<br>country : $country<br>Zip-code : $zip_code<br>phone code: $phone_code<br>zip code : $zip_code<br>Phone : $phone";
    }
}
else{
    echo "no no no no ";
}
?>