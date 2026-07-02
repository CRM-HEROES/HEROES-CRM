import ScriptLoader from "./script-loader";

export default function () {
    return ScriptLoader(
        "chart-adapter-date-fns",
        "https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"
    );
}
