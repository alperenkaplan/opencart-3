<?php
class ControllerExtensionPaymentTwoCheckout extends Controller {
    public function index(): string {
        if (!isset($this->session->data['order_id'])) {
            return false;
        }

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if ($order_info['payment_iso_code_2'] == 'US' || $order_info['payment_iso_code_2'] == 'CA') {
            $data['state'] = $order_info['payment_zone'];
        } else {
            $data['state'] = 'XX';
        }

        if ($this->cart->hasShipping()) {
            $data['ship_street_address'] = $order_info['shipping_address_1'];
            $data['ship_city']           = $order_info['shipping_city'];
            $data['ship_state']          = $order_info['shipping_zone'];
            $data['ship_zip']            = $order_info['shipping_postcode'];
            $data['ship_country']        = $order_info['shipping_country'];
        } else {
            $data['ship_street_address'] = $order_info['payment_address_1'];
            $data['ship_city']           = $order_info['payment_city'];
            $data['ship_state']          = $order_info['payment_zone'];
            $data['ship_zip']            = $order_info['payment_postcode'];
            $data['ship_country']        = $order_info['payment_country'];
        }

        $data['products'] = [];

        $products = $this->cart->getProducts();

        foreach ($products as $product) {
            $data['products'][] = [
                'product_id'  => $product['product_id'],
                'name'        => $product['name'],
                'description' => $product['name'],
                'quantity'    => $product['quantity'],
                'price'       => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'], false)
            ];
        }

        if ($this->config->get('payment_twocheckout_test')) {
            $data['demo'] = 'Y';
        } else {
            $data['demo'] = '';
        }

        if ($this->config->get('payment_twocheckout_display')) {
            $data['display'] = 'Y';
        } else {
            $data['display'] = '';
        }

        $data['lang']             = $this->config->get('config_language');
        $data['return_url']       = $this->url->link('extension/payment/twocheckout/callback', '', true);
        $data['action']           = 'https://www.2checkout.com/checkout/purchase';
        $data['sid']              = $this->config->get('payment_twocheckout_account');
        $data['currency_code']    = $order_info['currency_code'];
        $data['total']            = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        $data['cart_order_id']    = (int)$this->session->data['order_id'];
        $data['card_holder_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $data['street_address']   = $order_info['payment_address_1'];
        $data['city']             = $order_info['payment_city'];
        $data['zip']              = $order_info['payment_postcode'];
        $data['country']          = $order_info['payment_country'];
        $data['email']            = $order_info['email'];
        $data['phone']            = $order_info['telephone'];
        $data['button_confirm']   = $this->language->get('button_confirm');

        return $this->load->view('extension/payment/twocheckout', $data);
    }

    public function callback(): void {
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->request->post['cart_order_id']);

        if (!$this->config->get('payment_twocheckout_test')) {
            $order_number = $this->request->post['order_number'];
        } else {
            $order_number = '1';
        }

        if (strtoupper(md5($this->config->get('payment_twocheckout_secret') . $this->config->get('payment_twocheckout_account') . $order_number . $this->request->post['total'])) == $this->request->post['key']) {
            if ($this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) == $this->request->post['total']) {
                $this->model_checkout_order->addOrderHistory($this->request->post['cart_order_id'], $this->config->get('payment_twocheckout_order_status_id'));
            } else {
                // Ugh. Some one has faked the sum. What should we do? Probably drop an mail to the shop owner?
                $this->model_checkout_order->addOrderHistory($this->request->post['cart_order_id'], $this->config->get('config_order_status_id'));
            }

            // We can't use $this->response->redirect() here, because of 2CO behavior. It fetches this page
            // on behalf of the user and thus user (and his browser) see this as located at 2checkout.com
            // domain. So user's cookies are not here. They might see empty basket and probably other
            // weird things.

            echo '<html>' . "\n";
            echo '<head>' . "\n";
            echo '  <meta http-equiv="Refresh" content="0; url=' . $this->url->link('checkout/success') . '">' . "\n";
            echo '</head>' . "\n";
            echo '<body>' . "\n";
            echo '  <p>Please follow <a href="' . $this->url->link('checkout/success') . '">link</a>!</p>' . "\n";
            echo '</body>' . "\n";
            echo '</html>' . "\n";
            exit();
        } else {
            echo 'The response from 2checkout.com can\'t be parsed. Contact site administrator, please!';
        }
    }
}