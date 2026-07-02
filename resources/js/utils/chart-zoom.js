import ScriptLoader from "./script-loader";

export default function () {
    return ScriptLoader(
        "chart-zoom",
        "https://unpkg.com/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js"
    );
}
