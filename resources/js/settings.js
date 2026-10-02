const app = document.getElementById('app');

let settings = {};

try {
    settings = JSON.parse(app?.dataset.settings ?? '{}');
} catch {
    settings = {};
}

const applySettings = () => {
    if (settings.whatsapp_number) {
        document.querySelectorAll('.envelope-letter a[href*="wa.me"]').forEach((link) => {
            link.href = `https://wa.me/${settings.whatsapp_number}`;
        });
    }

    if (settings.instagram_url) {
        document.querySelectorAll('a[href*="instagram.com"]').forEach((link) => {
            link.href = settings.instagram_url;
        });
    }

    document.querySelectorAll('.footer-brand p').forEach((element) => {
        const brandName = settings.brand_name ?? 'Memoire';
        if (element.textContent !== brandName) element.textContent = brandName;
    });
    document.querySelectorAll('.footer-brand span').forEach((element) => {
        const tagline = settings.brand_tagline ?? 'A home for moments';
        if (element.textContent !== tagline) element.textContent = tagline;
    });

    const initialGreeting = document.querySelector('.guest-chat-messages > .guest-message.admin');
    if (initialGreeting && settings.chat_greeting && initialGreeting.textContent !== settings.chat_greeting) {
        initialGreeting.textContent = settings.chat_greeting;
    }
};

applySettings();

new MutationObserver(applySettings).observe(app, { childList: true, subtree: true });
