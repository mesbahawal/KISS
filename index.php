<?php
$user = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=localhost;dbname=kiss", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

function upload_json($conn){
    // define variables
    $json_upoload_path = 'jsonUploads';
    $file_name = 'data_'.date("Ymd").'.json';

    // make json upload directory
    if (!file_exists($json_upoload_path)) {
        mkdir($json_upoload_path, 0700, true);
    }

    // move json file
    if(copy($_FILES['jsonFile']['tmp_name'], $json_upoload_path.'/'.$file_name)){
        $data = file_get_contents($json_upoload_path.'/'.$file_name);
        $products = json_decode($data);
        foreach ($products as $product) {
            $stmt = $conn->prepare('insert into sales_product(sale_id, customer_name, customer_mail, product_id, product_name, product_price, sale_date, version) values(:sale_id, :customer_name, :customer_mail, :product_id, :product_name, :product_price, :sale_date, :version)');
            $stmt->bindValue('sale_id', $product->sale_id);
            $stmt->bindValue('customer_name', $product->customer_name);
            $stmt->bindValue('customer_mail', $product->customer_mail);
            $stmt->bindValue('product_id', $product->product_id);
            $stmt->bindValue('product_name', $product->product_name);
            $stmt->bindValue('product_price', $product->product_price);
            $stmt->bindValue('sale_date', $product->sale_date);
            $stmt->bindValue('version', $product->version);
            $stmt->execute();
        }
    }
    else{
        // error msg
        echo '<h3 style="color: red; text-align: center">'."JSON upload failed".'</h3>';
    }
}

function get_sales_product_detail($conn){
    // get total row count
    $query_count = "SELECT count(*) FROM `sales_product`";
    $stmt = current($conn->query($query_count)->fetch());
    $result_count = $stmt;

    if($result_count>0){
        // define filter query for all data:
        $filter_query = "SELECT * FROM `sales_product`";

        // return result data
        $stmt = $conn->prepare($filter_query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        if(isset($_POST['buttonFilter'])) {

            if($_POST['filt_price']=='-1' && $_POST['filt_cust']=='-1' && $_POST['filt_prod']=='-1'){
                // define query
                $filter_query = "SELECT * FROM `sales_product`";
                $stmt = $conn->prepare($filter_query);
                $stmt->execute();
                $result = $stmt->fetchAll();
            }
            elseif ($_POST['filt_cust']!='-1'){
                // define query
                $filter_query = "SELECT * FROM `sales_product` where customer_name=:customer_name";
                $stmt = $conn->prepare($filter_query);
                $stmt->execute(['customer_name' => $_POST['filt_cust']]);
                $result = $stmt->fetchAll();
            }
            elseif ($_POST['filt_prod']!='-1'){
                // define query
                $filter_query = "SELECT * FROM `sales_product` where product_id=:product_id";
                $stmt = $conn->prepare($filter_query);
                $stmt->execute(['product_id' => $_POST['filt_prod']]);
                $result = $stmt->fetchAll();
            }
            elseif ($_POST['filt_price']!='-1'){
                $product_price_high = $_POST['filt_price'] + 15;
                // define query
                $filter_query = "SELECT * FROM `sales_product` where product_price>=:product_price_low and product_price<=:product_price_high";
                $stmt = $conn->prepare($filter_query);
                $stmt->execute(['product_price_low' => $_POST['filt_price'],'product_price_high' => $product_price_high]);
                $result = $stmt->fetchAll();
            }
        }

        return $result;
    }
    else{
        return 0;
    }

}

function get_customer_name($conn){
    // define query
    $query = "SELECT distinct customer_name FROM `sales_product` order by customer_name";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll();

    return $result;
}

function get_product_name($conn){
    // define query
    $query = "SELECT distinct product_id, product_name FROM `sales_product` order by product_name";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll();

    return $result;
}
?>

<html lang="en">
<head>
    <title>KISS</title>
    <style>
        .center {
            margin-left: auto;
            margin-right: auto;
        }
        th, td {
            padding-right: 15px;
        }
    </style>
</head>
<body>
<h4 style="text-align: center;"><a href="index.php">Home</a> | <a href="index.php">Import JSON</a> </h4>
<form method="post" enctype="multipart/form-data" action="index.php">
    JSON File <input type="file" name="jsonFile">
    <br>
    <input type="submit" value="Import" name="buttonImport">
</form>

<?php

if(isset($_POST['buttonImport'])) {
    upload_json($conn);
}
?>

<h3 style="text-align: center;"><u>View Products</u></h3>

<form method="post" enctype="multipart/form-data" action="index.php" style="text-align: center">
    Customer <select name="filt_cust">
        <option value="-1">All</option>
        <?php
        $customer_data = get_customer_name($conn);
        foreach($customer_data as $data){
            ?>
            <option value="<?=$data["customer_name"]?>"><?=$data["customer_name"]?></option>
            <?php
        }
        ?>
    </select>

    Product <select name="filt_prod">
        <option value="-1">All</option>
        <?php
        $product_data = get_product_name($conn);
        foreach($product_data as $data){
            ?>
            <option value="<?=$data["product_id"]?>"><?=$data["product_name"]?></option>
            <?php
        }
        ?>
    </select>

    Price <select name="filt_price">
        <option value="-1">All</option>
        <option value="0">0>=15</option>
        <option value="15">15>=30</option>
        <option value="30">30>=45</option>
        <option value="45">45>=60</option>

    </select>
    <input type="submit" value="Filter" name="buttonFilter">
</form>


<table style="border: 1px solid;" class="center" >
    <tr>
        <th style='width:10px; border-bottom: 1px solid'>ID</th>
        <th style='width:150px; border-bottom: 1px solid'>Customer Name</th>
        <th style='width:80px; border-bottom: 1px solid'>Email</th>
        <th style='width:200px;border-bottom: 1px solid'>Product Name</th>
        <th style='width:50px; border-bottom: 1px solid'>Price</th>
        <th style='width:100px; border-bottom: 1px solid'>Sale Date</th>
    </tr>
    <?php
    //include_once "function.php";
    $sales_data = get_sales_product_detail($conn);

    if($sales_data == 0 || count($sales_data)==0){
        ?>
        <tr ><td colspan="6" style="border: 1px solid; text-align: center">No records found</td><tr/>
        <?php
    }
    else{
        $total_price = 0;
        foreach ($sales_data as $row) {
            $version = str_replace(['.','+'],'',$row['version']);
            $version = str_pad($version, 6, "0", STR_PAD_RIGHT);
            $timezone = $version > 101760 ? 'UTC' : 'Europe/Berlin';
            echo "<tr>
                         <td>".$row['sale_id']."</td>
                         <td>".$row['customer_name']."</td>
                         <td>".$row['customer_mail']."</td>
                         <td>".$row['product_name']."</td>
                         <td>".$row['product_price']."</td>
                         <td>".$row['sale_date']."(".$timezone.")</td>
                      </tr>";

            $total_price += $row['product_price'];
        }
        echo "<tr>
                         <td colspan='4' style='border-top: 1px solid; text-align: right'><strong>Total=</strong></td>
                         <td style='border-top: 1px solid'><strong>".$total_price."</strong></td>
                      </tr>";
    }
    ?>
</table>
</body>
</html>