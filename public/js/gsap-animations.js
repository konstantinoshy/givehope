/**
 * GiveHope - Animations
 * Powered by GSAP & ScrollTrigger
 */

document.addEventListener("DOMContentLoaded", () => {
    // Make sure GSAP is loaded
    if (typeof gsap === "undefined") {
        console.error("GSAP is not loaded.");
        return;
    }

    // Register ScrollTrigger if available
    if (typeof ScrollTrigger !== "undefined") {
        gsap.registerPlugin(ScrollTrigger);
    }

    /* ==========================================
       1. HERO SECTION ANIMATIONS
       ========================================== */
    const heroTitleSans = document.querySelector(".hero-title .sans-part");
    const heroTitleSerif = document.querySelector(".hero-title .serif-part");
    const heroSubtitle = document.querySelector(".hero-subtitle");
    const heroActions = document.querySelector(".hero-actions");
    const heroTrust = document.querySelector(".hero-trust");

    // Only run if we are on the homepage (hero exists)
    if (heroTitleSans) {
        const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

        tl.fromTo(heroTitleSans,
            { y: 50, opacity: 0 },
            { y: 0, opacity: 1, duration: 1, delay: 0.2 }
        )
            .fromTo(heroTitleSerif,
                { y: 50, opacity: 0 },
                { y: 0, opacity: 1, duration: 1.2 },
                "-=0.6" // overlapping animation
            )
            .fromTo(heroSubtitle,
                { y: 30, opacity: 0 },
                { y: 0, opacity: 1, duration: 1 },
                "-=0.8"
            )
            .fromTo(heroActions,
                { y: 20, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.8 },
                "-=0.6"
            )
            .fromTo(heroTrust,
                { opacity: 0 },
                { opacity: 1, duration: 1 },
                "-=0.4"
            );
    }

    /* ==========================================
       3. DASHBOARD CARDS
       ========================================== */
    const featuresSection = document.querySelector(".how-it-works");

    if (featuresSection) {
        // Shuffler Logic
        const shufflerItems = document.querySelectorAll(".shuffle-item");
        if (shufflerItems.length > 0) {
            let currentIndex = 0;
            setInterval(() => {
                shufflerItems.forEach((item, index) => {
                    item.className = "shuffle-item"; // reset classes
                    if (index === currentIndex) {
                        item.classList.add("active");
                    } else if (index === (currentIndex - 1 + shufflerItems.length) % shufflerItems.length) {
                        item.classList.add("prev");
                    } else {
                        item.classList.add("next");
                    }
                });
                currentIndex = (currentIndex + 1) % shufflerItems.length;
            }, 3000);
        }

        // Telemetry Typewriter Logic
        const typewriterSpan = document.querySelector(".typewriter-text");
        if (typewriterSpan) {
            const strings = window.PlatformTelemetry || [
                "Σύστημα έτοιμο για νέες δωρεές...",
                "Αναμονή για υποστήριξη εράνων..."
            ];
            let currentStringObj = { index: 0, textIndex: 0 };

            function typeText() {
                if (currentStringObj.textIndex < strings[currentStringObj.index].length) {
                    typewriterSpan.textContent += strings[currentStringObj.index].charAt(currentStringObj.textIndex);
                    currentStringObj.textIndex++;
                    setTimeout(typeText, 50 + Math.random() * 50);
                } else {
                    setTimeout(eraseText, 2500); // Wait before erasing
                }
            }

            function eraseText() {
                if (currentStringObj.textIndex > 0) {
                    typewriterSpan.textContent = strings[currentStringObj.index].substring(0, currentStringObj.textIndex - 1);
                    currentStringObj.textIndex--;
                    setTimeout(eraseText, 30);
                } else {
                    currentStringObj.index = (currentStringObj.index + 1) % strings.length;
                    setTimeout(typeText, 500);
                }
            }

            // Start typewriter
            setTimeout(typeText, 1000);
        }
    }

    /* ==========================================
       4. THE MANIFESTO (Philosophy)
       ========================================== */
    const manifestoSection = document.querySelector(".manifesto-section");
    if (manifestoSection && typeof ScrollTrigger !== "undefined") {
        const manifestoQ = document.querySelector(".manifesto-q");
        const manifestoDivider = document.querySelector(".manifesto-divider");
        const manifestoA = document.querySelector(".manifesto-a");
        const manifestoDesc = document.querySelector(".manifesto-desc");

        const manTl = gsap.timeline({
            scrollTrigger: {
                trigger: manifestoSection,
                start: "top 60%", // triggers when top of section hits 60% of viewport
                once: true
            }
        });

        manTl.fromTo(manifestoQ,
            { y: 50, opacity: 0 },
            { y: 0, opacity: 1, duration: 1, ease: "power3.out" }
        )
            .fromTo(manifestoDivider,
                { scaleY: 0, opacity: 0 },
                { scaleY: 1, opacity: 1, duration: 0.8, ease: "power2.out", transformOrigin: "top" },
                "-=0.4"
            )
            .fromTo(manifestoA,
                { y: 50, opacity: 0 },
                { y: 0, opacity: 1, duration: 1, ease: "power3.out" },
                "-=0.4"
            )
            .fromTo(manifestoDesc,
                { y: 30, opacity: 0 },
                { y: 0, opacity: 1, duration: 1, ease: "power3.out" },
                "-=0.6"
            );
    }
});
