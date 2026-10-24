-- Learning starter schema. Adapt for each actual train journey/service instance.
CREATE TABLE train (
    train_id INTEGER PRIMARY KEY,
    train_name VARCHAR(100) NOT NULL,
    source VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL
);

CREATE TABLE seat (
    seat_id INTEGER PRIMARY KEY,
    train_id INTEGER NOT NULL REFERENCES train(train_id),
    seat_number VARCHAR(10) NOT NULL,
    berth_type VARCHAR(20),
    UNIQUE (train_id, seat_number)
);

CREATE TABLE booking (
    booking_id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    passenger_id BIGINT NOT NULL,
    seat_id INTEGER NOT NULL REFERENCES seat(seat_id),
    journey_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL
        CHECK (status IN ('CONFIRMED', 'CANCELLED', 'WAITLISTED'))
);

-- Prevent duplicate confirmed bookings for one seat and date.
CREATE UNIQUE INDEX one_confirmed_booking_per_seat_date
ON booking (seat_id, journey_date)
WHERE status = 'CONFIRMED';

-- IMPORTANT:
-- A real system should identify a journey/service instance explicitly.
-- If a seat belongs to a particular train run, use journey_id in booking
-- and enforce uniqueness on (journey_id, seat_id), not just a calendar date.
