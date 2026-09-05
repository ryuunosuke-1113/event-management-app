//
console.log("app.js loaded");
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import "./echo";

if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker
            .register("/sw.js")
            .then(() => {
                console.log("Service Worker registered");
            })
            .catch((error) => {
                console.error("Service Worker registration failed:", error);
            });
    });
}
console.log("app.js loaded");

if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker
            .register("/sw.js")
            .then(() => {
                console.log("Service Worker registered");
            })
            .catch((error) => {
                console.error("Service Worker registration failed:", error);
            });
    });
}
let deferredPrompt = null;

window.addEventListener("beforeinstallprompt", (event) => {
    event.preventDefault();

    deferredPrompt = event;

    const installButton = document.getElementById("pwa-install-button");

    if (installButton) {
        installButton.style.display = "inline-block";
    }
});

document.addEventListener("click", async (event) => {
    if (event.target.id !== "pwa-install-button") {
        return;
    }

    if (!deferredPrompt) {
        return;
    }

    deferredPrompt.prompt();

    await deferredPrompt.userChoice;

    deferredPrompt = null;

    event.target.style.display = "none";
});

const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);

const isStandalone =
    window.matchMedia("(display-mode: standalone)").matches ||
    window.navigator.standalone === true;

if (isIos && !isStandalone) {
    const iosGuide = document.getElementById("ios-install-guide");

    if (iosGuide) {
        iosGuide.style.display = "block";
    }
}
