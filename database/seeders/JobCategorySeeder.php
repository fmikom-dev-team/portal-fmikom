<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JobCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            // IT & Software
            'Software Engineering',
            'Web Development',
            'Mobile Development',
            'Data Science & Analytics',
            'Artificial Intelligence / Machine Learning',
            'Cloud Computing & DevOps',
            'Cybersecurity',
            'Database Administration',
            'UI/UX Design',
            'Quality Assurance / Testing',
            'System Administration',
            'IT Support & Helpdesk',
            'Game Development',
            'Embedded Systems / IoT',
            'Blockchain & Web3',

            // Network & Infrastructure
            'Network Engineering',
            'Telecommunications',

            // Creative & Media
            'Graphic Design',
            'Multimedia & Animation',
            'Video Production & Editing',
            'Content Creation',
            'Photography',

            // Digital Marketing & Communication
            'Digital Marketing',
            'Social Media Management',
            'SEO / SEM Specialist',
            'Copywriting',
            'Public Relations',

            // Business & Management
            'Project Management',
            'Product Management',
            'Business Analyst',
            'Management Trainee',
            'Konsultan IT',

            // Education & Research
            'Dosen / Pengajar',
            'Peneliti',
            'Instruktur / Trainer',
            'Lab Assistant',

            // Sales & Customer Service
            'Sales / Business Development',
            'Customer Service',
            'Account Manager',
            'Technical Sales',

            // Finance & Accounting
            'Finance & Accounting',
            'Auditor',

            // Human Resources
            'Human Resources',
            'Recruitment',

            // E-commerce & Startup
            'E-Commerce',
            'Startup',

            // Government & NGO
            'Pemerintahan / ASN',
            'NGO / Organisasi Non-Profit',

            // Freelance & Remote
            'Freelance',
            'Remote Work',

            // Others
            'Lainnya',
        ];

        $data = array_map(fn (string $name) => [
            'nama' => $name,
            'slug' => Str::slug($name),
            'created_at' => $now,
            'updated_at' => $now,
        ], $categories);

        DB::table('job_categories')->upsert($data, ['slug'], ['nama', 'updated_at']);
    }
}
