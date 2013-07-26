auto
===================

auto is a CLI utility for automating content creation in Moodle and joule.

* Moodlerooms nightly sales data generation
* Create courses with all activities
* Grade student's work as a teacher

Install
-------

The install process assumes that you have setup selenium, zombie or goute to run how you want them to run.

# Download the project from
# Switch to the Moodle branch version
# Install [Composer](http://getcomposer.org/download/) into the root of this project: `curl -s http://getcomposer.org/installer | php`
# Run the following command within the root of this project: `php composer.phar install`
# Add the absolute path to this project's `bin` directory to your `PATH` environment variable: `export PATH="$PATH:/path/to/auto/bin"``
# This project stores its `.Autoconfig.yml` file in your home directory by default.  If you do not have a `HOME` environment variable or wish to customize the location of the config file, set the `autoCONFIGDIR` environment variable: `export autoCONFIGDIR="/Users/bob"`
# (Optional) For color output, you need posix_isatty().  You can get this by installing this MacPort: php5-posix
# Run the following command to configure: `auto config`
# Add a YAML file titled ConfigSites.yml for the sites to src/Auto/Resources/Yaml and should be stuctured like:
    sites:
      alias:
        url: master
        type: [local]
        owner:
          email: admin@localhost.com
          name: "Local Admin"
        sendemail: 0
# Add a YAML file titled ConfigUsers.yml for the users and their passwords for the site under src/Auto/Resources/Yaml and should be stuctured like:
    users:
      user: "password"
      student: "password"
      teacher: "password"
      admin: "password"
# Add a YAML file titled ConfigEmailAdmins.yml for the admin users that need to recieve emails of the logs when failures happen.
    emailadmins:
      admin1:
        email: "email@email.com"
        name: "User's Name"
      admin2:
        email: "email@email.com"
        name: "User's Name"

Updates
-------
# Update your code `git pull`
# Run the following command within the root of this project: `php composer.phar update`
# Run the following command to update configuration: `auto config -u`
# Run ./bin/auto config and update your configuration file


Usage
-----

Open up terminal and type:

`auto help`

See a list of commands:

`auto list`

See help for a command:

`auto help COMMAND`

See help for a command (Take 2):

`auto COMMAND --help`

Run application in shell mode (history, shorter commands and some autocompletion):

`auto-shell`

Release Notes
-------------
Newest first:

TODO
----
