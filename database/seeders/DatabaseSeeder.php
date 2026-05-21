<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use App\Models\Agent;
use App\Models\Deal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create sample units
        $units = [
            [
                'name' => 'Unit A-101',
                'status' => 'AVAILABLE',
                'type' => '2BR',
                'price' => 450000,
                'public' => true,
                'pre_arranged' => false,
                'shortlisted_count' => 12,
                'images_count' => 8,
                'description' => 'Beautiful 2-bedroom unit with city view'
            ],
            [
                'name' => 'Unit A-102',
                'status' => 'AVAILABLE',
                'type' => '2BR',
                'price' => 455000,
                'public' => true,
                'pre_arranged' => true,
                'shortlisted_count' => 8,
                'images_count' => 10,
                'description' => 'Spacious 2-bedroom unit with balcony'
            ],
            [
                'name' => 'Unit B-201',
                'status' => 'AVAILABLE',
                'type' => '3BR',
                'price' => 650000,
                'public' => false,
                'pre_arranged' => false,
                'shortlisted_count' => 5,
                'images_count' => 12,
                'description' => 'Luxurious 3-bedroom penthouse'
            ],
            [
                'name' => 'Unit B-202',
                'status' => 'SOLD',
                'type' => '3BR',
                'price' => 655000,
                'public' => true,
                'pre_arranged' => false,
                'shortlisted_count' => 15,
                'images_count' => 9,
                'description' => 'Premium 3-bedroom unit with garden view'
            ],
            [
                'name' => 'Unit C-301',
                'status' => 'AVAILABLE',
                'type' => '1BR',
                'price' => 350000,
                'public' => true,
                'pre_arranged' => true,
                'shortlisted_count' => 20,
                'images_count' => 6,
                'description' => 'Cozy 1-bedroom studio perfect for singles'
            ],
            [
                'name' => 'Unit C-302',
                'status' => 'PENDING',
                'type' => '1BR',
                'price' => 355000,
                'public' => false,
                'pre_arranged' => false,
                'shortlisted_count' => 3,
                'images_count' => 7,
                'description' => 'Modern 1-bedroom unit with minimalist design'
            ],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }

        // Create sample agents
        $agents = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@realestate.com',
                'phone' => '+1-555-0101',
                'license' => 'RE-12345',
                'active' => true,
                'commission_rate' => 2.5,
                'bio' => 'Experienced real estate agent with 10+ years in the industry'
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@realestate.com',
                'phone' => '+1-555-0102',
                'license' => 'RE-12346',
                'active' => true,
                'commission_rate' => 3.0,
                'bio' => 'Specialist in luxury properties and high-end clients'
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@realestate.com',
                'phone' => '+1-555-0103',
                'license' => 'RE-12347',
                'active' => false,
                'commission_rate' => 2.0,
                'bio' => 'Part-time agent focusing on residential properties'
            ],
        ];

        foreach ($agents as $agent) {
            Agent::create($agent);
        }

        // Create sample deals
        $deals = [
            [
                'deal_number' => 'DEAL-001',
                'client_name' => 'Alice Williams',
                'client_email' => 'alice.williams@email.com',
                'client_phone' => '+1-555-0201',
                'unit_id' => 4,
                'agent_id' => 1,
                'deal_price' => 655000,
                'status' => 'COMPLETED',
                'deal_date' => '2024-01-15',
                'notes' => 'Smooth transaction, client very satisfied'
            ],
            [
                'deal_number' => 'DEAL-002',
                'client_name' => 'Robert Davis',
                'client_email' => 'robert.davis@email.com',
                'client_phone' => '+1-555-0202',
                'unit_id' => 6,
                'agent_id' => 2,
                'deal_price' => 355000,
                'status' => 'PENDING',
                'deal_date' => '2024-02-20',
                'notes' => 'Waiting for loan approval'
            ],
            [
                'deal_number' => 'DEAL-003',
                'client_name' => 'Emma Martinez',
                'client_email' => 'emma.martinez@email.com',
                'client_phone' => '+1-555-0203',
                'unit_id' => 1,
                'agent_id' => 1,
                'deal_price' => 450000,
                'status' => 'PENDING',
                'deal_date' => '2024-03-10',
                'notes' => 'Client reviewing contract terms'
            ],
        ];

        foreach ($deals as $deal) {
            Deal::create($deal);
        }
    }
}
