<?php
class ControllerExtensionModuleInformation extends Controller {
    public function index(): string {
        $this->load->language('extension/module/information');

        $this->load->model('catalog/information');

        $data['informations'] = [];

        foreach ($this->model_catalog_information->getInformations() as $result) {
            $data['informations'][] = [
                'title' => $result['title'],
                'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
            ];
        }

        $data['contact'] = $this->url->link('information/contact');
        $data['sitemap'] = $this->url->link('information/sitemap');

        return $this->load->view('extension/module/information', $data);
    }
}