<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // User Management
            ['id' => 1, 'title' => 'user_management_access'],

            // Permissions
            ['id' => 2, 'title' => 'permission_create'],
            ['id' => 3, 'title' => 'permission_edit'],
            ['id' => 4, 'title' => 'permission_show'],
            ['id' => 5, 'title' => 'permission_delete'],
            ['id' => 6, 'title' => 'permission_access'],

            // Roles
            ['id' => 7, 'title' => 'role_create'],
            ['id' => 8, 'title' => 'role_edit'],
            ['id' => 9, 'title' => 'role_show'],
            ['id' => 10, 'title' => 'role_delete'],
            ['id' => 11, 'title' => 'role_access'],

            // Users
            ['id' => 12, 'title' => 'user_create'],
            ['id' => 13, 'title' => 'user_edit'],
            ['id' => 14, 'title' => 'user_show'],
            ['id' => 15, 'title' => 'user_delete'],
            ['id' => 16, 'title' => 'user_access'],

            // Audit Logs
            ['id' => 17, 'title' => 'audit_log_show'],
            ['id' => 18, 'title' => 'audit_log_access'],

            // Settings
            ['id' => 19, 'title' => 'setting_create'],
            ['id' => 20, 'title' => 'setting_edit'],
            ['id' => 21, 'title' => 'setting_show'],
            ['id' => 22, 'title' => 'setting_delete'],
            ['id' => 23, 'title' => 'setting_access'],

            // Profile
            ['id' => 24, 'title' => 'profile_password_edit'],
            // Country
            ['id' => 30, 'title' => 'country_create'],
            ['id' => 31, 'title' => 'country_edit'],
            ['id' => 32, 'title' => 'country_show'],
            ['id' => 33, 'title' => 'country_delete'],
            ['id' => 34, 'title' => 'country_access'],
            // State
            ['id' => 35, 'title' => 'state_create'],
            ['id' => 36, 'title' => 'state_edit'],
            ['id' => 37, 'title' => 'state_show'],
            ['id' => 38, 'title' => 'state_delete'],
            ['id' => 39, 'title' => 'state_access'],
            // City
            ['id' => 40, 'title' => 'city_create'],
            ['id' => 41, 'title' => 'city_edit'],
            ['id' => 42, 'title' => 'city_show'],
            ['id' => 43, 'title' => 'city_delete'],
            ['id' => 44, 'title' => 'city_access'],
            // menu
            ['id' => 45, 'title' => 'detail_access'],
            // Developer Options
            ['id' => 46, 'title' => 'developer_options'],
            // Sponsor Categories
            ['id' => 47, 'title' => 'sponsor_category_create'],
            ['id' => 48, 'title' => 'sponsor_category_edit'],
            ['id' => 49, 'title' => 'sponsor_category_show'],
            ['id' => 50, 'title' => 'sponsor_category_delete'],
            ['id' => 51, 'title' => 'sponsor_category_access'],
            // Sponsors
            ['id' => 52, 'title' => 'sponsor_create'],
            ['id' => 53, 'title' => 'sponsor_edit'],
            ['id' => 54, 'title' => 'sponsor_show'],
            ['id' => 55, 'title' => 'sponsor_delete'],
            ['id' => 56, 'title' => 'sponsor_access'],
            // Events
            ['id' => 57, 'title' => 'event_create'],
            ['id' => 58, 'title' => 'event_edit'],
            ['id' => 59, 'title' => 'event_show'],
            ['id' => 60, 'title' => 'event_delete'],
            ['id' => 61, 'title' => 'event_access'],
            // Speakers
            ['id' => 62, 'title' => 'speaker_create'],
            ['id' => 63, 'title' => 'speaker_edit'],
            ['id' => 64, 'title' => 'speaker_show'],
            ['id' => 65, 'title' => 'speaker_delete'],
            ['id' => 66, 'title' => 'speaker_access'],
            // Testimonials
            ['id' => 67, 'title' => 'testimonial_create'],
            ['id' => 68, 'title' => 'testimonial_edit'],
            ['id' => 69, 'title' => 'testimonial_show'],
            ['id' => 70, 'title' => 'testimonial_delete'],
            ['id' => 71, 'title' => 'testimonial_access'],
            // FAQs
            ['id' => 72, 'title' => 'faq_create'],
            ['id' => 73, 'title' => 'faq_edit'],
            ['id' => 74, 'title' => 'faq_show'],
            ['id' => 75, 'title' => 'faq_delete'],
            ['id' => 76, 'title' => 'faq_access'],
            // Members
            ['id' => 77, 'title' => 'member_create'],
            ['id' => 78, 'title' => 'member_edit'],
            ['id' => 79, 'title' => 'member_show'],
            ['id' => 80, 'title' => 'member_delete'],
            ['id' => 81, 'title' => 'member_access'],
            // Attractions
            ['id' => 82, 'title' => 'attraction_create'],
            ['id' => 83, 'title' => 'attraction_edit'],
            ['id' => 84, 'title' => 'attraction_show'],
            ['id' => 85, 'title' => 'attraction_delete'],
            ['id' => 86, 'title' => 'attraction_access'],
            // Login Activities
            ['id' => 87, 'title' => 'login_activity_show'],
            ['id' => 88, 'title' => 'login_activity_access'],
            // Participants
            ['id' => 89, 'title' => 'participant_access'],
            ['id' => 90, 'title' => 'participant_show'],
            ['id' => 91, 'title' => 'participant_approve'],
            // Ticket Bookings
            ['id' => 92, 'title' => 'ticket_booking_access'],
            ['id' => 93, 'title' => 'ticket_booking_show'],
            ['id' => 94, 'title' => 'ticket_booking_export'],
            // FAQ Categories
            ['id' => 95, 'title' => 'faq_category_create'],
            ['id' => 96, 'title' => 'faq_category_edit'],
            ['id' => 97, 'title' => 'faq_category_show'],
            ['id' => 98, 'title' => 'faq_category_delete'],
            ['id' => 99, 'title' => 'faq_category_access'],

        ];

        Permission::insertOrIgnore($permissions);
        Permission::firstOrCreate(['title' => 'train_management_access']);
        Permission::firstOrCreate(['title' => 'train_management_edit']);
    }
}
