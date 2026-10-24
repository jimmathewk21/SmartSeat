# SmartSeat starter algorithms

This is a small Python starter pack for the SmartSeat train-booking project.
It uses sample data for the recommendation algorithms and PostgreSQL SQL for
the booking integrity example. It does not connect to GitHub or modify your
repository automatically.

## Requirements
- Python 3.10+
- PostgreSQL only for the database schema and booking transaction example

## Run the demos
From this folder:

```bash
python3 algorithms/train_search.py
python3 algorithms/seat_recommendation.py
python3 algorithms/waitlist_prediction.py
python3 algorithms/train_recommendation.py
python3 algorithms/food_recommendation.py
python3 -m unittest discover -s tests -v
```

## Suggested implementation order
1. Run and understand the Python examples.
2. Create a PostgreSQL database and apply `database/schema.sql`.
3. Connect your existing application to the database.
4. Implement booking using a single transaction and database constraints.
5. Add cancellation and waitlist promotion.
6. Replace all sample data with database queries.

## Important notes
- Sample records are fictional and for learning only.
- Waitlist output is a rough estimate, not an official railway confirmation prediction.
- Food/allergen recommendations require verified ingredient and cross-contamination data.
- A recommendation does not guarantee a seat. The database is authoritative for allocation.
- Adapt this structure to your existing app language/framework if it is not Python.
