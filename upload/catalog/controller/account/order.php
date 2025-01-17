<?php
class ControllerAccountOrder extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/order');

		$this->document->setTitle($this->language->get('heading_title'));
		
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

        if (isset($this->request->get['year'])) {
            $url .= '&year=' . $this->request->get['year'];
        }
		
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
			'href' => $this->url->link('account/order', $url, true)
		);

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
            $order = 'DESC';
        }

        if (isset($this->request->get['year'])) {
            $selected_year = $this->request->get['year'];
        } else {
            $selected_year = date("Y");
        }

        $data['selected_year'] = $selected_year;
        $data['link_year'] = $this->url->link('account/order', $url, true);

		$data['orders'] = array();

		$this->load->model('account/order');

		$order_total = $this->model_account_order->getTotalOrders($selected_year);

        $data['count_orders'] = $this->right_word_ends($order_total, $titles = array(/*1*/$this->language->get('text_count_orders_1'), /*2*/$this->language->get('text_count_orders_2'), /*5*/$this->language->get('text_count_orders_5')));

        $years = $this->model_account_order->getOrdersYears();

        $data['years'] = array();

        foreach ($years as $year) {
            if (!isset($data['years'][date("Y", strtotime($year['date_added']))])) {
                $data['years'][date("Y", strtotime($year['date_added']))] = date("Y", strtotime($year['date_added']));
            }
        }

        $filter_data = array(
            'year'               => $selected_year,
            'sort'               => $sort,
            'order'              => $order,
            'start'              => ($page - 1) * 10,
            'limit'              => 10
        );

		$results = $this->model_account_order->getOrders($filter_data);

        $this->load->model('tool/image');

		foreach ($results as $result) {
		    $order_reward = 0;
			$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
			$products = $this->model_account_order->getOrderProducts($result['order_id']);

			$order_histories = $this->model_account_order->getOrderHistories($result['order_id']);

			foreach ($products as $k => $product) {
			    $product_info = $this->model_account_order->getProductInfo($product['product_id']);

                if (!empty($product_info)) {
                    $products[$k] = array_merge($products[$k], $product_info);
                }

			    if ($product_info['image']) {
                    $products[$k]['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100); ;
                } else {
                    $products[$k]['thumb'] = $this->model_tool_image->resize('placeholder.png', 100, 100);;
                }

                $order_reward += (int)$product['reward'];
            }

			$voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

			$order_info = $this->model_account_order->getOrder($result['order_id']);

			$data['orders'][] = array(
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'date_added' => date('d', strtotime($result['date_added'])) . ' ' . $this->language->get(date('M', strtotime($result['date_added']))) . ' ' . date('Y', strtotime($result['date_added'])),
				'products'   => $products,
				'histories'  => $order_histories,
				'shipping_method' => $order_info['shipping_method'],
				'payment_method' => $order_info['payment_method'],
				'delivery_rating' => $result['delivery_rating'],
				'reward'     => $this->currency->format($order_reward, $result['currency_code'], $result['currency_value']),
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'view'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], true),
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
            'value' => 'o.date_added-ASC',
            'href'  => $this->url->link('account/order', '&sort=o.date_added&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_date_added_desc'),
            'value' => 'o.date_added-DESC',
            'href'  => $this->url->link('account/order', '&sort=o.date_added&order=DESC' . $url)
        );

        $data['sort'] = $sort;
        $data['order'] = $order;

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/order', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($order_total - 10)) ? $order_total : ((($page - 1) * 10) + 10), $order_total, ceil($order_total / 10));

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/order_list', $data));
	}

	public function info() {
		$this->load->language('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			$this->document->setTitle($this->language->get('text_order'));

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

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
				'href' => $this->url->link('account/order', $url, true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_order'),
				'href' => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, true)
			);

			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = '';
			}

			$data['order_id'] = $this->request->get['order_id'];
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			$data['text_order_info_heading'] = sprintf($this->language->get('text_order_info_heading'), date('d', strtotime($order_info['date_added'])) . ' ' . $this->language->get(date('M', strtotime($order_info['date_added']))) . ' ' . date('Y', strtotime($order_info['date_added'])), $order_info['order_id']);

			$data['recipient'] = $order_info['lastname'] . ' ' . $order_info['firstname'] . ', ' . $order_info['telephone'];

			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

			$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['payment_method'] = $order_info['payment_method'];

			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['shipping_method'] = $order_info['shipping_method'];

			$this->load->model('catalog/product');
			$this->load->model('tool/upload');

			// Products
			$data['products'] = array();

			$products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);

			$data['products_count'] = 0;
			$data['products_weight_total'] = 0;
			$data['order_reward'] = 0;

			foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$product_info = $this->model_account_order->getProductInfo($product['product_id']);


				$order_product_info = $this->model_account_order->getOrderProductByProductId($this->request->get['order_id'], $product['product_id']);

                $data['products_weight_total'] += ($product_info['weight'] / $product_info['multiply']) * $order_product_info['quantity'];

				if ($product_info) {
					$reorder = $this->url->link('account/order/reorder', 'order_id=' . $order_id . '&order_product_id=' . $product['order_product_id'], true);
				} else {
					$reorder = '';
				}

				$this->load->model('tool/image');

				$data['products'][] = array(
					'product_id'=> $product['product_id'],
					'name'     => $product['name'],
					'model'    => $product['model'],
					'option'   => $option_data,
					'quantity' => $product['quantity'],
					'weight'   => (int)$product_info['weight'],
					'weight_unit' => $product_info['weight_unit'],
					'thumb'    => $product_info['image'] ? $this->model_tool_image->resize($product_info['image'], 100, 100) : $this->model_tool_image->resize('placeholder.png', 100, 100),
					'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'reorder'  => $reorder,
					'return'   => $this->url->link('account/return/add', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], true)
				);

                $data['order_reward'] += $product['reward'];

                $data['products_count']++;
			}

			// Voucher
			$data['vouchers'] = array();

			$vouchers = $this->model_account_order->getOrderVouchers($this->request->get['order_id']);

			foreach ($vouchers as $voucher) {
				$data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			// Totals
			$data['totals'] = array();

			$totals = $this->model_account_order->getOrderTotals($this->request->get['order_id']);

			foreach ($totals as $total) {
				$data['totals'][$total['code']] = array(
				    'code'  => $total['code'],
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
				);
			}

			$data['comment'] = nl2br($order_info['comment']);

			// History
			$data['histories'] = array();

			$results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);

			foreach ($results as $result) {
				$data['histories'][] = array(
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => $result['notify'] ? nl2br($result['comment']) : ''
				);
			}

			$data['actionReturn'] = $this->url->link('account/order/addReturn', 'order_id=' . $order_id, true);

            $this->load->model('localisation/return_reason');

            $data['return_reasons'] = $this->model_localisation_return_reason->getReturnReasons();

            $data['continue'] = $this->url->link('account/order', '', true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/order_info', $data));
		} else {
			return new Action('error/not_found');
		}
	}

	public function reorder() {
		$this->load->language('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			if (isset($this->request->get['order_product_id'])) {
				$order_product_id = $this->request->get['order_product_id'];
			} else {
				$order_product_id = 0;
			}

			$order_product_info = $this->model_account_order->getOrderProduct($order_id, $order_product_id);

			if ($order_product_info) {
				$this->load->model('catalog/product');

				$product_info = $this->model_catalog_product->getProduct($order_product_info['product_id']);

				if ($product_info) {
					$option_data = array();

					$order_options = $this->model_account_order->getOrderOptions($order_product_info['order_id'], $order_product_id);

					foreach ($order_options as $order_option) {
						if ($order_option['type'] == 'select' || $order_option['type'] == 'radio' || $order_option['type'] == 'image') {
							$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'checkbox') {
							$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'text' || $order_option['type'] == 'textarea' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
							$option_data[$order_option['product_option_id']] = $order_option['value'];
						} elseif ($order_option['type'] == 'file') {
							$option_data[$order_option['product_option_id']] = $this->encryption->encrypt($this->config->get('config_encryption'), $order_option['value']);
						}
					}

					$this->cart->add($order_product_info['product_id'], $order_product_info['quantity'], $option_data);

					$this->session->data['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $product_info['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);
					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				} else {
					$this->session->data['error'] = sprintf($this->language->get('error_reorder'), $order_product_info['name']);
				}
			}
		}

		$this->response->redirect($this->url->link('account/order/info', 'order_id=' . $order_id));
	}

	public function addDeliveryReview() {
        $this->load->language('account/order');
        $this->load->model('account/order');

        $json = array();

        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $post = $this->request->post;

            if (!isset($post['options'])) {
                $post['options'] = '';
            }

            $upload_dir_path = 'image/uploads';
            $upload_dir = 'uploads';

            if( ! is_dir( $upload_dir_path ) ) mkdir( $upload_dir_path, 0777 );

            $files      = $this->request->files; // полученные файлы

            $done_files = array();

            // переместим файлы из временной директории в указанную
            foreach( $files as $file ) {
                $files_name = $file['name'];

                foreach ($files_name as $key => $file_name) {
                    // Получаем расширение файла
                    $getMime = explode('.', $file_name);
                    $mime = end($getMime);

                    $randomName = substr(str_shuffle($permitted_chars), 0, 10) . '.' . $mime;

                    if( move_uploaded_file( $file['tmp_name'][$key], "$upload_dir_path/$randomName" ) ) {
                        $done_files[] =  "$upload_dir/$randomName";
                    }
                }

            }

            $post['images'] = json_encode(array_values($done_files));

            $this->model_account_order->addOrderReview($post);
        }
    }

	public function getOrderInfo() {
        $this->load->language('account/order');
        $this->load->model('account/order');

        $json = array();

        if (isset($this->request->get['order_id']) && ($this->request->server['REQUEST_METHOD'] == 'GET')) {
            $order_info = $this->model_account_order->getOrderInfo($this->request->get['order_id']);

            $json = array(
                'order_id'   => $order_info['order_id'],
                'status'     => $order_info['status'],
                'date_added' => date('d', strtotime($order_info['date_added'])) . ' ' . $this->language->get(date('M', strtotime($order_info['date_added']))) . ' ' . date('Y', strtotime($order_info['date_added'])),
                'total'      => $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']),
            );

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    }

	public function getOrderAndProductInfo() {
        $this->load->language('account/order');
        $this->load->model('account/order');
        $this->load->model('tool/image');

        $json = array();

        if (isset($this->request->get['order_id']) && isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] == 'GET')) {
            $order_info = $this->model_account_order->getOrderInfo($this->request->get['order_id']);
            $product_info = $this->model_account_order->getProductInfo($this->request->get['product_id']);

            $json = array(
                'order_id'   => $order_info['order_id'],
                'status'     => $order_info['status'],
                'date_added' => date('d', strtotime($order_info['date_added'])) . ' ' . $this->language->get(date('M', strtotime($order_info['date_added']))) . ' ' . date('Y', strtotime($order_info['date_added'])),
                'total'      => $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']),
                'product_name'        => $product_info['name'],
                'product_weight'      => (int)$product_info['weight'],
                'product_weight_unit' => $product_info['weight_unit'],
                'product_price'       => $this->currency->format($product_info['price'], $order_info['currency_code'], $order_info['currency_value']),
                'product_thumb'       => $product_info['image'] ? $this->model_tool_image->resize($product_info['image'], 100, 100) : $this->model_tool_image->resize('placeholder.png', 100, 100),
            );

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function addReturn() {
        $this->load->model('account/return');
        $this->load->model('account/order');
        $this->load->model('account/customer');

        $return = array();

        if (isset($this->request->post['order_id']) && isset($this->request->post['product_id']) && ($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $order_info = $this->model_account_order->getOrder($this->request->post['order_id']);
            $product_info = $this->model_account_order->getProductInfo($this->request->post['product_id']);

            $return = array(
                'order_id'   => $order_info['order_id'],
                'product_id'   => $this->request->post['product_id'],
                'firstname'  => $order_info['firstname'],
                'lastname'   => $order_info['lastname'],
                'email'      => $order_info['email'],
                'telephone'  => $order_info['order_id'],
                'product'    => $product_info['name'],
                'model'      => isset($product_info['model']) ? $product_info['model'] : '',
                'quantity'   => 1,
                'comment'    => $this->request->post['comment'],
                'opened'     => 0,
                'return_reason_id' => $this->request->post['return_reason_id'],
                'date_ordered' => $order_info['date_added'],
            );

            $this->model_account_return->addReturn($return);

            $this->response->redirect($this->url->link('account/order/info', 'order_id=' . $this->request->post['order_id'], true));
        }
    }

    protected function right_word_ends($number, $titles) {
        $cases = array (2, 0, 1, 1, 1, 2);

        return sprintf($titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]], $number);
    }
}