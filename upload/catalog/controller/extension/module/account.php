<?php
class ControllerExtensionModuleAccount extends Controller {
	public function index() {
		$this->load->language('extension/module/account');

		$data['logged'] = $this->customer->isLogged();
		$data['first_name'] = $this->customer->getFirstName();
		$data['first_letter_name'] = isset($data['first_name']) ? mb_substr($data['first_name'],0,1,'utf-8') : '';
		$data['last_name'] = $this->customer->getLastName();
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);
		$data['account'] = $this->url->link('account/account', '', true);
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist');
		$data['order'] = $this->url->link('account/order', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['reward'] = $this->url->link('account/reward', '', true);
		$data['return'] = $this->url->link('account/return', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);
		$data['recurring'] = $this->url->link('account/recurring', '', true);
		$data['cart'] = $this->url->link('checkout/cart', '', true);
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['review'] = $this->url->link('account/review', '', true);
		$data['statistic'] = $this->url->link('account/statistic', '', true);
		$data['recently_viewed'] = $this->url->link('account/recently_viewed', '', true);
		$data['support'] = $this->url->link('account/support', '', true);
		$data['faq'] = $this->url->link('account/faq', '', true);
		$data['subscriptions'] = $this->url->link('account/subscriptions', '', true);

		return $this->load->view('extension/module/account', $data);
	}
}