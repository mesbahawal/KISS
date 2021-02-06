# KISS
Keep It Simple, Stupid with PHP

# MySQL setup
1. Database name: kiss
2. Table name: sales_product

# Database table description:
CREATE TABLE `kiss`.`sales_product`(  
  `sale_id` INT(3),
  `customer_name` VARCHAR(255),
  `customer_mail` VARCHAR(255),
  `product_id` INT(3),
  `product_name` VARCHAR(255),
  `product_price` DECIMAL(5,2),
  `sale_date` DATETIME,
  `version` VARCHAR(50)
);

# Database connection:
Edit "index.php" (line no:2) and put username and password for DB connection.

# Home URL:
http://localhost/KISS/index.php
