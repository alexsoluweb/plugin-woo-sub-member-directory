# Default target
.DEFAULT_GOAL := zip

# Exclusions as a variable
EXCLUSIONS := \
	"*.git*" \
	".gitignore" \
	".env" \
	".prettierrc" \
	".eslintrc.js" \
	".eslintignore" \
	".stylelintrc.js" \
	".editorconfig" \
	".vscode/*" \
	"tailwind.config.js" \
	"package-lock.json" \
	"package.json" \
	"MANIFEST_FILE" \
	"makefile" \
	"webpack.config.js" \
	"src/*" \
	"node_modules/*" \
	"postcss.config.js" \
	"README.md" \
	"composer.json" \
	"composer.lock" \
	"bud.config.js" \
	"ressources/*" \
	"jsconfig.json" \
	"yarn.lock"

# Rule to run npm build
build:
	npm run build

# Rule to create a zip file, depends on the build rule
zip: build
	zip -r `basename $$PWD`.zip . -x $(EXCLUSIONS)
