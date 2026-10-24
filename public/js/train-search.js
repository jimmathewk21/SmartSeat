(() => {
    const maximumPassengers = 6;
    const stationInputs = new WeakMap();

    // Enhance server-rendered options; the select still submits the real station ID.
    document.querySelectorAll('[data-station-select]').forEach((select) => {
        const stations = [...select.options].filter((option) => option.value)
            .map((option) => ({ value: option.value, label: option.textContent.trim() }));
        if (!stations.length) return;

        const field = select.closest('.train-station-field');
        const input = document.createElement('input');
        input.id = `${select.id}-search`;
        input.type = 'text';
        input.required = true;
        input.autocomplete = 'off';
        input.placeholder = select.options[0].textContent;
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-describedby', select.getAttribute('aria-describedby'));
        const list = document.createElement('div');
        list.id = `${select.id}-options`;
        list.className = 'train-station-options';
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-label', select.id === 'from_station_id' ? 'Origin stations' : 'Destination stations');
        list.hidden = true;
        input.setAttribute('aria-controls', list.id);
        field.querySelector('label').htmlFor = input.id;
        select.after(input);
        field.append(list);
        select.hidden = true;
        select.required = false;
        stationInputs.set(select, input);
        let matches = [];
        let activeIndex = -1;

        const close = () => {
            list.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            activeIndex = -1;
        };
        const sync = () => {
            input.value = stations.find((station) => station.value === select.value)?.label ?? '';
            input.setCustomValidity(select.value ? '' : 'Choose a station from the suggestions.');
        };
        const choose = (station) => {
            select.value = station.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            close();
            input.focus();
        };
        const highlight = (index) => {
            activeIndex = index;
            [...list.querySelectorAll('[role="option"]')].forEach((option, optionIndex) => {
                option.setAttribute('aria-selected', String(optionIndex === index));
            });
            const active = list.children[index];
            if (active) {
                input.setAttribute('aria-activedescendant', active.id);
                active.scrollIntoView({ block: 'nearest' });
            }
        };
        const render = () => {
            const selectedLabel = stations.find((station) => station.value === select.value)?.label;
            const query = input.value === selectedLabel ? '' : input.value.trim().toLocaleLowerCase();
            matches = stations.filter((station) => station.label.toLocaleLowerCase().includes(query)).slice(0, 20);
            list.replaceChildren();
            activeIndex = -1;
            input.removeAttribute('aria-activedescendant');
            matches.forEach((station, index) => {
                const option = document.createElement('div');
                option.id = `${list.id}-${index}`;
                option.className = 'train-station-option';
                option.setAttribute('role', 'option');
                option.setAttribute('aria-selected', 'false');
                option.textContent = station.label;
                option.addEventListener('pointerdown', (event) => event.preventDefault());
                option.addEventListener('click', () => choose(station));
                list.append(option);
            });
            if (!matches.length) {
                const empty = document.createElement('div');
                empty.className = 'train-station-empty';
                empty.textContent = 'No matching stations. Try another name or code.';
                list.append(empty);
            }
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            const status = select.form.querySelector('[data-station-status]');
            if (status) status.textContent = `${matches.length} matching stations shown. Choose a suggestion to select a station.`;
        };
        input.addEventListener('focus', render);
        input.addEventListener('input', () => {
            select.value = '';
            input.setCustomValidity('Choose a station from the suggestions.');
            render();
        });
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') { event.preventDefault(); close(); return; }
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (list.hidden) render();
                if (matches.length) highlight(event.key === 'ArrowDown'
                    ? (activeIndex + 1) % matches.length
                    : (activeIndex <= 0 ? matches.length - 1 : activeIndex - 1));
            } else if (event.key === 'Enter' && !list.hidden && matches.length) {
                event.preventDefault();
                choose(matches[activeIndex < 0 ? 0 : activeIndex]);
            } else if (event.key === 'Tab') close();
        });
        input.addEventListener('blur', () => window.setTimeout(close, 120));
        select.addEventListener('change', () => { sync(); close(); });
        sync();
    });

    document.querySelectorAll('[data-rail-image]').forEach((image) => {
        image.addEventListener('error', () => {
            image.hidden = true; // The local SVG background keeps captions and layout usable.
        });
        if (image.complete && !image.naturalWidth) image.hidden = true;
    });
    if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08 });
        document.querySelectorAll('[data-reveal]').forEach((element) => {
            element.classList.add('will-reveal');
            observer.observe(element);
        });
    }
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('[data-search-submit]').forEach((button) => {
            button.classList.remove('is-loading');
            button.removeAttribute('aria-busy');
        });
    });

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
            (stationInputs.get(origin) ?? origin).focus();
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
            feedback.classList.toggle('is-valid', selectedCount === passengerCount);
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
