<?php
class ControllerCommonProductPrices extends Controller {
    public function index() {
        $this->load->model('catalog/product');
        
        $products_data = array();
        $product_ids = array(587, 528);
        
        foreach ($product_ids as $product_id) {
            $product_info = $this->model_catalog_product->getProduct($product_id);
            if ($product_info) {
                $products_data[$product_id] = array(
                    'name'  => $product_info['name'],
                    'price' => $this->currency->format($product_info['price'], $this->session->data['currency']),
                    'model' => $product_info['model'],
                    'price_raw' => $product_info['price']
                );
            }
        }
        
        // Конвертируем данные в UTF-8 если нужно
        $products_data = $this->convertToUtf8($products_data);
        
        $this->response->addHeader('Content-Type: application/javascript; charset=utf-8');
        
        $json_products = json_encode($products_data, JSON_UNESCAPED_UNICODE);
        $js_output = "var productPrices = " . $json_products . ";";
        
        $this->response->setOutput($js_output);
    }
    
    // Функция для конвертации в UTF-8
    private function convertToUtf8($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->convertToUtf8($value);
            }
        } elseif (is_string($data)) {
            // Пробуем определить кодировку и конвертировать в UTF-8
            if (function_exists('mb_detect_encoding')) {
                $encoding = mb_detect_encoding($data, 'UTF-8, Windows-1251, ISO-8859-1', true);
                if ($encoding != 'UTF-8' && $encoding !== false) {
                    $data = iconv($encoding, 'UTF-8//IGNORE', $data);
                }
            }
        }
        return $data;
    }
}
