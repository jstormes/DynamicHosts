#!/usr/bin/php
<?php

/**
 * Class to convert {$path}/directories/subdirectories into
 * subdomains.domains.  Also checks that for new files less than
 * $dirtyTime old.
 * 
 * @author jstormes
 *
 */
class ZendDomains {
    
    /** 
     * Dirty flag set if any subdirectory is only $dirtyTime old
     * 
     * @var unknown
     */
    public $dirty = false;
    
    /**
     * Array of new domains
     * 
     * @var unknown
     */
    public $domains = array();
    
    function __construct($path = '/usr/local/zend/apache2/htdocs/', $dirtyTime = 30) {
        // Get the domain directories
        $domains = array_filter(glob($path.'*'), 'is_dir');
        
        // Get the new domains into an array
        foreach($domains as $domain) {
            $subDomains = array_filter(glob($path.basename($domain).'/*'), 'is_dir');
            foreach($subDomains as $subDomain) {
                // if we have files that have been created in the last 30 seconds
                // we have a "dirty" hosts file so set the flag to update the hosts file.
                if ((time()-filectime($subDomain))<$dirtyTime) $this->dirty=true;
                $this->domains[] = basename($subDomain).".".basename($domain);
            }
        }
    }
}

/**
 * Class to manage the /etc/hosts file
 * 
 * @author jstormes
 *
 */
class hosts {
    
    /**
     * Host file location usualy /etc/hosts
     * 
     * @var unknown
     */
    private $hostFile = '';
    
    /**
     * Array of hosts file lines
     * 
     * @var unknown
     */
    private $lines = array();
    
    /**
     * Marker inside the hosts file to start new hosts
     * 
     * @var unknown
     */
    private $newHostsMarker = "# Begin dynamic DNS\n";
    
    function __construct($hostFile = '/etc/hosts') {
        $this->hostFile = $hostFile;
    }
    
    /**
     * Get the existing hosts file lines, ignore any lines
     * after our marker.
     */
    function getExistingHosts(){
        $handle = fopen($this->hostFile, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if ($line==$this->newHostsMarker)
                    break;
                $this->lines[]=$line;
            }
        }
        fclose($handle);
    }
    
    /**
     * Add new hosts to our lines from the passed array.
     * 
     * @param unknown $domains
     */
    function addNewHosts($domains) {
        // Make the first new line our marker
        $this->lines[] = $this->newHostsMarker;
        
        // Append our domains into the lines
        foreach($domains as $domain) {
            $this->lines[] = "127.0.0.1\t{$domain}\n";
        }
    }
    
    /**
     * Save our lines back into the hostFile.
     */
    function saveNewHosts() {
        $handle = fopen($this->hostFile,"w");
        if ($handle) {
            foreach($this->lines as $line) {
                fwrite($handle,$line);
            }
        }
        fclose($handle);
    }
}


/*
 * Begin Entry point
 */
echo "argv cound ".count($argv);
if (count($argv)==2)
    $ZendDomains = new ZendDomains($argv[1]);
else
    //$ZendDomains = new ZendDomains('/usr/local/zend/apache2/htdocs/');
    $ZendDomains = new ZendDomains('/var/www/');


// If we have directories that are less than 30 seconds old update
// the hosts file.
if ($ZendDomains->dirty) {

    $hosts = new hosts('/etc/hosts');
    
    $hosts->getExistingHosts();
    $hosts->addNewHosts($ZendDomains->domains);
    $hosts->saveNewHosts();
}

