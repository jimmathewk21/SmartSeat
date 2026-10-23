<?php

namespace App\Support;

use App\Models\Setting;

class AdminEmailTemplateSettings
{
    public const SETTING_KEY = 'admin_email_templates';

    public static function definitions(): array
    {
        return [
            'sponsor_inquiry' => [
                'label' => 'Sponsor Apply Form',
                'placeholders' => ['name', 'job_title', 'email', 'phone', 'company_name', 'country', 'category', 'website_url', 'linkedin_url', 'facebook_url', 'company_logo_url', 'company_profile', 'sponsorship_categories', 'budget_range', 'benefit_logo_placement', 'benefit_prominent_logo', 'benefit_participant_list', 'benefit_promotional_materials', 'a4_advert_option', 'benefit_social_media_posts', 'benefit_speaking_slot', 'benefit_exhibit_space', 'benefit_backdrop_logo', 'benefit_required_delegate_registrations', 'registration_category', 'total_registrations_required', 'rollup_standees_option', 'consent_accuracy', 'consent_approval_required', 'message'],
            ],
            'contact_submission' => [
                'label' => 'Contact Form',
                'placeholders' => ['name', 'email', 'subject', 'message'],
            ],
            'user_registration' => [
                'label' => 'Frontend Registration',
                'placeholders' => ['name', 'company_name', 'email', 'mobile'],
            ],
            'user_approval' => [
                'label' => 'User Approval',
                'placeholders' => ['name', 'email', 'mobile', 'phone', 'company_name', 'login_url', 'support_email', 'approved_at'],
            ],
            'speaker_application' => [
                'label' => 'Speaker Apply Form',
                'placeholders' => ['full_name', 'gender', 'nationality', 'designation', 'organization', 'short_bio', 'photo_url', 'linkedin_url', 'website_url', 'official_email', 'personal_email', 'mobile', 'alternate_mobile', 'city', 'state', 'country', 'proposed_topic_title', 'presentation_mode', 'speaking_slot_duration', 'abstract_summary', 'areas_of_expertise', 'years_of_experience', 'av_requirements', 'air_ticket_support', 'hotel_stay_support', 'registration_support', 'sponsor_interest', 'publications_awards', 'special_requirements'],
            ],
            'my_contact_submission' => [
                'label' => 'Add My Contact Form',
                'placeholders' => ['full_name', 'designation', 'nationality', 'category', 'gender', 'photo_url', 'company_name', 'area_of_expertise', 'official_email', 'alternate_email', 'mobile_whatsapp_number', 'linkedin_url', 'website_url', 'publish_basic_details', 'publish_contact_details', 'confirm_accuracy', 'grant_permission'],
            ],
            'newsletter_subscription' => [
                'label' => 'Newsletter Subscription',
                'placeholders' => ['email'],
            ],
            'testimonial_feedback' => [
                'label' => 'Testimonial Feedback',
                'placeholders' => ['name', 'company_name', 'email', 'country', 'experience_word', 'stars', 'comment', 'picture_url'],
            ],
            'hotel_booking' => [
                'label' => 'Hotel Booking / Venue Reserve',
                'placeholders' => ['booking_code', 'hotel_name', 'room_name', 'full_name', 'email', 'phone', 'country', 'occupancy', 'bed_size', 'preference', 'check_in_date', 'check_out_date', 'number_of_guests', 'number_of_rooms', 'special_requests'],
            ],
            'ticket_booking_paid' => [
                'label' => 'Ticket Booking Paid',
                'placeholders' => ['booking_code', 'event_title', 'plan_name', 'user_name', 'user_email', 'quantity', 'display_currency', 'grand_total', 'billing_country_code', 'billing_company_name', 'billing_address_line_1', 'billing_address_line_2', 'gstin', 'promo_code', 'delegates'],
            ],
            'ticket_booking_failed' => [
                'label' => 'Ticket Booking Failed',
                'placeholders' => ['booking_code', 'event_title', 'plan_name', 'user_name', 'user_email', 'quantity', 'display_currency', 'grand_total', 'billing_country_code', 'billing_company_name', 'billing_address_line_1', 'billing_address_line_2', 'gstin', 'promo_code', 'delegates', 'failure_reason', 'razorpay_order_id', 'razorpay_payment_id'],
            ],
            'admin_login' => [
                'label' => 'Admin Login',
                'placeholders' => ['admin_name', 'admin_email', 'login_ip', 'login_at', 'user_agent'],
            ],
            'user_profile_updated' => [
                'label' => 'Frontend Profile Update',
                'placeholders' => ['name', 'email', 'company_name', 'changed_fields', 'updated_at'],
            ],
        ];
    }

