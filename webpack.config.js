const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const RemoveEmptyScriptsPlugin = require("webpack-remove-empty-scripts");
const mode = process.argv.includes("production") ? "production" : "development";
const { ProvidePlugin } = require("webpack");
const BrowserSyncPlugin = require("browser-sync-webpack-plugin");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const globImporter = require("node-sass-glob-importer");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");

/**
 * Webpack configuration
 * @type {import('webpack').Configuration}
 * @see https://webpack.js.org/configuration/
 */
module.exports = {
  devtool: mode === "development" ? "source-map" : false,
  mode: mode,
  entry: {
    "member-directory": "./src/js/member-directory/MemberDirectory.js",
    "dashboard": "./src/js/dashboard/Dashboard.js",
    "admin-users": "./src/js/admin/users.js",
  },
  output: {
    filename: "js/[name].js",
    path: path.resolve(__dirname, "assets"),
    clean: true,
  },
  resolve: {
    alias: {
      '@node': path.resolve(__dirname, 'node_modules'),
    },
  },
  plugins: [
    // Browser Sync
    new BrowserSyncPlugin(
      {
        port: 3005,
        proxy: `localhost`,
        notify: false,
        files: [
          "./*.php",
          "./includes/**/*.php",
          "./templates/**/*.php",
          "./assets/**/*.{js,css}",
        ],
        injectChanges: true,
      },
      {
        reload: false,
        injectCss: true,
      }
    ),
    // Copy images to assets folder
    new CopyWebpackPlugin({
      patterns: [
        {
          from: "src/images",
          to: "images",
          globOptions: {
            ignore: ["**/*.gitkeep"],
          },
          noErrorOnMissing: true,
        },
      ],
    }),
    // Remove empty scripts for style entry
    new RemoveEmptyScriptsPlugin(),
    // Extract CSS from commonjs into seperate file
    new MiniCssExtractPlugin({
      filename: "css/[name].css",
    }),
    // Provide plugin globally
    new ProvidePlugin({
      $: "jquery",
      jQuery: "jquery",
    }),
  ],
  externals: {
    jquery: "jQuery",
  },
  module: {
    rules: [
      // Style
      {
        test: /\.s?css$/i,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: "css-loader",
            options: {
              // url: false, // Disable url() resolving
            },
          },
          "postcss-loader",
          "resolve-url-loader",
          {
            loader: "sass-loader",
            options: {
              sourceMap: true,
              sassOptions: {
                importer: globImporter(),
              },
            },
          },
        ],
      },
      // Fonts
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/i,
        type: "asset/resource",
        generator: {
          filename: "./fonts/[name][ext]",
        },
      },
      // Images
      {
        test: /\.(png|jpe?g|gif|svg)$/i,
        type: "asset/resource",
        generator: {
          filename: "./images/[name][ext]",
        },
      },
    ],
  },
  optimization: {
    minimizer: [
      // Minimize style
      new CssMinimizerPlugin({
        minimizerOptions: {
          preset: [
            "default",
            {
              discardComments: { removeAll: true },
            },
          ],
        },
      }),
      // Minimize js
      new TerserPlugin({
        terserOptions: {
          format: {
            comments: false,
          },
        },
        extractComments: false,
      }),
    ],
  },
  performance: {
    // Control which files type for performance hints
    assetFilter: function (assetFilename) {
      return assetFilename.endsWith(".js") || assetFilename.endsWith(".css");
    },
  },
};
