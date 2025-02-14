<?php
class ControllerExtensionModuleDividoCalculator extends Controller {
    public function index(): bool|string {
        if (!isset($this->request->get['product_id']) || !$this->config->get('payment_divido_status') || !$this->config->get('module_divido_calculator_status')) {
            return false;
        }

        $this->load->language('extension/module/divido_calculator');

        $this->load->model('catalog/product');
        $this->load->model('extension/payment/divido');

        $product_selection = $this->config->get('payment_divido_productselection');
        $product_threshold = $this->config->get('payment_divido_price_threshold');

        $product_info      = $this->model_catalog_product->getProduct($this->request->get['product_id']);

        $price             = 0;

        if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
            $base_price = !empty($product_info['special']) ? $product_info['special'] : $product_info['price'];
            $price      = $this->tax->calculate($base_price, $product_info['tax_class_id'], $this->config->get('config_tax'));
        }

        if ($product_selection == 'threshold' && $product_threshold > $price) {
            return false;
        }

        $api_key   = $this->config->get('payment_divido_api_key');
        $key_parts = explode('.', $api_key);
        $js_key    = strtolower(array_shift($key_parts));

        $this->model_extension_payment_divido->setMerchant($api_key);

        $plans     = $this->model_extension_payment_divido->getProductPlans($this->request->get['product_id']);

        if (!$plans) {
            return false;
        }

        $plans_ids  = array_map(function($plan) {
            return $plan->id;
        }, $plans);

        $plans_ids  = array_unique($plans_ids);
        $plans_list = implode(',', $plans_ids);

        $post_data = [
            'merchant_script'          => "//cdn.divido.com/calculator/{$js_key}.js",
            'product_price'            => $price,
            'plan_list'                => $plans_list,
            'generic_credit_req_error' => 'Credit request could not be initiated',
        ];

        return $this->load->view('extension/module/divido_calculator', $post_data);
    }
}
