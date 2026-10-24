def confirmation_probability(position, history):
    """
    Estimate confirmation percentage from nearby historical waitlist positions.
    Each history item must have integer 'position' and boolean 'confirmed'.
    Returns None when there is no comparable history.
    """
    similar = [
        item for item in history
        if abs(item["position"] - position) <= 5
    ]
    if not similar:
        return None

    confirmed = sum(bool(item["confirmed"]) for item in similar)
    total = len(similar)
    return round((confirmed + 1) / (total + 2) * 100, 2)


if __name__ == "__main__":
    history = [
        {"position": 3, "confirmed": True},
        {"position": 4, "confirmed": True},
        {"position": 5, "confirmed": False},
        {"position": 7, "confirmed": True},
        {"position": 8, "confirmed": False},
    ]
    print(confirmation_probability(4, history), "%")
