console.log("app.js loaded");

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

let deferredInstallPrompt = null;

window.addEventListener("DOMContentLoaded", () => {
    const guide = document.getElementById("install-app-guide");
    const installButton = document.getElementById("install-app-button");
    const iosGuideButton = document.getElementById("show-ios-install-guide");
    const iosGuide = document.getElementById("ios-install-guide");

    // この案内がないページでは何もしない
    if (!guide) {
        return;
    }

    // すでにPWAとして起動しているか
    const isStandalone =
        window.matchMedia("(display-mode: standalone)").matches ||
        window.navigator.standalone === true;

    if (isStandalone) {
        guide.style.display = "none";
        return;
    }

    // iPhone / iPad 判定
    const isIOS =
        /iPhone|iPad|iPod/i.test(navigator.userAgent) ||
        (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);

    if (isIOS) {
        guide.style.display = "block";
        iosGuideButton.style.display = "inline-block";

        iosGuideButton.addEventListener("click", () => {
            const isOpen = iosGuide.style.display !== "none";
            iosGuide.style.display = isOpen ? "none" : "block";
        });
    }
});

window.addEventListener("beforeinstallprompt", (event) => {
    event.preventDefault();

    deferredInstallPrompt = event;

    const guide = document.getElementById("install-app-guide");
    const installButton = document.getElementById("install-app-button");

    if (!guide || !installButton) {
        return;
    }

    guide.style.display = "block";
    installButton.style.display = "inline-block";
});

document.addEventListener("click", async (event) => {
    if (event.target.id !== "install-app-button") {
        return;
    }

    if (!deferredInstallPrompt) {
        return;
    }

    deferredInstallPrompt.prompt();

    await deferredInstallPrompt.userChoice;

    deferredInstallPrompt = null;

    const guide = document.getElementById("install-app-guide");

    if (guide) {
        guide.style.display = "none";
    }
});

window.addEventListener("appinstalled", () => {
    deferredInstallPrompt = null;

    const guide = document.getElementById("install-app-guide");

    if (guide) {
        guide.style.display = "none";
    }
});
