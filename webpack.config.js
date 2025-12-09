const Path = require('path');
const { JavascriptWebpackConfig } = require('@silverstripe/webpack-config');

const ENV = process.env.NODE_ENV;
const PATHS = {
	MODULES: 'node_modules',
	ROOT: Path.resolve(),
	SRC: Path.resolve('client/src'),
	DIST: Path.resolve('client/dist'),
};

const config = [
	// Main bundle
	new JavascriptWebpackConfig('tinymce', PATHS, 'flxlabs/silverstripe-dataobject-links')
		.setEntry({
			'TinyMCE_sslink-dataobject': `${PATHS.SRC}/TinyMCE_sslink-dataobject.jsx`,
			'sslink-dataobject-transforms': `${PATHS.SRC}/sslink-dataobject-transforms.js`,
		})
		.getConfig(),
];

module.exports = config;
