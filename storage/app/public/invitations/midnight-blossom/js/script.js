/* =========================================
   ELEMENTS
========================================= */

const body =
    document.body;

const opening =
    document.getElementById(
        "opening"
    );

const openButton =
    document.getElementById(
        "openInvitation"
    );

const OPENING_TRANSITION_MS =
    1400;



/* =========================================
   OPEN INVITATION
========================================= */

openButton.addEventListener(

    "click",

    () => {

        openButton.disabled = true;

        /*
         * Opening fade out
         */

        opening.classList.add(
            "hide"
        );


        /*
         * Main page fade in
         */

        body.classList.add(
            "page-ready"
        );


        /*
         * Unlock scroll
         */

        setTimeout(

            () => {

                body.classList.remove(
                    "locked"
                );

            },

            OPENING_TRANSITION_MS

        );

    }

);

document.addEventListener("DOMContentLoaded", () => {

    // =========================
    // SMOOTH SCROLL
    // =========================
    const scrollLinks = document.querySelectorAll('a[href^="#"]');

    scrollLinks.forEach(link => {
        link.addEventListener("click", function (e) {

            const targetId = this.getAttribute("href");

            if (!targetId || targetId === "#") return;

            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                e.preventDefault();

                targetElement.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            }

        });
    });


    // =========================
    // SCROLL REVEAL ANIMATION
    // =========================
    const revealElements = document.querySelectorAll(".reveal");

    if (!("IntersectionObserver" in window)) {
        revealElements.forEach(element => {
            element.classList.add("active");
        });
        return;
    }

    const revealObserver = new IntersectionObserver(
        (entries, observer) => {

            entries.forEach(entry => {

                if (entry.isIntersecting) {

                    entry.target.classList.add("active");

                    observer.unobserve(entry.target);
                }

            });

        },
        {
            threshold: 0.18
        }
    );

    revealElements.forEach(element => {
        revealObserver.observe(element);
    });

});


/* =========================================
   THE BIG DAY COUNTDOWN
========================================= */

const weddingCountdownTarget =
    new Date("2027-01-18T08:00:00+07:00").getTime();

const countdownFields = {
    days: document.getElementById("countdownDays"),
    hours: document.getElementById("countdownHours"),
    minutes: document.getElementById("countdownMinutes"),
    seconds: document.getElementById("countdownSeconds")
};

const updateWeddingCountdown = () => {
    if (!countdownFields.days) return;

    const remaining = Math.max(
        weddingCountdownTarget - Date.now(),
        0
    );

    const days = Math.floor(remaining / 86400000);
    const hours = Math.floor((remaining % 86400000) / 3600000);
    const minutes = Math.floor((remaining % 3600000) / 60000);
    const seconds = Math.floor((remaining % 60000) / 1000);

    countdownFields.days.textContent = String(days).padStart(2, "0");
    countdownFields.hours.textContent = String(hours).padStart(2, "0");
    countdownFields.minutes.textContent = String(minutes).padStart(2, "0");
    countdownFields.seconds.textContent = String(seconds).padStart(2, "0");
};

updateWeddingCountdown();
setInterval(updateWeddingCountdown, 1000);


/* =========================================
   WEDDING DAY TIMELINE PROGRESS
========================================= */
(() => {
    const timelineSection = document.querySelector(".wedding-timeline-section");
    const timelineProgress = document.querySelector(".timeline-progress");
    const timelineEvents = document.querySelectorAll(".timeline-event");

    if (!timelineSection || !timelineProgress || !timelineEvents.length) return;

    const updateTimelineProgress = () => {
        const rect = timelineSection.getBoundingClientRect();
        const triggerPoint = window.innerHeight * 0.63;
        const travelled = triggerPoint - rect.top;
        const progress = Math.max(0, Math.min(1, travelled / rect.height));

        timelineProgress.style.height = `${progress * 100}%`;
    };

    const eventObserver = new IntersectionObserver(
        entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("timeline-active");
                    eventObserver.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.28 }
    );

    timelineEvents.forEach(event => eventObserver.observe(event));
    window.addEventListener("scroll", updateTimelineProgress, { passive: true });
    window.addEventListener("resize", updateTimelineProgress);
    updateTimelineProgress();
})();


