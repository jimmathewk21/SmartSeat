<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Explicit DDL keeps CHECK constraints identical on SQLite and MariaDB 10.4.
 * Returns SQL only: no connection is opened by this class.
 */
final class RailwaySchema
{
    public const TABLES = [
        'stations', 'trains', 'train_route_stops', 'coaches', 'seats', 'train_trips',
        'trip_seat_inventory', 'bookings', 'passengers', 'seat_assignments', 'waitlist_entries',
    ];

    public static function statements(string $driver): array
    {
        if (! in_array($driver, ['sqlite', 'mysql', 'mariadb'], true)) {
            throw new InvalidArgumentException('Railway schema supports SQLite and MySQL/MariaDB only.');
        }

        $sqlite = $driver === 'sqlite';
        $id = $sqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT';
        $key = $sqlite ? 'INTEGER' : 'BIGINT UNSIGNED';
        $tail = $sqlite ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        $stationCode = $sqlite ? "code NOT GLOB '*[^A-Z]*'" : "BINARY code NOT REGEXP '[^A-Z]'";
        $trainNumber = $sqlite ? "number NOT GLOB '*[^0-9]*'" : "number NOT REGEXP '[^0-9]'";
        $timestamps = 'created_at DATETIME NULL, updated_at DATETIME NULL';

        $definitions = [
            'stations' => "id $id, code VARCHAR(5) NOT NULL UNIQUE, name VARCHAR(150) NOT NULL,
                city VARCHAR(100) NOT NULL, $timestamps,
                CHECK (length(code) BETWEEN 1 AND 5 AND $stationCode),
                CHECK (length(trim(name)) > 0 AND length(trim(city)) > 0)",
            'trains' => "id $id, number CHAR(5) NOT NULL UNIQUE, name VARCHAR(150) NOT NULL,
                is_active INTEGER NOT NULL DEFAULT 1, $timestamps,
                CHECK (length(number) = 5 AND $trainNumber),
                CHECK (length(trim(name)) > 0), CHECK (is_active IN (0, 1))",
            'train_route_stops' => "id $id, train_id $key NOT NULL, station_id $key NOT NULL,
                stop_order INTEGER NOT NULL, arrival_offset_minutes INTEGER NOT NULL,
                departure_offset_minutes INTEGER NOT NULL, $timestamps,
                UNIQUE (train_id, stop_order), UNIQUE (train_id, station_id),
                FOREIGN KEY (train_id) REFERENCES trains(id) ON DELETE RESTRICT,
                FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE RESTRICT,
                CHECK (stop_order BETWEEN 1 AND 100),
                CHECK (arrival_offset_minutes BETWEEN 0 AND 44639),
                CHECK (departure_offset_minutes BETWEEN arrival_offset_minutes AND 44639)",
            'coaches' => "id $id, train_id $key NOT NULL, label VARCHAR(10) NOT NULL,
                travel_class VARCHAR(2) NOT NULL, $timestamps,
                UNIQUE (train_id, label), UNIQUE (id, train_id),
                FOREIGN KEY (train_id) REFERENCES trains(id) ON DELETE RESTRICT,
                CHECK (length(trim(label)) BETWEEN 1 AND 10),
                CHECK (travel_class IN ('SL','3A','2A','1A','CC','2S','EC'))",
            'seats' => "id $id, coach_id $key NOT NULL, train_id $key NOT NULL,
                number INTEGER NOT NULL, berth_type VARCHAR(12) NOT NULL, $timestamps,
                UNIQUE (coach_id, number), UNIQUE (id, train_id),
                FOREIGN KEY (coach_id, train_id) REFERENCES coaches(id, train_id) ON DELETE RESTRICT,
                CHECK (number BETWEEN 1 AND 200),
                CHECK (berth_type IN ('lower','middle','upper','side_lower','side_upper','chair'))",
            'train_trips' => "id $id, train_id $key NOT NULL, service_date DATE NOT NULL,
                status VARCHAR(12) NOT NULL DEFAULT 'scheduled', cancelled_at DATETIME NULL, $timestamps,
                UNIQUE (train_id, service_date), UNIQUE (id, train_id),
                FOREIGN KEY (train_id) REFERENCES trains(id) ON DELETE RESTRICT,
                CHECK (status IN ('scheduled','cancelled')),
                CHECK ((status = 'cancelled' AND cancelled_at IS NOT NULL) OR (status <> 'cancelled' AND cancelled_at IS NULL))",
            'trip_seat_inventory' => "id $id, trip_id $key NOT NULL, seat_id $key NOT NULL,
                train_id $key NOT NULL, status VARCHAR(12) NOT NULL DEFAULT 'available', $timestamps,
                UNIQUE (trip_id, seat_id), UNIQUE (id, trip_id),
                FOREIGN KEY (trip_id, train_id) REFERENCES train_trips(id, train_id) ON DELETE RESTRICT,
                FOREIGN KEY (seat_id, train_id) REFERENCES seats(id, train_id) ON DELETE RESTRICT,
                CHECK (status IN ('available','blocked'))",
            'bookings' => "id $id, reference CHAR(36) NOT NULL UNIQUE, user_id $key NOT NULL,
                trip_id $key NOT NULL, train_id $key NOT NULL,
                boarding_order INTEGER NOT NULL, alighting_order INTEGER NOT NULL,
                status VARCHAR(12) NOT NULL DEFAULT 'pending', total_paise $key NOT NULL DEFAULT 0,
                cancelled_at DATETIME NULL, $timestamps, UNIQUE (id, trip_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
                FOREIGN KEY (trip_id, train_id) REFERENCES train_trips(id, train_id) ON DELETE RESTRICT,
                FOREIGN KEY (train_id, boarding_order) REFERENCES train_route_stops(train_id, stop_order) ON DELETE RESTRICT,
                FOREIGN KEY (train_id, alighting_order) REFERENCES train_route_stops(train_id, stop_order) ON DELETE RESTRICT,
                CHECK (boarding_order < alighting_order), CHECK (total_paise >= 0),
                CHECK (status IN ('pending','confirmed','waitlisted','cancelled')),
                CHECK ((status = 'cancelled' AND cancelled_at IS NOT NULL) OR (status <> 'cancelled' AND cancelled_at IS NULL))",
            'passengers' => "id $id, booking_id $key NOT NULL, trip_id $key NOT NULL,
                name VARCHAR(150) NOT NULL, age INTEGER NOT NULL, gender VARCHAR(20) NOT NULL,
                status VARCHAR(12) NOT NULL DEFAULT 'pending', cancelled_at DATETIME NULL, $timestamps,
                UNIQUE (id, trip_id),
                FOREIGN KEY (booking_id, trip_id) REFERENCES bookings(id, trip_id) ON DELETE RESTRICT,
                CHECK (length(trim(name)) > 0), CHECK (age BETWEEN 0 AND 120),
                CHECK (gender IN ('male','female','other','prefer_not_to_say')),
                CHECK (status IN ('pending','confirmed','waitlisted','cancelled')),
                CHECK ((status = 'cancelled' AND cancelled_at IS NOT NULL) OR (status <> 'cancelled' AND cancelled_at IS NULL))",
            'seat_assignments' => "id $id, passenger_id $key NOT NULL, inventory_id $key NOT NULL,
                trip_id $key NOT NULL, status VARCHAR(12) NOT NULL DEFAULT 'active',
                cancelled_at DATETIME NULL, $timestamps,
                active_inventory_id $key GENERATED ALWAYS AS (CASE WHEN status = 'active' THEN inventory_id ELSE NULL END) STORED,
                active_passenger_id $key GENERATED ALWAYS AS (CASE WHEN status = 'active' THEN passenger_id ELSE NULL END) STORED,
                UNIQUE (active_inventory_id), UNIQUE (active_passenger_id),
                FOREIGN KEY (passenger_id, trip_id) REFERENCES passengers(id, trip_id) ON DELETE RESTRICT,
                FOREIGN KEY (inventory_id, trip_id) REFERENCES trip_seat_inventory(id, trip_id) ON DELETE RESTRICT,
                CHECK (status IN ('active','cancelled')),
                CHECK ((status = 'cancelled' AND cancelled_at IS NOT NULL) OR (status <> 'cancelled' AND cancelled_at IS NULL))",
            'waitlist_entries' => "id $id, passenger_id $key NOT NULL, trip_id $key NOT NULL,
                travel_class VARCHAR(2) NOT NULL, position INTEGER NOT NULL,
                status VARCHAR(12) NOT NULL DEFAULT 'waiting', cancelled_at DATETIME NULL,
                promoted_at DATETIME NULL, $timestamps,
                active_passenger_id $key GENERATED ALWAYS AS (CASE WHEN status = 'waiting' THEN passenger_id ELSE NULL END) STORED,
                UNIQUE (active_passenger_id), UNIQUE (trip_id, travel_class, position),
                FOREIGN KEY (passenger_id, trip_id) REFERENCES passengers(id, trip_id) ON DELETE RESTRICT,
                CHECK (position > 0), CHECK (travel_class IN ('SL','3A','2A','1A','CC','2S','EC')),
                CHECK (status IN ('waiting','promoted','cancelled')),
                CHECK ((status = 'cancelled' AND cancelled_at IS NOT NULL) OR (status <> 'cancelled' AND cancelled_at IS NULL)),
                CHECK ((status = 'promoted' AND promoted_at IS NOT NULL) OR (status <> 'promoted' AND promoted_at IS NULL))",
        ];

        $sql = [];
        foreach ($definitions as $table => $definition) {
            $sql[] = "CREATE TABLE $table ($definition)$tail";
        }

        return array_merge($sql, [
            'CREATE INDEX inventory_trip_status_idx ON trip_seat_inventory (trip_id, status)',
            'CREATE INDEX trips_date_status_idx ON train_trips (service_date, status)',
            'CREATE INDEX stops_station_train_idx ON train_route_stops (station_id, train_id)',
            'CREATE INDEX bookings_user_trip_idx ON bookings (user_id, trip_id)',
            'CREATE INDEX assignments_inventory_status_idx ON seat_assignments (inventory_id, status)',
            'CREATE INDEX waitlist_queue_idx ON waitlist_entries (trip_id, travel_class, status, position)',
        ]);
    }
}
