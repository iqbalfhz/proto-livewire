/**
 * Editorial Letterpress — interaction layer.
 *
 * Everything here is idempotent and re-runs after `wire:navigate` swaps the
 * page, since Livewire keeps the document alive between visits.
 */

const reduceMotion = () =>
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/* ── Reveal elements as they scroll into view ───────────────────────────── */
let revealObserver = null;

function initReveal() {
    const targets = document.querySelectorAll("[data-reveal]:not(.is-visible)");

    if (reduceMotion()) {
        targets.forEach((el) => el.classList.add("is-visible"));
        return;
    }

    revealObserver ??= new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                const delay = Number(entry.target.dataset.revealDelay || 0);
                setTimeout(
                    () => entry.target.classList.add("is-visible"),
                    delay,
                );
                revealObserver.unobserve(entry.target);
            });
        },
        { rootMargin: "0px 0px -10% 0px", threshold: 0.05 },
    );

    targets.forEach((el) => revealObserver.observe(el));
}

/* ── Thumbnail that trails the cursor across index rows ─────────────────── */
let previewEl = null;
let previewImg = null;
let pointerX = 0;
let pointerY = 0;
let renderedX = 0;
let renderedY = 0;
let previewRunning = false;

function ensurePreviewElement() {
    if (previewEl?.isConnected) return;

    previewEl = document.createElement("div");
    previewEl.id = "hover-preview";
    previewImg = document.createElement("img");
    previewImg.alt = "";
    previewEl.appendChild(previewImg);
    document.body.appendChild(previewEl);
}

function animatePreview() {
    // Ease toward the pointer so the thumbnail lags slightly behind it.
    renderedX += (pointerX - renderedX) * 0.14;
    renderedY += (pointerY - renderedY) * 0.14;

    if (previewEl) {
        previewEl.style.left = `${renderedX}px`;
        previewEl.style.top = `${renderedY}px`;
    }

    if (previewRunning) requestAnimationFrame(animatePreview);
}

function initHoverPreview() {
    if (reduceMotion() || window.matchMedia("(max-width: 1024px)").matches) {
        return;
    }

    const rows = document.querySelectorAll("[data-preview]:not([data-preview-bound])");
    if (!rows.length) return;

    ensurePreviewElement();

    rows.forEach((row) => {
        row.dataset.previewBound = "true";

        row.addEventListener("mouseenter", () => {
            const src = row.dataset.preview;
            if (!src) return;

            previewImg.src = src;
            previewEl.classList.add("is-active");

            if (!previewRunning) {
                previewRunning = true;
                requestAnimationFrame(animatePreview);
            }
        });

        row.addEventListener("mouseleave", () => {
            previewEl.classList.remove("is-active");
            previewRunning = false;
        });
    });
}

function trackPointer(event) {
    pointerX = event.clientX;
    pointerY = event.clientY;

    if (!previewRunning) {
        renderedX = pointerX;
        renderedY = pointerY;
    }
}

/* ── Live local time in the masthead ────────────────────────────────────── */
let clockTimer = null;

function initClock() {
    const targets = document.querySelectorAll("[data-clock]");

    if (!targets.length) {
        clearInterval(clockTimer);
        clockTimer = null;
        return;
    }

    const paint = () => {
        const nodes = document.querySelectorAll("[data-clock]");

        if (!nodes.length) {
            clearInterval(clockTimer);
            clockTimer = null;
            return;
        }

        const now = new Date().toLocaleTimeString("en-GB", {
            hour: "2-digit",
            minute: "2-digit",
        });

        nodes.forEach((node) => {
            node.textContent = now;
        });
    };

    paint();
    clockTimer ??= setInterval(paint, 15_000);
}

/* ── Duplicate marquee content so the loop has no visible seam ──────────── */
function initMarquee() {
    document
        .querySelectorAll(".marquee:not([data-marquee-bound])")
        .forEach((marquee) => {
            const track = marquee.querySelector(".marquee__track");
            if (!track) return;

            marquee.dataset.marqueeBound = "true";

            const clone = track.cloneNode(true);
            clone.setAttribute("aria-hidden", "true");
            marquee.appendChild(clone);
        });
}

/* ── Progress rule that fills as an article is read ─────────────────────── */
function initReadingProgress() {
    const bar = document.querySelector("[data-reading-progress]");
    if (!bar) return;

    const paint = () => {
        const max = document.body.scrollHeight - window.innerHeight;
        const ratio = max > 0 ? Math.min(window.scrollY / max, 1) : 0;
        bar.style.transform = `scaleX(${ratio})`;
    };

    paint();
    window.addEventListener("scroll", paint, { passive: true });
}

/* ── Boot ───────────────────────────────────────────────────────────────── */
function boot() {
    initReveal();
    initHoverPreview();
    initClock();
    initMarquee();
    initReadingProgress();
}

window.addEventListener("pointermove", trackPointer, { passive: true });
document.addEventListener("DOMContentLoaded", boot);
document.addEventListener("livewire:navigated", boot);
