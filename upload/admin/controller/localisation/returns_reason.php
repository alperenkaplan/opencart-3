<?php
class ControllerLocalisationReturnsReason extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('localisation/returns_reason');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/returns_reason');

        $this->getList();
    }

    public function add(): void {
        $this->load->language('localisation/returns_reason');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/returns_reason');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_localisation_returns_reason->addReturnReason($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url                            = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('localisation/returns_reason', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('localisation/returns_reason');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/returns_reason');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_localisation_returns_reason->editReturnReason($this->request->get['return_reason_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url                            = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('localisation/returns_reason', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('localisation/returns_reason');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/returns_reason');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ((array)$this->request->post['selected'] as $return_reason_id) {
                $this->model_localisation_returns_reason->deleteReturnReason($return_reason_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url                            = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('localisation/returns_reason', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs']   = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('localisation/returns_reason', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        $data['add']            = $this->url->link('localisation/returns_reason/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete']         = $this->url->link('localisation/returns_reason/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['return_reasons'] = [];

        $filter_data            = [
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        ];

        $return_reason_total    = $this->model_localisation_returns_reason->getTotalReturnReasons();

        $results                = $this->model_localisation_returns_reason->getReturnReasons($filter_data);

        foreach ($results as $result) {
            $data['return_reasons'][] = [
                'return_reason_id' => $result['return_reason_id'],
                'name'             => $result['name'],
                'edit'             => $this->url->link('localisation/returns_reason/edit', 'user_token=' . $this->session->data['user_token'] . '&return_reason_id=' . $result['return_reason_id'] . $url, true)
            ];
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = [];
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_name'] = $this->url->link('localisation/returns_reason', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);

        $url               = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination          = new \Pagination();
        $pagination->total   = $return_reason_total;
        $pagination->page    = $page;
        $pagination->limit   = $this->config->get('config_limit_admin');
        $pagination->url     = $this->url->link('localisation/returns_reason', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination']  = $pagination->render();
        $data['results']     = sprintf($this->language->get('text_pagination'), ($return_reason_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($return_reason_total - $this->config->get('config_limit_admin'))) ? $return_reason_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $return_reason_total, ceil($return_reason_total / $this->config->get('config_limit_admin')));

        $data['sort']        = $sort;
        $data['order']       = $order;

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('localisation/returns_reason_list', $data));
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['return_reason_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = [];
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs']   = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('localisation/returns_reason', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        if (!isset($this->request->get['return_reason_id'])) {
            $data['action'] = $this->url->link('localisation/returns_reason/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('localisation/returns_reason/edit', 'user_token=' . $this->session->data['user_token'] . '&return_reason_id=' . $this->request->get['return_reason_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('localisation/returns_reason', 'user_token=' . $this->session->data['user_token'] . $url, true);

        if (isset($this->request->post['return_reason'])) {
            $data['return_reason'] = $this->request->post['return_reason'];
        } elseif (isset($this->request->get['return_reason_id'])) {
            $data['return_reason'] = $this->model_localisation_returns_reason->getReturnReasonDescriptions($this->request->get['return_reason_id']);
        } else {
            $data['return_reason'] = [];
        }

        $this->load->model('localisation/language');

        $data['languages']   = $this->model_localisation_language->getLanguages();

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('localisation/returns_reason_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'localisation/returns_reason')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        foreach ($this->request->post['return_reason'] as $language_id => $value) {
            if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 128)) {
                $this->error['name'][$language_id] = $this->language->get('error_name');
            }
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'localisation/returns_reason')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('sale/returns');

        foreach ((array)$this->request->post['selected'] as $return_reason_id) {
            $return_total = $this->model_sale_returns->getTotalReturnsByReturnReasonId($return_reason_id);

            if ($return_total) {
                $this->error['warning'] = sprintf($this->language->get('error_return'), $return_total);
            }
        }

        return !$this->error;
    }
}