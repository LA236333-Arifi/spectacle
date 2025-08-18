<?php

class ViewUtils
{
    public static function displayOptions($options)
    {
        foreach ($options as $index => $value)
        {
            echo '<option value="' 
            . htmlspecialchars($index, ENT_QUOTES, 'UTF-8') 
            . '">' 
            . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') 
            . '</option>';
        }
    }
}
