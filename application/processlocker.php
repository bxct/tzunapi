<?php

/**
 * Handles flags for locking system resrouces
 * 
 * @author Anton Matiyenko <amatiyenko@gmail.com>
 */
class ProcessLocker {
    
    /**
     * Holds the IPC id of shared memory segment
     * 
     * @var int
     */
    private static $shmSegment = false;
    
    /**
     * Holds the ID of current session's var inside the segment with locks
     * 
     * @var integer 
     */
    private static $shmSegmentVarId = false;
    
    /**
     * Detects if a process with given PID is running in the system
     */
    private static function isProcessRunning($pid) {
        
        //Check the the existence of process in POSIX-compatible systems - most common
        if(function_exists('posix_getpgid')) {
            return !empty(posix_getpgid($pid));
        }
        
        //Fallback solution - could work on Linux/Unix or definitive false in other cases
        return file_exists( "/proc/$pid" );
    }
    
    /**
     * Stores a PID in shared memory to make system know resources must be treated as locked for a while
     * 
     * @param integer $segmentId the int ID of shared memomry segment to use. It's better to use something like 
     * @param integer $maxParallelProcesses
     * @return boolean
     */
    public static function lock($segmentId = false, $maxParallelProcesses = 1) {
        if ($segmentId) {
            //Retrieve or create shared memory segment
            if(self::$shmSegment = shm_attach($segmentId, (192+50)*$maxParallelProcesses, 0644)) {
                if ($maxParallelProcesses) {
                    for ($i = 1; $i <= $maxParallelProcesses; $i++) {
                        if (!shm_has_var(self::$shmSegment, $i) || empty($pid = shm_get_var(self::$shmSegment, $i)) || !self::isProcessRunning($pid)) {
                            shm_put_var(self::$shmSegment, $i, getmypid());
                            self::$shmSegmentVarId = $i;
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
    
    /**
     * Try to lock resources within given timeout using given number of attempts
     * 
     * @param integer  $segmentId
     * @param integer $maxParallelProcesses
     * @param integer $allowedTimeoutInSeconds
     * @param integer $attempts
     * 
     * @return boolean TRUE if succeeded
     */
    public static function lockAndWaitIfNecessary($segmentId = false, $maxParallelProcesses = 1, $allowedTimeoutInSeconds = 60, $attempts = 12) {
        //If lock is successful => proceed immediately
        if(self::lock($segmentId, $maxParallelProcesses)) {
            return true;
        }
        //Try to make given number of attempts during alotted timeout
        for($i=1; $i<=$attempts; $i++) {
            sleep(floor($allowedTimeoutInSeconds/$attempts));
            if(self::lock($segmentId, $maxParallelProcesses)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Try to lock resources until success or until process is interrupted
     * 
     * @param integer $segmentId
     * @param integer $maxParallelProcesses
     * @param integer $attemptIntervalInSeconds
     * 
     * @return boolean true is something is returned at all
     */
    public static function lockWhateverItTakes($segmentId = false, $maxParallelProcesses = 1, $attemptIntervalInSeconds = 1) {
        if(!ini_get('safe_mode') && function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        while(!self::lock($segmentId, $maxParallelProcesses)) {
            sleep(1);
        }
        return true;
    }
    
    /**
     * Erases the lock mark stored by current process
     * 
     * @return boolean
     */
    public static function unlock() {
        if(self::$shmSegment && self::$shmSegmentVarId) {
            if(shm_has_var(self::$shmSegment, self::$shmSegmentVarId)) {
                shm_remove_var(self::$shmSegment , self::$shmSegmentVarId);
                shm_detach(self::$shmSegment);
            }
            self::$shmSegment = false;
            self::$shmSegmentVarId = false;
            return true;
        }
        return false;
    }

}
