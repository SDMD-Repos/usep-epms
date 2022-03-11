<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tomcat Configuration
    |--------------------------------------------------------------------------
    |
    | This will serve as the configuration between tomcat and JavaBridge
    |
    |
    |
    */

    'tomcat_class' => env('TOMCAT_CLASS'),
    'tomcat_resource' => env('TOMCAT_RESOURCE'),
    'tomcat_tmp' => env('TOMCAT_TMP'),
    'tomcat_cache' => env('TOMCAT_CACHE'),
    'tomcat_java' => env('TOMCAT_JAVA'),
    'file_backend_storage' => public_path() . env('FILE_BACKEND_STORAGE'),
];
