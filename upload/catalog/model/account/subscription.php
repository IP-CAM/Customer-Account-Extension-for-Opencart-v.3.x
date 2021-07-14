<?php
class ModelAccountSubscription extends Model {
	public function saveSubscriptionOnMailings($data = array()) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_mailings` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		if (empty($query->row)) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_mailings` SET customer_id = '" . (int)$this->customer->getId() . "', mailings = '" . $this->db->escape(json_encode($data)) . "'");
        } else {
            $this->db->query("UPDATE `" . DB_PREFIX . "customer_mailings` SET mailings = '" . $this->db->escape(json_encode($data)) . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
        }
	}

	public function getSubscriptionOnMailings() {
        $query = $this->db->query("SELECT mailings FROM `" . DB_PREFIX . "customer_mailings` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

        if ($query->row) {
            return json_decode($query->row['mailings'], true);
        } else {
            return array();
        }
    }

    public function getCategories() {
        $sql = "SELECT cp.category_id AS category_id, cd2.name AS name FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $sql .= " GROUP BY cp.category_id";

        $query = $this->db->query($sql);

        return $query->rows;
    }
}
