<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Report;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::count() < 3) {
            User::factory()->count(3)->create();
        }
        $users = User::all();

        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        if (Subcategory::count() === 0) {
            $this->call(SubcategorySeeder::class);
        }

        // Crear 30 informes aleatorios vinculados adecuadamente
        for ($i = 0; $i < 30; $i++) {
            $user = $users->random();
            $category = $categories->random();
            $subcategory = Subcategory::where('category_id', $category->id)->inRandomOrder()->first();
            if (! $subcategory) {
                $subcategory = Subcategory::inRandomOrder()->first();
            }

            $reportData = Report::factory()->make()->toArray();
            $reportData['user_id'] = $user->id;
            $reportData['category_id'] = $category->id;
            $reportData['subcategory_id'] = $subcategory->id;

            Report::create($reportData);
        }
    }
}
