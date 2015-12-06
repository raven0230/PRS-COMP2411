<?php

function __autoload($class_path)
{
    include __DIR__ . "/../" . str_replace("\\", "/", $class_path) . ".php";
}