/* =========================================
   KINDLY REPLY — RSVP FORM & GUESTBOOK
========================================= */
(() => {
    const form = document.getElementById("rsvpForm");
    const guestCountField = document.getElementById("rsvpGuestCount");
    const guestCount = document.getElementById("rsvpGuests");
    const status = document.getElementById("rsvpFormStatus");
    const submitButton = document.getElementById("rsvpSubmit");
    const responseList = document.getElementById("rsvpResponseList");
    const emptyState = document.getElementById("rsvpEmptyState");
    const loadMoreButton = document.getElementById("rsvpLoadMore");
    const counts = {
        attending: document.getElementById("rsvpAttendingCount"),
        unable: document.getElementById("rsvpUnableCount"),
        total: document.getElementById("rsvpTotalCount")
    };

    if (!form || !responseList) return;

    const storageKey = "weddingBlueRsvpResponses";
    const endpoint = document.querySelector('meta[name="rsvp-endpoint"]')?.content || "/rsvp";
    let responses = [];
    let visibleResponses = 6;

    try { responses = JSON.parse(localStorage.getItem(storageKey) || "[]"); } catch { responses = []; }

    const setError = (field, message = "") => {
        const target = form.querySelector(`[data-error="${field}"]`);
        if (target) target.textContent = message;
    };

    const updateGuestCountVisibility = () => {
        const attendance = form.querySelector('input[name="attendance"]:checked')?.value;
        const isAttending = attendance === "attending";
        guestCountField.classList.toggle("is-visible", isAttending);
        guestCountField.setAttribute("aria-hidden", String(!isAttending));
        if (!isAttending) guestCount.value = "";
    };

    const formatDate = date => new Intl.DateTimeFormat("en-GB", {
        day: "2-digit", month: "long", year: "numeric"
    }).format(new Date(date));

    const renderResponses = () => {
        const sorted = [...responses].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        const shown = sorted.slice(0, visibleResponses);
        responseList.replaceChildren();

        shown.forEach(response => {
            const card = document.createElement("article");
            const isAttending = response.attendance === "attending";
            card.className = "rsvp-response-card";

            const name = document.createElement("h4");
            name.textContent = response.guest_name;
            const badge = document.createElement("span");
            badge.className = `rsvp-badge${isAttending ? "" : " not-attending"}`;
            badge.textContent = isAttending
                ? `ATTENDING${response.guest_count ? ` · ${response.guest_count} GUEST${Number(response.guest_count) > 1 ? "S" : ""}` : ""}`
                : "UNABLE TO ATTEND";
            card.append(name, badge);

            if (response.message) {
                const message = document.createElement("p");
                message.textContent = `“${response.message}”`;
                card.append(message);
            }
            const time = document.createElement("time");
            time.dateTime = response.created_at;
            time.textContent = formatDate(response.created_at);
            card.append(time);
            responseList.append(card);
        });

        emptyState.hidden = responses.length > 0;
        loadMoreButton.hidden = responses.length <= visibleResponses;
        const attending = responses.filter(response => response.attendance === "attending").length;
        counts.attending.textContent = attending;
        counts.unable.textContent = responses.length - attending;
        counts.total.textContent = responses.length;
    };

    form.querySelectorAll('input[name="attendance"]').forEach(input => {
        input.addEventListener("change", () => { updateGuestCountVisibility(); setError("attendance"); });
    });
    form.querySelector("#rsvpName").addEventListener("input", () => setError("guest_name"));
    guestCount.addEventListener("change", () => setError("guest_count"));
    loadMoreButton.addEventListener("click", () => { visibleResponses += 6; renderResponses(); });

    form.addEventListener("submit", async event => {
        event.preventDefault();
        const data = Object.fromEntries(new FormData(form));
        const attendance = data.attendance;
        let valid = true;
        setError("guest_name"); setError("attendance"); setError("guest_count");

        if (!data.guest_name?.trim()) { setError("guest_name", "Please enter your name."); valid = false; }
        if (!attendance) { setError("attendance", "Please select your attendance."); valid = false; }
        if (attendance === "attending" && !data.guest_count) { setError("guest_count", "Please select the number of guests."); valid = false; }
        if (!valid) return;

        const payload = {
            guest_name: data.guest_name.trim(), attendance,
            guest_count: attendance === "attending" ? Number(data.guest_count) : null,
            message: data.message?.trim() || null
        };
        submitButton.disabled = true;
        submitButton.querySelector("span").textContent = "SENDING...";
        status.textContent = ""; status.classList.remove("is-error");

        let savedResponse = { ...payload, created_at: new Date().toISOString() };
        try {
            const result = await fetch(endpoint, {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "" },
                body: JSON.stringify(payload)
            });
            if (!result.ok) throw new Error("RSVP endpoint unavailable");
            const body = await result.json();
            savedResponse = body.rsvp || savedResponse;
        } catch {
            // The static invitation remains usable before a Laravel endpoint is connected.
        }

        responses.unshift(savedResponse);
        localStorage.setItem(storageKey, JSON.stringify(responses));
        visibleResponses = Math.max(visibleResponses, 6);
        renderResponses();
        form.reset(); updateGuestCountVisibility();
        status.textContent = attendance === "attending"
            ? "Your RSVP has been received. We can't wait to celebrate with you."
            : "Thank you for letting us know. You will be part of our special day in our hearts.";
        submitButton.querySelector("span").textContent = "SENT WITH LOVE ♡";
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.querySelector("span").textContent = "SEND RSVP";
        }, 1800);
    });

    updateGuestCountVisibility();
    renderResponses();

    // When this markup is served by Laravel, refresh the guestbook from the API.
    if (document.querySelector('meta[name="csrf-token"], meta[name="rsvp-endpoint"]')) {
        fetch(endpoint, { headers: { "Accept": "application/json" } })
            .then(result => result.ok ? result.json() : Promise.reject())
            .then(body => {
                if (Array.isArray(body.rsvps)) {
                    responses = body.rsvps;
                    localStorage.setItem(storageKey, JSON.stringify(responses));
                    renderResponses();
                }
            })
            .catch(() => {});
    }
})();


