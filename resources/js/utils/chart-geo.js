import ScriptLoader from "./script-loader";

export default function () {
    return ScriptLoader(
        "chart-geo",
        "https://unpkg.com/chartjs-chart-geo@4.2.5/build/index.umd.min.js"
    );
}
