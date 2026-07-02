let googleMapInitialized = !!window.googleMap;
let googleMapResolveInitPromise;
let googleMapRejectInitPromise;
// This promise handles the initialization
// status of the google maps script.
const initGoogleMapPromise = new Promise((resolve, reject) => {
    googleMapResolveInitPromise = resolve;
    googleMapRejectInitPromise = reject;
});

export default function () {
    // If Google Maps already is googleMapInitialized
    // the `initGoogleMapPromise` should get resolved
    // eventually.
    if (googleMapInitialized) return initGoogleMapPromise;

    googleMapInitialized = true;
    // The callback function is called by
    // the Google Maps script if it is
    // successfully loaded.
    window.emitGoogleMapLoaded = () =>
        googleMapResolveInitPromise(window.googleMap);

    // We inject a new script tag into
    // the `<head>` of our HTML to load
    // the Google Maps script.
    const script = document.createElement("script");
    script.async = true;
    script.defer = true;
    script.src = process.env.MIX_GOOGLE_MAP_API_JS_URI;
    script.onerror = googleMapRejectInitPromise;
    document.querySelector("head").appendChild(script);

    return initGoogleMapPromise;
}
