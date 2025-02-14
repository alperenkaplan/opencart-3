<?php
class ControllerExtensionModuleAccountSubscription extends Controller {
    // catalog/view/account/recurring_list/after
    public function index(string &$route, array &$args, mixed &$output): void {
        if ($this->config->get('config_information_subscription_id')) {
            $this->load->language('extension/module/account_subscription');

            $this->load->model('catalog/information');
            $this->load->model('account/subscription');

            $args['total_subscriptions'] = $this->model_account_subscription->getTotalSubscriptions();

            $information_info = $this->model_catalog_information->getInformation($this->config->get('config_information_subscription_id'));

            if ($information_info) {
                $args['text_subscription_marketing'] = sprintf($this->language->get('text_subscription_marketing'), $this->url->link('information/information', 'information_id=' . $this->config->get('config_information_subscription_id'), true), $information_info['title']);

                $search  = '<div id="content"';
                $replace = $this->load->view('extension/module/account_subscription', $args);

                $output = str_replace($search, $replace . $search, $output);
            } else {
                $args['text_subscription_marketing'] = '';
            }
        }
    }
}