-- Illustrative transaction only. Application must check for an existing
-- confirmed booking AFTER locking the seat, and roll back if already booked.
BEGIN;

SELECT seat_id
FROM seat
WHERE seat_id = 10
FOR UPDATE;

-- Application logic here:
-- 1. Query booking for seat_id=10, journey_date='2026-11-01',
--    status='CONFIRMED'.
-- 2. If one exists, ROLLBACK and report unavailable.
-- 3. Otherwise insert the booking.

INSERT INTO booking (passenger_id, seat_id, journey_date, status)
VALUES (101, 10, DATE '2026-11-01', 'CONFIRMED');

COMMIT;
