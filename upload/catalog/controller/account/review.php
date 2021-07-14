<?php
class ControllerAccountReview extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/review', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

        $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
        $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
        $this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');

		$this->load->language('account/review');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/review', '', true)
		);

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/review', $url, true)
		);

		$this->load->model('account/review');

        $url = '';

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'o.date_added';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

		$data['reviews'] = array();

		$reviews_total = $this->model_account_review->getTotalReviews();

        $filter_data = array(
            'sort'               => $sort,
            'order'              => $order,
            'start'              => ($page - 1) * 10,
            'limit'              => 10
        );

        $results = $this->model_account_review->getReviews($filter_data);

		foreach ($results as $result) {
			$data['reviews'][] = array(
				'review_id'  => $result['review_id'],
				'product_id' => $result['product_id'],
				'customer_id'=> $result['customer_id'],
				'author'     => $result['author'],
				'first_letter_author' => isset($result['author']) ? strtoupper(mb_substr($result['author'],0,1,'utf-8')) : '',
				'text'       => $result['text'],
				'rating'     => $result['rating'],
				'status'     => $result['status'] ? $this->language->get('text_review_published') : $this->language->get('text_review_denied'),
                'date_added' => date('d', strtotime($result['date_added'])) . ' ' . $this->language->get(date('M', strtotime($result['date_added']))) . ' ' . date('Y', strtotime($result['date_added'])),
			);
		}

		$products_without_review = $this->model_account_review->getProductsWithoutReview();

        if (count($products_without_review) == 1) {
            $data['text_products_waiting_rating'] = sprintf($this->language->get('text_products_waiting_rating_1'), count($products_without_review));
        } else if(count($products_without_review) == 2) {
            $data['text_products_waiting_rating'] = sprintf($this->language->get('text_products_waiting_rating_2'), count($products_without_review));
        } else {
            $data['text_products_waiting_rating'] = sprintf($this->language->get('text_products_waiting_rating_5'), count($products_without_review));
        }

        $data['products_waiting_rating'] = array();


        $this->load->model('tool/image');

        foreach ($products_without_review as $product) {
            $data['products_waiting_rating'][] = array(
                'product_id' => $product['product_id'],
                'name'       => $product['name'],
                'quantity'   => $product['quantity'],
                'total_weight' => ((int)$product['quantity'] * (int)$product['weight']),
                'weight_unit'=> $product['weight_unit'],
                'thumb'      => $product['image'] ? $this->model_tool_image->resize($product['image'], 100, 100) : $this->model_tool_image->resize('placeholder.png', 100, 100),
                'link'       => $this->url->link('product/product', '&product_id=' . $product['product_id']),

            );
        }

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (isset($this->request->get['year'])) {
            $url .= '&year=' . $this->request->get['year'];
        }

        $data['sorts'] = array();

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_date_added_asc'),
            'value' => 'r.date_added-ASC',
            'href'  => $this->url->link('account/review', '&sort=r.date_added&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_date_added_desc'),
            'value' => 'r.date_added-DESC',
            'href'  => $this->url->link('account/review', '&sort=r.date_added&order=DESC' . $url)
        );

        $data['sort'] = $sort;
        $data['order'] = $order;

		$pagination = new Pagination();
		$pagination->total = $reviews_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		$pagination->url = $this->url->link('account/review', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($reviews_total) ? (($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) + 1 : 0, ((($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) > ($reviews_total - $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'))) ? $reviews_total : ((($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) + $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')), $reviews_total, ceil($reviews_total / $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')));

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/review_list', $data));
	}

	public function info() {
		$this->load->language('account/return');

		if (isset($this->request->get['return_id'])) {
			$return_id = $this->request->get['return_id'];
		} else {
			$return_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return/info', 'return_id=' . $return_id, true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/return');

		$return_info = $this->model_account_return->getReturn($return_id);

		if ($return_info) {
			$this->document->setTitle($this->language->get('text_return'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', '', true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', true)
			);

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/return', $url, true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_return'),
				'href' => $this->url->link('account/return/info', 'return_id=' . $this->request->get['return_id'] . $url, true)
			);

			$data['return_id'] = $return_info['return_id'];
			$data['order_id'] = $return_info['order_id'];
			$data['date_ordered'] = date($this->language->get('date_format_short'), strtotime($return_info['date_ordered']));
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($return_info['date_added']));
			$data['firstname'] = $return_info['firstname'];
			$data['lastname'] = $return_info['lastname'];
			$data['email'] = $return_info['email'];
			$data['telephone'] = $return_info['telephone'];
			$data['product'] = $return_info['product'];
			$data['model'] = $return_info['model'];
			$data['quantity'] = $return_info['quantity'];
			$data['reason'] = $return_info['reason'];
			$data['opened'] = $return_info['opened'] ? $this->language->get('text_yes') : $this->language->get('text_no');
			$data['comment'] = nl2br($return_info['comment']);
			$data['action'] = $return_info['action'];

			$data['histories'] = array();

			$results = $this->model_account_return->getReturnHistories($this->request->get['return_id']);

			foreach ($results as $result) {
				$data['histories'][] = array(
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => nl2br($result['comment'])
				);
			}

			$data['continue'] = $this->url->link('account/return', $url, true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/return_info', $data));
		} else {
			$this->document->setTitle($this->language->get('text_return'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/return', '', true)
			);

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_return'),
				'href' => $this->url->link('account/return/info', 'return_id=' . $return_id . $url, true)
			);

			$data['continue'] = $this->url->link('account/return', '', true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	protected function validate() {
		if (!$this->request->post['order_id']) {
			$this->error['order_id'] = $this->language->get('error_order_id');
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ((utf8_strlen($this->request->post['product']) < 1) || (utf8_strlen($this->request->post['product']) > 255)) {
			$this->error['product'] = $this->language->get('error_product');
		}

		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->language->get('error_model');
		}

		if (empty($this->request->post['return_reason_id'])) {
			$this->error['reason'] = $this->language->get('error_reason');
		}

		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('return', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

			if ($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}

		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

		return !$this->error;
	}
}
