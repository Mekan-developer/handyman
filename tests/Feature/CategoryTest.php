<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_categories_index_requires_authentication(): void
    {
        $this->get(route('categories.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_categories_index(): void
    {
        $this->actingAsAdmin();
        Category::factory()->count(3)->create();

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Categories/Index')
                ->has('categories')
                ->has('parentCategories')
            );
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_user_can_create_a_root_category(): void
    {
        $this->actingAsAdmin();

        $this->post(route('categories.store'), [
            'name' => 'Электрика',
            'is_active' => true,
            'parent_id' => null,
        ])->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Электрика',
            'parent_id' => null,
            'is_active' => true,
        ]);
    }

    public function test_user_can_create_a_child_category(): void
    {
        $this->actingAsAdmin();
        $parent = Category::factory()->create(['name' => 'Электрика']);

        $this->post(route('categories.store'), [
            'name' => 'Розетки',
            'is_active' => true,
            'parent_id' => $parent->id,
        ])->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Розетки',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_store_fails_when_name_is_missing(): void
    {
        $this->actingAsAdmin();

        $this->post(route('categories.store'), [
            'name' => '',
            'is_active' => true,
            'parent_id' => null,
        ])->assertSessionHasErrors('name');
    }

    public function test_store_fails_when_parent_does_not_exist(): void
    {
        $this->actingAsAdmin();

        $this->post(route('categories.store'), [
            'name' => 'Тест',
            'is_active' => true,
            'parent_id' => 9999,
        ])->assertSessionHasErrors('parent_id');
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_user_can_update_a_category(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create(['name' => 'Старое']);

        $this->put(route('categories.update', $category), [
            'name' => 'Новое',
            'is_active' => false,
            'parent_id' => null,
        ])->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Новое',
            'is_active' => false,
        ]);
    }

    public function test_user_can_assign_parent_on_update(): void
    {
        $this->actingAsAdmin();
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => null]);

        $this->put(route('categories.update', $child), [
            'name' => $child->name,
            'is_active' => true,
            'parent_id' => $parent->id,
        ])->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_update_fails_when_name_is_missing(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $this->put(route('categories.update', $category), [
            'name' => '',
            'is_active' => true,
            'parent_id' => null,
        ])->assertSessionHasErrors('name');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_user_can_delete_a_category(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $this->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertModelMissing($category);
    }

    public function test_deleting_parent_nullifies_children_parent_id(): void
    {
        $this->actingAsAdmin();
        $parent = Category::factory()->create();
        $child = Category::factory()->child($parent)->create();

        $this->delete(route('categories.destroy', $parent))
            ->assertRedirect(route('categories.index'));

        $this->assertModelMissing($parent);
        $this->assertDatabaseHas('categories', [
            'id' => $child->id,
            'parent_id' => null,
        ]);
    }

    public function test_deleting_nonexistent_category_returns_404(): void
    {
        $this->actingAsAdmin();

        $this->delete(route('categories.destroy', 9999))->assertNotFound();
    }
}
