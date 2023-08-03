const defaults = require("@wordpress/scripts/config/webpack.config");
const path = require("path");

module.exports = {
	...defaults,
	entry: {
		blocks: "./gutenberg/block/assets/js/blocks.js",
	},
	output: {
		path: path.resolve(__dirname, "./gutenberg/block/assets/dist"),
		filename: "[name].js",
		publicPath: "/assets",
	},
};