# Testing ANGIE for Joomla and WordPress

## Installing prerequisites

First run `composer install` on the repo's root to install all PHP dependencies for testing.

You need to have Selenium Server set up and running. The easy way is using the NPM [Selenium Standalone](https://www.npmjs.com/package/selenium-standalone)
package. In a nutshell:
```bash
sudo npm install selenium-standalone@latest -g
sudo selenium-standalone install
selenium-standalone start 
```

**Please note** If you get a cryptic message like `unknown error: call function result missing 'value'`, it means that 
your Selenium install is obsolete. Please update it and run again `sudo selenium-standalone install`, we need Chrome Web
Driver to be >= 2.43.
