<?php
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase {
    private $base = 'http://localhost/Supermercado/backend/public/api.php';

    public function testProductsEndpoint() {
        $res = file_get_contents($this->base . '?action=products&page=1&per_page=2');
        $this->assertNotFalse($res, 'No response from products');
        $json = json_decode($res, true);
        $this->assertArrayHasKey('products', $json);
    }

    public function testSearch() {
        $res = file_get_contents($this->base . '?action=products&q=Arroz');
        $this->assertNotFalse($res);
        $json = json_decode($res, true);
        $this->assertArrayHasKey('products', $json);
    }

    public function testProductDetail() {
        $res = file_get_contents($this->base . '?action=products&page=1&per_page=1');
        $json = json_decode($res, true);
        $this->assertNotEmpty($json['products']);
        $id = $json['products'][0]['id'];
        $res2 = file_get_contents($this->base . '?action=product&id=' . $id);
        $j2 = json_decode($res2, true);
        $this->assertArrayHasKey('product', $j2);
    }

    public function testCouponValidate(){
        require_once __DIR__ . '/../backend/src/db.php';
        $code = 'TEST' . time();
        $stmt = $pdo->prepare('INSERT INTO coupons (code,type,value,active,expires_at) VALUES (?,?,?,?,?)');
        $stmt->execute([$code,'fixed',5,1,date('Y-m-d', strtotime('+1 day'))]);
        $res = file_get_contents($this->base . '?action=coupon_validate&code=' . urlencode($code) . '&total=50');
        $j = json_decode($res, true);
        $this->assertArrayHasKey('valid', $j);
        $this->assertTrue($j['valid']);
        $this->assertEquals(5, $j['discount']);
    }
}
