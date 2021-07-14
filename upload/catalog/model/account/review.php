<?php
class ModelAccountReview extends Model {
	public function getReviews($data = array()) {
	    $sql = "SELECT * FROM `" . DB_PREFIX . "review` r WHERE r.customer_id = '" . (int)$this->customer->getId() . "'";

        $sort_data = array(
            'date_added',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY date_added";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalReviews() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "review`WHERE customer_id = '" . $this->customer->getId() . "'");

		return $query->row['total'];
	}

	public function getProductsWithoutReview() {
	    $query = $this->db->query("SELECT op.*, p.image, p.weight, wcd.unit as weight_unit FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX ."product` p ON (op.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "weight_class_description` wcd ON (p.weight_class_id = wcd.weight_class_id) WHERE o.customer_id = '" . $this->customer->getId() . "' GROUP BY op.product_id");

	    foreach ($query->rows as $key => $row) {
	        $query2 = $this->db->query("SELECT review_id FROM `" . DB_PREFIX . "review` WHERE customer_id = '" . $this->customer->getId() . "' AND product_id = '" . (int)$row['product_id'] . "'");

	        if (!empty($query2->row)) {
	            unset($query->rows[$key]);
            }
        }

	    return $query->rows;
    }
}
