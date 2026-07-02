import ScriptLoader from "./script-loader";

export default function () {
    return ScriptLoader(
        "chart-funnel",
        "https://unpkg.com/chartjs-chart-funnel@4.2.4/build/index.umd.min.js"
    );
}
