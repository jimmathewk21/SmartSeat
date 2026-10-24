def recommend_seats(seats, preferences):
    """Rank available seats; this recommends, but does not allocate, a seat."""
    ranked = []
    for seat in seats:
        if not seat["available"]:
            continue

        score = 0
        if seat.get("berth") == preferences.get("berth"):
            score += 3
        if preferences.get("easy_access") and seat.get("easy_access"):
            score += 4
        if seat.get("fare", float("inf")) <= preferences.get("budget", 0):
            score += 2

        ranked.append((score, seat))

    ranked.sort(key=lambda item: item[0], reverse=True)
    return [seat for _, seat in ranked]


if __name__ == "__main__":
    sample_seats = [
        {"number": 1, "berth": "Lower", "fare": 500,
         "available": True, "easy_access": True},
        {"number": 2, "berth": "Upper", "fare": 400,
         "available": True, "easy_access": False},
        {"number": 3, "berth": "Lower", "fare": 500,
         "available": False, "easy_access": True},
    ]
    prefs = {"berth": "Lower", "easy_access": True, "budget": 550}
    print(recommend_seats(sample_seats, prefs))
