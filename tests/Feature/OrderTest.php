<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Master;
use App\Models\Order;
use App\Models\User;
use App\OrderStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    private function validPayload(City $city, Category $category): array
    {
        return [
            'city_id' => $city->id,
            'category_id' => $category->id,
            'client_name' => 'Aman Jumayev',
            'client_phone' => '+99362111222',
            'description' => 'Кран течёт уже неделю, нужна срочная починка.',
            'client_address' => 'ул. Андалиб, 12',
            'client_lat' => 37.952321,
            'client_lng' => 58.382345,
        ];
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_orders_index_requires_auth(): void
    {
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_orders_index(): void
    {
        $this->actingAsAdmin();
        Order::factory()->count(2)->create();

        $this->get(route('orders.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Orders/Index')->has('orders'));
    }

    public function test_orders_index_can_be_filtered_by_status(): void
    {
        $this->actingAsAdmin();
        Order::factory()->create();
        Order::factory()->completed()->create();

        $this->get(route('orders.index', ['status' => OrderStatus::Completed->value]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('orders.data.0.status', 'completed'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_admin_can_view_order_details(): void
    {
        $this->actingAsAdmin();
        $order = Order::factory()->create();

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Orders/Show')
                ->where('order.id', $order->id)
                ->has('eligibleMasters'));
    }

    public function test_show_returns_404_for_unknown_order(): void
    {
        $this->actingAsAdmin();
        $this->get(route('orders.show', 999))->assertNotFound();
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_admin_can_create_order(): void
    {
        $this->actingAsAdmin();
        $city = City::factory()->create();
        $category = Category::factory()->create();

        $this->post(route('orders.store'), $this->validPayload($city, $category))
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseHas('orders', [
            'client_name' => 'Aman Jumayev',
            'status' => 'pending',
        ]);
    }

    public function test_creating_order_with_photos_stores_them(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $city = City::factory()->create();
        $category = Category::factory()->create();

        $payload = array_merge($this->validPayload($city, $category), [
            'photos' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
            ],
        ]);

        $this->post(route('orders.store'), $payload)->assertRedirect();

        $order = Order::where('client_name', 'Aman Jumayev')->firstOrFail();
        $this->assertCount(2, $order->photos);
    }

    public function test_store_fails_without_required_fields(): void
    {
        $this->actingAsAdmin();
        $this->post(route('orders.store'), [])
            ->assertSessionHasErrors(['city_id', 'category_id', 'client_name', 'client_phone', 'description', 'client_lat', 'client_lng']);
    }

    public function test_store_rejects_more_than_4_photos(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $city = City::factory()->create();
        $category = Category::factory()->create();

        $payload = array_merge($this->validPayload($city, $category), [
            'photos' => array_fill(0, 5, UploadedFile::fake()->image('p.jpg')),
        ]);

        $this->post(route('orders.store'), $payload)->assertSessionHasErrors('photos');
    }

    // ── Assign master ─────────────────────────────────────────────────────────

    public function test_admin_can_assign_eligible_master(): void
    {
        $this->actingAsAdmin();
        $city = City::factory()->create();
        $category = Category::factory()->create();
        $master = Master::factory()->create(['city_id' => $city->id]);
        $master->categories()->sync([$category->id]);
        $order = Order::factory()->create(['city_id' => $city->id, 'category_id' => $category->id]);

        $this->post(route('orders.assign', $order), ['master_id' => $master->id])
            ->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'master_id' => $master->id,
            'status' => 'assigned',
        ]);
    }

    public function test_assigning_inactive_master_fails(): void
    {
        $this->actingAsAdmin();
        $city = City::factory()->create();
        $category = Category::factory()->create();
        $master = Master::factory()->inactive()->create(['city_id' => $city->id]);
        $master->categories()->sync([$category->id]);
        $order = Order::factory()->create(['city_id' => $city->id, 'category_id' => $category->id]);

        $this->post(route('orders.assign', $order), ['master_id' => $master->id])
            ->assertRedirect();

        $this->assertNull($order->fresh()->master_id);
    }

    public function test_assigning_master_from_different_city_fails(): void
    {
        $this->actingAsAdmin();
        $cityA = City::factory()->create();
        $cityB = City::factory()->create();
        $category = Category::factory()->create();
        $master = Master::factory()->create(['city_id' => $cityB->id]);
        $master->categories()->sync([$category->id]);
        $order = Order::factory()->create(['city_id' => $cityA->id, 'category_id' => $category->id]);

        $this->post(route('orders.assign', $order), ['master_id' => $master->id])
            ->assertRedirect();

        $this->assertNull($order->fresh()->master_id);
    }

    public function test_assigning_master_without_matching_category_fails(): void
    {
        $this->actingAsAdmin();
        $city = City::factory()->create();
        $orderCategory = Category::factory()->create();
        $masterCategory = Category::factory()->create();
        $master = Master::factory()->create(['city_id' => $city->id]);
        $master->categories()->sync([$masterCategory->id]);
        $order = Order::factory()->create(['city_id' => $city->id, 'category_id' => $orderCategory->id]);

        $this->post(route('orders.assign', $order), ['master_id' => $master->id])
            ->assertRedirect();

        $this->assertNull($order->fresh()->master_id);
    }

    // ── Set final price ───────────────────────────────────────────────────────

    public function test_admin_can_set_final_price(): void
    {
        $this->actingAsAdmin();
        $order = Order::factory()->create();

        $this->post(route('orders.set-price', $order), ['final_price' => 350.50])
            ->assertRedirect(route('orders.show', $order));

        $this->assertEquals('350.50', $order->fresh()->final_price);
    }

    public function test_setting_price_on_completed_order_fails(): void
    {
        $this->actingAsAdmin();
        $order = Order::factory()->completed()->create();

        $this->post(route('orders.set-price', $order), ['final_price' => 100])
            ->assertRedirect();

        $this->assertEquals('completed', $order->fresh()->status->value);
    }

    // ── Update status ─────────────────────────────────────────────────────────

    public function test_admin_can_transition_assigned_to_in_progress(): void
    {
        $this->actingAsAdmin();
        $order = Order::factory()->assigned()->create();

        $this->post(route('orders.update-status', $order), ['status' => 'in_progress'])
            ->assertRedirect();

        $this->assertEquals('in_progress', $order->fresh()->status->value);
        $this->assertNotNull($order->fresh()->started_at);
    }

    public function test_admin_can_complete_order(): void
    {
        $this->actingAsAdmin();
        $order = Order::factory()->inProgress()->create();

        $this->post(route('orders.update-status', $order), ['status' => 'completed'])
            ->assertRedirect();

        $this->assertEquals('completed', $order->fresh()->status->value);
        $this->assertNotNull($order->fresh()->completed_at);
    }

    public function test_admin_can_cancel_order_with_reason(): void
    {
        $this->actingAsAdmin();
        $order = Order::factory()->create();

        $this->post(route('orders.update-status', $order), [
            'status' => 'cancelled',
            'cancel_reason' => 'Клиент передумал',
        ])->assertRedirect();

        $fresh = $order->fresh();
        $this->assertEquals('cancelled', $fresh->status->value);
        $this->assertEquals('Клиент передумал', $fresh->cancel_reason);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $this->actingAsAdmin();
        $order = Order::factory()->create();

        $this->post(route('orders.update-status', $order), ['status' => 'completed'])
            ->assertRedirect();

        $this->assertEquals('pending', $order->fresh()->status->value);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_admin_can_delete_order(): void
    {
        $this->actingAsAdmin();
        $order = Order::factory()->create();

        $this->delete(route('orders.destroy', $order))
            ->assertRedirect(route('orders.index'));

        $this->assertModelMissing($order);
    }
}
