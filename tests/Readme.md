# Inertia.js CCMS Adapter Unit Testing
This directory is only meant to be included in a development environment for the Inertia-Concrete adapter. It is **not** needed to run the release version of the adapter.

## Installation
This directory should be located inside the "packages" directory of a Concrete installation, adjacent to the "inertia_ccms_adapter" folder.

## Running Tests
After installing Concrete and running `composer install` at the document root, run these tests with:  
`cd packages/tests`  
`../../concrete/vendor/bin/phpunit --verbose tests`

### Test Scope
These unit tests are meant to evaluate the functionality of the classes within the Concrete CMS / Inertia.js adapter package. To test other Concrete CMS functionality, use the test suite located in the 'tests' directory of the [Concrete CMS git repository](https://github.com/concretecms/concretecms).

Additional unit tests will be developed in the future to cover integration with Concrete CMS routing and pages, namely testing how routing is handled in between Concrete and Inertia (in the cases where Concrete pages exist at URLs)