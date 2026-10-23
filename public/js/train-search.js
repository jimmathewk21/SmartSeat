(() => {
    const maximumPassengers = 6;

    document.querySelectorAll('[data-train-search-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const submit = form.querySelector('[data-search-submit]');
            if (submit) {
                submit.classList.add('is-loading');
                submit.setAttribute('aria-busy', 'true');
            }
        });
    });

    document.querySelectorAll('[data-swap-stations]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.closest('[data-train-search-form]');
            const origin = form?.querySelector('[name="from_station_id"]');
            const destination = form?.querySelector('[name="to_station_id"]');

            if (!origin || !destination) return;

            [origin.value, destination.value] = [destination.value, origin.value];
            origin.dispatchEvent(new Event('change', { bubbles: true }));
            destination.dispatchEvent(new Event('change', { bubbles: true }));
            origin.focus();
        });
    });

    document.querySelectorAll('[data-passenger-form]').forEach((form) => {
        const rows = form.querySelector('[data-passenger-rows]');
        const addButton = form.querySelector('[data-add-passenger]');
        const countLabel = form.querySelector('[data-passenger-count]');
        const limitMessage = form.querySelector('[data-passenger-limit]');
        const feedback = form.querySelector('[data-seat-feedback]');
        const seats = [...form.querySelectorAll('input[name="inventory_ids[]"]')];

        if (!rows) return;

        const passengerRows = () => [...rows.querySelectorAll('.train-passenger-row')];

        const renumberRows = () => {
            const currentRows = passengerRows();
            currentRows.forEach((row, index) => {
                row.querySelectorAll('[name]').forEach((field) => {
                    field.name = field.name.replace(/passengers\[\d+\]/, `passengers[${index}]`);
                });

                const removeButton = row.querySelector('[data-remove-passenger]');
                if (removeButton) removeButton.hidden = currentRows.length === 1;
            });

            if (countLabel) countLabel.textContent = `${currentRows.length} of ${maximumPassengers}`;
            if (addButton) addButton.disabled = currentRows.length >= maximumPassengers;
            if (limitMessage) {
                limitMessage.textContent = currentRows.length >= maximumPassengers
                    ? `Up to ${maximumPassengers} passengers can be added.`
                    : '';
            }

            updateSeatFeedback();
        };

        const updateSeatFeedback = () => {
            if (!feedback || seats.length === 0) return;

            const selectedCount = seats.filter((seat) => seat.checked).length;
            const passengerCount = passengerRows().length;
            feedback.textContent = selectedCount === passengerCount
                ? `${selectedCount} seat${selectedCount === 1 ? '' : 's'} selected for ${passengerCount} passenger${passengerCount === 1 ? '' : 's'}.`
                : `Select one seat for each passenger (${selectedCount} selected, ${passengerCount} needed).`;
        };

        addButton?.addEventListener('click', () => {
            const currentRows = passengerRows();
            if (currentRows.length >= maximumPassengers) return;

            const row = currentRows[0].cloneNode(true);
            row.querySelectorAll('input').forEach((input) => {
                if (input.type === 'checkbox') input.checked = false;
                else input.value = '';
            });
            row.querySelectorAll('select').forEach((select) => { select.selectedIndex = 0; });
            rows.append(row);
            renumberRows();
            row.querySelector('input[type="text"]')?.focus();
        });

        rows.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-passenger]');
            if (!removeButton || passengerRows().length <= 1) return;

            removeButton.closest('.train-passenger-row')?.remove();
            renumberRows();
        });

        seats.forEach((seat) => seat.addEventListener('change', updateSeatFeedback));

        form.addEventListener('submit', (event) => {
            if (seats.length === 0) return;

            const selectedCount = seats.filter((seat) => seat.checked).length;
            const passengerCount = passengerRows().length;
            if (selectedCount === passengerCount) return;

            event.preventDefault();
            updateSeatFeedback();
            seats[0]?.focus();
        });

        renumberRows();
    });
})();
