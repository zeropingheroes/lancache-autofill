# lancache-autofill
Automatically fill a [lancache](https://github.com/zeropingheroes/lancache) with the content of your choosing, so that subsequent downloads for the same content will be served from the lancache, improving speeds and reducing load on your internet connection.

# Features
* Choose which platform(s) to download an app for
* Use multiple Steam accounts to download apps
* See which apps downloaded successfully
* See which apps failed, and what the error message was

# Requirements
* A working [lancache](https://github.com/zeropingheroes/lancache)
* Ubuntu 16.04 x64, configured to download via the lancache
* Sufficient disk space to (temporarily) store the downloaded content
* PHP 7.0

# Installation
* `git clone https://github.com/zeropingheroes/lancache-autofill.git`
* `cd lancache-autofill`
* `sudo ./install.sh`

# Quick Start
1. Install and initialise SteamCMD:

    `./lancache-autofill steam:initialise`

2. Set the default Steam account to be used when queueing apps for download:

    `nano .env`

3. Search for the apps you wish to download to find their app ID:

	`./lancache-autofill steam:search-apps "team fortress 2"`
	
	`440     Team Fortress 2
	[...]`

4. Queue the app for download by ID:

    `./lancache-autofill steam:queue-app 440`

5. Authorise your Steam account:

    `./lancache-autofill steam:authorise-account`

6. Start downloading items in the download queue:

    `./lancache-autofill steam:start-downloading`

7. View the download queue to see the status of the downloads:

    `./lancache-autofill steam:show-queue`

8. Clear the temporary download location:

    `./lancache-autofill app:initialise-downloads-directory`

# Command Reference
`app:initialise-database`

* Initialise the database.

`app:initialise-downloads-directory`

* Initialise the downloads directory.

`steam:initialise`

* Install and initialise SteamCMD

`steam:authorise-account [account]`

* Authorise a Steam account to allow download of apps in their library.
* If no account is specified, you will be prompted for the username

`steam:queue-app appid [--windows] [--osx] [--linux]`

* Queue a Steam app for downloading.
* Optionally the platform(s) to download can be specified as options
* If no platform option is specified, the Windows version of the app will be queued

`steam:dequeue [--app_id=] [--platform=] [--status=]`

* Dequeue a items from the download queue.
* Optionally specify any combination of app ID, platform and status
* Calling with no arguments clears the queue

`steam:requeue [status=failed] [--message=]`

* Requeue failed and/or completed items in the download queue.
* By default failed items are requeued
* Optionally only requeue items whose message contains the specified value

`steam:search-apps name`

* Search Steam apps by name.

`steam:show-queue [status]`

* Show the Steam app download queue.
* Optionally only show items with specified status
* Available statuses are: queued, failed, completed

`steam:start-downloading`

* Start downloading the Steam apps in the queue.
* The account(s) specified in the queue to download from are checked before any app downloads are attempted

`steam:update-app-list`

* Get the latest list of apps from Steam.

# Limitations & Known Issues
* Steam is the only supported platform currently
* Paid apps can only be cached with access to a Steam account that owns them
* No support for forcing download of 32 bit apps
* Yes, it's written in PHP. No shame.

# Reference

* [SteamCMD Reference](https://developer.valvesoftware.com/wiki/SteamCMD)
* [SteamCMD Commands and Variables](https://github.com/dgibbs64/SteamCMD-Commands-List/blob/master/steamcmdcommands.txt)
* [Laravel Query Builder](https://laravel.com/docs/5.5/queries)
* [Laravel Artisan Console](https://laravel.com/docs/5.5/artisan)
* [Symfony Process Component](http://symfony.com/doc/current/components/process.html)
* [dotenv Reference](https://github.com/vlucas/phpdotenv/blob/master/README.md)
