<?php
class ModelExtensionModuleCustomerInfo extends Model {
	public function install() {
		$this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "customer_info` (
              `customer_id` INT(11) NOT NULL,
              `sex` INT(1) DEFAULT 0,
              `delivery_address` VARCHAR(255) NOT NULL,
              `delivery_recipient_name` VARCHAR(50) NOT NULL,
              `review_name` VARCHAR(255) NOT NULL,
              PRIMARY KEY (`customer_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "customer_mailings` (
              `customer_id` INT(11) NOT NULL,
              `mailings` TEXT NOT NULL,
              `personal_mailings` TEXT NOT NULL,
              PRIMARY KEY (`customer_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "customer_voucher` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `customer_id` INT(11) NOT NULL,
              `voucher_id` INT(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "order_review`");
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "order_review` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `order_id` INT(11) NOT NULL,
              `delivery_rating` INT(1) NOT NULL,
              `options` TEXT NOT NULL,
              `text` VARCHAR(255) NOT NULL,
              `images` TEXT NOT NULL,
              `need_callback` INT(1) DEFAULT 0,
              `status` INT(1) DEFAULT 0,
              `date_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_info`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_mailings`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_voucher`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "order_review`");
	}

    public function saveCustomerInfo($customer_id, $data) {
	    $query = $this->db->query("SELECT customer_id FROM `" . DB_PREFIX ."customer_info` WHERE customer_id = '" . (int)$customer_id . "'");

	    if (!isset($query->row['customer_id'])) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_info` SET `customer_id` = '" . (int)$customer_id . "', `sex` = '" . (isset($data['sex']) ? (int)$data['sex'] : 0) . "', `delivery_address` = '" . $this->db->escape($data['delivery_address']) . "', `delivery_recipient_name` = '" . $this->db->escape($data['delivery_recipient_name']) . "', `review_name` = '" . $this->db->escape($data['review_name']) . "'");
        } else {
            $this->db->query("UPDATE `" . DB_PREFIX . "customer_info` SET `sex` = '" . (isset($data['sex']) ? (int)$data['sex'] : 0) . "', `delivery_address` = '" . $this->db->escape($data['delivery_address']) . "', `delivery_recipient_name` = '" . $this->db->escape($data['delivery_recipient_name']) . "', `review_name` = '" . $this->db->escape($data['review_name']) . "' WHERE `customer_id` = '" . (int)$customer_id . "'");
        }
	}

    public function removeCustomerInfo($customer_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_info` WHERE `customer_id` = '" . (int)$customer_id . "'");
    }

    public function getCustomerInfo($customer_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX ."customer_info` WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row;
    }
}
