<?php

namespace Wpmu_Client;

/**
 *  An easy way to keep in track of external processes.
 * Ever wanted to execute a process in php, but you still wanted to have somewhat controll of the process ? Well.. This is a way of doing it.
 * @compability: Linux only. (Windows does not work).
 * @author: Peec
 */
class Process
{
    /**
     * Summary of pid
     * @var 
     */
    private $pid;
    /**
     * Summary of command
     * @var 
     */
    private $command;
    /**
     * Log to write output
     * @var 
     */
    private $log = '/dev/null';


    /**
     * Summary of __construct
     * @param mixed $cl
     */
    public function __construct($cl = false, $log = false)
    {
        if ($cl != false) {
            $this->command = $cl;
        }

        if ($log != false) {
            $this->log = $log;
        }

        $this->runCom();
    }
    /**
     * Summary of runCom
     * @return void
     */
    private function runCom()
    {
        $command = 'nohup ' . $this->command . ' > ' . $this->log . ' 2>&1 & echo $!';
        exec($command, $op);
        $this->pid = (int) $op[0];
    }

    /**
     * Set the log file path for the object.
     *
     * @param string $log The absolute or relative path to the log file.
     * @return bool If the log was applied or not
     */
    public function setLog(string $log)
    {
        if (!empty($log)) {
            $this->log = $log;
            return true;
        }
        return false;
    }

    /**
     * Return the log path
     *
     * @return string The log path or empty string if not set
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Summary of setPid
     * @param mixed $pid
     * @return void
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * Summary of getPid
     * @return mixed
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Summary of status
     * @return bool
     */
    public function status()
    {
        $command = 'ps -p ' . $this->pid;
        exec($command, $op);
        if (!isset($op[1]))
            return false;
        else
            return true;
    }

    /**
     * Summary of start
     * @return bool
     */
    public function start()
    {
        if ($this->command != '')
            $this->runCom();
        else
            return true;
    }

    /**
     * Summary of stop
     * @return bool
     */
    public function stop()
    {
        $command = 'kill ' . $this->pid;
        exec($command);
        if ($this->status() == false)
            return true;
        else
            return false;
    }
}