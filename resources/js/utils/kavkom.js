export default function () {
    return new Promise((resolve, reject) => {
        if (window.KavkomSDK) {
            resolve();
            return;
        }

        const existingScript = document.querySelector('script[data-kavkom-sdk]');
        if (existingScript) {
            existingScript.addEventListener("load", resolve, { once: true });
            existingScript.addEventListener("error", () => reject(new Error("Kavkom SDK unavailable")), { once: true });
            return;
        }

        const script = document.createElement("script");
        script.async = true;
        script.defer = true;
        script.setAttribute("data-kavkom-sdk", "true");
        script.src = "https://cdn.kavkom.com/sdk/kavkom-sdk.js";
        script.onload = resolve;
        script.onerror = () => {
            console.error("Kavkom SDK script could not be loaded from the provided URL.");
            reject(new Error("Kavkom SDK unavailable"));
        };
        document.querySelector("head").appendChild(script);
    });
}
