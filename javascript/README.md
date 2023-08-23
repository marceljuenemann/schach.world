# nsv-js

React components for the nsv-online.de frontend.

## Development

1. Run `npm start` to start a development server
1. Set `NSV_JS_DEV=true` in `../env.local` in order to use the dev server from the NSV code
1. Run tests with `npm test` 

## Build

Run `npm run build` to create a new production build in `../public/core/js-build/`. This code
is checked into git in order to simplify the deployment process (the NSV server isn't able
to run the JavaScript build). 
