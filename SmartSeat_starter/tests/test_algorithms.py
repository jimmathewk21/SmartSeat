import unittest

from algorithms.train_search import search_trains
from algorithms.seat_recommendation import recommend_seats
from algorithms.waitlist_prediction import confirmation_probability
from algorithms.train_recommendation import recommend_trains
from algorithms.food_recommendation import recommend_food


class TestSmartSeatAlgorithms(unittest.TestCase):
    def test_train_search_is_case_insensitive(self):
        trains = [
            {"name": "A", "source": "Chennai", "destination": "Bengaluru"},
            {"name": "B", "source": "Chennai", "destination": "Hyderabad"},
        ]
        self.assertEqual(len(search_trains(trains, "chennai", "BENGALURU")), 1)

    def test_unavailable_seats_are_excluded(self):
        seats = [
            {"number": 1, "berth": "Lower", "fare": 100,
             "available": True, "easy_access": True},
            {"number": 2, "berth": "Lower", "fare": 100,
             "available": False, "easy_access": True},
        ]
        result = recommend_seats(seats, {
            "berth": "Lower", "easy_access": True, "budget": 200
        })
        self.assertEqual([s["number"] for s in result], [1])

    def test_waitlist_returns_none_without_history(self):
        self.assertIsNone(confirmation_probability(4, []))

    def test_cheapest_sort(self):
        trains = [
            {"name": "A", "fare": 500, "duration": 5, "rating": 4.0},
            {"name": "B", "fare": 400, "duration": 7, "rating": 4.1},
        ]
        result = recommend_trains(trains, "cheapest")
        self.assertEqual(result[0]["name"], "B")

    def test_food_excludes_declared_allergen(self):
        meals = [
            {"name": "Rice", "allergens": set(), "diets": {"vegetarian"},
             "tags": {"light"}, "price": 100, "rating": 4.0,
             "available": True, "allergens_verified": True},
            {"name": "Milk dessert", "allergens": {"milk"},
             "diets": {"vegetarian"}, "tags": {"sweet"}, "price": 80,
             "rating": 5.0, "available": True, "allergens_verified": True},
        ]
        user = {"allergies": {"milk"}, "diet": "vegetarian",
                "budget": 200, "preferred": "light"}
        result = recommend_food(meals, user)
        self.assertEqual([m["name"] for m in result], ["Rice"])


if __name__ == "__main__":
    unittest.main()
