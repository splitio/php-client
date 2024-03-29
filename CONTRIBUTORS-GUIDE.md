# Contributing to the Split PHP SDK

Split SDK is an open source project and we welcome feedback and contribution. The information below describes how to build the project with your changes, run the tests, and send the Pull Request(PR).

## Development

### Development process

1. Fork the repository and create a topic branch from `develop` branch. Please use a descriptive name for your branch.
2. While developing, use descriptive messages in your commits. Avoid short or meaningless sentences like "fix bug".
3. Make sure to add tests for both positive and negative cases.
4. Run the build script and make sure it runs with no errors.
5. Run all tests and make sure there are no failures.
6. `git push` your changes to GitHub within your topic branch.
7. Open a Pull Request(PR) from your forked repo and into the `develop` branch of the original repository.
8. When creating your PR, please fill out all the fields of the PR template, as applicable, for the project.
9. Check for conflicts once the pull request is created to make sure your PR can be merged cleanly into `develop`.
10. Keep an eye out for any feedback or comments from Split's SDK team.

### Building the SDK

- `composer install`

### Running tests

- `vendor/bin/phpunit -c phpunit.xml.dist --testsuite integration`

### Linting and other useful checks

- `vendor/bin/phpcs --ignore=functions.php --standard=PSR2 src/`

## Contact

If you have any other questions or need to contact us directly in a private manner send us a note at sdks@split.io
