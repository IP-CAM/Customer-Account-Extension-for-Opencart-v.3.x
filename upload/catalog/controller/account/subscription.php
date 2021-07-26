<?php
class ControllerAccountSubscription extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/subscription', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/subscription');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/subscription', '', true)
		);

		$url = '';

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/subscription', $url, true)
		);

        $this->load->model('account/subscription');

        $data['mailing_categories'] = array();

        $mailing_categories = $this->model_account_subscription->getMailingCategories();

        foreach ($mailing_categories as $mailing_category) {
            $subs = $this->model_account_subscription->getMailingCategoryCustomer($mailing_category['mailing_category_id']);
            $data['mailing_categories'][] = array(
                'mailing_category_id' => $mailing_category['mailing_category_id'],
                'name'                => $mailing_category['name'],
                'subscriber'          => !empty($subs)
            );
        }

        $data['actionMailings'] = $this->url->link('account/subscription/saveMailings');
        $data['actionPersonalMailings'] = $this->url->link('account/subscription/savePersonalMailings');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/subscription', $data));
	}

	public function subscribe() {
        $this->load->model('account/subscription');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') ) { //&& $this->validateForm()) {
            $this->model_account_subscription->changeSubscriberStatus($this->request->get);
        }
    }

    public function unsubscribeFromAll() {
        $this->load->model('account/subscription');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
            $this->model_account_subscription->unsubscribeFromAll();
        }
    }
}
