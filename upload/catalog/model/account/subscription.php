<?php
class ModelAccountSubscription extends Model {
    public function getMailingCategories() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mailing_category`");

        return $query->rows;
    }

    public function getMailingCategoryCustomer($mailing_category_id) {
        $query = $this->db->query("SELECT customer_id FROM `" . DB_PREFIX . "customer_to_mailing` WHERE customer_id = '" . (int)$this->customer->getId() . "' AND mailing_category_id = '" . (int)$mailing_category_id . "'");

        $customers = array();

        foreach ($query->rows as $row) {
            $customers[] = $row['customer_id'];
        }

        return $customers;
    }

    public function changeSubscriberStatus($data) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_to_mailing` WHERE customer_id = '" . (int)$this->customer->getId() . "' AND mailing_category_id = '" . (int)$data['mailing_category_id'] . "'");

        if (isset($data['status']) && (int)$data['status'] != 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_to_mailing` SET customer_id = '" . (int)$this->customer->getId() . "', mailing_category_id = '" . (int)$data['mailing_category_id'] . "'");
        }
    }

    public function unsubscribeFromAll() {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_to_mailing` WHERE customer_id = '" . (int)$this->customer->getId() . "'");
    }
}
