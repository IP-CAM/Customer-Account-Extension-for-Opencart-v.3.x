<?php
class ModelAccountVoucher extends Model {
	public function activateVoucherCode($voucher_id) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_voucher` SET customer_id = '" . (int)$this->customer->getId() . "', voucher_id = '" . (int)$voucher_id . "'");
	}

	public function checkVoucherStatus($code) {
	    $query = $this->db->query("SELECT cv.voucher_id FROM `" . DB_PREFIX . "customer_voucher` cv LEFT JOIN `" . DB_PREFIX . "voucher` v ON (cv.voucher_id = v.voucher_id) WHERE v.code = '" . $this->db->escape($code) . "'");

	    return (isset($query->row['voucher_id']) ? $query->row['voucher_id'] : '');
	}

    public function getVoucherIdByCode($code) {
        $query = $this->db->query("SELECT voucher_id FROM `" . DB_PREFIX . "voucher` WHERE `code` = '" . $this->db->escape($code) . "'");

        return (isset($query->row['voucher_id']) ? $query->row['voucher_id'] : '');
    }

	public function getCustomerVouchers() {
        $query = $this->db->query("SELECT v.*, vt.image, vtd.name FROM `" . DB_PREFIX . "voucher` v LEFT JOIN `" . DB_PREFIX . "customer_voucher` cv ON (v.voucher_id = cv.voucher_id) LEFT JOIN `" . DB_PREFIX ."voucher_theme` vt ON (v.voucher_theme_id = vt.voucher_theme_id) LEFT JOIN `" . DB_PREFIX . "voucher_theme_description` vtd ON (v.voucher_theme_id = vtd.voucher_theme_id) WHERE cv.customer_id = '" . (int)$this->customer->getId() . "' AND vtd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->rows;
    }
}
