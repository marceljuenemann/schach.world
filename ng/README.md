# nsv-ng: Next Generation NSV Frontend using Angular

If you are new to Angular, see [https://angular.dev/](angular.dev) for tutorials and setup instructions.

## Development server

For development, run `ng serve` and enable usage in `../.env.local` by setting `NSV_NG_DEV=true`. You can then use the local NSV site as you do for pages that don't have angular components.

## Running unit tests

Run `ng test` to execute the unit tests via [Karma](https://karma-runner.github.io).

## Build

Run `ng build` to build the project. The build artifacts will be checked in under  `../public/core/ng-build/`, so they can be directly served from there. Note that we have a GitHub workflow taking care of building and updating these files, so you don't need to run this step manually.

## Code scaffolding

Run `ng generate component component-name` to generate a new component. You can also use `ng generate directive|pipe|service|class|guard|interface|enum|module`.
