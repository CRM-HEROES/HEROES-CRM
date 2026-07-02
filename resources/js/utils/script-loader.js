window.JsScriptLoader = {};

export default function (name, url) {
    if (!JsScriptLoader[name]) {
        JsScriptLoader[name] = {
            initialized: false,
            promiseResolve: null,
            promiseReject: null,
        };

        JsScriptLoader[name].promise = new Promise((resolve, reject) => {
            JsScriptLoader[name].promiseResolve = resolve;
            JsScriptLoader[name].promiseReject = reject;
        });
    }

    const loader = JsScriptLoader[name];

    // If js already is initialized
    // the `promise` should get resolved
    // eventually.
    if (loader.initialized) {
        return loader.promise;
    }

    loader.initialized = true;

    // We inject a new script tag into
    // the `<head>` of our HTML to load
    // the Google Maps script.
    const script = document.createElement("script");
    script.async = true;
    script.defer = true;
    script.src = url;
    script.onload = loader.promiseResolve;
    script.onerror = loader.promiseReject;
    document.querySelector("head").appendChild(script);

    return loader.promise;
}
