<?php
/**
 * A registry to track potential changes to files we have parsed
 *
 * This could extend Zend_Registry or something similar if we desired.
 */
class Scisr_ChangeRegistry
{

    /**
     * Stores the raw data we've been given
     * @var array
     */
    private static $_data;

    /**
     * Set a value
     * @param string $name the name of the value
     * @param mixed $value the value
     */
    public static function set($name, $value)
    {
        self::$_data[$name] = $value;
    }

    /**
     * Get a value
     * @param string $name the name of the value
     * @return mixed the value stored for this name
     */
    public static function get($name)
    {
        return (isset(self::$_data[$name]) ? self::$_data[$name] : null);
    }

    /**
     * Clear all data stored in the registry
     */
    public static function clearAll()
    {
        self::$_data = array();
    }

    /**
     * Set a potential change to a file
     * @param string $filename the filename
     * @param int $line the line number that our change begins on
     * @param int $column the column number that our change begins at
     * @param int $length the length of the original text to be replaced
     * @param string $replacement the text to insert in its place
     * @param boolean $tentative true if this change is something we "aren't
     * sure about" - for example, a word match found in a string. Changes marked 
     * tentative will only be acted upon if we are in "aggressive" mode.
     */
    public static function addChange($filename, $line, $column, $length, $replacement, $tentative=false)
    {
        $file = self::getFile($filename);
        $file->addEdit($line, $column, $length, $replacement, $tentative);
        self::setFile($file);
    }

    /**
     * Get the stored file object for a given filename
     * @param string $filename the filename
     * @return Scisr_File
     */
    protected static function getFile($filename)
    {
        $changes = self::getChanges();
        // We just store our pending changes as file objects themselves. If one
        // doesn't exist yet for this file, create it
        if (!isset($changes[$filename])) {
            $changes[$filename] = new Scisr_File($filename);
        }
        return $changes[$filename];
    }

    /**
     * Save the stored file object
     * @param Scisr_File the file to save
     */
    protected static function setFile($file)
    {
        $changes = self::getChanges();
        $changes[$file->filename] = $file;
        self::set('storedChanges', $changes);
    }

    /**
     * Get stored file changes
     * @return array(Scisr_File)
     */
    private static function getChanges()
    {
        $changes = self::get('storedChanges');
        if (!is_array($changes)) {
            $changes = array();
        }
        return $changes;
    }

    /**
     * Set a file to be renamed
     * @param string $oldName the path to the file to be renamed
     * @param string $newName the new path to give it
     */
    public static function addRename($oldName, $newName)
    {
        $file = self::getFile($oldName);
        $file->rename($newName);
        self::setFile($file);
    }

}
