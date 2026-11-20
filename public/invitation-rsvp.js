(() => {
    const endpoint = document.querySelector('meta[name="rsvp-endpoint"]')?.content;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const guestName = document.querySelector('meta[name="invitation-guest-name"]')?.content;

    if (!endpoint) {
        return;
    }

    const attendanceValue = (value) => {
        const normalized = String(value || '').toLowerCase();

        if (/attending|\byes\b|hadir|joyfully|senang hati/.test(normalized)) {
            return 'attending';
        }

        if (/declined|not_attending|\bno\b|tidak hadir|cannot|unable|maaf/.test(normalized)) {
            return 'declined';
        }

        return null;
    };

    const fieldValue = (data, names) => names.map((name) => data.get(name)).find((value) => value !== null && value !== '');

    const isResponseForm = (form) => {
        const identifier = `${form.id} ${form.className}`.toLowerCase();

        return identifier.includes('rsvp') || identifier.includes('wish');
    };

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !isResponseForm(form)) {
            return;
        }

        const data = new FormData(form);
        const attendance = attendanceValue(fieldValue(data, ['attendance', 'attend', 'status']));
        const message = fieldValue(data, ['message', 'wish', 'wishes', 'note']);
        const guestCount = fieldValue(data, ['guest_count', 'guests', 'party_size']);

        if (!attendance && !String(message || '').trim()) {
            return;
        }

        try {
            await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({
                    attendance,
                    guest_count: guestCount ? Number.parseInt(guestCount, 10) : null,
                    message: String(message || '').trim() || null,
                }),
            });
        } catch {
            // Tampilan template tetap menangani feedback-nya sendiri saat jaringan bermasalah.
        }
    }, { capture: true });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form').forEach((form) => {
            if (!isResponseForm(form)) {
                return;
            }

            const nameInput = form.querySelector('[name="guest_name"], [name="name"]');
            if (nameInput && guestName) {
                nameInput.value = guestName;
                nameInput.readOnly = true;
            }

        });
    });
})();
