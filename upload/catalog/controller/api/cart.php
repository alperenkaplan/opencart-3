<?php
class ControllerApiCart extends Controller {
    public function add(): void {
        $this->load->language('api/cart');

        $json = [];

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->post['product'])) {
                $this->cart->clear();

                foreach ($this->request->post['product'] as $product) {
                    if (isset($product['option'])) {
                        $option = $product['option'];
                    } else {
                        $option = [];
                    }

                    $this->cart->add($product['product_id'], $product['quantity'], $option);
                }

                $json['success'] = $this->language->get('text_success');

                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
            } elseif (isset($this->request->post['product_id'])) {
                $this->load->model('catalog/product');

                $product_info = $this->model_catalog_product->getProduct($this->request->post['product_id']);

                if ($product_info) {
                    if (isset($this->request->post['quantity'])) {
                        $quantity = (int)$this->request->post['quantity'];
                    } else {
                        $quantity = 1;
                    }

                    if (isset($this->request->post['option'])) {
                        $option = array_filter($this->request->post['option']);
                    } else {
                        $option = [];
                    }

                    $product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

                    foreach ($product_options as $product_option) {
                        if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
                            $json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
                        }
                    }

                    if (isset($this->request->post['subscription_plan_id'])) {
                        $subscription_plan_id = (int)$this->request->post['subscription_plan_id'];
                    } else {
                        $subscription_plan_id = 0;
                    }

                    // Validate Subscription plan
                    $subscriptions = $this->model_catalog_product->getSubscriptions($this->request->post['product_id']);

                    if ($subscriptions) {
                        $subscription_plan_ids = [];

                        foreach ($subscriptions as $subscription) {
                            $subscription_plan_ids[]       = $subscription['subscription_plan_id'];
                        }

                        if (!in_array($subscription_plan_id, $subscription_plan_ids)) {
                            $json['error']['subscription'] = $this->language->get('error_subscription');
                        }
                    }

                    if (!$json) {
                        $this->cart->add($this->request->post['product_id'], $quantity, $option, $subscription_plan_id);

                        $json['success'] = $this->language->get('text_success');

                        unset($this->session->data['shipping_method']);
                        unset($this->session->data['shipping_methods']);
                        unset($this->session->data['payment_method']);
                        unset($this->session->data['payment_methods']);
                    }
                } else {
                    $json['error']['store'] = $this->language->get('error_store');
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function edit(): void {
        $this->load->language('api/cart');

        $json = [];

        if (!isset($this->session->data['api_id'])) {
            $json['error']   = $this->language->get('error_permission');
        } else {
            $this->cart->update($this->request->post['key'], $this->request->post['quantity']);

            $json['success'] = $this->language->get('text_success');

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['reward']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function remove(): void {
        $this->load->language('api/cart');

        $json = [];

        if (!isset($this->session->data['api_id'])) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            // Remove
            if (isset($this->request->post['key'])) {
                $this->cart->remove($this->request->post['key']);

                unset($this->session->data['vouchers'][$this->request->post['key']]);

                $json['success'] = $this->language->get('text_success');

                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
                unset($this->session->data['reward']);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function products(): void {
        $this->load->language('api/cart');

        $json = [];

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
        } else {
            // Stock
            if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
                $json['error']['stock'] = $this->language->get('error_stock');
            }

            // Products
            $json['products'] = [];

            $products         = $this->cart->getProducts();

            foreach ($products as $product) {
                $product_total = 0;

                foreach ($products as $product_2) {
                    if ($product_2['product_id'] == $product['product_id']) {
                        $product_total += $product_2['quantity'];
                    }
                }

                if ($product['minimum'] > $product_total) {
                    $json['error']['minimum'][] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
                }

                $option_data = [];

                foreach ($product['option'] as $option) {
                    $option_data[] = [
                        'product_option_id'       => $option['product_option_id'],
                        'product_option_value_id' => $option['product_option_value_id'],
                        'name'                    => $option['name'],
                        'value'                   => $option['value'],
                        'type'                    => $option['type']
                    ];
                }

                // Subscription
                $description = '';

                if ($product['subscription']) {
                    $trial_price     = $this->currency->format($this->tax->calculate($product['subscription']['trial_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    $trial_cycle     = $product['subscription']['trial_cycle'];
                    $trial_frequency = $this->language->get('text_' . $product['subscription']['trial_frequency']);
                    $trial_duration  = $product['subscription']['trial_duration'];

                    if ($product['subscription']['trial_status']) {
                        $description .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
                    }

                    $price     = $this->currency->format($this->tax->calculate($product['subscription']['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    $cycle     = $product['subscription']['cycle'];
                    $frequency = $this->language->get('text_' . $product['subscription']['frequency']);
                    $duration  = $product['subscription']['duration'];

                    if ($duration) {
                        $description .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
                    } else {
                        $description .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
                    }
                }

                $json['products'][] = [
                    'cart_id'      => $product['cart_id'],
                    'product_id'   => $product['product_id'],
                    'name'         => $product['name'],
                    'model'        => $product['model'],
                    'option'       => $option_data,
                    'subscription' => $description,
                    'quantity'     => $product['quantity'],
                    'stock'        => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                    'shipping'     => $product['shipping'],
                    'price'        => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
                    'total'        => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']),
                    'reward'       => $product['reward']
                ];
            }

            // Voucher
            $json['vouchers'] = [];

            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $key => $voucher) {
                    $json['vouchers'][] = [
                        'code'             => $voucher['code'],
                        'description'      => $voucher['description'],
                        'from_name'        => $voucher['from_name'],
                        'from_email'       => $voucher['from_email'],
                        'to_name'          => $voucher['to_name'],
                        'to_email'         => $voucher['to_email'],
                        'voucher_theme_id' => $voucher['voucher_theme_id'],
                        'message'          => $voucher['message'],
                        'price'            => $this->currency->format($voucher['amount'], $this->session->data['currency']),
                        'amount'           => $voucher['amount']
                    ];
                }
            }

            // Totals
            $this->load->model('setting/extension');

            $total  = 0;
            $totals = [];
            $taxes  = $this->cart->getTaxes();

            // Because __call can not keep var references so we put them into an array.
            $total_data = [
                'totals' => &$totals,
                'taxes'  => &$taxes,
                'total'  => &$total
            ];

            $sort_order = [];

            $results    = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get('total_' . $result['code'] . '_status')) {
                    $this->load->model('extension/total/' . $result['code']);

                    // We have to put the totals in an array so that they pass by reference.
                    $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                }
            }

            $sort_order = [];

            foreach ($totals as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $totals);

            $json['totals'] = [];

            foreach ($totals as $total) {
                $json['totals'][] = [
                    'title' => $total['title'],
                    'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}