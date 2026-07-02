const path = require("path");

module.exports = {
    entry: "./src/index.js", // Your entry file
    output: {
        path: path.resolve(__dirname, "dist"),
        filename: "bundle.js", // Output bundle file name
    },
    module: {
        rules: [
            {
                test: /\.js$/, // Apply Babel loader to all .js files
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader",
                    options: {
                        presets: ["@babel/preset-env"],
                    },
                },
            },
        ],
    },
    optimization: {
        minimize: true,
        minimizer: [
            new TerserPlugin({
                extractComments: false,
                terserOptions: {
                    format: {
                        comments: false,
                    },
                },
            }),
        ],
    },
};
