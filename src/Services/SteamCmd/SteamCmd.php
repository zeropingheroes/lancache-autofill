<?php

namespace Zeropingheroes\LancacheAutofill\Services\SteamCmd;

use InvalidArgumentException;
use Symfony\Component\Process\Process;
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
     * The Steam app ID to perform an action on
     * @var $appId
     */
    private $appId;

    /**
     * The arguments to execute SteamCMD with
     *
     * @var $arguments
     */
    private $arguments = [];

    /**
     * The permissible platforms.
     *
     * @var array
     */
    const PLATFORMS = ['windows', 'osx', 'linux'];

    public function __construct(string $steamCmdPath)
    {
        $this->steamCmdPath = $steamCmdPath;
    }

    /**
     * Set the Steam account username
     *
     * @param $username
     * @return $this
     */
    public function login(string $username, string $password = '', string $guard = '')
    {
        $password = addslashes($password);

        // Login using cached credentials
        if ($username && !$password) {
            $this->addArgument('login', $username);
        }

        // Login using username and password (Steam Guard disabled)
        if ($username && $password && !$guard) {
            $this->addArgument('login', "$username \"$password\"");
        }

        // Login using username, password and Steam Guard code
        if ($username && $password && $guard) {
            $this->addArgument('login', "$username \"$password\" $guard");
        }

        // Regardless of how the user is logging in, do not prompt for password interactively
        $this->addArgument('@NoPromptForPassword', 1);

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
        if (!in_array($platform, $this::PLATFORMS)) {
            throw new InvalidArgumentException('Invalid platform specified. Available platforms are: '.implode(' ',
                    $this::PLATFORMS));
        }

        $this->addArgument('@sSteamCmdForcePlatformType', $platform);

        return $this;
    }

    /**
     * Set the directory to use for installing or validating
     *
     * @param $directory
     * @return $this
     * @throws InvalidArgumentException
     */
    public function directory(string $directory)
    {
        $this->addArgument('force_install_dir', $directory);

        return $this;
    }

    /**
     * Install or update the specified app
     *
     * @param int $appId the appID to update
     * @return Process
     * @throws SteamCmdException
     */
    public function update(int $appId)
    {
        if (!array_key_exists('force_install_dir', $this->arguments)) {
            throw new SteamCmdException('No directory specified');
        }

        // If no platform is specified, default to windows
        if (!array_key_exists('@sSteamCmdForcePlatformType', $this->arguments)) {
            $this->addArgument('@sSteamCmdForcePlatformType', 'windows');
        }

        // Request a license for the app, to ensure free apps are downloaded
        $this->addArgument('app_license_request', $appId);

        // Specify which app to update
        $this->addArgument('app_update', $appId);

        return $this;
    }

    /**
     * Run SteamCMD with the arguments
     *
     * @param int $timeout
     * @param int $idleTimeout
     *
     * @return Process
     */
    public function run($timeout = 14400, $idleTimeout = 600)
    {
        // Always quit when finished
        $this->addArgument('quit');

        // Start SteamCMD with the arguments, using "unbuffer"
        // as SteamCMD buffers output when it is not run in a
        // tty, which prevents us showing output line by line
        $process = new Process("unbuffer $this->steamCmdPath {$this->arguments()}");

        $process->setTimeout($timeout);
        $process->setIdleTimeout($idleTimeout);

        // Return the process, allowing the client to execute it
        return $process;
    }

    /**
     * Add an argument
     *
     * @param string $argument
     * @param string $value
     * @return void
     */
    private function addArgument(string $argument, $value = '')
    {
        $this->arguments[$argument] = $value;
    }

    /**
     * Get the formatted arguments ready for execution
     *
     * @return string
     */
    private function arguments()
    {
        $arguments = '';
        // Build argument string
        foreach ($this->arguments as $argument => $value) {
            $arguments .= "+$argument $value ";
        }

        return $arguments;
    }


}