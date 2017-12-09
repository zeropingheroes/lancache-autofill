# lancache-autofill
Automatically fill a [lancache](https://github.com/zeropingheroes/lancache) with the content of your choosing, so that subsequent downloads for the same content will be served from the lancache, improving speeds and reducing load on your internet connection.

# Features
* Download the top popular free and/or paid apps on Steam
* Download a specific app by ID
* Choose which platform(s) apps should be downloaded for
* Download one (or more) users recently played apps
* Download using multiple Steam accounts
* Check which apps will be downloaded
* Alter the download queue as needed
* Start downloading process and leave to run unattended
* Check which apps downloaded successfully
* Check which apps failed to download, and why
* Retry downloading of some or all failed apps
* Clear the temporary download directory 


# Requirements
* A working [lancache](https://github.com/zeropingheroes/lancache)
* Ubuntu 16.04 x64, configured to download via the lancache
* Sufficient disk space to (temporarily) store the downloaded content
* Dependencies detailed in *Installation* section

# Installation
1. `apt update && apt install -y lib32gcc1 lib32stdc++6 lib32tinfo5 lib32ncurses5 php7.0-cli php7.0-mbstring php7.0-sqlite php7.0-bcmath composer expect zip unzip`
2. `git clone https://github.com/zeropingheroes/lancache-autofill.git && cd lancache-autofill`
3. `./install.sh`
4. Get a Steam API key from http://steamcommunity.com/dev/apikey and add it to the `.env` file

# Usage

    $ ./lancache-autofill
    
    Usage:
    
        lancache-autofill app:initialise-database
        lancache-autofill app:initialise-downloads-directory

        lancache-autofill steam:initialise
        lancache-autofill steam:authorise-account [<account>]
        lancache-autofill steam:update-app-list
        
	    lancache-autofill steam:search-apps <app name>
        lancache-autofill steam:queue-app <app id> [<app id>...] [--windows=true] [--osx] [--linux]
        lancache-autofill steam:queue-popular-apps [top=100] [--free] [--windows=true] [--osx] [--linux]
        lancache-autofill steam:queue-users-recent-apps <steam id 64> [<steam id 64>...] [--windows=true] [--osx] [--linux]
        lancache-autofill steam:queue-users-recent-apps <steam-ids.txt> [--windows=true] [--osx] [--linux]
        
        lancache-autofill steam:show-queue [<status>]
        lancache-autofill steam:start-downloading
        lancache-autofill steam:dequeue [--app_id=] [--platform=] [--status=] [--message=]
        lancache-autofill steam:requeue [status=failed] [--message=]

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
