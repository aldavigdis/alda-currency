# Alda Currency

This plugin provides a simple interactive currency conversion block that can be
inserted into your posts, pages and your WordPress FSE layout.

## Develop and Build

This project uses a couple of npm packages and scripts to get things going:

```bash
npm install
npm wp-scripts build
npm build-admin-css
npm build-admin-js
npm build-frontend-component
npm build-frontend-module
```

## Release

Given than you haven't added new files that need to be included in a zip archive
for release, you can run the automatic process for that:

```bash
npm npm run plugin-zip
```

This creates `alda-currency.zip` in the root directory of the project that can
be used.

## Licence

This software is realeased under the GPL-2.0 licence.
