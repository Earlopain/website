<?php

require_once "config.php";

class Logger {
    private static $logLevels = [
        LOG_EMERG => "EMERGENCY",
        LOG_ALERT => "ALERT",
        LOG_CRIT => "CRITICAL",
        LOG_ERR => "ERROR",
        LOG_WARNING => "WARNING",
        LOG_NOTICE => "NOTICE",
        LOG_INFO => "INFO",
        LOG_DEBUG => "DEBUG"
    ];

    private static $loggers = [];

    private $fileHandle;
    private $treshhold;

    public static function get($filePath, string $treshhold = LOG_DEBUG): self {
        if (!isset(self::$loggers[$filePath])) {
            self::$loggers[$filePath] = new self($filePath, $treshhold);
        }
        return self::$loggers[$filePath];
    }

    private function __construct(string $filePath, string $treshhold) {
        if (strpos($filePath, "/") === 0) {
            throw new Error("No absolute filepaths allowed: " . $filePath);
        }
        $logFolder = Config::get("logfolder");
        //If no trailing slash, add one
        if (substr($logFolder, -1) !== "/") {
            $logFolder .= "/";
        }
        $filePath = $logFolder . $filePath;

        if (file_exists($filePath) && !is_writable($filePath)) {
            throw new Error("Logfile is not writable\n" . $filePath);
        }
        $this->fileHandle = fopen($filePath, "a");
        $this->treshhold = $treshhold;
    }

    public function log(int $level, string $message, $object = null) {
        if ($this->treshhold < $level) {
            return;
        }
        $logThis = "[" . $this->getTimestamp() . "] [" . self::$logLevels[$level] . "] " . $message;
        if ($object !== null) {
            $objectString = print_r($object, true);
            $logThis .= " " . str_replace("\n", "\n\t", $objectString);
        }
        fwrite($this->fileHandle, $logThis . "\n");
    }

    private function getTimestamp() {
        $currentTime = microtime(true);
        $milli = round($currentTime - floor($currentTime), 7);
        list(, $decimal) = explode('.', $milli);
        $date = new DateTime(date('Y-m-d H:i:s.' . $decimal, $currentTime));

        return $date->format("Y-m-d H:i:s.u");
    }
}
