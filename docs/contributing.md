# Contributing to Guardian
First of all, thanks for considering contributing to Guardian!

There are a few guidelines to follow, which were arbitrarily taken, but we are always open for discussion! If you feel that something is wrong with our project management, feel free to open an issue.


## Security issues
If you find a security issue in the library, **do not open an issue** and contact Mathieu Bour <mathieu.tin.bour@gmail.com> instead.
Since Guardian is a cryptographic library which potentially protects sensitive data, security issues are take very seriously and analyzed in restricted environments.


## Source code
The Guardian source code is mostly located into the `src/` directly.

- PHP code is linted against the [mathrix-education/coding-standard](https://github.com/mathrix-education/coding-standard). If you modified the source code, ensure that the sources are still valid with `vendor/bin/phpcs`
- The library has to be compatible with both Laravel and Lumen frameworks, so you cannot use Laravel-specific features:
    - Facades, like `Auth::` - use dependency injection instead
- We follow [Laravel Support Policy](https://laravel.com/docs/master/releases#support-policy), so everything you write will be tested against the currently supported Laravel version. NB: Lumen follows the same support policy 
- Write tests! Since we reach 100% coverage, we won't allow any commit which provide untested code


## Documentation
The documentation is located in the `docs/` directory and based on [VuePress](https://vuepress.vuejs.org/).

Feel free to editing anything, but remember that:
- The `package.json` `version` field has to be synced with the `composer.json` `version` field
- If you edit the README.md or the contributing.md, run `npm run docs:export` to sync the files with the root files. **Never edit the repository README.md or CONTRIBUTING.md files directly** 
