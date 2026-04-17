<?php

namespace Database\Seeders;

use App\Enums\BusinessType;
use App\Enums\LinkType;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Tipta',
            'email' => 'admin@tipta.com',
        ]);

        // Manager with Restaurant
        $restaurantManager = User::factory()->manager()->create([
            'name' => 'Restaurant Manager',
            'email' => 'manager@restaurant.com',
        ]);

        $restaurant = Business::factory()->restaurant()->create([
            'user_id' => $restaurantManager->id,
            'name' => 'Mama Ntilie Restaurant',
        ]);

        $foodCategories = ['Breakfast', 'Lunch', 'Dinner', 'Drinks'];
        foreach ($foodCategories as $index => $categoryName) {
            $category = Category::factory()->create([
                'business_id' => $restaurant->id,
                'name' => $categoryName,
                'sort_order' => $index,
            ]);

            Product::factory()->count(3)->create([
                'business_id' => $restaurant->id,
                'category_id' => $category->id,
            ]);
        }

        // Manager with Salon
        $salonManager = User::factory()->manager()->create([
            'name' => 'Salon Manager',
            'email' => 'manager@salon.com',
        ]);

        $salon = Business::factory()->salon()->create([
            'user_id' => $salonManager->id,
            'name' => 'Style Studio Salon',
        ]);

        $serviceCategories = ['Haircuts', 'Coloring', 'Treatments', 'Styling'];
        foreach ($serviceCategories as $index => $categoryName) {
            $category = Category::factory()->create([
                'business_id' => $salon->id,
                'name' => $categoryName,
                'sort_order' => $index,
            ]);

            Product::factory()->count(3)->withDuration()->create([
                'business_id' => $salon->id,
                'category_id' => $category->id,
            ]);
        }

        // Workers
        $waiter = User::factory()->worker()->create([
            'name' => 'John Waiter',
            'email' => 'waiter@tipta.com',
        ]);

        BusinessWorker::factory()->create([
            'business_id' => $restaurant->id,
            'worker_id' => $waiter->id,
            'link_type' => LinkType::Permanent,
        ]);

        $stylist = User::factory()->worker()->create([
            'name' => 'Jane Stylist',
            'email' => 'stylist@tipta.com',
        ]);

        BusinessWorker::factory()->create([
            'business_id' => $salon->id,
            'worker_id' => $stylist->id,
            'link_type' => LinkType::Permanent,
        ]);

        // Unlinked worker
        User::factory()->worker()->create([
            'name' => 'Free Worker',
            'email' => 'worker@tipta.com',
        ]);

        // Sample orders
        $restaurantProducts = $restaurant->products;
        Order::factory()->count(5)->create([
            'business_id' => $restaurant->id,
            'worker_id' => $waiter->id,
        ])->each(function (Order $order) use ($restaurantProducts) {
            $items = $restaurantProducts->random(rand(1, 3));
            foreach ($items as $product) {
                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'unit_price' => $product->price,
                ]);
            }
        });
    }
}
