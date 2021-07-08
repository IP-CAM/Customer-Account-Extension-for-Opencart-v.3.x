<?php
class ControllerAccountRecentlyViewed extends Controller {
	private $error = array();

	public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/recently_viewed', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/recently_viewed');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
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
            'href' => $this->url->link('account/recently_viewed', $url, true)
        );

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

//		$data['recently_viewed'] = array();
//
//		$return_total = $this->model_account_return->getTotalReturns();
//
//		$results = $this->model_account_return->getReturns(($page - 1) * 10, 10);
//
//		foreach ($results as $result) {
//			$data['returns'][] = array(
//				'return_id'  => $result['return_id'],
//				'order_id'   => $result['order_id'],
//				'name'       => $result['firstname'] . ' ' . $result['lastname'],
//				'status'     => $result['status'],
//				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
//				'href'       => $this->url->link('account/return/info', 'return_id=' . $result['return_id'] . $url, true)
//			);
//		}
//
//		$pagination = new Pagination();
//		$pagination->total = $return_total;
//		$pagination->page = $page;
//		$pagination->limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
//		$pagination->url = $this->url->link('account/recently_viewed', 'page={page}', true);
//
//		$data['pagination'] = $pagination->render();
//
//		$data['results'] = sprintf($this->language->get('text_pagination'), ($return_total) ? (($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) + 1 : 0, ((($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) > ($return_total - $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'))) ? $return_total : ((($page - 1) * $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')) + $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')), $return_total, ceil($return_total / $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit')));

        $data['continue'] = $this->url->link('account/account', '', true);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/recently_viewed_list', $data));
    }
}
