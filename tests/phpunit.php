<?php

/**
 * PHPUnit For Drupal
 * 
 * @author Damien Loté <damien.lote@gmail.com>
 * 
 * 
 * @package    Drupal
 * @subpackage PHPUnit
 * 
 * 
 */
require_once 'PHPUnit/Autoload.php';
require_once 'classes/PhpUnitWrapper.php';

define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');
define('DS', DIRECTORY_SEPARATOR);

class Drupal_PHPUnit {

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var string
     */
    protected $_version = '1.0.0';


    /**
     * List all Longs Commands Options 
     * 
     * Syntax : if finish with "=", waiting for argument
     * @var array
     */
    protected $longOptions = array(
        'site=' => null,
        'target-folder=' => null,
        'ini-file=' => null,
        'help' => null,
        'version' => null
    );

    /**
     * @param boolean $exit
     */
    public static function main($exit = TRUE) {
        $command = new Drupal_PHPUnit;
        return $command->run($_SERVER['argv'], $exit);
    }

    /**
     * @param array   $argv
     * @param boolean $exit
     */
    public function run(array $argv, $exit = TRUE) {
        $this->handleArguments($argv);

        if(!isset($this->arguments['site'])){
            echo "You have to specify your site name (--site) \n";
            exit();
        }

        $rootDir = dirname(__DIR__);
        $siteDir = $rootDir . DS . 'sites' . DS . $this->arguments['site'];

        define('DRUPAL_ROOT', $rootDir);

        $this->iniConfig($siteDir . DS . $this->arguments['iniFile']);
        require_once DRUPAL_ROOT . '/includes/bootstrap.inc';

        $argv = array();
        array_push($argv, '/usr/bin/phpunit');
        array_push($argv, '--colors');
        array_push($argv, $siteDir . DS . $this->arguments['targetFolder']);

        $command = new PHPUnit_TextUI_Command();
        $command->run($argv);
    }

    /**
     * Handles the command-line arguments.
     *
     * @param array $argv
     */
    protected function handleArguments(array $argv) {
        try {
            $this->options = PHPUnit_Util_Getopt::getopt(
                            $argv, 's:tf:i:v:h', array_keys($this->longOptions)
            );
        } catch (PHPUnit_Framework_Exception $e) {
            PHPUnit_TextUI_TestRunner::showError($e->getMessage());
        }

        foreach ($this->options[0] as $option) {
            switch ($option[0]) {
                // site
                case '-s':
                case '--site': {
                        $this->arguments['site'] = $option[1];
                    }
                    break;

                // target folder
                case '-tf':
                case '--target-folder': {
                        $this->arguments['targetFolder'] = $option[1];
                    }
                    break;

                // init file
                case '-i':
                case '--ini-file': {
                        $this->arguments['iniFile'] = $option[1];
                    }
                    break;

                // help
                case 'v':
                case '--version': {
                    echo $this->_version . "\n";
                    exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
                }

                // help
                case 'h':
                case '--help': {
                    $this->showHelp();
                    exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
                }
                
                default: {
                        $optionName = str_replace('--', '', $option[0]);

                        if (isset($this->longOptions[$optionName])) {
                            $handler = $this->longOptions[$optionName];
                        } else if (isset($this->longOptions[$optionName . '='])) {
                            $handler = $this->longOptions[$optionName . '='];
                        }

                        if (isset($handler) && is_callable(array($this, $handler))) {
                            $this->$handler($option[1]);
                        }
                    }
            }
        }

        if (!isset($this->arguments['targetFolder']) || $this->arguments['targetFolder'] == '') {
            $this->arguments['targetFolder'] = 'modules';
        }

        if (!isset($this->arguments['iniFile']) || $this->arguments['iniFile'] == '') {
            $this->arguments['iniFile'] = 'pu-test.ini';
        }
    }

    /**
     * Read .ini config file
     * 
     * @param string $path
     */
    protected function iniConfig($path) {
        $ini = parse_ini_file($path, true);
        /**
         * @todo : permettre le choix de l'environement
         */
        array_push($_SERVER, $ini['default']);
        foreach ($ini['default'] as $key => $value) {
            $_SERVER[$key] = $value;
        }

        $_SERVER['SCRIPT_NAME'] = $path;
    }

    /**
     * Show the help message.
     */
    protected function showHelp()
    {
        echo "Version ". $this->_version . "\n";
        print <<<EOT
Usage: php tests/phpunit.php --site <sitename>

  --site <sitename>         Required. Folder sitename
  --target-folder <folder>  Folder where are your tests (default : modules )
  --ini-file <file.ini>     Specify custom config file (default : pu-test.ini)

  -h|--help                 Prints this usage information.
  --version                 Prints the version and exits.

EOT;
    }
}

$Drupal_PHPUnit = new Drupal_PHPUnit();
$Drupal_PHPUnit->run($_SERVER['argv']);
