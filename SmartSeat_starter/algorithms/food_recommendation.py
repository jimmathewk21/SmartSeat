def recommend_food(meals, user):
    """
    Filter meals by availability, verified allergen data, declared diet,
    declared allergies, and budget; then rank by rating and preference.
    """
    result = []
    for meal in meals:
        if not meal.get("available", False):
            continue
        if not meal.get("allergens_verified", False):
            continue
        if user["allergies"] & meal["allergens"]:
            continue
        if user["diet"] not in meal["diets"]:
            continue
        if meal["price"] > user["budget"]:
            continue

        score = meal["rating"]
        if user.get("preferred") in meal["tags"]:
            score += 2
        result.append((score, meal))

    result.sort(key=lambda item: item[0], reverse=True)
    return [meal for _, meal in result]


if __name__ == "__main__":
    meals = [
        {"name": "Vegetable Rice", "allergens": set(),
         "diets": {"vegetarian", "vegan"}, "tags": {"rice", "light"},
         "price": 100, "rating": 4.2, "available": True,
         "allergens_verified": True},
        {"name": "Paneer Sandwich", "allergens": {"milk"},
         "diets": {"vegetarian"}, "tags": {"sandwich"},
         "price": 120, "rating": 4.5, "available": True,
         "allergens_verified": True},
        {"name": "Fruit Bowl", "allergens": set(),
         "diets": {"vegetarian", "vegan"}, "tags": {"fruit", "light"},
         "price": 80, "rating": 4.0, "available": True,
         "allergens_verified": True},
    ]
    user = {
        "allergies": {"milk"}, "diet": "vegetarian",
        "budget": 110, "preferred": "light",
    }
    print([meal["name"] for meal in recommend_food(meals, user)])
