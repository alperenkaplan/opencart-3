<?php
class ControllerExtensionPaymentSecureTradingWs extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('extension/payment/securetrading_ws');

        $this->load->model('setting/setting');

        // Geo Zones
        $this->load->model('localisation/geo_zone');

        // Currencies
        $this->load->model('localisation/currency');

        // Order Statuses
        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->request->post['payment_securetrading_ws_site_reference'] = $this->request->post['payment_securetrading_ws_site_reference'];
            $this->request->post['payment_securetrading_ws_username']       = $this->request->post['payment_securetrading_ws_username'];

            $this->model_setting_setting->editSetting('payment_securetrading_ws', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        if (isset($this->request->post['payment_securetrading_ws_site_reference'])) {
            $data['payment_securetrading_ws_site_reference'] = $this->request->post['payment_securetrading_ws_site_reference'];
        } else {
            $data['payment_securetrading_ws_site_reference'] = $this->config->get('payment_securetrading_ws_site_reference');
        }

        if (isset($this->request->post['payment_securetrading_ws_username'])) {
            $data['payment_securetrading_ws_username'] = $this->request->post['payment_securetrading_ws_username'];
        } else {
            $data['payment_securetrading_ws_username'] = $this->config->get('payment_securetrading_ws_username');
        }

        if (isset($this->request->post['payment_securetrading_ws_password'])) {
            $data['payment_securetrading_ws_password'] = $this->request->post['payment_securetrading_ws_password'];
        } else {
            $data['payment_securetrading_ws_password'] = $this->config->get('payment_securetrading_ws_password');
        }

        if (isset($this->request->post['payment_securetrading_ws_csv_username'])) {
            $data['payment_securetrading_ws_csv_username'] = $this->request->post['payment_securetrading_ws_csv_username'];
        } else {
            $data['payment_securetrading_ws_csv_username'] = $this->config->get('payment_securetrading_ws_csv_username');
        }

        if (isset($this->request->post['payment_securetrading_ws_csv_password'])) {
            $data['payment_securetrading_ws_csv_password'] = $this->request->post['payment_securetrading_ws_csv_password'];
        } else {
            $data['payment_securetrading_ws_csv_password'] = $this->config->get('payment_securetrading_ws_csv_password');
        }

        $this->config->set('payment_securetrading_ws_3d_secure', 1);

        if (isset($this->request->post['payment_securetrading_ws_3d_secure'])) {
            $data['payment_securetrading_ws_3d_secure'] = $this->request->post['payment_securetrading_ws_3d_secure'];
        } else {
            $data['payment_securetrading_ws_3d_secure'] = $this->config->get('payment_securetrading_ws_3d_secure');
        }

        if (isset($this->request->post['payment_securetrading_ws_cards_accepted'])) {
            $data['payment_securetrading_ws_cards_accepted'] = $this->request->post['payment_securetrading_ws_cards_accepted'];
        } else {
            $data['payment_securetrading_ws_cards_accepted'] = $this->config->get('payment_securetrading_ws_cards_accepted');

            if ($data['payment_securetrading_ws_cards_accepted'] == null) {
                $data['payment_securetrading_ws_cards_accepted'] = [];
            }
        }

        if (isset($this->request->post['payment_securetrading_ws_order_status_id'])) {
            $data['payment_securetrading_ws_order_status_id'] = (int)$this->request->post['payment_securetrading_ws_order_status_id'];
        } elseif ($this->config->get('payment_securetrading_ws_order_status_id') != '') {
            $data['payment_securetrading_ws_order_status_id'] = $this->config->get('payment_securetrading_ws_order_status_id');
        } else {
            $data['payment_securetrading_ws_order_status_id'] = 1;
        }

        if (isset($this->request->post['payment_securetrading_ws_failed_order_status_id'])) {
            $data['payment_securetrading_ws_failed_order_status_id'] = (int)$this->request->post['payment_securetrading_ws_failed_order_status_id'];
        } elseif ($this->config->get('payment_securetrading_ws_failed_order_status_id') != '') {
            $data['payment_securetrading_ws_failed_order_status_id'] = $this->config->get('payment_securetrading_ws_failed_order_status_id');
        } else {
            $data['payment_securetrading_ws_failed_order_status_id'] = 10;
        }

        if (isset($this->request->post['payment_securetrading_ws_declined_order_status_id'])) {
            $data['payment_securetrading_ws_declined_order_status_id'] = (int)$this->request->post['payment_securetrading_ws_declined_order_status_id'];
        } elseif ($this->config->get('payment_securetrading_ws_declined_order_status_id') != '') {
            $data['payment_securetrading_ws_declined_order_status_id'] = $this->config->get('payment_securetrading_ws_declined_order_status_id');
        } else {
            $data['payment_securetrading_ws_declined_order_status_id'] = 8;
        }

        if (isset($this->request->post['payment_securetrading_ws_refunded_order_status_id'])) {
            $data['payment_securetrading_ws_refunded_order_status_id'] = (int)$this->request->post['payment_securetrading_ws_refunded_order_status_id'];
        } elseif ($this->config->get('payment_securetrading_ws_refunded_order_status_id') != '') {
            $data['payment_securetrading_ws_refunded_order_status_id'] = $this->config->get('payment_securetrading_ws_refunded_order_status_id');
        } else {
            $data['payment_securetrading_ws_refunded_order_status_id'] = 11;
        }

        if (isset($this->request->post['payment_securetrading_ws_authorisation_reversed_order_status_id'])) {
            $data['payment_securetrading_ws_authorisation_reversed_order_status_id'] = (int)$this->request->post['payment_securetrading_ws_authorisation_reversed_order_status_id'];
        } elseif ($this->config->get('payment_securetrading_ws_authorisation_reversed_order_status_id') != '') {
            $data['payment_securetrading_ws_authorisation_reversed_order_status_id'] = $this->config->get('payment_securetrading_ws_authorisation_reversed_order_status_id');
        } else {
            $data['payment_securetrading_ws_authorisation_reversed_order_status_id'] = 12;
        }

        if (isset($this->request->post['payment_securetrading_ws_settle_status'])) {
            $data['payment_securetrading_ws_settle_status'] = $this->request->post['payment_securetrading_ws_settle_status'];
        } else {
            $data['payment_securetrading_ws_settle_status'] = $this->config->get('payment_securetrading_ws_settle_status');
        }

        if (isset($this->request->post['payment_securetrading_ws_settle_due_date'])) {
            $data['payment_securetrading_ws_settle_due_date'] = $this->request->post['payment_securetrading_ws_settle_due_date'];
        } else {
            $data['payment_securetrading_ws_settle_due_date'] = $this->config->get('payment_securetrading_ws_settle_due_date');
        }

        if (isset($this->request->post['payment_securetrading_ws_geo_zone'])) {
            $data['payment_securetrading_ws_geo_zone_id'] = $this->request->post['payment_securetrading_ws_geo_zone_id'];
        } else {
            $data['payment_securetrading_ws_geo_zone_id'] = $this->config->get('payment_securetrading_ws_geo_zone_id');
        }

        if (isset($this->request->post['payment_securetrading_ws_status'])) {
            $data['payment_securetrading_ws_status'] = $this->request->post['payment_securetrading_ws_status'];
        } else {
            $data['payment_securetrading_ws_status'] = $this->config->get('payment_securetrading_ws_status');
        }

        if (isset($this->request->post['payment_securetrading_ws_sort_order'])) {
            $data['payment_securetrading_ws_sort_order'] = $this->request->post['payment_securetrading_ws_sort_order'];
        } else {
            $data['payment_securetrading_ws_sort_order'] = $this->config->get('payment_securetrading_ws_sort_order');
        }

        if (isset($this->request->post['payment_securetrading_ws_total'])) {
            $data['payment_securetrading_ws_total'] = $this->request->post['payment_securetrading_ws_total'];
        } else {
            $data['payment_securetrading_ws_total'] = $this->config->get('payment_securetrading_ws_total');
        }

        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['site_reference'])) {
            $data['error_site_reference'] = $this->error['site_reference'];
        } else {
            $data['error_site_reference'] = '';
        }

        if (isset($this->error['username'])) {
            $data['error_username'] = $this->error['username'];
        } else {
            $data['error_username'] = '';
        }

        if (isset($this->error['password'])) {
            $data['error_password'] = $this->error['password'];
        } else {
            $data['error_password'] = '';
        }

        if (isset($this->error['cards_accepted'])) {
            $data['error_cards_accepted'] = $this->error['cards_accepted'];
        } else {
            $data['error_cards_accepted'] = '';
        }

        $data['breadcrumbs']   = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/securetrading_ws', 'user_token=' . $this->session->data['user_token'], true)
        ];

        // Geo Zones
        $data['geo_zones']      = $this->model_localisation_geo_zone->getGeoZones();

        // Order Statuses
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['cards']               = [
            'AMEX'            => 'American Express',
            'VISA'            => 'Visa',
            'DELTA'           => 'Visa Debit',
            'ELECTRON'        => 'Visa Electron',
            'PURCHASING'      => 'Visa Purchasing',
            'VPAY'            => 'V Pay',
            'MASTERCARD'      => 'MasterCard',
            'MASTERCARDDEBIT' => 'MasterCard Debit',
            'MAESTRO'         => 'Maestro',
            'PAYPAL'          => 'PayPal'
        ];

        $data['settlement_statuses'] = [
            '0'   => $this->language->get('text_pending_settlement'),
            '1'   => $this->language->get('text_pending_settlement_manually_overridden'),
            '2'   => $this->language->get('text_pending_suspended'),
            '100' => $this->language->get('text_pending_settled'),
        ];

        $data['action']      = $this->url->link('extension/payment/securetrading_ws', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel']      = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
        $data['myst_status'] = !empty($data['securetrading_ws_csv_username']) && !empty($data['securetrading_ws_csv_password']);
        $data['hours']       = [];

        for ($i = 0; $i < 24; $i++) {
            $data['hours'][] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        $data['minutes'] = [];

        for ($i = 0; $i < 60; $i++) {
            $data['minutes'][] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        $data['currencies'] = $this->model_localisation_currency->getCurrencies();

        $data['user_token'] = $this->session->data['user_token'];

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/securetrading_ws', $data));
    }

    public function install(): void {
        $this->load->model('extension/payment/securetrading_ws');

        $this->model_extension_payment_securetrading_ws->install();
    }

    public function uninstall(): void {
        $this->load->model('extension/payment/securetrading_ws');

        $this->model_extension_payment_securetrading_ws->uninstall();
    }

    public function downloadTransactions(): void {
        $this->load->language('extension/payment/securetrading_ws');

        $this->load->model('extension/payment/securetrading_ws');

        $csv_data           = $this->request->post;
        $csv_data['detail'] = true;

        $response           = $this->model_extension_payment_securetrading_ws->getCsv($csv_data);

        $this->response->addheader('Content-Type: application/octet-stream');
        $this->response->addheader('Content-Disposition: attachment; filename="' . $this->language->get('text_transactions') . '.csv"');
        $this->response->addheader('Expires: 0');
        $this->response->addheader('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        $this->response->addheader('Pragma: public');
        $this->response->addheader('Content-Length: ' . strlen($response));

        $this->response->setOutput($response);
    }

    public function showTransactions(): string {
        $this->load->language('extension/payment/securetrading_ws');

        $this->load->model('extension/payment/securetrading_ws');

        $csv_data             = $this->request->post;
        $csv_data['detail']   = false;

        $response             = $this->model_extension_payment_securetrading_ws->getCsv($csv_data);

        $data['transactions'] = [];

        $status_mapping        = [
            '0'     => $this->language->get('text_ok'),
            '70000' => $this->language->get('text_denied'),
        ];

        $settle_status_mapping = [
            '0'   => $this->language->get('text_pending_settlement'),
            '1'   => $this->language->get('text_manual_settlement'),
            '2'   => $this->language->get('text_suspended'),
            '3'   => $this->language->get('text_cancelled'),
            '10'  => $this->language->get('text_settling'),
            '100' => $this->language->get('text_settled'),
        ];

        if ($response) {
            $lines = array_filter(explode("\n", $response));
            $csv   = [];
            $keys  = str_getcsv($lines[0]);

            for ($i = 1; $i < count($lines); $i++) {
                $csv[] = array_combine($keys, str_getcsv($lines[$i]));
            }

            foreach ($csv as $row) {
                $data['transactions'][] = [
                    'order_id'              => $row['orderreference'],
                    'order_href'            => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $row['orderreference'], true),
                    'transaction_reference' => $row['transactionreference'],
                    'customer'              => $row['billingfirstname'] . ' ' . $row['billinglastname'],
                    'total'                 => $row['mainamount'],
                    'currency'              => $row['currencyiso3a'],
                    'settle_status'         => $settle_status_mapping[$row['settlestatus']],
                    'status'                => $status_mapping[$row['errorcode']],
                    'type'                  => $row['requesttypedescription'],
                    'payment_type'          => $row['paymenttypedescription'],
                ];
            }
        }

        return $this->load->view('extension/payment/securetrading_ws_transactions', $data);
    }

    public function order(): string {
        if ($this->config->get('payment_securetrading_ws_status')) {
            $this->load->model('extension/payment/securetrading_ws');

            $securetrading_ws_order = $this->model_extension_payment_securetrading_ws->getOrder($this->request->get['order_id']);

            if (!empty($securetrading_ws_order)) {
                $this->load->language('extension/payment/securetrading_ws');

                $securetrading_ws_order['total_released']           = $this->model_extension_payment_securetrading_ws->getTotalReleased($securetrading_ws_order['securetrading_ws_order_id']);
                $securetrading_ws_order['total_formatted']          = $this->currency->format($securetrading_ws_order['total'], $securetrading_ws_order['currency_code'], false, false);
                $securetrading_ws_order['total_released_formatted'] = $this->currency->format($securetrading_ws_order['total_released'], $securetrading_ws_order['currency_code'], false, false);

                $data['securetrading_ws_order'] = $securetrading_ws_order;
                $data['auto_settle']            = $securetrading_ws_order['settle_type'];
                $data['order_id']               = (int)$this->request->get['order_id'];
                $data['user_token']             = $this->session->data['user_token'];

                return $this->load->view('extension/payment/securetrading_ws_order', $data);
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function void(): void {
        $this->load->language('extension/payment/securetrading_ws');

        $json = [];

        if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
            $this->load->model('extension/payment/securetrading_ws');

            $securetrading_ws_order = $this->model_extension_payment_securetrading_ws->getOrder($this->request->post['order_id']);
            $void_response          = $this->model_extension_payment_securetrading_ws->void($this->request->post['order_id']);

            $this->model_extension_payment_securetrading_ws->logger('Void result:\r\n' . print_r($void_response, 1));

            if ($void_response !== false) {
                $response_xml = simplexml_load_string($void_response);

                if ($response_xml->response['type'] == 'ERROR' || (string)$response_xml->response->error->code != '0') {
                    $json['msg']   = (string)$response_xml->response->error->message;
                    $json['error'] = true;
                } else {

                    $this->model_extension_payment_securetrading_ws->addTransaction($securetrading_ws_order['securetrading_ws_order_id'], 'reversed', 0.00);
                    $this->model_extension_payment_securetrading_ws->updateVoidStatus($securetrading_ws_order['securetrading_ws_order_id'], 1);

                    $this->data = [
                        'order_status_id' => $this->config->get('payment_securetrading_ws_authorisation_reversed_order_status_id'),
                        'notify'          => false,
                        'comment'         => '',
                    ];

                    $this->load->model('sale/order');

                    $this->model_sale_order->addOrderHistory($this->request->post['order_id'], $this->data);

                    $json['msg']             = $this->language->get('text_authorisation_reversed');
                    $json['data']['created'] = date('Y-m-d H:i:s');
                    $json['error']           = false;
                }
            } else {
                $json['msg']   = $this->language->get('error_connection');
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
            $json['msg']   = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function release(): void {
        $this->load->language('extension/payment/securetrading_ws');

        $json   = [];
        $amount = number_format($this->request->post['amount'], 2);

        if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '' && isset($amount) && $amount > 0) {
            $this->load->model('extension/payment/securetrading_ws');

            $securetrading_ws_order = $this->model_extension_payment_securetrading_ws->getOrder($this->request->post['order_id']);
            $release_response       = $this->model_extension_payment_securetrading_ws->release($this->request->post['order_id'], $amount);

            $this->model_extension_payment_securetrading_ws->logger('Release result:\r\n' . print_r($release_response, 1));

            if ($release_response !== false) {
                $response_xml = simplexml_load_string($release_response);

                if ($response_xml->response['type'] == 'ERROR' || (string)$response_xml->response->error->code != '0') {
                    $json['error'] = true;
                    $json['msg'] = (string)$response_xml->response->error->message;
                } else {
                    $this->model_extension_payment_securetrading_ws->addTransaction($securetrading_ws_order['securetrading_ws_order_id'], 'payment', $amount);

                    $total_released = $this->model_extension_payment_securetrading_ws->getTotalReleased($securetrading_ws_order['securetrading_ws_order_id']);

                    if ($total_released >= $securetrading_ws_order['total'] || $securetrading_ws_order['settle_type'] == 100) {
                        $this->model_extension_payment_securetrading_ws->updateReleaseStatus($securetrading_ws_order['securetrading_ws_order_id'], 1);

                        $release_status = 1;

                        $json['msg'] = $this->language->get('text_release_ok_order');

                        $this->load->model('sale/order');

                        $history                    = [];
                        $history['order_status_id'] = $this->config->get('securetrading_ws_order_status_success_settled_id');
                        $history['comment']         = '';
                        $history['notify']          = '';

                        $this->model_sale_order->addOrderHistory($this->request->post['order_id'], $history);
                    } else {
                        $release_status = 0;

                        $json['msg']    = $this->language->get('text_release_ok');
                    }

                    $json['data']                   = [];
                    $json['data']['created']        = date('Y-m-d H:i:s');
                    $json['data']['amount']         = $amount;
                    $json['data']['release_status'] = $release_status;
                    $json['data']['total']          = (double)$total_released;

                    $json['error']                  = false;
                }
            } else {
                $json['error'] = true;
                $json['msg']   = $this->language->get('error_connection');
            }
        } else {
            $json['error'] = true;
            $json['msg']   = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function rebate(): void {
        $this->load->language('extension/payment/securetrading_ws');

        $json = [];

        if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
            $this->load->model('extension/payment/securetrading_ws');

            $securetrading_ws_order = $this->model_extension_payment_securetrading_ws->getOrder($this->request->post['order_id']);

            $amount                 = number_format($this->request->post['amount'], 2);
            $rebate_response        = $this->model_extension_payment_securetrading_ws->rebate($this->request->post['order_id'], $amount);

            $this->model_extension_payment_securetrading_ws->logger('Rebate result:\r\n' . print_r($rebate_response, 1));

            if ($rebate_response !== false) {
                $response_xml = simplexml_load_string($rebate_response);
                $error_code   = (string)$response_xml->response->error->code;

                if ($error_code == '0') {
                    $this->model_extension_payment_securetrading_ws->addTransaction($securetrading_ws_order['securetrading_ws_order_id'], 'rebate', $amount * -1);

                    $total_rebated  = $this->model_extension_payment_securetrading_ws->getTotalRebated($securetrading_ws_order['securetrading_ws_order_id']);
                    $total_released = $this->model_extension_payment_securetrading_ws->getTotalReleased($securetrading_ws_order['securetrading_ws_order_id']);

                    if ($total_released <= 0 && $securetrading_ws_order['release_status'] == 1) {
                        $json['status'] = 1;
                        $json['message'] = $this->language->get('text_refund_issued');

                        $this->model_extension_payment_securetrading_ws->updateRebateStatus($securetrading_ws_order['securetrading_ws_order_id'], 1);

                        $rebate_status = 1;
                        $json['msg']   = $this->language->get('text_rebate_ok_order');

                        $this->load->model('sale/order');

                        $history                    = [];
                        $history['order_status_id'] = $this->config->get('payment_securetrading_ws_refunded_order_status_id');
                        $history['comment']         = '';
                        $history['notify']          = '';

                        $this->model_sale_order->addOrderHistory($this->request->post['order_id'], $history);
                    } else {
                        $rebate_status = 0;

                        $json['msg']   = $this->language->get('text_rebate_ok');
                    }

                    $json['data']                   = [];
                    $json['data']['created']        = date('Y-m-d H:i:s');
                    $json['data']['amount']         = $amount * -1;
                    $json['data']['total_released'] = (double)$total_released;
                    $json['data']['total_rebated']  = (double)$total_rebated;
                    $json['data']['rebate_status']  = $rebate_status;

                    $json['error'] = false;
                } else {
                    $json['error'] = true;
                    $json['msg']   = (string)$response_xml->response->error->message;
                }
            } else {
                $json['status']  = 0;
                $json['message'] = $this->language->get('error_connection');
            }
        } else {
            $json['error'] = true;
            $json['msg']   = $this->language->get('error_data_missing');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/securetrading_pp')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_securetrading_ws_site_reference']) {
            $this->error['site_reference'] = $this->language->get('error_site_reference');
        }

        if (!$this->request->post['payment_securetrading_ws_username']) {
            $this->error['username'] = $this->language->get('error_username');
        }

        if (!$this->request->post['payment_securetrading_ws_password']) {
            $this->error['password'] = $this->language->get('error_password');
        }

        if (empty($this->request->post['payment_securetrading_ws_cards_accepted'])) {
            $this->error['cards_accepted'] = $this->language->get('error_cards_accepted');
        }

        return !$this->error;
    }
}