    public static function defaults(): array
    {
        return [
            'templates' => [
                'sponsor_inquiry' => [
                    'enabled' => true,
                    'subject' => 'New sponsor enquiry from {{name}}',
                    'body' => '<p>A new sponsor enquiry was submitted.</p><p><strong>Name:</strong> {{name}}<br><strong>Job Title:</strong> {{job_title}}<br><strong>Email:</strong> {{email}}<br><strong>Phone:</strong> {{phone}}<br><strong>Company:</strong> {{company_name}}<br><strong>Country:</strong> {{country}}<br><strong>Category:</strong> {{category}}<br><strong>Website:</strong> {{website_url}}<br><strong>LinkedIn:</strong> {{linkedin_url}}<br><strong>Facebook:</strong> {{facebook_url}}<br><strong>Company Logo:</strong> {{company_logo_url}}<br><strong>Company Profile:</strong> {{company_profile}}<br><strong>Sponsorship Categories:</strong> {{sponsorship_categories}}<br><strong>Budget:</strong> {{budget_range}}<br><strong>Logo on Website/Brochure:</strong> {{benefit_logo_placement}}<br><strong>Prominent Logo Benefit:</strong> {{benefit_prominent_logo}}<br><strong>Participant List Benefit:</strong> {{benefit_participant_list}}<br><strong>Promotional Materials Benefit:</strong> {{benefit_promotional_materials}}<br><strong>A4 Advert Option:</strong> {{a4_advert_option}}<br><strong>Social Media Posts:</strong> {{benefit_social_media_posts}}<br><strong>Speaking Slot Benefit:</strong> {{benefit_speaking_slot}}<br><strong>Exhibit Space Benefit:</strong> {{benefit_exhibit_space}}<br><strong>Backdrop Logo Benefit:</strong> {{benefit_backdrop_logo}}<br><strong>Delegate Registrations Required:</strong> {{benefit_required_delegate_registrations}}<br><strong>Registration Category:</strong> {{registration_category}}<br><strong>Total Registrations Required:</strong> {{total_registrations_required}}<br><strong>Roll-up Standees Option:</strong> {{rollup_standees_option}}<br><strong>Consent Accuracy:</strong> {{consent_accuracy}}<br><strong>Consent Approval Required:</strong> {{consent_approval_required}}<br><strong>Message:</strong> {{message}}</p>',
                ],
                'contact_submission' => [
                    'enabled' => true,
                    'subject' => 'New contact enquiry from {{name}}',
                    'body' => '<p>A new contact enquiry was submitted.</p><p><strong>Name:</strong> {{name}}<br><strong>Email:</strong> {{email}}<br><strong>Subject:</strong> {{subject}}<br><strong>Message:</strong> {{message}}</p>',
                ],
                'user_registration' => [
                    'enabled' => true,
                    'subject' => 'New user registration pending approval: {{name}}',
                    'body' => '<p>A new frontend user registration is waiting for admin approval.</p><p><strong>Name:</strong> {{name}}<br><strong>Company:</strong> {{company_name}}<br><strong>Email:</strong> {{email}}<br><strong>Mobile:</strong> {{mobile}}</p>',
                ],
                'user_approval' => [
                    'enabled' => true,
                    'subject' => 'Your account has been approved, {{name}}',
                    'body' => '<p>Hello {{name}},</p><p>Your account has been approved and you can log in now.</p><p><strong>Email:</strong> {{email}}<br><strong>Phone:</strong> {{phone}}<br><strong>Company:</strong> {{company_name}}<br><strong>Approved At:</strong> {{approved_at}}<br><strong>Login URL:</strong> <a href="{{login_url}}">{{login_url}}</a><br><strong>Support Email:</strong> <a href="mailto:{{support_email}}">{{support_email}}</a></p>',
                ],
                'speaker_application' => [
                    'enabled' => true,
                    'subject' => 'New speaker application from {{full_name}}',
                    'body' => '<p>A new speaker application was submitted.</p><p><strong>Name:</strong> {{full_name}}<br><strong>Gender:</strong> {{gender}}<br><strong>Nationality:</strong> {{nationality}}<br><strong>Designation:</strong> {{designation}}<br><strong>Company Name:</strong> {{organization}}<br><strong>Short Bio:</strong> {{short_bio}}<br><strong>Photo:</strong> {{photo_url}}<br><strong>LinkedIn:</strong> {{linkedin_url}}<br><strong>Website:</strong> {{website_url}}<br><strong>Official Email:</strong> {{official_email}}<br><strong>Alternate Email:</strong> {{personal_email}}<br><strong>Mobile:</strong> {{mobile}}<br><strong>Alternate Mobile:</strong> {{alternate_mobile}}<br><strong>City:</strong> {{city}}<br><strong>State:</strong> {{state}}<br><strong>Country:</strong> {{country}}<br><strong>Proposed Topic:</strong> {{proposed_topic_title}}<br><strong>Presentation Mode:</strong> {{presentation_mode}}<br><strong>Speaking Slot Duration:</strong> {{speaking_slot_duration}}<br><strong>Abstract Summary:</strong> {{abstract_summary}}<br><strong>Areas of Expertise:</strong> {{areas_of_expertise}}<br><strong>Years of Experience:</strong> {{years_of_experience}}<br><strong>AV Support Required:</strong> {{av_requirements}}<br><strong>Can Book Own Air Tickets:</strong> {{air_ticket_support}}<br><strong>Can Cover Hotel Stay and Logistics:</strong> {{hotel_stay_support}}<br><strong>Can Register and Pay Online:</strong> {{registration_support}}<br><strong>Interested in Sponsorship:</strong> {{sponsor_interest}}<br><strong>Publications / Awards:</strong> {{publications_awards}}<br><strong>Remarks:</strong> {{special_requirements}}</p>',
                ],
                'my_contact_submission' => [
                    'enabled' => true,
                    'subject' => 'New my contact submission from {{full_name}}',
                    'body' => '<p>A new add-my-contact form was submitted.</p><p><strong>Full Name:</strong> {{full_name}}<br><strong>Job Title:</strong> {{designation}}<br><strong>Nationality:</strong> {{nationality}}<br><strong>Category:</strong> {{category}}<br><strong>Gender:</strong> {{gender}}<br><strong>Picture:</strong> {{photo_url}}<br><strong>Company Name:</strong> {{company_name}}<br><strong>Area of Expertise:</strong> {{area_of_expertise}}<br><strong>Official Email:</strong> {{official_email}}<br><strong>Alternate Email:</strong> {{alternate_email}}<br><strong>Mobile / WhatsApp:</strong> {{mobile_whatsapp_number}}<br><strong>LinkedIn URL:</strong> {{linkedin_url}}<br><strong>Website URL:</strong> {{website_url}}<br><strong>Publish Basic Details:</strong> {{publish_basic_details}}<br><strong>Publish Contact Details:</strong> {{publish_contact_details}}<br><strong>Confirm Accuracy:</strong> {{confirm_accuracy}}<br><strong>Grant Permission:</strong> {{grant_permission}}</p>',
                ],
                'newsletter_subscription' => [
                    'enabled' => true,
                    'subject' => 'New newsletter subscription: {{email}}',
                    'body' => '<p>A new newsletter subscription was received.</p><p><strong>Email:</strong> {{email}}</p>',
                ],
                'testimonial_feedback' => [
                    'enabled' => true,
                    'subject' => 'New testimonial feedback from {{name}}',
                    'body' => '<p>A new testimonial feedback form was submitted.</p><p><strong>Name:</strong> {{name}}<br><strong>Company:</strong> {{company_name}}<br><strong>Email:</strong> {{email}}<br><strong>Country:</strong> {{country}}<br><strong>Experience in One Word:</strong> {{experience_word}}<br><strong>Stars:</strong> {{stars}}<br><strong>Comment:</strong> {{comment}}<br><strong>Picture:</strong> {{picture_url}}</p>',
                ],
                'hotel_booking' => [
                    'enabled' => true,
                    'subject' => 'New hotel booking request: {{booking_code}}',
                    'body' => '<p>A new hotel booking / venue reservation was submitted.</p><p><strong>Booking Code:</strong> {{booking_code}}<br><strong>Hotel:</strong> {{hotel_name}}<br><strong>Room:</strong> {{room_name}}<br><strong>Name:</strong> {{full_name}}<br><strong>Email:</strong> {{email}}<br><strong>Phone:</strong> {{phone}}<br><strong>Country:</strong> {{country}}<br><strong>Occupancy:</strong> {{occupancy}}<br><strong>Bed Size:</strong> {{bed_size}}<br><strong>Preference:</strong> {{preference}}<br><strong>Check-in:</strong> {{check_in_date}}<br><strong>Check-out:</strong> {{check_out_date}}<br><strong>Guests:</strong> {{number_of_guests}}<br><strong>Rooms:</strong> {{number_of_rooms}}<br><strong>Special Requests:</strong> {{special_requests}}</p>',
                ],
                'ticket_booking_paid' => [
                    'enabled' => true,
                    'subject' => 'New paid ticket booking: {{booking_code}}',
                    'body' => '<p>A ticket booking was successfully paid.</p><p><strong>Booking Code:</strong> {{booking_code}}<br><strong>Event:</strong> {{event_title}}<br><strong>Plan:</strong> {{plan_name}}<br><strong>User:</strong> {{user_name}}<br><strong>Email:</strong> {{user_email}}<br><strong>Quantity:</strong> {{quantity}}<br><strong>Currency:</strong> {{display_currency}}<br><strong>Grand Total:</strong> {{grand_total}}<br><strong>Billing Country:</strong> {{billing_country_code}}<br><strong>Billing Company:</strong> {{billing_company_name}}<br><strong>Billing Address Line 1:</strong> {{billing_address_line_1}}<br><strong>Billing Address Line 2:</strong> {{billing_address_line_2}}<br><strong>GSTIN:</strong> {{gstin}}<br><strong>Promo Code:</strong> {{promo_code}}<br><strong>Delegates:</strong><br>{{delegates}}</p>',
                ],
                'ticket_booking_failed' => [
                    'enabled' => true,
                    'subject' => 'Failed ticket payment: {{booking_code}}',
                    'body' => '<p>A ticket payment failed or was not completed.</p><p><strong>Booking Code:</strong> {{booking_code}}<br><strong>Event:</strong> {{event_title}}<br><strong>Plan:</strong> {{plan_name}}<br><strong>User:</strong> {{user_name}}<br><strong>Email:</strong> {{user_email}}<br><strong>Quantity:</strong> {{quantity}}<br><strong>Currency:</strong> {{display_currency}}<br><strong>Grand Total:</strong> {{grand_total}}<br><strong>Billing Country:</strong> {{billing_country_code}}<br><strong>Billing Company:</strong> {{billing_company_name}}<br><strong>Billing Address Line 1:</strong> {{billing_address_line_1}}<br><strong>Billing Address Line 2:</strong> {{billing_address_line_2}}<br><strong>GSTIN:</strong> {{gstin}}<br><strong>Promo Code:</strong> {{promo_code}}<br><strong>Razorpay Order ID:</strong> {{razorpay_order_id}}<br><strong>Razorpay Payment ID:</strong> {{razorpay_payment_id}}<br><strong>Failure Reason:</strong> {{failure_reason}}<br><strong>Delegates:</strong><br>{{delegates}}</p>',
                ],
                'admin_login' => [
                    'enabled' => true,
                    'subject' => 'Admin login alert: {{admin_name}}',
                    'body' => '<p>An admin has logged into the dashboard.</p><p><strong>Name:</strong> {{admin_name}}<br><strong>Email:</strong> {{admin_email}}<br><strong>Login Time:</strong> {{login_at}}<br><strong>IP Address:</strong> {{login_ip}}<br><strong>User Agent:</strong> {{user_agent}}</p>',
                ],
                'user_profile_updated' => [
                    'enabled' => true,
                    'subject' => 'Frontend user profile updated: {{name}}',
                    'body' => '<p>A frontend user updated their profile details.</p><p><strong>Name:</strong> {{name}}<br><strong>Email:</strong> {{email}}<br><strong>Company:</strong> {{company_name}}<br><strong>Changed Fields:</strong> {{changed_fields}}<br><strong>Updated At:</strong> {{updated_at}}</p>',
                ],
            ],
        ];
    }

    public static function get(): array
    {
        $raw = Setting::query()->where('key', self::SETTING_KEY)->value('value');
        if (! $raw) {
            return self::defaults();
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return self::defaults();
        }

        if (! is_array($decoded)) {
            return self::defaults();
        }

        return self::normalize(array_merge(self::defaults(), $decoded));
    }

    public static function set(array $data): void
    {
        $payload = self::normalize(array_merge(self::defaults(), $data));

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }

    private static function normalize(array $data): array
    {
        $defaults = self::defaults()['templates'];
        $templates = [];

        foreach ($defaults as $key => $defaultTemplate) {
            $current = $data['templates'][$key] ?? [];
            $templates[$key] = [
                'enabled' => (bool) ($current['enabled'] ?? $defaultTemplate['enabled']),
                'subject' => trim((string) ($current['subject'] ?? $defaultTemplate['subject'])),
                'body' => trim((string) ($current['body'] ?? $defaultTemplate['body'])),
            ];
        }

        return ['templates' => $templates];
    }
}
