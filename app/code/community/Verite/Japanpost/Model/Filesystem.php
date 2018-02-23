<?php

class Verite_Japanpost_Model_Filesystem extends Varien_Data_Collection_Filesystem
{
    /**
     * Generate item row basing on the filename
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        return array(
            'filename' => $filename,
            'basename' => basename($filename),
            'size'     => filesize($filename),
            'last_modified' => filemtime($filename)
        );
    }
}