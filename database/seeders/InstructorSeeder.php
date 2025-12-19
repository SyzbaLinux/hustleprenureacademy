<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Instructor;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instructors = [
            [
                'name' => 'Dr. Angela Chen',
                'email' => 'angela.chen@hustleprenuer.com',
                'phone_number' => '+263771000001',
                'specialization' => 'Business Strategy & Innovation',
                'bio' => 'PhD in Business Administration with 15 years of experience in strategy consulting and entrepreneurship.',
                'is_active' => true,
            ],
            [
                'name' => 'Marcus Thompson',
                'email' => 'marcus.thompson@hustleprenuer.com',
                'phone_number' => '+263771000002',
                'specialization' => 'Digital Marketing & Social Media',
                'bio' => 'Former CMO of major tech companies, specializing in digital transformation and growth marketing.',
                'is_active' => true,
            ],
            [
                'name' => 'Prof. Sophia Martinez',
                'email' => 'sophia.martinez@hustleprenuer.com',
                'phone_number' => '+263771000003',
                'specialization' => 'Software Development & Programming',
                'bio' => 'Computer Science professor and senior software architect with expertise in full-stack development.',
                'is_active' => true,
            ],
            [
                'name' => 'David Okonkwo',
                'email' => 'david.okonkwo@hustleprenuer.com',
                'phone_number' => '+263771000004',
                'specialization' => 'Finance & Investment',
                'bio' => 'Certified Financial Analyst with 20 years in investment banking and wealth management.',
                'is_active' => true,
            ],
            [
                'name' => 'Rachel Kim',
                'email' => 'rachel.kim@hustleprenuer.com',
                'phone_number' => '+263771000005',
                'specialization' => 'Leadership & Management',
                'bio' => 'Executive coach and leadership consultant working with Fortune 500 companies.',
                'is_active' => true,
            ],
            [
                'name' => 'James Patterson',
                'email' => 'james.patterson@hustleprenuer.com',
                'phone_number' => '+263771000006',
                'specialization' => 'Data Science & Analytics',
                'bio' => 'Data scientist with expertise in machine learning, AI, and business intelligence.',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Fatima Al-Rashid',
                'email' => 'fatima.alrashid@hustleprenuer.com',
                'phone_number' => '+263771000007',
                'specialization' => 'Project Management',
                'bio' => 'PMP-certified project manager with extensive experience in agile and traditional methodologies.',
                'is_active' => true,
            ],
            [
                'name' => 'Carlos Rodriguez',
                'email' => 'carlos.rodriguez@hustleprenuer.com',
                'phone_number' => '+263771000008',
                'specialization' => 'Sales & Negotiation',
                'bio' => 'Sales trainer and negotiation expert with a track record of training top-performing sales teams.',
                'is_active' => true,
            ],
            [
                'name' => 'Linda Mwangi',
                'email' => 'linda.mwangi@hustleprenuer.com',
                'phone_number' => '+263771000009',
                'specialization' => 'Personal Development & Communication',
                'bio' => 'Motivational speaker and communication coach helping professionals unlock their potential.',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Benjamin Clarke',
                'email' => 'benjamin.clarke@hustleprenuer.com',
                'phone_number' => '+263771000010',
                'specialization' => 'Innovation & Technology',
                'bio' => 'Technology futurist and innovation consultant advising on digital transformation strategies.',
                'is_active' => true,
            ],
        ];

        foreach ($instructors as $instructor) {
            Instructor::create($instructor);
        }
    }
}