/* =========================================
   WORDS OF LOVE — DECORATIVE PARALLAX
========================================= */
(() => {
    const noteSection = document.querySelector(".gentle-note-section");
    if (!noteSection || window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    const florals = noteSection.querySelectorAll(".gentle-note-floral");
    const monogram = noteSection.querySelector(".gentle-note-monogram");

    const updateGentleNoteParallax = () => {
        const rect = noteSection.getBoundingClientRect();
        const amount = Math.max(-1, Math.min(1, (window.innerHeight / 2 - (rect.top + rect.height / 2)) / window.innerHeight));

        florals.forEach((floral, index) => {
            floral.style.translate = `0 ${amount * (index ? -17 : 17)}px`;
        });
        if (monogram) monogram.style.marginTop = `${amount * 12}px`;
    };

    window.addEventListener("scroll", updateGentleNoteParallax, { passive: true });
    updateGentleNoteParallax();
})();


/* =========================================
   US LATELY — LIGHTBOX & SUBTLE PARALLAX
========================================= */
(() => {
    const photos = Array.from(document.querySelectorAll(".memory-gallery .memory-photo"));
    const lightbox = document.getElementById("memoryLightbox");
    const lightboxImage = document.getElementById("memoryLightboxImage");
    const lightboxCaption = document.getElementById("memoryLightboxCaption");
    const lightboxCounter = document.getElementById("memoryLightboxCounter");
    const closeButton = document.querySelector(".memory-lightbox-close");
    const previousButton = document.querySelector(".memory-lightbox-prev");
    const nextButton = document.querySelector(".memory-lightbox-next");
    const memorySection = document.querySelector(".memory-journal-section");

    if (!photos.length || !lightbox || !lightboxImage) return;

    let activeIndex = 0;

    const renderLightbox = index => {
        activeIndex = (index + photos.length) % photos.length;
        const photo = photos[activeIndex];
        const image = photo.querySelector("img");
        lightboxImage.src = image.currentSrc || image.src;
        lightboxImage.alt = image.alt;
        lightboxCaption.textContent = photo.dataset.caption || "Our memory";
        lightboxCounter.textContent = `${activeIndex + 1} / ${photos.length}`;
    };

    const openLightbox = index => {
        renderLightbox(index);
        lightbox.classList.add("is-open");
        lightbox.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
    };

    const closeLightbox = () => {
        lightbox.classList.remove("is-open");
        lightbox.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "";
    };

    photos.forEach((photo, index) => {
        photo.addEventListener("click", () => openLightbox(index));
    });

    previousButton.addEventListener("click", () => renderLightbox(activeIndex - 1));
    nextButton.addEventListener("click", () => renderLightbox(activeIndex + 1));
    closeButton.addEventListener("click", closeLightbox);
    lightbox.addEventListener("click", event => {
        if (event.target === lightbox) closeLightbox();
    });

    document.addEventListener("keydown", event => {
        if (!lightbox.classList.contains("is-open")) return;
        if (event.key === "Escape") closeLightbox();
        if (event.key === "ArrowLeft") renderLightbox(activeIndex - 1);
        if (event.key === "ArrowRight") renderLightbox(activeIndex + 1);
    });

    if (memorySection && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        const florals = memorySection.querySelectorAll(".memory-floral");
        const parallax = () => {
            const rect = memorySection.getBoundingClientRect();
            const amount = Math.max(-1, Math.min(1, (window.innerHeight / 2 - (rect.top + rect.height / 2)) / window.innerHeight));
            florals.forEach((floral, index) => {
                floral.style.translate = `0 ${amount * (index ? -20 : 20)}px`;
            });
        };
        window.addEventListener("scroll", parallax, { passive: true });
        parallax();
    }
})();
