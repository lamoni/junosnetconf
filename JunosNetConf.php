<?php namespace Lamoni\JunosNetConf;

use Lamoni\NetConf\NetConf;
use Lamoni\NetConf\NetConfAuth\NetConfAuthAbstract;

/**
 * Class JunosNetConf
 * @package Lamoni\JunosNetConf
 */
class JunosNetConf
{
    /**
     * Holds the hostname
     *
     * @var string
     */
    protected $hostname;

    /**
     * Holds our instance of the NetConf class
     *
     * @var NetConf
     */
    protected $netconf;

    /**
     * Build our JunosNetConf instance.
     *
     * @param $hostname
     * @param NetConfAuthAbstract $netconfAuth
     */
    public function __construct($hostname, NetConfAuthAbstract $netconfAuth, array $options=[])
    {

        $this->hostname = $hostname;

        $this->netconf = new NetConf($hostname, $netconfAuth, $options);

    }

    /**
     * Returns our NetConf instance.
     *
     * @return NetConf
     */
    public function netConf()
    {

        return $this->netconf;

    }

    /**
     * Base function for sending a command (in CLI syntax) to the device.
     *
     * @param $command
     * @param $format (xml | text)
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function operationalCommandRaw($command, $format)
    {

        return $this->netConf()->sendRPC("<command format='{$format}'>{$command}</command>");

    }

    /**
     * Sends command (in CLI syntax) to the device and requests the output to be in text (CLI) format.
     *
     * @param $command
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function operationalCommandText($command)
    {

        return $this->operationalCommandRaw($command, 'text');

    }

    /**
     * Sends command (in CLI syntax) to the device and requests the output to be in XML format.
     *
     * @param $command
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function operationalCommandXML($command)
    {

        return $this->operationalCommandRaw($command, 'xml');

    }

    /**
     * Base method for loading configuration into the device's candidate configuration.
     *
     * @param string $configData
     * @param string $configNode
     * @param array $customAttributes
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function loadConfigurationRaw($configData="", $configNode="", array $customAttributes)
    {
        if ($configData !== "" && $configNode !== "") {

            $loadConfig = "<load-configuration>".
                                "<{$configNode}>".
                                    "{$configData}".
                                "</{$configNode}>".
                          "</load-configuration>";

        }
        else {

            $loadConfig = "<load-configuration/>";

        }

        $loadConfig = new \SimpleXMLElement($loadConfig);

        foreach ($customAttributes as $attributeName=>$attributeValue) {

            $loadConfig->addAttribute($attributeName, $attributeValue);

        }

        return $this->netConf()->sendRPC($loadConfig->asXML());

    }

    /**
     * Loads the device's rescue configuration.
     * Equivalent to "rollback rescue".
     *
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function loadConfigurationRescue()
    {

        return $this->loadConfigurationRaw(
            "",
            "",
            ['rescue' => 'rescue']
        );

    }

    /**
     * Rolls the configuration back to the rollback with the ID of $rollbackID.
     * Equivalent to "rollback $rollbackID".
     *
     * @param $rollbackID
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function loadConfigurationRollback($rollbackID)
    {

        return $this->loadConfigurationRaw(
            "",
            "",
            [
                'rollback' => $rollbackID
            ]
        );

    }

    /**
     * Loads the configuration given in file found at URL.
     * Can be HTTP, FTP, SCP, or device local file.
     *
     * @param $url
     * @param string $action
     * @param string $format
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function loadConfigurationURL($url, $action='merge', $format='text')
    {

        return $this->loadConfigurationRaw(
          "",
          "",
          [
              'url'    => $url,
              'action' => $action,
              'format' => $format
          ]
        );

    }

    /**
     * Loads the set commands given in file found at URL.
     * Can be HTTP, FTP, SCP, or device local file.
     *
     * @param $url
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function loadConfigurationSetURL($url)
    {

        return $this->loadConfigurationURL($url, 'set', 'text');

    }

    /**
     * Loads the XML configuration given in $configData.
     *
     * @param $configData
     * @param $action
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function loadConfigurationXML($configData, $action)
    {

        return $this->loadConfigurationRaw(
            $configData,
            'configuration',
            [
                'action' => $action,
                'format' => 'xml'
            ]
        );

    }

    /**
     * Loads curly-brace formatted configuration given in $configData.
     *
     * @param $configData
     * @param $action
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function loadConfigurationCurly($configData, $action)
    {

        return $this->loadConfigurationRaw(
            $configData,
            'configuration-text',
            [
                'action' => $action
            ]
        );

    }

    /**
     * Loads the set commands given in the array $configData.
     *
     * @param array $configData
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function loadConfigurationSet(array $configData)
    {

        return $this->loadConfigurationRaw(
            implode("\n", $configData),
            'configuration-set',
            [
                'action' => 'set'
            ]
        );

    }

    /**
     * Sends the <abort/> call for aborting a process currently in progress.
     *
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function sendAbort()
    {

        return $this->netConf()->sendRPC('<abort/>');

    }

    /**
     * Returns the MD5 checksum of the file at $filePath.
     *
     * @param $filePath
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function getChecksumInformation($filePath)
    {

        return $this->netConf()->sendRPC(
            "<get-checksum-information>".
                "<path>".
                    "{$filePath}".
                "</path>".
            "</get-checksum-information>"
        );

    }

    /**
     * Sends the <open-configuration/> call to the device.
     * Equivalent to "edit private" or "configure private".
     *
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function openConfiguration()
    {

        return $this->netConf()->sendRPC(
            "<open-configuration>".
                "<private/>".
            "</open-configuration>"
        );

    }

    /**
     * Sends the <close-configuration/> call to the device.
     * Equivalent to exiting a "edit private" or "configure private" session.
     *
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function closeConfiguration()
    {

        return $this->netConf()->sendRPC("<close-configuration/>");

    }

    /**
     * Sends the <lock-configuration/> call to the device.
     * Equivalent to a "edit exclusive" or "configure exclusive".
     *
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function lockConfiguration()
    {

        return $this->netConf()->sendRPC('<lock-configuration/>');

    }

    /**
     * Sends the <unlock-configuration/> call to the device.
     * Equivalent to exiting a "edit exclusive" or "configure exclusive" session.
     *
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function unlockConfiguration()
    {

        return $this->netConf()->sendRPC('<unlock-configuration/>');

    }

    /**
     * Sends the <request-end-session/> call to the device.
     * Requests the device to end the current NETCONF session.
     *
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function requestEndSession()
    {

        return $this->netConf()->sendRPC('<request-end-session/>');

    }

    /**
     * Base for committing the candidate configuration to the active configuration.
     *
     * @param array $customParams
     * @param bool $synchronize
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function commitConfigurationRaw(array $customParams, $synchronize=true)
    {

        if ($synchronize === true) {

            $customParams['synchronize'] = '';

        }

        $commitConfig = new \SimpleXMLElement('<commit-configuration/>');

        foreach ($customParams as $paramName => $paramValue) {

            $commitConfig->addChild(
                $paramName,
                $paramValue
            );

        }

        return $this->netConf()->sendRPC($commitConfig->asXML());

    }

    /**
     * Commits the candidate configuration.
     * Equivalent to "commit" or "commit synchronize", based on $synchronize's value.
     *
     * @param bool $synchronize
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function commitConfiguration($synchronize=true)
    {

        return $this->commitConfigurationRaw([], $synchronize);

    }

    /**
     * Commit checks the configuration to verify the validity of the candidate configuration.
     * Equivalent to "commit check" or "commit check synchronize", based on $synchronize's value.
     *
     * @param bool $synchronize
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function commitConfigurationCheck($synchronize=true)
    {

        return $this->commitConfigurationRaw(
            [
                'check' => ''
            ],
            $synchronize
        );

    }

    /**
     * Commits the candidate configuration to the active configuration, with a comment.
     * Equivalent to "commit comment $comment" or "commit synchronize comment $comment", based
     * on $synchronize's value.
     *
     * @param $comment
     * @param bool $synchronize
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function commitConfigurationWithComment($comment, $synchronize=true)
    {

        return $this->commitConfigurationRaw(
            [
                'log' => $comment
            ],
            $synchronize
        );

    }

    /**
     * Commits the candidate configuration to the active configuration at a certain time, with a comment.
     * Equivalent to "commit at-time $atTime comment $comment" or "commit synchronize at-time $atTime comment $comment",
     * based on $synchronize's value.
     *
     * @param $atTime
     * @param $comment
     * @param bool $synchronize
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function commitConfigurationAtTimeWithComment($atTime, $comment, $synchronize=true)
    {

        return $this->commitConfigurationRaw(
            [
                'at-time' => $atTime,
                'log'     => $comment
            ],
            $synchronize
        );

    }

    /**
     * Commits the candidate configuration to the active configuration, with the requirement that the
     * configuration be rolled back to its previous state if no follow-up commit is sent.  A commit comment
     * is also allowed but not required.
     * Equivalent to "commit confirmed" or "commit confirmed synchronize", based on $synchronize's value
     *
     * @param int $confirmTimeout
     * @param string $comment
     * @param bool $synchronize
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function commitConfigurationConfirmed($confirmTimeout=600, $comment='', $synchronize=true)
    {

        return $this->commitConfigurationRaw(
            [
                'confirmed' => '',
                'confirm-timeout' => $confirmTimeout,
                'log' => $comment
            ],
            $synchronize
        );

    }

    /**
     * Base for getting the device's configuration.
     *
     * @param $configData
     * @param array $customAttributes
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function getConfigurationRaw($configData, array $customAttributes=[])
    {

        $getConfig = new \SimpleXMLElement(
            "<get-configuration>".
                "{$configData}".
            "</get-configuration>"

        );

        foreach ($customAttributes as $attrName => $attrValue) {

            $getConfig->addAttribute($attrName, $attrValue);

        }

        return $this->netConf()->sendRPC($getConfig->asXML());

    }

    /**
     * Gets the configuration under XML stanza $configData from $database.
     * Equivalent to "show configuration"
     *
     * @param string $configData
     * @param string $database (candidate | committed)
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function getConfigurationCurly($configData="", $database='committed')
    {

        return $this->getConfigurationRaw(
            $configData,
            [
                'format'   => 'text',
                'database' => $database
            ]
        );

    }

    /**
     * Gets the configuration in "set command" format.
     *
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function getConfigurationSet()
    {

        return $this->operationalCommandText('show configuration | display set');

    }

    public function getConfigurationXML($configData="", $database='committed')
    {

        return $this->getConfigurationRaw(
            $configData,
            [
                'format'   => 'xml',
                'database' => $database
            ]
        );

    }

    /**
     * Gets the comparison of the candidate configuration to the configuration in a rollback.
     * Equivalent to "show | compare rollback $rollbackID".
     *
     * @param int $rollbackID
     * @param string $format
     * @param string $database
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function getConfigurationCompare($rollbackID=0, $format='xml', $database='committed')
    {

        return $this->getConfigurationRaw(
            "",
            [
                'compare'  => 'rollback',
                'rollback' => $rollbackID,
                'format'   => $format,
                'database' => $database
            ]
        );

    }

    /**
     * Gets the comparison between two different rollbacks.
     * Equivalent to a "show system rollback $rollbackID compare $compareID".
     *
     * @param $rollbackID
     * @param null $compareID
     * @return \Lamoni\NetConf\NetConfMessage\NetConfMessageRecv\NetConfMessageRecvRPC
     */
    public function getConfigurationRollbackComparison($rollbackID, $compareID=null)
    {

        $rollbackCompare = new \SimpleXMLElement('<get-rollback-information/>');

        $rollbackCompare->addChild('rollback', $rollbackID);

        if (!is_null($compareID)) {

            $rollbackCompare->addChild('compare', $compareID);

        }

        return $this->netConf()->sendRPC($rollbackCompare->asXML());

    }

}