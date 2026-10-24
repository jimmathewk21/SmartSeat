def search_trains(trains, source, destination):
    """Return trains matching source and destination (case-insensitive)."""
    return [
        train for train in trains
        if train["source"].casefold() == source.casefold()
        and train["destination"].casefold() == destination.casefold()
    ]


if __name__ == "__main__":
    sample_trains = [
        {"id": 101, "name": "Express A", "source": "Chennai",
         "destination": "Bengaluru", "fare": 500},
        {"id": 102, "name": "Express B", "source": "Chennai",
         "destination": "Hyderabad", "fare": 700},
    ]
    print(search_trains(sample_trains, "Chennai", "Bengaluru"))
