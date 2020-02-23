<?php

class Logger {
    private $logLevels = [
        LogLevel::CRITICAL => 0,
        LogLevel::ERROR => 1,
        LogLevel::WARNING => 2,
        LogLevel::INFO => 3,
        LogLevel::DEBUG => 4
    ];

    private $fileHandle;
    private $treshhold;

    public function __construct(string $filePath, string $treshhold = LogLevel::DEBUG) {
        if (file_exists($filePath) && !is_writable($filePath)) {
            throw new Error("Logfile is not writable\n" . $filePath);
        }
        $this->fileHandle = fopen($filePath, "a");
        $this->treshhold = $treshhold;
    }

    public function log(string $level, string $message, $object = null) {
        if ($this->logLevels[$this->treshhold] < $this->logLevels[$level]) {
            return;
        }
        $logThis = "[" . $this->getTimestamp() . "] [" . $level . "] " . $message;
        if ($object !== null) {
            $objectString = print_r($object, true);
            $logThis .= str_replace("\n", "\n\t", $objectString);
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

class LogLevel {
    const CRITICAL = "CRITICAL";
    const ERROR = "ERROR";
    const WARNING = "WARNING";
    const INFO = "INFO";
    const DEBUG = "DEBUG";
}
