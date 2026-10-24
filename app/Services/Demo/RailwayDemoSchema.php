<?php

namespace App\Services\Demo;

/** Columns written by the installer; generated columns are deliberately never inserted. */
class RailwayDemoSchema
{
    public const COLUMNS = [
        'stations' => 'code name city state is_active created_at updated_at',
        'users' => 'name email password verification_status train_owner_approved_at created_at updated_at',
        'participants' => 'user_id name email is_approved approved_at train_access_enabled created_at updated_at',
        'role_user' => 'user_id role_id',
        'trains' => 'code name owner_user_id is_active created_at updated_at',
        'train_route_stops' => 'train_id station_id stop_order arrival_offset_minutes departure_offset_minutes created_at updated_at',
        'train_coaches' => 'train_id code class_code sort_order created_at updated_at',
        'train_seats' => 'train_id train_coach_id number berth_type is_accessible created_at updated_at',
        'train_trips' => 'train_id starts_at ends_at status created_at updated_at',
        'trip_seat_inventories' => 'train_trip_id train_id train_seat_id status current_booking_id created_at updated_at',
        'train_bookings' => 'booking_code user_id train_trip_id boarding_station_id destination_station_id boarding_at arrival_at status seat_count confirmed_at cancelled_at created_at updated_at',
        'train_booking_passengers' => 'train_booking_id name age gender requires_accessible_seat created_at updated_at',
        'train_booking_seats' => 'train_booking_id train_trip_id trip_seat_inventory_id train_booking_passenger_id released_at created_at updated_at',
        'train_waitlist_entries' => 'user_id train_trip_id boarding_station_id destination_station_id boarding_at arrival_at seat_count requires_accessible_seat status promoted_booking_id promoted_at created_at updated_at',
        'train_waitlist_passengers' => 'train_waitlist_entry_id name age gender requires_accessible_seat created_at updated_at',
        'settings' => 'key value created_at updated_at deleted_at',
        'roles' => 'title deleted_at', 'permissions' => 'title deleted_at', 'permission_role' => 'role_id permission_id',
    ];

    public const UNIQUE_KEYS = [
        'stations' => [['code']], 'users' => [['email']], 'participants' => [['email'], ['user_id']],
        'trains' => [['code']], 'settings' => [['key']],
        'train_route_stops' => [['train_id', 'station_id'], ['train_id', 'stop_order']],
        'train_coaches' => [['train_id', 'code'], ['id', 'train_id']],
        'train_seats' => [['train_coach_id', 'number'], ['id', 'train_id']],
        'train_trips' => [['id', 'train_id']],
        'trip_seat_inventories' => [['train_trip_id', 'train_seat_id'], ['id', 'train_trip_id']],
        'train_bookings' => [['booking_code'], ['id', 'train_trip_id']],
        'train_booking_passengers' => [['id', 'train_booking_id']],
        'train_booking_seats' => [['train_booking_id', 'trip_seat_inventory_id'], ['active_trip_seat_inventory_id']],
    ];

    public const FOREIGN_KEYS = [
        'participants' => [['user_id', 'users', 'id']],
        'role_user' => [['user_id', 'users', 'id'], ['role_id', 'roles', 'id']],
        'trains' => [['owner_user_id', 'users', 'id']],
        'train_route_stops' => [['train_id', 'trains', 'id'], ['station_id', 'stations', 'id']],
        'train_coaches' => [['train_id', 'trains', 'id']],
        'train_seats' => [['train_coach_id train_id', 'train_coaches', 'id train_id']],
        'train_trips' => [['train_id', 'trains', 'id']],
        'trip_seat_inventories' => [
            ['train_trip_id train_id', 'train_trips', 'id train_id'],
            ['train_seat_id train_id', 'train_seats', 'id train_id'],
            ['current_booking_id train_trip_id', 'train_bookings', 'id train_trip_id'],
        ],
        'train_bookings' => [['user_id', 'users', 'id'], ['train_trip_id', 'train_trips', 'id'], ['boarding_station_id', 'stations', 'id'], ['destination_station_id', 'stations', 'id']],
        'train_booking_passengers' => [['train_booking_id', 'train_bookings', 'id']],
        'train_booking_seats' => [
            ['train_booking_id train_trip_id', 'train_bookings', 'id train_trip_id'],
            ['trip_seat_inventory_id train_trip_id', 'trip_seat_inventories', 'id train_trip_id'],
            ['train_booking_passenger_id train_booking_id', 'train_booking_passengers', 'id train_booking_id'],
        ],
        'train_waitlist_entries' => [['user_id', 'users', 'id'], ['train_trip_id', 'train_trips', 'id'], ['boarding_station_id', 'stations', 'id'], ['destination_station_id', 'stations', 'id'], ['promoted_booking_id', 'train_bookings', 'id']],
        'train_waitlist_passengers' => [['train_waitlist_entry_id', 'train_waitlist_entries', 'id']],
    ];

    public static function columns(string $table): array
    {
        return explode(' ', self::COLUMNS[$table]);
    }

    public static function normalizeExpression(string $expression): string
    {
        $expression = preg_replace("/_[a-z0-9]+(?=')/i", '', $expression);

        return strtolower(preg_replace('/[\s`()]+/', '', $expression));
    }
}
