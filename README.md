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

# Screenshots

![Queuing apps](docs/screenshots/lancache-autofill-01.png)
![Starting the download process](docs/screenshots/lancache-autofill-02.png)

# Requirements
* A working [lancache](https://github.com/zeropingheroes/lancache)
* Ubuntu 18.04 x64, configured to download via the lancache
* Sufficient disk space to (temporarily) store the downloaded content
* Dependencies detailed in *Installation* section

# Installation
1. `sudo apt update -y` 
2. `sudo apt install -y lib32gcc1 lib32stdc++6 lib32tinfo5 lib32ncurses5 php7.2-cli php7.2-mbstring php7.2-sqlite php7.2-bcmath php7.2-dom composer expect zip unzip`
3. `git clone https://github.com/zeropingheroes/lancache-autofill.git && cd lancache-autofill`
4. `./install.sh`
5. Get a Steam API key from http://steamcommunity.com/dev/apikey and add it to the `.env` file

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
        lancache-autofill steam:queue-popular-apps [<top X apps>] [--free] [--windows=true] [--osx] [--linux]
        lancache-autofill steam:queue-users-recent-apps <steam id 64> [<steam id 64>...] [--windows=true] [--osx] [--linux]
        lancache-autofill steam:queue-users-recent-apps <steam-ids.txt> [--windows=true] [--osx] [--linux]
        lancache-autofill steam:queue-users-owned-apps <steam id 64> [<steam id 64>...] [--include_free=true] [--include_paid=true] [--windows=true] [--osx] [--linux]
        lancache-autofill steam:queue-users-owned-apps <steam-ids.txt> [--include_free=true] [--include_paid=true] [--windows=true] [--osx] [--linux]
        
        lancache-autofill steam:show-queue [<status>]
        lancache-autofill steam:start-downloading
        lancache-autofill steam:dequeue [--app_id=] [--platform=] [--status=] [--message=]
        lancache-autofill steam:requeue [status=failed] [--message=]

# Limitations & Known Issues
* Steam is the only supported platform currently
* Paid apps can only be cached with access to a Steam account that owns them
* No support for forcing download of 32 bit apps
* Yes, it's written in PHP. No shame.

# SteamCMD Errors

| Error                                          | Possible Reason                                   |
| ---------------------------------------------- | ------------------------------------------------- |
| `ERROR! Timed out waiting for AppInfo update.` | Unknown                                           |
| `Login Failure: Rate Limit Exceeded (84)`      | Unknown                                           |
| `ERROR! Failed to install (No subscription)`   | Game not owned by any authorised accounts         |
| `Error! State is 0x202 after update job.`      | Not enough space to download game                 |
| `Error! State is 0x402 after update job.`      | Update required but not completed - check network |
| `Error! State is 0x602 after update job.`      | Update required but not completed - check network |

For other error codes, you can calculate the app's state(s) by converting the `0x000` code to decimal,
and finding which [AppState codes](https://github.com/lutris/lutris/blob/master/docs/steam.rst)
sum to the given code, which will give you some clues as to what's going on.

For example:
* [`0x402` to decimal](https://www.google.co.uk/search?q=0x402+to+decimal) = 1026
* 1026 is the sum of:
    * 2:    `StateUpdateRequired`
    * 1024: `StateUpdateStarted`

# Reference

* [SteamCMD Reference](https://developer.valvesoftware.com/wiki/SteamCMD)
* [SteamCMD Commands and Variables](https://github.com/dgibbs64/SteamCMD-Commands-List/blob/master/steamcmdcommands.txt)
* [Laravel Query Builder](https://laravel.com/docs/5.5/queries)
* [Laravel Artisan Console](https://laravel.com/docs/5.5/artisan)
* [Symfony Process Component](http://symfony.com/doc/current/components/process.html)
* [dotenv Reference](https://github.com/vlucas/phpdotenv/blob/master/README.md)
