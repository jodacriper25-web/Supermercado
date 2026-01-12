<?php
use PHPUnit\Framework\TestCase;

class CsvExportTest extends TestCase {
    private $base;

    public function setUp(): void {
        $this->base = getenv('TEST_BASE') ?: 'http://localhost/Supermercado/backend/public/api.php';
    }

    public function testProductsCsvExport(){
        $res = file_get_contents($this->base . '?action=products&format=csv');
        $this->assertNotFalse($res, 'No response from products CSV');
        $this->assertStringContainsString('id,category,name,price,stock,featured', trim(explode("\n", $res)[0]));
    }

    public function testCouponsCsvExportAsAdmin(){
        // Prepare admin account
        require_once __DIR__ . '/../backend/src/db.php';
        $email = 'ci_admin@example.com'; $password = 'secret123';
        // Clean any previous test admin
        $stmt = $pdo->prepare('DELETE FROM users WHERE email = ?'); $stmt->execute([$email]);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)'); $stmt->execute(['CI Admin', $email, $hash, 'admin']);

        // Login as admin and capture session cookie
        $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => json_encode(['email'=>$email,'password'=>$password])]]);
        $res = @file_get_contents($this->base . '?action=admin_login', false, $ctx);
        $this->assertNotFalse($res, 'No response from admin_login');
        // get cookie from $http_response_header
        $cookie = null; foreach($http_response_header as $h){ if (stripos($h,'Set-Cookie:') === 0){ $cookie = trim(substr($h, 11)); break; } }
        $this->assertNotNull($cookie, 'No session cookie returned');

        // Request coupons CSV with cookie
        $ctx2 = stream_context_create(['http' => ['method' => 'GET', 'header' => "Cookie: $cookie\r\n"]]);
        $res2 = @file_get_contents($this->base . '?action=list_coupons&format=csv', false, $ctx2);
        $this->assertNotFalse($res2, 'No response from coupons CSV');
        $this->assertStringContainsString('id,code,type,value,active,expires_at', trim(explode("\n", $res2)[0]));
    }
}
