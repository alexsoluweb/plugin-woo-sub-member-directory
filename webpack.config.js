const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const RemoveEmptyScriptsPlugin = require("webpack-remove-empty-scripts");
const { ProvidePlugin } = require("webpack");
const BrowserSyncPlugin = require("browser-sync-webpack-plugin");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const globImporter = require("node-sass-glob-importer");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const { WebpackManifestPlugin } = require("webpack-manifest-plugin");
const mode = process.argv.includes("production") ? "production" : "development";
const currentDirectory = path.basename(__dirname);
const parentDirectory = path.basename(path.resolve(__dirname, ".."));
const publicPath = "/wp-content/" + parentDirectory + "/" + currentDirectory + "/assets/";

/**
 * Webpack configuration
 * @type {import("webpack").Configuration}
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
    publicPath: publicPath,
    clean: true,
  },
  resolve: {
    alias: {
      '@node': path.resolve(__dirname, 'node_modules'),
      '@src': path.resolve(__dirname, 'src'),
    },
  },
  plugins: [
    new WebpackManifestPlugin({
      fileName: "manifest.json",
    }),
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
    new RemoveEmptyScriptsPlugin(),
    new MiniCssExtractPlugin({
      filename: "css/[name].css",
    }),
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
      {
        test: /\.s?css$/i,
        use: [
          MiniCssExtractPlugin.loader,
          "css-loader",
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
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/i,
        type: "asset/resource",
        generator: {
          filename: "./fonts/[name][ext]",
        },
      },
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
    assetFilter: function (assetFilename) {
      return assetFilename.endsWith(".js") || assetFilename.endsWith(".css");
    },
  },
};
