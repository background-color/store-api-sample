<?php
if (! function_exists('errmsg'))
{
    function errmsg($mes)
    {
        return ['message' => $mes];
    }
}
