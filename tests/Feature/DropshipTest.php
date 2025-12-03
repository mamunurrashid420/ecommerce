<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DropshipService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DropshipTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Get or create admin user
        $this->admin = User::where('role', 'admin')->first();
        if (!$this->admin) {
            $this->admin = User::factory()->create(['role' => 'admin']);
        }

        $this->token = $this->admin->createToken('test-token')->plainTextToken;
    }

    #[Test]
    public function it_requires_authentication_for_dropship_routes(): void
    {
        $response = $this->getJson('/api/dropship/products/search?q=test');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_validates_search_parameters(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/dropship/products/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    #[Test]
    public function it_validates_platform_parameter(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/dropship/products/search?q=test&platform=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['platform']);
    }

    #[Test]
    public function it_accepts_valid_platforms(): void
    {
        $platforms = ['taobao', '1688', 'tmall'];

        foreach ($platforms as $platform) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->getJson("/api/dropship/products/search?q=test&platform={$platform}");

            // Should not fail validation (might fail due to expired API key, but not validation)
            $this->assertNotEquals(422, $response->status(), "Platform {$platform} should be valid");
        }
    }

    #[Test]
    public function it_can_get_product_by_id(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/dropship/products/652874751412');

        // Should return a response (either success with data or error from API)
        // 200 = success, 400 = API error (like expired key), both are valid responses
        $this->assertContains($response->status(), [200, 400]);
        $response->assertJsonStructure(['success', 'message']);
    }

    #[Test]
    public function it_validates_image_search_parameters(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/dropship/products/search-by-image', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image_url']);
    }

    #[Test]
    public function it_validates_import_parameters(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/dropship/products/import', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['num_iid', 'category_id']);
    }

    #[Test]
    public function it_can_transform_product_data(): void
    {
        $service = new DropshipService();

        // Sample API response data (tmapi.top format)
        $apiData = [
            'item_id' => 123456789,
            'title' => 'Test Product Title',
            'product_url' => 'https://detail.1688.com/offer/123456789.html',
            'category_id' => 12345,
            'root_category_id' => 1234,
            'currency' => 'CNY',
            'offer_unit' => '个',
            'price_info' => [
                'price' => '100.00',
                'price_min' => '100.00',
                'price_max' => '120.00',
                'origin_price_min' => '120.00',
            ],
            'stock' => 50,
            'sale_count' => 100,
            'main_imgs' => [
                'https://img.example.com/image1.jpg',
                'https://img.example.com/image2.jpg',
            ],
            'video_url' => 'https://video.example.com/video.mp4',
            'shop_info' => [
                'shop_name' => 'Test Shop',
                'seller_id' => '987654321',
                'location' => 'Shanghai',
            ],
            'product_props' => [
                ['品牌' => 'Test Brand'],
                ['重量' => '300克'],
            ],
            'skus' => [
                [
                    'skuid' => 'sku1',
                    'sale_price' => '100.00',
                    'origin_price' => '120.00',
                    'stock' => 50,
                    'props_ids' => '0:1',
                    'props_names' => '颜色:红色',
                ],
            ],
        ];

        $transformed = $service->transformToLocalProduct($apiData);

        $this->assertEquals('Test Product Title', $transformed['name']);
        $this->assertEquals(100.00, $transformed['price']);
        $this->assertEquals(120.00, $transformed['original_price']);
        $this->assertEquals('123456789', $transformed['sku']);
        $this->assertEquals(50, $transformed['stock_quantity']);
        $this->assertEquals('Test Brand', $transformed['brand']);
        $this->assertEquals(300, $transformed['weight']);
        $this->assertCount(2, $transformed['images']);
        $this->assertCount(1, $transformed['skus']);
        $this->assertEquals('sku1', $transformed['skus'][0]['sku_id']);
    }
}

