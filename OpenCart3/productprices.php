<?php
class ControllerCommonProductPrices extends Controller {
    public function index() {
        $this->load->model('catalog/product');
        
        $products_data = array();
        $product_ids = array(587, 528);
        
        foreach ($product_ids as $product_id) {
            $product_info = $this->model_catalog_product->getProduct($product_id);
            if ($product_info) {
                // Получаем информацию о скидках
                $discount_price = $this->model_catalog_product->getProductDiscounts($product_id);
                
                // Определяем цену по акции
                $special_price = "0р.";
                $special_price_raw = "0";
                
                // Проверяем есть ли специальная цена
                if ((float)$product_info['special']) {
                    $special_price = $this->currency->format($product_info['special'], $this->session->data['currency']);
                    $special_price_raw = $product_info['special'];
                } 
                // Или проверяем скидки
                elseif ($discount_price) {
                    $special_price = $this->currency->format($discount_price[0]['price'], $this->session->data['currency']);
                    $special_price_raw = $discount_price[0]['price'];
                }
                
                $products_data[$product_id] = array(
                    'name'  => $product_info['name'],
                    'price' => $this->currency->format($product_info['price'], $this->session->data['currency']),
                    'model' => $product_info['model'],
                    'price_raw' => $product_info['price'],
                    'special_price' => $special_price,
                    'special_price_raw' => $special_price_raw
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
