USE `web_tra_sua`;

ALTER TABLE `orders`
ADD COLUMN `customer_name` VARCHAR(100) NOT NULL AFTER `total_amount`,
ADD COLUMN `phone` VARCHAR(15) NOT NULL AFTER `customer_name`,
ADD COLUMN `address` VARCHAR(255) NOT NULL AFTER `phone`;
