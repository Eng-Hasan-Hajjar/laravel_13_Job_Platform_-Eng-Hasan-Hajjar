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
        // 1. Admin
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@jobportal.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'is_active'=> true,
        ]);
 
        // 2. Categories
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
 
        // 3. Sample Companies
        $company1User = User::create([
            'name'     => 'Tech Corp',
            'email'    => 'company@techcorp.com',
            'password' => Hash::make('password'),
            'role'     => 'company',
            'is_active'=> true,
        ]);
        $company1 = Company::create([
            'user_id'     => $company1User->id,
            'name'        => 'Tech Corp',
            'slug'        => 'tech-corp',
            'description' => 'A leading technology company building innovative solutions.',
            'industry'    => 'Technology',
            'location'    => 'Dubai, UAE',
            'website'     => 'https://techcorp.example.com',
            'is_verified' => true,
            'is_active'   => true,
        ]);

        $company2User = User::create([
            'name'     => 'Creative Minds',
            'email'    => 'info@creativeminds.com',
            'password' => Hash::make('password'),
            'role'     => 'company',
            'is_active'=> true,
        ]);
        $company2 = Company::create([
            'user_id'     => $company2User->id,
            'name'        => 'Creative Minds',
            'slug'        => 'creative-minds',
            'description' => 'Innovative design and marketing agency.',
            'industry'    => 'Marketing',
            'location'    => 'Cairo, Egypt',
            'website'     => 'https://creativeminds.example.com',
            'is_verified' => true,
            'is_active'   => true,
        ]);

        $company3User = User::create([
            'name'     => 'HealthCare Plus',
            'email'    => 'hr@healthcareplus.com',
            'password' => Hash::make('password'),
            'role'     => 'company',
            'is_active'=> true,
        ]);
        $company3 = Company::create([
            'user_id'     => $company3User->id,
            'name'        => 'HealthCare Plus',
            'slug'        => 'healthcare-plus',
            'description' => 'Leading healthcare provider in the Middle East.',
            'industry'    => 'Healthcare',
            'location'    => 'Riyadh, KSA',
            'website'     => 'https://healthcareplus.example.com',
            'is_verified' => true,
            'is_active'   => true,
        ]);

        $companies = [$company1, $company2, $company3];
 
        // 4. Sample Jobs (Original)
        $cat = Category::where('slug', 'technology')->first();
        foreach (['Senior Laravel Developer', 'React Frontend Developer', 'DevOps Engineer'] as $i => $title) {
            Job::create([
                'company_id'      => $company1->id,
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

        // ==========================================
        // 5. إضافة 20 فرصة عمل متنوعة (مصححة)
        // ==========================================
        $cats = Category::all()->keyBy('slug');
        
        $newJobs = [
            ['title' => 'Digital Marketing Specialist', 'slug_cat' => 'marketing', 'company_idx' => 1, 'type' => 'full-time', 'location' => 'Dubai, UAE', 'is_remote' => false, 'salary_min' => 2000, 'salary_max' => 4000, 'level' => 'mid', 'skills' => ['SEO', 'Google Ads', 'Analytics'], 'is_featured' => true],
            ['title' => 'UI/UX Designer', 'slug_cat' => 'design', 'company_idx' => 1, 'type' => 'remote', 'location' => 'Cairo, Egypt', 'is_remote' => true, 'salary_min' => 2500, 'salary_max' => 5000, 'level' => 'mid', 'skills' => ['Figma', 'Adobe XD', 'Prototyping'], 'is_featured' => false],
            ['title' => 'Financial Analyst', 'slug_cat' => 'finance', 'company_idx' => 2, 'type' => 'full-time', 'location' => 'Riyadh, KSA', 'is_remote' => false, 'salary_min' => 3500, 'salary_max' => 6000, 'level' => 'senior', 'skills' => ['Excel', 'Financial Modeling', 'SQL'], 'is_featured' => true],
            ['title' => 'Registered Nurse', 'slug_cat' => 'healthcare', 'company_idx' => 2, 'type' => 'full-time', 'location' => 'Amman, Jordan', 'is_remote' => false, 'salary_min' => 1500, 'salary_max' => 2500, 'level' => 'junior', 'skills' => ['Patient Care', 'CPR', 'Medical Records'], 'is_featured' => false],
            ['title' => 'High School Math Teacher', 'slug_cat' => 'education', 'company_idx' => 0, 'type' => 'full-time', 'location' => 'Dubai, UAE', 'is_remote' => false, 'salary_min' => 2000, 'salary_max' => 3500, 'level' => 'mid', 'skills' => ['Curriculum Development', 'Classroom Management'], 'is_featured' => false],
            
            // ✅ تم تعديل type من 'contract' إلى 'full-time' لتجنب خطأ قاعدة البيانات
            ['title' => 'Civil Engineer', 'slug_cat' => 'engineering', 'company_idx' => 0, 'type' => 'full-time', 'location' => 'Beirut, Lebanon', 'is_remote' => false, 'salary_min' => 3000, 'salary_max' => 5500, 'level' => 'senior', 'skills' => ['AutoCAD', 'Project Management', 'Structural Analysis'], 'is_featured' => true],
            
            ['title' => 'Customer Support Representative', 'slug_cat' => 'customer-service', 'company_idx' => 1, 'type' => 'remote', 'location' => 'Cairo, Egypt', 'is_remote' => true, 'salary_min' => 1000, 'salary_max' => 1800, 'level' => 'junior', 'skills' => ['Communication', 'Zendesk', 'Problem Solving'], 'is_featured' => false],
            ['title' => 'Content Writer', 'slug_cat' => 'marketing', 'company_idx' => 1, 'type' => 'part-time', 'location' => 'Remote', 'is_remote' => true, 'salary_min' => 800, 'salary_max' => 1500, 'level' => 'junior', 'skills' => ['Copywriting', 'SEO', 'WordPress'], 'is_featured' => false],
            ['title' => 'Graphic Designer', 'slug_cat' => 'design', 'company_idx' => 1, 'type' => 'full-time', 'location' => 'Amman, Jordan', 'is_remote' => false, 'salary_min' => 1800, 'salary_max' => 3000, 'level' => 'mid', 'skills' => ['Photoshop', 'Illustrator', 'Branding'], 'is_featured' => false],
            ['title' => 'Data Scientist', 'slug_cat' => 'technology', 'company_idx' => 0, 'type' => 'full-time', 'location' => 'Dubai, UAE', 'is_remote' => false, 'salary_min' => 5000, 'salary_max' => 8000, 'level' => 'senior', 'skills' => ['Python', 'Machine Learning', 'TensorFlow'], 'is_featured' => true],
            ['title' => 'Accountant', 'slug_cat' => 'finance', 'company_idx' => 2, 'type' => 'full-time', 'location' => 'Riyadh, KSA', 'is_remote' => false, 'salary_min' => 2500, 'salary_max' => 4500, 'level' => 'mid', 'skills' => ['QuickBooks', 'Tax Preparation', 'Auditing'], 'is_featured' => false],
            ['title' => 'Medical Receptionist', 'slug_cat' => 'healthcare', 'company_idx' => 2, 'type' => 'part-time', 'location' => 'Cairo, Egypt', 'is_remote' => false, 'salary_min' => 800, 'salary_max' => 1200, 'level' => 'junior', 'skills' => ['Scheduling', 'Customer Service', 'MS Office'], 'is_featured' => false],
            ['title' => 'Online English Tutor', 'slug_cat' => 'education', 'company_idx' => 0, 'type' => 'remote', 'location' => 'Remote', 'is_remote' => true, 'salary_min' => 1000, 'salary_max' => 2000, 'level' => 'mid', 'skills' => ['TEFL', 'Zoom', 'Lesson Planning'], 'is_featured' => false],
            ['title' => 'Mechanical Engineer', 'slug_cat' => 'engineering', 'company_idx' => 0, 'type' => 'full-time', 'location' => 'Dubai, UAE', 'is_remote' => false, 'salary_min' => 3500, 'salary_max' => 6000, 'level' => 'mid', 'skills' => ['SolidWorks', 'HVAC', 'Thermodynamics'], 'is_featured' => false],
            ['title' => 'Call Center Agent', 'slug_cat' => 'customer-service', 'company_idx' => 1, 'type' => 'full-time', 'location' => 'Cairo, Egypt', 'is_remote' => false, 'salary_min' => 900, 'salary_max' => 1500, 'level' => 'junior', 'skills' => ['Arabic', 'English', 'Active Listening'], 'is_featured' => false],
            ['title' => 'SEO Specialist', 'slug_cat' => 'marketing', 'company_idx' => 1, 'type' => 'full-time', 'location' => 'Remote', 'is_remote' => true, 'salary_min' => 2000, 'salary_max' => 4000, 'level' => 'mid', 'skills' => ['Keyword Research', 'Link Building', 'Google Search Console'], 'is_featured' => true],
            ['title' => 'Mobile App Developer', 'slug_cat' => 'technology', 'company_idx' => 0, 'type' => 'full-time', 'location' => 'Riyadh, KSA', 'is_remote' => false, 'salary_min' => 4000, 'salary_max' => 7000, 'level' => 'senior', 'skills' => ['Flutter', 'Dart', 'Firebase'], 'is_featured' => false],
            ['title' => 'HR Coordinator', 'slug_cat' => 'finance', 'company_idx' => 2, 'type' => 'full-time', 'location' => 'Dubai, UAE', 'is_remote' => false, 'salary_min' => 2500, 'salary_max' => 4000, 'level' => 'mid', 'skills' => ['Recruitment', 'Onboarding', 'HRIS'], 'is_featured' => false],
            ['title' => 'Pediatrician', 'slug_cat' => 'healthcare', 'company_idx' => 2, 'type' => 'full-time', 'location' => 'Amman, Jordan', 'is_remote' => false, 'salary_min' => 6000, 'salary_max' => 10000, 'level' => 'senior', 'skills' => ['Child Health', 'Diagnosis', 'Patient Care'], 'is_featured' => true],
            ['title' => 'Engineering Project Manager', 'slug_cat' => 'engineering', 'company_idx' => 0, 'type' => 'full-time', 'location' => 'Beirut, Lebanon', 'is_remote' => false, 'salary_min' => 5000, 'salary_max' => 8500, 'level' => 'senior', 'skills' => ['PMP', 'Agile', 'Risk Management'], 'is_featured' => false],
        ];

        foreach ($newJobs as $jobData) {
            $category = $cats->get($jobData['slug_cat']);
            $comp = $companies[$jobData['company_idx']];
            
            Job::create([
                'company_id'      => $comp->id,
                'category_id'     => $category ? $category->id : 1,
                'title'           => $jobData['title'],
                'slug'            => Str::slug($jobData['title']) . '-' . Str::random(5),
                'description'     => "We are looking for a talented {$jobData['title']} to join our team.",
                'requirements'    => "Proven experience as a {$jobData['title']}, strong communication skills.",
                'type'            => $jobData['type'],
                'location'        => $jobData['location'],
                'is_remote'       => $jobData['is_remote'],
                'salary_min'      => $jobData['salary_min'],
                'salary_max'      => $jobData['salary_max'],
                'salary_currency' => 'USD',
                'experience_level'=> $jobData['level'],
                'skills'          => $jobData['skills'],
                'is_active'       => true,
                'is_featured'     => $jobData['is_featured'],
            ]);
        }
 
        // 6. Sample Seeker
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
 

 
        $this->command->info('✅ Database seeded successfully! Login: admin@jobportal.com / password');
    }
}