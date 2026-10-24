def recommend_trains(trains, sort_by="best"):
    """
    Sort trains by 'cheapest', 'fastest', 'best', or 'rating'.
    Expects each train to contain fare, duration, and rating.
    """
    if sort_by == "cheapest":
        key = lambda t: (t["fare"], t["duration"], -t["rating"])
    elif sort_by == "fastest":
        key = lambda t: (t["duration"], t["fare"], -t["rating"])
    elif sort_by == "rating":
        key = lambda t: (-t["rating"], t["fare"], t["duration"])
    else:
        # Simple transparent default: balance price, duration, and rating.
        # Scale these values appropriately for your real data before using.
        key = lambda t: (t["fare"] + 50 * t["duration"] - 100 * t["rating"])
    return sorted(trains, key=key)


if __name__ == "__main__":
    sample = [
        {"name": "Express A", "fare": 500, "duration": 6, "rating": 4.2},
        {"name": "Express B", "fare": 450, "duration": 7, "rating": 4.5},
        {"name": "Express C", "fare": 500, "duration": 5, "rating": 4.0},
    ]
    print("Cheapest:", recommend_trains(sample, "cheapest"))
    print("Fastest:", recommend_trains(sample, "fastest"))
    print("Best rated:", recommend_trains(sample, "rating"))
