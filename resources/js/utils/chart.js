import ScriptLoader from "./script-loader";

export default function () {
    return ScriptLoader(
        "chart",
        "https://unpkg.com/chart.js@4.4.0/dist/chart.umd.js"
    );
}
