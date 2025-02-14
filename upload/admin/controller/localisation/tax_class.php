<?php
class ControllerLocalisationTaxClass extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('localisation/tax_class');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_class');

        $this->getList();
    }

    public function add(): void {
        $this->load->language('localisation/tax_class');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_class');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_localisation_tax_class->addTaxClass($this->request->post);

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

            $this->response->redirect($this->url->link('localisation/tax_class', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('localisation/tax_class');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_class');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_localisation_tax_class->editTaxClass($this->request->get['tax_class_id'], $this->request->post);

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

            $this->response->redirect($this->url->link('localisation/tax_class', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('localisation/tax_class');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_class');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ((array)$this->request->post['selected'] as $tax_class_id) {
                $this->model_localisation_tax_class->deleteTaxClass($tax_class_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

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

            $this->response->redirect($this->url->link('localisation/tax_class', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'title';
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
            'href' => $this->url->link('localisation/tax_class', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        $data['add']         = $this->url->link('localisation/tax_class/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete']      = $this->url->link('localisation/tax_class/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['tax_classes'] = [];

        $filter_data         = [
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        ];

        $tax_class_total     = $this->model_localisation_tax_class->getTotalTaxClasses();

        $results             = $this->model_localisation_tax_class->getTaxClasses($filter_data);

        foreach ($results as $result) {
            $data['tax_classes'][] = [
                'tax_class_id' => $result['tax_class_id'],
                'title'        => $result['title'],
                'edit'         => $this->url->link('localisation/tax_class/edit', 'user_token=' . $this->session->data['user_token'] . '&tax_class_id=' . $result['tax_class_id'] . $url, true)
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

        $data['sort_title'] = $this->url->link('localisation/tax_class', 'user_token=' . $this->session->data['user_token'] . '&sort=title' . $url, true);

        $url                = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination          = new \Pagination();
        $pagination->total   = $tax_class_total;
        $pagination->page    = $page;
        $pagination->limit   = $this->config->get('config_limit_admin');
        $pagination->url     = $this->url->link('localisation/tax_class', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination']  = $pagination->render();
        $data['results']     = sprintf($this->language->get('text_pagination'), ($tax_class_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($tax_class_total - $this->config->get('config_limit_admin'))) ? $tax_class_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $tax_class_total, ceil($tax_class_total / $this->config->get('config_limit_admin')));

        $data['sort']        = $sort;
        $data['order']       = $order;

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('localisation/tax_class_list', $data));
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['tax_class_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['title'])) {
            $data['error_title'] = $this->error['title'];
        } else {
            $data['error_title'] = '';
        }

        if (isset($this->error['description'])) {
            $data['error_description'] = $this->error['description'];
        } else {
            $data['error_description'] = '';
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
            'href' => $this->url->link('localisation/tax_class', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        if (!isset($this->request->get['tax_class_id'])) {
            $data['action'] = $this->url->link('localisation/tax_class/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('localisation/tax_class/edit', 'user_token=' . $this->session->data['user_token'] . '&tax_class_id=' . $this->request->get['tax_class_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('localisation/tax_class', 'user_token=' . $this->session->data['user_token'] . $url, true);

        if (isset($this->request->get['tax_class_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $tax_class_info = $this->model_localisation_tax_class->getTaxClass($this->request->get['tax_class_id']);
        }

        if (isset($this->request->post['title'])) {
            $data['title'] = $this->request->post['title'];
        } elseif (!empty($tax_class_info)) {
            $data['title'] = $tax_class_info['title'];
        } else {
            $data['title'] = '';
        }

        if (isset($this->request->post['description'])) {
            $data['description'] = $this->request->post['description'];
        } elseif (!empty($tax_class_info)) {
            $data['description'] = $tax_class_info['description'];
        } else {
            $data['description'] = '';
        }

        if (isset($this->request->post['tax_rule'])) {
            $data['tax_rules'] = $this->request->post['tax_rule'];
        } elseif (isset($this->request->get['tax_class_id'])) {
            $data['tax_rules'] = $this->model_localisation_tax_class->getTaxRules($this->request->get['tax_class_id']);
        } else {
            $data['tax_rules'] = [];
        }

        $this->load->model('localisation/tax_rate');

        $data['tax_rates']   = $this->model_localisation_tax_rate->getTaxRates();

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('localisation/tax_class_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'localisation/tax_class')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((strlen($this->request->post['title']) < 3) || (strlen($this->request->post['title']) > 32)) {
            $this->error['title'] = $this->language->get('error_title');
        }

        if ((strlen($this->request->post['description']) < 3) || (strlen($this->request->post['description']) > 255)) {
            $this->error['description'] = $this->language->get('error_description');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'localisation/tax_class')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('catalog/product');

        foreach ((array)$this->request->post['selected'] as $tax_class_id) {
            $product_total = $this->model_catalog_product->getTotalProductsByTaxClassId($tax_class_id);

            if ($product_total) {
                $this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);
            }
        }

        return !$this->error;
    }
}