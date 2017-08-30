<?php

namespace Zeropingheroes\LancacheAutofill\Services\SteamCmd;

use InvalidArgumentException;
use Zeropingheroes\LancacheAutofill\Services\SteamCmd\Exceptions\SteamCmdException;

class SteamCmd
{
    /**
     * The path to SteamCMD
     *
     * @var $steamCmdPath
     */
    private $steamCmdPath;

    /**
     * The Steam account username
     *
     * @var $username
     */
    private $username = 'anonymous';

    /**
     * The Steam account password
     *
     * @var $password
     */
    private $password;

    /**
     * The Steam account SteamGuard code
     *
     * @var $guard
     */
    private $guard;

    /**
     * The Steam app ID to perform an action on
     * @var $appId
     */
    private $appId;

    /**
     * The platform to download an app for
     *
     * @var $platform
     */
    private $platform;

    /**
     * The directory to install or validate an app
     *
     * @var $directory
     */
    private $directory;

    /**
     * The permissible platforms.
     *
     * @var array
     */
    const PLATFORMS = ['windows', 'osx', 'linux'];

    public function __construct(string $steamCmdPath)
    {
        if (!file_exists($steamCmdPath)) {
            throw new InvalidArgumentException('The specified path does not exist');
        }

        $this->steamCmdPath = $steamCmdPath;

    }

    /**
     * Set the Steam account username
     *
     * @param $username
     * @return $this
     */
    public function login(string $username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set the Steam account password
     *
     * @param $password
     * @return $this
     */
    public function password(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set the Steam account SteamGuard code
     *
     * @param $guard
     * @return $this
     */
    public function guard(string $guard)
    {
        $this->guard = $guard;

        return $this;
    }

    /**
     * Set the app ID to perform an action on
     *
     * @param $appId
     * @return $this
     */
    public function appId(int $appId)
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * Set the platform to install an app for
     *
     * @param $platform
     * @return $this
     * @throws InvalidArgumentException
     */
    public function platform(string $platform)
    {
        if (array_diff($platform, $this::PLATFORMS)) {
            throw new InvalidArgumentException('Invalid platform specified. Available platforms are: '.implode(' ',
                    $this::PLATFORMS));
        }

        $this->platform = $platform;

        return $this;
    }

    /**
     * Set the directory to use for installing
     *
     * @param $directory
     * @return $this
     * @throws InvalidArgumentException
     */
    public function directory(string $directory)
    {
        if (!is_writable($directory)) {
            throw new InvalidArgumentException('Specified install directory is not writable');
        }
        $this->directory = $directory;

        return $this;
    }

    /**
     * Install or update the specified app
     */
    public function update()
    {
        $arguments = [];

        if (!$this->appId) {
            throw new SteamCmdException('No app ID specified to install or update');
        }

        if (!$this->directory) {
            throw new SteamCmdException('No directory specified to install into or update');
        }

        // Login using cached credentials
        if ($this->username && !$this->password) {
            $arguments = [
                'login' => $this->username,
                '@NoPromptForPassword' => 1,
            ];
        }

        // Login using username and password (Steam Guard disabled)
        if ($this->username && $this->password && !$this->guard) {
            $arguments = [
                'login' => $this->username.' '.$this->password,
            ];
        }

        // Login using username, password and Steam Guard code
        if ($this->username && $this->password && $this->guard) {
            $arguments = [
                'login' => $this->username.' '.$this->password.' '.$this->guard,
            ];
        }

        // If no platform specified, default to Windows
        $arguments['@sSteamCmdForcePlatformType'] = $this->platform ?? 'windows';

        // Set SteamCMD arguments
        $arguments['force_install_dir'] = $this->directory;
        $arguments['app_license_request'] = $this->appId;
        $arguments['app_update'] = $this->appId;
        $arguments['quit'] = null;

        // Build argument string
        foreach ($arguments as $argument => $value) {
            $argumentString .= "+$argument $value ";
        }

        // Start SteamCMD with the arguments, using "unbuffer"
        // as SteamCMD buffers output when it is not run in a
        // tty, which prevents us showing output line by line
        $update = new Process('unbuffer '.$this->steamCmdPath.' '.$argumentString);

        // Set a long timeout as updating could take a while
        $update->setTimeout(14400);
        $update->setIdleTimeout(60);

        // Return the process, allowing the client to execute it
        return $update;
    }
}