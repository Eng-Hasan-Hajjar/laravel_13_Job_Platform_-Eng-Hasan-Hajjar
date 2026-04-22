<?php

// ==========================================
// database/seeders/DatabaseSeeder.php
// ==========================================
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\{User, Company, Job, Category};
use Illuminate\Support\{Str, Facades\Hash};
 
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@jobportal.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'is_active'=> true,
        ]);
 
        // Categories
        $categories = [
            ['name' => 'Technology',        'icon' => '💻', 'slug' => 'technology'],
            ['name' => 'Marketing',         'icon' => '📣', 'slug' => 'marketing'],
            ['name' => 'Design',            'icon' => '🎨', 'slug' => 'design'],
            ['name' => 'Finance',           'icon' => '💰', 'slug' => 'finance'],
            ['name' => 'Healthcare',        'icon' => '🏥', 'slug' => 'healthcare'],
            ['name' => 'Education',         'icon' => '📚', 'slug' => 'education'],
            ['name' => 'Engineering',       'icon' => '⚙️', 'slug' => 'engineering'],
            ['name' => 'Customer Service',  'icon' => '🤝', 'slug' => 'customer-service'],
        ];
 
        foreach ($categories as $cat) {
            Category::create($cat + ['is_active' => true]);
        }
 
        // Sample Company
        $companyUser = User::create([
            'name'     => 'Tech Corp',
            'email'    => 'company@techcorp.com',
            'password' => Hash::make('password'),
            'role'     => 'company',
            'is_active'=> true,
        ]);
 
        $company = Company::create([
            'user_id'     => $companyUser->id,
            'name'        => 'Tech Corp',
            'slug'        => 'tech-corp',
            'description' => 'A leading technology company building innovative solutions.',
            'industry'    => 'Technology',
            'location'    => 'Dubai, UAE',
            'website'     => 'https://techcorp.example.com',
            'is_verified' => true,
            'is_active'   => true,
        ]);
 
        // Sample Jobs
        $cat = Category::where('slug', 'technology')->first();
        foreach (['Senior Laravel Developer', 'React Frontend Developer', 'DevOps Engineer'] as $i => $title) {
            Job::create([
                'company_id'      => $company->id,
                'category_id'     => $cat->id,
                'title'           => $title,
                'slug'            => Str::slug($title) . '-' . Str::random(5),
                'description'     => "We are looking for an experienced {$title} to join our growing team.",
                'requirements'    => "3+ years experience, strong communication skills.",
                'type'            => $i === 2 ? 'remote' : 'full-time',
                'location'        => 'Dubai, UAE',
                'is_remote'       => $i === 2,
                'salary_min'      => 3000 + ($i * 1000),
                'salary_max'      => 6000 + ($i * 1000),
                'salary_currency' => 'USD',
                'experience_level'=> 'mid',
                'skills'          => ['PHP', 'Laravel', 'MySQL', 'Git'],
                'is_active'       => true,
                'is_featured'     => $i === 0,
            ]);
        }
 
        // Sample Seeker
        User::create([
            'name'             => 'Ahmed Ali',
            'email'            => 'user@example.com',
            'password'         => Hash::make('password'),
            'role'             => 'user',
            'bio'              => 'Passionate web developer with 3 years of experience.',
            'location'         => 'Cairo, Egypt',
            'experience_level' => 'mid',
            'skills'           => ['PHP', 'Laravel', 'JavaScript', 'React', 'MySQL'],
            'is_active'        => true,
        ]);
 
        $this->command->info('✅ Database seeded! Login: admin@jobportal.com / password');
    }
}
 