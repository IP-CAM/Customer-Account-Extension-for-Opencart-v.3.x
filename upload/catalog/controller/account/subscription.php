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

        $mailings = $this->model_account_subscription->getSubscriptionOnMailings();
        $personalMailings = $this->model_account_subscription->getSubscriptionOnPersonalMailings();

		$data['mailings'] = array(
		    array(
                'name'    => $this->language->get('text_mailing_news'),
		        'value'   => 'mailing_news',
                'checked' => (isset($mailings['mailing_news']) ? $mailings['mailing_news'] : false),
            ),
            array(
                'name'  => $this->language->get('text_mailing_special'),
                'value' => 'mailing_special',
                'checked' => (isset($mailings['mailing_special']) ? $mailings['mailing_special'] : false),
            ),
            array(
                'name'  => $this->language->get('text_mailing_for_business'),
                'value' => 'mailing_for_business',
                'checked' => (isset($mailings['mailing_for_business']) ? $mailings['mailing_for_business'] : false),
            ),
        );

		$categories = array();

		$results = $this->model_account_subscription->getCategories();

        foreach ($results as $result) {
            $categories[] = array(
                'name'    => $result['name'],
                'value'   => $result['category_id'],
                'checked' => (isset($personalMailings[$result['category_id']]) ? $personalMailings[$result['category_id']] : false),
            );
		}

        $data['personal_mailings'] = array(
            array(
                'name'    => $this->language->get('text_interesting_products_available_now'),
                'value'   => 'interesting_products_available_now',
                'checked' => (isset($personalMailings['interesting_products_available_now']) ? $personalMailings['interesting_products_available_now'] : false),
            ),
            array(
                'name'     => $this->language->get('text_new_products_in_category'),
                'value'    => 'new_products_in_category',
                'checked'  => (isset($personalMailings['new_products_in_category']) ? $personalMailings['new_products_in_category'] : false),
                'children' => $categories
            ),
        );

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

	public function saveMailings() {
        $this->load->model('account/subscription');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') ) { //&& $this->validateForm()) {
            $this->model_account_subscription->saveSubscriptionOnMailings($this->request->post);

            $this->response->redirect($this->url->link('account/subscription'));
        }

        $this->index();
    }

	public function savePersonalMailings() {
        $this->load->model('account/subscription');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') ) { //&& $this->validateForm()) {
            $this->model_account_subscription->saveSubscriptionOnPersonalMailings($this->request->post);

            $this->response->redirect($this->url->link('account/subscription'));
        }

        $this->index();
    }
}
