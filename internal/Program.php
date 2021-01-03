<?php
require_once "ProgramFactory.php";

abstract class Program {
    protected $programBefore;
    protected $programAfter;

    protected function before($extraData) {}
    protected function after($extraData) {}
    abstract protected function getCommand($extraData);
    public static function execute($name, $extraData) {
        $program = ProgramFactory::getByName($name);
        if (isset($program->programBefore)) {
            if (Program::execute($program->programBefore, $extraData) === false) {
                return false;
            }
        }
        $program->before($extraData);
        if ($program->exec($extraData) === false) {
            return false;
        }
        $program->after($extraData);

        if (isset($program->programAfter)) {
            if (Program::execute($program->programAfter, $extraData) === false) {
                return false;
            }
        }
        return true;
    }

    private function exec($extraData) {
        $descriptorspec = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"] // stderr
        ];
        $proc = proc_open($this->getCommand($extraData), $descriptorspec, $pipes);
        $success = false;
        if (is_resource($proc)) {
            fwrite($pipes[0], $extraData);
            fclose($pipes[0]);
            while (!feof($pipes[1])) {
                echo fread($pipes[1], 4096);
            }
            fclose($pipes[1]);
            if(stream_get_meta_data($pipes[2])["unread_bytes"] === 0) {
                $success = true;
            } else {
                echo stream_get_contents($pipes[2]);
            }
            fclose($pipes[2]);
            proc_close($proc);
        }
        return $success;
    }
}
