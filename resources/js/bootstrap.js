import "bootstrap";
import ApiService from "@/apis/api.service";
import EventBus from "@/utils/event-bus";

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.headers.common["Accept"] = "application/json";
window.axios.defaults.withCredentials = true;

/**
 * Date
 */
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";

window.dayjs = dayjs;
// Load the relativeTime plugin
window.dayjs.extend(relativeTime);
/**
 * Remove accent in string
 * and make it lower case
 * @param {*} string
 * @returns
 */
window.removeStringAccent = function (string) {
    return ("" + string)
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim();
};

/**
 *
 * @param {*} date
 * @returns
 */
window.dateToString = function (date) {
    var tzoffset = new Date().getTimezoneOffset() * 60000;
    return new Date(date - tzoffset)
        .toISOString()
        .substring(0, 19)
        .replace("T", " ");
};

window.deviceType = function () {
    const userAgent = navigator.userAgent;

    if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(userAgent)) {
        return "tablet";
    } else if (
        /Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(
            userAgent
        )
    ) {
        return "mobile";
    }

    return "desktop";
};

window.hcConfirm = function (
    message,
    okCallback,
    cancelCallback,
    title,
    okButton,
    cancelButton
) {
    EventBus.emit(
        "confirm",
        message,
        okCallback,
        cancelCallback,
        title,
        okButton,
        cancelButton
    );
};

window.flash = function (message) {
    EventBus.emit("flash", message);
};

window.flashInfo = function (message) {
    EventBus.emit("flash", {
        ...message,
        ...{
            backgroundColor: "#009df1",
            color: "#FFFFFF",
            icon: "fa fa-info",
        },
    });
};

window.flashWarning = function (message) {
    EventBus.emit("flash", {
        ...message,
        ...{
            backgroundColor: "#df9714",
            color: "#FFFFFF",
            icon: "fa fa-exclamation-triangle",
        },
    });
};

window.flashError = function (message) {
    EventBus.emit("flash", {
        ...message,
        ...{
            backgroundColor: "#cc5338",
            color: "#FFFFFF",
            icon: "fa fa-ban",
        },
    });
};

window.tuto = function (element, html, name, key, timeout) {
    EventBus.emit("tuto", element, html, name, key, timeout);
};

window.tooltip = function (element, text, position) {
    EventBus.emit("tooltip", element, text, position);
};

/**
 * Init API service
 */
ApiService.init();

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
